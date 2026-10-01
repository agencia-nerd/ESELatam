/**
 * ESE Latam — Entry point
 *
 * Este archivo se carga en todas las plantillas. Módulos pesados como
 * Three.js se cargan de forma perezosa según lo que exista en la página.
 */

import '../css/main.css';
import { initGsap } from './lib/gsap';
import { initSmoothScroll } from './modules/smooth-scroll';
import { initScrollReveals } from './modules/scroll-reveals';
import { initSectionHeaders } from './modules/section-headers';
import { initHeader } from './modules/header';
import { initNavSubmenu } from './modules/nav-submenu';
import { initSearchOverlay } from './modules/search-overlay';
import { initMobileSearch } from './modules/mobile-search';
import { initSliders } from './modules/slider';
import { initMarquees } from './modules/marquee';
import { initParallax } from './modules/parallax';
import { initProductCarousel } from './modules/product-carousel';
import { initProductFilter } from './modules/product-filter';
import { initProductCardColors } from './modules/product-card-colors';
import { initFloat } from './modules/float';
import { initHeroCta } from './modules/hero-cta';
import { initContactoReveal } from './modules/contacto-reveal';
import { initResiduosSelector } from './modules/residuos-selector';
import { initDistribuidoresIntro } from './modules/distribuidores-intro';
import { initTabPanels } from './modules/tab-panels';
import { initCountUp } from './modules/count-up';

