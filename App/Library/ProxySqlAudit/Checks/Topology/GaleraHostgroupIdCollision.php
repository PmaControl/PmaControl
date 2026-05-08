<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit\Checks\Topology;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

/**
 * #902 — `mysql_galera_hostgroups` requires writer / reader /
 * backup_writer / offline to be FOUR DISTINCT hostgroup IDs.
 * Operators occasionally point two at the same id (e.g.
 * backup_writer = offline) — silently accepted at insert time,
 * breaks failover later.
 */
final class GaleraHostgroupIdCollision implements AuditCheck
{
    public function id(): string
    {
        return 'topology.galera-hostgroup-id-collision';
    }

    public function category(): string
    {
        return 'topology';
    }

    public function run(object $db, array $ctx = []): array
    {
        if (!method_exists($db, 'sql_query_silent')) {
            return [];
        }

        $sql = "SELECT writer_hostgroup, reader_hostgroup, backup_writer_hostgroup, offline_hostgroup "
             . "FROM runtime_mysql_galera_hostgroups "
             . "WHERE ("
             .   "writer_hostgroup IN (reader_hostgroup, backup_writer_hostgroup, offline_hostgroup) "
             .   "OR reader_hostgroup IN (backup_writer_hostgroup, offline_hostgroup) "
             .   "OR backup_writer_hostgroup = offline_hostgroup"
             . ")";

        $res = $db->sql_query_silent($sql);
        if ($res === false) {
            return [];
        }

        $offenders = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $offenders[] = sprintf(
                'writer=%s reader=%s backup_writer=%s offline=%s',
                (string) ($row['writer_hostgroup']        ?? '?'),
                (string) ($row['reader_hostgroup']        ?? '?'),
                (string) ($row['backup_writer_hostgroup'] ?? '?'),
                (string) ($row['offline_hostgroup']       ?? '?')
            );
        }

        if (empty($offenders)) {
            return [];
        }

        return [
            new Finding(
                Finding::SEVERITY_CRITICAL,
                $this->category(),
                $this->id(),
                sprintf(
                    '%d Galera cluster row(s) with overlapping hostgroup IDs',
                    count($offenders)
                ),
                $sql . "\n  → " . implode("\n  → ", $offenders),
                "Pick four distinct hostgroup IDs (writer, reader, backup_writer, offline).\n"
                . "  UPDATE mysql_galera_hostgroups SET offline_hostgroup=<NEW_OFFLINE_ID> WHERE writer_hostgroup=<W>;\n"
                . "  LOAD MYSQL SERVERS TO RUNTIME;",
                null
            ),
        ];
    }
}
