<?php
/**
 * Front page — Hero "Estado A" (Figma node 4271-1457) con animación de scroll:
 * escena en capas (fondo, isla flotante y hojas animadas) y nubes que cubren
 * la transición.
 *
 * @package EseLatam
 */
declare(strict_types=1);

get_header();

// Todo el copy de la portada se edita en la página fijada como Inicio
// (los campos se registran en inc/pcf-home.php). Cada campo vacío cae al
// texto del diseño, así que la home se ve igual sin tocar nada.
$ese_hero = [
    'lede'   => trim((string) ese_latam_home('hero_lede', '')),
    'stat'   => trim((string) ese_latam_home('hero_card_stat', '')),
    'tag'    => trim((string) ese_latam_home('hero_card_tag', '')),
    'desc'   => trim((string) ese_latam_home('hero_card_desc', '')),
];

$ese_hero_cta  = ese_latam_enlace(ese_latam_home('hero_cta'));

// La isla se pide por ID y no por URL: así la pinta wp_get_attachment_image()
// con el srcset que WordPress ya generó, en vez de mantener a mano los cuatro
// tamaños que antes vivían en el theme.
$ese_hero_isla = (int) ese_latam_home('hero_isla', 0);

// Fondo del hero: la primera imagen que se pinta. Mismo origen que el preload del <head>
// (ver ese_latam_hero_fondo() en inc/template-tags.php).
$ese_hero_fondo = ese_latam_hero_fondo();

// Hojas de las esquinas: decorado fijo del diseño (no editable). Medidas
// reales del archivo para que el <img> reserve su espacio.
$ese_hero_hojas = [
    'tl' => ['hoja-arriba-izq.webp', 333, 231],
    'bl' => ['hoja-abajo-izq.webp', 789, 719],
    'br' => ['hoja-abajo-der.webp', 900, 835],
];

// El alto de la isla depende de su proporción, así que se la pasamos al CSS
// en vez de dejarla escrita a ojo: así una imagen más cuadrada se achica sola
// y no termina contra la cabecera (ver .hero__island en main.css).
// Sin isla cargada en el campo se usa la del diseño que trae el theme
// (recortada al dibujo, en dos anchos).
$ese_isla_meta  = $ese_hero_isla > 0 ? wp_get_attachment_metadata($ese_hero_isla) : ['width' => 1400, 'height' => 1424];
$ese_isla_w     = (int) ($ese_isla_meta['width'] ?? 0);
$ese_isla_h     = (int) ($ese_isla_meta['height'] ?? 0);
$ese_isla_ratio = $ese_isla_w > 0 && $ese_isla_h > 0 ? round($ese_isla_w / $ese_isla_h, 4) : 1.7778;
$ese_isla_ancho = (int) ese_latam_home('hero_isla_ancho', 0);
$ese_isla_ancho = $ese_isla_ancho > 0 ? $ese_isla_ancho : 1720;

$ese_isla_estilo = '--island-ratio:' . $ese_isla_ratio . ';--island-max:' . $ese_isla_ancho . 'px;';

// `sizes` es la pista que usa el navegador para elegir del srcset, así que
// tiene que decir lo mismo que el CSS. No admite var(), de modo que los
// tres topes se calculan acá con los números reales (74svh es
// --island-alto-max en main.css; 40svh, su tope en mobile).
$ese_isla_sizes = sprintf(
    '(max-width: 63.9375rem) min(88vw, 560px, %ssvh), min(44vw, %dpx, %ssvh)',
    round(40 * $ese_isla_ratio, 2),
    $ese_isla_ancho,
    round(74 * $ese_isla_ratio, 2)
);
$ese_hero_card = '' !== $ese_hero['stat'] || '' !== $ese_hero['tag'] || '' !== $ese_hero['desc'];

// El titular se anima línea por línea (hero-scroll.ts busca cada
// [data-hero-line]), así que un salto de línea del campo ES una línea del
// marcado y el resaltado |así| se resuelve dentro de cada una.
$ese_hero_titulo = (string) ese_latam_home('hero_titulo', '');
$ese_hero_lineas = preg_split('/\R/', trim($ese_hero_titulo)) ?: [];
$ese_hero_lineas = array_values(array_filter(array_map('trim', $ese_hero_lineas), static fn (string $l): bool => '' !== $l));

$ese_hero_hay = [] !== $ese_hero_lineas || '' !== $ese_hero['lede'] || $ese_hero_card || '' !== $ese_hero_cta['label'];
?>


