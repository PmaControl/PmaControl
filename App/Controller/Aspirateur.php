<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use Fuz\Component\SharedMemory\Storage\StorageFile;
use Fuz\Component\SharedMemory\SharedMemory;
use App\Library\ParseCnf;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;
use \App\Library\Debug;
use \App\Library\Ssh;
use App\Library\System;
use App\Library\Mysql;
use \Glial\Cli\Color;
use \Glial\Sgbd\Sgbd;
use Pheanstalk\Pheanstalk;

/*
 *
 *
 * MySQL [(none)]> select @@version_comment limit 1;
  +-------------------+
  | @@version_comment |
  +-------------------+
  | (ProxySQL)        |
  +-------------------+
  1 row in set (0.000 sec)

 *
 */

// https://github.com/php-amqplib/php-amqplib
//each minute ?
//require ROOT."/application/library/Filter.php";
//https://blog.programster.org/php-multithreading-pool-example

class Aspirateur extends Controller
{

    use \App\Library\Filter;
    var $shared        = array();
    var $lock_variable = TMP."lock/variable/{id_mysql_server}.md5";
    var $lock_database = TMP."lock/database/{id_mysql_server}.lock";
    var $files         = array();

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
        $logger       = new Logger("Aspirateur");
        $handler      = new StreamHandler(LOG_FILE, Logger::INFO);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $logger->pushHandler($handler);
        $this->logger = $logger;
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

        $display_error = ini_get('display_errors');
        ini_set("display_errors", 0);

        Debug::parseDebug($param);
        $this->view = false;

        $name_server = $param[0];
        $id_server   = $param[1];

        $db = Sgbd::sql(DB_DEFAULT); // ?????
        $db->sql_close(); // ?????????????

        Debug::checkPoint('avant query');

        $time_start   = microtime(true);
        $mysql_tested = Sgbd::sql($name_server);

        $err = error_get_last();
        error_clear_last();

        $error_msg = "";
        if ($err !== NULL) {
            $error_msg = $err['message'];
        }

        $data                   = array();
        $data['server']['ping'] = microtime(true) - $time_start;

        /*
         * test for processing long time
          if ($id_server == 114) {
          sleep(55);
          } */

        $service                              = array();
        $service['mysql_server']['available'] = empty($error_msg) ? 1 : 0;
        $service['mysql_server']['ping']      = $data['server']['ping'];
        $service['mysql_server']['error']     = $error_msg;

        Debug::debug($service);


        $services                                  = array();
        $services[date('Y-m-d H:i:s')][$id_server] = $service;

        $this->allocate_shared_storage('service');
        $this->shared['service']->{$id_server} = $services;

        if (!empty($error_msg)) {
            echo $name_server." : ".$error_msg."\n";

            $db  = Sgbd::sql(DB_DEFAULT);
            $sql = "UPDATE `mysql_server` SET `error` ='".$db->sql_real_escape_string($error_msg)."', 
                `date_refresh` = '".date("Y-m-d H:i:s")."',
                    `is_available` = 0 WHERE id =".$id_server;
            Debug::sql($sql);

            $db->sql_query($sql);
            $db->sql_close();

            return false;
        }


        //$res = $mysql_tested->sql_multi_query("SHOW /*!40003 GLOBAL*/ VARIABLES; SHOW /*!40003 GLOBAL*/ STATUS; SHOW SLAVE STATUS; SHOW MASTER STATUS;");
        // SHOW /*!50000 ENGINE*/ INNODB STATUS

        Debug::debug("Avant");

        // traitement SHOW GLOBAL VARIABLES

        $var['variables'] = $mysql_tested->getVariables();

        Debug::debug($var['variables']['is_proxysql'], "is_proxysql");
        //shell_exec("echo 'is_proxy : ".json_encode($var['variables'])."' >> ".TMP."/proxysql");


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

        if (!empty($var['variables']['timestamp'])) {
            unset($var['variables']['timestamp']);
        }

        Debug::debug("apres Variables");
        $data['status'] = $mysql_tested->getStatus();
        Debug::debug("apres status");
        $data['master'] = $mysql_tested->isMaster();
        Debug::debug("apres master");
        $data['slave']  = $mysql_tested->isSlave();
        Debug::debug("apres slave");

        //SHOW SLAVE HOSTS; => add in glial
        //$data['processlist'] = $mysql_tested->getProcesslist(1);

        if ($var['variables']['log_bin'] === "ON") {
            $data['binlog'] = $this->binaryLog($mysql_tested);
        }

        Debug::debug("apres la récupération de la liste des binlogs");
        Debug::checkPoint('apres query');

        /* mysql > 5.6
          $sql = "SELECT `NAME`,`COUNT`,`TYPE` FROM `INFORMATION_SCHEMA`.`INNODB_METRICS` WHERE `STATUS` = 'enabled';";
          $res = $mysql_tested->sql_query($sql);

          while ($ob = $mysql_tested->sql_fetch_object($res)) {
          $data['innodb'][$ob->NAME] = $ob->COUNT;
          }
         */

