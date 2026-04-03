<?php

namespace App\Controller;

use App\Library\Display;
use App\Library\MysqlLogCollector;
use Glial\Security\Crypt\Crypt;
use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use \App\Library\Mysql;
use \App\Library\Debug;
use \App\Library\System;
use App\Library\Extraction2;

// ALTER TABLE mysql_server ADD SYSTEM VERSIONING PARTITION BY SYSTEM_TIME;
/*

ALTER TABLE mysql_server ADD SYSTEM VERSIONING;


ALTER TABLE mysql_server REMOVE PARTITIONING;
ALTER TABLE mysql_server DROP SYSTEM VERSIONING;
ALTER TABLE mysql_server ADD SYSTEM VERSIONING PARTITION BY SYSTEM_TIME;

-----------------

CREATE TABLE mysql_server2 LIKE mysql_server;

ALTER TABLE mysql_server2
    DROP COLUMN IF EXISTS SYS_START,
    DROP COLUMN IF EXISTS SYS_END;


INSERT INTO mysql_server2
SELECT * FROM mysql_server
WHERE SYS_END = 'infinity';

ALTER TABLE mysql_server2 ADD SYSTEM VERSIONING PARTITION BY SYSTEM_TIME;

RENAME TABLE mysql_server2 TO mysql_server;









*/

class MysqlServer extends Controller
{
/**
 * Stores `$table_exists_cache` for table exists cache.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    private $table_exists_cache = array();

/**
 * Handle mysql server state through `quickMysqlSilentTest`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $ip Input value for `ip`.
 * @phpstan-param mixed $ip
 * @psalm-param mixed $ip
 * @param mixed $port Input value for `port`.
 * @phpstan-param mixed $port
 * @psalm-param mixed $port
 * @param mixed $user Input value for `user`.
 * @phpstan-param mixed $user
 * @psalm-param mixed $user
 * @param mixed $password Input value for `password`.
 * @phpstan-param mixed $password
 * @psalm-param mixed $password
 * @return mixed Returned value for quickMysqlSilentTest.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::quickMysqlSilentTest()
 * @example /fr/mysqlserver/quickMysqlSilentTest
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function quickMysqlSilentTest($ip, $port, $user, $password)
    {
        $link = mysqli_init();
        if (!$link) {
            return 'mysqli_init failed';
        }

        @mysqli_options($link, MYSQLI_OPT_CONNECT_TIMEOUT, 1);
        $ok = @mysqli_real_connect($link, (string)$ip, (string)$user, (string)$password, 'mysql', (int)$port);

        if ($ok) {
            @mysqli_close($link);
            return true;
        }

        return 'Connect Error ('.mysqli_connect_errno().') '.mysqli_connect_error();
    }

/**
 * Retrieve mysql server state through `getProcesslistConnectionMetrics`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $db Input value for `db`.
 * @phpstan-param mixed $db
 * @psalm-param mixed $db
 * @return array Returned value for getProcesslistConnectionMetrics.
 * @phpstan-return array
 * @psalm-return array
 * @see self::getProcesslistConnectionMetrics()
 * @example /fr/mysqlserver/getProcesslistConnectionMetrics
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function getProcesslistConnectionMetrics($db): array
    {
        $metrics = [
            'threads_running' => 0,
            'threads_connected' => 0,
            'max_used_connections' => 0,
            'max_connections' => 0,
        ];

        $sqlStatus = "SHOW GLOBAL STATUS WHERE Variable_name IN ('Threads_running', 'Threads_connected', 'Max_used_connections')";
        $resStatus = Mysql::sqlQuerySilentCompat($db, $sqlStatus);
        if ($resStatus) {
            while ($arr = $db->sql_fetch_array($resStatus, MYSQLI_ASSOC)) {
                $name = strtolower((string)($arr['Variable_name'] ?? ''));
                $value = (int)($arr['Value'] ?? 0);

                if ($name === 'threads_running') {
                    $metrics['threads_running'] = $value;
                } elseif ($name === 'threads_connected') {
                    $metrics['threads_connected'] = $value;
                } elseif ($name === 'max_used_connections') {
                    $metrics['max_used_connections'] = $value;
                }
            }
        }

        $sqlVariables = "SHOW GLOBAL VARIABLES WHERE Variable_name = 'max_connections'";
        $resVariables = Mysql::sqlQuerySilentCompat($db, $sqlVariables);
        if ($resVariables) {
            while ($arr = $db->sql_fetch_array($resVariables, MYSQLI_ASSOC)) {
                $metrics['max_connections'] = (int)($arr['Value'] ?? 0);
            }
        }

        return $metrics;
    }

/**
 * Handle mysql server state through `informationSchemaTableExists`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $db Input value for `db`.
 * @phpstan-param mixed $db
 * @psalm-param mixed $db
 * @param mixed $schema Input value for `schema`.
 * @phpstan-param mixed $schema
 * @psalm-param mixed $schema
 * @param mixed $table Input value for `table`.
 * @phpstan-param mixed $table
 * @psalm-param mixed $table
 * @return mixed Returned value for informationSchemaTableExists.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::informationSchemaTableExists()
 * @example /fr/mysqlserver/informationSchemaTableExists
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function informationSchemaTableExists($db, $schema, $table, $id_mysql_server = null)
    {
        $cache_key = $db->host.":".$db->port.":".$schema.".".$table;
        if (isset($this->table_exists_cache[$cache_key])) {
            return $this->table_exists_cache[$cache_key];
        }

        $schema = $db->sql_real_escape_string($schema);
        $table  = $db->sql_real_escape_string($table);

        $sql = "SELECT 1 FROM information_schema.tables WHERE table_schema = '".$schema."' AND table_name = '".$table."' LIMIT 1";
        $res = Mysql::sqlQueryWithInformationSchemaTablesTimeout($db, $sql, $id_mysql_server, __METHOD__, true);
        if (!$res) {
            $this->table_exists_cache[$cache_key] = false;
            return false;
        }

        $row = $db->sql_fetch_array($res, MYSQLI_NUM);
        $exists = !empty($row);

        $this->table_exists_cache[$cache_key] = $exists;
        return $exists;
    }

/**
 * Handle mysql server state through `menu`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for menu.
 * @phpstan-return void
 * @psalm-return void
 * @see self::menu()
 * @example /fr/mysqlserver/menu
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function menu($param)
    {
        $this->di['js']->code_javascript('
        
        $("#mysql_server-id").change(function () {
            data = $(this).val();
            var segments = GLIAL_URL.split("/");

            if(segments.length > 2) {
                segments[2] = data;
            }
            newPath = GLIAL_LINK + segments.join("/");
            window.location.href=newPath;
        });');

        $this->set('param', $param);
    }


/**
 * Handle mysql server state through `processlist`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for processlist.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @see self::processlist()
 * @example /fr/mysqlserver/processlist
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function processlist($param)
    {
        $id_mysql_server = $param[0];
        $_GET['mysql_server']['id'] = $id_mysql_server;
        $showSystemThreads = !empty($_GET['show_system_threads']) && $_GET['show_system_threads'] === '1';

        $isAjax = !empty($_GET['ajax']) && $_GET['ajax'] === "true";
        if ($isAjax){
            $this->layout_name = false;
        }

        $metadataLockInfo = [];
        $metadataLockServers = [];
        $metadataLockServerIds = [];
        $checkMetadataLockPlugin = !$isAjax;

        if (!$checkMetadataLockPlugin) {
            $metadataLockServerIds = self::parseIdList($_GET['metadata_lock_servers'] ?? '');
            $metadataLockServers = $metadataLockServerIds;
        }

        $id_mysql_servers = array();
        $id_mysql_servers[] = $id_mysql_server;
        $nb_server =1;

        /* case for GALERA to merge serveur
        $data = Extraction::display(array("variables::performance_schema", "wsrep_incoming_addresses"));

        if (! empty($data[$id_mysql_server]['']['wsrep_incoming_addresses']))
        {
            $wsrep_incoming_addresses = $data[$id_mysql_server]['']['wsrep_incoming_addresses'];
            $id_mysql_servers = Mysql::getIdMySQLFromGalera($wsrep_incoming_addresses);

            $nb_server = count($id_mysql_servers);            
        }*/

        //test if performance_schema activated or not
        $data['processlist'] = array();
        $data['offline_diagnostics'] = array();
        $connectionSnapshot = [
            'threads_running' => 0,
            'threads_connected' => 0,
            'max_used_connections' => 0,
            'max_connections' => 0,
        ];

        $availability = Extraction2::display([
            "mysql_server::mysql_available","is_vip",
            'mysql_server::mysql_error',
        ], $id_mysql_servers);

        foreach($id_mysql_servers as $id_mysql_server)
        {
            $availableRow = $availability[$id_mysql_server] ?? [];
            $credentials = self::getMysqlServerCredentials((int)$id_mysql_server);

            $isServerMonitored = (string)($credentials['is_monitored'] ?? '0') === '1';
            $isClientMonitored = (string)($credentials['client_is_monitored'] ?? '1') !== '0';
            $isEffectiveMonitored = !empty($credentials['effective_is_monitored']) || ($isServerMonitored && $isClientMonitored);

            $hasAvailabilityMetric = array_key_exists('mysql_available', $availableRow)
                || array_key_exists('mysql_server::mysql_available', $availableRow);

            $mysqlAvailable = (string)($availableRow['mysql_available'] ?? $availableRow['mysql_server::mysql_available'] ?? ($isEffectiveMonitored ? '1' : '0'));
            $mysqlError = (string)($availableRow['mysql_error'] ?? $availableRow['mysql_server::mysql_error'] ?? '');

            if (!$isEffectiveMonitored && !$hasAvailabilityMetric) {
                $ip = (string)($credentials['ip'] ?? '');
                $port = (int)($credentials['port'] ?? 0);

                $finalError = trim($mysqlError);
                if ($finalError === '') {
                    $finalError = 'Monitoring is disabled for this server (or its client); connection check skipped.';
                }

                $data['offline_diagnostics'][$id_mysql_server] = [
                    'id_mysql_server' => $id_mysql_server,
                    'ip' => $ip,
                    'port' => $port,
                    'mysql_available' => 0,
                    'mysql_error' => $finalError,
                    'port_open' => 0,
                    'port_message' => 'Monitoring disabled: IP/Port check skipped',
                    'mysql_test_message' => '',
                ];

                continue;
            }

            if ($mysqlAvailable === '0') {
                $ip = (string)($credentials['ip'] ?? '');
                $port = (int)($credentials['port'] ?? 0);
                $login = (string)($credentials['login'] ?? '');
                $password = (string)($credentials['password'] ?? '');

                $portOpen = false;
                $portMessage = 'IP/Port unavailable';
                if ($ip !== '' && $port > 0) {
                    $portOpen = System::scanPort($ip, $port, 1);
                    $portMessage = $portOpen
                        ? 'IP/Port open ('.$ip.':'.$port.')'
                        : 'IP/Port closed ('.$ip.':'.$port.')';
                }

                $mysqlTestMessage = '';
                if ($portOpen && $ip !== '' && $port > 0 && $login !== '') {
                    $mysqlTest = self::quickMysqlSilentTest($ip, $port, $login, $password);
                    if ($mysqlTest === true) {
                        $mysqlTestMessage = 'MySQL silent test: connection OK';
                    } else {
                        $mysqlTestMessage = 'MySQL silent test: '.$mysqlTest;
                    }
                }

                $finalError = trim($mysqlError);
                if ($finalError === '' && $mysqlTestMessage !== '') {
                    $finalError = $mysqlTestMessage;
                }
                if ($finalError === '') {
                    $finalError = $portMessage;
                }

                $data['offline_diagnostics'][$id_mysql_server] = [
                    'id_mysql_server' => $id_mysql_server,
                    'ip' => $ip,
                    'port' => $port,
                    'mysql_available' => 0,
                    'mysql_error' => $finalError,
                    'port_open' => $portOpen ? 1 : 0,
                    'port_message' => $portMessage,
                    'mysql_test_message' => $mysqlTestMessage,
                ];

                continue;
            }

            set_error_handler(static function ($severity, $message, $file, $line) {
                if (!(error_reporting() & $severity)) {
                    return false;
                }

                throw new \ErrorException($message, 0, $severity, $file, $line);
            });

            try {
                $db = Mysql::getDbLink($id_mysql_server);
            } catch (\Throwable $e) {
                $ip = (string)($credentials['ip'] ?? '');
                $port = (int)($credentials['port'] ?? 0);

                $data['offline_diagnostics'][$id_mysql_server] = [
                    'id_mysql_server' => $id_mysql_server,
                    'ip' => $ip,
                    'port' => $port,
                    'mysql_available' => 0,
                    'mysql_error' => 'Unable to initialize DB link: '.$e->getMessage(),
                    'port_open' => 0,
                    'port_message' => 'IP/Port status unknown',
                    'mysql_test_message' => '',
                ];
                continue;
            } finally {
                restore_error_handler();
            }

            $metrics = self::getProcesslistConnectionMetrics($db);
            $connectionSnapshot['threads_running'] += (int)$metrics['threads_running'];
            $connectionSnapshot['threads_connected'] += (int)$metrics['threads_connected'];
            $connectionSnapshot['max_used_connections'] += (int)$metrics['max_used_connections'];
            $connectionSnapshot['max_connections'] += (int)$metrics['max_connections'];

            $has_innodb_trx = $this->informationSchemaTableExists($db, 'information_schema', 'innodb_trx', $id_mysql_server);
            $has_perf_threads = $this->informationSchemaTableExists($db, 'performance_schema', 'threads', $id_mysql_server);
            $has_info_processlist = $this->informationSchemaTableExists($db, 'information_schema', 'processlist', $id_mysql_server);

            $metadataLockEnabled = false;
            if ($checkMetadataLockPlugin) {
                $metadataLockEnabled = $this->hasMetadataLockInfoPlugin($db);
                if ($metadataLockEnabled) {
                    $metadataLockServers[] = $id_mysql_server;
                }
            } else {
                $metadataLockEnabled = in_array($id_mysql_server, $metadataLockServerIds, true);
            }

            if ($db->checkVersion(array('Percona Server' => '5.6')) && ! $db->checkVersion(array('Percona Server' => '5.7')))
            {
                $sql = "SHOW FULL PROCESSLIST";
            }
            else if ($db->checkVersion(array('MySQL' => '8.0')) && $has_perf_threads)
            {
                if ($has_innodb_trx) {
                    $sql = "SELECT /* pmacontrol-processlist */
                        $id_mysql_server                    AS id_mysql_server,
                        processlist_id                      AS id,
                        IFNULL(thread_id, '0')              AS mysql_thread_id,
                        IFNULL(processlist_user, '')        AS user,
                        IFNULL(processlist_host, '')        AS host,
                        IFNULL(processlist_db, '')          AS db,
                        IFNULL(processlist_command, '')     As command,
                        IFNULL(processlist_time, '0')       AS time,
                        IFNULL(processlist_info, '')        AS query,
                        IFNULL(processlist_state, '')       AS state,
                        IFNULL(trx_query, '')               AS trx_query,
                        IFNULL(trx_state, '')               AS trx_state,
                        IFNULL(trx_operation_state, '')     AS trx_operation_state,
                        IFNULL(trx_rows_locked, '0')        AS trx_rows_locked,
                        IFNULL(trx_rows_modified, '0')      AS trx_rows_modified,
                        IFNULL(trx_concurrency_tickets, '') AS trx_concurrency_tickets,
                        IFNULL(TIMESTAMPDIFF(SECOND, trx_started, NOW()), '') AS trx_time
                    FROM
                        performance_schema.threads t
                        LEFT JOIN information_schema.innodb_trx tx ON trx_mysql_thread_id = t.processlist_id
                    WHERE
                        processlist_id IS NOT NULL AND
                        processlist_time IS NOT NULL AND
                        ".($showSystemThreads ? "1 = 1" : "processlist_command != 'Daemon'
                        AND (processlist_command != 'Sleep' AND processlist_command NOT LIKE 'Binlog Dump%')")."
                        AND ".($showSystemThreads ? "1 = 1" : "(processlist_info IS NOT NULL OR trx_query IS NOT NULL)")."
                        AND IFNULL(processlist_state, '') NOT LIKE 'Group Replication Module%'
                        ORDER BY processlist_time DESC;";
                } else {
                    $sql = "SELECT /* pmacontrol-processlist */
                        $id_mysql_server                    AS id_mysql_server,
                        processlist_id                      AS id,
                        IFNULL(thread_id, '0')              AS mysql_thread_id,
                        IFNULL(processlist_user, '')        AS user,
                        IFNULL(processlist_host, '')        AS host,
                        IFNULL(processlist_db, '')          AS db,
                        IFNULL(processlist_command, '')     As command,
                        IFNULL(processlist_time, '0')       AS time,
                        IFNULL(processlist_info, '')        AS query,
                        IFNULL(processlist_state, '')       AS state,
                        ''                                  AS trx_query,
                        'N/A'                               AS trx_state,
                        'N/A'                               AS trx_operation_state,
                        'N/A'                               AS trx_rows_locked,
                        'N/A'                               AS trx_rows_modified,
                        'N/A'                               AS trx_concurrency_tickets,
                        'N/A'                               AS trx_time
                    FROM
                        performance_schema.threads t
                    WHERE
                        processlist_id IS NOT NULL AND
                        processlist_time IS NOT NULL AND
                        ".($showSystemThreads ? "1 = 1" : "processlist_command != 'Daemon'
                        AND (processlist_command != 'Sleep' AND processlist_command NOT LIKE 'Binlog Dump%')")."
                        AND ".($showSystemThreads ? "1 = 1" : "processlist_info IS NOT NULL")."
                        AND IFNULL(processlist_state, '') NOT LIKE 'Group Replication Module%'
                        ORDER BY processlist_time DESC;";
                }


            }
            else if ($has_info_processlist)
            {
                /*
                $sql ="SELECT 
                    p.ID AS mysql_thread_id,
                    p.USER AS user,
                    p.HOST AS host,
                    p.DB AS database_name,
                    p.COMMAND AS command,
                    p.TIME AS time,
                    p.STATE AS state,
                    p.INFO AS query,
                    0 AS trx_operation_state,
                    0 AS trx_rows_locked,
                    0 AS trx_rows_modified,
                    'sfhGSRHS' as trx_state,
                    IFNULL(TIMESTAMPDIFF(SECOND, trx_started, NOW()), '') AS trx_time
                    FROM information_schema.processlist p
                    INNER JOIN information_schema.innodb_trx t ON p.ID = t.trx_mysql_thread_id
                    WHERE Command != 'Sleep'
                    ORDER BY 
                    p.TIME DESC;";
                */

                if ($has_innodb_trx) {
                    $sql ="SELECT /* pmacontrol-processlist */
                        $id_mysql_server AS id_mysql_server,
                        p.ID AS id,
                        t.trx_mysql_thread_id as mysql_thread_id,
                        p.USER AS user,
                        p.HOST AS host,
                        p.DB AS database_name,
                        p.COMMAND AS command,
                        p.TIME AS time,
                        p.STATE AS state,
                        p.INFO AS query,
                        IFNULL(trx_query, '')               AS trx_query,
                        IFNULL(trx_state, '')               AS trx_state,
                        IFNULL(trx_operation_state, '')     AS trx_operation_state,
                        IFNULL(trx_rows_locked, '0')        AS trx_rows_locked,
                        IFNULL(trx_rows_modified, '0')      AS trx_rows_modified,
                        IFNULL(trx_concurrency_tickets, '') AS trx_concurrency_tickets,
                        IFNULL(TIMESTAMPDIFF(SECOND, trx_started, NOW()), '') AS trx_time
                    FROM information_schema.processlist p
                    LEFT JOIN information_schema.innodb_trx t ON p.ID = t.trx_mysql_thread_id
                    WHERE ".($showSystemThreads ? "1 = 1" : "(command != 'Sleep' AND command NOT LIKE 'Binlog Dump%')")."
                    ORDER BY 
                        p.TIME DESC;";
                } else {
                    $sql ="SELECT /* pmacontrol-processlist */
                        $id_mysql_server AS id_mysql_server,
                        p.ID AS id,
                        p.ID as mysql_thread_id,
                        p.USER AS user,
                        p.HOST AS host,
                        p.DB AS database_name,
                        p.COMMAND AS command,
                        p.TIME AS time,
                        p.STATE AS state,
                        p.INFO AS query,
                        '' AS trx_query,
                        'N/A' AS trx_state,
                        'N/A' AS trx_operation_state,
                        'N/A' AS trx_rows_locked,
                        'N/A' AS trx_rows_modified,
                        'N/A' AS trx_concurrency_tickets,
                        'N/A' AS trx_time
                    FROM information_schema.processlist p
                    WHERE ".($showSystemThreads ? "1 = 1" : "(command != 'Sleep' AND command NOT LIKE 'Binlog Dump%')")."
                    ORDER BY 
                        p.TIME DESC;";
                }
            }
            else
            {
                $sql = "SHOW FULL PROCESSLIST";
            }

            $res = $db->sql_query($sql);
            if (!$res) {
                continue;
            }

            if ($metadataLockEnabled) {
                $metadataLockInfo = array_merge(
                    $metadataLockInfo,
                    $this->fetchMetadataLockInfo($db, (int)$id_mysql_server)
                );
            }

            if ($sql === "SHOW FULL PROCESSLIST") {
                while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                    $command = $arr['Command'] ?? $arr['COMMAND'] ?? '';
                    $info = $arr['Info'] ?? $arr['INFO'] ?? '';

                    if (!$showSystemThreads && ($command === 'Sleep' || str_starts_with($command, 'Binlog Dump'))) {
                        continue;
                    }

                    $data['processlist'][] = array(
                        'id_mysql_server'       => $id_mysql_server,
                        'id'                    => $arr['Id'] ?? $arr['ID'] ?? '',
                        'mysql_thread_id'       => $arr['Id'] ?? $arr['ID'] ?? '',
                        'user'                  => $arr['User'] ?? $arr['USER'] ?? '',
                        'host'                  => $arr['Host'] ?? $arr['HOST'] ?? '',
                        'database_name'         => $arr['db'] ?? $arr['DB'] ?? '',
                        'command'               => $command,
                        'time'                  => $arr['Time'] ?? $arr['TIME'] ?? 0,
                        'state'                 => $arr['State'] ?? $arr['STATE'] ?? '',
                        'query'                 => $info,
                        'trx_query'             => '',
                        'trx_state'             => 'N/A',
                        'trx_operation_state'   => 'N/A',
                        'trx_rows_locked'       => 'N/A',
                        'trx_rows_modified'     => 'N/A',
                        'trx_concurrency_tickets' => 'N/A',
                        'trx_time'              => 'N/A',
                        'class'                 => self::getProcesslistClass($arr['Time'] ?? $arr['TIME'] ?? 0),
                    );
                }
                continue;
            }

