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

$labs = [
    ['title' => ['fr' => 'Failover live replay', 'en' => 'Live failover replay'], 'desc' => ['fr' => 'Rejouez la perte d’un nœud Galera et observez la reprise orchestrée.', 'en' => 'Replay a Galera node loss and watch the orchestrated recovery.']],
    ['title' => ['fr' => 'Topologies ProxySQL multi-tiers', 'en' => 'Multi-tier ProxySQL topologies'], 'desc' => ['fr' => 'Visualisation temps réel, injection de règles dynamiques.', 'en' => 'Real-time visualisation, dynamic rule injection.']],
    ['title' => ['fr' => 'Incidents anonymisés', 'en' => 'Anonymised incidents'], 'desc' => ['fr' => 'Timeline, métriques, actions IA + humains.', 'en' => 'Timeline, metrics, AI + human actions.']],
];
?>
<section class="page-hero">
    <div>
        <p class="eyebrow"><?php pmac_site_lang(['fr' => 'Internal Lab', 'en' => 'Internal Lab']); ?></p>
        <h1><?php pmac_site_lang(['fr' => 'Internal Lab Showcase', 'en' => 'Internal Lab showcase']); ?></h1>
    </div>
</section>

<section class="panel">
    <div class="cards">
        <?php foreach ($labs as $lab): ?>
            <article>
                <h3><?php pmac_site_lang($lab['title']); ?></h3>
                <?php pmac_site_lang($lab['desc'], 'p', 'lang-block'); ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>
