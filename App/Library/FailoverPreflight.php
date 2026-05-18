<?php

declare(strict_types=1);

namespace App\Library;

final class FailoverPreflight
{
    /**
     * @return array{verdict:string,checks:list<array{name:string,status:string,message:string}>}
     */
    public static function evaluate(array $slaveRow, array $context = []): array
    {
        $health = $context['health'] ?? ReplicationHealth::evaluate($slaveRow, $context);
        $filters = $context['filters'] ?? ReplicationFiltersAudit::audit($slaveRow);
        $ssl = $context['ssl'] ?? ReplicationUserSslAudit::audit($slaveRow);
        $stuck = $context['stuck_sql'] ?? ['stuck' => false, 'message' => 'No stuck applier worker detected.'];
        $heartbeat = $context['heartbeat'] ?? ReplicationHeartbeatCheck::evaluate($slaveRow);
        $errant = trim((string)($context['errant_gtid_set'] ?? ''));
        $binlogFormatOk = strtoupper((string)($context['binlog_format_consistent'] ?? '1')) !== '0';
        $serverIdUnique = (bool)($context['server_id_unique'] ?? true);
        $readOnly = self::truthy($context['super_read_only'] ?? $slaveRow['super_read_only'] ?? 'ON');
        $engineOk = (bool)($context['storage_engine_ok'] ?? true);
        $autoIncrementOk = (bool)($context['auto_increment_offset_ok'] ?? true);

        $checks = [
            self::check('GTID consistency', $errant === '', $errant === '' ? 'No errant transactions.' : 'Errant set: ' . $errant, true),
            self::check('Lag', (int)($health['score'] ?? 0) >= 60, 'Health score ' . (int)($health['score'] ?? 0) . '/100.', false),
            self::check('Semi-sync', (bool)(($context['semi_sync']['healthy'] ?? true)), (string)($context['semi_sync']['message'] ?? 'Semi-sync not blocking.'), false),
            self::check('Applier workers', empty($stuck['stuck']), (string)($stuck['message'] ?? ''), true),
            self::check('Filters', empty($filters['rows']), empty($filters['rows']) ? 'No replication filters.' : 'Replication filters active.', false),
            self::check('SSL', (bool)($ssl['healthy'] ?? false), (string)($ssl['message'] ?? ''), false),
            self::check('Heartbeat', (bool)($heartbeat['healthy'] ?? true), (string)($heartbeat['message'] ?? ''), false),
            self::check('binlog_format', $binlogFormatOk, 'Replica binlog format is consistent with siblings.', true),
            self::check('super_read_only', $readOnly, 'super_read_only should be enabled before promotion.', false),
            self::check('Storage engine', $engineOk, 'Replicated tables use supported engines.', true),
            self::check('Server-id', $serverIdUnique, 'Server id is unique in the cluster.', true),
            self::check('AUTO_INCREMENT offset', $autoIncrementOk, 'Auto increment offset is distinct from siblings.', true),
        ];

        $blocked = false;
        $warnings = 0;
        foreach ($checks as $check) {
            if ($check['status'] === 'blocked') {
                $blocked = true;
            } elseif ($check['status'] === 'warning') {
                $warnings++;
            }
        }

        return [
            'verdict' => $blocked ? 'blocked' : ($warnings > 0 ? 'risky' : 'ready'),
            'checks' => $checks,
        ];
    }

    /**
     * @return array{name:string,status:string,message:string}
     */
    private static function check(string $name, bool $ok, string $message, bool $blocker): array
    {
        return [
            'name' => $name,
            'status' => $ok ? 'ok' : ($blocker ? 'blocked' : 'warning'),
            'message' => $message,
        ];
    }

    private static function truthy(mixed $value): bool
    {
        return in_array(strtoupper((string)$value), ['1', 'ON', 'YES', 'TRUE'], true);
    }
}
