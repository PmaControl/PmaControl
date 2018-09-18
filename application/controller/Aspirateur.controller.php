<?php

use \Glial\Synapse\Controller;
use \Glial\Cli\SetTimeLimit;
use Fuz\Component\SharedMemory\Storage\StorageFile;
use Fuz\Component\SharedMemory\SharedMemory;
use App\Library\ParseCnf;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;
use \App\Library\Debug;
use \App\Library\Ssh;
use App\Library\Chiffrement;

//require ROOT."/application/library/Filter.php";

class Aspirateur extends Controller
{

    use \App\Library\Filter;
    var $shared = array();

    /*
     * (PmaControl 0.8)<br/>
     * @author Aurélien LEQUOY, <aurelien.lequoy@esysteme.com>
     * @return boolean Success
     * @package Controller
     * @since 0.8 First time this was introduced.
     * @description log error & start / stop daemon
     * @access public
     */

    public function before($param)
    {
        $logger       = new Logger('Daemon');
        $file_log     = LOG_FILE;
        $handler      = new StreamHandler($file_log, Logger::INFO);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $logger->pushHandler($handler);
        $this->logger = $logger;
    }

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

        $this->allocate_shared_storage('answer');
        $this->allocate_shared_storage('variable');


        Debug::parseDebug($param);

        if (Debug::$debug) {
            echo "[".date('Y-m-d H:i:s')."]"." Start all tests\n";
        }

        $this->view = false;
        $db         = $this->di['db']->sql(DB_DEFAULT);
        $sql        = "select id,name from mysql_server WHERE is_monitored =1;";

        Debug::debug($sql);
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

        Debug::debug("max execution time : ".$maxExecutionTime);


        //to prevent any trouble with fork
        //$this->debugShowQueries();
        $db->sql_close();

        //$maxThreads = \Glial\System\Cpu::getCpuCores();

        Debug::debug("Nombre de threads : ".$maxThreads);


        $openThreads     = 0;
        $child_processes = array();

        if (empty($server_list)) {

            $this->logger->info(Color::getColoredString('List of server to test is empty', "grey", "red"));
            sleep(1);
            //throw new Exception("List of server to test is empty", 20);
        }


        Debug::checkPoint("Avant MultiThread");

