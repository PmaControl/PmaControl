<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use \App\Library\Mysql;
use App\Library\Extraction;
use App\Library\Extraction2;

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



}