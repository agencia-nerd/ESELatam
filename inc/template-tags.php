<?php
/**
 * Componentes reutilizables de marcado (template tags).
 *
 * @package EseLatam
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Logo del sitio: el cargado en Apariencia → Personalizar → Identidad del
 * sitio y, si no hay ninguno, el PNG que trae el theme.
 *
 * Devuelve las medidas reales del archivo (no las del PNG del theme) para
 * que el `width`/`height` del <img> reserve el espacio correcto y el logo no
 * salte al cargar, sea cual sea la proporción que suba el cliente.
 *
 * @return array{url: string, width: int, height: int}
 */
function ese_latam_logo(): array {
    $id = (int) get_theme_mod('custom_logo');

    if ($id > 0) {
        $img = wp_get_attachment_image_src($id, 'full');
        if (is_array($img) && isset($img[0]) && '' !== $img[0]) {
            return [
                'url'    => (string) $img[0],
                'width'  => (int) ($img[1] ?: 142),
                'height' => (int) ($img[2] ?: 43),
            ];
        }
    }

    return [
        'url'    => ESE_LATAM_URI . '/assets/imgs/logo-ese.png',
        'width'  => 142,
        'height' => 43,
    ];
}

/**
 * CTA primario ("píldora" + chip de flecha) reutilizable en varias páginas.
 * El hover (color, escala, morph de las siluetas SVG) lo maneja por completo
 * el CSS/JS existente — `.hero-cta*` en main.css y hero-cta.ts — así que
 * cualquier instancia que imprima este marcado queda animada automáticamente
 * en cuanto `initHeroCta()` corre (busca todos los `.hero-cta` del documento).
 *
 * @param array{
 *     href?: string,
 *     label?: string,
 *     reveal?: bool,
 *     class?: string,
 *     target?: string,  '_blank' si el campo pidió abrir en otra pestaña
 * } $args
 */
function ese_latam_cta_button(array $args = []): void {
    $args = wp_parse_args($args, [
        'href'   => '#',
        'label'  => __('Explorar productos', 'ese-latam'),
        'reveal' => false,
        'class'  => '',
        'target' => '',
    ]);

    $classes = trim('hero-cta ' . $args['class']);
    ?>
    <a href="<?php echo esc_url($args['href']); ?>"
       class="<?php echo esc_attr($classes); ?>"<?php echo ese_latam_target_attr((string) $args['target']); ?>
        <?php echo $args['reveal'] ? 'data-hero-reveal' : ''; ?>>
        <span class="hero-cta__label">
            <?php echo esc_html($args['label']); ?>
            <span class="hero-cta__corner" aria-hidden="true">
                <svg viewBox="0 0 18 48" preserveAspectRatio="none">
                    <path d="M0 0h5.63c7.808 0 13.536 7.337 11.642 14.91l-6.09 24.359A11.527 11.527 0 0 1 0 48V0Z"></path>
                </svg>
            </span>
        </span>
        <span class="hero-cta__arrow" aria-hidden="true">
            <svg viewBox="0 0 73 58" preserveAspectRatio="none">
                <path d="M73 50C73 54.4183 69.4183 58 65 58H11.4975C5.92468 58 2.05929 52.4447 3.99626 47.2194L19.5652 5.21938C20.7282 2.08215 23.7206 0 27.0664 0H65C69.4183 0 73 3.58172 73 8V50Z"></path>
            </svg>
        </span>
    </a>
    <?php
}

/**
 * Íconos de trazo inline (stroke="currentColor", 24×24) compartidos por las
 * fichas de producto (template-parts/producto-hero.php y producto-specs.php).
 * Decorativos y siempre acompañados de texto, así que no ameritan un archivo
 * SVG propio por ícono en assets/icons/ — devuelve solo el/los `<path>`, para
 * envolver en el `<svg>` de cada sitio con su propio tamaño.
 *
 * @return string Markup de `<path>`/`<circle>`/`<rect>`, o cadena vacía si
 *                 `$name` no existe.
 */
