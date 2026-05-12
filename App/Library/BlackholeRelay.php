<?php
/**
 * BlackholeRelay — convert a supervised MySQL/MariaDB server into a
 * BLACKHOLE binlog-relay node. Issue #1212 (lots #1213-#1217).
 *
 * Shared core between the UI controller (App\Controller\Blackhole) and
 * the CLI runner (`php App/Webroot/index.php Blackhole runConvertCli <id>`).
 * Progress is streamed to the `blackhole_conversion.progress` JSON column
 * so the UI can poll a live step-by-step log.
 */

namespace App\Library;

use \Glial\Sgbd\Sgbd;

class BlackholeRelay
{
    private const SYSTEM_SCHEMAS = [
        'mysql', 'sys', 'performance_schema', 'information_schema',
    ];

    /** @var \Glial\Sgbd\Sgbd */
    private $db;
    private int $conversionId;
    private array $steps = [];

    public function __construct(int $conversionId)
    {
        $this->db = Sgbd::sql(DB_DEFAULT);
        $this->conversionId = $conversionId;
    }

    // ------------------------------------------------------------------
    //  Public API
    // ------------------------------------------------------------------

    /**
     * Run the full conversion pipeline. Status + steps are updated as we
     * go so the UI can poll the row.
     */
    public function run(): bool
    {
        $this->updateStatus('running');

        try {
            $this->addStep('init', 'Loading conversion parameters...');
            $row = $this->loadConversion();
            if (!$row) {
                throw new \RuntimeException("blackhole_conversion #{$this->conversionId} not found");
            }
            $serverId = (int) $row['id_mysql_server'];
            $dryRun   = (int) $row['dry_run'] === 1;
            $server   = $this->loadServer($serverId);
            if (!$server) {
                throw new \RuntimeException("mysql_server #{$serverId} not found or deleted");
            }
            $this->updateLastStep(
                "Conversion #{$this->conversionId} for " . ($server['display_name'] ?: $server['name'])
                . ($dryRun ? ' (DRY-RUN)' : '')
            );

            // Connect to the target server using the same connection
            // alias PmaControl already uses for SHOW SLAVE STATUS etc.
            $this->addStep('connect', "Connecting to target via Sgbd::sql('{$server['name']}')...");
            $link = Sgbd::sql($server['name']);
            if (!$link) {
                throw new \RuntimeException("Unable to open Sgbd connection {$server['name']}");
            }
            $this->updateLastStep('Connected');

            // Refuse if the target isn't already a replica: a BLACKHOLE
            // node only makes sense as a downstream relay.
            $this->addStep('guard', 'Checking pre-conditions (target must be a replica)...');
            if (!$this->targetIsReplica($link)) {
                throw new \RuntimeException(
                    'Target has no configured replication channel — refusing. '
                    . 'A BLACKHOLE relay must be a downstream slave; configure '
                    . 'CHANGE MASTER first then re-run the conversion.'
                );
            }
            $this->updateLastStep('Target is a replica — ok');

            // ── STOP SLAVE on every configured channel ────────────────
            $this->addStep('stop_slave', 'Stopping replication on all channels...');
            $this->stopAllReplication($link, $dryRun);
            $this->updateLastStep('Replication stopped' . ($dryRun ? ' (dry-run: skipped)' : ''));

            // ── Enumerate convertible tables ──────────────────────────
            $this->addStep('discover', 'Enumerating non-system tables...');
            $tables = $this->discoverTables($link);
            $this->setTableCounts(count($tables), 0);
            $this->updateLastStep('Found ' . count($tables) . ' candidate tables');

            // ── Convert each table ────────────────────────────────────
            $this->addStep('convert', 'Converting tables to ENGINE=BLACKHOLE...');
            $converted = $this->convertTables($link, $tables, $dryRun);
            $this->setTableCounts(count($tables), $converted);
            $this->updateLastStep("Converted {$converted}/" . count($tables) . ' tables'
                . ($dryRun ? ' (dry-run: ALTER statements only logged)' : ''));

            // ── Persist runtime variables on the target ───────────────
            $this->addStep('persist', 'Setting runtime variables (default engine, binlog_format, read_only)...');
            $this->persistRuntimeConfig($link, $dryRun);
            $this->updateLastStep('Runtime config applied' . ($dryRun ? ' (dry-run)' : ''));

            // ── Mark inventory ────────────────────────────────────────
            $this->addStep('flag', 'Marking mysql_server.is_binlog_relay = 1...');
            if (!$dryRun) {
                $this->db->sql_query(
                    "UPDATE mysql_server SET is_binlog_relay = 1 WHERE id = {$serverId}"
                );
            }
            $this->updateLastStep('Inventory updated' . ($dryRun ? ' (dry-run)' : ''));

            // ── Restart replication ───────────────────────────────────
            $this->addStep('start_slave', 'Restarting replication...');
            $this->startAllReplication($link, $dryRun);
            $this->updateLastStep('Replication restarted' . ($dryRun ? ' (dry-run)' : ''));

            $this->updateStatus('done');
            return true;

        } catch (\Throwable $e) {
            $this->updateLastStep('ERROR: ' . $e->getMessage(), 'error');
            $this->setError($e->getMessage());
            $this->updateStatus('failed');
            return false;
        }
    }

