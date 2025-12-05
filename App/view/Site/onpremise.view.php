<?php
$baseLink = defined('LINK') ? LINK : '/';
$phone    = $site_common['phone'] ?? '+33 6 63 28 27 47';

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
        <p class="eyebrow"><?php pmac_site_lang(['fr' => 'Pour les organisations qui gardent leurs serveurs', 'en' => 'For organisations keeping their servers']); ?></p>
        <h1><?php pmac_site_lang(['fr' => 'PmaControl On-Premise', 'en' => 'PmaControl On-Premise']); ?></h1>
        <p class="lead"><?php pmac_site_lang(['fr' => 'Vous gardez vos datacenters, nous apportons l’observabilité, l’IA et les bonnes pratiques.', 'en' => 'You keep your datacenters, we bring observability, AI, and best practices.']); ?></p>
        <div class="hero-ctas">
            <a class="btn btn-primary" href="<?= htmlspecialchars($baseLink.'site/demo/', ENT_QUOTES) ?>">
                <?php pmac_site_lang(['fr' => 'Planifier un pilote', 'en' => 'Plan a pilot']); ?>
            </a>
        </div>
    </div>
</section>

<section class="panel">
    <header>
        <h2><?php pmac_site_lang(['fr' => 'Installation dans votre environnement', 'en' => 'Deployment inside your environment']); ?></h2>
    </header>
    <div class="cards two-columns">
        <article>
            <h3><?php pmac_site_lang(['fr' => 'Compatibilité totale', 'en' => 'Full compatibility']); ?></h3>
            <p><?php pmac_site_lang(['fr' => 'Bare metal, VM, cloud privé, Kubernetes : on s’adapte.', 'en' => 'Bare metal, VM, private cloud, Kubernetes: we adapt.']); ?></p>
            <ul>
                <li>ProxySQL / MaxScale / HAProxy</li>
                <li>Réplication classique, semi-sync, Galera</li>
                <li>Connexion via bastions SSH ou VPN</li>
            </ul>
        </article>
        <article>
            <h3><?php pmac_site_lang(['fr' => 'Collectes & dashboards', 'en' => 'Collection & dashboards']); ?></h3>
            <p><?php pmac_site_lang(['fr' => 'Topologie complète, métriques temps réel, état des sauvegardes.', 'en' => 'Complete topology, real-time metrics, backup status.']); ?></p>
            <ul>
                <li><?php pmac_site_lang(['fr' => 'Recommandations IA (requêtes, index, réplication).', 'en' => 'AI recommendations (queries, indexes, replication).']); ?></li>
                <li><?php pmac_site_lang(['fr' => 'Alerting intégré Slack / Teams / Telegram.', 'en' => 'Integrated Slack / Teams / Telegram alerting.']); ?></li>
            </ul>
        </article>
    </div>
</section>

<section class="panel">
    <header>
        <h2><?php pmac_site_lang(['fr' => 'Sécurité & conformité', 'en' => 'Security & compliance']); ?></h2>
    </header>
    <div class="cards">
        <article>
            <span class="badge">Data stays local</span>
            <p><?php pmac_site_lang(['fr' => 'Les métriques restent dans votre périmètre, aucune donnée applicative ne sort.', 'en' => 'Metrics stay inside your perimeter, no application data leaves.']); ?></p>
        </article>
        <article>
            <span class="badge">Accès contrôlés</span>
            <p><?php pmac_site_lang(['fr' => 'Comptes restreints, audits, rotation des clés SSH.', 'en' => 'Restricted accounts, audits, SSH key rotation.']); ?></p>
        </article>
        <article>
            <span class="badge">Conformités</span>
            <p><?php pmac_site_lang(['fr' => 'Support RGPD, ISO 27001, SOC2, politiques zero-trust.', 'en' => 'Supports GDPR, ISO 27001, SOC2, zero-trust policies.']); ?></p>
        </article>
    </div>
</section>

<section class="panel highlight">
    <header>
        <h2><?php pmac_site_lang(['fr' => 'Connecter PmaControl à vos serveurs existants', 'en' => 'Connect PmaControl to your existing servers']); ?></h2>
    </header>
    <div class="cta-actions">
        <a class="btn btn-primary" href="<?= htmlspecialchars($baseLink.'site/demo/', ENT_QUOTES) ?>">
            <?php pmac_site_lang(['fr' => 'Lancer un diagnostic', 'en' => 'Start a diagnostic']); ?>
        </a>
        <div class="direct-contact">
            <strong>📞 <?= htmlspecialchars($phone, ENT_QUOTES) ?></strong>
        </div>
    </div>
</section>
