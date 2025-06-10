<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use App\Library\EngineV4;
use \Glial\Synapse\Controller;
use \App\Library\Debug;
use \App\Library\Microsecond;
use \Glial\I18n\I18n;
use \Glial\Sgbd\Sgbd;
use App\Library\Extraction2;

class Dashboard extends Controller
{
    public function json($param)
    {
        $this->di['js']->addJavascript(array(
        'moment.js',
        'bootstrap-datetimepicker.min.js',
        ));

        $this->di['js']->code_javascript("
        $(document).ready(function() {
            $('.datepick').datetimepicker({
                format: 'YYYY-MM-DD',
                ignoreReadonly: true
            });
        });");

        $this->di['js']->code_javascript("
        $(document).ready(function() {
            $('#datetimepicker3').datetimepicker({
                 format: 'hh:mm:ss'
             });
        });");

        $now = date("Y-m-d H:i:s");
        $hourAgo = date("H:i:s", strtotime("-1 hour", strtotime($now)));

        $split_get = explode("/", $_GET["url"]);

        $time = "";
        if (count($split_get)> 4 )
        {
            $time = $split_get[4];
        }

        $output_array = [];
        preg_match('/^\d{2}\:\d{2}\:\d{2}$/', $time, $output_array);


        if (!empty($output_array[0])){
            $_GET['date']['time'] = $output_array[0];
        }
        else{
            $_GET['date']['time'] = $hourAgo;
        }

        $_GET['mysql_server']['id'] = $param[0] ?? 1;
        $_GET['ts_variable']['id'] = $param[1]  ?? 1323;  // better to map processlist there, the id can change and will change
        $_GET['date']['date'] = $param[2] ?? date('Y-m-d');
        
        $limit = $param[4] ?? 100;

        $db = Sgbd::sql(DB_DEFAULT);

        if ($_SERVER['REQUEST_METHOD'] === "POST")
        {
            header("location: ".LINK."Dashboard/json/".$_POST['mysql_server']['id']."/".$_POST['ts_variable']['id'].
            "/".$_POST['date']['date']."/".$_POST['date']['time']."/10");
            exit;
        }

        $sql = "select * from ts_value_general_json where id_mysql_server =".$_GET['mysql_server']['id']." and id_ts_variable= ".$_GET['ts_variable']['id']." and date > '".$_GET['date']['date']." ".$_GET['date']['time']."' limit ".$limit."";
        //$sql = "select * from ts_value_general_json where id_mysql_server =19 and id_ts_variable= 1323 and date > '2025-02-06 01:20:00' limit 10";
        //debug($sql);
        $res = $db->sql_query($sql);
        $data = array();

        $data['json'] = array();
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC))
        {
            //debug(json_encode(json_decode($arr['value'], true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));   

            $tmp = array();
            $tmp['date'] = $arr['date'];
            
            $json = json_decode($arr['value'], true);

            // Vérifier si c'est un objet unique ou un tableau
            if (!isset($json[0])) {
                $json = [$json]; // Convertir l'objet unique en tableau
            }

            $tmp['value'] = $json;


            $data['json'][] = $tmp;
        }

        $this->set('data', $data);
    }


    public function hitRatio($param)
    {
        Debug::parseDebug($param);

        $data = Extraction2::display(array("innodb_buffer_pool_reads", "innodb_buffer_pool_read_requests", "aria_pagecache_reads","aria_pagecache_read_requests",
         "key_reads","key_read_requests", "hostname", "key_cache_size","aria_pagecache_buffer_size", "aria_sort_buffer_size", "aria_max_sort_file_size"));


        foreach($data as $id_mysql_server => $elem)
        {
            $data[$id_mysql_server]['ratio']['innodb'] = ($elem['innodb_buffer_pool_read_requests'] != 0)? round(100 * (1-($elem['innodb_buffer_pool_reads']/ $elem['innodb_buffer_pool_read_requests'])),2): null;
            $data[$id_mysql_server]['ratio']['aria'] = ($elem['aria_pagecache_read_requests'] != 0)? round(100 * (1-($elem['aria_pagecache_reads']/ $elem['aria_pagecache_read_requests'])),2): null;
            $data[$id_mysql_server]['ratio']['myisam'] = ($elem['key_read_requests'] != 0)? round(100 * (1-($elem['key_reads']/ $elem['key_read_requests'])),2): null;

        }

        Debug::debug($data);
        
        $sql ="SELECT
  -- InnoDB cache hit ratio
  ROUND(
    100 * (
      1 - (
        SELECT VARIABLE_VALUE FROM information_schema.GLOBAL_STATUS WHERE VARIABLE_NAME = 'Innodb_buffer_pool_reads'
      ) / NULLIF(
        (SELECT VARIABLE_VALUE FROM information_schema.GLOBAL_STATUS WHERE VARIABLE_NAME = 'Innodb_buffer_pool_read_requests'), 0
      )
    ), 2
  ) AS InnoDB_Cache_Hit_Percentage,

  -- Aria cache hit ratio
  ROUND(
    100 * (
      1 - (
        SELECT VARIABLE_VALUE FROM information_schema.GLOBAL_STATUS WHERE VARIABLE_NAME = 'aria_pagecache_reads'
      ) / NULLIF(
        (SELECT VARIABLE_VALUE FROM information_schema.GLOBAL_STATUS WHERE VARIABLE_NAME = 'aria_pagecache_read_requests'), 0
      )
    ), 2
  ) AS Aria_Cache_Hit_Percentage,

  -- MyISAM cache hit ratio
  ROUND(
    100 * (
      1 - (
        SELECT VARIABLE_VALUE FROM information_schema.GLOBAL_STATUS WHERE VARIABLE_NAME = 'Key_reads'
      ) / NULLIF(
        (SELECT VARIABLE_VALUE FROM information_schema.GLOBAL_STATUS WHERE VARIABLE_NAME = 'Key_read_requests'), 0
      )
    ), 2
  ) AS MyISAM_Cache_Hit_Percentage;";

    }


    public function ratioTable($param)
    {
        Debug::parseDebug($param);

        $data = Extraction2::display(array("open_table_definitions", "table_definition_cache", "opened_table_definitions"));

        foreach($data as $id_mysql_server => $elem)
        {
            $data[$id_mysql_server]['ratio'] = ($elem['table_definition_cache'] != 0)? round(100 * (1-($elem['open_table_definitions']/ $elem['table_definition_cache'])),2): null;
        }
        Debug::debug($data);
    }

    public function ratioLockTable($param)
    {
        Debug::parseDebug($param);

        $data = Extraction2::display(array("table_locks_immediate", "table_locks_waited"));

        foreach($data as $id_mysql_server => $elem)
        {
            $data[$id_mysql_server]['ratio'] = round(100 * ($elem['table_locks_immediate']/( $elem['table_locks_waited']+ $elem['table_locks_immediate'])),2);
        }
        Debug::debug($data);
    }


    public function ratioThreadCache($param)
    {
        Debug::parseDebug($param);

        $data = Extraction2::display(array("threads_cached", "thread_cache_size", "threads_created"));

        foreach($data as $id_mysql_server => $elem)
        {
            $data[$id_mysql_server]['ratio'] = round(100 * ($elem['threads_cached']/( $elem['thread_cache_size'])),2);
        }
        Debug::debug($data);
    }


}

        