            while($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC))
            {
                $queryText = (string)($arr['query'] ?? '');

                if ((!$showSystemThreads && $queryText === '') || str_contains($queryText, '/* pmacontrol-processlist */')) {
                    continue;
                }

                if ($arr['time'] > 600){
                    $arr['class'] = "danger";
                }else if ($arr['time'] > 60) {
                    $arr['class'] = "warning";
                }else if ($arr['time'] > 10) {
                    $arr['class'] = "primary";
                }else if ($arr['time'] > 1) {
                    $arr['class'] = "info";
                }else{
                    $arr['class'] = "";
                }

                $data['processlist'][] = $arr;
            }
        }

        //$your_arr = array_map(function(){ return [$v["period"] => $v["way"]]; },$your_arr);
        
        usort($data['processlist'], function($a, $b) {
            return $b['time'] <=> $a['time'];
        });

        if (!empty($metadataLockInfo)) {
            usort($metadataLockInfo, function($a, $b) {
                return ((int)($b['lock_time_ms'] ?? 0)) <=> ((int)($a['lock_time_ms'] ?? 0));
            });
        }

        $maxConnections = max(0, (int)$connectionSnapshot['max_connections']);
        $running = max(0, (int)$connectionSnapshot['threads_running']);
        $connected = max(0, (int)$connectionSnapshot['threads_connected']);
        $maxUsed = max(0, (int)$connectionSnapshot['max_used_connections']);

        $data['connections_bar'] = [
            'threads_running' => $running,
            'threads_connected' => $connected,
            'max_used_connections' => $maxUsed,
            'max_connections' => $maxConnections,
            'running_percent' => $maxConnections > 0 ? round(($running / $maxConnections) * 100, 2) : 0,
            'connected_percent' => $maxConnections > 0 ? round(($connected / $maxConnections) * 100, 2) : 0,
            'max_used_percent' => $maxConnections > 0 ? round(($maxUsed / $maxConnections) * 100, 2) : 0,
        ];

        $metadataLockServers = array_values(array_unique(array_map('intval', $metadataLockServers)));
        $data['metadata_lock_info'] = $metadataLockInfo;
        $data['metadata_lock_enabled'] = !empty($metadataLockServers);
        $data['metadata_lock_servers'] = $metadataLockServers;
        $data['show_system_threads'] = $showSystemThreads;

        $metadataLockQuery = '';
        if (!empty($metadataLockServers)) {
            $metadataLockQuery = '/metadata_lock_servers:' . implode(',', $metadataLockServers);
        }

        $metadataLockQueryJs = json_encode($metadataLockQuery);
        $data['processlist_extra_path'] = $metadataLockQuery;

        //debug($data['processlist']);

        $time = $nb_server * 500;

        $this->di['js']->code_javascript('
        $(document).ready(function()
        {
            var intervalId;
            var refreshInterval = 1000; // Intervalle par défaut de 1 secondes
            var metadataLockQuery = '.$metadataLockQueryJs.';

            function refresh()
            {
                var extraQuery = metadataLockQuery;
                var checkbox = document.getElementById("show-system-threads");
                var baseUrl = GLIAL_LINK+GLIAL_URL;

                baseUrl = baseUrl.replace(/\/ajax:true(?:\/|$)/, "/");
                baseUrl = baseUrl.replace(/\/show_system_threads:[^/]+/g, "");
                baseUrl = baseUrl.replace(/\/metadata_lock_servers:[^/]+/g, "");
                baseUrl = baseUrl.replace(/\/+$/, "");

                if (checkbox && checkbox.checked) {
                    extraQuery += "/show_system_threads:1";
                }

                var myURL = baseUrl+"/ajax:true"+extraQuery;
                $("#processlist").load(myURL);
            }

            function setRefreshInterval(newInterval) {
                refreshInterval = newInterval;
                clearInterval(intervalId);
                intervalId = setInterval(refresh, refreshInterval);
            }

            function stopRefresh() {
                clearInterval(intervalId);
                intervalId = null;
            }

            intervalId = setInterval(refresh, refreshInterval);

            // Rendre les fonctions accessibles globalement
            window.setRefreshInterval = setRefreshInterval;
            window.stopRefresh = stopRefresh;

        })');

        $this->set('param', $param);
        $this->set('data', $data);

    }

