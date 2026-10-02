/**
 * Hero con animación de scroll (front-page):
 *  - Intro al cargar (sin scroll): el titular aparece centrado con máscara por
 *    línea, viaja a su posición de layout (a la izquierda), entran menú y
 *    lede/CTA, y al final emergen la isla y la hero-card (el mensaje del
 *    hero: entra junto con la isla, no está desde la carga).
 *  - Escena en capas (imágenes, no video): fondo fijo —visible desde el primer
 *    paint—, isla flotante y tres hojas en las esquinas que entran
 *    con la intro y se mecen sin parar.
 *  - Con el scroll: el fondo hace un zoom leve, el titular y la isla derivan
 *    hacia abajo con parallax (velocidades distintas) y las hojas se abren
 *    hacia sus esquinas, hasta disolverse entre las nubes de la sección
 *    siguiente.
 *  - En el último viewport del pin, la sección siguiente (coronada por las
 *    nubes, ver .hero-next__clouds) se desliza por encima del hero.
 */

import { gsap, ScrollTrigger } from '../lib/gsap';

// Duración del pin en viewports. La sección siguiente (adelantada exactamente
// la altura del hero) entra deslizándose durante el ÚLTIMO viewport de scroll
// del pin: su frente son las nubes que la coronan, y llega al top justo al
// liberarse el pin. SWEEP_START = 1 - 1/PIN_VIEWPORTS.
// En móvil el pin es más corto (2.4 viewports): 3.5 pantallas de scroll
// se sentían interminables con el pulgar, y en pantallas bajas el usuario no veía avance. La coreografía
// (isla, barrido de la sección siguiente) se reparte igual, en proporción.
const PIN_VIEWPORTS_DESKTOP = 3.5;
const PIN_VIEWPORTS_MOBILE = 2.4;

// Hacia dónde se va cada hoja (data-hero-leaf en front-page.php): sale de su
// esquina y vuelve a ella. `x`/`y` es el signo del desplazamiento.
const LEAF_DIR: Record<string, { x: number; y: number }> = {
  tl: { x: -1, y: -1 },
  bl: { x: -1, y: 1 },
  br: { x: 1, y: 1 },
};

