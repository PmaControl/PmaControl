<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit\Checks\Hostgroup;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

/**
 * #907 — flags backends that ProxySQL routes as readers but whose
 * last-known `@@global.read_only` is OFF.
 *
 * Reader hostgroups must only carry `read_only=ON` backends; a
 * read_only=OFF server in there means writes will hit it and break
 * the writer/reader contract.
 *
 * Cross-source: needs `$ctx['pmacontrol_db']` to read the
 * `ts_value_general_text` cache for the `read_only` variable per
 * pmacontrol-monitored backend. When pmacontrol context is absent
 * or the lookup fails, returns an `info` finding ("cross-source
 * unavailable") rather than crashing.
 */
final class ReaderHostgroupReadOnlyOff implements AuditCheck
{
    public function id(): string
    {
        return 'hostgroup.reader-with-read-only-off';
    }

    public function category(): string
    {
        return 'hostgroup';
    }

    public function run(object $db, array $ctx = []): array
    {
        if (!method_exists($db, 'sql_query_silent')) {
            return [];
        }

        // Pull every (hostname, port) backend that lands in a reader
        // hostgroup. Reader hostgroups are declared by Galera +
        // Group Replication tables.
        $sql = "SELECT DISTINCT s.hostname, s.port "
             . "FROM runtime_mysql_servers s "
             . "WHERE s.hostgroup_id IN ("
             .   "SELECT reader_hostgroup FROM runtime_mysql_galera_hostgroups "
             .   "UNION "
             .   "SELECT hostgroup FROM runtime_mysql_group_replication_hostgroups"
             . ")";

        $res = $db->sql_query_silent($sql);
        if ($res === false) {
            return [];
        }

        $candidates = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $h = (string) ($row['hostname'] ?? '');
            $p = (int) ($row['port'] ?? 0);
            if ($h !== '' && $p > 0) {
                $candidates[] = [$h, $p];
            }
        }
        if (empty($candidates)) {
            return [];
        }

        $pmac = isset($ctx['pmacontrol_db']) && is_object($ctx['pmacontrol_db'])
            ? $ctx['pmacontrol_db']
            : null;
        if ($pmac === null || !method_exists($pmac, 'sql_query_silent')) {
            return [
                new Finding(
                    Finding::SEVERITY_INFO,
                    $this->category(),
                    $this->id(),
                    'Cross-source verification unavailable — read_only spot-check skipped',
                    'pmacontrol DB handle not in $ctx — ' . count($candidates) . ' reader backend(s) listed',
                    "Spot-check `SELECT @@global.read_only` on each reader backend manually.",
                    null
                ),
            ];
        }

        $findings = [];
        foreach ($candidates as [$h, $p]) {
            $hEsc = method_exists($pmac, 'sql_real_escape_string')
                ? $pmac->sql_real_escape_string($h)
                : addslashes($h);

            $idSql = "SELECT id FROM mysql_server WHERE ip='" . $hEsc . "' AND port=" . $p . " LIMIT 1";
            $idRes = $pmac->sql_query_silent($idSql);
            if ($idRes === false) {
                continue;
            }
            $idRow = method_exists($pmac, 'sql_fetch_array')
                ? $pmac->sql_fetch_array($idRes, MYSQLI_ASSOC)
                : null;
            if (empty($idRow['id'])) {
                continue; // pmacontrol doesn't monitor this backend
            }
            $idMs = (int) $idRow['id'];

            $valSql = "SELECT v.value FROM ts_value_general_text v "
                    . "JOIN ts_variable tv ON v.id_ts_variable = tv.id "
                    . "WHERE tv.name='read_only' AND v.id_mysql_server=" . $idMs . " "
                    . "ORDER BY v.date DESC LIMIT 1";
            $valRes = $pmac->sql_query_silent($valSql);
            if ($valRes === false) {
                continue;
            }
            $valRow = $pmac->sql_fetch_array($valRes, MYSQLI_ASSOC);
            if (empty($valRow)) {
                continue; // no last-known value — skip
            }
            $value = strtoupper(trim((string) ($valRow['value'] ?? '')));

            // 'OFF' / '0' / 'NO' / '' = not-read-only → offending.
            if (in_array($value, ['ON', '1', 'YES'], true)) {
                continue;
            }

            $findings[] = new Finding(
                Finding::SEVERITY_WARNING,
                $this->category(),
                $this->id(),
                sprintf(
                    'Reader-hostgroup backend %s:%d has @@global.read_only=%s',
                    $h, $p, $value === '' ? '<unknown>' : $value
                ),
                sprintf(
                    'mysql_server.id=%d ip=%s port=%d last-known read_only=%s',
                    $idMs, $h, $p, $value
                ),
                "Set the backend read-only and reload runtime:\n"
                . "  SET GLOBAL read_only = ON;\n"
                . "  -- on the backend itself\n",
                null
            );
        }

        return $findings;
    }
}
