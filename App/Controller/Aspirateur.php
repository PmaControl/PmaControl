<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use Fuz\Component\SharedMemory\Storage\StorageFile;
use Fuz\Component\SharedMemory\SharedMemory;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;
use \App\Library\Debug;
use \App\Library\Ssh;
use App\Library\System;
use App\Library\Mysql;
use App\Library\Proxy;
use \Glial\Sgbd\Sgbd;
use \App\Library\Extraction;
use \App\Library\Extraction2;
/*
 *  => systemctl is-active mysql
 *
 * MySQL [(none)]> select @@version_comment limit 1;
  +-------------------+
  | @@version_comment |
  +-------------------+
  | (ProxySQL)        |
  +-------------------+
  1 row in set (0.000 sec)
  SELECT /*!40001 SQL_NO_CACHE * / * FROM
 *
 * 
 * 
 * 
 SELECT
t.THREAD_ID AS id,
t.PROCESSLIST_USER AS user,
t.PROCESSLIST_HOST AS host,
CONVERT (
CAST( CONVERT ( uvt.VARIABLE_VALUE USING latin1 ) AS BINARY ) USING utf8
) AS replica_uuid
FROM
`performance_schema`.threads AS t JOIN
`performance_schema`.user_variables_by_thread AS uvt ON t.THREAD_ID = uvt.THREAD_ID
WHERE
t.PROCESSLIST_COMMAND LIKE 'Binlog Dump%'
AND uvt.VARIABLE_NAME = 'slave_uuid'

SELECT
            COUNT(*) AS count
        FROM
            performance_schema.file_instances
        WHERE
            file_name LIKE '%innodb_redo/%' AND
            file_name NOT LIKE '%_tmp'


SELECT
            NAME,
            COUNT
        FROM
            information_schema.INNODB_METRICS
        WHERE
            name IN ('adaptive_hash_searches', 'adaptive_hash_searches_btree', 'trx_rseg_history_len')

+------------------------------+-------+
| NAME                         | COUNT |
+------------------------------+-------+
| trx_rseg_history_len         |     0 |
| adaptive_hash_searches       |     0 |
| adaptive_hash_searches_btree |     0 |
+------------------------------+-------+
3 rows in set (0,001 sec)
    


        SELECT
            t.THREAD_ID AS id,
            t.PROCESSLIST_USER AS user,
            t.PROCESSLIST_HOST AS host,
            CONVERT (
                CAST( CONVERT ( uvt.VARIABLE_VALUE USING latin1 ) AS BINARY ) USING utf8
            ) AS replica_uuid
        FROM
            `performance_schema`.threads AS t JOIN
            `performance_schema`.user_variables_by_thread AS uvt ON t.THREAD_ID = uvt.THREAD_ID
        WHERE
            t.PROCESSLIST_COMMAND LIKE 'Binlog Dump%'
            AND uvt.VARIABLE_NAME = 'slave_uuid';



SELECT
CONVERT(SUM(SUM_NUMBER_OF_BYTES_READ), UNSIGNED) AS io_read,
CONVERT(SUM(SUM_NUMBER_OF_BYTES_WRITE), UNSIGNED) AS io_write
FROM
`performance_schema`.`file_summary_by_event_name`
WHERE
`performance_schema`.`file_summary_by_event_name`.`EVENT_NAME` LIKE 'wait/io/file/%' AND
`performance_schema`.`file_summary_by_event_name`.`COUNT_STAR` > 0

+------------+-------------+
| io_read    | io_write    |
+------------+-------------+
| 1146072309 | 22420098241 |
+------------+-------------+
1 row in set (0,002 sec)

 */

// https://github.com/php-amqplib/php-amqplib
//each minute ?
//require ROOT."/application/library/Filter.php";
//https://blog.programster.org/php-multithreading-pool-example

class Aspirateur extends Controller
{

    use \App\Library\Filter;
    var $shared            = array();
    var $lock_variable     = TMP."lock/variable/{id_mysql_server}.md5";
    var $lock_database     = TMP."lock/database/{id_mysql_server}.lock";
    var $lock_proxysql_var = TMP."lock/proxysql_var/{id_proxysql_server}.lock";

    var $lock_list_db = TMP."lock/list_db/{id_mysql_server}.md5";

