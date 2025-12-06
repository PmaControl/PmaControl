<?php
$phone = $site_common['phone'] ?? '+33 6 63 28 27 47';

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
        <p class="eyebrow"><?php pmac_site_lang(['fr' => 'SLA & Support', 'en' => 'SLA & Support']); ?></p>
        <h1><?php pmac_site_lang(['fr' => 'Support & SLA', 'en' => 'Support & SLA']); ?></h1>
        <p class="lead"><?php pmac_site_lang(['fr' => 'Accès direct Telegram, WhatsApp, téléphone. Niveaux Premium avec équipes dédiées.', 'en' => 'Direct access via Telegram, WhatsApp, phone. Premium tiers with dedicated squads.']); ?></p>
    </div>
</section>

<section class="panel">
    <header><h2><?php pmac_site_lang(['fr' => 'Niveaux de support', 'en' => 'Support tiers']); ?></h2></header>
    <div class="cards three-columns">
        <article>
            <h3><?php pmac_site_lang(['fr' => 'Standard', 'en' => 'Standard']); ?></h3>
            <ul>
                <li><?php pmac_site_lang(['fr' => 'Couverture 8h-20h / jours ouvrés', 'en' => '8am-8pm business days']); ?></li>
                <li><?php pmac_site_lang(['fr' => 'Réponse < 4h', 'en' => 'Response < 4h']); ?></li>
            </ul>
        </article>
        <article>
            <h3><?php pmac_site_lang(['fr' => 'Premium 24/7', 'en' => 'Premium 24/7']); ?></h3>
            <ul>
                <li><?php pmac_site_lang(['fr' => 'Astreinte 24/7 + Canal prioritaire', 'en' => '24/7 on-call + priority channel']); ?></li>
                <li><?php pmac_site_lang(['fr' => 'Playbooks dédiés, simulation failover trimestrielle', 'en' => 'Dedicated playbooks, quarterly failover simulation']); ?></li>
            </ul>
        </article>
        <article>
            <h3><?php pmac_site_lang(['fr' => 'Elite', 'en' => 'Elite']); ?></h3>
            <ul>
                <li><?php pmac_site_lang(['fr' => 'Equipe dédiée + Marina+ concierge', 'en' => 'Dedicated squad + Marina+ concierge']); ?></li>
                <li><?php pmac_site_lang(['fr' => 'Engagement 15 min P1', 'en' => '15 min P1 SLA']); ?></li>
            </ul>
        </article>
    </div>
</section>

<section class="panel highlight">
    <header>
        <h2><?php pmac_site_lang(['fr' => 'Contact direct', 'en' => 'Direct contact']); ?></h2>
    </header>
    <div class="cta-actions">
        <div class="direct-contact">
            <strong>📞 <?= htmlspecialchars($phone, ENT_QUOTES) ?></strong>
            <span>Telegram / WhatsApp</span>
        </div>
    </div>
</section>
