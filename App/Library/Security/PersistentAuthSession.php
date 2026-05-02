<?php

declare(strict_types=1);

namespace App\Library\Security;

use ReflectionClass;
use ReflectionObject;
use ReflectionProperty;
use Throwable;

final class PersistentAuthSession
{
    public const COOKIE_NAME = 'pmacontrol_persistent_auth';
    public const LEGACY_COOKIE_LOGIN = '3QafE7C6RSXTzGw6';
    public const LEGACY_COOKIE_PASSWORD = 'RU2M5wpaAvpEqeDz';
    public const DEFAULT_TTL_SECONDS = 1209600;
    public const DEFAULT_ABSOLUTE_TTL_SECONDS = 2592000;
    public const MAX_ACTIVE_SESSIONS_PER_USER = 10;

    private const SELECTOR_BYTES = 16;
    private const VERIFIER_BYTES = 32;
    private const SELECTOR_PATTERN = '/\A[a-f0-9]{32}\z/i';
    private const VERIFIER_PATTERN = '/\A[a-f0-9]{64}\z/i';

    /**
     * @param mixed $value
     * @return array{selector:string,verifier:string}|null
     */
    public static function parseCookieValue($value): ?array
    {
        if (!is_scalar($value)) {
            return null;
        }

        $parts = explode('.', trim((string) $value), 2);
        if (count($parts) !== 2) {
            return null;
        }

        [$selector, $verifier] = $parts;
        if (!preg_match(self::SELECTOR_PATTERN, $selector) || !preg_match(self::VERIFIER_PATTERN, $verifier)) {
            return null;
        }

        return [
            'selector' => strtolower($selector),
            'verifier' => strtolower($verifier),
        ];
    }

    public static function hashVerifier(string $verifier): string
    {
        return hash('sha256', strtolower($verifier));
    }

    /**
     * @return array{user_agent_hash:?string,ip_hash:?string}
     */
    public static function fingerprint(array $server): array
    {
        $userAgent = trim((string) ($server['HTTP_USER_AGENT'] ?? ''));
        $ip = trim((string) ($server['REMOTE_ADDR'] ?? ''));

        return [
            'user_agent_hash' => $userAgent === '' ? null : hash('sha256', $userAgent),
            'ip_hash' => $ip === '' ? null : hash('sha256', $ip),
        ];
    }

    public static function ttlSeconds(): int
    {
        if (defined('AUTH_SESSION_TIME')) {
            $ttl = (int) constant('AUTH_SESSION_TIME');
            if ($ttl > 0) {
                return $ttl;
            }
        }

        return self::DEFAULT_TTL_SECONDS;
    }

    public static function absoluteTtlSeconds(): int
    {
        if (defined('AUTH_SESSION_ABSOLUTE_TIME')) {
            $ttl = (int) constant('AUTH_SESSION_ABSOLUTE_TIME');
            if ($ttl > 0) {
                return $ttl;
            }
        }

        return self::DEFAULT_ABSOLUTE_TTL_SECONDS;
    }

    /**
     * @return array{selector:string,verifier:string,value:string,token_hash:string}
     */
    public static function generateCookie(?callable $randomBytes = null): array
    {
        $randomBytes = $randomBytes ?? 'random_bytes';
        $selector = bin2hex($randomBytes(self::SELECTOR_BYTES));
        $verifier = bin2hex($randomBytes(self::VERIFIER_BYTES));

        return self::cookieFromParts($selector, $verifier);
    }

    /**
     * @return array{selector:string,verifier:string,value:string,token_hash:string}
     */
    public static function generateCookieForSelector(string $selector, ?callable $randomBytes = null): array
    {
        $randomBytes = $randomBytes ?? 'random_bytes';
        $verifier = bin2hex($randomBytes(self::VERIFIER_BYTES));

        return self::cookieFromParts($selector, $verifier);
    }

    /**
     * @return array{selector:string,verifier:string,value:string,token_hash:string}
     */
    private static function cookieFromParts(string $selector, string $verifier): array
    {
        return [
            'selector' => strtolower($selector),
            'verifier' => strtolower($verifier),
            'value' => strtolower($selector) . '.' . strtolower($verifier),
            'token_hash' => self::hashVerifier($verifier),
        ];
    }

