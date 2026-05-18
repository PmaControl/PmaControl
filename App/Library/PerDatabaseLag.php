<?php

declare(strict_types=1);

namespace App\Library;

final class PerDatabaseLag
{
    /**
     * @param list<array<string,mixed>> $workerRows
     * @return list<array{worker:string,database:string,lag_ms:int,status:string}>
     */
    public static function fromWorkerRows(array $workerRows, ?int $now = null): array
    {
        $now ??= time();
        $out = [];
        foreach ($workerRows as $row) {
            $worker = (string)($row['WORKER_ID'] ?? $row['worker_id'] ?? $row['THREAD_ID'] ?? '');
            $db = (string)($row['APPLYING_TRANSACTION_SCHEMA'] ?? $row['database'] ?? $row['db_name'] ?? '');
            if ($db === '') {
                $transaction = (string)($row['APPLYING_TRANSACTION'] ?? $row['applying_transaction'] ?? '');
                if (preg_match('/`([^`]+)`\./', $transaction, $m)) {
                    $db = $m[1];
                }
            }
            $last = (string)($row['LAST_APPLIED_TRANSACTION_END_APPLY_TIMESTAMP'] ?? $row['last_applied_at'] ?? '');
            $ts = $last !== '' ? strtotime($last) : false;
            $lagMs = $ts === false ? 0 : max(0, ($now - $ts) * 1000);
            $out[] = [
                'worker' => $worker !== '' ? $worker : (string)count($out),
                'database' => $db !== '' ? $db : '(unknown)',
                'lag_ms' => $lagMs,
                'status' => $lagMs > 30000 ? 'warning' : 'ok',
            ];
        }

        return $out;
    }
}
