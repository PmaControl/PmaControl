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
    // Issue #773 / #1233 follow-up: window during which the previous verifier
    // stays valid after a rotation. Must cover any in-flight parallel request
    // that read the row before the rotation committed, *and* the very common
    // pattern where a user is idle on /home/index for a few minutes (no AJAX
    // poll, cooldown elapses), then clicks a navigation link from a tab whose
    // Set-Cookie hadn't yet propagated through the browser cookie jar (the
    // canonical token_mismatch reproducer in real-world traces). 5 minutes
    // covers that without weakening token-theft detection in any meaningful
    // way — a stolen verifier still becomes invalid as soon as ANY legit
    // request rotates again.
    public const PREVIOUS_TOKEN_GRACE_SECONDS = 300;
    // Issue #773 / #1233 follow-up: minimum interval between two rotations.
    // AJAX bursts already keep date_last_used fresh, so rotation never fires
    // on busy sessions anyway. Make the cooldown long enough that an idle
    // tab returning to focus after a coffee break doesn't trigger a rotation
    // whose Set-Cookie can race a follow-up click — the same scenario that
    // shipped users to /AuthSession/tokenMismatch repeatedly in May 2026.
    // 1 hour matches roughly the median desk-away duration and keeps the
    // rotation rate at "occasional refresh" rather than "every interaction".
    public const ROTATE_COOLDOWN_SECONDS = 3600;

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
        ?int $now = null,
        ?bool $isAjax = null
    ): bool {
        $parsed = self::parseCookieValue($cookies[self::COOKIE_NAME] ?? null);
        if ($parsed === null) {
            return false;
        }

        $row = self::findSession($db, $parsed['selector']);
        if (!is_object($row)) {
            self::logRevoke($parsed['selector'], 'unknown_selector', $server);
            self::deleteOpaqueCookie($server, $trustedProxies, $setter);
            return false;
        }

        $sessionId = (int) ($row->persistent_auth_session_id ?? 0);
        if ($sessionId <= 0) {
            self::logRevoke($parsed['selector'], 'invalid_session_id', $server);
            self::revokeSelector($db, $parsed['selector'], $now);
            self::deleteOpaqueCookie($server, $trustedProxies, $setter);
            return false;
        }

        // Issue #773: classify the verifier against the current and (optionally)
        // the previous token hash. This is what lets parallel in-flight
        // requests survive a rotation that committed between their SELECT and
        // their own would-be UPDATE.
        $verifierState = self::classifyVerifier($row, $parsed['verifier'], $now);
        if ($verifierState === 'invalid') {
            self::logRevoke($parsed['selector'], 'token_mismatch', $server);
            self::revokeSelector($db, $parsed['selector'], $now);
            self::deleteOpaqueCookie($server, $trustedProxies, $setter);
            return false;
        }

        if (self::isExpired((string) ($row->date_expires ?? ''), $now)
            || self::isExpired((string) ($row->date_absolute_expires ?? ''), $now)) {
            self::logRevoke($parsed['selector'], 'expired', $server);
            self::revokeSelector($db, $parsed['selector'], $now);
            self::deleteOpaqueCookie($server, $trustedProxies, $setter);
            return false;
        }

        if (!self::matchesFingerprint($row, $server)) {
            self::logRevoke($parsed['selector'], 'fingerprint_mismatch', $server);
            self::revokeSelector($db, $parsed['selector'], $now);
            self::deleteOpaqueCookie($server, $trustedProxies, $setter);
            return false;
        }

        if (!self::applyAuthenticatedUser($auth, $row)) {
            self::logRevoke($parsed['selector'], 'auth_apply_failed', $server);
            self::revokeSelector($db, $parsed['selector'], $now);
            self::deleteOpaqueCookie($server, $trustedProxies, $setter);
            return false;
        }

        // Verifier matched the previous (rotated) token: another concurrent
        // request already issued the new opaque cookie. Authenticate normally
        // but do NOT touch the row and do NOT emit a Set-Cookie — the browser
        // will pick up the rotated cookie from the winning response.
        if ($verifierState === 'previous') {
            return true;
        }

        $usedAt = self::formatDate($now ?? time());
        $expiresAt = self::nextSlidingExpiry($row, $now);
        $isAjaxRequest = $isAjax ?? self::detectAjax($server);
        $shouldRotate = !$isAjaxRequest && self::isRotationDue($row, $now);

        if (!$shouldRotate) {
            // Cooldown / AJAX path: just keep the row warm. Skip the CAS
            // entirely so multiple parallel touches can succeed concurrently.
            self::query($db, self::buildTouchSql($sessionId, $usedAt, $expiresAt, $db));
            return true;
        }

        $cookie = self::generateCookieForSelector($parsed['selector'], $randomBytes);
        $oldTokenHash = self::hashVerifier($parsed['verifier']);
        $previousExpiresAt = self::formatDate(($now ?? time()) + self::PREVIOUS_TOKEN_GRACE_SECONDS);

        if (!self::queryAffectingOne($db, self::buildRotateSql(
            $sessionId,
            $cookie['token_hash'],
            $oldTokenHash,
            $previousExpiresAt,
            $expiresAt,
            $usedAt,
            $db
        ))) {
            // Lost the rotation race. The user is still authenticated above;
            // a parallel request rotated the row and our verifier is now in
            // `previous_token_hash` (still valid for PREVIOUS_TOKEN_GRACE_SECONDS).
            // Do not revoke and do not send a new cookie — the winning response
            // already did.
            self::logRotationLostRace($parsed['selector'], $server);
            return true;
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

        // Issue #762: only switch the user from legacy cookies to the opaque
        // cookie *after* the INSERT into user_persistent_auth_session is
        // confirmed. If the INSERT failed (e.g. the migration was not applied
        // and the table is missing) we keep the legacy cookies in place so
        // the user stays logged in via the legacy fallback in Glial Auth.
        if (!$created) {
            self::logIssueFailure($db, $idUser);
            return false;
        }

        self::setOpaqueCookie($cookie['value'], strtotime($expiresAt . ' UTC') ?: time(), $server, $trustedProxies, $setter);
        self::deleteLegacyCookies($server, $trustedProxies, $setter);

        return true;
    }

    /**
     * @param mixed $db
     */
    private static function logIssueFailure($db, int $idUser): void
    {
        $error = '';
        if (is_object($db) && method_exists($db, 'sql_error')) {
            try {
                $error = (string) $db->sql_error();
            } catch (Throwable $throwable) {
                $error = '';
            }
        }

        error_log(sprintf(
            'PersistentAuthSession: failed to insert opaque token row for user_main.id=%d (db error: %s) — keeping legacy cookies as fallback. Has the migration sql/incremental_v2/20260502_persistent_auth_sessions.sql been applied?',
            $idUser,
            $error === '' ? 'unknown' : $error
        ));
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
        return "SELECT s.id AS persistent_auth_session_id, s.token_hash, "
            . "s.previous_token_hash, s.previous_token_expires, "
            . "s.user_agent_hash, s.ip_hash, "
            . "s.date_last_used, "
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
        string $previousExpiresAt,
        string $expiresAt,
        string $usedAt,
        $db = null
    ): string
    {
        // Issue #773: keep the old hash in `previous_token_hash` so any
        // parallel in-flight request that still carries the previous verifier
        // can authenticate within PREVIOUS_TOKEN_GRACE_SECONDS instead of
        // racing the rotation and revoking the session.
        return "UPDATE `user_persistent_auth_session` SET "
            . "`previous_token_hash` = `token_hash`, "
            . "`previous_token_expires` = '" . self::escapeLiteral($previousExpiresAt, $db) . "', "
            . "`token_hash` = '" . self::escapeLiteral($newTokenHash, $db) . "', "
            . "`date_last_used` = '" . self::escapeLiteral($usedAt, $db) . "', "
            . "`date_expires` = '" . self::escapeLiteral($expiresAt, $db) . "' "
            . "WHERE `id` = " . $sessionId . " AND `date_revoked` IS NULL "
            . "AND `token_hash` = '" . self::escapeLiteral($oldTokenHash, $db) . "'";
    }

    /**
     * Touch a session row without rotating the verifier. Used during the
     * cooldown window and on AJAX requests where a rotation would only burn
     * cycles and risk a race.
     *
     * @param mixed $db
     */
    public static function buildTouchSql(
        int $sessionId,
        string $usedAt,
        string $expiresAt,
        $db = null
    ): string
    {
        return "UPDATE `user_persistent_auth_session` SET "
            . "`date_last_used` = '" . self::escapeLiteral($usedAt, $db) . "', "
            . "`date_expires` = '" . self::escapeLiteral($expiresAt, $db) . "' "
            . "WHERE `id` = " . $sessionId . " AND `date_revoked` IS NULL";
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

    /**
     * Issue #773: classify the verifier presented by the request against
     * both the current and previous token hash. Returning 'previous' means
     * the request is in flight from before the latest rotation and must be
     * accepted (within the grace window) without re-rotating or replacing
     * the cookie.
     *
     * @return string 'current' | 'previous' | 'invalid'
     */
    public static function classifyVerifier(object $row, string $verifier, ?int $now): string
    {
        $candidate = self::hashVerifier($verifier);

        $current = (string) ($row->token_hash ?? '');
        if (preg_match(self::VERIFIER_PATTERN, $current) && hash_equals($current, $candidate)) {
            return 'current';
        }

        $previous = (string) ($row->previous_token_hash ?? '');
        $previousExpires = (string) ($row->previous_token_expires ?? '');
        if ($previous !== ''
            && preg_match(self::VERIFIER_PATTERN, $previous)
            && hash_equals($previous, $candidate)
            && !self::isExpired($previousExpires, $now)) {
            return 'previous';
        }

        return 'invalid';
    }

    private static function matchesFingerprint(object $row, array $server): bool
    {
        $fingerprint = self::fingerprint($server);
        if (!self::matchesOptionalHash((string) ($row->user_agent_hash ?? ''), $fingerprint['user_agent_hash'])) {
            return false;
        }

        return self::matchesOptionalHash((string) ($row->ip_hash ?? ''), $fingerprint['ip_hash']);
    }

    public static function isRotationDue(object $row, ?int $now): bool
    {
        $lastUsedRaw = trim((string) ($row->date_last_used ?? ''));
        if ($lastUsedRaw === '') {
            return true;
        }

        $lastUsed = strtotime($lastUsedRaw . ' UTC');
        if ($lastUsed === false) {
            return true;
        }

        return (($now ?? time()) - $lastUsed) >= self::ROTATE_COOLDOWN_SECONDS;
    }

    public static function detectAjax(array $server): bool
    {
        $requestedWith = trim((string) ($server['HTTP_X_REQUESTED_WITH'] ?? ''));
        if (strcasecmp($requestedWith, 'XMLHttpRequest') === 0) {
            return true;
        }

        if (defined('IS_AJAX') && constant('IS_AJAX') === true) {
            return true;
        }

        return false;
    }

    private static function logRevoke(string $selector, string $reason, array $server): void
    {
        $remote = (string) ($server['REMOTE_ADDR'] ?? '');
        $ua     = (string) ($server['HTTP_USER_AGENT'] ?? '');
        error_log(sprintf(
            'PersistentAuthSession: revoking selector=%s… reason=%s remote=%s ua=%s',
            substr($selector, 0, 8),
            $reason,
            $remote,
            substr($ua, 0, 32)
        ));
        // Audit module #1235: spool the revoke into auth_event for the
        // SuperAdmin audit timeline. Best-effort, never blocks the auth path.
        if (class_exists(\App\Library\Audit\AuthEventCollector::class)) {
            try {
                \App\Library\Audit\AuthEventCollector::logPersistentRevoke($reason, $selector, $remote, $ua !== '' ? $ua : null);
            } catch (\Throwable $e) {
                // Already swallowed inside the collector; this catch is belt-and-braces.
            }
        }
    }

    private static function logRotationLostRace(string $selector, array $server): void
    {
        error_log(sprintf(
            'PersistentAuthSession: rotation_lost_race selector=%s… remote=%s — accepted via grace window (issue #773)',
            substr($selector, 0, 8),
            (string) ($server['REMOTE_ADDR'] ?? '')
        ));
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