    var $lock_runtime_mysql_servers = TMP."lock/proxysql_runtime_server/{id_mysql_server}.md5";


    var $files             = array();
    public $variables         = array();

    static $timestamp_config_file = "";

    static $md5_file = array(4 => TMP."lock/variable/{id_mysql_server}.md5", 9 => TMP."lock/list_db/{id_mysql_server}.md5");

    //log with monolog
    var $logger;

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
        $monolog       = new Logger("Aspirateur");
        $handler      = new StreamHandler(LOG_FILE, Logger::NOTICE);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $monolog->pushHandler($handler);
        $this->logger = $monolog;
    }

    /**
     * (PmaControl 0.8)<br/>
     * @example ./glial aspirateur tryMysqlConnection name id_mysql_server
     * @author Aurélien LEQUOY, <aurelien@esysteme.com>
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
        $id_mysql_server   = $param[1];


        // To know if we use a proxy like PROXYSQL
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT is_proxy FROM mysql_server WHERE id=".$id_mysql_server;
        $res = $db->sql_query($sql);

        while($ob = $db->sql_fetch_object($res)) {
            $IS_PROXY = $ob->is_proxy;
        }
        $db->sql_close();
        //end of case of HA proxy

        Debug::checkPoint('avant query');
        $pid = getmypid();

        /* Only for testing for processing long time, don't think some got more than 999 999 mysql server :D */
        if ($id_mysql_server === "999999") {
            $wait_sec = 135;
            $this->logger->notice('### WAIT '.$wait_sec.' sec for id_mysql_server='.$id_mysql_server);
            sleep($wait_sec);
        }

        $time_start   = microtime(true);

        try{
            $error_msg='';
            $mysql_tested = Sgbd::sql($name_server);
        }
        catch(\Exception $e){
            $error_msg = $e->getMessage();
            $this->logger->emergency($error_msg." id_mysql_server:$id_mysql_server");
        }
        finally{
            $ping = microtime(true) - $time_start;
            $available = empty($error_msg) ? 1 : 0;

            // only if REAL server => should make test if Galera if select 1 => not ready to use too
            if (empty($IS_PROXY)) {
                $this->setService($id_mysql_server, $ping, $error_msg, $available, 'mysql');
            }
            
            $this->logger->info("[WORKER:".$pid."] id_mysql_server:".$id_mysql_server." - is_available : ".$available." - ping : ".round($ping,6));

            // VERY important else we got error and we kill the worker and have to restart with a new one
            if ($available === 0) {
                return false;
            }
        }
        
        //$res = $mysql_tested->sql_multi_query("SHOW /*!40003 GLOBAL*/ VARIABLES; SHOW /*!40003 GLOBAL*/ STATUS; SHOW SLAVE STATUS; SHOW MASTER STATUS;");
        // SHOW /*!50000 ENGINE*/ INNODB STATUS

        // traitement SHOW GLOBAL VARIABLES

        //CASE ProxySQL (with one hostgroup DEAD)
        try{
            $var['variables'] = $mysql_tested->getVariables();
        }
        catch(\Exception $e){
            $error_ori = $e->getMessage();
            //$error_msg = "ERROR 9001 (HY000) at line 1: Max connect timeout reached while reaching hostgroup 1 after 10000ms";

            $output_array = array();
            preg_match('/ERROR:\s(.*)}/', $error_ori, $output_array);

            if (!empty($output_array[1])) {
                $error_msg = $output_array[1];
            }
            else{
                $error_msg = $error_ori;
            }
            $this->logger->emergency("[ERROR] id_mysql_server:$id_mysql_server ==> $error_ori");

        }
        finally{
            // case for Proxy  and Galera Cluster
            if (!empty($IS_PROXY)) {
                $available = empty($error_ori) ? 1 : 0;
                $this->setService($id_mysql_server, $ping, $error_msg, $available, 'mysql');
                $this->logger->info("[WORKER:".$pid."] id_mysql_server:".$id_mysql_server." ".$error_msg." - is_available : ".$available." - ping : ".round($ping,6));

                // VERY important else we got error and we kill the worker and have to restart with a new one
                if ($available === 0) {
                    return false;
                }
                //return true;
            }
        }

        Debug::debug($var['variables']['is_proxysql'], "is_proxysql");
        //shell_exec("echo 'is_proxy : ".json_encode($var['variables'])."' >> ".TMP."/proxysql");

        //we delete variable who change each time and put in on status
        $remove_var = array('gtid_binlog_pos', 'gtid_binlog_state', 'gtid_current_pos','gtid_slave_pos', 'timestamp', 'gtid_executed');

        $move_to_status = array();
        foreach($remove_var as $var_to_remove){
            if (!empty($var['variables'][$var_to_remove])) {
                
                $move_to_status[$var_to_remove] = $var['variables'][$var_to_remove];
                unset($var['variables'][$var_to_remove]);
            }
        }

        /**** */
        //time server

        /*
        $this->logger->critical("version ". $var['variables']['version']);
        if (version_compare($var['variables']['version'], '8', ">="))
        {
            $sql ="WITH a AS (
                SELECT TIMESTAMPDIFF(SECOND, MIN(FIRST_SEEN), MAX(LAST_SEEN)) AS seconds_difference
                FROM performance_schema.events_statements_summary_by_digest
            )
            SELECT SUM((AVG_TIMER_WAIT / 1000000000000) * COUNT_STAR) / (SELECT seconds_difference FROM a) AS time_in_seconds
            FROM performance_schema.events_statements_summary_by_digest;";
    
            $res = $mysql_tested->sql_query($sql);
            while($ob = $mysql_tested->sql_fetch_object($res)){
                $move_to_status['time_server'] = $ob->time_in_seconds;
            }
        }
        /****** */

        
        $data = array();

        if (!empty($var['variables']['is_proxysql']) && $var['variables']['is_proxysql'] === 1) {
            $db  = Sgbd::sql(DB_DEFAULT);
            $sql ="SELECT id FROM mysql_server WHERE id=".$id_mysql_server." AND `is_proxy`!=1";
            $res = $db->sql_query($sql);
            while ($ob = $db->sql_fetch_object($res)){
                $sql = "UPDATE `mysql_server` SET `is_proxy`=1 WHERE `id`=".$id_mysql_server.";";
                Debug::sql($sql);
                $db->sql_query($sql);
                $this->logger->notice("We discover a new ProxySQL : id_mysql_server:".$ob->id_mysql_server);
            }

            $version = Extraction2::display(array("proxysql_main_var::admin-version"), array($id_mysql_server));
            
            $var_temp['variables']['is_proxysql']     = $var['variables']['is_proxysql'];
            $var_temp['variables']['version']         = $version[$id_mysql_server]['admin-version'];
            $var_temp['variables']['version_comment'] = "ProxySQL";
            unset($var);

            $var = $var_temp;
        } else {

            //get SHOW GLOBAL STATUS
            Debug::debug("apres Variables");
            $data['status'] = $mysql_tested->getStatus();
            $data['status'] = array_merge($data['status'], $move_to_status);

            Debug::debug("apres status");
            $data['master'] = $mysql_tested->isMaster();
            Debug::debug($data['master'], "STATUS");
            Debug::debug("apres master");
            $data['slave']  = $mysql_tested->isSlave();
            Debug::debug("apres slave");

            $data['locking'] = $this->getLockingQueries(array($id_mysql_server));

            //SHOW SLAVE HOSTS; => add in glial
            //$data['processlist'] = $mysql_tested->getProcesslist(1);

            if ($var['variables']['log_bin'] === "ON") {
                $data['binlog'] = $this->binaryLog(array($id_mysql_server));
            }

            Debug::debug("apres la récupération de la liste des binlogs");
            Debug::checkPoint('apres query');
        }
        /* mysql > 5.6
          $sql = "SELECT `NAME`,`COUNT`,`TYPE` FROM `INFORMATION_SCHEMA`.`INNODB_METRICS` WHERE `STATUS` = 'enabled';";
          $res = $mysql_tested->sql_query($sql);

          while ($ob = $mysql_tested->sql_fetch_object($res)) {
          $data['innodb'][$ob->NAME] = $ob->COUNT;
          }
         */

        $date[date('Y-m-d H:i:s')][$id_mysql_server] = $data;

        if ($mysql_tested->is_connected === false) {
            $this->logger->emergency("PAS BON DU TOUT ! ask creator of PmaControl");
            return false;
        }

        $export_variables = $this->isModified($id_mysql_server, $var,  $this->lock_variable);

        Debug::debug($date, "answer");

        //push data in memory
        $this->allocate_shared_storage('answer');
        $this->shared['answer']->{$id_mysql_server} = $date;

        if ($export_variables) {
            $this->allocate_shared_storage('variable');

            Debug::debug($var, "SHOW GLOBAL VARIABLE;");

            $variables                                  = array();
            $variables[date('Y-m-d H:i:s')][$id_mysql_server] = $var;
            $this->shared['variable']->{$id_mysql_server}     = $variables;
        }


        if (!empty($IS_PROXY)) {
            return true;
        }

        /*************************************** list Database  */

        $db_list = $this->getSchema($id_mysql_server);
        if ($this->isModified($id_mysql_server, $db_list,  $this->lock_list_db) === true){

            $this->logger->debug("we import DB to shared memory [id_mysql_server:".$id_mysql_server."]");

            $this->allocate_shared_storage('list_db');
            $export_list_db[date('Y-m-d H:i:s')][$id_mysql_server]['list_db']['schema_list']  = json_encode($db_list);
            $this->shared['list_db']->{$id_mysql_server}     = $export_list_db;
        }

        /*************************************** List table by DB  */


        //to know if we grab statistics on databases & tables

        $lock_database = str_replace('{id_mysql_server}', $id_mysql_server, $this->lock_database);

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
                $databases[date('Y-m-d H:i:s')][$id_mysql_server] = $dbs;
                $this->shared['database']->{$id_mysql_server}     = $databases;

                file_put_contents($lock_database, date('Y-m-d'));
            }
        }

        /****************************************************************** */

        $mysql_tested->sql_close();

        Debug::debugShowTime();
        //ini_set("display_errors", $display_error);

        return true;
    }

    public function allocate_shared_storage($name = 'answer')
    {
//storage shared

        Debug::debug($name, 'create file');

        $shared_file = TMP.'tmp_file/'.$name.'_'.time();
        $storage             = new StorageFile($shared_file); // to export in config ?
        $this->shared[$name] = new SharedMemory($storage);

        //to prevent error on fopen when executed in root and ww-data try to delete it
        //TODO replace with 
        if (file_exists($shared_file)) {
            chown($shared_file,"www-data");
        }
    }

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


          
            try{
                $error_msg='';
                $time_start = microtime(true);
                $ssh        = Ssh::ssh($id_mysql_server);
            }
            catch(\Exception $e){
                $error_msg = $e->getMessage();
                $this->logger->warning($error_msg." id_ssh_server:$id_mysql_server");
            }
            finally{
                $ping = microtime(true) - $time_start;
                $available = empty($error_msg) ? 1 : 0;
                
                $this->setService($id_mysql_server, $ping, $error_msg, $available, "ssh");
                $this->logger->info("id_ssh_server:".$id_mysql_server." - is_available : ".$available." - ping : ".round($ping,6));
    
                // VERY important else we got error and we kill the worker and have to restart with a new one
                if ($available === 0) {
                    return false;
                }
            }



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
                $date[date('Y-m-d H:i:s')][$ob->id]['ssh_server']         = $stats;
                $date[date('Y-m-d H:i:s')][$ob->id]['ssh_server']['ping'] = $ping;