function ese_latam_icon_svg(string $name): string {
    $icons = [
        'shield'   => '<path d="M12 2 4 5.5v6c0 5 3.4 8.9 8 10.5 4.6-1.6 8-5.5 8-10.5v-6L12 2Z"/>',
        'wheel'    => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3"/><path d="M12 3v6M12 15v6M3 12h6M15 12h6"/>',
        'check'    => '<path d="M12 2 4 5.5v6c0 5 3.4 8.9 8 10.5 4.6-1.6 8-5.5 8-10.5v-6L12 2Z"/><path d="m9 12 2 2 4-4"/>',
        'leaf'     => '<path d="M20 4c0 9-5.5 14-14 14 0-9 5-14 14-14Z"/><path d="M6 18c3-4 6-6 10-8"/>',
        'volumen'  => '<path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9"/>',
        'peso'     => '<path d="M7 8h10l2 12H5L7 8Z"/><path d="M9.5 8a2.5 2.5 0 1 1 5 0"/>',
        'carga'    => '<path d="M12 3v10M8 9l4 4 4-4"/><path d="M4 17h16v4H4z"/>',
        'material' => '<path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="m3 13 9 5 9-5"/>',
        'ruedas'   => '<circle cx="6" cy="17" r="3"/><circle cx="18" cy="17" r="3"/><path d="M9 17h6M12 17V7M8 7h8"/>',
        'cart'     => '<circle cx="9" cy="20" r="1.6"/><circle cx="18" cy="20" r="1.6"/><path d="M2 3h3l2.5 12h11L21 7H6"/>',
        'download' => '<path d="M12 3v11M8 10l4 4 4-4"/><path d="M4 19h16"/>',
        'mouse'    => '<rect x="8" y="3" width="8" height="14" rx="4"/><path d="M12 6.5v2.5"/>',
        'plus'     => '<path d="M12 6v12M6 12h12"/>',
        'arrow'    => '<path d="M4 12h15M13 6l6 6-6 6"/>',
    ];
    return $icons[$name] ?? '';
}

/**
 * Datos de una card de producto (`.product-card`) a partir de un post del
 * CPT: categoría (taxonomía producto_categoria), litraje por defecto,
 * material (del repeater `caracteristicas`), foto y enlace a la ficha.
 *
 * Una sola extracción para el slider de la home (front-page.php) y para
 * "Soluciones recomendadas" (template-parts/producto-recomendados.php).
 * catalogo-grid.php la hace dentro de su propio loop porque además muestra
 * la norma; si la grilla gana más campos compartidos, conviene traerla acá.
 *
 * @return array{cat: string, name: string, litraje: string, material: string, img: string, href: string}
 */
/**
 * Modelos de un producto, cada uno con sus capacidades, colores y fotos ya
 * resueltos.
 *
 * Un producto del catálogo representa a una FAMILIA —"Papeleras"— y agrupa a
 * los modelos que la componen: Open Dinova, Campus Goool, Venta… Las filas de
 * las pestañas de litraje, color y fotos declaran a qué modelo pertenecen;
 * las que dejan ese campo vacío valen para todos, que es como sigue
 * funcionando un producto de un solo modelo cargado antes de que existiera
 * esta pestaña.
 *
 * Devuelve siempre al menos un modelo: si el producto no declara ninguno, se
 * arma uno sin nombre con todo lo que tenga cargado, y la ficha no dibuja la
 * lista de modelos.
 *
 * @return list<array{nombre: string, descripcion: string, litrajes: list<array{valor: string, predeterminado: bool}>, colores: list<array{nombre: string, color: string, img: string, imgs: array<string, string>}>}>
 */
