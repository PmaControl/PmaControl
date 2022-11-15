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

class Binlog extends Controller {

    use \App\Library\Filter;

    CONST DELAIS_DE_RETENTION = 172800; //48 heures en secondes
    CONST DIRECTORY_BACKUP = '/data/backup/binlog';

    public function index() {



        $data = array();
        $this->set('data', $data);
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
            INNER JOIN binlog_backup d on a.id = d.id_mysql_server WHERE a.id = " . $id_mysql_server;

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

    public function purgeServer($param) {
        Debug::parseDebug($param);

        $id_mysql_server = intval($param[0]);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT *
            FROM mysql_server a
            INNER JOIN binlog_max d on a.id = d.id_mysql_server WHERE a.id = " . $id_mysql_server;

        $res = $db->sql_query($sql);

        //only one server at once
        while ($ob = $db->sql_fetch_object($res)) {


            $result = Extraction::display(array("binlog::file_first", "binlog::file_last", "binlog::files", "binlog::sizes", "binlog::total_size", "binlog::nb_files"),
                            array($id_mysql_server));

            $binarylogs = $result[$id_mysql_server][''];
            $bin_logs = array_combine(json_decode($binarylogs['files'], true), json_decode($binarylogs['sizes'], true));

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
                    $sql = "PURGE BINARY LOGS TO '" . $file_previous . "';";
                    Debug::sql($sql);
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
        $result = Extraction::display(array("binlog::file_first", "binlog::file_last", "binlog::files", "binlog::sizes", "binlog::total_size", "binlog::nb_files",
                    "variables::expire_logs_days"));

        $sql = "SELECT a.*, b.libelle as organization,c.*, d.*, a.id as id_mysql_server
            FROM mysql_server a
            INNER JOIN client b ON a.id_client = b.id
            INNER JOIN environment c ON a.id_environment = c.id
            LEFT JOIN binlog_max d on a.id = d.id_mysql_server
            WHERE 1 " . self::getFilter() . "
            ORDER BY display_name";

        $res = $db->sql_query($sql);

        $data['server'] = array();
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

    /*
     * *************************** 3. row ***************************
      Connection_name: slave5
      Slave_SQL_State:
      Slave_IO_State: Waiting for master to send event
      Master_Host: 10.64.8.151
      Master_User: repl
      Master_Port: 3306
      Connect_Retry: 60
      Master_Log_File: mysql-bin.027049
      Read_Master_Log_Pos: 49045710
      Relay_Log_File: relay-bin-slave5.000002
      Relay_Log_Pos: 693
      Relay_Master_Log_File: mysql-bin.026842
      Slave_IO_Running: Yes
      Slave_SQL_Running: No
      Replicate_Do_DB: pc_natixisFlows,pc_statistics
      Replicate_Ignore_DB:
      Replicate_Do_Table:
      Replicate_Ignore_Table:
      Replicate_Wild_Do_Table:
      Replicate_Wild_Ignore_Table:
      Last_Errno: 1032
      Last_Error: Could not execute Update_rows_v1 event on table pc_statistics.hourlyStatsToProcess; Can't find record in 'hourlyStatsToProcess', Error_code: 1032; handler error HA_ERR_KEY_NOT_FOUND; the event's master log mysql-bin.026842, end_log_pos 632
      Skip_Counter: 0
      Exec_Master_Log_Pos: 398
      Relay_Log_Space: 80445217873
      Until_Condition: None
      Until_Log_File:
      Until_Log_Pos: 0
      Master_SSL_Allowed: No
      Master_SSL_CA_File:
      Master_SSL_CA_Path:
      Master_SSL_Cert:
      Master_SSL_Cipher:
      Master_SSL_Key:
      Seconds_Behind_Master: NULL
      Master_SSL_Verify_Server_Cert: No
      Last_IO_Errno: 0
      Last_IO_Error:
      Last_SQL_Errno: 1032
      Last_SQL_Error: Could not execute Update_rows_v1 event on table pc_statistics.hourlyStatsToProcess; Can't find record in 'hourlyStatsToProcess', Error_code: 1032; handler error HA_ERR_KEY_NOT_FOUND; the event's master log mysql-bin.026842, end_log_pos 632
      Replicate_Ignore_Server_Ids:
      Master_Server_Id: 161
      Master_SSL_Crl:
      Master_SSL_Crlpath:
      Using_Gtid: No
      Gtid_IO_Pos: 161-161-2253305835,0-4255000500-51149,111-111-7767533535,162-162-2198746
      Replicate_Do_Domain_Ids:
      Replicate_Ignore_Domain_Ids:
      Parallel_Mode: optimistic
      SQL_Delay: 0
      SQL_Remaining_Delay: NULL
      Slave_SQL_Running_State:
      Slave_DDL_Groups: 0
      Slave_Non_Transactional_Groups: 0
      Slave_Transactional_Groups: 4555839
      Retried_transactions: 0
      Max_relay_log_size: 1073741824
      Executed_log_entries: 21713146
      Slave_received_heartbeats: 0
      Slave_heartbeat_period: 30.000
      Gtid_Slave_Pos: 0-4216710495-187665,21-21-44860636,22-22-425773,31-31-125433409,111-111-7807187333,161-161-2253378057,162-162-2198746
      3 rows in set (0.000 sec)




     * 
     */

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
