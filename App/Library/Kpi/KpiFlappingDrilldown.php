<?php

namespace App\Library\Kpi;

use Glial\Sgbd\Sgbd;

final class KpiFlappingDrilldown
{
    public const ROUTER_ATTEMPT_LIMIT = 500;
    public const TOP_ROUTER_LIMIT = 10;
    public const EVENT_LIMIT = 20;
    public const FLAPPING_THRESHOLD = 2;
    public const FLAPPING_DURATION_MINUTES = 5;

    private const CONNECTION_SLOT = 'kpi_flapping_drilldown';
    private const EVENT_TYPE = 'kpi_flapping';
    private static array $tableExistsCache = [];

    public static function buildGlobalPayload(array $options = []): array
    {
        $now = self::dateTimeObject($options['now'] ?? 'now');
        $since = self::dateTime($now->modify('-24 hours'));
        $warnings = [];
        $degraded = false;
        $buckets = [];
        $topRouters = [];
        $events = [];

        $db = null;
        try {
            $db = Sgbd::sql(DB_DEFAULT, self::CONNECTION_SLOT);

            if (self::tableExists($db, 'kpi_minute')) {
                $buckets = self::fetchRows($db, self::buildGlobalBucketsSql($db, $since));
            } else {
                $degraded = true;
                $warnings[] = 'Table kpi_minute is missing; global flapping histogram is unavailable.';
            }

            if (self::tableExists($db, 'aspirateur_attempt')) {
                $topRouters = self::fetchRows($db, self::buildTopRoutersSql($db, $since, self::TOP_ROUTER_LIMIT));
            } else {
                $degraded = true;
                $warnings[] = 'Table aspirateur_attempt is missing; router top-10 is unavailable.';
            }

            if (self::tableExists($db, 'event_log')) {
                $events = self::fetchRows($db, self::buildFlappingEventsSql($db, $since, self::EVENT_LIMIT));
            } else {
                $degraded = true;
                $warnings[] = 'Table event_log is missing; flapping events are unavailable.';
            }

            return [
                'since' => $since,
                'generated_at' => self::dateTime($now),
                'threshold' => self::FLAPPING_THRESHOLD,
                'duration_minutes' => self::FLAPPING_DURATION_MINUTES,
                'buckets' => self::normalizeBucketRows($buckets, 'state_transitions_total'),
                'windows' => self::detectFlappingWindows($buckets, self::FLAPPING_THRESHOLD, self::FLAPPING_DURATION_MINUTES),
                'top_routers' => self::decorateTopRouters($topRouters),
                'events' => $events,
                'event_type' => self::EVENT_TYPE,
                'degraded' => $degraded,
                'warnings' => $warnings,
            ];
        } catch (\Throwable $e) {
            return self::emptyGlobalPayload($now, [KpiSanitizer::errorMessage($e->getMessage(), 512)], true);
        } finally {
            if (is_object($db) && method_exists($db, 'sql_close')) {
                $db->sql_close();
            }
        }
    }

    public static function buildRouterPayload(int $routerId, array $options = []): array
    {
        $routerId = max(0, $routerId);
        $now = self::dateTimeObject($options['now'] ?? 'now');
        $since = self::dateTime($now->modify('-24 hours'));
        $warnings = [];
        $degraded = false;
        $linkedServers = [];
        $buckets = [];
        $attempts = [];

        $db = null;
        try {
            $db = Sgbd::sql(DB_DEFAULT, self::CONNECTION_SLOT);
            $router = self::fetchRow($db, self::buildRouterSql($db, $routerId));
            if ($router === null) {
                return self::emptyRouterPayload($routerId, $now, ['MySQL Router not found.']);
            }

            if (self::tableExists($db, 'mysqlrouter_server__mysql_server')) {
                $linkedServers = self::fetchRows($db, self::buildLinkedServersSql($db, $routerId));
            } else {
                $degraded = true;
                $warnings[] = 'Table mysqlrouter_server__mysql_server is missing; linked MySQL servers are unavailable.';
            }

            if (self::tableExists($db, 'aspirateur_attempt')) {
                $buckets = self::fetchRows($db, self::buildRouterHistogramSql($db, $routerId, $since));
                $attempts = self::fetchRows($db, self::buildRouterAttemptsSql($db, $routerId, $since, self::ROUTER_ATTEMPT_LIMIT));
            } else {
                $degraded = true;
                $warnings[] = 'Table aspirateur_attempt is missing; router attempts are unavailable.';
            }

            return [
                'router_id' => $routerId,
                'router' => $router,
                'linked_servers' => self::decorateLinkedServers($linkedServers),
                'since' => $since,
                'generated_at' => self::dateTime($now),
                'threshold' => self::FLAPPING_THRESHOLD,
                'duration_minutes' => self::FLAPPING_DURATION_MINUTES,
                'buckets' => self::normalizeBucketRows($buckets, 'transitions_total'),
                'windows' => self::detectFlappingWindows($buckets, self::FLAPPING_THRESHOLD, self::FLAPPING_DURATION_MINUTES),
                'attempts' => self::decorateAttempts($attempts),
                'attempt_limit' => self::ROUTER_ATTEMPT_LIMIT,
                'degraded' => $degraded,
                'warnings' => $warnings,
            ];
        } catch (\Throwable $e) {
            return self::emptyRouterPayload($routerId, $now, [KpiSanitizer::errorMessage($e->getMessage(), 512)], true);
        } finally {
            if (is_object($db) && method_exists($db, 'sql_close')) {
                $db->sql_close();
            }
        }
    }

