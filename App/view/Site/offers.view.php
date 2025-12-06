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

$plans = [
    [
        'badge'  => 'SaaS / DBaaS',
        'title'  => ['fr' => 'PmaControl SaaS', 'en' => 'PmaControl SaaS'],
        'price'  => ['fr' => '10 000 € / serveur / an', 'en' => '€10k / server / year'],
        'bullets'=> [
            ['fr' => 'Facturation à la minute par serveur pour les environnements élastiques.', 'en' => 'Per-minute per-server billing for elastic estates.'],
            ['fr' => 'SLA 99,99 % + astreinte 24/7, WhatsApp & Telegram.', 'en' => '99.99% SLA + 24/7 duty, WhatsApp & Telegram.'],
            ['fr' => 'Agents IA inclus : Max+, Marina+, Nina, Otto.', 'en' => 'AI Agents included: Max+, Marina+, Nina, Otto.'],
            ['fr' => 'Option burst minutes : ne payez que lorsque ça tourne.', 'en' => 'Burst minutes option: pay only when it runs.'],
        ],
        'cta'    => ['fr' => 'Choisir le mode SaaS', 'en' => 'Choose SaaS mode'],
        'href'   => $baseLink.'site/saas/',
    ],
    [
        'badge'  => 'On-Premise',
        'title'  => ['fr' => 'PmaControl On-Prem', 'en' => 'PmaControl On-Prem'],
        'price'  => ['fr' => 'Licence annuelle + services', 'en' => 'Annual licence + services'],
        'bullets'=> [
            ['fr' => 'Installation dans vos datacenters, données qui restent sur site.', 'en' => 'Deployed in your datacenters, data stays on-site.'],
            ['fr' => 'Cluster Galera, réplication, ProxySQL, backups orchestrés.', 'en' => 'Galera clusters, replication, ProxySQL, orchestrated backups.'],
            ['fr' => 'Agents IA Max+, Joséphine, Alex et Elias inclus.', 'en' => 'AI Agents Max+, Joséphine, Alex, and Elias bundled.'],
            ['fr' => 'Support enterprise + workshops trimestriels.', 'en' => 'Enterprise support + quarterly workshops.'],
        ],
        'cta'    => ['fr' => 'Déployer On-Premise', 'en' => 'Deploy On-Premise'],
        'href'   => $baseLink.'site/onpremise/',
    ],
];
?>
<section class="page-hero">
    <div>
        <p class="eyebrow"><?php pmac_site_lang(['fr' => 'Plans pensés pour les équipes SQL modernes', 'en' => 'Plans crafted for modern SQL teams']); ?></p>
        <h1><?php pmac_site_lang(['fr' => 'Nos offres PmaControl', 'en' => 'PmaControl plans']); ?></h1>
        <p class="lead"><?php pmac_site_lang([
            'fr' => 'Choisissez l’expérience SaaS / DBaaS opérée par nos équipes, ou gardez vos serveurs avec l’option On-Premise. Chaque offre embarque les Agents IA dont vous avez besoin.',
            'en' => 'Pick the SaaS / DBaaS experience operated by our squad or keep your servers with the On-Prem option. Each plan ships with the AI Agents you need.',
        ]); ?></p>
    </div>
</section>

<section class="panel">
    <header>
        <h2><?php pmac_site_lang(['fr' => 'Deux piliers, un même niveau d’exigence', 'en' => 'Two pillars, same level of obsession']); ?></h2>
    </header>
    <div class="cards two-columns">
        <?php foreach ($plans as $plan): ?>
            <article>
                <span class="badge"><?= htmlspecialchars($plan['badge'], ENT_QUOTES) ?></span>
                <h3><?php pmac_site_lang($plan['title']); ?></h3>
                <p class="price-tag"><?php pmac_site_lang($plan['price'], 'strong'); ?></p>
                <ul>
                    <?php foreach ($plan['bullets'] as $bullet): ?>
                        <li><?php pmac_site_lang($bullet); ?></li>
                    <?php endforeach; ?>
                </ul>
                <a class="btn btn-secondary" href="<?= htmlspecialchars($plan['href'], ENT_QUOTES) ?>"><?php pmac_site_lang($plan['cta']); ?></a>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="panel">
    <header>
        <h2><?php pmac_site_lang(['fr' => 'Packs IA & services complémentaires', 'en' => 'AI packs & complementary services']); ?></h2>
        <p><?php pmac_site_lang(['fr' => 'Composez votre stack : un agent, plusieurs, ou Marina+ en concierge.', 'en' => 'Compose your stack: one agent, several, or Marina+ as concierge.']); ?></p>
    </header>
    <div class="cards three-columns">
        <article>
            <h3>Max+ Intelligence Pack</h3>
            <ul>
                <li><?php pmac_site_lang(['fr' => 'Advisor index, détection trash index, rewrite SQL assisté.', 'en' => 'Index advisor, trash index detector, assisted SQL rewrite.']); ?></li>
                <li><?php pmac_site_lang(['fr' => 'Simulation d’impact avant mise en prod.', 'en' => 'Impact simulation before prod.']); ?></li>
            </ul>
        </article>
        <article>
            <h3>Marina+ Concierge</h3>
            <ul>
                <li><?php pmac_site_lang(['fr' => 'Briefings quotidiens via Telegram / email.', 'en' => 'Daily briefings via Telegram / email.']); ?></li>
                <li><?php pmac_site_lang(['fr' => 'Synthèse SLA, alertes, backlog priorisé.', 'en' => 'SLA summary, alerts, prioritised backlog.']); ?></li>
            </ul>
        </article>
        <article>
            <h3><?php pmac_site_lang(['fr' => 'Bundles Agents IA', 'en' => 'AI Agent bundles']); ?></h3>
            <ul>
                <li><?php pmac_site_lang(['fr' => 'Pack Performance : Max+, Nina, Otto.', 'en' => 'Performance pack: Max+, Nina, Otto.']); ?></li>
                <li><?php pmac_site_lang(['fr' => 'Pack Transformation : Joséphine, Léo, Alex.', 'en' => 'Transformation pack: Joséphine, Léo, Alex.']); ?></li>
            </ul>
        </article>
    </div>
