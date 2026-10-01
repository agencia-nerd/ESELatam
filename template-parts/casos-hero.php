<?php
/**
 * Hero del blog de Casos de éxito (Figma 3891-3678): oscuro, con el
 * breadcrumb arriba a la izquierda y kicker + titular + bajada CENTRADOS.
 * Reutiliza el .nos-hero de las internas (mismos data-attributes, así lo
 * anima initHero de nosotros.ts) con la variante `.cx-hero` para el
 * centrado y la altura fija de 720px del Figma.
 *
 * Debe ir dentro del wrapper `.nosotros[data-nosotros]` (ver archive-caso.php
 * y home.php). Los valores por defecto son los del archivo de casos; quien
 * tiene campos —el blog— pasa los suyos, y una cadena vacía apaga ese
 * elemento. Sin foto de fondo el hero queda en el negro liso del Figma.
 *
 * @param array{kicker?: string, title?: string, desc?: string, current?: string, bg?: string} $args
 *
 * @package EseLatam
 */
declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

$ese_hero = wp_parse_args($args ?? [], [
    'kicker'       => __('Casos de éxito', 'ese-latam'),
    'title'        => __('Transformando', 'ese-latam') . "\n" . __('|operaciones|', 'ese-latam'),
    'desc'         => __('Ideas, investigaciones científicas, estándares globales de calidad y adaptaciones de recolección inteligente.', 'ese-latam'),
    'current'      => __('Casos de éxito', 'ese-latam'),
    'bg'           => ESE_LATAM_URI . '/assets/imgs/hero-poster.webp',
]);
?>

<section class="nos-hero nos-hero--interna ccs cx-hero" data-nos-hero>
    <?php if ('' !== $ese_hero['bg']) : ?>
        <div class="nos-hero__bg" aria-hidden="true" data-nos-hero-bg>
            <img src="<?php echo esc_url($ese_hero['bg']); ?>" alt="" decoding="async" fetchpriority="high">
        </div>
        <div class="nos-hero__shade" aria-hidden="true"></div>
    <?php endif; ?>
    <div class="nos-hero__glow" aria-hidden="true" data-nos-hero-glow></div>

    <div class="nos-hero__content cx-hero__content" data-nos-hero-content>
        <?php
        get_template_part('template-parts/breadcrumbs', null, [
            'current' => $ese_hero['current'],
            'attrs'   => 'data-nos-hero-crumb',
        ]);
        ?>

        <div class="cx-hero__body">
            <?php if ('' !== $ese_hero['kicker']) : ?>
                <p class="nos-hero__kicker" data-nos-hero-kicker><?php echo esc_html($ese_hero['kicker']); ?></p>
            <?php endif; ?>
            <?php if ('' !== $ese_hero['title']) : ?>
                <h1 class="nos-hero__title" data-nos-hero-title>
                    <?php echo ese_latam_titulo($ese_hero['title']); ?>
                </h1>
            <?php endif; ?>
            <?php if ('' !== $ese_hero['desc']) : ?>
                <p class="nos-hero__desc" data-nos-hero-desc><?php echo esc_html($ese_hero['desc']); ?></p>
            <?php endif; ?>
        </div>
    </div>
</section>