        $date[date('Y-m-d H:i:s')][$id_server] = $data;

        if ($mysql_tested->is_connected === false) {
            echo "PAS BON DU TOUT ! ask creator of PmaControl";
            return false;
        }

        $md5      = md5(json_encode($var));
        $file_md5 = str_replace('{id_mysql_server}', $id_server, $this->lock_variable);

        $export_variables = false;

        Debug::debug($md5, "NEW MD5");

        if (file_exists($file_md5)) {

            $cmp_md5 = file_get_contents($file_md5);

            Debug::debug($cmp_md5, "OLD MD5");

            if ($cmp_md5 != $md5) {
                $export_variables = true;

                file_put_contents($file_md5, $md5);
            }
        } else {
            /*
              if (!is_writable(dirname($file_md5))) {
              Throw new \Exception('PMACTRL-858 : Cannot write file in directory : '.dirname($file_md5).'');
              } */

            file_put_contents($file_md5, $md5);
            $export_variables = true;
        }

        //to know if we grab statistics on databases & tables
        $lock_database = str_replace('{id_mysql_server}', $id_server, $this->lock_database);

        Debug::debug($lock_database, "lock file database");

        $get_databases = false;
        if (file_exists($lock_database)) {

            $date_last_run_db = file_get_contents($lock_database);

            if ($date_last_run_db !== date('Y-m-d')) {
                $get_databases = true;
            }
        } else {

            //test if first run, if yes remove grabbing of databases because getting to much time
            // test if last all 5 ts_dates before made it to grab data more faster

            

            $get_databases = true;
        }

        Debug::debug($get_databases, "getDatabase");

        if ($get_databases === true) {

            $data_db = $this->getDatabase($mysql_tested);

            if (!empty($data_db)) {
                $this->allocate_shared_storage('database');

                $dbs                           = array();
                $dbs['databases']['databases'] = $data_db;

                $databases                                  = array();
                $databases[date('Y-m-d H:i:s')][$id_server] = $dbs;
                $this->shared['database']->{$id_server}     = $databases;

                file_put_contents($lock_database, date('Y-m-d'));
            }
        }

        //push data in memory
        $this->allocate_shared_storage('answer');
        $this->shared['answer']->{$id_server} = $date;

        if ($export_variables) {
            $this->allocate_shared_storage('variable');
            $variables                                  = array();
            $variables[date('Y-m-d H:i:s')][$id_server] = $var;
            $this->shared['variable']->{$id_server}     = $variables;
        }

        $mysql_tested->sql_close();

