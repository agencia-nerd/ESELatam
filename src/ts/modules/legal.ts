/**
 * Índice que sigue al scroll: páginas legales (page-legal.php) y categorías
 * de Preguntas frecuentes (page-preguntas-frecuentes.php).
 *
 * Marca en el índice la sección que se está leyendo, con el mismo criterio
 * que el aside de "Objetivos con propósito": activa mientras cruza una línea
 * en la franja alta del viewport.
 *
 * Esa línea nunca queda por encima de donde aterriza un salto desde el
 * índice. Los enlaces son anclas normales y los mueve initSmoothScroll
 * (modules/smooth-scroll.ts) con la inercia de Lenis, dejando el título a
 * `scroll-margin-top` del borde. En móvil, con la barra de categorías
 * pegada, ese margen es grande (~220px) y en pantallas bajas superaba el 40%
 * del alto: la sección "no llegaba" y su enlace no se pintaba. Por eso la
 * línea es el mayor entre el 40% del alto y el margen más un respiro.
 *
 * Además, al hacer clic el enlace se marca en el acto y el scroll no lo
 * pisa hasta que termina el viaje: si no, las secciones intermedias que se
 * cruzan en el trayecto lo hacen parpadear.
 */

import { ScrollTrigger } from '../lib/gsap';

/** Mismo largo que el `duration` de Lenis, con margen. */
const BLOQUEO_CLIC_MS = 1600;

export function initLegal(root: HTMLElement): void {
  const links = Array.from(root.querySelectorAll<HTMLAnchorElement>('[data-legal-link]'));
  const secciones = Array.from(root.querySelectorAll<HTMLElement>('[data-legal-seccion]'));
  if (!links.length || !secciones.length) return;

  let actual = -1;
  let bloqueadoHasta = 0;

  const activar = (index: number): void => {
    if (index === actual) return;
    actual = index;
    links.forEach((link, i) => link.classList.toggle('is-active', i === index));
  };

  // Si se llega con un ancla en la URL, esa manda; si no, la primera.
  const inicial = secciones.findIndex((s) => `#${s.id}` === window.location.hash);
  activar(inicial >= 0 ? inicial : 0);

  links.forEach((link, i) => {
    link.addEventListener('click', () => {
      activar(i);
      bloqueadoHasta = performance.now() + BLOQUEO_CLIC_MS;
    });
  });

  // Se recalcula en cada refresh de ScrollTrigger (resize, rotación): el
  // margen cambia entre escritorio y móvil.
  const linea = (seccion: HTMLElement): number => {
    const margen = parseFloat(getComputedStyle(seccion).scrollMarginTop) || 0;
    return Math.round(Math.max(window.innerHeight * 0.4, margen + 24));
  };

  secciones.forEach((seccion, i) => {
    ScrollTrigger.create({
      trigger: seccion,
      start: () => `top ${linea(seccion)}px`,
      end: () => `bottom ${linea(seccion)}px`,
      invalidateOnRefresh: true,
      onToggle: (self) => {
        if (self.isActive && performance.now() >= bloqueadoHasta) activar(i);
      },
    });
  });
}