    public static function buildRouterSql($db, int $routerId): string
    {
        return 'SELECT `id`, `display_name`, `hostname`, `port`, `is_ssl`, `date_inserted` '
            .'FROM `mysqlrouter_server` '
            .'WHERE `id` = '.$routerId.' LIMIT 1;';
    }

    public static function buildLinkedServersSql($db, int $routerId): string
    {
        return 'SELECT l.id_mysql_server, s.name, s.display_name, s.ip, s.port, '
            .'c.libelle AS client, e.libelle AS environment '
            .'FROM `mysqlrouter_server__mysql_server` l '
            .'INNER JOIN `mysql_server` s ON s.id = l.id_mysql_server '
            .'INNER JOIN `client` c ON c.id = s.id_client '
            .'INNER JOIN `environment` e ON e.id = s.id_environment '
            .'WHERE l.id_mysqlrouter_server = '.$routerId.' AND s.is_deleted = 0 '
            .'ORDER BY s.ip, s.port;';
    }

    public static function buildRouterHistogramSql($db, int $routerId, string $since): string
    {
        return 'SELECT DATE_FORMAT(`started_at`, \'%Y-%m-%d %H:%i:00\') AS `bucket_start`, '
            .'COUNT(*) AS `attempts_total`, '
            .'COALESCE(SUM(CASE WHEN `triggered_state_change` = 1 THEN 1 ELSE 0 END), 0) AS `transitions_total`, '
            .'COALESCE(SUM(CASE WHEN `result` = 0 THEN 1 ELSE 0 END), 0) AS `failures_total`, '
            .'COALESCE(SUM(CASE WHEN `result` = 2 THEN 1 ELSE 0 END), 0) AS `readonly_total` '
            .'FROM `aspirateur_attempt` '
            .'WHERE `kind` = \'mysqlrouter\' '
            .'AND `id_mysqlrouter_server` = '.$routerId.' '
            .'AND `started_at` >= '.self::sqlLiteral($db, $since).' '
            .'GROUP BY `bucket_start` '
            .'ORDER BY `bucket_start` ASC;';
    }

    public static function buildRouterAttemptsSql($db, int $routerId, string $since, int $limit = self::ROUTER_ATTEMPT_LIMIT): string
    {
        $limit = max(1, min(1000, $limit));

        return 'SELECT a.id, a.id_mysql_server, a.id_worker_execution, a.result, a.ping_seconds, '
            .'a.error_class, a.error_message, a.transient, a.triggered_state_change, '
            .'a.started_at, a.ended_at, we.id_worker_run, wr.pid AS worker_pid '
            .'FROM `aspirateur_attempt` a '
            .'LEFT JOIN `worker_execution` we ON we.id = a.id_worker_execution '
            .'LEFT JOIN `worker_run` wr ON wr.id = we.id_worker_run '
            .'WHERE a.`kind` = \'mysqlrouter\' '
            .'AND a.`id_mysqlrouter_server` = '.$routerId.' '
            .'AND a.`started_at` >= '.self::sqlLiteral($db, $since).' '
            .'ORDER BY a.`started_at` DESC LIMIT '.$limit.';';
    }

