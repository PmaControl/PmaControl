<?php

declare(strict_types=1);

namespace App\Library\Audit;

/**
 * Per-request audit collector (#1235).
 *
 * Captures one NDJSON row per HTTP request in `tmp/audit/requests/<date>.log`.
 * Never touches the database on the hot path — the `AuditDrain` CLI is what
 * INSERTs into `request_log` in batches.
 *
 * Generates a 32-char `request_uid` early so the controller, views, sub-process
 * launcher and client-side JS can all stamp the same correlation id on their
 * own events (`auth_event.request_uid`, `subprocess_log.request_uid`,
 * `audit_client_metrics.request_uid`).
 *
 * Booted via `register_shutdown_function` from `Bootstrap.php` after the route
 * is resolved. CLI runs are skipped (they have their own subprocess_log row).
 */
final class RequestAuditCollector
{
    private const SPOOL_DIR = '/requests/';

    /** @var string|null */
    private static $requestUid = null;
    /** @var float */
    private static $timeStart = 0.0;
    /** @var bool */
    private static $registered = false;
    /** @var array<string,mixed> */
    private static $extra = [];

    /**
     * Initialise the collector. Idempotent — safe to call multiple times.
     * Generates the request_uid (16 random bytes → 32 hex chars) and stamps
     * the start time. Registers the shutdown hook the first time around.
     *
     * @param float|null $timeStart Microtime captured at the very top of the
     *                              entry script (mod_php sapi). Falls back to
     *                              now() if missing.
     */
    public static function begin(?float $timeStart = null): void
    {
        if (self::$requestUid !== null) {
            return;
        }
        self::$requestUid = bin2hex(random_bytes(16));
        self::$timeStart = $timeStart ?? microtime(true);

        if (!self::$registered && PHP_SAPI !== 'cli') {
            \register_shutdown_function([self::class, 'flush']);
            self::$registered = true;
        }
    }

    /**
     * Public request_uid getter so controllers can echo it into the response
     * (`<meta name="pma-audit-uid">`, `X-Pma-Audit-Id` header, etc.) and
     * sub-process launchers can propagate it via the `PMA_AUDIT_REQUEST_UID`
     * env var.
     */
    public static function requestUid(): ?string
    {
        return self::$requestUid;
    }

    /**
     * Late stamping for controller/action/role once routing resolved them.
     * Bootstrap.php calls this once `$_SYSTEM['controller']` is known.
     */
    public static function stampRoute(string $controller, string $action, ?string $userRoleClass = null): void
    {
        self::$extra['controller']     = $controller;
        self::$extra['action']         = $action;
        if ($userRoleClass !== null) {
            self::$extra['user_role_class'] = $userRoleClass;
        }
    }

    /**
     * Stamp the authenticated user id (after Auth resolved). Optional —
     * anonymous hits are recorded with `id_user_main = null`.
     */
    public static function stampUser(?int $idUserMain): void
    {
        self::$extra['id_user_main'] = $idUserMain;
    }

    /**
     * Shutdown handler — composes the NDJSON line and appends it to the
     * spool. Anything thrown in here is swallowed so the audit pipeline
     * can never break a real response.
     */
    public static function flush(): void
    {
        if (self::$requestUid === null) {
            return;
        }
        try {
            $row = self::buildRow();
            self::writeSpool($row);
        } catch (\Throwable $e) {
            error_log('RequestAuditCollector flush failed: ' . $e->getMessage());
        }
    }

