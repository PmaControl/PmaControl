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
<section class="site-hero">
    <div class="hero-grid">
        <div class="hero-text">
            <div class="eyebrow"><?php pmac_site_lang(['fr' => 'Control the uncontrollable', 'en' => 'Control the uncontrollable']); ?></div>
            <h1><?php pmac_site_lang(['fr' => 'PmaControl – supervision et IA pour vos bases MySQL / MariaDB', 'en' => 'PmaControl – observability & AI for your MySQL / MariaDB']); ?></h1>
            <p class="lead"><?php pmac_site_lang([
                'fr' => 'Une seule console pour superviser, optimiser et sécuriser toutes vos bases. Mode SaaS & DBaaS ou installation On-Premise, avec Agents IA spécialisés.',
                'en' => 'One console to supervise, optimise, and secure every database. Choose SaaS & DBaaS or keep everything On-Premise, powered by specialised AI Agents.',
            ]); ?></p>
            <ul class="hero-bullets">
                <li><?php pmac_site_lang([
                    'fr' => 'Réponses proactives grâce aux Agents IA Max+, Joséphine, Léo, Alex et Marina+.',
                    'en' => 'Proactive answers from AI Agents Max+, Joséphine, Léo, Alex, and Marina+.',
                ]); ?></li>
                <li><?php pmac_site_lang([
                    'fr' => 'SaaS 10 000 € / serveur / an + option pay-per-minute pour les bursts.',
                    'en' => 'SaaS from €10k / server / year + optional pay-per-minute bursts.',
                ]); ?></li>
                <li><?php pmac_site_lang([
                    'fr' => 'Support direct : démo 30 min & WhatsApp / Telegram / téléphone.',
                    'en' => 'Direct support: 30-minute live demo & WhatsApp / Telegram / phone.',
                ]); ?></li>
            </ul>
            <div class="hero-ctas">
                <a class="btn btn-primary" href="<?= htmlspecialchars($baseLink.'site/demo/', ENT_QUOTES) ?>">
                    <?php pmac_site_lang(['fr' => 'Réserver une démo', 'en' => 'Book a demo']); ?>
                </a>
                <a class="btn btn-secondary" href="<?= htmlspecialchars($baseLink.'site/max/', ENT_QUOTES) ?>">
                    <?php pmac_site_lang(['fr' => 'Talk to Max', 'en' => 'Talk to Max']); ?>
                </a>
                <div class="contact-pill">
                    <span>📞 <?= htmlspecialchars($phone, ENT_QUOTES) ?></span>
                    <small><?php pmac_site_lang(['fr' => 'WhatsApp • Telegram • 24/7', 'en' => 'WhatsApp • Telegram • 24/7']); ?></small>
                </div>
            </div>
        </div>
        <div class="hero-visual">
            <img src="<?= IMG ?>icon/pmacontrol_b.svg" alt="PmaControl emblem">
            <div class="hero-stats">
                <article>
                    <strong>99.99%</strong>
                    <?php pmac_site_lang(['fr' => 'SLA haute dispo', 'en' => 'High-availability SLA']); ?>
                </article>
                <article>
                    <strong>14 min</strong>
                    <?php pmac_site_lang(['fr' => 'Record recovery DC', 'en' => 'Datacenter recovery record']); ?>
                </article>
                <article>
                    <strong>24/7</strong>
                    <?php pmac_site_lang(['fr' => 'Veille IA + humains', 'en' => 'AI + human watch']); ?>
                </article>
            </div>
            <div class="hero-badge"><?php pmac_site_lang(['fr' => 'Zero downtime promise', 'en' => 'Zero downtime promise']); ?></div>
        </div>
    </div>
</section>