function ese_latam_producto_modelos(int $post_id): array {
    $nombres   = [];
    $descripcion = [];

    foreach ((array) ese_latam_campo('modelos', $post_id, []) as $modelo) {
        $nombre = trim((string) ($modelo['nombre'] ?? ''));
        if ('' !== $nombre && ! in_array($nombre, $nombres, true)) {
            $nombres[]              = $nombre;
            $descripcion[$nombre]   = trim((string) ($modelo['descripcion'] ?? ''));
        }
    }

    if ([] === $nombres) {
        $nombres           = [''];
        $descripcion[''] = '';
    }

    $destacada = (string) (get_the_post_thumbnail_url($post_id, 'large') ?: '');

    // Fotos agrupadas: modelo => color => litraje => URL.
    $fotos = [];
    foreach ((array) ese_latam_campo('fotos', $post_id, []) as $foto) {
        $img     = ese_latam_img_url($foto['imagen'] ?? '');
        $color   = trim((string) ($foto['color'] ?? ''));
        $litraje = trim((string) ($foto['litraje'] ?? ''));

        if ('' === $img || '' === $color || '' === $litraje) {
            continue;
        }

        $fotos[trim((string) ($foto['modelo'] ?? ''))][$color][$litraje] = $img;
    }

    $salida = [];

    foreach ($nombres as $nombre) {
        $litrajes = [];
        foreach ((array) ese_latam_campo('litrajes', $post_id, []) as $fila) {
            $suyo = trim((string) ($fila['modelo'] ?? ''));
            if ('' !== $suyo && $suyo !== $nombre) {
                continue;
            }
            $valor = trim((string) ($fila['valor'] ?? ''));
            if ('' !== $valor) {
                $litrajes[] = [
                    'valor'          => $valor,
                    'predeterminado' => ! empty($fila['predeterminado']),
                ];
            }
        }

        $colores = [];
        foreach ((array) ese_latam_campo('colores', $post_id, []) as $fila) {
            $suyo = trim((string) ($fila['modelo'] ?? ''));
            if ('' !== $suyo && $suyo !== $nombre) {
                continue;
            }
            $color = trim((string) ($fila['nombre'] ?? ''));
            if ('' === $color) {
                continue;
            }

            // Las fotos propias del modelo pisan a las declaradas sin modelo:
            // el "+" de arrays conserva las claves del operando izquierdo.
            $imgs = ($fotos[$nombre][$color] ?? []) + ($fotos[''][$color] ?? []);

            $colores[] = [
                'nombre' => $color,
                'color'  => (string) ($fila['color'] ?? '#ffffff'),
                'img'    => ese_latam_img_url($fila['imagen'] ?? '') ?: $destacada,
                'imgs'   => $imgs,
            ];
        }

        if ([] === $litrajes && [] === $colores) {
            continue;
        }

        $salida[] = [
            'nombre'      => $nombre,
            'descripcion' => $descripcion[$nombre] ?? '',
            'litrajes'    => $litrajes,
            'colores'     => $colores,
        ];
    }

    return $salida;
}

/**
 * Litraje por defecto de un producto: el marcado "Por defecto" en el primer
 * modelo y, si ninguno lo está, el primero de su lista. Es el que sale en la
 * tarjeta del catálogo y el que trae marcado la ficha al abrir.
 */
function ese_latam_producto_litraje(int $post_id): string {
    $modelos  = ese_latam_producto_modelos($post_id);
    $litrajes = $modelos[0]['litrajes'] ?? [];

    foreach ($litrajes as $item) {
        if ($item['predeterminado']) {
            return $item['valor'];
        }
    }

    return (string) ($litrajes[0]['valor'] ?? '');
}

/**
 * Fotos de la pestaña "3. Fotos" agrupadas por color: color => [litraje => URL].
 *
 * Lo usan la ficha —que necesita el mapa entero para cambiar de foto al
 * elegir color y capacidad— y ese_latam_producto_foto(), que solo quiere una.
 *
 * @return array<string, array<string, string>>
 */
function ese_latam_producto_fotos(int $post_id): array {
    $mapa = [];

    foreach ((array) ese_latam_campo('fotos', $post_id, []) as $foto) {
        $color   = trim((string) ($foto['color'] ?? ''));
        $litraje = trim((string) ($foto['litraje'] ?? ''));
        $img     = ese_latam_img_url($foto['imagen'] ?? '');

        if ('' !== $color && '' !== $litraje && '' !== $img) {
            $mapa[$color][$litraje] = $img;
        }
    }

    return $mapa;
}

