<?php
/**
 * Módulos de contenido: certificaciones, distribuidores, aliados y preguntas
 * frecuentes.
 *
 * Los cuatro eran listas escritas a mano —un repetidor global, una taxonomía
 * de producto y arreglos dentro de su template-part—, así que el cliente no
 * podía añadir ni reordenar nada sin tocar código. Como entradas, cada
 * elemento tiene su ficha, su orden y, donde hace falta, sus relaciones con
 * otros módulos (un producto apunta a sus sellos, un distribuidor a su país).
 *
 * Ninguno tiene página propia: son datos que se muestran dentro de otras
 * pantallas, así que van con `public => false` y `show_ui => true`. Eso evita
 * URLs vacías y deja el admin limpio.
 *
 * Los campos de cada uno viven en inc/pcf-modulos.php.
 *
 * @package EseLatam
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Etiquetas del admin para un CPT, a partir del singular y el plural.
 * Evita repetir el mismo bloque de quince cadenas en cada módulo.
 *
 * @param string $genero 'f' para femenino ("Añadir nueva certificación").
 * @return array<string, string>
 */
function ese_latam_labels(string $singular, string $plural, string $genero = 'm'): array {
    $un   = 'f' === $genero ? 'una' : 'un';
    $nuevo = 'f' === $genero ? 'nueva' : 'nuevo';

    return [
        'name'               => $plural,
        'singular_name'      => $singular,
        'add_new'            => sprintf(__('Añadir %s', 'ese-latam'), $singular),
        'add_new_item'       => sprintf(__('Añadir %1$s %2$s', 'ese-latam'), $nuevo, $singular),
        'edit_item'          => sprintf(__('Editar %s', 'ese-latam'), $singular),
        'new_item'           => sprintf(__('%1$s %2$s', 'ese-latam'), ucfirst($nuevo), $singular),
        'view_item'          => sprintf(__('Ver %s', 'ese-latam'), $singular),
        'search_items'       => sprintf(__('Buscar %s', 'ese-latam'), $plural),
        'not_found'          => sprintf(__('No se encontró %1$s %2$s', 'ese-latam'), $un, $singular),
        'not_found_in_trash' => sprintf(__('No hay %s en la papelera', 'ese-latam'), $plural),
        'all_items'          => $plural,
        'menu_name'          => $plural,
    ];
}

