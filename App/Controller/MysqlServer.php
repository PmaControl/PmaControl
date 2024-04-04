<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use \App\Library\Mysql;

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

        $this->di['js']->code_javascript('
        $(document).ready(function()
        {
            function refresh()
            {
                var myURL = GLIAL_LINK+GLIAL_URL+"ajax:true";
                $("#processlist").load(myURL);
            }

            var intervalId = window.setInterval(function(){
                // call your function here
                refresh()  
              }, 1000);

        })');


        $db = Mysql::getDbLink(  $id_mysql_server);

        $sql = "SELECT /* pmacontrol-processlist */
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

        $res = $db->sql_query($sql);
        $data['processlist'] = array();
        while($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC))
        {
            if (str_contains($arr['query'], '/* pmacontrol-processlist */')) {
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

        $this->set('param', $param);
        $this->set('data', $data);

    }
}