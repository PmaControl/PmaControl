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
?>
<section class="page-hero">
    <div>
        <p class="eyebrow"><?php pmac_site_lang(['fr' => 'Votre cerveau SQL augmenté', 'en' => 'Your amplified SQL brain']); ?></p>
        <h1>Max+ Platform</h1>
        <p class="lead"><?php pmac_site_lang(['fr' => 'Analyse toutes les requêtes, classe les priorités, propose index et rewrite SQL, simule l’impact avant exécution.', 'en' => 'Scans every query, ranks priorities, proposes indexes & rewrites, simulates impact before execution.']); ?></p>
    </div>
</section>

<section class="panel">
    <header><h2><?php pmac_site_lang(['fr' => 'Fonctionnalités clés', 'en' => 'Key capabilities']); ?></h2></header>
    <ul class="pill-list">
        <li><?php pmac_site_lang(['fr' => 'Replay de requêtes & explains comparatifs.', 'en' => 'Query replay & comparative explains.']); ?></li>
        <li><?php pmac_site_lang(['fr' => 'Advisor index automatique avec score de risque.', 'en' => 'Automatic index advisor with risk scoring.']); ?></li>
        <li><?php pmac_site_lang(['fr' => 'Détection des index inutilisés ("trash detector").', 'en' => 'Unused index detection ("trash detector").']); ?></li>
        <li><?php pmac_site_lang(['fr' => 'Suggestions ProxySQL + tests de routage.', 'en' => 'ProxySQL suggestions + routing tests.']); ?></li>
        <li><?php pmac_site_lang(['fr' => 'Visualisation live des clusters & latences.', 'en' => 'Live cluster & latency visualisation.']); ?></li>
    </ul>
</section>

<section class="panel">
    <header><h2><?php pmac_site_lang(['fr' => 'Mode opératoire', 'en' => 'Operating mode']); ?></h2></header>
    <div class="cards two-columns">
        <article>
            <h3><?php pmac_site_lang(['fr' => 'Collecte & digestion', 'en' => 'Collection & digestion']); ?></h3>
            <p><?php pmac_site_lang(['fr' => 'Digest des requêtes (MySQL, MariaDB, ProxySQL) + stats globales', 'en' => 'Query digest (MySQL, MariaDB, ProxySQL) + global stats']); ?></p>
        </article>
        <article>
            <h3><?php pmac_site_lang(['fr' => 'Priorisation', 'en' => 'Prioritisation']); ?></h3>
            <p><?php pmac_site_lang(['fr' => 'Score effort / impact, backlog visible et partageable.', 'en' => 'Effort vs. impact scoring, shareable backlog.']); ?></p>
        </article>
        <article>
            <h3><?php pmac_site_lang(['fr' => 'Simulation', 'en' => 'Simulation']); ?></h3>
            <p><?php pmac_site_lang(['fr' => 'Explains comparés avant/après, estimation stockage index.', 'en' => 'Before/after explains, index size estimation.']); ?></p>
        </article>
        <article>
            <h3><?php pmac_site_lang(['fr' => 'Automatisation', 'en' => 'Automation']); ?></h3>
            <p><?php pmac_site_lang(['fr' => 'Export patch SQL / ProxySQL, API CI/CD.', 'en' => 'SQL / ProxySQL patch export, CI/CD API.']); ?></p>
        </article>
    </div>
</section>
