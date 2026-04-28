<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use App\Library\EngineV4;
use App\Library\Kpi\KpiProcessDrilldown;
use App\Library\Kpi\WorkerExecutionLogger;
use App\Library\Kpi\WorkerSkipException;
use App\Library\Security\CsrfGuard;

use \App\Library\Debug;
use App\Library\Extraction2;
use App\Library\Microsecond;
use \App\Library\System;
use \App\Library\Log;

use Glial\Http\Request;
use Glial\Security\Csrf;
use \Glial\Synapse\Controller;
use \Glial\I18n\I18n;
use \Glial\Sgbd\Sgbd;
use \Glial\Synapse\FactoryController;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;

/**
 * Class responsible for worker workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Worker extends Controller
{
    private const WORKER_UPDATE_CSRF_SCOPE = 'worker.update';
    public const WORKER_KILL_SERVER_CSRF_SCOPE = 'worker.killServer';
    private const WORKER_UPDATE_FIELDS = ['nb_worker', 'queue_number'];
    private const WORKER_KILL_TYPES = ['mysql', 'maxscale', 'proxysql', 'ssh'];

/**
 * Stores `$timestamp_config_file` for timestamp config file.
 *
 * @var string
 * @phpstan-var string
 * @psalm-var string
 */
    static $timestamp_config_file = "";

/**
 * Stores `$logger` for logger.
 *
 * @var mixed
 * @phpstan-var mixed
 * @psalm-var mixed
 */
    var $logger;

