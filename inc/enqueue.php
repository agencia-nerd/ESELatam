<?php
/**
 * Vite integration: encola assets desde el dev server o desde el manifest de build.
 *
 * @package EseLatam
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Devuelve el manifest de Vite parseado (cacheado en memoria).
 *
 * @return array<string, array<string, mixed>>
 */
function ese_latam_get_manifest(): array {
    static $manifest = null;
    if ($manifest !== null) {
        return $manifest;
    }

    $path = ESE_LATAM_DIR . '/dist/.vite/manifest.json';
    if (! file_exists($path)) {
        $manifest = [];
        return $manifest;
    }

    $raw      = file_get_contents($path);
    $decoded  = $raw ? json_decode($raw, true) : [];
    $manifest = is_array($decoded) ? $decoded : [];

    return $manifest;
}

add_action('wp_enqueue_scripts', static function (): void {
    // Google Fonts — Plus Jakarta Sans (fuente oficial del design system)
    // Preconnect para acelerar la carga
    add_action('wp_head', static function (): void {
        echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
    }, 1);

    wp_enqueue_style(
        'ese-latam-fonts',
        'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap',
        [],
        null
    );

    if (ESE_LATAM_IS_DEV) {
        // Cliente HMR de Vite
        wp_enqueue_script(
            'vite-client',
            ESE_LATAM_VITE_SERVER . '/@vite/client',
            [],
            null,
            false
        );

        // Entry point TS (que a su vez importa el CSS)
        wp_enqueue_script(
            'ese-latam-main',
            ESE_LATAM_VITE_SERVER . '/src/ts/main.ts',
            [],
            null,
            true
        );

        return;
    }

    // ---------- Producción ----------
    $manifest = ese_latam_get_manifest();
    $entry    = $manifest['src/ts/main.ts'] ?? null;

    if (! is_array($entry)) {
        return;
    }

    // Importante: version null (sin ?ver=) — los archivos ya llevan hash en el
    // nombre, y un query string haría que los chunks dinámicos de Vite importen
    // el módulo main con otra URL, ejecutándolo dos veces (rompe ScrollTrigger).
    if (isset($entry['file'])) {
        wp_enqueue_script(
            'ese-latam-main',
            ESE_LATAM_URI . '/dist/' . $entry['file'],
            [],
            null,
            true
        );
    }

    if (! empty($entry['css']) && is_array($entry['css'])) {
        foreach ($entry['css'] as $index => $cssFile) {
            wp_enqueue_style(
                'ese-latam-style-' . $index,
                ESE_LATAM_URI . '/dist/' . $cssFile,
                [],
                null
            );
        }
    }
});

/**
 * Marca los scripts de Vite y del entry como type="module".
 */
add_filter('script_loader_tag', static function (string $tag, string $handle): string {
    $module_handles = ['vite-client', 'ese-latam-main'];
    if (! in_array($handle, $module_handles, true)) {
        return $tag;
    }
    return str_replace('<script ', '<script type="module" ', $tag);
}, 10, 2);

// Preload del fondo del hero de la portada: es lo primero que se ve y, sin esto,
// el navegador recién lo descubre al parsear el <body>. Mismo src/srcset que
// el <img> de front-page.php (ambos salen de ese_latam_hero_fondo()), así que
// la descarga se reutiliza en vez de duplicarse.
add_action('wp_head', static function (): void {
    if (! is_front_page()) {
        return;
    }

    $fondo = ese_latam_hero_fondo();

    printf(
        '<link rel="preload" as="image" href="%s"%s fetchpriority="high">' . "\n",
        esc_url($fondo['src']),
        '' !== $fondo['srcset'] ? ' imagesrcset="' . esc_attr($fondo['srcset']) . '" imagesizes="100vw"' : ''
    );
}, 2);
