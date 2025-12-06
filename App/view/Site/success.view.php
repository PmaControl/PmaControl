<?php
if (!function_exists('pmac_site_lang')) {
    function pmac_site_lang(array $texts = [], string $tag = 'span', string $class = '')
    {
        $fr        = htmlspecialchars($texts['fr'] ?? '', ENT_QUOTES);
        $en        = htmlspecialchars($texts['en'] ?? '', ENT_QUOTES);
        $classFr   = trim('lang lang-fr '.$class);
        $classEn   = trim('lang lang-en '.$class);
        $tag       = $tag ?: 'span';
        echo "<{$tag} class=\"{$classFr}\" lang=\"fr\">{$fr}</{$tag}>";
        echo "<{$tag} class=\"{$classEn}\" lang=\"en\">{$en}</{$tag}>";
    }
}

$stories = [
    [
        'before' => ['fr' => 'Avant : incidents répétés, plan de reprise jamais testé.', 'en' => 'Before: repeated incidents, DR plan never tested.'],
        'after'  => ['fr' => 'Après : Zero downtime promise tenue, recovery en 14 min.', 'en' => 'After: Zero downtime promise achieved, 14-min recovery.'],
    ],
    [
        'before' => ['fr' => 'Avant : Galera mass desync, latences réseau inconnues.', 'en' => 'Before: Galera mass desync, unknown network latency.'],
        'after'  => ['fr' => 'Après : Alex + Nina ont équilibré le routage et anticipé les pics.', 'en' => 'After: Alex + Nina rebalanced routing and predicted spikes.'],
    ],
    [
        'before' => ['fr' => 'Avant : ProxySQL meltdown, règles statiques.', 'en' => 'Before: ProxySQL meltdown, static rules.'],
        'after'  => ['fr' => 'Après : Reroute live, règles dynamiques en 30 secondes.', 'en' => 'After: Live reroute, dynamic rules in 30 seconds.'],
    ],
];
?>
<section class="page-hero">
    <div>
        <p class="eyebrow"><?php pmac_site_lang(['fr' => 'Avant / Après', 'en' => 'Before / After']); ?></p>
        <h1><?php pmac_site_lang(['fr' => 'Success Stories', 'en' => 'Success Stories']); ?></h1>
    </div>
</section>

<section class="panel">
    <div class="cards two-columns">
        <?php foreach ($stories as $story): ?>
            <article>
                <h3><?php pmac_site_lang(['fr' => 'Avant PmaControl', 'en' => 'Before PmaControl']); ?></h3>
                <?php pmac_site_lang($story['before'], 'p', 'lang-block'); ?>
                <h3><?php pmac_site_lang(['fr' => 'Après PmaControl', 'en' => 'After PmaControl']); ?></h3>
                <?php pmac_site_lang($story['after'], 'p', 'lang-block'); ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>
