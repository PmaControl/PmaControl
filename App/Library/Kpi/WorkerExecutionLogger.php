<?php

namespace App\Library\Kpi;

use Glial\Sgbd\Sgbd;

final class WorkerExecutionLogger
{
    private const CONNECTION_SLOT = 'worker_kpi';
    private const STATUSES = ['OK', 'ERROR', 'TIMEOUT', 'SKIPPED', 'KILLED', 'STUCK'];

    public static function startForWorkerPid(int $workerPid, int $idMysqlServer, int $attemptN = 1): ?int
    {
        $payload = self::buildStartPayload([
            'worker_pid' => $workerPid,
            'id_mysql_server' => $idMysqlServer,
            'attempt_n' => $attemptN,
            'date_started' => microtime(true),
        ]);
        if ($payload === null) {
            return null;
        }

        $db = null;
        try {
            $db = Sgbd::sql(DB_DEFAULT, self::CONNECTION_SLOT);
            $db->sql_query(self::buildStartSql($db, $payload));

            return (int)$db->sql_insert_id();
        } catch (\Throwable $e) {
            return null;
        } finally {
            if (is_object($db) && method_exists($db, 'sql_close')) {
                $db->sql_close();
            }
        }
    }

    public static function finish(?int $idWorkerExecution, array $input): bool
    {
        if (empty($idWorkerExecution)) {
            return false;
        }

        $input['id'] = $idWorkerExecution;
        $payload = self::buildFinishPayload($input);
        if ($payload === null) {
            return false;
        }

        $db = null;
        try {
            $db = Sgbd::sql(DB_DEFAULT, self::CONNECTION_SLOT);
            $db->sql_query(self::buildFinishSql($db, $payload));

            return true;
        } catch (\Throwable $e) {
            return false;
        } finally {
            if (is_object($db) && method_exists($db, 'sql_close')) {
                $db->sql_close();
            }
        }
    }

    public static function markKilledForRun(int $idWorkerRun): int
    {
        return self::markLatestOpenForWorkerRun($idWorkerRun, 'KILLED');
    }

    public static function markStuckForRun(int $idWorkerRun): int
    {
        return self::markLatestOpenForWorkerRun($idWorkerRun, 'STUCK');
    }

    public static function markLatestOpenForWorkerRun(int $idWorkerRun, string $status): int
    {
        $idWorkerRun = self::positiveInt($idWorkerRun) ?? 0;
        if ($idWorkerRun <= 0) {
            return 0;
        }

        $status = self::status($status);
        if (!in_array($status, ['KILLED', 'STUCK'], true)) {
            return 0;
        }

        $db = null;
        try {
            $db = Sgbd::sql(DB_DEFAULT, self::CONNECTION_SLOT);
            $db->sql_query(self::buildMarkLatestOpenSql($idWorkerRun, $status));

            return method_exists($db, 'sql_affected_rows') ? max(0, (int)$db->sql_affected_rows()) : 0;
        } catch (\Throwable $e) {
            return 0;
        } finally {
            if (is_object($db) && method_exists($db, 'sql_close')) {
                $db->sql_close();
            }
        }
    }

    public static function snapshotDbCounters(): ?array
    {
        if (getenv('KPI_PROFILE_QUERIES') !== '1') {
            return null;
        }

        return self::collectDbCounters();
    }

    public static function diffDbCounters(?array $start): array
    {
        if ($start === null) {
            return [
                'db_queries_count' => null,
                'db_queries_time_ms' => null,
            ];
        }

        $end = self::collectDbCounters();

        return [
            'db_queries_count' => max(0, $end['count'] - (int)$start['count']),
            'db_queries_time_ms' => max(0, $end['time_ms'] - (int)$start['time_ms']),
        ];
    }

