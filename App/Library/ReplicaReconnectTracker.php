<?php

declare(strict_types=1);

namespace App\Library;

final class ReplicaReconnectTracker
{
    /**
     * @param list<array{date:string,value:string}> $ioErrorHistory
     * @return array{total:int,peak:int,peak_window_start:string,last_events:list<array{ts:string,message:string,kind:string}>,storm:bool}
     */
    public static function summarize(string $errorLog, array $ioErrorHistory = [], int $windowSeconds = 30): array
    {
        $events = self::parseErrorLog($errorLog);
        foreach (self::transitions($ioErrorHistory) as $event) {
            $events[] = $event;
        }
        usort($events, static fn(array $a, array $b): int => strcmp($a['ts'], $b['ts']));

        $peak = 0;
        $peakStart = '';
        foreach ($events as $i => $event) {
            $start = strtotime($event['ts']) ?: 0;
            $count = 0;
            for ($j = $i; $j < count($events); $j++) {
                $ts = strtotime($events[$j]['ts']) ?: 0;
                if ($ts - $start > $windowSeconds) {
                    break;
                }
                $count++;
            }
            if ($count > $peak) {
                $peak = $count;
                $peakStart = $event['ts'];
            }
        }

        return [
            'total' => count($events),
            'peak' => $peak,
            'peak_window_start' => $peakStart,
            'last_events' => array_slice(array_reverse($events), 0, 3),
            'storm' => $peak > 1,
        ];
    }

    /**
     * @return list<array{ts:string,message:string,kind:string}>
     */
    public static function parseErrorLog(string $errorLog): array
    {
        $events = [];
        foreach (preg_split('/\R/', $errorLog) ?: [] as $line) {
            if (!preg_match('/(Slave|Replica) I\/O thread|START (SLAVE|REPLICA)|connected to master|lost connection|server has gone away|connection timed out/i', $line)) {
                continue;
            }
            $ts = self::extractTimestamp($line);
            if ($ts === '') {
                continue;
            }
            $events[] = [
                'ts' => $ts,
                'message' => trim($line),
                'kind' => preg_match('/START (SLAVE|REPLICA)/i', $line) ? 'operator' : 'reconnect',
            ];
        }

        return $events;
    }

    /**
     * @param list<array{date:string,value:string}> $rows
     * @return list<array{ts:string,message:string,kind:string}>
     */
    private static function transitions(array $rows): array
    {
        $events = [];
        $previous = null;
        foreach ($rows as $row) {
            $value = trim((string)($row['value'] ?? ''));
            if ($value === '' || $value === $previous) {
                $previous = $value;
                continue;
            }
            $events[] = [
                'ts' => (string)($row['date'] ?? ''),
                'message' => $value,
                'kind' => 'io_error',
            ];
            $previous = $value;
        }

        return $events;
    }

    private static function extractTimestamp(string $line): string
    {
        if (preg_match('/(\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2})/', $line, $m)) {
            return str_replace('T', ' ', $m[1]);
        }
        if (preg_match('/^(\d{6})\s+(\d{1,2}:\d{2}:\d{2})/', $line, $m)) {
            $yy = substr($m[1], 0, 2);
            $mm = substr($m[1], 2, 2);
            $dd = substr($m[1], 4, 2);
            [$h, $i, $s] = explode(':', $m[2]);
            return sprintf('20%s-%s-%s %02d:%s:%s', $yy, $mm, $dd, (int)$h, $i, $s);
        }

        return '';
    }
}