/**
 * La foto que representa al producto en una tarjeta.
 *
 * Misma precedencia que el hero de la ficha, para que la tarjeta y la ficha
 * muestren la misma pieza:
 *
 *   1. La foto del primer color en el litraje por defecto (pestaña "3. Fotos").
 *   2. La "foto por defecto" de ese color (pestaña "2. Colores").
 *   3. La imagen destacada del producto.
 *
 * Sin nada de eso devuelve cadena vacía y la tarjeta se pinta sin foto: el
 * theme no inventa una imagen que el producto no tiene.
 */
function ese_latam_producto_foto(int $post_id, string $litraje = ''): string {
    $modelos = ese_latam_producto_modelos($post_id);
    $primero = $modelos[0] ?? null;

    if (null !== $primero && isset($primero['colores'][0])) {
        $color   = $primero['colores'][0];
        $litraje = '' !== $litraje ? $litraje : ese_latam_producto_litraje($post_id);

        if ('' !== $litraje && isset($color['imgs'][$litraje])) {
            return $color['imgs'][$litraje];
        }

        if ('' !== $color['img']) {
            return $color['img'];
        }
    }

    return (string) (get_the_post_thumbnail_url($post_id, 'large') ?: '');
}

function ese_latam_producto_card_data(int $post_id): array {
    $terms = get_the_terms($post_id, 'producto_categoria');
    $cat   = is_array($terms) && ! empty($terms)
        ? $terms[0]->name
        : __('Producto ESE Latam', 'ese-latam');

    $litraje = ese_latam_producto_litraje($post_id);

    $caracteristicas = get_field('caracteristicas', $post_id);
    $material        = '—';
    if (is_array($caracteristicas)) {
        foreach ($caracteristicas as $caract) {
            if (false !== mb_strpos(mb_strtolower((string) ($caract['etiqueta'] ?? '')), 'material')) {
                $material = (string) $caract['valor'];
                break;
            }
        }
    }

    return [
        'cat'      => $cat,
        'name'     => get_the_title($post_id),
        'litraje'  => '' !== $litraje ? $litraje : '—',
        'material' => $material,
        // La foto de esa capacidad, no la destacada: sin foto por color y
        // capacidad las tarjetas salían vacías (ningún producto tiene destacada).
        'img'      => ese_latam_producto_foto($post_id, $litraje),
        'href'     => (string) get_permalink($post_id),
    ];
}

/**
 * Capacidades de un producto, cada una con los colores que puede mostrar.
 *
 * El carrusel de la portada arma un slide POR CAPACIDAD (pestaña
 * "1. Litraje") y deja los colores como swatches dentro de la tarjeta, así
 * que por cada capacidad quedan los colores de "2. Colores" que tengan foto
 * para ESA capacidad en "3. Fotos": son los únicos que la tarjeta puede
 * previsualizar al tocarlos.
 *
 * Un producto sin esas fotos conserva sus capacidades —son datos reales del
 * CPT— con la lista de colores vacía: la tarjeta cae a la foto que ya usaba
 * y no ofrece swatches que no podrían cambiar nada.
 *
 * @return list<array{litraje: string, colores: list<array{nombre: string, swatch: string, img: string}>}>
 */
function ese_latam_producto_capacidades(int $post_id): array {
    $colores  = (array) ese_latam_campo('colores', $post_id, []);
    $litrajes = (array) ese_latam_campo('litrajes', $post_id, []);
    $fotos    = ese_latam_producto_fotos($post_id);

    $capacidades = [];

    foreach ($litrajes as $litraje) {
        $valor = trim((string) ($litraje['valor'] ?? ''));
        if ('' === $valor) {
            continue;
        }

        $disponibles = [];
        foreach ($colores as $color) {
            $nombre = trim((string) ($color['nombre'] ?? ''));
            $img    = $fotos[$nombre][$valor] ?? '';

            if ('' !== $nombre && '' !== $img) {
                $disponibles[] = [
                    'nombre' => $nombre,
                    'swatch' => trim((string) ($color['color'] ?? '')),
                    'img'    => $img,
                ];
            }
        }

        $capacidades[] = ['litraje' => $valor, 'colores' => $disponibles];
    }

    if ([] !== $capacidades) {
        return $capacidades;
    }

    return [['litraje' => ese_latam_producto_litraje($post_id), 'colores' => []]];
}