        Debug::debugShowTime();
        ini_set("display_errors", $display_error);
    }

    public function allocate_shared_storage($name = 'answer')
    {
//storage shared
        $storage             = new StorageFile(TMP.'tmp_file/'.$name.'_'.time()); // to export in config ?
        $this->shared[$name] = new SharedMemory($storage);
    }
    /*
      public function after()
      {

      foreach($this->shared as $name => $array)
      {


      }
      } */

    public function trySshConnection($param)
    {
        $this->view      = false;
        $id_mysql_server = $param[0];

        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT a.id, a.ip,c.user,c.private_key FROM `mysql_server` a
        INNER JOIN `link__mysql_server__ssh_key` b ON a.id = b.id_mysql_server 
        INNER JOIN `ssh_key` c on c.id = b.id_ssh_key
        where a.id=".$id_mysql_server." AND b.`active` = 1 LIMIT 1;";

        Debug::sql($sql);

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            $time_start = microtime(true);
            $ssh        = Ssh::ssh($id_mysql_server);
            $ping       = microtime(true) - $time_start;

            if ($ssh !== false) {
                //Debug::debug($data);

                $stats    = $this->getStats($ssh);
                $hardware = $this->getHardware($ssh);

                //liberation de la connexion ssh https://github.com/phpseclib/phpseclib/issues/1194
                $ssh->disconnect();
                unset($ssh);

                $id   = $ob->id;
                $date = array();

                $this->allocate_shared_storage('ssh_stats');
                $date[date('Y-m-d H:i:s')][$ob->id]['stats']         = $stats;
                $date[date('Y-m-d H:i:s')][$ob->id]['stats']['ping'] = $ping;

                //$this->shared->$id                           = $date;
                $this->shared['ssh_stats']->{$id_mysql_server} = $date;

                $this->allocate_shared_storage('hardware');
                $date[date('Y-m-d H:i:s')][$ob->id]['hardware'] = $hardware;

                $this->shared['hardware']->{$id_mysql_server} = $date;

                Debug::debug($date);
            } else {
                //error connection ssh
            }
        }


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

        $freq_brut = $ssh->exec("cat /proc/cpuinfo | grep 'cpu MHz'");
        preg_match("/[0-9]+\.[0-9]+/", $freq_brut, $freq);

        // $freq[0] can be empty !!!

        if (empty($freq[0])) {

            //case of raspberry pi
            $freq_2 = 0;
            $freq_2 = trim($ssh->exec("cat /sys/devices/system/cpu/cpu0/cpufreq/scaling_max_freq"));

            if (!empty($freq_2)) {
                $hardware['cpu_frequency'] = round($freq_2 / 1000000, 2)." GHz";
            } else {
                $hardware['cpu_frequency'] = $freq_brut;
            }
        } else {
            $hardware['cpu_frequency'] = sprintf('%.2f', ($freq[0] / 1000))." GHz";
        }
        $os          = trim($ssh->exec("lsb_release -ds 2> /dev/null"));
        $distributor = trim($ssh->exec("lsb_release -si 2> /dev/null"));
        $codename    = trim($ssh->exec("lsb_release -cs 2> /dev/null"));

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

                $ver = explode(".", $version)[0];

                switch ($ver) {
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
                    case "10": $codename = "Buster";
                        break;
                }

                $os = trim("Debian GNU/Linux ".$version." (".$codename.")");
            }
        }

        $hardware['distributor']  = trim($distributor);
        $hardware['os']           = trim($os);
        $hardware['codename']     = trim($codename);
        $hardware['product_name'] = trim($ssh->exec("sudo dmidecode -s system-product-name 2> /dev/null"));
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

        // récupération de l'uptime et du load average
        $output_array = array();
        preg_match("/averages?:\s*([0-9]+[\.|\,][0-9]+)[\s|\.\,]\s+([0-9]+[\.|\,][0-9]+)[\s|\.\,]\s+([0-9]+[\.|\,][0-9]+)/", $uptime, $output_array);

        if (!empty($output_array[1])) {
            $stats['load_average_5_sec']  = str_replace(',', '.', $output_array[1]);
            $stats['load_average_5_min']  = str_replace(',', '.', $output_array[2]);
            $stats['load_average_15_min'] = str_replace(',', '.', $output_array[3]);
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
        $stats   = $this->getSwap($membrut);

        //on exclu les montage nfs
        $dd = trim($ssh->exec("df -l"));

        $lines = explode("\n", $dd);
        $items = array('Filesystem', 'Size', 'Used', 'Avail', 'Use%', 'Mounted on');
        unset($lines[0]);

        $tmp = array();
        foreach ($lines as $line) {

            $elems          = preg_split('/\s+/', $line);
            $tmp[$elems[5]] = $elems;
        }

        $stats['disks'] = json_encode($tmp);

        $ips = trim($ssh->exec("ip addr | grep 'state UP' -A2 | awk '{print $2}' | cut -f1 -d'/' | grep -Eo '([0-9]*\.){3}[0-9]*'"));

        $stats['ips'] = json_encode(explode("\n", $ips));
        $cpus         = trim($ssh->exec("grep 'cpu' /proc/stat"));
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
          }
         */


        /*
          $tmp_mem = trim($ssh->exec("ps aux | grep 'mysqld ' | grep -v grep | awk '{print $5,$6}'"));

          $mem = explode("\n", $tmp_mem);
          $mysql = explode(' ', end($mem));

          $stats['mysqld_mem_physical'] = $mysql[1];
          $stats['mysqld_mem_virtual'] = $mysql[0];
         */


//ifconfig

        /*
         * SELECT sum( data_length + index_length) / 1024 / 1024 " Taille en Mo" FROM information_schema.TABLES WHERE table_schema = "WORDPRESS" GROUP BY table_schema;
         *
         */

        return $stats;
    }

    /**
     * @example : ./glial aspirateur addToQueue 11 --debug
     *
     * Ajoute les serveurs monitoré dans la queue qui va etre ensuite traité par les workers
     * 
     */
    public function addToQueueMySQL($param)
    {

        //$param[] = '--debug';
        Debug::parseDebug($param);

        $id_daemon = $param[0];

        if (empty($id_daemon)) {
            throw new \Exception('PMATRL-347 : Arguement id_daemon missing');
        }

        if (Debug::$debug) {
            echo "[".date('Y-m-d H:i:s')."]"." Start all tests\n";
        }

        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM daemon_main WHERE id=".$id_daemon.";";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $queue_key        = intval($ob->queue_key);
            $maxExecutionTime = $ob->max_delay;
        }