//$this->shared->$id                           = $date;
                $this->shared['ssh_stats']->{$id_mysql_server} = $date;

                $this->allocate_shared_storage('hardware');
                $date[date('Y-m-d H:i:s')][$ob->id]['hardware'] = $hardware;

                $this->shared['hardware']->{$id_mysql_server} = $date;

                Debug::debug($date);
            } else {
                Debug::debug("Can't connect to ssh");
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
            $refresh_time = $ob->refresh_time;
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
                    $this->logger->warning('Number message waiting in queue : '. $msg_qnum .'');
                }
                sleep(1);
            }
        }

        $mysql_servers = array();
        $lock_directory = TMP."lock/worker/*.lock";

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
            $idmysqlserver = trim(file_get_contents(TMP."lock/worker/".$server['pid'].".pid"));

            // si le pid n'existe plus le fichier temporaire sera surcharger au prochain run
            if (System::isRunningPid($server['pid']) === true && $idmysqlserver == $server['id']) {


                Debug::debug($server['pid'], "GOOD");

                $mysql_servers[] = $server['id'];
                //$list[] = Color::getColoredString("MySQL server with id : " . $server['id'] . " is late !!! pid : " . $server['pid'], "grey", "red");

                $time = microtime(true) - $server['microtime'];

                $msg = "Worker still runnig since ".round($time, 2)." seconds - pid : ".$server['pid'];
                $msg2 = "Worker still runnig - pid : ".$server['pid'];
                $this->logger->warning("MySQL server with id : ".$server['id']." is late !!!  ".$msg);

            } else {
                //si pid n'existe plus alors on efface le fichier de lock
                $lock_file = TMP."lock/worker/".$server['id'].".lock";
                if (file_exists($lock_file)) {
                    $this->logger->notice('[addToQueueMySQL] the pid didn\'t exist anymore : "'.$lock_file.'", we deleted id !');
                    unlink($lock_file);
                }
            }
        }

