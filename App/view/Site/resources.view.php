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

$resources = [
    ['title' => ['fr' => 'Guide « MySQL crash recovery complet »', 'en' => 'Guide “Complete MySQL crash recovery”'], 'type' => 'PDF'],
    ['title' => ['fr' => 'Templates SLA / DPA SQL', 'en' => 'SLA / DPA SQL templates'], 'type' => 'Doc'],
    ['title' => ['fr' => 'Livre blanc « 80 % incidents SQL viennent de X »', 'en' => 'White paper “80% of SQL incidents come from X”'], 'type' => 'Whitepaper'],
    ['title' => ['fr' => 'Benchmark labs Galera / ProxySQL', 'en' => 'Galera / ProxySQL benchmark labs'], 'type' => 'Video + doc'],
    ['title' => ['fr' => 'Dashboard preview interactif', 'en' => 'Interactive dashboard preview'], 'type' => 'Live demo'],
    ['title' => ['fr' => 'Labs sandbox Docker / K8S', 'en' => 'Docker / K8S sandbox labs'], 'type' => 'Hands-on'],
];
?>
<section class="page-hero">
    <div>
        <p class="eyebrow"><?php pmac_site_lang(['fr' => 'Ressources & labs', 'en' => 'Resources & labs']); ?></p>
        <h1><?php pmac_site_lang(['fr' => 'Ressources PmaControl', 'en' => 'PmaControl resources']); ?></h1>
    </div>
</section>

<section class="panel">
    <div class="cards two-columns">
        <?php foreach ($resources as $resource): ?>
            <article>
                <span class="badge"><?= htmlspecialchars($resource['type'], ENT_QUOTES) ?></span>
                <h3><?php pmac_site_lang($resource['title']); ?></h3>
                <a class="btn btn-secondary" href="#"><?php pmac_site_lang(['fr' => 'Télécharger', 'en' => 'Download']); ?></a>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="panel highlight">
    <header>
        <h2><?php pmac_site_lang(['fr' => 'Internal Lab Showcase', 'en' => 'Internal Lab Showcase']); ?></h2>
        <p><?php pmac_site_lang(['fr' => 'Schémas anonymisés, clusters Galera extrêmes, rejouement de failover.', 'en' => 'Anonymised schemas, extreme Galera clusters, failover replays.']); ?></p>
    </header>
    <div class="cta-actions">
        <a class="btn btn-primary" href="<?= htmlspecialchars((defined('LINK') ? LINK : '/').'site/lab/', ENT_QUOTES) ?>"><?php pmac_site_lang(['fr' => 'Visiter le Lab', 'en' => 'Visit the Lab']); ?></a>
    </div>
</section>