//add message to queue
        $queue    = msg_get_queue($queue_key);
        $msg_qnum = (int) msg_stat_queue($queue)['msg_qnum'];

        // on attend d'avoir vider la file d'attente avant d'avoir une nouvelle liste de message (30 sec maximum)
        if ($msg_qnum != 0) {

            Debug::debug('On attends de vider la file d\'attente');

            for ($i = 0; $i < $maxExecutionTime; $i++) {
                $msg_qnum = msg_stat_queue($queue)['msg_qnum'];
                if ($msg_qnum == 0) {
                    break;
                } else {
                    Debug::debug($msg_qnum, "Nombre de message en attente");
                }
                sleep(1);
            }
        }


        $mysql_servers = array();

        //mémoire partagé 

        $lock_directory = TMP."lock/worker/*.lock";

        $elems = array();
        foreach (glob($lock_directory) as $filename) {

            Debug::debug($filename, "filename");

            $json    = file_get_contents($filename);
            $data    = json_decode($json, true);
            $elems[] = $data;
        }


        Debug::debug($elems, "liste des serveur en retard !");

        //$list = array();


        $db = Sgbd::sql(DB_DEFAULT);

        foreach ($elems as $server) {


            //on verifie avec le double buffer qu'on est bien sur le même pid
            //et ce dernier est toujours sur le serveur MySQL qui pose problème
            $idmysqlserver = trim(file_get_contents(TMP."lock/worker/".$server['pid'].".pid"));

            // si le pid n'existe plus le fichier de temporaire sera surcharger au prochain run
            if (System::isRunningPid($server['pid']) === true && $idmysqlserver == $server['id']) {


                Debug::debug($server['pid'], "GOOD");

                $mysql_servers[] = $server['id'];
                //$list[] = Color::getColoredString("MySQL server with id : " . $server['id'] . " is late !!! pid : " . $server['pid'], "grey", "red");

                $time = microtime(true) - $server['microtime'];
                $this->logger->warning("MySQL server with id : ".$server['id']." is late !!! Worker still runnig since ".round($time, 2)." seconds - pid : ".$server['pid']);

                //special case for timeout 60 seconds, else we see working since ... and not the real error
                $sql = "SELECT error,is_available from mysql_server WHERE id = ".$server['id'].";";
                $res = $db->sql_query($sql);

                while ($ob = $db->sql_fetch_object($res)) {
                    if ($ob->is_available != 0) {
                        // UPDATE is_available X => YELLOW  (not answered)
                        $sql = "UPDATE `mysql_server` SET is_available = -1,
                            `date_refresh` = '".date("Y-m-d H:i:s")."',
                            `warning`= 'Worker still runnig since ".round($time, 2)." seconds' WHERE `id` =".$server['id'].";";
                        echo \SqlFormatter::format($sql);
                        $db->sql_query($sql);
                    }
                }
            } else {
                //si pid n'existe plus alors on efface le fichier de lock
                $lock_file = TMP."lock/worker/".$server['id'].".lock";

                unlink($lock_file);
            }
        }

        //echo implode("\n", $list) . "\n";
        //Debug::debug($list, "list");


        $this->view = false;

        $sql = "select a.id,a.name from mysql_server a
            INNER JOIN client b on a.id_client =b.id
            WHERE a.is_monitored =1 and b.is_monitored=1";

        if (!empty($mysql_servers)) {
            $sql .= " AND a.id NOT IN (".implode(',', $mysql_servers).")";
        }

        $sql .= " ORDER by is_available ASC, date_refresh DESC;";

        //echo \SqlFormatter::format($sql);

        Debug::sql($sql);
        $res = $db->sql_query($sql);

        $server_list = array();
        while ($ob          = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $server_list[] = $ob;
        }

        //Debug::debug($server_list, "Liste des serveurs monitoré");
        //to prevent any trouble with fork
        //$this->debugShowQueries();

        $db->sql_close();

        // filename: add_to_queue.php
        //creating a queue requires we come up with an arbitrary number


        foreach ($server_list as $server) {

            // Create dummy message object
            $object       = new \stdclass;
            $object->name = $server['name'];
            $object->id   = $server['id'];

            //try to add message to queue
            if (msg_send($queue, 1, $object)) {

                Debug::debug($server, "Ajout dans la file d'attente");
                //echo "added to queue  \n";
                // you can use the msg_stat_queue() function to see queue status
                //print_r(msg_stat_queue($queue));
            } else {
                echo "could not add message to queue \n";
            }
        }