    /**
     * Compose the row. Pure, so tests can pin the schema without booting
     * the framework.
     *
     * @return array<string,mixed>
     */
    public static function buildRow(?array $serverOverride = null, ?int $idUserOverride = null): array
    {
        $server = $serverOverride ?? $_SERVER;
        $idUser = $idUserOverride ?? (self::$extra['id_user_main'] ?? null);

        $now = microtime(true);
        $phpMs = (int) round(($now - self::$timeStart) * 1000);

        $uaRaw = (string) ($server['HTTP_USER_AGENT'] ?? '');
        $uaHash = $uaRaw !== '' ? md5($uaRaw) : null;

        $referer = (string) ($server['HTTP_REFERER'] ?? '');
        if (strlen($referer) > 2048) {
            $referer = substr($referer, 0, 2048);
        }

        $uri = (string) ($server['REQUEST_URI'] ?? '');
        if (strlen($uri) > 2048) {
            $uri = substr($uri, 0, 2048);
        }
        $uri = self::redactSensitiveQueryParts($uri);

        return [
            'request_uid'     => self::$requestUid,
            'date'            => self::dateMillis($now),
            'id_user_main'    => $idUser,
            'ip'              => self::clientIp($server),
            'method'          => (string) ($server['REQUEST_METHOD'] ?? 'GET'),
            'uri'             => $uri,
            'status'          => http_response_code() ?: null,
            'bytes_sent'      => null, // filled by Apache backfill if needed; primary path leaves null
            'referer'         => $referer !== '' ? $referer : null,
            'user_agent_raw'  => $uaRaw !== '' ? $uaRaw : null,
            'user_agent_hash' => $uaHash,
            'duration_us'     => null, // Apache %D, only available via access-log enrichment
            'php_ms'          => $phpMs,
            'controller'      => self::$extra['controller']      ?? null,
            'action'          => self::$extra['action']          ?? null,
            'user_role_class' => self::$extra['user_role_class'] ?? null,
        ];
    }

    /**
     * Best-effort client IP. Honours `X-Forwarded-For` only when the
     * immediate REMOTE_ADDR is in the configured trustedProxies list —
     * mirrors what CookieSecurity does so the audit IP matches the auth IP.
     */
    private static function clientIp(array $server): string
    {
        $remote = (string) ($server['REMOTE_ADDR'] ?? '');
        $fwd    = trim((string) ($server['HTTP_X_FORWARDED_FOR'] ?? ''));
        if ($fwd === '') {
            return $remote;
        }
        // Use only the first hop (closest to the client); the rest may be spoofed.
        $first = trim(explode(',', $fwd)[0]);
        if ($first !== '' && filter_var($first, FILTER_VALIDATE_IP)) {
            return $first;
        }
        return $remote;
    }

    /**
     * Drop or replace query-string segments that commonly carry secrets
     * (csrf tokens, password resets, API keys). Conservative — only redacts
     * a known list; everything else is kept for forensics. Belt-and-braces:
     * the public surface for these logs is SuperAdmin only.
     */
    private static function redactSensitiveQueryParts(string $uri): string
    {
        $sensitive = [
            '_csrf_token',
            'csrf',
            'token',
            'password',
            'passwd',
            'secret',
            'key',
            'authorization',
        ];
        $parts = explode('?', $uri, 2);
        if (count($parts) !== 2) {
            return $uri;
        }
        $path = $parts[0];
        $qs   = $parts[1];
        parse_str($qs, $params);
        $rebuilt = [];
        foreach ($params as $k => $v) {
            $lc = strtolower((string) $k);
            $hit = false;
            foreach ($sensitive as $needle) {
                if (str_contains($lc, $needle)) {
                    $hit = true;
                    break;
                }
            }
            $rebuilt[$k] = $hit ? '[redacted]' : $v;
        }
        return $path . '?' . http_build_query($rebuilt);
    }

    /**
     * Append one NDJSON line, atomically. PHP's fopen(...,'a') + fwrite()
     * is atomic up to PIPE_BUF (4096 on Linux) per POSIX, and our rows are
     * comfortably below that. No locking required.
     */
    private static function writeSpool(array $row): void
    {
        $dir = self::spoolDir();
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return;
        }
        $path = $dir . date('Y-m-d') . '.ndjson';
        $line = json_encode($row, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
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

    /**
     * Spool directory resolution. Honors TMP (defined in Bootstrap) when
     * available, falls back to the in-repo path so tests can run outside
     * the framework boot.
     */
    public static function spoolDir(): string
    {
        if (defined('TMP')) {
            return rtrim(\constant('TMP'), '/') . '/audit' . self::SPOOL_DIR;
        }
        return dirname(__DIR__, 3) . '/tmp/audit' . self::SPOOL_DIR;
    }

    /**
     * "YYYY-MM-DD HH:MM:SS.mmm" — DATETIME(3) format MySQL accepts via
     * the drain's prepared INSERTs.
     */
    private static function dateMillis(float $when): string
    {
        $s = (int) $when;
        $ms = (int) (($when - $s) * 1000);
        return date('Y-m-d H:i:s', $s) . sprintf('.%03d', $ms);
    }
}
