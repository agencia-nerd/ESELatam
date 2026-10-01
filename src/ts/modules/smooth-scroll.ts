/**
 * Scroll suave con Lenis (estilo aquaexpeditions.com), sincronizado con
 * GSAP ScrollTrigger: Lenis anima el scroll nativo y el ticker de GSAP
 * conduce su raf, así el pin del hero y los scrubs quedan perfectamente
 * acompasados con la inercia.
 */

import Lenis from 'lenis';
import 'lenis/dist/lenis.css';
import { gsap, ScrollTrigger } from '../lib/gsap';

export function initSmoothScroll(): void {
  // Con reduce-motion se conserva el scroll nativo del navegador
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    return;
  }

  const lenis = new Lenis({
    duration: 1.35,
    easing: (t: number) => Math.min(1, 1.001 - Math.pow(2, -10 * t)), // easeOutExpo
  });

  lenis.on('scroll', ScrollTrigger.update);

  gsap.ticker.add((time) => {
    lenis.raf(time * 1000);
  });
  gsap.ticker.lagSmoothing(0);

  // Anclas internas con la misma inercia.
  //
  // Desde Lenis 1.3 el propio scrollTo() descuenta el `scroll-margin-top`
  // del destino, así que no se le pasa offset: restarlo acá también lo
  // aplicaba dos veces y el título caía el doble de abajo (en Preguntas
  // frecuentes, por debajo de la línea que activa su enlace del índice).
  document.addEventListener('click', (event) => {
    const link = (event.target as HTMLElement).closest<HTMLAnchorElement>('a[href^="#"]');
    if (!link) return;

    const href = link.getAttribute('href') ?? '';
    if (href.length < 2) return;

    const target = document.querySelector<HTMLElement>(href);
    if (!target) return;

    event.preventDefault();

    lenis.scrollTo(target);
  });
}
