<?php

declare(strict_types=1);

namespace App\Library;

final class ReplicationStuckSqlDetector
{
    /**
     * @param list<array<string,mixed>> $historyRows Chronological rows with
     *        `date` and exec position fields.
     * @return array{stuck:bool,age_seconds:int,position:int|null,message:string,wait_state:string}
     */
    public static function detect(array $currentStatus, array $historyRows = [], int $thresholdSeconds = 30, ?int $now = null): array
    {
        $now ??= time();
        $execPos = self::intField($currentStatus, ['Exec_Master_Log_Pos', 'Exec_Source_Log_Pos']);
        $readPos = self::intField($currentStatus, ['Read_Master_Log_Pos', 'Read_Source_Log_Pos']);

        if ($execPos === null || $readPos === null || $readPos <= $execPos) {
            return self::result(false, 0, $execPos, 'SQL position is advancing or IO is not ahead.', '');
        }

        $oldestSame = null;
        foreach (array_reverse($historyRows) as $row) {
            $pos = self::intField($row, ['Exec_Master_Log_Pos', 'Exec_Source_Log_Pos', 'exec_master_log_pos', 'exec_source_log_pos', 'value']);
            if ($pos !== $execPos) {
                break;
            }
            $ts = strtotime((string)($row['date'] ?? $row['ts'] ?? ''));
            if ($ts !== false) {
                $oldestSame = $ts;
            }
        }

        if ($oldestSame === null) {
            return self::result(false, 0, $execPos, 'No stable-position history available.', '');
        }

        $age = max(0, $now - $oldestSame);
        if ($age < $thresholdSeconds) {
            return self::result(false, $age, $execPos, 'SQL position has not crossed the stuck threshold.', '');
        }

        $wait = trim((string)($currentStatus['Sql_Thread_Wait_State'] ?? $currentStatus['SQL_Thread_Wait_State'] ?? ''));

        return self::result(true, $age, $execPos, 'Same Exec_Master_Log_Pos while IO keeps advancing.', $wait);
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

    /**
     * @return array{stuck:bool,age_seconds:int,position:int|null,message:string,wait_state:string}
     */
    private static function result(bool $stuck, int $age, ?int $position, string $message, string $wait): array
    {
        return [
            'stuck' => $stuck,
            'age_seconds' => $age,
            'position' => $position,
            'message' => $message,
            'wait_state' => $wait,
        ];
    }
}