//$stats = msg_stat_queue($queue);
//debug($stats);
    }

    public function worker()
    {


        $pid = getmypid();

        //get mypid
        //start worker => pid / id_mysql_server

        $db = Sgbd::sql(DB_DEFAULT);
        $db->sql_close();
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM daemon_main WHERE id=11;";

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

        while (msg_receive($queue, 1, $msg_type, $max_msg_size, $msg)) {
            //echo "[" . date("Y-m-d H:i:s") . "] Message pulled from queue - id:{$msg->id}, name:{$msg->name} [[" . $pid . "]]\n";

            $id_mysql_server = $msg->id;

            $data['id']        = $id_mysql_server;
            $data['microtime'] = microtime(true);

            $lock_file = TMP."lock/worker/".$id_mysql_server.".lock";

            $double_buffer = TMP."lock/worker/".$pid.".pid";

            $fp = fopen($lock_file, "w+");
            fwrite($fp, json_encode($data));
            fflush($fp);            // libère le contenu avant d'enlever le verrou
            fclose($fp);

            $fp2 = fopen($double_buffer, "w+");
            fwrite($fp2, $id_mysql_server);
            fflush($fp2);            // libère le contenu avant d'enlever le verrou
            fclose($fp2);

            //do your business logic here and process this message!

            $this->tryMysqlConnection(array($msg->name, $msg->id));

            /*
              if ($msg->id == "16") {
              sleep(60);
              }
              /**
             * test retard reponse mysql
             */
            if (file_exists($lock_file)) {
                unlink($lock_file);
            }

            //finally, reset our msg vars for when we loop and run again
            $msg_type = NULL;
            $msg      = NULL;
        }



//remove pid and id_mysql_server
    }

    public function checkWorker($param)
    {

        $id_daemon_main = intval($param[0]);
        Debug::parseDebug($param);

        $debug = '';
        if (Debug::$debug === true) {
            $debug = "--debug";
        }

        if (empty($id_daemon_main)) {
            throw new \Exception("PMATRL-586 : the first param should be and int matching daemon_main.id");
        }

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM `daemon_main` WHERE `id`=".$id_daemon_main.";";
        $res = $db->sql_query($sql);

        Debug::sql($sql);

        while ($ob = $db->sql_fetch_object($res)) {


            $sql2 = "SELECT * FROM `daemon_worker` where `id_daemon_main` = ".$ob->id.";";
            $res2 = $db->sql_query($sql2);
            Debug::sql($sql2);

            $nb_thread = 0;
            while ($ob2       = $db->sql_fetch_object($res2)) {


                $available = System::isRunningPid($ob2->pid);

                Debug::debug($available, "Result of pid : ".$ob2->pid);

                if ($available === false) {


                    $file = file_get_contents(TMP."log/worker_".$id_daemon_main."_".$ob2->id.".log");
                    Debug::debug($file, "FILE");

                    //remove pid of worker there

                    $this->addWorker(array($ob2->id, $id_daemon_main, $debug));
                }

                $nb_thread++;
            }


            Debug::debug($nb_thread, "\$nb_thread");
            Debug::debug($ob->thread_concurency, "\$ob->thread_concurency");

            if ($ob->thread_concurency > $nb_thread) {
                $tocreate = $ob->thread_concurency - $nb_thread;

                for ($i = 0; $i < $tocreate; $i++) {


                    $this->addWorker(array("0", $id_daemon_main, $debug));

                    Debug::debug("Add worker");
                }
            } elseif ($ob->thread_concurency < $nb_thread) {
                $todelete = $nb_thread - $ob->thread_concurency;

                for ($i = 0; $i < $todelete; $i++) {
                    $this->removeWorker(array($id_daemon_main, $debug));

                    Debug::debug("Remove worker");
                }
            }
        }
    }

    public function addWorker($param)
    {
        Debug::parseDebug($param);

        $id_daemon_worker = $param[0];
        $id_daemon_main   = $param[1];

        $debug = '';
        if (Debug::$debug === true) {
            $debug = " --debug";
        }



        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM daemon_main where id=".intval($id_daemon_main);
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $worker_name = $ob->worker_name;
        }



        if (empty($id_daemon_worker)) {
            $sql = "INSERT INTO daemon_worker (`id_daemon_main`, `pid`) VALUES (".$id_daemon_main.", 0);";
            Debug::sql($sql);

            $db->sql_query($sql);

            $id_daemon_worker = $db->_insert_id();
        }

        $php = explode(" ", shell_exec("whereis php"))[1];
        $cmd = $php." ".GLIAL_INDEX." Aspirateur ".$worker_name." $debug > ".TMP."log/worker_".$id_daemon_main."_".$id_daemon_worker.".log 2>&1 & echo $!";
        Debug::debug($cmd);

        $pid = shell_exec($cmd);

        $sql = "UPDATE `daemon_worker` SET `pid`=".$pid." WHERE `id`=".$id_daemon_worker.";";
        Debug::sql($sql);
        $db->sql_query($sql);
    }

    public function removeWorker($param)
    {
        Debug::parseDebug($param);
        $id_daemon_main = $param[0];

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM daemon_main where id=".intval($id_daemon_main);
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $worker_name = $ob->worker_name;
        }

        $sql = "SELECT * FROM `daemon_worker` WHERE `id_daemon_main`=".$id_daemon_main." LIMIT 1;";
        Debug::sql($sql);
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $cmd = "kill ".$ob->pid;
            shell_exec($cmd);

            $double_buffer = TMP."lock/".$worker_name."/".$ob->pid.".pid";

            if (file_exists($double_buffer)) {
                unlink($double_buffer);
            }


            $file = TMP."log/worker_".$id_daemon_main."_".$ob->id.".log";

            if (file_exists($file)) {
                unlink($file);
            }

            $sql = "DELETE FROM `daemon_worker` WHERE `id`=".$ob->id.";";
            Debug::sql($sql);
            $db->sql_query($sql);
        }
    }

    public function killAllWorker($param)
    {
        Debug::parseDebug($param);

        if (!empty($param[0])) {
            $id_daemon_main = $param[0];
        } else {
            $id_daemon_main = 0;
        }

        $db = Sgbd::sql(DB_DEFAULT);

        /*
          $sql = "SELECT * FROM `daemon_main` WHERE `id`=".$id_daemon_main.";";
          Debug::sql($sql);
          $res = $db->sql_query($sql);

          while ($ob = $db->sql_fetch_object($res)) {
          $worker_name = $ob->worker_name;
          }

          Debug::debug($worker_name, 'worker_name');
         */

        $sql = "SELECT a.*,b.worker_name  FROM `daemon_worker` a
            INNER JOIN `daemon_main` b ON a.`id_daemon_main` = b.id
            ";

        if ($id_daemon_main != 0) {
            $sql .= "WHERE `id_daemon_main`=".$id_daemon_main;
        }

        $sql .= ";";

        Debug::sql($sql);
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $this->removeWorker(array($ob->id_daemon_main));
            //array_map('unlink', glob(TMP."tmp/lock/".$worker_name."/*.lock"));
        }

        //System::deleteFiles($worker_name);
    }

    public function checkAllWorker($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM daemon_main where queue_number != 0";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $this->checkWorker(array($ob->id));
        }
    }

    public function binaryLog($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];

        $mysql_tested = Mysql::getDbLink($id_mysql_server);

        if ($mysql_tested->testAccess()) {

            $sql = "SHOW BINARY LOGS;";
            $res = $mysql_tested->sql_query($sql);
            if ($res) {

                if ($mysql_tested->sql_num_rows($res) > 0) {

                    $files = array();
                    $sizes = array();

                    while ($arr = $mysql_tested->sql_fetch_array($res, MYSQLI_NUM)) {

                        $files[] = $arr[0];
                        $sizes[] = $arr[1];
                    }

                    $data['file_first'] = $files[0];
                    $data['file_last']  = end($files);
                    $data['files']      = json_encode($files);
                    $data['sizes']      = json_encode($sizes);
                    $data['total_size'] = array_sum($sizes);
                    $data['nb_files']   = count($files);

                    Debug::debug($data);
                    return $data;
                }
            }
        }
        return false;
    }

    public function getArbitrator()
    {
        // cat error.log | grep -oE 'tcp://[0-9]+.[0-9]+.[0-9]+.[0-9]+:4567' | sort -d | uniq -c | grep -v '0.0.0.0'
        // et retirer les IP presente dans la table alias et la table mysql_server
    }

    public function addToQueueSsh($param)
    {
        $worker_type = 'worker_ssh';

        //$param[] = '--debug';
        Debug::parseDebug($param);

        $id_daemon = $param[0];

        if (empty($id_daemon)) {
            throw new \Exception('PMATRL-347 : Arguement id_daemon missing');
        }

        if (Debug::$debug) {
            echo "[".date('Y-m-d H:i:s')."]"." Start all tests\n";
        }

        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM daemon_main WHERE id=".$id_daemon.";";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $queue_key        = intval($ob->queue_key);
            $maxExecutionTime = $ob->max_delay;
        }

