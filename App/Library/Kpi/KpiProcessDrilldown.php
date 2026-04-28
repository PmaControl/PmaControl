<?php

namespace App\Library\Kpi;

use Glial\Sgbd\Sgbd;

final class KpiProcessDrilldown
{
    public const EXECUTION_LIMIT = 100;
    public const MAX_PID = 4194304;
    private const CONNECTION_SLOT = 'kpi_process_drilldown';
    private static array $tableExistsCache = [];

    public static function buildPayload(int $pid, array $options = []): array
    {
        $pid = self::validPid($pid) ? $pid : 0;
        $now = self::dateTimeObject($options['now'] ?? 'now');
        if ($pid <= 0) {
            return self::emptyPayload($pid, $now, ['Invalid process id.']);
        }

        $warnings = [];
        $db = null;
        try {
            $db = Sgbd::sql(DB_DEFAULT, self::CONNECTION_SLOT);
            $context = self::fetchProcessContext($db, $pid);
            $proc = self::readProcInfo($pid, $options);

            if ($context === null && empty($proc['alive'])) {
                return self::emptyPayload($pid, $now, ['Process not found.']);
            }

            if (!empty($proc['warnings'])) {
                $warnings = array_merge($warnings, $proc['warnings']);
            }

            $executions = [];
            if (($context['type'] ?? '') === 'worker' && !empty($context['id_worker_run'])) {
                try {
                    if (self::tableExists($db, 'worker_execution')) {
                        $executions = self::fetchWorkerExecutions($db, (int)$context['id_worker_run'], self::EXECUTION_LIMIT);
                    }
                } catch (\Throwable $e) {
                    $warnings[] = 'Worker execution history is unavailable: '.KpiSanitizer::errorMessage($e->getMessage(), 512);
                }
            }

            $stackTrace = self::readStackTrace($pid, !empty($proc['alive']));
            $kill = self::buildKillState($context, $proc);

            return [
                'pid' => $pid,
                'not_found' => false,
                'context' => $context ?? self::unknownContext($pid, $proc),
                'proc' => $proc,
                'stack_trace' => $stackTrace,
                'worker_executions' => $executions,
                'kill' => $kill,
                'generated_at' => self::dateTime($now),
                'warnings' => $warnings,
            ];
        } catch (\Throwable $e) {
            return self::emptyPayload($pid, $now, [KpiSanitizer::errorMessage($e->getMessage(), 512)]);
        } finally {
            if (is_object($db) && method_exists($db, 'sql_close')) {
                $db->sql_close();
            }
        }
    }

    public static function buildWorkerLookupSql(int $pid): string
    {
        return 'SELECT wr.id AS id_worker_run, wr.id_worker_queue, wr.pid, wr.date_created AS started_at, '
            .'wr.is_working, wr.is_safe_kill, wr.date_killed, '
            .'wq.name, wq.worker_class AS class, wq.worker_method AS method, '
            .'wq.max_execution_time, wq.`table` AS queue_table '
            .'FROM `worker_run` wr '
            .'INNER JOIN `worker_queue` wq ON wq.id = wr.id_worker_queue '
            .'WHERE wr.pid = '.$pid.' '
            .'ORDER BY wr.is_working DESC, wr.id DESC LIMIT 1;';
    }

    public static function buildDaemonLookupSql(int $pid): string
    {
        return 'SELECT `id` AS `id_daemon_main`, `name`, `pid`, `date` AS `started_at`, '
            .'`class`, `method`, `params`, `is_enabled`, `refresh_time`, `max_delay` '
            .'FROM `daemon_main` WHERE `pid` = '.$pid.' LIMIT 1;';
    }

    public static function buildJobLookupSql(int $pid): string
    {
        return 'SELECT `id` AS `id_job`, `uuid`, `class`, `method`, `param` AS `params`, '
            .'`date_start` AS `started_at`, `date_end`, `pid`, `status` '
            .'FROM `job` WHERE `pid` = '.$pid.' ORDER BY `date_start` DESC LIMIT 1;';
    }

