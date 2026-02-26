<?php

namespace App\Controller;

use App\Library\Display;
use Glial\Security\Crypt\Crypt;
use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use \App\Library\Mysql;
use \App\Library\Debug;
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
    private $table_exists_cache = array();

    private function informationSchemaTableExists($db, $schema, $table)
    {
        $cache_key = $db->host.":".$db->port.":".$schema.".".$table;
        if (isset($this->table_exists_cache[$cache_key])) {
            return $this->table_exists_cache[$cache_key];
        }

        $schema = $db->sql_real_escape_string($schema);
        $table  = $db->sql_real_escape_string($table);

        $sql = "SELECT 1 FROM information_schema.tables WHERE table_schema = '".$schema."' AND table_name = '".$table."' LIMIT 1";
        $res = $db->sql_query_silent($sql);
        if (!$res) {
            $this->table_exists_cache[$cache_key] = false;
            return false;
        }

        $row = $db->sql_fetch_array($res, MYSQLI_NUM);
        $exists = !empty($row);

        $this->table_exists_cache[$cache_key] = $exists;
        return $exists;
    }

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


    public function processlist($param)
    {
        $id_mysql_server = $param[0];
        $_GET['mysql_server']['id'] = $id_mysql_server;

        if (!empty($_GET['ajax']) && $_GET['ajax'] === "true"){
            $this->layout_name = false;
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

        foreach($id_mysql_servers as $id_mysql_server)
        {
            $db = Mysql::getDbLink(  $id_mysql_server);

            $has_innodb_trx = $this->informationSchemaTableExists($db, 'information_schema', 'innodb_trx');
            $has_perf_threads = $this->informationSchemaTableExists($db, 'performance_schema', 'threads');
            $has_info_processlist = $this->informationSchemaTableExists($db, 'information_schema', 'processlist');

            if ($db->checkVersion(array('MySQL' => '8.0')) && $has_perf_threads)
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
                        processlist_command != 'Daemon'
                        AND (processlist_command != 'Sleep' AND processlist_command NOT LIKE 'Binlog Dump%') 
                        AND (processlist_info IS NOT NULL OR trx_query IS NOT NULL) AND IFNULL(processlist_state, '') NOT LIKE 'Group Replication Module%'
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
                        processlist_command != 'Daemon'
                        AND (processlist_command != 'Sleep' AND processlist_command NOT LIKE 'Binlog Dump%') 
                        AND processlist_info IS NOT NULL AND IFNULL(processlist_state, '') NOT LIKE 'Group Replication Module%'
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
                    WHERE (command != 'Sleep' AND command NOT LIKE 'Binlog Dump%')
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
                    WHERE (command != 'Sleep' AND command NOT LIKE 'Binlog Dump%')
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

            if ($sql === "SHOW FULL PROCESSLIST") {
                while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                    $command = $arr['Command'] ?? $arr['COMMAND'] ?? '';
                    $info = $arr['Info'] ?? $arr['INFO'] ?? '';

                    if ($command === 'Sleep' || str_starts_with($command, 'Binlog Dump')) {
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
                if (empty($arr['query']) || str_contains($arr['query'], '/* pmacontrol-processlist */')) {
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

        //debug($data['processlist']);

        $time = $nb_server * 500;

        $this->di['js']->code_javascript('
        $(document).ready(function()
        {
            var intervalId;
            var refreshInterval = 1000; // Intervalle par défaut de 1 secondes

            function refresh()
            {
                var myURL = GLIAL_LINK+GLIAL_URL+"ajax:true";
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
            "log_bin","sync_binlog","binlog_format","binlog_row_image","binlog_checksum","binlog_cache_size","binlog_stmt_cache_size","max_binlog_size","binlog_space_limit","binlog_nb_files","binlog_total_size","binlog_expire_logs_seconds",
            "log_bin_basename","mysql_binlog::binlog_file_last","gtid_current_pos","gtid_binlog_pos",
            "wsrep_cluster_status","wsrep_ready","wsrep_local_state_comment",
            "ssl_version","ssl_cipher","ssl_server_not_before","ssl_server_not_after",
            "hostname","os","distributor","kernel","arch","cpu_thread_count","cpu_usage","memory_total","memory_used","swap_total","swap_used",
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
            "ssh_stats::disks","ips","processlist",
        ];

        $raw = Extraction2::display($keys, [$id_mysql_server]);
        Debug::debug($raw);
        //debug($raw);

        // On prend la première ligne
        $row = reset($raw);

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

        $ariaEnabled = $hasEnginesMetric
            ? self::hasSupportedEngine($supportedEngines, 'ARIA')
            : (
                is_numeric($g('aria_pagecache_buffer_size'))
                || $g('aria_recover') !== null
                || $g('aria_page_checksum') !== null
            );

        $rocksdbEnabled = $hasEnginesMetric
            ? self::hasSupportedEngine($supportedEngines, 'ROCKSDB')
            : (
                in_array(strtoupper((string)$g('have_rocksdb')), ['YES', 'ON', '1'], true)
                || strtolower((string)$g('default_storage_engine')) === 'rocksdb'
                || is_numeric($g('rocksdb_block_cache_size'))
            );

        $myisamEnabled = $hasEnginesMetric
            ? self::hasSupportedEngine($supportedEngines, 'MYISAM')
            : (
                in_array(strtoupper((string)$g('have_myisam')), ['YES', 'ON', '1'], true)
                || strtolower((string)$g('default_storage_engine')) === 'myisam'
                || is_numeric($g('key_buffer_size'))
            );

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

        $columnstoreEnabled = $hasEnginesMetric
            ? self::hasSupportedEngine($supportedEngines, 'COLUMNSTORE')
            : (
                in_array(strtoupper((string)$g('have_columnstore')), ['YES', 'ON', '1'], true)
                || strtolower((string)$g('default_storage_engine')) === 'columnstore'
                || !empty($g('columnstore_select_handler'))
                || !empty($g('columnstore_version'))
            );

        $spiderEnabled = $hasEnginesMetric
            ? self::hasSupportedEngine($supportedEngines, 'SPIDER')
            : (
                in_array(strtoupper((string)$value('spider_use_handler')), ['ON', 'YES', '1'], true)
                || strtolower((string)$g('default_storage_engine')) === 'spider'
            );

        if (!$spiderEnabled) {
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
        $credentials = self::getMysqlServerCredentials($id_mysql_server);

        $data['summary'] = [
            'Server' => Display::srv($id_mysql_server),
            'User' => $credentials['login'] ?? 'n/a',
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
            'Version' => $g('version'),
            'Commentaire' => $g('version_comment'),
            'Uptime' => $uptime_h,
            'Cmd' => self::getAdminInformation([$id_mysql_server])
        ];

        $data['connections'] = [
            'Threads running' => self::formatThreadUsage($g('threads_running'), $g('max_connections')),
            'Threads connected' => self::formatThreadUsage($g('threads_connected'), $g('max_connections')),
            'Max used' => self::formatConnectionUsage($g('max_used_connections'), $g('max_connections')),
            'Aborted clients' => $g('aborted_clients'),
            'Aborted connects' => $g('aborted_connects'),
        ];

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
        ];

        $data['wsrep'] = [
            'Cluster status' => $g('wsrep_cluster_status'),
            'Ready' => $g('wsrep_ready'),
            'State' => $g('wsrep_local_state_comment'),
        ];

        $data['ssl'] = [
            'Version' => $g('ssl_version'),
            'Cipher' => $g('ssl_cipher'),
            'Valid from' => $g('ssl_server_not_before'),
            'Valid to' => $g('ssl_server_not_after'),
        ];
        $data['os'] = [
            '<img height="16px" width="16px" src="'.IMG.'icon/hostname.svg" > Hostname' => $g('hostname'),
            '<img height="16px" width="16px" src="'.IMG.'icon/uptime.svg" > Uptime' => $uptime_h,
            '<img height="16px" width="16px" src="'.IMG.'icon/network.svg" > IPs' => self::formatIpsValue($g('ips')),
            '<img height="16px" width="16px" src="'.IMG.'icon/linux-svgrepo-com.svg" > OS' => self::formatOsWithIcon($g('os'), $g('distributor')),
            '<img height="16px" width="16px" src="'.IMG.'icon/kernel.svg" > Kernel' => $g('kernel'),
            '<img height="16px" width="16px" src="'.IMG.'icon/64bit.svg" > Arch' => self::formatArchWithBitLabel($g('arch')),
            '<img height="16px" width="16px" src="'.IMG.'icon/cpu.svg" > CPU Usage' => self::formatCpuUsage($g('cpu_usage'), $g('cpu_thread_count')),
            '<img height="16px" width="16px" src="'.IMG.'icon/ram.svg" > '.self::tr('RAM Usage') => self::formatRamUsage($g('memory_used'), $g('memory_total')),
            '<img height="16px" width="16px" src="'.IMG.'icon/swap.svg" > '.self::tr('SWAP Usage') => self::formatSwapUsage($g('swap_used'), $g('swap_total')),
        ];






        $data['disks'] = $g('disks');
        $data['ips'] = $g('ips');
        $data['processlist'] = $g('processlist');

        $this->set('data', $data);
        $this->set('param', $param);
   
    }



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

    private static function formatIpsValue($value): string
    {
        $ips = self::extractIpv4List($value);

        if (empty($ips)) {
            return self::formatJsonValue($value);
        }

        usort($ips, [self::class, 'compareIpv4']);

        return implode(' ', array_values($ips));
    }

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

    private static function hasSupportedEngine(array $engines, string $engine): bool
    {
        return !empty($engines[strtoupper($engine)]);
    }

    private static function formatRamUsage($usedBytes, $totalBytes): array
    {
        return self::formatUsageFromBytes(self::tr('RAM usage'), 'ram', $usedBytes, $totalBytes);
    }

    private static function formatSwapUsage($usedBytes, $totalBytes): array
    {
        return self::formatUsageFromBytes(self::tr('Swap usage'), 'swap', $usedBytes, $totalBytes);
    }

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

    private static function formatOsWithIcon($os, $distributor): string
    {
        $osLabel = (string)($os ?? 'n/a');

        if (empty($distributor)) {
            return $osLabel;
        }

        $icon = '<img src="'.IMG.'/os/'.strtolower((string)$distributor).'.png" alt="['.(string)$distributor.']" title="'.(string)$distributor.'" style="width:16px;height:16px;vertical-align:middle;"> ';

        return $icon.$osLabel;
    }

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


    public function lastRefresh($param)
    {
        Debug::parseDebug($param);


        $this->di['js']->addJavascript(array("https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"));
        
        $this->di['js']->code_javascript('

            $(".grid").masonry({
                // options...
                itemSelector: ".grid-item",
                columnWidth: 3
            });

        ');



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



    public static function getAdminInformation($param)
    {
        $id_mysql_server= $param[0];

        $credentials = self::getMysqlServerCredentials((int)$id_mysql_server);

        if (empty($credentials['ip']) || empty($credentials['port']) || empty($credentials['login'])) {
            return '';
        }

        return "mysql -A -P".$credentials['port']." -h ".$credentials['ip']." -u ".$credentials['login']." -p'".$credentials['password']."'";
    }

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

    private static function getMysqlServerCredentials(int $id_mysql_server): array
    {

        $db = Sgbd::sql(DB_DEFAULT);
        $data = [
            'ip' => null,
            'port' => null,
            'login' => null,
            'password' => null,
        ];

        $sql = "SELECT * FROM mysql_server where id=".(int)$id_mysql_server;
        $res = $db->sql_query($sql);
        while($arr = $db->sql_fetch_array($res , MYSQLI_ASSOC))
        {
            $data['ip'] = $arr['ip'] ?? null;
            $data['port'] = $arr['port'] ?? null;
            $data['login'] = $arr['login'] ?? null;
            $data['password'] = Crypt::decrypt($arr['passwd']);
        }

        return $data;
    }

}
