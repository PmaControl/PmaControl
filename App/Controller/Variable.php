<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 *

 *
 *
 *  */

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use App\Library\Extraction2;
use App\Library\Debug;

class Variable extends Controller
{

    public function index($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        //$testArray = Extraction2::display(array("variables::is_proxysql"));

        // on retire les PROXY
        $servers = array();
        $list_server = "SELECT distinct ID FROM mysql_server WHERE is_proxy=0";

        if (!empty($_GET['id_mysql_server'])) {
            $list_server = intval($_GET['id_mysql_server']);
        }


        $variable = '';
        if (!empty($_GET['variable'])) {
            $variable = ' AND `variable_name` ="'.$_GET['variable'].'" ';
        }



        $sql = "with z as (select id_mysql_server,variable_name from global_variable FOR SYSTEM_TIME ALL WHERE id_mysql_server IN (".$list_server.")
        $variable
GROUP BY id_mysql_server,variable_name having count(1) > 1)
 SELECT a.id_mysql_server, a.variable_name, a.value,date(ROW_START) as date, DATE_FORMAT(ROW_START, '%H:%i:%s') as time, DATE_FORMAT(ROW_START, '%W') as day
 FROM global_variable FOR SYSTEM_TIME ALL a
 INNER JOIN z ON a.id_mysql_server=z.id_mysql_server and a.variable_name=z.variable_name
 order by a.ROW_START,a.id_mysql_server, a.variable_name;";

        Debug::sql($sql);

        $res              = $db->sql_query($sql);
        $data['variable'] = array();
        while ($arr              = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['variable'][] = $arr;
        }
        
        $this->set('data', $data);
    }
}