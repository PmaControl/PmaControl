<?php

declare(strict_types=1);

namespace App\Library;

/**
 * Pre-flight evaluator for the "reload from master" operator action.
 *
 * Given the slave + master `mysql_server` rows, the current `SHOW REPLICA
 * STATUS` row, the {@see ReplicationSourceCoverage} verdict and a few SSH /
 * disk-space probes performed by the controller, returns a structured list
 * of conditions consumed by `App/view/Slave/reloadFromMaster.view.php`.
 *
 * The class is intentionally PURE (no DB, no SSH, no globals) so the
 * controller can mock every input and the test suite can cover every gate
 * deterministically.
 *
 * Two procedures are offered:
 *   - physical (xtrabackup / mariadb-backup): fast, hot, requires major-
 *     version match;
 *   - logical (mysqldump / mariadb-dump --master-data): always compatible
 *     across major versions and server types (MySQL ↔ MariaDB).
 *
 * Each pre-flight condition has a status:
 *   - 'ok'   — green check, no action required;
 *   - 'fail' — hard blocker; disables BOTH procedures (or only physical
 *              when the failure is version-specific);
 *   - 'warn' — operator must acknowledge before launching (the
 *              source-coverage `at_risk` case is the canonical example —
 *              sometimes a reload IS the fix for a purged position).
 *
 * @category PmaControl
 * @package  App
 * @subpackage Library
 * @license  GPL-3.0
 */
final class ReloadFromMaster
{
    public const STATUS_OK   = 'ok';
    public const STATUS_FAIL = 'fail';
    public const STATUS_WARN = 'warn';

    /**
     * Multiplier applied to `replica_lag_sla_seconds` to derive the
     * "really late" threshold. The SLA itself is a soft warning level —
     * a reload only makes sense when the replica is an order of magnitude
     * behind it.
     */
    public const LAG_BLOCKER_MULTIPLIER = 10;

    /** Free-disk-space safety margin over the master datadir size. */
    public const DISK_HEADROOM_RATIO = 1.2;

    /** Threshold beyond which `Slave_SQL_Running != Yes` blocks reload. */
    public const SQL_STOPPED_BLOCKER_SECONDS = 300;

