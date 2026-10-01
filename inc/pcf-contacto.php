<?php
/**
 * Campos de la página "Contacto" (Figma 3941-8369) y de los datos de
 * contacto del sitio.
 *
 * Acá va solo el COPY de la pantalla: titulares, rótulos del formulario y
 * textos de estado.
 *
 * Los DATOS —teléfono, correo, dirección, horario y redes— no son de esta
 * página sino de la empresa, así que viven en Apariencia → Personalizar →
 * ESE Latam (inc/customizer.php) y los lee ese_latam_contacto_datos().
 *
 * Las opciones de los tres desplegables no son campos: país sale de la
 * taxonomía "Países" de Distribuidores, sector del módulo "Sectores" y
 * producto del catálogo. Escribirlos otra vez acá sería tener la misma
 * lista en dos lados.
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

    $pagina = get_page_by_path('contacto');
    if (! $pagina instanceof WP_Post) {
        return;
    }

    // Rótulo + texto guía de un campo del formulario. El `name` lo fija
    // inc/contacto.php (es lo que valida y manda el correo), así que acá solo
    // se escribe lo que se ve. $tipo: 'casilla', 'desplegable' o 'area'.
    $par = static function (string $name, string $label, string $ph = '', string $tipo = 'casilla') use ($campo): array {
        $es_lista = 'desplegable' === $tipo;

        $etiqueta_rotulo = $es_lista
            /* translators: %s: nombre del campo del formulario. */
            ? __('Desplegable “%s” — rótulo', 'ese-latam')
            /* translators: %s: nombre del campo del formulario. */
            : __('Casilla “%s” — rótulo', 'ese-latam');

        $etiqueta_ph = $es_lista
            /* translators: %s: nombre del campo del formulario. */
            ? __('Desplegable “%s” — primera opción', 'ese-latam')
            /* translators: %s: nombre del campo del formulario. */
            : __('Casilla “%s” — texto guía', 'ese-latam');

        $ayuda_rotulo = $es_lista
            ? __('Texto en negrita encima del desplegable. Si es obligatorio, el asterisco lo pone la web. Hasta 30 caracteres. Vacío: el desplegable queda sin rótulo, pero sigue en el formulario.', 'ese-latam')
            : __('Texto en negrita encima de la casilla. Si es obligatoria, el asterisco lo pone la web. Hasta 30 caracteres. Vacío: la casilla queda sin rótulo, pero sigue en el formulario.', 'ese-latam');

        $ayuda_ph = [
            'casilla'     => __('Texto gris de ejemplo dentro de la casilla; se borra al escribir. Hasta 50 caracteres. Vacío: la casilla se ve en blanco.', 'ese-latam'),
            'desplegable' => __('Opción que se ve antes de elegir, al tope de la lista. Hasta 40 caracteres. Vacía: el desplegable arranca con una opción en blanco.', 'ese-latam'),
            'area'        => __('Texto gris de ejemplo dentro del cuadro grande; se borra al escribir. Hasta 100 caracteres. Vacío: el cuadro se ve en blanco.', 'ese-latam'),
        ][$tipo] ?? '';

        return [
            $campo('text', 'ctc_f_' . $name . '_label', sprintf($etiqueta_rotulo, $label), [
                'instructions' => $ayuda_rotulo,
                'placeholder'  => $label,
                'wrapper'      => ['width' => '50'],
            ]),
            $campo('text', 'ctc_f_' . $name . '_ph', sprintf($etiqueta_ph, $label), [
                'instructions' => $ayuda_ph,
                'placeholder'  => $ph,
                'wrapper'      => ['width' => '50'],
            ]),
        ];
    };

    acf_add_local_field_group([
        'key'      => 'group_pagina_contacto',
        'title'    => __('Contenido de la página', 'ese-latam'),
        'location' => [[['param' => 'page', 'operator' => '==', 'value' => (string) $pagina->ID]]],
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,
        'fields'                => array_merge(

            /* ---------------- Encabezado ---------------- */
            [$tab('ctc_encabezado', __('Encabezado', 'ese-latam'))],
            [
                $campo('text', 'ctc_kicker', __('Antetítulo sobre el titular de contacto', 'ese-latam'), [
                    'instructions' => __('Palabra o frase corta en mayúsculas, con una barra delante, encima del titular. Hasta 30 caracteres. Vacío: no se muestra.', 'ese-latam'),
                    'placeholder'  => __('Contacto', 'ese-latam'),
                    'wrapper'      => ['width' => '40'],
                ]),
                $campo('textarea', 'ctc_titulo', __('Titular principal de la página de contacto', 'ese-latam'), [
                    'instructions' => __('Frase grande al inicio de la página, sobre el formulario.', 'ese-latam') . ' ' . ese_latam_ayuda_titulo(),
                    'rows'         => 2,
                    'placeholder'  => "Cuéntanos tu\n|operación|",
                    'wrapper'      => ['width' => '60'],
                ]),
                $campo('wysiwyg', 'ctc_lede', __('Bajada bajo el titular de contacto', 'ese-latam'), [
                    'instructions' => __('Párrafo corto bajo el titular, una o dos frases. La <strong>negrita</strong> se ve en negrita, sin cambio de color. Vacía: no se muestra.', 'ese-latam'),
                    'tabs'         => 'visual',
                    'toolbar'      => 'basic',
                    'media_upload' => 0,
                ]),
            ],

            /* ---------------- Formulario ---------------- */
            [$tab('ctc_form', __('Formulario', 'ese-latam'))],
            [
                $campo('message', '', __('De dónde salen las opciones de los desplegables', 'ese-latam'), [
                    'key'      => 'field_ctcform_msg',
                    'message'  => __('Las opciones de <strong>País</strong> son los países de Distribuidores, las de <strong>Sector</strong> el módulo Sectores y las de <strong>Producto</strong> el catálogo. Acá solo se escriben los rótulos. Un rótulo vacío deja el campo sin etiqueta, no lo quita: los campos los fija el formulario.', 'ese-latam'),
                    'esc_html' => 0,
                ]),
            ],
            $par('nombre', __('Nombre y apellido', 'ese-latam'), __('Escribe tu nombre y apellido...', 'ese-latam')),
            $par('email', __('Correo electrónico', 'ese-latam'), 'ejemplo@entidad.com'),

            /* ---------------- Formulario — teléfono y país ---------------- */
            [$tab('ctc_form_telefono', __('Formulario — teléfono y país', 'ese-latam'))],
            $par('telefono', __('Teléfono', 'ese-latam'), '+51 999 999 999'),
            $par('pais', __('País', 'ese-latam'), __('Selecciona tu país...', 'ese-latam'), 'desplegable'),

            /* ---------------- Formulario — sector y producto ---------------- */
            [$tab('ctc_form_sector', __('Formulario — sector y producto', 'ese-latam'))],
            $par('sector', __('Sector', 'ese-latam'), __('Selecciona tu sector...', 'ese-latam'), 'desplegable'),
            $par('producto', __('Producto de interés', 'ese-latam'), __('Selecciona un producto...', 'ese-latam'), 'desplegable'),

            /* ---------------- Formulario — mensaje ---------------- */
            [$tab('ctc_form_mensaje', __('Formulario — mensaje y opción “Otro”', 'ese-latam'))],
            $par('mensaje', __('Mensaje adicional', 'ese-latam'), __('Cuéntanos volúmenes, plazos y el detalle de tu operación...', 'ese-latam'), 'area'),
            [
                $campo('text', 'ctc_f_otro', __('Última opción de los tres desplegables', 'ese-latam'), [
                    'instructions' => __('Se añade al final de País, Sector y Producto, para quien no se ve en la lista. Hasta 20 caracteres. Vacía: no se añade.', 'ese-latam'),
                    'placeholder'  => __('Otro', 'ese-latam'),
                    'wrapper'      => ['width' => '34'],
                ]),
            ],

            /* ---------------- Formulario — envío ---------------- */
            [$tab('ctc_form_envio', __('Formulario — botón y avisos', 'ese-latam'))],
            [
                $campo('text', 'ctc_boton', __('Texto del botón de envío', 'ese-latam'), [
                    'instructions' => __('Botón al pie del formulario. Hasta 25 caracteres. Vacío: el botón queda sin texto, así que no lo dejes vacío.', 'ese-latam'),
                    'placeholder'  => __('Solicitar asesoría', 'ese-latam'),
                    'wrapper'      => ['width' => '33'],
                ]),
                $campo('text', 'ctc_nota', __('Nota de campos obligatorios', 'ese-latam'), [
                    'instructions' => __('Línea junto al botón de envío. El primer * que escribas se pinta como el asterisco de color de los campos. Hasta 60 caracteres. Vacía: no se muestra.', 'ese-latam'),
                    'placeholder'  => __('Los campos con * son obligatorios.', 'ese-latam'),
                    'wrapper'      => ['width' => '33'],
                ]),
                $campo('textarea', 'ctc_msg_ok', __('Aviso de envío correcto', 'ese-latam'), [
                    'instructions' => __('Aparece bajo el botón solo si el navegador envía el formulario sin JavaScript; en el envío habitual la web muestra su propio aviso de confirmación. Una o dos frases. Vacío: la línea de aviso queda en blanco.', 'ese-latam'),
                    'rows'         => 2,
                    'placeholder'  => __('¡Gracias! Recibimos tu mensaje y te responderemos en menos de 24 horas hábiles.', 'ese-latam'),
                    'wrapper'      => ['width' => '50'],
                ]),
                $campo('textarea', 'ctc_msg_error', __('Aviso de error al enviar', 'ese-latam'), [
                    'instructions' => __('Aparece bajo el botón solo si el navegador envía el formulario sin JavaScript y el envío falla; en el envío habitual la web muestra su propio aviso de error. Una o dos frases. Vacío: la línea de aviso queda en blanco.', 'ese-latam'),
                    'rows'         => 2,
                    'placeholder'  => __('No pudimos enviar tu mensaje. Revisa los campos e inténtalo de nuevo.', 'ese-latam'),
                    'wrapper'      => ['width' => '50'],
                ]),
            ],

            /* ---------------- Panel lateral ---------------- */
            [$tab('ctc_aside', __('Panel lateral', 'ese-latam'))],
            [
                $campo('message', '', __('Dónde se editan los datos de contacto', 'ese-latam'), [
                    'key'      => 'field_ctcaside_msg',
                    'message'  => __('El teléfono, el correo, la dirección y el horario salen de <strong>Apariencia → Personalizar → ESE Latam</strong>. Acá solo el rótulo de cada uno.', 'ese-latam'),
                    'esc_html' => 0,
                ]),
                $campo('image', 'ctc_aside_imagen', __('Foto de la tarjeta del panel lateral', 'ese-latam'), [
                    'instructions'  => __('Tarjeta con foto a la derecha del formulario, encima de los datos de contacto. WebP o JPG · 1140×600 px (aprox. 19:10) · máx. 200 KB. Se recorta al centro y se oscurece abajo para leer el texto. Vacía: no se muestra la tarjeta, ni su antetítulo ni su texto.', 'ese-latam'),
                    'return_format' => 'url',
                    'preview_size'  => 'medium',
                    'wrapper'       => ['width' => '40'],
                ]),
                $campo('text', 'ctc_aside_kicker', __('Tarjeta del panel — antetítulo', 'ese-latam'), [
                    'instructions' => __('Frase corta en mayúsculas sobre la foto de la tarjeta, abajo. Hasta 30 caracteres. Vacío: no se muestra.', 'ese-latam'),
                    'placeholder'  => __('Sede Miraflores', 'ese-latam'),
                    'wrapper'      => ['width' => '60'],
                ]),
                $campo('textarea', 'ctc_aside_texto', __('Tarjeta del panel — texto', 'ese-latam'), [
                    'instructions' => __('Frase en blanco bajo el antetítulo, sobre la foto. Hasta 90 caracteres. Vacío: no se muestra.', 'ese-latam'),
                    'rows'         => 2,
                ]),
                $campo('text', 'ctc_aside_titulo', __('Título de la lista de datos de contacto', 'ese-latam'), [
                    'instructions' => __('Encabezado en mayúsculas sobre el teléfono, el correo, la dirección y el horario. Hasta 40 caracteres. Vacío: la lista se muestra sin título.', 'ese-latam'),
                    'placeholder'  => __('Información de contacto', 'ese-latam'),
                ]),
            ],

            /* ---------------- Panel lateral — rótulos ---------------- */
            [$tab('ctc_aside_rotulos', __('Panel lateral — rótulos', 'ese-latam'))],
            [
                $campo('text', 'ctc_info_telefono', __('Rótulo del teléfono en el panel', 'ese-latam'), [
                    'instructions' => __('Texto en azul oscuro sobre el número de teléfono. Hasta 25 caracteres. Vacío: el número se muestra sin rótulo. Sin teléfono cargado, la fila no aparece.', 'ese-latam'),
                    'placeholder'  => __('Teléfono', 'ese-latam'),
                    'wrapper'      => ['width' => '25'],
                ]),
                $campo('text', 'ctc_info_email', __('Rótulo del correo en el panel', 'ese-latam'), [
                    'instructions' => __('Texto en azul oscuro sobre el correo. Hasta 25 caracteres. Vacío: el correo se muestra sin rótulo. Sin correo cargado, la fila no aparece.', 'ese-latam'),
                    'placeholder'  => __('Correo electrónico', 'ese-latam'),
                    'wrapper'      => ['width' => '25'],
                ]),
                $campo('text', 'ctc_info_direccion', __('Rótulo de la dirección en el panel', 'ese-latam'), [
                    'instructions' => __('Texto en azul oscuro sobre la dirección. Hasta 25 caracteres. Vacío: la dirección se muestra sin rótulo. Sin dirección cargada, la fila no aparece.', 'ese-latam'),
                    'placeholder'  => __('Dirección', 'ese-latam'),
                    'wrapper'      => ['width' => '25'],
                ]),
                $campo('text', 'ctc_info_horario', __('Rótulo del horario en el panel', 'ese-latam'), [
                    'instructions' => __('Texto en azul oscuro sobre el horario de atención. Hasta 25 caracteres. Vacío: el horario se muestra sin rótulo. Sin horario cargado, la fila no aparece.', 'ese-latam'),
                    'placeholder'  => __('Horario de atención', 'ese-latam'),
                    'wrapper'      => ['width' => '25'],
                ]),
            ],

            /* ---------------- Sede ---------------- */
            [$tab('ctc_sede', __('Sede', 'ese-latam'))],
            [
                $campo('text', 'ctc_sede_kicker', __('Antetítulo sobre el titular de la sede', 'ese-latam'), [
                    'instructions' => __('Palabra o frase corta en mayúsculas, con una barra delante, encima del titular. Hasta 30 caracteres. Vacío: no se muestra.', 'ese-latam'),
                    'placeholder'  => __('Dónde estamos', 'ese-latam'),
                    'wrapper'      => ['width' => '40'],
                ]),
                $campo('textarea', 'ctc_sede_titulo', __('Titular de la sección de la sede', 'ese-latam'), [
                    'instructions' => __('Frase grande sobre la dirección y el mapa, bajo el formulario. Sin titular y sin dirección cargada en Personalizar, la sección no se muestra.', 'ese-latam') . ' ' . ese_latam_ayuda_titulo(),
                    'rows'         => 2,
                    'placeholder'  => "Sede central\n|ESE Latam|",
                    'wrapper'      => ['width' => '60'],
                ]),
                $campo('text', 'ctc_sede_enlace', __('Texto del enlace a Google Maps', 'ese-latam'), [
                    'instructions' => __('Enlace con flecha a la derecha de la dirección; abre Google Maps en otra pestaña. Hasta 30 caracteres. Vacío: no se muestra el enlace.', 'ese-latam'),
                    'placeholder'  => __('Llegar con Google Maps', 'ese-latam'),
                    'wrapper'      => ['width' => '50'],
                ]),
                $campo('true_false', 'ctc_sede_mapa', __('¿Mostrar el mapa de la sede?', 'ese-latam'), [
                    'instructions'  => __('Mapa de Google bajo la dirección, ubicado con la dirección de Personalizar. Apagado: la sección muestra solo el titular y la dirección.', 'ese-latam'),
                    'default_value' => 1,
                    'ui'            => 1,
                    'wrapper'       => ['width' => '50'],
                ]),
            ]
        ),
    ]);
});