function bootstrap(): void {
  initGsap();
  initSmoothScroll();
  initHeader();
  initNavSubmenu();
  initSearchOverlay();
  initMobileSearch();
  initScrollReveals();
  initSectionHeaders();
  initSliders();
  initMarquees();
  initParallax();
  // Antes del carrusel: deja en el slider solo las variantes de la categoría
  // activa, así Swiper mide de entrada el set que se va a ver.
  initProductFilter();
  initProductCarousel();
  // Listener delegado: no le importa cuándo entran o salen las tarjetas.
  initProductCardColors();
  // Después del carrusel: si Swiper duplicó slides para el loop, sus copias ya
  // están en el DOM y también reciben la flotación.
  initFloat();
  initHeroCta();
  initContactoReveal();
  initResiduosSelector();
  initDistribuidoresIntro();
  // Tabs genéricas (desafíos, certificaciones en detalle, valida, economía
  // circular) y contadores (impacto)
  initTabPanels();
  initCountUp();

  // Hero con scroll-animación (solo front-page)
  const hero = document.querySelector<HTMLElement>('[data-hero]');
  if (hero) {
    void import('./modules/hero-scroll').then(({ initHeroScroll }) => {
      initHeroScroll(hero);
    });
  }

  // Escena "isla + productos flotantes" del banner de catálogo
  const productIsland = document.querySelector<HTMLElement>('[data-product-island]');
  if (productIsland) {
    void import('./modules/product-island').then(({ initProductIsland }) => {
      initProductIsland(productIsland);
    });
  }

  // Panel de configuración de la ficha de producto (single-producto.php)
  const productConfig = document.querySelector<HTMLElement>('[data-product-config]');
  if (productConfig) {
    void import('./modules/product-config').then(({ initProductConfig }) => {
      initProductConfig(productConfig);
    });
  }

  // Entrada coreografiada del hero de la ficha (arriba del fold: se importa
  // ya, sin esperar a ningún observer)
  const productoHero = document.querySelector<HTMLElement>('[data-producto-hero]');
  if (productoHero) {
    void import('./modules/producto-hero-intro').then(({ initProductoHeroIntro }) => {
      initProductoHeroIntro(productoHero);
    });
  }

  // Selector de "Pruebas de rigurosidad" (single-producto.php)
  if (document.querySelector('[data-pruebas]')) {
    void import('./modules/pruebas-selector').then(({ initPruebasSelector }) => {
      initPruebasSelector();
    });
  }

  // Página Nosotros (page-nosotros.php): hero por líneas, aside sticky de
  // objetivos, tabs ESG, escena de pellets... todo en un módulo aparte.
  const nosotros = document.querySelector<HTMLElement>('[data-nosotros]');
  if (nosotros) {
    void import('./modules/nosotros').then(({ initNosotros }) => {
      initNosotros(nosotros);
    });
  }

  // Índice que sigue al scroll: páginas legales (page-legal.php) y
  // categorías de page-preguntas-frecuentes.php.
  const legal = document.querySelector<HTMLElement>('[data-legal]');
  if (legal) {
    void import('./modules/legal').then(({ initLegal }) => {
      initLegal(legal);
    });
  }

  // Artículo del blog (single-post.php): el botón de copiar el enlace.
  const artShare = document.querySelector<HTMLElement>('[data-art-share]');
  if (artShare) {
    void import('./modules/articulo-share').then(({ initArticuloShare }) => {
      initArticuloShare(artShare);
    });
  }

  // Acordeones de preguntas frecuentes (template-parts/faq-lista.php): al
  // pie de Contacto y uno por categoría en page-preguntas-frecuentes.php.
  const faqLists = Array.from(document.querySelectorAll<HTMLElement>('[data-faq]'));
  if (faqLists.length) {
    void import('./modules/faq-acordeon').then(({ initFaqAcordeon }) => {
      faqLists.forEach(initFaqAcordeon);
    });
  }

  // Página Contacto (page-contacto.php): envío del formulario sin recarga.
  const contactoPage = document.querySelector<HTMLElement>('[data-contacto-page]');
  if (contactoPage) {
    void import('./modules/contacto-page').then(({ initContactoPage }) => {
      initContactoPage(contactoPage);
    });
  }

  // "Estándar Blue Angel" (template-parts/blue-angel.php): carrusel en arco.
  const bangel = document.querySelector<HTMLElement>('[data-bangel]');
  if (bangel) {
    void import('./modules/blue-angel').then(({ initBlueAngel }) => {
      initBlueAngel(bangel);
    });
  }

  // Página "Encuentra un distribuidor" (page-distribuidores.php): filtro en
  // vivo de la lista y mapa que sigue a la tarjeta activa.
  const distribuidoresPage = document.querySelector<HTMLElement>('[data-distribuidores-page]');
  if (distribuidoresPage) {
    void import('./modules/distribuidores-page').then(({ initDistribuidoresPage }) => {
      initDistribuidoresPage(distribuidoresPage);
    });
  }

  // Pasos de "Entendemos tu operación" (page-sectores.php)
  const proceso = document.querySelector<HTMLElement>('[data-proceso]');
  if (proceso) {
    void import('./modules/proceso-steps').then(({ initProcesoSteps }) => {
      initProcesoSteps(proceso);
    });
  }

  // Lazy-load Three.js solo si la página tiene un canvas 3D
  const has3d = document.querySelector<HTMLElement>('[data-three-scene]');
  if (has3d) {
    void import('./modules/three-scene').then(({ initThreeScene }) => {
      initThreeScene(has3d);
    });
  }

  // Distribuidores: globo 3D (Three.js + texturas, ~1.1MB) — la sección está
  // varias pantallas abajo del fold, así que se difiere su import hasta que
  // esté por entrar en viewport. Sin esto compite por ancho de banda con el
  // contenido crítico del hero desde el primer instante de la carga.
  const globeSection = document.querySelector<HTMLElement>('[data-globe]');
  if (globeSection) {
    if (typeof IntersectionObserver === 'function') {
      const observer = new IntersectionObserver(
        (entries, obs) => {
          if (entries.some((entry) => entry.isIntersecting)) {
            obs.disconnect();
            void import('./modules/distribuidores').then(({ initDistribuidores }) => {
              initDistribuidores(globeSection);
            });
          }
        },
        { rootMargin: '200px' }
      );
      observer.observe(globeSection);
    } else {
      void import('./modules/distribuidores').then(({ initDistribuidores }) => {
        initDistribuidores(globeSection);
      });
    }
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', bootstrap, { once: true });
} else {
  bootstrap();
}
