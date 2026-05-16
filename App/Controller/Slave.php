<?php
/*
 * j'ai ajouté un mail automatique en cas d'erreur ou de manque sur une PK
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use App\Library\Extraction;
use App\Library\Extraction2;
use App\Library\Mysql;
use App\Library\Debug;
use App\Library\ServerCapabilities;
use App\Controller\Tunnel;
use App\Library\Security\CsrfGuard;
use \Glial\Sgbd\Sgbd;
use \App\Library\Chiffrement;
use \App\Library\DryRun;
use \App\Library\BinlogAnalyzer;
use Glial\Security\Csrf;

/**
 * Class responsible for slave workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Slave extends Controller
{

    use \App\Library\Filter;
    const BACKUP_TEMP = "/backup/";
    private const SLAVE_BINLOG_ANALYSIS_START_CSRF_SCOPE = 'slave.binlog_analysis.start';
    private const SLAVE_SETUP_SOURCE_CSRF_SCOPE = 'slave.setup_source';
    private const SLAVE_BINLOG_ROW_IMAGE_SET_CSRF_SCOPE = 'slave.binlog_row_image.set';
    private const SLAVE_REPLICATION_VARIABLE_SET_CSRF_SCOPE = 'slave.replication_variable.set';

    /** Allow-list for `binlog_row_image` (case-normalised on input). */
    private const BINLOG_ROW_IMAGE_VALUES = ['FULL', 'MINIMAL', 'NOBLOB'];

    /**
     * Issue #1198 — whitelist of replication-related GLOBAL variables
     * the operator can mutate from /slave/show via the generic
     * setReplicationVariable action.
     *
     * Each entry says how to validate the input + how to render the
     * SQL literal:
     *   - `type=enum` + `values=[…]`  → quoted single-quoted literal
     *   - `type=int`  + `min`/`max`   → bare integer literal
     *
     * `relay_log_recovery` is intentionally absent: it can only be
     * changed at server start (my.cnf + restart). The view tags it
     * config-only so the operator does not look for an editor that
     * would not exist.
     *
     * @return array<string,array{type:string,values?:array<int,string>,min?:int,max?:int,aliases?:array<int,string>}>
     */
    public static function replicationVariableWhitelist(): array
    {
        return [
            'sync_binlog'                              => ['type' => 'int',  'min' => 0, 'max' => 100000],
            'innodb_flush_log_at_trx_commit'           => ['type' => 'enum', 'values' => ['0', '1', '2']],
            'binlog_format'                            => ['type' => 'enum', 'values' => ['ROW', 'MIXED', 'STATEMENT']],
            'binlog_row_image'                         => ['type' => 'enum', 'values' => ['FULL', 'MINIMAL', 'NOBLOB']],
            'replica_preserve_commit_order'            => ['type' => 'enum', 'values' => ['ON', 'OFF'], 'aliases' => ['slave_preserve_commit_order']],
            'slave_preserve_commit_order'              => ['type' => 'enum', 'values' => ['ON', 'OFF']],
            'source_info_repository'                   => ['type' => 'enum', 'values' => ['TABLE', 'FILE'], 'aliases' => ['master_info_repository']],
            'master_info_repository'                   => ['type' => 'enum', 'values' => ['TABLE', 'FILE']],
            'relay_log_info_repository'                => ['type' => 'enum', 'values' => ['TABLE', 'FILE']],
            'super_read_only'                          => ['type' => 'enum', 'values' => ['ON', 'OFF']],
            'binlog_commit_wait_count'                 => ['type' => 'int',  'min' => 0, 'max' => 100000],
            'binlog_commit_wait_usec'                  => ['type' => 'int',  'min' => 0, 'max' => 10000000],
            'binlog_group_commit_sync_no_delay_count'  => ['type' => 'int',  'min' => 0, 'max' => 100000],
            'binlog_group_commit_sync_delay'           => ['type' => 'int',  'min' => 0, 'max' => 10000000],
        ];
    }

    private function getReplicationLagVariables(): array
    {
        return ['slave::seconds_behind_master', 'slave::seconds_behind_source'];
    }

    private function normalizeReplicationLagDisplayRows(array $rows): array
    {
        foreach ($rows as $idMysqlServer => $connections) {
            foreach ($connections as $connectionName => $row) {
                $sourceLag = trim((string)($row['seconds_behind_source'] ?? ''));
                $masterLag = trim((string)($row['seconds_behind_master'] ?? ''));

                if ($sourceLag !== '') {
                    $rows[$idMysqlServer][$connectionName]['seconds_behind_master'] = $sourceLag;
                } elseif (!isset($rows[$idMysqlServer][$connectionName]['seconds_behind_master'])) {
                    $rows[$idMysqlServer][$connectionName]['seconds_behind_master'] = $masterLag;
                }
            }
        }

        return $rows;
    }

    private static function sanitizeConnectionName(string $name): string
    {
        // MySQL/MariaDB connection names: alphanumeric, underscore, hyphen, dot
        return preg_replace('/[^a-zA-Z0-9_\-.]/', '', $name);
    }

    /**
     * Reject any positional segment that looks like a Glial key:value
     * (e.g. `ajax:true`) — those land in `$param` alongside positional
     * args and would otherwise be misread as a `connection_name`. (#816)
     */
    public static function extractConnectionNameParam(string $value): string
    {
        if ($value === '' || strpos($value, ':') !== false) {
            return '';
        }
        return self::sanitizeConnectionName($value);
    }

    /**
     * Build the SQL for STOP/START replication, fork-aware.
     * MariaDB:                STOP SLAVE 'conn_name'
     * MySQL  < 8.0.22:        STOP SLAVE FOR CHANNEL 'conn_name'
     * MySQL >= 8.0.22:        STOP REPLICA FOR CHANNEL 'conn_name'
     *
     * REPLICA terminology (gh#144) was added in MySQL 8.0.22 ; older MySQL
     * (5.7.x and 8.0.0-8.0.21) only understand SLAVE.
     */
    private static function buildReplicationCmd(string $verb, bool $isMariaDB, bool $useReplica, string $connectionName = ''): string
    {
        // verb = 'STOP' or 'START'
        if ($isMariaDB) {
            if (empty($connectionName)) {
                return "$verb SLAVE";
            }
            return "$verb SLAVE '$connectionName'";
        }

        $keyword = $useReplica ? 'REPLICA' : 'SLAVE';
        if (empty($connectionName)) {
            return "$verb $keyword";
        }
        return "$verb $keyword FOR CHANNEL '$connectionName'";
    }

    /**
     * Returns true iff the server expects the new MySQL "REPLICA" replication
     * terminology (added in MySQL 8.0.22). MariaDB and pre-8.0.22 MySQL must
     * keep using SLAVE / SHOW SLAVE STATUS / RESET SLAVE / START SLAVE UNTIL.
     * Public so views and other libraries can route through the same gate
     * without duplicating it (#830).
     *
     * @param mixed $db Glial DB handle exposing getServerType() and getVersion()
     */
    public static function usesReplicaSyntax($db): bool
    {
        return self::supportsReplicaStatusSyntax(
            (string) $db->getServerType(),
            (string) $db->getVersion()
        );
    }

    /**
     * Pure-data variant of {@see usesReplicaSyntax} for callers that only
     * have the type/version strings (e.g. the show.view.php template
     * already has `$data['server_type']` / `$data['server_version']`).
     */
    public static function supportsReplicaStatusSyntax(string $serverType, string $serverVersion): bool
    {
        if (stripos($serverType, 'mariadb') !== false) {
            return false;
        }
        return version_compare($serverVersion, '8.0.22', '>=');
    }

    /**
     * Build the right `SHOW {SLAVE|REPLICA} STATUS [FOR CHANNEL '<name>']`
     * SQL for the given server. Centralizes the gating that was
     * duplicated across Slave.php / show.view.php / BinlogAnalyzer.php
     * and the buggy Glial `Mysql::isSlave()` (#830).
     *
     * @param string      $serverType    e.g. "MySQL", "Percona", "MariaDB"
     * @param string      $serverVersion full version string (e.g. "5.6.51-91.0-log")
     * @param string|null $channel       optional connection_name / channel name;
     *                                   already validated by the caller
     */
    public static function buildShowReplicaStatusSql(string $serverType, string $serverVersion, ?string $channel = null): string
    {
        $isMariaDB = stripos($serverType, 'mariadb') !== false;
        $channel = $channel === null ? '' : self::sanitizeConnectionName($channel);

        if ($isMariaDB) {
            // MariaDB has its own per-connection variant. Empty channel
            // returns the default SHOW SLAVE STATUS.
            if ($channel === '') {
                return 'SHOW SLAVE STATUS';
            }
            return "SHOW SLAVE '" . $channel . "' STATUS";
        }

        $keyword = self::supportsReplicaStatusSyntax($serverType, $serverVersion) ? 'REPLICA' : 'SLAVE';
        if ($channel === '') {
            return "SHOW $keyword STATUS";
        }
        return "SHOW $keyword STATUS FOR CHANNEL '" . $channel . "'";
    }

    /**
     * Replace `$db->isSlave()` (Glial's buggy version) with a single
     * call that uses the same gate as the rest of PmaControl (#830).
     * Returns the rows of `SHOW {SLAVE|REPLICA} STATUS` (potentially
     * many on multi-source replicas), already normalized via Glial's
     * `normalizeReplicationStatusRow` so callers can keep reading
     * either Slave_* or Replica_* keys.
     *
     * @return list<array<string,mixed>>
     */
    public static function fetchReplicaStatusRows($db): array
    {
        $sql = self::buildShowReplicaStatusSql(
            (string) $db->getServerType(),
            (string) $db->getVersion(),
            null
        );
        $res = $db->sql_query_silent($sql);
        if (!$res) {
            return [];
        }
        $rows = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            if (method_exists('\\Glial\\Sgbd\\Sql\\Mysql\\Mysql', 'normalizeReplicationStatusRow')) {
                $row = \Glial\Sgbd\Sql\Mysql\Mysql::normalizeReplicationStatusRow($row);
            }
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Enable GTID on a MySQL 8+ server with SET PERSIST for durability.
     * Walks gtid_mode through: OFF → OFF_PERMISSIVE → ON_PERMISSIVE → ON.
     * Skips silently if the server is MariaDB (GTID works differently there).
     */
    private static function enableMySQLGtid($db): void
    {
        // MariaDB has no gtid_mode variable — GTID is always available via MASTER_USE_GTID
        if (stripos($db->getServerType(), 'mariadb') !== false) {
            return;
        }

        $res = $db->sql_query("SELECT @@GLOBAL.gtid_mode AS val");
        $gtidMode = '';
        if ($res && $row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $gtidMode = strtoupper($row['val']);
        }

        if ($gtidMode === 'ON') {
            return;
        }

        // enforce_gtid_consistency must be ON before gtid_mode can go to ON
        $db->sql_query("SET PERSIST enforce_gtid_consistency = WARN;");
        $db->sql_query("SET PERSIST enforce_gtid_consistency = ON;");

        if ($gtidMode === 'OFF') {
            $db->sql_query("SET PERSIST gtid_mode = OFF_PERMISSIVE;");
        }
        if (in_array($gtidMode, ['OFF', 'OFF_PERMISSIVE'])) {
            $db->sql_query("SET PERSIST gtid_mode = ON_PERMISSIVE;");
        }
        $db->sql_query("SET PERSIST gtid_mode = ON;");
    }

    /**
     * Disable GTID on a MySQL 8+ server with SET PERSIST for durability.
     * Walks gtid_mode through: ON → ON_PERMISSIVE → OFF_PERMISSIVE → OFF.
     * Skips silently if the server is MariaDB.
     */
    private static function disableMySQLGtid($db): void
    {
        if (stripos($db->getServerType(), 'mariadb') !== false) {
            return;
        }

        $res = $db->sql_query("SELECT @@GLOBAL.gtid_mode AS val");
        $gtidMode = '';
        if ($res && $row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $gtidMode = strtoupper($row['val']);
        }

        if ($gtidMode === 'OFF') {
            return;
        }

        if ($gtidMode === 'ON') {
            $db->sql_query("SET PERSIST gtid_mode = ON_PERMISSIVE;");
        }
        if (in_array($gtidMode, ['ON', 'ON_PERMISSIVE'])) {
            $db->sql_query("SET PERSIST gtid_mode = OFF_PERMISSIVE;");
        }
        $db->sql_query("SET PERSIST gtid_mode = OFF;");
        $db->sql_query("SET PERSIST enforce_gtid_consistency = OFF;");
    }

    private static function getMySQLParallelWorkersVariable($db): string
    {
        return version_compare((string) $db->getVersion(), '8.0.26', '>=')
            ? 'replica_parallel_workers'
            : 'slave_parallel_workers';
    }

    private static function getParallelReplicationSettingsFromTimeSeries(int $idMysqlServer): array
    {
        $display = Extraction::display([
            'variables::replica_parallel_workers',
            'variables::slave_parallel_workers',
            'variables::slave_parallel_threads',
            'variables::slave_parallel_mode',
        ], [$idMysqlServer]);

        return self::normalizeParallelReplicationSettings($display[$idMysqlServer][''] ?? []);
    }

    /**
     * Issue #1185 — pull the durability + crash-safety variables from
     * the Extraction time-series so the slave/show Actions column can
     * render them with severity badges and tooltips.
     *
     * @return list<array{name:string,value:string,level:string,label:string,tooltip:string}>
     */
    /**
     * @return list<array{
     *   name:string, value:string, level:string, label:string, tooltip:string,
     *   master: array{value:string, level:string, label:string, tooltip:string}
     * }>
     */
    private static function buildDurabilityRows(int $idMysqlServer, int $parallelThreads, ?int $idMaster = null): array
    {
        $keys = \App\Library\SlaveDurability::variableKeys();
        $extractionKeys = [];
        foreach ($keys as $k) {
            $extractionKeys[] = 'variables::' . $k;
        }
        // Both legacy aliases must be probed too so MySQL 5.7 / MariaDB
        // values land in the map.
        foreach (['slave_preserve_commit_order', 'master_info_repository'] as $legacy) {
            $extractionKeys[] = 'variables::' . $legacy;
        }

        $ids = [$idMysqlServer];
        if ($idMaster !== null && $idMaster > 0 && $idMaster !== $idMysqlServer) {
            $ids[] = $idMaster;
        }
        $display = Extraction::display($extractionKeys, $ids);

        $slaveValues  = is_array($display[$idMysqlServer][''] ?? null) ? $display[$idMysqlServer][''] : [];
        $masterValues = ($idMaster !== null && is_array($display[$idMaster][''] ?? null))
            ? $display[$idMaster]['']
            : [];

        $slaveRows  = \App\Library\SlaveDurability::rows(
            $slaveValues,
            ['parallel_threads' => $parallelThreads]
        );
        // We do not know the master's parallel_threads cheaply, so pass
        // 0 — that turns the conditional preserve_commit_order rule
        // into a plain warning rather than a hard risk on the master
        // side, which is the right default ("we cannot prove it's a
        // risk from here").
        $masterRows = \App\Library\SlaveDurability::rows(
            $masterValues,
            ['parallel_threads' => 0]
        );

        $masterByName = [];
        foreach ($masterRows as $mr) {
            $masterByName[$mr['name']] = $mr;
        }

        $unknownCell = [
            'value'   => '',
            'level'   => \App\Library\SlaveDurability::LEVEL_UNKNOWN,
            'label'   => 'n/a',
            'tooltip' => 'Master not monitored or value not yet collected from the time-series.',
        ];

        $paired = [];
        foreach ($slaveRows as $sr) {
            $mr = $masterByName[$sr['name']] ?? null;
            $paired[] = $sr + [
                'master' => $mr ? [
                    'value'   => $mr['value'],
                    'level'   => $mr['level'],
                    'label'   => $mr['label'],
                    'tooltip' => $mr['tooltip'],
                ] : $unknownCell,
            ];
        }
        return $paired;
    }

    /**
     * Issue #1196 — build the binlog group-commit rows for the
     * slave/show *Binlog group commit* card. Reads the relevant
     * variables for both servers from the existing Extraction
     * time-series (no extra MySQL connection).
     *
     * @return list<array{
     *   label: string,
     *   tooltip: string,
     *   slave: array{var:string, value:string, level:string, label:string},
     *   master: array{var:string, value:string, level:string, label:string},
     *   drift: bool,
     *   drift_reason: string,
     * }>
     */
    private static function buildBinlogGroupCommitRows(
        int $idSlave,
        ?int $idMaster,
        string $slaveFamily,
        string $masterFamily
    ): array {
        $keys = \App\Library\BinlogGroupCommit::variableKeys();
        $extractionKeys = [];
        foreach ($keys as $k) {
            $extractionKeys[] = 'variables::' . $k;
        }

        $ids = [$idSlave];
        if ($idMaster !== null && $idMaster > 0 && $idMaster !== $idSlave) {
            $ids[] = $idMaster;
        }
        $display = Extraction::display($extractionKeys, $ids);

        $slaveValues  = $display[$idSlave][''] ?? [];
        $masterValues = ($idMaster && isset($display[$idMaster][''])) ? $display[$idMaster][''] : [];

        return \App\Library\BinlogGroupCommit::rows(
            is_array($slaveValues)  ? $slaveValues  : [],
            is_array($masterValues) ? $masterValues : [],
            $slaveFamily,
            $masterFamily
        );
    }

    /**
     * Issue #1194 — coarse MySQL-family detection.
     *
     * Inspects `version_comment` (e.g. "MariaDB Server", "Source distribution",
     * "Percona Server (GPL), Release 28") and falls back on `version`
     * itself. Returns one of `'mariadb'`, `'mysql'`, `'unknown'`.
     *
     * `'mysql'` is a coarse bucket on purpose: Percona Server, Oracle
     * MySQL and vanilla MySQL share the same GTID format, so they
     * reinforce each other for replication-compat purposes. MariaDB
     * has its own GTID format that is incompatible with the MySQL
     * family.
     */
    private static function detectMysqlFamily(string $versionComment, string $version): string
    {
        $haystack = strtolower(trim($versionComment . ' ' . $version));
        $bareVersion = strtolower(trim($version));
        if ($haystack === '') {
            return 'unknown';
        }
        if (strpos($haystack, 'mariadb') !== false) {
            return 'mariadb';
        }
        if (strpos($haystack, 'percona') !== false
            || strpos($haystack, 'mysql') !== false
            || strpos($haystack, 'source distribution') !== false   // Oracle MySQL builds report this
            || preg_match('/^(?:5\.|8\.)\d/', $bareVersion) === 1) {
            return 'mysql';
        }
        return 'unknown';
    }

    /**
     * Issue #1194 — for the GTID action group on /slave/show, decide
     * whether the Activate button should be greyed out because the
     * slave and the master live in incompatible MySQL families.
     *
     * @return array{compatible:bool,reason:string,slave_family:string,master_family:string}
     */
    private static function evaluateGtidActivationCompatibility(
        string $slaveServerType,
        ?int $masterId
    ): array {
        $slaveFamily = self::detectMysqlFamily($slaveServerType, '');
        $masterFamily = 'unknown';

        // Wrap the time-series lookup in try/catch — an unreachable
        // master, stale credentials or a missing variables row must
        // not break /slave/show. Fall back to 'unknown' (treated as
        // "compatible" / fail-open here, but the activateGtid action
        // applies the same check live with the real DB handles so
        // the actual mutation still refuses cross-family on confirm).
        if ($masterId !== null && $masterId > 0) {
            try {
                $display = Extraction::display(
                    ['variables::version_comment', 'variables::version'],
                    [$masterId]
                );
                $vals = $display[$masterId][''] ?? [];
                if (is_array($vals)) {
                    $masterFamily = self::detectMysqlFamily(
                        (string) ($vals['version_comment'] ?? ''),
                        (string) ($vals['version'] ?? '')
                    );
                }
            } catch (\Throwable $e) {
                $masterFamily = 'unknown';
            }
        }

        $compatible = true;
        $reason = '';
        if ($slaveFamily !== 'unknown' && $masterFamily !== 'unknown' && $slaveFamily !== $masterFamily) {
            $compatible = false;
            $reason = sprintf(
                'GTID is incompatible across MySQL families: the slave runs %s and the master runs %s. '
                . 'Activating GTID would only configure one side correctly and break replication. '
                . 'Use file+position replication, or migrate one side first.',
                ucfirst($slaveFamily),
                ucfirst($masterFamily)
            );
        }

        return [
            'compatible'    => $compatible,
            'reason'        => $reason,
            'slave_family'  => $slaveFamily,
            'master_family' => $masterFamily,
        ];
    }

    /**
     * Issue #1192 — fast-path data prep for `/slave/show/<id>/__new__/`.
     *
     * The view's __new__ branch only renders a source-setup form. It
     * needs only local PmaControl data: cached replication-channel tabs,
     * the available-server picker list and the setupSource CSRF token.
     * We populate the rest of `$data` with defaults the view already
     * tolerates (it reads with `?? ''` / `?? []` guards) so the existing
     * branch keeps rendering without a single SHOW REPLICA STATUS or
     * live MySQL connection to the target server.
     *
     * @param array<string,mixed> $data  data array passed by reference,
     *        already pre-populated with id_mysql_server + replication_name.
     * @param array<string,mixed> $session $_SESSION reference (so the
     *        helper stays unit-testable without touching globals).
     */
    private static function prepareNewReplicationFormData(object $db, array &$data, int $idMysqlServer, array &$session): void
    {
        // Defaults the view tolerates — keeps the header / future
        // partials happy without firing target-server work.
        $data['slave']            = [];
        $data['parallel_threads'] = 0;
        $data['parallel_mode']    = null;
        $data['durability_rows']  = [];
        $data['server']           = [];
        $data['all_connections']  = self::buildReplicationConnectionTabsFromCachedMetrics((int) $idMysqlServer);
        $data['server_type']      = '';
        $data['server_version']   = '';
        $data['cpu_count']        = 0;
        $data['binlog_gap']       = null;
        $data['sparkline']        = '';
        $data['mysql_server_specify'] = [];
        $data['id_slave']         = [$idMysqlServer];
        $data['master_id']        = 0;
        $data['db_on_master']     = [];

        // available_servers — local pmacontrol DB SELECT, fast.
        $sql = "SELECT a.id, a.display_name, a.ip, a.port, b.libelle AS environment
                FROM mysql_server a
                INNER JOIN environment b ON a.id_environment = b.id
                WHERE a.is_deleted = 0 AND a.id != " . $idMysqlServer . "
                ORDER BY b.libelle, a.display_name";
        $res = $db->sql_query($sql);
        $data['available_servers'] = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['available_servers'][] = $row;
        }

        // CSRF tokens — only setupSource is actually used by the form
        // (the others stay empty so the durability picker / launch
        // button do not show up but the view renders cleanly).
        $data['slave_binlog_analysis_start_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['slave_binlog_analysis_start_csrf_token'] = '';
        $data['slave_binlog_row_image_set_csrf_field']  = Csrf::DEFAULT_FIELD;
        $data['slave_binlog_row_image_set_csrf_token']  = '';
        $data['binlog_row_image_values'] = self::BINLOG_ROW_IMAGE_VALUES;
        $data['slave_replication_variable_set_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['slave_replication_variable_set_csrf_token'] = '';
        $data['replication_variable_whitelist'] = self::replicationVariableWhitelist();

        if (self::normalizeSetupSourceServerId($idMysqlServer) !== null) {
            $data['slave_setup_source_csrf_field'] = Csrf::DEFAULT_FIELD;
            $data['slave_setup_source_csrf_token'] = Csrf::issueToken($session, self::SLAVE_SETUP_SOURCE_CSRF_SCOPE);
        }
    }

    /**
     * Build the source-tab strip from cached PmaControl metrics. This keeps
     * `/slave/show/<id>/__new__/` fast and independent from target-server
     * availability while preserving the operator's channel navigation.
     *
     * @return list<array{name:string,health:string,lag:mixed}>
     */
    private static function buildReplicationConnectionTabsFromCachedMetrics(int $idMysqlServer): array
    {
        $cached = Extraction2::display([
            'slave::slave_io_running',
            'slave::slave_sql_running',
            'slave::replica_io_running',
            'slave::replica_sql_running',
            'slave::seconds_behind_master',
            'slave::seconds_behind_source',
            'slave::last_io_error',
            'slave::last_sql_error',
            'slave::last_error',
        ], [$idMysqlServer]);

        $channels = $cached[$idMysqlServer]['@slave'] ?? [];
        if (!is_array($channels)) {
            return [];
        }

        return self::buildReplicationConnectionTabs($channels);
    }

    /**
     * @param array<int|string,array<string,mixed>> $channels Rows from either
     *        SHOW SLAVE/REPLICA STATUS or cached Extraction2 @slave metrics.
     * @return list<array{name:string,health:string,lag:mixed}>
     */
    private static function buildReplicationConnectionTabs(array $channels): array
    {
        $tabs = [];

        foreach ($channels as $key => $channel) {
            if (!is_array($channel)) {
                continue;
            }

            $name = self::firstReplicationFieldValue($channel, ['Connection_name', 'Channel_Name', 'connection_name']);
            if ($name === null && is_string($key)) {
                $name = $key;
            }

            $lag = self::replicationTabLag($channel);
            $tabs[] = [
                'name'   => (string) ($name ?? ''),
                'health' => self::replicationTabHealth($channel, $lag),
                'lag'    => $lag,
            ];
        }

        return $tabs;
    }

    /**
     * @param array<string,mixed> $channel
     * @param list<string> $keys
     */
    private static function firstReplicationFieldValue(array $channel, array $keys)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $channel)) {
                return $channel[$key];
            }
        }

        return null;
    }

    /** @param array<string,mixed> $channel */
    private static function replicationTabLag(array $channel)
    {
        foreach (['seconds_behind_source', 'Seconds_Behind_Source', 'seconds_behind_master', 'Seconds_Behind_Master'] as $key) {
            if (!array_key_exists($key, $channel)) {
                continue;
            }

            $value = trim((string) $channel[$key]);
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    /** @param array<string,mixed> $channel */
    private static function replicationTabHasError(array $channel): bool
    {
        foreach (['last_sql_error', 'Last_SQL_Error', 'last_io_error', 'Last_IO_Error', 'last_error', 'Last_Error'] as $key) {
            if (array_key_exists($key, $channel) && trim((string) $channel[$key]) !== '') {
                return true;
            }
        }

        return false;
    }

    /** @param array<string,mixed> $channel */
    private static function replicationTabHealth(array $channel, $lag): string
    {
        $io = self::firstReplicationFieldValue($channel, ['replica_io_running', 'Replica_IO_Running', 'slave_io_running', 'Slave_IO_Running']) ?? 'No';
        $sql = self::firstReplicationFieldValue($channel, ['replica_sql_running', 'Replica_SQL_Running', 'slave_sql_running', 'Slave_SQL_Running']) ?? 'No';

        if ($io !== 'Yes' && $sql !== 'Yes') {
            return 'stopped';
        }
        if ($io !== 'Yes' || $sql !== 'Yes' || self::replicationTabHasError($channel)) {
            return 'critical';
        }
        if ($lag === null || $lag === 'NULL') {
            return 'critical';
        }
        if ((int) $lag > 60) {
            return 'warning';
        }
        if ((int) $lag > 0) {
            return 'behind';
        }

        return 'ok';
    }

    private static function normalizeParallelReplicationSettings(array $values): array
    {
        $threads = 0;
        foreach (['replica_parallel_workers', 'slave_parallel_workers', 'slave_parallel_threads'] as $key) {
            if (isset($values[$key]) && is_numeric($values[$key])) {
                $threads = max(0, (int) $values[$key]);
                break;
            }
        }

        $mode = isset($values['slave_parallel_mode']) && is_scalar($values['slave_parallel_mode'])
            ? trim((string) $values['slave_parallel_mode'])
            : '';

        return [
            'parallel_threads' => $threads,
            'parallel_mode' => $mode !== '' ? $mode : null,
        ];
    }

    private function normalizeReplicationLagGraphRows(iterable $rows): array
    {
        return $this->normalizeReplicationLagRows(
            $rows,
            static function (array $row): string {
                // Include day in key when present (groupbyday mode) so that
                // multi-day extractions keep one series per channel per day.
                $day = $row['day'] ?? '';
                return $row['id_mysql_server'].'|'.($row['connection_name'] ?? '').'|'.$day;
            }
        );
    }

    private function normalizeReplicationLagPointRows(iterable $rows): array
    {
        return $this->normalizeReplicationLagRows(
            $rows,
            static function (array $row): string {
                return $row['id_mysql_server'].'|'.($row['connection_name'] ?? '').'|'.($row['date'] ?? '');
            }
        );
    }

    private function normalizeReplicationLagRows(iterable $rows, callable $buildKey): array
    {
        $normalized = [];

        foreach ($rows as $row) {
            $key = $buildKey($row);
            $metricName = $this->getReplicationLagMetricName((int)($row['id_ts_variable'] ?? 0));

            if (!isset($normalized[$key])) {
                $normalized[$key] = $row;
                continue;
            }

            if ($metricName === 'seconds_behind_source') {
                $normalized[$key] = $row;
                continue;
            }

            $existingMetricName = $this->getReplicationLagMetricName((int)($normalized[$key]['id_ts_variable'] ?? 0));
            if ($existingMetricName !== 'seconds_behind_source') {
                $normalized[$key] = $row;
            }
        }

        return array_values($normalized);
    }

    private function getReplicationLagMetricName(int $idTsVariable): string
    {
        return Extraction::$variable[$idTsVariable]['name'] ?? '';
    }

    private function buildBinlogAnalysisLagData(array $lagRows): array
    {
        $lagData = [];

        foreach ($this->normalizeReplicationLagPointRows($lagRows) as $lagRow) {
            $lagData[] = ['ts' => $lagRow['date'], 'lag' => (int) $lagRow['value']];
        }

        return $lagData;
    }

/**
 * Render slave state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/slave/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index()
    {

        $this->title = '<i class="fa fa-sitemap"></i> '.__("Master / Slave");

        $db = Sgbd::sql(DB_DEFAULT);

        $this->di['js']->code_javascript('
        $(function () {
  $(\'a[data-toggle="tooltip"]\').tooltip();  /* tooltip("show") */
  $(\'[data-toggle="tooltip"]\').tooltip();
})');

        $data['slave'] = Extraction::display(array_merge(
            ["slave::master_host", "slave::master_port", "slave::slave_io_running",
                "slave::slave_sql_running", "slave::replicate_do_db", "slave::replicate_ignore_db", "slave::last_io_errno", "slave::last_io_error",
                "slave::last_sql_error", "slave::last_sql_errno"],
            $this->getReplicationLagVariables()
        ));
        $data['slave'] = $this->normalizeReplicationLagDisplayRows($data['slave']);


                

        /* besoin de testé avec les thread (trouver autre chose)
          //order by master host
          function invenDescSort($item1, $item2)
          {
          if (substr($item1['']['master_host'], 6)
          == substr($item2['']['master_host'], 6)) return 0;
          return (substr($item1['']['master_host'], 6)
          < substr($item2['']['master_host'], 6)) ? 1 : -1;
          }
          usort($data['slave'], 'invenDescSort');
         */

        $data['info_server'] = Extraction::display(array("variables::hostname", "variables::is_proxysql", "mysql_available"));

        

        $sql = "SELECT a.*, c.libelle as client,d.libelle as environment,d.`class`  FROM mysql_server a
                 INNER JOIN client c on c.id = a.id_client
                 INNER JOIN environment d on d.id = a.id_environment
                 WHERE 1 ".self::getFilter()."
                 ORDER by `name`;";

        $res = $db->sql_query($sql);

        $data['server'] = array();
        while ($arr            = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {


            $data['server']['master'][$arr['ip'].':'.$arr['port']] = $arr;
            $data['server']['master'][$arr['id']]                  = $arr;
            $data['server']['slave'][$arr['id']]                   = $arr;
        }

        $data['tunnel_details'] = $this->getTunnelDetails();

        /*
          $res2 = Extraction::extract(array("slave::seconds_behind_master"), array(), "1 hour", false, true);
          $slaves = array();
          while($slave = $db->sql_fetch_array($res2, MYSQLI_ASSOC))
          {
          $slaves[] = $slave;
          }
          $this->generateGraph($slaves);
         */

        $slaves = Extraction::extract($this->getReplicationLagVariables(), array(), "1 hour", false, true);
        $slaves = $this->normalizeReplicationLagGraphRows($slaves ?: []);
        $this->generateGraph($slaves);

        if (!empty($slaves)) {
            foreach ($slaves as $slave) {
                $data['graph'][$slave['id_mysql_server']][$slave['connection_name']]             = $slave;
                $data['graph'][$slave['id_mysql_server']][$slave['connection_name']]['id_graph'] = $slave['id_mysql_server'].crc32($slave['connection_name']);
                $data['server']['idgraph'][$slave['id_mysql_server']][$slave['connection_name']] = $slave['id_mysql_server'].crc32($slave['connection_name']);

                $this->hydrateMasterFromAliasDns($data, $slave);
            }
        }

        $this->set('data', $data);
    }

    private function getTunnelDetails(): array
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT local_host, local_port, remote_host, remote_port, servers_jump
                FROM ssh_tunnel
                WHERE date_end IS NULL";

        $res = $db->sql_query($sql);
        $details = [];

        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $local = trim((string)$row['local_host']).':'.(int)$row['local_port'];
            $remote = trim((string)$row['remote_host']).':'.(int)$row['remote_port'];
            $jumps = json_decode((string)$row['servers_jump'], true);

            if (!is_array($jumps)) {
                $jumps = [];
            }

            $hops = [$local];

            foreach ($jumps as $jump) {
                if (!is_array($jump)) {
                    continue;
                }

                $jumpHost = trim((string)($jump['ip'] ?? $jump['host'] ?? $jump['remote_host'] ?? ''));
                $jumpPort = (int)($jump['port'] ?? 22);

                if ($jumpHost === '') {
                    continue;
                }

                $hops[] = $jumpHost.':'.$jumpPort;
            }

            if (empty($hops) || end($hops) !== $remote) {
                $hops[] = $remote;
            }

            $details[$local] = [
                'local' => $local,
                'remote' => $remote,
                'hops' => $hops,
            ];
        }

        return $details;
    }