<section class="panel">
    <header>
        <h2><?php pmac_site_lang(['fr' => 'Deux approches, une même exigence : la fiabilité', 'en' => 'Two approaches, one obsession: reliability']); ?></h2>
        <p><?php pmac_site_lang(['fr' => 'Choisissez SaaS & DBaaS ou On-Premise selon vos contraintes.', 'en' => 'Pick SaaS & DBaaS or On-Premise depending on your constraints.']); ?></p>
    </header>
    <div class="cards two-columns">
        <article>
            <span class="badge">SaaS & DBaaS</span>
            <h3>PmaControl SaaS</h3>
            <p><?php pmac_site_lang([
                'fr' => 'Nous opérons vos bases : hébergement, HA, sauvegardes testées, supervision 24/7. Vous gardez la maîtrise fonctionnelle.',
                'en' => 'We operate your databases: hosting, HA, tested backups, 24/7 observability. You keep functional control.',
            ]); ?></p>
            <ul>
                <li><?php pmac_site_lang(['fr' => 'Facturation à la minute + forfait 10 000 € / serveur / an.', 'en' => 'Per-minute billing + €10k / server / year plan.']); ?></li>
                <li><?php pmac_site_lang(['fr' => 'Agents IA actifs pour anticiper les pics.', 'en' => 'AI Agents anticipate spikes.']); ?></li>
                <li><?php pmac_site_lang(['fr' => 'SLA personnalisé, alerting WhatsApp / Telegram.', 'en' => 'Custom SLA, WhatsApp / Telegram alerts.']); ?></li>
            </ul>
            <a class="btn btn-secondary" href="<?= htmlspecialchars($baseLink.'site/saas/', ENT_QUOTES) ?>">
                <?php pmac_site_lang(['fr' => 'Découvrir l’offre SaaS', 'en' => 'Explore SaaS']); ?>
            </a>
        </article>
        <article>
            <span class="badge">On-Premise</span>
            <h3>PmaControl On-Premise</h3>
            <p><?php pmac_site_lang([
                'fr' => 'Déployé dans vos datacenters : vos données restent chez vous, nous apportons la console, l’IA et les bonnes pratiques.',
                'en' => 'Deployed inside your datacenters: data stays with you, we bring the console, AI, and best practices.',
            ]); ?></p>
            <ul>
                <li><?php pmac_site_lang(['fr' => 'Topologie complète + métriques temps réel + audit sécurité.', 'en' => 'Complete topology + real-time metrics + security audit.']); ?></li>
                <li><?php pmac_site_lang(['fr' => 'ProxySQL, MaxScale, Galera, réplication classique.', 'en' => 'ProxySQL, MaxScale, Galera, classic replication.']); ?></li>
                <li><?php pmac_site_lang(['fr' => 'Conformités RGPD & ISO, accès restreints.', 'en' => 'GDPR & ISO readiness, restricted access.']); ?></li>
            </ul>
            <a class="btn btn-secondary" href="<?= htmlspecialchars($baseLink.'site/onpremise/', ENT_QUOTES) ?>">
                <?php pmac_site_lang(['fr' => 'Passer en On-Prem', 'en' => 'Deploy On-Prem']); ?>
            </a>
        </article>
    </div>
</section>

