<?php
/**
 * Red de distribuidores — una sola fuente de datos para la sección del home
 * (front-page.php: globo 3D + riel de países) y la página "Encuentra un
 * distribuidor" (page-distribuidores.php, Figma 3952-9273).
 *
 * Los datos salen de dos módulos (inc/modulos.php): cada empresa es una
 * entrada de "Distribuidores" y su país un término de la taxonomía "Países",
 * que además guarda las coordenadas que el globo 3D necesita. Sin países
 * cargados devuelve una lista vacía y ambas vistas ocultan su sección.
 *
 * @package EseLatam
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Países con distribuidor, en el orden en que estén cargados.
 *
 * Cada `item` puede traer: name, city, desc, rep, phone, email, address,
 * web, whatsapp (solo dígitos, con código de país) y logo (URL absoluta).
 * Todo salvo `name` es opcional: la vista oculta lo que falte.
 *
 * Un país sin empresas cargadas se muestra igual —es un punto del mapa— con
 * la ficha "próximamente" que se redacta en la misma página de opciones; si
 * ese texto está vacío, el país aparece sin fichas.
 *
 * @return list<array{slug: string, name: string, lat: float, lng: float, iso: string, items: list<array<string, string>>}>
 */
function ese_latam_distribuidores(): array {
    static $cache = null;
    if (null !== $cache) {
        return $cache;
    }

    $pendiente = ese_latam_distribuidor_pendiente();
    $paises    = [];

    $terminos = get_terms([
        'taxonomy'   => 'pais',
        'hide_empty' => false,
        'orderby'    => 'term_order',
    ]);

    if (is_wp_error($terminos)) {
        return $cache = [];
    }

    foreach ($terminos as $termino) {
        $items = [];

        foreach (ese_latam_modulo_entradas_de_pais($termino->term_id) as $empresa) {
            $items[] = array_filter([
                'name'     => get_the_title($empresa->ID),
                'city'     => trim((string) ese_latam_campo('ciudad', $empresa->ID, '')),
                'desc'     => trim((string) ese_latam_campo('descripcion', $empresa->ID, '')),
                'rep'      => trim((string) ese_latam_campo('representante', $empresa->ID, '')),
                'phone'    => trim((string) ese_latam_campo('telefono', $empresa->ID, '')),
                'email'    => trim((string) ese_latam_campo('email', $empresa->ID, '')),
                'address'  => trim((string) ese_latam_campo('direccion', $empresa->ID, '')),
                'web'      => trim((string) ese_latam_campo('web', $empresa->ID, '')),
                'whatsapp' => preg_replace('/\\D+/', '', (string) ese_latam_campo('whatsapp', $empresa->ID, '')) ?? '',
                'logo'     => (string) (get_the_post_thumbnail_url($empresa->ID, 'medium') ?: ''),
            ], static fn (string $v): bool => '' !== $v);
        }

        if ([] === $items && [] !== $pendiente) {
            $items[] = $pendiente;
        }

        $paises[] = [
            'slug'  => $termino->slug,
            'name'  => $termino->name,
            'lat'   => (float) ese_latam_campo_termino('lat', $termino->term_id),
            'lng'   => (float) ese_latam_campo_termino('lng', $termino->term_id),
            'iso'   => ese_latam_pais_iso($termino),
            'items' => $items,
        ];
    }

    return $cache = $paises;
}

/**
 * Código ISO 3166-1 numérico del país (3 dígitos, con ceros a la izquierda):
 * es la clave con la que el globo encuentra su contorno en el mapa de
 * fronteras (assets/data/countries-110m.json). Sale del campo del término y,
 * vacío, de los países de Latinoamérica que ya conocemos por su slug.
 * Cadena vacía si no hay forma de saberlo: el globo solo pone el punto.
 */
