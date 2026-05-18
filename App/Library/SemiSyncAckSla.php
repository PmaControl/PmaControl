<?php

declare(strict_types=1);

namespace App\Library;

final class SemiSyncAckSla
{
    /**
     * @return array{configured:bool,healthy:bool,ack_ratio:float,timeout_ratio:float,message:string}
     */
    public static function evaluate(array $status, float $maxTimeoutRatio = 0.005): array
    {
        $yes = self::intField($status, ['Rpl_semi_sync_master_yes_tx', 'rpl_semi_sync_master_yes_tx']);
        $no = self::intField($status, ['Rpl_semi_sync_master_no_tx', 'rpl_semi_sync_master_no_tx']);
        $enabled = strtoupper((string)($status['rpl_semi_sync_master_enabled'] ?? $status['Rpl_semi_sync_master_status'] ?? ''));

        if ($yes === null && $no === null && !in_array($enabled, ['ON', '1', 'YES'], true)) {
            return [
                'configured' => false,
                'healthy' => true,
                'ack_ratio' => 1.0,
                'timeout_ratio' => 0.0,
                'message' => 'Semi-sync not configured.',
            ];
        }

        $yes = $yes ?? 0;
        $no = $no ?? 0;
        $total = max(1, $yes + $no);
        $timeoutRatio = $no / $total;

        return [
            'configured' => true,
            'healthy' => $timeoutRatio <= $maxTimeoutRatio,
            'ack_ratio' => $yes / $total,
            'timeout_ratio' => $timeoutRatio,
            'message' => $timeoutRatio <= $maxTimeoutRatio
                ? 'Semi-sync ACK timeout ratio is within SLA.'
                : 'Semi-sync timed out too often and fell back to async.',
        ];
    }

    /**
     * @param list<string> $keys
     */
    private static function intField(array $row, array $keys): ?int
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== '' && $row[$key] !== null) {
                return (int)$row[$key];
            }
        }

        return null;
    }
}