/**
 * Sectores / áreas de impacto (Figma 3266-2331), leídos del módulo "Sectores"
 * (CPT `sector`, ver inc/cpt-sectores.php). Una sola lista para el slider de
 * la portada, el submenú del nav (inc/setup.php), la grilla de "Soluciones por
 * sector" y las etiquetas de los casos de éxito.
 *
 * El orden es el del campo "Orden" de cada entrada. Sin sectores publicados
 * devuelve una lista vacía y cada plantilla oculta su sección.
 *
 * `img`/`grid_img` son URLs absolutas (o cadena vacía); `grid_desc` cae al
 * resumen cuando el sector no trae una bajada propia para la grilla.
 *
 * @return list<array{id: int, slug: string, title: string, desc: string, img: string, url: string, grid_desc: string, grid_img: string}>
 */
function ese_latam_sectores(): array {
    static $cache = null;
    if (null !== $cache) {
        return $cache;
    }

    $entradas = get_posts([
        'post_type'        => 'sector',
        'post_status'      => 'publish',
        'posts_per_page'   => -1,
        'orderby'          => 'menu_order title',
        'order'            => 'ASC',
        'suppress_filters' => false,
    ]);

    $lista = [];

    foreach ($entradas as $entrada) {
        $foto    = (string) (get_the_post_thumbnail_url($entrada->ID, 'large') ?: '');
        $resumen = trim((string) ese_latam_campo('resumen', $entrada->ID, ''));

        $lista[] = [
            'id'        => $entrada->ID,
            'slug'      => $entrada->post_name,
            'title'     => get_the_title($entrada->ID),
            'desc'      => $resumen,
            'img'       => $foto,
            'url'       => (string) get_permalink($entrada->ID),
            'grid_desc' => trim((string) ese_latam_campo('grilla_desc', $entrada->ID, '')) ?: $resumen,
            'grid_img'  => ese_latam_img_url(ese_latam_campo('grilla_imagen', $entrada->ID, ''), $foto),
        ];
    }

    return $cache = $lista;
}

/**
 * Argumentos de template-parts/casos-reales.php a partir de los campos de una
 * página. Varias pantallas presentan esa misma sección con su propio
 * encabezado, así que en cada una los campos se llaman igual salvo el prefijo.
 *
 * @param string $prefijo Por ejemplo 'impacto_casos' para 'impacto_casos_kicker'.
 * @return array<string, string>
 */
function ese_latam_args_casos(int $post_id, string $prefijo): array {
    $enlace = ese_latam_enlace(ese_latam_campo($prefijo . '_enlace', $post_id, null));

    return [
        'kicker'     => (string) ese_latam_campo($prefijo . '_kicker', $post_id, ''),
        'title'      => (string) ese_latam_campo($prefijo . '_titulo', $post_id, ''),
        // La plantilla escapa la bajada: se le quita el HTML que trae el wysiwyg de Certificaciones.
        'desc'       => trim(wp_strip_all_tags((string) ese_latam_campo($prefijo . '_desc', $post_id, ''))),
        'link_label' => $enlace['label'],
        'link_href'  => $enlace['href'],
    ];
}

/**
 * Traduce las filas de "Dolores"/"Alivios" (ficha del sector) al contrato que
 * espera template-parts/sector-desafios.php. Descarta las filas sin título.
 *
 * @param mixed $filas
 * @return list<array{title: string, sub: string, desc: string, img: string}>
 */
function ese_latam_sector_tarjetas($filas): array {
    $out = [];

    foreach ((array) $filas as $fila) {
        $titulo = trim((string) ($fila['titulo'] ?? ''));
        if ('' === $titulo) {
            continue;
        }

        $out[] = [
            'title' => $titulo,
            'sub'   => (string) ($fila['sub'] ?? ''),
            'desc'  => (string) ($fila['descripcion'] ?? ''),
            'img'   => ese_latam_img_url($fila['imagen'] ?? ''),
        ];
    }

    return $out;
}

/**
 * URL de la página de un sector (`/sectores/{slug}/`). La trae ya resuelta
 * ese_latam_sectores(); si por lo que sea faltara, cae al catálogo, que es
 * donde el sector puede ver productos.
 *
 * @param array{slug?: string} $sector Un ítem de ese_latam_sectores().
 */
