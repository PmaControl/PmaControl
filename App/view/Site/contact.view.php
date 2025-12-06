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
        <p class="eyebrow"><?php pmac_site_lang(['fr' => 'Contact', 'en' => 'Contact']); ?></p>
        <h1><?php pmac_site_lang(['fr' => 'Parlons de vos bases', 'en' => 'Let’s talk about your databases']); ?></h1>
    </div>
</section>

<section class="panel">
    <div class="cards two-columns">
        <article>
            <h3><?php pmac_site_lang(['fr' => 'Téléphone', 'en' => 'Phone']); ?></h3>
            <p>📞 <?= htmlspecialchars($phone, ENT_QUOTES) ?></p>
        </article>
        <article>
            <h3><?php pmac_site_lang(['fr' => 'Email', 'en' => 'Email']); ?></h3>
            <p><a href="mailto:contact@pmacontrol.com">contact@pmacontrol.com</a></p>
        </article>
        <article>
            <h3>Telegram / WhatsApp</h3>
            <p>@pmacontrol</p>
        </article>
        <article>
            <h3>Calendly</h3>
            <p><?php pmac_site_lang(['fr' => 'Lien envoyé après qualification', 'en' => 'Link shared after qualification']); ?></p>
        </article>
    </div>
</section>
