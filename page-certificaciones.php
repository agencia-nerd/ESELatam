<?php
/**
 * Template Name: Certificaciones
 *
 * Página "Certificaciones" (Figma node 3785-4089, "07 – Certificaciones").
 * WordPress la aplica sola a la página con slug `certificaciones` (que crea
 * inc/paginas.php) y, por el header de arriba, también se puede asignar a
 * mano desde el editor.
 *
 * Composición, en orden del Figma. Todo lo que ya existía se reutiliza:
 *   1. Hero oscuro (hero-interno.php)
 *   2. Nuestras certificaciones en detalle (certificaciones-detalle.php, propio)
 *   3. Marquee "marca la diferencia" (.nos-marquee)
 *   4. Marcando la diferencia → metodo-tabs.php (las tabs ESG de Nosotros, con 4 tabs)
 *   5. ¿Qué hace que sea excelente? → producto-pruebas.php (ficha de producto)
 *   6. Calidad superior desde el origen → objetivos-sticky.php (aside sticky de Nosotros)
 *   7. Estándar Blue Angel (blue-angel.php, propio)
 *   8. Valida certificados originales (valida-certificados.php, propio)
 *   9. Preguntas frecuentes de la categoría Certificaciones (faq-seccion.php)
 *  10. Casos reales (casos-reales.php)
 *  11. Contactemos → contacto.php
 *
 * @package EseLatam
 */
declare(strict_types=1);

get_header();

// Todo el copy de esta pantalla se edita en la propia página
// (inc/pcf-certificaciones.php). Los sellos salen del módulo.
$ese_id  = (int) get_queried_object_id();
$ese_cmp = static fn (string $name, $def = '') => ese_latam_campo($name, $ese_id, $def);

$ese_hero_cta = ese_latam_enlace($ese_cmp('certpag_hero_cta', null));
$ese_cal_salto = ese_latam_enlace($ese_cmp('certpag_cal_salto', null));
$ese_marquee  = trim((string) $ese_cmp('certpag_marquee'));

// "Marcando la diferencia": cada fila es una pestaña.
$ese_diferencia = [];
foreach ((array) $ese_cmp('certpag_dif_tabs', []) as $ese_fila) {
    $ese_t = trim((string) ($ese_fila['title'] ?? ''));
    if ('' === $ese_t) {
        continue;
    }
    $ese_diferencia[] = [
        'title' => $ese_t,
        'desc'  => (string) ($ese_fila['desc'] ?? ''),
        'img'   => ese_latam_img_url($ese_fila['img'] ?? ''),
    ];
}

// "Calidad superior": paneles del aside pegajoso. El id sale del título,
// que es lo que usa el ancla de navegación.
$ese_calidad = [];
foreach ((array) $ese_cmp('certpag_cal_items', []) as $ese_fila) {
    $ese_t = trim((string) ($ese_fila['title'] ?? ''));
    if ('' === $ese_t) {
        continue;
    }
    $ese_calidad[] = [
        'id'    => sanitize_title($ese_t),
        'title' => $ese_t,
        'img'   => ese_latam_img_url($ese_fila['img'] ?? ''),
        'text'  => (string) ($ese_fila['text'] ?? ''),
    ];
}
?>

<div class="nosotros nosotros--certificaciones" data-nosotros>
    <script>document.currentScript.parentElement.classList.add('is-js');</script>

    <?php
    get_template_part('template-parts/hero-interno', null, [
        'kicker'     => (string) $ese_cmp('certpag_hero_kicker'),
        'title'      => (string) $ese_cmp('certpag_hero_titulo'),
        'desc'       => (string) $ese_cmp('certpag_hero_desc'),
        'cta_label'  => $ese_hero_cta['label'],
        'cta_href'   => $ese_hero_cta['href'],
        'cta_target' => $ese_hero_cta['target'],
        'current'    => get_the_title($ese_id),
        'bg'         => ese_latam_img_url($ese_cmp('certpag_hero_imagen')),
    ]);

    get_template_part('template-parts/certificaciones-detalle');
    ?>

    <?php if ('' !== $ese_marquee) : ?>
        <div class="nos-marquee nos-marquee--sm" aria-hidden="true">
            <p class="nos-marquee__track" data-marquee="right">
                <span><?php echo esc_html($ese_marquee); ?></span>
                <span><?php echo esc_html($ese_marquee); ?></span>
            </p>
        </div>
    <?php endif; ?>

    <?php
    if ([] !== $ese_diferencia) {
        get_template_part('template-parts/metodo-tabs', null, [
            'id'     => 'diferencia',
            'kicker' => (string) $ese_cmp('certpag_dif_kicker'),
            'title'  => (string) $ese_cmp('certpag_dif_titulo'),
            'desc'   => ese_latam_texto_rico((string) $ese_cmp('certpag_dif_desc')),
            'tabs'   => $ese_diferencia,
        ]);
    }

    get_template_part('template-parts/producto-pruebas');

    if ([] !== $ese_calidad) {
        get_template_part('template-parts/objetivos-sticky', null, [
            'id'         => 'calidad-superior',
            'kicker'     => (string) $ese_cmp('certpag_cal_kicker'),
            'title'      => (string) $ese_cmp('certpag_cal_titulo'),
            'desc'       => ese_latam_texto_rico((string) $ese_cmp('certpag_cal_desc')),
            'items'      => $ese_calidad,
            'skip_label' => $ese_cal_salto['label'],
            'skip_href'  => $ese_cal_salto['href'],
        ]);
    }

    get_template_part('template-parts/blue-angel');
    get_template_part('template-parts/valida-certificados');

    $ese_faq_enlace = ese_latam_enlace($ese_cmp('certpag_faq_enlace', null));
    get_template_part('template-parts/faq-seccion', null, [
        'categoria'   => ese_latam_faq_categoria_id($ese_cmp('certpag_faq_categoria', null), 'certificaciones'),
        'kicker'      => (string) $ese_cmp('certpag_faq_kicker'),
        'title'       => (string) $ese_cmp('certpag_faq_titulo'),
        'link_label'  => $ese_faq_enlace['label'],
        'link_href'   => $ese_faq_enlace['href'],
        'link_target' => $ese_faq_enlace['target'],
    ]);

    get_template_part('template-parts/casos-reales', null, ese_latam_args_casos($ese_id, 'certpag_casos'));

    get_template_part('template-parts/contacto', null, [
        'class' => 'contacto--upper',
    ]);
    ?>
</div>

<?php
get_footer();
