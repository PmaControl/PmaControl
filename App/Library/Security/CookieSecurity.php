<?php

declare(strict_types=1);

namespace App\Library\Security;

final class CookieSecurity
{
    public const ENV_FORCE_SECURE = 'PMACONTROL_COOKIE_SECURE';
    public const ENV_SAMESITE = 'PMACONTROL_COOKIE_SAMESITE';
    public const ENV_TRUSTED_PROXIES = 'PMACONTROL_COOKIE_TRUSTED_PROXIES';
    public const SAMESITE_LAX = 'Lax';
    public const SAMESITE_STRICT = 'Strict';
    public const SAMESITE_NONE = 'None';

    /**
     * @param array<string,mixed> $server
     * @param array<int,string>|null $trustedProxies
     */
    public static function isHttpsRequest(array $server, ?array $trustedProxies = null): bool
    {
        $https = strtolower(trim((string) ($server['HTTPS'] ?? '')));
        if ($https !== '' && !in_array($https, ['off', '0', 'false', 'no'], true)) {
            return true;
        }

        if ((string) ($server['SERVER_PORT'] ?? '') === '443') {
            return true;
        }

        if (!self::isTrustedProxy((string) ($server['REMOTE_ADDR'] ?? ''), self::trustedProxies($trustedProxies))) {
            return false;
        }

        $forwardedProto = (string) ($server['HTTP_X_FORWARDED_PROTO'] ?? '');
        if ($forwardedProto !== '') {
            $proto = strtolower(trim((string) explode(',', $forwardedProto)[0]));
            if ($proto === 'https') {
                return true;
            }
        }

        $forwardedSsl = strtolower(trim((string) ($server['HTTP_X_FORWARDED_SSL'] ?? '')));
        if (in_array($forwardedSsl, ['on', '1', 'true', 'yes'], true)) {
            return true;
        }

        $frontEndHttps = strtolower(trim((string) ($server['HTTP_FRONT_END_HTTPS'] ?? '')));
        return in_array($frontEndHttps, ['on', '1', 'true', 'yes'], true);
    }

    /**
     * @param array<string,mixed> $server
     * @param array<int,string>|null $trustedProxies
     */
    public static function shouldUseSecureCookies(array $server, ?array $trustedProxies = null, ?string $configuredMode = null): bool
    {
        $mode = self::configuredValue(self::ENV_FORCE_SECURE, $configuredMode);
        if ($mode !== null) {
            $normalized = strtolower(trim($mode));
            if (in_array($normalized, ['1', 'true', 'on', 'yes'], true)) {
                return true;
            }
            if (in_array($normalized, ['0', 'false', 'off', 'no'], true)) {
                return false;
            }
        }

        return self::isHttpsRequest($server, $trustedProxies);
    }

    public static function sameSitePolicy(?string $configuredPolicy = null): string
    {
        $policy = self::configuredValue(self::ENV_SAMESITE, $configuredPolicy);
        $normalized = strtolower(trim((string) ($policy ?? self::SAMESITE_LAX)));

        if ($normalized === 'strict') {
            return self::SAMESITE_STRICT;
        }
        if ($normalized === 'none') {
            return self::SAMESITE_NONE;
        }

        return self::SAMESITE_LAX;
    }

    /**
     * @param array<int,string>|null $configuredProxies
     * @return array<int,string>
     */
    public static function trustedProxies(?array $configuredProxies = null): array
    {
        if ($configuredProxies !== null) {
            return self::normalizeProxyList($configuredProxies);
        }

        $env = getenv(self::ENV_TRUSTED_PROXIES);
        if (is_string($env)) {
            return self::normalizeProxyList($env);
        }

        if (defined(self::ENV_TRUSTED_PROXIES)) {
            return self::normalizeProxyList(constant(self::ENV_TRUSTED_PROXIES));
        }

        return [];
    }

    /**
     * @param array<string,mixed> $server
     * @param array<int,string>|null $trustedProxies
     * @param array<string,mixed>|null $currentParams
     * @return array{lifetime:int,path:string,domain:string,secure:bool,httponly:bool,samesite:string}
     */
    public static function sessionCookieParams(
        array $server,
        ?array $trustedProxies = null,
        ?array $currentParams = null
    ): array {
        $currentParams = $currentParams ?? session_get_cookie_params();
        $sameSite = self::sameSitePolicy();

        return [
            'lifetime' => (int) ($currentParams['lifetime'] ?? 0),
            'path' => (string) ($currentParams['path'] ?? '/'),
            'domain' => (string) ($currentParams['domain'] ?? ''),
            'secure' => self::secureCookieFlag($server, $trustedProxies, $sameSite),
            'httponly' => true,
            'samesite' => $sameSite,
        ];
    }

