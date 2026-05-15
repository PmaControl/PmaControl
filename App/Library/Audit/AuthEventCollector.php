<?php

declare(strict_types=1);

namespace App\Library\Audit;

/**
 * Spool writer for `auth_event` rows (#1235).
 *
 * Mirrors RequestAuditCollector — appends one NDJSON line per auth-pipeline
 * event under `tmp/audit/auth_events/<date>.ndjson`. Called from:
 *   - User::connection (login_success, login_fail)
 *   - User::logout (logout)
 *   - PersistentAuthSession::logRevoke (token_mismatch, fingerprint_mismatch,
 *     expired, unknown_selector, auth_apply_failed)
 *   - CsrfGuard::ensureOrFail (csrf_reject)
 *   - BasicAuthRateLimiter (rate_limit)
 *
 * Hot-path safe: no DB, no UA parsing, just an fwrite to a NDJSON file.
 */
final class AuthEventCollector
{
    private const SPOOL_DIR = '/auth_events/';

    public static function logLoginSuccess(int $idUserMain, string $ip, ?string $userAgent = null): void
    {
        self::log([
            'event_type'   => 'login_success',
            'id_user_main' => $idUserMain,
            'ip'           => $ip,
            'user_agent_raw' => $userAgent,
        ]);
    }

    public static function logLoginFail(?int $idUserMain, string $ip, ?string $userAgent = null, ?string $detail = null): void
    {
        self::log([
            'event_type'   => 'login_fail',
            'id_user_main' => $idUserMain,
            'ip'           => $ip,
            'user_agent_raw' => $userAgent,
            'detail'       => $detail,
        ]);
    }

    public static function logLogout(?int $idUserMain, string $ip, ?string $userAgent = null): void
    {
        self::log([
            'event_type'   => 'logout',
            'id_user_main' => $idUserMain,
            'ip'           => $ip,
            'user_agent_raw' => $userAgent,
        ]);
    }

    /**
     * Persistent auth revoke — mirrors PersistentAuthSession::logRevoke().
     * Accepts the same `reason` string so the column maps 1:1.
     */
    public static function logPersistentRevoke(
        string $reason,
        string $selectorPrefix,
        string $ip,
        ?string $userAgent = null,
        ?int $idUserMain = null
    ): void {
        $allowed = ['token_mismatch','fingerprint_mismatch','expired','unknown_selector','auth_apply_failed','invalid_session_id'];
        if (!in_array($reason, $allowed, true)) {
            $reason = 'token_mismatch';
        }
        // Map the two app-internal-only reasons to the common bucket the
        // column enum understands. Caller-provided enums kept verbatim.
        if ($reason === 'auth_apply_failed' || $reason === 'invalid_session_id') {
            // Persist as token_mismatch + detail so the column enum stays tight.
            self::log([
                'event_type'      => 'token_mismatch',
                'ip'              => $ip,
                'user_agent_raw'  => $userAgent,
                'id_user_main'    => $idUserMain,
                'selector_prefix' => substr($selectorPrefix, 0, 12),
                'detail'          => $reason,
            ]);
            return;
        }
        self::log([
            'event_type'      => $reason,
            'ip'              => $ip,
            'user_agent_raw'  => $userAgent,
            'id_user_main'    => $idUserMain,
            'selector_prefix' => substr($selectorPrefix, 0, 12),
        ]);
    }

    public static function logCsrfReject(?int $idUserMain, string $ip, ?string $userAgent = null, ?string $detail = null): void
    {
        self::log([
            'event_type'   => 'csrf_reject',
            'id_user_main' => $idUserMain,
            'ip'           => $ip,
            'user_agent_raw' => $userAgent,
            'detail'       => $detail,
        ]);
    }

    public static function logRateLimit(?int $idUserMain, string $ip, ?string $userAgent = null, ?string $detail = null): void
    {
        self::log([
            'event_type'   => 'rate_limit',
            'id_user_main' => $idUserMain,
            'ip'           => $ip,
            'user_agent_raw' => $userAgent,
            'detail'       => $detail,
        ]);
    }

    /**
     * Internal — actually compose + write the NDJSON line.
     * Adds the current request_uid + timestamp so the drain can correlate
     * back to the originating request_log row.
     *
     * @param array<string,mixed> $event
     */
    private static function log(array $event): void
    {
        try {
            $now = microtime(true);
            $event = array_merge([
                'request_uid' => RequestAuditCollector::requestUid(),
                'date'        => self::dateMillis($now),
            ], $event);
            // user_agent_hash is what the DB stores; raw is hashed here so
            // the drain can also upsert audit_user_agent in one pass.
            $uaRaw = $event['user_agent_raw'] ?? null;
            if ($uaRaw !== null && $uaRaw !== '') {
                $event['user_agent_hash'] = md5((string) $uaRaw);
            }
            self::write($event);
        } catch (\Throwable $e) {
            error_log('AuthEventCollector log failed: ' . $e->getMessage());
        }
    }

    private static function write(array $event): void
    {
        $dir = self::spoolDir();
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return;
        }
        $path = $dir . date('Y-m-d') . '.ndjson';
        $line = json_encode($event, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($line === false) {
            return;
        }
        $fh = @fopen($path, 'a');
        if ($fh === false) {
            return;
        }
        @fwrite($fh, $line . "\n");
        @fclose($fh);
    }

    public static function spoolDir(): string
    {
        if (defined('TMP')) {
            return rtrim(\constant('TMP'), '/') . '/audit' . self::SPOOL_DIR;
        }
        return dirname(__DIR__, 3) . '/tmp/audit' . self::SPOOL_DIR;
    }

    private static function dateMillis(float $when): string
    {
        $s = (int) $when;
        $ms = (int) (($when - $s) * 1000);
        return date('Y-m-d H:i:s', $s) . sprintf('.%03d', $ms);
    }
}
