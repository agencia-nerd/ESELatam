<?php
/**
 * Acordeón de preguntas frecuentes de la página "Preguntas frecuentes" (uno
 * por categoría).
 *
 * `<details>`/`<summary>` nativos: abrir y cerrar funciona sin JS, y
 * src/ts/modules/faq-acordeon.ts solo agrega la animación de altura y la
 * regla de "una abierta a la vez" dentro de cada lista.
 *
 * @param array{
 *     faqs: list<array{q: string, a: string}>,  `a` ya pasado por ese_latam_texto_rico()
 *     class?: string,                           Clase extra de la lista (variante de tamaño)
 *     abierta?: bool,                           ¿La primera pregunta sale abierta? (sí por defecto)
 * } $args
 *
 * @package EseLatam
 */
declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

$ese_fl = wp_parse_args($args ?? [], [
    'faqs'    => [],
    'class'   => '',
    'abierta' => true,
]);

if ([] === $ese_fl['faqs']) {
    return;
}
?>
<div class="<?php echo esc_attr(trim('ctc-faq__list ' . $ese_fl['class'])); ?>" data-faq>
    <?php foreach ($ese_fl['faqs'] as $ese_i => $ese_faq) : ?>
        <details class="ctc-faq__item" data-faq-item <?php echo $ese_fl['abierta'] && 0 === $ese_i ? 'open' : ''; ?>>
            <summary class="ctc-faq__q">
                <span><?php echo esc_html($ese_faq['q']); ?></span>
                <span class="ctc-faq__icon" aria-hidden="true">
                    <svg width="10" height="5" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5 5L0 0H10L5 5Z" fill="currentColor" />
                    </svg>
                </span>
            </summary>
            <div class="ctc-faq__a" data-faq-panel>
                <p><?php echo $ese_faq['a']; ?></p>
            </div>
        </details>
    <?php endforeach; ?>
</div>
