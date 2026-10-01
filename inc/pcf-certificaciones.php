<?php
/**
 * Campos de la página "Certificaciones" (Figma 3785-4089).
 *
 * Los sellos NO se escriben acá: son el módulo "Certificaciones"
 * (inc/modulos.php). Esta página solo elige cuáles muestra —con un campo de
 * relación— y aporta el copy que los envuelve: el hero, los cuatro
 * argumentos de "Marcando la diferencia", los paneles de "Calidad superior",
 * el ciclo Blue Angel y la guía para validar certificados.
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

    $pagina = get_page_by_path('certificaciones');
    if (! $pagina instanceof WP_Post) {
        return;
    }

    $campo = 'ese_latam_campo_def';
    $tab   = 'ese_latam_campo_tab';

    $foto = static fn (string $name, string $label, array $args = []): array =>
        ese_latam_campo_def('image', $name, $label, array_merge([
            'return_format' => 'url',
            'preview_size'  => 'medium',
        ], $args));

    // Encabezado de sección: el trío que se repite en casi todos los bloques.
    // $seccion es el nombre que el cliente ve en la web (va en las
    // etiquetas), $donde ubica el titular y $negrita dice qué pasa con la
    // negrita de la bajada en esa sección.
    $encabezado = static function (string $prefijo, string $seccion, string $donde, string $negrita, string $kicker, string $titulo) use ($campo): array {
        return [
            $campo('text', $prefijo . '_kicker', sprintf(
                /* translators: %s: nombre de la sección. */
                __('Antetítulo de «%s»', 'ese-latam'),
                $seccion
            ), [
                'instructions' => __('Palabra o frase corta en mayúsculas, con una barra delante, encima del titular. Hasta 30 caracteres. Vacío: no se muestra.', 'ese-latam'),
                'placeholder'  => $kicker,
                'wrapper'      => ['width' => '40'],
            ]),
            $campo('textarea', $prefijo . '_titulo', sprintf(
                /* translators: %s: nombre de la sección. */
                __('Titular de la sección «%s»', 'ese-latam'),
                $seccion
            ), [
                'instructions' => $donde . ' ' . ese_latam_ayuda_titulo(),
                'rows'         => 2,
                'placeholder'  => $titulo,
                'wrapper'      => ['width' => '60'],
            ]),
            $campo('wysiwyg', $prefijo . '_desc', sprintf(
                /* translators: %s: nombre de la sección. */
                __('Bajada de «%s»', 'ese-latam'),
                $seccion
            ), [
                'instructions' => __('Párrafo corto junto al titular, una o dos frases.', 'ese-latam') . ' ' . $negrita . ' ' . __('Vacía: no se muestra.', 'ese-latam'),
                'tabs'         => 'visual',
                'toolbar'      => 'basic',
                'media_upload' => 0,
            ]),
        ];
    };

    $negrita_verde = __('El tramo en <strong>negrita</strong> se muestra en verde.', 'ese-latam');

    acf_add_local_field_group([
        'key'      => 'group_pagina_certificaciones',
        'title'    => __('Contenido de la página', 'ese-latam'),
        'location' => [[['param' => 'page', 'operator' => '==', 'value' => (string) $pagina->ID]]],
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,
        'fields'                => array_merge(

            /* ---------------- Hero ---------------- */
            [$tab('cert_hero', __('Hero', 'ese-latam'))],
            [
                $campo('text', 'certpag_hero_kicker', __('Antetítulo sobre el titular del hero', 'ese-latam'), [
                    'instructions' => __('Palabra o frase corta en mayúsculas encima del titular. Hasta 30 caracteres. Vacío: no se muestra.', 'ese-latam'),
                    'placeholder'  => __('Respaldo comprobado', 'ese-latam'),
                    'wrapper'      => ['width' => '40'],
                ]),
                $campo('textarea', 'certpag_hero_titulo', __('Titular principal del hero', 'ese-latam'), [
                    'instructions' => __('Frase grande sobre la foto de fondo, abajo a la izquierda.', 'ese-latam') . ' ' . ese_latam_ayuda_titulo(),
                    'rows'         => 2,
                    'placeholder'  => 'calidad y estándar certificado |que nos respaldan|',
                    'wrapper'      => ['width' => '60'],
                ]),
                $campo('textarea', 'certpag_hero_desc', __('Bajada junto al titular del hero', 'ese-latam'), [
                    'instructions' => __('Párrafo corto a la derecha del titular, sobre el botón. Una o dos frases, hasta 200 caracteres. Vacía: no se muestra.', 'ese-latam'),
                    'rows'         => 3,
                ]),
                $foto('certpag_hero_imagen', __('Foto de fondo del hero', 'ese-latam'), [
                    'instructions' => __('Ocupa toda la pantalla detrás del titular y se muestra muy oscurecida. WebP o JPG · 1920×1080 px (16:9) · máx. 400 KB. Se recorta al centro. Vacía: el hero queda en negro liso.', 'ese-latam'),
                ]),
                $campo('link', 'certpag_hero_cta', __('Botón del hero (texto y enlace)', 'ese-latam'), [
                    'instructions' => __('Botón bajo la bajada. Escribe el texto del botón y la página a la que lleva. Vacío: el hero se muestra sin botón.', 'ese-latam'),
                ]),
            ],

            /* ---------------- Sellos ---------------- */
            [$tab('cert_sellos', __('Sellos', 'ese-latam'))],
            [
                $campo('message', '', __('Dónde se editan los sellos', 'ese-latam'), [
                    'key'      => 'field_certpag_msg',
                    'message'  => __('Cada sello, con su logo, su descripción y sus criterios, se edita en el módulo <strong>Certificaciones</strong> del menú lateral. Acá solo eliges cuáles aparecen en esta página y en qué orden.', 'ese-latam'),
                    'esc_html' => 0,
                ]),
            ],
            $encabezado(
                'certpag_sellos',
                __('Nuestras certificaciones', 'ese-latam'),
                __('Frase grande que abre la sección de sellos, sobre la grilla de logos.', 'ese-latam'),
                $negrita_verde,
                __('Respaldo comprobado', 'ese-latam'),
                "Nuestras\n|certificaciones|"
            ),
            [
                $campo('relationship', 'certpag_sellos_lista', __('Sellos que se muestran en la página', 'ese-latam'), [
                    'instructions'  => __('Elige qué sellos del módulo Certificaciones aparecen en la grilla y arrástralos para ordenarlos. El primero se abre con su ficha a la derecha. Vacío: se muestran todos los del módulo, en su propio orden. Sin sellos en el módulo, la sección no se muestra.', 'ese-latam'),
                    'post_type'     => ['certificacion'],
                    'filters'       => ['search'],
                    'return_format' => 'id',
                ]),
            ],

            /* ---------------- Marquee ---------------- */
            [$tab('cert_marquee', __('Marquee', 'ese-latam'))],
            [
                $campo('text', 'certpag_marquee', __('Frase de la cinta en movimiento', 'ese-latam'), [
                    'instructions' => __('Texto grande que se desliza de lado a lado entre los sellos y «Marcando la diferencia». Hasta 60 caracteres. Vacía: la cinta no se muestra.', 'ese-latam'),
                    'placeholder'  => __('marca la diferencia', 'ese-latam'),
                    'maxlength'    => 60,
                ]),
            ],

            /* ---------------- Marcando la diferencia ---------------- */
            [$tab('cert_diferencia', __('Marcando la diferencia', 'ese-latam'))],
            $encabezado(
                'certpag_dif',
                __('Marcando la diferencia', 'ese-latam'),
                __('Frase grande que abre la sección de pestañas con foto.', 'ese-latam'),
                $negrita_verde,
                __('Certificaciones', 'ese-latam'),
                "Marcando la\n|diferencia|"
            ),
            [
                $campo('repeater', 'certpag_dif_tabs', __('Argumentos de «Marcando la diferencia»', 'ese-latam'), [
                    'instructions' => __('Cada argumento es una pestaña con barra de progreso que cambia la foto grande de abajo; avanzan solas cada 6 segundos. Lo ideal son 3 o 4: con 4 se reparten en cuatro columnas. Las filas sin título se ignoran. Sin argumentos, la sección no se muestra.', 'ese-latam'),
                    'layout'       => 'block',
                    'button_label' => __('Añadir argumento', 'ese-latam'),
                    'sub_fields'   => [
                        $campo('text', 'title', __('Título del argumento', 'ese-latam'), [
                            'key'          => 'field_certdif_titulo',
                            'instructions' => __('Nombre de la pestaña, en mayúsculas. Hasta 40 caracteres.', 'ese-latam'),
                            'required'     => 1,
                        ]),
                        $campo('textarea', 'desc', __('Descripción del argumento', 'ese-latam'), [
                            'key'          => 'field_certdif_desc',
                            'instructions' => __('Texto corto bajo el título de la pestaña, una o dos frases. Vacía: la pestaña muestra solo el título.', 'ese-latam'),
                            'rows'         => 2,
                        ]),
                        $foto('img', __('Foto del argumento', 'ese-latam'), [
                            'key'          => 'field_certdif_img',
                            'instructions' => __('Foto grande bajo las pestañas; cambia al activar la pestaña. WebP o JPG · 2560×1340 px (aprox. 2:1) · máx. 500 KB. Se recorta al centro; en móvil pasa a 4:3. Vacía: el recuadro de la foto queda gris claro.', 'ese-latam'),
                        ]),
                    ],
                ]),
            ],

            /* ---------------- Calidad superior ---------------- */
            [$tab('cert_calidad', __('Calidad superior', 'ese-latam'))],
            $encabezado(
                'certpag_cal',
                __('Calidad superior', 'ese-latam'),
                __('Frase grande que abre la sección de paneles con foto y menú lateral.', 'ese-latam'),
                $negrita_verde,
                __('Sobre nosotros', 'ese-latam'),
                "Calidad superior\n|desde el origen|"
            ),
            [
                $campo('repeater', 'certpag_cal_items', __('Paneles de «Calidad superior»', 'ese-latam'), [
                    'instructions' => __('Cada panel es una foto con número y título. Al bajar, el menú lateral pegajoso marca el panel que se está leyendo. Las filas sin título se ignoran. Sin paneles, la sección no se muestra.', 'ese-latam'),
                    'layout'       => 'block',
                    'button_label' => __('Añadir panel', 'ese-latam'),
                    'sub_fields'   => [
                        $campo('text', 'title', __('Título del panel', 'ese-latam'), [
                            'key'          => 'field_certcal_titulo',
                            'instructions' => __('Va sobre la foto y en el menú lateral. Hasta 40 caracteres.', 'ese-latam'),
                            'required'     => 1,
                            'wrapper'      => ['width' => '60'],
                        ]),
                        $foto('img', __('Foto del panel', 'ese-latam'), [
                            'key'          => 'field_certcal_img',
                            'instructions' => __('WebP o JPG · 2200×1100 px (2:1) · máx. 400 KB. Se recorta al centro y se oscurece abajo para leer el título; en móvil pasa a 16:10. Vacía: el panel queda en color liso con el título encima.', 'ese-latam'),
                            'wrapper'      => ['width' => '40'],
                        ]),
                        $campo('wysiwyg', 'text', __('Texto bajo la foto del panel', 'ese-latam'), [
                            'key'          => 'field_certcal_text',
                            'instructions' => __('Párrafo bajo la foto, dos o tres frases. El tramo en <strong>negrita</strong> se muestra en verde. Vacío: solo se ve la foto.', 'ese-latam'),
                            'tabs'         => 'visual',
                            'toolbar'      => 'basic',
                            'media_upload' => 0,
                        ]),
                    ],
                ]),
                $campo('link', 'certpag_cal_salto', __('Enlace para saltar la sección', 'ese-latam'), [
                    'instructions' => __('Atajo bajo el menú lateral para pasar a la sección siguiente. Escribe el texto y la dirección, por ejemplo #blue-angel. Vacío: no se muestra.', 'ese-latam'),
                ]),
            ],

            /* ---------------- Blue Angel ---------------- */
            [$tab('cert_bangel', __('Blue Angel', 'ese-latam'))],
            $encabezado(
                'certpag_ba',
                __('Estándar Blue Angel', 'ese-latam'),
                __('Frase grande en blanco, centrada sobre la franja celeste. En esta franja el tramo resaltado se ve en negrita blanca.', 'ese-latam'),
                __('El tramo en <strong>negrita</strong> se muestra en negrita blanca.', 'ese-latam'),
                __('Productos', 'ese-latam'),
                'Estándar |Blue Angel|'
            ),
            [
                $campo('repeater', 'certpag_ba_pasos', __('Etapas del ciclo Blue Angel', 'ese-latam'), [
                    'instructions' => __('Pasos del carrusel en arco: el del centro se ve grande con su nombre debajo, y giran solos cada 4 segundos. El orden de la lista es el del ciclo. Las filas sin nombre se ignoran. Sin etapas, la sección no se muestra.', 'ese-latam'),
                    'layout'       => 'table',
                    'button_label' => __('Añadir etapa', 'ese-latam'),
                    'sub_fields'   => [
                        $campo('text', 'label', __('Nombre de la etapa', 'ese-latam'), [
                            'key'          => 'field_certba_label',
                            'instructions' => __('Se lee bajo la imagen cuando la etapa está al centro. Hasta 50 caracteres.', 'ese-latam'),
                            'required'     => 1,
                        ]),
                        $foto('img', __('Imagen de la etapa', 'ese-latam'), [
                            'key'          => 'field_certba_img',
                            'instructions' => __('PNG o WebP con fondo transparente · 1000×1000 px (1:1) · máx. 300 KB. Se muestra entera, sin recortes. Vacía: la etapa queda sin imagen en el arco.', 'ese-latam'),
                            'preview_size' => 'thumbnail',
                        ]),
                    ],
                ]),
                $campo('number', 'certpag_ba_inicial', __('Etapa centrada al abrir la página', 'ese-latam'), [
                    'instructions' => __('Número de la etapa que aparece al centro al cargar, contando desde 1 en el orden de la lista. Si es mayor que el total, se centra la última. Vacío: la primera.', 'ese-latam'),
                    'min'          => 1,
                    'wrapper'      => ['width' => '40'],
                ]),
            ],

            /* ---------------- Validación ---------------- */
            [$tab('cert_valida', __('Validar certificados', 'ese-latam'))],
            $encabezado(
                'certpag_val',
                __('Valida certificados originales', 'ese-latam'),
                __('Frase grande a la izquierda, sobre la foto del parque.', 'ese-latam'),
                $negrita_verde,
                __('Sobre nosotros', 'ese-latam'),
                "Valida certificados\n|originales|"
            ),

            /* ---------------- Validación — pasos ---------------- */
            [$tab('cert_valida_pasos', __('Validar certificados — pasos', 'ese-latam'))],
            [
                $campo('text', 'certpag_val_pasos_kicker', __('Antetítulo del carrusel de pasos', 'ese-latam'), [
                    'instructions' => __('Frase corta en mayúsculas encima de las tarjetas de pasos, a la derecha. Hasta 30 caracteres. Vacío: no se muestra.', 'ese-latam'),
                    'placeholder'  => __('Pasos a seguir', 'ese-latam'),
                    'wrapper'      => ['width' => '50'],
                ]),
                $campo('link', 'certpag_val_enlace', __('Enlace junto al carrusel de pasos', 'ese-latam'), [
                    'instructions' => __('Enlace con flecha a la derecha del antetítulo del carrusel, por ejemplo a un verificador externo. Escribe el texto y la dirección. Vacío: no se muestra.', 'ese-latam'),
                    'wrapper'      => ['width' => '50'],
                ]),
                $campo('repeater', 'certpag_val_pasos', __('Pasos para validar un certificado', 'ese-latam'), [
                    'instructions' => __('Tarjetas apiladas que avanzan solas cada 6 segundos, con su pestaña numerada debajo. Lo ideal son 4 pasos. Las filas sin texto de pestaña se ignoran. Sin pasos, la sección no se muestra.', 'ese-latam'),
                    'layout'       => 'block',
                    'button_label' => __('Añadir paso', 'ese-latam'),
                    'sub_fields'   => [
                        $campo('text', 'tab', __('Pestaña del paso', 'ese-latam'), [
                            'key'          => 'field_certval_tab',
                            'instructions' => __('Nombre corto bajo las tarjetas; el número lo pone la web. Hasta 20 caracteres.', 'ese-latam'),
                            'required'     => 1,
                            'maxlength'    => 20,
                            'wrapper'      => ['width' => '30'],
                        ]),
                        $campo('text', 'title', __('Título de la tarjeta del paso', 'ese-latam'), [
                            'key'          => 'field_certval_titulo',
                            'instructions' => __('Frase en la cabecera de la tarjeta, junto al número. Hasta 60 caracteres.', 'ese-latam'),
                            'required'     => 1,
                            'wrapper'      => ['width' => '70'],
                        ]),
                        $campo('textarea', 'text', __('Explicación del paso', 'ese-latam'), [
                            'key'          => 'field_certval_text',
                            'instructions' => __('Párrafo de la tarjeta, dos o tres frases. Vacía: la tarjeta queda sin párrafo.', 'ese-latam'),
                            'rows'         => 3,
                        ]),
                        $campo('textarea', 'tip', __('Recomendación al pie de la tarjeta', 'ese-latam'), [
                            'key'          => 'field_certval_tip',
                            'instructions' => __('Línea destacada al pie de la tarjeta, bajo el rótulo «Recomendación». Una frase. Vacía: el rótulo queda sin texto.', 'ese-latam'),
                            'rows'         => 2,
                        ]),
                    ],
                ]),
            ],

            /* ---------------- Preguntas frecuentes ---------------- */
            [$tab('cert_faq', __('Preguntas frecuentes', 'ese-latam'))],
            [
                $campo('message', '', __('Dónde se editan estas preguntas', 'ese-latam'), [
                    'key'      => 'field_certpag_faq_msg',
                    'message'  => __('Las preguntas salen solas del módulo <strong>Preguntas frecuentes</strong>: se muestran todas las de la categoría elegida abajo, en el orden de su campo «Orden». Una pregunta nueva con esa categoría aparece aquí sin tocar esta página. Sin preguntas en la categoría, la sección no se muestra.', 'ese-latam'),
                    'esc_html' => 0,
                ]),
                $campo('text', 'certpag_faq_kicker', __('Antetítulo de las preguntas frecuentes', 'ese-latam'), [
                    'instructions' => __('Palabra o frase corta en mayúsculas, con una barra delante, encima del titular, sobre Casos reales. Hasta 30 caracteres. Vacío: no se muestra.', 'ese-latam'),
                    'placeholder'  => __('Preguntas frecuentes', 'ese-latam'),
                    'maxlength'    => 30,
                    'wrapper'      => ['width' => '40'],
                ]),
                $campo('textarea', 'certpag_faq_titulo', __('Titular de las preguntas frecuentes', 'ese-latam'), [
                    'instructions' => __('Frase grande sobre el acordeón de preguntas, justo antes de Casos reales.', 'ese-latam') . ' ' . ese_latam_ayuda_titulo() . ' ' . __('Vacío: no se muestra.', 'ese-latam'),
                    'rows'         => 2,
                    'placeholder'  => "Dudas sobre\n|certificaciones|",
                    'wrapper'      => ['width' => '60'],
                ]),
                $campo('taxonomy', 'certpag_faq_categoria', __('Categoría de preguntas que se muestra', 'ese-latam'), [
                    'instructions'  => __('Elige de qué categoría del módulo Preguntas frecuentes salen las preguntas. Vacía: se usa la categoría Certificaciones.', 'ese-latam'),
                    'taxonomy'      => 'faq_categoria',
                    'field_type'    => 'select',
                    'allow_null'    => 1,
                    'add_term'      => 0,
                    'return_format' => 'id',
                    'wrapper'       => ['width' => '50'],
                ]),
                $campo('link', 'certpag_faq_enlace', __('Enlace a todas las preguntas frecuentes', 'ese-latam'), [
                    'instructions' => __('Enlace con flecha a la derecha del titular, normalmente a la página Preguntas frecuentes. Escribe el texto y la página. Vacío: la sección se muestra sin enlace.', 'ese-latam'),
                    'wrapper'      => ['width' => '50'],
                ]),
            ],

            /* ---------------- Casos ---------------- */
            [$tab('cert_casos', __('Casos reales', 'ese-latam'))],
            $encabezado(
                'certpag_casos',
                __('Casos reales', 'ese-latam'),
                __('Frase grande que abre la franja de casos de éxito. Las tarjetas son los 4 últimos casos publicados en Casos de éxito; sin casos, la sección no se muestra.', 'ese-latam'),
                __('Escribe texto simple, sin negritas ni enlaces.', 'ese-latam'),
                __('Casos reales', 'ese-latam'),
                "Casos reales en\n|gestión urbana|"
            ),
            [
                $campo('link', 'certpag_casos_enlace', __('Enlace a todos los casos', 'ese-latam'), [
                    'instructions' => __('Enlace con flecha a la derecha del titular, normalmente a Casos de éxito. Escribe el texto y la página. Vacío: la sección se muestra sin enlace.', 'ese-latam'),
                ]),
            ]
        ),
    ]);
});
