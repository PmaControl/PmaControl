<?php

declare(strict_types=1);

namespace App\Library\Security;

final class BasicAuthRateLimiter
{
    public const TABLE = 'webservice_auth_failure';
    public const MAX_FAILURES = 5;
    public const WINDOW_SECONDS = 900;
    public const LOCK_SECONDS = 900;
    public const IP_BUCKET_USER = '__ip__';
    public const PURGE_AFTER_SECONDS = 2592000;
    private const USER_KEY_MAX_LENGTH = 64;
    private const REMOTE_ADDR_KEY_MAX_LENGTH = 45;

    public static function remoteAddr(array $server): string
    {
        return self::normalizeKeyPart($server['REMOTE_ADDR'] ?? 'unknown', self::REMOTE_ADDR_KEY_MAX_LENGTH);
    }

    public static function normalizeUser($user): string
    {
        return self::normalizeKeyPart($user, self::USER_KEY_MAX_LENGTH, true);
    }

    public static function check($db, string $user, string $remoteAddr, ?int $now = null): array
    {
        $now = $now ?? time();

        return self::mostRestrictiveOutcome([
            self::evaluateState(self::fetchState($db, $user, $remoteAddr), $now),
            self::evaluateState(self::fetchState($db, self::IP_BUCKET_USER, $remoteAddr), $now),
        ]);
    }

    public static function recordFailure($db, string $user, string $remoteAddr, ?int $now = null): array
    {
        $now = $now ?? time();
        self::purgeOldRows($db, $now);

        $outcomes = [];
        foreach ([$user, self::IP_BUCKET_USER] as $bucketUser) {
            self::query($db, self::buildUpsertFailureSql($db, $bucketUser, $remoteAddr, $now));
            self::query($db, self::buildLockIfThresholdSql($db, $bucketUser, $remoteAddr, $now));
            $outcomes[] = self::evaluateState(self::fetchState($db, $bucketUser, $remoteAddr), $now);
        }

        return self::mostRestrictiveOutcome($outcomes);
    }

    public static function clearFailures($db, string $user, string $remoteAddr): void
    {
        // Keep the global IP bucket intact; a valid user must not reset IP-wide throttling.
        self::query($db, self::buildClearFailuresSql($db, $user, $remoteAddr));
    }

    public static function evaluateState(?array $state, int $now): array
    {
        $blockedUntil = self::timestamp($state['blocked_until'] ?? null);
        if ($blockedUntil !== null && $blockedUntil > $now) {
            return [
                'allowed' => false,
                'retry_after' => max(1, $blockedUntil - $now),
                'blocked_until' => self::formatTimestamp($blockedUntil),
            ];
        }

        return [
            'allowed' => true,
            'retry_after' => 0,
            'blocked_until' => null,
        ];
    }

    public static function nextFailureState(?array $state, int $now): array
    {
        $blockedUntil = self::timestamp($state['blocked_until'] ?? null);
        if ($blockedUntil !== null && $blockedUntil > $now) {
            return [
                'failure_count' => max(1, (int)($state['failure_count'] ?? self::MAX_FAILURES)),
                'window_started_at' => self::normalizeDate($state['window_started_at'] ?? null, $now),
                'last_failed_at' => self::formatTimestamp($now),
                'blocked_until' => self::formatTimestamp($blockedUntil),
            ];
        }

        $windowStarted = self::timestamp($state['window_started_at'] ?? null);
        if ($windowStarted === null || $windowStarted + self::WINDOW_SECONDS <= $now) {
            $failureCount = 1;
            $windowStarted = $now;
        } else {
            $failureCount = max(0, (int)($state['failure_count'] ?? 0)) + 1;
        }

        $newBlockedUntil = $failureCount >= self::MAX_FAILURES ? $now + self::LOCK_SECONDS : null;

        return [
            'failure_count' => $failureCount,
            'window_started_at' => self::formatTimestamp($windowStarted),
            'last_failed_at' => self::formatTimestamp($now),
            'blocked_until' => $newBlockedUntil === null ? null : self::formatTimestamp($newBlockedUntil),
        ];
    }

    public static function buildFetchStateSql($db, string $user, string $remoteAddr): string
    {
        return "SELECT failure_count, window_started_at, last_failed_at, blocked_until "
            ."FROM `".self::TABLE."` "
            ."WHERE `user`='".$db->sql_real_escape_string($user)."' "
            ."AND `remote_addr`='".$db->sql_real_escape_string($remoteAddr)."' "
            ."LIMIT 1";
    }

    public static function buildUpsertFailureSql($db, string $user, string $remoteAddr, int $now): string
    {
        $nowDate = self::formatTimestamp($now);
        $windowResetBefore = self::formatTimestamp($now - self::WINDOW_SECONDS);
        $nowSql = "'".$db->sql_real_escape_string($nowDate)."'";
        $windowResetBeforeSql = "'".$db->sql_real_escape_string($windowResetBefore)."'";

        return "INSERT INTO `".self::TABLE."` "
            ."(`user`, `remote_addr`, `failure_count`, `window_started_at`, `last_failed_at`, `blocked_until`) VALUES ("
            ."'".$db->sql_real_escape_string($user)."', "
            ."'".$db->sql_real_escape_string($remoteAddr)."', "
            ."1, "
            .$nowSql.", "
            .$nowSql.", "
            ."NULL) "
            ."ON DUPLICATE KEY UPDATE "
            ."`failure_count` = CASE "
            ."WHEN `blocked_until` IS NOT NULL AND `blocked_until` > ".$nowSql." THEN `failure_count` "
            ."WHEN `window_started_at` <= ".$windowResetBeforeSql." THEN 1 "
            ."ELSE `failure_count` + 1 END, "
            ."`window_started_at` = CASE "
            ."WHEN `blocked_until` IS NOT NULL AND `blocked_until` > ".$nowSql." THEN `window_started_at` "
            ."WHEN `window_started_at` <= ".$windowResetBeforeSql." THEN ".$nowSql." "
            ."ELSE `window_started_at` END, "
            ."`last_failed_at` = ".$nowSql;
    }

