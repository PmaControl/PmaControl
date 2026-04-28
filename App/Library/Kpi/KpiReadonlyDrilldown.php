<?php

namespace App\Library\Kpi;

use Glial\Sgbd\Sgbd;

final class KpiReadonlyDrilldown
{
    public const TRANSITION_LIMIT = 500;
    public const EVENT_LIMIT = 500;
    public const CORRELATION_WINDOW_SECONDS = 300;

    private const CONNECTION_SLOT = 'kpi_readonly_drilldown';
    private static array $tableExistsCache = [];

    public static function buildPayload(int $serverId, array $options = []): array
    {
        $serverId = max(0, $serverId);
        $now = self::dateTimeObject($options['now'] ?? 'now');
        $since = self::dateTime($now->modify('-24 hours'));
        $warnings = [];
        $degraded = false;
        $eventLog = [
            'available' => false,
            'message' => 'No readonly transition available for event_log correlation.',
        ];
        $history = [
            'available' => false,
            'message' => 'history_action correlation has not been evaluated.',
        ];

        $db = null;
        try {
            $db = Sgbd::sql(DB_DEFAULT, self::CONNECTION_SLOT);
            $server = self::fetchServer($db, $serverId);
            if ($server === null) {
                return self::emptyPayload($serverId, $now, ['Server not found.']);
            }

            $transitions = [];
            if (self::tableExists($db, 'aspirateur_attempt')) {
                $transitions = self::fetchTransitions($db, $serverId, $since, self::TRANSITION_LIMIT);
            } else {
                $degraded = true;
                $warnings[] = 'Table aspirateur_attempt is missing; readonly transitions are not available yet.';
            }

            if (!empty($transitions)) {
                if (self::tableExists($db, 'event_log')) {
                    $bounds = self::transitionBounds($transitions, self::CORRELATION_WINDOW_SECONDS);
                    $events = self::fetchEvents($db, $serverId, $bounds['from'], $bounds['to'], self::EVENT_LIMIT);
                    $transitions = self::correlateEvents($transitions, $events, self::CORRELATION_WINDOW_SECONDS);
                    $eventLog = [
                        'available' => true,
                        'message' => '',
                        'from' => $bounds['from'],
                        'to' => $bounds['to'],
                        'candidate_count' => count($events),
                    ];
                } else {
                    $degraded = true;
                    $eventLog = [
                        'available' => false,
                        'message' => 'Table event_log is missing; no event correlation can be displayed.',
                    ];
                }
            }

            $history = self::historyStatus($db);
            if (empty($history['available'])) {
                $degraded = true;
            }

            return [
                'server_id' => $serverId,
                'server' => $server,
                'since' => $since,
                'generated_at' => self::dateTime($now),
                'transitions' => $transitions,
                'transition_count' => count($transitions),
                'transition_limit' => self::TRANSITION_LIMIT,
                'event_log' => $eventLog,
                'history' => $history,
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

    public static function buildTransitionsSql($db, int $serverId, string $since, int $limit = self::TRANSITION_LIMIT): string
    {
        $limit = max(1, min(1000, $limit));

        return 'SELECT a.id, a.id_mysql_server, a.id_worker_execution, a.kind, a.phase, a.result, '
            .'a.error_class, a.error_message, a.transient, a.triggered_state_change, '
            .'a.set_readonly_reason, a.started_at, a.ended_at, '
            .'we.id_worker_run, wr.pid AS worker_pid '
            .'FROM `aspirateur_attempt` a '
            .'LEFT JOIN `worker_execution` we ON we.id = a.id_worker_execution '
            .'LEFT JOIN `worker_run` wr ON wr.id = we.id_worker_run '
            .'WHERE a.id_mysql_server = '.$serverId.' '
            .'AND a.result = 2 '
            .'AND a.triggered_state_change = 1 '
            .'AND a.started_at >= '.self::sqlLiteral($db, $since).' '
            .'ORDER BY a.started_at DESC LIMIT '.$limit.';';
    }

    public static function buildEventsSql($db, int $serverId, string $from, string $to, int $limit = self::EVENT_LIMIT): string
    {
        $limit = max(1, min(2000, $limit));

        return 'SELECT `id`, `id_mysql_server`, `type`, `message`, `date_start`, `date_end` '
            .'FROM `event_log` '
            .'WHERE `id_mysql_server` = '.$serverId.' '
            .'AND `date_start` <= '.self::sqlLiteral($db, $to).' '
            .'AND (`date_end` IS NULL OR `date_end` >= '.self::sqlLiteral($db, $from).') '
            .'ORDER BY `date_start` DESC LIMIT '.$limit.';';
    }

    public static function parseReadonlyReason($json): array
    {
        $raw = trim((string)$json);
        if ($raw === '') {
            return [
                'valid' => false,
                'raw' => null,
                'read_only' => null,
                'super_read_only' => null,
                'values' => [],
                'message' => 'No set_readonly_reason payload captured.',
            ];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [
                'valid' => false,
                'raw' => $raw,
                'read_only' => null,
                'super_read_only' => null,
                'values' => [],
                'message' => 'Invalid JSON payload.',
            ];
        }

        return [
            'valid' => true,
            'raw' => $raw,
            'read_only' => self::firstPresent($decoded, ['@@global.read_only', 'global.read_only', 'read_only']),
            'super_read_only' => self::firstPresent($decoded, ['@@global.super_read_only', 'global.super_read_only', 'super_read_only']),
            'values' => $decoded,
            'message' => '',
        ];
    }

    public static function buildContextPath(int $serverId, string $startedAt): string
    {
        $token = substr(preg_replace('/[^0-9]/', '', $startedAt) ?? '', 0, 14);
        if ($serverId <= 0 || strlen($token) !== 14) {
            return '';
        }

        return 'MysqlServer/runDetail/'.$serverId.'/'.$token;
    }

    public static function correlateEvents(array $transitions, array $events, int $windowSeconds = self::CORRELATION_WINDOW_SECONDS): array
    {
        $windowSeconds = max(0, $windowSeconds);
        foreach ($transitions as $index => $transition) {
            $transitionTs = strtotime((string)($transition['started_at'] ?? ''));
            $transitions[$index]['events'] = [];
            if ($transitionTs === false) {
                continue;
            }

            $from = $transitionTs - $windowSeconds;
            $to = $transitionTs + $windowSeconds;

            foreach ($events as $event) {
                $eventStart = strtotime((string)($event['date_start'] ?? ''));
                if ($eventStart === false) {
                    continue;
                }

                $eventEndValue = (string)($event['date_end'] ?? '');
                $eventEnd = trim($eventEndValue) === '' ? PHP_INT_MAX : strtotime($eventEndValue);
                if ($eventEnd === false) {
                    $eventEnd = $eventStart;
                }

                if ($eventStart <= $to && $eventEnd >= $from) {
                    $transitions[$index]['events'][] = $event;
                }
            }
        }

        return $transitions;
    }

    private static function fetchServer($db, int $serverId): ?array
    {
        $res = $db->sql_query(KpiServerDrilldown::buildServerSql($db, $serverId));
        $row = $db->sql_fetch_array($res, MYSQLI_ASSOC);

        return is_array($row) ? $row : null;
    }

    private static function fetchTransitions($db, int $serverId, string $since, int $limit): array
    {
        $transitions = [];
        $res = $db->sql_query(self::buildTransitionsSql($db, $serverId, $since, $limit));
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $row['readonly_reason'] = self::parseReadonlyReason($row['set_readonly_reason'] ?? null);
            $row['context_path'] = self::buildContextPath($serverId, (string)($row['started_at'] ?? ''));
            $row['events'] = [];
            $transitions[] = $row;
        }

        return $transitions;
    }

    private static function fetchEvents($db, int $serverId, string $from, string $to, int $limit): array
    {
        $events = [];
        $res = $db->sql_query(self::buildEventsSql($db, $serverId, $from, $to, $limit));
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $events[] = $row;
        }

        return $events;
    }

    private static function transitionBounds(array $transitions, int $windowSeconds): array
    {
        $timestamps = [];
        foreach ($transitions as $transition) {
            $timestamp = strtotime((string)($transition['started_at'] ?? ''));
            if ($timestamp !== false) {
                $timestamps[] = $timestamp;
            }
        }

        if (empty($timestamps)) {
            $now = time();
            return [
                'from' => date('Y-m-d H:i:s', $now - $windowSeconds),
                'to' => date('Y-m-d H:i:s', $now + $windowSeconds),
            ];
        }

        return [
            'from' => date('Y-m-d H:i:s', min($timestamps) - $windowSeconds),
            'to' => date('Y-m-d H:i:s', max($timestamps) + $windowSeconds),
        ];
    }

    private static function historyStatus($db): array
    {
        if (!self::tableExists($db, 'history_action')) {
            return [
                'available' => false,
                'message' => 'Table history_action is missing.',
            ];
        }

        if (!self::tableExists($db, 'history_main')) {
            return [
                'available' => false,
                'message' => 'history_action exists, but history_main is missing locally; no reliable server/date correlation is possible.',
            ];
        }

        return [
            'available' => false,
            'message' => 'history_main exists, but no mysql-server/date correlation contract is implemented yet.',
        ];
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

    private static function firstPresent(array $values, array $keys)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $values)) {
                return $values[$key];
            }
        }

        return null;
    }

    private static function emptyPayload(int $serverId, \DateTimeImmutable $now, array $warnings, bool $degraded = true): array
    {
        return [
            'server_id' => $serverId,
            'server' => null,
            'since' => self::dateTime($now->modify('-24 hours')),
            'generated_at' => self::dateTime($now),
            'transitions' => [],
            'transition_count' => 0,
            'transition_limit' => self::TRANSITION_LIMIT,
            'event_log' => [
                'available' => false,
                'message' => '',
            ],
            'history' => [
                'available' => false,
                'message' => '',
            ],
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