/**
 * Handle slave state through `generateGraph`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $slaves Input value for `slaves`.
 * @phpstan-param mixed $slaves
 * @psalm-param mixed $slaves
 * @return void Returned value for generateGraph.
 * @phpstan-return void
 * @psalm-return void
 * @see self::generateGraph()
 * @example /fr/slave/generateGraph
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function generateGraph($slaves)
    {
        // Issue #742: per-replica sparkline drawn directly on the 160×17
        // canvas with the raw 2D context. Chart.js v4 silently fails to lay
        // out a chart in a 17px-tall canvas (axis/legend hidden, but the
        // internal layout box still collapses), and the time-scale variant
        // additionally needed chartjs-adapter-moment. Native canvas drawing
        // sidesteps both problems and removes a few hundred kB of JS.
        if (empty($slaves)) {
            return;
        }

        foreach ($slaves as $slave) {
            $values = self::extractSparklineYValues((string) ($slave['graph'] ?? ''));
            if (empty($values)) {
                continue;
            }
            $valuesJs = '[' . implode(',', $values) . ']';
            $canvasId = 'myChart' . $slave['id_mysql_server'] . crc32($slave['connection_name']);

            $this->di['js']->code_javascript('
try{(function(){
var id="'.$canvasId.'";
var c=document.getElementById(id);
if(!c){console.warn("[spark] canvas missing",id);return;}
if(!c.getContext){console.warn("[spark] no getContext",id);return;}
var d='.$valuesJs.';
var ctx=c.getContext("2d");
var w=c.width,h=c.height;
var rect=c.getBoundingClientRect();
console.log("[spark]",id,"w=",w,"h=",h,"rect=",rect.width+"x"+rect.height,"n=",d.length);
ctx.clearRect(0,0,w,h);
var n=d.length,mx=0;
for(var i=0;i<n;i++){var v=d[i];if(v!==null&&v>mx)mx=v;}
if(mx<=0)mx=1;
function px(i){return n>1?(i/(n-1))*(w-1):0;}
function py(v){if(v===null)return null;return (h-1)-(v/mx)*(h-1);}
ctx.beginPath();ctx.moveTo(0,h);
var started=false;
for(var i=0;i<n;i++){var y=py(d[i]);if(y===null)continue;var x=px(i);if(!started){ctx.lineTo(x,h);ctx.lineTo(x,y);started=true;}else{ctx.lineTo(x,y);}}
ctx.lineTo(w,h);ctx.closePath();
ctx.fillStyle="rgba(22,40,90,0.3)";ctx.fill();
ctx.beginPath();started=false;
for(var i=0;i<n;i++){var y=py(d[i]);if(y===null){started=false;continue;}var x=px(i);if(!started){ctx.moveTo(x,y);started=true;}else{ctx.lineTo(x,y);}}
ctx.strokeStyle="rgba(0,0,0,1)";ctx.lineWidth=1;ctx.stroke();
})();}catch(e){console.error("[spark] failed",e);}
');
        }
    }

    /**
     * Pull the y values out of the "{x:new Date('…'),y:N},{x:…,y:M}" payload
     * emitted by Extraction::extract($graph=true). Numbers are kept as numeric
     * strings; NULL or missing values become "null" so Chart.js draws a gap
     * at the right index.
     *
     * @return array<int,string>
     */
    private static function extractSparklineYValues(string $graphPayload): array
    {
        if ($graphPayload === '') {
            return [];
        }

        $values = [];
        if (preg_match_all('/y:([^},]+)/', $graphPayload, $matches)) {
            foreach ($matches[1] as $raw) {
                $raw = trim($raw);
                $values[] = is_numeric($raw) ? (string) (float) $raw : 'null';
            }
        }

        return $values;
    }