<section class="panel">
    <header>
        <h2><?php pmac_site_lang(['fr' => 'Agents IA spécialisés', 'en' => 'Specialised AI Agents']); ?></h2>
        <p><?php pmac_site_lang(['fr' => 'Activez l’agent dont vous avez besoin ou toute l’équipe.', 'en' => 'Activate the agent you need or the whole squad.']); ?></p>
    </header>
    <div class="cards three-columns">
        <?php
        $agents = [
            ['name' => 'Max+', 'tagline' => ['fr' => 'Requêtes & index', 'en' => 'Query & index'], 'desc' => ['fr' => 'Analyse des requêtes coûteuses et propose des index prêts à valider.', 'en' => 'Scans costly queries and proposes ready-to-validate indexes.']],
            ['name' => 'Joséphine', 'tagline' => ['fr' => 'Architecture de schéma', 'en' => 'Schema architecture'], 'desc' => ['fr' => 'Normalisation, contraintes, cohérence avec l’usage réel.', 'en' => 'Normalisation, constraints, alignment with real workload.']],
            ['name' => 'Léo', 'tagline' => ['fr' => 'Migrations & upgrades', 'en' => 'Migrations & upgrades'], 'desc' => ['fr' => 'Inventaire, compatibilité versions, rollback scénarisé.', 'en' => 'Inventory, version compatibility, and scripted rollback.']],
            ['name' => 'Alex', 'tagline' => ['fr' => 'Architecture & routage', 'en' => 'Architecture & routing'], 'desc' => ['fr' => 'Visualise votre topologie et optimise le load balancing.', 'en' => 'Visualises topology and optimises load balancing.']],
            ['name' => 'Marina+', 'tagline' => ['fr' => 'Assistante IA globale', 'en' => 'Global AI assistant'], 'desc' => ['fr' => 'Briefings en langage naturel, notifications Telegram.', 'en' => 'Natural-language briefings, Telegram alerts.']],
            ['name' => 'Elias', 'tagline' => ['fr' => 'Sécurité & conformité', 'en' => 'Security & compliance'], 'desc' => ['fr' => 'RGPD, ISO, détection d’écarts de permissions.', 'en' => 'GDPR, ISO, and permission drift detection.']],
        ];
        foreach ($agents as $agent): ?>
            <article>
                <div class="agent-avatar"><?= substr($agent['name'], 0, 1) ?></div>
                <h3><?= htmlspecialchars($agent['name'], ENT_QUOTES) ?></h3>
                <?php pmac_site_lang($agent['tagline'], 'p', 'lang-block'); ?>
                <?php pmac_site_lang($agent['desc'], 'p', 'lang-block'); ?>
            </article>
        <?php endforeach; ?>
    </div>
    <div class="chips">
        <a class="chip" href="<?= htmlspecialchars($baseLink.'site/agents/', ENT_QUOTES) ?>"><?php pmac_site_lang(['fr' => 'Voir tous les Agents IA', 'en' => 'See all AI Agents']); ?></a>
        <a class="chip" href="<?= htmlspecialchars($baseLink.'site/max/', ENT_QUOTES) ?>"><?php pmac_site_lang(['fr' => 'Focus Max+', 'en' => 'Focus Max+']); ?></a>
        <a class="chip" href="<?= htmlspecialchars($baseLink.'site/resources/', ENT_QUOTES) ?>"><?php pmac_site_lang(['fr' => 'Ressources & Labs', 'en' => 'Resources & Labs']); ?></a>
    </div>
</section>

<section class="panel">
    <header>
        <h2><?php pmac_site_lang(['fr' => 'Pourquoi PmaControl ?', 'en' => 'Why PmaControl?']); ?></h2>
    </header>
    <div class="promise-grid">
        <article>
            <h3><?php pmac_site_lang(['fr' => 'Avant PmaControl', 'en' => 'Before PmaControl']); ?></h3>
            <p><?php pmac_site_lang(['fr' => 'Incidents en cascade, index non suivis, réplications fragiles.', 'en' => 'Cascading incidents, zero index visibility, fragile replication.']); ?></p>
        </article>
        <article>
            <h3><?php pmac_site_lang(['fr' => 'Après PmaControl', 'en' => 'After PmaControl']); ?></h3>
            <p><?php pmac_site_lang(['fr' => 'Console unique, Agents IA, prédiction et optimisation continue.', 'en' => 'Single console, AI Agents, prediction and continuous optimisation.']); ?></p>
        </article>
        <article>
            <h3><?php pmac_site_lang(['fr' => 'Zero downtime promise', 'en' => 'Zero downtime promise']); ?></h3>
            <p><?php pmac_site_lang(['fr' => 'Recovery orchestré, migration live, upgrade sans coupure.', 'en' => 'Orchestrated recovery, live migrations, outage-free upgrades.']); ?></p>
        </article>
        <article>
            <h3><?php pmac_site_lang(['fr' => 'Observabilité totale SQL', 'en' => 'Total SQL observability']); ?></h3>
            <p><?php pmac_site_lang(['fr' => 'Query-level time-series, index trash detector, ProxySQL intelligence.', 'en' => 'Query-level time-series, index trash detector, ProxySQL intelligence.']); ?></p>
        </article>
    </div>
</section>