<?php if ($ese_hero_hay) : ?>
<section class="hero" data-hero>
    <?php // Visible desde el primer paint: hero-scroll.ts solo le
    // anima la escala, nunca la opacidad. ?>
    <img class="hero__bg" data-hero-bg
        src="<?php echo esc_url($ese_hero_fondo['src']); ?>"
        <?php if ('' !== $ese_hero_fondo['srcset']) : ?>srcset="<?php echo esc_attr($ese_hero_fondo['srcset']); ?>" sizes="100vw"<?php endif; ?>
        width="<?php echo (int) $ese_hero_fondo['width']; ?>" height="<?php echo (int) $ese_hero_fondo['height']; ?>"
        alt="" fetchpriority="high" decoding="async">

    <div class="hero__shade" aria-hidden="true"></div>

    <?php // El srcset lo arma WordPress con los tamaños que generó al
    // subir la imagen; `sizes` sí es nuestro, porque la isla no ocupa
    // el ancho del contenedor sino el que le da .hero__island. Sin
    // fetchpriority alto: el fondo, que se precarga, va primero. ?>
    <div class="hero__island" data-hero-island aria-hidden="true"
        <?php echo '' !== $ese_isla_estilo ? 'style="' . esc_attr($ese_isla_estilo) . '"' : ''; ?>>
        <?php if ($ese_hero_isla > 0) : ?>
            <?php
            echo wp_get_attachment_image($ese_hero_isla, 'full', false, [
                'alt'           => '',
                'sizes'         => $ese_isla_sizes,
                // Explícito: si no, WordPress le pone `high` por ser la primera
                // imagen grande y le compite al fondo.
                'fetchpriority' => 'auto',
                'decoding'      => 'async',
            ]);
            ?>
        <?php else : ?>
            <img src="<?php echo esc_url(ese_latam_asset('hero/hero-isla-1400.webp')); ?>"
                srcset="<?php echo esc_url(ese_latam_asset('hero/hero-isla-800.webp')); ?> 800w, <?php echo esc_url(ese_latam_asset('hero/hero-isla-1400.webp')); ?> 1400w"
                sizes="<?php echo esc_attr($ese_isla_sizes); ?>" width="1400" height="1424" alt="" decoding="async">
        <?php endif; ?>
    </div>

    <?php // Dos capas por hoja (mismo patrón que .productos__leaves-wrap): el
    // wrapper recibe la entrada y el parallax de scroll, el <img> el vaivén
    // continuo — hero-scroll.ts anima cada uno por separado. ?>
    <?php foreach ($ese_hero_hojas as $ese_hoja_pos => [$ese_hoja_file, $ese_hoja_w, $ese_hoja_h]) : ?>
        <div class="hero__leaf hero__leaf--<?php echo esc_attr($ese_hoja_pos); ?>" data-hero-leaf="<?php echo esc_attr($ese_hoja_pos); ?>" aria-hidden="true">
            <img src="<?php echo esc_url(ese_latam_asset('hero/' . $ese_hoja_file)); ?>"
                width="<?php echo (int) $ese_hoja_w; ?>" height="<?php echo (int) $ese_hoja_h; ?>" alt="" decoding="async">
        </div>
    <?php endforeach; ?>

    <div class="hero__content">
        <div class="hero__title-wrap" data-hero-title-wrap>
            <?php if ([] !== $ese_hero_lineas) : ?>
                <h1 class="hero__title">
                    <?php foreach ($ese_hero_lineas as $ese_linea): ?>
                        <span class="hero__title-line"><span class="hero__title-inner"
                                data-hero-line><?php echo ese_latam_titulo($ese_linea); ?></span></span>
                    <?php endforeach; ?>
                </h1>
            <?php endif; ?>
        </div>

        <div class="hero__bottom" data-hero-bottom>
            <div class="hero__intro">
                <?php
                // Duplicado de .hero__title, visible solo en mobile (ver
                // .hero__title-settled en main.css): ahí el titular de arriba
                // es puramente el adorno de la intro (centrado, grande) y se
                // desvanece del todo en vez de viajar a una posición asentada
                // — este es el que realmente queda en el layout, justo
                // arriba del lede. En desktop no se muestra (display:none) y
                // .hero__title de arriba sigue siendo el único titular.
                // Es solo visual (aria-hidden): el único <h1> es el de arriba,
                // que en mobile queda oculto a la vista pero sigue presente
                // para lectores de pantalla y buscadores.
                ?>
                <?php if ([] !== $ese_hero_lineas) : ?>
                    <p class="hero__title hero__title-settled" data-hero-reveal aria-hidden="true">
                        <?php echo ese_latam_titulo(implode(' ', $ese_hero_lineas)); ?>
                    </p>
                <?php endif; ?>

                <?php if ('' !== $ese_hero['lede']) : ?>
                    <p class="hero__lede" data-hero-reveal>
                        <?php echo esc_html($ese_hero['lede']); ?>
                    </p>
                <?php endif; ?>

                <?php
                if ('' !== $ese_hero_cta['label']) {
                    ese_latam_cta_button([
                        'href' => $ese_hero_cta['href'],
                        'label' => $ese_hero_cta['label'],
                        'target' => $ese_hero_cta['target'],
                        'reveal' => true,
                    ]);
                }
                ?>

            </div>

            <?php
            // Es EL mensaje del hero, así que entra con la intro de carga (no
            // recién con el scroll, como antes) y flota como la isla. Dos
            // capas a propósito: el wrapper recibe la entrada de hero-scroll.ts
            // (data-hero-card) y la card la flotación de float.ts (data-float)
            // — cada módulo anima su propio elemento y no se pisan los transforms.
            ?>
            <?php if ($ese_hero_card) : ?>
                <div class="hero-card-wrap" data-hero-card>
                    <aside class="hero-card" data-float data-float-distance="7" data-float-duration="4.2">
                        <div class="hero-card__body">
                            <?php if ('' !== $ese_hero['stat']) : ?>
                                <p class="hero-card__stat"><?php echo esc_html($ese_hero['stat']); ?></p>
                            <?php endif; ?>
                            <?php if ('' !== $ese_hero['tag']) : ?>
                                <p class="hero-card__tag"><?php echo esc_html($ese_hero['tag']); ?></p>
                            <?php endif; ?>
                            <?php if ('' !== $ese_hero['desc']) : ?>
                                <p class="hero-card__desc"><?php echo esc_html($ese_hero['desc']); ?></p>
                            <?php endif; ?>
                        </div>
                    </aside>
                </div>
            <?php endif; ?>
        </div>
    </div>

</section>
<?php endif; ?>

<?php
// Sección "Sectores" (Figma node 3266-2331) — slider Embla de áreas de impacto.
// La lista vive en inc/template-tags.php: el submenú "Sectores" del nav
// (inc/setup.php) muestra los mismos ocho.
$ese_sectores = ese_latam_sectores();