    public static function buildWorkerExecutionsSql(int $idWorkerRun, int $limit = self::EXECUTION_LIMIT): string
    {
        $limit = max(1, min(500, $limit));

        return 'SELECT `id`, `id_worker_run`, `id_mysql_server`, `date_started`, `date_end`, '
            .'`execution_time`, `status`, `error_class`, `error_message`, `exit_code`, '
            .'`peak_rss_kb`, `db_queries_count`, `db_queries_time_ms`, `attempt_n` '
            .'FROM `worker_execution` '
            .'WHERE `id_worker_run` = '.$idWorkerRun.' '
            .'ORDER BY `date_started` DESC LIMIT '.$limit.';';
    }

    public static function parseStatusFile(string $content): array
    {
        $values = [];
        foreach (preg_split('/\r?\n/', $content) ?: [] as $line) {
            if (preg_match('/^([A-Za-z_]+):\s*(.*)$/', $line, $match) !== 1) {
                continue;
            }

            $values[$match[1]] = trim($match[2]);
        }

        return $values;
    }

    public static function parseStatFile(string $content, int $ticksPerSecond = 100): array
    {
        $content = trim($content);
        if (preg_match('/^\d+\s+\((.*)\)\s+([A-Za-z])\s+(.*)$/', $content, $match) !== 1) {
            return [];
        }

        $fields = preg_split('/\s+/', trim($match[3])) ?: [];
        $utime = (int)($fields[10] ?? 0);
        $stime = (int)($fields[11] ?? 0);
        $startTime = (int)($fields[18] ?? 0);
        $ticksPerSecond = max(1, $ticksPerSecond);

        return [
            'comm' => $match[1],
            'state' => $match[2],
            'ppid' => isset($fields[0]) ? (int)$fields[0] : null,
            'cpu_seconds' => round(($utime + $stime) / $ticksPerSecond, 3),
            'start_time_ticks' => $startTime,
        ];
    }

    public static function sanitizeCommandLine(string $commandLine): string
    {
        $commandLine = str_replace("\0", ' ', $commandLine);
        $commandLine = preg_replace('/(--password(?:=|\s+))\S+/i', '$1***', $commandLine) ?? $commandLine;
        $commandLine = preg_replace('/(^|\s)(-p)(\S+)/i', '$1$2***', $commandLine) ?? $commandLine;

        return KpiSanitizer::errorMessage($commandLine, 4096) ?? '';
    }

    public static function readStartTimeTicks(int $pid, string $procBase = '/proc'): ?int
    {
        if (!self::validPid($pid)) {
            return null;
        }

        $path = rtrim($procBase, '/').'/'.$pid.'/stat';
        if (!is_readable($path)) {
            return null;
        }

        $content = @file_get_contents($path);
        if (!is_string($content)) {
            return null;
        }

        $stat = self::parseStatFile($content, self::clockTicks());

        return isset($stat['start_time_ticks']) ? (int)$stat['start_time_ticks'] : null;
    }

    public static function computeUptimeSeconds(?int $startTimeTicks, ?float $systemUptimeSeconds, int $ticksPerSecond = 100): ?float
    {
        if ($startTimeTicks === null || $systemUptimeSeconds === null) {
            return null;
        }

        $ticksPerSecond = max(1, $ticksPerSecond);
        $uptime = $systemUptimeSeconds - ($startTimeTicks / $ticksPerSecond);

        return round(max(0.0, $uptime), 3);
    }

    public static function validPid(int $pid): bool
    {
        return $pid > 0 && $pid <= self::MAX_PID;
    }

    private static function fetchProcessContext($db, int $pid): ?array
    {
        $worker = self::fetchOne($db, self::buildWorkerLookupSql($pid));
        if ($worker !== null) {
            $worker['type'] = 'worker';
            $worker['label'] = 'Worker';
            $worker['params'] = '';

            return $worker;
        }

        $daemon = self::fetchOne($db, self::buildDaemonLookupSql($pid));
        if ($daemon !== null) {
            $daemon['type'] = 'daemon';
            $daemon['label'] = 'Daemon';

            return $daemon;
        }

        $job = self::fetchOne($db, self::buildJobLookupSql($pid));
        if ($job !== null) {
            $job['type'] = 'job';
            $job['label'] = 'Job';
            $job['name'] = 'Job #'.($job['id_job'] ?? '');

            return $job;
        }

        return null;
    }

