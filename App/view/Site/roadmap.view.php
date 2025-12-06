<?php
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

$roadmap = [
    ['quarter' => 'Q2', 'items' => ['Index trash detector 2.0', 'ProxySQL rules simulator', 'Support AlloyDB']],
    ['quarter' => 'Q3', 'items' => ['Max+ auto-remediation (beta)', 'Terraform provider stable', 'Dashboard 4K ultra-wide layouts']],
    ['quarter' => 'Q4', 'items' => ['Agents IA tiers externes', 'Chaos engineering pack', 'Matrix transitions pour Agents']],
];
?>
<section class="page-hero">
    <div>
        <p class="eyebrow"><?php pmac_site_lang(['fr' => 'Roadmap', 'en' => 'Roadmap']); ?></p>
        <h1><?php pmac_site_lang(['fr' => 'Roadmap publique', 'en' => 'Public roadmap']); ?></h1>
    </div>
</section>

<section class="panel">
    <div class="cards three-columns">
        <?php foreach ($roadmap as $entry): ?>
            <article>
                <span class="badge"><?= htmlspecialchars($entry['quarter'], ENT_QUOTES) ?></span>
                <ul>
                    <?php foreach ($entry['items'] as $item): ?>
                        <li><?= htmlspecialchars($item, ENT_QUOTES) ?></li>
                    <?php endforeach; ?>
                </ul>
            </article>
        <?php endforeach; ?>
    </div>
</section>
