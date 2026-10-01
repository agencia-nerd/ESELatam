<?php
/**
 * Campos de la página "Preguntas frecuentes".
 *
 * Solo el copy de la pantalla: el hero y el rótulo del índice. Las preguntas
 * y sus categorías son el módulo "Preguntas frecuentes" (inc/modulos.php) y
 * el cierre "Contactemos" se personaliza en el grupo "Secciones
 * compartidas" de la misma página (inc/pcf-globales.php).
 *
 * Se ubica por plantilla Y por la página con slug `preguntas-frecuentes`:
 * WordPress aplica page-preguntas-frecuentes.php por el slug, sin plantilla
 * asignada, y a la vez la plantilla se puede elegir a mano en otra página.
 *
 * @package EseLatam
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

add_action('acf/init', static function (): void {
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    $campo = 'ese_latam_campo_def';
    $tab   = 'ese_latam_campo_tab';

    $ubicacion = [[['param' => 'page_template', 'operator' => '==', 'value' => 'page-preguntas-frecuentes.php']]];
    $pagina    = get_page_by_path('preguntas-frecuentes');
    if ($pagina instanceof WP_Post) {
        $ubicacion[] = [['param' => 'page', 'operator' => '==', 'value' => (string) $pagina->ID]];
    }

    acf_add_local_field_group([
        'key'      => 'group_pagina_preguntas_frecuentes',
        'title'    => __('Contenido de la página', 'ese-latam'),
        'location' => $ubicacion,
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,
        'description'           => __('Solo lo de esta pantalla. Las preguntas y sus categorías salen del módulo Preguntas frecuentes; el cierre Contactemos se personaliza en «Secciones compartidas», más abajo.', 'ese-latam'),
        'fields'                => [

            /* ---------------- Hero ---------------- */
            $tab('faqpag_hero', __('Hero', 'ese-latam')),
            $campo('textarea', 'faqpag_hero_titulo', __('Titular principal del hero', 'ese-latam'), [
                'instructions' => __('Frase grande en mayúsculas, centrada bajo la miga de pan, sobre fondo blanco.', 'ese-latam')
                    . ' ' . ese_latam_ayuda_titulo()
                    . ' ' . __('Vacío: no se muestra.', 'ese-latam'),
                'rows'         => 2,
                'placeholder'  => "Preguntas\n|frecuentes|",
                'wrapper'      => ['width' => '50'],
            ]),
            $campo('wysiwyg', 'faqpag_hero_desc', __('Bajada bajo el titular del hero', 'ese-latam'), [
                'instructions' => __('Párrafo corto centrado bajo el titular, en gris. Una o dos frases, hasta 160 caracteres. Vacía: no se muestra.', 'ese-latam'),
                'tabs'         => 'visual',
                'toolbar'      => 'basic',
                'media_upload' => 0,
                'wrapper'      => ['width' => '50'],
            ]),

            /* ---------------- Preguntas ---------------- */
            $tab('faqpag_preguntas', __('Preguntas', 'ese-latam')),
            $campo('message', '', __('Dónde se editan las preguntas y las categorías', 'ese-latam'), [
                'key'      => 'field_faqpag_msg',
                'message'  => __('Las preguntas se editan en el módulo <strong>Preguntas frecuentes</strong> del menú lateral, y las categorías en <strong>Preguntas frecuentes → Categorías</strong>. Cada categoría es un punto del índice de la izquierda y un bloque de preguntas; el orden de los bloques es el campo «Orden» de cada categoría y, dentro de cada bloque, el campo «Orden» de cada pregunta. La primera pregunta de cada bloque sale abierta. Sin preguntas con categoría, la sección no se muestra.', 'ese-latam'),
                'esc_html' => 0,
            ]),
            $campo('text', 'faqpag_indice', __('Rótulo del índice de categorías', 'ese-latam'), [
                'instructions' => __('Texto corto en mayúsculas, en celeste, sobre la lista de categorías de la izquierda, que acompaña al bajar. Hasta 30 caracteres. Vacío: la lista se muestra sin rótulo.', 'ese-latam'),
                'placeholder'  => __('Categorías', 'ese-latam'),
                'maxlength'    => 30,
                'wrapper'      => ['width' => '50'],
            ]),

            /* ---------------- Contactemos ---------------- */
            $tab('faqpag_contacto', __('Contactemos', 'ese-latam')),
            $campo('message', '', __('Dónde se edita el cierre «Contactemos»', 'ese-latam'), [
                'key'      => 'field_faqpag_contacto_msg',
                'message'  => __('El banner con foto y el botón Contactar del pie de la página es el bloque compartido <strong>Contactemos</strong> (menú ESE Latam). Para cambiarlo solo en esta página, enciende «¿Personalizar “Contactemos” en esta página?» en el grupo <strong>Secciones compartidas</strong>, más abajo: ahí se editan el antetítulo, el titular, el texto, la foto de fondo y el botón (texto y enlace, por ejemplo /contacto/).', 'ese-latam'),
                'esc_html' => 0,
            ]),
        ],
    ]);
});
