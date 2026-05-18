<?php

declare(strict_types=1);

namespace App\Library;

/**
 * #1280 follow-up — source-side coverage check for the replica's
 * current position. Answers a single critical question on
 * `/slave/show/<id>/`:
 *
 *   « if this replica loses its relay logs right now, can it resume
 *     replication by re-fetching from the master, or is the position
 *     it sits on already PURGED on the master? »
 *
 * The check is essential because `Slave_IO_Running = Yes` /
 * `Slave_SQL_Running = Yes` is a fully-misleading signal in this
 * scenario: replication looks healthy, but a transient relay-log
 * loss (crash, disk wipe, ALTER on the relay log dir) becomes
 * unrecoverable — the operator has to re-clone the replica from
 * scratch.
 *
 * Two replication modes, two checks:
 *
 *   - File+position mode: the file referenced by `Master_Log_File` /
 *     `Relay_Master_Log_File` must still appear in the master's
 *     `SHOW BINARY LOGS` list. If it has been purged the replica is
 *     vulnerable.
 *   - GTID mode (auto-position): any GTID in the replica's
 *     `Retrieved_Gtid_Set` (the events it has already pulled, kept
 *     in the relay log) must NOT intersect with the master's
 *     `gtid_purged` set. If it does, those transactions have no
 *     binlog backing on the source anymore.
 *
 * In both cases the lib returns a `safe` boolean + a `reason`
 * string. The caller renders a warning banner and (separately)
 * gates the GTID activate/deactivate toggle when `safe === false`
 * AND replication is currently running — toggling at that point
 * would re-resolve the position and immediately error out.
 *
 * See: docs/replication_source_coverage.md (companion doc).
 */
final class ReplicationSourceCoverage
{
    public const MODE_FILE = 'file';
    public const MODE_GTID = 'gtid';
    public const MODE_NONE = 'none';

    public const STATUS_SAFE     = 'safe';
    public const STATUS_AT_RISK  = 'at_risk';
    public const STATUS_UNKNOWN  = 'unknown';

    /**
     * @param array<string,mixed> $replicaStatus  SHOW REPLICA STATUS row
     * @param list<string>        $masterBinlogFiles Files master still has (oldest → newest)
     * @param string|null         $masterGtidPurged @@global.gtid_purged, NULL when unknown
     *
     * @return array{
     *     status:string,
     *     mode:string,
     *     safe:bool,
     *     reason:string,
     *     replica_read_file:?string,
     *     replica_exec_file:?string,
     *     master_oldest_file:?string,
     *     master_newest_file:?string,
     *     replica_gtid_retrieved:?string,
     *     master_gtid_purged:?string,
     *     overlap_with_purged:?string
     * }
     */
    public static function evaluate(
        array $replicaStatus,
        array $masterBinlogFiles,
        ?string $masterGtidPurged
    ): array {
        $usingGtid = self::isUsingGtid($replicaStatus);
        $mode      = $usingGtid ? self::MODE_GTID : self::MODE_FILE;

        if ($usingGtid) {
            return self::evaluateGtid($replicaStatus, $masterGtidPurged);
        }
        return self::evaluateFile($replicaStatus, $masterBinlogFiles);
    }

    private static function isUsingGtid(array $row): bool
    {
        $usingGtidRaw = $row['Using_Gtid'] ?? null;
        if ($usingGtidRaw !== null && $usingGtidRaw !== '') {
            return $usingGtidRaw !== 'No';
        }
        $autoPosition = $row['Auto_Position'] ?? null;
        if ($autoPosition !== null) {
            return (string) $autoPosition === '1';
        }
        return false;
    }

    /**
     * @param array<string,mixed> $row
     * @param list<string>        $files
     */
    private static function evaluateFile(array $row, array $files): array
    {
        $read = (string) ($row['Master_Log_File']      ?? $row['Source_Log_File']      ?? '');
        $exec = (string) ($row['Relay_Master_Log_File'] ?? $row['Relay_Source_Log_File'] ?? '');
        $base = [
            'mode'                   => self::MODE_FILE,
            'replica_read_file'      => $read !== '' ? $read : null,
            'replica_exec_file'      => $exec !== '' ? $exec : null,
            'master_oldest_file'     => $files !== [] ? $files[0] : null,
            'master_newest_file'     => $files !== [] ? $files[count($files) - 1] : null,
            'replica_gtid_retrieved' => null,
            'master_gtid_purged'     => null,
            'overlap_with_purged'    => null,
        ];

        if ($read === '' || $files === []) {
            return $base + [
                'status' => self::STATUS_UNKNOWN,
                'safe'   => false,
                'reason' => $read === ''
                    ? 'replica has no Master_Log_File (replication never connected?)'
                    : 'master binlog list is empty or unavailable',
            ];
        }

        $missingFiles = [];
        if (!in_array($read, $files, true)) {
            $missingFiles[] = $read . ' (Read_Master_Log_Pos)';
        }
        if ($exec !== '' && $exec !== $read && !in_array($exec, $files, true)) {
            $missingFiles[] = $exec . ' (Exec_Master_Log_Pos)';
        }

        if ($missingFiles !== []) {
            return $base + [
                'status' => self::STATUS_AT_RISK,
                'safe'   => false,
                'reason' => 'master has PURGED the binlog file(s) the replica is reading: '
                          . implode(', ', $missingFiles)
                          . '. If this replica loses its relay logs, it cannot reconnect.',
            ];
        }
        return $base + [
            'status' => self::STATUS_SAFE,
            'safe'   => true,
            'reason' => 'replica position is still present in the master binlog list',
        ];
    }

    /**
     * @param array<string,mixed> $row
     */
    private static function evaluateGtid(array $row, ?string $masterGtidPurged): array
    {
        $retrieved = (string) ($row['Retrieved_Gtid_Set'] ?? '');
        $base = [
            'mode'                   => self::MODE_GTID,
            'replica_read_file'      => null,
            'replica_exec_file'      => null,
            'master_oldest_file'     => null,
            'master_newest_file'     => null,
            'replica_gtid_retrieved' => $retrieved !== '' ? $retrieved : null,
            'master_gtid_purged'     => $masterGtidPurged !== null && $masterGtidPurged !== '' ? $masterGtidPurged : null,
            'overlap_with_purged'    => null,
        ];

        if ($retrieved === '' || $masterGtidPurged === null || $masterGtidPurged === '') {
            return $base + [
                'status' => self::STATUS_UNKNOWN,
                'safe'   => false,
                'reason' => $retrieved === ''
                    ? 'replica has no Retrieved_Gtid_Set yet (recently started?)'
                    : 'master @@gtid_purged unavailable — cannot verify coverage',
            ];
        }

        $retrievedSet = GtidSet::parse($retrieved);
        $purgedSet    = GtidSet::parse($masterGtidPurged);
        $overlap      = $retrievedSet->intersect($purgedSet);

        if (! $overlap->isEmpty()) {
            $base['overlap_with_purged'] = (string) $overlap;
            return $base + [
                'status' => self::STATUS_AT_RISK,
                'safe'   => false,
                'reason' => 'master has PURGED GTIDs that the replica has already retrieved: '
                          . $overlap
                          . '. If this replica loses its relay logs, those transactions cannot be re-fetched.',
            ];
        }
        return $base + [
            'status' => self::STATUS_SAFE,
            'safe'   => true,
            'reason' => 'replica retrieved GTID set is fully covered by master binlogs',
        ];
    }
}