//echo implode("\n", $list) . "\n";
//Debug::debug($list, "list");


        $this->view = false;

        $sql = "select a.id,a.name from mysql_server a
            INNER JOIN client b on a.id_client =b.id
            INNER JOIN ts_max_date c on c.id_mysql_server = a.id AND id_ts_file=3
            WHERE a.is_monitored =1 and b.is_monitored=1";

        if (!empty($mysql_servers)) {
            $sql .= " AND a.id NOT IN (".implode(',', $mysql_servers).")";
        }

        $sql .= " ORDER by c.date DESC;";

        Debug::sql($sql);
        $res = $db->sql_query($sql);

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

        $delay = floor(1000000 * $refresh_time / 2 / $nb_server_to_monitor);

        $this->logger->debug("Delay : ".$delay."");

        foreach ($server_list as $server) {

            // Create dummy message object
            $object       = new \stdclass;
            $object->name = $server['name'];
            $object->id   = $server['id'];

            //try to add message to queue
            if (msg_send($queue, 1, $object)) {
                $this->logger->debug("Add id_mysql_server:".$server['id']." to the queue ($queue_key)");
                //usleep($delay);
            } else {
                $this->logger->warning("could not add message to queue ($queue_key)");
            }
        }

//$stats = msg_stat_queue($queue);
//debug($stats);
    }

    public function worker()
    {

        $pid = getmypid();
        $this->logger->notice("[WORKER:$pid] Started new worker with pid : $pid");

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
        $param['pid'] = $pid;
        

        $this->keepConfigFile($param);

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

            // on vide le pid 
            //$waiting = __("Waiting...");
            file_put_contents($worker_pid,"Waiting...");

            //finally, reset our msg vars for when we loop and run again
            $msg_type = NULL;
            $msg      = NULL;
        }

        $this->logger->warning("We not wait waited next msg in queue ($queue_key)");

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


                    //$file = file_get_contents(TMP."log/worker_".$id_daemon_main."_".$ob2->id.".log");
                    //Debug::debug($file, "FILE");

                    //remove pid of worker there
                    $double_buffer = TMP."lock/worker/".$ob2->pid.".pid";

                    //on a joute le worker avant de purger le fichier de l'ancien, afin d'aviter : PHP Warning:  file_get_contents 
                    $this->addWorker(array($ob2->id, $id_daemon_main, $debug));

                    if (file_exists($double_buffer)) {

                        $id_mysql_server = file_get_contents($double_buffer);
                        $lock = TMP."lock/worker/".$id_mysql_server.".lock";

                        unlink($double_buffer);
                        if (file_exists($lock)) {
                            unlink($lock);
                            $this->logger->notice("[WORKER] removed worker (lock) with id_mysql_server:".$id_mysql_server."");
                            
                        }

                        $this->logger->notice("[WORKER] removed worker with pid : ".$ob2->pid."");
                    }

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

                $id_mysql_server = Extraction::display(array("ssh_server::available"));
                Debug::debug($id_mysql_server, "ssh available");

                $this->logger->notice(json_encode($id_mysql_server));

            } else {
                //si pid n'existe plus alors on efface le fichier de lock
                $lock_file = TMP."lock/".$worker_type."/".$server['id'].".lock";

                unlink($lock_file);
            }
        }



        $this->view = false;

        $sql = "select a.`id`,a.`name` from `mysql_server` a 
        INNER JOIN ts_max_date c on c.id_mysql_server = a.id AND c.id_ts_file=1
        WHERE a.`is_monitored`=1 ";

        if (!empty($mysql_servers)) {
            $sql .= " AND a.id NOT IN (".implode(',', $mysql_servers).")";
        }
        $sql .= " ORDER by c.date DESC;";

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

            $worker_pid = TMP."lock/worker_ssh/".$pid.".pid";

            $fp = fopen($lock_file, "w+");
            fwrite($fp, json_encode($data));
            fflush($fp);            // libère le contenu avant d'enlever le verrou
            fclose($fp);


            file_put_contents($worker_pid,$id_mysql_server);

            /*
            $fp2 = fopen($double_buffer, "w+");
            fwrite($fp2, $id_mysql_server);
            fflush($fp2);            // libère le contenu avant d'enlever le verrou
            fclose($fp2);
            */
            //do your business logic here and process this message!
            $this->trySshConnection(array($msg->id));

            if (file_exists($lock_file)) {
                unlink($lock_file);
            }

            //$waiting = __("Waiting...");
            file_put_contents($worker_pid,"Waiting...");


            //finally, reset our msg vars for when we loop and run again
            $msg_type = NULL;
            $msg      = NULL;
        }

