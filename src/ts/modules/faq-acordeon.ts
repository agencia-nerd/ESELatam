/**
 * Acordeón de preguntas frecuentes (template-parts/faq-lista.php).
 *
 * Lo usa la página "Preguntas frecuentes", una lista por categoría.
 * El markup son `<details>` nativos: abrir/cerrar ya funciona solo. Acá se
 * le suma la animación de altura (hay que retrasar el cierre real del
 * `<details>` hasta que termine el tween, si no el navegador oculta el
 * panel de golpe) y la regla de "una abierta a la vez" dentro de cada
 * lista: cada categoría es una lista propia, así que cada bloque conserva
 * su pregunta abierta.
 */

import { gsap } from '../lib/gsap';

const REDUCED = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

export function initFaqAcordeon(list: HTMLElement): void {
  const items = Array.from(list.querySelectorAll<HTMLDetailsElement>('[data-faq-item]'));
  if (!items.length) return;

  const panelOf = (item: HTMLDetailsElement): HTMLElement | null =>
    item.querySelector<HTMLElement>('[data-faq-panel]');

  const open = (item: HTMLDetailsElement): void => {
    const panel = panelOf(item);
    item.open = true;
    if (!panel || REDUCED) return;
    gsap.fromTo(
      panel,
      { height: 0, autoAlpha: 0 },
      { height: 'auto', autoAlpha: 1, duration: 0.5, ease: 'power3.out', overwrite: true }
    );
  };

  const close = (item: HTMLDetailsElement): void => {
    const panel = panelOf(item);
    if (!panel || REDUCED) {
      item.open = false;
      return;
    }
    gsap.to(panel, {
      height: 0,
      autoAlpha: 0,
      duration: 0.35,
      ease: 'power2.inOut',
      overwrite: true,
      // El `<details>` sigue abierto durante el tween: recién al terminar se
      // cierra de verdad y se limpian los estilos inline, para que la
      // próxima apertura vuelva a medir la altura real del contenido.
      onComplete: () => {
        item.open = false;
        gsap.set(panel, { clearProps: 'height,opacity,visibility' });
      },
    });
  };

  items.forEach((item) => {
    const summary = item.querySelector<HTMLElement>('summary');
    if (!summary) return;

    summary.addEventListener('click', (event) => {
      // El toggle nativo se hace acá a mano para poder animarlo.
      event.preventDefault();

      if (item.open) {
        close(item);
        return;
      }

      items.forEach((other) => {
        if (other !== item && other.open) close(other);
      });
      open(item);
    });
  });
}