export function initHeroScroll(section: HTMLElement): void {
  // El fondo se ve desde el primer paint: nunca se oculta, solo se anima su escala.
  const bg = section.querySelector<HTMLElement>('[data-hero-bg]');
  // Hojas: el wrapper recibe entrada y parallax de scroll; el <img> de
  // adentro, el vaivén continuo — transforms en elementos distintos.
  const leaves = Array.from(section.querySelectorAll<HTMLElement>('[data-hero-leaf]'));
  const leafDir = (leaf: HTMLElement): { x: number; y: number } =>
    LEAF_DIR[leaf.dataset.heroLeaf ?? ''] ?? { x: -1, y: 1 };

  const island = section.querySelector<HTMLElement>('[data-hero-island]');
  const titleWrap = section.querySelector<HTMLElement>('[data-hero-title-wrap]');
  const bottom = section.querySelector<HTMLElement>('[data-hero-bottom]');
  const title = section.querySelector<HTMLElement>('.hero__title');
  // Wrapper de la hero-card (front-page.php): la entrada va acá; la card de
  // adentro flota con float.ts — elementos distintos, transforms que no chocan.
  const cardWrap = section.querySelector<HTMLElement>('[data-hero-card]');
  const titleLines = section.querySelectorAll<HTMLElement>('[data-hero-line]');
  const revealEls = section.querySelectorAll<HTMLElement>('[data-hero-reveal]');
  const header = document.querySelector<HTMLElement>('[data-header]');
  // .hero__title-settled (ver main.css) es un duplicado estático de este
  // titular, visible solo en mobile, dentro de .hero__intro justo arriba
  // del lede — no hace falta capturarlo acá: ya trae `data-hero-reveal`, así
  // que `revealEls` (abajo) lo agarra solo. Es solo visual (aria-hidden): el
  // único <h1> es .hero__title, que en mobile queda oculto a la vista pero
  // presente para lectores de pantalla y buscadores (ver main.css). En
  // desktop el duplicado no existe (display:none) y .hero__title es el que se ve.
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // En mobile `.hero__title` ocupa TODO el ancho de contenido de
  // `.hero__content` (el padding lateral es simétrico, no hay nada que lo
  // angoste) — su CAJA ya cae centrada en el viewport de por sí, así que
  // centrar la caja (lo que hace titleDx) no mueve nada visible: el texto,
  // más angosto y con text-align:left, seguía pegado al borde izquierdo de
  // esa caja ya centrada. Acá sí hace falta centrar el TEXTO (no solo la
  // caja), y solo durante la intro — el layout asentado usa .hero__title-settled.
  // 64rem: hasta tablet el hero usa la composición mobile (ver main.css).
  const isMobile = window.matchMedia('(width < 64rem)').matches;

  // Coreografía del pin (fracciones del progreso total), según breakpoint
  const PIN_VIEWPORTS = isMobile ? PIN_VIEWPORTS_MOBILE : PIN_VIEWPORTS_DESKTOP;
  const SWEEP_START = 1 - 1 / PIN_VIEWPORTS;      // ~0.71 desktop — la sección empieza a barrer

  if (prefersReducedMotion) {
    // Sin animación: estado final legible, todo visible.
    // En mobile el titular que se ve es .hero__title-settled (ya incluido en
    // revealEls); el h1 sigue oculto a la vista por CSS, no por autoAlpha.
    const staticEls = [island, cardWrap, ...leaves, ...revealEls].filter((el): el is HTMLElement => el !== null);
    gsap.set(staticEls, { autoAlpha: 1 });
    if (title) gsap.set(title, { autoAlpha: 1 });
    return;
  }

  // ---------- Intro al cargar (toda la coreografía de entrada) ----------

  // Estado final directo, sin animación (para cargas con scroll restaurado).
  // clearProps en los reveals: un transform inline residual de la intro
  // podría interferir con animaciones propias de cada elemento después
  // (p. ej. el hover del hero-cta, que anima el `d` de su SVG conector).
  const finishIntroState = (): void => {
    // En mobile el h1 está oculto solo a la vista (ver main.css): autoAlpha 1
    // para no sacarlo del árbol de accesibilidad.
    if (title) gsap.set(title, isMobile ? { autoAlpha: 1 } : { x: 0, y: 0, scale: 1, autoAlpha: 1 });
    if (island) gsap.set(island, { autoAlpha: 1 });
    if (leaves.length) gsap.set(leaves, { autoAlpha: 1 });
    if (cardWrap) gsap.set(cardWrap, { autoAlpha: 1, clearProps: 'transform' });
    if (header) gsap.set(header, { yPercent: 0, autoAlpha: 1 });
    if (revealEls.length) gsap.set(revealEls, { autoAlpha: 1, clearProps: 'transform' });
  };

  // La intro solo tiene sentido si la página arranca al inicio del hero.
  // Con scroll restaurado (reload a mitad de página) o navegación con ancla,
  // el titular "viajaría" por un viewport que ya no es el suyo — se salta.
  const startsAtTop = window.scrollY <= 8 && window.location.hash === '';

  if (startsAtTop) {
    // .hero__title-wrap está oculto a la vista en mobile (ver main.css) —
    // sin animación de entrada ahí, el titular que se ve es
    // .hero__title-settled, que entra con el resto de revealEls en el paso 5.
    // Medir/animar el original (una caja de 1px recortada) no tendría sentido.
    if (!isMobile && title) {
      // Desplazamiento del titular desde su posición de layout hasta el centro
      // del viewport, medido del DOM antes de aplicar cualquier transform.
      const r = title.getBoundingClientRect();
      const titleDx = window.innerWidth / 2 - (r.left + r.width / 2);
      const titleDy = window.innerHeight / 2 - (r.top + r.height / 2);
      // scale 1.2: el font-size CSS es el tamaño ASENTADO (reducido); la
      // escala compensa para que centrado se vea al tamaño grande original
      // y "reduzca ligeramente" al viajar a su posición (scale → 1).
      gsap.set(title, { x: titleDx, y: titleDy, scale: 1.2, autoAlpha: 1 });
    }

    // El menú arranca oculto arriba; el intro lo hace entrar.
    if (header) {
      gsap.set(header, { yPercent: -120, autoAlpha: 0 });
    }

    const intro = gsap.timeline({ defaults: { ease: 'power3.out' }, delay: 0.15 });

    // 1) El fondo asienta desde un ligero zoom. Solo escala, nunca opacidad:
    // tiene que pintarse en el primer frame.
    intro.from(bg ?? [], { scale: 1.12, duration: 2, ease: 'power2.out' }, 0);

    // 1b) Las hojas entran desde su esquina, girando apenas, mientras el
    // titular todavía sube: enmarcan la escena antes de que llegue la isla.
    leaves.forEach((leaf, i) => {
      const d = leafDir(leaf);
      intro.fromTo(
        leaf,
        { x: d.x * 70, y: d.y * 70, rotation: d.x * d.y * 8, autoAlpha: 0 },
        { x: 0, y: 0, rotation: 0, autoAlpha: 1, duration: 1.6, ease: 'expo.out' },
        0.9 + i * 0.15
      );
    });

    // 2) y 3) Solo desktop: el titular centrado sube tras su máscara y
    // luego viaja del centro a su posición de layout, a la izquierda. En
    // mobile .hero__title-wrap está oculto a la vista (ver main.css) — no hay
    // nada que animar; el titular que se ve ahí es .hero__title-settled,
    // que entra plano con el resto de revealEls en el paso 5. Los pasos
    // siguientes quedan en sus mismas posiciones absolutas de todos modos
    // (2.15, 2.25, 2.55), así que saltarse este tramo en mobile no
    // desarma la coreografía del resto — solo dejan de correr en el vacío.
    if (!isMobile) {
      intro.from(titleLines, { yPercent: 115, duration: 1.1, stagger: 0.14 }, 0.2);
      intro.to(title ?? [], { x: 0, y: 0, scale: 1, duration: 1.1, ease: 'power3.inOut' }, 1.5);
    }

    // 4) El menú baja y entra
    intro.to(header ?? [], { yPercent: 0, autoAlpha: 1, duration: 0.8 }, 2.15);

    // 5) Lede y CTA entran escalonados (clearProps: sin transform
    // inline residual que bloquee los hovers CSS, como el settle del CTA)
    intro.fromTo(
      revealEls,
      { y: 40, autoAlpha: 0 },
      { y: 0, autoAlpha: 1, duration: 0.9, stagger: 0.12, clearProps: 'transform' },
      2.25
    );

    // 6) La isla emerge mientras el titular viaja a la izquierda. En mobile
    // es el LCP, así que ahí no espera al titular (ver ISLAND_IN).
    //
    // Anima `y` en px (no yPercent): el parallax de scroll usa yPercent en el
    // mismo wrapper y ambas capas de transform componen sin pisarse. `scale` y
    // `rotation` son libres — el timeline de scroll no las toca.
    // En mobile no hay viaje del titular que esperar (pasos 2 y 3 son solo
    // desktop) y la isla es la imagen más grande que cuenta para el LCP (el
    // fondo, de tamaño viewport, Chrome no lo toma como candidato): entra
    // casi de inmediato.
    const ISLAND_IN = isMobile ? 0.3 : 1.9;

    intro.fromTo(
      island ?? [],
      { y: 170, scale: 0.84, autoAlpha: 0 },
      { y: 0, scale: 1, autoAlpha: 1, duration: 1.7, ease: 'expo.out' },
      ISLAND_IN
    );

    // La inclinación asienta más lenta que la subida: ese desfase es lo que da
    // la sensación de que la isla "encuentra" su flotación, en vez de aterrizar
    // rígida de una pieza.
    intro.fromTo(
      island ?? [],
      { rotation: -5 },
      { rotation: 0, duration: 2.4, ease: 'power2.out' },
      ISLAND_IN
    );

    // 7) La hero-card llega con la isla, apenas después de que esta arranca
    // a subir: es el mensaje del hero y merece su propio momento en vez de
    // colarse en la cascada del lede/CTA. Sube y "asienta" desde un leve
    // encogimiento; clearProps al final para que el transform de la intro
    // no se quede debajo del que anima float.ts en la card de adentro.
    intro.fromTo(
      cardWrap ?? [],
      { y: 56, scale: 0.94, autoAlpha: 0 },
      { y: 0, scale: 1, autoAlpha: 1, duration: 1.3, ease: 'expo.out', clearProps: 'transform' },
      ISLAND_IN + 0.25
    );

    // Si el navegador restaura el scroll tarde, o el usuario sale del hero
    // durante la intro, se completa de inmediato para que no se vea el titular
    // volando por la página ya scrolleada.
    const onEarlyScroll = (): void => {
      if (window.scrollY > window.innerHeight * 0.5) {
        intro.progress(1);
        window.removeEventListener('scroll', onEarlyScroll);
      }
    };
    window.addEventListener('scroll', onEarlyScroll, { passive: true });
    intro.eventCallback('onComplete', () => {
      window.removeEventListener('scroll', onEarlyScroll);
    });
  } else {
    finishIntroState();
  }

  // Flotación continua de la isla (independiente del scroll)
  const islandImg = island?.querySelector('img');
  if (islandImg) {
    gsap.to(islandImg, {
      y: -18,
      duration: 2.8,
      ease: 'sine.inOut',
      yoyo: true,
      repeat: -1,
    });
  }

  // Vaivén continuo de las hojas, como mecidas por el viento: cada una con su
  // propio ritmo y fase para que no se muevan al unísono. El desplazamiento
  // va HACIA su esquina (nunca hacia el centro) y el giro es chico, para que
  // la hoja no se despegue del borde de la pantalla (ver .hero__leaf).
  leaves.forEach((leaf) => {
    const img = leaf.querySelector('img');
    if (!img) return;
    const d = leafDir(leaf);
    const sway = gsap.to(img, {
      rotation: d.x * d.y * -1.5,
      x: d.x * 6,
      y: d.y * 5,
      duration: gsap.utils.random(3.2, 4.6),
      ease: 'sine.inOut',
      yoyo: true,
      repeat: -1,
    });
    sway.progress(Math.random());
  });

  // Solape de la sección siguiente: se adelanta EXACTAMENTE la altura real
  // del hero (no 100svh, que puede diferir con min-height, zoom o svh≠viewport)
  // para que llegue al top del viewport justo cuando el pin termina.
  // Solo se aplica con pin activo; sin JS o con reduce-motion, flujo normal.
  // Solo el margen: NO hace falta forzar ningún min-height (ni acá ni en la
  // sección siguiente) para que el hero quede tapado. Lo que sincroniza el
  // barrido es este margen; al liberarse el pin, .hero-next y todo lo que
  // sigue son un flujo continuo y opaco (bg-white + z-10) por encima del
  // hero, así que no hay hueco que rellenar. Forzar altura solo reservaba
  // scroll de más y dejaba un vacío visible en la sección a la que se le
  // aplicara.
  const next = document.querySelector<HTMLElement>('.hero-next');
  const setOverlap = (): void => {
    if (next) gsap.set(next, { marginTop: -section.offsetHeight });
  };
  setOverlap();
  ScrollTrigger.addEventListener('refreshInit', setOverlap);

  const tl = gsap.timeline({
    defaults: { ease: 'none' },
    scrollTrigger: {
      trigger: section,
      start: 'top top',
      // Distancia explícita en px de viewport (no % del trigger): así la
      // entrada de la sección siguiente coincide 1:1 con el fin del pin.
      end: () => '+=' + window.innerHeight * PIN_VIEWPORTS,
      scrub: true,
      pin: true,
      anticipatePin: 1,
    },
  });

  // El titular "retrocede" al scrollear (efecto homy.framer.media): encoge y
  // pierde opacidad en el primer tramo (con clamp) mientras deriva hacia abajo
  // con parallax, y termina de disolverse cuando las nubes del barrido lo
  // alcanzan. Va sobre el WRAPPER para no chocar con el h1 de la intro.
  if (titleWrap) {
    tl.fromTo(
      titleWrap,
      { scale: 1, opacity: 1 },
      { scale: 0.88, opacity: 0.75, duration: 0.2, ease: 'power1.out' },
      0
    );
    tl.fromTo(
      titleWrap,
      { y: 0 },
      { y: 190, duration: SWEEP_START + 0.12, ease: 'none' },
      0
    );
    // Disolución entre las nubes que suben con la sección
    tl.to(titleWrap, { opacity: 0, duration: 0.16, ease: 'none' }, SWEEP_START - 0.02);
  }

  // hero__bottom (lede, CTA y card) retrocede con el mismo lenguaje que el
  // titular, con un leve desfase para dar profundidad. Anclado a su borde
  // inferior para que al encoger no se despegue del pie del viewport.
  // Va sobre el WRAPPER: la intro anima a sus hijos ([data-hero-reveal])
  // individualmente y compartir target crearía conflicto de transforms.
  if (bottom) {
    gsap.set(bottom, { transformOrigin: 'center bottom' });
    tl.fromTo(
      bottom,
      { scale: 1, opacity: 1 },
      { scale: 0.94, opacity: 0.75, duration: 0.22, ease: 'power1.out' },
      0.04
    );
    tl.fromTo(
      bottom,
      { y: 0 },
      { y: 120, duration: SWEEP_START, ease: 'none' },
      0
    );
  }

  // La hero-card ya entró con la intro (paso 7) y vive dentro de
  // hero__bottom, así que retrocede con el scroll junto al lede y el CTA —
  // no necesita tween propio acá.

  // La isla (visible desde la carga) deriva hacia abajo con parallax — más
  // lenta que el titular, para dar profundidad — y se disuelve entre las
  // nubes del barrido. yPercent en el wrapper: compone con el `y` px de la
  // intro y con la flotación (que va en el <img> hijo) sin conflictos.
  // La disolución va sobre el <img>: un .to() en timeline scrubeado captura
  // su valor inicial al CREARSE, y el wrapper estaba aún en opacity 0 por la
  // intro — quedaría como tween 0→0. El img siempre parte de opacity 1.
  if (island) {
    tl.fromTo(
      island,
      { yPercent: 0 },
      { yPercent: 14, duration: SWEEP_START + 0.12, ease: 'none' },
      0
    );
    if (islandImg) {
      tl.to(islandImg, { autoAlpha: 0, duration: 0.2, ease: 'none' }, SWEEP_START - 0.06);
    }
  }

  // El fondo se acerca levemente durante el pin (reemplaza el movimiento que
  // antes daba el video).
  if (bg) {
    tl.fromTo(bg, { scale: 1 }, { scale: 1.06, duration: SWEEP_START, ease: 'none' }, 0);
  }

  // Las hojas se abren hacia sus esquinas (parallax de primer plano: más
  // rápido que la isla) y se disuelven con ella. xPercent/yPercent en el
  // wrapper: componen con los x/y en px de la intro sin pisarlos.
  leaves.forEach((leaf) => {
    const d = leafDir(leaf);
    tl.fromTo(
      leaf,
      { xPercent: 0, yPercent: 0 },
      { xPercent: d.x * 35, yPercent: d.y * 35, duration: SWEEP_START, ease: 'none' },
      0
    );
    const img = leaf.querySelector('img');
    if (img) tl.to(img, { autoAlpha: 0, duration: 0.2, ease: 'none' }, SWEEP_START - 0.06);
  });

  // Relleno hasta 1.0: el scrub normaliza la duración total del timeline al
  // rango de scroll, así que sin esto los tweens se estirarían hasta el final.
  // El tramo SWEEP_START→1 es donde la sección siguiente barre el hero.
  tl.to({}, { duration: 1 - SWEEP_START }, SWEEP_START);

  // El margen negativo de .hero-next cambió el layout: recalcula las
  // posiciones de los triggers creados antes (reveals, marquee…).
  // sort() primero: el orden de refresh por defecto es el de CREACIÓN, y los
  // triggers creados antes que este pin (módulos síncronos del bootstrap) se
  // medirían sin sumar su distancia de pin — sus start/end quedarían ~3800px
  // adelantados. Ordenados por posición en el documento, el offset del pin
  // se propaga correctamente a todo lo que está debajo.
  ScrollTrigger.sort();
  ScrollTrigger.refresh();
}
