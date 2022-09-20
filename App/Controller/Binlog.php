<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use \App\Library\Debug;
use App\Library\Extraction;
use App\Library\Mysql;
use Glial\Security\Crypt\Crypt;
use App\Library\Display;
use \Glial\Sgbd\Sgbd;

class Binlog extends Controller
{

    use \App\Library\Filter;
    CONST DELAIS_DE_RETENTION = 172800; //48 heures en secondes
    CONST DIRECTORY_BACKUP    = '/data/backup/binlog';

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

                $db  = Sgbd::sql(DB_DEFAULT);
                $sql = "REPLACE INTO binlog_max (`id_mysql_server`, `size_max`, `number_file_max`) VALUES ('".$_POST['mysql_server']['id']."', '".$number."', '".$max_file_to_keep."')";

                $db->sql_query($sql);

                header('location: '.LINK.$this->getClass().'/index');
            }
        }
    }

    public function max()
    {

        $db = Sgbd::sql(DB_DEFAULT);

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
        $db              = Sgbd::sql(DB_DEFAULT);

        $this->layout_name = false;
        $this->view        = false;

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

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT a.*, b.libelle as organization,c.*, a.id as id_mysql_server, d.*
            FROM mysql_server a
            INNER JOIN client b ON a.id_client = b.id
            INNER JOIN environment c ON a.id_environment = c.id
            INNER JOIN binlog_max d on a.id = d.id_mysql_server";

        //$sql ="select 1;";

        $res = $db->sql_query($sql);

        $data['extra'] = array();
        $mysql_server  = array();

        $data['max_bin_log'] = array();

        $all_id_mysql_server = array();
        while ($arr                 = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {


            $all_id_mysql_server[] = $arr['id_mysql_server'];

            $db_remote = Mysql::getDbLink($arr['id_mysql_server']);

            $res2 = $db_remote->sql_query("show binary logs;");

            while ($arr2 = $db_remote->sql_fetch_array($res2, MYSQLI_ASSOC)) {




                $data['extra'][$arr['id_mysql_server']]['binary_logs']['file'][] = $arr2['Log_name'];
                $data['extra'][$arr['id_mysql_server']]['binary_logs']['size'][] = $arr2['File_size'];
            }


            $mysql_server[] = $arr['id_mysql_server'];

            $data['max_bin_log'][] = $arr;
        }




        $res = Extraction::extract(array("binlog::max_binlog_size"), $all_id_mysql_server);

        /* */




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

    public function backupAll($param)
    {

    }

    public function backup($param)
    {

    }

    public function backupServer($param)
    {
        Debug::parseDebug($param);
        $db = Sgbd::sql(DB_DEFAULT);

        $id_mysql_server = intval($param[0]);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT d.*
            FROM mysql_server a
            INNER JOIN binlog_backup d on a.id = d.id_mysql_server WHERE a.id = ".$id_mysql_server;

        $res = $db->sql_query($sql);

        //get available binlogs

        $last_binlog_file = array();
        $bin_backup       = array();

        $last_file = '';
        $last_id   = '';

        while ($ob = $db->sql_fetch_object($res)) {

            $bin_backup[$ob->logfile_name] = $ob->logfile_size;
            $last_file                     = $ob->logfile_name;
            $last_id                       = $ob->id;
        }


        if (!empty($last_file)) {
            $last_binlog_file[$last_file] = $last_id;
        }

        array_pop($bin_backup);
        Debug::debug($last_binlog_file, "last element");

        $result = Extraction::display(array("binlog::file_first", "binlog::file_last", "binlog::files", "binlog::sizes", "binlog::total_size", "binlog::nb_files"),
                array($id_mysql_server));

        $binarylogs = $result[$id_mysql_server][''];
        $bin_logs   = array_combine(json_decode($binarylogs['files'], true), json_decode($binarylogs['sizes'], true));

        Debug::debug($bin_backup, "Binlog Backuped");
        Debug::debug($bin_logs, "Binlog available");

        $bin_to_backup = array_diff_key($bin_logs, $bin_backup);

        Debug::debug($bin_to_backup, "Binlog to backup");

        if (count($bin_to_backup) > 0) {

            $sql = "SELECT * FROM mysql_server where id=".$id_mysql_server;
            $res = $db->sql_query($sql);

            $directory = self::DIRECTORY_BACKUP."/".$id_mysql_server."/";

            if (!file_exists($directory)) {
                $cmd = "mkdir -p ".$directory;

                shell_exec($cmd);
            }

            while ($server = $db->sql_fetch_object($res)) {
                foreach ($bin_to_backup as $file => $size) {

                    $password = Crypt::decrypt($server->passwd, CRYPT_KEY);

                    $cmd = "cd ".$directory." && mysqlbinlog -R --raw --host=".$server->ip." -u ".$server->login." -p".$password." ".$file;
                    Debug::debug($cmd);

                    $db->sql_close();

                    $gg = shell_exec($cmd);

                    if (empty($gg)) {

                        $db  = Sgbd::sql(DB_DEFAULT);
                        $bck = array();

                        if (!empty($last_binlog_file[$file])) {

                            $bck['binlog_backup']['id'] = $last_binlog_file[$file];
                        }

                        $bck['binlog_backup']['id_mysql_server'] = $id_mysql_server;
                        $bck['binlog_backup']['logfile_name']    = $file;
                        $bck['binlog_backup']['logfile_size']    = $size;
                        $bck['binlog_backup']['md5']             = md5_file($directory.$file);
                        $bck['binlog_backup']['date_backup']     = date('Y-m-d H:i:s');

                        $err = $db->sql_save($bck);

                        if (!$err) {

                            Debug::debug($db->sql_error(), "Error Insert / update");
                        }

                        if (!empty($last_binlog_file[$file])) {
                            Debug($bck, $bck);
                        }

                        $db->sql_close();
                    }
                }
            }
        }
    }

    public function purgeAll($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT a.id as id_mysql_server
            FROM mysql_server a
            INNER JOIN binlog_max d on a.id = d.id_mysql_server";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $this->purgeServer(array($ob->id_mysql_server));
        }
    }

    public function purgeServer($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = intval($param[0]);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT *
            FROM mysql_server a
            INNER JOIN binlog_max d on a.id = d.id_mysql_server WHERE a.id = ".$id_mysql_server;

        $res = $db->sql_query($sql);

        //only one server at once
        while ($ob = $db->sql_fetch_object($res)) {


            $result = Extraction::display(array("binlog::file_first", "binlog::file_last", "binlog::files", "binlog::sizes", "binlog::total_size", "binlog::nb_files"),
                    array($id_mysql_server));

            $binarylogs = $result[$id_mysql_server][''];
            $bin_logs   = array_combine(json_decode($binarylogs['files'], true), json_decode($binarylogs['sizes'], true));

            Debug::debug($binarylogs);

            $binlog_reverse = array_reverse($bin_logs, true);

            Debug::debug($binlog_reverse);

            $total_size = 0;

            $loop = 0;
            foreach ($binlog_reverse as $file => $size) {
                $total_size += $size;
                $loop++;

                if ($loop == 1) {
                    $file_previous = $file;
                    continue;
                }

                if ($total_size > $ob->size_max) {
                    $name_link = Mysql::getDbLink($db, $id_mysql_server);
                    $db_remote = Sgbd::sql($name_link);
                    $sql       = "PURGE BINARY LOGS TO '".$file_previous."';";
                    Debug::sql($sql);
                    $db_remote->sql_query($sql);
                    break;
                }

                $file_previous = $file;
            }
        }
    }

    public function liste($param)
    {

        Debug::parseDebug($param);

        $db     = Sgbd::sql(DB_DEFAULT);
        $result = Extraction::display(array("binlog::file_first", "binlog::file_last", "binlog::files", "binlog::sizes", "binlog::total_size", "binlog::nb_files",
                "variables::expire_logs_days"));

        $sql = "SELECT a.*, b.libelle as organization,c.*, d.*, a.id as id_mysql_server
            FROM mysql_server a
            INNER JOIN client b ON a.id_client = b.id
            INNER JOIN environment c ON a.id_environment = c.id
            LEFT JOIN binlog_max d on a.id = d.id_mysql_server
            WHERE 1 ".self::getFilter()."
            ORDER BY display_name";

        $res = $db->sql_query($sql);

        $data['server']   = array();
        $data['max_size'] = 0;

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

            if (!empty($result[$arr['id_mysql_server']][''])) {

                $arr['binlog'] = $result[$arr['id_mysql_server']][''];
                Debug::debug($arr['binlog']);
            }

            $data['server'][] = $arr;

            $arr['binlog']['total_size'] = $arr['binlog']['total_size'] ?? 0;

            if (!empty($arr['binlog'])) {
                if ($arr['binlog']['total_size'] > $data['max_size']) {
                    $data['max_size'] = $arr['binlog']['total_size'];
                }
            }
        }
        //Debug::debug($data);

        $this->set('data', $data);
    }

    public function getBinlog($param)
    {

    }

    public function binlog2sql($param)
    {

    }
}
//glyphicon glyphicon-list