//add message to queue
        $queue    = msg_get_queue($queue_key);
        $msg_qnum = (int) msg_stat_queue($queue)['msg_qnum'];

        // on attend d'avoir vider la file d'attente avant d'avoir une nouvelle liste de message (30 sec maximum)
        if ($msg_qnum != 0) {

            Debug::debug('On attends de vider la file d\'attente');

            for ($i = 0; $i < $maxExecutionTime; $i++) {
                $msg_qnum = msg_stat_queue($queue)['msg_qnum'];
                if ($msg_qnum == 0) {
                    break;
                } else {
                    Debug::debug($msg_qnum, "Nombre de message en attente");
                }
                sleep(1);
            }
        }


        $mysql_servers = array();

        //mémoire partagé 

        $lock_directory = TMP."lock/".$worker_type."/*.lock";

        $elems = array();
        foreach (glob($lock_directory) as $filename) {

            Debug::debug($filename, "filename");

            $json = file_get_contents($filename);

            $data    = json_decode($json, true);
            $elems[] = $data;
        }


        Debug::debug($elems, "liste des serveur en retard !");

        $db = Sgbd::sql(DB_DEFAULT);

        $mysql_servers = array();
        foreach ($elems as $server) {


            //on verifie avec le double buffer qu'on est bien sur le même pid
            //et ce dernier est toujours sur le serveur MySQL qui pose problème
            $idmysqlserver = trim(file_get_contents(TMP."lock/".$worker_type."/".$server['pid'].".pid"));

            // si le pid n'existe plus le fichier de temporaire sera surcharger au prochain run
            if (System::isRunningPid($server['pid']) === true && $idmysqlserver == $server['id']) {


                Debug::debug($server['pid'], "GOOD");

                $mysql_servers[] = $server['id'];
                $time            = microtime(true) - $server['microtime'];
                $this->logger->warning("SSH server with id : ".$server['id']." is late !!! Worker still runnig since ".round($time, 2)." seconds - pid : ".$server['pid']);

                //special case for timeout 60 seconds, else we see working since ... and not the real error
                $sql = "SELECT ssh_error,ssh_available  from mysql_server WHERE id = ".$server['id'].";";
                $res = $db->sql_query($sql);

                while ($ob = $db->sql_fetch_object($res)) {
                    if (!empty($ob->is_available)) {
                        // UPDATE ssh_available X => YELLOW  (not answered)
                        $sql = "UPDATE `mysql_server` SET ssh_available  = -1,
                            ` 	ssh_date_refresh ` = '".date("Y-m-d H:i:s")."',
                            `ssh_error`= 'Worker still runnig since ".round($time, 2)." seconds' WHERE `id` =".$server['id'].";";
                        //Debug::sql($sql);
                        $db->sql_query($sql);
                    }
                }
            } else {
                //si pid n'existe plus alors on efface le fichier de lock
                $lock_file = TMP."lock/".$worker_type."/".$server['id'].".lock";

                unlink($lock_file);
            }
        }



        $this->view = false;

        $sql = "select `id`,`name` from `mysql_server` WHERE `is_monitored`=1 ";

        if (!empty($mysql_servers)) {
            $sql .= " AND id NOT IN (".implode(',', $mysql_servers).")";
        }
        $sql .= " ORDER by ssh_available ASC, ssh_date_refresh DESC;";

        Debug::sql($sql);
        $res = $db->sql_query($sql);

        $server_list = array();
        while ($ob          = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $server_list[] = $ob;
        }

        //Debug::debug($server_list, "Liste des serveurs monitoré");
        //to prevent any trouble with fork
        //$this->debugShowQueries();

        $db->sql_close();

        // filename: add_to_queue.php
        //creating a queue requires we come up with an arbitrary number

        foreach ($server_list as $server) {

            // Create dummy message object
            $object       = new \stdclass;
            $object->name = $server['name'];
            $object->id   = $server['id'];

            //try to add message to queue
            if (msg_send($queue, 1, $object)) {

                Debug::debug($server, "Ajout dans la file d'attente");
                //echo "added to queue  \n";
                // you can use the msg_stat_queue() function to see queue status
                //print_r(msg_stat_queue($queue));
            } else {
                echo "could not add message to queue \n";
            }
        }
    }

    public function queue($param)
    {
        $pheanstalk = Pheanstalk::create('127.0.0.1');

// ----------------------------------------
// producer (queues jobs)

        $pheanstalk
            ->useTube('testtube')
            ->put("job payload goes here\n");

        $job = $pheanstalk
            ->watch('testtube2')
            ->ignore('default')
            ->reserve();

        echo $job->getData();

        $pheanstalk->delete($job);
    }

    public function workerSsh()
    {


        $pid = getmypid();

        //get mypid
        //start worker => pid / id_mysql_server

        $db = Sgbd::sql(DB_DEFAULT);
        $db->sql_close();
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM daemon_main WHERE id=9;";

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

        while (msg_receive($queue, 1, $msg_type, $max_msg_size, $msg)) {
            //echo "[" . date("Y-m-d H:i:s") . "] Message pulled from queue - id:{$msg->id}, name:{$msg->name} [[" . $pid . "]]\n";

            $id_mysql_server = $msg->id;

            $data['id']        = $id_mysql_server;
            $data['microtime'] = microtime(true);

            $lock_file = TMP."lock/worker_ssh/".$id_mysql_server.".lock";

            $double_buffer = TMP."lock/worker_ssh/".$pid.".pid";

            $fp = fopen($lock_file, "w+");
            fwrite($fp, json_encode($data));
            fflush($fp);            // libère le contenu avant d'enlever le verrou
            fclose($fp);

            $fp2 = fopen($double_buffer, "w+");
            fwrite($fp2, $id_mysql_server);
            fflush($fp2);            // libère le contenu avant d'enlever le verrou
            fclose($fp2);

            //do your business logic here and process this message!
            $this->trySshConnection(array($msg->id));

            /*
              if ($msg->id == "16") {
              sleep(60);
              }
              /**
             * test retard reponse mysql
             */
            if (file_exists($lock_file)) {
                unlink($lock_file);
            }

            //finally, reset our msg vars for when we loop and run again
            $msg_type = NULL;
            $msg      = NULL;
        }

        //remove pid and id_mysql_server
    }

    public function getDatabase($mysql_tested)
    {
        //$grants = $this->getGrants();

        $sql = 'SELECT table_schema as `database`,
        engine,
        ROW_FORMAT as "row_format", 
        sum(`data_length`) as "size_data",
        sum( `index_length` ) as "size_index",
        sum( `data_free` ) as "size_free",
        count(1) as `tables`,
        sum(TABLE_ROWS) as `rows`,
        GROUP_CONCAT(DISTINCT(TABLE_COLLATION)) as table_collation,
        DEFAULT_CHARACTER_SET_NAME as "charset",
        DEFAULT_COLLATION_NAME as "collation"
        FROM information_schema.TABLES a
        INNER JOIN information_schema.SCHEMATA b ON a.table_schema = b.SCHEMA_NAME
        WHERE table_schema NOT IN ("information_schema", "performance_schema", "mysql") AND a.TABLE_TYPE = "BASE TABLE"
        GROUP BY table_schema, engine, ROW_FORMAT;
            ';

        Debug::sql($sql);

        $res = $mysql_tested->sql_query($sql);
        if ($res) {
            if ($mysql_tested->sql_num_rows($res) > 0) {
                $dbs = array();

                while ($arr = $mysql_tested->sql_fetch_array($res, MYSQLI_ASSOC)) {
                    $dbs[$arr['database']]['charset']   = $arr['charset'];
                    $dbs[$arr['database']]['collation'] = $arr['collation'];

                    unset($arr['charset']);
                    unset($arr['collation']);

                    $dbs[$arr['database']]['engine'][$arr['engine']][$arr['row_format']] = $arr;
                }

                return json_encode($dbs);
            }
        }
        return false;
    }

    public function getSwap($membrut)
    {
        Debug::debug($membrut);

        $lines  = explode("\n", $membrut);
        unset($lines[0]);
        $titles = array('memory', 'swap');
        $items  = array('total', 'used', 'free', 'shared', 'buff/cache', 'available');

        $i = 1;
        foreach ($titles as $title) {
            // to remove Mem: and Swap:  (or other text in other language)
            $value = trim(explode(':', $lines[$i])[1]);
            $elems = preg_split('/\s+/', $value);

            $j = 0;
            foreach ($elems as $elem) {
                $stats[$title.'_'.$items[$j]] = $elem;
                $j++;
            }
            $i++;
        }

        Debug::debug($stats);

        return $stats;
    }
    /*
     * Récuperation des tables : (dans le cadre d'un serveur Galera 4
     * - wsrep_cluster
     * - wsrep_cluster_members
     * - wsrep_streaming_log
     * 
     */

    public function getWsrep($param = array())
    {
        Debug::parseDebug($param);

        if (empty($param[0])) {
            echo "INVALID";
        } else {
            $name_server = $param[0];
        }

        $mysql_tested = Sgbd::sql($name_server);
        $sql1         = "SELECT * FROM `mysql`.`wsrep_cluster`;";
        $res1         = $mysql_tested->sql_query($sql1);
        if ($res1) {

            $data['wsrep_cluster'] = array();
            if ($mysql_tested->sql_num_rows($res1) > 0) {

                //should be only one line
                while ($arr1 = $mysql_tested->sql_fetch_array($res1, MYSQLI_ASSOC)) {
                    $data['wsrep_cluster'][] = $arr1;
                }
            }
        }
        $data['wsrep_cluster'] = json_encode($data['wsrep_cluster']);

        $sql2 = "SELECT * FROM `mysql`.`wsrep_cluster_members`;";
        $res2 = $mysql_tested->sql_query($sql2);
        if ($res2) {

            $data['wsrep_cluster_members'] = array();
            if ($mysql_tested->sql_num_rows($res2) > 0) {

                while ($arr2 = $mysql_tested->sql_fetch_array($res2, MYSQLI_ASSOC)) {
                    $data['wsrep_cluster_members'][] = $arr2;
                }
            }
        }
        $data['wsrep_cluster_members'] = json_encode($data['wsrep_cluster_members']);

        $sql3 = "SELECT * FROM `mysql`.`wsrep_streaming_log`;";
        $res3 = $mysql_tested->sql_query($sql3);
        if ($res3) {

            $data['wsrep_streaming_log'] = array();
            if ($mysql_tested->sql_num_rows($res3) > 0) {

                while ($arr3 = $mysql_tested->sql_fetch_array($res3, MYSQLI_ASSOC)) {
                    $data['wsrep_streaming_log'][] = $arr3;
                }
            }
        }
        $data['wsrep_streaming_log'] = json_encode($data['wsrep_streaming_log']);

        Debug::debug($data);

        return $data;

        //$sql2 = "SELECT * FROM wsrep_cluster_members;";
    }

    public function getLockingQueries($param = array())
    {

        Debug::parseDebug($param);

        $id_mysql_server = $param[0];

        $db = Mysql::getDbLink($id_mysql_server);

        $sql = "select count(1) as cpt FROM INFORMATION_SCHEMA.PLUGINS where PLUGIN_NAME='METADATA_LOCK_INFO' and PLUGIN_STATUS='ACTIVE';";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            if ($ob->cpt === "1") {
                $sql2 = "select concat('KILL ',C.ID,';') as `kill` , C.INFO as `query_locking`, GROUP_CONCAT(P.INFO) as `query_locked`, GROUP_CONCAT(P.ID ) as `id_locked`
FROM INFORMATION_SCHEMA.PROCESSLIST P, INFORMATION_SCHEMA.METADATA_LOCK_INFO M
INNER JOIN INFORMATION_SCHEMA.PROCESSLIST C ON M.THREAD_ID = C.ID
WHERE LOCATE(lcase(M.LOCK_TYPE), lcase(P.STATE))>0 and M.THREAD_ID != P.ID
GROUP BY C.ID, C.INFO;";

                $res2 = $db->sql_query($sql2);
                $tmp  = array();

                $tmp['query_locking_count'] = $db->sql_num_rows($res2);

                while ($arr2 = $db->sql_fetch_array($res2, MYSQLI_ASSOC)) {

                    $tmp['query_locking_detail'][] = json_encode($arr2, JSON_PRETTY_PRINT);
                }

                Debug::debug($tmp);

                return $tmp;
            }
            else
            {
                Debug::debug("METADATA_LOCK_INFO is not installed");
            }
        }
    }
}