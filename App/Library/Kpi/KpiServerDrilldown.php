<?php

namespace App\Library\Kpi;

use App\Library\Extraction2;
use App\Library\ServerStateTimeline;
use Glial\Sgbd\Sgbd;

final class KpiServerDrilldown
{
    public const ATTEMPT_LIMIT = 500;
    public const VARIABLE_DIFF_LIMIT = 200;

    private const CONNECTION_SLOT = 'kpi_server_drilldown';
    private static array $tableExistsCache = [];

    public static function buildPayload(int $serverId, array $options = []): array
    {
        $serverId = max(0, $serverId);
        $now = self::dateTimeObject($options['now'] ?? 'now');
        $since = self::dateTime($now->modify('-24 hours'));
        $warnings = [];
        $degraded = false;

        $db = null;
        try {
            $db = Sgbd::sql(DB_DEFAULT, self::CONNECTION_SLOT);
            $server = self::fetchServer($db, $serverId);
            if ($server === null) {
                return self::emptyPayload($serverId, $now, ['Server not found.']);
            }

            try {
                $current = self::fetchCurrentStatus($serverId);
            } catch (\Throwable $e) {
                $degraded = true;
                $warnings[] = 'Current server status is unavailable: '.KpiSanitizer::errorMessage($e->getMessage(), 256);
                $current = ['value' => null, 'mysql_error' => null];
            }

            try {
                $timeline = self::fetchTimeline($serverId, $now);
            } catch (\Throwable $e) {
                $degraded = true;
                $warnings[] = 'Server state timeline is unavailable: '.KpiSanitizer::errorMessage($e->getMessage(), 256);
                $timeline = self::timelineFromRows([], $now->modify('-24 hours'), $now);
            }

            $attempts = [];
            $lastSuccessAt = null;
            $lastError = null;
            $sparklines = [];

            if (self::tableExists($db, 'aspirateur_attempt')) {
                $attempts = self::fetchAttempts($db, $serverId, $since, self::ATTEMPT_LIMIT);
                $lastSuccessAt = self::fetchScalar($db, self::buildLastSuccessSql($serverId));
                $lastError = self::fetchLastError($db, $serverId);
                $sparklines = self::buildPingSparklines($attempts);
            } else {
                $degraded = true;
                $warnings[] = 'Table aspirateur_attempt is missing; KPI attempts are not available yet.';
            }

            if ($lastError === null && !empty($current['mysql_error'])) {
                $lastError = [
                    'error_class' => 'mysql_error',
                    'error_message' => $current['mysql_error'],
                    'started_at' => null,
                ];
            }

            $statusSince = self::detectStatusSince($timeline['points'], $current['value']);
            $variableDiff = self::tableExists($db, 'global_variable')
                ? self::fetchVariableDiff($db, $serverId, self::VARIABLE_DIFF_LIMIT)
                : ['available' => false, 'rows' => [], 'truncated' => 0, 'message' => 'global_variable is missing.'];

            return [
                'server_id' => $serverId,
                'server' => $server,
                'status' => [
                    'value' => $current['value'],
                    'label' => self::statusLabel($current['value']),
                    'class' => self::statusClass($current['value']),
                    'since' => $statusSince,
                ],
                'last_success_at' => $lastSuccessAt,
                'last_error' => $lastError,
                'timeline' => $timeline,
                'attempts' => $attempts,
                'sparklines' => $sparklines,
                'variable_diff' => $variableDiff,
                'postmortem_path' => self::buildPostmortemPath($serverId, $now),
                'postmortem_available' => self::isPostmortemIndexAvailable(),
                'generated_at' => self::dateTime($now),
                'degraded' => $degraded,
                'warnings' => $warnings,
            ];
        } catch (\Throwable $e) {
            return self::emptyPayload($serverId, $now, [KpiSanitizer::errorMessage($e->getMessage(), 512)], true);
        } finally {
            if (is_object($db) && method_exists($db, 'sql_close')) {
                $db->sql_close();
            }
        }
    }

    public static function buildServerSql($db, int $serverId): string
    {
        return 'SELECT a.id, a.name, a.display_name, a.ip, a.port, a.hostname, '
            .'a.is_monitored, a.is_acknowledged, c.libelle AS client, e.libelle AS environment '
            .'FROM `mysql_server` a '
            .'INNER JOIN `client` c ON c.id = a.id_client '
            .'INNER JOIN `environment` e ON e.id = a.id_environment '
            .'WHERE a.id = '.$serverId.' AND a.is_deleted = 0 LIMIT 1;';
    }

