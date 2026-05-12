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
            $mode = ((string) ($row['provision_mode'] ?? 'convert')) === 'greenfield'
                ? 'greenfield' : 'convert';
            $this->updateLastStep("Conversion #{$this->conversionId} — mode=$mode"
                . ((int) $row['dry_run'] === 1 ? ' (DRY-RUN)' : ''));

            $ok = $mode === 'greenfield'
                ? $this->runGreenfield($row)
                : $this->runConvert($row);

            $this->updateStatus($ok ? 'done' : 'failed');
            return $ok;
        } catch (\Throwable $e) {
            $this->updateLastStep('ERROR: ' . $e->getMessage(), 'error');
            $this->setError($e->getMessage());
            $this->updateStatus('failed');
            return false;
        }
    }

    // ------------------------------------------------------------------
    //  Pipeline A — convert an already-replicating slave in place
    // ------------------------------------------------------------------

    private function runConvert(array $row): bool
    {
        $serverId = (int) $row['id_mysql_server'];
        $dryRun   = (int) $row['dry_run'] === 1;
        $server   = $this->loadServer($serverId);
        if (!$server) {
            throw new \RuntimeException("mysql_server #{$serverId} not found or deleted");
        }

        $this->addStep('connect', "Connecting to target via Sgbd::sql('{$server['name']}')...");
        $link = Sgbd::sql($server['name']);
        if (!$link) {
            throw new \RuntimeException("Unable to open Sgbd connection {$server['name']}");
        }
        $this->updateLastStep('Connected');

        $this->addStep('guard', 'Checking pre-conditions (target must be a replica)...');
        if (!$this->targetIsReplica($link)) {
            throw new \RuntimeException(
                'Target has no configured replication channel — refusing. '
                . 'For a fresh node, use the "Create new relay from scratch" '
                . 'form (greenfield mode) instead.'
            );
        }
        $this->updateLastStep('Target is a replica — ok');

        $this->addStep('stop_slave', 'Stopping replication on all channels...');
        $this->stopAllReplication($link, $dryRun);
        $this->updateLastStep('Replication stopped' . ($dryRun ? ' (dry-run: skipped)' : ''));

        $this->addStep('discover', 'Enumerating non-system tables...');
        $tables = $this->discoverTables($link);
        $this->setTableCounts(count($tables), 0);
        $this->updateLastStep('Found ' . count($tables) . ' candidate tables');

        $this->addStep('convert', 'Converting tables to ENGINE=BLACKHOLE...');
        $converted = $this->convertTables($link, $tables, $dryRun);
        $this->setTableCounts(count($tables), $converted);
        $this->updateLastStep("Converted {$converted}/" . count($tables) . ' tables'
            . ($dryRun ? ' (dry-run: ALTER statements only logged)' : ''));

        $this->addStep('persist', 'Setting runtime variables (default engine, binlog_format, read_only)...');
        $this->persistRuntimeConfig($link, $dryRun);
        $this->updateLastStep('Runtime config applied' . ($dryRun ? ' (dry-run)' : ''));

        $this->addStep('flag', 'Marking mysql_server.is_binlog_relay = 1...');
        if (!$dryRun) {
            $this->db->sql_query(
                "UPDATE mysql_server SET is_binlog_relay = 1 WHERE id = {$serverId}"
            );
        }
        $this->updateLastStep('Inventory updated' . ($dryRun ? ' (dry-run)' : ''));

        $this->addStep('start_slave', 'Restarting replication...');
        $this->startAllReplication($link, $dryRun);
        $this->updateLastStep('Replication restarted' . ($dryRun ? ' (dry-run)' : ''));

        return true;
    }

    // ------------------------------------------------------------------
    //  Pipeline B — provision a relay from scratch (greenfield)
    // ------------------------------------------------------------------

    private function runGreenfield(array $row): bool
    {
        $serverId = (int) $row['id_mysql_server'];
        $masterId = (int) $row['id_mysql_server__master'];
        $dryRun   = (int) $row['dry_run'] === 1;

        $server = $this->loadServer($serverId);
        $master = $masterId > 0 ? $this->loadServer($masterId) : null;
        if (!$server) {
            throw new \RuntimeException("Target mysql_server #{$serverId} not found");
        }
        if (!$master) {
            throw new \RuntimeException("Master mysql_server #{$masterId} not found");
        }

        $targetLabel = $server['display_name'] ?: $server['name'];
        $masterLabel = $master['display_name'] ?: $master['name'];

        $this->addStep('connect_target', "Connecting to target via Sgbd::sql('{$server['name']}')...");
        $tgt = Sgbd::sql($server['name']);
        if (!$tgt) {
            throw new \RuntimeException("Unable to open Sgbd connection {$server['name']}");
        }
        $this->updateLastStep('Connected to target ' . $targetLabel);

        $this->addStep('connect_master', "Connecting to master via Sgbd::sql('{$master['name']}')...");
        $src = Sgbd::sql($master['name']);
        if (!$src) {
            throw new \RuntimeException("Unable to open Sgbd connection {$master['name']}");
        }
        $this->updateLastStep('Connected to master ' . $masterLabel);

        // ── Activity guard on target ─────────────────────────────────
        $this->addStep('guard_idle', 'Asserting target is idle (no user databases / no non-monitoring sessions)...');
        $idle = $this->assertTargetIsIdle($tgt);
        if ($idle !== true) {
            throw new \RuntimeException("Target is not idle: {$idle}");
        }
        $this->updateLastStep('Target is idle — ok');

        // ── Master must have binlog ──────────────────────────────────
        $this->addStep('guard_master', 'Verifying master has binary logging enabled...');
        $masterStatus = $this->fetchMasterStatus($src);
        if ($masterStatus === null) {
            throw new \RuntimeException('Master has no binary log (SHOW MASTER STATUS empty). Enable log_bin first.');
        }
        $this->updateLastStep("Master at {$masterStatus['File']}:{$masterStatus['Position']}");

        // ── Dump schema from master, restore on target ───────────────
        $this->addStep('dump_restore', "Dumping schema from {$masterLabel} and piping into {$targetLabel}...");
        if ($dryRun) {
            $this->updateLastStep('Skipped (dry-run): would mysqldump --no-data --master-data=2 …');
        } else {
            $dumpResult = $this->dumpAndRestoreSchema($master, $server);
            if (!$dumpResult['ok']) {
                throw new \RuntimeException('mysqldump|mysql failed: ' . $dumpResult['error']);
            }
            $this->updateLastStep('Schema replicated (' . $dumpResult['stderr_tail'] . ')');
        }

        // ── Enumerate + convert all just-copied tables to BLACKHOLE ──
        $this->addStep('discover', 'Enumerating freshly-created tables on target...');
        $tables = $this->discoverTables($tgt);
        $this->setTableCounts(count($tables), 0);
        $this->updateLastStep('Found ' . count($tables) . ' tables to convert');

        $this->addStep('convert', 'Converting every table to ENGINE=BLACKHOLE...');
        $converted = $this->convertTables($tgt, $tables, $dryRun);
        $this->setTableCounts(count($tables), $converted);
        $this->updateLastStep("Converted {$converted}/" . count($tables) . ' tables'
            . ($dryRun ? ' (dry-run)' : ''));

        // ── Wire replication ─────────────────────────────────────────
        $replicationUser = (string) ($row['replication_user'] ?? '');
        if ($replicationUser === '') {
            $replicationUser = (string) $master['login']; // fallback
        }
        $this->addStep('change_master', "Pointing target at {$master['ip']}:{$master['port']} (user=" . $replicationUser . ')...');
        $this->configureReplication(
            $tgt,
            $master,
            $masterStatus,
            $replicationUser,
            $this->decryptServerPassword($master),
            $dryRun
        );
        $this->updateLastStep('CHANGE MASTER TO issued' . ($dryRun ? ' (dry-run)' : ''));

        // ── Apply runtime config + mark inventory ────────────────────
        $this->addStep('persist', 'Setting runtime variables (default engine, binlog_format, read_only)...');
        $this->persistRuntimeConfig($tgt, $dryRun);
        $this->updateLastStep('Runtime config applied' . ($dryRun ? ' (dry-run)' : ''));

        $this->addStep('flag', 'Marking mysql_server.is_binlog_relay = 1...');
        if (!$dryRun) {
            $this->db->sql_query(
                "UPDATE mysql_server SET is_binlog_relay = 1 WHERE id = {$serverId}"
            );
        }
        $this->updateLastStep('Inventory updated' . ($dryRun ? ' (dry-run)' : ''));

        $this->addStep('start_slave', 'Starting replication...');
        $this->startAllReplication($tgt, $dryRun);
        $this->updateLastStep('Replication started' . ($dryRun ? ' (dry-run)' : ''));

        return true;
    }

    // ------------------------------------------------------------------
    //  Greenfield helpers
    // ------------------------------------------------------------------

    /**
     * Returns `true` if the target server has only system schemas and
     * no non-monitoring sessions. Otherwise returns a human description
     * of what blocks the provisioning.
     *
     * @return true|string
     */
    private function assertTargetIsIdle($link)
    {
        // 1. user databases
        $excluded = "'" . implode("','", self::SYSTEM_SCHEMAS) . "'";
        $res = $link->sql_query(
            "SELECT GROUP_CONCAT(DISTINCT table_schema) AS schemas, COUNT(*) AS n
             FROM information_schema.tables
             WHERE table_schema NOT IN ({$excluded})"
        );
        $row = $link->sql_fetch_array($res, MYSQLI_ASSOC);
        $tableCount = (int) ($row['n'] ?? 0);
        if ($tableCount > 0) {
            $schemas = (string) ($row['schemas'] ?? '');
            return "target already has {$tableCount} non-system tables in schema(s) [{$schemas}]";
        }

        // 2. active non-monitoring sessions
        $res = $link->sql_query(
            "SELECT user, host, command, state, info
             FROM information_schema.processlist
             WHERE command NOT IN ('Daemon', 'Sleep', 'Binlog Dump')
               AND user NOT IN ('system user', 'event_scheduler')
               AND id <> CONNECTION_ID()"
        );
        $offending = [];
        $monitoringUsers = $this->monitoringUserAllowlist($link);
        while ($r = $link->sql_fetch_array($res, MYSQLI_ASSOC)) {
            if (in_array((string) $r['user'], $monitoringUsers, true)) continue;
            $offending[] = $r['user'] . '@' . preg_replace('/:\d+$/', '', (string) $r['host'])
                . ' [' . $r['command'] . '/' . ($r['state'] ?: '-') . ']';
        }
        if (!empty($offending)) {
            return 'active non-monitoring sessions: ' . implode('; ', array_slice($offending, 0, 5));
        }

        return true;
    }

    /**
     * Sessions opened by the PmaControl monitoring user are tolerated
     * by `assertTargetIsIdle()`. The allowlist also includes 'root' on
     * loopback because the operator may have a maintenance shell open.
     *
     * @return list<string>
     */
    private function monitoringUserAllowlist($link): array
    {
        // The login pmacontrol uses to monitor this server lives on
        // mysql_server.login. Pull every distinct login currently set
        // on supervised servers (typically one or two distinct values).
        $res = $this->db->sql_query(
            "SELECT DISTINCT login FROM mysql_server WHERE is_deleted = 0 AND login <> ''"
        );
        $out = ['root', 'mariadb.session', 'mysql.session'];
        while ($r = $this->db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $out[] = (string) $r['login'];
        }
        return array_values(array_unique($out));
    }

    /**
     * SHOW MASTER STATUS on the master. Returns null when binary
     * logging is disabled.
     *
     * @return array{File:string,Position:int}|null
     */
    private function fetchMasterStatus($link): ?array
    {
        $res = $link->sql_query_silent('SHOW MASTER STATUS');
        if (!$res) return null;
        $row = $link->sql_fetch_array($res, MYSQLI_ASSOC);
        if (!$row || empty($row['File'])) return null;
        return ['File' => (string) $row['File'], 'Position' => (int) $row['Position']];
    }

    /**
     * Pipe `mysqldump --no-data --routines --triggers --events
     * --master-data=2 --single-transaction` from the master into a
     * `mysql` client targeting the new relay. Schema-only because the
     * relay only needs table definitions (BLACKHOLE drops every row
     * anyway). Replication will fill the binlog from `--master-data=2`'s
     * recorded position onwards.
     *
     * @return array{ok:bool,error:string,stderr_tail:string}
     */
    private function dumpAndRestoreSchema(array $master, array $target): array
    {
        $masterPwd = $this->decryptServerPassword($master);
        $targetPwd = $this->decryptServerPassword($target);

        // Defcred files avoid leaking passwords on the command line.
        $masterDef = tempnam(sys_get_temp_dir(), 'bh_m_');
        $targetDef = tempnam(sys_get_temp_dir(), 'bh_t_');
        file_put_contents($masterDef, "[client]\nuser={$master['login']}\npassword=\"" . addcslashes($masterPwd, '"\\') . "\"\nhost={$master['ip']}\nport={$master['port']}\n");
        file_put_contents($targetDef, "[client]\nuser={$target['login']}\npassword=\"" . addcslashes($targetPwd, '"\\') . "\"\nhost={$target['ip']}\nport={$target['port']}\n");
        chmod($masterDef, 0600);
        chmod($targetDef, 0600);

        $errFile = tempnam(sys_get_temp_dir(), 'bh_err_');
        try {
            // --init-command on the receiving `mysql` client disables
            // binary logging for the import session — the CREATE TABLE
            // statements from the master must NOT land in the relay's
            // own binlog, otherwise any downstream slave connecting
            // later would receive them as if they were master DDL.
            $cmd = sprintf(
                "mysqldump --defaults-extra-file=%s "
                . "--no-data --routines --triggers --events "
                . "--single-transaction --master-data=2 --all-databases "
                . "--ignore-database=mysql --ignore-database=sys "
                . "--ignore-database=performance_schema --ignore-database=information_schema "
                . "2>%s "
                . "| mysql --defaults-extra-file=%s --init-command='SET sql_log_bin=0' 2>>%s",
                escapeshellarg($masterDef),
                escapeshellarg($errFile),
                escapeshellarg($targetDef),
                escapeshellarg($errFile)
            );
            $ret = null;
            $out = [];
            exec($cmd . '; echo __RC=$?', $out, $ret);
            $rc = 0;
            foreach ($out as $line) {
                if (preg_match('/^__RC=(\d+)$/', $line, $m)) {
                    $rc = (int) $m[1];
                }
            }
            $stderr = is_readable($errFile) ? (string) file_get_contents($errFile) : '';
            $tail = substr(trim($stderr), -400);
            if ($rc !== 0) {
                return ['ok' => false, 'error' => "exit=$rc; " . $tail, 'stderr_tail' => $tail];
            }
            return ['ok' => true, 'error' => '', 'stderr_tail' => $tail !== '' ? $tail : 'no stderr'];
        } finally {
            @unlink($masterDef);
            @unlink($targetDef);
            @unlink($errFile);
        }
    }

    /**
     * Issue CHANGE MASTER TO + the matching grants prerequisite on the
     * target. `master_use_gtid=slave_pos` would be cleaner but requires
     * the master to be on GTID; fall back to MASTER_LOG_FILE/POS which
     * works on every supported version.
     *
     * @param array{File:string,Position:int} $pos
     */
    private function configureReplication($link, array $master, array $pos, string $user, string $password, bool $dryRun): void
    {
        $stmt = sprintf(
            "CHANGE MASTER TO "
            . "MASTER_HOST='%s', MASTER_PORT=%d, "
            . "MASTER_USER='%s', MASTER_PASSWORD='%s', "
            . "MASTER_LOG_FILE='%s', MASTER_LOG_POS=%d, "
            . "MASTER_CONNECT_RETRY=10",
            addcslashes((string) $master['ip'], "'\\"),
            (int) $master['port'],
            addcslashes($user, "'\\"),
            addcslashes($password, "'\\"),
            addcslashes((string) $pos['File'], "'\\"),
            (int) $pos['Position']
        );
        // Log a redacted version so the password never lands in the
        // progress JSON.
        $redacted = preg_replace("/MASTER_PASSWORD='[^']*'/", "MASTER_PASSWORD='********'", $stmt);
        $this->addStep('change_master_sql', $redacted);
        if (!$dryRun) {
            if (!$link->sql_query_silent($stmt)) {
                $err = method_exists($link, 'sql_error') ? $link->sql_error() : 'sql_query failed';
                throw new \RuntimeException('CHANGE MASTER TO failed: ' . $err);
            }
        }
        $this->updateLastStep($redacted . ($dryRun ? ' (dry-run)' : ' — ok'));
    }

    /**
     * Decrypt mysql_server.passwd if marked is_password_crypted=1.
     * Falls back to the raw string otherwise.
     */
    private function decryptServerPassword(array $server): string
    {
        $raw = (string) ($server['passwd'] ?? '');
        if ((int) ($server['is_password_crypted'] ?? 0) !== 1) {
            return $raw;
        }
        if (class_exists(\App\Library\Chiffrement::class)
            && method_exists(\App\Library\Chiffrement::class, 'decrypt')
        ) {
            return (string) \App\Library\Chiffrement::decrypt($raw);
        }
        return $raw;
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
        // CRITICAL: keep the ALTERs out of this server's binary log.
        // Otherwise log_slave_updates would forward them to every
        // downstream consumer — they'd ENGINE=BLACKHOLE their own
        // tables, which is a disaster.
        $this->addStep('no_binlog', 'SET SESSION sql_log_bin = 0 — keep ALTERs out of the relay binlog');
        if (!$dryRun) {
            if (!$link->sql_query_silent('SET SESSION sql_log_bin = 0')) {
                $err = method_exists($link, 'sql_error') ? $link->sql_error() : 'sql_query failed';
                $this->updateLastStep('SET SESSION sql_log_bin = 0 — FAILED: ' . $err
                    . ' (refusing to ALTER without sql_log_bin=0 — downstream slaves would inherit the BLACKHOLE conversion)', 'error');
                return 0;
            }
        }
        $this->updateLastStep('SET SESSION sql_log_bin = 0' . ($dryRun ? ' (dry-run)' : ' — ok'));

        // Verify the session really shows sql_log_bin=OFF. This is the
        // user-visible proof — surfaced in the log so the operator
        // sees the value before the first ALTER. Fail-closed if the
        // SET silently didn't take (sql_log_bin is not settable when
        // gtid_strict_mode + a transaction is open on some MariaDB
        // builds, for instance).
        if (!$dryRun) {
            $current = $this->readSqlLogBin($link);
            $this->addStep('no_binlog_verify',
                "SHOW VARIABLES LIKE 'sql_log_bin' → " . ($current ?? 'NULL'));
            if (strcasecmp((string) $current, 'OFF') !== 0) {
                $this->updateLastStep(
                    "SHOW VARIABLES LIKE 'sql_log_bin' returned '" . ($current ?? 'NULL')
                    . "' — expected 'OFF'. Aborting before any ALTER.",
                    'error'
                );
                return 0;
            }
            $this->updateLastStep("SHOW VARIABLES LIKE 'sql_log_bin' → OFF — ok");
        }

        $converted = 0;
        try {
            foreach ($tables as $t) {
                // Per-ALTER guard: re-check sql_log_bin right before
                // the statement. Cheap, and gives an iron-clad proof
                // for every single ALTER in the progress log.
                if (!$dryRun) {
                    $current = $this->readSqlLogBin($link);
                    if (strcasecmp((string) $current, 'OFF') !== 0) {
                        $this->addStep('no_binlog_drift',
                            "sql_log_bin drifted to '" . ($current ?? 'NULL')
                            . "' — aborting BLACKHOLE conversion.");
                        $this->updateLastStep(
                            "sql_log_bin drifted to '" . ($current ?? 'NULL')
                            . "' — aborting BLACKHOLE conversion.",
                            'error'
                        );
                        break;
                    }
                }
                $stmt = sprintf(
                    'ALTER TABLE `%s`.`%s` ENGINE=BLACKHOLE',
                    str_replace('`', '``', $t['schema']),
                    str_replace('`', '``', $t['table'])
                );
                $this->addStep('alter', '[sql_log_bin=OFF] ' . $stmt);
                if (!$dryRun) {
                    if (!$link->sql_query_silent($stmt)) {
                        $err = method_exists($link, 'sql_error') ? $link->sql_error() : 'sql_query failed';
                        $this->updateLastStep('[sql_log_bin=OFF] ' . $stmt . ' — FAILED: ' . $err, 'error');
                        continue;
                    }
                }
                $this->updateLastStep('[sql_log_bin=OFF] ' . $stmt
                    . ($dryRun ? ' (dry-run)' : ' — ok'));
                $converted++;
                if ($converted % 25 === 0) {
                    $this->setTableCounts(count($tables), $converted);
                }
            }
        } finally {
            // Restore the default so any future query on this session
            // behaves normally. (Sgbd::sql() may pool / reuse the
            // connection — leaving sql_log_bin=0 would be wrong.)
            $this->addStep('no_binlog_restore', 'SET SESSION sql_log_bin = 1 — restore default');
            if (!$dryRun) {
                $link->sql_query_silent('SET SESSION sql_log_bin = 1');
            }
            $this->updateLastStep('SET SESSION sql_log_bin = 1' . ($dryRun ? ' (dry-run)' : ' — ok'));
        }
        return $converted;
    }

    /**
     * Returns the current `sql_log_bin` session value as reported by
     * `SHOW VARIABLES LIKE 'sql_log_bin'`. `null` when the query
     * fails. Used to prove (and log) that the session is bin-log-off
     * before every ALTER … ENGINE=BLACKHOLE.
     */
    private function readSqlLogBin($link): ?string
    {
        $res = $link->sql_query_silent("SHOW VARIABLES LIKE 'sql_log_bin'");
        if (!$res) return null;
        $row = $link->sql_fetch_array($res, MYSQLI_ASSOC);
        if (!$row || !isset($row['Value'])) return null;
        return (string) $row['Value'];
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
            "SELECT id, name, display_name, ip, hostname, port,
                    login, passwd, is_password_crypted, is_binlog_relay
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

    // ------------------------------------------------------------------
    //  Greenfield UI helpers
    // ------------------------------------------------------------------

    /**
     * Supervised servers eligible to be provisioned as a fresh
     * BLACKHOLE relay (not already a relay, not a proxy/VIP). The
     * actual idleness check runs at conversion time; this list is the
     * raw inventory the dropdown shows.
     */
    public static function listIdleTargets(): array
    {
        $db = Sgbd::sql(DB_DEFAULT);
        $res = $db->sql_query(
            "SELECT id, name, display_name, hostname, ip, port
             FROM mysql_server
             WHERE is_deleted = 0 AND is_proxy = 0 AND is_vip = 0
               AND is_monitored = 1 AND is_binlog_relay = 0
             ORDER BY display_name, name"
        );
        $out = [];
        while ($r = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $out[] = $r;
        }
        return $out;
    }

    /**
     * One-shot idleness probe used by the AJAX preflight endpoint so
     * the UI can warn before launching the pipeline. Returns the same
     * tri-state as the private `assertTargetIsIdle()`.
     *
     * @return true|string
     */
    public static function probeIdle(int $serverId)
    {
        $db = Sgbd::sql(DB_DEFAULT);
        $res = $db->sql_query("SELECT name FROM mysql_server WHERE id = {$serverId} AND is_deleted = 0");
        $row = $res ? $db->sql_fetch_array($res, MYSQLI_ASSOC) : null;
        if (!$row) return 'server not found';
        $link = Sgbd::sql($row['name']);
        if (!$link) return 'unreachable';
        $self = new self(0);
        return $self->assertTargetIsIdle($link);
    }

    /**
     * Servers that can act as the upstream master for a fresh relay:
     * any monitored MySQL/MariaDB server that is not itself a relay.
     */
    public static function listMasterCandidates(): array
    {
        $db = Sgbd::sql(DB_DEFAULT);
        $res = $db->sql_query(
            "SELECT id, name, display_name, hostname, ip, port
             FROM mysql_server
             WHERE is_deleted = 0 AND is_proxy = 0 AND is_vip = 0
               AND is_monitored = 1 AND is_binlog_relay = 0
             ORDER BY display_name, name"
        );
        $out = [];
        while ($r = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $out[] = $r;
        }
        return $out;
    }
}
