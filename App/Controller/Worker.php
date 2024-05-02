<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use App\Library\EngineV4;

use \App\Library\Debug;
use App\Library\Microsecond;
use \App\Library\System;
use \App\Library\Log;

use \Glial\Synapse\Controller;
use \Glial\I18n\I18n;
use \Glial\Sgbd\Sgbd;
use \Glial\Synapse\FactoryController;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;

class Worker extends Controller
{

    static $timestamp_config_file = "";

    public function before($param)
    {
        $monolog       = new Logger("Worker");
        $handler      = new StreamHandler(LOG_FILE, Logger::NOTICE);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $monolog->pushHandler($handler);
        $this->logger = $monolog;
    }

    public function run($param)
    {
        $id_worker_queue = $param[0];

        $pid = getmypid();
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM worker_queue WHERE id=".$id_worker_queue.";";

        $res = $db->sql_query($sql);
        while ($arr  = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $WORKER = $arr;
            $queue_key = intval($WORKER['queue_number']);
        }

        $this->logger->notice("[WORKER:$pid] Started new worker (".$WORKER['name'].") with pid : $pid");

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
            $data['microtime'] = Microsecond::timestamp();

            $lock_file = EngineV4::getFileLock($WORKER['name'],$msg->id );
            $worker_pid = EngineV4::getFilePid($WORKER['name'], $pid);
            
            file_put_contents($lock_file, json_encode($data));
            file_put_contents($worker_pid,$msg->id);

            $this->logger->info("[WORKER:$pid] [@Start] process id_mysql_server:$msg->id");

            //do your business logic here and process this message!
            FactoryController::addNode($WORKER['worker_class'], $WORKER['worker_method'], array($msg->name, $msg->id));
            //$this->tryMysqlConnection(array($msg->name, $msg->id));
            
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

    public function keepConfigFile($param)
    {
        Debug::parseDebug($param);
        $file = CONFIG."db.config.ini.php";

        $ts = filemtime($file);
        if (empty(self::$timestamp_config_file)) {
            $this->logger->debug("[WORKER:".$param['pid']."] We set TS for DB config");
            self::$timestamp_config_file = $ts;
        }
        else if (self::$timestamp_config_file != $ts) {
            
            $db_config = parse_ini_file($file, true);
            Sgbd::setConfig($db_config);
            $this->logger->notice("[WORKER:".$param['pid']."] We imported new config for DB");
            self::$timestamp_config_file = $ts;
        }
        Debug::debug($ts, "timestamp");
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


    public function index()
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM worker_queue";

        $res = $db->sql_query($sql);
        while($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)){
            $data['worker'][] = $arr;
        } 


        $this->set('data',$data);

    }


    public function list($param)
    {
        if (!empty($_GET['ajax']) && $_GET['ajax'] === "true") {
            $this->layout_name = false;
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $data['worker'] = array();

        $sql2 = "SELECT b.*,a.name  FROM worker_queue a
            INNER JOIN worker_run b ON a.id = b.id_worker_queue
            ORDER BY a.id, b.pid";
        $res2 = $db->sql_query($sql2);
        while ($arr  = $db->sql_fetch_array($res2, MYSQLI_ASSOC)) {

            $id_current        = EngineV4::getFilePid($arr['name'],$arr['pid'] );
        
            $arr['id_current'] = "N/A";
            if (file_exists($id_current)) {
                $arr['id_current'] = file_get_contents($id_current);
            }
            

            $data['worker'][] = $arr;
        }

        $this->set('data', $data);
    

    }

    public function checkAll($param)
    {
        Debug::parseDebug($param);
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM worker_queue WHERE id in (1,3)";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $this->check(array($ob->id));
        }

        $this->logger->debug("----------------------------------------------------------------");
    }