<section class="panel panel--ghost">
    <header>
        <h2><?php pmac_site_lang(['fr' => 'Architecture complète du site', 'en' => 'Full site architecture']); ?></h2>
        <p><?php pmac_site_lang(['fr' => 'Chaque page dispose de son propre MVC pour rester maintenable.', 'en' => 'Every page runs its own MVC slice for clean maintainability.']); ?></p>
    </header>
    <div class="cards three-columns compact">
        <?php
        $pages = [
            ['href' => 'site/saas/', 'labels' => ['fr' => 'PmaControl SaaS & DBaaS', 'en' => 'PmaControl SaaS & DBaaS']],
            ['href' => 'site/onpremise/', 'labels' => ['fr' => 'PmaControl On-Premise', 'en' => 'PmaControl On-Premise']],
            ['href' => 'site/agents/', 'labels' => ['fr' => 'Agents IA', 'en' => 'AI Agents']],
            ['href' => 'site/max/', 'labels' => ['fr' => 'Max+ Platform', 'en' => 'Max+ Platform']],
            ['href' => 'site/integrations/', 'labels' => ['fr' => 'Intégrations', 'en' => 'Integrations']],
            ['href' => 'site/documentation/', 'labels' => ['fr' => 'Documentation & API', 'en' => 'Docs & API']],
            ['href' => 'site/faq/', 'labels' => ['fr' => 'FAQ', 'en' => 'FAQ']],
            ['href' => 'site/process/', 'labels' => ['fr' => 'Processus', 'en' => 'Process']],
            ['href' => 'site/resources/', 'labels' => ['fr' => 'Ressources & templates', 'en' => 'Resources & templates']],
            ['href' => 'site/blog/', 'labels' => ['fr' => 'Blog / Insights', 'en' => 'Blog / Insights']],
            ['href' => 'site/support/', 'labels' => ['fr' => 'Support & SLA', 'en' => 'Support & SLA']],
            ['href' => 'site/contact/', 'labels' => ['fr' => 'Contact', 'en' => 'Contact']],
            ['href' => 'site/roadmap/', 'labels' => ['fr' => 'Roadmap publique', 'en' => 'Public roadmap']],
            ['href' => 'site/success/', 'labels' => ['fr' => 'Success Stories', 'en' => 'Success Stories']],
            ['href' => 'site/lab/', 'labels' => ['fr' => 'Internal Lab Showcase', 'en' => 'Internal Lab Showcase']],
            ['href' => 'site/incidents/', 'labels' => ['fr' => 'Incidents Hall of Fame', 'en' => 'Incidents Hall of Fame']],
        ];
        foreach ($pages as $page): ?>
            <article>
                <a href="<?= htmlspecialchars($baseLink.$page['href'], ENT_QUOTES) ?>">
                    <?php pmac_site_lang($page['labels'], 'p', 'lang-block'); ?>
                </a>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="panel highlight">
    <header>
        <h2><?php pmac_site_lang(['fr' => 'Une démo vaut mieux qu’un long discours', 'en' => 'A demo beats a thousand slides']); ?></h2>
        <p><?php pmac_site_lang(['fr' => 'En 30 minutes, nous cartographions votre architecture et livrons un plan d’action.', 'en' => 'In 30 minutes we map your architecture and deliver an action plan.']); ?></p>
    </header>
    <div class="cta-actions">
        <a class="btn btn-primary" href="<?= htmlspecialchars($baseLink.'site/demo/', ENT_QUOTES) ?>">
            <?php pmac_site_lang(['fr' => 'Réserver une démo', 'en' => 'Book a demo']); ?>
        </a>
        <a class="btn btn-secondary" href="https://t.me/">
            <?php pmac_site_lang(['fr' => 'Parler à Marina sur Telegram', 'en' => 'Talk to Marina on Telegram']); ?>
        </a>
        <div class="direct-contact">
            <strong>📞 <?= htmlspecialchars($phone, ENT_QUOTES) ?></strong>
            <span><?php pmac_site_lang(['fr' => 'Réponse sous 24h', 'en' => 'Reply within 24h']); ?></span>
        </div>
    </div>
</section>
