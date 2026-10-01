<?php
/**
 * Página de Contacto — bloque 3: preguntas frecuentes (Figma 3948-9141).
 *
 * El acordeón es template-parts/faq-lista.php, el mismo de la página
 * "Preguntas frecuentes".
 *
 * Las preguntas salen del módulo "Preguntas frecuentes" (las que tienen
 * encendido «¿Mostrar también en la página de Contacto?»); el antetítulo y
 * el titular, de la página.
 *
 * @package EseLatam
 */
declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

$ese_faqs = [];

foreach (ese_latam_modulo_entradas('faq') as $ese_pregunta) {
    // Solo se excluyen las que tienen el interruptor apagado a propósito:
    // las preguntas anteriores al campo no tienen valor y siguen saliendo.
    if ('0' === (string) get_post_meta($ese_pregunta->ID, 'en_contacto', true)) {
        continue;
    }
    $ese_faqs[] = [
        'q' => get_the_title($ese_pregunta->ID),
        'a' => ese_latam_texto_rico((string) ese_latam_campo('respuesta', $ese_pregunta->ID, '')),
    ];
}

if ([] === $ese_faqs) {
    return;
}

$ese_id     = (int) get_queried_object_id();
$ese_kicker = (string) ese_latam_campo('ctc_faq_kicker', $ese_id, '');
$ese_titulo = (string) ese_latam_campo('ctc_faq_titulo', $ese_id, '');
?>

<section class="ctc-faq">
    <header class="ctc-faq__header" data-reveal-header>
        <?php if ('' !== $ese_kicker) : ?>
            <p class="type-kicker text-secondary">/ <?php echo esc_html($ese_kicker); ?></p>
        <?php endif; ?>
        <?php if ('' !== $ese_titulo) : ?>
            <h2 class="type-h2 uppercase">
                <?php echo ese_latam_titulo($ese_titulo, 'span', 'hl'); ?>
            </h2>
        <?php endif; ?>
    </header>

    <?php get_template_part('template-parts/faq-lista', null, ['faqs' => $ese_faqs]); ?>
</section>