$ese_sec_copy = [
    'kicker'  => trim((string) ese_latam_home('sectores_kicker', '')),
    'titulo'  => trim((string) ese_latam_home('sectores_titulo', '')),
    'desc'    => ese_latam_texto_rico((string) ese_latam_home('sectores_desc', '')),
    'leyenda' => trim((string) ese_latam_home('sectores_leyenda', '')),
];
?>
<?php // El slider ES la sección: sin sectores cargados no se pinta nada. ?>
<?php if ([] !== $ese_sectores) : ?>
<section id="sectores" class="hero-next bg-white relative z-10">
    <div class="hero-next__clouds" aria-hidden="true" data-reveal="fade">
        <img src="<?php echo esc_url(ESE_LATAM_URI . '/assets/imgs/clouds.webp'); ?>" alt="" decoding="async">
    </div>

    <div class="sectores" data-embla data-embla-contain="false" data-embla-loop="true" data-embla-autoplay="6000">
        <header class="sectores__header" data-reveal-header>
            <div>
                <?php if ('' !== $ese_sec_copy['kicker']) : ?>
                    <p class="type-kicker text-secondary">/ <?php echo esc_html($ese_sec_copy['kicker']); ?></p>
                <?php endif; ?>
                <?php if ('' !== $ese_sec_copy['titulo']) : ?>
                    <h2 class="type-h2 mt-6 uppercase">
                        <?php echo ese_latam_titulo($ese_sec_copy['titulo'], 'span', 'hl'); ?>
                    </h2>
                <?php endif; ?>
            </div>
            <?php if ('' !== $ese_sec_copy['desc']) : ?>
                <p class="sectores__desc"><?php echo $ese_sec_copy['desc']; ?></p>
            <?php endif; ?>
        </header>

        <div class="sectores__slider">
            <div class="sectores__legend" data-reveal="right">
                <div class="sectores__legend-text">
                    <p class="sectores__counter">
                        <span data-embla-current>01</span>
                        <?php // slider.ts lo reescribe al iniciar; se imprime igual para que
                        // el total sea correcto antes del JS y sin él. ?>
                        <span class="sectores__counter-total" data-embla-total>/<?php echo esc_html(str_pad((string) count($ese_sectores), 2, '0', STR_PAD_LEFT)); ?></span>
                    </p>
                    <?php if ('' !== $ese_sec_copy['leyenda']) : ?>
                        <p><?php echo esc_html($ese_sec_copy['leyenda']); ?></p>
                    <?php endif; ?>
                    <div class="sectores__progress" aria-hidden="true">
                        <span class="sectores__progress-bar" data-embla-progress></span>
                    </div>
                </div>
                <div class="sectores__controls">
                    <button type="button" class="embla__arrow embla__arrow--solid is-mirrored" data-embla-prev
                        aria-label="<?php esc_attr_e('Slide anterior', 'ese-latam'); ?>">
                        <svg width="16" height="13" viewBox="0 0 16 13" fill="none" xmlns="http://www.w3.org/2000/svg"
                            aria-hidden="true">
                            <path
                                d="M15.7165 7.15792L9.95748 12.7276C9.77717 12.902 9.53261 13 9.2776 13C9.02259 13 8.77803 12.902 8.59772 12.7276C8.4174 12.5532 8.3161 12.3167 8.3161 12.0701C8.3161 11.8235 8.4174 11.587 8.59772 11.4126L12.7178 7.42945H0.959834C0.70527 7.42945 0.461133 7.33164 0.281129 7.15756C0.101125 6.98347 0 6.74736 0 6.50116C0 6.25496 0.101125 6.01885 0.281129 5.84476C0.461133 5.67067 0.70527 5.57287 0.959834 5.57287H12.7178L8.59932 1.58743C8.419 1.41304 8.3177 1.17652 8.3177 0.929896C8.3177 0.683272 8.419 0.44675 8.59932 0.27236C8.77963 0.0979708 9.02419 0 9.2792 0C9.5342 0 9.77877 0.0979708 9.95908 0.27236L15.7181 5.84208C15.8076 5.92843 15.8786 6.03104 15.9269 6.144C15.9753 6.25696 16.0001 6.37805 16 6.50032C15.9998 6.62259 15.9747 6.74362 15.9261 6.85647C15.8774 6.96933 15.8062 7.07177 15.7165 7.15792Z"
                                fill="currentColor" />
                        </svg>
                    </button>
                    <button type="button" class="embla__arrow embla__arrow--solid" data-embla-next
                        aria-label="<?php esc_attr_e('Slide siguiente', 'ese-latam'); ?>">
                        <svg width="16" height="13" viewBox="0 0 16 13" fill="none" xmlns="http://www.w3.org/2000/svg"
                            aria-hidden="true">
                            <path
                                d="M15.7165 7.15792L9.95748 12.7276C9.77717 12.902 9.53261 13 9.2776 13C9.02259 13 8.77803 12.902 8.59772 12.7276C8.4174 12.5532 8.3161 12.3167 8.3161 12.0701C8.3161 11.8235 8.4174 11.587 8.59772 11.4126L12.7178 7.42945H0.959834C0.70527 7.42945 0.461133 7.33164 0.281129 7.15756C0.101125 6.98347 0 6.74736 0 6.50116C0 6.25496 0.101125 6.01885 0.281129 5.84476C0.461133 5.67067 0.70527 5.57287 0.959834 5.57287H12.7178L8.59932 1.58743C8.419 1.41304 8.3177 1.17652 8.3177 0.929896C8.3177 0.683272 8.419 0.44675 8.59932 0.27236C8.77963 0.0979708 9.02419 0 9.2792 0C9.5342 0 9.77877 0.0979708 9.95908 0.27236L15.7181 5.84208C15.8076 5.92843 15.8786 6.03104 15.9269 6.144C15.9753 6.25696 16.0001 6.37805 16 6.50032C15.9998 6.62259 15.9747 6.74362 15.9261 6.85647C15.8774 6.96933 15.8062 7.07177 15.7165 7.15792Z"
                                fill="currentColor" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="embla__viewport" data-embla-viewport>
                <div class="embla__container" data-reveal-stagger data-reveal-delay="0.2">
                    <?php foreach ($ese_sectores as $sector): ?>
                        <article class="embla__slide sector-card">
                            <?php if ('' !== $sector['img']) : ?>
                                <img class="sector-card__img"
                                    src="<?php echo esc_url($sector['img']); ?>"
                                    alt="<?php echo esc_attr($sector['title']); ?>" loading="lazy" decoding="async">
                            <?php endif; ?>
                            <a href="<?php echo esc_url(ese_latam_sector_url($sector)); ?>" class="sector-card__chip"
                                aria-label="<?php echo esc_attr(sprintf(__('Ver más sobre %s', 'ese-latam'), $sector['title'])); ?>">
                                <svg width="16" height="13" viewBox="0 0 16 13" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path
                                        d="M15.7165 7.15792L9.95748 12.7276C9.77717 12.902 9.53261 13 9.2776 13C9.02259 13 8.77803 12.902 8.59772 12.7276C8.4174 12.5532 8.3161 12.3167 8.3161 12.0701C8.3161 11.8235 8.4174 11.587 8.59772 11.4126L12.7178 7.42945H0.959834C0.70527 7.42945 0.461133 7.33164 0.281129 7.15756C0.101125 6.98347 0 6.74736 0 6.50116C0 6.25496 0.101125 6.01885 0.281129 5.84476C0.461133 5.67067 0.70527 5.57287 0.959834 5.57287H12.7178L8.59932 1.58743C8.419 1.41304 8.3177 1.17652 8.3177 0.929896C8.3177 0.683272 8.419 0.44675 8.59932 0.27236C8.77963 0.0979708 9.02419 0 9.2792 0C9.5342 0 9.77877 0.0979708 9.95908 0.27236L15.7181 5.84208C15.8076 5.92843 15.8786 6.03104 15.9269 6.144C15.9753 6.25696 16.0001 6.37805 16 6.50032C15.9998 6.62259 15.9747 6.74362 15.9261 6.85647C15.8774 6.96933 15.8062 7.07177 15.7165 7.15792Z"
                                        fill="currentColor" />
                                </svg>
                            </a>
                            <div class="sector-card__body">
                                <h3 class="sector-card__title"><?php echo esc_html($sector['title']); ?></h3>
                                <?php if ('' !== $sector['desc']) : ?>
                                    <p class="sector-card__desc"><?php echo esc_html($sector['desc']); ?></p>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php // Marquee "Transformamos" (Figma node 3287-283) — texto gigante ligado al
      // scroll. Las dos pasadas van en UNA sola sección: marquee.ts anima por
      // track ([data-marquee]), no por sección, así que la segunda conserva su
      // sentido invertido y además ambas comparten el mismo rango de scroll
      // (el trigger es el padre), con lo que se espejan exactamente. ?>