    public static function buildStartPayload(array $input): ?array
    {
        $workerPid = self::positiveInt($input['worker_pid'] ?? null);
        $idMysqlServer = self::positiveInt($input['id_mysql_server'] ?? null);
        if ($workerPid === null || $idMysqlServer === null) {
            return null;
        }

        return [
            'worker_pid' => $workerPid,
            'id_mysql_server' => $idMysqlServer,
            'date_started' => self::dateTime($input['date_started'] ?? microtime(true)),
            'status' => 'OK',
            'attempt_n' => max(1, self::nonNegativeInt($input['attempt_n'] ?? 1)),
        ];
    }

    public static function buildStartSql($db, array $payload): string
    {
        return 'INSERT INTO `worker_execution` '
            .'(`id_worker_run`, `id_mysql_server`, `date_started`, `status`, `attempt_n`) '
            .'SELECT `id`, '
            .(int)$payload['id_mysql_server'].', '
            .self::sqlLiteral($db, $payload['date_started']).', '
            .self::sqlLiteral($db, $payload['status']).', '
            .(int)$payload['attempt_n'].' '
            .'FROM `worker_run` '
            .'WHERE `pid` = '.(int)$payload['worker_pid'].' '
            .'AND `is_working` = 1 '
            .'ORDER BY `id` DESC LIMIT 1;';
    }

    public static function buildFinishPayload(array $input): ?array
    {
        $id = self::positiveInt($input['id'] ?? null);
        if ($id === null) {
            return null;
        }

        $executionTime = self::nonNegativeInt($input['execution_time_ms'] ?? $input['execution_time'] ?? 0);
        $maxExecutionTime = self::nonNegativeInt($input['max_execution_time'] ?? 0);
        $error = $input['throwable'] ?? null;
        $requestedStatus = self::status((string)($input['status'] ?? 'OK'));
        $status = self::resolveStatus($requestedStatus, $error instanceof \Throwable, $executionTime, $maxExecutionTime);
        $errorClass = $error instanceof \Throwable ? get_class($error) : ($input['error_class'] ?? null);
        $errorMessage = $error instanceof \Throwable ? $error->getMessage() : ($input['error_message'] ?? null);
        $exitCode = self::exitCode($status, $error instanceof \Throwable ? $error->getCode() : ($input['exit_code'] ?? null));

        return [
            'id' => $id,
            'date_end' => self::dateTime($input['date_end'] ?? microtime(true)),
            'execution_time' => $executionTime,
            'status' => $status,
            'error_class' => self::shortString($errorClass, 128),
            'error_message' => KpiSanitizer::errorMessage($errorMessage, 512),
            'exit_code' => $exitCode,
            'peak_rss_kb' => self::positiveInt($input['peak_rss_kb'] ?? null),
            'db_queries_count' => self::nullableNonNegativeInt($input['db_queries_count'] ?? null),
            'db_queries_time_ms' => self::nullableNonNegativeInt($input['db_queries_time_ms'] ?? null),
            'attempt_n' => max(1, self::nonNegativeInt($input['attempt_n'] ?? 1)),
        ];
    }

    public static function buildFinishSql($db, array $payload): string
    {
        $assignments = [
            '`date_end` = '.self::sqlLiteral($db, $payload['date_end']),
            '`execution_time` = '.self::sqlLiteral($db, $payload['execution_time']),
            '`status` = '.self::sqlLiteral($db, $payload['status']),
            '`error_class` = '.self::sqlLiteral($db, $payload['error_class']),
            '`error_message` = '.self::sqlLiteral($db, $payload['error_message']),
            '`exit_code` = '.self::sqlLiteral($db, $payload['exit_code']),
            '`peak_rss_kb` = '.self::sqlLiteral($db, $payload['peak_rss_kb']),
            '`db_queries_count` = '.self::sqlLiteral($db, $payload['db_queries_count']),
            '`db_queries_time_ms` = '.self::sqlLiteral($db, $payload['db_queries_time_ms']),
            '`attempt_n` = '.self::sqlLiteral($db, $payload['attempt_n']),
        ];

        return 'UPDATE `worker_execution` SET '
            .implode(', ', $assignments)
            .' WHERE `id` = '.(int)$payload['id']
            ." AND `status` NOT IN ('KILLED', 'STUCK');";
    }