function ese_latam_sector_url(array $sector): string {
    $url = isset($sector['url']) && is_string($sector['url']) ? $sector['url'] : '';

    return '' !== $url ? $url : (string) get_post_type_archive_link('producto');
}

/**
 * Chips de sugerencia del buscador global (header.php). Si ya hay líneas de
 * producto (taxonomía producto_categoria con productos), cada chip filtra
 * el catálogo por esa línea (`?linea=`, que catalogo-grid.php entiende);
 * mientras no las haya, cae a búsquedas frecuentes por texto (`?s=`).
 *
 * @return list<array{label: string, url: string}>
 */
function ese_latam_buscador_chips(): array {
    $catalogo = (string) get_post_type_archive_link('producto');

    $terms = get_terms(['taxonomy' => 'producto_categoria', 'hide_empty' => true, 'number' => 6]);
    if (is_array($terms) && ! empty($terms)) {
        return array_map(
            static fn (WP_Term $t): array => ['label' => $t->name, 'url' => add_query_arg('linea', $t->slug, $catalogo)],
            $terms
        );
    }

    return array_map(
        static fn (string $q): array => ['label' => $q, 'url' => add_query_arg('s', $q, $catalogo)],
        [__('Contenedor', 'ese-latam'), __('Papelera', 'ese-latam'), __('Soterrado', 'ese-latam'), '120L', __('HDPE', 'ese-latam')]
    );
}

/**
 * Los tres íconos de las tarjetas HDPE (Figma ArrowClockwise / Leaf /
 * ShieldCheck, 72px). Son dibujo, no contenido: el editor elige cuál usa
 * cada tarjeta desde un desplegable y el trazado vive acá.
 *
 * @return array<string, array{label: string, path: string}>
 */