/**
 * Handle slave state through `show`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for show.
 * @phpstan-return void
 * @psalm-return void
 * @see self::show()
 * @example /fr/slave/show
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function show($param)
    {

        $this->title = '<i class="fa fa-sitemap"></i> '.__("Slave status");

        $db = Sgbd::sql(DB_DEFAULT);

//debug($db);

        $id_mysql_server  = $param[0];
        $replication_name = $param[1];

        $data['id_mysql_server']  = $id_mysql_server;
        $data['replication_name'] = $replication_name;

        $sql = "SELECT * from mysql_server where id = ".$id_mysql_server.";";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $server = $ob;
        }

        // Issue #1192 — fast path for the "new replication" form. The
        // /slave/show/<id>/__new__/ page only renders the source-setup
        // form; it does not need any replica-status / time-series /
        // live-connection data, and it must NOT emit the 5-second
        // getLag poll loop. Short-circuit here so the page loads
        // instantly and the browser stays quiet.
        if ($replication_name === '__new__') {
            self::prepareNewReplicationFormData($db, $data, (int) $id_mysql_server, $_SESSION);
            $data['class']    = $this->getClass();
            $data['function'] = __FUNCTION__;
            $this->set('data', $data);
            return;
        }

        $data['slave'] = array();
        $parallelSettings = self::getParallelReplicationSettingsFromTimeSeries((int) $id_mysql_server);
        $data['parallel_threads'] = $parallelSettings['parallel_threads'];
        $data['parallel_mode'] = $parallelSettings['parallel_mode'];

        // Issue #1196 — durability_rows is computed later (after master_id
        // is known) so it can carry both slave and master values. Init
        // empty here in case any intermediate code reads it.
        $data['durability_rows'] = [];

        $data['server'] = Extraction::display(array("mysql_server::mysql_available"));

        //debug($data['server'][$server['id']]['']['mysql_available']);

        if ($data['server'][$server['id']]['']['mysql_available'] === "1") {
            $link_slave = Sgbd::sql($server['name']);

            // Use PmaControl's centralized gate, not Glial's buggy
            // `isSlave()` (which picks REPLICA on Percona 5.6) — #830.
            $slaves = self::fetchReplicaStatusRows($link_slave);

            $data['all_connections'] = self::buildReplicationConnectionTabs($slaves);

            $data['server_type'] = $link_slave->getServerType();
            $data['server_version'] = $link_slave->getVersion();

            if ($replication_name === '__new__') {
                $data['slave'] = [];

                // Fetch available servers for the master_host dropdown
                $sql_servers = "SELECT a.id, a.display_name, a.ip, a.port, b.libelle AS environment
                    FROM mysql_server a
                    INNER JOIN environment b ON a.id_environment = b.id
                    WHERE a.is_deleted = 0 AND a.id != " . (int)$id_mysql_server . "
                    ORDER BY b.libelle, a.display_name";
                $res_servers = $db->sql_query($sql_servers);
                $data['available_servers'] = [];
                while ($row = $db->sql_fetch_array($res_servers, MYSQLI_ASSOC)) {
                    $data['available_servers'][] = $row;
                }
            } else {
                if (empty($replication_name) && !empty($slaves)) {
                    $replication_name = $slaves[0]['Connection_name'] ?? $slaves[0]['Channel_Name'] ?? '';
                    $data['replication_name'] = $replication_name;
                }

                $slave = [];
                if (count($slaves) === 1) {
                    $slave = end($slaves);
                } else {
                    foreach ($slaves as $option) {
                        $cn = $option['Connection_name'] ?? $option['Channel_Name'] ?? '';
                        if ($cn === $replication_name) {
                            $slave = $option;
                        }
                    }
                }

                $data['slave'] = $slave;
            }

            // Live values override the last collected time-series values when the server answers.
            $isMariaDB = (stripos($data['server_type'], 'mariadb') !== false);

            if ($isMariaDB) {
                $res_pt = $link_slave->sql_query_silent("SELECT @@GLOBAL.slave_parallel_threads AS val");
                if ($res_pt && $row_pt = $link_slave->sql_fetch_array($res_pt, MYSQLI_ASSOC)) {
                    $data['parallel_threads'] = (int)$row_pt['val'];
                }
                $res_pm = $link_slave->sql_query_silent("SELECT @@GLOBAL.slave_parallel_mode AS val");
                if ($res_pm && $row_pm = $link_slave->sql_fetch_array($res_pm, MYSQLI_ASSOC)) {
                    $data['parallel_mode'] = $row_pm['val'];
                }
            } else {
                $var_name = self::getMySQLParallelWorkersVariable($link_slave);
                $res_pt = $link_slave->sql_query_silent("SELECT @@GLOBAL.$var_name AS val");
                if ($res_pt && $row_pt = $link_slave->sql_fetch_array($res_pt, MYSQLI_ASSOC)) {
                    $data['parallel_threads'] = (int)$row_pt['val'];
                }
            }
        }
        ksort($data['slave']);

        // CPU count from time-series
        $cpu_data = Extraction::display(array("ssh_hardware::cpu_thread_count"), array($id_mysql_server));
        $data['cpu_count'] = 0;
        if (!empty($cpu_data[$id_mysql_server]['']['cpu_thread_count'])) {
            $data['cpu_count'] = (int)$cpu_data[$id_mysql_server]['']['cpu_thread_count'];
        }

        $data['replication_name'] = $replication_name;
        Extraction::setOption('groupbyday', true);

        $date        = date('Y-m-d H:i:s');
        $date_format = 'Y-m-d';

        $array_date = date_parse_from_format($date_format, $date);

        $more_days = -1;
        $next_date = date(
            $date_format, mktime(0, 0, 0, $array_date['month'], $array_date['day'] + $more_days, $array_date['year'])
        );

        $slaves = Extraction::extract($this->getReplicationLagVariables(), array($id_mysql_server), array($next_date, $date), true, true);
        $slaves = $this->normalizeReplicationLagGraphRows($slaves ?: []);

        // Filter to the selected replication source only
        $slaves = array_values(array_filter($slaves, function($s) use ($replication_name) {
            return ($s['connection_name'] ?? '') === $replication_name;
        }));

        $slaves = $this->enrichSlavesWithRelayLogSpaceGraph(
            $slaves,
            (int) $id_mysql_server,
            array($next_date, $date),
            (string) $replication_name
        );

        $this->generateGraphSlave($slaves);

        foreach ($slaves as $slave) {
            $data['graph'][$slave['day']] = $slave;
        }

        $sql = "WITH LastCluster AS (
        SELECT id_dot3_cluster
        FROM dot3_cluster__mysql_server
        WHERE id_mysql_server = ".$id_mysql_server."
        ORDER BY date_inserted DESC
        LIMIT 1
        )
        SELECT id_mysql_server
        FROM dot3_cluster__mysql_server
        WHERE id_dot3_cluster = (SELECT id_dot3_cluster FROM LastCluster);";
        


//change master


        $res = $db->sql_query($sql);

        $data['mysql_server_specify'] = array();
        while ($ob                           = $db->sql_fetch_object($res)) {
            $data['mysql_server_specify'][] = $ob->id_mysql_server;
        }

// find master
        $_GET['mysql_server']['id'] = Mysql::getMaster($id_mysql_server, $replication_name);

        $data['id_slave']              = array($id_mysql_server);
        $_GET['mysql_slave']['server'] = $id_mysql_server;


       // debug($_GET['mysql_server']['id']);
       
//le cas ou on arrive pas a trouver le master

/*
if (!empty($_GET['mysql_server']['id'])) {
    $db_master = Mysql::getDbLink($_GET['mysql_server']['id']);
    //exit;
    $sql  = "show databases;";
    $res7 = $db_master->sql_query($sql);

    $data['db_on_master'] = array();
    $i                    = 0;
    while ($ob7                  = $db->sql_fetch_object($res7)) {
        if (in_array($ob7->Database, array('information_schema', 'performance_schema', 'mysql', 'sys'))) {
            continue;
        }

        if ($i > 1) {
            $data['db_on_master'][] = "...";
            break;
        }

        $data['db_on_master'][] = $ob7->Database;
        $i++;
    }
}*/

        // Binlog gap estimation: how far behind is the SQL thread vs IO thread
        $data['binlog_gap'] = null;
        $master_id = $_GET['mysql_server']['id'] ?? null;
        if ($master_id && !empty($data['slave'])) {
            $sv = $data['slave'];
            $exec_file = $sv['Relay_Master_Log_File'] ?? $sv['Relay_Source_Log_File'] ?? '';
            $exec_pos  = (int)($sv['Exec_Master_Log_Pos'] ?? $sv['Exec_Source_Log_Pos'] ?? 0);
            $read_file = $sv['Master_Log_File'] ?? $sv['Source_Log_File'] ?? '';
            $read_pos  = (int)($sv['Read_Master_Log_Pos'] ?? $sv['Read_Source_Log_Pos'] ?? 0);

            // Extract numeric suffix from binlog filenames (e.g. mysql-bin.1044404 → 1044404)
            preg_match('/\.(\d+)$/', $exec_file, $m_exec);
            preg_match('/\.(\d+)$/', $read_file, $m_read);
            $exec_num = isset($m_exec[1]) ? (int)$m_exec[1] : null;
            $read_num = isset($m_read[1]) ? (int)$m_read[1] : null;

            $binlog_files_data = Extraction2::display(array("mysql_binlog::binlog_files"), array($master_id));
            $binlog_sizes_data = Extraction2::display(array("mysql_binlog::binlog_sizes"), array($master_id));

            $files_raw = $binlog_files_data[$master_id]['binlog_files'] ?? '';
            $sizes_raw = $binlog_sizes_data[$master_id]['binlog_sizes'] ?? '';

            $files = is_array($files_raw) ? $files_raw : (is_string($files_raw) && $files_raw !== '' ? json_decode($files_raw, true) : null);
            $sizes = is_array($sizes_raw) ? $sizes_raw : (is_string($sizes_raw) && $sizes_raw !== '' ? json_decode($sizes_raw, true) : null);

            $gap_computed = false;

            if (is_array($files) && is_array($sizes) && count($files) === count($sizes)) {
                $exec_idx = array_search($exec_file, $files);
                $read_idx = array_search($read_file, $files);

                if ($exec_idx !== false && $read_idx !== false && $read_idx >= $exec_idx) {
                    $gap_bytes = 0;
                    $gap_files = $read_idx - $exec_idx;

                    if ($exec_idx === $read_idx) {
                        $gap_bytes = max(0, $read_pos - $exec_pos);
                    } else {
                        $gap_bytes += max(0, (int)$sizes[$exec_idx] - $exec_pos);
                        for ($fi = $exec_idx + 1; $fi < $read_idx; $fi++) {
                            $gap_bytes += (int)$sizes[$fi];
                        }
                        $gap_bytes += $read_pos;
                    }

                    $data['binlog_gap'] = [
                        'bytes'    => $gap_bytes,
                        'files'    => $gap_files,
                        'estimate' => false,
                    ];
                    $gap_computed = true;
                }
            }

            // Fallback: estimate from file numbers + average binlog size
            if (!$gap_computed && $exec_num !== null && $read_num !== null && $read_num >= $exec_num) {
                $gap_files = $read_num - $exec_num;
                $avg_size = 0;
                if (is_array($sizes) && count($sizes) > 0) {
                    $avg_size = array_sum(array_map('intval', $sizes)) / count($sizes);
                }
                $gap_bytes = (int)($gap_files * $avg_size);
                // Adjust for partial positions
                if ($gap_files === 0) {
                    $gap_bytes = max(0, $read_pos - $exec_pos);
                }

                $data['binlog_gap'] = [
                    'bytes'    => $gap_bytes,
                    'files'    => $gap_files,
                    'estimate' => true,
                ];
            }
        }

        // Sparkline: last 1 hour of replication lag for this server
        $data['sparkline'] = '';
        $spark_slaves = Extraction::extract($this->getReplicationLagVariables(), array($id_mysql_server), "1 hour", false, true);
        $spark_slaves = $this->normalizeReplicationLagGraphRows($spark_slaves ?: []);
        if (!empty($spark_slaves)) {
            $spark = end($spark_slaves);
            $data['sparkline'] = $spark['graph'] ?? '';
        }

        $data['class']    = $this->getClass();
        $data['function'] = __FUNCTION__;
        $data['master_id'] = $master_id ?? 0;

        // Issue #1194 — disable the GTID Activate button when slave
        // and master sit in incompatible MySQL families (MariaDB ↔
        // MySQL/Percona). Cross-family GTID activation only configures
        // one side correctly and breaks replication.
        $gtidCompat = self::evaluateGtidActivationCompatibility(
            (string) ($data['server_type'] ?? ''),
            $master_id ? (int) $master_id : null
        );
        $data['gtid_compatible']    = $gtidCompat['compatible'];
        $data['gtid_compat_reason'] = $gtidCompat['reason'];

        // Issue #1196 — render the Binlog group-commit pair (count +
        // usec) for slave AND master, with family-aware variable names
        // and drift detection. Reuses the family classification from
        // the GTID compat check so we don't classify twice.
        $data['group_commit_rows'] = self::buildBinlogGroupCommitRows(
            (int) $id_mysql_server,
            $master_id ? (int) $master_id : null,
            $gtidCompat['slave_family'],
            $gtidCompat['master_family']
        );

        // Issue #1196 follow-up — rebuild durability rows now that
        // master_id is known so each row carries BOTH slave and master
        // values (master left, slave right in the view per the
        // operator request).
        $data['durability_rows'] = self::buildDurabilityRows(
            (int) $id_mysql_server,
            (int) $data['parallel_threads'],
            $master_id ? (int) $master_id : null
        );

        $data['slave_binlog_analysis_start_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['slave_binlog_analysis_start_csrf_token'] = Csrf::issueToken($_SESSION, self::SLAVE_BINLOG_ANALYSIS_START_CSRF_SCOPE);
        // Issue #1190 — CSRF token for the editable binlog_row_image
        // picker on the durability card.
        $data['slave_binlog_row_image_set_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['slave_binlog_row_image_set_csrf_token'] = Csrf::issueToken($_SESSION, self::SLAVE_BINLOG_ROW_IMAGE_SET_CSRF_SCOPE);
        $data['binlog_row_image_values'] = self::BINLOG_ROW_IMAGE_VALUES;

        // Issue #1198 — CSRF token + whitelist for the generic
        // replication-variable setter (master + slave editing on every
        // dynamic durability/group-commit variable).
        $data['slave_replication_variable_set_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['slave_replication_variable_set_csrf_token'] = Csrf::issueToken($_SESSION, self::SLAVE_REPLICATION_VARIABLE_SET_CSRF_SCOPE);
        $data['replication_variable_whitelist'] = self::replicationVariableWhitelist();
        if ($replication_name === '__new__' && self::normalizeSetupSourceServerId($id_mysql_server) !== null) {
            $data['slave_setup_source_csrf_field'] = Csrf::DEFAULT_FIELD;
            $data['slave_setup_source_csrf_token'] = Csrf::issueToken($_SESSION, self::SLAVE_SETUP_SOURCE_CSRF_SCOPE);
        }

        $this->di['js']->code_javascript('
function svHumanDuration(sec) {
    if (sec === null || sec === "NULL") return "NULL";
    var s = parseInt(sec);
    if (isNaN(s)) return "NULL";
    if (s < 60) return s + "s";
    if (s < 3600) return Math.floor(s/60) + "m " + (s%60) + "s";
    if (s < 86400) return Math.floor(s/3600) + "h " + Math.floor((s%3600)/60) + "m " + (s%60) + "s";
    return Math.floor(s/86400) + "d " + Math.floor((s%86400)/3600) + "h " + Math.floor((s%3600)/60) + "m " + (s%60) + "s";
}

$(document).ready(function() {

    // Remove native title tooltip from topology selects
    $(".sv-action-group .bootstrap-select button").removeAttr("title");

    // Sparkline: lag last hour
    (function() {
        var sparkCanvas = document.getElementById("sv-sparkline");
        if (!sparkCanvas) return;
        var sparkData = ['.$data['sparkline'].'];
        if (!sparkData.length) return;
        var existing = Chart.getChart(sparkCanvas);
        if (existing) existing.destroy();
        new Chart(sparkCanvas.getContext("2d"), {
            type: "line",
            data: {
                datasets: [{
                    data: sparkData,
                    borderColor: "#16285a",
                    backgroundColor: "rgba(22,40,90,0.3)",
                    fill: true,
                    borderWidth: 1.5,
                    pointRadius: 0,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: {
                    x: { display: false, type: "time" },
                    y: { display: false }
                }
            }
        });

        // ETA calculation from sparkline data
        if (sparkData.length >= 2) {
            var last  = sparkData[sparkData.length - 1];
            var etaEl = document.getElementById("sv-eta");
            if (last && etaEl) {
                var lagEnd = last.y;

                // Find the max lag in the sparkline data to use as reference
                // for computing the recovery rate. This handles cases where
                // the lag started at 0, spiked, and is now being absorbed.
                var peakIdx = 0;
                var peakVal = sparkData[0].y;
                for (var si = 1; si < sparkData.length; si++) {
                    if (sparkData[si].y > peakVal) {
                        peakVal = sparkData[si].y;
                        peakIdx = si;
                    }
                }
                // If peak is at the very end (still rising), use first point instead
                if (peakIdx === sparkData.length - 1) {
                    peakIdx = 0;
                }
                var ref = sparkData[peakIdx];
                var tRef = (ref.x instanceof Date) ? ref.x.getTime() : new Date(ref.x).getTime();
                var tEnd = (last.x instanceof Date) ? last.x.getTime() : new Date(last.x).getTime();
                var elapsed = (tEnd - tRef) / 1000;

                if (elapsed > 0 && lagEnd > 0) {
                    var delta = ref.y - lagEnd; // positive = catching up since peak
                    if (delta > 0) {
                        var rate = delta / elapsed;
                        var etaSec = lagEnd / rate;
                        var etaDate = new Date(Date.now() + etaSec * 1000);

                        var etaStr = "";
                        if (etaSec < 60) {
                            etaStr = "< 1 min";
                        } else if (etaSec < 3600) {
                            etaStr = "~" + Math.round(etaSec / 60) + " min";
                        } else if (etaSec < 86400) {
                            var h = Math.floor(etaSec / 3600);
                            var m = Math.round((etaSec % 3600) / 60);
                            etaStr = "~" + h + "h" + (m > 0 ? ("0"+m).slice(-2) : "");
                        } else {
                            var d = Math.floor(etaSec / 86400);
                            var h = Math.round((etaSec % 86400) / 3600);
                            etaStr = "~" + d + " '.__('days').' " + h + "h";
                        }

                        var hh = ("0" + etaDate.getHours()).slice(-2);
                        var mm = ("0" + etaDate.getMinutes()).slice(-2);
                        var dd = etaDate.getFullYear() + "-" + ("0"+(etaDate.getMonth()+1)).slice(-2) + "-" + ("0"+etaDate.getDate()).slice(-2);

                        etaEl.innerHTML = "<span style=\"color:var(--clr-ok)\"><i class=\"fa fa-clock-o\"></i> " + etaStr + "</span><br><small style=\"color:var(--clr-muted)\">~" + dd + " " + hh + ":" + mm + "</small>";
                    } else if (delta < 0) {
                        etaEl.innerHTML = "<span style=\"color:var(--clr-crit)\"><i class=\"fa fa-arrow-up\"></i> '.__('increasing').'</span>";
                    } else {
                        etaEl.innerHTML = "<span style=\"color:var(--clr-warn)\"><i class=\"fa fa-minus\"></i> '.__('stable').'</span>";
                    }
                } else if (lagEnd === 0) {
                    etaEl.innerHTML = "<span style=\"color:var(--clr-ok)\"><i class=\"fa fa-check\"></i> '.__('caught up').'</span>";
                }
            }
        }
    })();

    // Load previous day graph
    $("#btn-load-more-days").on("click", function() {
        var btn = $(this);
        var server = btn.data("server");
        var oldest = btn.data("oldest");
        var replName = btn.data("replication") || "";

        if (!oldest) return;

        var d = new Date(oldest);
        d.setDate(d.getDate() - 1);
        var newDay = d.getFullYear() + "-" + ("0"+(d.getMonth()+1)).slice(-2) + "-" + ("0"+d.getDate()).slice(-2);

        btn.prop("disabled", true).html("<i class=\"fa fa-spinner fa-spin\"></i> '.__('Loading').'...");

        // Single-source replicas have replName="", which used to bleed into the
        // route as "//" — Apache collapsed it and ajax:true ended up as
        // $param[2] (the connection_name slot), filtering every row out.
        // Append the segment only when it carries a real value. (#816)
        var path = "slave/showGraphDay/" + server + "/" + newDay + "/";
        if (replName) {
            path += encodeURIComponent(replName) + "/";
        }
        path += "ajax:true/";

        $.get(GLIAL_LINK + path, function(html) {
            var trimmed = $.trim(html.replace(/<script[\s\S]*?<\/script>/gi, ""));
            if (trimmed.length > 10) {
                var $parts = $($.parseHTML(html, document, true));
                var scripts = [];
                $parts.each(function() {
                    if (this.nodeName === "SCRIPT") {
                        scripts.push(this.textContent);
                    }
                });
                $parts.not("script").prependTo("#slave-graphs-container");
                for (var i = 0; i < scripts.length; i++) {
                    $.globalEval(scripts[i]);
                }
                // Re-arm Binlog Analysis drag-select on the freshly added
                // canvases — the show view binds at load time only (#818).
                if (typeof window.attachLagDragSelectAll === "function") {
                    window.attachLagDragSelectAll();
                }
            } else {
                // No data for this day — show placeholder
                var placeholder = $("<div class=\"sv-chart-wrap\" style=\"display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:12px;border:1px dashed #e2e8f0;border-radius:6px;margin-bottom:4px\">" + newDay + " — '.__('no data').'</div>");
                placeholder.prependTo("#slave-graphs-container");
            }
            // Always decrement date so next click goes further back
            btn.data("oldest", newDay);
            btn.prop("disabled", false).html("<i class=\"fa fa-plus\"></i> '.__('Load previous day').'");
        }).fail(function() {
            // Network error — still decrement so user can retry next day
            btn.data("oldest", newDay);
            btn.prop("disabled", false).html("<i class=\"fa fa-plus\"></i> '.__('Load previous day').'");
        });
    });

    // SHOW SLAVE STATUS search filter
    var filterTimer = null;
    $("#sv-vars-filter").on("keyup", function() {
        var input = this;
        clearTimeout(filterTimer);
        filterTimer = setTimeout(function() {
            var q = input.value.toLowerCase().trim();
            var rows = document.querySelectorAll(".sv-vars-row");
            var cats = document.querySelectorAll(".sv-vars-cat");
            var total = 0;

            rows.forEach(function(row) {
                var varName = row.getAttribute("data-var") || "";
                var varVal  = row.getAttribute("data-val") || "";
                var match   = !q || varName.indexOf(q) !== -1 || varVal.indexOf(q) !== -1;
                row.classList.toggle("sv-hidden", !match);
                if (match) total++;
            });

            cats.forEach(function(cat) {
                var visible = cat.querySelectorAll(".sv-vars-row:not(.sv-hidden)").length;
                var countEl = cat.querySelector(".sv-vars-cat-count");
                if (countEl) countEl.textContent = "(" + visible + ")";
                cat.style.display = visible ? "" : "none";
            });

            document.getElementById("sv-vars-no-match").style.display = total ? "none" : "block";
        }, 150);
    });

    // Parallel threads slider + input sync
    var ptInput = document.getElementById("sv-parallel-threads");
    var ptRange = document.getElementById("sv-parallel-range");
    var ptBtn   = document.getElementById("sv-parallel-apply");
    if (ptInput && ptRange && ptBtn) {
        ptInput.addEventListener("input", function() {
            var v = Math.max(0, Math.min(parseInt(ptInput.max), parseInt(this.value) || 0));
            ptRange.value = v;
        });
        ptRange.addEventListener("input", function() {
            ptInput.value = this.value;
        });
        var ptMode = document.getElementById("sv-parallel-mode");
        ptBtn.addEventListener("click", function(e) {
            e.preventDefault();
            var v = Math.max(0, Math.min(parseInt(ptInput.max), parseInt(ptInput.value) || 0));
            var msg = "'.__('This will STOP and restart replication.').'\\n\\n" + "slave_parallel_threads = " + v;
            if (ptMode) msg += "\\nslave_parallel_mode = " + ptMode.value;
            if (!confirm(msg)) return;
            var url = ptBtn.getAttribute("data-base-threads") + "threads:" + v + "/";
            if (ptMode) url += "mode:" + ptMode.value + "/";
            window.location.href = url;
        });
    }

    // Live lag polling every 5s
    function svDot(state) {
        if (state === "ok") return "<span class=\"sv-dot ok\"></span>";
        if (state === "info") return "<span class=\"sv-dot info\"></span>";
        return "<span class=\"sv-dot fail halo\"></span>";
    }
    function svLagClass(io, sql, lag) {
        var stopped = (io !== "Yes" && sql !== "Yes");
        if (stopped) return "stopped";
        if (lag === null || lag === "NULL") return "critical";
        lag = parseInt(lag);
        if (isNaN(lag)) return "critical";
        if (lag === 0) return "ok";
        if (lag < 60) return "behind";
        if (lag <= 60) return "warning";
        return "critical";
    }
    setInterval(function() {
        $.getJSON(GLIAL_LINK + "slave/getLag/'.$id_mysql_server.'/'.$replication_name.'/ajax:true/", function(d) {
            var both = (d.io !== "Yes" && d.sql !== "Yes");
            var ioDot  = d.io === "Yes" ? "ok" : (both ? "info" : "fail");
            var sqlDot = d.sql === "Yes" ? "ok" : (both ? "info" : "fail");

            $("#sv-live-io").html(svDot(ioDot) + " " + (d.io || "N/A") + (d.io_state ? " <small>&mdash; " + $("<span>").text(d.io_state).html() + "</small>" : ""));
            $("#sv-live-sql").html(svDot(sqlDot) + " " + (d.sql || "N/A"));

            var lagText = svHumanDuration(d.lag);
            var lagCls = svLagClass(d.io, d.sql, d.lag);
            $("#sv-live-lag").attr("class", "sv-lag " + lagCls).text(lagText);

            if (d.master_log !== undefined) $("#sv-live-master-log").text(d.master_log || "");
            if (d.read_pos   !== undefined) $("#sv-live-read-pos").text(d.read_pos);
            if (d.relay_log  !== undefined) $("#sv-live-relay-log").text(d.relay_log || "");
            if (d.exec_pos   !== undefined) $("#sv-live-exec-pos").text(d.exec_pos);
        });
    }, 5000);
});
');

        $this->set('data', $data);
    }

/**
 * Handle slave state through `box`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for box.
 * @phpstan-return void
 * @psalm-return void
 * @see self::box()
 * @example /fr/slave/box
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function box()
    {

        $db = Sgbd::sql(DB_DEFAULT);

        $data['slave'] = Extraction::display(array_merge(
            ["slave::master_host", "slave::master_port", "slave::slave_io_running",
                "slave::slave_sql_running", "slave::last_io_errno", "slave::last_io_error",
                "slave::last_sql_error", "slave::last_sql_errno"],
            $this->getReplicationLagVariables()
        ));
        $data['slave'] = $this->normalizeReplicationLagDisplayRows($data['slave']);

        $sql = "SELECT a.*, c.libelle as client,d.libelle as environment,d.`class`,a.is_available  FROM mysql_server a
                 INNER JOIN client c on c.id = a.id_client
                 INNER JOIN environment d on d.id = a.id_environment
                 WHERE 1 ".self::getFilter()."
                 ORDER by `name`;";

        $res = $db->sql_query($sql);

        $data['server'] = array();
        while ($arr            = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['server']['master'][$arr['ip'].':'.$arr['port']] = $arr;
            $data['server']['master'][$arr['id']]                  = $arr;
            $data['server']['slave'][$arr['id']]                   = $arr;
        }

//debug($data['slave']);

        $data['box'] = array();

        foreach ($data['slave'] as $id_mysql_server => $slaves) {
            foreach ($slaves as $connect_name => $slave) {
                $this->hydrateMasterFromAliasDns($data, $slave);

                if ($slave['slave_sql_running'] !== "Yes" || $slave['slave_io_running'] !== "Yes" || $slave['seconds_behind_master'] !== "0"
                ) {
                    $export = array();

                    if (empty($data['server']['master'][$slave['master_host'].':'.$slave['master_port']])) {
                        $data['server']['master'][$slave['master_host'].':'.$slave['master_port']]['display_name'] = "Unknow ".$slave['master_host'].':'.$slave['master_port'];

                        if ($slave['slave_io_running'] === 'Yes') {
                            $data['server']['master'][$slave['master_host'].':'.$slave['master_port']]['is_available'] = "1";
                        } else {
                            $data['server']['master'][$slave['master_host'].':'.$slave['master_port']]['is_available'] = "0";
                        }
                    }

                    $export['master']            = $data['server']['master'][$slave['master_host'].':'.$slave['master_port']];
                    $export['slave']             = $data['server']['slave'][$id_mysql_server];
                    $export['connect']           = $connect_name;
                    $export['seconds']           = $slave['seconds_behind_master'];
                    $export['slave_sql_running'] = $slave['slave_sql_running'];
                    $export['slave_io_running']  = $slave['slave_io_running'];
                    $export['slave_sql_error']   = $slave['last_sql_error'];
                    $export['slave_io_error']    = $slave['last_io_error'];
                    $export['slave_sql_errno']   = $slave['last_sql_errno'];
                    $export['slave_io_errno']    = $slave['last_io_errno'];

                    $data['box'][] = $export;
                }
            }
        }


        $this->set('data', $data);
//debug($data['box']);
    }

    private function hydrateMasterFromAliasDns(array &$data, array $slave): void
    {
        if (empty($slave['master_host']) || empty($slave['master_port'])) {
            return;
        }

        $masterKey = $slave['master_host'].':'.$slave['master_port'];

        if (!empty($data['server']['master'][$masterKey])) {
            return;
        }

        $id_mysql_server = Mysql::getIdFromDns($masterKey);
        if (empty($id_mysql_server) || empty($data['server']['master'][$id_mysql_server])) {
            return;
        }

        $data['server']['master'][$masterKey] = $data['server']['master'][$id_mysql_server];
    }

/**
 * Handle slave state through `generateGraphSlave`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $slaves Input value for `slaves`.
 * @phpstan-param mixed $slaves
 * @psalm-param mixed $slaves
 * @return void Returned value for generateGraphSlave.
 * @phpstan-return void
 * @psalm-return void
 * @see self::generateGraphSlave()
 * @example /fr/slave/generateGraphSlave
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function generateGraphSlave($slaves)
    {
        $this->di['js']->addJavascript(array("moment.js", "chart-4.5.1.umd.min.js", "chartjs-adapter-moment.min.js", "hammer.min.js", "chartjs-plugin-zoom.js", "chartjs-chart-treemap.min.js"));

        foreach ($slaves as $slave) {
            $this->di['js']->code_javascript(self::buildLagChartJs($slave));
        }
    }

    /**
     * Pure-PHP helper: emit the Chart.js construction block for a
     * single per-day lag chart. Shared between
     * `Slave::generateGraphSlave()` (initial page render via
     * `code_javascript`) and `App/view/Slave/showGraphDay.view.php`
     * (AJAX response for the "Load previous day" button) so the two
     * paths can never drift apart.
     *
     * Renders:
     * - Left axis `y` — `Second behind source` (lag) — same
     *   navy area we've shown forever.
     * - Right axis `y_gb` (only when `$slave['graph_relay_log_space']`
     *   is non-empty) — `Relay_Log_Space` in bytes, with a
     *   B/KB/MB/GB tick formatter and per-segment colouring:
     *     up   = red, down = green, flat = blue 50% opacity.
     *
     * @param array{
     *     id_mysql_server:int,
     *     connection_name?:string,
     *     day:string,
     *     graph:string,
     *     graph_relay_log_space?:string
     * } $slave
     */
    public static function buildLagChartJs(array $slave): string
    {
        $relayPayload = (string) ($slave['graph_relay_log_space'] ?? '');
        $hasRelay = $relayPayload !== '';

        $relayDataset = $hasRelay ? '
            ,{
                label: "Relay_Log_Space (queued bytes)",
                data: ['.$relayPayload.'],
                borderColor: "#b91c1c",
                backgroundColor: "rgba(185,28,28,0.30)",
                fill: "origin",
                borderWidth: 1,
                pointRadius: 0,
                tension: 0,
                yAxisID: "y_gb",
                segment: {
                    borderColor: function (ctx) {
                        if (!ctx.p0 || !ctx.p1) return "#3b82f6";
                        var d = ctx.p1.parsed.y - ctx.p0.parsed.y;
                        if (d > 0) return "#dc2626";
                        if (d < 0) return "#16a34a";
                        return "#3b82f6";
                    },
                    backgroundColor: function (ctx) {
                        if (!ctx.p0 || !ctx.p1) return "rgba(59,130,246,0.50)";
                        var d = ctx.p1.parsed.y - ctx.p0.parsed.y;
                        if (d > 0) return "rgba(220,38,38,0.30)";
                        if (d < 0) return "rgba(22,163,74,0.30)";
                        return "rgba(59,130,246,0.50)";
                    }
                }
            }' : '';

        $relayScale = $hasRelay ? ',
            y_gb: {
                position: "right",
                grid: { drawOnChartArea: false },
                title: { display: true, text: "Relay log queued (B → MB/GB)" },
                ticks: {
                    callback: function (v) {
                        if (v == null) return "";
                        var n = Number(v);
                        if (!isFinite(n)) return "";
                        if (n >= 1073741824) return (n / 1073741824).toFixed(2) + " GB";
                        if (n >= 1048576)    return (n / 1048576).toFixed(1)    + " MB";
                        if (n >= 1024)       return (n / 1024).toFixed(1)       + " KB";
                        return n + " B";
                    }
                }
            }' : '';

        $tooltipPlugin = $hasRelay ? '
            tooltip: {
                callbacks: {
                    label: function (ctx) {
                        var v = ctx.parsed && ctx.parsed.y;
                        if (ctx.dataset && ctx.dataset.yAxisID === "y_gb") {
                            if (v == null) return ctx.dataset.label + ": —";
                            if (v >= 1073741824) return ctx.dataset.label + ": " + (v / 1073741824).toFixed(2) + " GB";
                            if (v >= 1048576)    return ctx.dataset.label + ": " + (v / 1048576).toFixed(1)    + " MB";
                            if (v >= 1024)       return ctx.dataset.label + ": " + (v / 1024).toFixed(1)       + " KB";
                            return ctx.dataset.label + ": " + v + " B";
                        }
                        return ctx.dataset.label + ": " + (v == null ? "—" : v + " s");
                    }
                }
            },' : '';

        $canvasId = 'myChart' . $slave['id_mysql_server'] . crc32(($slave['connection_name'] ?? '') . $slave['day']);

        return '
(function() {
var canvas = document.getElementById("' . $canvasId . '");
if (!canvas) return;
var existing = Chart.getChart(canvas);
if (existing) existing.destroy();
var ctx = canvas.getContext("2d");

var chart = new Chart(ctx, {

    type: "line",
    data: {
        datasets: [{
            label: "' . __('Second behind source') . '",
            data: [' . $slave['graph'] . '],
                borderColor: "#16285a",
                backgroundColor: "rgba(22,40,90,0.3)",
                fill: true,
                borderWidth: 1,
             pointRadius :1,
             tension: 0,
             yAxisID: "y"

        }' . $relayDataset . ']
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: ' . ($hasRelay ? 'true' : 'false') . ' },' . $tooltipPlugin . '
            title: {
                display: true,
                text: "Replication : ' . $slave['day'] . '",
                position: "top",
                padding: 0
            }
        },
        scales: {
            x: {
                type: "time",
                display: true,
                title: {
                  display: true,
                  text: "Date",
                },
                time: {
                    tooltipFormat: "dddd YYYY-MM-DD, HH:mm:ss",
                    displayFormats: {
                        minute: "HH:mm"
                    }
                },
                min: new Date("' . $slave['day'] . ' 00:00:00"),
                max: new Date("' . $slave['day'] . ' 23:59:59"),
            },
            y: {
                min: 0,
                position: "left",
                title: {
                    display: true,
                    text: "Second behind source",
                }
            }' . $relayScale . '
        }
    }
});
})();
';
    }

    /**
     * Run the parallel `slave::relay_log_space` extraction and stamp
     * each `$slaves` row with a `graph_relay_log_space` field
     * carrying the Chart.js-formatted payload for the right-side GB
     * axis. Same date range / groupbyday flag as the lag query so
     * the timestamps line up.
     *
     * Filters by `$replicationName` so a multi-channel server doesn't
     * leak the wrong relay queue onto the chart for the active
     * channel.
     *
     * @param array<int,array> $slaves
     * @param int $serverId
     * @param array{0:string,1:string} $dateRange  [start, end]
     * @param string $replicationName
     * @return array<int,array>
     */
    private function enrichSlavesWithRelayLogSpaceGraph(array $slaves, int $serverId, array $dateRange, string $replicationName): array
    {
        $relayRows = Extraction::extract(
            array('slave::relay_log_space'),
            array($serverId),
            $dateRange,
            true,
            true
        ) ?: [];

        $relayByDay = [];
        foreach ($relayRows as $r) {
            if (($r['connection_name'] ?? '') !== $replicationName) continue;
            if (!isset($r['day'], $r['graph'])) continue;
            $relayByDay[(string) $r['day']] = (string) $r['graph'];
        }

        foreach ($slaves as &$slave) {
            $slave['graph_relay_log_space'] = $relayByDay[(string) ($slave['day'] ?? '')] ?? '';
        }
        unset($slave);

        return $slaves;
    }

    public function showGraphDay($param)
    {
        if (!empty($_GET['ajax']) && $_GET['ajax'] === "true") {
            $this->layout_name = false;
        }

        $id_mysql_server = $param[0];
        $day = $param[1];
        // Glial pushes "key:value" segments into $param too, so a stale
        // browser hitting `…/<day>//ajax:true/` would land "ajax:true" in
        // the connection_name slot and silently filter every row out (#816).
        // A real connection_name can never contain ":" — sanitizeConnectionName
        // strips it — so colon-bearing segments are framework noise.
        $replication_name = self::extractConnectionNameParam($param[2] ?? '');

        Extraction::setOption('groupbyday', true);

        $date_start = $day;
        $date_end   = $day.' 23:59:59';

        $slaves = Extraction::extract(
            $this->getReplicationLagVariables(),
            array($id_mysql_server),
            array($date_start, $date_end),
            true,
            true
        );
        $slaves = $this->normalizeReplicationLagGraphRows($slaves ?: []);

        // Filter to selected replication source
        if ($replication_name !== '') {
            $slaves = array_values(array_filter($slaves, function($s) use ($replication_name) {
                return ($s['connection_name'] ?? '') === $replication_name;
            }));
        }

        // Same dual-axis treatment as the initial show() render: tag
        // each slave row with the relay_log_space payload so the
        // shared `Slave::buildLagChartJs()` helper renders the
        // right-side GB axis on the AJAX-loaded "previous day" chart
        // too.
        $slaves = $this->enrichSlavesWithRelayLogSpaceGraph(
            $slaves,
            (int) $id_mysql_server,
            array($date_start, $date_end),
            (string) $replication_name
        );

        $data['graphs'] = [];
        foreach ($slaves as $slave) {
            $data['graphs'][] = $slave;
        }

        $this->set('data', $data);
    }

    public function getLag($param)
    {
        $this->layout_name = false;
        $this->view = false;

        $id_mysql_server  = $param[0];
        $replication_name = $param[1] ?? '';

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM mysql_server WHERE id = ".(int)$id_mysql_server.";";
        $res = $db->sql_query($sql);
        $server = $db->sql_fetch_array($res, MYSQLI_ASSOC);

        $result = [
            'io' => null,
            'sql' => null,
            'lag' => null,
            'io_state' => '',
            'master_log' => '',
            'read_pos' => '',
            'relay_log' => '',
            'exec_pos' => '',
        ];

        $server_data = Extraction::display(array("mysql_server::mysql_available"));
        if (!empty($server_data[$id_mysql_server]['']['mysql_available']) && $server_data[$id_mysql_server]['']['mysql_available'] === "1") {
            $link = Sgbd::sql($server['name']);
            // Centralized gate — Glial's `isSlave()` mis-selects REPLICA on
            // Percona 5.6 (#830).
            $slaves = self::fetchReplicaStatusRows($link);

            $slave = [];
            if (count($slaves) === 1) {
                $slave = end($slaves);
            } else {
                foreach ($slaves as $option) {
                    if (($option['Connection_name'] ?? '') === $replication_name) {
                        $slave = $option;
                    }
                }
            }

            $result['io']         = $slave['Slave_IO_Running']  ?? $slave['Replica_IO_Running']  ?? null;
            $result['sql']        = $slave['Slave_SQL_Running'] ?? $slave['Replica_SQL_Running'] ?? null;
            $result['lag']        = $slave['Seconds_Behind_Master'] ?? $slave['Seconds_Behind_Source'] ?? null;
            $result['io_state']   = $slave['Slave_IO_State'] ?? $slave['Replica_IO_State'] ?? '';
            $result['master_log'] = $slave['Master_Log_File']       ?? $slave['Source_Log_File']       ?? '';
            $result['read_pos']   = $slave['Read_Master_Log_Pos']   ?? $slave['Read_Source_Log_Pos']   ?? '';
            $result['relay_log']  = $slave['Relay_Master_Log_File'] ?? $slave['Relay_Source_Log_File'] ?? '';
            $result['exec_pos']   = $slave['Exec_Master_Log_Pos']   ?? $slave['Exec_Source_Log_Pos']   ?? '';
        }

        header('Content-Type: application/json');
        echo json_encode($result);
    }

    public function setParallelThreads($param)
    {
        $this->view = false;

        $id_mysql_server = $param[0];
        // param[1] may be connection_name or a key:value — skip if it contains ':'
        $connection_name = (!empty($param[1]) && strpos($param[1], ':') === false) ? $param[1] : '';
        $threads = (int)($_GET['threads'] ?? 0);
        $mode = $_GET['mode'] ?? null;

        // Enforce limits: min 0, max 50
        $threads = max(0, min(50, $threads));

        // Validate mode if provided
        $allowed_modes = ['conservative', 'optimistic', 'aggressive', 'minimal', 'none'];
        if ($mode !== null && !in_array($mode, $allowed_modes)) {
            $mode = null;
        }

        $db = Mysql::getDbLink($id_mysql_server);
        $isMariaDB = (stripos($db->getServerType(), 'mariadb') !== false);
        $useReplica = self::usesReplicaSyntax($db);
        $connection_name = self::sanitizeConnectionName($connection_name);

        try {
            if ($isMariaDB) {
                $var_name = 'slave_parallel_threads';
                $connClause = !empty($connection_name) ? " '$connection_name'" : "";
                $db->sql_query("STOP SLAVE $connClause;");
                $db->sql_query("SET GLOBAL $var_name = $threads;");
                $msg = "SET GLOBAL $var_name = $threads";
                if ($mode !== null) {
                    $db->sql_query("SET GLOBAL slave_parallel_mode = '$mode';");
                    $msg .= ", slave_parallel_mode = '$mode'";
                }
                $db->sql_query("START SLAVE $connClause;");
            } else {
                $var_name = version_compare((string) $db->getVersion(), '8.0.26', '>=') ? 'replica_parallel_workers' : 'slave_parallel_workers';
                $channelClause = !empty($connection_name) ? " FOR CHANNEL '$connection_name'" : "";
                $kw = $useReplica ? 'REPLICA' : 'SLAVE';
                $db->sql_query("STOP $kw $channelClause;");
                $db->sql_query("SET GLOBAL $var_name = $threads;");
                $msg = "SET GLOBAL $var_name = $threads";
                $db->sql_query("START $kw $channelClause;");
            }
            set_flash("success", __("Success"), $msg);
        } catch (\Exception $e) {
            set_flash("error", __("Error"), $e->getMessage());
        }

        header('location: '.LINK.'slave/show/'.$id_mysql_server.'/'.$connection_name.'/');
    }

    /**
     * Issue #1190 — set `binlog_row_image` from the slave/show
     * Durability picker.
     *
     * POST /slave/setBinlogRowImage/<id_mysql_server>/<connection_name>/
     * Body: `value=FULL|MINIMAL|NOBLOB` + CSRF token.
     * Returns JSON `{ ok: true, applied, previous }` or `{ error: '<msg>' }`.
     */
    public function setBinlogRowImage($param)
    {
        $this->layout_name = false;
        $this->view = false;
        header('Content-Type: application/json; charset=UTF-8');

        $outcome = self::evaluateSetBinlogRowImageRequest(
            $param,
            $_POST,
            $_SERVER,
            $_SESSION
        );
        if ($outcome['status'] !== 200) {
            self::sendSlaveJsonError($outcome['status'], $outcome['body'], $outcome['headers'] ?? []);
            return;
        }
        $request = $outcome['request'];

        try {
            $db = Mysql::getDbLink((int) $request['id_mysql_server']);
        } catch (\Throwable $e) {
            self::sendSlaveJsonError(503, 'Cannot reach target server: ' . $e->getMessage());
            return;
        }

        // Read the current value so the response can echo previous → applied.
        $previous = self::readBinlogRowImage($db);
        if ($previous === null) {
            self::sendSlaveJsonError(503, 'Cannot read @@global.binlog_row_image (server unreachable or insufficient privileges)');
            return;
        }

        // Apply.
        $sql = "SET GLOBAL binlog_row_image = '" . $request['value'] . "'";
        try {
            $db->sql_query($sql);
        } catch (\Throwable $e) {
            self::sendSlaveJsonError(500, 'SET GLOBAL failed: ' . $e->getMessage());
            return;
        }

        // Re-read so we report what the server actually has now (catches
        // weird-cased silently-rejected values etc.).
        $applied = self::readBinlogRowImage($db) ?? $request['value'];

        echo json_encode([
            'ok'       => true,
            'previous' => $previous,
            'applied'  => $applied,
        ]);
    }

    /**
     * Issue #1198 — generic SET GLOBAL setter for the replication
     * variables surfaced on /slave/show. Scope is gated by the
     * REPLICATION_VARIABLES whitelist (see replicationVariableWhitelist()).
     *
     * POST /slave/setReplicationVariable/<id_mysql_server>/<conn>/
     * Body: variable=<name>&value=<value> + CSRF token.
     * Returns JSON `{ok, variable, previous, applied}` or `{error}`.
     *
     * The id_mysql_server in the URL is the *target* server — the
     * picker on the master column posts to the master's id, the
     * picker on the slave column posts to the slave's id. Both flow
     * through this single action.
     */
    public function setReplicationVariable($param)
    {
        $this->slaveBeginJsonResponse();

        $outcome = self::evaluateSetReplicationVariableRequest($param, $_POST, $_SERVER, $_SESSION);
        if ($outcome['status'] !== 200) {
            $this->slaveSendJson(['error' => $outcome['body']], $outcome['status']);
            return;
        }
        $request = $outcome['request'];

        try {
            $db = Mysql::getDbLink((int) $request['id_mysql_server']);
        } catch (\Throwable $e) {
            $this->slaveSendJson(['error' => 'Cannot reach target server: ' . $e->getMessage()], 503);
            return;
        }

        $previous = self::readReplicationVariable($db, $request['variable']);

        // SQL composition is safe: $request['variable'] was matched
        // against the whitelist; $request['value_sql_literal'] was
        // assembled from validated values only (enum quoted, int bare,
        // numeric-enum bare — see #1224).
        $sql = "SET GLOBAL `" . $request['variable'] . "` = " . $request['value_sql_literal'];
        try {
            $db->sql_query($sql);
        } catch (\Throwable $e) {
            $this->slaveSendJson(['error' => 'SET GLOBAL failed: ' . $e->getMessage(), 'sql' => $sql], 500);
            return;
        }

        $applied = self::readReplicationVariable($db, $request['variable']) ?? $request['value'];

        $this->slaveSendJson([
            'ok'       => true,
            'variable' => $request['variable'],
            'previous' => $previous,
            'applied'  => $applied,
            'sql'      => $sql,
        ]);
    }

    /**
     * #1219/#1222/#1223 hardening for the slave/show JSON endpoints
     * — same belt-and-braces pattern documented in
     * `feedback_glial_ajax_json.md` (memory): discard whatever Glial
     * already buffered, start our own buffer, kill `display_errors`,
     * and register a shutdown handler so a fatal between here and
     * `slaveSendJson()` produces a deterministic JSON error rather
     * than an empty body / "Unexpected end of JSON input" client-side.
     */
    private function slaveBeginJsonResponse(): void
    {
        $this->layout_name = false;
        $this->view = false;
        while (ob_get_level() > 0) { @ob_end_clean(); }
        ob_start();
        @ini_set('display_errors', '0');
        header('Content-Type: application/json; charset=UTF-8');
        register_shutdown_function(static function () {
            $err = error_get_last();
            if (!$err) return;
            if (!in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) return;
            if (headers_sent()) {
                while (ob_get_level() > 0) { @ob_end_clean(); }
                echo json_encode([
                    'error' => 'Server-side fatal during JSON response — see Apache error_log',
                    'fatal' => $err['message'],
                    'at'    => basename((string) $err['file']) . ':' . $err['line'],
                ]);
            }
        });
    }

    private function slaveSendJson(array $payload, int $status = 200): void
    {
        if ($status !== 200) http_response_code($status);
        while (ob_get_level() > 0) { @ob_end_clean(); }
        echo json_encode($payload);
        exit;
    }

    public static function evaluateSetReplicationVariableRequest(array $param, array $post, array $server, array $session): array
    {
        if ($failure = CsrfGuard::ensureOrFail($post, $server, $session, self::SLAVE_REPLICATION_VARIABLE_SET_CSRF_SCOPE)) {
            return [
                'status'  => $failure['status'],
                'body'    => $failure['body'],
                'headers' => $failure['headers'],
            ];
        }

        $request = self::normalizeSetReplicationVariablePayload($param, $post);
        if ($request === null) {
            return [
                'status'  => 400,
                'body'    => 'Invalid replication-variable setter payload',
                'headers' => [],
            ];
        }
        return [
            'status'  => 200,
            'body'    => '',
            'headers' => [],
            'request' => $request,
        ];
    }

    /**
     * Validate + normalise a setter payload against the whitelist.
     * Returns `null` on any rejection (variable unknown, enum value
     * not in the allow-list, int out of bounds, missing fields).
     *
     * @return array{id_mysql_server:int,variable:string,value:string,value_sql_literal:string}|null
     */
    public static function normalizeSetReplicationVariablePayload(array $param, array $post): ?array
    {
        if (!isset($param[0]) || !is_scalar($param[0])) {
            return null;
        }
        $idMysqlServer = trim((string) $param[0]);
        if ($idMysqlServer === '' || !ctype_digit($idMysqlServer) || (int) $idMysqlServer < 1) {
            return null;
        }

        if (!array_key_exists('variable', $post) || !is_scalar($post['variable'])) {
            return null;
        }
        $varName = strtolower(trim((string) $post['variable']));
        $whitelist = self::replicationVariableWhitelist();
        if (!isset($whitelist[$varName])) {
            return null;
        }
        $spec = $whitelist[$varName];

        if (!array_key_exists('value', $post) || !is_scalar($post['value'])) {
            return null;
        }
        $rawValue = trim((string) $post['value']);

        if (($spec['type'] ?? '') === 'enum') {
            $upper = strtoupper($rawValue);
            $values = $spec['values'] ?? [];
            if (!in_array($upper, $values, true)) {
                return null;
            }
            // For numeric enums (e.g. innodb_flush_log_at_trx_commit
            // = 0/1/2/3) MariaDB rejects a quoted string with
            // "Incorrect argument type to variable …" (errno 1232).
            // Emit a bare integer literal whenever the whole picker
            // value-set is digit-only — the value is still
            // whitelist-validated so injection is impossible.
            $valuesAreNumeric = !empty($values) && array_reduce(
                $values,
                static function ($carry, $v) { return $carry && (is_string($v) || is_int($v)) && ctype_digit((string) $v); },
                true
            );
            $literal = $valuesAreNumeric
                ? (string) (int) $upper
                : "'" . $upper . "'";
            return [
                'id_mysql_server'   => (int) $idMysqlServer,
                'variable'          => $varName,
                'value'             => $upper,
                'value_sql_literal' => $literal,
            ];
        }

        if (($spec['type'] ?? '') === 'int') {
            if ($rawValue === '' || preg_match('/^-?\d+$/', $rawValue) !== 1) {
                return null;
            }
            $intVal = (int) $rawValue;
            if (isset($spec['min']) && $intVal < $spec['min']) return null;
            if (isset($spec['max']) && $intVal > $spec['max']) return null;
            return [
                'id_mysql_server'   => (int) $idMysqlServer,
                'variable'          => $varName,
                'value'             => (string) $intVal,
                'value_sql_literal' => (string) $intVal,
            ];
        }

        return null;
    }

    /**
     * Read a global variable. Returns null on silent failure so the
     * caller can surface a clean JSON error.
     */
    private static function readReplicationVariable(object $db, string $varName): ?string
    {
        if (!method_exists($db, 'sql_query_silent')) {
            return null;
        }
        // varName comes from the whitelist — safe to interpolate.
        $sql = "SELECT @@global.`" . $varName . "` AS v";
        $res = $db->sql_query_silent($sql);
        if ($res === false) {
            return null;
        }
        $row = $db->sql_fetch_array($res, MYSQLI_ASSOC);
        if (!$row || !isset($row['v'])) {
            return null;
        }
        return (string) $row['v'];
    }

    public static function evaluateSetBinlogRowImageRequest(array $param, array $post, array $server, array $session): array
    {
        if ($failure = CsrfGuard::ensureOrFail($post, $server, $session, self::SLAVE_BINLOG_ROW_IMAGE_SET_CSRF_SCOPE)) {
            return [
                'status'  => $failure['status'],
                'body'    => $failure['body'],
                'headers' => $failure['headers'],
            ];
        }

        $request = self::normalizeSetBinlogRowImagePayload($param, $post);
        if ($request === null) {
            return [
                'status'  => 400,
                'body'    => 'Invalid binlog_row_image setter payload',
                'headers' => [],
            ];
        }

        return [
            'status'  => 200,
            'body'    => '',
            'headers' => [],
            'request' => $request,
        ];
    }

    public static function normalizeSetBinlogRowImagePayload(array $param, array $post): ?array
    {
        if (!isset($param[0]) || !is_scalar($param[0])) {
            return null;
        }
        $idMysqlServer = trim((string) $param[0]);
        if ($idMysqlServer === '' || !ctype_digit($idMysqlServer) || (int) $idMysqlServer < 1) {
            return null;
        }

        if (!array_key_exists('value', $post) || !is_scalar($post['value'])) {
            return null;
        }
        $value = strtoupper(trim((string) $post['value']));
        if (!in_array($value, self::BINLOG_ROW_IMAGE_VALUES, true)) {
            return null;
        }

        return [
            'id_mysql_server' => (int) $idMysqlServer,
            'value'           => $value,
        ];
    }

    /**
     * Read the current binlog_row_image value.
     * Returns null when the query fails so the caller can surface a
     * clean JSON error instead of guessing.
     */
    private static function readBinlogRowImage(object $db): ?string
    {
        if (!method_exists($db, 'sql_query_silent')) {
            return null;
        }
        $res = $db->sql_query_silent("SELECT @@global.binlog_row_image AS v");
        if ($res === false) {
            return null;
        }
        $row = $db->sql_fetch_array($res, MYSQLI_ASSOC);
        if (!$row || !isset($row['v'])) {
            return null;
        }
        $v = strtoupper(trim((string) $row['v']));
        return $v !== '' ? $v : null;
    }

    /**
     * Shared JSON error emitter for slave/* mutating actions.
     */
    private static function sendSlaveJsonError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['error' => $message]);
    }

/**
 * Handle slave state through `startSlave`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for startSlave.
 * @phpstan-return void
 * @psalm-return void
 * @see self::startSlave()
 * @example /fr/slave/startSlave
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function startSlave($param)
    {
        $this->view = false;

        $id_mysql_server = $param[0];
        $connection_name = self::sanitizeConnectionName($param[1] ?? '');

        $db = Mysql::getDbLink($id_mysql_server);
        $isMariaDB = (stripos($db->getServerType(), 'mariadb') !== false);
        $useReplica = self::usesReplicaSyntax($db);

        try {
            $sql = self::buildReplicationCmd('START', $isMariaDB, $useReplica, $connection_name);
            $db->sql_query("$sql;");
            set_flash("success", __("Success"), $sql);
        } catch (\Exception $e) {
            set_flash("error", __("Error"), $e->getMessage());
        }

        if (!IS_CLI) {
            header('location: '.LINK.'slave/show/'.$id_mysql_server.'/'.$connection_name.'/');
        }
    }

/**
 * Handle slave state through `stopSlave`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for stopSlave.
 * @phpstan-return void
 * @psalm-return void
 * @see self::stopSlave()
 * @example /fr/slave/stopSlave
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function stopSlave($param)
    {
        $this->view = false;

        $id_mysql_server = $param[0];
        $connection_name = self::sanitizeConnectionName($param[1] ?? '');

        $db = Mysql::getDbLink($id_mysql_server);
        $isMariaDB = (stripos($db->getServerType(), 'mariadb') !== false);
        $useReplica = self::usesReplicaSyntax($db);

        try {
            $sql = self::buildReplicationCmd('STOP', $isMariaDB, $useReplica, $connection_name);
            $db->sql_query("$sql;");
            set_flash("success", __("Success"), $sql);
        } catch (\Exception $e) {
            set_flash("error", __("Error"), $e->getMessage());
        }

        if (!IS_CLI) {
            header('location: '.LINK.'slave/show/'.$id_mysql_server.'/'.$connection_name.'/');
        }
    }

/**
 * Handle slave state through `setSlave`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for setSlave.
 * @phpstan-return void
 * @psalm-return void
 * @see self::setSlave()
 * @example /fr/slave/setSlave
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function setSlave($param)
    {
        //add param force
        Debug::parseDebug($param);

        Debug::debug($param);

        $id_mysql_server__source = $param[0];
        $id_mysql_server__target = $param[1];
        $databases               = $param[2];

        $databases = explode(',', $databases);

        Debug::debug($databases, "database");

        //db source add account for replication
        $cmd2 = "openssl rand -base64 32";
        Debug::debug($cmd2);

        $slave_password = trim(shell_exec($cmd2));
        $slave_user     = "replication";

        $db_source = Mysql::getDbLink($id_mysql_server__source);
        $isMariaDB_source = (stripos($db_source->getServerType(), 'mariadb') !== false);
        if ($isMariaDB_source) {
            $db_source->sql_query("GRANT REPLICATION SLAVE, BINLOG MONITOR ON *.* TO `".$slave_user."`@`%` IDENTIFIED BY '".$db_source->sql_real_escape_string($slave_password)."'");
        } else {
            $db_source->sql_query_silent("CREATE USER IF NOT EXISTS `".$slave_user."`@`%` IDENTIFIED BY '".$db_source->sql_real_escape_string($slave_password)."'");
            $db_source->sql_query("GRANT REPLICATION SLAVE ON *.* TO `".$slave_user."`@`%`");
        }
        $db_source->sql_close();
        //end create user replication


        $db        = Sgbd::sql(DB_DEFAULT);
        $source    = $this->getInfoServer($id_mysql_server__source);
        $db_passwd = Chiffrement::decrypt($source->passwd);
        $db->sql_close();

        foreach ($databases as $database) {

            $dir = self::BACKUP_TEMP.$source->display_name."/".$database;
            shell_exec("mkdir -p ".$dir);
            $cmd = "mydumper -c -ERG --trx-consistency-only -h ".$source->ip." -u ".$source->login." -p ".$db_passwd." -B ".$database." -o '".$dir."'";
            Debug::debug($cmd, "Mydumper");

            sleep(1);
            shell_exec($cmd);

            //$pid_array[] = $this->runInBackground($cmd, );
        }

        sleep(1);

        $slaves = Mysql::getSlave(array($id_mysql_server__target));

        $servers = array_merge($slaves['slave'], array($id_mysql_server__target));

        $pid_array = array();
        foreach ($servers as $server) {
            $target = $this->getInfoServer($server);
            $db_passwd = Chiffrement::decrypt($target->passwd);

            foreach ($databases as $database) {

                $dir = self::BACKUP_TEMP.$source->display_name."/".$database;

                $cmd = "myloader -h ".$target->ip." -u ".$target->login." -p ".$db_passwd." -o -d '".$dir."'";
                Debug::debug($cmd);

                $pid_array[] = $this->runInBackground($cmd, "/tmp/".$target->ip."-".$database.'.log');
            }

            Debug::debug("------");
        }


        Debug::debug($pid_array, 'pid');

        do {

            sleep(1);

            $finished = true;
            foreach ($pid_array as $pid) {
                if ($this->isProcessRunning($pid) === false) {
                    $finished = false;
                    Debug($pid, "finished");
                }
            }
        } while ($finished);

        Debug::debug("All myloader finished");

        $db_target = Mysql::getDbLink($id_mysql_server__target);
        $isMariaDB_target = (stripos($db_target->getServerType(), 'mariadb') !== false);
        $useReplica_target = self::usesReplicaSyntax($db_target);

        $stop = self::buildReplicationCmd('STOP', $isMariaDB_target, $useReplica_target);
        $start = self::buildReplicationCmd('START', $isMariaDB_target, $useReplica_target);

        Debug::sql("$stop;");
        $db_target->sql_query("$stop;");
        if ($isMariaDB_target || !$useReplica_target) {
            $resetCmd = "RESET SLAVE ALL";
        } else {
            $resetCmd = "RESET REPLICA ALL";
        }
        Debug::sql("$resetCmd;");
        $db_target->sql_query("$resetCmd;");

        $i = 0;
        foreach ($servers as $server) {

            $i++;
            $dir = self::BACKUP_TEMP.$source->display_name."/".$database;
            Debug::debug($dir_backup, "backup_dir");

            $master_info = $this->getMasterInfo(array($dir.'/metadata'));

            if ($i === 1) {
                if ($isMariaDB_target) {
                    $sql = "CHANGE MASTER TO MASTER_HOST='".$source->ip."',MASTER_USER='".$slave_user."', MASTER_PASSWORD='".$slave_password."', MASTER_LOG_FILE='".$master_info['master_log_file']."', MASTER_LOG_POS=".$master_info['master_log_pos'].";";
                } else {
                    $sql = "CHANGE REPLICATION SOURCE TO SOURCE_HOST='".$source->ip."',SOURCE_USER='".$slave_user."', SOURCE_PASSWORD='".$slave_password."', SOURCE_LOG_FILE='".$master_info['master_log_file']."', SOURCE_LOG_POS=".$master_info['master_log_pos'].";";
                }
            } else {
                if ($isMariaDB_target || !$useReplica_target) {
                    // MariaDB et MySQL <8.0.22 : START SLAVE UNTIL avec MASTER_LOG_FILE
                    $sql = "START SLAVE UNTIL MASTER_LOG_FILE='".$master_info['master_log_file']."', MASTER_LOG_POS=".$master_info['master_log_pos'].";";
                } else {
                    $sql = "START REPLICA UNTIL SOURCE_LOG_FILE='".$master_info['master_log_file']."', SOURCE_LOG_POS=".$master_info['master_log_pos'].";";
                }
            }

            Debug::sql($sql);
            $db_target->sql_query($sql);
        }

        Debug::sql("$stop;");
        $db_target->sql_query("$stop;");

        Debug::sql("$start;");
        $db_target->sql_query("$start;");

        // set up replication
    }

/**
 * Retrieve slave state through `getInfoServer`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param int $id_mysql_server Input value for `id_mysql_server`.
 * @phpstan-param int $id_mysql_server
 * @psalm-param int $id_mysql_server
 * @return mixed Returned value for getInfoServer.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getInfoServer()
 * @example /fr/slave/getInfoServer
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function getInfoServer($id_mysql_server)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server where id=".$id_mysql_server.";";
        Debug::sql($sql);

        $res = $db->sql_query($sql);

        $data = false;
        while ($ob   = $db->sql_fetch_object($res)) {
            $data = $ob;
        }

        return $data;
    }

/**
 * Handle slave state through `runInBackground`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $command Input value for `command`.
 * @phpstan-param mixed $command
 * @psalm-param mixed $command
 * @param mixed $log Input value for `log`.
 * @phpstan-param mixed $log
 * @psalm-param mixed $log
 * @param mixed $priority Input value for `priority`.
 * @phpstan-param mixed $priority
 * @psalm-param mixed $priority
 * @return mixed Returned value for runInBackground.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::runInBackground()
 * @example /fr/slave/runInBackground
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function runInBackground($command, $log, $priority = 0)
    {
        if ($priority) {
            $PID = trim(shell_exec("nohup nice -n $priority $command > $log 2>&1 & echo $!"));
        } else {
            $PID = trim(shell_exec("nohup $command > $log 2>&1 & echo $!"));
        }


        return($PID);
    }

/**
 * Handle slave state through `isProcessRunning`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $PID Input value for `PID`.
 * @phpstan-param mixed $PID
 * @psalm-param mixed $PID
 * @return mixed Returned value for isProcessRunning.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::isProcessRunning()
 * @example /fr/slave/isProcessRunning
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function isProcessRunning($PID)
    {

        if ($PID == 0) {
            return false;
        }
        if ($PID == "") {
            return false;
        }

        $cmd = "ps -p $PID 2>&1 ";
        Debug::debug($cmd);
        exec($cmd, $state);

        Debug::debug($state, "STATE");
        Debug::debug(count($state), "STATE");
        var_dump(count($state) >= 2);
        return ( count($state) >= 2);
    }

    // /srv/backup/export-20220927-162928
    /*
     * lis les paramètre du fichier metadata (master info)
     *
     *
     */
    function getMasterInfo($param)
    {
        Debug::parseDebug($param);

        $file_metadata = $param[0];

        $metadata = file_get_contents($file_metadata);

        $output_array = array();
        preg_match_all('/SHOW MASTER STATUS\:\s+Log:\s(\S+)\s+Pos:\s(\S+)/', $metadata, $output_array);

        $data = array();

        if (!empty($output_array[1][0])) {
            $data['master_log_file'] = $output_array[1][0];
        }

        if (!empty($output_array[2][0])) {
            $data['master_log_pos'] = $output_array[2][0];
        }

        Debug::debug($data, "MASTER STATUS");

        return $data;
    }

/**
 * Handle slave state through `switchOver`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for switchOver.
 * @phpstan-return void
 * @psalm-return void
 * @throws \Throwable When the underlying operation fails.
 * @see self::switchOver()
 * @example /fr/slave/switchOver
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function switchOver($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server__old_master = $param[0];
        $id_mysql_server__new_master = $param[1];

        $slaves_old_master = Mysql::getSlave(array($id_mysql_server__old_master));
        $slaves_new_master = Mysql::getSlave(array($id_mysql_server__new_master));

        if (!in_array($id_mysql_server__new_master, $slaves_old_master['id_mysql_server'])) {
            throw new \Exception("Cannot find the new master in salve of old one");
        }

        $old_master = getDbLink($id_mysql_server__old_master);
        $new_master = getDbLink($id_mysql_server__new_master);

        if (ServerCapabilities::supports($new_master, 'show_binary_log_status')) {
            $sql = "SHOW BINARY LOG STATUS";
        } else {
            $sql = "SHOW MASTER STATUS";
        }
        Debug::debug($sql);
        $res = $new_master->sql_query($sql);

        while ($arr = $new_master->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $master_log_file = $arr['File'];
            $master_log_pos  = $arr['Position'];
        }

        $name = "gcp-prod-oos-sql-001-mariadb-g04-003.gcp.dlns.io";

        $output_array = array();
        preg_match('/(.*)-g([0-9]{2})\-([0-9]{3})/', $name, $output_array);

        if (empty($output_array[1])) {
            throw new \Exception("Impossible to find name");
        }

        if (empty($output_array[2])) {
            throw new \Exception("number of cluster");
        }
        $connection_name = $output_array[1].'g'.$output_array[2];

        $sql2 = "CHANGE MASTER '' TO MASTER_HOST='', MASTER_USER='', MASTER_PASSWORD=''";

        $sql3 = "CHANGE MASTER '' TO MASTER_LOG_FILE='".$master_log_file."', MASTER_LOG_POS='.$master_log_pos.'";

        //test if old_master is on proxysql
    }


    public function setupSource($param)
    {
        $this->view = false;
        $idForRedirect = self::normalizeSetupSourceServerId($param[0] ?? null) ?? 0;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('location: '.LINK.'slave/show/'.$idForRedirect.'/');
            return;
        }

        $outcome = self::evaluateSetupSourceRequest($param, $_POST, $_SERVER, $_SESSION);
        if ($outcome['status'] !== 200) {
            set_flash("error", __("Error"), __($outcome['body']));
            header('location: '.LINK.'slave/show/'.$idForRedirect.'/__new__/');
            return;
        }

        $request = $outcome['request'];
        $id_mysql_server = $request['id_mysql_server'];
        $connection_name = $request['connection_name'];
        $master_host = $request['master_host'];
        $master_port = $request['master_port'];
        $master_user = $request['master_user'];
        $master_password = $request['master_password'];
        $use_gtid = $request['use_gtid'];
        $use_ssl = $request['use_ssl'];
        $replicate_do_db = $request['replicate_do_db'];
        $replicate_rewrite_db = $request['replicate_rewrite_db'];

        $db = Mysql::getDbLink($id_mysql_server);
        $isMariaDB = (stripos($db->getServerType(), 'mariadb') !== false);
        $useReplica = self::usesReplicaSyntax($db);

        try {
            if ($isMariaDB) {
                $sql = "CHANGE MASTER '$connection_name' TO "
                    ."MASTER_HOST='".$db->sql_real_escape_string($master_host)."', "
                    ."MASTER_PORT=$master_port, "
                    ."MASTER_USER='".$db->sql_real_escape_string($master_user)."', "
                    ."MASTER_PASSWORD='".$db->sql_real_escape_string($master_password)."'";
                if ($use_gtid) {
                    $sql .= ", MASTER_USE_GTID=slave_pos";
                }
                if ($use_ssl) {
                    $sql .= ", MASTER_SSL=1";
                }
                $db->sql_query($sql.";");

                if ($replicate_do_db !== '') {
                    $dbParts = [];
                    foreach (explode(',', $replicate_do_db) as $dbName) {
                        $dbName = trim($dbName);
                        if ($dbName !== '') {
                            $dbParts[] = $connection_name.':'.$db->sql_real_escape_string($dbName);
                        }
                    }
                    if (!empty($dbParts)) {
                        $db->sql_query("SET GLOBAL replicate_do_db='".implode(',', $dbParts)."';");
                    }
                }

                if ($replicate_rewrite_db !== '') {
                    $db->sql_query("SET GLOBAL replicate_rewrite_db='$connection_name:(".$db->sql_real_escape_string($replicate_rewrite_db).")';");
                }

                $db->sql_query("START SLAVE '$connection_name';");
            } else {
                $escapedCn = $db->sql_real_escape_string($connection_name);
                $sql = "CHANGE REPLICATION SOURCE TO "
                    ."SOURCE_HOST='".$db->sql_real_escape_string($master_host)."', "
                    ."SOURCE_PORT=$master_port, "
                    ."SOURCE_USER='".$db->sql_real_escape_string($master_user)."', "
                    ."SOURCE_PASSWORD='".$db->sql_real_escape_string($master_password)."'";
                if ($use_gtid) {
                    $sql .= ", SOURCE_AUTO_POSITION=1";
                }
                if ($use_ssl) {
                    $sql .= ", SOURCE_SSL=1";
                }
                $sql .= " FOR CHANNEL '$escapedCn'";
                $db->sql_query($sql.";");

                if ($replicate_do_db !== '') {
                    $db->sql_query("CHANGE REPLICATION FILTER REPLICATE_DO_DB=(".
                        implode(',', array_map(function($d) use ($db) {
                            return "'".$db->sql_real_escape_string(trim($d))."'";
                        }, explode(',', $replicate_do_db))).
                        ") FOR CHANNEL '$escapedCn';");
                }

                $db->sql_query(self::buildReplicationCmd('START', false, $useReplica, $connection_name).";");
            }

            set_flash("success", __("Success"), __("Replication source created and started").": $connection_name");
            header('location: '.LINK.'slave/show/'.$id_mysql_server.'/'.$connection_name.'/');
        } catch (\Exception $e) {
            set_flash("error", __("Error"), $e->getMessage());
            header('location: '.LINK.'slave/show/'.$id_mysql_server.'/__new__/');
        }
    }

    public static function evaluateSetupSourceRequest(array $param, array $post, array $server, array $session): array
    {
        if ($failure = CsrfGuard::ensureOrFail($post, $server, $session, self::SLAVE_SETUP_SOURCE_CSRF_SCOPE)) {
            return self::buildSetupSourceOutcome($failure['status'], $failure['body'], $failure['headers']);
        }

        $request = self::normalizeSetupSourcePayload($param, $post);
        if ($request === null) {
            return self::buildSetupSourceOutcome(400, 'Invalid replication source payload');
        }

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'request' => $request,
        ];
    }

    public static function normalizeSetupSourcePayload(array $param, array $post): ?array
    {
        $idMysqlServer = self::normalizeSetupSourceServerId($param[0] ?? null);
        if ($idMysqlServer === null) {
            return null;
        }

        foreach (['connection_name', 'master_host', 'master_user', 'master_password'] as $field) {
            if (!array_key_exists($field, $post) || !is_scalar($post[$field])) {
                return null;
            }
        }

        $connectionName = self::sanitizeConnectionName(trim((string) $post['connection_name']));
        $masterHost = trim((string) $post['master_host']);
        $masterUser = trim((string) $post['master_user']);
        $masterPassword = (string) $post['master_password'];

        if ($connectionName === '' || $masterHost === '' || $masterUser === '') {
            return null;
        }

        $masterPort = self::normalizeSetupSourcePort($post['master_port'] ?? '3306');
        if ($masterPort === null) {
            return null;
        }

        $replicateDoDb = self::normalizeSetupSourceOptionalString($post['replicate_do_db'] ?? '');
        $replicateRewriteDb = self::normalizeSetupSourceOptionalString($post['replicate_rewrite_db'] ?? '');
        if ($replicateDoDb === null || $replicateRewriteDb === null) {
            return null;
        }

        return [
            'id_mysql_server' => $idMysqlServer,
            'connection_name' => $connectionName,
            'master_host' => $masterHost,
            'master_port' => $masterPort,
            'master_user' => $masterUser,
            'master_password' => $masterPassword,
            'use_gtid' => self::normalizeSetupSourceCheckbox($post, 'use_gtid'),
            'use_ssl' => self::normalizeSetupSourceCheckbox($post, 'use_ssl'),
            'replicate_do_db' => $replicateDoDb,
            'replicate_rewrite_db' => $replicateRewriteDb,
        ];
    }

    private static function normalizeSetupSourceServerId($value): ?int
    {
        if (!is_scalar($value)) {
            return null;
        }

        $id = trim((string) $value);
        if ($id === '' || !ctype_digit($id) || (int) $id < 1) {
            return null;
        }

        return (int) $id;
    }

    private static function normalizeSetupSourcePort($value): ?int
    {
        if (!is_scalar($value)) {
            return null;
        }

        $port = trim((string) $value);
        if ($port === '' || !ctype_digit($port)) {
            return null;
        }

        $portNumber = (int) $port;
        if ($portNumber < 1 || $portNumber > 65535) {
            return null;
        }

        return $portNumber;
    }

    private static function normalizeSetupSourceOptionalString($value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        return trim((string) $value);
    }

    private static function normalizeSetupSourceCheckbox(array $post, string $field): bool
    {
        return isset($post[$field]) && is_scalar($post[$field]) && (string) $post[$field] !== '';
    }

    private static function buildSetupSourceOutcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'request' => null,
        ];
    }

/**
 * Handle slave state through `activateGtid`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for activateGtid.
 * @phpstan-return void
 * @psalm-return void
 * @see self::activateGtid()
 * @example /fr/slave/activateGtid
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function activateGtid($param)
    {
        $id_mysql_server = $param[0];
        $connection_name = self::sanitizeConnectionName($param[1] ?? '');

        $this->view = false;

        $db = Mysql::getDbLink($id_mysql_server);
        $isMariaDB = (stripos($db->getServerType(), 'mariadb') !== false);
        $useReplica = self::usesReplicaSyntax($db);

        // Issue #1194 follow-up — server-side guard mirroring the UI
        // grey-out. The disabled <a> in the view is purely cosmetic;
        // a bookmark, browser history entry or copy-pasted URL would
        // otherwise still bypass the check and brick replication.
        // Refuse the cross-family transition here, before any STOP /
        // CHANGE is sent.
        $id_master_compat = Mysql::getMaster($id_mysql_server, $connection_name);
        $gtidCompat = self::evaluateGtidActivationCompatibility(
            (string) $db->getServerType(),
            !empty($id_master_compat) ? (int) $id_master_compat : null
        );
        if ($gtidCompat['compatible'] === false) {
            set_flash("error", __("Error"), $gtidCompat['reason']);
            header('location: '.LINK.'slave/show/'.$id_mysql_server.'/'.$connection_name.'/');
            return;
        }

        try {
            if ($isMariaDB) {
                $db->sql_query(self::buildReplicationCmd('STOP', true, false, $connection_name).";");
                $connClause = !empty($connection_name) ? " '$connection_name' " : "";
                $db->sql_query("CHANGE MASTER $connClause TO MASTER_USE_GTID = slave_pos;");
                $db->sql_query(self::buildReplicationCmd('START', true, false, $connection_name).";");
            } else {
                // MySQL: enable GTID on master first, then slave, using SET PERSIST for durability
                $id_master = Mysql::getMaster($id_mysql_server, $connection_name);
                if (!empty($id_master)) {
                    $db_master = Mysql::getDbLink($id_master);
                    self::enableMySQLGtid($db_master);
                }

                self::enableMySQLGtid($db);

                $channelClause = !empty($connection_name) ? " FOR CHANNEL '$connection_name'" : "";
                $db->sql_query(self::buildReplicationCmd('STOP', false, $useReplica, $connection_name).";");
                $db->sql_query("CHANGE REPLICATION SOURCE TO SOURCE_AUTO_POSITION = 1 $channelClause;");
                $db->sql_query(self::buildReplicationCmd('START', false, $useReplica, $connection_name).";");
            }
            set_flash("success", __("Success"), __("GTID Activated"));
        } catch (\Exception $e) {
            set_flash("error", __("Error"), $e->getMessage());
        }

        header('location: '.LINK.'slave/show/'.$id_mysql_server.'/'.$connection_name.'/');
    }


/**
 * Handle slave state through `deactivateGtid`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for deactivateGtid.
 * @phpstan-return void
 * @psalm-return void
 * @see self::deactivateGtid()
 * @example /fr/slave/deactivateGtid
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function deactivateGtid($param)
    {
        $id_mysql_server = $param[0];
        $connection_name = self::sanitizeConnectionName($param[1] ?? '');

        $this->view = false;

        $db = Mysql::getDbLink($id_mysql_server);
        $isMariaDB = (stripos($db->getServerType(), 'mariadb') !== false);
        $useReplica = self::usesReplicaSyntax($db);

        try {
            if ($isMariaDB) {
                $db->sql_query(self::buildReplicationCmd('STOP', true, false, $connection_name).";");
                $connClause = !empty($connection_name) ? " '$connection_name' " : "";
                $db->sql_query("CHANGE MASTER $connClause TO MASTER_USE_GTID = no;");
                $db->sql_query(self::buildReplicationCmd('START', true, false, $connection_name).";");
            } else {
                $channelClause = !empty($connection_name) ? " FOR CHANNEL '$connection_name'" : "";
                $db->sql_query(self::buildReplicationCmd('STOP', false, $useReplica, $connection_name).";");
                $db->sql_query("CHANGE REPLICATION SOURCE TO SOURCE_AUTO_POSITION = 0 $channelClause;");

                // Disable GTID on slave, then master
                self::disableMySQLGtid($db);

                $id_master = Mysql::getMaster($id_mysql_server, $connection_name);
                if (!empty($id_master)) {
                    $db_master = Mysql::getDbLink($id_master);
                    self::disableMySQLGtid($db_master);
                }

                $db->sql_query(self::buildReplicationCmd('START', false, $useReplica, $connection_name).";");
            }
            set_flash("success", __("Success"), __("GTID Deactivated"));
        } catch (\Exception $e) {
            set_flash("error", __("Error"), $e->getMessage());
        }

        header('location: '.LINK.'slave/show/'.$id_mysql_server.'/'.$connection_name.'/');
    }

/**
 * Handle slave state through `skipCounter`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for skipCounter.
 * @phpstan-return void
 * @psalm-return void
 * @see self::skipCounter()
 * @example /fr/slave/skipCounter
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function skipCounter($param)
    {
        $this->view = false;

        $id_mysql_server = $param[0];
        $connection_name = self::sanitizeConnectionName($param[1] ?? '');

        $db = Mysql::getDbLink($id_mysql_server);
        $isMariaDB = (stripos($db->getServerType(), 'mariadb') !== false);
        $useReplica = self::usesReplicaSyntax($db);

        try {
            if ($isMariaDB) {
                // MariaDB: sql_slave_skip_counter works with or without GTID
                $db->sql_query(self::buildReplicationCmd('STOP', true, false, $connection_name).";");
                $db->sql_query("SET GLOBAL sql_slave_skip_counter=1;");
                $db->sql_query(self::buildReplicationCmd('START', true, false, $connection_name).";");
            } else {
                // MySQL: check if GTID is enabled
                $res = $db->sql_query("SELECT @@GLOBAL.gtid_mode AS val");
                $gtidMode = '';
                if ($res && $row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                    $gtidMode = strtoupper($row['val']);
                }

                $db->sql_query(self::buildReplicationCmd('STOP', false, $useReplica, $connection_name).";");

                if ($gtidMode === 'ON') {
                    // With GTID: inject an empty transaction for the next pending GTID
                    $gtidToSkip = self::getNextPendingGtid($db, $connection_name);
                    if (empty($gtidToSkip)) {
                        throw new \Exception(__("Cannot determine GTID to skip. No pending transaction found."));
                    }
                    $db->sql_query("SET GTID_NEXT = '".$db->sql_real_escape_string($gtidToSkip)."';");
                    $db->sql_query("BEGIN;");
                    $db->sql_query("COMMIT;");
                    $db->sql_query("SET GTID_NEXT = 'AUTOMATIC';");
                } else {
                    // Without GTID: classic skip counter
                    $db->sql_query("SET GLOBAL sql_slave_skip_counter=1;");
                }

                $db->sql_query(self::buildReplicationCmd('START', false, $useReplica, $connection_name).";");
            }
            set_flash("success", __("Success"), __("Skipped 1 transaction"));
        } catch (\Exception $e) {
            set_flash("error", __("Error"), $e->getMessage());
        }

        if (!IS_CLI) {
            header('location: '.LINK.'slave/show/'.$id_mysql_server.'/'.$connection_name.'/');
        }
    }

    /**
     * Find the next GTID to skip for a MySQL replica channel.
     * Computes GTID_SUBTRACT(Retrieved_Gtid_Set, Executed_Gtid_Set) and
     * returns the first GTID from the result (uuid:seq).
     */
    private static function getNextPendingGtid($db, string $connectionName): string
    {
        $channelFilter = $db->sql_real_escape_string($connectionName);

        // Use performance_schema for precise per-channel data
        $sql = "SELECT GTID_SUBTRACT(
            (SELECT Received_transaction_set FROM performance_schema.replication_connection_status WHERE Channel_Name = '$channelFilter'),
            @@GLOBAL.gtid_executed
        ) AS pending";

        $res = $db->sql_query_silent($sql);
        if ($res && $row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $pending = trim($row['pending'] ?? '');
            if ($pending !== '') {
                // pending is like "uuid:5-8,uuid2:3". Take the first uuid:first_seq.
                $firstSet = explode(',', $pending)[0];
                $parts = explode(':', trim($firstSet));
                if (count($parts) === 2) {
                    $uuid = $parts[0];
                    $seqRange = $parts[1];
                    $firstSeq = explode('-', $seqRange)[0];
                    return $uuid.':'.$firstSeq;
                }
            }
        }

        // Fallback: get Source_UUID and Executed_Gtid_Set from SHOW {SLAVE|REPLICA} STATUS
        // (REPLICA syntax requires MySQL >= 8.0.22, see gh#144)
        $showSql = self::usesReplicaSyntax($db) ? "SHOW REPLICA STATUS" : "SHOW SLAVE STATUS";
        if (!empty($connectionName)) {
            $showSql .= " FOR CHANNEL '$channelFilter'";
        }
        $res = $db->sql_query_silent($showSql);
        if ($res && $row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $sourceUuid = $row['Source_UUID'] ?? $row['Master_UUID'] ?? '';
            $executed = $row['Executed_Gtid_Set'] ?? '';

            if ($sourceUuid !== '' && $executed !== '') {
                // Find the highest sequence for this source UUID in executed set
                $lastSeq = 0;
                foreach (explode(',', $executed) as $part) {
                    $part = trim($part);
                    if (stripos($part, $sourceUuid) === 0) {
                        $seqPart = explode(':', $part)[1] ?? '0';
                        $ranges = explode('-', $seqPart);
                        $lastSeq = (int)end($ranges);
                    }
                }
                return $sourceUuid.':'.($lastSeq + 1);
            }
        }

        return '';
    }


    /**
     * Extrait le fichier binaire, la position et le nom de la base de données à partir d'une ligne de log.
     *
     * @param string $line Ligne de log au format "mariadb-bin.009564 2735165 /srv/backup/..."
     * @return array|null Tableau associatif avec les clés 'binlog_file', 'binlog_pos' et 'db_name', ou null si la ligne est invalide.
     */
    function extractBinlogInfo($line) {
        // Utilisation d'une expression régulière pour extraire les informations
        $pattern = '/^([^\s]+)\s+(\d+)\s+.+\/([^\/]+)_\d{4}-\d{2}-\d{2}_\d{2}h\d{2}m\.[^\.]+\.sql\.gz$/';
        if (preg_match($pattern, trim($line), $matches)) {
            return [
                'binlog_file' => $matches[1],
                'binlog_pos'  => $matches[2],
                'db_name'     => $matches[3],
            ];
        }
        return [];
    }



    /**
     * Regroupe les informations de binlog par fichier et position, avec un GROUP_CONCAT sur les noms de bases.
     *
     * @param array $extractedData Tableau d'informations extraites (binlog_file, binlog_pos, db_name).
     * @return array Tableau regroupé par binlog_file et binlog_pos, avec les db_name concaténés.
     */
    function groupBinlogInfoByPosition(array $extractedData) {
        $grouped = [];

        foreach ($extractedData as $entry) {
            $key = $entry['binlog_file'] . '|' . $entry['binlog_pos'];
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'binlog_file' => $entry['binlog_file'],
                    'binlog_pos'  => $entry['binlog_pos'],
                    'db_names'    => [],
                ];
            }
            $grouped[$key]['db_names'][] = $entry['db_name'];
        }

        // Concaténation des noms de bases avec une virgule
        foreach ($grouped as &$group) {
            $group['db_names'] = implode(', ', $group['db_names']);
        }

        //Debug::debug($grouped, "GROUPED");

        // Réindexer le tableau pour une sortie plus propre
        return array_values($grouped);
    }



