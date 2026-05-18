<?php

declare(strict_types=1);

namespace App\Library;

final class ReplicationHealth
{
    private const WEIGHTS = [
        'io' => 15,
        'sql' => 20,
        'lag' => 15,
        'gtid' => 15,
        'semi_sync' => 5,
        'ssl' => 5,
        'purge' => 5,
        'filters' => 10,
        'workers' => 5,
        'heartbeat' => 5,
    ];

    /**
     * @return array{score:int,status:string,lights:array<string,array{ok:bool,weight:int,label:string,reason:string,anchor:string}>,stale:bool}
     */
    public static function evaluate(array $slaveRow, array $context = []): array
    {
        $sampledAt = isset($context['sampled_at']) ? strtotime((string)$context['sampled_at']) : time();
        $stale = $sampledAt !== false && $sampledAt < time() - (int)($context['stale_after_seconds'] ?? 300);
        if ($slaveRow === [] || $stale) {
            return self::withStatus(0, self::lightsAll(false, 'No fresh replication sample.'), true);
        }

        $ioRunning = (string)($slaveRow['Slave_IO_Running'] ?? $slaveRow['Replica_IO_Running'] ?? '') === 'Yes';
        $sqlRunning = (string)($slaveRow['Slave_SQL_Running'] ?? $slaveRow['Replica_SQL_Running'] ?? '') === 'Yes';
        $ioError = trim((string)($slaveRow['Last_IO_Error'] ?? ''));
        $sqlError = trim((string)($slaveRow['Last_SQL_Error'] ?? $slaveRow['Last_Error'] ?? ''));
        $lag = self::lagSeconds($slaveRow);
        $lagSla = (int)($context['lag_sla_seconds'] ?? 30);

        $filters = $context['filters'] ?? ReplicationFiltersAudit::audit($slaveRow);
        $semiSync = $context['semi_sync'] ?? SemiSyncAckSla::evaluate($context['semi_sync_status'] ?? []);
        $heartbeat = $context['heartbeat'] ?? ReplicationHeartbeatCheck::evaluate($slaveRow);
        $stuck = $context['stuck_sql'] ?? ['stuck' => false, 'message' => 'No stuck SQL thread detected.'];
        $ssl = $context['ssl'] ?? ReplicationUserSslAudit::audit($slaveRow);
        $errantSet = trim((string)($context['errant_gtid_set'] ?? $slaveRow['errant_gtid_set'] ?? ''));

        $lights = [
            'io' => self::light($ioRunning && $ioError === '', 'io', $ioRunning ? 'IO running' : 'IO stopped or errored', '#sv-repl-status'),
            'sql' => self::light($sqlRunning && $sqlError === '', 'sql', $sqlRunning ? 'SQL running' : 'SQL stopped or errored', '#sv-repl-status'),
            'lag' => self::light($lag !== null && $lag <= $lagSla, 'lag', $lag === null ? 'Lag unknown' : 'Lag ' . $lag . 's / SLA ' . $lagSla . 's', '#slave-graphs-container'),
            'gtid' => self::light($errantSet === '', 'gtid', $errantSet === '' ? 'No errant GTID cached' : 'Errant GTID set present', '#sv-gtid-drift'),
            'semi_sync' => self::light((bool)($semiSync['healthy'] ?? true), 'semi-sync', (string)($semiSync['message'] ?? ''), '#sv-semisync-sla'),
            'ssl' => self::light((bool)($ssl['healthy'] ?? false), 'ssl', (string)($ssl['message'] ?? ''), '#sv-repl-user-ssl'),
            'purge' => self::light(self::truthy($slaveRow['Relay_Log_Purge'] ?? $context['relay_log_purge'] ?? 'ON'), 'purge', 'relay_log_purge check', '#sv-repl-status'),
            'filters' => self::light(empty($filters['rows']), 'filters', empty($filters['rows']) ? 'No active replication filters' : 'Replication filters active', '#sv-repl-filters'),
            'workers' => self::light(empty($stuck['stuck']), 'workers', (string)($stuck['message'] ?? ''), '#sv-stuck-sql'),
            'heartbeat' => self::light((bool)($heartbeat['healthy'] ?? true), 'heartbeat', (string)($heartbeat['message'] ?? ''), '#sv-heartbeat'),
        ];

        $score = 0;
        foreach ($lights as $key => $light) {
            if ($light['ok']) {
                $score += self::WEIGHTS[$key];
            }
        }

        return self::withStatus($score, $lights, false);
    }

    /**
     * @return array{score:int,status:string,lights:array<string,array{ok:bool,weight:int,label:string,reason:string,anchor:string}>,stale:bool}
     */
    private static function withStatus(int $score, array $lights, bool $stale): array
    {
        $coreCritical = empty($lights['io']['ok']) || empty($lights['sql']['ok']) || empty($lights['gtid']['ok']);
        $status = $stale ? 'stale' : ($coreCritical ? 'critical' : ($score >= 90 ? 'healthy' : ($score >= 60 ? 'risky' : 'critical')));

        return [
            'score' => $score,
            'status' => $status,
            'lights' => $lights,
            'stale' => $stale,
        ];
    }

    /**
     * @return array<string,array{ok:bool,weight:int,label:string,reason:string,anchor:string}>
     */
    private static function lightsAll(bool $ok, string $reason): array
    {
        $lights = [];
        foreach (self::WEIGHTS as $key => $weight) {
            $lights[$key] = self::light($ok, str_replace('_', '-', $key), $reason, '#');
        }

        return $lights;
    }

    /**
     * @return array{ok:bool,weight:int,label:string,reason:string,anchor:string}
     */
    private static function light(bool $ok, string $label, string $reason, string $anchor): array
    {
        $key = str_replace('-', '_', $label);

        return [
            'ok' => $ok,
            'weight' => self::WEIGHTS[$key] ?? 0,
            'label' => $label,
            'reason' => $reason,
            'anchor' => $anchor,
        ];
    }

    private static function lagSeconds(array $row): ?int
    {
        foreach (['Seconds_Behind_Source', 'Seconds_Behind_Master', 'seconds_behind_source', 'seconds_behind_master'] as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== 'NULL' && $row[$key] !== '') {
                return (int)$row[$key];
            }
        }

        return null;
    }

    private static function truthy(mixed $value): bool
    {
        return in_array(strtoupper((string)$value), ['1', 'ON', 'YES', 'TRUE'], true);
    }
}
