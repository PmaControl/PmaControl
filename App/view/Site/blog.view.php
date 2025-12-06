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

$posts = [
    ['title' => ['fr' => 'Control the uncontrollable : anatomie d’un crash DC', 'en' => 'Control the uncontrollable: anatomy of a DC crash'], 'desc' => ['fr' => '14 minutes pour relancer un datacenter complet – timeline et lessons learned.', 'en' => '14 minutes to recover a full datacenter – timeline & lessons learned.']],
    ['title' => ['fr' => 'Pourquoi 80 % des incidents SQL sont évitables', 'en' => 'Why 80% of SQL incidents are avoidable'], 'desc' => ['fr' => 'Index manquants, backups non testés, réplications silencieuses : notre analyse.', 'en' => 'Missing indexes, untested backups, silent replication drifts – our analysis.']],
    ['title' => ['fr' => 'ProxySQL meltdown → reroute live', 'en' => 'ProxySQL meltdown → live reroute'], 'desc' => ['fr' => 'Cas réel anonymisé : comment Alex a réécrit les règles en 30 secondes.', 'en' => 'Real anonymised case: how Alex rewrote routing in 30 seconds.']],
];
?>
<section class="page-hero">
    <div>
        <p class="eyebrow"><?php pmac_site_lang(['fr' => 'Blog / Insights', 'en' => 'Blog / Insights']); ?></p>
        <h1><?php pmac_site_lang(['fr' => 'Performance SQL, market vision, case studies', 'en' => 'SQL performance, market vision, case studies']); ?></h1>
    </div>
</section>

<section class="panel">
    <div class="cards">
        <?php foreach ($posts as $post): ?>
            <article>
                <h3><?php pmac_site_lang($post['title']); ?></h3>
                <?php pmac_site_lang($post['desc'], 'p', 'lang-block'); ?>
                <a class="btn btn-secondary" href="#"><?php pmac_site_lang(['fr' => 'Lire l’article', 'en' => 'Read the story']); ?></a>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="panel highlight">
    <header>
        <h2><?php pmac_site_lang(['fr' => 'Success Stories', 'en' => 'Success Stories']); ?></h2>
        <p><?php pmac_site_lang(['fr' => 'Avant / après 🔥 : découvrez nos cas les plus marquants.', 'en' => 'Before / after 🔥 – discover our most striking cases.']); ?></p>
    </header>
    <div class="cta-actions">
        <a class="btn btn-primary" href="<?= htmlspecialchars((defined('LINK') ? LINK : '/').'site/success/', ENT_QUOTES) ?>"><?php pmac_site_lang(['fr' => 'Voir les Success Stories', 'en' => 'See Success Stories']); ?></a>
    </div>
</section>
