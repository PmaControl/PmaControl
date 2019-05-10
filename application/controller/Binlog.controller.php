<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;
use \App\Library\Debug;
use App\Library\Extraction;
use App\Library\Mysql;

class Binlog extends Controller
{

    public function index()
    {


        $data = array();
        $this->set('data', $data);
    }

    public function add()
    {
        $this->di['js']->addJavascript(array('Binlog/index.js'));

        //debug($_POST);

        if ($_SERVER['REQUEST_METHOD'] === "POST") {

            if (!empty($_POST['binlog_max']['size'])) {

                preg_match('/[kmgKMG]$/', $_POST['binlog_max']['size'], $output_array);

                if (!empty($output_array[0])) {

                    $number = substr($_POST['binlog_max']['size'], 0, -1);

                    switch (strtolower($output_array[0])) {
                        case 'g':
                            $number *= 1024 * 1024 * 1024;
                            break;
                        case 'm':
                            $number *= 1024 * 1024;
                            break;
                        case 'k':
                            $number *= 1024;
                            break;
                    }
                }

                $max_file_to_keep = ceil($number / $_POST['variables']['file_binlog_size']);


                $db  = $this->di['db']->sql(DB_DEFAULT);
                $sql = "REPLACE INTO binlog_max (`id_mysql_server`, `size_max`, `number_file_max`) VALUES ('".$_POST['mysql_server']['id']."', '".$number."', '".$max_file_to_keep."')";

                $db->sql_query($sql);
            }
        }
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
        $db              = $this->di['db']->sql(DB_DEFAULT);

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

    public function view($param)
    {

        Debug::parseDebug($param);

        $db = $this->di['db']->sql(DB_DEFAULT);


        $sql = "SELECT a.*, b.libelle as organization,c.*, a.id as id_mysql_server, d.*
            FROM mysql_server a
            INNER JOIN binlog_max d on a.id = d.id_mysql_server
            INNER JOIN client b ON a.id_client = b.id
            INNER JOIN environment c ON a.id_environment = c.id";


        $res = $db->sql_query($sql);


        $data['extra'] = array();
        $mysql_server  = array();
        while ($arr           = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {


            $remote    = Mysql::getDbLink($db, $arr['id_mysql_server']);
            $db_remote = $this->di['db']->sql($remote);


            $res2 = $db_remote->sql_query("show binary logs;");

            while ($arr2 = $db_remote->sql_fetch_array($res2, MYSQLI_ASSOC)) {




                $data['extra'][$arr['id_mysql_server']]['binary_logs']['file'][] = $arr2['Log_name'];
                $data['extra'][$arr['id_mysql_server']]['binary_logs']['size'][] = $arr2['File_size'];
            }


            $mysql_server[] = $arr['id_mysql_server'];

            $data['max_bin_log'][] = $arr;
        }





        Extraction::setDb($db);
        $res = Extraction::extract(array("variables::max_binlog_size"), $mysql_server);


        while ($ob = $db->sql_fetch_object($res)) {


            $data['extra'][$ob->id_mysql_server]['max_binlog_size'] = $ob->value;
        }

        //debug($data);


        $this->set('data', $data);
    }

    public function search($param)
    {
        
    }

    public function backup($param)
    {

    }
}
//glyphicon glyphicon-list
