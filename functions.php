<?php
/**
 * ESE Latam theme — bootstrap
 *
 * @package EseLatam
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

define('ESE_LATAM_VERSION', '0.1.0');
define('ESE_LATAM_DIR', get_template_directory());
define('ESE_LATAM_URI', get_template_directory_uri());

// Modo dev: si existe .vite-dev en la raíz del theme y estamos en un entorno local, se usa el server de Vite.
// En producción, se leen los assets compilados desde dist/.vite/manifest.json.
$is_local_env = in_array( wp_get_environment_type(), ['local', 'development'], true ) || strpos( $_SERVER['HTTP_HOST'] ?? '', '.local' ) !== false || strpos( $_SERVER['HTTP_HOST'] ?? '', 'localhost' ) !== false;
define('ESE_LATAM_IS_DEV', file_exists(ESE_LATAM_DIR . '/.vite-dev') && $is_local_env);
define('ESE_LATAM_VITE_SERVER', 'http://localhost:5173');

require_once ESE_LATAM_DIR . '/inc/setup.php';
require_once ESE_LATAM_DIR . '/inc/menus.php';
require_once ESE_LATAM_DIR . '/inc/customizer.php';
require_once ESE_LATAM_DIR . '/inc/enqueue.php';
require_once ESE_LATAM_DIR . '/inc/pcf.php';
require_once ESE_LATAM_DIR . '/inc/pcf-home.php';
require_once ESE_LATAM_DIR . '/inc/pcf-globales.php';
require_once ESE_LATAM_DIR . '/inc/contenido.php';
require_once ESE_LATAM_DIR . '/inc/template-tags.php';
require_once ESE_LATAM_DIR . '/inc/imagenes-catalogo.php';
require_once ESE_LATAM_DIR . '/inc/cpt-productos.php';
require_once ESE_LATAM_DIR . '/inc/pcf-productos.php';
require_once ESE_LATAM_DIR . '/inc/modulos.php';
require_once ESE_LATAM_DIR . '/inc/pcf-modulos.php';
require_once ESE_LATAM_DIR . '/inc/cpt-sectores.php';
require_once ESE_LATAM_DIR . '/inc/pcf-sectores.php';
require_once ESE_LATAM_DIR . '/inc/pcf-impacto.php';
require_once ESE_LATAM_DIR . '/inc/pcf-certificaciones.php';
require_once ESE_LATAM_DIR . '/inc/pcf-nosotros.php';
require_once ESE_LATAM_DIR . '/inc/cpt-casos.php';
require_once ESE_LATAM_DIR . '/inc/pcf-casos.php';
require_once ESE_LATAM_DIR . '/inc/blog.php';
require_once ESE_LATAM_DIR . '/inc/pcf-blog.php';
require_once ESE_LATAM_DIR . '/inc/distribuidores.php';
require_once ESE_LATAM_DIR . '/inc/pcf-legal.php';
require_once ESE_LATAM_DIR . '/inc/paginas.php';
require_once ESE_LATAM_DIR . '/inc/contacto.php';
require_once ESE_LATAM_DIR . '/inc/pcf-contacto.php';
require_once ESE_LATAM_DIR . '/inc/preguntas-frecuentes.php';
require_once ESE_LATAM_DIR . '/inc/pcf-preguntas-frecuentes.php';
