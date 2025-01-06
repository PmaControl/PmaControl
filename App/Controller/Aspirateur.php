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
use App\Library\EngineV4;
use \Glial\Sgbd\Sgbd;
use \App\Library\Extraction;
use \App\Library\Extraction2;
use \App\Library\Microsecond;
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


    //to delete
    var $shared            = array();

    static $timestamp_config_file = "";

    static $file_created = array();
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
        $refresh = $param[2];

        Debug::checkPoint('Init');
        // To know if we use a proxy like PROXYSQL
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT is_proxy FROM mysql_server WHERE id=".$id_mysql_server;
        $res = $db->sql_query($sql);

        while($ob = $db->sql_fetch_object($res)) {
            $IS_PROXY = $ob->is_proxy;
        }
        $db->sql_close();
        //end of case of HA proxy

        Debug::checkPoint('After getting proxy');
        $pid = getmypid();

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
                if ($available === 0) {
                    //$mysql_tested->sql_close();
                    return false;
                }
            }
            else{
                // need try one case if hostgroup 2 ok but hostgroup 1 ko
                try{
                    $error_filter='';
                    $sql ="BEGIN;";
                    $mysql_tested->sql_query($sql);
                    $sql ="COMMIT;";
                    $mysql_tested->sql_query($sql);
                }
                catch(\Exception $e){
                    $error_ori = $e->getMessage();
                    preg_match('/ERROR:(.*)}/', $error_ori, $output_array);
                    if (!empty($output_array[1])) {
                        $error_filter = $output_array[1];
                    }
                    else {
                        $error_filter =$error_ori;
                    }
                    $this->logger->emergency("[ERROR][WORKER:".$pid."] id_mysql_server:$id_mysql_server ==> $error_filter");
                }
                finally
                {
                    $available = empty($error_ori) ? 1 : 0;
                    $this->setService($id_mysql_server, $ping, $error_filter, $available, 'mysql');

                    if ($available === 0) {
                        $mysql_tested->sql_close();
                        return false;
                    }
                }
            }

            $this->logger->info("[WORKER:".$pid."] id_mysql_server:".$id_mysql_server." - is_available : ".$available." - ping : ".round($ping,6));

            // VERY important else we got error and we kill the worker and have to restart with a new one
            if ($available === 0) {
                return false;
            }
        }

        // traitement SHOW GLOBAL VARIABLES
        $var['variables'] = $mysql_tested->getVariables();
        Debug::checkPoint('After global variables');

        if (!empty($var['variables']['is_proxysql']) && $var['variables']['is_proxysql'] === 1) {

            $data = array();
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
            
            $var_temp = array();
            $var_temp['variables']['is_proxysql']     = $var['variables']['is_proxysql'];

            if (! empty($version[$id_mysql_server]['admin-version']))
            {
                $version_proxysql = $version[$id_mysql_server]['admin-version'];
            }
            else{
                $version_proxysql = "N/A";
            }

            $var_temp['variables']['version']         = $version_proxysql;
            $var_temp['variables']['version_comment'] = "ProxySQL";
            
            $this->exportData($id_mysql_server,"mysql_global_variable", $var_temp);
            return true;
        } 

        if ($IS_PROXY)
        {
            return true;
        }

        //we delete variable who change each time and put in on status
        $remove_var = array('gtid_binlog_pos', 'gtid_binlog_state', 'gtid_current_pos','gtid_slave_pos', 'timestamp', 'gtid_executed');
        $data = array();
        foreach($remove_var as $var_to_remove){
            if (!empty($var['variables'][$var_to_remove])) {
                
                $data['gtid'][$var_to_remove] = $var['variables'][$var_to_remove];
                
                unset($var['variables'][$var_to_remove]);
            }
        }

        if ((time()+$id_mysql_server)%3 === 0)
        {
            $this->exportData($id_mysql_server,"mysql_global_variable", $var);
        }

        if ((time()+$id_mysql_server)%3 === 0)
        {
            $this->exportData($id_mysql_server,"mysql_variable_gtid", $data, false );
        }

        //get SHOW GLOBAL STATUS
        Debug::debug("apres Variables");
        
        $data = array();
        $data['status'] = $mysql_tested->getStatus();
        
        
        $slave  = $mysql_tested->isSlave();
        if (($slave) != 0) {
            $data['slave'] = $slave;
        }

        $this->exportData($id_mysql_server, "mysql_global", $data, false);
        
        $data = array();
        //$data['mysql_meta_data_lock']['meta_data_lock'] = $this->getLockingQueries(array($id_mysql_server));
        //$this->exportData($id_mysql_server, "mysql_meta_data_lock", $data);


        //SHOW SLAVE HOSTS; => add in glial
        $data = array();
        $data['mysql_processlist']['processlist'] = json_encode($mysql_tested->getProcesslist(0));
        $this->exportData($id_mysql_server, "mysql_processlist", $data);



        if ((time()+$id_mysql_server)%(10*$refresh) < $refresh)
        {
            if ($var['variables']['log_bin'] === "ON") {
                $data = array();
                $data['mysql_binlog'] = $this->binaryLog(array($id_mysql_server));
                $data['master_status'] = $mysql_tested->isMaster();
                //Debug::debug($data);
                $this->exportData($id_mysql_server, "mysql_binlog", $data);
            }
        }

        Debug::debug("apres la récupération de la liste des binlogs");
        Debug::checkPoint('apres query');
    
        if ((time()+$id_mysql_server)%(10*$refresh) < $refresh)
        {
            $data['innodb_metrics'] = $this->getInnodbMetrics($name_server);
            $this->exportData($id_mysql_server, "mysql_innodb_metrics", $data, false);
        }


        if ((time()+$id_mysql_server)%(60*$refresh) < $refresh)
        {
            $data['mysql_latency'] = $this->getMysqlLatencyByQuery($name_server);
            $this->exportData($id_mysql_server, "mysql_statistics", $data);
        }

        /*************************************** list Database  */

        if ((time()+$id_mysql_server)%(10*$refresh) < $refresh)
        {
            $data = array();
            $data['mysql_database']['database'] = json_encode($this->getSchema($id_mysql_server));
            $this->exportData($id_mysql_server, "mysql_schemata", $data);
        }
        /*************************************** List table by DB  */

        //to know if we grab statistics on databases & tables
        if ((time()+$id_mysql_server)%(3600*$refresh) < $refresh) {
            $data = array();
            $data['mysql_table']['mysql_table'] = json_encode($this->getDatabase($mysql_tested));
            $this->exportData($id_mysql_server, "mysql_table", $data);
        }

        /****************************************************************** */

        $mysql_tested->sql_close();

        Debug::debugShowTime();

        return true;
    }

    public function allocate_shared_storage($name)
    {
        //storage shared

        Debug::debug($name, 'create file');

        $shared_file = EngineV4::PATH_PIVOT_FILE.time().EngineV4::SEPERATOR.$name;
        $storage             = new StorageFile($shared_file); // to export in config ?
        $SHARED_MEMORY = new SharedMemory($storage);

        //to prevent error on fopen when executed in root and ww-data try to delete it
        //TODO change with 
        
        static::$file_created[] = $shared_file;

        return $SHARED_MEMORY;
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
                    //return false;
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

                    $data['binlog_file_first'] = $files[0];
                    $data['binlog_file_last']  = end($files);
                    $data['binlog_files']      = json_encode($files);
                    $data['binlog_sizes']      = json_encode($sizes);
                    $data['binlog_total_size'] = array_sum($sizes);
                    $data['binlog_nb_files']   = count($files);

                    
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



    public function getDatabase($mysql_tested)
    {
//$grants = $this->getGrants();

        $sql2 = 'select * from information_schema.SCHEMATA;';
        $res2 = $mysql_tested->sql_query($sql2);

        $db_number = $mysql_tested->sql_num_rows($res2);

        if ($db_number > 100) {
            Debug::sql("Too much DBs");
            return false;
        }

        // TEMPORARY => from MariaDB 10.3
        if ($mysql_tested->checkVersion(array("MariaDB", "10.3"), array("Percona", "5.7"), array("MySQL", "5.7")))
        {
            $sql = "select TABLE_CATALOG , TABLE_SCHEMA , TABLE_NAME, TABLE_TYPE , ENGINE,  ROW_FORMAT,
         TABLE_COLLATION, CREATE_OPTIONS, TABLE_COMMENT, TEMPORARY
         from information_schema.TABLES";
        }
        else
        {
            $sql = "select TABLE_CATALOG , TABLE_SCHEMA , TABLE_NAME, TABLE_TYPE , ENGINE,  ROW_FORMAT,
         TABLE_COLLATION, CREATE_OPTIONS, TABLE_COMMENT
         from information_schema.TABLES";
        }
        
        Debug::sql($sql);

        $res = $mysql_tested->sql_query($sql);
        if ($res) {
            if ($mysql_tested->sql_num_rows($res) > 0) {
                $dbs = array();

                while ($arr = $mysql_tested->sql_fetch_array($res, MYSQLI_ASSOC)) {
                    $dbs[] = array_change_key_case($arr);
                }

                return $dbs;
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
        $service[$type.'_server'][$type.'_ping']      = round($ping, 6);
        $service[$type.'_server'][$type.'_error']     = $error_msg;
        $this->exportData($id_mysql_server,$type.'_server',$service,false);
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

            //$this->logger->debug("We import schema list from id_mysql_server : ".$id_mysql_server);
            $sql = "SELECT * FROM information_schema.schemata";

            $res = $mysql_tested->sql_query($sql);

            while ($arr = $mysql_tested->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $schemas[] = array_change_key_case($arr);
            }
        }
        
        return $schemas;
    }
    /*
    *
    *   chack if data as been modified if yes, we will import

    */



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



    public function testproxysql($param)
    {
        $db = Sgbd::sql('server_6612a9fcb1641');

        $db->sql_query("SHOW VARIABLES");

        Debug::parseDebug($param);

        //Mysql::test2("127.0.0.1", 6033, "stnduser", "stnduser");
    }

    /*
    type => key/value, json 

    */

    
    public function exportData($id_mysql_server, $ts_file, array $data, $check_data = true)
    {
        Debug::debug("exportData $id_mysql_server, $ts_file, ".count($data)."");

        //better there than in integrate
        $data = array_change_key_case($data);

        if (! is_array($data)) {
            trigger_error("PMATRL-347 : data must be an array", E_USER_ERROR);
            throw new \Exception("data must be an array !");
        }
        
        $import = true;
        if ($check_data === true){
            $import = false;
            if (($this->isDataModified($id_mysql_server, $ts_file, $data) === true) ){
                $import = true;
            }
        }

        if ($import === true) {
            //$this->logger->debug("[IMPORT] $ts_file we import DB to shared memory [id_mysql_server:".$id_mysql_server."]");
            //$ts_in_µs = Microsecond::timestamp();
            $ts  = time();

            $tmp[$ts][$id_mysql_server] = $data;

            $memory = $this->allocate_shared_storage($ts_file);
            $memory->{$id_mysql_server}     = $tmp;
        }
    }


    public function isDataModified($id_mysql_server, $ts_file, $data)
    {
        self::isValidStruc($data);
        $json = json_encode($data);
        //return true;
        $md5      = md5($json);

        $file_md5 = EngineV4::getFileMd5($ts_file, $id_mysql_server);
        
        $export = false;

        Debug::debug($md5, "NEW MD5 $file_md5");

        if (file_exists($file_md5)) {
            $cmp_md5 = file_get_contents($file_md5);
            Debug::debug($cmp_md5, "OLD MD5 $file_md5");

            if ($cmp_md5 != $md5) {
                $export = true;

                file_put_contents($file_md5, $md5);
            }
        } else {
            if (!is_writable(dirname($file_md5))) {
                Throw new \Exception('PMACTRL-858 : Cannot write file in directory : '.dirname($file_md5).'');
            } 
            file_put_contents($file_md5, $md5);
            $export = true;
        }

        return $export;
    }

    public function test2($param)
    {
        Debug::parseDebug($param);
        $id_mysql_server = 1;

        $data['mysql_database'] = json_encode($this->getSchema($id_mysql_server));
        $this->exportData($id_mysql_server, "mysql_schemata", $data);
    }

    public function import($param)
    {
        Debug::parseDebug($param);
        $db = Sgbd::sql(DB_DEFAULT);

        
        $files = glob(EngineV4::PATH_PIVOT_FILE."*");

        $list = array();
        foreach($files as $file)
        {
            $file_name = pathinfo($file)['basename'];
            Debug::debug($file_name, 'file name');

            $ts_file = explode(EngineV4::SEPERATOR, $file_name)[1];

            if (! isset($list[$ts_file])) {
                $list[$ts_file] = 0;
            }
            $list[$ts_file]++;
        }

        foreach ($list as $ts_file => $val)
        {
            if ($val >= 4)
            {
                $sql = "INSERT IGNORE INTO ts_file (file_name) VALUES ('".$ts_file."')";
                $db->sql_query($sql);

                Mysql::addMaxDate();
                \App\Controller\Control::updateLinkVariableServeur();
            }
        }

        Debug::debug($list);
    }

    /*
    only used for command line
    */
    function updateChown()
    {
        foreach(self::$file_created as $i => $shared_file) {
            $this->Chown($shared_file);
            unset(self::$file_created[$i]);
        }
    }

    /*
    only used for command line
    the goal is to prevent file with root
    */
    function after($param)
    {
        $this->updateChown();
    }

    /*
    to call at end of worker to prevent memory leak
    */
    function purge()
    {
        self::$file_created = array();
    }

    function Chown($shared_file)
    {
        if (file_exists($shared_file)) {

            $user = "www-data";

            chown($shared_file,$user); // get is redhat put apache
            chgrp($shared_file,$user);
        }
    }

    static function isValidStruc($array) {
        // Vérifier si le tableau est un tableau à deux niveaux
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                // Parcourir le sous-tableau pour vérifier que toutes les valeurs sont des chaînes
                foreach ($value as $subKey => $subValue) {
                    if (is_array($subValue)) {
                        if (isset($subValue['Seconds_Behind_Master'])){
                            return true;
                        }

                        //Debug::debug($subValue, "NOT A STRING");
                        trigger_error("PMATRL-478 : Wrong level of DATA (One rank of Array too much) check : $key", E_USER_ERROR);
                        throw new \Exception("Wrong level of DATA (One rank of Array too much) check : $key");
                    }
                }
            } else {
                trigger_error("PMATRL-83 : Wrong level of DATA (One rank of Array missing) check : $key", E_USER_ERROR);
                throw new \Exception("Wrong level of DATA (One rank of Array missing) check : $key");
            }
        }
        return true; // Tout est vérifié, retourner true
    }


    public function getMysqlLatencyByQuery($name_server)
    {
        //to be friendly with all version, we take all, value who not move will be not imported anymore that's all
        $db = Sgbd::sql($name_server);
        $sql = "SELECT `s2`.`avg_us` AS `avg_us` 
        from (
            (select count(0) AS `cnt`,round(`performance_schema`.`events_statements_summary_by_digest`.`AVG_TIMER_WAIT` / 1000000,0) AS `avg_us` 
            from `performance_schema`.`events_statements_summary_by_digest` 
            group by round(`performance_schema`.`events_statements_summary_by_digest`.`AVG_TIMER_WAIT` / 1000000,0)) `s1` 
            join 
            (select count(0) AS `cnt`,round(`performance_schema`.`events_statements_summary_by_digest`.`AVG_TIMER_WAIT` / 1000000,0) AS `avg_us` 
            from `performance_schema`.`events_statements_summary_by_digest` 
            group by round(`performance_schema`.`events_statements_summary_by_digest`.`AVG_TIMER_WAIT` / 1000000,0)) `s2` 
            on(`s1`.`avg_us` <= `s2`.`avg_us`)
        ) 
        group by `s2`.`avg_us` 
        having ifnull(sum(`s1`.`cnt`) / nullif((select count(0) from `performance_schema`.`events_statements_summary_by_digest`),0),0) > 0.95
        order by ifnull(sum(`s1`.`cnt`) / nullif((select count(0) from `performance_schema`.`events_statements_summary_by_digest`),0),0) 
        limit 1";

        $res = $db->sql_query($sql);

        $data = array();
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            
            $data['query_latency_µs_95'] =  $arr['avg_us'];
        }

        $sql = "SELECT round((sum(COUNT_STAR * AVG_TIMER_WAIT))/(sum(COUNT_STAR))/1000000,1) as time_average 
        FROM  `performance_schema`.`events_statements_summary_by_digest`;";
        $res = $db->sql_query($sql);
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            
            $data['query_latency_avg'] = $arr['time_average'];
        }

        return $data;
    }


    public function getInnodbMetrics($name_server)
    {
        $db = Sgbd::sql($name_server);
        $sql = "SELECT * FROM `INFORMATION_SCHEMA`.`INNODB_METRICS`;";

        $res = $db->sql_query($sql);
        $data = array();
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            if (empty($arr['ENABLED'])) {
                continue;
            }
            $arr = array_change_key_case($arr);
            $key = $arr['name'];
            unset($arr['name']);
            $data[$key] = json_encode($arr);

        }
        return $data;

    }


    public function tryProxySqlConnection($param)
    {
        Debug::parseDebug($param);
        $this->view = false;
        
        $name_server = $param[0];
        $id_proxysql_server   = $param[1];
        $id_mysql_server_server = $param[2] ?? "";

        if (empty($id_proxysql_server)) {
            throw new \Exception(__function__.' should have id_proxysql_server in parameter');
        }

        $db = Sgbd::sql($name_server);

        /* get id_mysql_server associed to the front_end */
        $default = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM proxysql_server WHERE id=".$id_proxysql_server;
        $res = $default->sql_query($sql);
        while($ob = $db->sql_fetch_object($res))
        {
            $id_mysql_server = $ob->id_mysql_server;
        }
        /*********************** */

        if ((time()+$id_mysql_server)%1 === 0)
        {
            $data = array();
            $data['proxysql_runtime_mysql_servers']['proxysql_runtime_mysql_servers'] = json_encode($this->getRuntimeMysqlServer(array($name_server)));
            $this->exportData($id_mysql_server, "proxysql_runtime_mysql_servers", $data, false);
        }

       // $import = array();
       // $import['table']['proxysql_connect_error'] = $this->getErrorConnect($param);


       $default->sql_close();
        $db->sql_close();

    }


    public function getRuntimeMysqlServer($param)
    {
        $name_connection = $param[0];
        $mysql_tested = Sgbd::sql($name_connection);

        $sql = "select * from runtime_mysql_servers;";
        $res = $mysql_tested->sql_query($sql);

        $runtime_mysql_servers = array();
        while ($arr = $mysql_tested->sql_fetch_array($res, MYSQLI_ASSOC))
        {
            $runtime_mysql_servers[] = $arr; 
        }

        return $runtime_mysql_servers;
    }



    
}