    private static function readProcInfo(int $pid, array $options = []): array
    {
        $base = rtrim((string)($options['proc_base'] ?? '/proc'), '/');
        $dir = $base.'/'.$pid;
        $warnings = [];
        $alive = is_dir($dir);
        $canSignal = $alive;

        if ($alive && $base === '/proc' && function_exists('posix_kill')) {
            $canSignal = @posix_kill($pid, 0);
            if (!$canSignal && function_exists('posix_get_last_error') && defined('POSIX_EPERM')) {
                $canSignal = posix_get_last_error() === POSIX_EPERM;
            }
        }

        $status = [];
        $statusPath = $dir.'/status';
        if (is_readable($statusPath)) {
            $content = @file_get_contents($statusPath);
            if (is_string($content)) {
                $status = self::parseStatusFile($content);
            }
        } elseif ($alive) {
            $warnings[] = 'Cannot read /proc/'.$pid.'/status.';
        }

        $stat = [];
        $ticksPerSecond = self::clockTicks();
        $statPath = $dir.'/stat';
        if (is_readable($statPath)) {
            $content = @file_get_contents($statPath);
            if (is_string($content)) {
                $stat = self::parseStatFile($content, $ticksPerSecond);
            }
        }
        $uptimeSeconds = self::computeUptimeSeconds(
            isset($stat['start_time_ticks']) ? (int)$stat['start_time_ticks'] : null,
            self::readSystemUptimeSeconds($base),
            $ticksPerSecond
        );

        $cmdline = '';
        $cmdlinePath = $dir.'/cmdline';
        if (is_readable($cmdlinePath)) {
            $content = @file_get_contents($cmdlinePath);
            if (is_string($content)) {
                $cmdline = self::sanitizeCommandLine($content);
            }
        } elseif ($alive) {
            $warnings[] = 'Cannot read /proc/'.$pid.'/cmdline.';
        }

        $fdCount = null;
        $fdPath = $dir.'/fd';
        if (is_readable($fdPath)) {
            $files = @scandir($fdPath);
            if (is_array($files)) {
                $fdCount = max(0, count($files) - 2);
            }
        } elseif ($alive) {
            $warnings[] = 'Cannot read /proc/'.$pid.'/fd.';
        }

        return [
            'alive' => $alive,
            'can_signal' => $canSignal,
            'cmdline' => $cmdline,
            'state' => $status['State'] ?? ($stat['state'] ?? null),
            'ppid' => $status['PPid'] ?? ($stat['ppid'] ?? null),
            'rss_kb' => self::statusKb($status['VmRSS'] ?? null),
            'rss_peak_kb' => self::statusKb($status['VmHWM'] ?? null),
            'threads' => isset($status['Threads']) ? (int)$status['Threads'] : null,
            'uid' => $status['Uid'] ?? null,
            'fd_count' => $fdCount,
            'cpu_seconds' => $stat['cpu_seconds'] ?? null,
            'start_time_ticks' => $stat['start_time_ticks'] ?? null,
            'uptime_seconds' => $uptimeSeconds,
            'warnings' => $warnings,
        ];
    }

    private static function readSystemUptimeSeconds(string $procBase): ?float
    {
        $path = rtrim($procBase, '/').'/uptime';
        if (!is_readable($path)) {
            return null;
        }

        $content = @file_get_contents($path);
        if (!is_string($content) || preg_match('/^(\d+(?:\.\d+)?)/', trim($content), $match) !== 1) {
            return null;
        }

        return (float)$match[1];
    }

    private static function readStackTrace(int $pid, bool $alive): array
    {
        if (getenv('KPI_PROCESS_STACK') !== '1') {
            return ['available' => false, 'reason' => 'disabled', 'output' => ''];
        }

        if (!$alive) {
            return ['available' => false, 'reason' => 'process is not alive', 'output' => ''];
        }

        $gdb = trim((string)@shell_exec('command -v gdb 2>/dev/null'));
        if ($gdb === '') {
            return ['available' => false, 'reason' => 'gdb is not installed', 'output' => ''];
        }

        $output = (string)@shell_exec('timeout 3s '.escapeshellarg($gdb).' -p '.$pid.' -batch -ex bt 2>/dev/null');
        $sanitized = KpiSanitizer::errorMessage($output, 16384) ?? '';
        $sanitized = substr($sanitized, 0, 8192);

        return [
            'available' => trim($sanitized) !== '',
            'reason' => trim($sanitized) === '' ? 'empty gdb output or insufficient ptrace permissions' : '',
            'output' => $sanitized,
        ];
    }