//remove pid and id_mysql_server
    }

    public function getDatabase($mysql_tested)
    {
//$grants = $this->getGrants();

        $sql2 = 'select * from information_schema.SCHEMATA;';
        $res2 = $mysql_tested->sql_query($sql2);

        $db_number = $mysql_tested->sql_num_rows($res2);

        if ($db_number > 500) {
            Debug::sql("Too much DBs");
            return false;
        }


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
        GROUP BY table_schema, engine, ROW_FORMAT, DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME;';

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

        $sql = "select /* pmacontrol */ count(1) as cpt FROM INFORMATION_SCHEMA.PLUGINS where PLUGIN_NAME='METADATA_LOCK_INFO' and PLUGIN_STATUS='ACTIVE';";
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
            } else {
                Debug::debug("METADATA_LOCK_INFO is not installed");
            }
        }
    }

    public function getProxySQL($param = array())
    {
        Debug::parseDebug($param);

        $id_proxysql_server = $param[0];
    }

    public function addToQueueProxySQL($param)
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

        $lock_directory = TMP."lock/worker_proxysql/*.lock";

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
            $idmysqlserver = trim(file_get_contents(TMP."lock/worker_proxysql/".$server['pid'].".pid"));

// si le pid n'existe plus le fichier de temporaire sera surcharger au prochain run
            if (System::isRunningPid($server['pid']) === true && $idmysqlserver == $server['id']) {


                Debug::debug($server['pid'], "GOOD");

                $mysql_servers[] = $server['id'];
//$list[] = Color::getColoredString("MySQL server with id : " . $server['id'] . " is late !!! pid : " . $server['pid'], "grey", "red");

                $time = microtime(true) - $server['microtime'];
                $this->logger->warning("MySQL server with id : ".$server['id']." is late !!! Worker still runnig since ".round($time, 2)." seconds - pid : ".$server['pid']);


            } else {
//si pid n'existe plus alors on efface le fichier de lock
                $lock_file = TMP."lock/worker_proxysql/".$server['id'].".lock";

                unlink($lock_file);
            }
        }

