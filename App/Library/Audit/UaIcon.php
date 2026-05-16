<?php

declare(strict_types=1);

namespace App\Library\Audit;

/**
 * UA → icon mapper for the AuditLog UI (#1235 post-MVP step 2).
 *
 * Returns Font Awesome class names (the icon font ships with the
 * project under `App/Webroot/css/font-awesome.css`) plus a brand-
 * coloured background. Stays inside the existing icon set instead of
 * pulling in the bbclone GIF/PNG pack — vector icons render crisp at
 * any DPR, no additional HTTP request, and the colour palette lines
 * up with the rest of the UI.
 *
 * Pure / static so views can call it directly.
 */
final class UaIcon
{
    /**
     * Returns the OS family icon snippet (HTML).
     */
    public static function osIcon(?string $family): string
    {
        $map = [
            'Windows'  => ['fa fa-windows',  '#0078d4'],
            'macOS'    => ['fa fa-apple',    '#1c1c1e'],
            'iOS'      => ['fa fa-apple',    '#1c1c1e'],
            'iPadOS'   => ['fa fa-apple',    '#1c1c1e'],
            'Linux'    => ['fa fa-linux',    '#f59e0b'],
            'Android'  => ['fa fa-android',  '#3ddc84'],
            'ChromeOS' => ['fa fa-chrome',   '#4c8bf5'],
            'FreeBSD'  => ['fa fa-server',   '#ab2b28'],
            'OpenBSD'  => ['fa fa-server',   '#fff100'],
        ];
        return self::render($map, $family, 'fa fa-desktop', '#64748b');
    }

    /**
     * Returns the browser family icon snippet (HTML).
     */
    public static function browserIcon(?string $family): string
    {
        $map = [
            'Chrome'  => ['fa fa-chrome',   '#4285f4'],
            'Firefox' => ['fa fa-firefox',  '#ff7139'],
            'Safari'  => ['fa fa-safari',   '#1e88e5'],
            'Edge'    => ['fa fa-edge',     '#0078d7'],
            'Opera'   => ['fa fa-opera',    '#ff1b2d'],
            'Vivaldi' => ['fa fa-globe',    '#ef3939'],
            'Brave'   => ['fa fa-shield',   '#fb542b'],
            'curl'    => ['fa fa-terminal', '#073642'],
            'wget'    => ['fa fa-terminal', '#073642'],
        ];
        return self::render($map, $family, 'fa fa-globe', '#64748b');
    }

    /**
     * Returns the robot icon snippet (HTML). Different colour palette
     * so robot rows stand out at a glance.
     */
    public static function robotIcon(?string $family): string
    {
        if ($family === null || $family === '') {
            return '';
        }
        // Hard-code a few high-traffic crawlers; everything else gets a
        // generic bug icon in amber.
        $map = [
            'Googlebot'      => ['fa fa-google',       '#4285f4'],
            'Bingbot'        => ['fa fa-bing',         '#008373'],
            'DuckDuckBot'    => ['fa fa-duckduckgo',   '#de5833'],
            'Applebot'       => ['fa fa-apple',        '#1c1c1e'],
            'YandexBot'      => ['fa fa-yandex',       '#ff0000'],
            'Twitterbot'     => ['fa fa-twitter',      '#1da1f2'],
            'LinkedInBot'    => ['fa fa-linkedin',     '#0a66c2'],
            'FacebookExternalHit' => ['fa fa-facebook', '#1877f2'],
            'curl'           => ['fa fa-terminal',     '#073642'],
            'wget'           => ['fa fa-terminal',     '#073642'],
            'python-requests'=> ['fa fa-terminal',     '#3776ab'],
            'Go-http-client' => ['fa fa-terminal',     '#00add8'],
            'HeadlessChrome' => ['fa fa-chrome',       '#92400e'],
            'PhantomJS'      => ['fa fa-ghost',        '#92400e'],
        ];
        return self::render($map, $family, 'fa fa-bug', '#b45309');
    }

    /**
     * Device-type fallback icon (mobile / tablet / desktop).
     */
    public static function deviceIcon(?string $type): string
    {
        $map = [
            'mobile'  => ['fa fa-mobile', '#0ea5e9'],
            'tablet'  => ['fa fa-tablet', '#0ea5e9'],
            'desktop' => ['fa fa-desktop', '#64748b'],
        ];
        return self::render($map, $type, 'fa fa-question-circle', '#cbd5e1');
    }

    /**
     * @param array<string,array{0:string,1:string}> $map family → [icon class, hex colour]
     */
    private static function render(array $map, ?string $family, string $fallbackIcon, string $fallbackColor): string
    {
        $label = (string) ($family ?? '');
        if (isset($map[$label])) {
            [$cls, $color] = $map[$label];
        } else {
            $cls   = $fallbackIcon;
            $color = $fallbackColor;
        }
        return sprintf(
            '<i class="%s audit-ua-icon" style="color:%s" title="%s"></i>',
            htmlspecialchars($cls),
            htmlspecialchars($color),
            htmlspecialchars($label !== '' ? $label : 'unknown')
        );
    }
}
