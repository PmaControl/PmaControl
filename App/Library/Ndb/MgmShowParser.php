<?php

declare(strict_types=1);

namespace App\Library\Ndb;

/**
 * Parses the textual output of `ndb_mgm -e show` into a normalized
 * structure that PmaControl persists in `ndb_cluster` / `ndb_node`.
 *
 * The output format is documented at
 * https://dev.mysql.com/doc/refman/8.4/en/mysql-cluster-ndb-mgm.html ;
 * we tolerate the small variations seen across NDB 7.x / 8.0 / 8.4
 * (whitespace columns, optional `*` primary marker, "Starting Phase X"
 * vs "Starting", "(not connected, accepting connect from <ip>)" vs the
 * shorter "(not connected)" form) and the error envelope ndb_mgm
 * writes to stdout when it can't reach the management node.
 *
 * The class is deliberately I/O-free: SSH / TCP plumbing lives in
 * App\Service\Ndb\NdbCollector — this parser is pure string in, array
 * out, so the unit tests in tests/Library/Ndb/MgmShowParserTest.php
 * can pin every variant against the real lab fixtures (#798).
 */
final class MgmShowParser
{
    public const ROLE_MGMD = 'mgmd';
    public const ROLE_DATA = 'data';
    public const ROLE_SQL  = 'sql';

    public const STATUS_STARTED      = 'STARTED';      // data node fully online
    public const STATUS_CONNECTED    = 'CONNECTED';    // mgmd / sql online
    public const STATUS_STARTING     = 'STARTING';     // in start-phase
    public const STATUS_NOT_STARTED  = 'NOT_STARTED';  // data node down
    public const STATUS_DISCONNECTED = 'DISCONNECTED'; // mgmd / sql unreachable

    /**
     * @return array{
     *   reachable: bool,
     *   mgm_endpoint: ?string,
     *   cluster_version: ?string,
     *   nodes: list<array{
     *     ndb_node_id:int, role:string, ip:string, status:string,
     *     node_group:?int, is_primary:bool, ndb_version:?string
     *   }>,
     *   errors: list<string>
     * }
     */
    public static function parse(string $output): array
    {
        $result = [
            'reachable'       => false,
            'mgm_endpoint'    => null,
            'cluster_version' => null,
            'nodes'           => [],
            'errors'          => [],
        ];

        // Detect the "could not contact" envelope ndb_mgm prints when the
        // mgmd is unreachable. We surface it as an error and short-circuit
        // — there's no body to parse.
        foreach (self::iterLines($output) as $line) {
            if (preg_match('/(could not contact|connection refused|connect failed|unable to connect)/i', $line)) {
                $result['errors'][] = trim($line, " \t\r\n*-");
            }
        }

        // The "Connected to Management Server at: <ip>:<port>" header
        // always precedes a successful Show — we use its presence as the
        // reachable flag.
        if (preg_match('/^Connected to Management Server at:\s*([\w\.\-]+):(\d+)/mi', $output, $m)) {
            $result['reachable']    = true;
            $result['mgm_endpoint'] = $m[1] . ':' . $m[2];
        }

        $currentRole = null;
        $sections = [
            '[ndbd(NDB)]'      => self::ROLE_DATA,
            '[ndb_mgmd(MGM)]'  => self::ROLE_MGMD,
            '[mysqld(API)]'    => self::ROLE_SQL,
        ];

        foreach (self::iterLines($output) as $line) {
            $stripped = trim($line);
            if ($stripped === '') {
                continue;
            }

            // Section header: locks the role for the following node lines.
            $matchedSection = false;
            foreach ($sections as $marker => $role) {
                if (strpos($stripped, $marker) !== false) {
                    $currentRole = $role;
                    $matchedSection = true;
                    break;
                }
            }
            if ($matchedSection) {
                continue;
            }

            if ($currentRole === null) {
                continue;
            }

            $node = self::parseNodeLine($stripped, $currentRole);
            if ($node !== null) {
                $result['nodes'][] = $node;
            }
        }

        if ($result['nodes'] !== []) {
            $result['reachable']       = true;
            $result['cluster_version'] = self::pickClusterVersion($result['nodes']);
        }

        return $result;
    }

