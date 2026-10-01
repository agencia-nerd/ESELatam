<?php
/**
 * Página "Preguntas frecuentes": lectura de las preguntas agrupadas por
 * categoría y la carga inicial del contenido.
 *
 * Las preguntas son el módulo "Preguntas frecuentes" (CPT `faq`, ver
 * inc/modulos.php) y se agrupan con la taxonomía `faq_categoria`. La página
 * no guarda preguntas propias: solo el copy del hero y del índice
 * (inc/pcf-preguntas-frecuentes.php).
 *
 * @package EseLatam
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Categorías con preguntas publicadas, en el orden del campo "Orden" de cada
 * categoría (las que no lo tienen van al final, por nombre). Cada pregunta
 * cuenta solo en su primera categoría, para que no se repita en dos bloques.
 *
 * @return list<array{
 *     id: string,
 *     nombre: string,
 *     titulo: string,
 *     preguntas: list<array{q: string, a: string}>
 * }>
 */
function ese_latam_faq_por_categoria(): array {
    $terminos = get_terms([
        'taxonomy'   => 'faq_categoria',
        'hide_empty' => true,
    ]);
    if (is_wp_error($terminos) || [] === $terminos) {
        return [];
    }

    $orden = static function (WP_Term $t): int {
        $valor = ese_latam_campo_termino('orden', $t->term_id);
        return is_numeric($valor) ? (int) $valor : PHP_INT_MAX;
    };
    usort($terminos, static fn (WP_Term $a, WP_Term $b): int =>
        [$orden($a), $a->name] <=> [$orden($b), $b->name]);

    $vistas     = [];
    $categorias = [];

    foreach ($terminos as $termino) {
        $preguntas = [];

        foreach (ese_latam_faq_de_categoria($termino->term_id) as $faq_id => $faq) {
            if (isset($vistas[$faq_id])) {
                continue;
            }
            $vistas[$faq_id] = true;
            $preguntas[]     = $faq;
        }

        if ([] === $preguntas) {
            continue;
        }

        $titulo = trim((string) ese_latam_campo_termino('titulo_bloque', $termino->term_id));

        $categorias[] = [
            'id'        => 'faq-' . $termino->slug,
            'nombre'    => $termino->name,
            'titulo'    => '' !== $titulo ? $titulo : $termino->name,
            'preguntas' => $preguntas,
        ];
    }

    return $categorias;
}

/**
 * Preguntas publicadas de una categoría, en el orden del campo "Orden" de
 * cada pregunta, indexadas por ID (para poder descartar repetidas).
 *
 * @return array<int, array{q: string, a: string}>
 */
function ese_latam_faq_de_categoria(int $term_id): array {
    if ($term_id <= 0) {
        return [];
    }

    $entradas = get_posts([
        'post_type'        => 'faq',
        'post_status'      => 'publish',
        'posts_per_page'   => -1,
        'orderby'          => 'menu_order title',
        'order'            => 'ASC',
        'suppress_filters' => false,
        'tax_query'        => [[
            'taxonomy' => 'faq_categoria',
            'field'    => 'term_id',
            'terms'    => $term_id,
        ]],
    ]);

    $preguntas = [];
    foreach ($entradas as $entrada) {
        $preguntas[$entrada->ID] = [
            'q' => get_the_title($entrada->ID),
            'a' => ese_latam_texto_rico((string) ese_latam_campo('respuesta', $entrada->ID, '')),
        ];
    }

    return $preguntas;
}

/**
 * Resuelve la categoría de una sección de preguntas: la que eligió el
 * editor (ID de término) o, si no eligió ninguna, la del slug por defecto.
 * 0 si no existe ninguna de las dos.
 *
 * @param mixed $elegida Valor del campo de taxonomía (ID, objeto o vacío).
 */
function ese_latam_faq_categoria_id($elegida, string $slug_defecto): int {
    if ($elegida instanceof WP_Term) {
        return $elegida->term_id;
    }
    if (is_array($elegida)) {
        $elegida = reset($elegida);
    }
    if (is_numeric($elegida) && (int) $elegida > 0 && term_exists((int) $elegida, 'faq_categoria')) {
        return (int) $elegida;
    }

    $termino = get_term_by('slug', $slug_defecto, 'faq_categoria');

    return $termino instanceof WP_Term ? $termino->term_id : 0;
}

/**
 * Copy inicial de la sección de preguntas de Certificaciones, una sola vez
 * y solo en los campos vacíos (mismo criterio que ese_latam_sembrar_faq()).
 */