    public static function buildLockIfThresholdSql($db, string $user, string $remoteAddr, int $now): string
    {
        $nowDate = self::formatTimestamp($now);
        $lockUntil = self::formatTimestamp($now + self::LOCK_SECONDS);

        return "UPDATE `".self::TABLE."` SET "
            ."`blocked_until` = '".$db->sql_real_escape_string($lockUntil)."' "
            ."WHERE `user`='".$db->sql_real_escape_string($user)."' "
            ."AND `remote_addr`='".$db->sql_real_escape_string($remoteAddr)."' "
            ."AND (`blocked_until` IS NULL OR `blocked_until` <= '".$db->sql_real_escape_string($nowDate)."') "
            ."AND `failure_count` >= ".self::MAX_FAILURES;
    }

    public static function buildClearFailuresSql($db, string $user, string $remoteAddr): string
    {
        return "DELETE FROM `".self::TABLE."` "
            ."WHERE `user`='".$db->sql_real_escape_string($user)."' "
            ."AND `remote_addr`='".$db->sql_real_escape_string($remoteAddr)."'";
    }

    public static function buildPurgeOldRowsSql($db, int $now): string
    {
        $cutoff = self::formatTimestamp($now - self::PURGE_AFTER_SECONDS);

        return "DELETE FROM `".self::TABLE."` "
            ."WHERE `last_failed_at` < '".$db->sql_real_escape_string($cutoff)."' "
            ."AND (`blocked_until` IS NULL OR `blocked_until` < '".$db->sql_real_escape_string($cutoff)."')";
    }

    private static function fetchState($db, string $user, string $remoteAddr): ?array
    {
        $res = self::queryResult($db, self::buildFetchStateSql($db, $user, $remoteAddr));
        if ($res === false || !is_object($db) || !method_exists($db, 'sql_fetch_object')) {
            return null;
        }

        try {
            $row = $db->sql_fetch_object($res);
        } catch (\Throwable $throwable) {
            return null;
        }
        if (!is_object($row)) {
            return null;
        }

        return [
            'failure_count' => (int)($row->failure_count ?? 0),
            'window_started_at' => $row->window_started_at ?? null,
            'last_failed_at' => $row->last_failed_at ?? null,
            'blocked_until' => $row->blocked_until ?? null,
        ];
    }

    private static function purgeOldRows($db, int $now): void
    {
        self::query($db, self::buildPurgeOldRowsSql($db, $now));
    }

    /**
     * @param array<int,array{allowed:bool,retry_after:int,blocked_until:?string}> $outcomes
     * @return array{allowed:bool,retry_after:int,blocked_until:?string}
     */
    private static function mostRestrictiveOutcome(array $outcomes): array
    {
        $blocked = array_values(array_filter($outcomes, static fn (array $outcome): bool => !$outcome['allowed']));
        if ($blocked === []) {
            return [
                'allowed' => true,
                'retry_after' => 0,
                'blocked_until' => null,
            ];
        }

        usort($blocked, static fn (array $left, array $right): int => $right['retry_after'] <=> $left['retry_after']);

        return $blocked[0];
    }

    private static function query($db, string $sql): bool
    {
        return self::queryResult($db, $sql) !== false;
    }

    private static function queryResult($db, string $sql)
    {
        if (!is_object($db) || !method_exists($db, 'sql_query')) {
            return false;
        }

        try {
            return $db->sql_query($sql);
        } catch (\Throwable $throwable) {
            return false;
        }
    }

    private static function normalizeKeyPart($value, int $maxLength, bool $foldCaseAndAccent = false): string
    {
        if (!is_scalar($value)) {
            return 'unknown';
        }

        $normalized = preg_replace('/[[:cntrl:]]+/', '', trim((string)$value));
        if (!is_string($normalized) || $normalized === '') {
            return 'unknown';
        }

        if ($foldCaseAndAccent) {
            $normalized = mb_strtolower($normalized, 'UTF-8');
            $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalized);
            if (is_string($transliterated) && $transliterated !== '') {
                $normalized = $transliterated;
            }
        }

        $normalized = mb_substr($normalized, 0, $maxLength);

        return $normalized === self::IP_BUCKET_USER ? 'unknown' : $normalized;
    }

    private static function normalizeDate($value, int $fallback): string
    {
        $timestamp = self::timestamp($value);

        return self::formatTimestamp($timestamp ?? $fallback);
    }

    private static function timestamp($value): ?int
    {
        if (!is_scalar($value) || (string)$value === '') {
            return null;
        }

        $timestamp = strtotime((string)$value);

        return $timestamp === false ? null : $timestamp;
    }

    private static function formatTimestamp(int $timestamp): string
    {
        return date('Y-m-d H:i:s', $timestamp);
    }
}
