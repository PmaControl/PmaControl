<?php

use \Glial\Synapse\Controller;
use \Glial\Cli\SetTimeLimit;
use Fuz\Component\SharedMemory\Storage\StorageFile;
use Fuz\Component\SharedMemory\SharedMemory;
use App\Library\ParseCnf;

//require ROOT."/application/library/Filter.php";

class Aspirateur extends Controller
{

    use \App\Library\Filter;

    use \App\Library\Debug;
    var $shared;

    /**
     * (PmaControl 2.0)<br/>
     * @author Aurélien LEQUOY, <aurelien.lequoy@esysteme.com>
     * @return boolean Success
     * @package Controller
     * @since 2.0 First time this was introduced.
     * @description get list of MySQL server and try to connect on each one
     * @access public
     * @debug ./glial Aspirateur testAllMysql 6  loop:28 --debug
     */
    public function testAllMysql($param)
    {

        $id_daemon  = $param[0];
        $date_start = microtime(true);

        $this->allocate_shared_storage();


        $this->parseDebug($param);

        if ($this->debug) {
            echo "[".date('Y-m-d H:i:s')."]"." Start all tests\n";
        }

        $this->view = false;
        $db         = $this->di['db']->sql(DB_DEFAULT);
        $sql        = "select id,name from mysql_server WHERE is_monitored =1;";

        $this->debug($sql);
        $res = $db->sql_query($sql);

        $server_list = array();
        while ($ob          = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $server_list[] = $ob;
        }


        $sql = "SELECT * FROM daemon_main where id=".$id_daemon;
        $res = $db->sql_query($sql);

        if ($db->sql_num_rows($res) !== 1) {
            throw new \Exception("PMACTRL-874 : This daemon with id=".$id_daemon." doesn't exist !", 80);
        }

        while ($ob = $db->sql_fetch_object($res)) {
            $maxThreads       = $ob->thread_concurency; // check MySQL server x by x
            $maxExecutionTime = $ob->max_delay;
        }

        $this->debug("max execution time : ".$maxExecutionTime);


        //to prevent any trouble with fork
        //$this->debugShowQueries();
        $db->sql_close();

        //$maxThreads = \Glial\System\Cpu::getCpuCores();

        $this->debug("Nombre de threads : ".$maxThreads);


        $openThreads     = 0;
        $child_processes = array();

        if (empty($server_list)) {

            $this->logger->info(Color::getColoredString('List of server to test is empty', "grey", "red"));
            sleep(1);
            //throw new Exception("List of server to test is empty", 20);
        }


        $this->checkPoint("Avant MultiThread");

        $father = false;
        foreach ($server_list as $server) {
            //
            //echo str_repeat("#", count($child_processes)) . "\n";

            $pid                   = pcntl_fork();
            $child_processes[$pid] = 1;

            if ($pid == -1) {
                throw new Exception('PMACTRL-057 : Couldn\'t fork thread !', 80);
            } else if ($pid) {


                $this->debug($pid, "PID");
                $this->checkPoint("[START] : ".$pid." [".$server['name']."]");
                if (count($child_processes) > $maxThreads) {
                    $childPid = pcntl_wait($status);
                    $this->checkPoint("[END] : ".$server['name']);
                    unset($child_processes[$childPid]);
                }
                $father = true;
            } else {

                // one thread to test each MySQL server

                $this->testMysqlServer(array($server['name'], $server['id'], $maxExecutionTime));

                $father = false;
                //we want that child exit the foreach
                break;
            }
            usleep(500);
            //$this->debug($child_processes);
        }

        if ($father) {

            $tmp = $child_processes;
            foreach ($tmp as $thread) {


                $childPid = pcntl_wait($status);
                $this->checkPoint("[END] : ".$childPid);
                unset($child_processes[$childPid]);
            }

            //$this->isGaleraCluster(array());


            foreach ($server_list as $server) {

                $this->debug($this->shared[$server['name']], $server['name']);
                $this->debug($this->shared[$server['name']], $server['name']);
            }


            $time = microtime(true) - $date_start;
            $this->debug("All tests termined : ".round($time, 2)." sec");
        } else {

            exit;
        }

        $this->debugQueriesOff();
    }

    /**
     * (PmaControl 1.0)<br/>
     * @author Aurélien LEQUOY, <aurelien.lequoy@esysteme.com>
     * @return boolean Success
     * @package Controller
     * @since 1.0 First time this was introduced.
     * @description launch a subprocess limited in time to try MySQL connection, if ok get status and show master/slave status
     * @access public
     * @debug 
     */
    public function testMysqlServer($param)
    {

        $this->parseDebug($param);


        $this->view = false;

        $name_server = $param[0];
        $id_server   = $param[1];

        //$this->debug($name_server, "Name server");
        //$this->debug($id_server, "Id server");

        if (empty($param[2])) {
            $max_execution_time = 3;
        } else {
            $max_execution_time = $param[2];
        }

        //$this->debug($max_execution_time, "Max execution time");
        //execute a process with a timelimit (in case of MySQL don't answer and keep connection)
        //$max_execution_time = 20; // in seconds
        //$this->debug("monitoring : " . $server['name'] . ":" . $server['id']);

        $this->checkPoint("Avant TimeLimit");
        $ret = SetTimeLimit::run("Aspirateur", "tryMysqlConnection", array($name_server, $id_server), $max_execution_time, $this);

        $this->checkPoint("Après TimeLimit");

        //$this->debug($ret, "RET");

        if (!SetTimeLimit::exitWithoutError($ret)) {
            /* in case of somthing wrong :
             * server don't answer
             * server didn't give msg
             * wrong credentials
             * error in PHP script
             */
            $db = $this->di['db']->sql(DB_DEFAULT);

            //in case of no answer provided we create a msg of error
            if (empty($ret['stdout'])) {
                $ret['stdout'] = "[".date("Y-m-d H:i:s")."]"." Server MySQL didn't answer in time (delay max : ".$max_execution_time." seconds)";
            }

            $sql = "UPDATE mysql_server SET `error`='".$db->sql_real_escape_string($ret['stdout'])."',is_available=0, `date_refresh`='".date("Y-m-d H:i:s")."' where id = '".$id_server."'";
            $db->sql_query($sql);

            $db->sql_close();

            echo ($this->debug) ? $name_server." KO :\n" : "";
            ($this->debug) ? print_r($ret) : '';
            return false;
        } else {
            //echo ($this->debug) ? $server['name']." OK \n" : "";

            return $ret;

            //return true;
        }
    }

