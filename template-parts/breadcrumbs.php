<?php
/**
 * Breadcrumb reutilizable: Inicio + crumbs intermedios opcionales + item
 * actual (sin link). Reemplaza el markup que antes se repetía copiado en
 * hero-interno.php, ficha-hero.php, casos-hero.php, sectores-hero.php,
 * page-nosotros.php, page-distribuidores.php, catalogo-banner.php y
 * producto-hero.php.
 *
 * Estilo base (`.crumbs`) para hero oscuro tipo foto de fondo (texto gris
 * que aclara al hover); variante `.crumbs--light` para fondos con imagen a
 * pantalla completa donde el crumb va en blanco (catálogo, ficha de
 * producto). El resto de clases en `class` son solo hooks de posición/JS
 * del contexto que lo usa (ej. `producto-hero__crumb`, `sec-hero__crumb`).
 *
 * @param array{
 *     items?: list<array{label: string, url: string}>,
 *     current?: string,
 *     class?: string,
 *     attrs?: string,
 *     home_icon?: bool    Ícono de casa antes de "Inicio" (hero claro: Sectores y Preguntas frecuentes)
 * } $args
 *
 * @package EseLatam
 */
declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

$ese_bc = wp_parse_args($args ?? [], [
    'items'     => [],
    'current'   => '',
    'class'     => '',
    'attrs'     => '',
    'home_icon' => false,
]);

$ese_bc_class = trim('crumbs ' . (string) $ese_bc['class']);

$ese_bc_sep = '<svg width="4" height="7" viewBox="0 0 4 7" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M3.86395 3.8233L0.788805 6.86609C0.70215 6.95183 0.58462 7 0.462071 7C0.339522 7 0.221993 6.95183 0.135337 6.86609C0.0486823 6.78034 0 6.66405 0 6.54279C0 6.42153 0.0486823 6.30524 0.135337 6.21949L2.88413 3.50038L0.136106 0.780507C0.0931991 0.738051 0.059163 0.687648 0.0359418 0.632177C0.0127205 0.576706 0.000768656 0.517252 0.000768656 0.45721C0.000768656 0.397169 0.0127205 0.337715 0.0359418 0.282243C0.059163 0.226772 0.0931991 0.17637 0.136106 0.133914C0.179014 0.0914579 0.229952 0.0577801 0.286013 0.0348031C0.342074 0.0118261 0.40216 0 0.46284 0C0.52352 0 0.583606 0.0118261 0.639667 0.0348031C0.695728 0.0577801 0.746666 0.0914579 0.789574 0.133914L3.86471 3.1767C3.90767 3.21916 3.94173 3.26958 3.96494 3.32509C3.98816 3.38059 4.00007 3.44009 4 3.50016C3.99993 3.56023 3.98787 3.61969 3.96453 3.67515C3.94118 3.7306 3.907 3.78094 3.86395 3.8233Z" fill="currentColor"/></svg>';
?>
<nav class="<?php echo esc_attr($ese_bc_class); ?>" aria-label="<?php esc_attr_e('Ruta de navegación', 'ese-latam'); ?>"<?php echo '' !== $ese_bc['attrs'] ? ' ' . $ese_bc['attrs'] : ''; ?>>
    <a href="<?php echo esc_url(home_url('/')); ?>">
        <?php if ($ese_bc['home_icon']) : ?>
            <svg width="11" height="11" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M5.38.23a.95.95 0 0 1 1.24 0l5.05 4.33c.4.35.16 1-.37 1H10.5v5.49c0 .53-.43.95-.95.95H7.6V8.6a.6.6 0 0 0-.6-.6H5a.6.6 0 0 0-.6.6V12H2.45a.95.95 0 0 1-.95-.95V5.56H.7c-.53 0-.77-.65-.37-1L5.38.23Z" fill="currentColor"/></svg>
        <?php endif; ?>
        <?php esc_html_e('Inicio', 'ese-latam'); ?>
    </a>
    <?php foreach ($ese_bc['items'] as $ese_bc_item) : ?>
        <span class="crumbs__sep" aria-hidden="true"><?php echo $ese_bc_sep; ?></span>
        <a href="<?php echo esc_url($ese_bc_item['url']); ?>"><?php echo esc_html($ese_bc_item['label']); ?></a>
    <?php endforeach; ?>
    <?php if ('' !== $ese_bc['current']) : ?>
        <span class="crumbs__sep" aria-hidden="true"><?php echo $ese_bc_sep; ?></span>
        <span class="crumbs__current" aria-current="page"><?php echo esc_html($ese_bc['current']); ?></span>
    <?php endif; ?>
</nav>
