<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;
use \App\Library\Debug;
use App\Library\Extraction;

class Binlog extends Controller
{

    public function index()
    {
        $data = array();
        $this->set('data', $data);
    }

    public function add()
    {
        $sql = "INSSERT INTO ";
    }

    public function max()
    {

        $db = $this->di['db']->sql(DB_DEFAULT);


        $sql = "SELECT * FROM binlog_max a
            INNER JOIN mysql_server b ON b.id = a.id_mysql_server";

        $res = $db->sql_query($sql);


        $data = array();

        while ($arr = $db->sql_fetch_object($res)) {
            $data['binlog'] = $arr;
        }
    }

    public function getMaxBinlogSize($param)
    {

        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $db = $this->di['db']->sql(DB_DEFAULT);

        $this->layout_name = false;
        $this->view        = false;

        Extraction::setDb($db);
        $res = Extraction::extract(array("variables::max_binlog_size"), array($id_mysql_server));


        $data = array();
        while ($ob   = $db->sql_fetch_object($res)) {
            $data['max_binlog_size'] = $ob->value;
        }


        if (!empty($data['max_binlog_size'])) {
            echo $data['max_binlog_size'];
        } else {
            echo "N/A";
        }
    }
}
//glyphicon glyphicon-list