function ese_latam_sembrar_faq_certificaciones(): void {
    if (get_option('ese_latam_faq_cert_sembrada') || ! ese_latam_campos_activos()) {
        return;
    }

    $pagina = get_page_by_path('certificaciones');
    if ($pagina instanceof WP_Post) {
        $copy = [
            'certpag_faq_kicker' => 'Preguntas frecuentes',
            'certpag_faq_titulo' => "Dudas sobre\n|certificaciones|",
            'certpag_faq_enlace' => [
                'title'  => 'Ver todas las preguntas',
                'url'    => ese_latam_pagina_url('preguntas-frecuentes', home_url('/preguntas-frecuentes/')),
                'target' => '',
            ],
        ];
        foreach ($copy as $campo => $valor) {
            if (ese_latam_campo_vacio(get_field($campo, $pagina->ID))) {
                update_field($campo, $valor, $pagina->ID);
            }
        }
    }

    update_option('ese_latam_faq_cert_sembrada', 1);
}
add_action('init', 'ese_latam_sembrar_faq_certificaciones', 31);

/**
 * Carga inicial: las cuatro categorías y las preguntas del documento de
 * contenido (docs-preguntas/FAQ_ESE_LATAM-_2_.pdf), más el copy del hero.
 *
 * Corre UNA sola vez, igual que ese_latam_asegurar_paginas(): queda una
 * marca en opciones y no vuelve a tocar nada aunque el cliente después
 * borre, renombre o reordene. Es idempotente por si se corta a mitad: una
 * categoría o pregunta que ya existe con el mismo nombre no se duplica, y
 * los campos que ya tienen valor no se pisan.
 */