    /**
     * @param mixed $auth
     * @param mixed $db
     */
    public static function authenticate(
        $auth,
        $db,
        array $cookies,
        array $server,
        ?array $trustedProxies = null,
        ?callable $setter = null,
        ?callable $randomBytes = null,
        ?int $now = null
    ): bool {
        $parsed = self::parseCookieValue($cookies[self::COOKIE_NAME] ?? null);
        if ($parsed === null) {
            return false;
        }

        $row = self::findSession($db, $parsed['selector']);
        if (!is_object($row)) {
            self::deleteOpaqueCookie($server, $trustedProxies, $setter);
            return false;
        }

        $sessionId = (int) ($row->persistent_auth_session_id ?? 0);
        if ($sessionId <= 0 || !self::isUsableSession($row, $parsed['verifier'], $server, $now)) {
            self::revokeSelector($db, $parsed['selector'], $now);
            self::deleteOpaqueCookie($server, $trustedProxies, $setter);
            return false;
        }

        $cookie = self::generateCookieForSelector($parsed['selector'], $randomBytes);
        $usedAt = self::formatDate($now ?? time());
        $expiresAt = self::nextSlidingExpiry($row, $now);
        $oldTokenHash = self::hashVerifier($parsed['verifier']);
        if (!self::queryAffectingOne($db, self::buildRotateSql($sessionId, $cookie['token_hash'], $oldTokenHash, $expiresAt, $usedAt, $db))) {
            self::revokeSelector($db, $parsed['selector'], $now);
            self::deleteOpaqueCookie($server, $trustedProxies, $setter);
            return false;
        }

        if (!self::applyAuthenticatedUser($auth, $row)) {
            self::revokeSelector($db, $parsed['selector'], $now);
            self::deleteOpaqueCookie($server, $trustedProxies, $setter);
            return false;
        }

        self::setOpaqueCookie($cookie['value'], strtotime($expiresAt . ' UTC') ?: time(), $server, $trustedProxies, $setter);

        return true;
    }

    /**
     * @param mixed $auth
     * @param mixed $db
     */
    public static function issueForAuthenticatedUser(
        $auth,
        $db,
        array $server,
        ?array $trustedProxies = null,
        ?callable $setter = null,
        ?callable $randomBytes = null,
        ?int $now = null
    ): bool {
        $user = self::currentAuthUser($auth);
        $idUser = self::extractUserId($user);
        if ($idUser <= 0) {
            return false;
        }

        $cookie = self::generateCookie($randomBytes);
        $fingerprint = self::fingerprint($server);
        $createdAt = self::formatDate($now ?? time());
        $absoluteExpiresAt = self::formatDate(($now ?? time()) + self::absoluteTtlSeconds());
        $expiresAt = self::nextExpiryWithinAbsolute($now ?? time(), $absoluteExpiresAt);

        self::cleanupForUser($db, $idUser, $createdAt, self::MAX_ACTIVE_SESSIONS_PER_USER - 1);
        $created = self::query($db, self::buildInsertSql(
            $idUser,
            $cookie['selector'],
            $cookie['token_hash'],
            $fingerprint['user_agent_hash'],
            $fingerprint['ip_hash'],
            $createdAt,
            $expiresAt,
            $absoluteExpiresAt,
            $db
        ));

        self::deleteLegacyCookies($server, $trustedProxies, $setter);
        if (!$created) {
            self::deleteOpaqueCookie($server, $trustedProxies, $setter);
            return false;
        }

        self::setOpaqueCookie($cookie['value'], strtotime($expiresAt . ' UTC') ?: time(), $server, $trustedProxies, $setter);

        return true;
    }

    /**
     * @param mixed $db
     */
    public static function revokeCurrent(
        $db,
        array $cookies,
        array $server,
        ?array $trustedProxies = null,
        ?callable $setter = null,
        ?int $now = null
    ): void {
        $parsed = self::parseCookieValue($cookies[self::COOKIE_NAME] ?? null);
        if ($parsed !== null) {
            self::revokeSelector($db, $parsed['selector'], $now);
        }

        self::deleteOpaqueCookie($server, $trustedProxies, $setter);
        self::deleteLegacyCookies($server, $trustedProxies, $setter);
    }

