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

        $testArray = Extraction2::display(array("variables::is_proxysql"));

        // on retire les PROXY
        $servers = array();
        foreach ($testArray as $id_mysql_server => $data) {
            if ($data['is_proxysql'] === '0') {
                $servers[] = $id_mysql_server;
            }
        }
        $list_server = implode(',', $servers);

        if (!empty($_GET['id_mysql_server'])) {
            $list_server = intval($_GET['id_mysql_server']);
        }


        $variable = '';
        if (!empty($_GET['variable'])) {
            $variable = ' AND `variable` ="'.$_GET['variable'].'" ';
        }



        $sql = "with z as (select id_mysql_server,variable from mysql_variable FOR SYSTEM_TIME ALL WHERE id_mysql_server IN (".$list_server.")
        $variable
GROUP BY id_mysql_server,variable having count(1) > 1)
 SELECT a.id_mysql_server, a.variable, a.value,date(ROW_START) as date, DATE_FORMAT(ROW_START, '%H:%i-%s') as time, DATE_FORMAT(ROW_START, '%W') as day
 FROM mysql_variable FOR SYSTEM_TIME ALL a
 INNER JOIN z ON a.id_mysql_server=z.id_mysql_server and a.variable=z.variable
 order by a.ROW_START,a.id_mysql_server, a.variable;";

        Debug::sql($sql);

        $res              = $db->sql_query($sql);
        $data['variable'] = array();
        while ($arr              = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['variable'][] = $arr;
        }



        $this->set('data', $data);
    }
}