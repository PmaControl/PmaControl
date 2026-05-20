<?php

declare(strict_types=1);

namespace App\Library;

/**
 * Issue #1196 — surface binlog group-commit tuning on /slave/show
 * with master + slave shown side by side.
 *
 * Two semantic concepts, each backed by a family-specific variable:
 *
 *   "Count threshold"  — wait until N transactions are ready to commit
 *     - MariaDB:        `binlog_commit_wait_count`
 *     - MySQL/Percona:  `binlog_group_commit_sync_no_delay_count`
 *
 *   "Max wait (μs)"    — cap on how long to wait for grouping
 *     - MariaDB:        `binlog_commit_wait_usec`
 *     - MySQL/Percona:  `binlog_group_commit_sync_delay`
 *
 * Output rows the view can render directly, with severity badges and
 * tooltips. Drift between slave and master is surfaced as a warning
 * because the writer batches according to the master's settings;
 * silent drift means the replica's apply pace doesn't match the
 * source's commit pace.
 */
final class BinlogGroupCommit
{
    public const LEVEL_OK      = 'ok';
    public const LEVEL_WARN    = 'warn';
    public const LEVEL_INFO    = 'info';
    public const LEVEL_UNKNOWN = 'unknown';

    /**
     * Variable keys to fetch from the time-series for ONE server. We
     * pull both family flavours so the helper can pick whichever the
     * server happens to expose (Extraction returns null for the
     * absent ones).
     *
     * @return list<string>
     */
    public static function variableKeys(): array
    {
        return [
            'binlog_commit_wait_count',                 // MariaDB
            'binlog_commit_wait_usec',                  // MariaDB
            'binlog_group_commit_sync_no_delay_count',  // MySQL/Percona
            'binlog_group_commit_sync_delay',           // MySQL/Percona
        ];
    }

    /**
     * Build the per-row rendering data.
     *
     * @param array<string,scalar|null> $slaveValues  raw `[name => value]` for the slave
     * @param array<string,scalar|null> $masterValues raw `[name => value]` for the master ([] if unknown)
     * @param string $slaveFamily  'mariadb' | 'mysql' | 'unknown'
     * @param string $masterFamily 'mariadb' | 'mysql' | 'unknown'
     * @return list<array{
     *   label: string,
     *   tooltip: string,
     *   slave:  array{var:string, value:string, level:string, label:string},
     *   master: array{var:string, value:string, level:string, label:string},
     *   drift:  bool,
     *   drift_reason: string,
     * }>
     */
    public static function rows(
        array $slaveValues,
        array $masterValues,
        string $slaveFamily,
        string $masterFamily
    ): array {
        return [
            self::countRow($slaveValues, $masterValues, $slaveFamily, $masterFamily),
            self::usecRow($slaveValues, $masterValues, $slaveFamily, $masterFamily),
        ];
    }

    private static function countRow(
        array $slave,
        array $master,
        string $slaveFamily,
        string $masterFamily
    ): array {
        $tooltip = 'Group-commit count threshold: how many transactions the binlog '
                 . 'thread waits to accumulate before fsync()ing as a single batch. '
                 . '0 = no batching (max fsync rate, lowest throughput on write-heavy '
                 . 'workloads). 1-100 = healthy batching. >100 = aggressive batching, '
                 . 'risks visible commit latency on low-traffic periods. '
                 . 'MariaDB default: 20. MySQL default: 0.';

        $slaveCell  = self::cellForCount($slave, $slaveFamily);
        $masterCell = self::cellForCount($master, $masterFamily);
        [$drift, $reason] = self::countDrift($slaveCell, $masterCell, $slaveFamily, $masterFamily);

        return [
            'label'        => 'Count threshold',
            'tooltip'      => $tooltip,
            'slave'        => $slaveCell,
            'master'       => $masterCell,
            'drift'        => $drift,
            'drift_reason' => $reason,
        ];
    }

    private static function usecRow(
        array $slave,
        array $master,
        string $slaveFamily,
        string $masterFamily
    ): array {
        $tooltip = 'Group-commit max wait: upper bound (microseconds) the binlog '
                 . 'thread waits for more transactions to join a batch before fsync. '
                 . '0 = no wait (max throughput, no batching). 1-50 000 (50 ms) = '
                 . 'healthy. >50 000 = excessive, app commits will feel laggy. '
                 . 'MariaDB default: 0 (waits only for the count). MySQL default: 0.';

        $slaveCell  = self::cellForUsec($slave, $slaveFamily);
        $masterCell = self::cellForUsec($master, $masterFamily);
        [$drift, $reason] = self::usecDrift($slaveCell, $masterCell, $slaveFamily, $masterFamily);

        return [
            'label'        => 'Max wait (μs)',
            'tooltip'      => $tooltip,
            'slave'        => $slaveCell,
            'master'       => $masterCell,
            'drift'        => $drift,
            'drift_reason' => $reason,
        ];
    }

