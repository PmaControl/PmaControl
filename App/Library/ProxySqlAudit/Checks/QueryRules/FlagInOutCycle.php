<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit\Checks\QueryRules;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

/**
 * #921 — flags cycles in the query-rule flag chain.
 *
 * ProxySQL evaluates rules in flag passes: each rule has
 * `flagIN` (the pass it activates on) and `flagOUT` (the pass to
 * jump to next). A cycle in that graph means matching queries
 * loop forever and die on `mysql_max_query_chain_size`.
 *
 * Cycle detection is a standard DFS with grey/black coloring
 * (white = unvisited, grey = on the current DFS stack, black =
 * fully explored). Encountering a grey successor is a back-edge =
 * cycle.
 */
final class FlagInOutCycle implements AuditCheck
{
    public function id(): string
    {
        return 'query_rules.flag-in-out-cycle';
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

        $sql = "SELECT rule_id, flagIN, flagOUT "
             . "FROM runtime_mysql_query_rules "
             . "WHERE active = 1 AND flagOUT IS NOT NULL";

        $res = $db->sql_query_silent($sql);
        if ($res === false) {
            return [];
        }

        // Adjacency list: flagIN -> [flagOUT, ...]
        // Track the originating rule_id for each edge so we can name
        // them in the cycle evidence.
        /** @var array<int,array<int,int>> $edges */
        $edges = [];
        /** @var array<string,int> $edgeRule */
        $edgeRule = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $from   = (int) ($row['flagIN']  ?? 0);
            $to     = (int) ($row['flagOUT'] ?? 0);
            $ruleId = (int) ($row['rule_id'] ?? 0);
            $edges[$from][] = $to;
            // Last writer wins for "the rule on this edge"; cycles
            // surface the rule_ids in the chain via this lookup.
            $edgeRule[$from . '->' . $to] = $ruleId;
        }
        if (empty($edges)) {
            return [];
        }

        // 0=white, 1=grey, 2=black
        $color  = [];
        $stack  = [];           // current DFS path of nodes
        /** @var list<list<int>> $cycles  list of node-id sequences */
        $cycles = [];
        $seenCycleKey = [];

        $visit = static function (int $u) use (&$visit, &$edges, &$color, &$stack, &$cycles, &$seenCycleKey): void {
            $color[$u] = 1;
            $stack[] = $u;
            foreach ($edges[$u] ?? [] as $v) {
                $cv = $color[$v] ?? 0;
                if ($cv === 0) {
                    $visit($v);
                } elseif ($cv === 1) {
                    // back-edge u -> v; reconstruct cycle from $stack.
                    $idx = array_search($v, $stack, true);
                    if ($idx === false) continue;
                    $cycle = array_slice($stack, $idx);
                    $cycle[] = $v; // close the loop visually
                    $key = implode(',', $cycle);
                    if (!isset($seenCycleKey[$key])) {
                        $seenCycleKey[$key] = true;
                        $cycles[] = $cycle;
                    }
                }
            }
            array_pop($stack);
            $color[$u] = 2;
        };

        foreach (array_keys($edges) as $start) {
            if (($color[$start] ?? 0) === 0) {
                $visit($start);
            }
        }
        if (empty($cycles)) {
            return [];
        }

        $findings = [];
        foreach ($cycles as $cycle) {
            $flagSeq = implode(' -> ', $cycle);
            $ruleIds = [];
            for ($i = 0, $n = count($cycle) - 1; $i < $n; $i++) {
                $key = $cycle[$i] . '->' . $cycle[$i + 1];
                if (isset($edgeRule[$key])) {
                    $ruleIds[] = $edgeRule[$key];
                }
            }
            $ruleIds = array_values(array_unique($ruleIds));

            $findings[] = new Finding(
                Finding::SEVERITY_WARNING,
                $this->category(),
                $this->id(),
                'Query-rule flag cycle detected: ' . $flagSeq,
                $sql . "\n  flag_sequence: " . $flagSeq
                     . "\n  rule_ids on the cycle: " . implode(', ', $ruleIds),
                "Pick a rule on the cycle and break it (set flagOUT=NULL or to a new sink flag):\n"
                . "  UPDATE mysql_query_rules SET flagOUT=NULL WHERE rule_id IN (" . implode(',', $ruleIds) . ");\n"
                . "  LOAD MYSQL QUERY RULES TO RUNTIME;",
                null
            );
        }

        return $findings;
    }
}
