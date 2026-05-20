<?php

namespace App\Library\Kpi;

use Glial\Sgbd\Sgbd;

final class Dot3KpiRecorder
{
    private const CONNECTION_SLOT = 'dot3_kpi';
    private const GROUP_KINDS = [
        'master_slave',
        'proxysql',
        'innodb_cluster',
        'vip',
        'galera',
        'maxscale',
        'imported',
        'mysqlrouter',
    ];

    public static function startRun(array $input = []): ?int
    {
        $payload = [
            'started_at' => self::dateTime($input['started_at'] ?? microtime(true)),
        ];

        $db = null;
        try {
            $db = Sgbd::sql(DB_DEFAULT, self::CONNECTION_SLOT);
            $db->sql_query(self::buildStartRunSql($db, $payload));

            return (int)$db->sql_insert_id();
        } catch (\Throwable $e) {
            return null;
        } finally {
            if (is_object($db) && method_exists($db, 'sql_close')) {
                $db->sql_close();
            }
        }
    }

    public static function finishRun(?int $idDot3Run, array $input): bool
    {
        if (empty($idDot3Run)) {
            return false;
        }

        $payload = self::buildFinishRunPayload(array_merge($input, [
            'id' => $idDot3Run,
        ]));
        if ($payload === null) {
            return false;
        }

        $db = null;
        try {
            $db = Sgbd::sql(DB_DEFAULT, self::CONNECTION_SLOT);
            $db->sql_query(self::buildFinishRunSql($db, $payload));

            return true;
        } catch (\Throwable $e) {
            return false;
        } finally {
            if (is_object($db) && method_exists($db, 'sql_close')) {
                $db->sql_close();
            }
        }
    }

    public static function recordGroup(?int $idDot3Run, string $kind, array $input): bool
    {
        if (empty($idDot3Run)) {
            return false;
        }

        $payload = self::buildGroupPayload(array_merge($input, [
            'id_dot3_run' => $idDot3Run,
            'group_kind' => $kind,
        ]));
        if ($payload === null) {
            return false;
        }

        $db = null;
        try {
            $db = Sgbd::sql(DB_DEFAULT, self::CONNECTION_SLOT);
            $db->sql_query(self::buildRecordGroupSql($db, $payload));

            return true;
        } catch (\Throwable $e) {
            return false;
        } finally {
            if (is_object($db) && method_exists($db, 'sql_close')) {
                $db->sql_close();
            }
        }
    }

    public static function buildStartRunSql($db, array $payload): string
    {
        return 'INSERT INTO `dot3_run` (`started_at`) VALUES ('
            .self::sqlLiteral($db, $payload['started_at'])
            .');';
    }

    public static function buildFinishRunPayload(array $input): ?array
    {
        $id = self::positiveInt($input['id'] ?? null);
        if ($id === null) {
            return null;
        }

        $startedAt = self::timestamp($input['started_at'] ?? microtime(true));
        $endedAt = self::timestamp($input['ended_at'] ?? microtime(true));

        return [
            'id' => $id,
            'ended_at' => self::dateTime($endedAt),
            'duration_ms' => self::durationMs($startedAt, $endedAt),
            'groups_total' => self::nullableNonNegativeInt($input['groups_total'] ?? null),
            'nodes_total' => self::nullableNonNegativeInt($input['nodes_total'] ?? null),
            'edges_total' => self::nullableNonNegativeInt($input['edges_total'] ?? null),
            'dot_size_bytes' => self::nullableNonNegativeInt($input['dot_size_bytes'] ?? null),
            'svg_size_bytes' => self::nullableNonNegativeInt($input['svg_size_bytes'] ?? null),
        ];
    }

    public static function buildFinishRunSql($db, array $payload): string
    {
        $assignments = [
            '`ended_at` = '.self::sqlLiteral($db, $payload['ended_at']),
            '`duration_ms` = '.self::sqlLiteral($db, $payload['duration_ms']),
            '`groups_total` = '.self::sqlLiteral($db, $payload['groups_total']),
            '`nodes_total` = '.self::sqlLiteral($db, $payload['nodes_total']),
            '`edges_total` = '.self::sqlLiteral($db, $payload['edges_total']),
            '`dot_size_bytes` = '.self::sqlLiteral($db, $payload['dot_size_bytes']),
            '`svg_size_bytes` = '.self::sqlLiteral($db, $payload['svg_size_bytes']),
        ];

        return 'UPDATE `dot3_run` SET '
            .implode(', ', $assignments)
            .' WHERE `id` = '.(int)$payload['id'].';';
    }