<?php // Dos frases de marca, una por pista: arriba el claim (el mismo del
// pie del footer), abajo el diferenciador. Tres copias por pista para que
// el recorrido del scrub (±12%) nunca descubra el borde. ?>
<?php
// Una palabra por pista (el lema partido en dos): el track recorre solo ±12%
// con el scroll, así que una frase larga nunca llegaba a leerse entera; una
// palabra sí cabe completa en el ancho de la pantalla.
$ese_marquee = ese_latam_home('marquee_pistas');
$ese_marquee = is_array($ese_marquee)
    ? array_values(array_filter($ese_marquee, static fn (array $p): bool => '' !== trim((string) ($p['texto'] ?? ''))))
    : [];
?>
<?php if ([] !== $ese_marquee) : ?>
<section class="marquee bg-white relative z-10" aria-hidden="true">
    <?php // data-marquee="right" invierte el recorrido del scrub (ver marquee.ts).
    // Tres copias por pista para que el recorrido nunca descubra el borde. ?>
    <?php foreach ($ese_marquee as $ese_pista):
        $ese_texto = trim((string) ($ese_pista['texto'] ?? ''));
        $ese_dir   = 'right' === ($ese_pista['direccion'] ?? '') ? 'right' : '';
        ?>
        <div class="marquee__track" <?php echo '' !== $ese_dir ? 'data-marquee="right"' : 'data-marquee'; ?>>
            <span><?php echo esc_html($ese_texto); ?></span>
            <span><?php echo esc_html($ese_texto); ?></span>
            <span><?php echo esc_html($ese_texto); ?></span>
        </div>
    <?php endforeach; ?>
</section>
<?php endif; ?>

<?php
// Sección "Productos" (Figma node 3287-243) — slider de foco central con
// parallax de fondo. Productos reales del CPT (mismo criterio que
// template-parts/catalogo-banner.php): cada card enlaza a su propia ficha;
// si el cliente todavía no publicó ninguno, cae al set estático de ejemplo
// (que enlaza al catálogo). Datos de card y fallback viven en
// inc/template-tags.php, compartidos con "Soluciones recomendadas" de la
// ficha de producto.
// "Productos destacados" (campo de relación) fija cuáles salen y en qué
// orden; vacío, siguen siendo los 12 más recientes del catálogo.
$ese_productos_ids = ese_latam_home('productos_destacados');
$ese_productos_ids = is_array($ese_productos_ids) ? array_map('intval', $ese_productos_ids) : [];

if ([] !== $ese_productos_ids) {
    $ese_productos_query = new WP_Query([
        'post_type'      => 'producto',
        'post_status'    => 'publish',
        'post__in'       => $ese_productos_ids,
        'orderby'        => 'post__in',
        'posts_per_page' => count($ese_productos_ids),
    ]);
} else {
    $ese_productos_query = new WP_Query([
        'post_type'      => 'producto',
        'post_status'    => 'publish',
        'posts_per_page' => 12,
        'orderby'        => 'menu_order date',
        'order'          => 'ASC',
    ]);
}

// Una pastilla por PRODUCTO (los de "Productos destacados") y un slide por
// cada una de sus CAPACIDADES; los colores se eligen dentro de la tarjeta.
// Ver ese_latam_producto_capacidades() en inc/template-tags.php.
$ese_producto_slides  = [];
$ese_producto_filtros = [];

foreach ($ese_productos_query->posts as $ese_prod_post) {
    $ese_prod_card = ese_latam_producto_card_data($ese_prod_post->ID);
    $ese_prod_key  = (string) $ese_prod_post->post_name;

    $ese_producto_filtros[$ese_prod_key] = $ese_prod_card['name'];

    foreach (ese_latam_producto_capacidades($ese_prod_post->ID) as $ese_prod_cap) {
        $ese_producto_slides[] = [
            'producto' => $ese_prod_key,
            'cat'      => $ese_prod_card['cat'],
            'name'     => $ese_prod_card['name'],
            'href'     => $ese_prod_card['href'],
            'material' => $ese_prod_card['material'],
            'litraje'  => $ese_prod_cap['litraje'],
            'colores'  => $ese_prod_cap['colores'],
            // El primer color es el que la tarjeta muestra al cargar; sin
            // fotos por color y capacidad cae a la del producto.
            'img'      => $ese_prod_cap['colores'][0]['img']
                ?? ese_latam_producto_foto($ese_prod_post->ID, $ese_prod_cap['litraje']),
        ];
    }
}

