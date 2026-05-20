<?php

use \App\Library\Display;

$escape = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$isSettingsMenuItem = static function (array $item): bool {
    if (($item['code'] ?? '') === 'settings') {
        return true;
    }

    return (string) ($item['parent_id'] ?? '') === '92' && ($item['title'] ?? '') === 'Settings';
};

$renderLanguageMenu = static function (array $languageMenu, callable $escape): void {
    if (empty($languageMenu['items'])) {
        return;
    }

    $currentFlag      = $languageMenu['current_flag'] ?? '';
    $currentShortCode = $languageMenu['current_short_code'] ?? '';

    echo '<li class="dropdown-submenu pmacontrol-language-submenu" role="presentation">';
    echo '<a id="drop-language-settings" href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" role="button" aria-expanded="false">';
    echo '<span class="glyphicon glyphicon-globe" aria-hidden="true"></span> ';
    if ($currentFlag !== '') {
        echo '<span class="pmacontrol-language-emoji" aria-hidden="true">'.$escape($currentFlag).'</span>';
    }
    echo __("Languages");
    if ($currentShortCode !== '') {
        echo ' <span class="pmacontrol-language-current-code">'.$escape($currentShortCode).'</span>';
    }
    echo '</a>';
    echo '<ul class="dropdown-menu pmacontrol-language-menu" role="menu" aria-labelledby="drop-language-settings">';

    foreach ($languageMenu['items'] as $language) {
        $active    = !empty($language['active']) ? ' active' : '';
        $flag      = $language['flag'] ?? '';
        $shortCode = $language['short_code'] ?? strtoupper((string) $language['code']);

        echo '<li class="'.$active.'" role="presentation"><a class="pmacontrol-language-item" role="menuitem" tabindex="-1" href="'.$escape($language['url']).'" aria-label="'.$escape($language['label']).'">';
        if ($flag !== '') {
            echo '<span class="pmacontrol-language-emoji" aria-hidden="true">'.$escape($flag).'</span>';
        }
        echo '<span class="pmacontrol-language-code">'.$escape($shortCode).'</span>';
        echo '<span class="pmacontrol-language-name">'.$escape($language['label']).'</span>';
        echo '</a></li>'."\n";
    }

    echo '</ul></li>'."\n";
};

?>
<div>
    <nav class="navbar navbar-inverse navbar-static navbar-fixed-<?= $data['position'] ?>">

        <div class="container-fluid">

            <?php
            if ($data['position'] === "top"):
                ?>
                <div class="navbar-header">
                    <button class="navbar-toggle collapsed" type="button" data-toggle="collapse" data-target=".pmacontrol-js-navbar-collapse">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="<?= LINK ?>home/index" style="color:#fff"><span><img style="float:left" width="20px" height="20px" src="<?=IMG ?>icon/pmacontrol_b.svg">&nbsp;<?= SITE_NAME ?> <span class="badge badge-info" style="font-variant: small-caps; font-size: 14px; vertical-align: middle; background-color: #4384c7" title="<?= SITE_LAST_UPDATE ?>">v<?= SITE_VERSION ?></span></a>
                </div>
                <?php
            endif;
            ?>


            <?php
            $class = "";
            if ($data['position'] === "bottom") {
                $class = " pull-right";
            }
            ?>

            <div class="collapse navbar-collapse pmacontrol-js-navbar-collapse <?= $class ?>">
                <ul class="nav navbar-nav multi-level">
                    <?php
                    $close_at = [];
                    $i        = 1;


                    foreach ($data['menu'] as $item) {

                        $item['icon'] = Display::icon($item['icon']);


                        foreach ($close_at as $key => $to_close) {
                            if ($item['bg'] > $to_close) {
                                echo '</ul>
                                </li>'."\n";
                                unset($close_at[$key]);
                            }
                        }


                        $active = '';
                        if ($data['selectedmenu'] == $item["id"]) {
                            $active = ' active';
                        }

                        //debug($data['selectedmenu']);
                        //debug($item);


                        if (($item['bd'] - $item['bg'] > 1) && (!empty($item['dropdown']) )) {

                            // can change need to put an other solution with level inside of menu
                            if ($item['parent_id'] === "92") {
                                $class = "";
                            } else {
                                $class = "dropdown-submenu";
                            }




                            echo '
                                <li class="'.$class.' dropdown'.$active.'">
                                <a id="drop'.$i.'" href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" role="button" aria-expanded="false">
                                '.$item['icon'].' '.__($item['title']).'';

                            if ($item['parent_id'] === "92") {
                                echo '<span class="caret"></span>';
                            }



                            echo '</a>
                                <ul class="dropdown-menu" role="menu" aria-labelledby="drop'.$i.'">';

                            if ($data['position'] === "top" && $isSettingsMenuItem($item)) {
                                $renderLanguageMenu($data['language_menu'] ?? [], $escape);
                            }

                            $close_at[] = $item['bd'];
                            $i++;
                        } else {
                            
                            $item['url'] = str_replace(array('{LINK}'), array(LINK), $item['url']);

                            if (strstr($item['url'], '{PATH}')) {
                                $item['url'] = WWW_ROOT.str_replace('{PATH}', '', $item['url']).'/'.substr($_GET['glial_path'], 3);
                            }


                            $PATH = WWW_ROOT.substr($_GET['glial_path'], 3);


                            echo '<li class="'.$active.'" role="presentation"><a role="menuitem" tabindex="-1" href="'.$item['url'].'">'.$item['icon'].' '.__($item['title']).'</a></li>'."\n";
                        }
                    }
                    ?>


                </ul>
            </div>
        </div>
    </nav>
</div>
