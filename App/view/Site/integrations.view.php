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

$integrations = [
    [
        'title' => ['fr' => 'Bases de données', 'en' => 'Databases'],
        'items' => ['MySQL', 'MariaDB', 'Percona Server', 'Aurora MySQL', 'AlloyDB'],
    ],
    [
        'title' => ['fr' => 'Proxys & routage', 'en' => 'Proxies & routing'],
        'items' => ['ProxySQL', 'MaxScale', 'HAProxy', 'Envoy SQL'],
    ],
    [
        'title' => ['fr' => 'Haute-disponibilité', 'en' => 'High availability'],
        'items' => ['Galera Cluster', 'Réplication semi-sync', 'MySQL InnoDB Cluster'],
    ],
    [
        'title' => ['fr' => 'Cloud & plateformes', 'en' => 'Cloud & platforms'],
        'items' => ['AWS RDS', 'Azure Database', 'GCP CloudSQL', 'OVH DBaaS'],
    ],
    [
        'title' => ['fr' => 'Alerting & ChatOps', 'en' => 'Alerting & ChatOps'],
        'items' => ['Slack', 'Teams', 'Telegram', 'WhatsApp', 'PagerDuty'],
    ],
    [
        'title' => ['fr' => 'Ticketing & ITSM', 'en' => 'Ticketing & ITSM'],
        'items' => ['Jira', 'ServiceNow', 'Freshservice', 'ClickUp'],
    ],
];
?>
<section class="page-hero">
    <div>
        <p class="eyebrow"><?php pmac_site_lang(['fr' => 'Nous nous branchons sur votre réalité technique', 'en' => 'We plug into your technical reality']); ?></p>
        <h1><?php pmac_site_lang(['fr' => 'Intégrations PmaControl', 'en' => 'PmaControl integrations']); ?></h1>
        <p class="lead"><?php pmac_site_lang(['fr' => 'Aucune refonte imposée : nous connectons vos bases, vos proxys, vos outils de tickets et vos canaux d’alerte.', 'en' => 'No rebuild required: we connect databases, proxies, ticketing tools, and alert channels you already trust.']); ?></p>
    </div>
</section>

<section class="panel">
    <header>
        <h2><?php pmac_site_lang(['fr' => 'Catalogue d’intégrations natives', 'en' => 'Native integration catalogue']); ?></h2>
    </header>
    <div class="logo-cloud">
        <?php foreach ($integrations as $integration): ?>
            <article>
                <strong><?php pmac_site_lang($integration['title']); ?></strong>
                <div class="chips">
                    <?php foreach ($integration['items'] as $item): ?>
                        <span class="chip"><?= htmlspecialchars($item, ENT_QUOTES) ?></span>
                    <?php endforeach; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="panel">
    <header>
        <h2><?php pmac_site_lang(['fr' => 'Observabilité & API', 'en' => 'Observability & API']); ?></h2>
        <p><?php pmac_site_lang(['fr' => 'Exposez les métriques, faites vos dashboards custom, automatisez vos workflows.', 'en' => 'Expose metrics, build custom dashboards, automate workflows.']); ?></p>
    </header>
    <ul class="pill-list">
        <li>REST API</li>
        <li>Webhook</li>
        <li>Prometheus scrape</li>
        <li>Grafana JSON</li>
        <li>Terraform provider (beta)</li>
    </ul>
</section>

<section class="panel highlight">
    <header>
        <h2><?php pmac_site_lang(['fr' => 'Besoin d’un connecteur spécifique ?', 'en' => 'Need a specific connector?']); ?></h2>
        <p><?php pmac_site_lang(['fr' => 'Notre Internal Lab construit rapidement des hooks personnalisés (GitOps, pipelines maison, alerting SIEM).', 'en' => 'Our Internal Lab quickly builds custom hooks (GitOps, in-house pipelines, SIEM alerts).']); ?></p>
    </header>
    <div class="cta-actions">
        <a class="btn btn-primary" href="<?= htmlspecialchars($baseLink.'site/lab/', ENT_QUOTES) ?>"><?php pmac_site_lang(['fr' => 'Voir Internal Lab', 'en' => 'Discover Internal Lab']); ?></a>
    </div>
</section>
