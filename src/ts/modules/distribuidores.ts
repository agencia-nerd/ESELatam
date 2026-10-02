/**
 * Orquesta la sección Distribuidores: lee los países desde el DOM (marcado
 * en front-page.php), monta el planeta 3D (globe-scene.ts) y conecta el riel
 * de países con él — en ambos sentidos.
 *
 * Elegir un país (desde el riel, el <select> mobile, o tocándolo sobre el
 * planeta) marca su pastilla, hace crossfade a su panel y gira el planeta
 * hacia él. Los paneles ya están todos en el DOM, apilados en la misma celda
 * de grilla (ver .distribuidores__panel-stack en main.css): acá solo se
 * alterna la clase — el fundido y el alto estable los resuelve el CSS.
 */

import type { GlobeCountry, GlobeHandle } from './globe-scene';

// Mismo corte que el @media de .distribuidores en main.css (68.75rem).
const NARROW_QUERY = '(max-width: 1099px)';

export function initDistribuidores(section: HTMLElement): void {
  const canvasHost = section.querySelector<HTMLElement>('[data-globe-canvas]');
  const pills = Array.from(section.querySelectorAll<HTMLButtonElement>('[data-country]'));
  const panels = Array.from(section.querySelectorAll<HTMLElement>('[data-country-panel]'));
  // Mismo set de países que el riel, pero como <select> (solo visible en
  // mobile/tablet). Los datos de cada país se siguen leyendo del riel, que
  // está en el DOM en los dos breakpoints.
  const countrySelect = section.querySelector<HTMLSelectElement>('[data-country-select]');
  const loader = section.querySelector<HTMLElement>('[data-globe-loader]');
  if (!canvasHost || !pills.length) return;

  const countries: GlobeCountry[] = pills.map((pill) => ({
    slug: pill.dataset.countrySlug ?? '',
    name: pill.querySelector('.country-pill__name')?.textContent?.trim() ?? pill.dataset.countrySlug ?? '',
    lat: Number(pill.dataset.lat),
    lng: Number(pill.dataset.lng),
    iso: pill.dataset.iso ?? '',
  }));

  const defaultSlug = pills.find((p) => p.classList.contains('is-active'))?.dataset.countrySlug ?? countries[0].slug;
  let handle: GlobeHandle | null = null;

  // Punto único de selección: lo disparan el riel, el <select> y el planeta.
  // `fromTap` evita la onda automática cuando el toque ya dejó la suya.
  const selectCountry = (slug: string, fromTap = false): void => {
    const pill = pills.find((p) => p.dataset.countrySlug === slug);
    if (!pill || pill.classList.contains('is-active')) return;

    pills.forEach((p) => {
      const isActive = p === pill;
      p.classList.toggle('is-active', isActive);
      p.setAttribute('aria-selected', String(isActive));
    });

    panels.forEach((panel) => {
      panel.classList.toggle('is-active', panel.dataset.countryPanel === slug);
    });

    // La selección también puede venir del planeta: en ese caso el <select>
    // tiene que reflejarla, no quedarse en el país viejo.
    if (countrySelect && countrySelect.value !== slug) {
      countrySelect.value = slug;
    }

    handle?.focusCountry(slug, fromTap);
  };

  // El riel y el select funcionan desde ya (paneles incluidos), aunque el
  // planeta todavía esté cargando: al terminar se enfoca el país vigente.
  pills.forEach((pill) => {
    pill.addEventListener('click', () => {
      const slug = pill.dataset.countrySlug;
      if (slug) selectCountry(slug);
    });
  });

  countrySelect?.addEventListener('change', () => {
    selectCountry(countrySelect.value);
  });

  const narrow = window.matchMedia(NARROW_QUERY);

  void import('./globe-scene')
    .then(({ initGlobeScene }) =>
      initGlobeScene(
        canvasHost,
        countries,
        {
          map: canvasHost.dataset.earthMap ?? '',
          topology: canvasHost.dataset.topology ?? '',
        },
        {
          hoverTip: section.querySelector<HTMLElement>('[data-globe-hover-tip]'),
          selTip: section.querySelector<HTMLElement>('[data-globe-sel-tip]'),
          selTipLabel: section.querySelector<HTMLElement>('[data-globe-sel-label]'),
        },
        () => narrow.matches,
        (slug) => selectCountry(slug, true),
      ),
    )
    .then((globe) => {
      handle = globe;
      loader?.setAttribute('hidden', '');
      const current = pills.find((p) => p.classList.contains('is-active'))?.dataset.countrySlug ?? defaultSlug;
      globe.focusCountry(current);
    })
    .catch((error: unknown) => {
      console.error(error);
      if (loader) loader.textContent = loader.dataset.error ?? '';
    });
}
