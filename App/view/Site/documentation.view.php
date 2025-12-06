<?php
$baseLink = defined('LINK') ? LINK : '/';

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

$docs = [
    ['title' => ['fr' => 'Guides d’installation', 'en' => 'Install guides'], 'desc' => ['fr' => 'SaaS, On-Prem, ProxySQL, Galera.', 'en' => 'SaaS, On-Prem, ProxySQL, Galera.']],
    ['title' => ['fr' => 'API & webhooks', 'en' => 'API & webhooks'], 'desc' => ['fr' => 'Endpoints REST, exemples cURL & Postman.', 'en' => 'REST endpoints, cURL & Postman samples.']],
    ['title' => ['fr' => 'Playbooks incidents', 'en' => 'Incident playbooks'], 'desc' => ['fr' => 'Crash recovery, failover live, upgrade zero downtime.', 'en' => 'Crash recovery, live failover, zero-downtime upgrades.']],
    ['title' => ['fr' => 'Guides DBA / Dev', 'en' => 'DBA / Dev guides'], 'desc' => ['fr' => 'Intégration CI/CD, tests, qualité SQL.', 'en' => 'CI/CD integration, testing, SQL quality.']],
];
?>
<section class="page-hero">
    <div>
        <p class="eyebrow"><?php pmac_site_lang(['fr' => 'Toute la connaissance, accessible', 'en' => 'All knowledge, accessible']); ?></p>
        <h1><?php pmac_site_lang(['fr' => 'Documentation PmaControl', 'en' => 'PmaControl documentation']); ?></h1>
        <p class="lead"><?php pmac_site_lang(['fr' => 'Guides, API, FAQ techniques, exports d’exemples et dashboards à réutiliser.', 'en' => 'Guides, APIs, technical FAQ, reusable exports and dashboards.']); ?></p>
    </div>
</section>

<section class="panel">
    <header>
        <h2><?php pmac_site_lang(['fr' => 'Docs hub', 'en' => 'Docs hub']); ?></h2>
    </header>
    <div class="doc-grid">
        <?php foreach ($docs as $doc): ?>
            <article>
                <h3><?php pmac_site_lang($doc['title']); ?></h3>
                <?php pmac_site_lang($doc['desc'], 'p', 'lang-block'); ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="panel">
    <header>
        <h2><?php pmac_site_lang(['fr' => 'Templates prêts à l’emploi', 'en' => 'Ready-to-use templates']); ?></h2>
    </header>
    <ul class="pill-list">
        <li>SLA template</li>
        <li>DPA SQL</li>
        <li>Checklist upgrade</li>
        <li>Modèle de rapport incident</li>
        <li>Dashboard Grafana JSON</li>
    </ul>
</section>

<section class="panel highlight">
    <header>
        <h2><?php pmac_site_lang(['fr' => 'Besoin d’un guide supplémentaire ?', 'en' => 'Need an extra guide?']); ?></h2>
        <p><?php pmac_site_lang(['fr' => 'Envoyez-nous votre cas, on produit un how-to dédié pour votre équipe.', 'en' => 'Send us your use case, we produce a dedicated how-to for your team.']); ?></p>
    </header>
    <div class="cta-actions">
        <a class="btn btn-primary" href="<?= htmlspecialchars($baseLink.'site/contact/', ENT_QUOTES) ?>"><?php pmac_site_lang(['fr' => 'Contacter la doc team', 'en' => 'Contact the doc team']); ?></a>
    </div>
</section>
