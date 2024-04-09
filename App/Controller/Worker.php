<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use \App\Library\Debug;
use \App\Library\Log;
use \Glial\I18n\I18n;
use \Glial\Sgbd\Sgbd;

class Worker extends Controller
{

    public function index($param)
    {
        if (!empty($_GET['ajax']) && $_GET['ajax'] === "true") {
            $this->layout_name = false;
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $data['worker'] = array();

        $sql2 = "SELECT b.*,a.name,a.worker_path  FROM daemon_main a
            INNER JOIN daemon_worker b ON a.id = b.id_daemon_main
            ORDER BY a.id, b.pid";
        $res2 = $db->sql_query($sql2);
        while ($arr  = $db->sql_fetch_array($res2, MYSQLI_ASSOC)) {

            $pid_file        = TMP."lock/".$arr['worker_path']."/".$arr['pid'].".pid";
            $arr['pid_file'] = $pid_file;

            if (file_exists($pid_file)) {
                $arr['id_proxysql'] = file_get_contents($pid_file);
            } else {
                $arr['id_proxysql'] = "...";
            }

            $log_file = TMP."log/worker_".$arr['id_daemon_main']."_".$arr['id_daemon_main'].".log";
            if (file_exists($log_file)) {
                $arr['log']      = $log_file;
                $arr['filesize'] = filesize($log_file);
            } else {
                $arr['log']      = '';
                $arr['filesize'] = 0;
            }
            $data['worker'][] = $arr;
        }

        $this->set('data', $data);
    }




    public function run($param)
    {

        Debug::parseDebug($parma);

        $id_daemon_main = $param[0];
        $class_name = $param[1];
        $function_name = $param[2];

        $pid = getmypid();
        $this->logger->notice("[WORKER:$pid] Started new worker with pid : $pid");

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM daemon_main WHERE id=".$id_daemon_main.";";

        $res = $db->sql_query($sql);
        while ($ob  = $db->sql_fetch_object($res)) {
            $queue_key = intval($ob->queue_key);
        }

        $db->sql_close();

        $queue = msg_get_queue($queue_key);

        $msg_type     = NULL;
        $msg          = NULL;
        $max_msg_size = 512;

        $data        = array();
        $data['pid'] = $pid;
        $param['pid'] = $pid;
        

        if ($id_daemon_main == 11)
        {
            $this->keepConfigFile($param);
        }
        

        while (msg_receive($queue, 1, $msg_type, $max_msg_size, $msg)) {

            $this->keepConfigFile($param);

            $id_mysql_server = $msg->id;

            $data['id']        = $id_mysql_server;
            $data['microtime'] = microtime(true);

            $lock_file = TMP."lock/worker/".$id_mysql_server.".lock";

            $worker_pid = TMP."lock/worker/".$pid.".pid";

            $fp = fopen($lock_file, "w+");
            fwrite($fp, json_encode($data));
            fflush($fp);            // libère le contenu avant d'enlever le verrou
            fclose($fp);

            file_put_contents($worker_pid,$id_mysql_server);

            $this->logger->info("[WORKER:$pid] [@Start] process id_mysql_server:$msg->id");

            //do your business logic here and process this message!
            $this->tryMysqlConnection(array($msg->name, $msg->id));
            // if mysql connection is down, the worker will be down too and we have to restart one

            $this->logger->info("[WORKER:$pid] [@END] process id_mysql_server:$msg->id");

            if (file_exists($lock_file)) {
                unlink($lock_file);
            }

            // on vide le pid 
            //$waiting = __("Waiting...");
            file_put_contents($worker_pid,"Waiting...");

            //finally, reset our msg vars for when we loop and run again
            $msg_type = NULL;
            $msg      = NULL;
        }

        $this->logger->warning("We not wait waited next msg in queue ($queue_key)");

    }



    public function test($param)
    {

        Debug::parseDebug($param);

        Log::get()->warning("GGGGGGGGGGGGGG");
    }

}