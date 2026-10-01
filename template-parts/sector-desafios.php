<?php
/**
 * Single de sector — "Desafíos en la gestión urbana" (Figma 3583-4927):
 * header + par de pills que alternan entre "Dolores y brechas" y "Alivio
 * ESE Latam", cada una con su grilla bento de 6 tarjetas (4 grises + 2 con
 * foto). En "Alivio" las 4 grises pasan a azul/naranja/verde y el globo se
 * cambia por el tacho azul (solo CSS). El cambio lo maneja tab-panels.ts
 * (data-tabs).
 *
 * @param array{
 *     kicker?: string, title?: string, desc?: string,
 *     dolores?: list<array<string, string>>, alivios?: list<array<string, string>>
 * } $args Cada tarjeta: title, sub, desc y opcionalmente img (URL absoluta).
 *   El orden de las 6 define su lugar en la grilla (a…f).
 *
 * @package EseLatam
 */
declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

$ese_d = wp_parse_args($args ?? [], [
    'kicker'        => '',
    'title'         => '',
    'desc'          => '',
    'dolores'       => [],
    'alivios'       => [],
    'label_dolores' => '',
    'label_alivios' => '',
]);

if (empty($ese_d['dolores'])) {
    return;
}

$ese_img   = static fn (string $file): string => ESE_LATAM_URI . '/assets/imgs/' . $file;
$ese_areas = ['a', 'b', 'c', 'd', 'e', 'f'];

$ese_paneles = [
    ['key' => 'dolores', 'label' => $ese_d['label_dolores'], 'cards' => $ese_d['dolores']],
    ['key' => 'alivio',  'label' => $ese_d['label_alivios'], 'cards' => $ese_d['alivios']],
];
$ese_paneles = array_values(array_filter($ese_paneles, static fn (array $p): bool => ! empty($p['cards'])));
?>

<section class="desafios" id="desafios" data-tabs>
    <header class="desafios__header" data-reveal-header>
        <div>
            <p class="type-kicker text-secondary">/ <?php echo esc_html($ese_d['kicker']); ?></p>
            <h2 class="type-h2 uppercase">
                <?php echo ese_latam_titulo($ese_d['title'], 'span', 'hl'); ?>
            </h2>
        </div>
        <?php if ('' !== $ese_d['desc']) : ?>
            <p class="nos-desc" data-reveal-desc><?php echo wp_kses($ese_d['desc'], ['span' => ['class' => []], 'strong' => []]); ?></p>
        <?php endif; ?>
    </header>

    <?php if (count($ese_paneles) > 1) : ?>
        <div class="desafios__toggle" data-reveal="up">
            <div class="desafios__pills" role="tablist">
                <?php foreach ($ese_paneles as $ese_i => $ese_panel) : ?>
                    <button type="button" role="tab"
                        class="desafios__pill desafios__pill--<?php echo esc_attr($ese_panel['key']); ?><?php echo 0 === $ese_i ? ' is-active' : ''; ?>"
                        aria-selected="<?php echo 0 === $ese_i ? 'true' : 'false'; ?>" data-tab>
                        <?php echo esc_html($ese_panel['label']); ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="desafios__panels">
        <?php foreach ($ese_paneles as $ese_i => $ese_panel) : ?>
            <div class="desafios__panel desafios__panel--<?php echo esc_attr($ese_panel['key']); ?><?php echo 0 === $ese_i ? ' is-active' : ''; ?>" data-tab-panel>
                <ul class="desafios__grid"<?php echo 0 === $ese_i ? ' data-reveal-stagger' : ''; ?>>
                    <?php foreach (array_slice($ese_panel['cards'], 0, 6) as $ese_j => $ese_card) :
                        $ese_photo = ! empty($ese_card['img']);
                        $ese_class = 'desafio desafio--' . $ese_areas[$ese_j]
                            . ($ese_photo ? ' desafio--photo' : '')
                            . ('b' === $ese_areas[$ese_j] ? ' desafio--tall' : '');
                        ?>
                        <li class="<?php echo esc_attr($ese_class); ?>">
                            <?php if ($ese_photo) : ?>
                                <img class="desafio__img" src="<?php echo esc_url($ese_card['img']); ?>" alt="" loading="lazy" decoding="async">
                                <span class="desafio__shade" aria-hidden="true"></span>
                            <?php endif; ?>
                            <h3 class="desafio__title"><?php echo esc_html($ese_card['title']); ?></h3>
                            <p class="desafio__sub"><?php echo esc_html($ese_card['sub']); ?></p>
                            <p class="desafio__desc"><?php echo esc_html($ese_card['desc']); ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>

        <?php // Flota el contenedor, no cada foto: así el globo (dolores) y el tacho (alivio) se funden en el mismo punto. ?>
        <span class="desafios__globo" aria-hidden="true" data-float data-float-distance="10" data-float-duration="4">
            <img class="desafios__globo-img desafios__globo-img--dolores" src="<?php echo esc_url($ese_img('sectores/desafio-globo.webp')); ?>" alt=""
                width="600" height="720" loading="lazy" decoding="async">
            <?php if (count($ese_paneles) > 1) : ?>
                <img class="desafios__globo-img desafios__globo-img--alivio" src="<?php echo esc_url($ese_img('sectores/tacho-azul.webp')); ?>" alt=""
                    width="622" height="678" loading="lazy" decoding="async">
            <?php endif; ?>
        </span>
    </div>
</section>
