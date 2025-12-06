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

$faqs = [
    [
        'q' => ['fr' => 'Où sont hébergées les données ?', 'en' => 'Where is data hosted?'],
        'a' => ['fr' => 'En SaaS, dans des datacenters UE audités. En On-Prem, tout reste dans vos réseaux.', 'en' => 'In SaaS, inside audited EU datacenters. On-Prem stays within your networks.'],
    ],
    [
        'q' => ['fr' => 'Pouvez-vous travailler avec nos équipes internes ?', 'en' => 'Can you work with our internal teams?'],
        'a' => ['fr' => 'Oui : pair-workshop, co-pilotage de migrations et transfert de compétences.', 'en' => 'Yes: pair workshops, co-piloting migrations, and skill transfer.'],
    ],
    [
        'q' => ['fr' => 'Comment se passe une migration ?', 'en' => 'How does a migration work?'],
        'a' => ['fr' => 'Léo prépare un plan détaillé (inventaire, tests, rollback). Nous exécutons en dry-run puis en prod.', 'en' => 'Léo prepares a detailed plan (inventory, tests, rollback). We run dry-runs then production.'],
    ],
    [
        'q' => ['fr' => 'Que se passe-t-il si on arrête le service ?', 'en' => 'What happens if we stop the service?'],
        'a' => ['fr' => 'Export complet des métriques, transfert des playbooks, désinstallation assistée.', 'en' => 'Full metrics export, playbook handover, assisted uninstall.'],
    ],
    [
        'q' => ['fr' => 'Comment gérez-vous les sauvegardes ?', 'en' => 'How do you handle backups?'],
        'a' => ['fr' => 'Backups orchestrés, tests de restauration planifiés et rapports automatiques.', 'en' => 'Orchestrated backups, scheduled restore tests, automated reports.'],
    ],
];
?>
<section class="page-hero">
    <div>
        <p class="eyebrow"><?php pmac_site_lang(['fr' => 'FAQ pricing, data, SLA', 'en' => 'FAQ: pricing, data, SLA']); ?></p>
        <h1><?php pmac_site_lang(['fr' => 'Questions fréquentes', 'en' => 'Frequently asked']); ?></h1>
        <p class="lead"><?php pmac_site_lang(['fr' => 'Si vous avez un doute, contactez-nous : nous aimons parler bases de données.', 'en' => 'If in doubt, reach out: we love talking databases.']); ?></p>
    </div>
</section>

<section class="panel">
    <div class="accordion">
        <?php foreach ($faqs as $faq): ?>
            <details>
                <summary><?php pmac_site_lang($faq['q']); ?></summary>
                <?php pmac_site_lang($faq['a'], 'p', 'lang-block'); ?>
            </details>
        <?php endforeach; ?>
    </div>
</section>

<section class="panel highlight">
    <header>
        <h2><?php pmac_site_lang(['fr' => 'Vous ne trouvez pas la réponse ?', 'en' => 'Can’t find the answer?']); ?></h2>
        <p><?php pmac_site_lang(['fr' => 'Parlez directement à Marina+ ou réservez un créneau avec Max.', 'en' => 'Talk to Marina+ directly or book a slot with Max.']); ?></p>
    </header>
    <div class="cta-actions">
        <a class="btn btn-primary" href="<?= htmlspecialchars($baseLink.'site/demo/', ENT_QUOTES) ?>"><?php pmac_site_lang(['fr' => 'Réserver une démo', 'en' => 'Book a demo']); ?></a>
        <a class="btn btn-secondary" href="<?= htmlspecialchars($baseLink.'site/contact/', ENT_QUOTES) ?>"><?php pmac_site_lang(['fr' => 'Écrire à l’équipe', 'en' => 'Write to the team']); ?></a>
    </div>
</section>