function ese_latam_iconos_hdpe(): array {
    return [
        'circularidad' => [
            'label' => __('Flecha circular', 'ese-latam'),
            'path'  => '<path d="M51.5 26.2489V32.7492C51.5 33.0366 51.3906 33.3121 51.1959 33.5153C51.0011 33.7185 50.737 33.8326 50.4616 33.8326H44.231C43.9555 33.8326 43.6914 33.7185 43.4967 33.5153C43.3019 33.3121 43.1925 33.0366 43.1925 32.7492C43.1925 32.4619 43.3019 32.1863 43.4967 31.9832C43.6914 31.78 43.9555 31.6658 44.231 31.6658H47.7876L44.3361 28.3669L44.3036 28.3344C42.8603 26.8292 41.0238 25.801 39.0237 25.3784C37.0235 24.9557 34.9483 25.1573 33.0573 25.9579C31.1662 26.7585 29.5433 28.1228 28.3911 29.8801C27.2389 31.6375 26.6086 33.71 26.5789 35.839C26.5492 37.9679 27.1215 40.0587 28.2242 41.8503C29.3268 43.6419 30.9111 45.0548 32.779 45.9125C34.6469 46.7701 36.7156 47.0346 38.7267 46.6728C40.7378 46.3111 42.6022 45.3391 44.0869 43.8783C44.2871 43.6807 44.5543 43.5743 44.8297 43.5823C45.1051 43.5903 45.3663 43.7121 45.5556 43.9209C45.745 44.1298 45.847 44.4086 45.8393 44.6959C45.8317 44.9833 45.7149 45.2557 45.5147 45.4532C43.2061 47.7375 40.1436 49.0074 36.9619 49H36.7906C34.7497 48.9708 32.7469 48.4191 30.9583 47.3933C29.1697 46.3675 27.65 44.8991 26.5327 43.117C25.4155 41.335 24.7349 39.2939 24.5508 37.1732C24.3667 35.0524 24.6847 32.917 25.477 30.9545C26.2692 28.9921 27.5113 27.2627 29.0942 25.9184C30.6771 24.574 32.5522 23.6559 34.5549 23.2447C36.5575 22.8335 38.6263 22.9418 40.5794 23.5601C42.5326 24.1784 44.3102 25.2877 45.7562 26.7906L49.4231 30.2845V26.2489C49.4231 25.9616 49.5325 25.686 49.7273 25.4829C49.922 25.2797 50.1862 25.1655 50.4616 25.1655C50.737 25.1655 51.0011 25.2797 51.1959 25.4829C51.3906 25.686 51.5 25.9616 51.5 26.2489Z"/>',
        ],
        'eco' => [
            'label' => __('Hojas', 'ese-latam'),
            'path'  => '<path d="M50.4536 24.9854C50.4393 24.7407 50.3357 24.5098 50.1624 24.3366C49.9891 24.1633 49.7581 24.0596 49.5135 24.0454C43.0424 23.6703 37.8466 25.6379 35.6137 29.323C34.1385 31.7594 34.141 34.7183 35.5937 37.5409C34.7668 38.5251 34.1625 39.6764 33.8222 40.916L31.7881 38.8747C32.7657 36.8333 32.7282 34.7058 31.6631 32.9382C30.0128 30.2143 26.2034 28.7543 21.4739 29.0318C21.2292 29.0461 20.9983 29.1497 20.825 29.323C20.6517 29.4963 20.548 29.7272 20.5337 29.9718C20.255 34.7008 21.7164 38.5096 24.4406 40.1597C25.3396 40.7089 26.3726 40.9995 27.4261 40.9997C28.4487 40.9871 29.4553 40.7449 30.3716 40.291L33.4971 43.4161V47C33.4971 47.2652 33.6025 47.5196 33.79 47.7071C33.9776 47.8946 34.232 48 34.4973 48C34.7625 48 35.0169 47.8946 35.2045 47.7071C35.3921 47.5196 35.4974 47.2652 35.4974 47V43.3136C35.493 41.7226 36.0344 40.1783 37.0314 38.9384C38.3178 39.6106 39.7445 39.9703 41.1959 39.9885C42.5991 39.993 43.9762 39.6097 45.1753 38.8809C48.8609 36.6508 50.8337 31.4556 50.4536 24.9854ZM25.4721 38.4496C23.5542 37.2883 22.4691 34.5395 22.4953 30.9994C26.0359 30.9694 28.7851 32.0582 29.9465 33.9757C30.5529 34.9758 30.6516 36.1421 30.2541 37.3434L27.2023 34.292C27.0132 34.1124 26.7614 34.0137 26.5006 34.017C26.2398 34.0204 25.9907 34.1254 25.8062 34.3099C25.6218 34.4943 25.5167 34.7434 25.5134 35.0042C25.51 35.265 25.6087 35.5167 25.7884 35.7058L28.8401 38.7572C27.6387 39.1547 26.4735 39.0559 25.4721 38.4496ZM44.1388 37.1721C42.4636 38.1859 40.4945 38.2634 38.4942 37.4221L45.2065 30.7094C45.3862 30.5203 45.4848 30.2685 45.4815 30.0078C45.4782 29.747 45.3731 29.4978 45.1886 29.3134C45.0042 29.129 44.755 29.0239 44.4942 29.0206C44.2334 29.0173 43.9816 29.1159 43.7925 29.2955L37.0789 35.9996C36.2338 33.9995 36.3101 32.0294 37.329 30.3556C39.0718 27.4805 43.2049 25.8779 48.497 26.0017C48.6171 31.2919 47.0168 35.4295 44.1388 37.1721Z"/>',
        ],
        'durabilidad' => [
            'label' => __('Escudo con visto', 'ese-latam'),
            'path'  => '<path d="M47.3333 23H25.6667C25.092 23 24.5409 23.2276 24.1346 23.6326C23.7283 24.0377 23.5 24.5871 23.5 25.16V32.72C23.5 39.8372 26.9558 44.1504 29.8551 46.5156C32.9778 49.0617 36.0843 49.9257 36.2197 49.9621C36.4059 50.0126 36.6022 50.0126 36.7884 49.9621C36.9239 49.9257 40.0263 49.0617 43.153 46.5156C46.0442 44.1504 49.5 39.8372 49.5 32.72V25.16C49.5 24.5871 49.2717 24.0377 48.8654 23.6326C48.4591 23.2276 47.908 23 47.3333 23ZM47.3333 32.72C47.3333 37.7244 45.4835 41.7865 41.8354 44.7916C40.2474 46.0953 38.4413 47.1099 36.5 47.7886C34.5841 47.1217 32.8003 46.1252 31.2296 44.8443C27.5381 41.8338 25.6667 37.7555 25.6667 32.72V25.16H47.3333V32.72ZM30.3169 36.7241C30.1136 36.5214 29.9994 36.2466 29.9994 35.96C29.9994 35.6734 30.1136 35.3985 30.3169 35.1959C30.5202 34.9932 30.7959 34.8794 31.0833 34.8794C31.3708 34.8794 31.6465 34.9932 31.8498 35.1959L34.3333 37.6731L41.1502 30.8759C41.2509 30.7755 41.3704 30.6959 41.5019 30.6416C41.6334 30.5873 41.7743 30.5594 41.9167 30.5594C42.059 30.5594 42.2 30.5873 42.3315 30.6416C42.463 30.6959 42.5825 30.7755 42.6831 30.8759C42.7838 30.9762 42.8636 31.0953 42.9181 31.2264C42.9726 31.3576 43.0006 31.4981 43.0006 31.64C43.0006 31.7819 42.9726 31.9224 42.9181 32.0535C42.8636 32.1846 42.7838 32.3037 42.6831 32.4041L35.0998 39.9641C34.9992 40.0645 34.8797 40.1441 34.7482 40.1985C34.6167 40.2528 34.4757 40.2808 34.3333 40.2808C34.191 40.2808 34.05 40.2528 33.9185 40.1985C33.787 40.1441 33.6675 40.0645 33.5669 39.9641L30.3169 36.7241Z"/>',
        ],
    ];
}