    public static function hasLegacyCookies(array $cookies): bool
    {
        return !empty($cookies[self::LEGACY_COOKIE_LOGIN]) && !empty($cookies[self::LEGACY_COOKIE_PASSWORD]);
    }

    /**
     * @param mixed $db
     */
    public static function buildSelectSql(string $selector, $db = null): string
    {
        return "SELECT s.id AS persistent_auth_session_id, s.token_hash, s.user_agent_hash, s.ip_hash, "
            . "s.date_expires, s.date_absolute_expires, s.date_revoked, u.* "
            . "FROM `user_persistent_auth_session` s "
            . "INNER JOIN `user_main` u ON u.id = s.id_user_main "
            . "WHERE s.selector = '" . self::escapeLiteral($selector, $db) . "' "
            . "AND s.date_revoked IS NULL LIMIT 1";
    }

    /**
     * @param mixed $db
     */
    public static function buildInsertSql(
        int $idUser,
        string $selector,
        string $tokenHash,
        ?string $userAgentHash,
        ?string $ipHash,
        string $createdAt,
        string $expiresAt,
        string $absoluteExpiresAt,
        $db = null
    ): string {
        return "INSERT INTO `user_persistent_auth_session` "
            . "(`id_user_main`, `selector`, `token_hash`, `user_agent_hash`, `ip_hash`, "
            . "`date_created`, `date_last_used`, `date_expires`, `date_absolute_expires`) VALUES ("
            . $idUser . ", "
            . "'" . self::escapeLiteral($selector, $db) . "', "
            . "'" . self::escapeLiteral($tokenHash, $db) . "', "
            . self::nullableString($userAgentHash, $db) . ", "
            . self::nullableString($ipHash, $db) . ", "
            . "'" . self::escapeLiteral($createdAt, $db) . "', "
            . "NULL, "
            . "'" . self::escapeLiteral($expiresAt, $db) . "', "
            . "'" . self::escapeLiteral($absoluteExpiresAt, $db) . "')";
    }

    /**
     * @param mixed $db
     */
    public static function buildRotateSql(
        int $sessionId,
        string $newTokenHash,
        string $oldTokenHash,
        string $expiresAt,
        string $usedAt,
        $db = null
    ): string
    {
        return "UPDATE `user_persistent_auth_session` SET "
            . "`token_hash` = '" . self::escapeLiteral($newTokenHash, $db) . "', "
            . "`date_last_used` = '" . self::escapeLiteral($usedAt, $db) . "', "
            . "`date_expires` = '" . self::escapeLiteral($expiresAt, $db) . "' "
            . "WHERE `id` = " . $sessionId . " AND `date_revoked` IS NULL "
            . "AND `token_hash` = '" . self::escapeLiteral($oldTokenHash, $db) . "'";
    }

    /**
     * @param mixed $db
     */
    public static function buildRevokeSql(string $selector, string $revokedAt, $db = null): string
    {
        return "UPDATE `user_persistent_auth_session` SET "
            . "`date_revoked` = '" . self::escapeLiteral($revokedAt, $db) . "' "
            . "WHERE `selector` = '" . self::escapeLiteral($selector, $db) . "' AND `date_revoked` IS NULL";
    }

    /**
     * @param mixed $db
     */
    public static function buildCleanupExpiredSql(string $now, $db = null): string
    {
        return "DELETE FROM `user_persistent_auth_session` "
            . "WHERE `date_revoked` IS NOT NULL "
            . "OR `date_expires` < '" . self::escapeLiteral($now, $db) . "' "
            . "OR `date_absolute_expires` < '" . self::escapeLiteral($now, $db) . "'";
    }

    public static function buildCapActiveSessionsSql(int $idUser, int $keepActive): string
    {
        $keepActive = max(0, $keepActive);

        return "DELETE FROM `user_persistent_auth_session` "
            . "WHERE `id_user_main` = " . $idUser . " AND `date_revoked` IS NULL "
            . "AND `id` NOT IN ("
            . "SELECT `id` FROM ("
            . "SELECT `id` FROM `user_persistent_auth_session` "
            . "WHERE `id_user_main` = " . $idUser . " AND `date_revoked` IS NULL "
            . "ORDER BY COALESCE(`date_last_used`, `date_created`) DESC, `id` DESC "
            . "LIMIT " . $keepActive
            . ") keep_rows)";
    }