    public static function buildTopRoutersSql($db, string $since, int $limit = self::TOP_ROUTER_LIMIT): string
    {
        $limit = max(1, min(100, $limit));

        return 'SELECT a.id_mysqlrouter_server, '
            .'COALESCE(r.display_name, CONCAT(\'router #\', a.id_mysqlrouter_server)) AS display_name, '
            .'r.hostname, r.port, COUNT(*) AS attempts_total, '
            .'COALESCE(SUM(CASE WHEN a.triggered_state_change = 1 THEN 1 ELSE 0 END), 0) AS transitions_total, '
            .'COALESCE(SUM(CASE WHEN a.result = 0 THEN 1 ELSE 0 END), 0) AS failures_total, '
            .'MAX(a.started_at) AS last_seen_at '
            .'FROM `aspirateur_attempt` a '
            .'LEFT JOIN `mysqlrouter_server` r ON r.id = a.id_mysqlrouter_server '
            .'WHERE a.kind = \'mysqlrouter\' '
            .'AND a.id_mysqlrouter_server IS NOT NULL '
            .'AND a.started_at >= '.self::sqlLiteral($db, $since).' '
            .'GROUP BY a.id_mysqlrouter_server, r.display_name, r.hostname, r.port '
            .'ORDER BY transitions_total DESC, failures_total DESC, last_seen_at DESC LIMIT '.$limit.';';
    }

    public static function buildGlobalBucketsSql($db, string $since): string
    {
        return 'SELECT `bucket_start`, `state_transitions_total` '
            .'FROM `kpi_minute` '
            .'WHERE `bucket_start` >= '.self::sqlLiteral($db, $since).' '
            .'ORDER BY `bucket_start` ASC LIMIT 1440;';
    }

    public static function buildFlappingEventsSql($db, string $since, int $limit = self::EVENT_LIMIT): string
    {
        $limit = max(1, min(100, $limit));

        return 'SELECT `id`, `type`, `message`, `date_start`, `date_end` '
            .'FROM `event_log` '
            .'WHERE `type` = '.self::sqlLiteral($db, self::EVENT_TYPE).' '
            .'AND (`date_end` IS NULL OR `date_start` >= '.self::sqlLiteral($db, $since).') '
            .'ORDER BY `date_start` DESC LIMIT '.$limit.';';
    }

    public static function classifyErrorMessage($message): array
    {
        $message = (string)$message;
        $patterns = [
            'MaxScaleSessionLost' => '/MaxScaleSessionLost/i',
            'Connection refused' => '/Connection refused|refused/i',
            'Timed out' => '/timed?\\s*out|Operation timed out|Connection timed out/i',
            'Access denied' => '/Access denied/i',
            'No route to host' => '/No route to host/i',
            'DNS resolution' => '/Name or service not known|getaddrinfo|Could not resolve/i',
        ];

        foreach ($patterns as $code => $pattern) {
            if (preg_match($pattern, $message)) {
                return ['code' => $code, 'class' => strtolower((string)preg_replace('/[^a-z0-9]+/i', '_', $code))];
            }
        }

        return ['code' => $message === '' ? '-' : 'Other', 'class' => 'other'];
    }

    public static function detectFlappingWindows(
        array $rows,
        int $threshold = self::FLAPPING_THRESHOLD,
        int $durationMinutes = self::FLAPPING_DURATION_MINUTES
    ): array {
        $threshold = max(0, $threshold);
        $durationMinutes = max(1, $durationMinutes);
        $windows = [];
        $streak = [];
        $previousTs = null;

        foreach ($rows as $row) {
            $bucket = (string)($row['bucket_start'] ?? '');
            $timestamp = strtotime($bucket);
            $value = self::rowTransitionValue($row);
            $isConsecutive = $previousTs === null || $timestamp === ($previousTs + 60);

            if ($timestamp === false || $value <= $threshold || !$isConsecutive) {
                self::appendWindow($windows, $streak, $durationMinutes);
                $streak = [];
            }

            if ($timestamp !== false && $value > $threshold) {
                $streak[] = ['bucket_start' => $bucket, 'value' => $value];
            }

            $previousTs = $timestamp === false ? null : $timestamp;
        }

        self::appendWindow($windows, $streak, $durationMinutes);

        return $windows;
    }

    private static function appendWindow(array &$windows, array $streak, int $durationMinutes): void
    {
        if (count($streak) < $durationMinutes) {
            return;
        }

        $values = array_column($streak, 'value');
        $windows[] = [
            'start' => $streak[0]['bucket_start'],
            'end' => $streak[count($streak) - 1]['bucket_start'],
            'minutes' => count($streak),
            'max_value' => max($values),
            'total_transitions' => array_sum($values),
        ];
    }

    private static function rowTransitionValue(array $row): int
    {
        $value = $row['transitions_total'] ?? $row['state_transitions_total'] ?? 0;

        return is_numeric($value) ? (int)$value : 0;
    }