/**
 * Las mismas opciones, listas para el desplegable del campo.
 *
 * @return array<string, string>
 */
function ese_latam_iconos_hdpe_opciones(): array {
    return array_map(static fn (array $i): string => $i['label'], ese_latam_iconos_hdpe());
}

/**
 * El trazado de un ícono HDPE, o cadena vacía si la clave no existe.
 */
function ese_latam_icono_hdpe(string $clave): string {
    $iconos = ese_latam_iconos_hdpe();

    return isset($iconos[$clave]) ? $iconos[$clave]['path'] : '';
}

/**
 * Imagen de fondo del hero de la portada. La usan el
 * <img> de front-page.php y el <link rel="preload"> del <head> (ver
 * inc/enqueue.php): salen del mismo lugar para que el navegador no descargue
 * dos versiones distintas.
 *
 * El campo "Imagen de fondo del hero" (hero_video_poster, devuelve URL) manda;
 * si es un adjunto de la biblioteca se aprovecha el srcset que generó
 * WordPress. Vacío: el fondo del diseño que trae el theme, en tres anchos.
 *
 * @return array{src: string, srcset: string, width: int, height: int}
 */
function ese_latam_hero_fondo(): array {
    static $fondo = null;
    if (null !== $fondo) {
        return $fondo;
    }

    $url = ese_latam_img_url(ese_latam_home('hero_video_poster'));

    if ('' !== $url) {
        $id  = attachment_url_to_postid($url);
        $img = $id > 0 ? wp_get_attachment_image_src($id, 'full') : false;

        $fondo = [
            'src'    => is_array($img) ? (string) $img[0] : $url,
            'srcset' => $id > 0 ? (string) wp_get_attachment_image_srcset($id, 'full') : '',
            'width'  => is_array($img) ? (int) $img[1] : 1920,
            'height' => is_array($img) ? (int) $img[2] : 1080,
        ];
        return $fondo;
    }

    $srcset = [];
    foreach ([960, 1600, 2560] as $ancho) {
        $srcset[] = ese_latam_asset('hero/hero-fondo-' . $ancho . '.webp') . ' ' . $ancho . 'w';
    }

    $fondo = [
        'src'    => ese_latam_asset('hero/hero-fondo-1600.webp'),
        'srcset' => implode(', ', $srcset),
        'width'  => 2560,
        'height' => 1448,
    ];
    return $fondo;
}
