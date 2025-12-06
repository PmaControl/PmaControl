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

$steps = [
    ['title' => ['fr' => 'Audit & découverte', 'en' => 'Audit & discovery'], 'desc' => ['fr' => 'Etat des lieux de vos bases, architecture, risques, SLA.', 'en' => 'State of your databases, architecture, risks, SLA.']],
    ['title' => ['fr' => 'Design & recommandations', 'en' => 'Design & recommendations'], 'desc' => ['fr' => 'Plan d’amélioration priorisé avec quick wins et chantiers de fond.', 'en' => 'Prioritised improvement plan with quick wins and strategic work.']],
    ['title' => ['fr' => 'Déploiement PmaControl', 'en' => 'Deploy PmaControl'], 'desc' => ['fr' => 'SaaS ou On-Prem, connexion serveurs, métriques live.', 'en' => 'SaaS or On-Prem, server connection, live metrics.']],
    ['title' => ['fr' => 'Activation des Agents IA', 'en' => 'Activate AI agents'], 'desc' => ['fr' => 'Max+, Joséphine, Léo, Alex, Marina+ selon vos priorités.', 'en' => 'Max+, Joséphine, Léo, Alex, Marina+ depending on priorities.']],
    ['title' => ['fr' => 'Suivi & optimisation continue', 'en' => 'Follow-up & continuous optimisation'], 'desc' => ['fr' => 'Revues régulières, roadmap partagée, ateliers ciblés.', 'en' => 'Regular reviews, shared roadmap, targeted workshops.']],
];
?>
<section class="page-hero">
    <div>
        <p class="eyebrow"><?php pmac_site_lang(['fr' => 'Comment on travaille ensemble', 'en' => 'How we work together']); ?></p>
        <h1><?php pmac_site_lang(['fr' => 'Processus PmaControl', 'en' => 'PmaControl process']); ?></h1>
    </div>
</section>

<section class="panel">
    <ol class="timeline">
        <?php foreach ($steps as $step): ?>
            <li>
                <h3><?php pmac_site_lang($step['title']); ?></h3>
                <?php pmac_site_lang($step['desc'], 'p', 'lang-block'); ?>
            </li>
        <?php endforeach; ?>
    </ol>
</section>

<section class="panel highlight">
    <header>
        <h2><?php pmac_site_lang(['fr' => 'Zero downtime promise', 'en' => 'Zero downtime promise']); ?></h2>
        <p><?php pmac_site_lang(['fr' => 'Recovery orchestré, upgrade sans coupure, migration live.', 'en' => 'Orchestrated recovery, outage-free upgrades, live migrations.']); ?></p>
    </header>
    <div class="cta-actions">
        <a class="btn btn-primary" href="<?= htmlspecialchars($baseLink.'site/incidents/', ENT_QUOTES) ?>"><?php pmac_site_lang(['fr' => 'Voir nos incidents résolus', 'en' => 'See resolved incidents']); ?></a>
    </div>
</section>
