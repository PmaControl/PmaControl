<?php

use Glial\I18n\I18n;

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

$lang       = I18n::Get() ?: 'fr';
$common     = $GLIALE_DATA['site_common'] ?? [];
$menu       = $common['menu'] ?? [];
$phone      = $common['phone'] ?? '';
$activeItem = $GLIALE_DATA['site_active'] ?? 'home';
$baseLink   = defined('LINK') ? LINK : '/';
?><!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang, ENT_QUOTES) ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="PmaControl — observabilité SQL augmentée avec Agents IA, disponible en SaaS & On-Premise.">
        <meta name="keywords" content="PmaControl, MySQL, MariaDB, observability, DBaaS, IA, Max+, Marina+">
        <title><?= htmlspecialchars(strip_tags($GLIALE_TITLE)) ?></title>
        <link rel="icon" type="image/svg+xml" href="<?= IMG ?>icon/pmacontrol.svg">
        <link rel="stylesheet" href="<?= CSS ?>site.css">
        <script src="<?= WWW_ROOT ?>js/site.js" defer></script>
    </head>
    <body class="site-body" data-theme="light" data-lang="<?= htmlspecialchars($lang, ENT_QUOTES) ?>">
        <div class="site-frame">
            <header class="site-header">
                <div class="site-nav">
                    <a class="brand" href="<?= htmlspecialchars($baseLink.'site/index/', ENT_QUOTES) ?>" aria-label="PmaControl">
                        <img src="<?= IMG ?>icon/pmacontrol.svg" alt="PmaControl logo">
                        <div>
                            <strong>PmaControl</strong>
                            <span class="lang lang-fr">SQL augmenté</span>
                            <span class="lang lang-en">Augmented SQL</span>
                        </div>
                    </a>
                    <button class="nav-toggle" type="button" aria-label="Toggle menu" data-mobile-toggle>
                        <span></span><span></span><span></span>
                    </button>
                    <nav class="primary-nav" data-nav>
                        <ul>
                            <?php foreach ($menu as $item): ?>
                                <?php
                                $hasChildren = !empty($item['children']);
                                $isCta       = !empty($item['is_cta']);
                                $external    = !empty($item['external']);
                                $route       = $item['route'] ?? '';
                                $url         = $route;

                                if (!empty($route) && strpos($route, 'http') !== 0 && !$external) {
                                    $url = $baseLink.ltrim($route, '/');
                                }

                                $childIsActive = false;
                                if ($hasChildren) {
                                    foreach ($item['children'] as $child) {
                                        if (($child['id'] ?? '') === $activeItem) {
                                            $childIsActive = true;
                                            break;
                                        }
                                    }
                                }

                                $classes = [];
                                if ($hasChildren) {
                                    $classes[] = 'has-children';
                                }
                                if ($isCta) {
                                    $classes[] = 'is-cta';
                                }
                                if ($activeItem === $item['id'] || $childIsActive) {
                                    $classes[] = 'is-active';
                                }
                                $linkClasses = [];
                                if ($isCta) {
                                    $linkClasses[] = 'btn';
                                    $linkClasses[] = 'btn-primary';
                                } elseif ($item['id'] === 'login') {
                                    $linkClasses[] = 'btn';
                                    $linkClasses[] = 'btn-ghost';
                                }
                                $targetAttr = $external ? ' target="_blank" rel="noopener"' : '';
                                ?>
                                <li class="<?= implode(' ', $classes) ?>">
                                    <a href="<?= htmlspecialchars($url, ENT_QUOTES) ?>"<?= $linkClasses ? ' class="'.implode(' ', $linkClasses).'"' : '' ?><?= $targetAttr ?>>
                                        <?php pmac_site_lang($item['labels'] ?? []); ?>
                                    </a>
                                    <?php if ($hasChildren): ?>
                                        <ul class="submenu">
                                            <?php foreach ($item['children'] as $child): ?>
                                                <?php
                                                $childRoute = $child['route'] ?? '';
                                                $childUrl   = $childRoute;
                                                if (!empty($childRoute) && strpos($childRoute, 'http') !== 0) {
                                                    $childUrl = $baseLink.ltrim($childRoute, '/');
                                                }
                                                $childActive = $activeItem === $child['id'] ? ' class="is-active"' : '';
                                                ?>
                                                <li<?= $childActive ?>>
                                                    <a href="<?= htmlspecialchars($childUrl, ENT_QUOTES) ?>">
                                                        <?php pmac_site_lang($child['labels'] ?? []); ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="nav-actions">
                            <div class="language-switch" role="group" aria-label="Language switch">
                                <button type="button" class="lang-btn is-active" data-lang-btn="fr">FR</button>
                                <button type="button" class="lang-btn" data-lang-btn="en">EN</button>
                            </div>

                            <div class="language-switch" role="group" data-theme-toggle aria-label="Toggle dark mode">
                                <button type="button" class="lang-btn is-active" data-lang-btn="fr">☀️</button>
                                <button type="button" class="lang-btn" data-lang-btn="en">🌙</button>
                            </div>



                        </div>
                    </nav>
                </div>
            </header>

            <main class="site-main-frame">
                <?php get_flash(); ?>
                <div class="nav-aligned-container">
                    <?= $GLIALE_CONTENT ?>
                </div>
            </main>

            <footer class="site-footer">
                <div>
                    <strong>PmaControl</strong>
                    <span><?php pmac_site_lang(['fr' => 'The SQL brain behind your infrastructure.', 'en' => 'The SQL brain behind your infrastructure.']); ?></span>
                </div>
                <div>
                    <span>📞 <?= htmlspecialchars($phone, ENT_QUOTES) ?></span>
                    <a href="mailto:contact@pmacontrol.com">contact@pmacontrol.com</a>
                </div>
                <div class="footer-slogans">
                    <span><?php pmac_site_lang(['fr' => 'Don’t wait for failure — predict it.', 'en' => 'Don’t wait for failure — predict it.']); ?></span>
                    <span><?php pmac_site_lang(['fr' => 'Backups ne sont pas du recovery.', 'en' => 'Backups are not recovery.']); ?></span>
                </div>
            </footer>
        </div>
    </body>
</html>