    /**
     * @return array<int,string>
     */
    public static function legacyCookieNames(): array
    {
        return [self::LEGACY_COOKIE_LOGIN, self::LEGACY_COOKIE_PASSWORD];
    }

    public static function deleteLegacyCookies(
        array $server,
        ?array $trustedProxies = null,
        ?callable $setter = null
    ): void {
        foreach (self::legacyCookieNames() as $name) {
            CookieSecurity::setCookie($name, '', time() - 3600, $server, '/', self::cookieDomain($server), true, $trustedProxies, $setter);
        }
    }

    public static function deleteOpaqueCookie(
        array $server,
        ?array $trustedProxies = null,
        ?callable $setter = null
    ): void {
        CookieSecurity::setCookie(self::COOKIE_NAME, '', time() - 3600, $server, '/', self::cookieDomain($server), true, $trustedProxies, $setter);
    }

    public static function setOpaqueCookie(
        string $value,
        int $expires,
        array $server,
        ?array $trustedProxies = null,
        ?callable $setter = null
    ): bool {
        return CookieSecurity::setCookie(self::COOKIE_NAME, $value, $expires, $server, '/', self::cookieDomain($server), true, $trustedProxies, $setter);
    }

    /**
     * @param mixed $db
     * @return object|null
     */
    private static function findSession($db, string $selector)
    {
        $result = self::queryResult($db, self::buildSelectSql($selector, $db));
        if ($result === false || !method_exists($db, 'sql_num_rows') || $db->sql_num_rows($result) !== 1) {
            return null;
        }

        if (!method_exists($db, 'sql_fetch_object')) {
            return null;
        }

        $row = $db->sql_fetch_object($result);
        return is_object($row) ? $row : null;
    }

    private static function isUsableSession(object $row, string $verifier, array $server, ?int $now): bool
    {
        $storedHash = (string) ($row->token_hash ?? '');
        if (!preg_match(self::VERIFIER_PATTERN, $storedHash) || !hash_equals($storedHash, self::hashVerifier($verifier))) {
            return false;
        }

        if (self::isExpired((string) ($row->date_expires ?? ''), $now)) {
            return false;
        }

        if (self::isExpired((string) ($row->date_absolute_expires ?? ''), $now)) {
            return false;
        }

        $fingerprint = self::fingerprint($server);
        if (!self::matchesOptionalHash((string) ($row->user_agent_hash ?? ''), $fingerprint['user_agent_hash'])) {
            return false;
        }

        return self::matchesOptionalHash((string) ($row->ip_hash ?? ''), $fingerprint['ip_hash']);
    }

    private static function matchesOptionalHash(string $storedHash, ?string $currentHash): bool
    {
        $storedHash = trim($storedHash);
        if ($storedHash === '') {
            return true;
        }

        return $currentHash !== null && hash_equals($storedHash, $currentHash);
    }

    private static function isExpired(string $expiresAt, ?int $now): bool
    {
        $expiresAtTimestamp = strtotime(trim($expiresAt) . ' UTC');
        if ($expiresAtTimestamp === false) {
            return true;
        }

        return $expiresAtTimestamp <= ($now ?? time());
    }

    private static function nextSlidingExpiry(object $row, ?int $now): string
    {
        return self::nextExpiryWithinAbsolute($now ?? time(), (string) ($row->date_absolute_expires ?? ''));
    }

    private static function nextExpiryWithinAbsolute(int $now, string $absoluteExpiresAt): string
    {
        $candidate = $now + self::ttlSeconds();
        $absolute = strtotime(trim($absoluteExpiresAt) . ' UTC');
        if ($absolute !== false) {
            $candidate = min($candidate, $absolute);
        }

        return self::formatDate($candidate);
    }

