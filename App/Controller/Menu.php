<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Synapse\FactoryController;
use \Glial\Sgbd\Sgbd;
use \Glial\I18n\I18n;


/**
 * Class responsible for menu workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Menu extends Controller {

/**
 * Handle menu state through `show`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $params Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $params
 * @psalm-param array<int,mixed> $params
 * @return void Returned value for show.
 * @phpstan-return void
 * @psalm-return void
 * @see self::show()
 * @example /fr/menu/show
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function show($params) {
        $id_menu = $params[0];
        //debug($id_menu);

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT b.*, MAX(c.id) AS dropdown FROM `menu_group` a
INNER JOIN `menu` b ON a.id = b.group_id
LEFT JOIN `menu` c ON b.bg < c.bg AND b.bd > c.bd AND c.active = 1
WHERE b.active = 1 and b.parent_id is not null and a.id='" . $id_menu . "' GROUP BY b.id "
                . "ORDER BY b.bg";



        $data['sql'] = $sql;
        $data['menu'] = $db->sql_fetch_yield($sql);

        switch ($id_menu) {
            case 1:
                $data['position'] = "top";
                break;

            case 2:
                $data['position'] = "bottom";
                break;
            case 3:
                $data['position'] = "top";
                break;
        }

        $data['selectedmenu'] = $this->getSelectedLevelOneMenu($id_menu);
        $currentLanguage = I18n::Get();
        if (!is_string($currentLanguage) || $currentLanguage === '') {
            $currentLanguage = isset($_GET['lg']) ? (string) $_GET['lg'] : '';
        }

        $data['language_menu'] = self::buildLanguageMenu(
            isset($_GET['glial_path']) ? (string) $_GET['glial_path'] : '',
            isset($_SERVER['QUERY_STRING']) ? (string) $_SERVER['QUERY_STRING'] : '',
            $currentLanguage,
            LANGUAGE_AVAILABLE,
            WWW_ROOT,
            I18n::$languagesUTF8
        );

        $this->set('data', $data);
    }

/**
 * Build language switcher entries for the current route.
 *
 * @param string $glialPath Current routed path, including the language prefix.
 * @param string $queryString Original query string.
 * @param string $currentLanguage Current language code.
 * @param string $availableLanguages Comma-separated language list.
 * @param string $wwwRoot Application root URL.
 * @param array<string,string> $languageLabels Language labels indexed by code.
 * @return array{current:string,current_label:string,current_flag:string,current_short_code:string,items:array<int,array{code:string,label:string,flag:string,short_code:string,url:string,active:bool}>}
 */
    public static function buildLanguageMenu(
        string $glialPath,
        string $queryString,
        string $currentLanguage,
        string $availableLanguages,
        string $wwwRoot,
        array $languageLabels = []
    ): array {
        $route     = self::getLanguageSwitchRoute($glialPath);
        $query     = self::getLanguageSwitchQueryString($queryString);
        $root      = rtrim($wwwRoot, '/').'/';
        $languages = array_values(array_filter(array_map('trim', explode(',', $availableLanguages)), static fn (string $language): bool => $language !== ''));

        $items            = [];
        $currentLabel     = $currentLanguage;
        $currentFlag      = self::getLanguageFlag($currentLanguage);
        $currentShortCode = self::getLanguageShortCode($currentLanguage);

        foreach ($languages as $language) {
            $label     = isset($languageLabels[$language]) ? $languageLabels[$language] : $language;
            $flag      = self::getLanguageFlag($language);
            $shortCode = self::getLanguageShortCode($language);

            if ($language === $currentLanguage) {
                $currentLabel     = $label;
                $currentFlag      = $flag;
                $currentShortCode = $shortCode;
            }

            $items[] = [
                'code'       => $language,
                'label'      => $label,
                'flag'       => $flag,
                'short_code' => $shortCode,
                'url'        => $root.rawurlencode($language).'/'.$route.($query !== '' ? '?'.$query : ''),
                'active'     => $language === $currentLanguage,
            ];
        }

        self::sortLanguageMenuItems($items, $currentLanguage);

        return [
            'current'            => $currentLanguage,
            'current_label'      => $currentLabel,
            'current_flag'       => $currentFlag,
            'current_short_code' => $currentShortCode,
            'items'              => $items,
        ];
    }

