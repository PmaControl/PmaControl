<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use \App\Library\Extraction;
use \Glial\Sgbd\Sgbd;
use \App\Library\Debug;
use App\Library\Extraction2;

class Alert extends Controller
{
    var $to_check = array("wsrep_cluster_size", "wsrep_cluster_name", "wsrep_on");

    public function check($date, $id_servers)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $res = Extraction($this->to_check, $id_servers, $date);

        while ($ob = $db->sql_fetch_object($res)) {

        }
    }

    public function reboot($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $id_mysql_server = $param[0];

        $sql = "SELECT b.date, b.date_p1, b.date_p2, b.date_p3, b.date_p4 FROM `ts_variable` a
            INNER JOIN `ts_max_date` b ON a.`id_ts_file` = b.`id_ts_file`
            WHERE a.name='uptime' and a.`from` = 'status' and b.id_mysql_server=".$id_mysql_server.";
            ";

        $res = $db->sql_query($sql);

        $list_dates = array();
        while ($arr        = $db->sql_fetch_array($res, MYSQLI_NUM)) {

            Debug::debug($arr);
            foreach ($arr as $date) {
                $list_dates[] = $date;
            }
        }

        Debug::debug($list_dates, "DATES");

        Debug::sql($sql);

        $uptime = Extraction2::extract(array('status::Uptime'), array($id_mysql_server), $list_dates);

        Debug::debug($uptime, "UPTIME");

        //$sql = "SELECT * FROM ts_max_date WHERE id_mysql_server"=.$id_mysql_server;
        //while ($)
        //extract($var = array(), $server = array(), $date = "", $range = false, $graph = false) {
        //display($var = array(), $server = array(), array("")) {
    }
}