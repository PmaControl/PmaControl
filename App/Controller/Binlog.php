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
use App\Library\Extraction2;
use App\Library\Mysql;
use Glial\Security\Crypt\Crypt;
use App\Library\Display;
use \Glial\Sgbd\Sgbd;
use \Glial\I18n\I18n;

use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;


class Binlog extends Controller {

    use \App\Library\Filter;

    CONST DELAIS_DE_RETENTION = 172800; //48 heures en secondes
    CONST DIRECTORY_BACKUP = '/data/backup/binlog';

    var $logger;

    public function index() {
        $data = array();
        $this->set('data', $data);
    }

    public function before($param)
    {
        $monolog       = new Logger("Binlog");
        $handler      = new StreamHandler(LOG_FILE, Logger::NOTICE);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $monolog->pushHandler($handler);
        $this->logger = $monolog;
    }

    public function add() {
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
                else{
                    $number = intval($_POST['binlog_max']['size']);
                }

                if ($number < $_POST['variables']['file_binlog_size'])
                {
                    $msg   = I18n::getTranslation(__("The size cannot be less than max_binlog_size (".$_POST['variables']['file_binlog_size']." < ".$number.")"));
                    $title = I18n::getTranslation(__("Error"));
                    set_flash("error", $title, $msg);

                    header('location: ' . LINK . $this->getClass() . '/index');
                    exit;
                }

                $max_file_to_keep = ceil($number / $_POST['variables']['file_binlog_size']);

                $db = Sgbd::sql(DB_DEFAULT);
                $sql = "REPLACE INTO binlog_max (`id_mysql_server`, `size_max`, `number_file_max`) VALUES ('" . $_POST['mysql_server']['id'] . "', '" . $number . "', '" . $max_file_to_keep . "')";

                $db->sql_query($sql);

                header('location: ' . LINK . $this->getClass() . '/index');
            }
        }
    }

    public function max() {

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM binlog_max a
            INNER JOIN mysql_server b ON b.id = a.id_mysql_server";

        $res = $db->sql_query($sql);

        $data = array();

        while ($arr = $db->sql_fetch_object($res)) {
            $data['binlog'] = $arr;
        }
    }

    public function getMaxBinlogSize($param) {

        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $db = Sgbd::sql(DB_DEFAULT);

        $this->layout_name = false;
        $this->view = false;

        $res = Extraction::extract(array("variables::max_binlog_size"), array($id_mysql_server));

        $data = array();
        while ($ob = $db->sql_fetch_object($res)) {
            $data['max_binlog_size'] = $ob->value;
        }

        if (!empty($data['max_binlog_size'])) {
            echo $data['max_binlog_size'];
        } else {
            echo "N/A";
        }
    }

    public function view($param) {

        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);


        // need to select only Available server
        $sql = "SELECT a.*, b.libelle as organization,c.*, a.id as id_mysql_server, d.*
            FROM mysql_server a
            INNER JOIN client b ON a.id_client = b.id
            INNER JOIN environment c ON a.id_environment = c.id
            INNER JOIN binlog_max d on a.id = d.id_mysql_server";

        //$sql ="select 1;";

        $res = $db->sql_query($sql);

        $data['extra'] = array();
        $mysql_server = array();

        $data['max_bin_log'] = array();

        $all_id_mysql_server = array();
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

            $all_id_mysql_server[] = $arr['id_mysql_server'];
            $binlog_files = Extraction2::display(array("mysql_binlog::binlog_files"), array($arr['id_mysql_server']));

            if (empty($binlog_files[$arr['id_mysql_server']]['binlog_files'])) {
                continue;
            }

            $data['extra'][$arr['id_mysql_server']]['binary_logs']['file'] = $binlog_files[$arr['id_mysql_server']]['binlog_files'];

            $binlog_sizes = Extraction2::display(array("mysql_binlog::binlog_sizes"), array($arr['id_mysql_server']));
            $data['extra'][$arr['id_mysql_server']]['binary_logs']['size'] = $binlog_sizes[$arr['id_mysql_server']]['binlog_sizes'];

            $mysql_server[] = $arr['id_mysql_server'];

            $data['max_bin_log'][] = $arr;
        }

        //$res = Extraction::extract(array("binlog::max_binlog_size"), $all_id_mysql_server);
        /* */

        $res = Extraction::extract(array("variables::max_binlog_size"), $mysql_server);

        while ($ob = $db->sql_fetch_object($res)) {
            $data['extra'][$ob->id_mysql_server]['max_binlog_size'] = $ob->value;
        }

        $this->set('data', $data);
    }

    public function search($param) {
        
    }

    public function backupAll($param) {
        
    }

    public function backup($param) {
        
    }

    public function backupServer($param) {
        Debug::parseDebug($param);
        $db = Sgbd::sql(DB_DEFAULT);

        $id_mysql_server = intval($param[0]);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT d.*
            FROM mysql_server a
            INNER JOIN binlog_backup d on a.id = d.id_mysql_server 
            WHERE a.id = " . $id_mysql_server;

        $res = $db->sql_query($sql);

        //get available binlogs

        $last_binlog_file = array();
        $bin_backup = array();

        $last_file = '';
        $last_id = '';

        while ($ob = $db->sql_fetch_object($res)) {

            $bin_backup[$ob->logfile_name] = $ob->logfile_size;
            $last_file = $ob->logfile_name;
            $last_id = $ob->id;
        }


        if (!empty($last_file)) {
            $last_binlog_file[$last_file] = $last_id;
        }

        array_pop($bin_backup);
        Debug::debug($last_binlog_file, "last element");

        $result = Extraction::display(array("binlog::file_first", "binlog::file_last", "binlog::files", "binlog::sizes", "binlog::total_size", "binlog::nb_files"),
                        array($id_mysql_server));

        $binarylogs = $result[$id_mysql_server][''];
        $bin_logs = array_combine(json_decode($binarylogs['files'], true), json_decode($binarylogs['sizes'], true));

        Debug::debug($bin_backup, "Binlog Backuped");
        Debug::debug($bin_logs, "Binlog available");

        $bin_to_backup = array_diff_key($bin_logs, $bin_backup);

        Debug::debug($bin_to_backup, "Binlog to backup");

        if (count($bin_to_backup) > 0) {

            $sql = "SELECT * FROM mysql_server where id=" . $id_mysql_server;
            $res = $db->sql_query($sql);

            $directory = self::DIRECTORY_BACKUP . "/" . $id_mysql_server . "/";

            if (!file_exists($directory)) {
                $cmd = "mkdir -p " . $directory;

                shell_exec($cmd);
            }

            while ($server = $db->sql_fetch_object($res)) {
                foreach ($bin_to_backup as $file => $size) {

                    $password = Crypt::decrypt($server->passwd, CRYPT_KEY);

                    $cmd = "cd " . $directory . " && mysqlbinlog -R --raw --host=" . $server->ip . " -u " . $server->login . " -p" . $password . " " . $file;
                    Debug::debug($cmd);

                    $db->sql_close();

                    $gg = shell_exec($cmd);

                    if (empty($gg)) {

                        $db = Sgbd::sql(DB_DEFAULT);
                        $bck = array();

                        if (!empty($last_binlog_file[$file])) {

                            $bck['binlog_backup']['id'] = $last_binlog_file[$file];
                        }

                        $bck['binlog_backup']['id_mysql_server'] = $id_mysql_server;
                        $bck['binlog_backup']['logfile_name'] = $file;
                        $bck['binlog_backup']['logfile_size'] = $size;
                        $bck['binlog_backup']['md5'] = md5_file($directory . $file);
                        $bck['binlog_backup']['date_backup'] = date('Y-m-d H:i:s');

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

    public function purgeAll($param) {
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

    /* Purge les binlog qui dépasse la valeur max pour être juste en dessous */


    public function purgeServer($param) {
        Debug::parseDebug($param);

        $id_mysql_server = intval($param[0]);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT d.*
            FROM mysql_server a
            INNER JOIN binlog_max d on a.id = d.id_mysql_server WHERE a.id = " . $id_mysql_server;

        $res = $db->sql_query($sql);

        //only one server at once
        while ($ob = $db->sql_fetch_object($res)) {

            $result = Extraction::display(array("mysql_binlog::binlog_file_first", "mysql_binlog::binlog_file_last", "mysql_binlog::binlog_files",
             "mysql_binlog::binlog_sizes", "mysql_binlog::binlog_total_size", "mysql_binlog::binlog_nb_files"),array($ob->id_mysql_server));

            Debug::debug($result);

            if (empty($result[$ob->id_mysql_server]['']))
            {
                break;
            }

            $binarylogs = $result[$ob->id_mysql_server][''];

            if (empty($binarylogs['binlog_files'])) {
                break;
            }
            if (empty($binarylogs['binlog_sizes'])){
                break;
            }

            $bin_logs = array_combine(json_decode($binarylogs['binlog_files'], true), json_decode($binarylogs['binlog_sizes'], true));

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
                    
                    $db_remote = Mysql::getDbLink($ob->id_mysql_server);
                    $sql = "PURGE BINARY LOGS TO '" . $file_previous . "';";
                    Debug::sql($sql);
                    $this->logger->notice('We purged binary logs on id_mysql_server:'.$ob->id_mysql_server.' "'.$sql.'"');
                    $db_remote->sql_query($sql);
                    break;
                }

                $file_previous = $file;
            }
        }
    }

    public function liste($param) {

        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);
        $result = Extraction::display(array("mysql_binlog::binlog_file_first", "mysql_binlog::binlog_file_last", 
        "mysql_binlog::binlog_files","variables::binlog_expire_logs_seconds",
         "mysql_binlog::binlog_sizes", "mysql_binlog::binlog_total_size", "mysql_binlog::binlog_nb_files", "variables::expire_logs_days"));

        $sql = "SELECT a.*, b.libelle as organization,c.*, d.*, a.id as id_mysql_server
            FROM mysql_server a
            INNER JOIN client b ON a.id_client = b.id
            INNER JOIN environment c ON a.id_environment = c.id
            LEFT JOIN binlog_max d on a.id = d.id_mysql_server
            WHERE 1 " . self::getFilter() . " AND a.is_proxy = 0
            ORDER BY display_name";

        //debug($sql);

        $res = $db->sql_query($sql);

        $data['server'] = array();
        $data['max_size'] = 0;

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

            if (!empty($result[$arr['id_mysql_server']][''])) {

                $arr['mysql_binlog'] = $result[$arr['id_mysql_server']][''];
                Debug::debug($arr['mysql_binlog']);
            }

            $data['server'][] = $arr;
            $arr['mysql_binlog']['total_size'] = $arr['mysql_binlog']['total_size'] ?? 0;
        
            if (!empty($arr['mysql_binlog']['binlog_total_size'])) {
                if ($arr['mysql_binlog']['binlog_total_size'] > $data['max_size']) {
                    $data['max_size'] = $arr['mysql_binlog']['binlog_total_size'];
                }
            }
        }

        $this->set('data', $data);
    }

/* récupére une portion de binlog directement depuis le serveur */

    public function getBinlog($param) {

        //
        //Exec_Master_Log_Pos 65557710
        //end_log_pos         65558192


        $id_mysql_server = $param[0];
        $binlog_file = $param[1];
        $binlog_position_start = $param[2];
        $binlog_position_end = $param[3];

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server WHERE id=" . $id_mysql_server . ";";

        $res = $db->sql_query($sql);

        Crypt::$key = CRYPT_KEY;
        $password = Crypt::decrypt($ob->passwd);

        while ($ob = $db->sql_fetch_object($res)) {
            $cmd = "mysqlbinlog -R -h " . $ob->ip . " -u " . $ob->login . " -p" . $password . " -j " . $binlog_position_start . " --stop-position=" . $binlog_position_end . " $binlog_file";
        }



        //
    }

    public function binlog2sql($param) {
        
    }

    public function getLastSqlError($param) {
        
    }

}

//glyphicon glyphicon-list
