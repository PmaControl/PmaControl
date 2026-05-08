<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit\Checks\QueryRules;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

/**
 * #920 — flags active query rules whose `destination_hostgroup`
 * doesn't appear in `runtime_mysql_servers`.
 *
 * Matching queries get routed to a hostgroup that holds zero
 * backends → they sit until `mysql-default_query_timeout` and the
 * client gets a wall-clock timeout. Same family as #904, but on
 * the routing side rather than the user-default side.
 */
final class DestinationHostgroupUndefined implements AuditCheck
{
    public function id(): string
    {
        return 'query_rules.destination-hostgroup-undefined';
    }

    public function category(): string
    {
        return 'query_rules';
    }

    public function run(object $db, array $ctx = []): array
    {
        if (!method_exists($db, 'sql_query_silent')) {
            return [];
        }

        $sql = "SELECT rule_id, destination_hostgroup "
             . "FROM runtime_mysql_query_rules "
             . "WHERE active = 1 "
             .   "AND destination_hostgroup IS NOT NULL "
             .   "AND destination_hostgroup NOT IN ("
             .     "SELECT DISTINCT hostgroup_id FROM runtime_mysql_servers"
             .   ")";

        $res = $db->sql_query_silent($sql);
        if ($res === false) {
            return [];
        }

        $findings = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $ruleId = (string) ($row['rule_id'] ?? '?');
            $hg     = (string) ($row['destination_hostgroup'] ?? '?');

            $findings[] = new Finding(
                Finding::SEVERITY_CRITICAL,
                $this->category(),
                $this->id(),
                sprintf(
                    'Query rule rule_id=%s routes to undefined hostgroup %s',
                    $ruleId,
                    $hg
                ),
                $sql . "\n  → rule_id=" . $ruleId . " destination_hostgroup=" . $hg,
                "Either populate hostgroup " . $hg . " with mysql_servers rows, or re-target the rule:\n"
                . "  UPDATE mysql_query_rules SET destination_hostgroup=<EXISTING_HG> WHERE rule_id=" . $ruleId . ";\n"
                . "  LOAD MYSQL QUERY RULES TO RUNTIME;",
                null
            );
        }

        return $findings;
    }
}
