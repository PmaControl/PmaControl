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

            if ($db->checkVersion(array('MySQL' => '8.0')))
            {
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


            }
            else
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
            }

            $res = $db->sql_query($sql);
            
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
            "innodb_flush_log_at_trx_commit",
            "log_bin","binlog_format","binlog_row_image","binlog_nb_files","binlog_total_size","binlog_expire_logs_seconds",
            "wsrep_cluster_status","wsrep_ready","wsrep_local_state_comment",
            "ssl_version","ssl_cipher","ssl_server_not_before","ssl_server_not_after",
            "hostname","os","kernel","arch","cpu_thread_count","cpu_usage","memory_total","memory_used","swap_total","swap_used",
            "buffer_pool_size","buffer_pool_bytes_data","buffer_pool_pages_total","buffer_pool_pages_free","buffer_pool_read_requests","buffer_pool_reads",
            "disks","ips","processlist",
        ];

        $raw = Extraction2::display($keys, [$id_mysql_server]);
        Debug::debug($raw);

        // On prend la première ligne
        $row = reset($raw);

        // Helpers simples
        $g = function($k) use ($row) { return $row[$k] ?? null; };
        $metric = function($k) use ($row) {
            return isset($row[$k]['count']) ? $row[$k]['count'] : null;
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

        $data = [];
        $data['id_mysql_server'] = $id_mysql_server;

        $data['summary'] = [
            'Server' => Display::srv($id_mysql_server),
            'Version' => $g('version'),
            'Commentaire' => $g('version_comment'),
            'Uptime' => $uptime_h,
            'Cmd' => self::getAdminInformation([$id_mysql_server])
        ];

        $data['connections'] = [
            'Threads connected' => $g('threads_connected'),
            'Threads running' => $g('threads_running'),
            'Max used' => $g('max_used_connections')." / ".$g('max_connections'),
            'Utilisation %' => $conn_usage !== null ? $conn_usage."%" : 'n/a',
            'Aborted clients' => $g('aborted_clients'),
            'Aborted connects' => $g('aborted_connects'),
        ];

        $data['innodb'] = [
            'Buffer pool size' => $bp_size,
            'Buffer data bytes' => $bp_data,
            'Buffer Used %' => $bp_used_pct !== null ? $bp_used_pct.'%' : 'n/a',
            'BP Hit Ratio %' => $hit !== null ? $hit.'%' : 'n/a',
            'FLUSH_LOG_AT_TRX_COMMIT' => $g('innodb_flush_log_at_trx_commit'),
        ];

        $data['binlog'] = [
            'log_bin' => $g('log_bin'),
            'binlog_format' => $g('binlog_format'),
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
            'Hostname' => $g('hostname'),
            'OS' => $g('os'),
            'Kernel' => $g('kernel'),
            'Arch' => $g('arch'),
            'CPU usage %' => $g('cpu_usage'),
            'CPU threads' => $g('cpu_thread_count'),
            'Mem used / total' => $g('memory_used')." / ".$g('memory_total'),
            'Swap used / total' => $g('swap_used')." / ".$g('swap_total'),
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



    public static function getAdminInformation($param)
    {
        $id_mysql_server= $param[0];

        $db = Sgbd::sql(DB_DEFAULT);

        $data = [];

        $sql = "SELECT * FROM mysql_server where id=".(int)$id_mysql_server;
        $res = $db->sql_query($sql);
        while($arr = $db->sql_fetch_array($res , MYSQLI_ASSOC))
        {
            $password = Crypt::decrypt($arr['passwd']);

            $data['cmd'] = "mysql -A -P".$arr['port']." -h ".$arr['ip']." -u ".$arr['login']." -p'".$password."'";
        }

        return $data['cmd'];
    }

}