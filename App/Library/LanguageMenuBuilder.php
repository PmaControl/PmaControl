<?php

namespace App\Library;

/**
 * Build language switcher data without exposing a controller action.
 */
final class LanguageMenuBuilder
{
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
    public static function build(
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

        if ($parts === []) {
            return defined('ROUTE_DEFAULT') ? trim(ROUTE_DEFAULT, '/') : 'home/index';
        }

        return implode('/', array_map([self::class, 'encodeRouteSegment'], $parts));
    }

/**
 * Encode a route segment without encoding the colon used by existing route parameters.
 *
 * @param string $segment Route segment.
 * @return string Encoded route segment.
 */
    private static function encodeRouteSegment(string $segment): string
    {
        return str_replace('%3A', ':', rawurlencode($segment));
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
}
