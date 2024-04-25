<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use App\Library\EngineV4;
use \Glial\Synapse\Controller;
use \App\Library\Debug;
use \App\Library\Log;
use \Glial\I18n\I18n;
use \Glial\Sgbd\Sgbd;
use \Glial\Synapse\FactoryController;

class Worker extends Controller
{

    public function run($param)
    {

        $id_worker_queue = $param[0];



        $pid = getmypid();
        $this->logger->notice("[WORKER:$pid] Started new worker with pid : $pid");

        //get mypid
        //start worker => pid / id_mysql_server

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM worker_queue WHERE id=".$id_worker_queue.";";

        $res = $db->sql_query($sql);
        while ($ob  = $db->sql_fetch_object($res, MYSQLI_ASSOC)) {
            $WORKER = $arr;
            $queue_key = intval($WORKER['queue_number']);
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
            
            $data['id']        = $msg->id;
            $data['microtime'] = microtime(true);

            $lock_file = EngineV4::getFileLock($WORKER['name'],$msg->id );
            $worker_pid = EngineV4::getFilePid($WORKER['name'],$msg->id );
            

            file_put_contents($lock_file, json_encode($data));
            file_put_contents($worker_pid,$msg->id);

            $this->logger->info("[WORKER:$pid] [@Start] process id_mysql_server:$msg->id");

            //do your business logic here and process this message!

            //$ob->worker



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

        //Log::get()->warning("GGGGGGGGGGGGGG");
        
        $class= 'Aspirateur';
        $method = 'tryMysqlConnection';


        FactoryController::addNode($class, $method, array('pmacontrol','1'));


    }

}