/**
 * Handle slave state through `processBinlogFile`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for processBinlogFile.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @see self::processBinlogFile()
 * @example /fr/slave/processBinlogFile
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function processBinlogFile($param) {

        Debug::parseDebug($param);

        $filePath = $param[0] ?? '';

        if (!file_exists($filePath)) {
            throw new \Exception("Le fichier $filePath n'existe pas.");
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $extractedData = [];

        foreach ($lines as $line) {
            $info = $this->extractBinlogInfo($line);
            if ($info !== null) {
                $extractedData[] = $info;
            }
        }

        return $this->groupBinlogInfoByPosition($extractedData);
    }


/**
 * Handle slave state through `generateCmd`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for generateCmd.
 * @phpstan-return void
 * @psalm-return void
 * @see self::generateCmd()
 * @example /fr/slave/generateCmd
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function generateCmd($param)
    {
        Debug::parseDebug($param);
        $DRY_RUN = DryRun::parseDryRun($param);

        Debug::debug($DRY_RUN, "--dry-run");

        $elems = $this->processBinlogFile($param);

        $id_mysql_server__master = $param[1] ?? '';
        $id_mysql_server__slave = $param[2] ?? '';

        $master_host = $param[3] ?? '';

        $user = "replication_pmacontrol";
        $password = $this->generateSecurePassword();

        $master = "MASTER";
        $slave = "SLAVE";
        if (! $DRY_RUN) {
            $master = Mysql::getDbLink($id_mysql_server__master);
            $slave = Mysql::getDbLink($id_mysql_server__slave);
        }

/**
 * Handle slave state through `execute`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param mixed $sql Input value for `sql`.
 * @phpstan-param mixed $sql
 * @psalm-param mixed $sql
 * @param mixed $db Input value for `db`.
 * @phpstan-param mixed $db
 * @psalm-param mixed $db
 * @param mixed $DRY_RUN Input value for `DRY_RUN`.
 * @phpstan-param mixed $DRY_RUN
 * @psalm-param mixed $DRY_RUN
 * @return void Returned value for execute.
 * @phpstan-return void
 * @psalm-return void
 * @see self::execute()
 * @example /fr/slave/execute
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
        function execute($sql, $db, $DRY_RUN)
        {
            if ($DRY_RUN === true) {
                //echo "$db > $sql\n";
                Debug::sql($sql);
            }
            else{
                echo "[".date("Y-m-d H:i:s")."] $sql";
                $db->sql_query($sql);
            }
        }



        // Detect fork + REPLICA-vs-SLAVE syntax (gh#144) for the slave
        $isMariaDB_slave = true;
        $useReplica_slave = false;
        if ($DRY_RUN === false && is_object($slave)) {
            $isMariaDB_slave = (stripos($slave->getServerType(), 'mariadb') !== false);
            $useReplica_slave = self::usesReplicaSyntax($slave);
        }
        $isMariaDB_master = true;
        if ($DRY_RUN === false && is_object($master)) {
            $isMariaDB_master = (stripos($master->getServerType(), 'mariadb') !== false);
        }

        if ($isMariaDB_master) {
            $sql = "GRANT REPLICATION SLAVE, REPLICATION CLIENT ON *.* TO '".$user."'@'%' IDENTIFIED BY '".$password."';";
        } else {
            $sql = "CREATE USER IF NOT EXISTS '".$user."'@'%' IDENTIFIED BY '".$password."'; GRANT REPLICATION SLAVE ON *.* TO '".$user."'@'%';";
        }
        execute($sql, $master, $DRY_RUN);

        $databases = "";
        $i = 0;

        foreach ($elems as $elem) {
            $i++;

            if ($i === 1) {
                $databases .= $elem['db_names'];

                $sql = ($isMariaDB_slave || !$useReplica_slave) ? "RESET SLAVE ALL;" : "RESET REPLICA ALL;";
                execute($sql, $slave, $DRY_RUN);

                if ($isMariaDB_slave) {
                    $sql = "CHANGE MASTER TO MASTER_HOST='".$master_host."', MASTER_USER='".$user."', MASTER_PASSWORD='".$password."',
                    MASTER_SSL=0, MASTER_SSL_VERIFY_SERVER_CERT=0,
                    MASTER_LOG_FILE='".$elem['binlog_file']."', MASTER_LOG_POS=".$elem['binlog_pos'].";";
                } else {
                    $sql = "CHANGE REPLICATION SOURCE TO SOURCE_HOST='".$master_host."', SOURCE_USER='".$user."', SOURCE_PASSWORD='".$password."',
                    SOURCE_SSL=0, SOURCE_SSL_VERIFY_SERVER_CERT=0,
                    SOURCE_LOG_FILE='".$elem['binlog_file']."', SOURCE_LOG_POS=".$elem['binlog_pos'].";";
                }
                execute($sql, $slave, $DRY_RUN);

                $sql = "SET GLOBAL replicate_do_db='".$databases."';";
                execute($sql, $slave, $DRY_RUN);
            } else {
                $databases .= ",".$elem['db_names'];

                if ($isMariaDB_slave || !$useReplica_slave) {
                    $sql = "START SLAVE UNTIL MASTER_LOG_FILE='".$elem['binlog_file']."', MASTER_LOG_POS=".$elem['binlog_pos'].";";
                } else {
                    $sql = "START REPLICA UNTIL SOURCE_LOG_FILE='".$elem['binlog_file']."', SOURCE_LOG_POS=".$elem['binlog_pos'].";";
                }
                execute($sql, $slave, $DRY_RUN);

                if ($DRY_RUN === false) {
                    $this->waitForSlavePosition([$id_mysql_server__slave, $elem['binlog_file'], $elem['binlog_pos']]);
                }
                execute(self::buildReplicationCmd('STOP', $isMariaDB_slave, $useReplica_slave).";", $slave, $DRY_RUN);

                $sql = "SET GLOBAL replicate_do_db='".$databases."';";
                execute($sql, $slave, $DRY_RUN);
            }
        }

        execute(self::buildReplicationCmd('STOP', $isMariaDB_slave, $useReplica_slave).";", $slave, $DRY_RUN);

        $sql = "SET GLOBAL replicate_do_db='';";
        execute($sql, $slave, $DRY_RUN);
        execute(self::buildReplicationCmd('START', $isMariaDB_slave, $useReplica_slave).";", $slave, $DRY_RUN);

        
    }



    /**
     * Génère un mot de passe aléatoire sécurisé, avec au moins une minuscule, une majuscule, un chiffre et un caractère spécial,
     * et sans guillemets simples ni doubles.
     *
     * @param int $length Longueur du mot de passe (par défaut : 16).
     * @return string Mot de passe généré.
     */
    function generateSecurePassword($length = 16) {
        // Définition des ensembles de caractères
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $digits = '0123456789';
        $specialChars = '!@#$%^&*()_+-=[]{}|;:,.<>?';

        // On s'assure que chaque type de caractère est présent
        $password = [
            $lowercase[random_int(0, strlen($lowercase) - 1)],
            $uppercase[random_int(0, strlen($uppercase) - 1)],
            $digits[random_int(0, strlen($digits) - 1)],
            $specialChars[random_int(0, strlen($specialChars) - 1)]
        ];

        // Remplissage du reste du mot de passe avec tous les caractères autorisés
        $allChars = $lowercase . $uppercase . $digits . $specialChars;
        $allCharsLength = strlen($allChars);

        for ($i = 4; $i < $length; $i++) {
            $password[] = $allChars[random_int(0, $allCharsLength - 1)];
        }

        // Mélange des caractères pour éviter une séquence prévisible
        shuffle($password);

        // Conversion du tableau en chaîne
        return implode('', $password);
    }

    /**
     * Vérifie si le slave a atteint la position spécifiée dans le binlog.
     *
     * @param string $masterLogFile Fichier de binlog à atteindre.
     * @param int $masterLogPos Position dans le binlog à atteindre.
     * @param int $timeout Délai maximal d'attente en secondes (par défaut : 3600).
     * @param int $interval Intervalle entre les vérifications en secondes (par défaut : 5).
     * @return bool True si la position est atteinte, false si le délai est dépassé.
     * @throws Exception En cas d'erreur lors de la vérification du statut du slave.
     */
    function waitForSlavePosition($param) {

        Debug::parseDebug($param);
        Debug::debug($param);

        $startTime = time();
        $timeout = 3600;

        $id_mysql_server = $param[0] ?? "";
        $binlog_file = $param[1];
        $binlog_pos = $param[2];
        
        while (true) {
            $db = Mysql::getDbLink($id_mysql_server, "SLAVE");
            $useReplica = self::usesReplicaSyntax($db);

            // Vérification du timeout
            if (time() - $startTime > $timeout) {
                return false;
            }

            // Exécution de SHOW SLAVE/REPLICA STATUS (gh#144)
            $showCmd = $useReplica ? "SHOW REPLICA STATUS" : "SHOW SLAVE STATUS";
            $result = $db->sql_query($showCmd);
            if (!$result) {
                throw new \Exception("Erreur lors de l'exécution de $showCmd : " . $db->sql_error());
            }

            $slaveStatus = array_change_key_case($db->sql_fetch_array($result, MYSQLI_ASSOC));
            $db->sql_free_result($result);
            // Vérifie qu'on n'a pas dépassé le fichier binlog ciblé
            
            
            
            if (
                isset($slaveStatus['relay_master_log_file']) &&
                strnatcmp($slaveStatus['relay_master_log_file'], $binlog_file) > 0
            ) {
                $db->sql_close();
                throw new \Exception(
                    "waitForSlavePosition aborted: slave binlog file {$slaveStatus['relay_master_log_file']} exceeded requested {$binlog_file}"
                );
            }

            // Vérification si la position est atteinte
            if (
                isset($slaveStatus['relay_master_log_file'], $slaveStatus['exec_master_log_pos']) &&
                $slaveStatus['relay_master_log_file'] === $binlog_file &&
                $slaveStatus['exec_master_log_pos'] >= $binlog_pos
            ) {
                Debug::debug("relay_master_log_file : ".$slaveStatus['relay_master_log_file']." - exec_master_log_pos : ".$slaveStatus['exec_master_log_pos'], "");

                $db->sql_close();
                return true;
            }


            $slaveSqlError   = trim($slaveStatus['last_sql_error'] ?? '');
            $slaveIoError    = trim($slaveStatus['last_io_error'] ?? '');
            $slaveSqlErrno   = (int)($slaveStatus['last_sql_errno'] ?? 0);
            $slaveIoErrno    = (int)($slaveStatus['last_io_errno'] ?? 0);

            $hasSqlError = $slaveSqlErrno !== 0 || $slaveSqlError !== '';
            $hasIoError  = $slaveIoErrno !== 0 || $slaveIoError !== '';

            if ($hasSqlError || $hasIoError) {
                Debug::debug($slaveStatus, "Replication error detected, aborting wait");
                $message = sprintf(
                    "SQL[%d/%s] IO[%d/%s]",
                    $slaveSqlErrno,
                    $slaveSqlError !== '' ? $slaveSqlError : 'No error',
                    $slaveIoErrno,
                    $slaveIoError !== '' ? $slaveIoError : 'No error'
                );
                Debug::debug($message, "Replication error message");
                $db->sql_close();
                throw new \Exception("waitForSlavePosition aborted due to replication error: " . $message);
            }

            Debug::debug("master_log_file : ".$slaveStatus['master_log_file']." - exec_master_log_pos : ".$slaveStatus['exec_master_log_pos'], "");
            unset($slaveStatus);

            

            // Attente avant la prochaine vérification
            sleep(1);
        }


    }

    // =========================================================================
    //  Binlog Analysis — AJAX endpoints
    // =========================================================================

    /**
     * AJAX: Start a binlog analysis for a given slave/master and time range.
     * POST /slave/startBinlogAnalysis/<id_mysql_server>/
     * Params: time_start, time_end, connection_name
     * Returns JSON: { id: <analysis_id>, status: 'pending' }
     */
    public function startBinlogAnalysis($param)
    {
        $this->layout_name = false;
        $this->view = false;
        header('Content-Type: application/json; charset=UTF-8');

        $outcome = self::evaluateStartBinlogAnalysisRequest($param, $_POST, $_SERVER, $_SESSION);
        if ($outcome['status'] !== 200) {
            self::sendStartBinlogAnalysisError($outcome['status'], $outcome['body'], $outcome['headers']);
            return;
        }

        $request = $outcome['request'];
        $id_mysql_server = $request['id_mysql_server'];
        $connection_name = $request['connection_name'];
        $time_start = $request['time_start'];
        $time_end = $request['time_end'];

        // Find master — buffer output to prevent SQL warnings from corrupting JSON
        ob_start();
        try {
            $master_id = Mysql::getMaster($id_mysql_server, $connection_name);
        } catch (\Throwable $e) {
            $master_id = 0;
        }
        ob_end_clean();

        if (!$master_id) {
            echo json_encode(['error' => 'Cannot find master server for this slave']);
            return;
        }

        $db = Sgbd::sql(DB_DEFAULT);

        // Table may not exist on all installations
        $check = $db->sql_query_silent("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'binlog_analysis'");
        if (!$check || $db->sql_num_rows($check) === 0) {
            echo json_encode(['error' => 'binlog_analysis table not found — run the SQL patch first']);
            return;
        }

        $criteria = self::buildInFlightBinlogAnalysisCriteria(
            $id_mysql_server,
            (int) $master_id,
            $connection_name,
            $time_start,
            $time_end
        );
        $lockName = self::buildBinlogAnalysisLockName($criteria);
        $escapedLockName = $db->sql_real_escape_string($lockName);
        $lockResult = $db->sql_query("SELECT GET_LOCK('" . $escapedLockName . "', 5) AS acquired");
        $lockRow = $lockResult ? $db->sql_fetch_array($lockResult, MYSQLI_ASSOC) : false;
        if (!$lockRow || (int) $lockRow['acquired'] !== 1) {
            echo json_encode(['error' => 'Cannot acquire binlog analysis lock']);
            return;
        }

        $analysisId = 0;
        try {
            $statusList = "'" . implode("','", array_map([$db, 'sql_real_escape_string'], $criteria['statuses'])) . "'";
            $lookupSql = "SELECT id, status
                    FROM binlog_analysis
                    WHERE id_mysql_server = " . $criteria['id_mysql_server'] . "
                      AND id_mysql_server__master = " . $criteria['id_mysql_server__master'] . "
                      AND connection_name = '" . $db->sql_real_escape_string($criteria['connection_name']) . "'
                      AND time_start = '" . $db->sql_real_escape_string($criteria['time_start']) . "'
                      AND time_end = '" . $db->sql_real_escape_string($criteria['time_end']) . "'
                      AND status IN (" . $statusList . ")
                    ORDER BY id ASC
                    LIMIT 1";
            $existing = $db->sql_query($lookupSql);
            if ($existingRow = $db->sql_fetch_array($existing, MYSQLI_ASSOC)) {
                echo json_encode([
                    'id' => (int) $existingRow['id'],
                    'status' => $existingRow['status'],
                    'reused' => true,
                ]);
                return;
            }

            $sql = "INSERT INTO binlog_analysis (id_mysql_server, id_mysql_server__master, connection_name, status, time_start, time_end, created_at)
                    VALUES (" . $id_mysql_server . ", " . (int) $master_id . ", '" . $db->sql_real_escape_string($connection_name) . "',
                    'pending', '" . $db->sql_real_escape_string($time_start) . "', '" . $db->sql_real_escape_string($time_end) . "', NOW())";
            $db->sql_query($sql);
            $analysisId = $db->sql_insert_id();
        } finally {
            $db->sql_query("SELECT RELEASE_LOCK('" . $escapedLockName . "')");
        }

        // Launch background process via Glial CLI
        $cmd = "cd " . escapeshellarg(ROOT) . " && php App/Webroot/index.php slave runBinlogAnalysisCli " . (int) $analysisId . " > /tmp/binlog_analysis_{$analysisId}.log 2>&1 &";
        exec($cmd);

        echo json_encode(['id' => $analysisId, 'status' => 'pending']);
    }

    public static function evaluateStartBinlogAnalysisRequest(array $param, array $post, array $server, array $session): array
    {
        if ($failure = CsrfGuard::ensureOrFail($post, $server, $session, self::SLAVE_BINLOG_ANALYSIS_START_CSRF_SCOPE)) {
            return self::buildStartBinlogAnalysisOutcome($failure['status'], $failure['body'], $failure['headers']);
        }

        $request = self::normalizeStartBinlogAnalysisPayload($param, $post);
        if ($request === null) {
            return self::buildStartBinlogAnalysisOutcome(400, 'Invalid binlog analysis start payload');
        }

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'request' => $request,
        ];
    }

    public static function normalizeStartBinlogAnalysisPayload(array $param, array $post): ?array
    {
        if (!isset($param[0]) || !is_scalar($param[0])) {
            return null;
        }

        $idMysqlServer = trim((string) $param[0]);
        if ($idMysqlServer === '' || !ctype_digit($idMysqlServer) || (int) $idMysqlServer < 1) {
            return null;
        }

        if (
            !array_key_exists('connection_name', $post)
            || !array_key_exists('time_start', $post)
            || !array_key_exists('time_end', $post)
            || !is_scalar($post['connection_name'])
        ) {
            return null;
        }

        $timeStart = self::normalizeBinlogAnalysisDate($post['time_start']);
        $timeEnd = self::normalizeBinlogAnalysisDate($post['time_end']);
        if ($timeStart === null || $timeEnd === null) {
            return null;
        }
        if ($timeStart >= $timeEnd) {
            return null;
        }

        return [
            'id_mysql_server' => (int) $idMysqlServer,
            'connection_name' => self::sanitizeConnectionName((string) $post['connection_name']),
            'time_start' => $timeStart,
            'time_end' => $timeEnd,
        ];
    }

    private static function normalizeBinlogAnalysisDate($value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $date = trim((string) $value);
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $date) === 1) {
            $date .= ':00';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $date) !== 1) {
            return null;
        }

        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $date);
        $errors = \DateTimeImmutable::getLastErrors();
        if (
            $parsed === false
            || ($errors !== false && ((int) $errors['warning_count'] > 0 || (int) $errors['error_count'] > 0))
        ) {
            return null;
        }

        return $parsed->format('Y-m-d H:i:s');
    }

    private static function buildStartBinlogAnalysisOutcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'request' => null,
        ];
    }

    private static function sendStartBinlogAnalysisError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['error' => $message]);
    }

    public static function buildInFlightBinlogAnalysisCriteria(
        int $idMysqlServer,
        int $idMaster,
        string $connectionName,
        string $timeStart,
        string $timeEnd
    ): array {
        return [
            'id_mysql_server' => $idMysqlServer,
            'id_mysql_server__master' => $idMaster,
            'connection_name' => $connectionName,
            'time_start' => $timeStart,
            'time_end' => $timeEnd,
            'statuses' => ['pending', 'running'],
        ];
    }

    public static function buildBinlogAnalysisLockName(array $criteria): string
    {
        $parts = [
            $criteria['id_mysql_server'] ?? '',
            $criteria['id_mysql_server__master'] ?? '',
            $criteria['connection_name'] ?? '',
            $criteria['time_start'] ?? '',
            $criteria['time_end'] ?? '',
        ];

        return 'pmacontrol:ba:' . sha1(implode("\0", array_map('strval', $parts)));
    }

    /**
     * AJAX: Get binlog analysis status/result.
     * GET /slave/binlogAnalysisResult/<analysis_id>/
     * Returns JSON with full analysis data.
     */
    public function binlogAnalysisResult($param)
    {
        $this->layout_name = false;
        $this->view = false;
        header('Content-Type: application/json');

        $analysisId = (int) $param[0];

        $db = Sgbd::sql(DB_DEFAULT);
        $res = $db->sql_query("SELECT * FROM binlog_analysis WHERE id = " . $analysisId);
        $row = $db->sql_fetch_array($res, MYSQLI_ASSOC);

        if (!$row) {
            echo json_encode(['error' => 'Analysis not found']);
            return;
        }

        // Decode JSON fields
        $row['volume_per_second'] = json_decode($row['volume_per_second'] ?? '[]', true);
        $row['top_tables'] = json_decode($row['top_tables'] ?? '[]', true);
        $row['recommendations'] = json_decode($row['recommendations'] ?? '[]', true);
        $row['parallelism_distribution'] = json_decode($row['parallelism_distribution'] ?? '{}', true);
        $row['binlog_files'] = json_decode($row['binlog_files'] ?? '[]', true);
        $row['binlog_file_ranges'] = json_decode($row['binlog_file_ranges'] ?? '[]', true);
        $row['ddl_details'] = json_decode($row['ddl_details'] ?? '[]', true);
        $row['parallelism_per_second'] = json_decode($row['parallelism_per_second'] ?? '[]', true);
        $row['progress'] = json_decode($row['progress'] ?? '[]', true);

        // Add replication lag data: raw time-series from ts_value_slave_int
        $row['lag_data'] = [];
        if ($row['status'] === 'done' && !empty($row['time_start']) && !empty($row['time_end'])) {
            ob_start();
            try {
                $slaveId = (int) $row['id_mysql_server'];
                $cn = $db->sql_real_escape_string($row['connection_name'] ?? '');
                $timeStart = $db->sql_real_escape_string($row['time_start']);
                $timeEnd = $db->sql_real_escape_string($row['time_end']);

                // Find the ts_variable IDs for lag metrics
                $varRes = $db->sql_query(
                    "SELECT id, name FROM ts_variable WHERE name IN ('seconds_behind_master','seconds_behind_source') AND radical = 'slave'"
                );
                $varIds = [];
                while ($vr = $db->sql_fetch_array($varRes, MYSQLI_ASSOC)) {
                    $varId = (int) $vr['id'];
                    $varIds[] = $varId;
                    Extraction::$variable[$varId]['name'] = $vr['name'];
                }

                if (!empty($varIds)) {
                    $varIdList = implode(',', $varIds);
                    $lagSql = "SELECT id_ts_variable, date, value FROM ts_value_slave_int
                               WHERE id_mysql_server = $slaveId
                               AND id_ts_variable IN ($varIdList)
                               AND connection_name = '$cn'
                               AND date BETWEEN '$timeStart' AND '$timeEnd'
                               ORDER BY date, id_ts_variable";
                    $lagRes = $db->sql_query_silent($lagSql);
                    if ($lagRes) {
                        $lagRows = [];
                        while ($lr = $db->sql_fetch_array($lagRes, MYSQLI_ASSOC)) {
                            $lagRows[] = [
                                'id_mysql_server' => $slaveId,
                                'connection_name' => $row['connection_name'] ?? '',
                                'id_ts_variable' => (int) $lr['id_ts_variable'],
                                'date' => $lr['date'],
                                'value' => $lr['value'],
                            ];
                        }
                        $row['lag_data'] = $this->buildBinlogAnalysisLagData($lagRows);
                    }
                }
                // Fetch threads_running for master and slave
                $trVarRes = $db->sql_query(
                    "SELECT id FROM ts_variable WHERE name = 'threads_running' AND radical = 'general' LIMIT 1"
                );
                $trVarId = 0;
                if ($trVarRes && $trRow = $db->sql_fetch_array($trVarRes, MYSQLI_ASSOC)) {
                    $trVarId = (int) $trRow['id'];
                }

                if ($trVarId > 0) {
                    $masterId = (int) $row['id_mysql_server__master'];
                    foreach (['master' => $masterId, 'slave' => $slaveId] as $role => $srvId) {
                        $trSql = "SELECT date, value FROM ts_value_general_int
                                  WHERE id_mysql_server = $srvId
                                  AND id_ts_variable = $trVarId
                                  AND date BETWEEN '$timeStart' AND '$timeEnd'
                                  ORDER BY date";
                        $trRes = $db->sql_query_silent($trSql);
                        $key = 'threads_running_' . $role;
                        $row[$key] = [];
                        if ($trRes) {
                            while ($tr = $db->sql_fetch_array($trRes, MYSQLI_ASSOC)) {
                                $row[$key][] = ['ts' => $tr['date'], 'value' => (int) $tr['value']];
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {}
            ob_end_clean();
        }

        echo json_encode($row);
    }

    /**
     * CLI: Run binlog analysis in background.
     * Called via: php App/Webroot/index.php slave runBinlogAnalysisCli <analysis_id>
     */
    public function runBinlogAnalysisCli($param)
    {
        $this->view = false;
        $analysisId = (int) $param[0];

        $analyzer = new BinlogAnalyzer($analysisId);
        $ok = $analyzer->run();

        // Send Telegram notification
        $db = Sgbd::sql(DB_DEFAULT);
        $res = $db->sql_query(
            "SELECT ba.*, ms.display_name AS slave_name, ms.ip AS slave_ip,
                    mm.display_name AS master_name, mm.ip AS master_ip
             FROM binlog_analysis ba
             JOIN mysql_server ms ON ba.id_mysql_server = ms.id
             JOIN mysql_server mm ON ba.id_mysql_server__master = mm.id
             WHERE ba.id = $analysisId"
        );
        $a = $db->sql_fetch_array($res, MYSQLI_ASSOC);

        if ($a) {
            $slaveName = $a['slave_name'] ?: $a['slave_ip'];
            $masterName = $a['master_name'] ?: $a['master_ip'];

            if ($a['status'] === 'done') {
                $totalRows = number_format($a['total_inserts'] + $a['total_updates'] + $a['total_deletes']);
                $sizeMb = round($a['total_size_bytes'] / 1048576, 1);

                $msg = "<b>Binlog Analysis Complete</b>\n"
                     . "<b>Slave:</b> " . htmlspecialchars($slaveName) . "\n"
                     . "<b>Master:</b> " . htmlspecialchars($masterName) . " (" . htmlspecialchars($a['mysql_version'] ?? '?') . ")\n"
                     . "<b>Range:</b> " . $a['time_start'] . " → " . $a['time_end'] . "\n"
                     . "━━━━━━━━━━━━━━━━━━━\n"
                     . "<b>Size:</b> {$sizeMb} MB | <b>Txn:</b> " . number_format($a['total_transactions']) . " | <b>Duration:</b> {$a['duration_seconds']}s\n"
                     . "<b>DML:</b> I:" . number_format($a['total_inserts']) . " U:" . number_format($a['total_updates']) . " D:" . number_format($a['total_deletes']) . " = {$totalRows} rows\n"
                     . "<b>Peak:</b> {$a['peak_txn_per_sec']} txn/s | <b>Avg:</b> {$a['avg_txn_per_sec']} txn/s\n"
                     . "<b>Sequential:</b> {$a['sequential_pct']}% | <b>Max parallel:</b> {$a['max_parallelism']}\n"
                     . "<b>Large txn:</b> {$a['large_txn_100k']} >100K, {$a['large_txn_500k']} >500K (max " . round($a['max_txn_size_bytes'] / 1024) . " KB)\n"
                     . "<b>Databases:</b> {$a['databases_count']}\n";

                $recs = json_decode($a['recommendations'] ?? '[]', true);
                if (!empty($recs)) {
                    $msg .= "━━━━━━━━━━━━━━━━━━━\n";
                    foreach (array_slice($recs, 0, 3) as $r) {
                        $msg .= "• " . htmlspecialchars($r) . "\n";
                    }
                }

                Telegram::broadcast($msg, 'HTML');
            } else {
                $msg = "<b>Binlog Analysis Failed</b>\n"
                     . "<b>Slave:</b> " . htmlspecialchars($slaveName) . "\n"
                     . "<b>Error:</b> " . htmlspecialchars(substr($a['error_message'] ?? 'Unknown', 0, 300));
                Telegram::broadcast($msg, 'HTML');
            }
        }
    }

    /**
     * AJAX: List past binlog analyses for a slave.
     * GET /slave/binlogAnalysisList/<id_mysql_server>/ajax:true/?connection_name=<connection_name>
     */
    public function binlogAnalysisList($param)
    {
        $this->layout_name = false;
        $this->view = false;
        header('Content-Type: application/json');

        $id_mysql_server = (int) $param[0];
        $connection_name = self::sanitizeConnectionName((string) ($_GET['connection_name'] ?? ($param[1] ?? '')));

        $db = Sgbd::sql(DB_DEFAULT);

        // Table may not exist on all installations
        $check = $db->sql_query_silent("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'binlog_analysis'");
        if (!$check || $db->sql_num_rows($check) === 0) {
            echo json_encode([]);
            return;
        }

        ob_start();
        $connection_name = $db->sql_real_escape_string($connection_name);
        $res = $db->sql_query_silent(
            "SELECT id, status, connection_name, time_start, time_end, total_transactions, total_size_bytes, duration_seconds, peak_txn_per_sec, created_at, completed_at
             FROM binlog_analysis
             WHERE id_mysql_server = $id_mysql_server
             AND connection_name = '$connection_name'
             ORDER BY created_at DESC
             LIMIT 20"
        );
        ob_end_clean();

        $rows = [];
        if ($res) {
            while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $rows[] = $row;
            }
        }

        echo json_encode($rows);
    }

    /**
     * CLI: Detect and clean up stuck binlog analyses.
     * An analysis is considered stuck if status='running' for > 10 minutes
     * and no PHP process is alive for it.
     *
     * Usage: php App/Webroot/index.php slave checkStuckAnalyses
     * Should be called periodically (e.g. every 5 minutes via cron).
     */
    public function checkStuckAnalyses($param = [])
    {
        $this->view = false;

        $db = Sgbd::sql(DB_DEFAULT);
        $log = $this->di['log'] ?? null;

        $res = $db->sql_query(
            "SELECT ba.id, ba.id_mysql_server, ba.created_at, ba.time_start, ba.time_end,
                    ms.display_name, ms.ip
             FROM binlog_analysis ba
             JOIN mysql_server ms ON ba.id_mysql_server = ms.id
             WHERE ba.status = 'running'
               AND ba.created_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE)"
        );

        $fixed = 0;
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            // Check if a process is still alive for this analysis
            $pid = trim(shell_exec("pgrep -f 'runBinlogAnalysisCli " . (int)$row['id'] . "' 2>/dev/null") ?: '');

            if (empty($pid)) {
                // Process is dead — mark as error
                $msg = "Stuck analysis #" . $row['id'] . " detected: running since " . $row['created_at']
                     . " for slave " . ($row['display_name'] ?: $row['ip']) . " (id=" . $row['id_mysql_server'] . ")"
                     . " range [" . $row['time_start'] . " → " . $row['time_end'] . "]. Process is dead, marking as error.";

                $db->sql_query(
                    "UPDATE binlog_analysis SET status = 'error', error_message = 'Process died (stuck > 10 min, no PID found)', completed_at = NOW() WHERE id = " . (int)$row['id']
                );

                if ($log) {
                    $log->error($msg);
                }

                // Also notify via Telegram
                Telegram::broadcast("<b>Binlog Analysis Stuck</b>\n" . htmlspecialchars($msg), 'HTML');

                $fixed++;
            }
        }

        if (IS_CLI) {
            echo $fixed > 0 ? "Fixed $fixed stuck analysis(es)\n" : "No stuck analyses found\n";
        }
    }

}
