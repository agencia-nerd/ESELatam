<?php
/**
 * Campos de los módulos de contenido (inc/modulos.php) y las coordenadas de
 * la taxonomía "Países".
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

    $grupo = static fn (string $key, string $title, string $tipo, array $fields, string $desc = ''): array => [
        'key'                   => 'group_' . $key,
        'title'                 => $title,
        'location'              => [[['param' => 'post_type', 'operator' => '==', 'value' => $tipo]]],
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,
        'description'           => $desc,
        'fields'                => $fields,
    ];

    /* -----------------------------------------------------------------
     * Certificación
     *
     * Ocho campos: van en tres pestañas según dónde se ven (franja,
     * página de Certificaciones y datos que hoy no se pintan).
     * -------------------------------------------------------------- */
    acf_add_local_field_group($grupo(
        'certificacion',
        __('Ficha del sello', 'ese-latam'),
        'certificacion',
        [
            $tab('certificacion_franja', __('Franja de sellos', 'ese-latam')),
            $campo('true_false', 'destacado', __('¿Mostrar el sello en la franja de sellos?', 'ese-latam'), [
                'instructions'  => __('La franja corta de sellos que sale en la portada, en las páginas de sectores y en la ficha de producto. Si no marcas ninguno, la franja muestra todos. La página de Certificaciones no depende de esta opción.', 'ese-latam'),
                'default_value' => 0,
                'ui'            => 1,
            ]),
            $campo('textarea', 'resumen', __('Resumen del sello en la franja', 'ese-latam'), [
                'instructions' => __('Una línea bajo el nombre del sello en la franja de sellos. Hasta 90 caracteres.', 'ese-latam'),
                'rows'         => 2,
                'required'     => 1,
                'maxlength'    => 90,
            ]),

            $tab('certificacion_ficha', __('Ficha en Certificaciones', 'ese-latam')),
            $campo('text', 'subtitulo', __('Qué certifica el sello', 'ese-latam'), [
                'instructions' => __('Línea bajo el nombre del sello en su ficha de la página de Certificaciones. Hasta 80 caracteres. Vacío: la línea queda en blanco.', 'ese-latam'),
                'placeholder'  => __('Norma alemana de diseño y dimensiones (DIN EN 840)', 'ese-latam'),
            ]),
            $campo('wysiwyg', 'detalle', __('Descripción del sello', 'ese-latam'), [
                'instructions' => __('Texto bajo el rótulo «Descripción» en la ficha del sello de la página de Certificaciones. Un párrafo. Vacía: el rótulo queda sin texto debajo.', 'ese-latam'),
                'tabs'         => 'visual',
                'toolbar'      => 'basic',
                'media_upload' => 0,
            ]),
            $campo('repeater', 'criterios', __('Criterios que verifica el sello', 'ese-latam'), [
                'instructions' => __('Lista con check bajo el rótulo «Criterios» en la ficha del sello de la página de Certificaciones. Entre 3 y 6 criterios. Vacía: el rótulo queda sin lista.', 'ese-latam'),
                'layout'       => 'table',
                'button_label' => __('Añadir criterio', 'ese-latam'),
                'sub_fields'   => [
                    $campo('text', 'texto', __('Criterio verificado', 'ese-latam'), [
                        'key'          => 'field_crit_texto',
                        'instructions' => __('Una frase corta, hasta 80 caracteres. Las filas vacías se ignoran.', 'ese-latam'),
                    ]),
                ],
            ]),
        ],
        __('El título de la entrada es el nombre del sello y la imagen destacada es su logo: PNG o WebP con fondo transparente · 400×400 px (1:1) · máx. 50 KB. En la franja el logo se ve en gris hasta pasar el mouse; sin logo, se muestra solo el nombre. El sello aparece en la franja de sellos (portada, sectores y ficha de producto), en la tarjeta de certificaciones de la ficha de producto y en la página de Certificaciones.', 'ese-latam')
    ));

    /* -----------------------------------------------------------------
     * Distribuidor
     * -------------------------------------------------------------- */
    acf_add_local_field_group($grupo(
        'distribuidor',
        __('Ficha del distribuidor', 'ese-latam'),
        'distribuidor',
        [
            $tab('dst_datos', __('Datos', 'ese-latam')),
            $campo('message', '', __('Dónde se elige el país del distribuidor', 'ese-latam'), [
                'key'      => 'field_dstm_msg',
                'message'  => __('El <strong>país</strong> se elige en la caja de la derecha. Sus coordenadas —las que usa el globo 3D para apuntar— se editan una vez en <strong>Distribuidores → Países</strong>.', 'ese-latam'),
                'esc_html' => 0,
            ]),
            $campo('text', 'ciudad', __('Ciudad del distribuidor', 'ese-latam'), [
                'instructions' => __('Va antes del país en la línea sobre el nombre de la tarjeta: «Lima, Perú». Vacía: solo se muestra el país.', 'ese-latam'),
                'wrapper'      => ['width' => '50'],
            ]),
            $campo('text', 'representante', __('Nombre del representante', 'ese-latam'), [
                'instructions' => __('Fila «Representante» de la tarjeta en la página de Distribuidores. Vacío: la fila no se muestra. Si faltan a la vez representante, teléfono y correo, la tarjeta sale como pendiente y solo muestra la dirección.', 'ese-latam'),
                'wrapper'      => ['width' => '50'],
            ]),
            $campo('textarea', 'direccion', __('Dirección del distribuidor', 'ese-latam'), [
                'instructions' => __('Fila «Ubicación» de la tarjeta y texto bajo el nombre en el globo de la portada. Se usa tal cual, junto al país, para centrar el mapa: escríbela como la buscarías en Google Maps. Vacía: la fila no se muestra y el mapa apunta al país.', 'ese-latam'),
                'rows'         => 2,
            ]),
            $campo('textarea', 'descripcion', __('Descripción breve del distribuidor', 'ese-latam'), [
                'instructions' => __('Frase bajo el nombre de la empresa en la tarjeta, por ejemplo a qué tipo de cliente atiende. Hasta 120 caracteres. Vacía: no se muestra.', 'ese-latam'),
                'rows'         => 2,
            ]),

            $tab('dst_contacto', __('Contacto', 'ese-latam')),
            $campo('text', 'telefono', __('Teléfono del distribuidor', 'ese-latam'), [
                'instructions' => __('Fila «Contacto» de la tarjeta; al tocarlo, llama. Escríbelo con código de país: +51 999 888 777. Vacío: la fila no se muestra.', 'ese-latam'),
                'wrapper'      => ['width' => '50'],
            ]),
            $campo('email', 'email', __('Correo del distribuidor', 'ese-latam'), [
                'instructions' => __('Fila «E-mail» de la tarjeta; al tocarlo, abre el correo. Vacío: la fila no se muestra.', 'ese-latam'),
                'wrapper'      => ['width' => '50'],
            ]),
            $campo('text', 'whatsapp', __('WhatsApp del distribuidor', 'ese-latam'), [
                'instructions' => __('Botón «WhatsApp» al pie de la tarjeta. Solo dígitos, con código de país: 51999888777. Vacío: no se muestra el botón.', 'ese-latam'),
                'wrapper'      => ['width' => '50'],
            ]),
            $campo('url', 'web', __('Sitio web del distribuidor', 'ese-latam'), [
                'instructions' => __('Botón «Visitar web» al pie de la tarjeta; abre en otra pestaña. Escribe la dirección completa, con https://. Vacío: no se muestra el botón.', 'ese-latam'),
                'wrapper'      => ['width' => '50'],
            ]),
        ],
        __('El título de la entrada es el nombre de la empresa y la imagen destacada es su logo: PNG o WebP con fondo transparente · 200×200 px (1:1) · máx. 50 KB; se muestra a 48 px en la tarjeta. Sin logo, la tarjeta muestra la inicial del nombre. Si faltan a la vez representante, teléfono y correo, la tarjeta sale como pendiente: solo muestra la dirección y el mapa apunta al país.', 'ese-latam')
    ));

    /* -----------------------------------------------------------------
     * Aliado
     * -------------------------------------------------------------- */
    acf_add_local_field_group($grupo(
        'aliado',
        __('Ficha del aliado', 'ese-latam'),
        'aliado',
        [
            $campo('url', 'web', __('Sitio web del aliado', 'ese-latam'), [
                'instructions' => __('Al hacer clic en el logo del aliado se abre esta dirección en otra pestaña. Escribe la dirección completa, con https://. Vacío: el logo no enlaza.', 'ese-latam'),
            ]),
        ],
        __('El título de la entrada es el texto alternativo del logo y la imagen destacada es el logo: PNG o WebP con fondo transparente · 300×60 px (5:1) · máx. 50 KB. Se encaja en una caja de 5:1 sin respetar su proporción, así que súbelo en esa medida. Sin logo, el aliado no aparece. Los logos salen en las páginas Nosotros e Impacto.', 'ese-latam')
    ));

    /* -----------------------------------------------------------------
     * Pregunta frecuente
     * -------------------------------------------------------------- */
    acf_add_local_field_group($grupo(
        'faq',
        __('Respuesta', 'ese-latam'),
        'faq',
        [
            $campo('wysiwyg', 'respuesta', __('Respuesta a la pregunta frecuente', 'ese-latam'), [
                'instructions' => __('Texto que se despliega al abrir la pregunta en el acordeón de la página Preguntas frecuentes. Un párrafo corto.', 'ese-latam'),
                'tabs'         => 'visual',
                'toolbar'      => 'basic',
                'media_upload' => 0,
                'required'     => 1,
            ]),
        ],
        __('El título de la entrada es la pregunta. En la página Preguntas frecuentes se agrupan por la categoría que marques en la columna derecha (una sola); sin categoría, no salen ahí. El campo «Orden» define el orden dentro de su categoría.', 'ese-latam')
    ));

    /* -----------------------------------------------------------------
     * Categoría de preguntas frecuentes (taxonomía)
     * -------------------------------------------------------------- */
    acf_add_local_field_group([
        'key'      => 'group_faq_categoria',
        'title'    => __('Bloque en la página Preguntas frecuentes', 'ese-latam'),
        'location' => [[['param' => 'taxonomy', 'operator' => '==', 'value' => 'faq_categoria']]],
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,
        'fields'                => [
            $campo('text', 'titulo_bloque', __('Título del bloque de la categoría', 'ese-latam'), [
                'instructions' => __('Encabeza el grupo de preguntas en la página, por ejemplo «Especificaciones de contenedores». El nombre de la categoría es el texto corto del índice de la izquierda. Hasta 60 caracteres. Vacío: el bloque usa el nombre de la categoría.', 'ese-latam'),
                'maxlength'    => 60,
                'wrapper'      => ['width' => '70'],
            ]),
            $campo('number', 'orden', __('Orden de la categoría en la página', 'ese-latam'), [
                'instructions' => __('Posición del bloque y de su punto en el índice; el 1 va primero. Vacío: va después de las numeradas, por nombre.', 'ese-latam'),
                'min'          => 1,
                'step'         => 1,
                'wrapper'      => ['width' => '30'],
            ]),
        ],
    ]);

    /* -----------------------------------------------------------------
     * Coordenadas del país (taxonomía)
     *
     * El globo 3D gira hasta el centro del país al elegirlo en el riel, así
     * que cada término guarda su latitud y longitud.
     * -------------------------------------------------------------- */
    acf_add_local_field_group([
        'key'      => 'group_pais',
        'title'    => __('Ubicación en el globo', 'ese-latam'),
        'location' => [[['param' => 'taxonomy', 'operator' => '==', 'value' => 'pais']]],
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,
        'fields'                => [
            $campo('number', 'lat', __('Latitud del centro del país', 'ese-latam'), [
                'instructions' => __('Punto al que gira el globo 3D de la portada al elegir el país. Entre -90 y 90; al sur del ecuador es negativa (Perú: -9.19). Cópiala de Google Maps. Vacía: el globo apunta al punto 0,0, frente a África.', 'ese-latam'),
                'min'          => -90,
                'max'          => 90,
                'step'         => 'any',
                'wrapper'      => ['width' => '50'],
            ]),
            $campo('number', 'lng', __('Longitud del centro del país', 'ese-latam'), [
                'instructions' => __('Entre -180 y 180; en América es negativa (Perú: -75.02). Cópiala de Google Maps. Vacía: el globo apunta al punto 0,0, frente a África.', 'ese-latam'),
                'min'          => -180,
                'max'          => 180,
                'step'         => 'any',
                'wrapper'      => ['width' => '50'],
            ]),
        ],
    ]);
});
