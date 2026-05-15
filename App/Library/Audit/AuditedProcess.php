<?php

declare(strict_types=1);

namespace App\Library\Audit;

/**
 * Audited shell-out wrapper (#1235).
 *
 * Single point of truth for every `exec` / `shell_exec` / `passthru` /
 * `proc_open` / `popen` site in the app — replaces them so we get an
 * NDJSON `subprocess_log` row for every spawn, correlated to the
 * originating request via `request_uid`.
 *
 *   AuditedProcess::run('mariadb --version', ['label' => 'mariadb_version']);
 *   AuditedProcess::startBackground('php bin/foo.php 42', ['label' => 'foo']);
 *   AuditedProcess::attachCurrentFromEnv(); // child reads PMA_AUDIT_PROCESS_ID
 *
 * Codex (#1235 review) flagged that PHP gives us no clean way to globally
 * intercept the raw functions; a `tests/StaticScan` test (#1235 nice-to-
 * have) will flag any new raw-call site so the wrapper coverage doesn't
 * drift.
 *
 * Hot-path safety: spawn audit logging is best-effort and never blocks
 * the actual process — start/end NDJSON rows are appended, exceptions
 * are swallowed, and a failure to log a sub-process never reaches the
 * caller.
 */
final class AuditedProcess
{
    private const SPOOL_DIR = '/subprocess/';

    /**
     * Run a command synchronously, capturing stdout/stderr/exit code,
     * and emit a single `subprocess_log` row that covers the lifetime.
     *
     * @param string|array $cmd If array, escapeshellarg every part then
     *                          implode with spaces; if string, used verbatim
     *                          (caller is responsible for escaping).
     * @param array{label?:string,log_path?:string,timeout_s?:int,env?:array<string,string>} $opts
     *
     * @return array{ok:bool,exit_code:int,stdout:string,stderr_tail:string,duration_ms:int}
     */
    public static function run($cmd, array $opts = []): array
    {
        $start = microtime(true);
        $commandLine = self::compile($cmd);
        $label = (string) ($opts['label'] ?? '');
        $logPath = (string) ($opts['log_path'] ?? '');
        $timeoutS = (int) ($opts['timeout_s'] ?? 0);
        $env = $opts['env'] ?? null;

        // Compose the spool row in advance; only emit it once we know the
        // final status (foreground commands are typically short — we don't
        // need a "running" intermediate row that the drain would then
        // duplicate. startBackground() does emit one because the parent
        // needs the in-flight visibility before exit.).
        $spoolRow = [
            'request_uid' => RequestAuditCollector::requestUid(),
            'date_start'  => self::dateMillis($start),
            'command'     => $commandLine,
            'label'       => $label !== '' ? $label : null,
            'log_path'    => $logPath !== '' ? $logPath : null,
        ];

        $effectiveCmd = $timeoutS > 0
            ? 'timeout ' . (int) $timeoutS . 's ' . $commandLine
            : $commandLine;

        $proc = self::propagateEnvFor($env);

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $process = @proc_open($effectiveCmd, $descriptors, $pipes, null, $proc);
        if (!is_resource($process)) {
            $end = microtime(true);
            $row = $spoolRow;
            $row['date_end']    = self::dateMillis($end);
            $row['duration_ms'] = (int) round(($end - $start) * 1000);
            $row['status']      = 'failed';
            $row['exit_code']   = 127;
            self::spool($row);
            return ['ok' => false, 'exit_code' => 127, 'stdout' => '', 'stderr_tail' => 'proc_open failed', 'duration_ms' => $row['duration_ms']];
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]) ?: '';
        $stderr = stream_get_contents($pipes[2]) ?: '';
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit = proc_close($process);

        $end = microtime(true);
        $row = $spoolRow;
        $row['date_end']    = self::dateMillis($end);
        $row['duration_ms'] = (int) round(($end - $start) * 1000);
        $row['exit_code']   = $exit;
        $row['status']      = $exit === 0 ? 'done' : ($exit === 124 ? 'timeout' : 'failed');
        self::spool($row);