    /**
     * @param mixed $auth
     */
    private static function applyAuthenticatedUser($auth, object $user): bool
    {
        $idUser = self::extractUserId($user);
        if ($idUser <= 0 || !is_object($auth)) {
            return false;
        }

        try {
            $object = new ReflectionObject($auth);
            $class = new ReflectionClass($auth);

            if (!$object->hasProperty('_user') || !$object->hasProperty('id_user') || !$class->hasProperty('id_user_main')) {
                return false;
            }

            $userProperty = $object->getProperty('_user');
            self::allowPropertyAccess($userProperty);
            $userProperty->setValue($auth, $user);

            $idUserProperty = $object->getProperty('id_user');
            self::allowPropertyAccess($idUserProperty);
            $idUserProperty->setValue($auth, $idUser);

            $idUserMainProperty = $class->getProperty('id_user_main');
            self::allowPropertyAccess($idUserMainProperty);
            $idUserMainProperty->setValue(null, $idUser);
        } catch (Throwable $throwable) {
            return false;
        }

        return true;
    }

    private static function allowPropertyAccess(ReflectionProperty $property): void
    {
        if (PHP_VERSION_ID < 80100) {
            $property->setAccessible(true);
        }
    }

    /**
     * @param mixed $auth
     * @return object|null
     */
    private static function currentAuthUser($auth)
    {
        if (!is_object($auth) || !method_exists($auth, 'getUser')) {
            return null;
        }

        $user = $auth->getUser();
        return is_object($user) ? $user : null;
    }

    private static function extractUserId(?object $user): int
    {
        if ($user === null || !isset($user->id) || !is_numeric($user->id)) {
            return 0;
        }

        $id = (int) $user->id;
        return $id > 0 ? $id : 0;
    }

    /**
     * @param mixed $db
     */
    private static function revokeSelector($db, string $selector, ?int $now): bool
    {
        return self::query($db, self::buildRevokeSql($selector, self::formatDate($now ?? time()), $db));
    }

    /**
     * @param mixed $db
     */
    private static function cleanupForUser($db, int $idUser, string $now, int $keepActive): void
    {
        self::query($db, self::buildCleanupExpiredSql($now, $db));
        self::query($db, self::buildCapActiveSessionsSql($idUser, $keepActive));
    }

    /**
     * @param mixed $db
     */
    private static function query($db, string $sql): bool
    {
        return self::queryResult($db, $sql) !== false;
    }

    /**
     * @param mixed $db
     */
    private static function queryAffectingOne($db, string $sql): bool
    {
        if (self::queryResult($db, $sql) === false || !is_object($db) || !method_exists($db, 'sql_affected_rows')) {
            return false;
        }

        try {
            return (int) $db->sql_affected_rows() === 1;
        } catch (Throwable $throwable) {
            return false;
        }
    }

    /**
     * @param mixed $db
     * @return mixed
     */
    private static function queryResult($db, string $sql)
    {
        if (!is_object($db) || !method_exists($db, 'sql_query')) {
            return false;
        }

        try {
            return $db->sql_query($sql);
        } catch (Throwable $throwable) {
            return false;
        }
    }

    private static function formatDate(int $timestamp): string
    {
        return gmdate('Y-m-d H:i:s', $timestamp);
    }

    private static function cookieDomain(array $server): string
    {
        return trim((string) ($server['SERVER_NAME'] ?? ''));
    }

    /**
     * @param mixed $db
     */
    private static function nullableString(?string $value, $db = null): string
    {
        return $value === null || $value === '' ? 'NULL' : "'" . self::escapeLiteral($value, $db) . "'";
    }

    /**
     * @param mixed $db
     */
    private static function escapeLiteral(string $value, $db = null): string
    {
        if (is_object($db) && method_exists($db, 'sql_real_escape_string')) {
            try {
                return (string) $db->sql_real_escape_string($value);
            } catch (Throwable $throwable) {
                return str_replace(["\\", "'", "\0", "\r", "\n", "\x1a"], ["\\\\", "\\'", "\\0", "\\r", "\\n", "\\Z"], $value);
            }
        }

        return str_replace(
            ["\\", "'", "\0", "\r", "\n", "\x1a"],
            ["\\\\", "\\'", "\\0", "\\r", "\\n", "\\Z"],
            $value
        );
    }
}
