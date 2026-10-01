<?php
/**
 * Hero claro (Figma 3510-6983): breadcrumb + titular centrado light/bold +
 * bajada. El breadcrumb reutiliza template-parts/breadcrumbs.php, que ya
 * tiene el mismo look del Figma.
 *
 * Nació para la página Sectores y la reusa Preguntas frecuentes. Sin `$args`
 * lee los campos de Sectores, como siempre; cualquier otra página le pasa
 * su propio copy.
 *
 * @param array{
 *     title?: string,    Titular con la convención |resaltado| y saltos de línea
 *     desc?: string,     Bajada (HTML de ese_latam_texto_rico())
 *     current?: string,  Último tramo de la miga de pan
 * } $args
 *
 * @package EseLatam
 */
declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

$ese_sh_id   = (int) get_queried_object_id();
$ese_sh_args = wp_parse_args($args ?? [], [
    'title'     => null,
    'desc'      => null,
    'current'   => __('Soluciones', 'ese-latam'),
]);

$ese_sh_titulo = trim((string) ($ese_sh_args['title'] ?? ese_latam_campo('sectores_hero_titulo', $ese_sh_id, '')));
$ese_sh_desc   = $ese_sh_args['desc'] ?? ese_latam_texto_rico((string) ese_latam_campo('sectores_hero_desc', $ese_sh_id, ''));
$ese_sh_desc   = trim((string) $ese_sh_desc);
?>

<section class="sec-hero">
    <?php
    get_template_part('template-parts/breadcrumbs', null, [
        'current'   => (string) $ese_sh_args['current'],
        'class'     => 'sec-hero__crumb',
        'attrs'     => 'data-reveal="fade"',
        'home_icon' => true,
    ]);
    ?>

    <?php if ('' !== $ese_sh_titulo || '' !== $ese_sh_desc) : ?>
        <header class="sec-hero__header" data-reveal-header>
            <?php if ('' !== $ese_sh_titulo) : ?>
                <h1 class="sec-hero__title">
                    <?php echo ese_latam_titulo($ese_sh_titulo, 'span', 'hl'); ?>
                </h1>
            <?php endif; ?>
            <?php if ('' !== $ese_sh_desc) : ?>
                <p class="sec-hero__desc" data-reveal-desc><?php echo $ese_sh_desc; ?></p>
            <?php endif; ?>
        </header>
    <?php endif; ?>
</section>
