<?php

namespace App\Library\Kpi;

use Glial\Sgbd\Sgbd;

final class AspirateurAttemptLogger
{
    private const KINDS = [
        'mysql',
        'ssh',
        'proxysql',
        'maxscale',
        'maxscale_service',
        'mysqlrouter',
        'mysql_log',
    ];

    private const PHASES = [
        'connect',
        'query',
        'parse',
        'export',
        'retry',
    ];

    public static function log(array $input): bool
    {
        $attempt = self::buildAttempt($input);
        if ($attempt === null) {
            return false;
        }

        $db = null;
        try {
            $db = Sgbd::sql(DB_DEFAULT);
            $db->sql_query(self::buildInsertSql($db, $attempt));

            return true;
        } catch (\Throwable $e) {
            return false;
        } finally {
            if (is_object($db) && method_exists($db, 'sql_close')) {
                $db->sql_close();
            }
        }
    }

    public static function buildAttempt(array $input): ?array
    {
        $idWorkerExecution = self::positiveInt($input['id_worker_execution'] ?? null);
        if ($idWorkerExecution === null) {
            return null;
        }

        $result = self::normalizeResult($input['result'] ?? $input['available'] ?? 0);
        $previousResult = self::nullableResult($input['previous_result'] ?? null);
        $triggeredStateChange = array_key_exists('triggered_state_change', $input)
            ? self::boolInt($input['triggered_state_change'])
            : (($previousResult !== null && $previousResult !== $result) ? 1 : 0);

        return [
            'id_worker_execution' => $idWorkerExecution,
            'id_mysql_server' => self::positiveInt($input['id_mysql_server'] ?? null),
            'id_proxysql_server' => self::positiveInt($input['id_proxysql_server'] ?? null),
            'id_maxscale_server' => self::positiveInt($input['id_maxscale_server'] ?? null),
            'id_mysqlrouter_server' => self::positiveInt($input['id_mysqlrouter_server'] ?? null),
            'kind' => self::enumValue((string)($input['kind'] ?? 'mysql'), self::KINDS, 'mysql'),
            'phase' => self::enumValue((string)($input['phase'] ?? 'connect'), self::PHASES, 'connect'),
            'result' => $result,
            'ping_seconds' => self::nullableDecimal($input['ping_seconds'] ?? $input['ping'] ?? null),
            'error_class' => self::shortString($input['error_class'] ?? null, 128),
            'error_message' => KpiSanitizer::errorMessage($input['error_message'] ?? null, 512),
            'transient' => self::boolInt($input['transient'] ?? false),
            'triggered_state_change' => $triggeredStateChange,
            'set_readonly_reason' => self::shortString($input['set_readonly_reason'] ?? null, 255),
            'started_at' => self::dateTime($input['started_at'] ?? null),
            'ended_at' => self::dateTime($input['ended_at'] ?? microtime(true)),
        ];
    }

    public static function buildInsertSql($db, array $attempt): string
    {
        $columns = [
            'id_worker_execution',
            'id_mysql_server',
            'id_proxysql_server',
            'id_maxscale_server',
            'id_mysqlrouter_server',
            'kind',
            'phase',
            'result',
            'ping_seconds',
            'error_class',
            'error_message',
            'transient',
            'triggered_state_change',
            'set_readonly_reason',
            'started_at',
            'ended_at',
        ];

        $values = [];
        foreach ($columns as $column) {
            $values[] = self::sqlLiteral($db, $attempt[$column] ?? null);
        }

        return 'INSERT INTO `aspirateur_attempt` (`'
            .implode('`, `', $columns)
            .'`) VALUES ('
            .implode(', ', $values)
            .');';
    }

    private static function positiveInt($value): ?int
    {
        if (!is_numeric($value)) {
            return null;
        }

        $int = (int)$value;

        return $int > 0 ? $int : null;
    }

    private static function normalizeResult($value): int
    {
        $int = is_numeric($value) ? (int)$value : 0;
        if ($int === 2) {
            return 2;
        }

        return $int === 1 ? 1 : 0;
    }

    private static function nullableResult($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::normalizeResult($value);
    }

    private static function enumValue(string $value, array $allowed, string $fallback): string
    {
        return in_array($value, $allowed, true) ? $value : $fallback;
    }

    private static function nullableDecimal($value): ?float
    {
        if (!is_numeric($value)) {
            return null;
        }

        return round((float)$value, 6);
    }

    private static function boolInt($value): int
    {
        return !empty($value) ? 1 : 0;
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

    private static function sqlLiteral($db, $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_int($value)) {
            return (string)$value;
        }

        if (is_float($value)) {
            return number_format($value, 6, '.', '');
        }

        if (!method_exists($db, 'sql_real_escape_string')) {
            throw new \InvalidArgumentException('Database adapter must expose sql_real_escape_string().');
        }

        $escaped = $db->sql_real_escape_string((string)$value);

        return "'".$escaped."'";
    }
}
