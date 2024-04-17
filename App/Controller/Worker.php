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





    public function run($param)
    {

        $pid = getmypid();
        $this->logger->notice("[WORKER:$pid] Started new worker with pid : $pid");

        //get mypid
        //start worker => pid / id_mysql_server

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM daemon_main WHERE id=11;";

        $res = $db->sql_query($sql);
        while ($ob  = $db->sql_fetch_object($res)) {
            $queue_key = intval($ob->queue_key);
        }

        $db->sql_close();

        

        $msg_type     = NULL;
        $msg          = NULL;
        $max_msg_size = 512;

        $data        = array();
        $data['pid'] = $pid;
        $param['pid'] = $pid;
        

        $this->keepConfigFile($param);

        $queue = msg_get_queue($queue_key);
        while (msg_receive($queue, 1, $msg_type, $max_msg_size, $msg)) {

            $this->keepConfigFile($param);
            $id_mysql_server = $msg->id;

            $data['id']        = $id_mysql_server;
            $data['microtime'] = microtime(true);

            $lock_file = TMP."lock/worker/".$id_mysql_server.".lock";
            $worker_pid = TMP."lock/worker/".$pid.".pid";

            file_put_contents($lock_file, json_encode($data));
            file_put_contents($worker_pid,$id_mysql_server);

            $this->logger->info("[WORKER:$pid] [@Start] process id_mysql_server:$msg->id");

            //do your business logic here and process this message!
            $this->tryMysqlConnection(array($msg->name, $msg->id));
            // if mysql connection is down, the worker will be down too and we have to restart one

            $this->logger->info("[WORKER:$pid] [@END] process id_mysql_server:$msg->id");

            if (file_exists($lock_file)) {
                unlink($lock_file);
            }

            file_put_contents($worker_pid,"Waiting...");

            //finally, reset our msg vars for when we loop and run again
            $msg_type = NULL;
            $msg      = NULL;
        }

        $this->logger->warning("We not wait waited next msg in queue ($queue_key)");
    }

    public function keepWorker()
    {




    }


    public function test($param)
    {

        Debug::parseDebug($param);

        Log::get()->warning("GGGGGGGGGGGGGG");
    }

}