    // ------------------------------------------------------------------
    //  Pipeline steps
    // ------------------------------------------------------------------

    /**
     * Returns true if the target has any configured replication source
     * (SHOW REPLICA STATUS / SHOW SLAVE STATUS / SHOW ALL SLAVES STATUS
     * on MariaDB returns at least one row).
     */
    private function targetIsReplica($link): bool
    {
        foreach (['SHOW ALL SLAVES STATUS', 'SHOW REPLICA STATUS', 'SHOW SLAVE STATUS'] as $sql) {
            $res = $link->sql_query_silent($sql);
            if ($res === false || $res === null) continue;
            if (method_exists($link, 'sql_num_rows')) {
                if ((int) $link->sql_num_rows($res) > 0) return true;
            } else {
                if ($link->sql_fetch_array($res, MYSQLI_ASSOC)) return true;
            }
        }
        return false;
    }

    /**
     * @return list<array{schema:string,table:string,engine:string}>
     */
    private function discoverTables($link): array
    {
        $excluded = "'" . implode("','", self::SYSTEM_SCHEMAS) . "'";
        $sql = "SELECT table_schema, table_name, engine
                FROM information_schema.tables
                WHERE table_type = 'BASE TABLE'
                  AND table_schema NOT IN ({$excluded})
                  AND COALESCE(engine, '') <> 'BLACKHOLE'
                ORDER BY table_schema, table_name";
        $res = $link->sql_query($sql);
        $out = [];
        while ($r = $link->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $out[] = [
                'schema' => (string) $r['table_schema'],
                'table'  => (string) $r['table_name'],
                'engine' => (string) ($r['engine'] ?? ''),
            ];
        }
        return $out;
    }

    private function convertTables($link, array $tables, bool $dryRun): int
    {
        $converted = 0;
        foreach ($tables as $t) {
            $stmt = sprintf(
                'ALTER TABLE `%s`.`%s` ENGINE=BLACKHOLE',
                str_replace('`', '``', $t['schema']),
                str_replace('`', '``', $t['table'])
            );
            $this->addStep('alter', $stmt);
            if (!$dryRun) {
                if (!$link->sql_query_silent($stmt)) {
                    $err = method_exists($link, 'sql_error') ? $link->sql_error() : 'sql_query failed';
                    $this->updateLastStep($stmt . ' — FAILED: ' . $err, 'error');
                    continue;
                }
            }
            $this->updateLastStep($stmt . ($dryRun ? ' (dry-run)' : ' — ok'));
            $converted++;
            if ($converted % 25 === 0) {
                $this->setTableCounts(count($tables), $converted);
            }
        }
        return $converted;
    }

    private function persistRuntimeConfig($link, bool $dryRun): void
    {
        $statements = [
            "SET GLOBAL default_storage_engine = 'BLACKHOLE'",
            "SET GLOBAL binlog_format = 'ROW'",
            "SET GLOBAL read_only = ON",
            "SET GLOBAL super_read_only = ON",
        ];
        foreach ($statements as $stmt) {
            $this->addStep('runtime', $stmt);
            if (!$dryRun) {
                $link->sql_query_silent($stmt);
            }
            $this->updateLastStep($stmt . ($dryRun ? ' (dry-run)' : ' — ok'));
        }
    }

    private function stopAllReplication($link, bool $dryRun): void
    {
        if ($dryRun) return;
        // STOP ALL SLAVES works on MariaDB; STOP REPLICA on MySQL 8+;
        // fall back to plain STOP SLAVE.
        foreach (['STOP ALL SLAVES', 'STOP REPLICA', 'STOP SLAVE'] as $sql) {
            if ($link->sql_query_silent($sql)) {
                return;
            }
        }
    }