    public function check($param)
    {
        $id_worker_queue = intval($param[0]);
        Debug::parseDebug($param);

        $debug = '';
        if (Debug::$debug === true) {
            $debug = "--debug";
        }

        if (empty($id_worker_queue)) {
            trigger_error("PMATRL-586 : the first param should be and int matching worker_queue.id", E_USER_ERROR);
            throw new \Exception("PMATRL-586 : the first param should be and int matching worker_queue.id");
        }

        $db = Sgbd::sql(DB_DEFAULT);


        //in case of no worker launched
        $sql = "SELECT * FROM worker_queue WHERE id=".$id_worker_queue.";";
        $res = $db->sql_query($sql);
        while($ob = $db->sql_fetch_object($res))
        {
            $WORKER_QUEUE = $ob;
        }


        $sql2 = "SELECT a.*, b.name, b.nb_worker FROM `worker_run` a
        INNER JOIN worker_queue b ON a.id_worker_queue = b.id
        where `id_worker_queue` = ".$id_worker_queue.";";
        $res2 = $db->sql_query($sql2);
        Debug::sql($sql2);

        $nb_thread = 0;
        while ($ob2       = $db->sql_fetch_object($res2)) {

            Debug::debug($ob2, "OBJECT");

            $available = System::isRunningPid($ob2->pid);

            Debug::debug($available, "Result of pid : ".$ob2->pid);

            if ($available === false) {

                $this->logger->debug("[WORKER] Detected has failed ! worker ($ob2->name : $ob2->pid) with id_server:".$ob2->id."");
                //remove pid of worker there
                $double_buffer = EngineV4::getFilePid($ob2->name, $ob2->id);
                
                //$double_buffer = TMP."lock/worker/".$ob2->pid.".pid";
                
                //on a joute le worker avant de purger le fichier de l'ancien, afin d'aviter : PHP Warning:  file_get_contents 
                //$this->addWorker(array($ob2->id, $id_worker_queue, $debug));

                if (file_exists($double_buffer)) {

                    $id_server = file_get_contents($double_buffer);

                    $lock = EngineV4::getFileLock($ob2->name, $id_server);
                    //$lock = TMP."lock/worker/".$id_mysql_server.".lock";

                    unlink($double_buffer);
                    if (file_exists($lock)) {
                        unlink($lock);
                        $this->logger->notice("[WORKER] removed worker ($ob2->name) with id_mysql_server:".$id_server."");
                        
                    }
                    $this->logger->notice("[WORKER] removed worker ($ob2->name) with pid : ".$ob2->pid."");
                }

                $sql = "DELETE FROM worker_run WHERE id=".$ob2->id;
                Debug::sql($sql);
                $db->sql_query($sql);
                $nb_thread--;
            }

            $nb_thread++;
        }

        Debug::debug($nb_thread, "\$nb_thread");
        Debug::debug($WORKER_QUEUE->nb_worker, "\$ob->nb_worker");


        //$this->logger->notice("[WORKER] $WORKER_QUEUE->nb_worker > $nb_thread");

        if ($WORKER_QUEUE->nb_worker > $nb_thread) {
            $tocreate = $WORKER_QUEUE->nb_worker - $nb_thread;

            $this->logger->notice("[WORKER] TOO FEW WE NEED TO ADD $WORKER_QUEUE->nb_worker [defined] > $nb_thread [in use]");

            for ($i = 0; $i < $tocreate; $i++) {

                
                $this->addWorker(array($id_worker_queue, $debug));

                Debug::debug("Add worker");
            }
        } elseif ($WORKER_QUEUE->nb_worker < $nb_thread) {
            $todelete = $nb_thread - $WORKER_QUEUE->nb_worker;

            $this->logger->notice("[WORKER] TOO MUCH WE NEED TO DELETE $WORKER_QUEUE->nb_worker [defined] < $nb_thread [in use]");

            for ($i = 0; $i < $todelete; $i++) {
                $this->removeWorker(array($id_worker_queue, $debug));

                Debug::debug("Remove worker");
            }
        }
    
    }