//echo implode("\n", $list) . "\n";
//Debug::debug($list, "list");


        $this->view = false;

        $sql = "select a.id,concat('proxysql_', a.id) as name, a.id_mysql_server
            from proxysql_server a
            INNER JOIN mysql_server z ON z.id = a.id_mysql_server
            INNER JOIN client b on z.id_client =b.id
            WHERE z.is_monitored =1 and b.is_monitored=1";

        if (!empty($mysql_servers)) {
            $sql .= " AND a.id NOT IN (".implode(',', $mysql_servers).")";
        }

        $sql .= " ORDER by 1 DESC;";

//echo \SqlFormatter::format($sql);

        Debug::sql($sql);
        $res = $db->sql_query($sql);

        $server_list = array();
        while ($ob          = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $server_list[] = $ob;
        }

        $db->sql_close();

        foreach ($server_list as $server) {

// Create dummy message object
            $object       = new \stdclass;
            $object->name = $server['name'];
            $object->id   = $server['id'];
            $object->id_mysql_server   = $server['id_mysql_server'];
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
    public function tryProxySQLConnection($param)
    {

        $display_error = ini_get('display_errors');
        ini_set("display_errors", 0);

        Debug::parseDebug($param);
        $this->view = false;

        $name_server = $param[0];
        $id_server   = $param[1];
        $id_mysql_server = $param[2];

        $db = Sgbd::sql(DB_DEFAULT);
        $db->sql_close();

        Debug::checkPoint('avant query');

        $time_start   = microtime(true);
        $mysql_tested = Sgbd::sql($name_server);

        $err = error_get_last();
        error_clear_last();

        $error_msg = "";
        if ($err !== NULL) {
            $error_msg = $err['message'];
        }
        // save error_msg in proper way

        $var['proxysql_main_var'] = $mysql_tested->getProxysqlVariables();
        $md5                      = md5(json_encode($var));
        Debug::debug($md5, "NEW MD5");

        $file_md5 = str_replace('{id_proxysql_server}', $id_server, $this->lock_proxysql_var);

        $export_variables = false;

        if (file_exists($file_md5)) {

            $cmp_md5 = file_get_contents($file_md5);

            Debug::debug($cmp_md5, "OLD MD5");

            if ($cmp_md5 != $md5) {
                $export_variables = true;

                file_put_contents($file_md5, $md5);
            }
        } else {
            file_put_contents($file_md5, $md5);
            $export_variables = true;
        }

        //$export_variables = true;

        if ($export_variables) {
            $this->allocate_shared_storage('proxysql_main_var');

            Debug::debug($var, "SHOW GLOBAL VARIABLE;");

            $variables                                  = array();
            $variables[date('Y-m-d H:i:s')][$id_mysql_server] = $var;
            $this->shared['proxysql_main_var']->{$id_mysql_server}     = $variables;
            $this->logger->warning("INSERTION DANS proxysql variables - id_mysql_server : ".$id_mysql_server);
        }

        /************************************** */
        $sql = "select * from runtime_mysql_servers;";
        $res = $mysql_tested->sql_query($sql);

        $runtime_mysql_servers = array();
        while ($arr = $mysql_tested->sql_fetch_array($res, MYSQLI_ASSOC))
        {
            $runtime_mysql_servers[] = $arr; 
        }

        if ($this->isModified($id_server, $runtime_mysql_servers, $this->lock_runtime_mysql_servers) || true)
        {
            $this->allocate_shared_storage('proxysql_runtime_server');

            $variables                                  = array();
            $variables[date('Y-m-d H:i:s')][$id_mysql_server]['proxysql_runtime_server']['mysql_servers'] = json_encode($runtime_mysql_servers);
            $this->shared['proxysql_runtime_server']->{$id_mysql_server}     = $variables;
        }

        $mysql_tested->sql_close();

        Debug::debugShowTime();
        ini_set("display_errors", $display_error);

        return true;
    }

    public function testProxy($param)
    {
        Debug::parseDebug($param);

        $db = Proxy::getDbLink(1);

        $sql = "select * from stats_mysql_connection_pool;";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            Debug::debug($ob);
        }
    }

    public function workerProxysql()
    {


        $pid = getmypid();

//get mypid
//start worker => pid / id_mysql_server

        $db = Sgbd::sql(DB_DEFAULT);
        $db->sql_close();
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM daemon_main WHERE id=13;";

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

            $lock_file = TMP."lock/worker_proxysql/".$id_mysql_server.".lock";

            $double_buffer = TMP."lock/worker_proxysql/".$pid.".pid";

            $fp = fopen($lock_file, "w+");
            fwrite($fp, json_encode($data));
            fflush($fp);            // libère le contenu avant d'enlever le verrou
            fclose($fp);

            $fp2 = fopen($double_buffer, "w+");
            fwrite($fp2, $id_mysql_server);
            fflush($fp2);            // libère le contenu avant d'enlever le verrou
            fclose($fp2);

//do your business logic here and process this message!
            //$this->logger->critical("PROXYSQL ADMIN : ".$msg->id);

            $this->tryProxySQLConnection(array($msg->name, $msg->id, $msg->id_mysql_server));

            if (file_exists($lock_file)) {
                unlink($lock_file);
            }

//finally, reset our msg vars for when we loop and run again
            $msg_type = NULL;
            $msg      = NULL;
        }



