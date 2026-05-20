<?php

declare(strict_types=1);

namespace App\Library;

final class ReplicationHeartbeatCheck
{
    /**
     * @return array{status:string,healthy:bool,period_seconds:float,last_seen:string,age_seconds:int|null,message:string}
     */
    public static function evaluate(array $status, ?int $now = null): array
    {
        $now ??= time();
        $io = (string)($status['Slave_IO_Running'] ?? $status['Replica_IO_Running'] ?? '');
        $period = self::floatField($status, [
            'Master_Heartbeat_Period',
            'Source_Heartbeat_Period',
            'Heartbeat',
            'HEARTBEAT_INTERVAL',
            'master_heartbeat_period',
        ]);

        if ($io !== 'Yes') {
            return self::result('skipped', true, $period ?? 0.0, '', null, 'IO thread is not running.');
        }
        if ($period === null || $period <= 0.0) {
            return self::result('disabled', false, 0.0, '', null, 'Heartbeat disabled.');
        }

        $last = self::stringField($status, [
            'LAST_HEARTBEAT_TIMESTAMP',
            'Last_Heartbeat_Timestamp',
            'last_heartbeat_timestamp',
        ]);
        if ($last === '') {
            return self::result('unknown', false, $period, '', null, 'Last heartbeat timestamp is not available.');
        }

        $lastTs = strtotime($last);
        if ($lastTs === false) {
            return self::result('unknown', false, $period, $last, null, 'Last heartbeat timestamp is invalid.');
        }

        $age = max(0, $now - $lastTs);
        $healthy = $age <= (int)ceil($period * 2);

        return self::result(
            $healthy ? 'healthy' : 'stale',
            $healthy,
            $period,
            date('Y-m-d H:i:s', $lastTs),
            $age,
            $healthy ? 'Heartbeat arrived within 2x period.' : 'Heartbeat is stale; lag may be inaccurate.'
        );
    }

    /**
     * @param list<string> $keys
     */
    private static function floatField(array $row, array $keys): ?float
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== '' && $row[$key] !== null) {
                return (float)$row[$key];
            }
        }

        return null;
    }

    /**
     * @param list<string> $keys
     */
    private static function stringField(array $row, array $keys): string
    {
        foreach ($keys as $key) {
            if (isset($row[$key]) && trim((string)$row[$key]) !== '') {
                return trim((string)$row[$key]);
            }
        }

        return '';
    }

    /**
     * @return array{status:string,healthy:bool,period_seconds:float,last_seen:string,age_seconds:int|null,message:string}
     */
    private static function result(string $status, bool $healthy, float $period, string $last, ?int $age, string $message): array
    {
        return [
            'status' => $status,
            'healthy' => $healthy,
            'period_seconds' => $period,
            'last_seen' => $last,
            'age_seconds' => $age,
            'message' => $message,
        ];
    }
}