function ese_latam_pais_iso(WP_Term $termino): string {
    $iso = preg_replace('/\D+/', '', (string) ese_latam_campo_termino('iso', $termino->term_id)) ?? '';
    if ('' !== $iso) {
        return str_pad($iso, 3, '0', STR_PAD_LEFT);
    }

    $conocidos = [
        'argentina'            => '032',
        'bolivia'              => '068',
        'brasil'               => '076',
        'chile'                => '152',
        'colombia'             => '170',
        'costa-rica'           => '188',
        'cuba'                 => '192',
        'ecuador'              => '218',
        'el-salvador'          => '222',
        'guatemala'            => '320',
        'honduras'             => '340',
        'mexico'               => '484',
        'nicaragua'            => '558',
        'panama'               => '591',
        'paraguay'             => '600',
        'peru'                 => '604',
        'puerto-rico'          => '630',
        'republica-dominicana' => '214',
        'uruguay'              => '858',
        'venezuela'            => '862',
    ];

    $clave = sanitize_title(remove_accents($termino->name));

    return $conocidos[$termino->slug] ?? $conocidos[$clave] ?? '';
}

/**
 * Distribuidores publicados de un país, en el orden del admin.
 *
 * @return list<WP_Post>
 */
function ese_latam_modulo_entradas_de_pais(int $term_id): array {
    return get_posts([
        'post_type'      => 'distribuidor',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order title',
        'order'          => 'ASC',
        'tax_query'      => [[
            'taxonomy' => 'pais',
            'field'    => 'term_id',
            'terms'    => $term_id,
        ]],
    ]);
}

/**
 * Valor de un campo de la taxonomía "Países" (las coordenadas del globo).
 *
 * @return mixed
 */
function ese_latam_campo_termino(string $name, int $term_id) {
    return ese_latam_campos_activos() ? get_field($name, 'term_' . $term_id) : null;
}

/**
 * Ficha "todavía sin distribuidor" para los países del mapa que aún no tienen
 * empresas cargadas. Se redacta en ESE Latam → Distribuidores; vacía, esos
 * países aparecen sin ninguna ficha.
 *
 * @return array{name?: string, address?: string}
 */
function ese_latam_distribuidor_pendiente(): array {
    $nombre = trim((string) ese_latam_opcion('distribuidores_pendiente_nombre', ''));
    $texto  = trim((string) ese_latam_opcion('distribuidores_pendiente_texto', ''));

    if ('' === $nombre && '' === $texto) {
        return [];
    }

    return array_filter([
        'name'    => $nombre,
        'address' => $texto,
    ], static fn (string $v): bool => '' !== $v);
}

/**
 * URL de la página "Encuentra un distribuidor" (la crea inc/paginas.php);
 * mientras no exista, cae a la sección de distribuidores de la home.
 */
function ese_latam_distribuidores_url(): string {
    return ese_latam_pagina_url('distribuidores', home_url('/#distribuidores'));
}

/**
 * Lista plana de distribuidores (un registro por empresa, con su país) para
 * la página del buscador. Cada registro suma `pais`, `pais_slug`, `lugar`
 * ("Lima, Perú"), `pendiente` (true si es placeholder), `mapa` (query para
 * el embed de Google Maps) y `buscar` (texto normalizado para filtrar).
 *
 * @return list<array<string, mixed>>
 */
function ese_latam_distribuidores_planos(): array {
    $lista = [];

    foreach (ese_latam_distribuidores() as $pais) {
        foreach ($pais['items'] as $item) {
            $city      = (string) ($item['city'] ?? '');
            $pendiente = empty($item['rep']) && empty($item['phone']) && empty($item['email']);
            $lugar     = '' !== $city ? $city . ', ' . $pais['name'] : $pais['name'];

            $lista[] = $item + [
                'city'      => $city,
                'pais'      => $pais['name'],
                'pais_slug' => $pais['slug'],
                'lugar'     => $lugar,
                'pendiente' => $pendiente,
                'mapa'      => $pendiente ? $pais['name'] : ($item['address'] ?? '') . ', ' . $pais['name'],
                'buscar'    => mb_strtolower(implode(' ', array_filter([
                    $item['name'] ?? '', $city, $pais['name'], $item['rep'] ?? '', $item['address'] ?? '', $item['desc'] ?? '',
                ]))),
            ];
        }
    }

    return $lista;
}
