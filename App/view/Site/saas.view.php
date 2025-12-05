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
        <p class="eyebrow"><?php pmac_site_lang(['fr' => 'DBaaS opéré par des experts SQL', 'en' => 'DBaaS operated by SQL experts']); ?></p>
        <h1><?php pmac_site_lang(['fr' => 'PmaControl SaaS & DBaaS', 'en' => 'PmaControl SaaS & DBaaS']); ?></h1>
        <p class="lead"><?php pmac_site_lang([
            'fr' => 'Déléguez l’infrastructure, gardez le contrôle fonctionnel. Nous opérons vos bases MySQL / MariaDB en continu, avec Agents IA intégrés.',
            'en' => 'Delegate the infrastructure, keep functional control. We operate your MySQL / MariaDB estate with embedded AI Agents.',
        ]); ?></p>
        <div class="hero-ctas">
            <a class="btn btn-primary" href="<?= htmlspecialchars($baseLink.'site/demo/', ENT_QUOTES) ?>">
                <?php pmac_site_lang(['fr' => 'Réserver une démo', 'en' => 'Book a demo']); ?>
            </a>
            <div class="contact-pill">
                <span>📞 <?= htmlspecialchars($phone, ENT_QUOTES) ?></span>
                <small><?php pmac_site_lang(['fr' => 'Réponse sous 24h', 'en' => 'Reply within 24h']); ?></small>
            </div>
        </div>
    </div>
</section>

<section class="panel">
    <header>
        <h2><?php pmac_site_lang(['fr' => 'Ce que vous nous déléguez', 'en' => 'What you delegate']); ?></h2>
    </header>
    <div class="cards two-columns">
        <article>
            <h3><?php pmac_site_lang(['fr' => 'Infrastructure & haute-disponibilité', 'en' => 'Infrastructure & high availability']); ?></h3>
            <ul>
                <li><?php pmac_site_lang(['fr' => 'Hébergement sécurisé dans l’UE, tuning MySQL / MariaDB.', 'en' => 'Secure EU hosting, MySQL / MariaDB tuning.']); ?></li>
                <li><?php pmac_site_lang(['fr' => 'Clusters Galera, réplication semi-sync, ProxySQL managé.', 'en' => 'Managed Galera clusters, semi-sync replication, ProxySQL.']); ?></li>
                <li><?php pmac_site_lang(['fr' => 'Mises à jour système, patch day-zero.', 'en' => 'System updates, day-zero patching.']); ?></li>
            </ul>
        </article>
        <article>
            <h3><?php pmac_site_lang(['fr' => 'Sauvegardes & supervision 24/7', 'en' => 'Backups & 24/7 monitoring']); ?></h3>
            <ul>
                <li><?php pmac_site_lang(['fr' => 'Backups orchestrées + tests de restauration réguliers.', 'en' => 'Orchestrated backups + regular restore tests.']); ?></li>
                <li><?php pmac_site_lang(['fr' => 'Observabilité temps réel, alertes Telegram / WhatsApp.', 'en' => 'Real-time observability, Telegram / WhatsApp alerts.']); ?></li>
                <li><?php pmac_site_lang(['fr' => 'Agents IA actifs : Max+, Marina+, Otto.', 'en' => 'Active AI agents: Max+, Marina+, Otto.']); ?></li>
            </ul>
        </article>
    </div>
</section>

<section class="panel">
    <header>
        <h2><?php pmac_site_lang(['fr' => 'Modèle tarifaire', 'en' => 'Pricing model']); ?></h2>
    </header>
    <div class="cards">
        <article>
            <span class="badge">Pay-per-minute</span>
            <p><?php pmac_site_lang(['fr' => 'Facturation à la minute et par serveur : idéal pour environnements dynamiques, tests, campagnes.', 'en' => 'Per-minute, per-server billing: ideal for dynamic environments, tests, bursty campaigns.']); ?></p>
        </article>
        <article>
            <span class="badge">Forfait annuel</span>
            <p><?php pmac_site_lang(['fr' => 'À partir de 10 000 € / serveur / an pour une exploitation continue avec support avancé.', 'en' => 'From €10k / server / year for full operations with advanced support.']); ?></p>
        </article>
        <article>
            <span class="badge">SLA dédié</span>
            <p><?php pmac_site_lang(['fr' => 'Engagements 99,99 %, astreinte 24/7, canal prioritaire.', 'en' => '99.99% SLA options, 24/7 on-call, priority channel.']); ?></p>
        </article>
    </div>
</section>

<section class="panel">
    <header>
        <h2><?php pmac_site_lang(['fr' => 'Avantages clés', 'en' => 'Key advantages']); ?></h2>
    </header>
    <ul class="pill-list">
        <li><?php pmac_site_lang(['fr' => 'Aucun serveur à maintenir, aucune mise à jour système à faire.', 'en' => 'No server to maintain, no system updates to chase.']); ?></li>
        <li><?php pmac_site_lang(['fr' => 'Agents IA qui surveillent vos requêtes et index en continu.', 'en' => 'AI Agents watching queries & indexes continuously.']); ?></li>
        <li><?php pmac_site_lang(['fr' => 'Playbooks d’incident documentés et rejouables.', 'en' => 'Documented, replayable incident playbooks.']); ?></li>
        <li><?php pmac_site_lang(['fr' => 'Accès portail client + dashboards temps réel.', 'en' => 'Client portal + real-time dashboards.']); ?></li>
    </ul>
</section>

<section class="panel highlight">
    <header>
        <h2><?php pmac_site_lang(['fr' => 'Discutons de votre DBaaS', 'en' => 'Let’s size your DBaaS']); ?></h2>
        <p><?php pmac_site_lang(['fr' => 'On dimensionne ensemble RAM, vCPU, stockage et SLA.', 'en' => 'We size RAM, vCPU, storage, and SLA together.']); ?></p>
    </header>
    <div class="cta-actions">
        <a class="btn btn-primary" href="<?= htmlspecialchars($baseLink.'site/demo/', ENT_QUOTES) ?>">
            <?php pmac_site_lang(['fr' => 'Réserver une démo', 'en' => 'Book a demo']); ?>
        </a>
        <div class="direct-contact">
            <strong>📞 <?= htmlspecialchars($phone, ENT_QUOTES) ?></strong>
            <span><?php pmac_site_lang(['fr' => 'Canal Telegram / WhatsApp disponible', 'en' => 'Telegram / WhatsApp channel available']); ?></span>
        </div>
    </div>
</section>
