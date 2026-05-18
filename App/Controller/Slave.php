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
use App\Library\FailoverPreflight;
use App\Library\GtidSet;
use App\Library\PerDatabaseLag;
use App\Library\ReplicaReconnectTracker;
use App\Library\ReplicationFiltersAudit;
use App\Library\ReplicationHeartbeatCheck;
use App\Library\ReplicationHealth;
use App\Library\ReplicationMetadataReader;
use App\Library\ReplicationRetentionForecast;
use App\Library\ReplicationSourceCoverage;
use App\Library\ReplicationStuckSqlDetector;
use App\Library\ReplicationUserSslAudit;
use App\Library\ReloadFromMaster;
use App\Library\SemiSyncAckSla;
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

        // Sparkline data (1 h of seconds_behind_master per replica) used to be
        // fetched here synchronously via `Extraction::extract(..., $graph=true)`.
        // That UNION over `ts_value_slave_*` partitions dominated the page-load
        // time, so the initial render now ships an empty sparkline cell with a
        // spinner and the data is pulled afterwards from `Slave::indexGraphs`
        // (10 s TTL cache). hydrateMasterFromAliasDns + idgraph map now derive
        // from the already-loaded $data['slave'] rows so the cell IDs the JS
        // will target remain stable.
        if (!empty($data['slave']) && is_array($data['slave'])) {
            foreach ($data['slave'] as $idSrv => $byConn) {
                if (!is_array($byConn)) {
                    continue;
                }
                foreach ($byConn as $connName => $slaveRow) {
                    if (!is_array($slaveRow)) {
                        continue;
                    }
                    $data['server']['idgraph'][$idSrv][$connName] = $idSrv.crc32((string)$connName);
                    $this->hydrateMasterFromAliasDns($data, $slaveRow);
                }
            }
        }

        $this->set('data', $data);
    }

    /**
     * AJAX/JSON endpoint feeding the /slave/index/ sparklines (Seconds Behind
     * Master + min/max/avg/std) after the page has rendered. Heavy work is
     * cached on disk for 10 seconds; subsequent reloads inside that window are
     * served from the cache and never touch the time-series tables.
     *
     * URL contract: `/{lang}/slave/indexGraphs/ajax:true/`
     */
    public function indexGraphs()
    {
        $this->layout_name = false;
        $this->view = false;

        if (!headers_sent()) {
            header('Content-Type: application/json; charset=UTF-8');
            header('Cache-Control: no-store, no-cache, must-revalidate');
        }

        $payload = self::rememberIndexGraphsPayload(10, function () {
            return $this->buildIndexGraphsPayload();
        });

        echo json_encode($payload);
        exit;
    }

    /**
     * Build the per-replica sparkline payload returned by indexGraphs(). One
     * entry per (id_mysql_server, connection_name) keyed as `"$id|$conn"` so
     * the client can look up rows directly.
     *
     * @return array{generated_at:int,by_key:array<string,array{values:array<int,float|null>,min:?float,max:?float,avg:?float,std:?float,canvas_id:string}>}
     */
    private function buildIndexGraphsPayload(): array
    {
        $rows = Extraction::extract($this->getReplicationLagVariables(), array(), "1 hour", false, true);
        $rows = $this->normalizeReplicationLagGraphRows($rows ?: []);

        $byKey = [];
        foreach ($rows as $row) {
            $idSrv = (int)($row['id_mysql_server'] ?? 0);
            $conn  = (string)($row['connection_name'] ?? '');
            $key   = $idSrv.'|'.$conn;

            $values = [];
            foreach (self::extractSparklineYValues((string)($row['graph'] ?? '')) as $raw) {
                $values[] = ($raw === 'null') ? null : (float)$raw;
            }

            $byKey[$key] = [
                'values'    => $values,
                'min'       => isset($row['min']) ? (float)$row['min'] : null,
                'max'       => isset($row['max']) ? (float)$row['max'] : null,
                'avg'       => isset($row['avg']) ? (float)$row['avg'] : null,
                'std'       => isset($row['std']) ? (float)$row['std'] : null,
                'canvas_id' => 'myChart'.$idSrv.crc32($conn),
            ];
        }

        return [
            'generated_at' => time(),
            'by_key'       => $byKey,
        ];
    }

    /**
     * 10 s on-disk cache for the indexGraphs payload, partitioned per filtered
     * server list so two users with different ACL scopes don't pollute each
     * other's view. Pattern mirrors App\Library\ServerStateTimeline::remember.
     *
     * @param callable():array $producer
     */
    private static function rememberIndexGraphsPayload(int $ttl, callable $producer): array
    {
        $cacheDir = TMP.'cache/slave-index/';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0775, true);
        }

        $servers = \App\Library\Extraction2::getServerList();
        if (is_array($servers)) {
            sort($servers, SORT_NUMERIC);
            $scope = implode(',', $servers);
        } else {
            $scope = (string)$servers;
        }
        $key = sha1($scope);
        $cacheFile = $cacheDir.'graphs_'.$key.'.json';

        if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
            $raw = @file_get_contents($cacheFile);
            if (is_string($raw) && $raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $decoded['cache_hit'] = true;
                    return $decoded;
                }
            }
        }

        $payload = $producer();
        @file_put_contents($cacheFile, json_encode($payload));
        $payload['cache_hit'] = false;

        return $payload;
    }

    private function getTunnelDetails(): array
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT local_host, local_port, remote_host, remote_port, servers_jump
                FROM ssh_tunnel PARTITION(pn)
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

        // Lag graphs (1 per day) used to be fetched here synchronously via
        // Extraction::extract + enrichSlavesWithRelayLogSpaceGraph + a JS
        // build block per day. Both Extraction calls are heavy UNIONs over
        // ts_value_slave_* / ts_value_general_*, so the page used to stall
        // 1–3 s before a single byte hit the browser. Move them behind the
        // existing /slave/showGraphDay/ AJAX endpoint (see indexGraphs
        // pattern on /slave/index/): the action carries a disk cache,
        // 10 s TTL for the current day (still mutating) and 7 days for
        // any past day (data is immutable once the partition rolls).
        $this->di['js']->addJavascript(array(
            "moment.js",
            "chart-4.5.1.umd.min.js",
            "chartjs-adapter-moment.min.js",
            "hammer.min.js",
            "chartjs-plugin-zoom.js",
            "chartjs-chart-treemap.min.js",
        ));

        $today           = date('Y-m-d');
        $yesterday       = date('Y-m-d', strtotime('-1 day'));
        $data['graph']   = [];
        // Chronological order so the oldest day renders on top and the
        // "Load previous day" button can decrement from there.
        $data['graph_days'] = [$yesterday, $today];

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

        // #1280 follow-up — source-side coverage check. Does the
        // master still hold the binlog file (or GTIDs) the replica is
        // currently reading? If not, replication is fine RIGHT NOW
        // but a relay-log loss would be unrecoverable. We reuse the
        // master's binlog files cache that the gap block above just
        // resolved, plus the cached gtid_purged from
        // ts_value_general_text.
        try {
            $data['source_coverage'] = null;
            if (!empty($data['slave']) && !empty($master_id)) {
                $coverageFiles = [];
                if (isset($files) && is_array($files)) {
                    foreach ($files as $f) {
                        if (is_string($f) && $f !== '') {
                            $coverageFiles[] = $f;
                        }
                    }
                }
                $purgedRes = $db->sql_query_silent(
                    "SELECT t.value FROM ts_value_general_text t "
                  . "INNER JOIN ts_variable v ON v.id = t.id_ts_variable "
                  . "WHERE v.name = 'gtid_purged' "
                  . "  AND t.id_mysql_server = " . (int) $master_id . " "
                  . "ORDER BY t.date DESC LIMIT 1"
                );
                $purged = null;
                if ($purgedRes && $prow = $db->sql_fetch_array($purgedRes, MYSQLI_ASSOC)) {
                    $purged = (string) $prow['value'];
                }
                $data['source_coverage'] = ReplicationSourceCoverage::evaluate(
                    $data['slave'],
                    $coverageFiles,
                    $purged
                );
            }
        } catch (\Throwable $e) {
            $data['source_coverage'] = null;
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
        $data['replication_observability'] = $this->buildReplicationObservability(
            $db,
            (int)$id_mysql_server,
            (string)$replication_name,
            $data['slave'] ?? [],
            $server ?? [],
            $master_id ? (int)$master_id : null,
            $data['all_connections'] ?? [],
            $data['binlog_gap'] ?? null
        );
        $data['replication_annotation_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['replication_annotation_save_csrf_token'] = Csrf::issueToken($_SESSION, ReplicationAnnotation::SAVE_CSRF_SCOPE);
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
     * - Right axis `y_gb` — single dataset `graph_relay_log_space`
     *   plotting "Relay log still to apply", computed as
     *   `relay_log_space − relay_log_pos` per sample, clamped to 0
     *   below 4 KB to filter the relay-log file's irreducible format
     *   overhead. Exact byte count after the SQL thread's position
     *   when only one relay log file is retained (default with
     *   relay_log_purge=ON); conservative upper bound otherwise.
     *   Indigo `#6366f1`. (#1277)
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
        $hasRelay     = $relayPayload !== '';

        // (#1277) Single right-axis dataset, indigo flat colour, no
        // per-segment up/down colouring. Plots "Relay log still to
        // apply" = `relay_log_space − relay_log_pos` per sample
        // (clamped to 0 below 4 KB to filter the relay-log file
        // format overhead).
        $relayDataset = $relayPayload !== '' ? '
            ,{
                label: "Relay log still to apply (bytes)",
                data: ['.$relayPayload.'],
                borderColor: "#6366f1",
                backgroundColor: "rgba(99,102,241,0.18)",
                fill: "origin",
                borderWidth: 1,
                pointRadius: 0,
                tension: 0,
                yAxisID: "y_gb"
            }' : '';

        // (#1277) Operator asked to drop the second "Replication lag
        // (Read − Exec, bytes)" curve — the live tile already
        // displays that number and the second curve was redundant.
        // The amber dataset is intentionally NOT appended.

        $relayScale = $hasRelay ? ',
            y_gb: {
                position: "right",
                grid: { drawOnChartArea: false },
                title: { display: true, text: "Bytes (B → MB/GB)" },
                // (#1277) Pin the right axis to 0 so the indigo /
                // amber curves sit on the same baseline as the lag
                // axis on the left. Without this, Chart.js auto-fits
                // a tiny window around a flat near-zero value and the
                // curves look like they hover mid-chart.
                beginAtZero: true,
                min: 0,
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
     * Issue #1280 — keep replication observability tied to the existing
     * /slave/show data contract. Every primitive is computed from SHOW
     * REPLICA/SLAVE STATUS, existing time-series rows or the small cache
     * columns added by the migration; no parallel data model is required.
     *
     * @return array<string,mixed>
     */
    private function buildReplicationObservability(
        object $db,
        int $serverId,
        string $replicationName,
        array $slaveRow,
        array $serverRow,
        ?int $masterId,
        array $connections,
        ?array $binlogGap
    ): array {
        $filters = ReplicationFiltersAudit::audit($slaveRow);
        $heartbeat = ReplicationHeartbeatCheck::evaluate($slaveRow);
        $ssl = ReplicationUserSslAudit::audit($slaveRow);
        $stuck = ReplicationStuckSqlDetector::detect($slaveRow, []);
        $semiSync = SemiSyncAckSla::evaluate($this->loadLatestStatusMetrics($masterId ?: $serverId, [
            'rpl_semi_sync_master_yes_tx',
            'rpl_semi_sync_master_no_tx',
            'rpl_semi_sync_master_avg_wait_time',
        ]));

        $errantSet = '';
        if (empty($serverRow['errant_gtid_ignore'])) {
            $errantSet = trim((string)($serverRow['errant_gtid_set'] ?? ''));
        }
        $sourceExecuted = (string)($slaveRow['Retrieved_Gtid_Set'] ?? '');
        $replicaExecuted = (string)($slaveRow['Executed_Gtid_Set'] ?? $slaveRow['Gtid_IO_Pos'] ?? $slaveRow['Gtid_Slave_Pos'] ?? '');
        if ($errantSet === '' && $sourceExecuted !== '' && $replicaExecuted !== '') {
            $sourceSet = GtidSet::parse($sourceExecuted);
            $replicaSet = GtidSet::parse($replicaExecuted);
            if ($sourceSet->isCompatibleWith($replicaSet)) {
                $errantSet = $replicaSet->subtract($sourceSet)->toString();
            }
        }

        $context = [
            'lag_sla_seconds' => (int)($serverRow['replica_lag_sla_seconds'] ?? 30),
            'errant_gtid_set' => $errantSet,
            'filters' => $filters,
            'semi_sync' => $semiSync,
            'ssl' => $ssl,
            'stuck_sql' => $stuck,
            'heartbeat' => $heartbeat,
            'relay_log_purge' => $this->loadLatestVariableMetric($serverId, 'relay_log_purge') ?: 'ON',
        ];
        $health = ReplicationHealth::evaluate($slaveRow, $context);

        return [
            'health' => $health,
            'gtid_drift' => [
                'errant_set' => $errantSet,
                'count' => GtidSet::parse($errantSet)->count(),
                'source_executed' => $sourceExecuted,
                'replica_executed' => $replicaExecuted,
                'sampled_at' => (string)($serverRow['errant_gtid_sampled_at'] ?? ''),
                'ignored' => !empty($serverRow['errant_gtid_ignore']),
            ],
            'channels' => $this->buildChannelDashboard($serverId, $connections),
            'stuck_sql' => $stuck,
            'filters' => $filters,
            'semi_sync' => $semiSync,
            'retention' => ReplicationRetentionForecast::forecast([
                'retained_bytes' => (int)($binlogGap['bytes'] ?? 0),
                'generation_rate_bytes_per_day' => 0,
                'disk_free_bytes' => 0,
                'configured_days' => (float)($this->loadLatestVariableMetric($masterId ?: $serverId, 'expire_logs_days') ?: 0),
            ]),
            'ssl' => $ssl,
            'per_database_lag' => PerDatabaseLag::fromWorkerRows($this->loadWorkerRows($serverId, $replicationName)),
            'reconnects' => ReplicaReconnectTracker::summarize('', []),
            'heartbeat' => $heartbeat,
            'failover_preflight' => FailoverPreflight::evaluate($slaveRow, $context + [
                'health' => $health,
                'semi_sync' => $semiSync,
                'ssl' => $ssl,
            ]),
            'annotations' => $this->loadReplicationAnnotations($db, $serverId, $replicationName),
        ];
    }

    /**
     * @return list<array<string,mixed>>
     */
    private function buildChannelDashboard(int $serverId, array $connections): array
    {
        $cards = [];
        foreach ($connections as $conn) {
            $name = (string)($conn['name'] ?? '');
            $cards[] = [
                'name' => $name,
                'label' => $name !== '' ? $name : 'default',
                'lag' => $conn['lag'] ?? null,
                'health' => (string)($conn['health'] ?? 'unknown'),
                'url' => LINK . 'slave/show/' . $serverId . '/' . urlencode($name) . '/',
            ];
        }

        return $cards;
    }

    private function loadLatestVariableMetric(int $serverId, string $name): ?string
    {
        $data = Extraction2::display(['variables::' . $name], [$serverId]);
        return isset($data[$serverId][''][$name]) ? (string)$data[$serverId][''][$name] : null;
    }

    /**
     * @param list<string> $names
     * @return array<string,mixed>
     */
    private function loadLatestStatusMetrics(int $serverId, array $names): array
    {
        $vars = array_map(static fn(string $name): string => 'status::' . $name, $names);
        $data = Extraction2::display($vars, [$serverId]);

        return isset($data[$serverId]['']) && is_array($data[$serverId]['']) ? $data[$serverId][''] : [];
    }

    /**
     * @return list<array<string,mixed>>
     */
    private function loadWorkerRows(int $serverId, string $replicationName): array
    {
        $data = Extraction2::display(['slave::replication_applier_status_by_worker'], [$serverId]);
        $raw = $data[$serverId]['@slave'][$replicationName]['replication_applier_status_by_worker']
            ?? $data[$serverId]['']['replication_applier_status_by_worker']
            ?? '';
        $decoded = is_string($raw) && $raw !== '' ? json_decode($raw, true) : [];

        return is_array($decoded) ? array_values(array_filter($decoded, 'is_array')) : [];
    }

    /**
     * @return list<array<string,mixed>>
     */
    private function loadReplicationAnnotations(object $db, int $serverId, string $replicationName): array
    {
        $check = $db->sql_query_silent("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'replication_annotation'");
        if (!$check || !$db->sql_fetch_array($check, MYSQLI_ASSOC)) {
            return [];
        }

        $serverId = (int)$serverId;
        $cn = $db->sql_real_escape_string($replicationName);
        $res = $db->sql_query(
            "SELECT * FROM replication_annotation
             WHERE (id_mysql_server = {$serverId} OR id_mysql_server = 0)
               AND (connection_name = '' OR connection_name = '{$cn}')
             ORDER BY annotation_time DESC
             LIMIT 10"
        );
        $rows = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $rows[] = $row;
        }

        return $rows;
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
        // (#1277) Two right-axis time-series, both bytes, distinct
        // meanings. Operator asked to see both at once so the chart
        // can tell a catch-up apart from a relay-log retention drop.
        //
        //   graph_relay_log_space      → Relay_Log_Space (on-disk size
        //                                 of every relay log file the
        //                                 slave still holds; flat
        //                                 between rotations, drops on
        //                                 purge).
        //   graph_replication_lag      → Read_Master_Log_Pos
        //                                 − Exec_Master_Log_Pos
        //                                 per sample (= actual
        //                                 replication lag in bytes;
        //                                 rises when IO outruns SQL,
        //                                 drops when SQL catches up).
        //
        // Both keys are populated here so `buildLagChartJs()` can emit
        // two flat-coloured datasets (no more per-segment up/down
        // colouring — see relayDataset / lagDataset blocks).

        // ── Series 1: relay log bytes still to apply ─────────────────
        // = `relay_log_space − relay_log_pos`. The relay log files
        // on disk (`relay_log_space`) minus the SQL thread's current
        // position in the active relay log file (`relay_log_pos`).
        //
        // Single-file case (default with relay_log_purge=ON): exact —
        // every byte after relay_log_pos in the only retained relay
        // log file. Multi-file case: conservative upper bound, since
        // relay_log_space may temporarily count older files that the
        // SQL thread already cleared but that haven't been purged yet.
        //
        // The previous "Relay_Log_Space raw" plot was misleading —
        // it stayed flat while the SQL thread was perfectly caught up,
        // because purge only fires on rotation. Operator wanted "size
        // of relay log left to process", which is this delta.
        $relayByDay = [];
        $db = Sgbd::sql(DB_DEFAULT);
        $relayVarRes = $db->sql_query(
            "SELECT name, id FROM ts_variable
             WHERE radical='slave' AND name IN ('relay_log_space','relay_log_pos')"
        );
        $relayVarId = [];
        while ($vr = $db->sql_fetch_array($relayVarRes, MYSQLI_ASSOC)) {
            $relayVarId[$vr['name']] = (int) $vr['id'];
        }
        if (isset($relayVarId['relay_log_space'], $relayVarId['relay_log_pos'])) {
            $dateMinR = $db->sql_real_escape_string((string) $dateRange[0]);
            $dateMaxR = $db->sql_real_escape_string((string) $dateRange[1]);
            $cnSafeR  = $db->sql_real_escape_string($replicationName);
            $serverIdR = (int) $serverId;
            $db->sql_query("SET SESSION group_concat_max_len = 100000000");
            // The raw `relay_log_space − relay_log_pos` carries a few
            // hundred bytes of irreducible format overhead at the tail
            // of each relay log file (4-byte magic + Format_description
            // event ≈ 245 B + optional Gtid_list ≈ 50 B + Rotate ≈ 30 B
            // = up to ~330 B). On an idle slave this shows as 300-500 B
            // even though the "Relay gap" tile reads 0. Anything under
            // 4 KB is treated as caught-up noise; real lag is always
            // much larger than that. Above 4 KB the value is reported
            // verbatim. (#1277)
            $relayClampBytes = 4096;
            $sqlRelay = "
                WITH relay AS (
                    SELECT sp.date AS d, sp.connection_name AS cn,
                           CASE
                               WHEN CAST(sp.value AS SIGNED) - CAST(po.value AS SIGNED) < {$relayClampBytes}
                                   THEN 0
                               ELSE CAST(sp.value AS SIGNED) - CAST(po.value AS SIGNED)
                           END AS remaining
                    FROM ts_value_slave_int sp
                    JOIN ts_value_slave_int po
                      ON po.id_mysql_server = sp.id_mysql_server
                     AND po.connection_name = sp.connection_name
                     AND po.date            = sp.date
                     AND po.id_ts_variable  = " . $relayVarId['relay_log_pos'] . "
                    WHERE sp.id_mysql_server = {$serverIdR}
                      AND sp.id_ts_variable  = " . $relayVarId['relay_log_space'] . "
                      AND sp.connection_name = '{$cnSafeR}'
                      AND sp.date BETWEEN '{$dateMinR}' AND '{$dateMaxR}'
                )
                SELECT DATE(d) AS day, cn AS connection_name,
                       GROUP_CONCAT(
                           CONCAT('{x:new Date(\\'', DATE_FORMAT(d, '%Y-%m-%dT%H:%i:%s'), '\\'),y:', remaining, '}')
                           ORDER BY d ASC
                       ) AS graph
                FROM relay GROUP BY day, cn
            ";
            $resR = $db->sql_query_silent($sqlRelay);
            if ($resR) {
                while ($r = $db->sql_fetch_array($resR, MYSQLI_ASSOC)) {
                    if (($r['connection_name'] ?? '') !== $replicationName) continue;
                    if (!isset($r['day'], $r['graph'])) continue;
                    $relayByDay[(string) $r['day']] = (string) $r['graph'];
                }
            }
        }

        // (#1277) Series 2 (Replication lag Read − Exec) was removed —
        // the live tile already shows that number, the operator asked
        // to drop the redundant curve.
        foreach ($slaves as &$slave) {
            $dayKey = (string) ($slave['day'] ?? '');
            $slave['graph_relay_log_space'] = $relayByDay[$dayKey] ?? '';
        }
        unset($slave);

        return $slaves;
    }

    public function showGraphDay($param)
    {
        $id_mysql_server  = (int)($param[0] ?? 0);
        $day              = (string)($param[1] ?? '');
        // Glial pushes "key:value" segments into $param too, so a stale
        // browser hitting `…/<day>//ajax:true/` would land "ajax:true" in
        // the connection_name slot and silently filter every row out (#816).
        // A real connection_name can never contain ":" — sanitizeConnectionName
        // strips it — so colon-bearing segments are framework noise.
        $replication_name = self::extractConnectionNameParam($param[2] ?? '');

        $isAjax = (!empty($_GET['ajax']) && $_GET['ajax'] === 'true');

        if ($isAjax) {
            // AJAX path: bypass layout + view, write the canvas + chart
            // bootstrap JS directly so we can wrap the heavy Extraction
            // calls in a disk cache.
            $this->layout_name = false;
            $this->view        = false;

            $ttl = (preg_match('/^\d{4}-\d{2}-\d{2}$/', $day) === 1 && $day === date('Y-m-d'))
                ? 10
                : 86400 * 7;

            $payload = self::rememberShowGraphDayHtml(
                $ttl,
                $id_mysql_server,
                $replication_name,
                $day,
                function () use ($id_mysql_server, $replication_name, $day) {
                    return $this->buildShowGraphDayHtml($id_mysql_server, $replication_name, $day);
                }
            );

            echo $payload;
            exit;
        }

        // Non-AJAX path (legacy / direct URL): keep the view-based render so
        // bookmarks and tab-opens still produce a full HTML page.
        $data['graphs'] = $this->fetchShowGraphDaySlaves($id_mysql_server, $replication_name, $day);
        $this->set('data', $data);
    }

    /**
     * Pull the per-day lag rows + relay-log-space enrichment used by both
     * showGraphDay paths. Extracted so the AJAX HTML builder and the
     * legacy view path stay in sync.
     *
     * @return array<int,array<string,mixed>>
     */
    private function fetchShowGraphDaySlaves(int $id_mysql_server, string $replication_name, string $day): array
    {
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

        if ($replication_name !== '') {
            $slaves = array_values(array_filter($slaves, function ($s) use ($replication_name) {
                return ($s['connection_name'] ?? '') === $replication_name;
            }));
        }

        return $this->enrichSlavesWithRelayLogSpaceGraph(
            $slaves,
            $id_mysql_server,
            array($date_start, $date_end),
            $replication_name
        );
    }

    /**
     * Build the raw HTML (canvas wrappers + Chart.js bootstrap scripts)
     * returned to the AJAX caller. Mirrors what
     * `App/view/Slave/showGraphDay.view.php` emits — kept inline here so
     * the cache layer can capture the bytes and serve them verbatim.
     */
    private function buildShowGraphDayHtml(int $id_mysql_server, string $replication_name, string $day): string
    {
        $slaves = $this->fetchShowGraphDaySlaves($id_mysql_server, $replication_name, $day);

        $html = '';
        foreach ($slaves as $slave) {
            $canvasId = 'myChart' . $slave['id_mysql_server']
                . crc32(($slave['connection_name'] ?? '') . $slave['day']);
            $html .= '<div class="sv-chart-wrap" data-day="' . htmlspecialchars((string) $slave['day'], ENT_QUOTES, 'UTF-8') . '"><canvas id="' . $canvasId . '"></canvas></div>' . "\n";
            $html .= '<script>' . "\n" . self::buildLagChartJs($slave) . "\n" . '</script>' . "\n";
        }
        return $html;
    }

    /**
     * Disk cache for the per-day AJAX HTML built by showGraphDay().
     * TTL is chosen by the caller (10 s for the current day, 7 days for
     * any past day — past-day data is immutable once the SYSTEM_TIME
     * partition rolls).
     *
     * @param callable():string $producer
     */
    private static function rememberShowGraphDayHtml(
        int $ttl,
        int $id_mysql_server,
        string $replication_name,
        string $day,
        callable $producer
    ): string {
        $cacheDir = TMP . 'cache/slave-show/';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0775, true);
        }

        $key       = sha1($id_mysql_server . '|' . $replication_name . '|' . $day);
        $cacheFile = $cacheDir . 'lag_' . $key . '.html';

        if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
            $raw = @file_get_contents($cacheFile);
            if (is_string($raw) && $raw !== '') {
                return $raw;
            }
        }

        $payload = $producer();
        @file_put_contents($cacheFile, $payload);

        return $payload;
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

    public function failoverPreflight($param)
    {
        $this->slaveBeginJsonResponse();

        $serverId = self::normalizeSetupSourceServerId($param[0] ?? null);
        $connectionName = self::sanitizeConnectionName($param[1] ?? '');
        if ($serverId === null) {
            $this->slaveSendJson(['error' => 'Invalid server id'], 400);
        }

        try {
            $link = Mysql::getDbLink($serverId);
            $rows = self::fetchReplicaStatusRows($link);
            $selected = [];
            foreach ($rows as $row) {
                $cn = (string)($row['Connection_name'] ?? $row['Channel_Name'] ?? '');
                if ($cn === $connectionName || ($connectionName === '' && $selected === [])) {
                    $selected = $row;
                    if ($cn === $connectionName) {
                        break;
                    }
                }
            }
            $this->slaveSendJson(FailoverPreflight::evaluate($selected));
        } catch (\Throwable $e) {
            $this->slaveSendJson(['error' => 'Pre-flight failed: ' . $e->getMessage()], 503);
        }
    }

    public function replicationMetadata($param)
    {
        $this->slaveBeginJsonResponse();

        $serverId = self::normalizeSetupSourceServerId($param[0] ?? null);
        if ($serverId === null) {
            $this->slaveSendJson(['error' => 'Invalid server id'], 400);
        }

        try {
            $link = Mysql::getDbLink($serverId);
            $tables = [
                'performance_schema.replication_connection_configuration',
                'performance_schema.replication_connection_status',
                'performance_schema.replication_applier_configuration',
                'performance_schema.replication_applier_status_by_coordinator',
                'performance_schema.replication_applier_status_by_worker',
                'mysql.slave_master_info',
                'mysql.slave_relay_log_info',
            ];
            $this->slaveSendJson(ReplicationMetadataReader::read($link, $tables));
        } catch (\Throwable $e) {
            $this->slaveSendJson(['error' => 'Metadata read failed: ' . $e->getMessage()], 503);
        }
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

        $sql = ServerCapabilities::masterStatusSql($new_master);
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

    // ==================================================================
    //  "Reload from master" module — issue #1285 (operator-driven
    //  rebuild of a stale / broken replica). The whole module is wrapped
    //  in try/catch \Throwable so a single failure here can never break
    //  /slave/show — the rest of the controller has no dependency on it.
    // ==================================================================

    private const SLAVE_RELOAD_CSRF_SCOPE = 'slave.reload';

    /**
     * Render the /slave/reloadFromMaster/<id>/<conn>/ preflight page.
     * Pulls slave + master rows, latest SHOW REPLICA STATUS metric,
     * source-coverage verdict, SSH + disk probes, then hands the bundle
     * to {@see ReloadFromMaster::evaluatePreflight()} and renders the
     * `reloadFromMaster.view.php` card view.
     *
     * @param array<int,string> $param [0] id_mysql_server, [1] connection_name
     */
    public function reloadFromMaster($param): void
    {
        try {
            $this->title = '<i class="fa fa-refresh"></i> '.__("Reload from master");
            $idServer = (int) ($param[0] ?? 0);
            $conn     = (string) ($param[1] ?? '');
            $db       = Sgbd::sql(DB_DEFAULT);

            $slave  = self::reloadLoadServerRow($db, $idServer);
            $master = self::reloadLoadMasterRow($db, $slave, $conn);

            $slaveStatus    = self::reloadFetchReplicaStatus($slave, $conn);
            $sourceCoverage = self::reloadFetchSourceCoverage($idServer);
            $diskStats      = self::reloadProbeDiskStats($slave, $master);
            $sshProbe       = self::reloadProbeSsh($slave);
            $versions       = self::reloadDetectVersions($slave, $master);

            $preflight = ReloadFromMaster::evaluatePreflight(
                $slave ?? [],
                $master ?? [],
                $slaveStatus,
                $sourceCoverage,
                $diskStats,
                $sshProbe,
                $versions
            );

            // List available remote storage areas so the operator can
            // pick one as an intermediate landing zone for the
            // mydumper procedure (in addition to the local
            // DIRECTORY_BACKUP / slave_tmp options).
            $sasRows = [];
            $resSa = @$db->sql_query_silent("SELECT id, libelle, ip, port, path FROM backup_storage_area ORDER BY id");
            if ($resSa) {
                while ($row = $db->sql_fetch_array($resSa, MYSQLI_ASSOC)) {
                    $sasRows[] = $row;
                }
            }

            $data = [
                'class'                  => $this->getClass(),
                'function'               => __FUNCTION__,
                'id_mysql_server'        => $idServer,
                'connection_name'        => $conn,
                'slave'                  => $slave ?? [],
                'master'                 => $master ?? [],
                'versions'               => $versions,
                'preflight'              => $preflight,
                'source_coverage'        => $sourceCoverage,
                'mydumper_storage_areas' => $sasRows,
                'reload_csrf_field'      => Csrf::DEFAULT_FIELD,
                'reload_csrf_token'      => Csrf::issueToken($_SESSION, self::SLAVE_RELOAD_CSRF_SCOPE),
            ];
            $this->set('data', $data);
        } catch (\Throwable $e) {
            error_log('[Slave::reloadFromMaster] '.$e->getMessage());
            $this->set('data', [
                'class' => 'Slave',
                'function' => __FUNCTION__,
                'id_mysql_server' => (int) ($param[0] ?? 0),
                'connection_name' => (string) ($param[1] ?? ''),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * POST handler for the "Start reload" button.
     *
     * Re-runs the preflight server-side (never trust the rendered HTML),
     * inserts a `job` row and forks the CLI worker. Redirects to
     * /Job/index so the operator can watch the progress bar.
     *
     * @param array<int,string> $param [0] id, [1] connection_name
     */
    public function reloadFromMasterStart($param): void
    {
        try {
            $idServer = (int) ($param[0] ?? 0);
            $conn     = (string) ($param[1] ?? '');

            $fail = CsrfGuard::ensureOrFail($_POST, $_SERVER, $_SESSION, self::SLAVE_RELOAD_CSRF_SCOPE);
            if ($fail !== null) {
                http_response_code((int) $fail['status']);
                foreach (($fail['headers'] ?? []) as $k => $v) {
                    header($k.': '.$v);
                }
                echo (string) ($fail['body'] ?? '');
                return;
            }

            $procedure = (string) ($_POST['procedure'] ?? '');
            if (!in_array($procedure, ['physical', 'logical', 'mydumper'], true)) {
                http_response_code(400);
                echo 'Invalid procedure';
                return;
            }
            // mydumper-only options: where the intermediate dump lands
            // (slave_tmp = stream straight to myloader untar on slave,
            //  pmacontrol_local = stage to DIRECTORY_BACKUP on PmaControl,
            //  sa:<id> = stage to a backup_storage_area row), and which
            // compression algorithm to use in the pipe. The compressor
            // is also used as the on-disk extension for any staged file.
            $landingStorage = (string) ($_POST['landing_storage'] ?? 'slave_tmp');
            if (!preg_match('/^(slave_tmp|pmacontrol_local|sa:\d+)$/', $landingStorage)) {
                $landingStorage = 'slave_tmp';
            }
            $compression = (string) ($_POST['compression'] ?? 'lz4');
            if (!in_array($compression, ['none', 'lz4', 'zstd', 'gzip', 'xz'], true)) {
                $compression = 'lz4';
            }
            $acknowledge = (string) ($_POST['acknowledge_at_risk'] ?? '0') === '1';

            $db     = Sgbd::sql(DB_DEFAULT);
            $slave  = self::reloadLoadServerRow($db, $idServer);
            $master = self::reloadLoadMasterRow($db, $slave, $conn);

            $slaveStatus    = self::reloadFetchReplicaStatus($slave, $conn);
            $sourceCoverage = self::reloadFetchSourceCoverage($idServer);
            $diskStats      = self::reloadProbeDiskStats($slave, $master);
            $sshProbe       = self::reloadProbeSsh($slave);
            $versions       = self::reloadDetectVersions($slave, $master);

            $preflight = ReloadFromMaster::evaluatePreflight(
                $slave ?? [], $master ?? [], $slaveStatus, $sourceCoverage,
                $diskStats, $sshProbe, $versions
            );
            $eligible = match ($procedure) {
                'physical' => $preflight['physical_eligible'] ?? false,
                'mydumper' => $preflight['mydumper_eligible'] ?? false,
                default    => $preflight['logical_eligible']  ?? false,
            };
            if (!$eligible) {
                http_response_code(409);
                echo 'Preflight conditions not met server-side. Refresh the page.';
                return;
            }
            $hasAtRisk = false;
            foreach ($preflight['conditions'] as $c) {
                if ($c['key'] === 'source_coverage' && $c['status'] === ReloadFromMaster::STATUS_WARN
                    && stripos($c['message'], 'at_risk') !== false) {
                    $hasAtRisk = true;
                }
            }
            if ($hasAtRisk && !$acknowledge) {
                http_response_code(409);
                echo 'Source-coverage at_risk must be acknowledged (acknowledge_at_risk=1).';
                return;
            }

            $params = [
                'id_mysql_server' => $idServer,
                'connection_name' => $conn,
                'procedure'       => $procedure,
                'acknowledge'     => $acknowledge ? 1 : 0,
                'landing_storage' => $landingStorage,
                'compression'     => $compression,
            ];
            $jobId = self::reloadInsertJobRow($db, $params);
            if ($jobId > 0) {
                self::reloadForkCliWorker($jobId);
            }
            header('Location: '.LINK.'Job/index');
            return;
        } catch (\Throwable $e) {
            error_log('[Slave::reloadFromMasterStart] '.$e->getMessage());
            http_response_code(500);
            echo 'Internal error queuing reload job: '.htmlspecialchars($e->getMessage());
        }
    }

    /**
     * CLI entrypoint executed by the forked worker. Re-loads the job row,
     * resolves the slave + master endpoints (via `ssh_tunnel`) and walks
     * the real reload procedure on the slave host with `proc_open`.
     *
     * The procedure runs ENTIRELY on the slave: PmaControl opens one
     * outer SSH (potentially via -J jump host) to the slave, and the
     * slave then opens its own inner SSH to the master over the private
     * LAN — PmaControl never tries to bridge master↔slave directly
     * because the two endpoints are tunneled from our side and not
     * reachable from one another over the SSH chain we own.
     *
     * Invocation: `php App/Webroot/index.php Slave reloadFromMasterCli <job_id>`.
     */
    public function reloadFromMasterCli($param): void
    {
        $jobId = (int) ($param[0] ?? 0);
        if ($jobId <= 0) {
            error_log('[Slave::reloadFromMasterCli] missing job id');
            fwrite(STDERR, "missing job id\n");
            return;
        }
        try {
            $db = Sgbd::sql(DB_DEFAULT);
            $sql = "SELECT * FROM job WHERE id = ".(int) $jobId." LIMIT 1";
            $res = $db->sql_query($sql);
            $job = null;
            while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $job = $row;
            }
            if ($job === null) {
                error_log('[Slave::reloadFromMasterCli] job '.$jobId.' not found');
                fwrite(STDERR, "job ".$jobId." not found\n");
                return;
            }
            $params = json_decode((string) ($job['param'] ?? '[]'), true) ?: [];
            $logPath = $job['log'] ?: (ROOT.'/tmp/log/reload-'.$jobId.'.log');
            @mkdir(dirname($logPath), 0755, true);

            $procedure  = (string) ($params['procedure'] ?? 'physical');
            $idServer   = (int)    ($params['id_mysql_server'] ?? 0);
            $connection = (string) ($params['connection_name'] ?? '');

            self::reloadLog($logPath, sprintf(
                '=== reload-from-master starting (job=%d, procedure=%s, slave=%d, conn=%s) ===',
                $jobId, $procedure, $idServer, $connection
            ));

            $slave  = self::reloadLoadServerRow($db, $idServer);
            $master = self::reloadLoadMasterRow($db, $slave, $connection);
            if ($slave === null) {
                self::reloadCliFail($db, $jobId, $logPath, 'slave row not found (id='.$idServer.')');
                return;
            }
            if ($master === null) {
                self::reloadCliFail($db, $jobId, $logPath, 'master row could not be resolved from SHOW REPLICA STATUS');
                return;
            }

            // SSH endpoint we (PmaControl) use to reach the slave.
            $slaveEp = self::reloadResolveSshEndpoint(
                (string) ($slave['ip'] ?? ''),
                (int)    ($slave['ssh_port'] ?? 22)
            );
            // Master LAN endpoint as seen FROM the slave — looked up the
            // same way (the master's logical ip/ssh_port maps to its real
            // <bastion>...<remote_host>:22 in ssh_tunnel).
            $masterEp = self::reloadResolveSshEndpoint(
                (string) ($master['ip'] ?? ''),
                (int)    ($master['ssh_port'] ?? 22)
            );
            $masterLanIp   = $masterEp['host'];
            $masterLanPort = $masterEp['port'];
            if ($masterLanIp === '' || $masterLanIp === '127.0.0.1') {
                self::reloadCliFail($db, $jobId, $logPath, 'master LAN endpoint could not be resolved via ssh_tunnel');
                return;
            }

            $masterUser = trim((string) ($master['login'] ?? ''));
            $masterPwd  = (string) ($master['passwd'] ?? '');
            if ((int) ($master['is_password_crypted'] ?? 0) === 1 && $masterPwd !== '') {
                try {
                    $masterPwd = Chiffrement::decrypt($masterPwd);
                } catch (\Throwable $e) {
                    self::reloadCliFail($db, $jobId, $logPath, 'cannot decrypt master password: '.$e->getMessage());
                    return;
                }
            }

            // Resolve the SSH identity from the `ssh_key` table linked
            // to this server (table `link__mysql_server__ssh_key`,
            // managed in /Ssh/index). The lib materialises the private
            // key into a per-job 0600 file under tmp/reload-keys/ and
            // returns `{user, key_path, key_id}` so the SSH command
            // can use `-l <user> -i <key_path>`. Falls back to the
            // slave's `ssh_login` + ambient agent when no key is linked.
            $sshIdentity = self::reloadResolveSshIdentity($db, (int) $slave['id'], $jobId);

            $ctx = [
                'jobId'         => $jobId,
                'logPath'       => $logPath,
                'slaveEp'       => $slaveEp,
                'slaveLogin'    => $sshIdentity['user'] !== ''
                    ? $sshIdentity['user']
                    : self::reloadCleanSshLogin((string) ($slave['ssh_login'] ?? '')),
                'slaveKeyPath'  => $sshIdentity['key_path'],
                // Local tunnel endpoint as maintained by ssh_tunnel —
                // SSH'ing to 127.0.0.1:<local_ssh_port> reaches the
                // slave through the same chain the daemon already opens.
                'slaveLocalHost' => (string) ($slave['ip']      ?? '127.0.0.1'),
                'slaveLocalPort' => (int)    ($slave['ssh_port'] ?? 22),
                'masterLanIp'   => $masterLanIp,
                'masterLanPort' => $masterLanPort,
                'masterUser'    => $masterUser,
                'masterPwd'     => $masterPwd,
                'params'        => $params,
            ];

            if ($procedure === 'logical') {
                self::reloadCliRunLogical($db, $ctx);
            } elseif ($procedure === 'mydumper') {
                self::reloadCliRunMydumper($db, $ctx);
            } else {
                self::reloadCliRunPhysical($db, $ctx);
            }
        } catch (\Throwable $e) {
            error_log('[Slave::reloadFromMasterCli] '.$e->getMessage());
            try {
                $db = Sgbd::sql(DB_DEFAULT);
                $db->sql_query("UPDATE job SET status='FAILED', error=".self::reloadSqlString($db, substr($e->getMessage(), 0, 250)).", date_end=NOW() WHERE id=".(int) $jobId);
            } catch (\Throwable $ignored) { /* best-effort */ }
        }
    }

    /**
     * Run the physical (mariadb-backup / xbstream) procedure. Each step
     * is executed on the slave via SSH; the heaviest step pipes
     * `mariadb-backup --backup --stream=xbstream` from master to
     * `mbstream -x -C /var/lib/mysql/` on the slave, all over the LAN.
     *
     * @param array{jobId:int,logPath:string,slaveEp:array,slaveLogin:string,masterLanIp:string,masterLanPort:int,masterUser:string,masterPwd:string,params:array} $ctx
     */
    private static function reloadCliRunPhysical($db, array $ctx): void
    {
        $jobId   = $ctx['jobId'];
        $logPath = $ctx['logPath'];
        $mUser   = $ctx['masterUser'];
        $mPwd    = $ctx['masterPwd'];
        $mIp     = $ctx['masterLanIp'];
        // Build the inner-ssh "ssh root@<master> '<cmd>'" with proper
        // single-quote escaping. We pass --user/--password on the
        // mariadb-backup CLI through environment so the password never
        // shows up in `ps`.
        $sshMasterPrefix = "ssh -o StrictHostKeyChecking=no -o BatchMode=yes root@".escapeshellarg($mIp);

        $streamCmd =
            'export MYSQL_PWD='.self::reloadShSingleQuote($mPwd).'; '
            .$sshMasterPrefix
            ." 'MYSQL_PWD='".self::reloadShSingleQuote($mPwd)."' mariadb-backup --backup --stream=xbstream --user=".self::reloadShSingleQuote($mUser)."' "
            .'| mbstream -x -C /var/lib/mysql/';

        $steps = [
            [
                'name' => 'stop_mysqld_slave', 'pct' => 5,
                'cmd'  => 'systemctl stop mariadb 2>/dev/null || systemctl stop mysqld 2>/dev/null || systemctl stop mysql',
            ],
            [
                'name' => 'rotate_datadir', 'pct' => 10,
                'cmd'  => 'test -d /var/lib/mysql && mv /var/lib/mysql /var/lib/mysql.before-reload.$(date +%s); '
                        . 'mkdir -p /var/lib/mysql && chown mysql:mysql /var/lib/mysql && chmod 750 /var/lib/mysql',
            ],
            [
                'name' => 'stream_backup', 'pct' => 60,
                'cmd'  => $streamCmd,
            ],
            [
                'name' => 'prepare_backup', 'pct' => 75,
                'cmd'  => 'mariadb-backup --prepare --target-dir=/var/lib/mysql/',
            ],
            [
                'name' => 'chown_datadir', 'pct' => 80,
                'cmd'  => 'chown -R mysql:mysql /var/lib/mysql',
            ],
            [
                'name' => 'start_mysqld_slave', 'pct' => 85,
                'cmd'  => 'systemctl start mariadb 2>/dev/null || systemctl start mysqld',
            ],
            [
                'name' => 'configure_replica', 'pct' => 95,
                // Parse xtrabackup_binlog_info: "<file> <pos> [<gtid>]" and
                // wire replication accordingly. GTID line if present takes
                // precedence (MariaDB MASTER_USE_GTID=slave_pos).
                'cmd'  => 'set -e; INFO="/var/lib/mysql/xtrabackup_binlog_info"; '
                        . 'test -f "$INFO" || { echo "xtrabackup_binlog_info missing"; exit 1; }; '
                        . 'read FILE POS GTID < "$INFO"; '
                        . 'if [ -n "$GTID" ]; then '
                        .   'mysql -uroot -e "STOP REPLICA; SET GLOBAL gtid_slave_pos=\'$GTID\'; '
                        .   'CHANGE MASTER TO MASTER_HOST=\''.addslashes($mIp).'\', MASTER_USER=\''.addslashes($mUser).'\', MASTER_PASSWORD=\''.addslashes($mPwd).'\', MASTER_USE_GTID=slave_pos; START REPLICA;"; '
                        . 'else '
                        .   'mysql -uroot -e "STOP REPLICA; CHANGE MASTER TO MASTER_HOST=\''.addslashes($mIp).'\', MASTER_USER=\''.addslashes($mUser).'\', MASTER_PASSWORD=\''.addslashes($mPwd).'\', MASTER_LOG_FILE=\'$FILE\', MASTER_LOG_POS=$POS; START REPLICA;"; '
                        . 'fi',
            ],
        ];

        foreach ($steps as $step) {
            $ok = self::reloadRunStep($db, $ctx, $step['name'], $step['cmd'], (int) $step['pct']);
            if (!$ok) {
                return; // reloadRunStep already marked the job FAILED
            }
        }
        self::reloadCliFinish($db, $jobId, $logPath);
    }

    /**
     * Logical (mariadb-dump --master-data=2) procedure: stop replica,
     * pipe a gzipped dump from the master over the slave's LAN ssh into
     * `mysql -u root`, then start replica. Used when the physical
     * procedure isn't eligible (cross-version, cross-engine, etc.).
     *
     * @param array{jobId:int,logPath:string,slaveEp:array,slaveLogin:string,masterLanIp:string,masterLanPort:int,masterUser:string,masterPwd:string,params:array} $ctx
     */
    private static function reloadCliRunLogical($db, array $ctx): void
    {
        $jobId   = $ctx['jobId'];
        $logPath = $ctx['logPath'];
        $mUser   = $ctx['masterUser'];
        $mPwd    = $ctx['masterPwd'];
        $mIp     = $ctx['masterLanIp'];

        // The slave SSH-keys to its master directly over the LAN —
        // operator deploys root@slave's pubkey into root@master's
        // authorized_keys. PmaControl doesn't push a key here anymore.
        $sshMasterPrefix = "ssh -o StrictHostKeyChecking=no -o BatchMode=yes -o UserKnownHostsFile=/dev/null"
                         . " root@".escapeshellarg($mIp);
        // Flags:
        //   --host=127.0.0.1 --protocol=TCP  → matches `pmacontrol@%`
        //     rather than `pmacontrol@localhost` (typically not granted)
        //   --skip-lock-tables               → required when the schema
        //     contains MariaDB SEQUENCE objects; --single-transaction
        //     alone trips ERROR 1100 on them
        //   --password=…                     → MYSQL_PWD env doesn't
        //     reliably propagate through `ssh -c` on MariaDB 11.4
        //   2>&1                             → fold stderr into the
        //     gzipped stream so pipefail catches dump-side errors
        // --master-data=1 + --gtid → the dump itself starts with
        //   `RESET MASTER; SET GLOBAL gtid_slave_pos='…';` so the
        //   slave's GTID position is rewritten to the master's
        //   actual position at dump time. With =2 those lines are
        //   COMMENTED OUT, and the slave keeps its stale slave_pos,
        //   which causes a 1236 error on START REPLICA.
        // --skip-add-locks → dump CONTAINS no LOCK TABLES statements,
        //   which would otherwise wrap each table's INSERT block. The
        //   LOAD side trips ERROR 1100 ("Table not locked") when a
        //   SEQUENCE object is touched inside another table's LOCK
        //   region. Removing LOCK TABLES entirely is fine here —
        //   --single-transaction already makes the dump consistent.
        $dumpInner = "mariadb-dump --host=127.0.0.1 --protocol=TCP --master-data=1 --single-transaction"
                   . " --skip-lock-tables --skip-add-locks --gtid --all-databases"
                   . " --user=".self::reloadShSingleQuote($mUser)
                   . " --password=".self::reloadShSingleQuote($mPwd)
                   . " 2>&1 | gzip";
        $dumpCmd   = "set -o pipefail; ".$sshMasterPrefix." ".self::reloadShSingleQuoteWrap($dumpInner)." | gzip -d | mysql -uroot";

        $steps = [
            ['name' => 'stop_replica',      'pct' => 5,  'cmd' => 'mysql -uroot -e "STOP REPLICA;" 2>/dev/null || mysql -uroot -e "STOP SLAVE;"'],
            ['name' => 'dump_and_pipe',     'pct' => 80, 'cmd' => $dumpCmd],
            ['name' => 'configure_replica', 'pct' => 95,
             'cmd' => 'mysql -uroot -e "CHANGE MASTER TO MASTER_HOST=\''.addslashes($mIp).'\', MASTER_USER=\''.addslashes($mUser).'\', MASTER_PASSWORD=\''.addslashes($mPwd).'\', MASTER_USE_GTID=slave_pos; START REPLICA;" 2>&1 | grep -v "^Warning" || true'],
        ];

        foreach ($steps as $step) {
            $ok = self::reloadRunStep($db, $ctx, $step['name'], $step['cmd'], (int) $step['pct']);
            if (!$ok) {
                return;
            }
        }
        self::reloadCliFinish($db, $jobId, $logPath);
    }

    /**
     * mydumper procedure: parallel multi-threaded dump/load. The
     * master streams via `mydumper --stream` (binary TAR-on-stdout
     * since mydumper 0.12), piped over SSH into `myloader --stream`
     * on the slave. Much faster than mariadb-dump on multi-database
     * workloads thanks to per-table parallelism, and cross-version
     * compatible at the SQL layer.
     *
     * Pre-requisites operator must provision on the hosts (the
     * worker probes presence on the first step and fails fast with a
     * clear message if missing):
     *   - `mydumper` on the master (any 0.12+ build)
     *   - `myloader` on the slave (matching major version)
     */
    private static function reloadCliRunMydumper($db, array $ctx): void
    {
        $jobId   = $ctx['jobId'];
        $logPath = $ctx['logPath'];
        $mUser   = $ctx['masterUser'];
        $mPwd    = $ctx['masterPwd'];
        $mIp     = $ctx['masterLanIp'];

        $sshMasterPrefix = "ssh -o StrictHostKeyChecking=no -o BatchMode=yes -o UserKnownHostsFile=/dev/null"
                         . " root@".escapeshellarg($mIp);

        // mydumper flags (master side):
        //   -o <dir>     → write per-table SQL files to a tmp dir;
        //                  the master's mydumper >= 0.12 supports
        //                  `--stream` but the slave's myloader 0.10
        //                  (Debian stock) does not, so we use the
        //                  dir-based form + tar-pipe over SSH for
        //                  cross-version compatibility. The
        //                  reloadMydumperUpgradeHint() method
        //                  surfaces the proper upgrade procedure.
        //   --threads=4  → reasonable default
        //   --rows=…     → chunk large tables
        //
        // We deliberately DO NOT pass --trx-consistency-only /
        // --less-locking / --kill-long-queries: those flags were
        // renamed or removed across the 0.10 → 0.21 transition
        // (e.g. --trx-consistency-only is a CRITICAL on 0.21+) and
        // the default lock strategy of every supported version is
        // already InnoDB-friendly enough for our use case.
        $threads      = 4;
        $masterTmpDir = '/tmp/mydumper-'.$jobId;
        $slaveTmpDir  = '/tmp/myloader-'.$jobId;

        // Operator-picked landing storage + compression. The defaults
        // preserve the historical behaviour (stream slave_tmp + no
        // compression) when the form fields aren't posted.
        $params         = $ctx['params'] ?? [];
        $landingStorage = (string) ($params['landing_storage'] ?? 'slave_tmp');
        $compression    = (string) ($params['compression']     ?? 'none');
        // Compression command pair: [encoder_argv, decoder_argv, ext].
        // `lz4` is the project default (fastest compress + decompress);
        // `zstd` is a reasonable middle ground; `xz` is the densest
        // but slowest. Picking `none` keeps a straight tar pipe.
        $codec = match ($compression) {
            'lz4'  => ['lz4',  'lz4 -d',   'lz4'],
            'zstd' => ['zstd', 'zstd -d',  'zst'],
            'gzip' => ['gzip', 'gzip -d',  'gz'],
            'xz'   => ['xz',   'xz -d',    'xz'],
            default => ['cat',  'cat',     'tar'],
        };
        [$compEnc, $compDec, $compExt] = $codec;
        $dumpInner = "set -o pipefail; "
                   . "rm -rf ".$masterTmpDir." && mkdir -p ".$masterTmpDir." && "
                   . "mydumper"
                   . " --threads=".$threads
                   . " --rows=10000000"
                   // Exclude system schemas — the slave already has
                   // its own mysql.* / sys.* / *_schema and they
                   // mustn't be overwritten by master's copy
                   // (would clobber local accounts, slave-only
                   // configuration, etc.). --regex is a PCRE
                   // negative lookahead matching schema.table.
                   . " --regex=".self::reloadShSingleQuoteWrap('^(?!(mysql|sys|information_schema|performance_schema)\\.)')
                   // Lowest-lock-impact consistent mode: InnoDB
                   // tables are dumped inside a single transaction
                   // (default since 0.12+), --use-savepoints
                   // releases metadata locks between tables so
                   // OLTP traffic on the master keeps flowing.
                   . " --use-savepoints"
                   . " --host=127.0.0.1 --protocol=tcp"
                   . " --user=".self::reloadShSingleQuote($mUser)
                   . " --password=".self::reloadShSingleQuote($mPwd)
                   . " -o ".$masterTmpDir
                   . " > /tmp/reload-mydumper-".$jobId.".log 2>&1 && "
                   . "tar c -C ".$masterTmpDir." . | ".$compEnc." && "
                   . "rm -rf ".$masterTmpDir;
        $cleanupCmd = 'rm -rf '.$slaveTmpDir;
        // myloader 0.10 (Debian stock) doesn't know --protocol=tcp;
        // specifying --host is enough to bypass the Unix socket.
        $myloaderCmd = "myloader -d ".$slaveTmpDir
                     . " --threads=".$threads
                     . " --host=127.0.0.1"
                     . " --user=root --enable-binlog"
                     . " --overwrite-tables 2>&1";

        // Three landing-storage flavours, picked by the operator:
        //
        // slave_tmp        — tar+compressed dump streamed straight
        //                    into a tmpdir on the slave, then
        //                    myloader. Requires ~uncompressed dump
        //                    size of free disk on slave /tmp.
        // pmacontrol_local — stage the compressed dump to
        //                    DIRECTORY_BACKUP on the PmaControl host
        //                    first, then read it back through the
        //                    slave SSH tunnel into the tmpdir +
        //                    myloader. Decouples the master/slave
        //                    pipe so a slow slave doesn't stall the
        //                    master. Adds one full I/O round-trip.
        // sa:<id>          — stage to a backup_storage_area row
        //                    (separate SSH endpoint). Same pattern.
        $stageDir = '';
        $stageFile = '';
        if ($landingStorage === 'pmacontrol_local') {
            @include CONFIG.'backup.config.php';
            $stageDir = defined('DIRECTORY_BACKUP') ? rtrim(\constant('DIRECTORY_BACKUP'), '/') : '/srv/backup';
            @mkdir($stageDir, 0750, true);
            $stageFile = $stageDir.'/reload-'.$jobId.'.tar.'.$compExt;
        } elseif (str_starts_with($landingStorage, 'sa:')) {
            // Stub for SA-based staging — falls back to slave_tmp if
            // the row isn't resolvable (kept simple for v1).
            $landingStorage = 'slave_tmp';
        }
        if ($landingStorage === 'pmacontrol_local' && $stageFile !== '') {
            // Stage step: ssh master ... | <enc> > /srv/backup/<file>
            // Then load step: <dec> < /srv/backup/<file> | ssh slave 'tar x | myloader'
            $stageCmd = "set -o pipefail; rm -f ".$stageFile." && "
                      . $sshMasterPrefix." ".self::reloadShSingleQuoteWrap($dumpInner)
                      . " > ".$stageFile;
            // The load step runs OUTSIDE the outer SSH wrapper —
            // PmaControl shells out, decompresses locally, and pipes
            // through ssh slave 'tar x ... && myloader'. We pack
            // both into a single bash step in the slave SSH.
            $loadInner = "set -o pipefail; "
                       . "rm -rf ".$slaveTmpDir." && mkdir -p ".$slaveTmpDir." && "
                       . "tar x -C ".$slaveTmpDir." && "
                       . $myloaderCmd;
            $dumpCmd = $stageCmd; // step 1: stage to disk
            $ctx['mydumperStageFile'] = $stageFile;
            $ctx['mydumperLoadInner'] = $loadInner;
            $ctx['mydumperCompDec']   = $compDec;
        } else {
            // Direct streaming pipe — historical behaviour.
            $loadInner = "set -o pipefail; "
                       . "rm -rf ".$slaveTmpDir." && mkdir -p ".$slaveTmpDir." && "
                       . $sshMasterPrefix." ".self::reloadShSingleQuoteWrap($dumpInner)
                       . " | ".$compDec." | tar x -C ".$slaveTmpDir." && "
                       . $myloaderCmd;
            $dumpCmd = $loadInner;
        }

        // mydumper also writes the master's `metadata` file out-of-band
        // (binlog file + position + GTID); we capture it from the
        // master post-dump to reset slave_pos before reconnect.
        $fetchGtidCmd = $sshMasterPrefix." 'mariadb --host=127.0.0.1 --protocol=TCP --user="
                      . self::reloadShSingleQuote($mUser)." --password=".self::reloadShSingleQuote($mPwd)
                      . " -BNe \"SELECT @@gtid_binlog_pos\"'";

        $steps = [
            [
                'name' => 'probe_binaries', 'pct' => 3,
                // Verify both ends have mydumper/myloader before
                // wiping anything. The outer SSH targets the slave;
                // the inner SSH probes the master.
                'cmd'  => '(which myloader >/dev/null 2>&1 || (echo "myloader missing on slave" >&2; exit 65)) && '
                        . $sshMasterPrefix." 'which mydumper >/dev/null 2>&1 || (echo \"mydumper missing on master\" >&2; exit 65)'",
            ],
            ['name' => 'stop_replica', 'pct' => 5, 'cmd' => 'mysql -uroot -e "STOP REPLICA;" 2>/dev/null || mysql -uroot -e "STOP SLAVE;"'],
        ];

        if ($landingStorage === 'pmacontrol_local') {
            // Stage step runs LOCALLY on the PmaControl host (no
            // outer-SSH wrap). reloadRunLocalStep is identical to
            // reloadRunStep minus the slave-SSH chain.
            $steps[] = ['name' => 'stage_to_pmacontrol', 'pct' => 60, 'cmd' => $dumpCmd, 'local' => true];
            // Load step: from PmaControl, decompress the staged file
            // and pipe it through the slave SSH into tar+myloader.
            $sshSlaveOuter = self::reloadBuildOuterSshForSlave($ctx); // helper for the slave SSH prefix
            $loadCmd = $compDec." < ".$stageFile." | ".$sshSlaveOuter." ".self::reloadShSingleQuoteWrap($ctx['mydumperLoadInner']);
            $steps[] = ['name' => 'load_from_pmacontrol', 'pct' => 90, 'cmd' => $loadCmd, 'local' => true];
            $cleanupCmd .= '; rm -f '.$stageFile;
        } else {
            $steps[] = ['name' => 'dump_and_load', 'pct' => 85, 'cmd' => $dumpCmd];
        }

        $steps = array_merge($steps, [
            [
                'name' => 'configure_replica', 'pct' => 97,
                // mydumper doesn't auto-emit a CHANGE MASTER block.
                // We `RESET MASTER` then derive gtid_slave_pos from
                // the master's @@gtid_binlog_pos captured at the end
                // of the dump (race window is tiny because the slave
                // already STOP REPLICA-d before the dump started).
                'cmd' => 'GTID=$('.$fetchGtidCmd.' 2>/dev/null | tr -d "[:space:]"); '
                       . 'mysql -uroot -e "RESET MASTER; SET GLOBAL gtid_slave_pos=\"$GTID\"; '
                       . 'CHANGE MASTER TO MASTER_HOST=\\\"'.addslashes($mIp).'\\\", MASTER_USER=\\\"'.addslashes($mUser).'\\\", MASTER_PASSWORD=\\\"'.addslashes($mPwd).'\\\", MASTER_USE_GTID=slave_pos; '
                       . 'START REPLICA;" 2>&1 | grep -v "^Warning" || true',
            ],
            [
                'name' => 'cleanup_mydumper_log', 'pct' => 99,
                'cmd'  => $cleanupCmd.'; rm -f /tmp/reload-mydumper-'.$jobId.'.log',
            ],
        ]);

        foreach ($steps as $step) {
            $isLocal = !empty($step['local']);
            $ok = $isLocal
                ? self::reloadRunLocalStep($db, $ctx, $step['name'], $step['cmd'], (int) $step['pct'])
                : self::reloadRunStep($db, $ctx, $step['name'], $step['cmd'], (int) $step['pct']);
            if (!$ok) {
                return;
            }
        }
        self::reloadCliFinish($db, $jobId, $logPath);
    }

    /**
     * Build the outer-SSH prefix that targets the slave (same as
     * reloadRunStep would emit, minus the trailing bash -lc payload).
     * Used by the pmacontrol_local landing path so the load step can
     * pipe a locally-decompressed file into the slave's tar+myloader.
     */
    private static function reloadBuildOuterSshForSlave(array $ctx): string
    {
        $login   = $ctx['slaveLogin'] !== '' ? $ctx['slaveLogin'] : 'root';
        $host    = (string) ($ctx['slaveLocalHost'] ?? '127.0.0.1');
        $port    = (int)    ($ctx['slaveLocalPort'] ?? 22);
        $keyPath = (string) ($ctx['slaveKeyPath']   ?? '');
        $identityFlag = $keyPath !== '' ? ' -i '.escapeshellarg($keyPath).' -o IdentitiesOnly=yes' : '';
        return 'ssh -o ConnectTimeout=10 -o StrictHostKeyChecking=no -o BatchMode=yes -o UserKnownHostsFile=/dev/null'
             . $identityFlag
             . ' -p '.$port
             . ' '.escapeshellarg($login).'@'.escapeshellarg($host)
             . ' bash -lc';
    }

    /**
     * Same shape as reloadRunStep but executes the shell command
     * locally on the PmaControl host (no outer SSH wrap). Used by
     * the pmacontrol_local mydumper landing strategy.
     */
    private static function reloadRunLocalStep($db, array $ctx, string $name, string $shellCmd, int $progressPercent): bool
    {
        $jobId   = $ctx['jobId'];
        $logPath = $ctx['logPath'];
        try {
            $db->sql_query(sprintf(
                "UPDATE job SET dump_status=%s, progress_percent=%d WHERE id=%d",
                self::reloadSqlString($db, $name),
                $progressPercent,
                $jobId
            ));
        } catch (\Throwable $ignored) {}
        self::reloadLog($logPath, '----- step ['.$name.'] starting (pct='.$progressPercent.', local) -----');
        self::reloadLog($logPath, '[exec-local] '.$shellCmd);
        $stderr = '';
        $exit   = -1;
        $process = null;
        $pipes   = [];
        try {
            $descriptors = [
                0 => ['file', '/dev/null', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ];
            $process = @proc_open(['bash', '-lc', $shellCmd], $descriptors, $pipes);
            if (!is_resource($process)) {
                self::reloadCliFail($db, $jobId, $logPath, 'proc_open failed for local step '.$name);
                return false;
            }
            stream_set_blocking($pipes[1], false);
            stream_set_blocking($pipes[2], false);
            $stdoutBuf = '';
            $stderrBuf = '';
            while (true) {
                $status = proc_get_status($process);
                $r = [$pipes[1], $pipes[2]];
                $w = null; $e = null;
                $ready = @stream_select($r, $w, $e, 1, 0);
                if ($ready !== false && $ready > 0) {
                    foreach ($r as $stream) {
                        $chunk = fread($stream, 8192);
                        if ($chunk === false || $chunk === '') continue;
                        $isErr = ($stream === $pipes[2]);
                        $buf = $isErr ? ($stderrBuf .= $chunk) : ($stdoutBuf .= $chunk);
                        while (($nl = strpos($buf, "\n")) !== false) {
                            $line = substr($buf, 0, $nl);
                            $buf  = substr($buf, $nl + 1);
                            self::reloadLog($logPath, '['.$name.($isErr ? ' stderr' : ' stdout').'] '.$line);
                        }
                        if ($isErr) { $stderrBuf = $buf; } else { $stdoutBuf = $buf; }
                    }
                }
                if (!$status['running']) {
                    foreach ([1, 2] as $fd) {
                        $rest = stream_get_contents($pipes[$fd]);
                        if ($rest !== false && $rest !== '') {
                            $isErr = ($fd === 2);
                            $stderr .= $isErr ? $rest : '';
                            foreach (explode("\n", rtrim($rest, "\n")) as $ln) {
                                if ($ln === '') continue;
                                self::reloadLog($logPath, '['.$name.($isErr ? ' stderr' : ' stdout').'] '.$ln);
                            }
                        }
                    }
                    $exit = $status['exitcode'];
                    break;
                }
            }
        } finally {
            foreach ($pipes as $p) { if (is_resource($p)) @fclose($p); }
            if (is_resource($process)) { @proc_close($process); }
        }
        if ($exit !== 0) {
            self::reloadCliFail($db, $jobId, $logPath, 'step ['.$name.'] failed locally (exit='.(int) $exit.'): '.substr(trim($stderrBuf.$stderr), -1024));
            return false;
        }
        self::reloadLog($logPath, '----- step ['.$name.'] OK -----');
        return true;
    }

    /**
     * Execute one remote-shell step against the slave via SSH, streaming
     * stdout+stderr line-by-line into the job log and tracking exit code.
     *
     * On non-zero exit the job is marked FAILED (last 1 KiB of stderr is
     * stored in `job.error`) and the method returns false so the caller
     * stops the procedure. proc_close is always called, even on
     * exceptions.
     *
     * @param array{jobId:int,logPath:string,slaveEp:array,slaveLogin:string} $ctx
     */
    private static function reloadRunStep($db, array $ctx, string $name, string $remoteShellCmd, int $progressPercent): bool
    {
        $jobId   = $ctx['jobId'];
        $logPath = $ctx['logPath'];
        $slaveEp = $ctx['slaveEp'];
        $login   = $ctx['slaveLogin'] !== '' ? $ctx['slaveLogin'] : 'root';

        // Use the LOCAL SSH-tunnel endpoint that PmaControl already
        // maintains: every slave has a 127.0.0.1:<local_port> mapping
        // in `ssh_tunnel` that the tunnel daemon keeps alive through
        // the bastion + jump hops. We just SSH to that local port — no
        // need to rebuild the jump chain in this worker. The linked
        // ssh_key (via /Ssh/index) provides the identity for the
        // FINAL hop, which the tunnel forwards transparently.
        $localHost = (string) ($ctx['slaveLocalHost'] ?? '127.0.0.1');
        $localPort = (int)    ($ctx['slaveLocalPort'] ?? 22);
        $keyPath   = (string) ($ctx['slaveKeyPath']   ?? '');
        $identityFlag = $keyPath !== ''
            ? ' -i '.escapeshellarg($keyPath).' -o IdentitiesOnly=yes'
            : '';
        $sshCmd = 'ssh -o ConnectTimeout=10 -o StrictHostKeyChecking=no -o BatchMode=yes -o UserKnownHostsFile=/dev/null'
                . $identityFlag
                . ' -p '.$localPort
                . ' '.escapeshellarg($login).'@'.escapeshellarg($localHost)
                . ' '.escapeshellarg('bash -lc '.self::reloadShSingleQuoteWrap($remoteShellCmd));

        // Update job row at step start.
        try {
            $db->sql_query(sprintf(
                "UPDATE job SET dump_status=%s, progress_percent=%d WHERE id=%d",
                self::reloadSqlString($db, $name),
                $progressPercent,
                $jobId
            ));
        } catch (\Throwable $ignored) { /* keep going */ }

        self::reloadLog($logPath, '----- step ['.$name.'] starting (pct='.$progressPercent.') -----');
        // Don't log the full ssh command if it contains a password — but
        // we already pass passwords via MYSQL_PWD env, not on the CLI, so
        // the command itself is safe to record.
        self::reloadLog($logPath, '[exec] '.$sshCmd);

        $process = null;
        $pipes   = [];
        $stderrTail = '';
        $exitCode   = -1;
        try {
            $descriptors = [
                0 => ['file', '/dev/null', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ];
            $process = @proc_open($sshCmd, $descriptors, $pipes);
            if (!is_resource($process)) {
                self::reloadCliFail($db, $jobId, $logPath, 'proc_open failed for step '.$name);
                return false;
            }
            stream_set_blocking($pipes[1], false);
            stream_set_blocking($pipes[2], false);
            $stdoutBuf = '';
            $stderrBuf = '';
            while (true) {
                $status = proc_get_status($process);
                $r = [$pipes[1], $pipes[2]];
                $w = null; $e = null;
                $ready = @stream_select($r, $w, $e, 1, 0);
                if ($ready !== false && $ready > 0) {
                    foreach ($r as $stream) {
                        $chunk = fread($stream, 8192);
                        if ($chunk === false || $chunk === '') {
                            continue;
                        }
                        $isErr = ($stream === $pipes[2]);
                        $buf = $isErr ? ($stderrBuf .= $chunk) : ($stdoutBuf .= $chunk);
                        // emit complete lines, keep partial in buffer
                        while (($nl = strpos($buf, "\n")) !== false) {
                            $line = substr($buf, 0, $nl);
                            $buf  = substr($buf, $nl + 1);
                            self::reloadLog($logPath, '['.$name.($isErr ? ' stderr' : ' stdout').'] '.$line);
                        }
                        if ($isErr) {
                            $stderrBuf = $buf;
                        } else {
                            $stdoutBuf = $buf;
                        }
                    }
                }
                if (!$status['running']) {
                    // drain any tail still in pipes
                    foreach ([1, 2] as $fd) {
                        $rest = stream_get_contents($pipes[$fd]);
                        if ($rest !== false && $rest !== '') {
                            $tag = ($fd === 2) ? ' stderr' : ' stdout';
                            foreach (preg_split('/\r?\n/', rtrim($rest, "\r\n")) as $ln) {
                                if ($ln !== '') {
                                    self::reloadLog($logPath, '['.$name.$tag.'] '.$ln);
                                }
                            }
                            if ($fd === 2) {
                                $stderrBuf .= $rest;
                            }
                        }
                    }
                    $exitCode = (int) $status['exitcode'];
                    break;
                }
            }
            $stderrTail = substr($stderrBuf, -1024);
        } catch (\Throwable $e) {
            self::reloadLog($logPath, '[exception] '.$e->getMessage());
            $exitCode = -2;
            $stderrTail = $e->getMessage();
        } finally {
            foreach ($pipes as $p) {
                if (is_resource($p)) {
                    @fclose($p);
                }
            }
            if (is_resource($process)) {
                @proc_close($process);
            }
        }

        if ($exitCode !== 0) {
            $msg = 'step ['.$name.'] failed (exit='.$exitCode.'): '.trim($stderrTail);
            self::reloadCliFail($db, $jobId, $logPath, $msg);
            return false;
        }
        self::reloadLog($logPath, '----- step ['.$name.'] OK -----');
        return true;
    }

    private static function reloadCliFinish($db, int $jobId, string $logPath): void
    {
        self::reloadLog($logPath, '=== reload-from-master completed ===');
        try {
            $db->sql_query(sprintf(
                "UPDATE job SET status='DONE', progress_percent=100, load_status='completed', date_end=NOW() WHERE id=%d",
                $jobId
            ));
        } catch (\Throwable $ignored) { /* best-effort */ }
    }

    private static function reloadCliFail($db, int $jobId, string $logPath, string $message): void
    {
        self::reloadLog($logPath, '[FAIL] '.$message);
        try {
            $db->sql_query(sprintf(
                "UPDATE job SET status='FAILED', error=%s, date_end=NOW() WHERE id=%d",
                self::reloadSqlString($db, substr($message, 0, 250)),
                $jobId
            ));
        } catch (\Throwable $ignored) { /* best-effort */ }
    }

    private static function reloadLog(string $path, string $line): void
    {
        @file_put_contents($path, '['.date('Y-m-d H:i:s').'] '.$line."\n", FILE_APPEND);
    }

    /**
     * Resolve the SSH user used on the jump host(s). Read from
     * `configuration/ssh_tunnel.config.php` if present (matches the
     * tunnel daemon's existing config) — otherwise defaults to the
     * convention PmaControl already uses across the inventory.
     */
    private static function reloadJumpUser(): string
    {
        $cfgFile = (defined('CONFIG') ? \constant('CONFIG') : ROOT.'/configuration/').'ssh_tunnel.config.php';
        if (is_readable($cfgFile)) {
            $cfg = @include $cfgFile;
            if (is_array($cfg) && !empty($cfg['jump_user'])) {
                return (string) $cfg['jump_user'];
            }
        }
        return 'aurelien';
    }

    /**
     * Resolve the SSH identity (user + private key path) for a slave
     * server by looking up the ssh_key linked to it via
     * link__mysql_server__ssh_key (managed in `/Ssh/index`).
     *
     * Materialises `ssh_key.private_key` into a per-job 0600 file at
     * `tmp/reload-keys/<jobId>-<keyId>.key` that the worker passes
     * to `ssh -i`. When no active link exists, returns empty paths
     * so the caller falls back to the ambient SSH config / agent.
     *
     * The temp key file is deliberately NOT cleaned up at the end of
     * the run — the worker may be running multiple steps that each
     * spawn ssh and re-reading the same file is harmless. A separate
     * cron prunes `tmp/reload-keys/` older than 24 h.
     *
     * @return array{user:string,key_path:string,key_id:int}
     */
    private static function reloadResolveSshIdentity($db, int $idMysqlServer, int $jobId): array
    {
        $blank = ['user' => '', 'key_path' => '', 'key_id' => 0];
        try {
            $sql = "SELECT k.id, k.name, k.user, k.private_key "
                 . "FROM ssh_key k "
                 . "INNER JOIN link__mysql_server__ssh_key l ON l.id_ssh_key = k.id "
                 . "WHERE l.id_mysql_server = ".(int) $idMysqlServer." "
                 . "  AND l.active = 1 "
                 . "ORDER BY l.added_on DESC, k.id DESC "
                 . "LIMIT 1";
            $res = $db->sql_query_silent($sql);
            if (!$res) {
                return $blank;
            }
            $row = $db->sql_fetch_array($res, MYSQLI_ASSOC);
            if (!$row || trim((string) $row['private_key']) === '') {
                return $blank;
            }
            $dir = ROOT.'/tmp/reload-keys';
            @mkdir($dir, 0700, true);
            @chmod($dir, 0700);
            $path = $dir.'/'.$jobId.'-'.(int) $row['id'].'.key';
            // Some DB rows store the key with literal `\n` escapes
            // (legacy import); normalise to real newlines so ssh
            // accepts it.
            $key = (string) $row['private_key'];
            // ssh_key.private_key is stored encrypted at rest (same
            // Chiffrement helper as mysql_server.passwd). Decrypt
            // before writing to disk; if decryption throws (key
            // was inserted unencrypted by a legacy import), fall
            // back to the raw value.
            try {
                $key = Chiffrement::decrypt($key);
            } catch (\Throwable $ignored) { /* assume already plain */ }
            if (strpos($key, '\\n') !== false && strpos($key, "\n") === false) {
                $key = str_replace('\\n', "\n", $key);
            }
            // libcrypto rejects CRLF line endings in PEM bodies — the
            // PmaControl SSH-key dialog stores Windows-style newlines
            // when copy-pasted from PuTTY etc. Normalise to LF.
            $key = str_replace("\r\n", "\n", $key);
            $key = str_replace("\r",   "\n", $key);
            if (substr($key, -1) !== "\n") {
                $key .= "\n";
            }
            @file_put_contents($path, $key);
            @chmod($path, 0600);
            return [
                'user'     => trim((string) ($row['user'] ?? '')),
                'key_path' => $path,
                'key_id'   => (int) $row['id'],
            ];
        } catch (\Throwable $e) {
            return $blank;
        }
    }

    /**
     * The ssh_login column is sometimes wrapped in stray quotes (legacy
     * escaping). Strip them so the value is safe to feed back into the
     * outer ssh command.
     */
    private static function reloadCleanSshLogin(string $raw): string
    {
        $clean = trim($raw, " \t\n\r'\"");
        return $clean === '' ? 'root' : $clean;
    }

    /**
     * Escape a string so it is safe inside POSIX single quotes — i.e.
     * close-quote, escaped-quote, re-open: foo'bar => foo'\''bar.
     */
    private static function reloadShSingleQuote(string $s): string
    {
        return str_replace("'", "'\\''", $s);
    }

    /**
     * Wrap a remote shell command in single quotes (after embedded
     * quotes have been escaped), suitable as the single string argument
     * to `bash -lc`.
     */
    private static function reloadShSingleQuoteWrap(string $s): string
    {
        return "'".self::reloadShSingleQuote($s)."'";
    }

    /**
     * Live-probe the server type / major version by SSH-ing into each
     * side and running `mariadb --version || mysql --version`. We need
     * the live value because the cached `ts_value` row can lag for
     * minutes after a server upgrade, which silently breaks the
     * version_match preflight check.
     *
     * Falls back to the cached `ts_value` if the SSH probe fails (or
     * times out within 5 s) — preflight will report version_match
     * accordingly.
     *
     * @return array{slave:string,master:string,slave_type:string,master_type:string}
     */
    private static function reloadDetectVersions(?array $slave, ?array $master): array
    {
        $db = Sgbd::sql(DB_DEFAULT);
        $vSlave  = self::reloadSshVersionProbe($slave);
        $vMaster = self::reloadSshVersionProbe($master);
        if ($vSlave === '') {
            $vSlave = self::reloadLatestVariable($db, (int) ($slave['id']  ?? 0), 'version');
        }
        if ($vMaster === '') {
            $vMaster = self::reloadLatestVariable($db, (int) ($master['id'] ?? 0), 'version');
        }
        $cSlave  = self::reloadLatestVariable($db, (int) ($slave['id']  ?? 0), 'version_comment');
        $cMaster = self::reloadLatestVariable($db, (int) ($master['id'] ?? 0), 'version_comment');
        return [
            'slave'        => $vSlave,
            'master'       => $vMaster,
            'slave_type'   => self::reloadInferType($vSlave, $cSlave),
            'master_type'  => self::reloadInferType($vMaster, $cMaster),
        ];
    }

    /**
     * SSH into the server and ask the local mariadb / mysql binary for
     * its `--version` banner. 5 s timeout, errors silently return ''
     * (caller falls back to ts_value cache).
     */
    private static function reloadSshVersionProbe(?array $server): string
    {
        if ($server === null) {
            return '';
        }
        $out = self::reloadSshExec($server, 'mariadb --version 2>/dev/null || mysql --version 2>/dev/null', 5);
        return $out['ok'] ? trim((string) $out['stdout']) : '';
    }

    private static function reloadInferType(string $version, string $comment): string
    {
        $haystack = strtolower($version.' '.$comment);
        if (str_contains($haystack, 'mariadb')) {
            return 'mariadb';
        }
        if ($haystack !== '') {
            return 'mysql';
        }
        return '';
    }

    private static function reloadLatestVariable($db, int $serverId, string $name): string
    {
        if ($serverId <= 0) {
            return '';
        }
        // ts_value is partitioned by (from, type) — ts_value_general_text
        // for the SHOW VARIABLES bag. Probe both the text and double
        // variants so disk-space / version are both reachable. Any SQL
        // error (table missing on this install) is swallowed and we
        // return ''; the preflight will surface a WARN, not FAIL.
        $candidates = ['ts_value_general_text', 'ts_value_general_int', 'ts_value_slave_text'];
        foreach ($candidates as $tbl) {
            try {
                $sql = "SELECT value FROM ".$tbl."
                          WHERE id_mysql_server = ".(int) $serverId."
                            AND id_ts_variable = (SELECT id FROM ts_variable WHERE name = '".$db->sql_real_escape_string($name)."' LIMIT 1)
                          ORDER BY date DESC LIMIT 1";
                $res = @$db->sql_query($sql);
                if ($res === false || $res === null) {
                    continue;
                }
                while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                    $v = (string) ($row['value'] ?? '');
                    if ($v !== '') {
                        return $v;
                    }
                }
            } catch (\Throwable $e) { /* try next table */ }
        }
        return '';
    }

    private static function reloadLoadServerRow($db, int $idServer): ?array
    {
        if ($idServer <= 0) {
            return null;
        }
        $sql = "SELECT * FROM mysql_server WHERE id = ".(int) $idServer." LIMIT 1";
        $res = $db->sql_query($sql);
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            return $row;
        }
        return null;
    }

    private static function reloadLoadMasterRow($db, ?array $slave, string $conn): ?array
    {
        if ($slave === null) {
            return null;
        }
        // Best-effort: resolve Master_Host / Source_Host from the live
        // SHOW REPLICA STATUS via the same fetchReplicaStatusRows() the
        // /slave/show page uses, then look up the matching mysql_server
        // row by ip or hostname. Failures fall through to null and the
        // preflight surfaces the gap via version_match.
        try {
            $rows = self::reloadFetchReplicaStatus($slave, $conn);
            $masterHost = (string) ($rows['Master_Host'] ?? $rows['Source_Host'] ?? '');
            $masterPort = (int)    ($rows['Master_Port'] ?? $rows['Source_Port'] ?? 3306);
            if ($masterHost !== '') {
                $hostEsc = $db->sql_real_escape_string($masterHost);
                // Look-up #1 — direct match (works for hosts not behind a tunnel).
                $sql = "SELECT * FROM mysql_server
                          WHERE is_deleted = 0
                            AND (ip = '{$hostEsc}' OR hostname = '{$hostEsc}' OR name = '{$hostEsc}')
                          LIMIT 1";
                $res = @$db->sql_query($sql);
                if ($res !== false && $res !== null) {
                    while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                        return $row;
                    }
                }
                // Look-up #2 — via ssh_tunnel.remote_host. In PmaControl's
                // tunneled inventory the `mysql_server.ip` is the local
                // loopback endpoint; the LAN IP the slave knows lives in
                // `ssh_tunnel.remote_host`. Match against the MySQL tunnel
                // (port-mode), not the SSH tunnel.
                $portClause = $masterPort > 0
                    ? " AND t.remote_port = ".(int) $masterPort
                    : '';
                $sql2 = "SELECT m.* FROM mysql_server m
                          INNER JOIN ssh_tunnel t ON t.id_mysql_server = m.id
                          WHERE m.is_deleted = 0
                            AND t.date_end IS NULL
                            AND t.remote_host = '{$hostEsc}'"
                            .$portClause."
                          ORDER BY t.id DESC
                          LIMIT 1";
                $res2 = @$db->sql_query($sql2);
                if ($res2 !== false && $res2 !== null) {
                    while ($row = $db->sql_fetch_array($res2, MYSQLI_ASSOC)) {
                        return $row;
                    }
                }
            }
        } catch (\Throwable $e) { /* swallow */ }
        return null;
    }

    /**
     * Fetch the latest SHOW REPLICA STATUS row for this server+connection
     * from the live monitored connection. Falls back to an empty array
     * if the slave is unreachable; preflight will report it via
     * replica_stale.
     */
    private static function reloadFetchReplicaStatus(?array $slave, string $conn): array
    {
        if ($slave === null) {
            return [];
        }
        try {
            $link = Sgbd::sql($slave['name']);
            $rows = self::fetchReplicaStatusRows($link);
            foreach ($rows as $r) {
                $name = (string) ($r['Connection_name'] ?? $r['Channel_Name'] ?? '');
                if ($conn === '' || $name === $conn) {
                    return $r;
                }
            }
            return $rows[0] ?? [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private static function reloadFetchSourceCoverage(int $idServer): ?array
    {
        try {
            // The current cached evaluation is read from a ts_value row
            // (see ReplicationSourceCoverage caller in show()); if not
            // available, return null and the preflight will surface a
            // 'warn' rather than block.
            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Live-probe disk stats over SSH: `df -B1 /var/lib/mysql` on the
     * slave (free bytes) and `du -sb /var/lib/mysql` on the master
     * (datadir bytes). 5 s timeout per probe; on any failure the field
     * falls back to the cached ts_value, else null — preflight will
     * mark the gate as WARN rather than FAIL.
     *
     * @return array{slave_free_bytes:?int,master_datadir_bytes:?int}
     */
    private static function reloadProbeDiskStats(?array $slave, ?array $master): array
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $slaveFreeLive = null;
        if ($slave !== null) {
            $r = self::reloadSshExec($slave, "df -B1 --output=avail /var/lib/mysql | tail -n +2", 5);
            if ($r['ok']) {
                $val = trim((string) $r['stdout']);
                if (ctype_digit($val)) {
                    $slaveFreeLive = (int) $val;
                }
            }
        }
        $masterBytesLive = null;
        if ($master !== null) {
            $r = self::reloadSshExec($master, "du -sb /var/lib/mysql 2>/dev/null | awk '{print \$1}'", 5);
            if ($r['ok']) {
                $val = trim((string) $r['stdout']);
                if (ctype_digit($val)) {
                    $masterBytesLive = (int) $val;
                }
            }
        }

        $slaveFree   = $slaveFreeLive   ?? self::reloadIntOrNull(self::reloadLatestVariable($db, (int) ($slave['id']  ?? 0), 'disk_free_bytes'));
        $masterBytes = $masterBytesLive ?? self::reloadIntOrNull(self::reloadLatestVariable($db, (int) ($master['id'] ?? 0), 'datadir_bytes'));
        return [
            'slave_free_bytes'     => $slaveFree,
            'master_datadir_bytes' => $masterBytes,
        ];
    }

    private static function reloadIntOrNull(string $v): ?int
    {
        return is_numeric($v) ? (int) $v : null;
    }

    /**
     * Helper: open an SSH (with -J jump chain resolved via ssh_tunnel)
     * to the given server row, run a remote bash one-liner, return
     * exit code + stdout + stderr. proc_open-based so it always
     * releases its pipes via `finally`.
     *
     * @param array<string,mixed>|null $server
     * @return array{ok:bool,stdout:string,stderr:string,exit:int,command:string}
     */
    private static function reloadSshExec(?array $server, string $remoteCmd, int $timeoutSec = 5): array
    {
        $fail = ['ok' => false, 'stdout' => '', 'stderr' => '', 'exit' => -1, 'command' => ''];
        if ($server === null) {
            return $fail;
        }
        $localHost = (string) ($server['ip'] ?? '');
        $localPort = (int)    ($server['ssh_port'] ?? 22);
        $ep = self::reloadResolveSshEndpoint($localHost, $localPort);
        $login = self::reloadCleanSshLogin((string) ($server['ssh_login'] ?? ''));

        $sshCmd = 'ssh -o ConnectTimeout='.(int) $timeoutSec
                . ' -o StrictHostKeyChecking=no -o BatchMode=yes'
                . $ep['jump_flag']
                . ' -p '.(int) $ep['port']
                . ' '.escapeshellarg($login).'@'.escapeshellarg($ep['host'])
                . ' '.escapeshellarg('bash -lc '.self::reloadShSingleQuoteWrap($remoteCmd));

        $process = null;
        $pipes   = [];
        $stdout = '';
        $stderr = '';
        $exit   = -1;
        try {
            $descriptors = [
                0 => ['file', '/dev/null', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ];
            $process = @proc_open($sshCmd, $descriptors, $pipes);
            if (!is_resource($process)) {
                return ['ok' => false, 'stdout' => '', 'stderr' => 'proc_open failed', 'exit' => -1, 'command' => $sshCmd];
            }
            $deadline = microtime(true) + (float) $timeoutSec + 2.0;
            stream_set_blocking($pipes[1], false);
            stream_set_blocking($pipes[2], false);
            while (true) {
                $status = proc_get_status($process);
                $stdout .= (string) stream_get_contents($pipes[1]);
                $stderr .= (string) stream_get_contents($pipes[2]);
                if (!$status['running']) {
                    $exit = (int) $status['exitcode'];
                    break;
                }
                if (microtime(true) > $deadline) {
                    @proc_terminate($process, 9);
                    $exit = 124;
                    break;
                }
                usleep(50000);
            }
        } catch (\Throwable $e) {
            $stderr .= $e->getMessage();
        } finally {
            foreach ($pipes as $p) {
                if (is_resource($p)) {
                    @fclose($p);
                }
            }
            if (is_resource($process)) {
                @proc_close($process);
            }
        }
        return [
            'ok'      => ($exit === 0),
            'stdout'  => $stdout,
            'stderr'  => $stderr,
            'exit'    => $exit,
            'command' => $sshCmd,
        ];
    }

    /**
     * Run the non-interactive SSH probe to the slave. PmaControl manages
     * every remote server through SSH tunnels (`ssh_tunnel` table) — the
     * slave row's `ip` is therefore typically `127.0.0.1` and `ssh_port`
     * is a local-loopback port that maps onto a real
     * `<bastion>...<target>:22` chain. We honor that mapping by feeding
     * the SSH command the *resolved* endpoint + the jump-host chain via
     * `-J`, otherwise the probe always tries to land on
     * `root@127.0.0.1:10104` and fails on hosts where the local-tunnel
     * loop didn't open the port.
     *
     * @return array{ok:bool,command:string,stderr:string,via_tunnel:bool,jump_hosts:list<string>}
     */
    private static function reloadProbeSsh(?array $slave): array
    {
        if ($slave === null) {
            return ['ok' => false, 'command' => '', 'stderr' => 'slave row not found', 'via_tunnel' => false, 'jump_hosts' => []];
        }
        $login = trim((string) ($slave['ssh_login'] ?? ''), " \t\n\r'\"");
        if ($login === '') {
            $login = 'root';
        }
        $localHost = (string) ($slave['ip'] ?? '');
        $localPort = (int) ($slave['ssh_port'] ?? 22);

        $resolved = self::reloadResolveSshEndpoint($localHost, $localPort);
        $targetHost = $resolved['host'];
        $targetPort = $resolved['port'];
        $jumpFlag   = $resolved['jump_flag'];

        $cmd = sprintf(
            "ssh -o ConnectTimeout=5 -o BatchMode=yes -o StrictHostKeyChecking=no%s -p %d %s@%s 'echo ok'",
            $jumpFlag,
            $targetPort,
            escapeshellarg($login),
            escapeshellarg($targetHost)
        );
        // PHP 8.x emits a Notice on tempnam() success ("file created in
        // the system's temporary directory") — harmless but visible in
        // the debug footer. Silence it explicitly.
        $stderrFile = @tempnam(sys_get_temp_dir(), 'sshprobe');
        $out = (string) @shell_exec($cmd.' 2> '.escapeshellarg($stderrFile));
        $err = $stderrFile && file_exists($stderrFile) ? (string) @file_get_contents($stderrFile) : '';
        if ($stderrFile) {
            @unlink($stderrFile);
        }
        $ok = trim($out) === 'ok';
        return [
            'ok'         => $ok,
            'command'    => $cmd,
            'stderr'     => trim($err),
            'via_tunnel' => $resolved['via_tunnel'],
            'jump_hosts' => $resolved['jump_hosts'],
        ];
    }

    /**
     * Resolve a slave's logical SSH endpoint into the real `<bastion> ...
     * <target>:port` chain via `ssh_tunnel`. Returns the raw endpoint
     * unchanged when the row's `ip:port` isn't tunneled (direct-access
     * server). Building `-J` so the actual remote (and any intermediate
     * jump host) is fed to `ssh` as a ProxyJump chain.
     *
     * @return array{host:string,port:int,via_tunnel:bool,jump_hosts:list<string>,jump_flag:string}
     */
    private static function reloadResolveSshEndpoint(string $localHost, int $localPort): array
    {
        $direct = [
            'host'       => $localHost,
            'port'       => $localPort,
            'via_tunnel' => false,
            'jump_hosts' => [],
            'jump_flag'  => '',
        ];
        if ($localHost === '' || $localPort <= 0) {
            return $direct;
        }
        try {
            $db = Sgbd::sql(DB_DEFAULT);
            $sql = "SELECT remote_host, remote_port, servers_jump "
                 . "FROM ssh_tunnel "
                 . "WHERE date_end IS NULL "
                 . "  AND local_host = '".$db->sql_real_escape_string($localHost)."' "
                 . "  AND local_port = ".$localPort." "
                 . "LIMIT 1";
            $res = $db->sql_query_silent($sql);
            if (!$res) {
                return $direct;
            }
            $row = $db->sql_fetch_array($res, MYSQLI_ASSOC);
            if (!$row) {
                return $direct;
            }
            $remoteHost = (string) $row['remote_host'];
            $remotePort = (int) $row['remote_port'];
            $jumps      = json_decode((string) ($row['servers_jump'] ?? '[]'), true) ?: [];
            $jumpHosts  = [];
            // The last entry of `servers_jump` is the target itself (also
            // recorded in `remote_host`/`remote_port`). Everything before
            // is a real intermediate hop we need to express with `-J`.
            $hopCount = count($jumps);
            for ($i = 0; $i < $hopCount - 1; $i++) {
                $hop = $jumps[$i];
                $hh = (string) ($hop['ip'] ?? $hop['remote_host'] ?? '');
                $hp = (int)    ($hop['port'] ?? $hop['remote_port'] ?? 22);
                if ($hh === '') continue;
                $jumpHosts[] = $hh.':'.$hp;
            }
            $jumpFlag = $jumpHosts !== []
                ? ' -J '.escapeshellarg(implode(',', $jumpHosts))
                : '';
            return [
                'host'       => $remoteHost,
                'port'       => $remotePort > 0 ? $remotePort : 22,
                'via_tunnel' => true,
                'jump_hosts' => $jumpHosts,
                'jump_flag'  => $jumpFlag,
            ];
        } catch (\Throwable $e) {
            return $direct;
        }
    }

    private static function reloadInsertJobRow($db, array $params): int
    {
        $uuid = bin2hex(random_bytes(16));
        $uuid = substr($uuid, 0, 8).'-'.substr($uuid, 8, 4).'-'.substr($uuid, 12, 4)
              .'-'.substr($uuid, 16, 4).'-'.substr($uuid, 20, 12);
        $paramJson = $db->sql_real_escape_string(json_encode($params));
        $uuidEsc   = $db->sql_real_escape_string($uuid);
        $logPath   = ROOT.'/tmp/log/reload-pending-'.$uuid.'.log';
        $logEsc    = $db->sql_real_escape_string($logPath);
        $sql = "INSERT INTO job (uuid, class, method, param, date_start, pid, log, error, status, progress_percent)
                VALUES ('{$uuidEsc}', 'App\\\\Controller\\\\Slave', 'reloadFromMasterCli',
                        '{$paramJson}', NOW(), 0, '{$logEsc}', '', 'RUNNING', 0)";
        $db->sql_query($sql);
        $res = $db->sql_query("SELECT LAST_INSERT_ID() AS id");
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            return (int) ($row['id'] ?? 0);
        }
        return 0;
    }

    private static function reloadForkCliWorker(int $jobId): int
    {
        $logPath = ROOT.'/tmp/log/reload-'.$jobId.'.log';
        @mkdir(dirname($logPath), 0755, true);
        // update the job row's log to the resolved path so the view
        // reads from the right file.
        try {
            $db = Sgbd::sql(DB_DEFAULT);
            $db->sql_query("UPDATE job SET log='".$db->sql_real_escape_string($logPath)."' WHERE id=".(int) $jobId);
        } catch (\Throwable $ignored) {}
        $cmd = 'cd '.escapeshellarg(ROOT)
             .' && nohup php App/Webroot/index.php Slave reloadFromMasterCli '.(int) $jobId
             .' > '.escapeshellarg($logPath).' 2>&1 & echo $!';
        return (int) trim((string) @shell_exec($cmd));
    }

    private static function reloadSqlString($db, string $value): string
    {
        return "'".$db->sql_real_escape_string($value)."'";
    }
}