    public function addWorker($param)
    {
        Debug::parseDebug($param);

        $id_worker_queue   = $param[0];

        $debug = '';
        if (Debug::$debug === true) {
            $debug = " --debug";
        }

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM worker_queue where id=".intval($id_worker_queue);
        $res = $db->sql_query($sql);

        if ($db->sql_num_rows($res) != 1)
        {
            $this->logger->debug('Impossible to find id_worker_queue : '. $id_worker_queue);
            throw new \Exception('Impossible to find id_worker_queue : '. $id_worker_queue);
        }


        while ($ob = $db->sql_fetch_object($res)) {
            $worker_name = $ob->name;
        }

        $this->logger->notice("Add new worker : ($worker_name)");

        $php = explode(" ", shell_exec("whereis php"))[1];
        $cmd = $php." ".GLIAL_INDEX." worker run $id_worker_queue $debug >> ".TMP."log/".$worker_name.".log 2>&1 & echo $!";
        Debug::debug($cmd);

        $pid = shell_exec($cmd);

        $sql = "INSERT INTO worker_run (`id_worker_queue`, `pid`) VALUES (".$id_worker_queue.", ".$pid.");";
        $db->sql_query($sql);
    }

    public function removeWorker($param)
    {
        Debug::parseDebug($param);
        $id_worker_queue = $param[0];

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT a.*, b.name FROM `worker_run` a
        INNER JOIN worker_queue b ON a.id_worker_queue = b.id
        WHERE `id_worker_queue`=".$id_worker_queue." LIMIT 1;";
        Debug::sql($sql);
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            Debug::debug($ob, "WORKER");

            if (System::isRunningPid($ob->pid))
            {
                $cmd = "kill ".$ob->pid;
                shell_exec($cmd);    
            }

            $double_buffer = EngineV4::getFilePid($ob->name, $ob->id);
            //$double_buffer = TMP."lock/".$worker_name."/".$ob->pid.".pid";

            if (file_exists($double_buffer)) {
                unlink($double_buffer);
            }

            $sql = "DELETE FROM `worker_run` WHERE `id`=".$ob->id.";";
            Debug::sql($sql);
            $db->sql_query($sql);
        }
    }

    public function killAll($param)
    {
        Debug::parseDebug($param);

        if (!empty($param[0])) {
            $id_worker_queue = $param[0];
        } else {
            $id_worker_queue = 0;
        }

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT a.*,b.name  FROM `worker_run` a
            INNER JOIN `worker_queue` b ON a.`id_worker_queue` = b.id
            ";

        if ($id_worker_queue != 0) {
            $sql .= "WHERE `id_worker_queue`=".$id_worker_queue;
        }

        $sql .= ";";

        Debug::sql($sql);
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $this->removeWorker(array($ob->id_worker_queue));
        }
    }


    public function addToQueue($param)
    {

        //$param[] = '--debug';
        Debug::parseDebug($param);

        $id_worker_queue = $param[0];

        if (empty($id_worker_queue)) {
            trigger_error("PMATRL-347 : Arguement id_daemon missing", E_USER_ERROR);
            throw new \Exception('PMATRL-347 : Arguement id_daemon missing');
        }

        if (Debug::$debug) {
            echo "[".date('Y-m-d H:i:s')."]"." Start all tests\n";
        }

        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM worker_queue WHERE id=".$id_worker_queue.";";
        //$this->logger->warning("SQL : ".$sql);

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $queue_key        = intval($ob->queue_number);
            $maxExecutionTime = $ob->max_execution_time;
            $refresh_time = $ob->interval;
            $main_query = $ob->query;
            $name = $ob->name;
        }

        //add message to queue
        $queue    = msg_get_queue($queue_key);
        $msg_qnum = (int) msg_stat_queue($queue)['msg_qnum'];

        // on attend d'avoir vider la file d'attente avant d'avoir une nouvelle liste de message (daemon_main.max_delay sec maximum)
        // securité dans 2 cas de figure, 
        // 1 - trop de serveurs à monitorer et les worker non pas le temps de suivre
        // 2 - trop de serveur en erreur (DNS unreachable or MySQL who don't give back or just going down)
        if ($msg_qnum != 0) {

            Debug::debug('On attends de vider la file d\'attente');

            for ($i = 0; $i < $maxExecutionTime; $i++) {
                $msg_qnum = msg_stat_queue($queue)['msg_qnum'];
                if ($msg_qnum == 0) {
                    break;
                } else {
                    $this->logger->warning('Number message waiting in queue ('.$name.') : '. $msg_qnum .'');
                }
                sleep(1);
            }
        }

        $mysql_servers = array();
        $lock_directory = EngineV4::PATH_LOCK.$name."*.lock";

        $elems = array();
        foreach (glob($lock_directory) as $filename) {

            Debug::debug($filename, "filename");

            $json    = file_get_contents($filename);
            $data    = json_decode($json, true);
            $elems[] = $data;
        }

        Debug::debug($elems, "liste des serveur en retard !");

        $db = Sgbd::sql(DB_DEFAULT);



        foreach ($elems as $server) {
            //on verifie avec le double buffer qu'on est bien sur le même pid
            //et ce dernier est toujours sur le serveur MySQL qui pose problème


            //$idmysqlserver = trim(file_get_contents(TMP."lock/worker/".$server['pid'].".pid"));
            $idmysqlserver = trim(file_get_contents(EngineV4::getFilePid("worker_mysql", $server['pid'])));

            // si le pid n'existe plus le fichier temporaire sera surcharger au prochain run
            if (System::isRunningPid($server['pid']) === true && $idmysqlserver == $server['id']) {

                $mysql_servers[] = $server['id'];
                $time = microtime(true) - $server['microtime'];

                if ($maxExecutionTime > $time) {
                    $msg = "Worker still runnig since ".round($time, 2)." seconds - pid : ".$server['pid'];
                    $this->logger->warning("MySQL server with id : ".$server['id']." is late !!!  ".$msg);
                }
            } else {
                //si pid n'existe plus alors on efface le fichier de lock
                //$lock_file = TMP."lock/worker/".$server['id'].".lock";
                $lock_file = EngineV4::getFileLock("worker_mysql", $server['id'] );
                
                if (file_exists($lock_file)) {
                    $this->logger->notice('[addToQueueMySQL] the pid didn\'t exist anymore : "'.$lock_file.'", (id_mysql_server:'.$server['id'].') we deleted id !');
                    unlink($lock_file);
                }
            }
        }

        $this->view = false;


        /*
        $sql = "select a.id,a.name from mysql_server a
            INNER JOIN client b on a.id_client =b.id
            WHERE a.is_monitored =1 and b.is_monitored=1";

        if (!empty($mysql_servers)) {
            $sql .= " AND a.id NOT IN (".implode(',', $mysql_servers).")";
        }
        */

        Debug::sql($main_query);
        $res = $db->sql_query($main_query);

        $server_list = array();
        while ($ob          = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $server_list[] = $ob;
        }

        //Debug::debug($server_list, "Liste des serveurs monitoré");
        //to prevent any trouble with fork
        $db->sql_close();


        //to prevent negative value from UI, and impose minimal wait to not kill the serveur
        if ($refresh_time < 1){
            $refresh_time = 1;
        }


        // le but est de lisser le charge du serveur au maximum afin d'éviter l'effet dans de scie sur le CPU.
        $nb_server_to_monitor = count($server_list);

        $delay = floor(1000000 * $refresh_time / 10 / $nb_server_to_monitor);

        $this->logger->debug("Delay : ".$delay."");

        foreach ($server_list as $server) {

            // Create dummy message object
            $object       = new \stdclass;
            $object->name = $server['name'];
            $object->id   = $server['id'];

            //try to add message to queue
            if (msg_send($queue, 1, $object)) {

                $this->logger->debug("Add id_mysql_server:".$server['id']." to the queue ($queue_key)");
                usleep($delay);
            } else {
                $this->logger->warning("could not add message to queue ($queue_key)");
            }
        }


        
    }

}