/**
 * Sort language entries alphabetically by their rendered label.
 *
 * @param array<int,array{code:string,label:string,flag:string,short_code:string,url:string,active:bool}> $items Menu entries.
 * @param string $currentLanguage Current language code used as collation locale.
 * @return void
 */
    private static function sortLanguageMenuItems(array &$items, string $currentLanguage): void
    {
        $collator = null;
        if (class_exists('\\Collator')) {
            $collator = new \Collator(self::getLanguageSortLocale($currentLanguage));
        }

        usort($items, static function (array $left, array $right) use ($collator): int {
            return self::compareLanguageLabels((string) $left['label'], (string) $right['label'], $collator);
        });
    }

/**
 * Compare language labels with locale-aware collation when available.
 *
 * @param string $left Left label.
 * @param string $right Right label.
 * @param mixed $collator Optional \Collator instance.
 * @return int Sort comparison result.
 */
    private static function compareLanguageLabels(string $left, string $right, $collator = null): int
    {
        if ($collator instanceof \Collator) {
            $comparison = $collator->compare($left, $right);
            if ($comparison !== false && $comparison !== 0) {
                return $comparison <=> 0;
            }
        }

        $normalizedLeft  = function_exists('mb_strtolower') ? mb_strtolower($left, 'UTF-8') : strtolower($left);
        $normalizedRight = function_exists('mb_strtolower') ? mb_strtolower($right, 'UTF-8') : strtolower($right);

        return $normalizedLeft <=> $normalizedRight;
    }

/**
 * Return the locale used to sort language labels.
 *
 * @param string $language Language code.
 * @return string ICU locale.
 */
    private static function getLanguageSortLocale(string $language): string
    {
        $locales = [
            'ar'    => 'ar_SA',
            'ru'    => 'ru_RU',
            'pl'    => 'pl_PL',
            'fr'    => 'fr_FR',
            'en'    => 'en_GB',
            'zh-cn' => 'zh_CN',
        ];

        return isset($locales[$language]) ? $locales[$language] : str_replace('-', '_', $language);
    }

/**
 * Return the flag glyph used by the public language selector.
 *
 * @param string $language Language code.
 * @return string Flag glyph or an empty string when no flag is mapped.
 */
    private static function getLanguageFlag(string $language): string
    {
        $flags = [
            'ar'    => '🇸🇦',
            'ru'    => '🇷🇺',
            'pl'    => '🇵🇱',
            'fr'    => '🇫🇷',
            'en'    => '🇬🇧',
            'zh-cn' => '🇨🇳',
        ];

        return isset($flags[$language]) ? $flags[$language] : '';
    }

/**
 * Return the short display code used next to the flag.
 *
 * @param string $language Language code.
 * @return string Uppercase language code shortened for display.
 */
    private static function getLanguageShortCode(string $language): string
    {
        $shortCodes = [
            'zh-cn' => 'ZH',
        ];

        return isset($shortCodes[$language]) ? $shortCodes[$language] : strtoupper($language);
    }

/**
 * Keep the current route and only remove the language prefix.
 *
 * @param string $glialPath Current routed path.
 * @return string Route without language prefix.
 */
    private static function getLanguageSwitchRoute(string $glialPath): string
    {
        $parts = array_values(array_filter(explode('/', trim($glialPath, '/')), static fn (string $part): bool => $part !== ''));

        if ($parts !== []) {
            array_shift($parts);
        }

        $route = implode('/', $parts);
        if ($route === '') {
            return defined('ROUTE_DEFAULT') ? trim(ROUTE_DEFAULT, '/') : 'home/index';
        }

        return $route;
    }

/**
 * Keep public query-string parameters and drop the internal rewritten path.
 *
 * @param string $queryString Original query string.
 * @return string Query string safe to append to language links.
 */
    private static function getLanguageSwitchQueryString(string $queryString): string
    {
        if ($queryString === '') {
            return '';
        }

        $query = [];
        parse_str($queryString, $query);
        unset($query['glial_path']);

        return http_build_query($query);
    }

/**
 * Retrieve menu state through `getSelectedLevelOneMenu`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param int $id_menu Input value for `id_menu`.
 * @phpstan-param int $id_menu
 * @psalm-param int $id_menu
 * @return mixed Returned value for getSelectedLevelOneMenu.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getSelectedLevelOneMenu()
 * @example /fr/menu/getSelectedLevelOneMenu
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getSelectedLevelOneMenu($id_menu) {
        $Array = FactoryController::getRootNode();

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "WITH a as (SELECT bg,bd, group_id FROM menu where `class`='" . $Array[0] . "' AND `method` = '" . $Array[1] . "' AND parent_id is not null LIMIT 1)
            SELECT * FROM menu b,a WHERE b.bg <= a.bg AND b.bd >= a.bg AND a.group_id = b.group_id AND parent_id is not null ORDER by b.bg";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            return $ob->id;
        }
    }
}