/**
 * Prepare worker state through `before`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for before.
 * @phpstan-return void
 * @psalm-return void
 * @see self::before()
 * @example /fr/worker/before
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function before($param)
    {
        $monolog       = new Logger("Worker");
        $handler      = new StreamHandler(LOG_FILE, Logger::NOTICE);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $monolog->pushHandler($handler);
        $this->logger = $monolog;
    }

/**
 * Handle worker state through `logger`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for logger.
 * @phpstan-return void
 * @psalm-return void
 * @see self::logger()
 * @example /fr/worker/logger
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public static function logger($param)
    {
        $monolog       = new Logger("Worker");
        $handler      = new StreamHandler(LOG_FILE, Logger::NOTICE);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $monolog->pushHandler($handler);
        self::$logger = $monolog;
    }



/**
 * Handle worker state through `run`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for run.
 * @phpstan-return void
 * @psalm-return void
 * @see self::run()
 * @example /fr/worker/run
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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
            $data['pid']       = $pid;
            $data['microtime'] = Microsecond::timestamp();

            $lock_file = EngineV4::getFileLock($WORKER['name'],$msg->id );
            $worker_pid = EngineV4::getFilePid($WORKER['name'], $pid);
            
            file_put_contents($lock_file, json_encode($data));
            file_put_contents($worker_pid,$msg->id);

            $this->logger->info("[WORKER:$pid] [@Start] process id_mysql_server:$msg->id");

            $id_worker_execution = WorkerExecutionLogger::startForWorkerPid($pid, (int)$msg->id);
            $start = microtime(true);
            $dbCounters = WorkerExecutionLogger::snapshotDbCounters();
            $workerStatus = 'OK';
            $workerError = null;
            $workerErrorMessage = null;

            $this->logger->debug("====> id_worker_execution ".$id_worker_execution);

            //do your business logic here and process this message!
            if (function_exists('memory_reset_peak_usage')) {
                memory_reset_peak_usage();
            }

            try{
                FactoryController::addNode($WORKER['worker_class'], $WORKER['worker_method'], array($msg->name, $msg->id, $msg->refresh, $id_worker_execution));
            }
            catch (WorkerSkipException $e) {
                $workerStatus = 'SKIPPED';
                $workerErrorMessage = $e->getMessage();
                $this->logger->notice("[WORKER:$pid] SKIPPED id_mysql_server:$msg->id (".$e->getMessage().")");
            }
            catch (\Throwable $e) {
                $workerStatus = 'ERROR';
                $workerError = $e;
                $this->logger->warning("[WORKER:$pid] CRASHED with id_mysql_server:$msg->id (ERROR : ".$e->getMessage().")");

                if (file_exists($worker_pid)) {
                    unlink($worker_pid);
                }
            }
            finally
            {
                $end = microtime(true);
                $executionTime = round(($end - $start) * 1000, 0);
                $dbMetrics = WorkerExecutionLogger::diffDbCounters($dbCounters);

                WorkerExecutionLogger::finish($id_worker_execution, [
                    'status' => $workerStatus,
                    'throwable' => $workerError,
                    'error_message' => $workerErrorMessage,
                    'execution_time_ms' => (int)$executionTime,
                    'max_execution_time' => (int)($WORKER['max_execution_time'] ?? 0),
                    'peak_rss_kb' => (int)ceil(memory_get_peak_usage(true) / 1024),
                    'db_queries_count' => $dbMetrics['db_queries_count'],
                    'db_queries_time_ms' => $dbMetrics['db_queries_time_ms'],
                    'attempt_n' => 1,
                ]);

                // if mysql connection is down, the worker will be down too and we have to restart one
                $this->logger->info("[WORKER:$pid] [@END] process id_mysql_server:$msg->id");

                usleep(50);
                if (file_exists($lock_file)) {
                    unlink($lock_file);
                }

                file_put_contents($worker_pid,"Waiting...");

                //finally, reset our msg vars for when we loop and run again
                $msg_type = NULL;
                $msg      = NULL;
            }
        }

        $this->logger->warning("We not wait waited next msg in queue ($queue_key)");
    }

/**
 * Handle worker state through `keepConfigFile`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for keepConfigFile.
 * @phpstan-return void
 * @psalm-return void
 * @see self::keepConfigFile()
 * @example /fr/worker/keepConfigFile
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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


/**
 * Handle worker state through `adaptNumberWorker`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for adaptNumberWorker.
 * @phpstan-return void
 * @psalm-return void
 * @see self::adaptNumberWorker()
 * @example /fr/worker/adaptNumberWorker
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function adaptNumberWorker($param)
    {
        Debug::parseDebug($param);
        
        $db = Sgbd::sql(DB_DEFAULT);

        $sql ='SELECT `name`, `nb_worker` FROM worker_queue';
        $res = $db->sql_query($sql);

        $worker = array();
        while($ob = $db->sql_fetch_object($res))
        {
            $type = str_replace('worker_', '', $ob->name);
            $worker[$type] = $ob->nb_worker;
        }

        Debug::debug($worker);

        $elem_to_get = [];
        foreach($worker as $type => $value){
            $elem_to_get[] = $type.'_available';
        }

        Debug::debug($elem_to_get, "ELEMENTS TO GET");

        $elems = Extraction2::display($elem_to_get);

        $result = self::summarizeAvailability($elems);

        Debug::debug($result);
        // if 5 fail => black list for 1 minutes   


        $queries = self::generateWorkerUpdateQueries($result);


        foreach($queries as $sql2)
        {
            $db->sql_query($sql2);
        }

        Debug::debug($queries);

    }

/**
 * Handle worker state through `summarizeAvailability`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array $data Input value for `data`.
 * @phpstan-param array $data
 * @psalm-param array $data
 * @return array Returned value for summarizeAvailability.
 * @phpstan-return array
 * @psalm-return array
 * @see self::summarizeAvailability()
 * @example /fr/worker/summarizeAvailability
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public static function summarizeAvailability(array $data): array
    {
        $result = [];

        foreach ($data as $row) {
            foreach ($row as $key => $value) {
                // On cherche tous les champs de type xxxx_available
                if (str_ends_with($key, '_available')) {

                    // Initialise la structure si besoin
                    if (!isset($result[$key])) {
                        $result[$key] = [
                            'available_1_count' => 0,
                            'available_1_ids'   => [],
                            'available_0_count' => 0,
                            'available_0_ids'   => [],
                        ];
                    }

                    // On range les valeurs
                    if ((int)$value === 1) {
                        $result[$key]['available_1_count']++;
                        $result[$key]['available_1_ids'][] = $row['id_mysql_server'];
                    } else {
                        $result[$key]['available_0_count']++;
                        $result[$key]['available_0_ids'][] = $row['id_mysql_server'];
                    }
                }
            }
        }

        return $result;
    }

/**
 * Handle worker state through `generateWorkerUpdateQueries`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array $availability Input value for `availability`.
 * @phpstan-param array $availability
 * @psalm-param array $availability
 * @return array Returned value for generateWorkerUpdateQueries.
 * @phpstan-return array
 * @psalm-return array
 * @see self::generateWorkerUpdateQueries()
 * @example /fr/worker/generateWorkerUpdateQueries
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function generateWorkerUpdateQueries(array $availability): array
    {
        // Correspondance entre les clés *_available et la colonne `table` de worker_queue
        $mapping = [
            'mysql_available'    => 'mysql_server',
            'ssh_available'      => 'ssh_server',
            'proxysql_available' => 'proxysql_server',
            'maxscale_available' => 'maxscale_server',
        ];

        $queries = [];

        foreach ($availability as $key => $info) {

            // Vérifie que la clé est bien mappée à une table
            if (!isset($mapping[$key])) {
                continue;
            }

            $available   = $info['available_1_count'] ?? 0;
            $unavailable = $info['available_0_count'] ?? 0;

            // Règle métier : floor(count(1)/5) + count(0)
            $nb_worker = floor($available / 5) + $unavailable;

            $queries[] = sprintf(
                "UPDATE worker_queue SET nb_worker = %d WHERE `table` = '%s';",
                $nb_worker,
                $mapping[$key]
            );
        }

        return $queries;
    }

/**
 * Handle worker state through `test`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for test.
 * @phpstan-return void
 * @psalm-return void
 * @see self::test()
 * @example /fr/worker/test
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function test($param)
    {

        Debug::parseDebug($param);

        //Log::get()->warning("GGGGGGGGGGGGGG");
        
        $class= 'Aspirateur';
        $method = 'tryMysqlConnection';


        FactoryController::addNode($class, $method, array('pmacontrol','1'));


    }


/**
 * Render worker state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/worker/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);
        $data = [
            'worker' => [],
            'worker_update_csrf_field' => Csrf::DEFAULT_FIELD,
            'worker_update_csrf_token' => Csrf::issueToken($_SESSION, self::WORKER_UPDATE_CSRF_SCOPE),
        ];

        if (!empty($_GET['ajax']) && $_GET['ajax'] === "true") {
            $this->layout_name = false;
        }
        else {
            $this->di['js']->addJavascript(array('bootstrap-editable.min.js', 'Tree/index.js'));
        }


        $sql = "SELECT * FROM worker_queue";

        $res = $db->sql_query($sql);
        while($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)){

            Debug::debug($arr,"ARRAY");

            // check elem by list

            $queue    = msg_get_queue($arr['queue_number']);
            $msg_qnum = msg_stat_queue($queue);
            $arr = array_merge($msg_qnum , $arr);


            $data['worker'][] = $arr;
        } 




        Debug::debug($data,"DATA");

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
            INNER JOIN worker_run b ON a.id = b.id_worker_queue WHERE is_working=1
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

/**
 * Handle worker state through `checkAll`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for checkAll.
 * @phpstan-return void
 * @psalm-return void
 * @see self::checkAll()
 * @example /fr/worker/checkAll
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function checkAll($param)
    {
        Debug::parseDebug($param);
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM worker_queue";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $this->check(array($ob->id));
        }

        self::refresh($param);

        $this->logger->debug("----------------------------------------------------------------");
    }




/**
 * Handle worker state through `check`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for check.
 * @phpstan-return void
 * @psalm-return void
 * @throws \Throwable When the underlying operation fails.
 * @see self::check()
 * @example /fr/worker/check
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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
        where `id_worker_queue` = ".$id_worker_queue." AND a.is_working=1;";
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

                //$sql = "DELETE FROM worker_run WHERE id=".$ob2->id;
                
                WorkerExecutionLogger::markStuckForRun((int)$ob2->id);

                $sql = "UPDATE worker_run SET is_working=0, date_killed='".date('Y-m-d H:i:s')."' WHERE id=".$ob2->id.";";
                
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

/**
 * Create worker state through `addWorker`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for addWorker.
 * @phpstan-return void
 * @psalm-return void
 * @throws \Throwable When the underlying operation fails.
 * @see self::addWorker()
 * @example /fr/worker/addWorker
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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

/**
 * Delete worker state through `removeWorker`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for removeWorker.
 * @phpstan-return void
 * @psalm-return void
 * @see self::removeWorker()
 * @example /fr/worker/removeWorker
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function removeWorker($param)
    {
        Debug::parseDebug($param);
        $id_worker_queue = $param[0];

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT a.*, b.name FROM `worker_run` a
        INNER JOIN worker_queue b ON a.id_worker_queue = b.id
        WHERE `id_worker_queue`=".$id_worker_queue." AND is_working=1 LIMIT 1;";
        Debug::sql($sql);
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            Debug::debug($ob, "WORKER");

            if (System::isRunningPid($ob->pid))
            {
                WorkerExecutionLogger::markKilledForRun((int)$ob->id);
                $cmd = "kill ".$ob->pid;
                shell_exec($cmd);    
            }

            $double_buffer = EngineV4::getFilePid($ob->name, $ob->id);
            //$double_buffer = TMP."lock/".$worker_name."/".$ob->pid.".pid";

            if (file_exists($double_buffer)) {
                unlink($double_buffer);
            }

            //$sql = "DELETE FROM `worker_run` WHERE `id`=".$ob->id.";";
            $sql = "UPDATE worker_run SET is_working=0, is_safe_kill=1, date_killed='".date('Y-m-d H:i:s')."' WHERE id=".$ob->id.";";

            Debug::sql($sql);
            $db->sql_query($sql);
        }
    }

/**
 * Kill the worker currently processing one server.
 *
 * This action handles a POST request from `Server/main` and redirects back to
 * the referring page after killing the matching worker process.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for killServerWorker.
 * @phpstan-return void
 * @psalm-return void
 */
    public function killServerWorker($param): void
    {
        $this->view = false;
        $this->layout_name = false;

        $redirect = self::getSafeWorkerRedirectUrl($_SERVER);
        $outcome = self::evaluateKillServerWorkerRequest($_POST, $_SERVER, $_SESSION, $param);

        if ($outcome['status'] !== 200) {
            if (function_exists('set_flash')) {
                set_flash('error', __('Worker'), __($outcome['body']));
            }
            header('Location: '.$redirect);
            return;
        }

        $result = $this->killProcessingServerWorker(
            (int)$outcome['server_id'],
            (string)$outcome['worker_type'],
            $outcome['pid']
        );

        if (function_exists('set_flash')) {
            set_flash(
                $result['killed'] ? 'success' : 'caution',
                __('Worker'),
                __($result['message'])
            );
        }

        header('Location: '.$redirect);
    }

    public static function evaluateKillServerWorkerRequest(array $post, array $server, array $session, array $params = []): array
    {
        $guard = CsrfGuard::check($post, $server, $session, self::WORKER_KILL_SERVER_CSRF_SCOPE);
        if (!$guard['allowed']) {
            return self::buildWorkerUpdateOutcome($guard['status'], $guard['body'], $guard['headers']);
        }

        $serverId = (string)($post['id_mysql_server'] ?? $params[0] ?? '');
        if (!ctype_digit($serverId) || (int)$serverId < 1) {
            return self::buildWorkerUpdateOutcome(400, 'Invalid server id');
        }

        $workerType = (string)($post['worker_type'] ?? $params[1] ?? 'mysql');
        if (!in_array($workerType, self::WORKER_KILL_TYPES, true)) {
            return self::buildWorkerUpdateOutcome(400, 'Invalid worker type');
        }

        $pid = null;
        $pidValue = (string)($post['pid'] ?? '');
        if ($pidValue !== '') {
            if (!ctype_digit($pidValue) || (int)$pidValue < 1) {
                return self::buildWorkerUpdateOutcome(400, 'Invalid worker pid');
            }
            $pid = (int)$pidValue;
        }

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'sql' => null,
            'server_id' => (int)$serverId,
            'worker_type' => $workerType,
            'pid' => $pid,
        ];
    }

    private function killProcessingServerWorker(int $serverId, string $workerType, ?int $pid): array
    {
        $workerName = 'worker_'.$workerType;
        $db = Sgbd::sql(DB_DEFAULT);
        $workerNameSql = $db->sql_real_escape_string($workerName);

        $sql = "SELECT a.*, b.name FROM `worker_run` a
            INNER JOIN worker_queue b ON a.id_worker_queue = b.id
            WHERE a.is_working = 1
            AND b.name = '".$workerNameSql."'";

        if ($pid !== null) {
            $sql .= " AND a.pid = ".(int)$pid;
        }

        $sql .= " ORDER BY a.id DESC";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $candidatePid = (int)$ob->pid;
            if (!self::isWorkerPidHandlingServer($workerName, $candidatePid, $serverId)) {
                continue;
            }

            if (System::isRunningPid($candidatePid)) {
                WorkerExecutionLogger::markKilledForRun((int)$ob->id);
                shell_exec('kill '.$candidatePid);
            }

            self::clearWorkerServerFiles($workerName, $candidatePid, $serverId);

            $sqlUpdate = "UPDATE worker_run SET is_working=0, is_safe_kill=1, date_killed='".date('Y-m-d H:i:s')."'
                WHERE id=".(int)$ob->id.";";
            $db->sql_query($sqlUpdate);

            return [
                'killed' => true,
                'message' => 'Worker '.$workerName.' PID '.$candidatePid.' killed for server '.$serverId,
            ];
        }

        return [
            'killed' => false,
            'message' => 'No active worker found for server '.$serverId,
        ];
    }

    public static function killByPid(int $pid, ?int $expectedStartTimeTicks = null): array
    {
        if ($pid <= 0) {
            return ['killed' => false, 'message' => 'Invalid worker pid'];
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $res = $db->sql_query(self::buildActiveWorkerRunByPidSql($pid));
        $ob = $db->sql_fetch_object($res);
        if (!is_object($ob)) {
            return ['killed' => false, 'message' => 'No active worker found for PID '.$pid];
        }

        $isRunning = System::isRunningPid($pid);
        $signalSent = !$isRunning;
        if ($isRunning) {
            if ($expectedStartTimeTicks !== null) {
                $currentStartTimeTicks = KpiProcessDrilldown::readStartTimeTicks($pid);
                if ($currentStartTimeTicks === null) {
                    return ['killed' => false, 'message' => 'Unable to verify worker PID '.$pid.' identity'];
                }

                if ($currentStartTimeTicks !== $expectedStartTimeTicks) {
                    return ['killed' => false, 'message' => 'Worker PID '.$pid.' changed before kill'];
                }
            }

            if (function_exists('posix_kill')) {
                $signalSent = @posix_kill($pid, defined('SIGTERM') ? SIGTERM : 15);
            } else {
                shell_exec('kill '.$pid);
                $signalSent = true;
            }
        }

        if (!$signalSent) {
            return ['killed' => false, 'message' => 'Unable to signal worker PID '.$pid];
        }

        WorkerExecutionLogger::markKilledForRun((int)$ob->id);

        $doubleBuffer = EngineV4::getFilePid((string)$ob->name, (int)$ob->id);
        if (is_string($doubleBuffer) && file_exists($doubleBuffer)) {
            unlink($doubleBuffer);
        }

        $db->sql_query(self::buildMarkWorkerRunKilledSql((int)$ob->id));

        return [
            'killed' => true,
            'message' => 'Worker PID '.$pid.' marked safe-kill',
            'id_worker_run' => (int)$ob->id,
        ];
    }

    public static function buildActiveWorkerRunByPidSql(int $pid): string
    {
        return 'SELECT a.*, b.name FROM `worker_run` a '
            .'INNER JOIN `worker_queue` b ON a.id_worker_queue = b.id '
            .'WHERE a.is_working = 1 AND a.pid = '.$pid.' '
            .'ORDER BY a.id DESC LIMIT 1;';
    }

    public static function buildMarkWorkerRunKilledSql(int $idWorkerRun, ?string $dateKilled = null): string
    {
        $dateKilled = str_replace("'", "''", $dateKilled ?? date('Y-m-d H:i:s'));

        return "UPDATE worker_run SET is_working=0, is_safe_kill=1, date_killed='".$dateKilled."' "
            .'WHERE id='.$idWorkerRun.';';
    }

    private static function getSafeWorkerRedirectUrl(array $server): string
    {
        $referer = (string)($server['HTTP_REFERER'] ?? '');
        if ($referer !== '' && Request::isSameSiteUrl($referer, $server)) {
            return $referer;
        }

        return LINK.'server/main';
    }

    public static function findWorkerPidForServerId(string $workerName, int $serverId, ?int $preferredPid = null): ?int
    {
        if ($preferredPid !== null && self::isWorkerPidHandlingServer($workerName, $preferredPid, $serverId)) {
            return $preferredPid;
        }

        $pidFiles = glob(EngineV4::PATH_LOCK.$workerName.EngineV4::SEPERATOR.'*.'.EngineV4::EXT_PID);
        if ($pidFiles === false) {
            return null;
        }

        foreach ($pidFiles as $file) {
            if (!preg_match('/'.preg_quote(EngineV4::SEPERATOR, '/').'(\d+)\.'.preg_quote(EngineV4::EXT_PID, '/').'$/', $file, $matches)) {
                continue;
            }

            $pid = (int)$matches[1];
            if (self::isWorkerPidHandlingServer($workerName, $pid, $serverId)) {
                return $pid;
            }
        }

        return null;
    }

    public static function isWorkerPidHandlingServer(string $workerName, int $pid, int $serverId): bool
    {
        $id = self::readWorkerServerIdFromPidFile(EngineV4::getFilePid($workerName, $pid));
        if ($id !== null && ctype_digit($id) && (int)$id === $serverId) {
            return true;
        }

        $lockFile = EngineV4::getFileLock($workerName, $serverId);
        if (!is_readable($lockFile)) {
            return false;
        }

        $json = @file_get_contents($lockFile);
        if ($json === false) {
            return false;
        }

        $data = json_decode($json, true);
        return is_array($data)
            && !empty($data['pid'])
            && ctype_digit((string)$data['pid'])
            && (int)$data['pid'] === $pid;
    }

    private static function clearWorkerServerFiles(string $workerName, int $pid, int $serverId): void
    {
        $pidFile = EngineV4::getFilePid($workerName, $pid);
        if (file_exists($pidFile)) {
            unlink($pidFile);
        }

        $lockFile = EngineV4::getFileLock($workerName, $serverId);
        if (file_exists($lockFile)) {
            unlink($lockFile);
        }
    }

/**
 * Handle worker state through `killAll`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for killAll.
 * @phpstan-return void
 * @psalm-return void
 * @see self::killAll()
 * @example /fr/worker/killAll
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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
            WHERE 1=1 ";

        if ($id_worker_queue != 0) {
            $sql .= " `id_worker_queue`=".$id_worker_queue;
        }

        $sql .= " AND is_working=1;";

        Debug::sql($sql);
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $this->removeWorker(array($ob->id_worker_queue));
        }
    }


/**
 * Create worker state through `addToQueue`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for addToQueue.
 * @phpstan-return void
 * @psalm-return void
 * @throws \Throwable When the underlying operation fails.
 * @see self::addToQueue()
 * @example /fr/worker/addToQueue
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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
        $sql = "SELECT * FROM worker_queue a
        INNER JOIN daemon_main b ON a.id_daemon_main = b.id WHERE a.id=".$id_worker_queue.";";
        //$this->logger->warning("SQL : ".$sql);

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $queue_key        = intval($ob->queue_number);
            $maxExecutionTime = $ob->max_execution_time;
            $refresh_time = $ob->refresh_time;
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


        // if  $msg_qnum > (number of mysql_server)

        
        $sql2 = preg_replace('/select\s+(.*)\s+from/','SELECT count(1) as cpt FROM' ,$main_query);
        Debug::sql($sql2);
        $res2 = $db->sql_query($sql2);

        //bug avec group by with SSH queue
        $number_of_server_to_monitor = 0;

        while ($ob2 = $db->sql_fetch_object($res2)) {
            $number_of_server_to_monitor = $ob2->cpt;
        }

        if ($msg_qnum > $number_of_server_to_monitor) {

            Debug::debug('On attends de vider la file d\'attente');

            for ($i = 0; $i < $maxExecutionTime; $i++) {
                $msg_qnum = msg_stat_queue($queue)['msg_qnum'];
                if ($msg_qnum == 0) {
                    break;
                } else {
                    $this->logger->emergency('[EMERGENCY] Number message waiting in queue ('.$name.') : '. $msg_qnum .'');
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

        Debug::debug($elems, "SERVER LOCK");

        foreach ($elems as $server) {
            //on verifie avec le double buffer qu'on est bien sur le même pid
            //et ce dernier est toujours sur le serveur MySQL qui pose problème


            //$idmysqlserver = trim(file_get_contents(TMP."lock/worker/".$server['pid'].".pid"));
            
            // why workermysql ? good idea to replacE?
            //$idmysqlserver = trim(file_get_contents(EngineV4::getFilePid("worker_mysql", $server['pid'])));
            $idmysqlserver = self::readWorkerServerIdFromPidFile(EngineV4::getFilePid($name, $server['pid']));

            // si le pid n'existe plus le fichier temporaire sera surcharger au prochain run
            if ($idmysqlserver !== null && System::isRunningPid($server['pid']) === true && $idmysqlserver == $server['id']) {

                $mysql_servers[] = $server['id'];
                $time = microtime(true) - $server['microtime'];

                if ($maxExecutionTime > $time) {
                    $msg = "Worker still runnig since ".round($time, 2)." seconds - pid : ".$server['pid'];
                    $this->logger->warning("MySQL server with id : ".$server['id']." is late !!!  ".$msg);
                }
            } else {
                //si pid n'existe plus alors on efface le fichier de lock
                //$lock_file = TMP."lock/worker/".$server['id'].".lock";

                //$lock_file = EngineV4::getFileLock("worker_mysql", $server['id'] );
                $lock_file = EngineV4::getFileLock($name, $server['id'] );
                
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

        //remove server already in current process 
        $blacklist = $this->getListofWorkingServer(array('mysql'));

        Debug::sql($main_query);
        $res = $db->sql_query($main_query);

        $server_list = array();
        while ($ob          = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

            if (in_array($ob['id'], $blacklist)) {
                continue;
            }

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

        if ($nb_server_to_monitor < 1)
        {
            $nb_server_to_monitor = 1;
        }

        //le but est de lisser la charge sur la totallité du créneau entre 2 run (ca ne change rien si c'est sur une seconde)
        $delay = floor(1000000 * $refresh_time / 2 / $nb_server_to_monitor - 100);

        $this->logger->debug("Delay : ".$delay."");

        foreach ($server_list as $server) {

            $server['refresh_time'] = $refresh_time;

            // Create dummy message object
            $object          = new \stdclass;
            $object->name    = $server['name'];
            $object->id      = $server['id'];
            $object->refresh = $server['refresh_time'];
            
            //try to add message to queue
            if (msg_send($queue, 1, $object)) {

                $this->logger->debug("Add id_mysql_server:".$server['id']." to the queue ($queue_key)");
                usleep($delay);
            } else {
                $this->logger->error("could not add message to queue ($queue_key)");
            }
        }
    }

/**
 * Update worker state through `update`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @return void Returned value for update.
 * @phpstan-return void
 * @psalm-return void
 * @see self::update()
 * @example /fr/worker/update
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function update()
    {

        $this->view        = false;
        $this->layout_name = false;

        $outcome = self::evaluateUpdateRequest($_POST, $_SERVER, $_SESSION);
        if ($outcome['status'] !== 200) {
            self::sendWorkerUpdateError($outcome['status'], $outcome['body'], $outcome['headers']);
            return;
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $db->sql_query($outcome['sql']);

        if ($db->sql_affected_rows() === 1) {
            echo "OK";
        } else {
            self::sendWorkerUpdateError(503, "Worker queue not updated");
        }
    }

    public static function evaluateUpdateRequest(array $post, array $server, array $session): array
    {
        $guard = CsrfGuard::check($post, $server, $session, self::WORKER_UPDATE_CSRF_SCOPE);
        if (!$guard['allowed']) {
            return self::buildWorkerUpdateOutcome($guard['status'], $guard['body'], $guard['headers']);
        }

        $sql = self::buildWorkerUpdateSql($post);
        if ($sql === null) {
            return self::buildWorkerUpdateOutcome(400, "Invalid worker update payload");
        }

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'sql' => $sql,
        ];
    }

    public static function buildWorkerUpdateSql(array $post): ?string
    {
        $field = (string) ($post['name'] ?? '');
        $value = (string) ($post['value'] ?? '');
        $id = (string) ($post['pk'] ?? '');

        if (! in_array($field, self::WORKER_UPDATE_FIELDS, true)) {
            return null;
        }

        if (! ctype_digit($value) || ! ctype_digit($id) || (int) $id < 1) {
            return null;
        }

        return sprintf(
            'UPDATE worker_queue SET `%s` = %d WHERE id = %d',
            $field,
            (int) $value,
            (int) $id
        );
    }

    private static function buildWorkerUpdateOutcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'sql' => null,
        ];
    }

    private static function sendWorkerUpdateError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        header('Content-Type: text/plain; charset=UTF-8');
        echo $message;
    }

/**
 * Handle worker state through `dropAllQueue`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for dropAllQueue.
 * @phpstan-return void
 * @psalm-return void
 * @see self::dropAllQueue()
 * @example /fr/worker/dropAllQueue
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function dropAllQueue($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM worker_queue ORDER BY id";
        $res = $db->sql_query($sql);

        while($ob = $db->sql_fetch_object($res))
        {
            Debug::debug("Queue number : (".$ob->name.") ".$ob->queue_number);

            if (msg_queue_exists($ob->queue_number))
            {
                Debug::debug("This queue exist !");

                $queue         = msg_get_queue($ob->queue_number);
                $number_of_msg = msg_stat_queue($queue)['msg_qnum'];

                Debug::debug("Number of Msg waiting : $number_of_msg ");
                
                msg_remove_queue($queue);

                Debug::debug("This queue has been destroyed !");
                Debug::debug("--------------------");
            }
        }
    }

/**
 * Handle worker state through `file`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for file.
 * @phpstan-return void
 * @psalm-return void
 * @see self::file()
 * @example /fr/worker/file
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function file($param)
    {
        if (!empty($_GET['ajax']) && $_GET['ajax'] === "true") {
            $this->layout_name = false;
        }

        $data['ls'] = "";
        $data['ls'] = trim(shell_exec("ls -lh ". TMP."tmp_file"));
        $data['files'] = explode("\n", $data['ls']);
        $data['nb_files'] = count($data['files']);

        if ($data['nb_files'] > 2 )
        {
            $data['nb_files']--;
        }

        $this->set('data', $data);
    }


/**
 * Retrieve worker state through `getPidWorking`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for getPidWorking.
 * @phpstan-return void
 * @psalm-return void
 * @see self::getPidWorking()
 * @example /fr/worker/getPidWorking
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getPidWorking($param)
    {
        $data['ls'] = shell_exec("ls -lh ". TMP."tmp_file");
    }

/**
 * Handle worker state through `checkPid`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for checkPid.
 * @phpstan-return void
 * @psalm-return void
 * @see self::checkPid()
 * @example /fr/worker/checkPid
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function checkPid($param)
    {
        Debug::parseDebug($param);
        $pid_to_check = glob(EngineV4::PATH_LOCK."*\.".EngineV4::EXT_PID);
        foreach($pid_to_check as $filname)
        {
            Debug::debug($filname);
            $pid = EngineV4::getPid($filname);

            Debug::debug($pid, 'PID');

            if (!System::isRunningPid($pid)) {

                //delete worker withPID
                unlink($filname);

                //Debug::debug($pid, 'DELETE PID');
            }
        }
    }

/**
 * Delete worker state through `deleteWorkerPid`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for deleteWorkerPid.
 * @phpstan-return void
 * @psalm-return void
 * @see self::deleteWorkerPid()
 * @example /fr/worker/deleteWorkerPid
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function deleteWorkerPid($param)
    {
        $pid = $param[0];

    }


/**
 * Retrieve worker state through `getRunningId`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for getRunningId.
 * @phpstan-return void
 * @psalm-return void
 * @see self::getRunningId()
 * @example /fr/worker/getRunningId
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getRunningId($param)
    {
        Debug::parseDebug($param);
    }



/**
 * Retrieve worker state through `getListofWorkingServer`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for getListofWorkingServer.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getListofWorkingServer()
 * @example /fr/worker/getListofWorkingServer
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getListofWorkingServer($param)
    {
        Debug::parseDebug($param);

        $worker_type = $param[0] ?? "mysql";


        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM worker_run WHERE is_working = 1;";
        $res = $db->sql_query($sql);

        $elems = array();

        while($ob = $db->sql_fetch_object($res))
        {
            //echo "PID : $ob->pid\n";
            $file =  EngineV4::getFilePid("worker_".$worker_type,$ob->pid);

            //echo $file."\n";

            if ( file_exists($file))
            {
                $id_mysql_server = self::readWorkerServerIdFromPidFile($file);
                if ($id_mysql_server === null) {
                    continue;
                }

                Debug::debug($id_mysql_server, "id mysql server");

                $elems[] = $id_mysql_server;
            }

        }

        Debug::debug($elems);

        $count_values = self::countWorkerServerIds($elems);

        Debug::debug($count_values);

        $result = array_keys(array_filter($count_values, fn($count) => $count >= 1));

        Debug::debug($result);

        if (count($result) > 0) {
            $this->logger->warning("getListofWorkingServer worker_".$worker_type." : ".json_encode($result)."");
        }
        

        return $result;
    }


    /* Remove old expired PID 
    
    param : ssh mysql proxysql
    */

    public static function deleteExpiredPid($param)
    {
        Debug::parseDebug($param);
        $dir = EngineV4::PATH_LOCK;

        $dir = substr($dir,0,-1);

        $worker_type = $param[0] ?? "mysql";

        $elems = [];
        // Parcourir tous les fichiers du dossier
        foreach (glob("{$dir}/worker_".$worker_type."::*.pid") as $file) {
            // Extraire le PID depuis le nom du fichier
            if (preg_match('/worker_'.$worker_type.'::(\d+)\.pid$/', $file, $m)) {
                $pid = (int)$m[1];

                // Vérifier si le PID existe encore
                // posix_kill($pid, 0) renvoie true si le processus existe
                if ($pid > 0 && !posix_kill($pid, 0)) {
                    echo "Suppression de $file (PID $pid inactif)\n";
                    
                    if ( file_exists($file))
                    {
                        $id_mysql_server = self::readWorkerServerIdFromPidFile($file);
                        if ($id_mysql_server === null) {
                            continue;
                        }

                        Debug::debug($id_mysql_server);

                        $elems[] = $id_mysql_server;

                        unlink($file);
                    }      
                }
            }
        }

        $count_values = self::countWorkerServerIds($elems);

        //$this->logger->warning("deleteExpiredPid worker_".$worker_type." : ".json_encode($count_values).""); 
        Debug::debug($count_values);

        return $count_values;
    }

    public static function readWorkerServerIdFromPidFile(string $file): ?string
    {
        if (!is_readable($file)) {
            return null;
        }

        $id_mysql_server = @file_get_contents($file);
        if ($id_mysql_server === false) {
            return null;
        }

        $id_mysql_server = trim($id_mysql_server);
        if ($id_mysql_server === '' || $id_mysql_server === 'Waiting...') {
            return null;
        }

        return $id_mysql_server;
    }

    public static function countWorkerServerIds(array $ids): array
    {
        $normalized_ids = [];
        foreach ($ids as $id) {
            if (is_string($id) || is_int($id)) {
                $normalized_ids[] = (string) $id;
            }
        }

        return array_count_values($normalized_ids);
    }


/**
 * Handle `refresh`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for refresh.
 * @phpstan-return void
 * @psalm-return void
 * @example refresh(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public static function refresh($param)
    {
        Debug::parseDebug($param);
        $elems = Extraction2::display(['version', 'mysql_available']);

        foreach($elems as $id_mysql_server => $elem)
        {
            if (!empty($elem['mysql_available']))
            {
                if (empty($elem['version']) || $elem['version'] == "N/A")
                {
                    Debug::debug($elem);

                    // purge file
                    $path = EngineV4::PATH_MD5."*::$id_mysql_server.md5";
                    Debug::debug($path);
                    $files = glob($path);

                    Debug::debug($files, "FILE TO DELETE");
                    foreach($files as $file)
                    {
                        
                        if (file_exists($file))
                        {
                            unlink($file);
                        }
                    }
                }
            }
        }
    }

/**
 * Handle `purgeAll`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for purgeAll.
 * @phpstan-return void
 * @psalm-return void
 * @example purgeAll(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public static function purgeAll($param)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql ="SET FOREIGN_KEY_CHECKS=0;";
        Debug::sql($sql);
        $db->sql_query($sql);

        $sql ="TRUNCATE TABLE worker_execution";
        Debug::sql($sql);
        $db->sql_query($sql);

        $sql ="TRUNCATE TABLE worker_run";
        Debug::sql($sql);
        $db->sql_query($sql);
        
        $sql ="SET FOREIGN_KEY_CHECKS=1;";
        Debug::sql($sql);
        $db->sql_query($sql);
    }
}