function ese_latam_sembrar_faq(): void {
    if (get_option('ese_latam_faq_sembradas') || ! ese_latam_campos_activos()) {
        return;
    }

    $contenido = [
        [
            'nombre' => 'Especificaciones',
            'titulo' => 'Especificaciones de contenedores',
            'faqs'   => [
                ['¿Dónde y cuándo usar un contenedor de 2 ruedas?', 'Los contenedores ESE de 2 ruedas (120L a 360L) son ideales para recolección domiciliaria, zonas residenciales, edificios y comercios pequeños. Su diseño ergonómico facilita el traslado por veredas y pasadizos estrechos, y son compatibles con el sistema de recolección mecanizada ("lifter"), el mecanismo de izaje estándar de los camiones recolectores en la región.'],
                ['¿Dónde y cuándo usar un contenedor de 3 ruedas?', 'Los contenedores de 3 ruedas (240L y 370L) están pensados para almacenes, edificios y propiedades comerciales que necesitan mayor capacidad sin perder maniobrabilidad. La tercera rueda giratoria de 360° permite desplazarlos con facilidad en espacios y pasajes estrechos, incluso con el contenedor lleno.'],
                ['¿Dónde y cuándo usar un contenedor de 4 ruedas?', 'Los contenedores de 4 ruedas (660L a 1100L) son la opción para espacios públicos, empresas de recolección y puntos de alto volumen de residuos, como condominios, supermercados o aeropuertos. Su mayor capacidad y compatibilidad con sistemas de elevación de camiones recolectores los hace ideales para el recojo masivo y frecuente.'],
                ['¿Qué contenedores ESE son compatibles con camiones recolectores de carga trasera?', 'Todos los contenedores ESE de 2, 3 y 4 ruedas son compatibles con camiones recolectores de carga trasera: el borde superior está diseñado según la norma europea EN 840 para encajar directamente en el lifter que estos camiones usan para levantar y vaciar el contenedor de forma automática.'],
                ['¿Qué certificaciones tienen los contenedores?', 'Los contenedores ESE cuentan con certificaciones agrupadas en tres frentes: técnico (DIN EN 840, CEN), de gestión y calidad (ISO 9001, ISO 14001, ISO 50001, RAL-GZ 951/1) y ambiental (Blue Angel, avalado por DEKRA). En conjunto, garantizan que el diseño, la fabricación y el impacto ambiental del producto cumplen los estándares europeos más exigentes.'],
                ['Si eres representante de ESE LATAM, ¿cuál es la garantía que respalda a nuestros contenedores?', 'La garantía se aplica si hay fallas de fábrica: después de la entrega, se tienen 3 meses para hacer el reclamo. En el caso de los contenedores de 4 ruedas, el plazo es de 6 meses para reclamos por rotura por apilamiento.'],
                ['¿Cuál es la capacidad máxima y la carga útil de cada contenedor?', 'La carga útil aumenta junto con el litraje: en los contenedores de 2 ruedas va de 60 kg (120L) a 160 kg (360L); en los de 3 ruedas, de 110 kg (240L) a 170 kg (370L); y en los de 4 ruedas, de 350 kg (400L) a 510 kg (1100L). La ficha técnica de cada modelo detalla el peso máximo permitido exacto.'],
                ['¿Cuál es la vida útil proyectada de un contenedor ESE?', 'Los contenedores ESE tienen una vida útil proyectada de hasta 20 años, gracias al HDPE reciclado con el que están fabricados, un material que puede reutilizarse hasta 10 veces sin perder calidad ni resistencia estructural.'],
            ],
        ],
        [
            'nombre' => 'Certificaciones',
            'titulo' => 'Certificaciones',
            'faqs'   => [
                ['¿Cuál es la diferencia entre DIN y RAL?', 'DIN EN 840 es la norma técnica europea (emitida bajo el Comité Europeo de Normalización, CEN, con origen en el instituto alemán DIN) que define las especificaciones de fabricación de un contenedor (ruedas, capacidad, dimensiones, diseño). RAL-GZ 951/1, en cambio, es una certificación de calidad que evalúa mediante pruebas anuales la durabilidad y resistencia del producto terminado: DIN certifica el diseño, RAL certifica la calidad y resistencia.'],
                ['¿De qué se tratan las 38 pruebas RAL?', 'Las 38 pruebas RAL-GZ 951/1 son ensayos anuales de resistencia mecánica y calidad, evaluados por entidades independientes como GGAWB, SKZ y LRQA. Incluyen, por ejemplo, pruebas de impacto por caída (caída libre y caída de bala, que miden resistencia a golpes), de corte en las bisagras ("guillotina"), de resistencia de ruedas y de los mecanismos de elevación del contenedor.'],
                ['¿La certificación Blue Angel se otorga al producto o al proceso de producción?', 'La certificación Blue Angel se otorga al producto terminado, no al proceso de fabricación. Acredita que el contenedor está hecho con un mínimo de 80% de plástico reciclado, certificado según el estándar EuCertPlast. En cambio, la certificación ISO 9001 sí se otorga al proceso de producción.'],
            ],
        ],
        [
            'nombre' => 'Residuos Peligrosos',
            'titulo' => 'Residuos peligrosos y línea Smart',
            'faqs'   => [
                ['¿Qué son los contenedores de residuos peligrosos?', 'Los contenedores de residuos peligrosos son todos aquellos que tienen características especiales para poder almacenar y transportar residuos que pueden generar contaminación o daño a su alrededor.'],
                ['¿Qué es un contenedor inteligente?', 'Es aquel al que se le coloca un sensor de identificación y/o de llenado, que permite identificar y detallar información sobre el contenedor: su ubicación, el nivel de llenado, la trazabilidad del residuo y hasta su valoración.'],
                ['¿Qué es un sensor inteligente (smart) en un contenedor y para qué sirve?', 'El sensor inteligente Quamtra mide en tiempo real el nivel de llenado de un contenedor mediante ultrasonido, e incluye GPS, acelerómetro y sensor de temperatura para detectar su ubicación, vandalismo o incendios. Se usa en la línea semisoterrada Bagio Smart y permite planificar rutas de recolección más eficientes, reduciendo costos operativos.'],
            ],
        ],
        [
            'nombre' => 'Regulación LATAM',
            'titulo' => 'Regulación en Latinoamérica',
            'faqs'   => [
                ['¿Qué normativa de OEFA aplica al almacenamiento y segregación de residuos sólidos en Perú?', 'En Perú, el Organismo de Evaluación y Fiscalización Ambiental (OEFA), adscrito al Ministerio del Ambiente, fiscaliza el cumplimiento del Decreto Legislativo N° 1278, Ley de Gestión Integral de Residuos Sólidos, que regula el almacenamiento, la segregación y la disposición final de residuos. Empresas y municipalidades deben contar con contenedores adecuados para evitar sanciones por manejo inadecuado.'],
                ['¿Qué es la Ley REP (Responsabilidad Extendida del Productor) en Chile y cómo afecta a la gestión de residuos?', 'La Ley REP (Ley 20.920) de Chile establece la Responsabilidad Extendida del Productor: obliga a las empresas que introducen envases, neumáticos, baterías u otros productos prioritarios al mercado a financiar y gestionar la recolección y valorización de los residuos que generan, bajo el principio "el que contamina paga".'],
                ['¿Qué es la Ley General de Economía Circular en México y qué obligaciones trae para productores e importadores?', 'En enero de 2026 México publicó la Ley General de Economía Circular, que introduce formalmente la Responsabilidad Extendida del Productor a nivel nacional. Exige a productores e importadores implementar diseño circular, inscribirse en una plataforma nacional de trazabilidad y gestionar el ciclo de vida de sus residuos.'],
                ['¿Qué es el PGIRS (Plan de Gestión Integral de Residuos Sólidos) en Colombia?', 'El PGIRS es el instrumento con el que cada municipio colombiano organiza la prevención, el aprovechamiento y la disposición final de sus residuos, incluyendo la separación en la fuente bajo el código de colores nacional establecido por la Resolución 2184 de 2019.'],
                ['¿El código de colores para segregar residuos es el mismo en todos los países de Latinoamérica?', 'No. Cada país de Latinoamérica tiene su propio código de colores para segregar residuos: en Colombia rige el esquema verde-blanco-negro (Resolución 2184), mientras Perú, Chile y México manejan esquemas propios según su normativa local. Por eso conviene verificar la norma vigente en cada mercado antes de definir el etiquetado de los contenedores.'],
                ['¿Qué es la "responsabilidad extendida del productor" y por qué se está adoptando en cada vez más países de la región?', 'La responsabilidad extendida del productor es un modelo regulatorio que obliga a las empresas que fabrican o importan un producto a hacerse cargo de su gestión como residuo al final de su vida útil. Chile y México ya la aplican formalmente, y cada vez más países de la región avanzan hacia marcos similares como parte de sus estrategias de economía circular.'],
            ],
        ],
    ];

    foreach ($contenido as $i => $cat) {
        $existente = term_exists($cat['nombre'], 'faq_categoria');
        $term_id   = is_array($existente) ? (int) $existente['term_id'] : 0;

        if (0 === $term_id) {
            $nuevo = wp_insert_term($cat['nombre'], 'faq_categoria');
            if (is_wp_error($nuevo)) {
                continue;
            }
            $term_id = (int) $nuevo['term_id'];
        }

        $sin_valor = static fn (string $campo): bool =>
            ese_latam_campo_vacio(get_field($campo, 'term_' . $term_id));

        if ($sin_valor('titulo_bloque')) {
            update_field('titulo_bloque', $cat['titulo'], 'term_' . $term_id);
        }
        if ($sin_valor('orden')) {
            update_field('orden', $i + 1, 'term_' . $term_id);
        }

        foreach ($cat['faqs'] as $j => [$pregunta, $respuesta]) {
            $ya = get_posts([
                'post_type'      => 'faq',
                'post_status'    => 'any',
                'title'          => $pregunta,
                'posts_per_page' => 1,
                'fields'         => 'ids',
            ]);

            if ([] !== $ya) {
                $faq_id = (int) $ya[0];
            } else {
                $faq_id = wp_insert_post([
                    'post_type'   => 'faq',
                    'post_status' => 'publish',
                    'post_title'  => $pregunta,
                    'menu_order'  => $j + 1,
                ]);
                if (is_wp_error($faq_id) || 0 === $faq_id) {
                    continue;
                }
            }

            if ('' === trim((string) get_field('respuesta', $faq_id))) {
                update_field('respuesta', '<p>' . esc_html($respuesta) . '</p>', $faq_id);
            }

            if (! has_term('', 'faq_categoria', $faq_id)) {
                wp_set_object_terms($faq_id, [$term_id], 'faq_categoria');
            }
        }
    }

    // Copy inicial de la página (solo si está vacío).
    $pagina = get_page_by_path('preguntas-frecuentes');
    if ($pagina instanceof WP_Post) {
        $copy = [
            'faqpag_hero_titulo' => "Preguntas\n|frecuentes|",
            'faqpag_hero_desc'   => 'Resolvemos las dudas más comunes sobre nuestros productos, certificaciones y normativa en Latinoamérica.',
            'faqpag_indice'      => 'Categorías',
        ];
        foreach ($copy as $campo => $valor) {
            if (ese_latam_campo_vacio(get_field($campo, $pagina->ID))) {
                update_field($campo, $valor, $pagina->ID);
            }
        }

        // "Contactemos": se personaliza solo el botón para que lleve a
        // /contacto/; el resto del banner sigue usando el global.
        if (! get_field('contacto_override', $pagina->ID)) {
            update_field('contacto_override', 1, $pagina->ID);
            update_field('contacto_pag_cta', [
                'title'  => 'Contactar',
                'url'    => ese_latam_pagina_url('contacto', home_url('/contacto/')),
                'target' => '',
            ], $pagina->ID);
        }
    }

    update_option('ese_latam_faq_sembradas', 1);
}
// Prioridad alta: después de registrar la taxonomía y la página, y con los
// campos locales ya cargados (acf/init corre en init, prioridad 5 o antes).
add_action('init', 'ese_latam_sembrar_faq', 30);
