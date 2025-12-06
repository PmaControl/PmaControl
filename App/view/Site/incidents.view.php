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

$incidents = [
    [
        'title' => ['fr' => 'Crash datacenter → recovery 14 min', 'en' => 'Datacenter crash → 14 min recovery'],
        'detail'=> ['fr' => 'Backup auto-test, failover orchestré, redémarrage progressif.', 'en' => 'Auto-tested backup, orchestrated failover, progressive restart.'],
    ],
    [
        'title' => ['fr' => 'Galera mass desync → auto fix', 'en' => 'Galera mass desync → auto fix'],
        'detail'=> ['fr' => 'Léo + Nina ont détecté la dérive 6 min avant les clients.', 'en' => 'Léo + Nina detected drift 6 min before clients felt it.'],
    ],
    [
        'title' => ['fr' => 'ProxySQL meltdown → reroute live', 'en' => 'ProxySQL meltdown → live reroute'],
        'detail'=> ['fr' => 'Alex a réécrit les règles et Marina+ a diffusé la timeline.', 'en' => 'Alex rewrote routing rules, Marina+ broadcast the timeline.'],
    ],
];
?>
<section class="page-hero">
    <div>
        <p class="eyebrow"><?php pmac_site_lang(['fr' => 'Incidents Hall of Fame', 'en' => 'Incidents Hall of Fame']); ?></p>
        <h1><?php pmac_site_lang(['fr' => 'Incidents résolus', 'en' => 'Resolved incidents']); ?></h1>
    </div>
</section>

<section class="panel">
    <div class="cards">
        <?php foreach ($incidents as $incident): ?>
            <article>
                <h3><?php pmac_site_lang($incident['title']); ?></h3>
                <?php pmac_site_lang($incident['detail'], 'p', 'lang-block'); ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>
