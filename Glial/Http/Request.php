<?php

namespace Glial\Http;

class Request
{
    public static function isMethod(array $server, string $expected): bool
    {
        return strtoupper((string) ($server['REQUEST_METHOD'] ?? '')) === strtoupper($expected);
    }

    public static function isSameSite(array $server, ?string $currentOrigin = null): bool
    {
        $origin = $server['HTTP_ORIGIN'] ?? null;
        if (is_string($origin) && $origin !== '') {
            return self::isSameSiteUrl($origin, $server, $currentOrigin);
        }

        $referer = $server['HTTP_REFERER'] ?? null;
        if (is_string($referer) && $referer !== '') {
            return self::isSameSiteUrl($referer, $server, $currentOrigin);
        }

        return false;
    }

    public static function isSameSiteUrl(string $url, array $server, ?string $currentOrigin = null): bool
    {
        if ($url === 'null' || str_starts_with($url, '//')) {
            return false;
        }

        $parts = parse_url($url);
        if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
            return false;
        }

        $current = parse_url($currentOrigin ?? self::getCurrentOrigin($server));
        if ($current === false || empty($current['scheme']) || empty($current['host'])) {
            return false;
        }

        return strcasecmp((string) $parts['scheme'], (string) $current['scheme']) === 0
            && strcasecmp((string) $parts['host'], (string) $current['host']) === 0
            && self::normalizePort($parts) === self::normalizePort($current);
    }

    public static function getCurrentOrigin(array $server): string
    {
        $host = (string) ($server['HTTP_HOST'] ?? $server['SERVER_NAME'] ?? '');
        $scheme = (!empty($server['HTTPS']) && strtolower((string) $server['HTTPS']) !== 'off') ? 'https' : 'http';

        return $scheme . '://' . $host;
    }

    private static function normalizePort(array $parts): int
    {
        if (!empty($parts['port'])) {
            return (int) $parts['port'];
        }

        return strtolower((string) ($parts['scheme'] ?? '')) === 'https' ? 443 : 80;
    }
}
