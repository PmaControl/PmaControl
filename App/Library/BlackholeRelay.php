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

    public static function listCandidateServers(): array
    {
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT id, name, display_name, ip, hostname, is_binlog_relay
                FROM mysql_server
                WHERE is_deleted = 0
                  AND is_proxy = 0
                  AND is_vip = 0
                ORDER BY is_binlog_relay DESC, display_name, name";
        $res = $db->sql_query($sql);
        $out = [];
        while ($r = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $out[] = $r;
        }
        return $out;
    }
}