    /**
     * Evaluate every pre-flight gate.
     *
     * @param array<string,mixed>       $slaveRow       Row from `mysql_server` for the replica being reloaded.
     * @param array<string,mixed>       $masterRow     Row from `mysql_server` for the upstream master.
     * @param array<string,mixed>       $slaveStatus   Latest `SHOW REPLICA STATUS` row (may be empty).
     * @param ?array<string,mixed>      $sourceCoverage Output of {@see ReplicationSourceCoverage::evaluate()}, or null.
     * @param array<string,mixed>       $diskStats      ['slave_free_bytes' => ?int, 'master_datadir_bytes' => ?int]
     * @param array<string,mixed>       $sshProbe       ['ok' => bool, 'command' => string, 'stderr' => string]
     * @param array<string,mixed>       $versions       ['slave' => '10.11.5', 'master' => '10.11.5', 'slave_type' => 'mariadb', 'master_type' => 'mariadb']
     *
     * @return array{conditions:list<array{key:string,status:string,message:string,detail?:string}>,physical_eligible:bool,logical_eligible:bool}
     */
    public static function evaluatePreflight(
        array $slaveRow,
        array $masterRow,
        array $slaveStatus,
        ?array $sourceCoverage,
        array $diskStats,
        array $sshProbe,
        array $versions
    ): array {
        $conditions = [];

        // Gate 1 — operator-flagged HS. Without this flag, BOTH procedures
        // are disabled: we never wipe a datadir on a server still serving
        // traffic.
        $isHs = (int) ($slaveRow['is_out_of_service'] ?? 0) === 1;
        $conditions[] = [
            'key'     => 'out_of_service',
            'status'  => $isHs ? self::STATUS_OK : self::STATUS_FAIL,
            'message' => $isHs
                ? 'Slave is flagged out-of-service (is_out_of_service=1)'
                : 'Slave is NOT flagged out-of-service — toggle mysql_server.is_out_of_service to 1 first',
        ];

        // Gate 2 — major-version match (physical only).
        $slaveMajor  = self::majorVersion((string) ($versions['slave']  ?? ''));
        $masterMajor = self::majorVersion((string) ($versions['master'] ?? ''));
        $slaveType   = strtolower((string) ($versions['slave_type']  ?? ''));
        $masterType  = strtolower((string) ($versions['master_type'] ?? ''));
        $majorMatch = $slaveMajor !== null && $masterMajor !== null
                   && $slaveMajor === $masterMajor
                   && ($slaveType === '' || $masterType === '' || $slaveType === $masterType);
        $conditions[] = [
            'key'     => 'version_match',
            'status'  => $majorMatch ? self::STATUS_OK : self::STATUS_FAIL,
            'message' => $majorMatch
                ? sprintf('Major version match: %s %s ↔ %s %s', $masterType ?: 'mysql', $masterMajor ?? '?', $slaveType ?: 'mysql', $slaveMajor ?? '?')
                : sprintf('Major versions differ — physical disabled (master=%s %s, slave=%s %s)', $masterType ?: '?', $masterMajor ?? '?', $slaveType ?: '?', $slaveMajor ?? '?'),
        ];

        // Gate 3 — replication is stopped or far behind.
        $stoppedFor    = self::secondsSqlStopped($slaveStatus);
        $sqlRunning    = self::slaveSqlRunning($slaveStatus);
        $ioRaw         = strtolower((string) ($slaveStatus['Slave_IO_Running'] ?? $slaveStatus['Replica_IO_Running'] ?? ''));
        // `Connecting` means the IO thread cannot reach the master — it is
        // NOT a healthy state. Without this check, a slave whose master is
        // unreachable looks "healthy within SLA" because `seconds_behind`
        // is NULL and SQL=Yes (it just keeps applying what's already in
        // the relay log). Reload is exactly the right remediation here.
        $ioConnecting  = $ioRaw === 'connecting' || $ioRaw === 'no';
        $secondsBehind = self::secondsBehindSource($slaveStatus);
        $sla = (int) ($slaveRow['replica_lag_sla_seconds'] ?? 30);
        $reallyLate = $sla * self::LAG_BLOCKER_MULTIPLIER;
        $lagBlocker = false;
        $lagMsg = 'Replication is running within SLA';
        if ($ioConnecting) {
            $lagBlocker = true;
            $lagMsg = sprintf(
                'Slave_IO_Running = %s — master is unreachable (%s). Reload required.',
                $ioRaw === '' ? 'unknown' : $ioRaw,
                (string) ($slaveStatus['Last_IO_Error'] ?? 'no error message')
            );
        } elseif (!$sqlRunning && $stoppedFor !== null && $stoppedFor >= self::SQL_STOPPED_BLOCKER_SECONDS) {
            $lagBlocker = true;
            $lagMsg = sprintf('Slave_SQL_Running != Yes for ≥ %ds — reload required', self::SQL_STOPPED_BLOCKER_SECONDS);
        } elseif ($secondsBehind !== null && $secondsBehind > $reallyLate) {
            $lagBlocker = true;
            $lagMsg = sprintf('Replica is %ds behind master — threshold %ds (SLA %ds × %d)', $secondsBehind, $reallyLate, $sla, self::LAG_BLOCKER_MULTIPLIER);
        } elseif (!$sqlRunning) {
            // Stopped, but for less than the blocker window — surface as warn,
            // operator may want to attempt a regular start before reloading.
            $lagBlocker = false;
            $lagMsg = 'Slave_SQL_Running != Yes — consider START REPLICA before reload';
        }
        // A "stale" replica is a PREREQUISITE for reload: if everything is
        // healthy there is nothing to fix. Map the gate so that "needs
        // reload" is OK and "healthy" is a FAIL (we refuse to wipe a
        // healthy datadir for no reason).
        $needsReload = $lagBlocker;
        $conditions[] = [
            'key'     => 'replica_stale',
            'status'  => $needsReload ? self::STATUS_OK : self::STATUS_FAIL,
            'message' => $needsReload
                ? $lagMsg
                : 'Replica is healthy — there is nothing to reload from master ('.$lagMsg.')',
        ];

        // Gate 4 — SSH reachability slave (the PmaControl host acts as
        // proxy: if we can ssh to the slave with BatchMode, the master can
        // too as long as keys are deployed there. The actual streaming
        // happens master → slave at runtime).
        $sshOk = (bool) ($sshProbe['ok'] ?? false);
        $conditions[] = [
            'key'     => 'ssh_reachable',
            'status'  => $sshOk ? self::STATUS_OK : self::STATUS_FAIL,
            'message' => $sshOk
                ? 'SSH probe to slave succeeded: '.((string) ($sshProbe['command'] ?? ''))
                : 'SSH probe to slave FAILED: '.((string) ($sshProbe['command'] ?? '')).' — '.((string) ($sshProbe['stderr'] ?? '')),
            'detail'  => (string) ($sshProbe['command'] ?? ''),
        ];

        // Gate 5 — slave has free disk space ≥ master datadir × ratio.
        $masterBytes = $diskStats['master_datadir_bytes'] ?? null;
        $slaveFree   = $diskStats['slave_free_bytes']      ?? null;
        if ($masterBytes === null || $slaveFree === null) {
            $conditions[] = [
                'key'     => 'disk_space',
                'status'  => self::STATUS_WARN,
                'message' => 'Disk-space probe inconclusive — verify manually before reload',
            ];
        } else {
            $needed = (int) ceil((int) $masterBytes * self::DISK_HEADROOM_RATIO);
            $ok = (int) $slaveFree >= $needed;
            $conditions[] = [
                'key'     => 'disk_space',
                'status'  => $ok ? self::STATUS_OK : self::STATUS_FAIL,
                'message' => sprintf(
                    'Slave free: %s | needed: %s (master datadir %s × %.2f)',
                    self::humanBytes((int) $slaveFree),
                    self::humanBytes($needed),
                    self::humanBytes((int) $masterBytes),
                    self::DISK_HEADROOM_RATIO
                ),
            ];
        }

        // Gate 6 — source-coverage at_risk warning (NOT a blocker). The
        // operator must tick "acknowledge" client-side, but the eligibility
        // booleans below stay TRUE.
        $coverageStatus = (string) ($sourceCoverage['status'] ?? '');
        if ($coverageStatus === 'at_risk') {
            $conditions[] = [
                'key'     => 'source_coverage',
                'status'  => self::STATUS_WARN,
                'message' => 'ReplicationSourceCoverage reports at_risk — acknowledge to proceed: '.((string) ($sourceCoverage['reason'] ?? '')),
            ];
        } elseif ($coverageStatus === 'safe') {
            $conditions[] = [
                'key'     => 'source_coverage',
                'status'  => self::STATUS_OK,
                'message' => 'Replication position is still covered by master binlogs',
            ];
        } else {
            $conditions[] = [
                'key'     => 'source_coverage',
                'status'  => self::STATUS_WARN,
                'message' => 'Source coverage unknown — proceeding will start from xtrabackup_binlog_info on reload',
            ];
        }

        return [
            'conditions'        => $conditions,
            'physical_eligible' => self::allOkOrWarn($conditions, /* allowVersionFail */ false),
            'logical_eligible'  => self::allOkOrWarn($conditions, /* allowVersionFail */ true),
        ];
    }