    public static function buildMarkLatestOpenSql(int $idWorkerRun, string $status): string
    {
        $status = self::status($status);
        $exitCode = $status === 'KILLED' ? 137 : 1;

        return 'UPDATE `worker_execution` SET '
            .'`date_end` = NOW(), '
            .'`execution_time` = GREATEST(0, ROUND(TIMESTAMPDIFF(MICROSECOND, `date_started`, NOW(6)) / 1000)), '
            .'`status` = \''.$status.'\', '
            .'`exit_code` = COALESCE(`exit_code`, '.$exitCode.') '
            .'WHERE `id_worker_run` = '.$idWorkerRun.' '
            .'AND `date_end` IS NULL '
            .'ORDER BY `id` DESC LIMIT 1;';
    }

    public static function resolveStatus(string $requestedStatus, bool $hasError, int $executionTimeMs, int $maxExecutionTimeSeconds): string
    {
        $requestedStatus = self::status($requestedStatus);
        if (in_array($requestedStatus, ['SKIPPED', 'KILLED', 'STUCK'], true)) {
            return $requestedStatus;
        }

        if ($hasError || $requestedStatus === 'ERROR') {
            return 'ERROR';
        }

        if ($maxExecutionTimeSeconds > 0 && $executionTimeMs > ($maxExecutionTimeSeconds * 1000)) {
            return 'TIMEOUT';
        }

        return 'OK';
    }

    private static function collectDbCounters(): array
    {
        $count = 0;
        $timeMs = 0;

        foreach (Sgbd::$db as $connections) {
            if (!is_array($connections)) {
                continue;
            }

            foreach ($connections as $connection) {
                if (!is_object($connection)) {
                    continue;
                }

                $count += isset($connection->number_of_query) ? (int)$connection->number_of_query : 0;
                if (!empty($connection->query) && is_array($connection->query)) {
                    foreach ($connection->query as $query) {
                        $timeMs += (int)round(((float)($query['time'] ?? 0)) * 1000);
                    }
                }
            }
        }

        return [
            'count' => $count,
            'time_ms' => $timeMs,
        ];
    }

    private static function exitCode(string $status, $code): ?int
    {
        if ($status === 'OK' || $status === 'TIMEOUT' || $status === 'SKIPPED') {
            return 0;
        }

        if (is_numeric($code) && (int)$code !== 0) {
            return (int)$code;
        }

        return $status === 'ERROR' ? 1 : null;
    }

    private static function status(string $status): string
    {
        return in_array($status, self::STATUSES, true) ? $status : 'OK';
    }

    private static function dateTime($value): string
    {
        if (is_int($value) || is_float($value)) {
            $timestamp = (float)$value;
            $seconds = (int)$timestamp;
            $microseconds = (int)round(($timestamp - $seconds) * 1000000);

            if ($microseconds >= 1000000) {
                $seconds++;
                $microseconds = 0;
            }

            return date('Y-m-d H:i:s', $seconds).'.'.sprintf('%06d', $microseconds);
        }

        $value = trim((string)$value);
        if ($value !== '' && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}(?:\.\d{1,6})?$/', $value) === 1) {
            return $value;
        }

        return self::dateTime(microtime(true));
    }

    private static function shortString($value, int $maxLength): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }

        return substr($value, 0, $maxLength);
    }

    private static function positiveInt($value): ?int
    {
        if (!is_numeric($value)) {
            return null;
        }

        $int = (int)$value;

        return $int > 0 ? $int : null;
    }

    private static function nonNegativeInt($value): int
    {
        if (!is_numeric($value)) {
            return 0;
        }

        return max(0, (int)$value);
    }

    private static function nullableNonNegativeInt($value): ?int
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }

        return max(0, (int)$value);
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