    /**
     * (PmaControl 0.8)<br/>
     * @example ./glial aspirateur tryMysqlConnection name id_mysql_server
     * @author Aurélien LEQUOY, <aurelien.lequoy@esysteme.com>
     * @return boolean Success
     * @package Controller
     * @since 0.8 First time this was introduced.
     * @description try to connect successfully on MySQL, if any one error in process even in PHP it throw a new Exception.
     * @access public
     */
    public function tryMysqlConnection($param)
    {

        $this->parseDebug($param);
        $this->view = false;

        $name_server = $param[0];
        $id_server   = $param[1];

        $this->allocate_shared_storage();

        $lock_file = TMP."lock/".$name_server.".txt";


        $fp = fopen($lock_file, "w");


        /*
          if (!is_writable($lock_file)) {
          throw new \Exception("PMACTRL-068 lock file : " . $lock_file . " is not writable !", 80);
          } */

        if (!flock($fp, LOCK_EX | LOCK_NB)) {
            fwrite(STDERR, 'Un processus est déjà en cours');
            exit(15);
        } else {
            ftruncate($fp, 0);
            fwrite($fp, getmypid());
        }


        $this->checkPoint('avant query');

        $time_start             = microtime(true);
        $mysql_tested           = $this->di['db']->sql($name_server);
        $data['server']['ping'] = microtime(true) - $time_start;

        //$res = $mysql_tested->sql_multi_query("SHOW /*!40003 GLOBAL*/ VARIABLES; SHOW /*!40003 GLOBAL*/ STATUS; SHOW SLAVE STATUS; SHOW MASTER STATUS;");

        // SHOW /*!50000 ENGINE*/ INNODB STATUS


        $this->debug("Avant");
        $data['variables'] = $mysql_tested->getVariables();
        $this->debug("apres Variables");
        $data['status']    = $mysql_tested->getStatus();
        $this->debug("apres status");
        $data['master']    = $mysql_tested->isMaster();
        $this->debug("apres master");
        $data['slave']     = $mysql_tested->isSlave();
        $this->debug("apres slave");


        $this->debug($data['slave']);

        $this->checkPoint('apres query');

        /* mysql > 5.6
          $sql = "SELECT `NAME`,`COUNT`,`TYPE` FROM `INFORMATION_SCHEMA`.`INNODB_METRICS` WHERE `STATUS` = 'enabled';";
          $res = $mysql_tested->sql_query($sql);

          while ($ob = $mysql_tested->sql_fetch_object($res)) {
          $data['innodb'][$ob->NAME] = $ob->COUNT;
          } */

        $date[date('Y-m-d H:i:s')][$id_server] = $data;
        //$this->debug($date);
        //$json                                  = json_encode($date);
        //$this->debug($data['server']['ping'], "ping");

        $err = error_get_last();


        if ($err !== NULL) {
            throw new \Exception('PMACTRL-056 : '.$err['message'].' in '.$err['file'].' on line '.$err['line'], 80);
        }

        $this->allocate_shared_storage();

        $lock_file = TMP."lock/".$name_server.".txt";


        $fp = fopen($lock_file, "w");

        if (!is_writable($lock_file)) {
            throw new \Exception("PMACTRL-068 lock file : ".$lock_file." is not writable !", 80);
        }

        if (!flock($fp, LOCK_EX | LOCK_NB)) {
            fwrite(STDERR, 'Un processus est déjà en cours');
            exit(15);
        } else {
            ftruncate($fp, 0);
            fwrite($fp, getmypid());
        }

        //push data in memory
        $this->shared->{$id_server} = $date;

        fflush($fp);            // libère le contenu avant d'enlever le verrou
        flock($fp, LOCK_UN);    // Enlève le verrou


        fclose($fp);
        unlink($lock_file);


        //$this->showQueries();
        $mysql_tested->sql_close();


        $this->debugQueriesOff();
    }


    public function allocate_shared_storage()
    {
        //storage shared
        $storage      = new StorageFile('/dev/shm/answer_'.time()); // to export in config ?
        $this->shared = new SharedMemory($storage);
    }

// https://github.com/php-amqplib/php-amqplib
    //each minute ?


    public function TrySystemSsh()
    {
        $ret = ParseCnf::getCnf("/etc/mysql/my.cnf");

        debug($ret);
    }


    
}