    /**
     * Logical (mariadb-dump --master-data) tolerates major-version drift.
     * Every other ✗ remains a blocker.
     *
     * @param list<array{key:string,status:string,message:string}> $conditions
     */
    private static function allOkOrWarn(array $conditions, bool $allowVersionFail): bool
    {
        foreach ($conditions as $c) {
            if ($c['status'] !== self::STATUS_FAIL) {
                continue;
            }
            if ($allowVersionFail && $c['key'] === 'version_match') {
                continue;
            }
            return false;
        }
        return true;
    }

    /**
     * Pick the first non-empty "5.7" / "10.11" prefix out of a SHOW
     * VARIABLES like 'version' string. Returns null for empty / garbled
     * inputs.
     */
    public static function majorVersion(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }
        if (!preg_match('/(\d+)\.(\d+)/', $raw, $m)) {
            return null;
        }
        return $m[1].'.'.$m[2];
    }

    /**
     * Read the canonical Slave_SQL_Running / Replica_SQL_Running flag.
     */
    private static function slaveSqlRunning(array $status): bool
    {
        $raw = $status['Slave_SQL_Running'] ?? $status['Replica_SQL_Running'] ?? '';
        return strtolower((string) $raw) === 'yes';
    }

    /**
     * Best-effort number of seconds Slave_SQL_Running has been != Yes,
     * derived from `last_sql_error_timestamp` if present (PmaControl
     * synthetic field) — falls back to null so the caller treats the
     * gate as "stopped, duration unknown".
     */
    private static function secondsSqlStopped(array $status): ?int
    {
        $ts = $status['last_sql_error_timestamp'] ?? null;
        if (!$ts) {
            return null;
        }
        $unix = is_numeric($ts) ? (int) $ts : strtotime((string) $ts);
        if (!$unix) {
            return null;
        }
        return max(0, time() - $unix);
    }

    /**
     * `Seconds_Behind_Source` (MySQL ≥ 8.0.22) or `Seconds_Behind_Master`.
     */
    private static function secondsBehindSource(array $status): ?int
    {
        foreach (['Seconds_Behind_Source', 'Seconds_Behind_Master'] as $key) {
            if (!array_key_exists($key, $status)) {
                continue;
            }
            $v = $status[$key];
            if ($v === null || $v === '') {
                continue;
            }
            return (int) $v;
        }
        return null;
    }

    /** Render bytes as 'X.X TiB' / 'Y.Y GiB' / … for the view. */
    public static function humanBytes(int $bytes): string
    {
        $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB'];
        $i = 0;
        $v = (float) $bytes;
        while ($v >= 1024 && $i < count($units) - 1) {
            $v /= 1024;
            $i++;
        }
        return sprintf('%.2f %s', $v, $units[$i]);
    }
}