// Un solo producto no es un filtro: la pastilla repetiría el nombre que ya
// lleva cada tarjeta.
if (count($ese_producto_filtros) < 2) {
    $ese_producto_filtros = [];
}

$ese_producto_filtro_activo = (string) (array_key_first($ese_producto_filtros) ?? '');

$ese_prod_copy = [
    'kicker' => trim((string) ese_latam_home('productos_kicker', '')),
    'titulo' => trim((string) ese_latam_home('productos_titulo', '')),
    'desc'   => ese_latam_texto_rico((string) ese_latam_home('productos_desc', '')),
];

$ese_productos_cta = ese_latam_enlace(ese_latam_home('productos_cta'));

?>
<?php // El slider vive de productos publicados: sin ninguno, no hay sección. ?>
<?php if ([] !== $ese_producto_slides) : ?>
<section id="productos" class="productos-wrap bg-white relative z-10">
    <div class="productos">
        <?php // Skyline de fondo: el parallax (parallax.ts) va en el <img>, no
        // en el wrapper — el wrapper es el que recorta (overflow hidden) y es
        // el trigger que mide el recorrido. ?>
        <div class="productos__bg" aria-hidden="true">
            <img src="<?php echo esc_url(ESE_LATAM_URI . '/assets/imgs/productos/bg-skyline.png'); ?>" alt=""
                loading="lazy" decoding="async" data-parallax data-parallax-from="9" data-parallax-to="-9">
        </div>
        <?php // Las hojas entran con un fade+diagonal al llegar la sección, y
        // además EMERGEN de su esquina hacia el centro de la sección con el
        // scroll (parallax en diagonal: X e Y a la vez) — dos animaciones GSAP
        // independientes, por eso van en dos elementos distintos (wrapper +
        // img, ver main.css): la entrada en el <img>, el parallax continuo en
        // el wrapper. La izquierda baja hacia la derecha; la derecha (espejada
        // por CSS en el <img>) baja hacia la izquierda. ?>
        <div class="productos__leaves-wrap productos__leaves-wrap--left" aria-hidden="true" data-parallax
            data-parallax-from="-34" data-parallax-to="18" data-parallax-x-from="-34" data-parallax-x-to="18">
            <img class="productos__leaves"
                src="<?php echo esc_url(ESE_LATAM_URI . '/assets/imgs/productos/leaves.png'); ?>" alt=""
                loading="lazy" decoding="async" data-reveal="corner-tl">
        </div>
        <div class="productos__leaves-wrap productos__leaves-wrap--right" aria-hidden="true" data-parallax
            data-parallax-from="-34" data-parallax-to="18" data-parallax-x-from="34" data-parallax-x-to="-18">
            <img class="productos__leaves"
                src="<?php echo esc_url(ESE_LATAM_URI . '/assets/imgs/productos/leaves.png'); ?>" alt=""
                loading="lazy" decoding="async" data-reveal="corner-tr" data-reveal-delay="0.18">
        </div>

        <header class="productos__header" data-reveal-header>
            <?php if ('' !== $ese_prod_copy['kicker']) : ?>
                <p class="type-kicker text-white/90">/ <?php echo esc_html($ese_prod_copy['kicker']); ?></p>
            <?php endif; ?>
            <?php if ('' !== $ese_prod_copy['titulo']) : ?>
                <h2 class="productos__title">
                    <?php echo ese_latam_titulo($ese_prod_copy['titulo']); ?>
                </h2>
            <?php endif; ?>
            <?php if ('' !== $ese_prod_copy['desc']) : ?>
                <p class="productos__desc"><?php echo $ese_prod_copy['desc']; ?></p>
            <?php endif; ?>
        </header>

        <?php // Una pastilla por producto: al tocarla, product-filter.ts deja en
        // el slider solo las tarjetas con su mismo data-producto (las variantes
        // de ese producto) y reinicia Swiper. Sin JS quedan todas las variantes
        // de todos los productos, que es el mismo contenido sin recortar. ?>
        <?php if ([] !== $ese_producto_filtros) : ?>
            <div class="productos__filters" data-reveal="up" role="tablist" data-producto-filtros
                aria-label="<?php esc_attr_e('Elegir producto', 'ese-latam'); ?>">
                <?php foreach ($ese_producto_filtros as $ese_filtro_slug => $ese_filtro_nombre): ?>
                    <?php $ese_filtro_on = $ese_filtro_slug === $ese_producto_filtro_activo; ?>
                    <button type="button" role="tab"
                        class="productos__filter<?php echo $ese_filtro_on ? ' is-active' : ''; ?>"
                        data-producto-filtro="<?php echo esc_attr($ese_filtro_slug); ?>"
                        aria-selected="<?php echo $ese_filtro_on ? 'true' : 'false'; ?>">
                        <?php echo esc_html($ese_filtro_nombre); ?>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="productos__slider" data-reveal="up">
            <button type="button" class="embla__arrow embla__arrow--glass is-mirrored" data-carousel-prev
                aria-label="<?php esc_attr_e('Producto anterior', 'ese-latam'); ?>">
                <svg width="16" height="13" viewBox="0 0 16 13" fill="none" xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true">
                    <path
                        d="M15.7165 7.15792L9.95748 12.7276C9.77717 12.902 9.53261 13 9.2776 13C9.02259 13 8.77803 12.902 8.59772 12.7276C8.4174 12.5532 8.3161 12.3167 8.3161 12.0701C8.3161 11.8235 8.4174 11.587 8.59772 11.4126L12.7178 7.42945H0.959834C0.70527 7.42945 0.461133 7.33164 0.281129 7.15756C0.101125 6.98347 0 6.74736 0 6.50116C0 6.25496 0.101125 6.01885 0.281129 5.84476C0.461133 5.67067 0.70527 5.57287 0.959834 5.57287H12.7178L8.59932 1.58743C8.419 1.41304 8.3177 1.17652 8.3177 0.929896C8.3177 0.683272 8.419 0.44675 8.59932 0.27236C8.77963 0.0979708 9.02419 0 9.2792 0C9.5342 0 9.77877 0.0979708 9.95908 0.27236L15.7181 5.84208C15.8076 5.92843 15.8786 6.03104 15.9269 6.144C15.9753 6.25696 16.0001 6.37805 16 6.50032C15.9998 6.62259 15.9747 6.74362 15.9261 6.85647C15.8774 6.96933 15.8062 7.07177 15.7165 7.15792Z"
                        fill="currentColor" />
                </svg>
            </button>

            <?php // Cards con el diseño de "Soluciones recomendadas" (product-card--glass),
            // pero con la animación original de la home: coverflow plano (valores por
            // defecto del carrusel: rotate 0 / depth 100 / modifier 2.5).
            // Una tarjeta por capacidad: la capacidad es fija y el color se elige
            // con los swatches (product-card-colors.ts cambia la foto y el rótulo
            // sin recargar). `data-capacidad` identifica la tarjeta junto a
            // `data-producto`, para que el cambio alcance también a las copias
            // que el carrusel crea para cerrar el loop. ?>
            <div class="swiper" data-product-carousel data-carousel-visible="5">
                <div class="swiper-wrapper">
                    <?php foreach ($ese_producto_slides as $producto): ?>
                        <?php
                        $ese_slide_alt   = trim($producto['name'] . ' ' . $producto['litraje']);
                        $ese_slide_color = $producto['colores'][0] ?? null;
                        ?>
                        <article class="swiper-slide product-card product-card--glass"
                            data-producto="<?php echo esc_attr($producto['producto']); ?>"
                            data-capacidad="<?php echo esc_attr($producto['litraje']); ?>">
                            <div class="product-card__media">
                                <span class="product-card__shadow" aria-hidden="true" data-float-shadow></span>
                                <?php if ('' !== $producto['img']) : ?>
                                    <img class="product-card__img" data-card-img
                                        data-card-alt="<?php echo esc_attr($ese_slide_alt); ?>"
                                        src="<?php echo esc_url($producto['img']); ?>"
                                        alt="<?php echo esc_attr(trim($ese_slide_alt . ' ' . (string) ($ese_slide_color['nombre'] ?? ''))); ?>"
                                        loading="lazy" decoding="async"
                                        data-float data-float-distance="14" data-float-duration="3.2">
                                <?php endif; ?>
                            </div>
                            <div class="product-card__body">
                                <p class="product-card__cat"><?php echo esc_html($producto['cat']); ?></p>
                                <h3 class="product-card__name"><?php echo esc_html($producto['name']); ?></h3>
                                <?php // Con un solo color no hay nada que elegir: una fila de un
                                // swatch sería un control muerto. ?>
                                <?php if (count($producto['colores']) > 1) : ?>
                                    <div class="product-card__colors" role="group"
                                        aria-label="<?php esc_attr_e('Elegir color', 'ese-latam'); ?>">
                                        <?php foreach ($producto['colores'] as $ese_i => $ese_color): ?>
                                            <button type="button"
                                                class="product-card__color<?php echo 0 === $ese_i ? ' is-active' : ''; ?>"
                                                style="--swatch: <?php echo esc_attr($ese_color['swatch']); ?>;"
                                                data-card-color
                                                data-color-img="<?php echo esc_url($ese_color['img']); ?>"
                                                data-color-name="<?php echo esc_attr($ese_color['nombre']); ?>"
                                                aria-pressed="<?php echo 0 === $ese_i ? 'true' : 'false'; ?>"
                                                aria-label="<?php echo esc_attr($ese_color['nombre']); ?>"></button>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <dl class="product-card__specs">
                                    <div>
                                        <dt><?php esc_html_e('Litraje', 'ese-latam'); ?></dt>
                                        <dd><?php echo esc_html('' !== $producto['litraje'] ? $producto['litraje'] : '—'); ?></dd>
                                    </div>
                                    <div>
                                        <dt><?php esc_html_e('Material', 'ese-latam'); ?></dt>
                                        <dd><?php echo esc_html($producto['material']); ?></dd>
                                    </div>
                                </dl>
                            </div>
                            <?php // Mismo criterio que template-parts/catalogo-grid.php: el chip
                            // ES el enlace (no la card entera), así el drag/swipe del slider
                            // sobre el resto de la tarjeta no compite con la navegación. ?>
                            <a class="product-card__chip" href="<?php echo esc_url($producto['href']); ?>"
                                aria-label="<?php echo esc_attr(sprintf(__('Ver %s', 'ese-latam'), $producto['name'])); ?>">
                                <svg width="16" height="13" viewBox="0 0 16 13" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path
                                        d="M15.7165 7.15792L9.95748 12.7276C9.77717 12.902 9.53261 13 9.2776 13C9.02259 13 8.77803 12.902 8.59772 12.7276C8.4174 12.5532 8.3161 12.3167 8.3161 12.0701C8.3161 11.8235 8.4174 11.587 8.59772 11.4126L12.7178 7.42945H0.959834C0.70527 7.42945 0.461133 7.33164 0.281129 7.15756C0.101125 6.98347 0 6.74736 0 6.50116C0 6.25496 0.101125 6.01885 0.281129 5.84476C0.461133 5.67067 0.70527 5.57287 0.959834 5.57287H12.7178L8.59932 1.58743C8.419 1.41304 8.3177 1.17652 8.3177 0.929896C8.3177 0.683272 8.419 0.44675 8.59932 0.27236C8.77963 0.0979708 9.02419 0 9.2792 0C9.5342 0 9.77877 0.0979708 9.95908 0.27236L15.7181 5.84208C15.8076 5.92843 15.8786 6.03104 15.9269 6.144C15.9753 6.25696 16.0001 6.37805 16 6.50032C15.9998 6.62259 15.9747 6.74362 15.9261 6.85647C15.8774 6.96933 15.8062 7.07177 15.7165 7.15792Z"
                                        fill="currentColor" />
                                </svg>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="button" class="embla__arrow embla__arrow--glass" data-carousel-next
                aria-label="<?php esc_attr_e('Producto siguiente', 'ese-latam'); ?>">
                <svg width="16" height="13" viewBox="0 0 16 13" fill="none" xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true">
                    <path
                        d="M15.7165 7.15792L9.95748 12.7276C9.77717 12.902 9.53261 13 9.2776 13C9.02259 13 8.77803 12.902 8.59772 12.7276C8.4174 12.5532 8.3161 12.3167 8.3161 12.0701C8.3161 11.8235 8.4174 11.587 8.59772 11.4126L12.7178 7.42945H0.959834C0.70527 7.42945 0.461133 7.33164 0.281129 7.15756C0.101125 6.98347 0 6.74736 0 6.50116C0 6.25496 0.101125 6.01885 0.281129 5.84476C0.461133 5.67067 0.70527 5.57287 0.959834 5.57287H12.7178L8.59932 1.58743C8.419 1.41304 8.3177 1.17652 8.3177 0.929896C8.3177 0.683272 8.419 0.44675 8.59932 0.27236C8.77963 0.0979708 9.02419 0 9.2792 0C9.5342 0 9.77877 0.0979708 9.95908 0.27236L15.7181 5.84208C15.8076 5.92843 15.8786 6.03104 15.9269 6.144C15.9753 6.25696 16.0001 6.37805 16 6.50032C15.9998 6.62259 15.9747 6.74362 15.9261 6.85647C15.8774 6.96933 15.8062 7.07177 15.7165 7.15792Z"
                        fill="currentColor" />
                </svg>
            </button>
        </div>

        <div class="productos__pagination" aria-hidden="true"></div>

        <?php if ('' !== $ese_productos_cta['label']) : ?>
            <a href="<?php echo esc_url($ese_productos_cta['href']); ?>" class="productos__cta"<?php echo ese_latam_target_attr($ese_productos_cta['target']); ?> data-reveal="up">
                <?php echo esc_html($ese_productos_cta['label']); ?>
            </a>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<?php
