<?php

declare(strict_types=1);

namespace App\Library;

final class ReplicationRetentionForecast
{
    /**
     * @return array{configured_days:float,retained_bytes:int,generation_rate_bytes_per_day:int,disk_free_bytes:int,eta_full_days:float|null,status:string,message:string}
     */
    public static function forecast(array $data): array
    {
        $retainedBytes = max(0, (int)($data['retained_bytes'] ?? $data['binlog_total_size'] ?? 0));
        $rate = max(0, (int)($data['generation_rate_bytes_per_day'] ?? 0));
        $diskFree = max(0, (int)($data['disk_free_bytes'] ?? 0));
        $configuredDays = (float)($data['configured_days'] ?? $data['expire_logs_days'] ?? 0);
        if ($configuredDays <= 0 && isset($data['binlog_expire_logs_seconds'])) {
            $configuredDays = ((float)$data['binlog_expire_logs_seconds']) / 86400.0;
        }

        $eta = $rate > 0 ? round($diskFree / $rate, 1) : null;
        $status = 'unknown';
        if ($rate > 0 && $eta !== null) {
            $status = $eta < 2.0 ? 'critical' : ($eta < 7.0 ? 'warning' : 'ok');
        }

        return [
            'configured_days' => round($configuredDays, 2),
            'retained_bytes' => $retainedBytes,
            'generation_rate_bytes_per_day' => $rate,
            'disk_free_bytes' => $diskFree,
            'eta_full_days' => $eta,
            'status' => $status,
            'message' => $status === 'unknown'
                ? 'Not enough data to forecast retention.'
                : 'Forecast based on current binlog generation rate.',
        ];
    }
}