    public static function buildGroupPayload(array $input): ?array
    {
        $idDot3Run = self::positiveInt($input['id_dot3_run'] ?? null);
        $kind = self::groupKind((string)($input['group_kind'] ?? ''));
        if ($idDot3Run === null || $kind === null) {
            return null;
        }

        return [
            'id_dot3_run' => $idDot3Run,
            'group_kind' => $kind,
            'duration_ms' => self::nullableNonNegativeInt($input['duration_ms'] ?? null),
            'nodes' => self::nullableNonNegativeInt($input['nodes'] ?? null),
            'edges' => self::nullableNonNegativeInt($input['edges'] ?? null),
        ];
    }

    public static function buildRecordGroupSql($db, array $payload): string
    {
        $columns = ['id_dot3_run', 'group_kind', 'duration_ms', 'nodes', 'edges'];
        $values = [
            $payload['id_dot3_run'],
            $payload['group_kind'],
            $payload['duration_ms'],
            $payload['nodes'],
            $payload['edges'],
        ];

        return 'INSERT INTO `dot3_run_group` (`'
            .implode('`, `', $columns)
            .'`) VALUES ('
            .implode(', ', array_map(static fn($value): string => self::sqlLiteral($db, $value), $values))
            .') ON DUPLICATE KEY UPDATE '
            .'`duration_ms` = VALUES(`duration_ms`), '
            .'`nodes` = VALUES(`nodes`), '
            .'`edges` = VALUES(`edges`);';
    }

    public static function measureGroups(array $groups): array
    {
        $nodes = array();
        $edges = 0;

        foreach ($groups as $group) {
            if (!is_array($group)) {
                continue;
            }

            $groupNodes = array();
            foreach ($group as $node) {
                if (!is_scalar($node) || (string)$node === '') {
                    continue;
                }

                $groupNodes[(string)$node] = true;
                $nodes[(string)$node] = true;
            }

            $edges += max(0, count($groupNodes) - 1);
        }

        return [
            'nodes' => count($nodes),
            'edges' => $edges,
        ];
    }

    public static function measureGraphFiles($svgPath, $dot): array
    {
        $svgPath = is_string($svgPath) ? $svgPath : '';

        return [
            'dot_size_bytes' => strlen((string)$dot),
            'svg_size_bytes' => ($svgPath !== '' && is_file($svgPath)) ? (int)filesize($svgPath) : null,
        ];
    }

    public static function durationMs(float $startedAt, float $endedAt): int
    {
        return max(0, (int)round(($endedAt - $startedAt) * 1000));
    }

    private static function groupKind(string $kind): ?string
    {
        return in_array($kind, self::GROUP_KINDS, true) ? $kind : null;
    }

    private static function timestamp($value): float
    {
        if (is_int($value) || is_float($value)) {
            return (float)$value;
        }

        $value = trim((string)$value);
        if ($value !== '' && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}(?:\.\d{1,6})?$/', $value) === 1) {
            $format = strpos($value, '.') === false ? 'Y-m-d H:i:s' : 'Y-m-d H:i:s.u';
            $date = \DateTimeImmutable::createFromFormat($format, $value);
            if ($date instanceof \DateTimeImmutable) {
                return (float)$date->format('U.u');
            }
        }

        return microtime(true);
    }

    private static function dateTime($value): string
    {
        if (!is_int($value) && !is_float($value)) {
            $value = self::timestamp($value);
        }

        $timestamp = (float)$value;
        $seconds = (int)$timestamp;
        $microseconds = (int)round(($timestamp - $seconds) * 1000000);

        if ($microseconds >= 1000000) {
            $seconds++;
            $microseconds = 0;
        }

        return date('Y-m-d H:i:s', $seconds).'.'.sprintf('%06d', $microseconds);
    }

    private static function positiveInt($value): ?int
    {
        if (!is_numeric($value)) {
            return null;
        }

        $int = (int)$value;

        return $int > 0 ? $int : null;
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