    private function startAllReplication($link, bool $dryRun): void
    {
        if ($dryRun) return;
        foreach (['START ALL SLAVES', 'START REPLICA', 'START SLAVE'] as $sql) {
            if ($link->sql_query_silent($sql)) {
                return;
            }
        }
    }

    // ------------------------------------------------------------------
    //  Progress / status helpers — mirror BinlogAnalyzer's pattern
    // ------------------------------------------------------------------

    private function addStep(string $key, string $message): void
    {
        $this->steps[] = [
            'key'        => $key,
            'message'    => $message,
            'status'     => 'running',
            'time_start' => date('H:i:s'),
            'time_end'   => null,
        ];
        $this->flushProgress();
    }

    private function updateLastStep(string $message, string $status = 'done'): void
    {
        if (empty($this->steps)) return;
        $i = count($this->steps) - 1;
        $this->steps[$i]['message']  = $message;
        $this->steps[$i]['status']   = $status;
        $this->steps[$i]['time_end'] = date('H:i:s');
        $this->flushProgress();
    }

    private function flushProgress(): void
    {
        $json = json_encode($this->steps, JSON_UNESCAPED_UNICODE);
        $this->db->sql_query(
            "UPDATE blackhole_conversion SET progress = '"
            . $this->db->sql_real_escape_string((string) $json)
            . "' WHERE id = " . $this->conversionId
        );
    }

    private function updateStatus(string $status): void
    {
        $completed = in_array($status, ['done', 'failed', 'rolledback'], true)
            ? ", completed_at = NOW()"
            : "";
        $this->db->sql_query(
            "UPDATE blackhole_conversion
             SET status = '" . $this->db->sql_real_escape_string($status) . "'"
             . $completed .
            " WHERE id = " . $this->conversionId
        );
    }

    private function setError(string $message): void
    {
        $this->db->sql_query(
            "UPDATE blackhole_conversion
             SET error_message = '" . $this->db->sql_real_escape_string($message) . "'
             WHERE id = " . $this->conversionId
        );
    }

    private function setTableCounts(int $total, int $converted): void
    {
        $this->db->sql_query(
            "UPDATE blackhole_conversion
             SET tables_total = {$total}, tables_converted = {$converted}
             WHERE id = " . $this->conversionId
        );
    }

    // ------------------------------------------------------------------
    //  Lookups
    // ------------------------------------------------------------------

    private function loadConversion(): ?array
    {
        $res = $this->db->sql_query(
            "SELECT * FROM blackhole_conversion WHERE id = " . $this->conversionId
        );
        $row = $res ? $this->db->sql_fetch_array($res, MYSQLI_ASSOC) : null;
        return $row ?: null;
    }

    private function loadServer(int $id): ?array
    {
        $res = $this->db->sql_query(
            "SELECT id, name, display_name, ip, is_binlog_relay
             FROM mysql_server WHERE id = {$id} AND is_deleted = 0"
        );
        $row = $res ? $this->db->sql_fetch_array($res, MYSQLI_ASSOC) : null;
        return $row ?: null;
    }

    // ------------------------------------------------------------------
    //  Inventory helpers — used by the controller's index page so we
    //  keep the SQL in one place.
    // ------------------------------------------------------------------

