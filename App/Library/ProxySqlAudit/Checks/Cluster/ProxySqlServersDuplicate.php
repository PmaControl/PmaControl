<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit\Checks\Cluster;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

/**
 * #922 — flags duplicate (hostname, port) rows in
 * `runtime_proxysql_servers`.
 *
 * Cluster peer membership is keyed on the (hostname, port) tuple.
 * Duplicates make `proxysql_cluster` flap between peers, can hide a
 * stale peer behind a live one in `LOAD … TO RUNTIME` ordering, and
 * skew the diff/sync counters. Cleanup is a delete + reinsert under
 * a known weight + comment, then push the table to runtime.
 */
final class ProxySqlServersDuplicate implements AuditCheck
{
    public function id(): string
    {
        return 'cluster.proxysql-servers-duplicate';
    }

    public function category(): string
    {
        return 'cluster';
    }

    public function run(object $db, array $ctx = []): array
    {
        if (!method_exists($db, 'sql_query_silent')) {
            return [];
        }

        $sql = "SELECT hostname, port, COUNT(*) AS n "
             . "FROM runtime_proxysql_servers "
             . "GROUP BY hostname, port "
             . "HAVING n > 1";

        $res = $db->sql_query_silent($sql);
        if ($res === false) {
            return [];
        }

        $findings = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $h = (string) ($row['hostname'] ?? '?');
            $p = (string) ($row['port']     ?? '?');
            $n = (int)    ($row['n']        ?? 0);

            $findings[] = new Finding(
                Finding::SEVERITY_WARNING,
                $this->category(),
                $this->id(),
                sprintf('proxysql_servers has %d rows for %s:%s', $n, $h, $p),
                $sql . "\n  → hostname=" . $h . " port=" . $p . " count=" . $n,
                "Collapse the duplicates and reload:\n"
                . "  DELETE FROM proxysql_servers WHERE hostname='" . $h . "' AND port=" . $p . ";\n"
                . "  INSERT INTO proxysql_servers(hostname, port, weight, comment) "
                . "VALUES ('" . $h . "', " . $p . ", 0, '');\n"
                . "  LOAD PROXYSQL SERVERS TO RUNTIME;",
                null
            );
        }

        return $findings;
    }
}