    private static function normalizeBucketRows(array $rows, string $valueKey): array
    {
        $normalized = [];
        foreach ($rows as $row) {
            $value = is_numeric($row[$valueKey] ?? null) ? (int)$row[$valueKey] : 0;
            $normalized[] = [
                'bucket_start' => (string)($row['bucket_start'] ?? ''),
                'value' => $value,
                'attempts_total' => is_numeric($row['attempts_total'] ?? null) ? (int)$row['attempts_total'] : null,
                'failures_total' => is_numeric($row['failures_total'] ?? null) ? (int)$row['failures_total'] : null,
                'readonly_total' => is_numeric($row['readonly_total'] ?? null) ? (int)$row['readonly_total'] : null,
            ];
        }

        return $normalized;
    }

    private static function decorateTopRouters(array $rows): array
    {
        foreach ($rows as $index => $row) {
            $rows[$index]['id_mysqlrouter_server'] = (int)($row['id_mysqlrouter_server'] ?? 0);
            $rows[$index]['attempts_total'] = (int)($row['attempts_total'] ?? 0);
            $rows[$index]['transitions_total'] = (int)($row['transitions_total'] ?? 0);
            $rows[$index]['failures_total'] = (int)($row['failures_total'] ?? 0);
            $rows[$index]['router_path'] = 'Kpi/router/'.$rows[$index]['id_mysqlrouter_server'];
        }

        return $rows;
    }

    private static function decorateLinkedServers(array $rows): array
    {
        foreach ($rows as $index => $row) {
            $serverId = (int)($row['id_mysql_server'] ?? 0);
            $rows[$index]['id_mysql_server'] = $serverId;
            $rows[$index]['logs_path'] = $serverId > 0 ? 'MysqlServer/logs/'.$serverId.'/error' : '';
        }

        return $rows;
    }

    private static function decorateAttempts(array $rows): array
    {
        foreach ($rows as $index => $row) {
            $rows[$index]['result_label'] = KpiServerDrilldown::statusLabel(self::normalizeAvailability($row['result'] ?? null));
            $rows[$index]['result_class'] = KpiServerDrilldown::statusClass(self::normalizeAvailability($row['result'] ?? null));
            $rows[$index]['error_code'] = self::classifyErrorMessage($row['error_message'] ?? '');
        }

        return $rows;
    }

    private static function normalizeAvailability($value): ?int
    {
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

    private static function fetchRow($db, string $sql): ?array
    {
        $res = $db->sql_query($sql);
        $row = $db->sql_fetch_array($res, MYSQLI_ASSOC);

        return is_array($row) ? $row : null;
    }

    private static function fetchRows($db, string $sql): array
    {
        $rows = [];
        $res = $db->sql_query($sql);
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $rows[] = $row;
        }

        return $rows;
    }

    private static function tableExists($db, string $table): bool
    {
        if (array_key_exists($table, self::$tableExistsCache)) {
            return self::$tableExistsCache[$table];
        }

        $sql = 'SELECT COUNT(*) FROM information_schema.tables '
            .'WHERE table_schema = DATABASE() '
            .'AND table_name = '.self::sqlLiteral($db, $table).';';

        $row = self::fetchRow($db, $sql);
        $count = is_array($row) ? ($row['COUNT(*)'] ?? reset($row)) : 0;
        self::$tableExistsCache[$table] = (int)$count > 0;

        return self::$tableExistsCache[$table];
    }

    private static function emptyGlobalPayload(\DateTimeImmutable $now, array $warnings, bool $degraded = true): array
    {
        return [
            'since' => self::dateTime($now->modify('-24 hours')),
            'generated_at' => self::dateTime($now),
            'threshold' => self::FLAPPING_THRESHOLD,
            'duration_minutes' => self::FLAPPING_DURATION_MINUTES,
            'buckets' => [],
            'windows' => [],
            'top_routers' => [],
            'events' => [],
            'event_type' => self::EVENT_TYPE,
            'degraded' => $degraded,
            'warnings' => $warnings,
        ];
    }

    private static function emptyRouterPayload(int $routerId, \DateTimeImmutable $now, array $warnings, bool $degraded = true): array
    {
        return [
            'router_id' => $routerId,
            'router' => null,
            'linked_servers' => [],
            'since' => self::dateTime($now->modify('-24 hours')),
            'generated_at' => self::dateTime($now),
            'threshold' => self::FLAPPING_THRESHOLD,
            'duration_minutes' => self::FLAPPING_DURATION_MINUTES,
            'buckets' => [],
            'windows' => [],
            'attempts' => [],
            'attempt_limit' => self::ROUTER_ATTEMPT_LIMIT,
            'degraded' => $degraded,
            'warnings' => $warnings,
        ];
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