    /**
     * @return array{
     *   ndb_node_id:int, role:string, ip:string, status:string,
     *   node_group:?int, is_primary:bool, ndb_version:?string
     * }|null
     */
    private static function parseNodeLine(string $line, string $role): ?array
    {
        if (!preg_match('/^id=(\d+)\b(.*)$/', $line, $m)) {
            return null;
        }

        $nodeId = (int) $m[1];
        $rest   = $m[2];

        $ip          = '';
        $status      = self::defaultStatusForRole($role);
        $nodeGroup   = null;
        $isPrimary   = false;
        $ndbVersion  = null;

        // Connected form: `@<ip> (mysql-X.Y.Z ndb-A.B.C[, Nodegroup: G][, *])`
        // or `@<ip> (Starting Phase 50, Nodegroup: G)`.
        if (preg_match('/^\s*@([\w\.\-:]+)\s*\((.*)\)\s*$/', $rest, $mm)) {
            $ip   = $mm[1];
            $body = $mm[2];

            if (preg_match('/\bStarting\b/i', $body)) {
                $status = self::STATUS_STARTING;
            } else {
                $status = ($role === self::ROLE_DATA)
                    ? self::STATUS_STARTED
                    : self::STATUS_CONNECTED;
            }

            if (preg_match('/\bndb-([\d\.]+)/', $body, $mv)) {
                $ndbVersion = 'ndb-' . $mv[1];
            }
            if (preg_match('/Nodegroup:\s*(\d+)/i', $body, $mn)) {
                $nodeGroup = (int) $mn[1];
            }
            // The standalone `*` in the parenthesised body (after a comma)
            // marks the primary replica of a node group.
            $isPrimary = (bool) preg_match('/(?:^|,\s*)\*\s*(?:,|$)/', $body);
        } elseif (preg_match('/^\s*\(\s*not connected\b(.*)\)\s*$/i', $rest, $mm)) {
            // Disconnected form. The body sometimes carries the configured
            // hostname via "accepting connect from <ip>".
            $body = $mm[1];
            if (preg_match('/accepting connect from\s+([\w\.\-:]+)/i', $body, $mh)) {
                $ip = $mh[1];
            }
            $status = ($role === self::ROLE_DATA)
                ? self::STATUS_NOT_STARTED
                : self::STATUS_DISCONNECTED;
            $isPrimary = (bool) preg_match('/(?:^|,\s*)\*\s*(?:,|$)/', $body);
        } else {
            // Unknown shape — keep the id, drop the rest, mark as UNKNOWN
            // rather than crash on a single oddly-formatted line.
            $status = 'UNKNOWN';
        }

        return [
            'ndb_node_id' => $nodeId,
            'role'        => $role,
            'ip'          => $ip,
            'status'      => $status,
            'node_group'  => $nodeGroup,
            'is_primary'  => $isPrimary,
            'ndb_version' => $ndbVersion,
        ];
    }

    private static function defaultStatusForRole(string $role): string
    {
        return $role === self::ROLE_DATA
            ? self::STATUS_NOT_STARTED
            : self::STATUS_DISCONNECTED;
    }

    /**
     * Returns the most-frequent `ndb-X.Y.Z` string across the parsed
     * nodes — that's the cluster's "advertised" NDB version. Returns
     * null if no node carried a version (everyone disconnected).
     *
     * @param list<array{ndb_version:?string}> $nodes
     */
    private static function pickClusterVersion(array $nodes): ?string
    {
        $counts = [];
        foreach ($nodes as $node) {
            $v = $node['ndb_version'] ?? null;
            if (is_string($v) && $v !== '') {
                $counts[$v] = ($counts[$v] ?? 0) + 1;
            }
        }
        if ($counts === []) {
            return null;
        }
        arsort($counts);
        return (string) array_key_first($counts);
    }

    /**
     * @return iterable<int,string>
     */
    private static function iterLines(string $output): iterable
    {
        // Normalize CRLF/CR to LF before split.
        $normalized = str_replace(["\r\n", "\r"], "\n", $output);
        foreach (explode("\n", $normalized) as $line) {
            yield $line;
        }
    }
}
