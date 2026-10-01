<?php
/**
 * Páginas que el theme necesita que existan (Nosotros, Contacto, Sectores,
 * single de sector, Certificaciones, Impacto…): se crean una sola vez y sus URLs se
 * resuelven desde un único helper.
 *
 * Antes cada plantilla nueva repetía este mismo bloque (ver el historial
 * de inc/contacto.php); acá vive una sola vez para todas.
 *
 * @package EseLatam
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Slug => datos de cada página que el theme trae "de fábrica".
 *
 * `template` vacío significa que WordPress aplica page-{slug}.php solo, por
 * convención. Las páginas de cada sector ya no están acá: son entradas del
 * módulo "Sectores" (inc/cpt-sectores.php) y se sirven con single-sector.php.
 *
 * @return array<string, array{title: string, template: string}>
 */
function ese_latam_paginas_base(): array {
    return [
        // La portada necesita existir como página para poder colgarle los
        // campos de contenido (inc/pcf-home.php): sin ella, front-page.php
        // renderiza el índice del blog y no hay nada que editar en el admin.
        // ese_latam_asegurar_portada() la fija en Ajustes → Lectura.
        'inicio'          => ['title' => __('Inicio', 'ese-latam'),                  'template' => ''],
        'nosotros'        => ['title' => __('Nosotros', 'ese-latam'),                'template' => ''],
        'contacto'        => ['title' => __('Contacto', 'ese-latam'),                'template' => ''],
        'sectores'        => ['title' => __('Soluciones por sector', 'ese-latam'),   'template' => ''],
        'certificaciones' => ['title' => __('Certificaciones', 'ese-latam'),         'template' => ''],
        'impacto'         => ['title' => __('Residuos inteligentes', 'ese-latam'),   'template' => ''],
        'distribuidores'  => ['title' => __('Encuentra un distribuidor', 'ese-latam'), 'template' => ''],
        'preguntas-frecuentes' => ['title' => __('Preguntas frecuentes', 'ese-latam'), 'template' => ''],
        // El blog no tiene page-blog.php: es la página de entradas
        // (Ajustes → Lectura), así que WordPress la pinta con home.php.
        // ese_latam_asegurar_blog() la fija. Existe como página para poder
        // colgarle los campos de inc/pcf-blog.php.
        'blog'            => ['title' => __('Blog', 'ese-latam'),                    'template' => ''],
        // Las dos legales comparten plantilla y grupo de campos: la regla de
        // ubicación es la plantilla, no el ID, así que cualquier página legal
        // que se cree después hereda los mismos campos (ver inc/pcf-legal.php).
        'terminos-y-condiciones'  => ['title' => __('Términos y condiciones', 'ese-latam'),  'template' => 'page-legal.php'],
        'politicas-de-privacidad' => ['title' => __('Políticas de privacidad', 'ese-latam'), 'template' => 'page-legal.php'],
    ];
}

/**
 * URL de una página del theme por slug. Si todavía no existe (instalación
 * nueva antes de que corra `ese_latam_asegurar_paginas`), devuelve el
 * fallback — normalmente el ancla de la sección equivalente en la home.
 */
function ese_latam_pagina_url(string $slug, string $fallback = ''): string {
    $page = get_page_by_path($slug);

    if ($page instanceof WP_Post && 'publish' === $page->post_status) {
        return (string) get_permalink($page);
    }

    return '' !== $fallback ? $fallback : home_url('/');
}

/**
 * Crea las páginas base que falten, una sola vez. Idempotente: las que ya
 * existen —aunque las hayan renombrado o mandado a la papelera— no se
 * vuelven a crear, y todo queda anotado en una opción autoload para que el
 * resto de las cargas sea una lectura en memoria y nada más.
 *
 * Va en `init` y no solo en `after_switch_theme`: el theme ya está activo
 * en los entornos donde se desarrolla, así que engancharlo solo a la
 * activación no crearía nunca las páginas nuevas.
 */