add_action('init', static function (): void {

    /* -----------------------------------------------------------------
     * Certificaciones
     *
     * Reemplazan al repetidor global y a la taxonomía `producto_certificacion`:
     * ahora un producto apunta a sus sellos con un campo de relación, así que
     * el sello se edita una vez y se ve igual en la franja de la home, en el
     * hero del producto y en la página de Certificaciones.
     * -------------------------------------------------------------- */
    register_post_type('certificacion', [
        'labels'        => ese_latam_labels(__('certificación', 'ese-latam'), __('Certificaciones', 'ese-latam'), 'f') + [
            'featured_image'     => __('Logo del sello', 'ese-latam'),
            'set_featured_image' => __('Elegir logo', 'ese-latam'),
        ],
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => true,
        'menu_icon'     => 'dashicons-awards',
        'menu_position' => 7,
        'supports'      => ['title', 'thumbnail', 'page-attributes'],
        'show_in_rest'  => true,
    ]);

    /* -----------------------------------------------------------------
     * Distribuidores
     *
     * Una entrada por empresa. El país es una taxonomía porque además de
     * agrupar guarda las coordenadas que el globo 3D necesita para apuntar.
     * -------------------------------------------------------------- */
    register_post_type('distribuidor', [
        'labels'        => ese_latam_labels(__('distribuidor', 'ese-latam'), __('Distribuidores', 'ese-latam')) + [
            'featured_image'     => __('Logo de la empresa', 'ese-latam'),
            'set_featured_image' => __('Elegir logo', 'ese-latam'),
        ],
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => true,
        'menu_icon'     => 'dashicons-location-alt',
        'menu_position' => 8,
        'supports'      => ['title', 'thumbnail', 'page-attributes'],
        'show_in_rest'  => true,
    ]);

    register_taxonomy('pais', 'distribuidor', [
        'labels' => [
            'name'          => __('Países', 'ese-latam'),
            'singular_name' => __('País', 'ese-latam'),
            'menu_name'     => __('Países', 'ese-latam'),
            'add_new_item'  => __('Añadir país', 'ese-latam'),
            'edit_item'     => __('Editar país', 'ese-latam'),
        ],
        'hierarchical'      => true,
        'public'            => false,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
    ]);

    /* -----------------------------------------------------------------
     * Aliados
     * -------------------------------------------------------------- */
    register_post_type('aliado', [
        'labels'        => ese_latam_labels(__('aliado', 'ese-latam'), __('Aliados', 'ese-latam')) + [
            'featured_image'     => __('Logo del aliado', 'ese-latam'),
            'set_featured_image' => __('Elegir logo', 'ese-latam'),
        ],
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => true,
        'menu_icon'     => 'dashicons-groups',
        'menu_position' => 9,
        'supports'      => ['title', 'thumbnail', 'page-attributes'],
        'show_in_rest'  => true,
    ]);

    /* -----------------------------------------------------------------
     * Preguntas frecuentes
     *
     * El título de la entrada ES la pregunta; la respuesta va en un campo.
     * -------------------------------------------------------------- */
    register_post_type('faq', [
        'labels'        => ese_latam_labels(__('pregunta', 'ese-latam'), __('Preguntas frecuentes', 'ese-latam'), 'f'),
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => true,
        'menu_icon'     => 'dashicons-editor-help',
        'menu_position' => 10,
        'supports'      => ['title', 'page-attributes'],
        'show_in_rest'  => true,
    ]);

    /* -----------------------------------------------------------------
     * Categorías de preguntas frecuentes
     *
     * Agrupan las preguntas en la página "Preguntas frecuentes": cada
     * categoría es un punto del índice lateral y un bloque del acordeón. El
     * nombre del término es el texto corto del índice; el título largo del
     * bloque y el orden van en sus campos (inc/pcf-modulos.php).
     * -------------------------------------------------------------- */
    register_taxonomy('faq_categoria', 'faq', [
        'labels' => [
            'name'          => __('Categorías', 'ese-latam'),
            'singular_name' => __('Categoría', 'ese-latam'),
            'menu_name'     => __('Categorías', 'ese-latam'),
            'all_items'     => __('Todas las categorías', 'ese-latam'),
            'add_new_item'  => __('Añadir categoría', 'ese-latam'),
            'edit_item'     => __('Editar categoría', 'ese-latam'),
            'search_items'  => __('Buscar categorías', 'ese-latam'),
            'not_found'     => __('No hay categorías', 'ese-latam'),
        ],
        // Jerárquica solo por la interfaz: en el editor se marca con casillas
        // en vez de escribir etiquetas a mano.
        'hierarchical'      => true,
        'public'            => false,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
    ]);
});

/**
 * Los cuatro módulos se listan por el campo "Orden" del editor, que es el
 * orden en que salen en el frontend.
 */
add_action('pre_get_posts', static function (WP_Query $q): void {
    if (! is_admin() || ! $q->is_main_query()) {
        return;
    }

    $modulos = ['certificacion', 'distribuidor', 'aliado', 'faq'];
    if (in_array((string) $q->get('post_type'), $modulos, true) && '' === $q->get('orderby')) {
        $q->set('orderby', 'menu_order title');
        $q->set('order', 'ASC');
    }
});

/**
 * Entradas publicadas de un módulo, en el orden del admin.
 *
 * @return list<WP_Post>
 */
function ese_latam_modulo_entradas(string $tipo, int $limite = -1): array {
    return get_posts([
        'post_type'        => $tipo,
        'post_status'      => 'publish',
        'posts_per_page'   => $limite,
        'orderby'          => 'menu_order title',
        'order'            => 'ASC',
        'suppress_filters' => false,
    ]);
}
