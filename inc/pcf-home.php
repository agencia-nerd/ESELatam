<?php
/**
 * Campos de la portada (front-page.php), registrados por PHP igual que los
 * del CPT producto (inc/acf-productos.php): así viven en el theme, versionados
 * con el resto del código, y no dependen de que nadie los exporte a mano.
 *
 * Se cuelgan de la página fijada como portada (Ajustes → Lectura), que crea
 * inc/paginas.php. Las secciones que la home comparte con otras plantillas
 * —certificaciones, distribuidores, residuos y el cierre de contacto— NO
 * están acá: viven en inc/pcf-globales.php, porque su contenido es el mismo
 * en todo el sitio.
 *
 * Los sectores tampoco: son su propio módulo (inc/cpt-sectores.php), porque
 * cada uno tiene además su página. Acá quedan el hero, el marquee y los
 * titulares con los que cada sección se presenta en la portada.
 *
 * @package EseLatam
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Ayuda que acompaña a todos los campos de titular: la convención del
 * resaltado es la misma en toda la web (ver ese_latam_titulo() en inc/pcf.php).
 */
function ese_latam_ayuda_titulo(): string {
    return __('Encierra entre barras el tramo que va en color de marca: <code>sectores que |transformamos|</code>. Cada salto de línea es un renglón del titular.', 'ese-latam');
}

/**
 * Atajos para no repetir el mismo array 60 veces. `$k` arma la key a partir
 * del nombre, que es lo único que las plantillas necesitan conocer.
 *
 * @return array<string, mixed>
 */
function ese_latam_campo_def(string $type, string $name, string $label, array $extra = []): array {
    return array_merge([
        'key'   => 'field_' . $name,
        'name'  => $name,
        'type'  => $type,
        'label' => $label,
    ], $extra);
}

/**
 * Pestaña separadora dentro de un grupo.
 *
 * @return array<string, mixed>
 */
function ese_latam_campo_tab(string $key, string $label): array {
    return [
        'key'       => 'field_tab_' . $key,
        'name'      => '',
        'type'      => 'tab',
        'label'     => $label,
        'placement' => 'top',
    ];
}

