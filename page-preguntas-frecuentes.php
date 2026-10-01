<?php
/**
 * Template Name: Preguntas Frecuentes
 *
 * Página "Preguntas frecuentes". WordPress la aplica sola a la página con
 * slug `preguntas-frecuentes` (que crea inc/paginas.php) y, por el header de
 * arriba, también se puede asignar a mano desde el editor.
 *
 * Composición, reutilizando lo que ya existe en el sitio:
 *   1. Hero claro centrado → template-parts/sectores-hero.php (el de Sectores)
 *   2. Índice de categorías pegajoso + un acordeón por categoría. El índice
 *      marca la categoría que se está leyendo con src/ts/modules/legal.ts
 *      (el mismo de las páginas legales) y cada acordeón es
 *      template-parts/faq-lista.php (el mismo de Contacto).
 *   3. Contactemos → template-parts/contacto.php
 *
 * Las preguntas y sus categorías salen del módulo "Preguntas frecuentes"
 * (inc/preguntas-frecuentes.php); el copy de la pantalla, de sus campos
 * (inc/pcf-preguntas-frecuentes.php).
 *
 * @package EseLatam
 */
declare(strict_types=1);

get_header();

$ese_id     = (int) get_queried_object_id();
$ese_cmp    = static fn (string $name, $def = '') => ese_latam_campo($name, $ese_id, $def);
$ese_cats   = ese_latam_faq_por_categoria();
$ese_indice = trim((string) $ese_cmp('faqpag_indice'));
?>

<div class="faq-page">
    <?php
    get_template_part('template-parts/sectores-hero', null, [
        'title'     => (string) $ese_cmp('faqpag_hero_titulo'),
        'desc'      => ese_latam_texto_rico((string) $ese_cmp('faqpag_hero_desc')),
        'current'   => get_the_title($ese_id),
    ]);
    ?>

    <?php if ([] !== $ese_cats) : ?>
        <section class="faq" data-legal>
            <aside class="faq__aside">
                <nav class="faq__indice" aria-label="<?php echo esc_attr('' !== $ese_indice ? $ese_indice : __('Categorías', 'ese-latam')); ?>">
                    <?php if ('' !== $ese_indice) : ?>
                        <p class="faq__indice-title"><?php echo esc_html($ese_indice); ?></p>
                    <?php endif; ?>
                    <ul class="faq__indice-list">
                        <?php foreach ($ese_cats as $ese_i => $ese_cat) : ?>
                            <li>
                                <a class="faq__indice-link<?php echo 0 === $ese_i ? ' is-active' : ''; ?>"
                                   href="#<?php echo esc_attr($ese_cat['id']); ?>"
                                   data-legal-link="<?php echo (int) $ese_i; ?>">
                                    <?php echo esc_html($ese_cat['nombre']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            </aside>

            <div class="faq__cuerpo">
                <?php foreach ($ese_cats as $ese_i => $ese_cat) : ?>
                    <section class="faq__bloque" id="<?php echo esc_attr($ese_cat['id']); ?>" data-legal-seccion="<?php echo (int) $ese_i; ?>" aria-labelledby="<?php echo esc_attr($ese_cat['id']); ?>-titulo">
                        <h2 class="faq__titulo" id="<?php echo esc_attr($ese_cat['id']); ?>-titulo" data-reveal="fade">
                            <?php echo esc_html($ese_cat['titulo']); ?>
                        </h2>
                        <?php
                        get_template_part('template-parts/faq-lista', null, [
                            'faqs'  => $ese_cat['preguntas'],
                            'class' => 'faq__lista',
                        ]);
                        ?>
                    </section>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php
    get_template_part('template-parts/contacto', null, [
        'class' => 'contacto--upper',
    ]);
    ?>
</div>

<?php
get_footer();