//remove pid and id_mysql_server
    }


    /*
     * available = 0 : server down
     * available = 1 : server operationel
     * available = 2 : waiting answer
     * 
     */
    public function setService($id_mysql_server, $ping, $error_msg, $available, $type)
    {
        if (! in_array($type, array('mysql', 'ssh'))) {
            die('error');
        }

        $service                              = array();
        $service[$type.'_server'][$type.'_available'] = $available;
        $service[$type.'_server'][$type.'_ping']      = $ping;
        $service[$type.'_server'][$type.'_error']     = $error_msg;

        $services                                  = array();
        $services[date('Y-m-d H:i:s')][$id_mysql_server] = $service;

        $this->allocate_shared_storage('service_'.$type);
        $this->shared['service_'.$type]->{$id_mysql_server} = $services;

        return $services;
    }


    public function debug($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = Extraction::display(array(  "ssh_server::ssh_available"));
        Debug::debug($id_mysql_server, "ssh available");

        $mysql = Extraction::display(array( "mysql_server::mysql_available"), array(1));
        Debug::debug($mysql, "mysql available");
    }


    public function getSchema($id_mysql_server)
    {

        
        $mysql_tested = Mysql::getDbLink($id_mysql_server);
        $schemas = array();
        if ($mysql_tested->testAccess()) {

            $this->logger->debug("We import schema list from id_mysql_server : ".$id_mysql_server);
            $sql = "SELECT SCHEMA_NAME as schema_name, DEFAULT_CHARACTER_SET_NAME as character_set_name, DEFAULT_COLLATION_NAME as collation_name  
            FROM information_schema.schemata";

            $res = $mysql_tested->sql_query($sql);

            while ($arr = $mysql_tested->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $schemas[] = $arr;
            }
        }
        
        return $schemas;
    }
    /*
    *
    *   chack if data as been modified if yes, we will import

    */

    public function isModified($id_mysql_server, $var, $lock_file)
    {
        //return true;

        $md5      = md5(json_encode($var));
        $file_md5 = str_replace('{id_mysql_server}', $id_mysql_server, $lock_file);
        $export = false;

        Debug::debug($md5, "NEW MD5 $file_md5");

        if (file_exists($file_md5)) {

            $cmp_md5 = file_get_contents($file_md5);

            Debug::debug($cmp_md5, "OLD MD5 $file_md5");

            if ($cmp_md5 != $md5) {
                $export = true;
                $info = pathinfo($file_md5);
                if (is_dir($info['dirname'])){
                    file_put_contents($file_md5, $md5);
                }
                else {
                    $this->logger->emergency('Cannot create directory : '.$info['dirname']);
                }
            }
        } else {
            /*
              if (!is_writable(dirname($file_md5))) {
              Throw new \Exception('PMACTRL-858 : Cannot write file in directory : '.dirname($file_md5).'');
              } */

            file_put_contents($file_md5, $md5);
            $export = true;
        }

        return $export;

    }


    // Dans le cas des worker pour eviter de les relancer on recharge la configuation des serveurs MySQL, lorsque un nouveau server est ajouté.
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

    static function cleanMd5($tab_id_mysql_servers)
    {
        foreach($tab_id_mysql_servers as $id_mysql_server) {
            $file_md5 = str_replace('{id_mysql_server}', $id_mysql_server, self::$md5_file[4]);

            unlink($file_md5);
        }

    }

    public function testproxysql($param)
    {
        $db = Sgbd::sql('server_6612a9fcb1641');

        $db->sql_query("SHOW VARIABLES");

        Debug::parseDebug($param);

        //Mysql::test2("127.0.0.1", 6033, "stnduser", "stnduser");


    }
}