/**
 * Handle mysql server state through `logs`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for logs.
 * @phpstan-return void
 * @psalm-return void
 * @see self::logs()
 * @example /fr/mysqlserver/logs
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function logs($param)
    {
        if (empty($param[0]) || !ctype_digit((string)$param[0])) {
            throw new \Exception("Usage: /mysqlserver/logs/{id_mysql_server}/{log_type}");
        }

        $id_mysql_server = (int)$param[0];
        $currentType = (string)($param[1] ?? MysqlLogCollector::LOG_TYPE_ERROR);
        $pageSize = 100;
        $currentPage = max(1, (int)($_GET['page'] ?? 1));

        $tabs = [
            MysqlLogCollector::LOG_TYPE_ERROR => __('Error log'),
            MysqlLogCollector::LOG_TYPE_SLOW_QUERY => __('Slow query'),
            MysqlLogCollector::LOG_TYPE_SQL_ERROR => __('SQL error'),
            MysqlLogCollector::LOG_TYPE_GENERAL => __('General log'),
            MysqlLogCollector::LOG_TYPE_OOM => __('OOM killer'),
        ];

        if (!isset($tabs[$currentType])) {
            $currentType = MysqlLogCollector::LOG_TYPE_ERROR;
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $this->di['js']->addJavascript(array(
            'chart-4.5.1.umd.min.js',
            'MysqlServer/logs.js?v=' . @filemtime(APP_DIR . DS . 'Webroot' . DS . 'js' . DS . 'MysqlServer' . DS . 'logs.js')
        ));

        $sqlServer = "SELECT `name`, `display_name`
            FROM `mysql_server`
            WHERE `id` = " . $id_mysql_server . "
            LIMIT 1";
        $resServer = $db->sql_query($sqlServer);
        $server = $db->sql_fetch_array($resServer, MYSQLI_ASSOC) ?: [];
        [$counts, $summaryByType, $sourcesByType] = $this->loadMysqlLogsCacheSummary($id_mysql_server, array_keys($tabs));

        $summary = $summaryByType[$currentType] ?? [
            'total_rows' => 0,
            'min_event_time' => null,
            'max_event_time' => null,
        ];
        $lines = [];
        $currentSource = (string)($sourcesByType[$currentType] ?? '');

        $totalRowsCurrentType = (int)($summary['total_rows'] ?? 0);
        $totalPages = max(1, (int)ceil($totalRowsCurrentType / $pageSize));
        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }

        $this->title = __('Logs');
        $this->ariane = " > " . __('Mysql Server') . " > " . $this->title;

        $this->set('param', $param);
        $this->set('id_mysql_server', $id_mysql_server);
        $this->set('current_type', $currentType);
        $this->set('tabs', $tabs);
        $this->set('counts', $counts);
        $this->set('lines', $lines);
        $this->set('summary', $summary);
        $this->set('server', $server);
        $this->set('current_source', $currentSource);
        $this->set('current_page', $currentPage);
        $this->set('page_size', $pageSize);
        $this->set('total_pages', $totalPages);

        $chartPayload = $this->buildMysqlLogsChartPayload($id_mysql_server, $currentType);
        $initialLinesPayload = $this->loadMysqlLogWindowLines($id_mysql_server, $currentType, 'month', '', $currentPage, $pageSize);
        $this->set('chart_payload', $chartPayload);
        $this->set('initial_lines_payload', $initialLinesPayload);
    }

    public function logsChartData($param)
    {
        $this->layout_name = false;
        $this->layout = false;
        $this->view = false;

        $idMysqlServer = (int)($param[0] ?? 0);
        $logType = (string)($param[1] ?? MysqlLogCollector::LOG_TYPE_ERROR);
        $granularity = (string)($param[2] ?? 'day');
        $key = (string)($param[3] ?? '');

        if ($key === 'ajax:true') {
            $key = '';
        }

        $result = [
            'labels' => [],
            'datasets' => [
                'ERROR' => [],
                'WARNING' => [],
                'NOTE' => [],
            ],
        ];

        $baseDir = DATA . 'logs/' . $idMysqlServer . '/' . MysqlLogCollector::getStorageDirectoryName($logType);

        if ($granularity === 'day') {
            $result = $this->buildMysqlLogsChartPayload($idMysqlServer, $logType)['day'] ?? $result;
        } elseif ($granularity === 'hour' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $key)) {
            $dayDir = $baseDir . '/' . $key;
            $hourCache = $this->readMysqlLogsChartCache($dayDir . '/chart.hour.json');
            if (!empty($hourCache['hours']) && is_array($hourCache['hours'])) {
                $result = [
                    'labels' => array_keys($hourCache['hours']),
                    'datasets' => [
                        'ERROR' => array_column($hourCache['hours'], 'ERROR'),
                        'WARNING' => array_column($hourCache['hours'], 'WARNING'),
                        'NOTE' => array_column($hourCache['hours'], 'NOTE'),
                    ],
                ];
            }
        } elseif ($granularity === 'minute' && preg_match('/^\d{4}-\d{2}-\d{2}_\d{2}$/', $key)) {
            [$dayKey, $hourPrefix] = explode('_', $key, 2);
            $dayDir = $baseDir . '/' . $dayKey;
            $minuteCache = $this->readMysqlLogsChartCache($dayDir . '/chart.minute.json');
            $hourLabel = $hourPrefix . ':00';
            if (!empty($minuteCache['hours'][$hourLabel]) && is_array($minuteCache['hours'][$hourLabel])) {
                $result = $minuteCache['hours'][$hourLabel];
            }
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($result);
    }

    public function logsLinesData($param)
    {
        $this->layout_name = false;
        $this->layout = false;
        $this->view = false;

        $idMysqlServer = (int)($param[0] ?? 0);
        $logType = (string)($param[1] ?? MysqlLogCollector::LOG_TYPE_ERROR);
        $scope = (string)($param[2] ?? 'month');
        $key = (string)($param[3] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));

        if ($key === 'ajax:true') {
            $key = '';
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($this->loadMysqlLogWindowLines($idMysqlServer, $logType, $scope, $key, $page, 100));
    }

    /**
     * @param array<int,string> $logTypes
     * @return array{0: array<string,int>, 1: array<string,array<string,mixed>>, 2: array<string,array<int,array<string,mixed>>>, 3: array<string,string>}
     */
    private function loadMysqlLogsFromFilesystem(int $idMysqlServer, array $logTypes, int $currentPage = 1, int $pageSize = 100): array
    {
        $counts = [];
        $summaryByType = [];
        $linesByType = [];
        $sourcesByType = [];

        foreach ($logTypes as $logType) {
            $counts[$logType] = 0;
            $summaryByType[$logType] = [
                'total_rows' => 0,
                'min_event_time' => null,
                'max_event_time' => null,
            ];
            $linesByType[$logType] = [];
            $sourcesByType[$logType] = '';

            if ($logType === MysqlLogCollector::LOG_TYPE_OOM) {
                continue;
            }

            $baseDir = DATA . 'logs/' . $idMysqlServer . '/' . MysqlLogCollector::getStorageDirectoryName($logType);
            $partFiles = glob($baseDir . '/*/*.part.*') ?: [];
            sort($partFiles, SORT_STRING);

            $latestLines = [];

            foreach ($partFiles as $partFile) {
                [, $meta] = $this->getMysqlLogPartMeta($partFile);
                $sourceName = (string)($meta['source_name'] ?? '');
                $events = $this->loadParsedMysqlLogPartEvents($partFile, $idMysqlServer, $logType);

                if ($sourcesByType[$logType] === '' && $sourceName !== '') {
                    $sourcesByType[$logType] = $sourceName;
                }

                foreach ($events as $event) {
                    $counts[$logType]++;

                    $eventTime = (string)($event['event_time'] ?? '');
                    if ($eventTime !== '') {
                        if (empty($summaryByType[$logType]['min_event_time']) || $eventTime < $summaryByType[$logType]['min_event_time']) {
                            $summaryByType[$logType]['min_event_time'] = $eventTime;
                        }
                        if (empty($summaryByType[$logType]['max_event_time']) || $eventTime > $summaryByType[$logType]['max_event_time']) {
                            $summaryByType[$logType]['max_event_time'] = $eventTime;
                        }
                    }

                    $latestLines[] = [
                        'event_time' => $event['event_time'],
                        'source_kind' => $event['source_kind'],
                        'log_path' => $event['log_path'],
                        'user_name' => $event['user_name'],
                        'host_name' => $event['host_name'],
                        'db_name' => '',
                        'process_name' => $event['process_name'],
                        'level' => $event['level'],
                        'error_code' => $event['error_code'],
                        'message' => $event['message'],
                        'raw_line' => $event['raw_line'],
                    ];

                    if (!empty($event['meta_json'])) {
                        $eventMeta = json_decode((string)$event['meta_json'], true);
                        if (is_array($eventMeta) && isset($eventMeta['db_name'])) {
                            $latestLines[count($latestLines) - 1]['db_name'] = (string)$eventMeta['db_name'];
                        }
                    }
                }
            }

            $summaryByType[$logType]['total_rows'] = $counts[$logType];

            if (!empty($latestLines)) {
                usort($latestLines, static function (array $a, array $b): int {
                    return strcmp((string)($b['event_time'] ?? ''), (string)($a['event_time'] ?? ''));
                });
                $offset = max(0, ($currentPage - 1) * $pageSize);
                $linesByType[$logType] = array_slice($latestLines, $offset, $pageSize);
            }
        }

        return [$counts, $summaryByType, $linesByType, $sourcesByType];
    }

    /**
     * @return array<string,mixed>
     */
    private function buildMysqlLogsChartPayload(int $idMysqlServer, string $logType): array
    {
        $payload = [
            'current_type' => $logType,
            'id_mysql_server' => $idMysqlServer,
            'data_url_base' => LINK . 'MysqlServer/logsChartData/' . $idMysqlServer . '/' . $logType . '/',
            'lines_url_base' => LINK . 'MysqlServer/logsLinesData/' . $idMysqlServer . '/' . $logType . '/',
            'day' => [
                'labels' => [],
                'datasets' => [
                    'ERROR' => [],
                    'WARNING' => [],
                    'NOTE' => [],
                ],
            ],
        ];

        if ($logType === MysqlLogCollector::LOG_TYPE_OOM) {
            return $payload;
        }

        $baseDir = DATA . 'logs/' . $idMysqlServer . '/' . MysqlLogCollector::getStorageDirectoryName($logType);

        $today = new \DateTimeImmutable('today');
        $dayStart = $today->sub(new \DateInterval('P29D'));
        $dayBuckets = [];

        for ($i = 0; $i < 30; $i++) {
            $dayKey = $dayStart->modify('+' . $i . ' day')->format('Y-m-d');
            $dayBuckets[$dayKey] = ['ERROR' => 0, 'WARNING' => 0, 'NOTE' => 0];
        }

        foreach (array_keys($dayBuckets) as $dayKey) {
            $dayDir = $baseDir . '/' . $dayKey;
            $dayCache = $this->readMysqlLogsChartCache($dayDir . '/chart.day.json');
            if (!empty($dayCache['counts']) && is_array($dayCache['counts'])) {
                foreach (['ERROR', 'WARNING', 'NOTE'] as $levelName) {
                    $dayBuckets[$dayKey][$levelName] = (int)($dayCache['counts'][$levelName] ?? 0);
                }
            }
        }

        foreach ($dayBuckets as $dayKey => $levels) {
            $payload['day']['labels'][] = $dayKey;
            $payload['day']['datasets']['ERROR'][] = $levels['ERROR'];
            $payload['day']['datasets']['WARNING'][] = $levels['WARNING'];
            $payload['day']['datasets']['NOTE'][] = $levels['NOTE'];
        }

        return $payload;
    }

    /**
     * @param array<int,string> $logTypes
     * @return array{0: array<string,int>, 1: array<string,array<string,mixed>>, 2: array<string,string>}
     */
    private function loadMysqlLogsCacheSummary(int $idMysqlServer, array $logTypes): array
    {
        $counts = [];
        $summaryByType = [];
        $sourcesByType = [];

        foreach ($logTypes as $logType) {
            $storageName = MysqlLogCollector::getStorageDirectoryName($logType);
            $baseDir = DATA . 'logs/' . $idMysqlServer . '/' . $storageName;
            $dayDirs = glob($baseDir . '/*', GLOB_ONLYDIR) ?: [];
            sort($dayDirs, SORT_STRING);

            $totalRows = 0;
            $minEventTime = null;
            $maxEventTime = null;

            foreach ($dayDirs as $dayDir) {
                $dayCache = $this->readMysqlLogsChartCache($dayDir . '/chart.day.json');
                if (empty($dayCache['counts']) || !is_array($dayCache['counts'])) {
                    continue;
                }

                $dayTotal = 0;
                foreach (['ERROR', 'WARNING', 'NOTE'] as $levelName) {
                    $dayTotal += (int)($dayCache['counts'][$levelName] ?? 0);
                }

                if ($dayTotal === 0) {
                    continue;
                }

                $totalRows += $dayTotal;
                $dayKey = basename($dayDir);
                $dayStart = $dayKey . ' 00:00:00';
                $dayEnd = $dayKey . ' 23:59:59';

                if ($minEventTime === null || strcmp($dayStart, $minEventTime) < 0) {
                    $minEventTime = $dayStart;
                }
                if ($maxEventTime === null || strcmp($dayEnd, $maxEventTime) > 0) {
                    $maxEventTime = $dayEnd;
                }
            }

            $counts[$logType] = $totalRows;
            $summaryByType[$logType] = [
                'total_rows' => $totalRows,
                'min_event_time' => $minEventTime,
                'max_event_time' => $maxEventTime,
            ];

            $sourcesByType[$logType] = $this->findMysqlLogsRemoteSourceName($baseDir);
        }

        return [$counts, $summaryByType, $sourcesByType];
    }

    private function findMysqlLogsRemoteSourceName(string $baseDir): string
    {
        $dayDirs = glob($baseDir . '/*', GLOB_ONLYDIR) ?: [];
        rsort($dayDirs, SORT_STRING);

        foreach ($dayDirs as $dayDir) {
            $metaFiles = glob($dayDir . '/.*.meta.json') ?: [];
            rsort($metaFiles, SORT_STRING);

            foreach ($metaFiles as $metaFile) {
                $meta = json_decode((string)file_get_contents($metaFile), true);
                if (!is_array($meta)) {
                    continue;
                }

                $sourceName = trim((string)($meta['source_name'] ?? ''));
                if ($sourceName !== '') {
                    return $sourceName;
                }
            }
        }

        return '';
    }

    /**
     * @return array<string,mixed>
     */
    private function loadMysqlLogWindowLines(int $idMysqlServer, string $logType, string $scope, string $key, int $page, int $pageSize): array
    {
        $result = [
            'scope' => $scope,
            'key' => $key,
            'page' => $page,
            'page_size' => $pageSize,
            'total_rows' => 0,
            'total_pages' => 1,
            'lines' => [],
        ];

        if ($logType === MysqlLogCollector::LOG_TYPE_OOM) {
            return $result;
        }

        $baseDir = DATA . 'logs/' . $idMysqlServer . '/' . MysqlLogCollector::getStorageDirectoryName($logType);
        $partFiles = $this->getMysqlLogWindowPartFiles($baseDir, $scope, $key);
        if (empty($partFiles)) {
            return $result;
        }

        $window = $this->getMysqlLogWindowBounds($scope, $key);
        $lineRange = $this->getMysqlLogLineRange($baseDir, $scope, $key);
        $latestLines = [];
        $lineIndex = -1;

        foreach ($partFiles as $partFile) {
            $events = $this->loadParsedMysqlLogPartEvents($partFile, $idMysqlServer, $logType);

            foreach ($events as $event) {
                $lineIndex++;
                if ($lineRange['start'] !== null && $lineIndex < $lineRange['start']) {
                    continue;
                }
                if ($lineRange['end'] !== null && $lineIndex > $lineRange['end']) {
                    break 2;
                }

                $eventTime = (string)($event['event_time'] ?? '');
                if ($eventTime === '') {
                    continue;
                }

                $eventTs = strtotime($eventTime);
                if ($eventTs === false) {
                    continue;
                }

                if ($window['start'] !== null && $eventTs < $window['start']) {
                    continue;
                }
                if ($window['end'] !== null && $eventTs > $window['end']) {
                    continue;
                }

                $row = [
                    'event_time' => $event['event_time'],
                    'source_kind' => $event['source_kind'],
                    'log_path' => $event['log_path'],
                    'user_name' => $event['user_name'],
                    'host_name' => $event['host_name'],
                    'db_name' => '',
                    'process_name' => $event['process_name'],
                    'level' => $event['level'],
                    'error_code' => $event['error_code'],
                    'message' => $event['message'],
                    'raw_line' => $event['raw_line'],
                ];

                if (!empty($event['meta_json'])) {
                    $eventMeta = json_decode((string)$event['meta_json'], true);
                    if (is_array($eventMeta) && isset($eventMeta['db_name'])) {
                        $row['db_name'] = (string)$eventMeta['db_name'];
                    }
                }

                $latestLines[] = $row;
            }
        }

        usort($latestLines, static function (array $a, array $b): int {
            return strcmp((string)($b['event_time'] ?? ''), (string)($a['event_time'] ?? ''));
        });

        $result['total_rows'] = count($latestLines);
        $result['total_pages'] = max(1, (int)ceil($result['total_rows'] / $pageSize));
        $result['page'] = min($page, $result['total_pages']);
        $offset = max(0, ($result['page'] - 1) * $pageSize);
        $result['lines'] = array_slice($latestLines, $offset, $pageSize);

        return $result;
    }

    /**
     * @return array{start: int|null, end: int|null}
     */
    private function getMysqlLogLineRange(string $baseDir, string $scope, string $key): array
    {
        if ($scope === 'hour' && preg_match('/^\d{4}-\d{2}-\d{2}_\d{2}$/', $key)) {
            [$dayKey, $hourKey] = explode('_', $key, 2);
            $hourCache = $this->readMysqlLogsChartCache($baseDir . '/' . $dayKey . '/chart.hour.json');
            $hourLabel = $hourKey . ':00';
            if (!empty($hourCache['hours'][$hourLabel]['line_start']) || !empty($hourCache['hours'][$hourLabel]['line_end'])) {
                return [
                    'start' => isset($hourCache['hours'][$hourLabel]['line_start']) ? (int)$hourCache['hours'][$hourLabel]['line_start'] : null,
                    'end' => isset($hourCache['hours'][$hourLabel]['line_end']) ? (int)$hourCache['hours'][$hourLabel]['line_end'] : null,
                ];
            }
        }

        if ($scope === 'minute' && preg_match('/^\d{4}-\d{2}-\d{2}_\d{2}_\d{2}$/', $key)) {
            [$dayKey, $hourKey, $minuteKey] = explode('_', $key, 3);
            $minuteCache = $this->readMysqlLogsChartCache($baseDir . '/' . $dayKey . '/chart.minute.json');
            $hourLabel = $hourKey . ':00';
            if (!empty($minuteCache['hours'][$hourLabel]['line_ranges'][$minuteKey])) {
                $range = $minuteCache['hours'][$hourLabel]['line_ranges'][$minuteKey];
                return [
                    'start' => isset($range['line_start']) ? (int)$range['line_start'] : null,
                    'end' => isset($range['line_end']) ? (int)$range['line_end'] : null,
                ];
            }
        }

        return ['start' => null, 'end' => null];
    }

    /**
     * @return array{start: int|null, end: int|null}
     */
    private function getMysqlLogWindowBounds(string $scope, string $key): array
    {
        if ($scope === 'month') {
            $today = new \DateTimeImmutable('today');
            return [
                'start' => $today->sub(new \DateInterval('P29D'))->setTime(0, 0, 0)->getTimestamp(),
                'end' => $today->setTime(23, 59, 59)->getTimestamp(),
            ];
        }

        if ($scope === 'day' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $key)) {
            return [
                'start' => (new \DateTimeImmutable($key . ' 00:00:00'))->getTimestamp(),
                'end' => (new \DateTimeImmutable($key . ' 23:59:59'))->getTimestamp(),
            ];
        }

        if ($scope === 'hour' && preg_match('/^\d{4}-\d{2}-\d{2}_\d{2}$/', $key)) {
            [$dayKey, $hourKey] = explode('_', $key, 2);
            return [
                'start' => (new \DateTimeImmutable($dayKey . ' ' . $hourKey . ':00:00'))->getTimestamp(),
                'end' => (new \DateTimeImmutable($dayKey . ' ' . $hourKey . ':59:59'))->getTimestamp(),
            ];
        }

        if ($scope === 'minute' && preg_match('/^\d{4}-\d{2}-\d{2}_\d{2}_\d{2}$/', $key)) {
            [$dayKey, $hourKey, $minuteKey] = explode('_', $key, 3);
            return [
                'start' => (new \DateTimeImmutable($dayKey . ' ' . $hourKey . ':' . $minuteKey . ':00'))->getTimestamp(),
                'end' => (new \DateTimeImmutable($dayKey . ' ' . $hourKey . ':' . $minuteKey . ':59'))->getTimestamp(),
            ];
        }

        return ['start' => null, 'end' => null];
    }

    /**
     * @return array<int,string>
     */
    private function getMysqlLogWindowPartFiles(string $baseDir, string $scope, string $key): array
    {
        if ($scope === 'month') {
            $today = new \DateTimeImmutable('today');
            $partFiles = [];
            for ($i = 0; $i < 30; $i++) {
                $dayKey = $today->sub(new \DateInterval('P' . $i . 'D'))->format('Y-m-d');
                $partFiles = array_merge($partFiles, glob($baseDir . '/' . $dayKey . '/*.part.*') ?: []);
            }
            sort($partFiles, SORT_STRING);
            return $partFiles;
        }

        if ($scope === 'day' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $key)) {
            $partFiles = glob($baseDir . '/' . $key . '/*.part.*') ?: [];
            sort($partFiles, SORT_STRING);
            return $partFiles;
        }

        if ($scope === 'hour' && preg_match('/^\d{4}-\d{2}-\d{2}_\d{2}$/', $key)) {
            [$dayKey] = explode('_', $key, 2);
            $partFiles = glob($baseDir . '/' . $dayKey . '/*.part.*') ?: [];
            sort($partFiles, SORT_STRING);
            return $partFiles;
        }

        if ($scope === 'minute' && preg_match('/^\d{4}-\d{2}-\d{2}_\d{2}_\d{2}$/', $key)) {
            [$dayKey] = explode('_', $key, 2);
            $partFiles = glob($baseDir . '/' . $dayKey . '/*.part.*') ?: [];
            sort($partFiles, SORT_STRING);
            return $partFiles;
        }

        return [];
    }

    private function ensureMysqlLogsChartCaches(int $idMysqlServer, string $logType): void
    {
        if ($logType === MysqlLogCollector::LOG_TYPE_OOM) {
            return;
        }

        $baseDir = DATA . 'logs/' . $idMysqlServer . '/' . MysqlLogCollector::getStorageDirectoryName($logType);
        $dayDirs = glob($baseDir . '/*', GLOB_ONLYDIR) ?: [];
        rsort($dayDirs, SORT_STRING);

        foreach ($dayDirs as $index => $dayDir) {
            if ($index > 0 && $this->hasFreshMysqlLogsChartCachesForDay($dayDir)) {
                continue;
            }
            $this->buildMysqlLogsChartCachesForDay($idMysqlServer, $logType, $dayDir);
        }
    }

    private function buildMysqlLogsChartCachesForDay(int $idMysqlServer, string $logType, string $dayDir): void
    {
        $partFiles = glob($dayDir . '/*.part.*') ?: [];
        if (empty($partFiles)) {
            return;
        }

        $cacheFiles = [
            $dayDir . '/chart.day.json',
            $dayDir . '/chart.hour.json',
            $dayDir . '/chart.minute.json',
        ];

        $latestSourceMtime = 0;
        foreach ($partFiles as $partFile) {
            $latestSourceMtime = max($latestSourceMtime, (int)@filemtime($partFile));
            $metaPath = dirname($partFile) . '/.' . basename($partFile) . '.meta.json';
            if (file_exists($metaPath)) {
                $latestSourceMtime = max($latestSourceMtime, (int)@filemtime($metaPath));
            }
            $parsedPath = dirname($partFile) . '/.' . basename($partFile) . '.parsed.json';
            if (file_exists($parsedPath)) {
                $latestSourceMtime = max($latestSourceMtime, (int)@filemtime($parsedPath));
            }
        }

        $cacheIsFresh = true;
        foreach ($cacheFiles as $cacheFile) {
            if (!file_exists($cacheFile) || (int)@filemtime($cacheFile) < $latestSourceMtime) {
                $cacheIsFresh = false;
                break;
            }
        }

        if ($cacheIsFresh) {
            return;
        }

        $dayKey = basename($dayDir);
        $dayCounts = ['ERROR' => 0, 'WARNING' => 0, 'NOTE' => 0];
        $hourCounts = [];
        $minuteCounts = [];

        for ($h = 0; $h < 24; $h++) {
            $hourLabel = sprintf('%02d:00', $h);
            $hourCounts[$hourLabel] = ['ERROR' => 0, 'WARNING' => 0, 'NOTE' => 0, 'line_start' => null, 'line_end' => null];
            $minuteCounts[$hourLabel] = [
                'labels' => [],
                'datasets' => [
                    'ERROR' => [],
                    'WARNING' => [],
                    'NOTE' => [],
                ],
                'line_ranges' => [],
            ];

            for ($m = 0; $m < 60; $m++) {
                $minuteLabel = sprintf('%02d', $m);
                $minuteCounts[$hourLabel]['labels'][] = $minuteLabel;
                $minuteCounts[$hourLabel]['datasets']['ERROR'][] = 0;
                $minuteCounts[$hourLabel]['datasets']['WARNING'][] = 0;
                $minuteCounts[$hourLabel]['datasets']['NOTE'][] = 0;
                $minuteCounts[$hourLabel]['line_ranges'][$minuteLabel] = ['line_start' => null, 'line_end' => null];
            }
        }

        sort($partFiles, SORT_STRING);
        $lineIndex = -1;
        foreach ($partFiles as $partFile) {
            $events = $this->loadParsedMysqlLogPartEvents($partFile, $idMysqlServer, $logType);

            foreach ($events as $event) {
                $lineIndex++;
                $eventTime = (string)($event['event_time'] ?? '');
                if ($eventTime === '') {
                    continue;
                }

                $timestamp = strtotime($eventTime);
                if ($timestamp === false || date('Y-m-d', $timestamp) !== $dayKey) {
                    continue;
                }

                $levelBucket = $this->normalizeMysqlLogLevelBucket((string)($event['level'] ?? ''));
                $dayCounts[$levelBucket]++;

                $hourLabel = date('H:00', $timestamp);
                $minuteLabel = date('i', $timestamp);

                $hourCounts[$hourLabel][$levelBucket]++;
                if ($hourCounts[$hourLabel]['line_start'] === null) {
                    $hourCounts[$hourLabel]['line_start'] = $lineIndex;
                }
                $hourCounts[$hourLabel]['line_end'] = $lineIndex;

                $minuteIndex = (int)$minuteLabel;
                $minuteCounts[$hourLabel]['datasets'][$levelBucket][$minuteIndex]++;
                if ($minuteCounts[$hourLabel]['line_ranges'][$minuteLabel]['line_start'] === null) {
                    $minuteCounts[$hourLabel]['line_ranges'][$minuteLabel]['line_start'] = $lineIndex;
                }
                $minuteCounts[$hourLabel]['line_ranges'][$minuteLabel]['line_end'] = $lineIndex;
            }
        }

        file_put_contents($dayDir . '/chart.day.json', json_encode([
            'date' => $dayKey,
            'counts' => $dayCounts,
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        file_put_contents($dayDir . '/chart.hour.json', json_encode([
            'date' => $dayKey,
            'hours' => $hourCounts,
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        file_put_contents($dayDir . '/chart.minute.json', json_encode([
            'date' => $dayKey,
            'hours' => $minuteCounts,
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }

    private function hasFreshMysqlLogsChartCachesForDay(string $dayDir): bool
    {
        $cacheFiles = [
            $dayDir . '/chart.day.json',
            $dayDir . '/chart.hour.json',
            $dayDir . '/chart.minute.json',
        ];

        foreach ($cacheFiles as $cacheFile) {
            if (!file_exists($cacheFile)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string,mixed>
     */
    private function readMysqlLogsChartCache(string $path): array
    {
        if (!file_exists($path)) {
            return [];
        }

        $decoded = json_decode((string)file_get_contents($path), true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @return array{0: string, 1: array<string,mixed>}
     */
    private function getMysqlLogPartMeta(string $partFile): array
    {
        $metaPath = dirname($partFile) . '/.' . basename($partFile) . '.meta.json';
        $meta = file_exists($metaPath) ? json_decode((string)file_get_contents($metaPath), true) : [];
        if (!is_array($meta)) {
            $meta = [];
        }

        return [$metaPath, $meta];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function loadParsedMysqlLogPartEvents(string $partFile, int $idMysqlServer, string $logType): array
    {
        [$metaPath, $meta] = $this->getMysqlLogPartMeta($partFile);
        $parsedPath = dirname($partFile) . '/.' . basename($partFile) . '.parsed.json';
        $sourceMtime = max(
            (int)@filemtime($partFile),
            file_exists($metaPath) ? (int)@filemtime($metaPath) : 0
        );

        if (file_exists($parsedPath) && (int)@filemtime($parsedPath) >= $sourceMtime) {
            $decoded = json_decode((string)file_get_contents($parsedPath), true);
            return is_array($decoded) ? $decoded : [];
        }

        $sourceName = (string)($meta['source_name'] ?? '');
        $inode = (int)($meta['inode'] ?? 0);
        $offsetStart = (int)($meta['offset_start'] ?? 0);
        $content = (string)file_get_contents($partFile);

        $events = MysqlLogCollector::buildFileEvents(
            $idMysqlServer,
            $logType,
            $sourceName,
            $inode,
            $offsetStart,
            $content
        );

        file_put_contents($parsedPath, json_encode($events, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        return $events;
    }

    private function normalizeMysqlLogLevelBucket(string $level): string
    {
        $level = strtoupper(trim($level));

        if ($level === 'WARNING' || $level === 'WARN') {
            return 'WARNING';
        }

        if ($level === 'NOTE') {
            return 'NOTE';
        }

        return 'ERROR';
    }

/**
 * Retrieve mysql server state through `getProcesslistClass`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $time Input value for `time`.
 * @phpstan-param mixed $time
 * @psalm-param mixed $time
 * @return string Returned value for getProcesslistClass.
 * @phpstan-return string
 * @psalm-return string
 * @see self::getProcesslistClass()
 * @example /fr/mysqlserver/getProcesslistClass
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function getProcesslistClass($time): string
    {
        $time = (int) $time;

        if ($time > 600) {
            return 'danger';
        }
        if ($time > 60) {
            return 'warning';
        }
        if ($time > 10) {
            return 'primary';
        }
        if ($time > 1) {
            return 'info';
        }

        return '';
    }

/**
 * Handle mysql server state through `parseIdList`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $raw Input value for `raw`.
 * @phpstan-param mixed $raw
 * @psalm-param mixed $raw
 * @return array Returned value for parseIdList.
 * @phpstan-return array
 * @psalm-return array
 * @see self::parseIdList()
 * @example /fr/mysqlserver/parseIdList
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function parseIdList($raw): array
    {
        if (is_array($raw)) {
            $raw = implode(',', $raw);
        }

        $raw = (string) $raw;
        if ($raw === '') {
            return [];
        }

        $ids = [];
        foreach (preg_split('/[\s,;]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) as $token) {
            if (!ctype_digit($token)) {
                continue;
            }

            $id = (int) $token;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }

/**
 * Handle mysql server state through `hasMetadataLockInfoPlugin`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $db Input value for `db`.
 * @phpstan-param mixed $db
 * @psalm-param mixed $db
 * @return bool Returned value for hasMetadataLockInfoPlugin.
 * @phpstan-return bool
 * @psalm-return bool
 * @see self::hasMetadataLockInfoPlugin()
 * @example /fr/mysqlserver/hasMetadataLockInfoPlugin
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function hasMetadataLockInfoPlugin($db): bool
    {
        $sql = "SELECT 1
            FROM information_schema.plugins
            WHERE PLUGIN_NAME = 'METADATA_LOCK_INFO'
              AND PLUGIN_STATUS = 'ACTIVE'
            LIMIT 1";

        $res = Mysql::sqlQuerySilentCompat($db, $sql);
        if (!$res) {
            return false;
        }

        $row = $db->sql_fetch_array($res, MYSQLI_NUM);
        return !empty($row);
    }

/**
 * Retrieve mysql server state through `fetchMetadataLockInfo`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $db Input value for `db`.
 * @phpstan-param mixed $db
 * @psalm-param mixed $db
 * @param int $id_mysql_server Input value for `id_mysql_server`.
 * @phpstan-param int $id_mysql_server
 * @psalm-param int $id_mysql_server
 * @return array Returned value for fetchMetadataLockInfo.
 * @phpstan-return array
 * @psalm-return array
 * @see self::fetchMetadataLockInfo()
 * @example /fr/mysqlserver/fetchMetadataLockInfo
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function fetchMetadataLockInfo($db, int $id_mysql_server): array
    {
        $sql = "SELECT *
            FROM information_schema.METADATA_LOCK_INFO";

        $res = Mysql::sqlQuerySilentCompat($db, $sql);
        if (!$res) {
            return [];
        }

        $rows = [];
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $row = array_change_key_case($arr, CASE_LOWER);
            $row['id_mysql_server'] = $id_mysql_server;
            $rows[] = $row;
        }

        return $rows;
    }


/**
 * Render mysql server state through `main`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for main.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @see self::main()
 * @example /fr/mysqlserver/main
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function main($param)
    {
        Debug::parseDebug($param);

        if (empty($param[0]) || !ctype_digit((string)$param[0])) {
            throw new \Exception("Usage: /serverdashboard/main/{id_mysql_server}");
        }

        $id_mysql_server = (int)$param[0];
        $_GET['mysql_server']['id'] = $id_mysql_server;

        $keys = [
            "version","version_comment","uptime",
            "threads_connected","threads_running","max_used_connections","max_connections",
            "questions","queries","bytes_sent","bytes_received",
            "aborted_clients","aborted_connects","access_denied_errors",
            "innodb_flush_log_at_trx_commit","innodb_log_file_size","innodb_log_buffer_size","innodb_buffer_pool_instances",
            "innodb_page_size","innodb_read_io_threads","innodb_write_io_threads",
            "log_bin","sync_binlog","binlog_format","binlog_row_image","binlog_checksum","binlog_cache_size","binlog_stmt_cache_size","max_binlog_size","binlog_space_limit","binlog_nb_files","binlog_total_size","binlog_expire_logs_seconds","expire_logs_days",
            "log_bin_basename","mysql_binlog::binlog_file_last","gtid_current_pos","gtid_binlog_pos",
            "wsrep_on","wsrep_connected","wsrep_cluster_name","wsrep_cluster_status","wsrep_cluster_size","wsrep_cluster_state_uuid",
            "wsrep_local_state","wsrep_local_state_comment","wsrep_local_state_uuid","wsrep_ready","wsrep_desync","wsrep_sst_method",
            "wsrep_provider_version","wsrep_provider_options","wsrep_incoming_addresses","wsrep_node_address","wsrep_gcomm_uuid",
            "wsrep_flow_control_paused","wsrep_local_recv_queue","wsrep_local_send_queue","wsrep_cert_deps_distance",
            "wsrep_apply_window","wsrep_commit_window","wsrep_cert_interval","wsrep_last_committed","wsrep_repl_data_bytes",
            "wsrep_repl_keys","wsrep_repl_keys_bytes",
            "wsrep_cluster_address","wsrep_node_name","wsrep_node_incoming_address","wsrep_provider","wsrep_notify_cmd",
            "wsrep_osu_method","wsrep_sync_wait","wsrep_slave_threads","wsrep_sst_auth","wsrep_sst_donor","wsrep_sst_donor_rejects_queries",
            "wsrep_reject_queries","wsrep_auto_increment_control","wsrep_gtid_mode","wsrep_gtid_domain_id","wsrep_forced_binlog_format",
            "wsrep_log_conflicts","wsrep_max_ws_rows","wsrep_max_ws_size","wsrep_retry_autocommit","wsrep_restart_slave",
            "wsrep_recover","wsrep_start_position","wsrep_data_home_dir","wsrep_convert_lock_to_trx","wsrep_causal_reads",
            "wsrep_dirty_reads","wsrep_drupal_282555_workaround","wsrep_mysql_replication_bundle","wsrep_slave_fk_checks",
            "wsrep_slave_uk_checks","wsrep_replicate_myisam","wsrep_patch_version","wsrep_dbug_option",
            "ssl_version","ssl_cipher","ssl_server_not_before","ssl_server_not_after",
            "Ssl_version","Ssl_cipher","Ssl_server_not_before","Ssl_server_not_after",
            "hostname","os","distributor","kernel","arch","cpu_thread_count","cpu_usage","cpu_detail","memory_total","memory_used","swap_total","swap_used",
            "buffer_pool_size","buffer_pool_bytes_data","buffer_pool_pages_total","buffer_pool_pages_free","buffer_pool_pages_dirty","buffer_pool_read_requests","buffer_pool_reads",
            "aria_pagecache_buffer_size","aria_log_file_size","aria_block_size","aria_used_for_temp_tables","aria_encrypt_tables","aria_recover","aria_page_checksum",
            "aria_pagecache_blocks_used","aria_pagecache_blocks_unused","aria_pagecache_blocks_not_flushed","aria_pagecache_reads","aria_pagecache_read_requests",
            "have_rocksdb","default_storage_engine","rocksdb_block_cache_size","rocksdb_db_write_buffer_size","rocksdb_max_total_wal_size",
            "rocksdb_wal_size_limit_mb","rocksdb_write_disable_wal","rocksdb_flush_log_at_trx_commit","rocksdb_info_log_level",
            "rocksdb_enable_ttl","rocksdb_cache_index_and_filter_blocks","rocksdb_use_direct_reads","rocksdb_use_direct_io_for_flush_and_compaction",
            "have_myisam","key_buffer_size","key_blocks_used","key_blocks_unused","key_read_requests","key_reads","key_write_requests","key_writes",
            "myisam_sort_buffer_size","myisam_recover_options","delay_key_write",
            "have_columnstore","columnstore_version","columnstore_select_handler","columnstore_cache_inserts",
            "columnstore_use_import_for_batchinsert","columnstore_diskjoin_smallsidelimit","columnstore_ordered_only",
            "columnstore_um_mem_limit","columnstore_pm_mem_limit","columnstore_num_compress_threads",
            "spider_use_handler","spider_connect_timeout","spider_connect_retry_count","spider_connect_retry_interval",
            "spider_connect_error_interval","spider_net_read_timeout","spider_net_write_timeout","spider_quick_mode","spider_split_read",
            "spider_semi_split_read","spider_semi_split_read_limit","spider_support_xa","spider_internal_xa","spider_sync_autocommit",
            "spider_sync_trx_isolation","spider_sync_time_zone","spider_remote_trx_isolation","spider_remote_autocommit","spider_general_log",
            "information_schema::engines",
            "mysql_server::mysql_available","is_vip",
            "vip::ip",
            "vip::port",
            "vip::destination_id",
            "vip::destination_date",
            "vip::destination_previous_id",
            "vip::destination_previous_date",
            "ssh_stats::disks","ssh_hardware::ips","processlist",
            "ssh_stats::mysql_datadir_path",
            "ssh_stats::mysql_datadir_total_size",
            "ssh_stats::mysql_datadir_clean_size",
            "ssh_stats::mysql_sst_elapsed_sec",
            "ssh_stats::mysql_sst_in_progress",
        ];

        $raw = Extraction2::display($keys, [$id_mysql_server]);
        Debug::debug($raw);

        // Après certaines opérations (ex: control/rebuildAll), Extraction2 peut
        // temporairement retourner false/structure vide avant la reconstruction
        // complète des données. On protège donc le flux pour éviter un fatal.
        $row = [];
        if (is_array($raw) && !empty($raw)) {
            $tmp = reset($raw);
            if (is_array($tmp)) {
                $row = $tmp;
            }
        }

        // Helpers simples
        $g = function($k) use ($row) { return $row[$k] ?? null; };
        $metric = function($k) use ($row) {
            return isset($row[$k]['count']) ? $row[$k]['count'] : null;
        };
        $value = function($k) use ($row) {
            if (!array_key_exists($k, $row)) {
                return null;
            }
            $v = $row[$k];
            if (is_array($v) && array_key_exists('count', $v)) {
                return $v['count'];
            }
            return $v;
        };

        // Dérivés
        $uptime_h = self::secToHuman((int)$g('uptime'));

        $conn_usage = null;
        if ($g('max_connections') > 0) {
            $conn_usage = round(100 * $g('threads_connected') / $g('max_connections'), 1);
        }

        $bp_size  = $metric('buffer_pool_size');
        $bp_data  = $metric('buffer_pool_bytes_data');
        $bp_total = $metric('buffer_pool_pages_total');
        $bp_free  = $metric('buffer_pool_pages_free');
        $bp_dirty = $metric('buffer_pool_pages_dirty');

        if ($bp_size > 0 && $bp_data >= 0) {
            $bp_used_pct = round(100 * $bp_data / $bp_size, 1);
        } elseif ($bp_total > 0) {
            $bp_used_pct = round(100 * (1 - ($bp_free / $bp_total)), 1);
        } else {
            $bp_used_pct = null;
        }

        $hit = null;
        $req = $metric('buffer_pool_read_requests');
        $reads = $metric('buffer_pool_reads');
        if ($req > 0) {
            $hit = round(100 * (1 - $reads / $req), 2);
        }

        $bp_free_pct = null;
        if ($bp_total > 0 && is_numeric($bp_free)) {
            $bp_free_pct = round(100 * ((float)$bp_free / (float)$bp_total), 2);
        }

        $bp_dirty_pct = null;
        if ($bp_total > 0 && is_numeric($bp_dirty)) {
            $bp_dirty_pct = round(100 * ((float)$bp_dirty / (float)$bp_total), 2);
        }

        $bp_miss_pct = null;
        if ($req > 0 && is_numeric($reads)) {
            $bp_miss_pct = round(100 * ((float)$reads / (float)$req), 4);
        }

        $ariaBlocksUsed = $metric('aria_pagecache_blocks_used');
        $ariaBlocksUnused = $metric('aria_pagecache_blocks_unused');
        $ariaBlocksNotFlushed = $metric('aria_pagecache_blocks_not_flushed');
        $ariaTotalBlocks = (is_numeric($ariaBlocksUsed) ? (float)$ariaBlocksUsed : 0)
            + (is_numeric($ariaBlocksUnused) ? (float)$ariaBlocksUnused : 0);

        $ariaCacheUsedPct = null;
        if ($ariaTotalBlocks > 0) {
            $ariaCacheUsedPct = round(100 * ((float)$ariaBlocksUsed / $ariaTotalBlocks), 2);
        }

        $ariaDirtyPct = null;
        if (is_numeric($ariaBlocksUsed) && (float)$ariaBlocksUsed > 0 && is_numeric($ariaBlocksNotFlushed)) {
            $ariaDirtyPct = round(100 * ((float)$ariaBlocksNotFlushed / (float)$ariaBlocksUsed), 2);
        }

        $ariaHit = null;
        $ariaReq = $metric('aria_pagecache_read_requests');
        $ariaReads = $metric('aria_pagecache_reads');
        if (is_numeric($ariaReq) && (float)$ariaReq > 0 && is_numeric($ariaReads)) {
            $ariaHit = round(100 * (1 - ((float)$ariaReads / (float)$ariaReq)), 2);
        }

        $supportedEngines = self::extractSupportedEngines($g('engines'));
        $hasEnginesMetric = !empty($supportedEngines);
        $mysqlAvailableRaw = $g('mysql_available');
        $serverIsAvailable = in_array(strtoupper((string)$mysqlAvailableRaw), ['1', 'ON', 'YES', 'TRUE'], true);
        $canShowStorageEngines = $hasEnginesMetric && $serverIsAvailable;

        $innodbEnabled = $canShowStorageEngines && self::hasSupportedEngine($supportedEngines, 'INNODB');
        $ariaEnabled = $canShowStorageEngines && self::hasSupportedEngine($supportedEngines, 'ARIA');
        $rocksdbEnabled = $canShowStorageEngines && self::hasSupportedEngine($supportedEngines, 'ROCKSDB');
        $myisamEnabled = $canShowStorageEngines && self::hasSupportedEngine($supportedEngines, 'MYISAM');

        $keyBlocksUsed = $metric('key_blocks_used');
        $keyBlocksUnused = $metric('key_blocks_unused');
        $keyTotalBlocks = (is_numeric($keyBlocksUsed) ? (float)$keyBlocksUsed : 0)
            + (is_numeric($keyBlocksUnused) ? (float)$keyBlocksUnused : 0);

        $keyCacheUsedPct = null;
        if ($keyTotalBlocks > 0) {
            $keyCacheUsedPct = round(100 * ((float)$keyBlocksUsed / $keyTotalBlocks), 2);
        }

        $keyHit = null;
        $keyReq = $metric('key_read_requests');
        $keyReads = $metric('key_reads');
        if (is_numeric($keyReq) && (float)$keyReq > 0 && is_numeric($keyReads)) {
            $keyHit = round(100 * (1 - ((float)$keyReads / (float)$keyReq)), 2);
        }

        $keyMiss = null;
        if (is_numeric($keyReq) && (float)$keyReq > 0 && is_numeric($keyReads)) {
            $keyMiss = round(100 * ((float)$keyReads / (float)$keyReq), 4);
        }

        $columnstoreEnabled = $canShowStorageEngines && self::hasSupportedEngine($supportedEngines, 'COLUMNSTORE');
        $spiderEnabled = $canShowStorageEngines && self::hasSupportedEngine($supportedEngines, 'SPIDER');

        if ($canShowStorageEngines && !$spiderEnabled) {
            foreach ($row as $k => $v) {
                if (strpos((string)$k, 'spider_') !== 0) {
                    continue;
                }

                if (is_array($v) && array_key_exists('count', $v)) {
                    $v = $v['count'];
                }

                if ($v !== null && $v !== '') {
                    $spiderEnabled = true;
                    break;
                }
            }
        }

        $data = [];
        $data['id_mysql_server'] = $id_mysql_server;
        $data['mysql_available'] = $serverIsAvailable ? 1 : 0;
        $credentials = self::getMysqlServerCredentials($id_mysql_server);
        $data['is_proxy'] = !empty($credentials['is_proxy']) ? (int)$credentials['is_proxy'] : 0;
        $data['is_vip'] = !empty($credentials['is_vip']) ? (int)$credentials['is_vip'] : 0;

        $extractedIsVip = $value('is_vip');
        if ($data['is_vip'] === 0 && $extractedIsVip !== null && $extractedIsVip !== '') {
            $data['is_vip'] = (int)$extractedIsVip;
        }

        $vipIp = $value('vip::ip') ?? $value('ip');
        $vipPort = $value('vip::port') ?? $value('port');
        $vipDestinationId = $value('vip::destination_id') ?? $value('destination_id');
        $vipDestinationDate = $value('vip::destination_date') ?? $value('destination_date');
        $vipPreviousId = $value('vip::destination_previous_id') ?? $value('destination_previous_id');
        $vipPreviousDate = $value('vip::destination_previous_date') ?? $value('destination_previous_date');

        $isVipServer = $data['is_vip'] === 1
            || $vipIp !== null
            || $vipPort !== null
            || $vipDestinationId !== null
            || $vipDestinationDate !== null
            || $vipPreviousId !== null
            || $vipPreviousDate !== null;
        $vipUptime = $isVipServer ? self::formatVipUptime($vipDestinationDate) : null;

        $data['summary'] = [
            'Server' => Display::srv($id_mysql_server),
            'User' => $credentials['login'] ?? 'n/a',
            'Monitoring' => [
                'type' => 'action_button',
                'status' => !empty($credentials['is_monitored']) ? self::tr('Enabled') : self::tr('Disabled'),
                'url' => LINK.'MysqlServer/toggleMonitored/'.$id_mysql_server,
                'label' => !empty($credentials['is_monitored']) ? self::tr('Disable monitoring') : self::tr('Enable monitoring'),
                'class' => !empty($credentials['is_monitored']) ? 'btn btn-xs btn-danger' : 'btn btn-xs btn-success',
            ],
            'Password' => [
                'type' => 'copy_clipboard',
                'text' => $credentials['password'] ?? '',
                'icon' => '<i class="fa fa-files-o" aria-hidden="true"></i>',
            ],
            'Debug' => [
                'type' => 'copy_clipboard',
                'text' => self::getTryMysqlConnectionDebugCommand((int)$id_mysql_server),
                'icon' => '<i class="fa fa-files-o" aria-hidden="true"></i>',
            ],
            'Version' => $isVipServer ? 'VIP' : $g('version'),
            'Commentaire' => $isVipServer ? 'VIP' : $g('version_comment'),
            'Uptime' => $isVipServer ? ($vipUptime ?? 'n/a') : $uptime_h,
            'Cmd' => self::getAdminInformation([$id_mysql_server])
        ];

        if ($isVipServer) {
            $data['vip'] = [
                'IP' => self::formatJsonValue($vipIp),
                'Port' => self::formatJsonValue($vipPort),
                'Destination ID' => self::formatJsonValue($vipDestinationId),
                'Destination date' => self::formatJsonValue($vipDestinationDate),
                'Destination previous ID' => self::formatJsonValue($vipPreviousId),
                'Destination previous date' => self::formatJsonValue($vipPreviousDate),
            ];
        }

        $data['connections'] = [
            'Threads running' => self::formatThreadUsage($g('threads_running'), $g('max_connections')),
            'Threads connected' => self::formatThreadUsage($g('threads_connected'), $g('max_connections')),
            'Max used' => self::formatConnectionUsage($g('max_used_connections'), $g('max_connections')),
            'Aborted clients' => $g('aborted_clients'),
            'Aborted connects' => $g('aborted_connects'),
        ];

        if ($innodbEnabled) {
            $data['innodb'] = [
                '<img height="16px" width="16px" src="'.IMG.'icon/ram.svg" > Buffer pool size' => self::formatBytesToMbGbTb($bp_size),
                '<img height="16px" width="16px" src="'.IMG.'icon/bar-chart-svgrepo-com.svg" > Buffer pool used' => self::formatPercentUsage($bp_used_pct),
                '<img height="16px" width="16px" src="'.IMG.'icon/bar-chart-svgrepo-com.svg" > Buffer pool free' => self::formatPercentUsage($bp_free_pct),
                '<img height="16px" width="16px" src="'.IMG.'icon/bar-chart-svgrepo-com.svg" > Dirty pages' => self::formatPercentUsage($bp_dirty_pct),
                '<img height="16px" width="16px" src="'.IMG.'icon/bar-chart-svgrepo-com.svg" > BP hit ratio' => self::formatQualityPercent($hit),
                'Read miss ratio' => $bp_miss_pct !== null ? $bp_miss_pct.'%' : 'n/a',
                'Log file size' => self::formatBytesToMbGbTb($g('innodb_log_file_size')),
                'Log buffer size' => self::formatBytesToMbGbTb($g('innodb_log_buffer_size')),
                'Buffer pool instances' => $g('innodb_buffer_pool_instances') ?? 'n/a',
                'Page size' => is_numeric($g('innodb_page_size')) ? number_format((float)$g('innodb_page_size'), 0).' B' : 'n/a',
                'Read IO threads' => $g('innodb_read_io_threads') ?? 'n/a',
                'Write IO threads' => $g('innodb_write_io_threads') ?? 'n/a',
                'FLUSH_LOG_AT_TRX_COMMIT' => $g('innodb_flush_log_at_trx_commit') ?? 'n/a',
            ];
        }

        if ($ariaEnabled) {
            $data['aria'] = [
                '<img height="16px" width="16px" src="'.IMG.'icon/ram.svg" > Aria pagecache' => self::formatBytesToMbGbTb($g('aria_pagecache_buffer_size')),
                '<img height="16px" width="16px" src="'.IMG.'icon/bar-chart-svgrepo-com.svg" > Cache used' => self::formatPercentUsage($ariaCacheUsedPct),
                '<img height="16px" width="16px" src="'.IMG.'icon/bar-chart-svgrepo-com.svg" > Dirty pages' => self::formatPercentUsage($ariaDirtyPct),
                '<img height="16px" width="16px" src="'.IMG.'icon/bar-chart-svgrepo-com.svg" > Hit ratio' => self::formatPercentUsage($ariaHit),
                'Log file size' => self::formatBytesToMbGbTb($g('aria_log_file_size')),
                'Block size' => is_numeric($g('aria_block_size')) ? number_format((float)$g('aria_block_size'), 0).' B' : 'n/a',
                'Temp tables engine' => $g('aria_used_for_temp_tables') ?? 'n/a',
                'Encrypt tables' => $g('aria_encrypt_tables') ?? 'n/a',
                'Recover mode' => $g('aria_recover') ?? 'n/a',
                'Page checksum' => $g('aria_page_checksum') ?? 'n/a',
            ];
        }

        if ($rocksdbEnabled) {
            $walLimitBytes = is_numeric($g('rocksdb_wal_size_limit_mb')) ? ((float)$g('rocksdb_wal_size_limit_mb') * 1024 * 1024) : null;

            $data['rocksdb'] = [
                '<img height="16px" width="16px" src="'.IMG.'icon/ram.svg" > Block cache' => self::formatBytesToMbGbTb($g('rocksdb_block_cache_size')),
                '<img height="16px" width="16px" src="'.IMG.'icon/bar-chart-svgrepo-com.svg" > Block cache / RAM' => self::formatUsageFromBytes('', 'rocksdb', $g('rocksdb_block_cache_size'), $g('memory_total')),
                '<img height="16px" width="16px" src="'.IMG.'icon/ram.svg" > Write buffer' => self::formatBytesToMbGbTb($g('rocksdb_db_write_buffer_size')),
                '<img height="16px" width="16px" src="'.IMG.'icon/bar-chart-svgrepo-com.svg" > Write buffer / RAM' => self::formatUsageFromBytes('', 'rocksdb', $g('rocksdb_db_write_buffer_size'), $g('memory_total')),
                'Max total WAL size' => self::formatBytesToMbGbTb($g('rocksdb_max_total_wal_size')),
                'WAL size limit' => self::formatBytesToMbGbTb($walLimitBytes),
                'flush_log_at_trx_commit' => $g('rocksdb_flush_log_at_trx_commit') ?? 'n/a',
                'Write disable WAL' => $g('rocksdb_write_disable_wal') ?? 'n/a',
                'Info log level' => $g('rocksdb_info_log_level') ?? 'n/a',
                'Enable TTL' => $g('rocksdb_enable_ttl') ?? 'n/a',
                'Cache index/filter blocks' => $g('rocksdb_cache_index_and_filter_blocks') ?? 'n/a',
                'Direct reads' => $g('rocksdb_use_direct_reads') ?? 'n/a',
                'Direct IO flush/compaction' => $g('rocksdb_use_direct_io_for_flush_and_compaction') ?? 'n/a',
            ];
        }

        if ($myisamEnabled) {
            $data['myisam'] = [
                '<img height="16px" width="16px" src="'.IMG.'icon/ram.svg" > Key buffer size' => self::formatBytesToMbGbTb($g('key_buffer_size')),
                '<img height="16px" width="16px" src="'.IMG.'icon/bar-chart-svgrepo-com.svg" > Key cache used' => self::formatPercentUsage($keyCacheUsedPct),
                '<img height="16px" width="16px" src="'.IMG.'icon/bar-chart-svgrepo-com.svg" > Key hit ratio' => self::formatQualityPercent($keyHit),
                'Key miss ratio' => $keyMiss !== null ? $keyMiss.'%' : 'n/a',
                'Read requests' => is_numeric($g('key_read_requests')) ? number_format((float)$g('key_read_requests'), 0) : 'n/a',
                'Reads from disk' => is_numeric($g('key_reads')) ? number_format((float)$g('key_reads'), 0) : 'n/a',
                'Write requests' => is_numeric($g('key_write_requests')) ? number_format((float)$g('key_write_requests'), 0) : 'n/a',
                'Writes' => is_numeric($g('key_writes')) ? number_format((float)$g('key_writes'), 0) : 'n/a',
                'Sort buffer size' => self::formatBytesToMbGbTb($g('myisam_sort_buffer_size')),
                'Recover options' => $g('myisam_recover_options') ?? 'n/a',
                'Delay key write' => $g('delay_key_write') ?? 'n/a',
            ];
        }

        if ($columnstoreEnabled) {
            $data['columnstore'] = [
                'Version' => $g('columnstore_version') ?? 'n/a',
                'Select handler' => $g('columnstore_select_handler') ?? 'n/a',
                'Cache inserts' => $g('columnstore_cache_inserts') ?? 'n/a',
                'Use import for batch insert' => $g('columnstore_use_import_for_batchinsert') ?? 'n/a',
                'Ordered only' => $g('columnstore_ordered_only') ?? 'n/a',
                'Diskjoin small side limit' => self::formatBytesToMbGbTb($g('columnstore_diskjoin_smallsidelimit')),
                '<img height="16px" width="16px" src="'.IMG.'icon/bar-chart-svgrepo-com.svg" > UM mem / RAM' => self::formatUsageFromBytes('', 'columnstore', $g('columnstore_um_mem_limit'), $g('memory_total')),
                '<img height="16px" width="16px" src="'.IMG.'icon/bar-chart-svgrepo-com.svg" > PM mem / RAM' => self::formatUsageFromBytes('', 'columnstore', $g('columnstore_pm_mem_limit'), $g('memory_total')),
                'Compression threads' => $g('columnstore_num_compress_threads') ?? 'n/a',
            ];
        }

        if ($spiderEnabled) {
            $data['spider'] = [
                'Use handler' => $value('spider_use_handler') ?? 'n/a',
                'Connect timeout' => is_numeric($value('spider_connect_timeout')) ? $value('spider_connect_timeout').'s' : ($value('spider_connect_timeout') ?? 'n/a'),
                'Retry count' => $value('spider_connect_retry_count') ?? 'n/a',
                'Retry interval' => is_numeric($value('spider_connect_retry_interval')) ? $value('spider_connect_retry_interval').'s' : ($value('spider_connect_retry_interval') ?? 'n/a'),
                'Error interval' => is_numeric($value('spider_connect_error_interval')) ? $value('spider_connect_error_interval').'s' : ($value('spider_connect_error_interval') ?? 'n/a'),
                'Net read timeout' => is_numeric($value('spider_net_read_timeout')) ? $value('spider_net_read_timeout').'s' : ($value('spider_net_read_timeout') ?? 'n/a'),
                'Net write timeout' => is_numeric($value('spider_net_write_timeout')) ? $value('spider_net_write_timeout').'s' : ($value('spider_net_write_timeout') ?? 'n/a'),
                'Quick mode' => $value('spider_quick_mode') ?? 'n/a',
                'Split read' => $value('spider_split_read') ?? 'n/a',
                'Semi split read' => $value('spider_semi_split_read') ?? 'n/a',
                'Semi split read limit' => $value('spider_semi_split_read_limit') ?? 'n/a',
                'Support XA' => $value('spider_support_xa') ?? 'n/a',
                'Internal XA' => $value('spider_internal_xa') ?? 'n/a',
                'Sync autocommit' => $value('spider_sync_autocommit') ?? 'n/a',
                'Sync trx isolation' => $value('spider_sync_trx_isolation') ?? 'n/a',
                'Sync time zone' => $value('spider_sync_time_zone') ?? 'n/a',
                'Remote trx isolation' => $value('spider_remote_trx_isolation') ?? 'n/a',
                'Remote autocommit' => $value('spider_remote_autocommit') ?? 'n/a',
                'General log' => $value('spider_general_log') ?? 'n/a',
            ];
        }

        $data['binlog'] = [
            'log_bin' => $g('log_bin'),
            'sync_binlog' => $g('sync_binlog') ?? 'n/a',
            'Last binlog file' => $g('binlog_file_last') ?? 'n/a',
            'Binlog basename' => $g('log_bin_basename') ?? 'n/a',
            'Last position (GTID current)' => $g('gtid_current_pos') ?? 'n/a',
            'Last position (GTID binlog)' => $g('gtid_binlog_pos') ?? 'n/a',
            'binlog_format' => $g('binlog_format'),
            'binlog_row_image' => $g('binlog_row_image') ?? 'n/a',
            'binlog_checksum' => $g('binlog_checksum') ?? 'n/a',
            'max_binlog_size' => self::formatBytesToMbGbTb($g('max_binlog_size')),
            'binlog_cache_size' => self::formatBytesToMbGbTb($g('binlog_cache_size')),
            'binlog_stmt_cache_size' => self::formatBytesToMbGbTb($g('binlog_stmt_cache_size')),
            'binlog_space_limit' => self::formatBytesToMbGbTb($g('binlog_space_limit')),
            '#files' => $g('binlog_nb_files'),
            'Total size' => self::formatBytesToMbGb($g('binlog_total_size')),
            'expire (sec)' => $g('binlog_expire_logs_seconds'),
            'expire_logs_days' => $g('expire_logs_days') ?? 'n/a',
        ];

        $wsrepOn = strtoupper((string)($g('wsrep_on') ?? ''));
        $data['galera_cluster'] = [];
        $data['galera_cluster_flow'] = [];
        $data['galera_cluster_provider'] = [];
        $data['galera_cluster_config'] = [];

        if ($wsrepOn === 'ON') {
            $clusterStatus = (string)($g('wsrep_cluster_status') ?? 'n/a');
            $localStateComment = (string)($g('wsrep_local_state_comment') ?? 'n/a');
            $ready = (string)($g('wsrep_ready') ?? 'n/a');
            $connected = (string)($g('wsrep_connected') ?? 'n/a');
            $desync = (string)($g('wsrep_desync') ?? 'n/a');
            $recvQueue = $g('wsrep_local_recv_queue');
            $sendQueue = $g('wsrep_local_send_queue');
            $flowPaused = $g('wsrep_flow_control_paused');

            $quorumOk = (
                strcasecmp($clusterStatus, 'Primary') === 0
                && strtoupper((string)$ready) === 'ON'
                && strtoupper((string)$connected) === 'ON'
            );

            $providerOptionsRaw = (string)($g('wsrep_provider_options') ?? '');
            $providerOptionsParsed = self::parseWsrepProviderOptions($providerOptionsRaw);

            $data['galera_cluster'] = [
                'Cluster name' => $g('wsrep_cluster_name') ?: 'n/a',
                'Cluster status' => self::formatGaleraClusterStatus($clusterStatus),
                'Node state' => self::formatGaleraNodeState($localStateComment),
                'Ready' => self::formatGaleraBoolean($ready),
                'Connected' => self::formatGaleraBoolean($connected),
                'Quorum health' => self::formatGaleraBoolean($quorumOk ? 'ON' : 'OFF', 'QUORUM OK', 'QUORUM LOST'),
                'Desync' => self::formatGaleraBoolean($desync, 'DESYNC', 'SYNCED'),
                'Cluster size' => $g('wsrep_cluster_size') ?? 'n/a',
                'Local state id' => $g('wsrep_local_state') ?? 'n/a',
                'SST method' => $g('wsrep_sst_method') ?? 'n/a',
                'Provider version' => $g('wsrep_provider_version') ?? 'n/a',
                'Flow control paused' => self::formatGaleraFlowControlPaused($g('wsrep_flow_control_paused')),
                'Recv queue' => $g('wsrep_local_recv_queue') ?? 'n/a',
                'Send queue' => $g('wsrep_local_send_queue') ?? 'n/a',
                'Cert deps distance' => $g('wsrep_cert_deps_distance') ?? 'n/a',
                'Apply window' => $g('wsrep_apply_window') ?? 'n/a',
                'Commit window' => $g('wsrep_commit_window') ?? 'n/a',
                'Cert interval' => $g('wsrep_cert_interval') ?? 'n/a',
                'Last committed' => $g('wsrep_last_committed') ?? 'n/a',
                'Replicated data' => self::formatBytesHuman($g('wsrep_repl_data_bytes')),
                'Replicated keys' => $g('wsrep_repl_keys') ?? 'n/a',
                'Replicated keys size' => self::formatBytesHuman($g('wsrep_repl_keys_bytes')),
                'wsrep_incoming_addresses' => $g('wsrep_incoming_addresses') ?: 'n/a',
                'Node address' => $g('wsrep_node_address') ?: 'n/a',
                'Cluster UUID' => $g('wsrep_cluster_state_uuid') ?: 'n/a',
                'Local UUID' => $g('wsrep_local_state_uuid') ?: 'n/a',
                'gcomm UUID' => $g('wsrep_gcomm_uuid') ?: 'n/a',
                'Bootstrap safety' => self::tr('pc.bootstrap=true should be used only when the cluster lost quorum and only on one node.'),
            ];

            $data['galera_cluster_flow'] = [
                'Flow control paused' => self::formatGaleraFlowControlPaused($flowPaused),
                'Flow pressure' => self::formatGaleraQueuePressure($flowPaused, $recvQueue, $sendQueue),
                'Recv queue' => self::formatGaleraQueue($recvQueue),
                'Send queue' => self::formatGaleraQueue($sendQueue),
                'Cert deps distance' => $g('wsrep_cert_deps_distance') ?? 'n/a',
                'Apply window' => $g('wsrep_apply_window') ?? 'n/a',
                'Commit window' => $g('wsrep_commit_window') ?? 'n/a',
                'Cert interval' => $g('wsrep_cert_interval') ?? 'n/a',
                'Last committed' => $g('wsrep_last_committed') ?? 'n/a',
                'Replicated data' => self::formatBytesHuman($g('wsrep_repl_data_bytes')),
                'Replicated keys' => $g('wsrep_repl_keys') ?? 'n/a',
                'Replicated keys size' => self::formatBytesHuman($g('wsrep_repl_keys_bytes')),
            ];

            $data['galera_cluster_provider'] = [
                'Provider library' => $g('wsrep_provider') ?: 'n/a',
                'Provider version' => $g('wsrep_provider_version') ?: 'n/a',
                'Patch version' => $g('wsrep_patch_version') ?: 'n/a',
            ];

            if (!empty($providerOptionsParsed)) {
                foreach ($providerOptionsParsed as $providerOptionKey => $providerOptionValue) {
                    $data['galera_cluster_provider']['wsrep_provider_options.'.$providerOptionKey] = $providerOptionValue;
                }
            } else {
                $data['galera_cluster_provider']['wsrep_provider_options.*'] = 'n/a';
            }

            $data['galera_cluster_config'] = [
                'Cluster address' => $g('wsrep_cluster_address') ?: 'n/a',
                'Incoming addresses' => $g('wsrep_incoming_addresses') ?: 'n/a',
                'Node name' => $g('wsrep_node_name') ?: 'n/a',
                'Node address' => $g('wsrep_node_address') ?: 'n/a',
                'Node incoming address' => $g('wsrep_node_incoming_address') ?: 'n/a',
                'SST method' => $g('wsrep_sst_method') ?: 'n/a',
                'SST donor' => $g('wsrep_sst_donor') ?: 'n/a',
                'SST donor rejects queries' => self::formatGaleraBoolean($g('wsrep_sst_donor_rejects_queries')),
                'SST auth (masked)' => self::maskGaleraSecret((string)($g('wsrep_sst_auth') ?? '')),
                'Reject queries' => self::formatGaleraBoolean($g('wsrep_reject_queries')),
                'wsrep_sync_wait' => $g('wsrep_sync_wait') ?? 'n/a',
                'wsrep_slave_threads' => $g('wsrep_slave_threads') ?? 'n/a',
                'wsrep_auto_increment_control' => self::formatGaleraBoolean($g('wsrep_auto_increment_control')),
                'wsrep_gtid_mode' => self::formatGaleraBoolean($g('wsrep_gtid_mode')),
                'wsrep_gtid_domain_id' => $g('wsrep_gtid_domain_id') ?? 'n/a',
                'wsrep_forced_binlog_format' => $g('wsrep_forced_binlog_format') ?? 'n/a',
                'wsrep_log_conflicts' => self::formatGaleraBoolean($g('wsrep_log_conflicts')),
                'wsrep_max_ws_rows' => is_numeric($g('wsrep_max_ws_rows')) ? number_format((float)$g('wsrep_max_ws_rows'), 0) : ($g('wsrep_max_ws_rows') ?? 'n/a'),
                'wsrep_max_ws_size' => self::formatBytesHuman($g('wsrep_max_ws_size')),
                'wsrep_retry_autocommit' => $g('wsrep_retry_autocommit') ?? 'n/a',
                'wsrep_restart_slave' => self::formatGaleraBoolean($g('wsrep_restart_slave')),
                'wsrep_recover' => self::formatGaleraBoolean($g('wsrep_recover')),
                'wsrep_start_position' => $g('wsrep_start_position') ?: 'n/a',
                'wsrep_data_home_dir' => $g('wsrep_data_home_dir') ?: 'n/a',
                'wsrep_convert_lock_to_trx' => self::formatGaleraBoolean($g('wsrep_convert_lock_to_trx')),
                'wsrep_causal_reads' => self::formatGaleraBoolean($g('wsrep_causal_reads')),
                'wsrep_dirty_reads' => self::formatGaleraBoolean($g('wsrep_dirty_reads')),
                'wsrep_mysql_replication_bundle' => $g('wsrep_mysql_replication_bundle') ?? 'n/a',
                'wsrep_slave_fk_checks' => self::formatGaleraBoolean($g('wsrep_slave_fk_checks')),
                'wsrep_slave_uk_checks' => self::formatGaleraBoolean($g('wsrep_slave_uk_checks')),
                'wsrep_replicate_myisam' => self::formatGaleraBoolean($g('wsrep_replicate_myisam')),
                'wsrep_osu_method' => $g('wsrep_osu_method') ?? 'n/a',
                'wsrep_notify_cmd' => $g('wsrep_notify_cmd') ?: 'n/a',
                'wsrep_dbug_option' => $g('wsrep_dbug_option') ?: 'n/a',
                'wsrep_drupal_282555_workaround' => self::formatGaleraBoolean($g('wsrep_drupal_282555_workaround')),
            ];

            $incomingAddresses = (string)($g('wsrep_incoming_addresses') ?? '');
            $clusterNodeIds = [];

            if ($incomingAddresses !== '' && strpos($incomingAddresses, ':') !== false) {
                try {
                    $clusterNodeIds = Mysql::getIdMySQLFromGalera($incomingAddresses);
                } catch (\Throwable $e) {
                    $clusterNodeIds = [];
                }
            }

            $clusterNodeIds = array_values(array_unique(array_filter(array_map('intval', (array)$clusterNodeIds))));

            if (!empty($clusterNodeIds)) {
                $nodeStateRows = Extraction2::display([
                    'mysql_server::mysql_available',
                    'wsrep_on',
                    'wsrep_cluster_status',
                    'wsrep_local_state_comment',
                    'wsrep_ready',
                ], $clusterNodeIds);

                $dbDefault = Sgbd::sql(DB_DEFAULT);
                $sqlNodes = "SELECT id, display_name, ip, port FROM mysql_server WHERE id IN (".implode(',', $clusterNodeIds).")";
                $resNodes = $dbDefault->sql_query($sqlNodes);
                $nodeMeta = [];

                while ($arrNode = $dbDefault->sql_fetch_array($resNodes, MYSQLI_ASSOC)) {
                    $nodeMeta[(int)$arrNode['id']] = $arrNode;
                }

                $hasPrimarySyncedAvailableNode = false;

                foreach ($clusterNodeIds as $clusterNodeIdScan) {
                    $nodeScan = $nodeStateRows[$clusterNodeIdScan] ?? [];

                    $scanAvailable = (string)($nodeScan['mysql_available'] ?? $nodeScan['mysql_server::mysql_available'] ?? '0');
                    $scanClusterStatus = (string)($nodeScan['wsrep_cluster_status'] ?? '');
                    $scanState = (string)($nodeScan['wsrep_local_state_comment'] ?? '');

                    if (
                        $scanAvailable === '1'
                        && strcasecmp($scanClusterStatus, 'Primary') === 0
                        && strcasecmp($scanState, 'Synced') === 0
                    ) {
                        $hasPrimarySyncedAvailableNode = true;
                        break;
                    }
                }

                $hasRemoteNode = false;
                $printedNodeIds = [];

                foreach ($clusterNodeIds as $clusterNodeId) {
                    if ($clusterNodeId === $id_mysql_server) {
                        continue;
                    }

                    if (isset($printedNodeIds[$clusterNodeId])) {
                        continue;
                    }
                    $printedNodeIds[$clusterNodeId] = true;

                    $hasRemoteNode = true;
                    $node = $nodeStateRows[$clusterNodeId] ?? [];

                    $nodeAvailable = (string)($node['mysql_available'] ?? $node['mysql_server::mysql_available'] ?? '0');
                    $nodeWsrepOn = strtoupper((string)($node['wsrep_on'] ?? ''));
                    $nodeClusterStatus = (string)($node['wsrep_cluster_status'] ?? 'n/a');
                    $nodeState = (string)($node['wsrep_local_state_comment'] ?? 'n/a');
                    $nodeReady = strtoupper((string)($node['wsrep_ready'] ?? ''));

                    $eligibleByStandardRule = (
                        $nodeAvailable === '1'
                        && $nodeWsrepOn === 'ON'
                        && strcasecmp($nodeClusterStatus, 'Primary') !== 0
                        && strcasecmp($nodeState, 'Synced') === 0
                        && $nodeReady === 'ON'
                    );

                    $eligibleByEmergencyRule = (
                        $nodeAvailable === '1'
                        && !$hasPrimarySyncedAvailableNode
                    );

                    $eligibleForPrimary = $eligibleByStandardRule || $eligibleByEmergencyRule;

                    $nodeIdentity = $nodeMeta[$clusterNodeId] ?? [];
                    $nodeLabel = trim((string)($nodeIdentity['display_name'] ?? 'Node #'.$clusterNodeId));
                    $nodeIp = (string)($nodeIdentity['ip'] ?? 'n/a');
                    $nodePort = (string)($nodeIdentity['port'] ?? 'n/a');

                    $nodeRowClass = '';
                    if ($nodeAvailable !== '1') {
                        $nodeRowClass = 'danger';
                    } elseif (strcasecmp($nodeClusterStatus, 'Primary') !== 0) {
                        $nodeRowClass = 'warning';
                    }

                    $data['galera_cluster']['Node #'.$clusterNodeId.' ('.$nodeLabel.')'] = [
                        'type' => 'status_text',
                        'value' =>
                            'IP: '.$nodeIp.':'.$nodePort
                            .' | Cluster: '.$nodeClusterStatus
                            .' | State: '.$nodeState
                            .' | Ready: '.($nodeReady === '' ? 'n/a' : $nodeReady)
                            .' | MySQL available: '.($nodeAvailable === '1' ? 'YES' : 'NO'),
                        'row_class' => $nodeRowClass,
                    ];

                    $data['galera_cluster']['SET primary → node #'.$clusterNodeId] = [
                        'type' => 'action_button',
                        'status' => $eligibleForPrimary ? self::tr('Eligible') : self::tr('Not eligible'),
                        'status_class' => $eligibleForPrimary ? 'label label-success' : 'label label-default',
                        'url' => $eligibleForPrimary ? LINK.'GaleraCluster/setNodeAsPrimary/'.$clusterNodeId : '',
                        'label' => self::tr('SET PRIMARY'),
                        'class' => $eligibleForPrimary ? 'btn btn-xs btn-danger' : 'btn btn-xs btn-default',
                        'disabled' => !$eligibleForPrimary,
                        'title' => $eligibleForPrimary
                            ? ($eligibleByEmergencyRule
                                ? self::tr('Emergency mode: no reachable node is currently Primary + Synced')
                                : self::tr('Node is Non-Primary + Synced + Ready=ON'))
                            : self::tr('Required: mysql_available=1 and (Non-Primary + Synced + Ready=ON OR no reachable node Primary + Synced)'),
                    ];
                }

                if (!$hasRemoteNode) {
                    $data['galera_cluster']['SET primary'] = self::tr('No other node detected in wsrep_incoming_addresses.');
                }
            } else {
                $data['galera_cluster']['SET primary'] = self::tr('No cluster peers detected in wsrep_incoming_addresses.');
            }
        } else {
            $data['galera_cluster'] = [
                'Mode' => '<span class="label label-default">wsrep_on=OFF</span>',
                'Info' => self::tr('This server is not currently running as a Galera node.'),
            ];

            $data['galera_cluster_config'] = [
                'wsrep_on' => '<span class="label label-default">OFF</span>',
            ];
        }

        $sslVersion = self::firstNonEmpty(
            $g('ssl_version'),
            $g('Ssl_version')
        );

        $sslCipher = self::firstNonEmpty(
            $g('ssl_cipher'),
            $g('Ssl_cipher')
        );

        $sslValidFrom = self::firstNonEmpty(
            $g('ssl_server_not_before'),
            $g('Ssl_server_not_before')
        );

        $sslValidTo = self::firstNonEmpty(
            $g('ssl_server_not_after'),
            $g('Ssl_server_not_after')
        );

        $data['ssl'] = [
            'Version' => $sslVersion ?? 'n/a',
            'Cipher' => $sslCipher ?? 'n/a',
            'Valid from' => $sslValidFrom ?? 'n/a',
            'Valid to' => $sslValidTo ?? 'n/a',
        ];
        $resolvedCpuUsage = $g('cpu_usage');
        if (!is_numeric($resolvedCpuUsage)) {
            $resolvedCpuUsage = $this->resolveCpuUsageFromDetail($g('cpu_detail'));
        }

        $data['os'] = [
            '<img height="16px" width="16px" src="'.IMG.'icon/hostname.svg" > Hostname' => $g('hostname'),
            '<img height="16px" width="16px" src="'.IMG.'icon/uptime.svg" > Uptime' => $uptime_h,
            '<img height="16px" width="16px" src="'.IMG.'icon/network.svg" > IPs' => self::formatIpsValue($g('ips')),
            '<img height="16px" width="16px" src="'.IMG.'icon/linux-svgrepo-com.svg" > OS' => self::formatOsWithIcon($g('os'), $g('distributor')),
            '<img height="16px" width="16px" src="'.IMG.'icon/kernel.svg" > Kernel' => $g('kernel'),
            '<img height="16px" width="16px" src="'.IMG.'icon/64bit.svg" > Arch' => self::formatArchWithBitLabel($g('arch')),
            '<img height="16px" width="16px" src="'.IMG.'icon/cpu.svg" > CPU Usage' => self::formatCpuUsage($resolvedCpuUsage, $g('cpu_thread_count')),
            '<img height="16px" width="16px" src="'.IMG.'icon/ram.svg" > '.self::tr('RAM Usage') => self::formatRamUsage($g('memory_used'), $g('memory_total')),
            '<img height="16px" width="16px" src="'.IMG.'icon/swap.svg" > '.self::tr('SWAP Usage') => self::formatSwapUsage($g('swap_used'), $g('swap_total')),
        ];

        $getSshMetric = function(string $metricName) use ($value, $g) {
            $metricValue = $value($metricName);

            if ($metricValue === null || $metricValue === '') {
                $prefixed = $g('ssh_stats::'.$metricName);
                if (is_array($prefixed) && array_key_exists('count', $prefixed)) {
                    $prefixed = $prefixed['count'];
                }
                $metricValue = $prefixed;
            }

            return $metricValue;
        };

        $toDisplayValue = function($metricValue): string {
            if ($metricValue === null || $metricValue === '') {
                return 'n/a';
            }

            if (is_bool($metricValue)) {
                return $metricValue ? '1' : '0';
            }

            if (is_scalar($metricValue)) {
                return (string)$metricValue;
            }

            $json = json_encode($metricValue, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return $json !== false ? $json : 'n/a';
        };

        $data['ssh_sst_metrics'] = [
            'mysql_datadir_path' => $toDisplayValue($getSshMetric('mysql_datadir_path')),
            'mysql_datadir_total_size' => $toDisplayValue($getSshMetric('mysql_datadir_total_size')),
            'mysql_datadir_clean_size' => $toDisplayValue($getSshMetric('mysql_datadir_clean_size')),
            'mysql_sst_elapsed_sec' => $toDisplayValue($getSshMetric('mysql_sst_elapsed_sec')),
            'mysql_sst_in_progress' => $toDisplayValue($getSshMetric('mysql_sst_in_progress')),
        ];






        $data['disks'] = $g('disks');
        $data['ips'] = $g('ips');
        $data['processlist'] = $g('processlist');

        $this->set('data', $data);
        $this->set('param', $param);
   
    }



/**
 * Handle mysql server state through `secToHuman`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param int $s Input value for `s`.
 * @phpstan-param int $s
 * @psalm-param int $s
 * @return string Returned value for secToHuman.
 * @phpstan-return string
 * @psalm-return string
 * @see self::secToHuman()
 * @example /fr/mysqlserver/secToHuman
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function secToHuman(int $s): string
    {
        $d = intdiv($s, 86400); $s %= 86400;
        $h = intdiv($s, 3600);  $s %= 3600;
        $m = intdiv($s, 60);    $s %= 60;
        $out = [];
        if ($d) $out[] = $d.'d';
        if ($h || !empty($out)) $out[] = $h.'h';
        if ($m || !empty($out)) $out[] = $m.'m';
        $out[] = $s.'s';
        return implode(' ', $out);
    }

/**
 * Handle mysql server state through `formatVipUptime`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $destinationDate Input value for `destinationDate`.
 * @phpstan-param mixed $destinationDate
 * @psalm-param mixed $destinationDate
 * @return ?string Returned value for formatVipUptime.
 * @phpstan-return ?string
 * @psalm-return ?string
 * @see self::formatVipUptime()
 * @example /fr/mysqlserver/formatVipUptime
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function formatVipUptime($destinationDate): ?string
    {
        $raw = trim((string)($destinationDate ?? ''));
        if ($raw === '') {
            return null;
        }

        $timestamp = strtotime($raw);
        if ($timestamp === false) {
            return null;
        }

        $diff = time() - $timestamp;
        if ($diff < 0) {
            $diff = 0;
        }

        return self::secToHuman((int)$diff);
    }

/**
 * Handle mysql server state through `formatBytesToMbGb`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $bytes Input value for `bytes`.
 * @phpstan-param mixed $bytes
 * @psalm-param mixed $bytes
 * @return string Returned value for formatBytesToMbGb.
 * @phpstan-return string
 * @psalm-return string
 * @see self::formatBytesToMbGb()
 * @example /fr/mysqlserver/formatBytesToMbGb
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function formatBytesToMbGb($bytes): string
    {
        if ($bytes === null || $bytes === '') {
            return 'n/a';
        }

        $bytes = (float) $bytes;

        $oneGb = 1024 * 1024 * 1024;
        $oneMb = 1024 * 1024;

        if ($bytes >= $oneGb) {
            return number_format($bytes / $oneGb, 2).' GB';
        }

        return number_format($bytes / $oneMb, 2).' MB';
    }

/**
 * Handle mysql server state through `formatBytesToMbGbTb`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $bytes Input value for `bytes`.
 * @phpstan-param mixed $bytes
 * @psalm-param mixed $bytes
 * @return string Returned value for formatBytesToMbGbTb.
 * @phpstan-return string
 * @psalm-return string
 * @see self::formatBytesToMbGbTb()
 * @example /fr/mysqlserver/formatBytesToMbGbTb
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function formatBytesToMbGbTb($bytes): string
    {
        if ($bytes === null || $bytes === '') {
            return 'n/a';
        }

        $bytes = (float) $bytes;

        $oneTb = 1024 * 1024 * 1024 * 1024;
        $oneGb = 1024 * 1024 * 1024;
        $oneMb = 1024 * 1024;
        $oneKb = 1024;

        if ($bytes >= $oneTb) {
            return number_format($bytes / $oneTb, 2).' TB';
        }

        if ($bytes >= $oneGb) {
            return number_format($bytes / $oneGb, 2).' GB';
        }

        if ($bytes >= $oneMb) {
            return number_format($bytes / $oneMb, 2).' MB';
        }

        return number_format($bytes / $oneKb, 2).' KB';
    }

/**
 * Handle mysql server state through `formatBytesHuman`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $bytes Input value for `bytes`.
 * @phpstan-param mixed $bytes
 * @psalm-param mixed $bytes
 * @return string Returned value for formatBytesHuman.
 * @phpstan-return string
 * @psalm-return string
 * @see self::formatBytesHuman()
 * @example /fr/mysqlserver/formatBytesHuman
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function formatBytesHuman($bytes): string
    {
        if ($bytes === null || $bytes === '' || !is_numeric($bytes)) {
            return 'n/a';
        }

        $bytes = (float) $bytes;

        $oneTb = 1024 * 1024 * 1024 * 1024;
        $oneGb = 1024 * 1024 * 1024;
        $oneMb = 1024 * 1024;
        $oneKb = 1024;

        if ($bytes >= $oneTb) {
            return number_format($bytes / $oneTb, 2).' TB';
        }

        if ($bytes >= $oneGb) {
            return number_format($bytes / $oneGb, 2).' GB';
        }

        if ($bytes >= $oneMb) {
            return number_format($bytes / $oneMb, 2).' MB';
        }

        if ($bytes >= $oneKb) {
            return number_format($bytes / $oneKb, 2).' KB';
        }

        return number_format($bytes, 0).' B';
    }

/**
 * Handle mysql server state through `formatJsonValue`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $value Input value for `value`.
 * @phpstan-param mixed $value
 * @psalm-param mixed $value
 * @return string Returned value for formatJsonValue.
 * @phpstan-return string
 * @psalm-return string
 * @see self::formatJsonValue()
 * @example /fr/mysqlserver/formatJsonValue
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function formatJsonValue($value): string
    {
        if ($value === null || $value === '') {
            return 'n/a';
        }

        if (is_array($value) || is_object($value)) {
            $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return $json !== false ? $json : 'n/a';
        }

        return (string) $value;
    }

/**
 * Handle mysql server state through `firstNonEmpty`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param ... $values Input value for `values`.
 * @phpstan-param ... $values
 * @psalm-param ... $values
 * @return mixed Returned value for firstNonEmpty.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::firstNonEmpty()
 * @example /fr/mysqlserver/firstNonEmpty
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function firstNonEmpty(...$values)
    {
        foreach ($values as $value) {
            if (is_array($value)) {
                continue;
            }

            if ($value === null) {
                continue;
            }

            if (is_string($value) && trim($value) === '') {
                continue;
            }

            return $value;
        }

        return null;
    }

/**
 * Handle mysql server state through `formatIpsValue`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $value Input value for `value`.
 * @phpstan-param mixed $value
 * @psalm-param mixed $value
 * @return string Returned value for formatIpsValue.
 * @phpstan-return string
 * @psalm-return string
 * @see self::formatIpsValue()
 * @example /fr/mysqlserver/formatIpsValue
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function formatIpsValue($value): string
    {
        $ips = self::extractIpv4List($value);

        if (empty($ips)) {
            return self::formatJsonValue($value);
        }

/**
 * Class responsible for implode workflows.
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
        usort($ips, [self::class, 'compareIpv4']);

        return implode(' ', array_values($ips));
    }

/**
 * Handle implode state through `extractIpv4List`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $value Input value for `value`.
 * @phpstan-param mixed $value
 * @psalm-param mixed $value
 * @return array Returned value for extractIpv4List.
 * @phpstan-return array
 * @psalm-return array
 * @see self::extractIpv4List()
 * @example /fr/implode/extractIpv4List
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function extractIpv4List($value): array
    {
        if (is_array($value)) {
            $candidates = $value;
        } else {
            $raw = trim((string) $value);
            $decoded = json_decode($raw, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $candidates = $decoded;
            } else {
                $candidates = preg_split('/[\s,;]+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
            }
        }

        $ips = [];
        foreach ($candidates as $candidate) {
            $ip = trim((string) $candidate);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
                $ips[$ip] = $ip;
            }
        }

        return array_values($ips);
    }

/**
 * Handle `compareIpv4`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param string $a Input value for `a`.
 * @phpstan-param string $a
 * @psalm-param string $a
 * @param string $b Input value for `b`.
 * @phpstan-param string $b
 * @psalm-param string $b
 * @return int Returned value for compareIpv4.
 * @phpstan-return int
 * @psalm-return int
 * @example compareIpv4(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function compareIpv4(string $a, string $b): int
    {
        $pa = array_map('intval', explode('.', $a));
        $pb = array_map('intval', explode('.', $b));

        for ($i = 0; $i < 4; $i++) {
            $diff = ($pa[$i] ?? 0) <=> ($pb[$i] ?? 0);
            if ($diff !== 0) {
                return $diff;
            }
        }

        return 0;
    }

/**
 * Handle `extractSupportedEngines`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $value Input value for `value`.
 * @phpstan-param mixed $value
 * @psalm-param mixed $value
 * @return array Returned value for extractSupportedEngines.
 * @phpstan-return array
 * @psalm-return array
 * @example extractSupportedEngines(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function extractSupportedEngines($value): array
    {
        $rows = [];

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $rows = $decoded;
            }
        } elseif (is_array($value)) {
            $rows = $value;
        }

        $engines = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $engine = strtoupper((string)($row['engine'] ?? $row['ENGINE'] ?? ''));
            $support = strtoupper((string)($row['support'] ?? $row['SUPPORT'] ?? ''));

            if ($engine === '') {
                continue;
            }

            if (in_array($support, ['YES', 'DEFAULT'], true)) {
                $engines[$engine] = true;
            }
        }

        return $engines;
    }

/**
 * Handle `hasSupportedEngine`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array $engines Input value for `engines`.
 * @phpstan-param array $engines
 * @psalm-param array $engines
 * @param string $engine Input value for `engine`.
 * @phpstan-param string $engine
 * @psalm-param string $engine
 * @return bool Returned value for hasSupportedEngine.
 * @phpstan-return bool
 * @psalm-return bool
 * @example hasSupportedEngine(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function hasSupportedEngine(array $engines, string $engine): bool
    {
        return !empty($engines[strtoupper($engine)]);
    }

/**
 * Handle `formatRamUsage`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $usedBytes Input value for `usedBytes`.
 * @phpstan-param mixed $usedBytes
 * @psalm-param mixed $usedBytes
 * @param mixed $totalBytes Input value for `totalBytes`.
 * @phpstan-param mixed $totalBytes
 * @psalm-param mixed $totalBytes
 * @return array Returned value for formatRamUsage.
 * @phpstan-return array
 * @psalm-return array
 * @example formatRamUsage(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function formatRamUsage($usedBytes, $totalBytes): array
    {
        return self::formatUsageFromBytes(self::tr('RAM usage'), 'ram', $usedBytes, $totalBytes);
    }

/**
 * Handle `formatSwapUsage`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $usedBytes Input value for `usedBytes`.
 * @phpstan-param mixed $usedBytes
 * @psalm-param mixed $usedBytes
 * @param mixed $totalBytes Input value for `totalBytes`.
 * @phpstan-param mixed $totalBytes
 * @psalm-param mixed $totalBytes
 * @return array Returned value for formatSwapUsage.
 * @phpstan-return array
 * @psalm-return array
 * @example formatSwapUsage(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function formatSwapUsage($usedBytes, $totalBytes): array
    {
        return self::formatUsageFromBytes(self::tr('Swap usage'), 'swap', $usedBytes, $totalBytes);
    }

/**
 * Handle `formatCpuUsage`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $cpuPercent Input value for `cpuPercent`.
 * @phpstan-param mixed $cpuPercent
 * @psalm-param mixed $cpuPercent
 * @param mixed|null $cpuThreads Input value for `cpuThreads`.
 * @phpstan-param mixed|null $cpuThreads
 * @psalm-param mixed|null $cpuThreads
 * @return array Returned value for formatCpuUsage.
 * @phpstan-return array
 * @psalm-return array
 * @example formatCpuUsage(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function formatCpuUsage($cpuPercent, $cpuThreads = null): array
    {
        $percent = is_numeric($cpuPercent) ? (float) $cpuPercent : 0;
        $percent = max(0, min(100, $percent));
        $usageColor = self::getUsageColorByPercent($percent);

        $cpuLabel = number_format($percent, 2).'%';

        if (is_numeric($cpuThreads) && (int)$cpuThreads > 0) {
            $cpuLabel .= ' of '.(int)$cpuThreads.' CPU(s)';
        }

        return [
            'type' => 'usage_meter',
            'metric' => 'cpu',
            'percent' => $percent,
            'text' => $cpuLabel,
            'color' => $usageColor['color'],
            'level' => $usageColor['level'],
        ];
    }

/**
 * Handle `formatConnectionUsage`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $maxUsed Input value for `maxUsed`.
 * @phpstan-param mixed $maxUsed
 * @psalm-param mixed $maxUsed
 * @param mixed $maxTotal Input value for `maxTotal`.
 * @phpstan-param mixed $maxTotal
 * @psalm-param mixed $maxTotal
 * @return array Returned value for formatConnectionUsage.
 * @phpstan-return array
 * @psalm-return array
 * @example formatConnectionUsage(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function formatConnectionUsage($maxUsed, $maxTotal): array
    {
        $used = is_numeric($maxUsed) ? (float) $maxUsed : 0;
        $total = is_numeric($maxTotal) ? (float) $maxTotal : 0;

        if ($total <= 0) {
            $usageColor = self::getUsageColorByPercent(0);
            return [
                'type' => 'usage_meter',
                'metric' => 'connections',
                'percent' => 0,
                'text' => 'n/a',
                'color' => $usageColor['color'],
                'level' => $usageColor['level'],
            ];
        }

        $percent = round(($used / $total) * 100, 2);
        $usageColor = self::getUsageColorByPercent($percent);

        return [
            'type' => 'usage_meter',
            'metric' => 'connections',
            'percent' => $percent,
            'text' => number_format($percent, 0).'% of total of '.(int)$total,
            'color' => $usageColor['color'],
            'level' => $usageColor['level'],
        ];
    }

/**
 * Handle `formatThreadUsage`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $current Input value for `current`.
 * @phpstan-param mixed $current
 * @psalm-param mixed $current
 * @param mixed $maxTotal Input value for `maxTotal`.
 * @phpstan-param mixed $maxTotal
 * @psalm-param mixed $maxTotal
 * @return array Returned value for formatThreadUsage.
 * @phpstan-return array
 * @psalm-return array
 * @example formatThreadUsage(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function formatThreadUsage($current, $maxTotal): array
    {
        $value = is_numeric($current) ? (float) $current : 0;
        $total = is_numeric($maxTotal) ? (float) $maxTotal : 0;

        if ($total <= 0) {
            $usageColor = self::getUsageColorByPercent(0);
            return [
                'type' => 'usage_meter',
                'metric' => 'connections',
                'percent' => 0,
                'text' => 'n/a',
                'color' => $usageColor['color'],
                'level' => $usageColor['level'],
            ];
        }

        $percent = round(($value / $total) * 100, 2);
        $usageColor = self::getUsageColorByPercent($percent);

        return [
            'type' => 'usage_meter',
            'metric' => 'connections',
            'percent' => $percent,
            'text' => number_format($percent, 0).'% of total of '.(int)$total,
            'color' => $usageColor['color'],
            'level' => $usageColor['level'],
        ];
    }

/**
 * Handle `formatPercentUsage`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $percent Input value for `percent`.
 * @phpstan-param mixed $percent
 * @psalm-param mixed $percent
 * @return array Returned value for formatPercentUsage.
 * @phpstan-return array
 * @psalm-return array
 * @example formatPercentUsage(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function formatPercentUsage($percent): array
    {
        $value = is_numeric($percent) ? (float)$percent : 0;
        $value = max(0, min(100, $value));
        $usageColor = self::getUsageColorByPercent($value);

        return [
            'type' => 'usage_meter',
            'metric' => 'aria',
            'percent' => $value,
            'text' => (is_numeric($percent) ? number_format($value, 2).'%' : 'n/a'),
            'color' => $usageColor['color'],
            'level' => $usageColor['level'],
        ];
    }

/**
 * Handle `formatQualityPercent`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $percent Input value for `percent`.
 * @phpstan-param mixed $percent
 * @psalm-param mixed $percent
 * @return array Returned value for formatQualityPercent.
 * @phpstan-return array
 * @psalm-return array
 * @example formatQualityPercent(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function formatQualityPercent($percent): array
    {
        $value = is_numeric($percent) ? (float)$percent : 0;
        $value = max(0, min(100, $value));

        if (!is_numeric($percent)) {
            return [
                'type' => 'usage_meter',
                'metric' => 'quality',
                'percent' => 0,
                'text' => 'n/a',
                'color' => '#5bc0de',
                'level' => 'unknown',
            ];
        }

        if ($value >= 99) {
            $color = '#5cb85c';
            $level = 'excellent';
        } elseif ($value >= 95) {
            $color = '#f0ad4e';
            $level = 'warning';
        } else {
            $color = '#d9534f';
            $level = 'critical';
        }

        return [
            'type' => 'usage_meter',
            'metric' => 'quality',
            'percent' => $value,
            'text' => number_format($value, 2).'%',
            'color' => $color,
            'level' => $level,
        ];
    }

/**
 * Handle `formatGaleraBoolean`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $value Input value for `value`.
 * @phpstan-param mixed $value
 * @psalm-param mixed $value
 * @param string $labelOn Input value for `labelOn`.
 * @phpstan-param string $labelOn
 * @psalm-param string $labelOn
 * @param string $labelOff Input value for `labelOff`.
 * @phpstan-param string $labelOff
 * @psalm-param string $labelOff
 * @return string Returned value for formatGaleraBoolean.
 * @phpstan-return string
 * @psalm-return string
 * @example formatGaleraBoolean(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function formatGaleraBoolean($value, string $labelOn = 'ON', string $labelOff = 'OFF'): string
    {
        $normalized = strtoupper((string)($value ?? ''));
        $isOn = in_array($normalized, ['ON', 'YES', '1', 'TRUE'], true);

        if ($normalized === '' || $normalized === 'N/A') {
            return '<span class="label label-default">n/a</span>';
        }

        return $isOn
            ? '<span class="label label-success">'.htmlspecialchars($labelOn, ENT_QUOTES, 'UTF-8').'</span>'
            : '<span class="label label-default">'.htmlspecialchars($labelOff, ENT_QUOTES, 'UTF-8').'</span>';
    }

/**
 * Handle `formatGaleraClusterStatus`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $status Input value for `status`.
 * @phpstan-param mixed $status
 * @psalm-param mixed $status
 * @return string Returned value for formatGaleraClusterStatus.
 * @phpstan-return string
 * @psalm-return string
 * @example formatGaleraClusterStatus(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function formatGaleraClusterStatus($status): string
    {
        $text = trim((string)($status ?? ''));
        if ($text === '') {
            return '<span class="label label-default">n/a</span>';
        }

        $isPrimary = strcasecmp($text, 'Primary') === 0;
        $class = $isPrimary ? 'label label-success' : 'label label-danger';

        return '<span class="'.$class.'">'.htmlspecialchars($text, ENT_QUOTES, 'UTF-8').'</span>';
    }

/**
 * Handle `formatGaleraNodeState`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $state Input value for `state`.
 * @phpstan-param mixed $state
 * @psalm-param mixed $state
 * @return string Returned value for formatGaleraNodeState.
 * @phpstan-return string
 * @psalm-return string
 * @example formatGaleraNodeState(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function formatGaleraNodeState($state): string
    {
        $text = trim((string)($state ?? ''));
        if ($text === '') {
            return '<span class="label label-default">n/a</span>';
        }

        $class = 'label label-default';
        if (strcasecmp($text, 'Synced') === 0) {
            $class = 'label label-success';
        } elseif (stripos($text, 'Donor') !== false || stripos($text, 'Joining') !== false) {
            $class = 'label label-warning';
        } elseif (stripos($text, 'Initialized') !== false || stripos($text, 'Non-Primary') !== false) {
            $class = 'label label-danger';
        }

        return '<span class="'.$class.'">'.htmlspecialchars($text, ENT_QUOTES, 'UTF-8').'</span>';
    }

/**
 * Handle `formatGaleraFlowControlPaused`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $value Input value for `value`.
 * @phpstan-param mixed $value
 * @psalm-param mixed $value
 * @return string Returned value for formatGaleraFlowControlPaused.
 * @phpstan-return string
 * @psalm-return string
 * @example formatGaleraFlowControlPaused(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function formatGaleraFlowControlPaused($value): string
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return 'n/a';
        }

        $ratio = max(0, (float)$value);
        $percent = round($ratio * 100, 4);

        if ($ratio >= 0.20) {
            $class = 'label label-danger';
        } elseif ($ratio >= 0.05) {
            $class = 'label label-warning';
        } else {
            $class = 'label label-success';
        }

        return '<span class="'.$class.'">'.$percent.'%</span>';
    }

/**
 * Handle `formatGaleraQueue`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $value Input value for `value`.
 * @phpstan-param mixed $value
 * @psalm-param mixed $value
 * @return string Returned value for formatGaleraQueue.
 * @phpstan-return string
 * @psalm-return string
 * @example formatGaleraQueue(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function formatGaleraQueue($value): string
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return 'n/a';
        }

        $queue = max(0, (float)$value);

        if ($queue >= 5) {
            $class = 'label label-danger';
        } elseif ($queue > 0) {
            $class = 'label label-warning';
        } else {
            $class = 'label label-success';
        }

        return '<span class="'.$class.'">'.number_format($queue, 2).'</span>';
    }

/**
 * Handle `formatGaleraQueuePressure`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $flowPaused Input value for `flowPaused`.
 * @phpstan-param mixed $flowPaused
 * @psalm-param mixed $flowPaused
 * @param mixed $recvQueue Input value for `recvQueue`.
 * @phpstan-param mixed $recvQueue
 * @psalm-param mixed $recvQueue
 * @param mixed $sendQueue Input value for `sendQueue`.
 * @phpstan-param mixed $sendQueue
 * @psalm-param mixed $sendQueue
 * @return string Returned value for formatGaleraQueuePressure.
 * @phpstan-return string
 * @psalm-return string
 * @example formatGaleraQueuePressure(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function formatGaleraQueuePressure($flowPaused, $recvQueue, $sendQueue): string
    {
        $paused = (is_numeric($flowPaused) ? (float)$flowPaused : 0.0);
        $recv = (is_numeric($recvQueue) ? (float)$recvQueue : 0.0);
        $send = (is_numeric($sendQueue) ? (float)$sendQueue : 0.0);

        $score = $paused + ($recv / 10) + ($send / 10);

        if ($score >= 0.50) {
            return '<span class="label label-danger">HIGH</span>';
        }
        if ($score >= 0.10) {
            return '<span class="label label-warning">MEDIUM</span>';
        }

        return '<span class="label label-success">LOW</span>';
    }

/**
 * Handle `parseWsrepProviderOptions`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param string $raw Input value for `raw`.
 * @phpstan-param string $raw
 * @psalm-param string $raw
 * @return array Returned value for parseWsrepProviderOptions.
 * @phpstan-return array
 * @psalm-return array
 * @example parseWsrepProviderOptions(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function parseWsrepProviderOptions(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }

        $result = [];
        $parts = preg_split('/\s*;\s*/', $raw, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($parts as $part) {
            $eqPos = strpos($part, '=');
            if ($eqPos === false) {
                continue;
            }

            $key = trim(substr($part, 0, $eqPos));
            $value = trim(substr($part, $eqPos + 1));

            if ($key === '') {
                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }

/**
 * Handle `maskGaleraSecret`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param string $value Input value for `value`.
 * @phpstan-param string $value
 * @psalm-param string $value
 * @return string Returned value for maskGaleraSecret.
 * @phpstan-return string
 * @psalm-return string
 * @example maskGaleraSecret(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function maskGaleraSecret(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return 'n/a';
        }

        if (strpos($value, ':') !== false) {
            [$user] = explode(':', $value, 2);
            return $user.':********';
        }

        return '********';
    }

/**
 * Handle `formatOsWithIcon`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $os Input value for `os`.
 * @phpstan-param mixed $os
 * @psalm-param mixed $os
 * @param mixed $distributor Input value for `distributor`.
 * @phpstan-param mixed $distributor
 * @psalm-param mixed $distributor
 * @return string Returned value for formatOsWithIcon.
 * @phpstan-return string
 * @psalm-return string
 * @example formatOsWithIcon(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function formatOsWithIcon($os, $distributor): string
    {
        $osLabel = (string)($os ?? 'n/a');

        if (empty($distributor)) {
            return $osLabel;
        }

        $icon = '<img src="'.IMG.'/os/'.strtolower((string)$distributor).'.png" alt="['.(string)$distributor.']" title="'.(string)$distributor.'" style="width:16px;height:16px;vertical-align:middle;"> ';

        return $icon.$osLabel;
    }

/**
 * Handle `formatArchWithBitLabel`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $arch Input value for `arch`.
 * @phpstan-param mixed $arch
 * @psalm-param mixed $arch
 * @return string Returned value for formatArchWithBitLabel.
 * @phpstan-return string
 * @psalm-return string
 * @example formatArchWithBitLabel(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function formatArchWithBitLabel($arch): string
    {
        if ($arch === null || $arch === '') {
            return 'n/a';
        }

        $archStr = mb_strtolower((string) $arch, 'UTF-8');
        $bits = self::detectArchitectureBits($archStr);

        if ($bits === null) {
            return $archStr;
        }

        return $arch.' <span class="label label-default">'.$bits.' BIT</span>';
        //return $archStr.' ('.$bits.' BIT)';
    }

/**
 * Handle `detectArchitectureBits`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param string $arch Input value for `arch`.
 * @phpstan-param string $arch
 * @psalm-param string $arch
 * @return ?int Returned value for detectArchitectureBits.
 * @phpstan-return ?int
 * @psalm-return ?int
 * @example detectArchitectureBits(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function detectArchitectureBits(string $arch): ?int
    {
        $patterns64 = [
            'x86_64', 'amd64', 'aarch64', 'arm64', 'ppc64', 's390x',
            'riscv64', 'sparc64', 'mips64', 'ia64',
        ];

        foreach ($patterns64 as $p) {
            if (str_contains($arch, $p)) {
                return 64;
            }
        }

        $patterns32 = [
            'i386', 'i486', 'i586', 'i686', 'x86', 'armv7', 'armv6',
            'armv5', 'armhf', 'armel', 'ppc', 'mips', 's390',
        ];

        foreach ($patterns32 as $p) {
            if (str_contains($arch, $p)) {
                return 32;
            }
        }

        if (preg_match('/\b64\b/', $arch)) {
            return 64;
        }

        if (preg_match('/\b32\b/', $arch)) {
            return 32;
        }

        return null;
    }

/**
 * Handle `formatUsageFromBytes`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param string $label Input value for `label`.
 * @phpstan-param string $label
 * @psalm-param string $label
 * @param string $metric Input value for `metric`.
 * @phpstan-param string $metric
 * @psalm-param string $metric
 * @param mixed $usedBytes Input value for `usedBytes`.
 * @phpstan-param mixed $usedBytes
 * @psalm-param mixed $usedBytes
 * @param mixed $totalBytes Input value for `totalBytes`.
 * @phpstan-param mixed $totalBytes
 * @psalm-param mixed $totalBytes
 * @return array Returned value for formatUsageFromBytes.
 * @phpstan-return array
 * @psalm-return array
 * @example formatUsageFromBytes(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function formatUsageFromBytes(string $label, string $metric, $usedBytes, $totalBytes): array
    {
        $used = is_numeric($usedBytes) ? (float) $usedBytes : 0;
        $total = is_numeric($totalBytes) ? (float) $totalBytes : 0;

        if ($total <= 0) {
            $usageColor = self::getUsageColorByPercent(0);
            return [
                'type' => 'usage_meter',
                'metric' => $metric,
                'percent' => 0,
                'text' => 'n/a',
                'color' => $usageColor['color'],
                'level' => $usageColor['level'],
            ];
        }

        $percent = round(($used / $total) * 100, 2);
        $usageColor = self::getUsageColorByPercent($percent);

        return [
            'type' => 'usage_meter',
            'metric' => $metric,
            'percent' => $percent,
            'text' => number_format($percent, 2).'% ('
                .self::formatBytesHuman($used).' of '.self::formatBytesHuman($total).')',
            'color' => $usageColor['color'],
            'level' => $usageColor['level'],
        ];
    }

/**
 * Handle `resolveCpuUsageFromDetail`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $cpuDetail Input value for `cpuDetail`.
 * @phpstan-param mixed $cpuDetail
 * @psalm-param mixed $cpuDetail
 * @return mixed Returned value for resolveCpuUsageFromDetail.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @example resolveCpuUsageFromDetail(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function resolveCpuUsageFromDetail($cpuDetail)
    {
        if ($cpuDetail === null || $cpuDetail === '') {
            return null;
        }

        if (is_string($cpuDetail)) {
            $decoded = json_decode($cpuDetail, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $cpuDetail = $decoded;
            }
        }

        if (!is_array($cpuDetail)) {
            return null;
        }

        $values = [];
        foreach ($cpuDetail as $label => $value) {
            if (!is_string($label) || strpos($label, 'cpu') !== 0) {
                continue;
            }

            if (!is_numeric($value)) {
                continue;
            }

            if ($label === 'cpu') {
                return (float)$value;
            }

            if (preg_match('/^cpu\d+$/', $label)) {
                $values[] = (float)$value;
            }
        }

        if (empty($values)) {
            return null;
        }

        return array_sum($values) / count($values);
    }

    /**
     * Fonction réutilisable pour mapper un pourcentage d'utilisation vers une couleur/état.
     *
     * Règles:
     *  - 0%   -> 79.99% : vert (normal)
     *  - 80%  -> 89.99% : orange (élevé)
     *  - >=90%          : rouge (critique)
     */
    public static function getUsageColorByPercent(float $percent): array
    {
        if ($percent >= 90) {
            return ['color' => '#d9534f', 'level' => 'critical'];
        }

        if ($percent >= 80) {
            return ['color' => '#f0ad4e', 'level' => 'warning'];
        }

        return ['color' => '#5cb85c', 'level' => 'ok'];
    }

/**
 * Handle `tr`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param string $text Input value for `text`.
 * @phpstan-param string $text
 * @psalm-param string $text
 * @return string Returned value for tr.
 * @phpstan-return string
 * @psalm-return string
 * @example tr(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function tr(string $text): string
    {
        if (function_exists('_')) {
            return _( $text );
        }

        if (function_exists('__')) {
            return __( $text );
        }

        return $text;
    }


/**
 * Handle `lastRefresh`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for lastRefresh.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @example lastRefresh(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function lastRefresh($param)
    {
        Debug::parseDebug($param);


        $this->di['js']->addJavascript(array("vendor/masonry-layout-4.2.2.pkgd.min.js"));
        
        $this->di['js']->code_javascript('

            $(".grid").masonry({
                // options...
                itemSelector: ".grid-item",
                columnWidth: 3
            });

        ');



/**
 * Handle `human_time_diff_dec`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $date_start Input value for `date_start`.
 * @phpstan-param mixed $date_start
 * @psalm-param mixed $date_start
 * @param mixed $precision Input value for `precision`.
 * @phpstan-param mixed $precision
 * @psalm-param mixed $precision
 * @return mixed Returned value for human_time_diff_dec.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @example human_time_diff_dec(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
        function human_time_diff_dec($date_start, $precision = 1) {

            if (empty($date_start)) {
                return '0s';
            }

            $seconds = time() - strtotime($date_start);
            $seconds--;

            if ($seconds < 60) {
                return round($seconds, $precision) . 's';
            }

            $minutes = $seconds / 60;
            if ($minutes < 60) {
                return round($minutes, $precision) . 'm';
            }

            $hours = $minutes / 60;
            if ($hours < 24) {
                return round($hours, $precision) . 'h';
            }

            $days = $hours / 24;
            return round($days, $precision) . 'j';
        }


        $id_mysql_server = intval($param[0] ?? 0);

        if (empty($id_mysql_server)) {
            throw new \Exception("Missing id_mysql_server");
        }

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "
            SELECT 
                m.id,
                m.id_mysql_server,
                m.date,
                m.date_p1,
                m.date_p2,
                m.date_p3,
                m.date_p4,
                m.last_date_listener,
                f.file_name,
                f.each
            FROM ts_max_date m
            INNER JOIN ts_file f ON f.id = m.id_ts_file
            WHERE m.id_mysql_server = '".$db->sql_real_escape_string($id_mysql_server)."'
            AND m.date != m.date_p1
            ORDER BY m.date DESC
        ";

        $res = $db->sql_query($sql);
        $rows = array();

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            // ajout direct du time diff pour simplifier la vue
            $arr['diff_date']          = human_time_diff_dec($arr['date']);
            $arr['diff_date_p1']       = human_time_diff_dec($arr['date_p1']);
            $arr['diff_date_p2']       = human_time_diff_dec($arr['date_p2']);
            $arr['diff_date_p3']       = human_time_diff_dec($arr['date_p3']);
            $arr['diff_date_p4']       = human_time_diff_dec($arr['date_p4']);
            $arr['diff_last_listener'] = human_time_diff_dec($arr['last_date_listener']);

            $rows[] = $arr;
        }

        $data['rows'] = $rows;
        $data['id_mysql_server'] = $id_mysql_server;

        $this->set('data', $data);
        $this->set('title', "TS Refresh Status for Server #".$id_mysql_server);
        $this->set('id_mysql_server', $id_mysql_server);


    }

    public function runDetail($param)
    {
        $id_mysql_server = (int) ($param[0] ?? 0);
        $dateToken = (string) ($param[1] ?? '');
        $date = '';

        if ($id_mysql_server <= 0) {
            throw new \Exception("Missing id_mysql_server");
        }

        if (preg_match('/^\d{14}$/', $dateToken)) {
            $date = substr($dateToken, 0, 4) . '-'
                . substr($dateToken, 4, 2) . '-'
                . substr($dateToken, 6, 2) . ' '
                . substr($dateToken, 8, 2) . ':'
                . substr($dateToken, 10, 2) . ':'
                . substr($dateToken, 12, 2);
        } elseif (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', urldecode($dateToken))) {
            $date = urldecode($dateToken);
        }

        if ($date === '') {
            throw new \Exception("Invalid date format");
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $escapedDate = $db->sql_real_escape_string($date);

        $sqlServer = "SELECT id, display_name, name, ip, port
            FROM mysql_server
            WHERE id = " . $id_mysql_server . " LIMIT 1";
        $resServer = $db->sql_query($sqlServer);
        $server = $db->sql_fetch_array($resServer, MYSQLI_ASSOC);

        if (empty($server)) {
            throw new \Exception("Unknown mysql server");
        }

        $sqlFiles = "SELECT f.file_name
            FROM ts_date_by_server a
            INNER JOIN ts_file f ON f.id = a.id_ts_file
            WHERE a.id_mysql_server = " . $id_mysql_server . "
              AND a.date = '" . $escapedDate . "'
            ORDER BY f.file_name";
        $resFiles = $db->sql_query($sqlFiles);
        $files = [];
        while ($row = $db->sql_fetch_array($resFiles, MYSQLI_ASSOC)) {
            $files[] = $row['file_name'];
        }

        $queries = [];
        $definitions = [
            ['general', 'int', ''],
            ['general', 'double', ''],
            ['general', 'text', ''],
            ['general', 'json', ''],
            ['slave', 'int', 'a.connection_name'],
            ['slave', 'double', 'a.connection_name'],
            ['slave', 'text', 'a.connection_name'],
            ['slave', 'json', 'a.connection_name'],
            ['digest', 'int', 'CAST(a.id_ts_mysql_query AS CHAR)'],
            ['digest', 'double', 'CAST(a.id_ts_mysql_query AS CHAR)'],
            ['digest', 'text', 'CAST(a.id_ts_mysql_query AS CHAR)'],
            ['digest', 'json', 'CAST(a.id_ts_mysql_query AS CHAR)'],
        ];

        foreach ($definitions as $definition) {
            [$scopeType, $metricType, $scopeExpr] = $definition;

            $table = "ts_value_" . $scopeType . "_" . $metricType;
            $scopeSql = $scopeExpr === '' ? "''" : $scopeExpr;
            $digestJoin = $scopeType === 'digest'
                ? "LEFT JOIN ts_mysql_query q ON q.id = a.id_ts_mysql_query"
                : "";
            $digestTextSql = $scopeType === 'digest'
                ? "q.digest_text"
                : "''";

            $queries[] = "
                SELECT
                    '" . $scopeType . "' AS scope_type,
                    '" . strtoupper($metricType) . "' AS metric_type,
                    " . $scopeSql . " AS scope_name,
                    a.id_ts_variable,
                    f.file_name,
                    v.name AS metric_name,
                    v.`from` AS metric_source,
                    v.radical,
                    a.`date`,
                    CAST(a.`value` AS CHAR) AS metric_value,
                    " . $digestTextSql . " AS digest_text
                FROM " . $table . " a
                INNER JOIN ts_variable v ON v.id = a.id_ts_variable
                INNER JOIN ts_file f ON f.id = v.id_ts_file
                " . $digestJoin . "
                WHERE a.id_mysql_server = " . $id_mysql_server . "
                  AND a.`date` = '" . $escapedDate . "'
            ";
        }

        $sqlMetrics = implode("\nUNION ALL\n", $queries) . "\nORDER BY scope_type, scope_name, file_name, metric_name";
        $resMetrics = $db->sql_query($sqlMetrics);

        $sections = [];
        $totalMetrics = 0;
        while ($row = $db->sql_fetch_array($resMetrics, MYSQLI_ASSOC)) {
            $scopeType = $row['scope_type'];
            $scopeName = trim((string) ($row['scope_name'] ?? ''));

            if ($scopeType === 'general') {
                $groupKey = 'General';
                $subgroupKey = (string) $row['file_name'];
            } elseif ($scopeType === 'slave') {
                $groupKey = 'Slave';
                $subgroupKey = $scopeName !== '' ? $scopeName : 'default';
            } else {
                $groupKey = 'Digest';
                $subgroupKey = $scopeName !== '' ? ('query #' . $scopeName) : 'query';
            }

            $sections[$groupKey][$subgroupKey][] = $row;
            $totalMetrics++;
        }

        $data = [
            'server' => $server,
            'date' => $date,
            'files' => $files,
            'sections' => $sections,
            'total_metrics' => $totalMetrics,
        ];

        $this->set('data', $data);
        $this->set('title', 'Run Detail ' . $server['display_name'] . ' @ ' . $date);
    }


    
/**
 * Handle `refresh`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for refresh.
 * @phpstan-return void
 * @psalm-return void
 * @throws \Throwable When the underlying operation fails.
 * @example refresh(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function refresh($param)
    {
        if (empty($param[0]) || !ctype_digit((string)$param[0])) {
            throw new \Exception("Usage: /MysqlServer/refresh/{id_mysql_server}");
        }

        $id_mysql_server = (int)$param[0];

        $dir = TMP."md5";
        $pattern = $dir . "/*::" . $id_mysql_server . ".md5";

        $deleted = 0;
        foreach (glob($pattern) as $file) {

            if (@unlink($file)) {
                $deleted++;
            }
        }

        // Mode CLI
        if (defined("IS_CLI") && IS_CLI === true) {
            echo "Cleared {$deleted} cache files for server {$id_mysql_server}\n";
            return;
        }

        // Mode WEB : retour page précédente (fallback sur dashboard)
        $back = $_SERVER['HTTP_REFERER'] ?? "/serverdashboard/main/{$id_mysql_server}";
        header("Location: " . $back);
        exit;
    }

/**
 * Handle `refreshMetric`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for refreshMetric.
 * @phpstan-return void
 * @psalm-return void
 * @throws \Throwable When the underlying operation fails.
 * @example refreshMetric(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function refreshMetric($param)
    {
        if (empty($param[0]) || !ctype_digit((string)$param[0]) || empty($param[1])) {
            throw new \Exception("Usage: /MysqlServer/refreshMetric/{id_mysql_server}/{ts_file}");
        }

        $id_mysql_server = (int) $param[0];
        $ts_file = urldecode((string) $param[1]);

        if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $ts_file)) {
            throw new \Exception("Invalid metric file name");
        }

        $file = TMP."md5/".$ts_file."::".$id_mysql_server.".md5";
        $deleted = 0;

        if (is_file($file) && @unlink($file)) {
            $deleted = 1;
        }

        if (defined("IS_CLI") && IS_CLI === true) {
            echo "Cleared {$deleted} cache file for metric {$ts_file} on server {$id_mysql_server}\n";
            return;
        }

        $back = $_SERVER['HTTP_REFERER'] ?? "/serverdashboard/main/{$id_mysql_server}";
        header("Location: " . $back);
        exit;
    }

/**
 * Toggle `toggleMonitored`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for toggleMonitored.
 * @phpstan-return void
 * @psalm-return void
 * @throws \Throwable When the underlying operation fails.
 * @example toggleMonitored(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function toggleMonitored($param)
    {
        if (empty($param[0]) || !ctype_digit((string)$param[0])) {
            throw new \Exception("Usage: /MysqlServer/toggleMonitored/{id_mysql_server}");
        }

        $id_mysql_server = (int)$param[0];
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "UPDATE mysql_server
            SET is_monitored = CASE WHEN is_monitored = 1 THEN 0 ELSE 1 END
            WHERE id = ".$id_mysql_server." LIMIT 1";
        $db->sql_query($sql);

        $back = $_SERVER['HTTP_REFERER'] ?? (LINK."MysqlServer/main/".$id_mysql_server."/pmacontrol");
        header("Location: " . $back);
        exit;
    }



/**
 * Retrieve `getAdminInformation`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for getAdminInformation.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @example getAdminInformation(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public static function getAdminInformation($param)
    {
        $id_mysql_server= $param[0];

        $credentials = self::getMysqlServerCredentials((int)$id_mysql_server);

        if (empty($credentials['ip']) || empty($credentials['port']) || empty($credentials['login'])) {
            return '';
        }

        return "mysql -A -P".$credentials['port']." -h ".$credentials['ip']." -u ".$credentials['login']." -p'".$credentials['password']."'";
    }

/**
 * Retrieve `getTryMysqlConnectionDebugCommand`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param int $id_mysql_server Input value for `id_mysql_server`.
 * @phpstan-param int $id_mysql_server
 * @psalm-param int $id_mysql_server
 * @return string Returned value for getTryMysqlConnectionDebugCommand.
 * @phpstan-return string
 * @psalm-return string
 * @example getTryMysqlConnectionDebugCommand(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public static function getTryMysqlConnectionDebugCommand(int $id_mysql_server): string
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $serverName = '';
        $refresh = 1;

        $sql = "SELECT name FROM mysql_server WHERE id=".(int)$id_mysql_server." LIMIT 1";
        $res = $db->sql_query($sql);
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $serverName = (string)($arr['name'] ?? '');
        }

        $sql2 = "SELECT b.refresh_time
        FROM worker_queue a
        INNER JOIN daemon_main b ON b.id = a.id_daemon_main
        WHERE a.`table`='mysql_server'
        LIMIT 1";
        $res2 = $db->sql_query($sql2);
        while ($arr2 = $db->sql_fetch_array($res2, MYSQLI_ASSOC)) {
            if (isset($arr2['refresh_time']) && is_numeric($arr2['refresh_time'])) {
                $refresh = max(1, (int)$arr2['refresh_time']);
            }
        }

        return "pmacontrol Aspirateur tryMysqlConnection ".$serverName." ".$id_mysql_server." ".$refresh." --debug";
    }

/**
 * Retrieve `getMysqlServerCredentials`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param int $id_mysql_server Input value for `id_mysql_server`.
 * @phpstan-param int $id_mysql_server
 * @psalm-param int $id_mysql_server
 * @return array Returned value for getMysqlServerCredentials.
 * @phpstan-return array
 * @psalm-return array
 * @example getMysqlServerCredentials(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function getMysqlServerCredentials(int $id_mysql_server): array
    {

        $db = Sgbd::sql(DB_DEFAULT);
        $data = [
            'ip' => null,
            'port' => null,
            'login' => null,
            'password' => null,
            'is_monitored' => null,
            'client_is_monitored' => 1,
            'effective_is_monitored' => 0,
            'is_proxy' => 0,
            'is_vip' => 0,
        ];

        $sql = "SELECT a.*, c.is_monitored AS client_is_monitored
        FROM mysql_server a
        LEFT JOIN client c ON c.id = a.id_client
        WHERE a.id=".(int)$id_mysql_server." LIMIT 1";
        $res = $db->sql_query($sql);
        while($arr = $db->sql_fetch_array($res , MYSQLI_ASSOC))
        {
            $data['ip'] = $arr['ip'] ?? null;
            $data['port'] = $arr['port'] ?? null;
            $data['login'] = $arr['login'] ?? null;
            $data['password'] = Crypt::decrypt($arr['passwd']);
            $data['is_monitored'] = $arr['is_monitored'] ?? null;
            $data['client_is_monitored'] = $arr['client_is_monitored'] ?? 1;
            $data['effective_is_monitored'] = (
                (string)($arr['is_monitored'] ?? '0') === '1'
                && (string)($arr['client_is_monitored'] ?? '1') === '1'
            ) ? 1 : 0;
            $data['is_proxy'] = $arr['is_proxy'] ?? 0;
            $data['is_vip'] = $arr['is_vip'] ?? 0;
        }

        return $data;
    }

}