    /**
     * @param array<string,mixed> $server
     * @param array<int,string>|null $trustedProxies
     */
    public static function configureSessionCookies(array $server, ?array $trustedProxies = null): bool
    {
        if (session_status() !== PHP_SESSION_NONE || headers_sent()) {
            return false;
        }

        $params = self::sessionCookieParams($server, $trustedProxies);

        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', $params['secure'] ? '1' : '0');
        ini_set('session.cookie_samesite', $params['samesite']);

        return session_set_cookie_params($params);
    }

    /**
     * @param array<string,mixed> $server
     * @param array<int,string>|null $trustedProxies
     * @return array{expires:int,path:string,secure:bool,httponly:bool,samesite:string,domain?:string}
     */
    public static function cookieOptions(
        array $server,
        int $expires,
        string $path = '/',
        string $domain = '',
        bool $httpOnly = true,
        ?array $trustedProxies = null
    ): array {
        $sameSite = self::sameSitePolicy();
        $options = [
            'expires' => $expires,
            'path' => $path,
            'secure' => self::secureCookieFlag($server, $trustedProxies, $sameSite),
            'httponly' => $httpOnly,
            'samesite' => $sameSite,
        ];

        if ($domain !== '') {
            $options['domain'] = $domain;
        }

        return $options;
    }

    /**
     * @param array<string,mixed> $server
     * @param array<int,string>|null $trustedProxies
     */
    public static function setCookie(
        string $name,
        string $value,
        int $expires,
        array $server,
        string $path = '/',
        string $domain = '',
        bool $httpOnly = true,
        ?array $trustedProxies = null,
        ?callable $setter = null
    ): bool {
        $options = self::cookieOptions($server, $expires, $path, $domain, $httpOnly, $trustedProxies);
        $setter = $setter ?? static function (string $cookieName, string $cookieValue, array $cookieOptions): bool {
            return setcookie($cookieName, $cookieValue, $cookieOptions);
        };

        return (bool) $setter($name, $value, $options);
    }

    public static function hardenSetCookieHeader(string $rawHeader, bool $secure, ?string $sameSite = null): string
    {
        $sameSite = self::sameSitePolicy($sameSite);
        if ($sameSite === self::SAMESITE_NONE) {
            $secure = true;
        }

        $header = trim($rawHeader);
        $prefix = '';
        $cookie = $header;

        if (stripos($header, 'Set-Cookie:') === 0) {
            $prefix = 'Set-Cookie: ';
            $cookie = trim(substr($header, strlen('Set-Cookie:')));
        }

        if ($cookie === '') {
            return $rawHeader;
        }

        $parts = array_map('trim', explode(';', $cookie));
        $nameValue = array_shift($parts);
        if ($nameValue === null || $nameValue === '') {
            return $rawHeader;
        }

        $attributes = [];
        $hasSecure = false;
        $existingSameSite = null;
        foreach ($parts as $attribute) {
            if ($attribute === '') {
                continue;
            }

            $normalized = strtolower($attribute);
            if ($normalized === 'secure') {
                $hasSecure = true;
                continue;
            }
            if ($normalized === 'httponly') {
                continue;
            }
            if (strpos($normalized, 'samesite=') === 0) {
                $existingSameSite = self::sameSitePolicy(substr($attribute, strlen('samesite=')));
                continue;
            }

            $attributes[] = $attribute;
        }

        $sameSite = self::effectiveSameSitePolicy($sameSite, $existingSameSite);
        if ($secure || $hasSecure || $sameSite === self::SAMESITE_NONE) {
            $attributes[] = 'Secure';
        }
        $attributes[] = 'HttpOnly';
        $attributes[] = 'SameSite='.$sameSite;

        return $prefix.$nameValue.'; '.implode('; ', $attributes);
    }

    /**
     * @param array<string,mixed> $server
     * @param array<int,string>|null $trustedProxies
     */
    public static function registerOutgoingCookieHardener(
        array $server,
        ?array $trustedProxies = null,
        ?callable $registrar = null
    ): bool {
        $registrar = $registrar ?? (function_exists('header_register_callback') ? 'header_register_callback' : null);
        if ($registrar === null) {
            return false;
        }

        $sameSite = self::sameSitePolicy();
        $secure = self::secureCookieFlag($server, $trustedProxies, $sameSite);

        $registrar(static function () use ($secure, $sameSite): void {
            self::hardenPendingSetCookieHeaders($secure, $sameSite);
        });

        return true;
    }

