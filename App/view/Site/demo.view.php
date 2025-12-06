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
        <p class="eyebrow"><?php pmac_site_lang(['fr' => 'Une démo vaut mieux qu’un long discours', 'en' => 'A demo beats a thousand slides']); ?></p>
        <h1><?php pmac_site_lang(['fr' => 'Réserver une démo', 'en' => 'Book a live demo']); ?></h1>
        <p class="lead"><?php pmac_site_lang(['fr' => '30 minutes pour visualiser votre architecture, activer les Agents IA et définir un plan d’action.', 'en' => '30 minutes to visualise your architecture, activate AI Agents, and define an action plan.']); ?></p>
        <div class="contact-pill">
            <span>📞 <?= htmlspecialchars($phone, ENT_QUOTES) ?></span>
            <small>WhatsApp • Telegram</small>
        </div>
    </div>
</section>

<section class="panel">
    <header><h2><?php pmac_site_lang(['fr' => 'Formulaire rapide', 'en' => 'Quick form']); ?></h2></header>
    <form class="site-form" action="#" method="post">
        <div class="form-grid">
            <label>
                <span><?php pmac_site_lang(['fr' => 'Nom & société', 'en' => 'Name & company']); ?></span>
                <input type="text" name="name" placeholder="Jane Doe / Acme">
            </label>
            <label>
                <span>Email</span>
                <input type="email" name="email" placeholder="team@example.com">
            </label>
            <label>
                <span><?php pmac_site_lang(['fr' => 'Mode souhaité', 'en' => 'Preferred mode']); ?></span>
                <select name="mode">
                    <option>SaaS / DBaaS</option>
                    <option>On-Premise</option>
                    <option>Mixte</option>
                </select>
            </label>
            <label>
                <span><?php pmac_site_lang(['fr' => 'Nombre de serveurs / bases', 'en' => 'Number of servers / DBs']); ?></span>
                <input type="text" name="servers" placeholder="6 MySQL • 2 ProxySQL...">
            </label>
        </div>
        <label>
            <span><?php pmac_site_lang(['fr' => 'Principaux enjeux aujourd’hui', 'en' => 'Main challenges today']); ?></span>
            <textarea name="context" rows="4"></textarea>
        </label>
        <div class="cta-actions">
            <button class="btn btn-primary" type="submit"><?php pmac_site_lang(['fr' => 'Envoyer la demande', 'en' => 'Send request']); ?></button>
            <span><?php pmac_site_lang(['fr' => 'Réponse sous 24h ouvrées', 'en' => 'Reply within 24h']); ?></span>
        </div>
    </form>
</section>