// Sección "Distribuidores" (Figma node 3323-55) — globo 3D interactivo
// (Three.js + GSAP, ver globe-scene.ts) con lista de países a la derecha:
// al elegir un país, el planeta gira hasta él, lo ilumina y lo señala.
//
// Los datos viven en inc/distribuidores.php (compartidos con la página
// "Encuentra un distribuidor"): solo Perú y México traen contenido real.
$ese_distribuidores = ese_latam_distribuidores();

$ese_dst_copy = [
    'kicker' => trim((string) ese_latam_home('distribuidores_kicker', '')),
    'titulo' => trim((string) ese_latam_home('distribuidores_titulo', '')),
    'desc'   => trim((string) ese_latam_home('distribuidores_desc', '')),
];

$ese_dst_cta = ese_latam_enlace(ese_latam_home('distribuidores_cta'));
?>
<?php // El globo y el riel viven de la lista de países: sin ninguno, no hay sección. ?>
<?php if ([] !== $ese_distribuidores) : ?>
<section id="distribuidores" class="distribuidores relative z-10" data-globe
    <?php echo '' !== $ese_dst_copy['titulo'] ? 'aria-labelledby="distribuidores-titulo"' : ''; ?>>
    <?php
    // El planeta es el fondo de la sección: ocupa las tres columnas de la
    // grilla y el resto flota encima. Órbitas, partículas y halo se dibujan
    // dentro del mismo canvas (globe-scene.ts), así que no hay capas de
    // decoración aparte. Los dos tooltips los posiciona el JS sobre la
    // proyección 3D del país: el de hover sigue al cursor y el del país
    // elegido queda clavado sobre él.
    ?>
    <?php // Sin data-lenis-prevent: el stage cubre TODA la sección y el globo
    // no usa la rueda, así que excluirlo de Lenis solo trababa el scroll de
    // la página al pasar el mouse por encima. ?>
    <div class="distribuidores__stage" aria-hidden="true">
        <div class="distribuidores__canvas" data-globe-canvas
            data-earth-map="<?php echo esc_url(ESE_LATAM_URI . '/assets/imgs/distribuidores/earth-diffuse.webp'); ?>"
            data-topology="<?php echo esc_url(ESE_LATAM_URI . '/assets/data/countries-110m.json'); ?>"></div>
        <div class="distribuidores__hover-tip" data-globe-hover-tip></div>
        <div class="distribuidores__sel-tip" data-globe-sel-tip>
            <div class="distribuidores__sel-tip-inner">
                <div class="distribuidores__sel-tip-label" data-globe-sel-label></div>
                <div class="distribuidores__sel-tip-arrow"></div>
            </div>
        </div>
        <div class="distribuidores__loader" data-globe-loader
            data-error="<?php esc_attr_e('No se pudo cargar el planeta', 'ese-latam'); ?>">
            <?php esc_html_e('Cargando planeta…', 'ese-latam'); ?>
        </div>
    </div>

    <?php // La entrada de toda la sección (globo, textos, riel, tarjeta y enlace)
    // la coreografía distribuidores-intro.ts en un solo timeline — por eso
    // estos bloques NO llevan data-reveal como el resto de la home. ?>
    <header class="distribuidores__intro">
        <?php if ('' !== $ese_dst_copy['kicker']) : ?>
            <p class="distribuidores__eyebrow">/ <?php echo esc_html($ese_dst_copy['kicker']); ?></p>
        <?php endif; ?>
        <?php if ('' !== $ese_dst_copy['titulo']) : ?>
            <h2 class="distribuidores__title" id="distribuidores-titulo">
                <?php echo ese_latam_titulo($ese_dst_copy['titulo']); ?>
            </h2>
        <?php endif; ?>
        <?php if ('' !== $ese_dst_copy['desc']) : ?>
            <p class="distribuidores__desc"><?php echo esc_html($ese_dst_copy['desc']); ?></p>
        <?php endif; ?>
    </header>

    <div class="distribuidores__picker">
        <?php // Riel de países (desktop). ?>
        <?php // Con muchos países el riel scrollea: Lenis (allowNestedScroll en
        // smooth-scroll.ts) le cede la rueda solo cuando de verdad tiene
        // recorrido; si no, la rueda sigue moviendo la página. ?>
        <div class="distribuidores__rail">
            <p class="distribuidores__total">
                <span class="distribuidores__rail-count"><?php echo count($ese_distribuidores); ?></span>
                <span class="distribuidores__total-label"><?php esc_html_e('Países conectados', 'ese-latam'); ?></span>
            </p>

            <div class="distribuidores__rail-items" data-country-list role="tablist"
                aria-label="<?php esc_attr_e('Países con distribuidor', 'ese-latam'); ?>">
                <?php foreach ($ese_distribuidores as $i => $pais): ?>
                    <button type="button" class="country-pill<?php echo 0 === $i ? ' is-active' : ''; ?>" role="tab"
                        aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>"
                        aria-controls="distribuidor-<?php echo esc_attr($pais['slug']); ?>" data-country
                        data-country-slug="<?php echo esc_attr($pais['slug']); ?>"
                        data-lat="<?php echo esc_attr($pais['lat']); ?>" data-lng="<?php echo esc_attr($pais['lng']); ?>"
                        data-iso="<?php echo esc_attr($pais['iso']); ?>">
                        <span class="country-pill__dot" aria-hidden="true"></span>
                        <span class="country-pill__name"><?php echo esc_html($pais['name']); ?></span>
                        <span class="country-pill__count"><?php echo count($pais['items']); ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <?php
        // Mobile y tablet: el mismo set de países en un <select> nativo — el
        // riel envolvía en filas irregulares y empujaba el globo fuera de
        // pantalla. Va en el marcado (no armado por JS) para que exista
        // aunque el JS todavía no haya corrido; CSS decide cuál se ve.
        ?>
        <div class="distribuidores__mobile">
            <p class="distribuidores__total">
                <span class="distribuidores__rail-count"><?php echo count($ese_distribuidores); ?></span>
                <span class="distribuidores__total-label"><?php esc_html_e('Países conectados', 'ese-latam'); ?></span>
            </p>
            <div class="distribuidores__select">
                <label class="sr-only"
                    for="distribuidores-pais"><?php esc_html_e('Elegir país', 'ese-latam'); ?></label>
                <span class="distribuidores__select-dot" aria-hidden="true"></span>
                <select id="distribuidores-pais" data-country-select>
                    <?php foreach ($ese_distribuidores as $i => $pais): ?>
                        <option value="<?php echo esc_attr($pais['slug']); ?>" <?php selected(0, $i); ?>>
                            <?php echo esc_html($pais['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <svg class="distribuidores__select-chev" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">
                    <path d="M6 9l6 6 6-6" />
                </svg>
            </div>
        </div>
    </div>

    <?php // Tarjeta flotante: los paneles se apilan en la misma celda de grilla,
    // así la tarjeta toma el alto del más largo y no salta al cambiar de país. ?>
    <div class="distribuidores__panel">
        <div class="distribuidores__panel-stack" data-country-panels aria-live="polite">
            <?php foreach ($ese_distribuidores as $i => $pais): ?>
                <article class="country-panel<?php echo 0 === $i ? ' is-active' : ''; ?>"
                    id="distribuidor-<?php echo esc_attr($pais['slug']); ?>"
                    data-country-panel="<?php echo esc_attr($pais['slug']); ?>" role="tabpanel">
                    <div class="country-panel__head">
                        <p class="country-panel__kicker"><?php esc_html_e('Distribuidores en', 'ese-latam'); ?></p>
                        <h3 class="country-panel__name"><?php echo esc_html($pais['name']); ?></h3>
                    </div>

                    <?php if ([] !== $pais['items']) : ?>
                    <ul class="country-panel__list">
                        <?php foreach ($pais['items'] as $n => $item):
                            $ese_item_web  = (string) ($item['web'] ?? '');
                            $ese_item_text = (string) ($item['address'] ?? $item['city'] ?? '');
                            $ese_item_tag  = '' !== $ese_item_web ? 'a' : 'div';
                            ?>
                            <li>
                                <<?php echo $ese_item_tag; ?> class="country-panel__item"
                                    <?php if ('' !== $ese_item_web) : ?>href="<?php echo esc_url($ese_item_web); ?>" target="_blank" rel="noopener"<?php endif; ?>>
                                    <span class="country-panel__num" aria-hidden="true"><?php echo (int) $n + 1; ?></span>
                                    <span class="country-panel__text">
                                        <strong><?php echo esc_html($item['name'] ?? ''); ?></strong>
                                        <?php if ('' !== $ese_item_text) : ?>
                                            <span><?php echo esc_html($ese_item_text); ?></span>
                                        <?php endif; ?>
                                    </span>
                                    <?php if ('' !== $ese_item_web) : ?>
                                        <svg class="country-panel__arrow" width="14" height="14" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"
                                            stroke-linejoin="round" aria-hidden="true">
                                            <path d="M7 17L17 7" />
                                            <path d="M8 7h9v9" />
                                        </svg>
                                    <?php endif; ?>
                                </<?php echo $ese_item_tag; ?>>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if ('' !== $ese_dst_cta['label']) : ?>
    <div class="distribuidores__cta-wrap">
        <a href="<?php echo esc_url($ese_dst_cta['href']); ?>" class="distribuidores__link"<?php echo ese_latam_target_attr($ese_dst_cta['target']); ?>>
            <span class="distribuidores__link-text">
                <span class="distribuidores__link-label"><?php echo esc_html($ese_dst_cta['label']); ?></span>
                <span class="distribuidores__link-line" aria-hidden="true"></span>
            </span>
            <span class="distribuidores__link-icon" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14" />
                    <path d="M13 6l6 6-6 6" />
                </svg>
            </span>
        </a>
    </div>
    <?php endif; ?>
</section>
<?php endif; ?>

<?php get_template_part('template-parts/residuos'); ?>

<?php get_template_part('template-parts/certificaciones'); ?>

<?php // Sección "Contactemos" (Figma node 535-782) — CTA de cierre, al pie de todo el contenido ?>
<?php get_template_part('template-parts/contacto'); ?>

<?php get_footer(); ?>