    /**
     * List servers usable as a BLACKHOLE relay candidate, with one row
     * per (server, replication_channel). A server qualifies when it has
     * at least one channel with a resolvable upstream master, and is
     * not part of a 2-cycle master ↔ master topology.
     *
     * Each row also carries `downstream_slaves` — the number of other
     * monitored servers replicating *from* this one, computed in a
     * single pass over the same Extraction2 result.
     */
    public static function listCandidateServers(): array
    {
        $db = Sgbd::sql(DB_DEFAULT);

        // Inventory keyed by id for fast lookup of names + flags.
        $res = $db->sql_query(
            "SELECT id, name, display_name, ip, hostname, is_binlog_relay
             FROM mysql_server
             WHERE is_deleted = 0 AND is_proxy = 0 AND is_vip = 0"
        );
        $servers = [];
        while ($r = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $servers[(int) $r['id']] = $r;
        }
        if (empty($servers)) return [];

        // master_host + master_port per (server, connection_name).
        // Extraction2 returns ['@slave' => [cn => [field => value, ...]]]
        $slaveData = \App\Library\Extraction2::display([
            'slave::master_host', 'slave::master_port',
        ]);

        // First pass: resolve every (server, cn) → upstream master_id.
        $upstream = [];        // [server_id][cn] = master_id (or 0)
        $downstreamCount = []; // [master_id] = count of distinct (slave_id, cn) pairs
        foreach ($slaveData as $sid => $row) {
            $sid = (int) $sid;
            if (!isset($servers[$sid])) continue;
            if (!isset($row['@slave']) || !is_array($row['@slave'])) continue;

            foreach ($row['@slave'] as $cn => $channel) {
                if (!is_array($channel)) continue;
                $host = (string) ($channel['master_host'] ?? '');
                $port = (int)    ($channel['master_port'] ?? 0);
                if ($host === '' || $port === 0) continue;

                $masterId = self::resolveServerByHostPort($db, $servers, $host, $port);
                if ($masterId === 0) continue;
                $upstream[$sid][(string) $cn] = $masterId;
                $downstreamCount[$masterId] = ($downstreamCount[$masterId] ?? 0) + 1;
            }
        }

        // Second pass: emit one row per (server, cn) where the server is
        // a replica and is *not* in a master↔master 2-cycle with its
        // upstream. A server already flagged as relay is always shown
        // so the operator can inspect it.
        $out = [];
        foreach ($servers as $sid => $srv) {
            $isRelay = (int) $srv['is_binlog_relay'] === 1;
            $hasChannel = !empty($upstream[$sid]);
            if (!$isRelay && !$hasChannel) continue;

            $channels = $upstream[$sid] ?? ['' => 0];
            foreach ($channels as $cn => $masterId) {
                if ($masterId > 0 && self::isMutualReplica($upstream, $sid, $masterId)) {
                    continue;
                }
                $masterDisplay = $masterId > 0 && isset($servers[$masterId])
                    ? ($servers[$masterId]['display_name'] ?: $servers[$masterId]['name'])
                    : '';
                $out[] = [
                    'id'                => (int) $srv['id'],
                    'name'              => (string) $srv['name'],
                    'display_name'      => (string) $srv['display_name'],
                    'ip'                => (string) $srv['ip'],
                    'hostname'          => (string) $srv['hostname'],
                    'is_binlog_relay'   => (int) $srv['is_binlog_relay'],
                    'connection_name'   => (string) $cn,
                    'master_id'         => (int) $masterId,
                    'master_display'    => $masterDisplay,
                    'downstream_slaves' => (int) ($downstreamCount[(int) $srv['id']] ?? 0),
                ];
            }
        }

        // Stable order: relays first, then by downstream desc, then by name.
        usort($out, static function ($a, $b) {
            if ($a['is_binlog_relay'] !== $b['is_binlog_relay']) {
                return $b['is_binlog_relay'] <=> $a['is_binlog_relay'];
            }
            if ($a['downstream_slaves'] !== $b['downstream_slaves']) {
                return $b['downstream_slaves'] <=> $a['downstream_slaves'];
            }
            return strcasecmp($a['display_name'] ?: $a['name'], $b['display_name'] ?: $b['name']);
        });

        return $out;
    }

    /**
     * Best-effort host:port → mysql_server.id resolver. Tries the
     * canonical Mysql::getIdFromDns helper first, falls back to a
     * direct SELECT on (ip, port), then a hostname scan.
     */
    private static function resolveServerByHostPort($db, array $servers, string $host, int $port): int
    {
        if (class_exists(\App\Library\Mysql::class) && method_exists(\App\Library\Mysql::class, 'getIdFromDns')) {
            $resolved = (int) \App\Library\Mysql::getIdFromDns($host . ':' . $port);
            if ($resolved > 0) return $resolved;
        }
        $h = $db->sql_real_escape_string($host);
        $sql = "SELECT id FROM mysql_server WHERE ip = '{$h}' AND port = {$port} AND is_deleted = 0 LIMIT 1";
        $res = $db->sql_query_silent($sql);
        if ($res && ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC))) {
            return (int) $row['id'];
        }
        foreach ($servers as $s) {
            if (((string) $s['hostname']) === $host) {
                // hostname match — port already filtered upstream.
                return (int) $s['id'];
            }
        }
        return 0;
    }

    /**
     * True iff $a replicates from $b AND $b replicates from $a on any
     * channel — the master ↔ master pattern we want to hide.
     */
    private static function isMutualReplica(array $upstream, int $a, int $b): bool
    {
        $aMasters = $upstream[$a] ?? [];
        $bMasters = $upstream[$b] ?? [];
        return in_array($b, $aMasters, true) && in_array($a, $bMasters, true);
    }
}