    /**
     * @return array{var:string, value:string, level:string, label:string}
     */
    private static function cellForCount(array $values, string $family): array
    {
        if ($family === 'mariadb') {
            $varName = 'binlog_commit_wait_count';
        } elseif ($family === 'mysql') {
            $varName = 'binlog_group_commit_sync_no_delay_count';
        } else {
            return self::cellUnknown('binlog_commit_wait_count');
        }

        $raw = self::lookup($values, $varName);
        if ($raw === null) {
            return self::cellUnknown($varName);
        }
        $intVal = (int) $raw;
        $level  = self::countLevel($intVal);

        return [
            'var'   => $varName,
            'value' => (string) $intVal,
            'level' => $level,
            'label' => (string) $intVal,
        ];
    }

    /**
     * @return array{var:string, value:string, level:string, label:string}
     */
    private static function cellForUsec(array $values, string $family): array
    {
        if ($family === 'mariadb') {
            $varName = 'binlog_commit_wait_usec';
        } elseif ($family === 'mysql') {
            $varName = 'binlog_group_commit_sync_delay';
        } else {
            return self::cellUnknown('binlog_commit_wait_usec');
        }

        $raw = self::lookup($values, $varName);
        if ($raw === null) {
            return self::cellUnknown($varName);
        }
        $intVal = (int) $raw;
        $level  = self::usecLevel($intVal);

        return [
            'var'   => $varName,
            'value' => (string) $intVal,
            'level' => $level,
            'label' => (string) $intVal,
        ];
    }

    private static function cellUnknown(string $varName): array
    {
        return [
            'var'   => $varName,
            'value' => '',
            'level' => self::LEVEL_UNKNOWN,
            'label' => 'n/a',
        ];
    }

    private static function lookup(array $values, string $name): ?string
    {
        if (!array_key_exists($name, $values)) {
            return null;
        }
        $v = $values[$name];
        if ($v === null || $v === '') {
            return null;
        }
        return (string) $v;
    }

    private static function countLevel(int $v): string
    {
        if ($v === 0)  return self::LEVEL_INFO;   // no batching
        if ($v <= 100) return self::LEVEL_OK;
        return self::LEVEL_WARN;                  // aggressive
    }

    private static function usecLevel(int $v): string
    {
        if ($v === 0)        return self::LEVEL_INFO;   // no wait
        if ($v <= 50_000)    return self::LEVEL_OK;     // ≤ 50 ms
        return self::LEVEL_WARN;                        // excessive wait
    }

    /**
     * Drift between slave and master count thresholds. We flag when:
     *  - one side is in the unknown bucket but not both, OR
     *  - the families differ (already a warning all by itself), OR
     *  - the values differ by a factor of 5+ (5x is the practical
     *    threshold beyond which the replica apply pace meaningfully
     *    diverges from the source commit pace).
     *
     * @return array{0:bool,1:string}
     */
    private static function countDrift(array $slave, array $master, string $slaveFamily, string $masterFamily): array
    {
        return self::driftPair($slave, $master, $slaveFamily, $masterFamily, 5);
    }

    private static function usecDrift(array $slave, array $master, string $slaveFamily, string $masterFamily): array
    {
        return self::driftPair($slave, $master, $slaveFamily, $masterFamily, 5);
    }

    /**
     * @return array{0:bool,1:string}
     */
    private static function driftPair(
        array $slave,
        array $master,
        string $slaveFamily,
        string $masterFamily,
        int $factor
    ): array {
        $slaveKnown  = $slave['level']  !== self::LEVEL_UNKNOWN;
        $masterKnown = $master['level'] !== self::LEVEL_UNKNOWN;
        if (!$slaveKnown || !$masterKnown) {
            // Don't fire a drift warning when half the data is missing
            // — the operator should not be told about a problem we
            // cannot prove exists.
            return [false, ''];
        }

        if ($slaveFamily !== $masterFamily) {
            return [true, sprintf(
                'Slave (%s) and master (%s) live in different MySQL families — group-commit '
                . 'tuning semantics differ between MariaDB and MySQL/Percona. Compare the '
                . 'effective behaviour, not just the numbers.',
                ucfirst($slaveFamily),
                ucfirst($masterFamily)
            )];
        }

        $sv = (int) $slave['value'];
        $mv = (int) $master['value'];
        if ($sv === 0 && $mv === 0) {
            return [false, ''];
        }
        if ($sv === 0 || $mv === 0) {
            return [true, 'One side has batching disabled while the other does not — '
                . 'replica apply pace will not match the source commit pace.'];
        }
        $ratio = max($sv, $mv) / max(1, min($sv, $mv));
        if ($ratio >= $factor) {
            return [true, sprintf(
                'Slave / master mis-aligned (×%.1f): replica apply pace will not match '
                . 'the source commit pace. Align the variables on both sides for predictable '
                . 'replication latency.',
                $ratio
            )];
        }

        return [false, ''];
    }
}
