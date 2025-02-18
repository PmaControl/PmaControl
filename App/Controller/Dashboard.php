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
 
        $_GET['mysql_server']['id'] = $param[0] ?? 1;
        $_GET['ts_variable']['id'] = $param[1]  ?? 148;
        $_GET['date']['date'] = $param[2] ?? '';
        $_GET['date']['time'] = $param[3] ?? '';
        $limit = $param[4] ?? 10;
        
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
            //debug($arr);

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
}

        