    public static function hardenPendingSetCookieHeaders(
        bool $secure,
        ?string $sameSite = null,
        ?callable $headersList = null,
        ?callable $headerRemove = null,
        ?callable $headerEmitter = null
    ): void {
        $headersList = $headersList ?? 'headers_list';
        $headerRemove = $headerRemove ?? 'header_remove';
        $headerEmitter = $headerEmitter ?? static function (string $headerLine, bool $replace): void {
            header($headerLine, $replace);
        };

        $cookieHeaders = [];
        foreach ($headersList() as $header) {
            if (stripos((string) $header, 'Set-Cookie:') === 0) {
                $cookieHeaders[] = self::hardenSetCookieHeader((string) $header, $secure, $sameSite);
            }
        }

        if ($cookieHeaders === []) {
            return;
        }

        $headerRemove('Set-Cookie');
        foreach ($cookieHeaders as $cookieHeader) {
            $headerEmitter($cookieHeader, false);
        }
    }

    /**
     * @param array<int,string>|null $trustedProxies
     */
    private static function secureCookieFlag(array $server, ?array $trustedProxies, string $sameSite): bool
    {
        if ($sameSite === self::SAMESITE_NONE) {
            return true;
        }

        return self::shouldUseSecureCookies($server, $trustedProxies);
    }

    private static function configuredValue(string $name, ?string $configuredValue): ?string
    {
        if ($configuredValue !== null) {
            return $configuredValue;
        }

        $env = getenv($name);
        if (is_string($env)) {
            return $env;
        }

        if (defined($name)) {
            $constant = constant($name);
            if (is_scalar($constant)) {
                return (string) $constant;
            }
        }

        return null;
    }

    private static function effectiveSameSitePolicy(string $configuredPolicy, ?string $existingPolicy): string
    {
        if ($existingPolicy === self::SAMESITE_STRICT) {
            return self::SAMESITE_STRICT;
        }

        if ($existingPolicy === self::SAMESITE_NONE) {
            return self::SAMESITE_NONE;
        }

        return $configuredPolicy;
    }

    /**
     * @param mixed $proxies
     * @return array<int,string>
     */
    private static function normalizeProxyList($proxies): array
    {
        if (is_string($proxies)) {
            $proxies = preg_split('/[\s,]+/', $proxies, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        if (!is_array($proxies)) {
            return [];
        }

        $normalized = [];
        foreach ($proxies as $proxy) {
            $proxy = trim((string) $proxy);
            if ($proxy !== '') {
                $normalized[] = $proxy;
            }
        }

        return array_values(array_unique($normalized));
    }

    /**
     * @param array<int,string> $trustedProxies
     */
    private static function isTrustedProxy(string $remoteAddress, array $trustedProxies): bool
    {
        if ($remoteAddress === '' || $trustedProxies === []) {
            return false;
        }

        foreach ($trustedProxies as $proxy) {
            if ($proxy === '*') {
                return true;
            }
            if ($proxy === $remoteAddress) {
                return true;
            }
            if (strpos($proxy, '/') !== false && self::ipMatchesCidr($remoteAddress, $proxy)) {
                return true;
            }
        }

        return false;
    }

    private static function ipMatchesCidr(string $ip, string $cidr): bool
    {
        [$network, $bits] = array_pad(explode('/', $cidr, 2), 2, null);
        if ($network === null || $bits === null || !ctype_digit($bits)) {
            return false;
        }

        $ipPacked = inet_pton($ip);
        $networkPacked = inet_pton($network);
        if ($ipPacked === false || $networkPacked === false || strlen($ipPacked) !== strlen($networkPacked)) {
            return false;
        }

        $bitLength = (int) $bits;
        $maxBits = strlen($ipPacked) * 8;
        if ($bitLength < 0 || $bitLength > $maxBits) {
            return false;
        }

        $fullBytes = intdiv($bitLength, 8);
        $remainingBits = $bitLength % 8;

        if ($fullBytes > 0 && substr($ipPacked, 0, $fullBytes) !== substr($networkPacked, 0, $fullBytes)) {
            return false;
        }

        if ($remainingBits === 0) {
            return true;
        }

        $mask = (0xff << (8 - $remainingBits)) & 0xff;
        return (ord($ipPacked[$fullBytes]) & $mask) === (ord($networkPacked[$fullBytes]) & $mask);
    }
}
