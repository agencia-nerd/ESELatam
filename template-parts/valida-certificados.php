<?php
/**
 * Página Certificaciones — "Valida certificados originales" (Figma
 * 3812-5504): texto + foto a la izquierda; a la derecha un mazo de
 * tarjetas (una por paso) que avanzan solas con barras de progreso, como
 * las tabs de Método Circulogic. Lo mueve tab-panels.ts (data-tabs con
 * data-tabs-autoplay): la tarjeta activa al frente y la siguiente asomando.
 *
 * Solo el paso 01 trae copy del Figma; 02–04 siguen la misma lógica
 * (reconoce → evita → verifica → valida) a la espera de texto del cliente.
 *
 * @package EseLatam
 */
declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

// Los pasos se editan en la página de Certificaciones.
$ese_vc_id = (int) get_queried_object_id();
$ese_pasos = [];

foreach ((array) ese_latam_campo('certpag_val_pasos', $ese_vc_id, []) as $ese_fila) {
    $ese_tab = trim((string) ($ese_fila['tab'] ?? ''));
    if ('' === $ese_tab) {
        continue;
    }
    $ese_pasos[] = [
        'tab'   => $ese_tab,
        'title' => (string) ($ese_fila['title'] ?? ''),
        'text'  => (string) ($ese_fila['text'] ?? ''),
        'tip'   => (string) ($ese_fila['tip'] ?? ''),
    ];
}

if ([] === $ese_pasos) {
    return;
}

$ese_vc_kicker = trim((string) ese_latam_campo('certpag_val_kicker', $ese_vc_id, ''));
$ese_vc_titulo = trim((string) ese_latam_campo('certpag_val_titulo', $ese_vc_id, ''));
$ese_vc_desc   = ese_latam_texto_rico((string) ese_latam_campo('certpag_val_desc', $ese_vc_id, ''));
$ese_vc_pasos_kicker = trim((string) ese_latam_campo('certpag_val_pasos_kicker', $ese_vc_id, ''));
$ese_vc_enlace       = ese_latam_enlace(ese_latam_campo('certpag_val_enlace', $ese_vc_id, null));
?>

<section class="valida" id="valida-certificados">
    <div class="valida__intro">
        <header data-reveal-header>
            <?php if ('' !== $ese_vc_kicker) : ?>
                <p class="type-kicker text-secondary">/ <?php echo esc_html($ese_vc_kicker); ?></p>
            <?php endif; ?>
            <?php if ('' !== $ese_vc_titulo) : ?>
                <h2 class="type-h2 uppercase">
                    <?php echo ese_latam_titulo($ese_vc_titulo, 'span', 'hl'); ?>
                </h2>
            <?php endif; ?>
            <?php if ('' !== $ese_vc_desc) : ?>
                <p class="nos-desc" data-reveal-desc><?php echo $ese_vc_desc; ?></p>
            <?php endif; ?>
        </header>
        <div class="valida__photo" data-reveal="up" data-reveal-delay="0.2">
            <img src="<?php echo esc_url(ESE_LATAM_URI . '/assets/imgs/certificaciones/valida-certificados.webp'); ?>" alt="" loading="lazy" decoding="async"
                data-parallax data-parallax-from="8" data-parallax-to="-8">
        </div>
    </div>

    <div class="valida__steps" data-tabs data-tabs-autoplay="6000">
        <div class="valida__head" data-reveal-header>
            <?php if ('' !== $ese_vc_pasos_kicker) : ?>
                <p class="type-kicker text-secondary">/ <?php echo esc_html($ese_vc_pasos_kicker); ?></p>
            <?php endif; ?>
            <?php if ('' !== $ese_vc_enlace['label']) : ?>
            <a href="<?php echo esc_url($ese_vc_enlace['href']); ?>" class="link-arrow"<?php echo ese_latam_target_attr($ese_vc_enlace['target']); ?>>
                <span class="link-arrow__text"><?php echo esc_html($ese_vc_enlace['label']); ?></span>
                <span class="link-arrow__icon" aria-hidden="true">
                    <svg width="16" height="13" viewBox="0 0 16 13" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15.7165 7.15792L9.95748 12.7276C9.77717 12.902 9.53261 13 9.2776 13C9.02259 13 8.77803 12.902 8.59772 12.7276C8.4174 12.5532 8.3161 12.3167 8.3161 12.0701C8.3161 11.8235 8.4174 11.587 8.59772 11.4126L12.7178 7.42945H0.959834C0.70527 7.42945 0.461133 7.33164 0.281129 7.15756C0.101125 6.98347 0 6.74736 0 6.50116C0 6.25496 0.101125 6.01885 0.281129 5.84476C0.461133 5.67067 0.70527 5.57287 0.959834 5.57287H12.7178L8.59932 1.58743C8.419 1.41304 8.3177 1.17652 8.3177 0.929896C8.3177 0.683272 8.419 0.44675 8.59932 0.27236C8.77963 0.0979708 9.02419 0 9.2792 0C9.5342 0 9.77877 0.0979708 9.95908 0.27236L15.7181 5.84208C15.8076 5.92843 15.8786 6.03104 15.9269 6.144C15.9753 6.25696 16.0001 6.37805 16 6.50032C15.9998 6.62259 15.9747 6.74362 15.9261 6.85647C15.8774 6.96933 15.8062 7.07177 15.7165 7.15792Z" fill="currentColor"/></svg>
                </span>
            </a>
            <?php endif; ?>
        </div>

        <div class="valida__deck" data-reveal="up" data-reveal-delay="0.15">
            <?php foreach ($ese_pasos as $ese_i => $ese_paso) : ?>
                <article class="valida__card<?php echo 0 === $ese_i ? ' is-active' : ''; ?>" data-tab-panel>
                    <div class="valida__card-head">
                        <h3 class="valida__card-title"><?php echo esc_html($ese_paso['title']); ?></h3>
                        <span class="valida__card-num" aria-hidden="true"><?php echo esc_html(str_pad((string) ($ese_i + 1), 2, '0', STR_PAD_LEFT)); ?></span>
                    </div>
                    <p class="valida__card-text"><?php echo esc_html($ese_paso['text']); ?></p>
                    <div class="valida__card-tip">
                        <div class="valida__tip">
                            <p class="valida__tip-kicker">/ <?php esc_html_e('Recomendación', 'ese-latam'); ?></p>
                            <p class="valida__tip-text"><?php echo esc_html($ese_paso['tip']); ?></p>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="valida__tabs" role="tablist" data-reveal="up" data-reveal-delay="0.3">
            <?php foreach ($ese_pasos as $ese_i => $ese_paso) : ?>
                <button type="button" role="tab"
                    class="valida__tab<?php echo 0 === $ese_i ? ' is-active' : ''; ?>"
                    aria-selected="<?php echo 0 === $ese_i ? 'true' : 'false'; ?>" data-tab>
                    <span class="valida__bar" aria-hidden="true"></span>
                    <span><?php echo esc_html(($ese_i + 1) . '. ' . $ese_paso['tab']); ?></span>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
</section>