    public static function buildAttemptsSql($db, int $serverId, string $since, int $limit = self::ATTEMPT_LIMIT): string
    {
        $limit = max(1, min(1000, $limit));

        return 'SELECT a.kind, a.phase, a.result, a.ping_seconds, a.error_class, a.error_message, '
            .'a.transient, a.triggered_state_change, a.started_at, a.ended_at, '
            .'a.id_worker_execution, we.id_worker_run, wr.pid AS worker_pid '
            .'FROM `aspirateur_attempt` a '
            .'LEFT JOIN `worker_execution` we ON we.id = a.id_worker_execution '
            .'LEFT JOIN `worker_run` wr ON wr.id = we.id_worker_run '
            .'WHERE a.id_mysql_server = '.$serverId.' '
            .'AND a.started_at >= '.self::sqlLiteral($db, $since).' '
            .'ORDER BY a.started_at DESC LIMIT '.$limit.';';
    }

    public static function buildLastSuccessSql(int $serverId): string
    {
        return 'SELECT MAX(`started_at`) AS `last_success_at` '
            .'FROM `aspirateur_attempt` '
            .'WHERE `id_mysql_server` = '.$serverId.' AND `result` = 1;';
    }

    public static function buildLastErrorSql(int $serverId): string
    {
        return 'SELECT `error_class`, `error_message`, `started_at` '
            .'FROM `aspirateur_attempt` '
            .'WHERE `id_mysql_server` = '.$serverId.' '
            .'AND (`error_class` IS NOT NULL OR `error_message` IS NOT NULL OR `result` = 0) '
            .'ORDER BY `started_at` DESC LIMIT 1;';
    }

    public static function buildVariableDiffSql($db, int $serverId, int $limit = self::VARIABLE_DIFF_LIMIT): string
    {
        $limit = max(1, min(1000, $limit + 1));

        return 'SELECT c.variable_name, c.value AS value_today, p.value AS value_yesterday '
            .'FROM `global_variable` c '
            .'LEFT JOIN `global_variable` FOR SYSTEM_TIME AS OF TIMESTAMP (NOW() - INTERVAL 1 DAY) p '
            .'ON p.id_mysql_server = c.id_mysql_server AND p.variable_name = c.variable_name '
            .'WHERE c.id_mysql_server = '.$serverId.' '
            .'AND (p.variable_name IS NULL OR c.value <> p.value) '
            .'ORDER BY c.variable_name ASC LIMIT '.$limit.';';
    }

    public static function timelineFromRows(array $rows, \DateTimeImmutable $start, \DateTimeImmutable $end): array
    {
        $bucketRows = [];
        foreach ($rows as $row) {
            $timestamp = strtotime((string)($row['date'] ?? ''));
            if ($timestamp === false) {
                continue;
            }

            $bucketTimestamp = $timestamp - ($timestamp % 60);
            $bucketRows[date('Y-m-d H:i:00', $bucketTimestamp)][] = $row['value'] ?? null;
        }

        $labels = [];
        $values = [];
        $points = [];
        $cursor = self::minuteStart($start);
        $end = self::minuteStart($end);

        while ($cursor <= $end) {
            $key = $cursor->format('Y-m-d H:i:s');
            $value = ServerStateTimeline::aggregateMinuteValues($bucketRows[$key] ?? []);
            $labels[] = $cursor->format('H:i');
            $values[] = $value;
            $points[] = ['date' => $key, 'value' => $value];
            $cursor = $cursor->modify('+1 minute');
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'points' => $points,
            'summary' => self::summarizeTimeline($values),
        ];
    }

    public static function detectStatusSince(array $points, ?int $currentStatus): ?string
    {
        if ($currentStatus === null || empty($points)) {
            return null;
        }

        $since = null;
        for ($i = count($points) - 1; $i >= 0; $i--) {
            if (($points[$i]['value'] ?? null) !== $currentStatus) {
                break;
            }

            $since = $points[$i]['date'] ?? null;
        }

        return $since;
    }

    public static function buildPingSparklines(array $attempts): array
    {
        $series = [];
        foreach (array_reverse($attempts) as $attempt) {
            if (!is_numeric($attempt['ping_seconds'] ?? null)) {
                continue;
            }

            $kind = (string)($attempt['kind'] ?? 'unknown');
            $series[$kind][] = [
                'x' => (string)($attempt['started_at'] ?? ''),
                'y' => round(((float)$attempt['ping_seconds']) * 1000, 3),
            ];
        }

        return $series;
    }

    public static function statusLabel(?int $value): string
    {
        if ($value === 1) {
            return 'UP';
        }

        if ($value === 2) {
            return 'READ ONLY';
        }

        if ($value === 0) {
            return 'DOWN';
        }

        return 'UNKNOWN';
    }