add_action('acf/init', static function (): void {
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    $campo = 'ese_latam_campo_def';
    $tab   = 'ese_latam_campo_tab';

    acf_add_local_field_group([
        'key'      => 'group_home',
        'title'    => __('Contenido de la portada', 'ese-latam'),
        'location' => [
            [
                ['param' => 'page_type', 'operator' => '==', 'value' => 'front_page'],
            ],
        ],
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,
        'description'           => __('Todo el contenido de la portada sale de estos campos. Un campo vacío no se muestra: el tema no trae textos ni imágenes de respaldo.', 'ese-latam'),
        'fields'                => [

            // ---------- Hero ----------
            $tab('home_hero', __('Hero', 'ese-latam')),
            $campo('textarea', 'hero_titulo', __('Titular principal del hero', 'ese-latam'), [
                'instructions' => __('Frase grande sobre el video, a la izquierda; cada renglón entra animado con el scroll. Dos o tres renglones cortos.', 'ese-latam') . ' ' . ese_latam_ayuda_titulo(),
                'rows'         => 2,
                'placeholder'  => "Contener\npara |transformar|",
            ]),
            $campo('textarea', 'hero_lede', __('Bajada bajo el titular del hero', 'ese-latam'), [
                'instructions' => __('Párrafo corto abajo a la izquierda, sobre el botón. Una o dos frases, hasta 200 caracteres. Vacía: no se muestra.', 'ese-latam'),
                'rows'        => 3,
                'placeholder' => __('Diseñamos y distribuimos soluciones de contención de residuos…', 'ese-latam'),
            ]),
            $campo('link', 'hero_cta', __('Botón principal del hero (texto y enlace)', 'ese-latam'), [
                'instructions' => __('Botón bajo la bajada. Escribe el texto del botón y la página o sección a la que lleva. Vacío: no se muestra. Si también quedan vacíos el titular, la bajada y la tarjeta flotante, el hero entero no se muestra.', 'ese-latam'),
            ]),

            // ---------- Hero — fondo e isla ----------
            // hero_video_poster conserva su nombre (y lo ya cargado) aunque
            // ahora es el fondo del hero: el video se retiró por rendimiento.
            $tab('home_hero_medios', __('Hero — fondo e isla', 'ese-latam')),
            $campo('image', 'hero_video_poster', __('Imagen de fondo del hero', 'ese-latam'), [
                'instructions'  => __('Paisaje a pantalla completa detrás del titular y de la isla. Es lo primero que carga la portada, así que cuida el peso. WebP o JPG · 2560×1440 px (16:9) · máx. 300 KB. En pantallas angostas se recorta a los costados. Vacía: se usa el cielo con montañas del diseño.', 'ese-latam'),
                'return_format' => 'url',
                'preview_size'  => 'medium',
            ]),
            $campo('image', 'hero_isla', __('Isla flotante sobre el fondo', 'ese-latam'), [
                'instructions'  => __('La ilustración que flota a la derecha del titular (centrada en celulares). PNG o WebP con fondo transparente y recortada al dibujo, sin márgenes vacíos · 1600 px de ancho · máx. 300 KB. WordPress genera solo los tamaños chicos para móvil. Una imagen más cuadrada se muestra más chica para no chocar con el menú. Vacía: se usa la isla con contenedores del diseño.', 'ese-latam'),
                'return_format' => 'id',
                'preview_size'  => 'medium',
            ]),
            $campo('number', 'hero_isla_ancho', __('Isla flotante — ancho máximo en píxeles', 'ese-latam'), [
                'instructions' => __('Tope de ancho de la isla en pantallas grandes, para achicarla si se ve muy grande. Entre 320 y 3000. Su alto se ajusta solo, según la proporción de la imagen. Vacío: 1720 px.', 'ese-latam'),
                'min'          => 320,
                'max'          => 3000,
                'append'       => 'px',
                'wrapper'      => ['width' => '40'],
            ]),

            // ---------- Hero — tarjeta flotante ----------
            $tab('home_hero_tarjeta', __('Hero — tarjeta flotante', 'ese-latam')),
            $campo('text', 'hero_card_stat', __('Tarjeta flotante — cifra destacada', 'ese-latam'), [
                'instructions' => __('Número grande de la tarjeta translúcida, abajo a la derecha del hero (por ejemplo, 100%). Hasta 6 caracteres. Vacía: no se muestra la cifra. Si las tres partes de la tarjeta quedan vacías, la tarjeta no se muestra.', 'ese-latam'),
                'placeholder' => '100%',
                'wrapper'     => ['width' => '30'],
            ]),
            $campo('text', 'hero_card_tag', __('Tarjeta flotante — etiqueta bajo la cifra', 'ese-latam'), [
                'instructions' => __('Línea corta en verde y mayúsculas bajo la cifra. Hasta 30 caracteres. Vacía: no se muestra.', 'ese-latam'),
                'placeholder' => __('HDPE de alta calidad', 'ese-latam'),
                'wrapper'     => ['width' => '70'],
            ]),
            $campo('textarea', 'hero_card_desc', __('Tarjeta flotante — descripción', 'ese-latam'), [
                'instructions' => __('Texto al pie de la tarjeta, bajo una línea divisoria. Una frase, hasta 100 caracteres. Vacía: no se muestra.', 'ese-latam'),
                'rows'        => 2,
                'placeholder' => __('Contenedores de residuos sólidos para municipios y empresas en Latinoamérica', 'ese-latam'),
            ]),

            // ---------- Sectores ----------
            $tab('home_sectores', __('Sectores', 'ese-latam')),
            $campo('message', '', __('Dónde se editan las tarjetas de sectores', 'ese-latam'), [
                'key'      => 'field_msg_sectores',
                'message'  => __('Las tarjetas del slider salen del módulo <strong>Sectores</strong> del menú lateral: ahí se añaden, se ordenan y se edita la página de cada uno. Sin sectores publicados, la sección no se muestra. Aquí solo se define cómo se presenta la sección en la portada.', 'ese-latam'),
                'esc_html' => 0,
            ]),
            $campo('text', 'sectores_kicker', __('Antetítulo de la sección Sectores', 'ese-latam'), [
                'instructions' => __('Frase corta en azul sobre el titular, precedida por una barra (por ejemplo, Áreas de impacto). Hasta 30 caracteres. Vacío: no se muestra.', 'ese-latam'),
                'placeholder' => __('Áreas de impacto', 'ese-latam'),
                'wrapper'     => ['width' => '50'],
            ]),
            $campo('textarea', 'sectores_titulo', __('Titular de la sección Sectores', 'ese-latam'), [
                'instructions' => __('Titular en mayúsculas arriba a la izquierda, sobre el slider de sectores. Vacío: no se muestra.', 'ese-latam') . ' ' . ese_latam_ayuda_titulo(),
                'rows'         => 2,
                'placeholder'  => "sectores que\n|transformamos|",
                'wrapper'      => ['width' => '50'],
            ]),
            $campo('wysiwyg', 'sectores_desc', __('Bajada junto al titular de Sectores', 'ese-latam'), [
                'instructions' => __('Párrafo a la derecha del titular. Dos o tres frases. El tramo en negrita se muestra en color de acento. Vacía: no se muestra.', 'ese-latam'),
                'tabs'         => 'visual',
                'toolbar'      => 'basic',
                'media_upload' => 0,
            ]),
            $campo('textarea', 'sectores_leyenda', __('Texto junto al contador del slider', 'ese-latam'), [
                'instructions' => __('Frase corta a la izquierda del slider, bajo el contador de tarjetas (01/08). Hasta 90 caracteres. Vacía: solo se ve el contador.', 'ese-latam'),
                'rows'        => 2,
                'placeholder' => __('Contenedores adaptados a cada sector…', 'ese-latam'),
            ]),

            // ---------- Marquee ----------
            $tab('home_marquee', __('Marquee', 'ese-latam')),
            $campo('repeater', 'marquee_pistas', __('Pistas del marquee (texto gigante)', 'ese-latam'), [
                'instructions' => __('Las palabras gigantes en gris claro que se desplazan con el scroll, bajo los sectores. Cada fila es un renglón. Una palabra por pista: el recorrido es corto y una frase larga no se alcanza a leer entera. Hasta 4 pistas. Sin pistas, la franja no se muestra.', 'ese-latam'),
                'layout'       => 'table',
                'min'          => 0,
                'max'          => 4,
                'button_label' => __('Añadir pista', 'ese-latam'),
                'sub_fields'   => [
                    $campo('text', 'texto', __('Palabra de la pista', 'ese-latam'), [
                        'key'          => 'field_marquee_texto',
                        'instructions' => __('Una sola palabra, hasta 12 letras. Vacía: la pista no se muestra.', 'ese-latam'),
                    ]),
                    $campo('select', 'direccion', __('Sentido del movimiento de la pista', 'ese-latam'), [
                        'key'     => 'field_marquee_direccion',
                        'instructions' => __('Hacia dónde se mueve al bajar con el scroll. Alterna el sentido entre pistas para que se crucen.', 'ese-latam'),
                        'choices' => [
                            'left'  => __('Hacia la izquierda', 'ese-latam'),
                            'right' => __('Hacia la derecha', 'ese-latam'),
                        ],
                        'default_value' => 'left',
                    ]),
                ],
            ]),

            // ---------- Productos ----------
            $tab('home_productos', __('Productos', 'ese-latam')),
            $campo('text', 'productos_kicker', __('Antetítulo de la sección Productos', 'ese-latam'), [
                'instructions' => __('Frase corta en blanco sobre el titular, precedida por una barra. Hasta 40 caracteres. Vacío: no se muestra.', 'ese-latam'),
                'placeholder' => __('Nuestra gama de productos', 'ese-latam'),
                'wrapper'     => ['width' => '50'],
            ]),
            $campo('textarea', 'productos_titulo', __('Titular de la sección Productos', 'ese-latam'), [
                'instructions' => __('Titular sobre las pastillas de filtro y el carrusel de productos. Vacío: no se muestra.', 'ese-latam') . ' ' . ese_latam_ayuda_titulo(),
                'rows'         => 2,
                'placeholder'  => 'soluciones para |cada necesidad|',
                'wrapper'      => ['width' => '50'],
            ]),
            $campo('wysiwyg', 'productos_desc', __('Bajada bajo el titular de Productos', 'ese-latam'), [
                'instructions' => __('Párrafo corto bajo el titular, sobre las pastillas de filtro. Una o dos frases. Vacía: no se muestra.', 'ese-latam'),
                'tabs'         => 'visual',
                'toolbar'      => 'basic',
                'media_upload' => 0,
            ]),
            $campo('relationship', 'productos_destacados', __('Productos destacados del carrusel', 'ese-latam'), [
                'instructions'  => __('Elige qué productos aparecen en el carrusel y en qué orden. Hasta 12. Vacío: los primeros 12 productos publicados, en el orden del catálogo. Sin productos publicados, la sección no se muestra.', 'ese-latam'),
                'post_type'     => ['producto'],
                'filters'       => ['search'],
                'return_format' => 'id',
                'max'           => 12,
            ]),
            $campo('message', '', __('Cómo se arman las pastillas de filtro', 'ese-latam'), [
                'key'      => 'field_msg_productos_filtros',
                'message'  => __('Se arman solas: una pastilla por cada <strong>producto destacado</strong> de arriba (con uno solo no se muestran). Al elegir una, el carrusel muestra ese producto con una tarjeta por cada capacidad; los colores con foto cargada en su ficha (pestaña “4. Fotos”) se eligen dentro de la tarjeta.', 'ese-latam'),
                'esc_html' => 0,
            ]),
            $campo('link', 'productos_cta', __('Enlace al catálogo completo', 'ese-latam'), [
                'instructions' => __('Enlace bajo el carrusel. Escribe el texto (por ejemplo, Explora todo el catálogo) y la página a la que lleva. Vacío: no se muestra.', 'ese-latam'),
            ]),

            // ---------- Distribuidores ----------
            $tab('home_distribuidores', __('Distribuidores', 'ese-latam')),
            $campo('message', '', __('Dónde se editan los países y distribuidores', 'ese-latam'), [
                'key'      => 'field_msg_distribuidores',
                'message'  => __('Las empresas se editan en el módulo <strong>Distribuidores</strong> del menú lateral, y los países con sus coordenadas en <strong>Distribuidores → Países</strong>. La misma lista alimenta el globo de la portada y la página “Encuentra un distribuidor”. Sin países cargados, la sección no se muestra.', 'ese-latam'),
                'esc_html' => 0,
            ]),
            $campo('text', 'distribuidores_kicker', __('Antetítulo de la sección Distribuidores', 'ese-latam'), [
                'instructions' => __('Frase corta en blanco sobre el titular, encima del globo, precedida por una barra. Hasta 30 caracteres. Vacío: no se muestra.', 'ese-latam'),
                'placeholder' => __('Distribuidores', 'ese-latam'),
                'wrapper'     => ['width' => '50'],
            ]),
            $campo('textarea', 'distribuidores_titulo', __('Titular de la sección Distribuidores', 'ese-latam'), [
                'instructions' => __('Titular grande sobre el globo, arriba a la izquierda. Vacío: no se muestra.', 'ese-latam') . ' ' . ese_latam_ayuda_titulo(),
                'rows'         => 2,
                'placeholder'  => "Presencia |sin|\n|fronteras|",
                'wrapper'      => ['width' => '50'],
            ]),
            $campo('textarea', 'distribuidores_desc', __('Bajada bajo el titular de Distribuidores', 'ese-latam'), [
                'instructions' => __('Párrafo corto bajo el titular, sobre el globo. Una o dos frases, hasta 160 caracteres. Vacía: no se muestra.', 'ese-latam'),
                'rows'        => 2,
                'placeholder' => __('Nuestra red de distribuidores autorizados…', 'ese-latam'),
            ]),
            $campo('link', 'distribuidores_cta', __('Enlace al pie de la tarjeta de país', 'ese-latam'), [
                'instructions' => __('Enlace con flecha al pie de la tarjeta que lista los distribuidores del país elegido. Escribe el texto (por ejemplo, Contactar distribuidor) y la página a la que lleva. Vacío: no se muestra.', 'ese-latam'),
            ]),
        ],
    ]);
});