function ese_latam_asegurar_paginas(): void {
    $hechas = get_option('ese_latam_paginas', []);
    if (! is_array($hechas)) {
        $hechas = [];
    }

    $pendientes = array_diff_key(ese_latam_paginas_base(), $hechas);
    if (empty($pendientes)) {
        return;
    }

    foreach ($pendientes as $slug => $datos) {
        $existente = get_page_by_path($slug);
        if ($existente instanceof WP_Post) {
            $hechas[$slug] = $existente->ID;
            continue;
        }

        $post = [
            'post_title'   => $datos['title'],
            'post_name'    => $slug,
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => '',
        ];
        if ('' !== $datos['template']) {
            $post['meta_input'] = ['_wp_page_template' => $datos['template']];
        }

        $id = wp_insert_post($post);

        if (! is_wp_error($id)) {
            $hechas[$slug] = $id;
        }
    }

    update_option('ese_latam_paginas', $hechas);
}
add_action('init', 'ese_latam_asegurar_paginas');
add_action('after_switch_theme', 'ese_latam_asegurar_paginas');

/**
 * Fija la página "Inicio" como portada en Ajustes → Lectura.
 *
 * front-page.php ya se usaba con "Tus últimas entradas", pero entonces la
 * home no era ninguna página y no había dónde guardar su contenido. Con la
 * portada estática, el cliente la edita como cualquier otra página y los
 * campos de inc/pcf-home.php aparecen ahí.
 *
 * Se hace UNA sola vez y nunca se revierte: si alguien elige otra portada (o
 * vuelve a las entradas), la marca ya está puesta y esto no vuelve a tocar
 * los ajustes. El índice de entradas se mueve a la página "Blog"
 * (ese_latam_asegurar_blog()).
 */
function ese_latam_asegurar_portada(): void {
    if (get_option('ese_latam_portada_fijada')) {
        return;
    }

    $inicio = get_page_by_path('inicio');
    if (! $inicio instanceof WP_Post || 'publish' !== $inicio->post_status) {
        return;
    }

    if ('page' !== get_option('show_on_front')) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $inicio->ID);
    }

    update_option('ese_latam_portada_fijada', 1);
}
add_action('init', 'ese_latam_asegurar_portada', 11);
add_action('after_switch_theme', 'ese_latam_asegurar_portada');

/**
 * El header del sitio está pensado para heros OSCUROS: el logo va en blanco
 * (filtro brightness/invert en main.css) y el nav-pill sin borde. Las
 * páginas que abren con fondo claro (Contacto, Sectores, Preguntas frecuentes) necesitan la
 * variante inversa — esta clase en el <body> y el CSS hace el resto.
 *
 * @param list<string> $classes
 * @return list<string>
 */
function ese_latam_body_class_hero_claro(array $classes): array {
    if (is_page(['contacto', 'sectores', 'preguntas-frecuentes'])
        || is_page_template(['page-contacto.php', 'page-sectores.php', 'page-preguntas-frecuentes.php'])
    ) {
        $classes[] = 'has-light-hero';
    }

    return $classes;
}
add_filter('body_class', 'ese_latam_body_class_hero_claro');

/**
 * Fija la página "Blog" como página de entradas en Ajustes → Lectura.
 *
 * Con la portada estática, el índice de entradas necesita una página propia:
 * sin ella WordPress no lo pinta en ningún lado y el enlace "Blog" del footer
 * no tiene destino. Al fijarla, /blog/ lo pinta home.php (Figma 3848-9017) y
 * los campos de inc/pcf-blog.php se editan en esa misma página.
 *
 * Misma política que la portada: se hace UNA vez y no se revierte, para no
 * pisar la decisión del cliente si más adelante mueve el índice.
 */
function ese_latam_asegurar_blog(): void {
    if (get_option('ese_latam_blog_fijado')) {
        return;
    }

    $blog = get_page_by_path('blog');
    if (! $blog instanceof WP_Post || 'publish' !== $blog->post_status) {
        return;
    }

    if ((int) get_option('page_for_posts') <= 0) {
        update_option('page_for_posts', $blog->ID);
    }

    update_option('ese_latam_blog_fijado', 1);
}
add_action('init', 'ese_latam_asegurar_blog', 12);