    public static function statusClass(?int $value): string
    {
        if ($value === 1) {
            return 'up';
        }

        if ($value === 2) {
            return 'readonly';
        }

        if ($value === 0) {
            return 'down';
        }

        return 'unknown';
    }

    private static function fetchServer($db, int $serverId): ?array
    {
        $res = $db->sql_query(self::buildServerSql($db, $serverId));
        $row = $db->sql_fetch_array($res, MYSQLI_ASSOC);

        return is_array($row) ? $row : null;
    }

    private static function fetchCurrentStatus(int $serverId): array
    {
        $display = Extraction2::display(['mysql_available', 'mysql_error'], [$serverId]);
        $value = $display[$serverId]['mysql_available'] ?? null;

        return [
            'value' => self::normalizeAvailability($value),
            'mysql_error' => $display[$serverId]['mysql_error'] ?? null,
        ];
    }

    private static function fetchTimeline(int $serverId, \DateTimeImmutable $now): array
    {
        $end = self::minuteStart($now);
        $start = $end->modify('-24 hours');
        $rows = [];

        $result = Extraction2::extract(
            ['mysql_available'],
            [$serverId],
            [$start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')],
            true,
            false
        );

        if ($result instanceof \mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }

        return self::timelineFromRows($rows, $start, $end);
    }

    private static function fetchAttempts($db, int $serverId, string $since, int $limit): array
    {
        $attempts = [];
        $res = $db->sql_query(self::buildAttemptsSql($db, $serverId, $since, $limit));
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $row['result_label'] = self::statusLabel(self::normalizeAvailability($row['result'] ?? null));
            $row['result_class'] = self::statusClass(self::normalizeAvailability($row['result'] ?? null));
            $attempts[] = $row;
        }

        return $attempts;
    }

    private static function fetchLastError($db, int $serverId): ?array
    {
        $res = $db->sql_query(self::buildLastErrorSql($serverId));
        $row = $db->sql_fetch_array($res, MYSQLI_ASSOC);

        return is_array($row) ? $row : null;
    }

    private static function fetchVariableDiff($db, int $serverId, int $limit): array
    {
        try {
            $rows = [];
            $res = $db->sql_query(self::buildVariableDiffSql($db, $serverId, $limit));
            while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $rows[] = $row;
            }

            $hasMore = count($rows) > $limit;

            return [
                'available' => true,
                'rows' => array_slice($rows, 0, $limit),
                'has_more' => $hasMore,
                'truncated' => $hasMore ? null : 0,
                'message' => '',
            ];
        } catch (\Throwable $e) {
            return [
                'available' => false,
                'rows' => [],
                'has_more' => false,
                'truncated' => 0,
                'message' => KpiSanitizer::errorMessage($e->getMessage(), 512),
            ];
        }
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

    private static function summarizeTimeline(array $values): array
    {
        $summary = ['up' => 0, 'readonly' => 0, 'down' => 0, 'unknown' => 0];
        foreach ($values as $value) {
            $summary[self::statusClass(is_int($value) ? $value : null)]++;
        }

        return $summary;
    }

    private static function normalizeAvailability($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ((string)$value === '0') {
            return 0;
        }

        if ((string)$value === '1') {
            return 1;
        }

        if ((string)$value === '2') {
            return 2;
        }

        return null;
    }

    private static function emptyPayload(int $serverId, \DateTimeImmutable $now, array $warnings, bool $degraded = true): array
    {
        return [
            'server_id' => $serverId,
            'server' => null,
            'status' => ['value' => null, 'label' => 'UNKNOWN', 'class' => 'unknown', 'since' => null],
            'last_success_at' => null,
            'last_error' => null,
            'timeline' => self::timelineFromRows([], $now->modify('-24 hours'), $now),
            'attempts' => [],
            'sparklines' => [],
            'variable_diff' => ['available' => false, 'rows' => [], 'truncated' => 0, 'message' => ''],
            'postmortem_path' => self::buildPostmortemPath($serverId, $now),
            'postmortem_available' => self::isPostmortemIndexAvailable(),
            'generated_at' => self::dateTime($now),
            'degraded' => $degraded,
            'warnings' => $warnings,
        ];
    }

    private static function buildPostmortemPath(int $serverId, \DateTimeImmutable $now): string
    {
        return 'postmortem/index/'.$serverId.'?around='.rawurlencode(self::dateTime($now)).'&radius=24h';
    }

    private static function isPostmortemIndexAvailable(): bool
    {
        return method_exists('\\App\\Controller\\PostMortem', 'index');
    }

    private static function minuteStart(\DateTimeImmutable $date): \DateTimeImmutable
    {
        return $date->setTime((int)$date->format('H'), (int)$date->format('i'), 0, 0);
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