        $father = false;
        foreach ($server_list as $server) {
            //
            //echo str_repeat("#", count($child_processes)) . "\n";

            $pid                   = pcntl_fork();
            $child_processes[$pid] = 1;

            if ($pid == -1) {
                throw new Exception('PMACTRL-057 : Couldn\'t fork thread !', 80);
            } else if ($pid) {


                Debug::debug($pid, "PID");
                Debug::checkPoint("[START] : ".$pid." [".$server['name']."]");
                if (count($child_processes) > $maxThreads) {
                    $childPid = pcntl_wait($status);
                    Debug::checkPoint("[END] : ".$server['name']);
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
            //Debug::debug($child_processes);
        }

        if ($father) {

            $tmp = $child_processes;
            foreach ($tmp as $thread) {


                $childPid = pcntl_wait($status);
                Debug::checkPoint("[END] : ".$childPid);
                unset($child_processes[$childPid]);
            }

            //$this->isGaleraCluster(array());

            foreach ($server_list as $server) {
                Debug::debug($this->shared[$server['name']], $server['name']);
                Debug::debug($this->shared[$server['name']], $server['name']);
            }


            $time = microtime(true) - $date_start;
            Debug::debug("All tests termined : ".round($time, 2)." sec");
        } else {

            exit;
        }

        Debug::debugQueriesOff();
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

        Debug::parseDebug($param);


        $this->view = false;

        $name_server = $param[0];
        $id_server   = $param[1];

        //Debug::debug($name_server, "Name server");
        //Debug::debug($id_server, "Id server");

        if (empty($param[2])) {
            $max_execution_time = 3;
        } else {
            $max_execution_time = $param[2];
        }

        //Debug::debug($max_execution_time, "Max execution time");
        //execute a process with a timelimit (in case of MySQL don't answer and keep connection)
        //$max_execution_time = 20; // in seconds
        //Debug::debug("monitoring : " . $server['name'] . ":" . $server['id']);

        Debug::checkPoint("Avant TimeLimit");
        $ret = SetTimeLimit::run("Aspirateur", "tryMysqlConnection", array($name_server, $id_server), $max_execution_time, $this);

        Debug::checkPoint("Après TimeLimit");

        //Debug::debug($ret, "RET");

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

            echo (Debug::$debug) ? $name_server." KO :\n" : "";
            (Debug::$debug) ? print_r($ret) : '';
            return false;
        } else {
            //echo (Debug::$debug) ? $server['name']." OK \n" : "";

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

        Debug::parseDebug($param);
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


        Debug::checkPoint('avant query');

        $time_start             = microtime(true);
        $mysql_tested           = $this->di['db']->sql($name_server);
        $data['server']['ping'] = microtime(true) - $time_start;

        //$res = $mysql_tested->sql_multi_query("SHOW /*!40003 GLOBAL*/ VARIABLES; SHOW /*!40003 GLOBAL*/ STATUS; SHOW SLAVE STATUS; SHOW MASTER STATUS;");
        // SHOW /*!50000 ENGINE*/ INNODB STATUS


        Debug::debug("Avant");


        // traitement SHOW GLOBAL VARIABLES

        $var['variables'] = $mysql_tested->getVariables();

        if (!empty($var['variables']['gtid_binlog_pos'])) {
            unset($var['variables']['gtid_binlog_pos']);
        }

        if (!empty($var['variables']['gtid_binlog_state'])) {
            unset($var['variables']['gtid_binlog_state']);
        }

        if (!empty($var['variables']['gtid_current_pos'])) {
            unset($var['variables']['gtid_current_pos']);
        }

        if (!empty($var['variables']['gtid_slave_pos'])) {
            unset($var['variables']['gtid_slave_pos']);
        }




        Debug::debug("apres Variables");
        $data['status'] = $mysql_tested->getStatus();
        Debug::debug("apres status");
        $data['master'] = $mysql_tested->isMaster();
        Debug::debug("apres master");
        $data['slave']  = $mysql_tested->isSlave();
        Debug::debug("apres slave");


        Debug::debug($data['slave']);

        Debug::checkPoint('apres query');

        /* mysql > 5.6
          $sql = "SELECT `NAME`,`COUNT`,`TYPE` FROM `INFORMATION_SCHEMA`.`INNODB_METRICS` WHERE `STATUS` = 'enabled';";
          $res = $mysql_tested->sql_query($sql);

          while ($ob = $mysql_tested->sql_fetch_object($res)) {
          $data['innodb'][$ob->NAME] = $ob->COUNT;
          } */

        $date[date('Y-m-d H:i:s')][$id_server] = $data;
        //Debug::debug($date);
        //$json                                  = json_encode($date);
        //Debug::debug($data['server']['ping'], "ping");

        $err = error_get_last();


        if ($err !== NULL) {
            throw new \Exception('PMACTRL-056 : '.$err['message'].' in '.$err['file'].' on line '.$err['line'], 80);
        }



        $md5 = md5(json_encode($var));

        $this->allocate_tmp_storage('server_'.$id_server);

        $export_variables = false;

        Debug::debug($md5, "NEW MD5");

        if (!empty($this->shared['server_'.$id_server]->md5)) {

            Debug::debug($this->shared['server_'.$id_server]->md5, "OLD MD5");

            if ($this->shared['server_'.$id_server]->md5 != $md5) {
                $export_variables                        = true;
                $this->shared['server_'.$id_server]->md5 = $md5;
            }
        } else {
            $this->shared['server_'.$id_server]->md5 = $md5;
            $export_variables                        = true;
        }

        if ($export_variables) {
            Debug::debug($export_variables, "SET VARIABLES");
            $this->allocate_shared_storage('variable');
        }

        $this->allocate_shared_storage('answer');

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
        $this->shared['answer']->{$id_server} = $date;

        if ($export_variables) {
            $this->shared['variable']->{$id_server} = $var;
        }




        fflush($fp);            // libère le contenu avant d'enlever le verrou
        flock($fp, LOCK_UN);    // Enlève le verrou


        fclose($fp);
        unlink($lock_file);


        //$this->showQueries();
        $mysql_tested->sql_close();


        Debug::debugQueriesOff();
    }

    public function allocate_shared_storage($name = 'answer')
    {
        //storage shared
        $storage             = new StorageFile('/dev/shm/'.$name.'_'.time()); // to export in config ?
        $this->shared[$name] = new SharedMemory($storage);
    }

    public function allocate_tmp_storage($name = '')
    {
        //storage shared
        $storage             = new StorageFile('/dev/shm/'.$name); // to export in config ?
        $this->shared[$name] = new SharedMemory($storage);
    }

// https://github.com/php-amqplib/php-amqplib
    //each minute ?


    public function TrySystemSsh()
    {
        $ret = ParseCnf::getCnf("/etc/mysql/my.cnf");

        debug($ret);
    }

    public function testAllSsh($param)
    {
        $this->view = false;


        $this->loop = $param[0];

        Debug::parseDebug($param);

        $this->logger->info(str_repeat("#", 40));


        $db = $this->di['db']->sql(DB_DEFAULT);


        $sql = "SELECT a.* FROM mysql_server a
          LEFT JOIN link__mysql_server__ssh_key b ON a.id = b.id_mysql_server
          WHERE `active` = 1";

        Debug::debug(SqlFormatter::highlight($sql));
        $res = $db->sql_query($sql);

        $server_list = array();
        while ($ob          = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $server_list[] = $ob;
        }

        $sql = "SELECT * FROM daemon_main where id=4;";
        $res = $db->sql_query($sql);

        Debug::debug(SqlFormatter::highlight($sql));

        while ($ob = $db->sql_fetch_object($res)) {
            $maxThreads       = $ob->thread_concurency; // check MySQL server x by x
            $maxExecutionTime = $ob->max_delay;
        }

        //to prevent any trouble with fork
        $db->sql_close();

        //$maxThreads = \Glial\System\Cpu::getCpuCores();

        $openThreads     = 0;
        $child_processes = array();

        if (empty($server_list)) {
            sleep(10);
            $this->logger->info(Color::getColoredString('List of server to test is empty', "grey", "red"));
            //throw new Exception("List of server to test is empty", 20);
        }


        //to prevent collision at first running (the first run is not made in multi thread
        if ($this->loop == 0) {

            $maxThreads = 1;
            $this->loop = 1;
        }


        $father = false;
        foreach ($server_list as $server) {
            //echo str_repeat("#", count($child_processes)) . "\n";

            $pid                   = pcntl_fork();
            $child_processes[$pid] = 1;

            if ($pid == -1) {
                throw new Exception('PMACTRL-057 : Couldn\'t fork thread !', 80);
            } else if ($pid) {



                if (count($child_processes) > $maxThreads) {
                    $childPid = pcntl_wait($status);
                    unset($child_processes[$childPid]);
                }
                $father = true;
            } else {

                // one thread to test each MySQL server
                //$this->logger->info("Test SSH server (" . $server['id'].")");
                $this->testSshServer($server['id'], $this->loop, $maxExecutionTime);
                $father = false;
                //we want that child exit the foreach
                break;
            }
            usleep(500);
        }

        if ($father) {
            $tmp = $child_processes;
            foreach ($tmp as $thread) {
                $childPid = pcntl_wait($status);
                unset($child_processes[$childPid]);
            }

            if (Debug::$debug) {
                echo "[".date('Y-m-d H:i:s')."]"." All tests termined\n";
            }
        } else {
            exit;
        }
    }

    public function testSshServer($server_id, $id_loop, $max_execution_time = 20)
    {
        //exeute a process with a timelimit (in case of SSH server don't answer and keep connection)
        //$max_execution_time = 20; // in seconds



        $debug = "";
        if (Debug::$debug) {
            $debug = " --debug ";
        }

        //$this->logger->info("trySshConnection (" . $server_id.")");

        $ret = SetTimeLimit::run("Aspirateur", "trySshConnection", array($server_id, $id_loop, $debug), $max_execution_time);

        Debug::debug($ret);

        $db = $this->di['db']->sql(DB_DEFAULT);


        if (!SetTimeLimit::exitWithoutError($ret)) {
            /* in case of somthing wrong :
             * server don't answer
             * server didn't give msg
             * wrong credentials
             * error in PHP script
             */


            //in case of no answer provided we create a msg of error
            if (empty($ret['stdout'])) {
                $ret['stdout'] = "[".date("Y-m-d H:i:s")."]"." Server Ssh didn't answer in time (delay max : ".$max_execution_time." seconds)";

                Debug::debug($ret['stdout']);
            }

            //echo $sql . "\n";
            //$sql = "UPDATE mysql_server SET ssh_available=0 where id = '".$server_id."'";
            //$db->sql_query($sql);

            Debug::debug("Server ID : ".$server_id."(FAILED !)");

            $db->sql_close();

            return false;
        } else {

            //$sql = "UPDATE mysql_server SET ssh_available=1 where id = '".$server_id."'";
            //$db->sql_query($sql);

            Debug::debug("Server ID : ".$server_id." (answered in time)");
            //echo (Debug::$debug) ? $server['name']." OK \n" : "";
            return true;
        }
    }

    public function trySshConnection($param)
    {
        $this->view = false;
        $id_server  = $param[0];

        Debug::parseDebug($param);

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT a.id, a.ip,c.user,c.private_key FROM `mysql_server` a
        INNER JOIN `link__mysql_server__ssh_key` b ON a.id = b.id_mysql_server 
        INNER JOIN `ssh_key` c on c.id = b.id_ssh_key
        where a.id=".$id_server." AND b.`active` = 1 LIMIT 1;";

        Debug::debug(SqlFormatter::highlight($sql));

        $res = $db->sql_query($sql);



        while ($ob = $db->sql_fetch_object($res)) {


            $time_start             = microtime(true);
            $ssh                    = Ssh::connect($ob->ip, 22, $ob->user, Chiffrement::decrypt($ob->private_key));
            $data['server']['ping'] = microtime(true) - $time_start;


            $stats = $this->getStats($ssh, $data);

            $hardware = $this->getHardware($ssh, $data);



            $id = $ob->id;

            $date = array();

            $this->allocate_shared_storage('ssh_stats');
            $date[date('Y-m-d H:i:s')][$ob->id]['stats'] = $stats;
            $this->shared->$id                           = $date;

            $this->allocate_shared_storage('hardware');
            $date[date('Y-m-d H:i:s')][$ob->id]['hardware'] = $hardware;

            Debug::debug($date);
        }



        Debug::debug(SqlFormatter::highlight($sql));
        $db->sql_query($sql);
        $db->sql_close();
    }

    private function getHardware($ssh)
    {

        //$hardware['memory']           = $ssh->exec("grep MemTotal /proc/meminfo | awk '{print $2}'") or die("error");
        $hardware['cpu_thread_count'] = trim($ssh->exec("cat /proc/cpuinfo | grep processor | wc -l"));


        $brut_memory = $ssh->exec("cat /proc/meminfo | grep MemTotal");
        preg_match("/[0-9]+/", $brut_memory, $memory);

        $mem    = $memory[0];
        $memory = sprintf('%.2f', $memory[0] / 1024 / 1024)." Go";


        $hardware['memory'] = $memory;

        $freq_brut                 = $ssh->exec("cat /proc/cpuinfo | grep 'cpu MHz'");
        preg_match("/[0-9]+\.[0-9]+/", $freq_brut, $freq);
        $hardware['cpu_frequency'] = sprintf('%.2f', ($freq[0] / 1000))." GHz";


        $hardware['os'] = trim($ssh->exec("lsb_release -ds 2> /dev/null"));
        $distributor    = trim($ssh->exec("lsb_release -si 2> /dev/null"));

        if ($distributor === "RedHatEnterpriseServer") {
            $distributor = "RedHat";
        }


        if (empty($os)) {
            $os          = trim($ssh->exec("cat /etc/centos-release 2> /dev/null"));
            $distributor = trim("Centos");
        }

        if (empty($os)) {
            $version = trim($ssh->exec("cat /etc/debian_version 2> /dev/null"));
            if (!empty($version)) {
                $distributor = trim("Debian");

                switch ($version{0}) {
                    case "4": $codename = "Etch";
                        break;
                    case "5": $codename = "Lenny";
                        break;
                    case "6": $codename = "Squeeze";
                        break;
                    case "7": $codename = "Wheezy";
                        break;
                    case "8": $codename = "Jessie";
                        break;
                    case "9": $codename = "Stretch";
                        break;
                }

                $os = trim("Debian GNU/Linux ".$version." (".$codename.")");
            }
        }

        $hardware['distributor']  = trim($distributor);
        $hardware['os']           = trim($os);
        $hardware['codename']     = trim($codename);
        $hardware['product_name'] = trim($ssh->exec("dmidecode -s system-product-name 2> /dev/null"));
        $hardware['arch']         = trim($ssh->exec("uname -m"));
        $hardware['kernel']       = trim($ssh->exec("uname -r"));
        $hardware['hostname']     = trim($ssh->exec("hostname"));
        $hardware['swapiness']    = $ssh->exec("cat /proc/sys/vm/swappiness");

        return $hardware;
    }

    public function getStats($ssh)
    {
        $stats = array();

        $uptime = $ssh->exec("uptime");

        preg_match("/averages?:\s*([0-9]+[\.|\,][0-9]+)[\s|\.\,]\s+([0-9]+[\.|\,][0-9]+)[\s|\.\,]\s+([0-9]+[\.|\,][0-9]+)/", $uptime, $output_array);

        if (!empty($output_array[1])) {
            $stats['load_average_5_sec']  = $output_array[1];
            $stats['load_average_5_min']  = $output_array[2];
            $stats['load_average_15_min'] = $output_array[3];
        }

        preg_match("/([0-9]+)\s+user/", $uptime, $output_array);
        if (!empty($output_array[1])) {
            $stats['user_connected'] = $output_array[1];
        }


        preg_match("/up\s+([0-9]+\s[a-z]+),/", $uptime, $output_array);
        if (!empty($output_array[1])) {
            $stats['uptime'] = $output_array[1];
        }


        $membrut = trim($ssh->exec("free -b"));

        $lines = explode("\n", $membrut);

        unset($lines[0]);


        $titles = array('memory', 'swap');
        $items  = array('total', 'used', 'free', 'shared', 'buff/cache', 'available');


        $i = 1;
        foreach ($titles as $title) {
            $elems = preg_split('/\s+/', $lines[$i]);
            unset($elems[0]); // to remove Mem: and Swap:


            $j = 0;
            foreach ($elems as $elem) {
                $stats[$title.'_'.$items[$j]] = $elem;
                $j++;
            }

            $i++;
        }

        $dd    = trim($ssh->exec("df"));
        $lines = explode("\n", $dd);

        $items = array('Filesystem', 'Size', 'Used', 'Avail', 'Use%', 'Mounted on');

        unset($lines[0]);


        $tmp = array();
        foreach ($lines as $line) {
            $elems = preg_split('/\s+/', $line);

            $tmp[$elems[5]] = $elems;
        }

        $stats['disks'] = json_encode($tmp);


        $ips = trim($ssh->exec("ip addr | grep 'state UP' -A2 | awk '{print $2}' | cut -f1 -d'/' | grep -Eo '([0-9]*\.){3}[0-9]*'"));



        $stats['ips'] = json_encode(explode("\n", $ips));



        $cpus = trim($ssh->exec("grep 'cpu' /proc/stat"));
        //$cpus = trim(shell_exec("grep 'cpu' /proc/stat"));

        $cpu_lines = explode("\n", $cpus);


        $i = 0;
        foreach ($cpu_lines as $line) {

            $elems = preg_split('/\s+/', $line);

            //debug($elems);
            //system + user + idle
            if ($i === 0) {
                $stats['cpu_usage'] = (($elems[1] + $elems[3]) * 100) / ($elems[1] + $elems[3] + $elems[4]);
            } else {
                $cpu[$elems[0]] = ($elems[1] + $elems[3]) * 100 / ($elems[1] + $elems[3] + $elems[4]);
            }
            $i++;
        }
        $stats['cpu_detail'] = json_encode($cpu);



        /* io wait */

        /*
          $cpu_user = trim($ssh->exec("iostat -c | tail -2 | head -n 1"));
          $cpu_user = trim(shell_exec("iostat -c | tail -2 | head -n 1"));

          $titles = array('cpu_user%', 'cpu_nice%', 'cpu_system%', 'cpu_iowait%', 'cpu_steal%', 'cpu_idle%');

          $elems = preg_split('/\s+/', $cpu_user);
          $i     = 0;
          foreach ($titles as $title) {

          $stats[$title] = $elems[$i];
          $i++;
          } */


        $mem = trim($ssh->exec("ps aux | grep mysqld | grep -v grep | awk '{print $5,$6}'"));

        $mysql = explode(' ', $mem);

        $stats['mysqld_mem_physical'] = $mysql[1];
        $stats['mysqld_mem_virtual']  = $mysql[0];



        //ifconfig

        /*
         * SELECT sum( data_length + index_length) / 1024 / 1024 " Taille en Mo" FROM information_schema.TABLES WHERE table_schema = "WORDPRESS" GROUP BY table_schema;
         *
         */

        return $stats;
    }
}