        return [
            'ok'           => $exit === 0,
            'exit_code'    => $exit,
            'stdout'       => $stdout,
            'stderr_tail'  => substr(trim($stderr), -400),
            'duration_ms'  => $row['duration_ms'],
        ];
    }

    /**
     * Fork + return the pid without waiting. Caller is responsible for
     * the child output (typically redirected to log_path). The child can
     * call `attachCurrentFromEnv()` to log its own completion.
     *
     * @param array{label?:string,log_path?:string,env?:array<string,string>} $opts
     * @return int Child PID, or 0 on failure.
     */
    public static function startBackground($cmd, array $opts = []): int
    {
        $start = microtime(true);
        $commandLine = self::compile($cmd);
        $label = (string) ($opts['label'] ?? '');
        $logPath = (string) ($opts['log_path'] ?? '/dev/null');

        $auditEnv = self::propagateEnvFor($opts['env'] ?? null);

        $fullCmd = 'nohup ' . $commandLine . ' > ' . escapeshellarg($logPath) . ' 2>&1 & echo $!';
        $envPrefix = '';
        foreach ($auditEnv as $k => $v) {
            $envPrefix .= escapeshellarg($k) . '=' . escapeshellarg((string) $v) . ' ';
        }
        $pid = (int) trim((string) shell_exec($envPrefix . $fullCmd));
        if ($pid <= 0) {
            return 0;
        }

        self::spool([
            'request_uid' => RequestAuditCollector::requestUid(),
            'date_start'  => self::dateMillis($start),
            'pid'         => $pid,
            'command'     => $commandLine,
            'label'       => $label !== '' ? $label : null,
            'log_path'    => $logPath,
            'status'      => 'running',
        ]);

        return $pid;
    }

    /**
     * Read `PMA_AUDIT_PROCESS_ID` from env (set by the parent's spool row).
     * A forked child that runs through this function gets its completion
     * spooled with the same id so /AuditLog can show parent → child chains.
     */
    public static function attachCurrentFromEnv(): ?string
    {
        $env = getenv('PMA_AUDIT_PROCESS_ID');
        return $env !== false && $env !== '' ? (string) $env : null;
    }

    /**
     * Build the env array that gets passed to the child. The request_uid
     * is shipped via PMA_AUDIT_REQUEST_UID so the child can self-spool a
     * row carrying it; the parent's audit row id (when known) goes into
     * PMA_AUDIT_PROCESS_ID.
     *
     * @param array<string,string>|null $caller
     * @return array<string,string>
     */
    private static function propagateEnvFor(?array $caller): array
    {
        $env = $caller ?? [];
        $uid = RequestAuditCollector::requestUid();
        if ($uid !== null) {
            $env['PMA_AUDIT_REQUEST_UID'] = $uid;
        }
        return $env;
    }

    /**
     * Spool a single subprocess_log NDJSON line. Best-effort.
     *
     * @param array<string,mixed> $row
     */
    private static function spool(array $row): void
    {
        try {
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
        } catch (\Throwable $e) {
            error_log('AuditedProcess spool failed: ' . $e->getMessage());
        }
    }

    public static function spoolDir(): string
    {
        if (defined('TMP')) {
            return rtrim(\constant('TMP'), '/') . '/audit' . self::SPOOL_DIR;
        }
        return dirname(__DIR__, 3) . '/tmp/audit' . self::SPOOL_DIR;
    }

    /**
     * @param string|array $cmd
     */
    private static function compile($cmd): string
    {
        if (is_array($cmd)) {
            $parts = [];
            foreach ($cmd as $p) {
                $parts[] = escapeshellarg((string) $p);
            }
            return implode(' ', $parts);
        }
        return (string) $cmd;
    }

    private static function dateMillis(float $when): string
    {
        $s = (int) $when;
        $ms = (int) (($when - $s) * 1000);
        return date('Y-m-d H:i:s', $s) . sprintf('.%03d', $ms);
    }
}