    private static function buildKillState(?array $context, array $proc): array
    {
        if (($context['type'] ?? '') !== 'worker') {
            return ['available' => false, 'reason' => 'Kill is only enabled for worker processes.'];
        }

        if ((int)($context['is_working'] ?? 0) !== 1) {
            return ['available' => false, 'reason' => 'Worker is not marked as working.'];
        }

        if (empty($proc['alive']) || empty($proc['can_signal'])) {
            return ['available' => false, 'reason' => 'Process is not alive or cannot be signalled.'];
        }

        if (!isset($proc['start_time_ticks'])) {
            return ['available' => false, 'reason' => 'Cannot verify process identity from /proc.'];
        }

        return ['available' => true, 'reason' => ''];
    }

    private static function fetchWorkerExecutions($db, int $idWorkerRun, int $limit): array
    {
        $rows = [];
        $res = $db->sql_query(self::buildWorkerExecutionsSql($idWorkerRun, $limit));
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $rows[] = $row;
        }

        return $rows;
    }

    private static function fetchOne($db, string $sql): ?array
    {
        $res = $db->sql_query($sql);
        $row = $db->sql_fetch_array($res, MYSQLI_ASSOC);

        return is_array($row) ? $row : null;
    }

    private static function tableExists($db, string $table): bool
    {
        if (array_key_exists($table, self::$tableExistsCache)) {
            return self::$tableExistsCache[$table];
        }

        $sql = 'SELECT COUNT(*) FROM information_schema.tables '
            .'WHERE table_schema = DATABASE() '
            .'AND table_name = '.self::sqlLiteral($db, $table).';';

        self::$tableExistsCache[$table] = (int)self::fetchScalar($db, $sql) > 0;

        return self::$tableExistsCache[$table];
    }

    private static function fetchScalar($db, string $sql)
    {
        $res = $db->sql_query($sql);
        $row = $db->sql_fetch_array($res, MYSQLI_NUM);

        return is_array($row) ? ($row[0] ?? null) : null;
    }

    private static function unknownContext(int $pid, array $proc): array
    {
        return [
            'type' => 'unknown',
            'label' => 'Unknown PHP process',
            'name' => 'PID '.$pid,
            'pid' => $pid,
            'class' => '',
            'method' => '',
            'params' => $proc['cmdline'] ?? '',
            'started_at' => null,
        ];
    }

    private static function emptyPayload(int $pid, \DateTimeImmutable $now, array $warnings): array
    {
        return [
            'pid' => $pid,
            'not_found' => true,
            'context' => self::unknownContext($pid, ['cmdline' => '']),
            'proc' => ['alive' => false, 'can_signal' => false, 'warnings' => []],
            'stack_trace' => ['available' => false, 'reason' => 'process not found', 'output' => ''],
            'worker_executions' => [],
            'kill' => ['available' => false, 'reason' => 'Process not found.'],
            'generated_at' => self::dateTime($now),
            'warnings' => $warnings,
        ];
    }

    private static function statusKb($value): ?int
    {
        if (!is_string($value) || preg_match('/(\d+)/', $value, $match) !== 1) {
            return null;
        }

        return (int)$match[1];
    }

    private static function clockTicks(): int
    {
        $ticks = (int)trim((string)@shell_exec('getconf CLK_TCK 2>/dev/null'));

        return $ticks > 0 ? $ticks : 100;
    }

    private static function dateTimeObject($value): \DateTimeImmutable
    {
        if ($value instanceof \DateTimeImmutable) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return \DateTimeImmutable::createFromInterface($value);
        }

        return new \DateTimeImmutable(is_string($value) && trim($value) !== '' ? $value : 'now');
    }

    private static function dateTime(\DateTimeImmutable $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    private static function sqlLiteral($db, $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_int($value)) {
            return (string)$value;
        }

        if (!method_exists($db, 'sql_real_escape_string')) {
            throw new \InvalidArgumentException('Database adapter must expose sql_real_escape_string().');
        }

        return "'".$db->sql_real_escape_string((string)$value)."'";
    }
}
