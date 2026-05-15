<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MenuLanguageMenuPlacementTest extends TestCase
{
    public function testLanguageMenuIsFirstChildOfSettingsMenu(): void
    {
        if (!defined('LINK')) {
            define('LINK', '/pmacontrol/fr/');
        }
        if (!defined('IMG')) {
            define('IMG', '/pmacontrol/image/');
        }
        if (!defined('WWW_ROOT')) {
            define('WWW_ROOT', '/pmacontrol/');
        }
        if (!defined('SITE_NAME')) {
            define('SITE_NAME', 'PmaControl');
        }
        if (!defined('SITE_VERSION')) {
            define('SITE_VERSION', 'test');
        }
        if (!defined('SITE_LAST_UPDATE')) {
            define('SITE_LAST_UPDATE', 'test');
        }
        if (!function_exists('__')) {
            eval('function __($s) { return $s; }');
        }

        $data = [
            'position' => 'top',
            'selectedmenu' => 0,
            'language_menu' => [
                'current_flag' => '🇫🇷',
                'current_short_code' => 'FR',
                'items' => [
                    ['code' => 'fr', 'label' => 'French', 'flag' => '🇫🇷', 'short_code' => 'FR', 'url' => '/pmacontrol/fr/home/index', 'active' => true],
                    ['code' => 'en', 'label' => 'English', 'flag' => '🇬🇧', 'short_code' => 'EN', 'url' => '/pmacontrol/en/home/index', 'active' => false],
                ],
            ],
            'menu' => [
                [
                    'id' => 113,
                    'parent_id' => '92',
                    'bg' => 1,
                    'bd' => 6,
                    'dropdown' => 121,
                    'icon' => '<span class="glyphicon glyphicon-cog"></span>',
                    'title' => 'Settings',
                    'url' => '',
                ],
                [
                    'id' => 121,
                    'parent_id' => '113',
                    'bg' => 2,
                    'bd' => 3,
                    'dropdown' => null,
                    'icon' => '',
                    'title' => 'Users',
                    'url' => '{LINK}user/index',
                ],
                [
                    'id' => 137,
                    'parent_id' => '92',
                    'bg' => 7,
                    'bd' => 8,
                    'dropdown' => null,
                    'icon' => '',
                    'title' => 'Logout',
                    'url' => '{LINK}user/logout',
                ],
            ],
        ];
        $hadGlialPath = array_key_exists('glial_path', $_GET);
        $previousGlialPath = $_GET['glial_path'] ?? null;
        $_GET['glial_path'] = 'fr/home/index';

        $bufferLevel = ob_get_level();
        ob_start();
        try {
            include __DIR__.'/../../App/view/Menu/show.view.php';
            $html = (string) ob_get_clean();
        } finally {
            if ($hadGlialPath) {
                $_GET['glial_path'] = $previousGlialPath;
            } else {
                unset($_GET['glial_path']);
            }

            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }
        }

        $settingsDropdownPosition = strpos($html, 'aria-labelledby="drop1"');
        $languagePosition = strpos($html, 'id="drop-language-settings"');
        $usersPosition = strpos($html, '> Users</a>');

        $this->assertNotFalse($settingsDropdownPosition);
        $this->assertNotFalse($languagePosition);
        $this->assertNotFalse($usersPosition);
        $this->assertGreaterThan($settingsDropdownPosition, $languagePosition);
        $this->assertLessThan($usersPosition, $languagePosition);
        $this->assertStringNotContainsString('id="drop-language"', $html);
        $this->assertStringContainsString('<span class="pmacontrol-language-emoji" aria-hidden="true">🇫🇷</span>', $html);
        $this->assertStringContainsString('<span class="pmacontrol-language-code">EN</span>', $html);
    }
}
