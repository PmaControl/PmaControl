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

$agents = [
    [
        'name'    => 'Max+',
        'role'    => ['fr' => 'Intelligence requêtes & index', 'en' => 'Query & index intelligence'],
        'tagline' => ['fr' => '« Je vois ce que vous ne voyez pas. »', 'en' => '"I see what you don’t."'],
        'focus'   => ['fr' => 'Analyse des requêtes coûteuses, index manquants, partitionnement potentiel.', 'en' => 'Finds costly queries, missing indexes, partitioning opportunities.'],
    ],
    [
        'name'    => 'Joséphine',
        'role'    => ['fr' => 'Architecture de schéma', 'en' => 'Schema architect'],
        'tagline' => ['fr' => '« Votre modèle au niveau supérieur. »', 'en' => '"Your model, elevated."'],
        'focus'   => ['fr' => 'Normalisation, contraintes, cohérence avec l’usage.', 'en' => 'Normalisation, constraints, real-world alignment.'],
    ],
    [
        'name'    => 'Léo',
        'role'    => ['fr' => 'Migrations & upgrades', 'en' => 'Migrations & upgrades'],
        'tagline' => ['fr' => '« Bougez sereinement. »', 'en' => '"Move safely."'],
        'focus'   => ['fr' => 'Inventaire, compatibilité versions, tests de rollback.', 'en' => 'Inventory, version compatibility, rollback tests.'],
    ],
    [
        'name'    => 'Alex',
        'role'    => ['fr' => 'Architecture & routage', 'en' => 'Architecture & routing'],
        'tagline' => ['fr' => '« Toujours router pour la performance. »', 'en' => '"Always route for performance."'],
        'focus'   => ['fr' => 'Visualisation topologie, SPOF, routage lecture/écriture.', 'en' => 'Topology visualisation, SPOF hunt, read/write routing.'],
    ],
    [
        'name'    => 'Marina+',
        'role'    => ['fr' => 'Assistante générale IA', 'en' => 'AI chief of staff'],
        'tagline' => ['fr' => '« Votre quotidien, simplifié. »', 'en' => '"Your daily ops simplified."'],
        'focus'   => ['fr' => 'Briefings en langage naturel, notifications Telegram.', 'en' => 'Natural-language briefings, Telegram notifications.'],
    ],
    [
        'name'    => 'Elias',
        'role'    => ['fr' => 'Sécurité & conformité', 'en' => 'Security & compliance'],
        'tagline' => ['fr' => '« Privacy sans compromis. »', 'en' => '"Privacy without compromise."'],
        'focus'   => ['fr' => 'RGPD, ISO, cartographie des permissions.', 'en' => 'GDPR, ISO, permission mapping.'],
    ],
    [
        'name'    => 'Nina',
        'role'    => ['fr' => 'Monitoring prédictif', 'en' => 'Predictive monitoring'],
        'tagline' => ['fr' => '« J’alerte avant les dégâts. »', 'en' => '"I alert before damage."'],
        'focus'   => ['fr' => 'Apprentissage depuis vos incidents, signaux faibles.', 'en' => 'Learns from incidents, tracks weak signals.'],
    ],
    [
        'name'    => 'Otto',
        'role'    => ['fr' => 'Capacity planner & cost', 'en' => 'Capacity planner & cost'],
        'tagline' => ['fr' => '« Dépensez moins, scalez plus. »', 'en' => '"Spend less, scale more."'],
        'focus'   => ['fr' => 'Analyse coût / requêtes, projections budgétaires.', 'en' => 'Cost-per-query analysis, budget projections.'],
    ],
    [
        'name'    => 'Soren',
        'role'    => ['fr' => 'Time-series doctor', 'en' => 'Time-series doctor'],
        'tagline' => ['fr' => '« Vos métriques optimisées pour toujours. »', 'en' => '"Your metrics optimised forever."'],
        'focus'   => ['fr' => 'RocksDB, TSDB, stockage métriques.', 'en' => 'RocksDB, TSDB, metrics storage.'],
    ],
    [
        'name'    => 'Klara',
        'role'    => ['fr' => 'Orchestratrice ProxySQL', 'en' => 'ProxySQL orchestrator'],
        'tagline' => ['fr' => '« L’équilibrage comme un art. »', 'en' => '"Balancing is an art."'],
        'focus'   => ['fr' => 'Routage multi-tiers, règles dynamiques.', 'en' => 'Multi-tier routing, dynamic rules.'],
    ],
    [
        'name'    => 'Helios',
        'role'    => ['fr' => 'Cloud DB multi-vendeur', 'en' => 'Cloud DB multi-vendor'],
        'tagline' => ['fr' => '« RDS, Aurora, AlloyDB maîtrisés. »', 'en' => '"RDS, Aurora, AlloyDB mastered."'],
        'focus'   => ['fr' => 'Optimisation multi-cloud, failover multi-region.', 'en' => 'Multi-cloud optimisation, multi-region failover.'],
    ],
    [
        'name'    => 'Gaia',
        'role'    => ['fr' => 'Durabilité & green Ops', 'en' => 'Sustainability & green ops'],
        'tagline' => ['fr' => '« Optimisez l’empreinte énergie / SQL. »', 'en' => '"Optimise energy per query."'],
        'focus'   => ['fr' => 'KPIs énergie, consolidation workloads.', 'en' => 'Energy KPIs, workload consolidation.'],
    ],
    [
        'name'    => 'Rex',
        'role'    => ['fr' => 'Chaos & résilience', 'en' => 'Chaos & resilience'],
        'tagline' => ['fr' => '« Testons vos limites avant la prod. »', 'en' => '"Break it before prod."'],
        'focus'   => ['fr' => 'Jeux de panne, gameday Galera & ProxySQL.', 'en' => 'Failure games, Galera & ProxySQL gamedays.'],
    ],
];
?>
<section class="page-hero">
    <div>
        <p class="eyebrow"><?php pmac_site_lang(['fr' => 'Une équipe IA dédiée à vos bases de données', 'en' => 'An AI squad for your databases']); ?></p>
        <h1><?php pmac_site_lang(['fr' => 'Catalogue des Agents IA PmaControl', 'en' => 'PmaControl AI Agent catalogue']); ?></h1>
        <p class="lead"><?php pmac_site_lang(['fr' => 'Activez un agent ou composez votre propre task force selon vos priorités (performance, migration, coût, sécurité).', 'en' => 'Activate one agent or compose your own task force around performance, migration, cost, or security priorities.']); ?></p>
    </div>
</section>

<section class="panel">
    <div class="cards three-columns">
        <?php foreach ($agents as $agent): ?>
            <article>
                <div class="agent-avatar"><?= substr($agent['name'], 0, 1) ?></div>
                <h3><?= htmlspecialchars($agent['name'], ENT_QUOTES) ?></h3>
                <?php pmac_site_lang($agent['role'], 'p', 'lang-block'); ?>
                <?php pmac_site_lang($agent['tagline'], 'p', 'lang-block'); ?>
                <?php pmac_site_lang($agent['focus'], 'p', 'lang-block'); ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="panel highlight">
    <header>
        <h2><?php pmac_site_lang(['fr' => 'Motion UI & expériences immersives', 'en' => 'Motion UI & immersive experiences']); ?></h2>
        <p><?php pmac_site_lang(['fr' => 'Bubbles IA animées, switch fluide dark/light, transitions matrix lors du changement d’agent.', 'en' => 'Animated AI bubbles, fluid dark/light switch, matrix-style transitions per agent.']); ?></p>
    </header>
</section>