</section>

<section class="panel">
    <header>
        <h2><?php pmac_site_lang(['fr' => 'Comparatif express', 'en' => 'Quick comparison']); ?></h2>
    </header>
    <div class="doc-grid">
        <article>
            <strong><?php pmac_site_lang(['fr' => 'SaaS / DBaaS', 'en' => 'SaaS / DBaaS']); ?></strong>
            <ul>
                <li><?php pmac_site_lang(['fr' => 'Infra opérée par nos équipes.', 'en' => 'Infrastructure operated by our team.']); ?></li>
                <li><?php pmac_site_lang(['fr' => 'Facturation à l’usage + forfait annuel.', 'en' => 'Usage billing + annual plan.']); ?></li>
                <li><?php pmac_site_lang(['fr' => 'Upgrade sans coupure, migration live.', 'en' => 'Zero-downtime upgrades, live migration.']); ?></li>
            </ul>
        </article>
        <article>
            <strong><?php pmac_site_lang(['fr' => 'On-Premise', 'en' => 'On-Premise']); ?></strong>
            <ul>
                <li><?php pmac_site_lang(['fr' => 'Déploiement chez vous, données locales.', 'en' => 'Deployment on your side, data stays local.']); ?></li>
                <li><?php pmac_site_lang(['fr' => 'Intégration ProxySQL / MaxScale.', 'en' => 'ProxySQL / MaxScale integration.']); ?></li>
                <li><?php pmac_site_lang(['fr' => 'Playbooks incidents personnalisés.', 'en' => 'Custom incident playbooks.']); ?></li>
            </ul>
        </article>
        <article>
            <strong>Max+ Platform</strong>
            <ul>
                <li><?php pmac_site_lang(['fr' => 'Inclus dans chaque offre ou en add-on standalone.', 'en' => 'Included everywhere or as standalone add-on.']); ?></li>
                <li><?php pmac_site_lang(['fr' => 'Talk to Max via portail ou API.', 'en' => 'Talk to Max in-portal or via API.']); ?></li>
            </ul>
        </article>
        <article>
            <strong><?php pmac_site_lang(['fr' => 'Marina+ Ops', 'en' => 'Marina+ Ops']); ?></strong>
            <ul>
                <li><?php pmac_site_lang(['fr' => 'Assistant général : planning, rappel, suivi SLA.', 'en' => 'General assistant: planning, reminders, SLA follow-up.']); ?></li>
                <li><?php pmac_site_lang(['fr' => 'Conversation Telegram, WhatsApp, Teams.', 'en' => 'Talk via Telegram, WhatsApp, Teams.']); ?></li>
            </ul>
        </article>
    </div>
</section>

<section class="panel highlight">
    <header>
        <h2><?php pmac_site_lang(['fr' => 'Besoin d’un mix sur mesure ?', 'en' => 'Need a custom blend?']); ?></h2>
        <p><?php pmac_site_lang(['fr' => 'Nous combinons SaaS + On-Prem, ou ajoutons vos propres agents IA internes.', 'en' => 'We mix SaaS + On-Prem or plug in your internal AI agents.']); ?></p>
    </header>
    <div class="cta-actions">
        <a class="btn btn-primary" href="<?= htmlspecialchars($baseLink.'site/demo/', ENT_QUOTES) ?>"><?php pmac_site_lang(['fr' => 'Réserver une démo', 'en' => 'Book a demo']); ?></a>
        <div class="direct-contact">
            <strong>📞 <?= htmlspecialchars($phone, ENT_QUOTES) ?></strong>
            <span><?php pmac_site_lang(['fr' => 'Réponse en moins de 24 h', 'en' => 'Answer within 24h']); ?></span>
        </div>
    </div>
</section>
