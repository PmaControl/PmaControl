<?php

use \Glial\Synapse\Controller;
use \Glial\Cli\SetTimeLimit;
use \Glial\Cli\Color;
use \Glial\Security\Crypt\Crypt;
use \Glial\I18n\I18n;
use \Glial\Synapse\Basic;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;
//use phpseclib\Crypt;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;
use \Glial\Synapse\Config;
use \App\Library\Debug;

class Agent extends Controller
{

    use \App\Library\Decoupage;


    use \App\Library\System;
    var $debug    = false;
    var $url      = "Daemon/index/";
    var $log_file = TMP."log/daemon.log";
    var $logger;
    var $loop     = 0;

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
        $file_log     = $this->log_file;
        $handler      = new StreamHandler($file_log, Logger::INFO);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $logger->pushHandler($handler);
        $this->logger = $logger;
    }
    /*
     * (PmaControl 0.8)<br/>
     * @author Aurélien LEQUOY, <aurelien.lequoy@esysteme.com>
     * @return boolean Success
     * @package Controller
     * @since 0.8 First time this was introduced.
     * @description to start the daemon
     * @access public
     */

    public function start($param)
    {
        if (empty($param[0])) {
            Throw new \Exception("No idea set for this Daemon", 80);
        }

        $id_daemon         = $param[0];
        $db                = $this->di['db']->sql(DB_DEFAULT);
        $this->view        = false;
        $this->layout_name = false;



        if (!$this->checkAllEngines()) {


            $msg   = I18n::getTranslation(__("One storage engine is missing on this MySQL server"));
            $title = I18n::getTranslation(__("Error"));
            set_flash("error", $title, $msg);
            //header("location: ".LINK.$this->url);
            exit;
        }

        $sql = "SELECT * FROM daemon_main where id ='".$id_daemon."'";
        $res = $db->sql_query($sql);

        if ($db->sql_num_rows($res) !== 1) {
            $msg   = I18n::getTranslation(__("Impossible to find the daemon with the id : ")."'".$id_daemon."'");
            $title = I18n::getTranslation(__("Error"));
            set_flash("error", $title, $msg);
            //header("location: ".LINK.$this->url);
            exit;
        }

        $ob = $db->sql_fetch_object($res);

        if ($ob->pid === "0") {
            $php = explode(" ", shell_exec("whereis php"))[1];
            //todo add error flux in the log

            $cmd = $php." ".GLIAL_INDEX." Agent launch ".$id_daemon." >> ".TMP.$ob->log_file." & echo $!";
            //debug($cmd);

            $pid = shell_exec($cmd);


            $this->logger->info(Color::getColoredString('Started daemon with pid : '.$pid, "grey", "green"));

            $sql   = "UPDATE daemon_main SET pid ='".$pid."' WHERE id = '".$id_daemon."'";
            $db->sql_query($sql);
            $msg   = I18n::getTranslation(__("The daemon successfully started with")." pid : ".$pid);
            $title = I18n::getTranslation(__("Success"));
            set_flash("success", $title, $msg);
            header("location: ".LINK.$this->url);
        } else {
            $this->logger->info(Color::getColoredString('Impossible to start daemon (Already running)', "yellow"));
            $msg   = I18n::getTranslation(__("Impossible to launch the daemon ")."(".__("Already running !").")");
            $title = I18n::getTranslation(__("Error"));
            set_flash("caution", $title, $msg);
            header("location: ".LINK.$this->url);
        }
    }
    /*
     * (PmaControl 0.8)<br/>
     * @author Aurélien LEQUOY, <aurelien.lequoy@esysteme.com>
     * @return boolean Success
     * @package Controller
     * @since 0.8 First time this was introduced.
     * @description to stop the daemon
     * @access public
     * 
     */

    function stop($param)
    {
        $id_daemon = $param[0];


        $db                = $this->di['db']->sql(DB_DEFAULT);
        $this->view        = false;
        $this->layout_name = false;

        $sql = "SELECT * FROM daemon_main where id ='".$id_daemon."'";
        $res = $db->sql_query($sql);

        if ($db->sql_num_rows($res) !== 1) {
            $msg   = I18n::getTranslation(__("Impossible to find the daemon with the id : ")."'".$id_daemon."'");
            $title = I18n::getTranslation(__("Error"));
            set_flash("error", $title, $msg);
            header("location: ".LINK.$this->url);
            exit;
        }

        $ob = $db->sql_fetch_object($res);

        if ($this->isRunning($ob->pid)) {
            $msg   = I18n::getTranslation(__("The daemon with pid : '".$ob->pid."' successfully stopped "));
            $title = I18n::getTranslation(__("Success"));
            set_flash("success", $title, $msg);

            $cmd = "kill ".$ob->pid;
            shell_exec($cmd);
            //shell_exec("echo '[" . date("Y-m-d H:i:s") . "] DAEMON STOPPED !' >> " . $ob->log_file);

            $this->logger->info(Color::getColoredString('Stopped daemon with the pid : '.$ob->pid, "grey", "red"));
        } else {

            if (!empty($pid)) {
                $this->logger->info(Color::getColoredString('Impossible to find the daemon with the pid : '.$pid, "yellow"));
            }

            $msg   = I18n::getTranslation(__("Impossible to find the daemon with the pid : ")."'".$ob->pid."'");
            $title = I18n::getTranslation(__("Daemon was already stopped or in error"));
            set_flash("caution", $title, $msg);
        }

        sleep(1);

        if (!$this->isRunning($ob->pid)) {
            $sql = "UPDATE daemon_main SET pid ='0' WHERE id = '".$id_daemon."'";
            $db->sql_query($sql);
        } else {
            $this->logger->info(Color::getColoredString('Impossible to stop daemon with pid : '.$pid, "grey", "red"));
            throw new Exception('PMACTRL-876 : Impossible to stop daemon with pid : "'.$ob->pid.'"');
        }

        header("location: ".LINK.$this->url);
    }
    /*
     * (PmaControl 0.8)<br/>
     * @author Aurélien LEQUOY, <aurelien.lequoy@esysteme.com>
     * @return boolean Success
     * @package Controller
     * @since 0.8 First time this was introduced.
     * @description loop to execute check on each mysql server
     * @access public
     * 
     */

    public function launch($params)
    {

        Debug::parseDebug($params);
        $id = $params[0];

        $debug = "";
        if ($this->debug === "1") {
            $debug = "--debug";
        }

        $id_loop = 0;
        while (1) {

            $id_loop++;

            $db  = $this->di['db']->sql(DB_DEFAULT);
            $sql = "SELECT * FROM daemon_main where id=".$id;
            $res = $db->sql_query($sql);

            while ($ob = $db->sql_fetch_object($res)) {

                // to prevent inactive daemon or crontab failure (to move to right place
                $cmd = $php." ".GLIAL_INDEX." control service";
                Debug::debug($cmd);
                shell_exec($cmd);


                $php = explode(" ", shell_exec("whereis php"))[1];
                //$cmd = $php . " " . GLIAL_INDEX . " " . $ob->class . " " . $ob->method . " " . $ob->params . " " . $debug . " >> " . $this->log_file . " & echo $!";
                $cmd = $php." ".GLIAL_INDEX." ".$ob->class." ".$ob->method." ".$ob->id." ".$ob->params." loop:".$id_loop." ".$debug." >> ".$this->log_file."";
                Debug::debug($cmd);

                $pid = shell_exec($cmd);
                Debug::debug($pid);

                $refresh_time = $ob->refresh_time;
            }

            Debug::debug("refresh time : ".$refresh_time);

            // in case of mysql gone away, like this daemon restart when mysql is back
            $this->di['db']->sql(DB_DEFAULT)->sql_close();


            if (empty($refresh_time)) {
                $refresh_time = 60;
            }

            sleep($refresh_time);
        }
    }

    /**
     * (PmaControl 0.8)<br/>
     * @author Aurélien LEQUOY, <aurelien.lequoy@esysteme.com>
     * @return boolean Success
     * @package Controller
     * @since 0.8 First time this was introduced.
     * @description get list of MySQL server and try to connect on each one
     * @access public
     */
    public function testAllMysql($param)
    {

        $date_start = microtime(true);


        Debug::parseDebug($param);

        if (Debug::$debug) {
            echo "[".date('Y-m-d H:i:s')."]"." Start all tests\n";
        }

        $this->view = false;
        $db         = $this->di['db']->sql(DB_DEFAULT);
        $sql        = "select * from mysql_server WHERE is_monitored =1";
        $res        = $db->sql_query($sql);

        $server_list = array();
        while ($ob          = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $server_list[] = $ob;
        }


        $sql = "SELECT * FROM daemon_main where id=1";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $maxThreads       = $ob->thread_concurency; // check MySQL server x by x
            $maxExecutionTime = $ob->max_delay;
        }

        Debug::debug("max execution time : ".$maxExecutionTime);

        //to prevent any trouble with fork
        $db->sql_close();

        //$maxThreads = \Glial\System\Cpu::getCpuCores();




        Debug::debug("Nombre de threads : ".$maxThreads);


        $openThreads     = 0;
        $child_processes = array();

        if (empty($server_list)) {
            sleep(10);
            $this->logger->info(Color::getColoredString('List of server to test is empty', "grey", "red"));
            //throw new Exception("List of server to test is empty", 20);
        }


        //to prevent collision at first running (the first run is not made in multi thread

        /*
          if ($this->loop == 0) {

          $maxThreads = 1;
          $this->loop = 1;
          } */


        $father = false;
        foreach ($server_list as $server) {
            //
            //echo str_repeat("#", count($child_processes)) . "\n";

            $pid                   = pcntl_fork();
            $child_processes[$pid] = 1;

            if ($pid == -1) {
                throw new Exception('PMACTRL-057 : Couldn\'t fork thread !', 80);
            } else if ($pid) {

                if (count($child_processes) > $maxThreads) {
                    $childPid = pcntl_wait($status); //  pcntl_wexitstatus
                    unset($child_processes[$childPid]);

                    // status error => do sth
                }
                $father = true;
            } else {

                // one thread to test each MySQL server
                $this->testMysqlServer($server, $maxExecutionTime);
                $father = false;
                //we want that child exit the foreach
                break;
            }
            usleep(50000);




            //Debug::debug($child_processes);
        }

        if ($father) {
            $tmp = $child_processes;
            foreach ($tmp as $thread) {
                $childPid = pcntl_wait($status);
                unset($child_processes[$childPid]);
            }

            //$this->isGaleraCluster(array());

            $time = microtime(true) - $date_start;


            Debug::debug("All tests termined : ".round($time, 2)." sec");
        } else {
            exit;
        }
    }
    /*
     * only for test
     */

    public function testMaxExecution($param)
    {
        Debug::parseDebug($param);

        if (Debug::$debug) {
            echo "[".date('Y-m-d H:i:s')."]"." Start all tests\n";
        }

        $this->view = false;
        $db         = $this->di['db']->sql(DB_DEFAULT);
        $sql        = "select * from mysql_server WHERE is_monitored =1";
        $res        = $db->sql_query($sql);

        $server_list = array();
        while ($ob          = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $server_list[] = $ob;
        }


        $sql = "SELECT * FROM daemon_main where id=1";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $maxThreads       = $ob->thread_concurency; // check MySQL server x by x
            $maxExecutionTime = $ob->max_delay;
        }



        //to prevent any trouble with fork
        $db->sql_close();


        foreach ($server_list as $server) {
            //echo str_repeat("#", count($child_processes)) . "\n";

            $this->testMysqlServer($server, $maxExecutionTime);
        }
    }

    /**
     * (PmaControl 0.8)<br/>
     * @author Aurélien LEQUOY, <aurelien.lequoy@esysteme.com>
     * @return boolean Success
     * @package Controller
     * @since 0.8 First time this was introduced.
     * @description launch a subprocess limited in time to try MySQL connection, if ok get status and show master/slave status
     * @access public
     */
    private function testMysqlServer($server, $max_execution_time = 10)
    {
        $this->view = false;

        //execute a process with a timelimit (in case of MySQL don't answer and keep connection)
        //$max_execution_time = 20; // in seconds
        //Debug::debug("monitoring : " . $server['name'] . ":" . $server['id']);

        $ret = SetTimeLimit::run("Agent", "tryMysqlConnection", array($server['name'], $server['id']), $max_execution_time);

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

            $sql = "UPDATE mysql_server SET `error`='".$db->sql_real_escape_string($ret['stdout'])."', `date_refresh`='".date("Y-m-d H:i:s")."' where id = '".$server['id']."'";
            $db->sql_query($sql);

            //echo $sql . "\n";

            $sql = "UPDATE mysql_replication_stats SET is_available = 0 where id_mysql_server = '".$server['id']."'";
            $db->sql_query($sql);
            $db->sql_close();

            echo (Debug::$debug) ? $server['name']." KO :\n" : "";
            (Debug::$debug) ? print_r($ret) : '';
            return false;
        } else {
            //echo (Debug::$debug) ? $server['name']." OK \n" : "";
            return true;
        }
    }

    /**
     * (PmaControl 0.8)<br/>
     * @example ./glial agent tryMysqlConnection name id_mysql_server
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



        // on évite de lancer deux fois le même process sur le même serveur MySQL/MariaDB/Percona

        $pid = getmypid();
        Debug::debug($pid);

        $cmd = "ps -eaf | grep -v grep | grep -v sh | grep php | grep tryMysqlConnection | grep ".$name_server." | grep ".$id_server." | awk '/php/{print $2}'";
        $cmp = trim(shell_exec($cmd));


        Debug::debug($cmp);


        $pids = explode("\n", $cmp);
        Debug::debug($pids);

        if (count($pids) > 1) {
            throw new \Exception('PMACTRL-521 : un processus est déjà en cours sur ce serveur', 80);
            //Debug::debug("");
            exit(12);
        }
        /*         * * */

        $mysql_tested = $this->di['db']->sql($name_server);

        $db = $this->di['db']->sql(DB_DEFAULT);

        $variables = $mysql_tested->getVariables();

        Debug::debug($variables);

        $status = $mysql_tested->getStatus();

        $master = $mysql_tested->isMaster();
        $slave  = $mysql_tested->isSlave();


        Debug::debug($slave);
        /*
          $sql       = "SELECT now() as date_time";
          $res2      = $mysql_tested->sql_query($sql);
          $date_time = $mysql_tested->sql_fetch_object($res2);  //can be empty ???????????
         *
          $date_time =  $date_time->date_time;
         *
         */

        //$date_time;


        $date_time = date("Y-m-d H:i:s");


        $schema = array();


        $update_db = false;
        if (date("H:i") === "13:00") {


            if (version_compare($mysql_tested->getVersion(), '5.0', '>=')) {


                $update_db = true;


                $sql = 'set global innodb_stats_on_metadata=0;';
                $mysql_tested->sql_query($sql);

                $sql2 = 'SELECT table_schema,
sum( data_length ) as "data",
sum( index_length ) as "index",
sum( data_free ) as "data_free" ,
count(1) as "tables",
sum(TABLE_ROWS) as "rows",
DEFAULT_CHARACTER_SET_NAME,
DEFAULT_COLLATION_NAME
FROM information_schema.TABLES a
INNER JOIN information_schema.SCHEMATA b ON a.table_schema = b.SCHEMA_NAME
WHERE table_schema !="information_schema" AND table_schema !="performance_schema" AND table_schema !="mysql"
GROUP BY table_schema ;';


                //@bug : can crash MySQL have to see : https://mariadb.atlassian.net/browse/MDEV-9631

                $schema = [];


                //a exporter dans un autre daemon && ne pas le lancer deux fois
                //$mysql_tested->


                $sql   = "SHOW DATABASES";
                Debug::debug(SqlFormatter::highlight($sql));
                $res50 = $mysql_tested->sql_query($sql);

                if ($mysql_tested->sql_num_rows($res50) < 100) {
                    Debug::debug(SqlFormatter::highlight($sql2));
                    $res5 = $mysql_tested->sql_query($sql2);
                    while ($ob   = $mysql_tested->sql_fetch_array($res5)) {
                        $schema[$ob['table_schema']] = $ob;
                    }
                }
            }

            /*
             */
        }

        try {

            $sql = "START TRANSACTION;";
            Debug::debug(trim(SqlFormatter::highlight($sql)));
            $db->sql_query($sql);
            $sql = "SELECT id FROM mysql_replication_stats where id_mysql_server = '".$id_server."'";

            Debug::debug(trim(SqlFormatter::highlight($sql)));
            $res3 = $db->sql_query($sql);

            $table                                 = array();
            $table['mysql_server']['id']           = $id_server;
            $table['mysql_server']['error']        = '';
            $table['mysql_server']['date_refresh'] = date("Y-m-d H:i:s");

            $res10 = $db->sql_save($table);

            if (!$res10) {
                throw new \Exception('PMACTRL-159 : impossible to remove error !', 60);
            }

            $table = array();

            if ($db->sql_num_rows($res3) == 1) {
                $ob                                     = $db->sql_fetch_object($res3);
                $table['mysql_replication_stats']['id'] = $ob->id;
            }

            $table['mysql_replication_stats']['id_mysql_server'] = $id_server;
            $table['mysql_replication_stats']['is_available']    = 1;

            $table['mysql_replication_stats']['ping']           = 1;
            $table['mysql_replication_stats']['version']        = $mysql_tested->getServerType()." : ".$mysql_tested->getVersion();
            $table['mysql_replication_stats']['date']           = $date_time;
            $table['mysql_replication_stats']['is_master']      = ($master) ? 1 : 0;
            $table['mysql_replication_stats']['is_slave']       = ($slave) ? 1 : 0;
            $table['mysql_replication_stats']['uptime']         = ($mysql_tested->getStatus('Uptime')) ? $mysql_tested->getStatus('Uptime') : '-1';
            $table['mysql_replication_stats']['time_zone']      = ($mysql_tested->getVariables('system_time_zone')) ? $mysql_tested->getVariables('system_time_zone') : '-1';
            $table['mysql_replication_stats']['ping']           = 1;
            $table['mysql_replication_stats']['last_sql_error'] = '';
            $table['mysql_replication_stats']['binlog_format']  = ($mysql_tested->getVariables('binlog_format')) ? $mysql_tested->getVariables('binlog_format') : 'N/A';

            $id_mysql_replication_stats = $db->sql_save($table);

            if (!$id_mysql_replication_stats) {



                Debug::debug($table);
                Debug::debug($db->sql_error());

                throw new \Exception('PMACTRL-059 : insert in mysql_replication_stats !', 60);
            }


            if ($update_db) {
                //get all id_mysql_database
                $id_mysql_server = [];
                $sql             = "SELECT * FROM mysql_database WHERE id_mysql_server = '".$id_server."'";
                Debug::debug(trim(SqlFormatter::highlight($sql)));
                $res6            = $db->sql_query($sql);

                while ($ob = $db->sql_fetch_array($res6)) {
                    $id_mysql_server[$ob['name']] = $ob;
                }




                foreach ($schema as $database) {
                    $mysql_database = [];

                    if (!empty($id_mysql_server[$database['table_schema']])) {

                        $mysql_database['mysql_database']['id'] = $id_mysql_server[$database['table_schema']]['id'];
                        //remove DB updated or add
                        unset($id_mysql_server[$database['table_schema']]);
                    } else {
                        // push event new DB add
                    }

                    if (empty($database['data'])) $database['data']      = 0;
                    if (empty($database['data_free'])) $database['data_free'] = 0;
                    if (empty($database['index'])) $database['index']     = 0;

                    $mysql_database['mysql_database']['id_mysql_server'] = $id_server;
                    $mysql_database['mysql_database']['name']            = $database['table_schema'];
                    $mysql_database['mysql_database']['tables']          = $database['tables'];

                    if (empty($database['rows'])) {
                        $database['rows'] = 0;
                    }

                    $mysql_database['mysql_database']['rows']               = $mysql_database['mysql_database']['data_length']        = $database['data'];
                    $mysql_database['mysql_database']['data_free']          = $database['data_free'];
                    $mysql_database['mysql_database']['index_length']       = $database['index'];
                    $mysql_database['mysql_database']['character_set_name'] = $database['DEFAULT_CHARACTER_SET_NAME'];
                    $mysql_database['mysql_database']['collation_name']     = $database['DEFAULT_COLLATION_NAME'];
                    $mysql_database['mysql_database']['binlog_do_db']       = 0;
                    $mysql_database['mysql_database']['binlog_ignore_db']   = 0;


                    // ????
                    if ($master) {
                        $mysql_database['mysql_database']['binlog_do_db']     = 1;
                        $mysql_database['mysql_database']['binlog_ignore_db'] = 0;
                    }

                    $res7 = $db->sql_save($mysql_database);

                    if (!$res7) {
                        print_r($mysql_database);
                        print_r($db->sql_error());
                        throw new \Exception('PMACTRL-060 : insert in mysql_database !', 60);
                    }
                }


                //delete DB deleted
                foreach ($id_mysql_server as $key => $tab) {
                    $sql = "DELETE FROM mysql_database WHERE id = '".$tab['id']."'";
                    Debug::debug(SqlFormatter::highlight($sql));
                    $db->sql_query($sql);
                    // push event DB deleted

                    $this->logger->info(Color::getColoredString('['.$name_server.'] Databases deleted', "yellow"));
                }
            }
            /*             * ********************** */

            if ($slave) {



                $connection_name = array();

                foreach ($slave as $thread_slave) {
                    $mysql_replication_thread = array();
                    if (empty($thread_slave['Connection_name'])) {
                        $thread_slave['Connection_name'] = '';
                    }
                    $sql = "SELECT id from mysql_replication_thread where id_mysql_replication_stats = '".$id_mysql_replication_stats."' 
			AND thread_name = '".$thread_slave['Connection_name']."'";

                    Debug::debug($sql);

                    $res34 = $db->sql_query($sql);

                    while ($ob = $db->sql_fetch_object($res34)) {
                        $mysql_replication_thread['mysql_replication_thread']['id'] = $ob->id;
                    }

                    $mysql_replication_thread['mysql_replication_thread']['id_mysql_replication_stats'] = $id_mysql_replication_stats;
                    $mysql_replication_thread['mysql_replication_thread']['relay_master_log_file']      = $thread_slave['Relay_Master_Log_File'];
                    $mysql_replication_thread['mysql_replication_thread']['exec_master_log_pos']        = $thread_slave['Exec_Master_Log_Pos'];
                    $mysql_replication_thread['mysql_replication_thread']['thread_io']                  = $thread_slave['Slave_IO_Running'];
                    $mysql_replication_thread['mysql_replication_thread']['thread_sql']                 = $thread_slave['Slave_SQL_Running'];
                    $mysql_replication_thread['mysql_replication_thread']['thread_name']                = (empty($thread_slave['Connection_name'])) ? '' : $thread_slave['Connection_name'];
                    $mysql_replication_thread['mysql_replication_thread']['time_behind']                = $thread_slave['Seconds_Behind_Master'];
                    $mysql_replication_thread['mysql_replication_thread']['master_host']                = $thread_slave['Master_Host'];
                    $mysql_replication_thread['mysql_replication_thread']['master_port']                = $thread_slave['Master_Port'];
                    $mysql_replication_thread['mysql_replication_thread']['last_sql_error']             = (empty($thread_slave['Last_SQL_Error'])) ? $thread_slave['Last_Error'] : $thread_slave['Last_SQL_Error'];
                    $mysql_replication_thread['mysql_replication_thread']['last_sql_errno']             = (empty($thread_slave['Last_SQL_Errno'])) ? $thread_slave['Last_Errno'] : $thread_slave['Last_SQL_Errno'];
                    $mysql_replication_thread['mysql_replication_thread']['last_io_error']              = (empty($thread_slave['Last_IO_Error'])) ? $thread_slave['Last_Error'] : $thread_slave['Last_IO_Error'];
                    $mysql_replication_thread['mysql_replication_thread']['last_io_errno']              = (empty($thread_slave['Last_IO_Errno'])) ? $thread_slave['Last_Errno'] : $thread_slave['Last_IO_Errno'];


                    $connection_name[] = $mysql_replication_thread['mysql_replication_thread']['thread_name'];

                    Debug::debug($mysql_replication_thread);

                    $res8 = $db->sql_save($mysql_replication_thread);

                    if (!$res8) {
                        debug($db->sql_error());
                        throw new \Exception('PMACTRL-060 : insert in mysql_database !', 60);
                    }
                }

                // was bug there in case of multi source replication and we remove one thread !

                $sql   = "SELECT `id`,`thread_name` from `mysql_replication_thread` where `id_mysql_replication_stats` = '".$id_mysql_replication_stats."'";
                Debug::debug(trim(SqlFormatter::highlight($sql)));
                $res35 = $db->sql_query($sql);

                while ($ob = $db->sql_fetch_object($res35)) {

                    if (!in_array($ob->thread_name, $connection_name)) {

                        $sql = "DELETE FROM mysql_replication_thread WHERE id = ".$ob->id.";";
                        Debug::debug(trim(SqlFormatter::highlight($sql)));
                        $db->sql_query($sql);
                    }
                }
            } else {

                $sql   = "SELECT id from mysql_replication_thread where id_mysql_replication_stats = '".$id_mysql_replication_stats."'";
                Debug::debug(trim(SqlFormatter::highlight($sql)));
                $res34 = $db->sql_query($sql);

                while ($ob = $db->sql_fetch_object($res34)) {
                    //$mysql_replication_thread['mysql_replication_thread']['id'] = $ob->id;

                    $sql = "DELETE FROM mysql_replication_thread WHERE id = ".$ob->id.";";
                    Debug::debug(trim(SqlFormatter::highlight($sql)));
                    $out = $db->sql_query($sql);

                    if (!$out) {
                        $this->logger->info(Color::getColoredString(print_r($db->sql_error()), "red"));
                    }

                    $this->logger->info(Color::getColoredString($sql, "red"));
                    $this->logger->info(Color::getColoredString('Slave deleted', "yellow"));
                    //log delete of a slave !
                }
            }

            $sql = "COMMIT;";
            Debug::debug(trim(SqlFormatter::highlight($sql)));
            $db->sql_query($sql);
        } catch (\Exception $ex) {

            $sql = "ROLLBACK;";
            Debug::debug(trim(SqlFormatter::highlight($sql)));
            $db->sql_query($sql);

            $msg = $ex->getMessage();

            throw new \Exception("PMACTRL-058 : ROLLBACK made ! (".$msg.")", 60);
        }


        if (version_compare($mysql_tested->getVersion(), '5.0', '>=')) {
            $this->saveVariables($variables, $id_server);
        }


        if (version_compare($mysql_tested->getVersion(), '5.0', '>=')) {
            $this->saveStatus($status, $id_server);
        }


        $db->sql_close();
        $mysql_tested->sql_close();


        if (count($err = error_get_last()) != 0) {
            throw new \Exception('PMACTRL-056 : '.$err['message'].' in '.$err['file'].' on line '.$err['line'], 80);
        }
    }
    /*
     * (PmaControl 0.8)<br/>
     * @author Aurélien LEQUOY, <aurelien.lequoy@esysteme.com>
     * @return boolean Success
     * @package Controller
     * @since 0.8 First time this was introduced.
     * @description refresh list of MySQL according pmacontrol/configuration/db.config.ini.php
     * @access public
     */

    public function updateServerList()
    {
        $this->view    = false;
        $db            = $this->di['db']->sql(DB_DEFAULT);
        $sql           = "SELECT * FROM `mysql_server`";
        $servers_mysql = $db->sql_fetch_yield($sql);
        $all_server    = array();
        foreach ($servers_mysql as $mysql) {
            $all_server[$mysql['name']] = $mysql;
        }
        Crypt::$key = CRYPT_KEY;

        $all = array();
        foreach ($this->di['db']->getAll() as $server) {

            $all[]       = $server;
            $info_server = $this->di['db']->getParam($server);
            $data        = array();

            if (!empty($all_server[$server])) {
                $data['mysql_server']['id'] = $all_server[$server]['id'];

                unset($all_server[$server]);
            } else {
                echo "Add : ".$server." to monitoring\n";

                //to update
                $data['mysql_server']['id_client']      = 1;
                $data['mysql_server']['id_environment'] = 1;
            }

            $data['mysql_server']['name']         = $server;
            $data['mysql_server']['display_name'] = $server;
            $data['mysql_server']['ip']           = $info_server['hostname'];
            $data['mysql_server']['login']        = $info_server['user'];

            if (!empty($info_server['crypted']) && $info_server['crypted'] == 1) {
                $passwd                                      = $info_server['password'];
                $data['mysql_server']['is_password_crypted'] = 1;
            } else {
                $passwd                                      = Crypt::encrypt($info_server['password']);
                $data['mysql_server']['is_password_crypted'] = 0;
            }

            $data['mysql_server']['passwd']       = $passwd;
            $data['mysql_server']['port']         = empty($info_server['port']) ? 3306 : $info_server['port'];
            $data['mysql_server']['date_refresh'] = date('Y-m-d H:i:s');
            $data['mysql_server']['database']     = $info_server['database'];

            //$data['mysql_server']['is_monitored'] = 1;

            if (!empty($info_server['ssh_login'])) {
                $data['mysql_server']['ssh_login'] = Crypt::encrypt($info_server['ssh_login']);
            }

            if (!empty($info_server['ssh_password'])) {
                $data['mysql_server']['ssh_password'] = Crypt::encrypt($info_server['ssh_password']);
            }

            if (!$id_mysql_server = $db->sql_save($data)) {
                debug($data);
                debug($db->sql_error());
                exit;
            } else {

                //$this->OnAddServer(array($id_mysql_server));
                //echo $data['mysql_server']['name'] . PHP_EOL;
            }
        }

        foreach ($all_server as $to_delete) {
            $sql = "DELETE FROM `mysql_server` WHERE id=".$to_delete['id']."";
            $db->sql_query($sql);

            echo "[Warning] Removed : ".$to_delete['name']." from monitoring\n";
        }

        $sql5 = "INSERT IGNORE INTO `ts_max_date` (`id_daemon_main`, `id_mysql_server`, `date`, `date_previous`) SELECT 7,id, now(), now() from mysql_server;";
        $db->sql_query($sql5);
    }

    public function index()
    {
        $db  = $this->di['db']->sql(DB_DEFAULT);
        $sql = "SELECT * FROM `daemon_main` order by id";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['daemon'][] = $ob;
        }

        $this->set('data', $data);
    }
    /*
     * (PmaControl 0.8)<br/>
     * @author Aurélien LEQUOY, <aurelien.lequoy@esysteme.com>
     * @return boolean Success
     * @package Controller
     * @since 0.8 First time this was introduced.
     * @description test if daemon is launched or not according with pid saved in table daemon_main
     * @access public
     * 
     */

    private function isRunning($param)
    {
        if (is_array($param)) {
            $pid = $param[0];
        } else {
            $pid = $param;
        }

        if (empty($pid)) {
            return false;
        }
        $cmd   = "ps -p ".$pid;
        $alive = shell_exec($cmd);

        if (strpos($alive, $pid) !== false) {
            return true;
        }
        return false;
    }
    /*
     * This method can provide duplicate KEY, if daemon is started for first time with more than 2 servers, next run all will be fine
     *
     *
     * Type :
     *  - 1 => int
     *  - 2 => double
     *  - 3 => text
     */

    public function saveStatus($all_status, $id_mysql_server)
    {

        $default  = $this->di['db']->sql(DB_DEFAULT);
        $all_name = array_keys($all_status);

        $sql = "SELECT * FROM status_name";
        Debug::debug(SqlFormatter::highlight($sql));


        $index = [];
        $data  = [];

        $res = $default->sql_query($sql);

        while ($ob = $default->sql_fetch_object($res)) {
            $index[]                 = $ob->name;
            $data[$ob->name]['id']   = $ob->id;
            $data[$ob->name]['type'] = strtolower($ob->type);
        }

        foreach ($all_status as $name => $status) {
            $name = strtolower($name);

            if (!in_array($name, $index)) {
                echo "add ".$name."\n";

                $status_name['status_name']['name'] = $name;
                $status_name['status_name']['type'] = self::getTypeOfData($status);
                //$status_name['status_name']['value'] = $status;

                $id = $default->sql_save($status_name);
                if (!$id) {
                    debug($status_name);
                    debug($default->sql_error());

                    throw new Exception('PMACTRL : Impossible to save');
                }
            }
        }
        $this->saveValue($data, $all_status, $id_mysql_server);
    }
    /*
     *
     *  $data => contient les données préformaté des entêtes
     *
     *
     */

    public function saveValue($data, $all_status, $id_mysql_server, $variables = 0)
    {
        $db = $this->di['db']->sql(DB_DEFAULT);

        if ($variables === 0) {
            $table_name = 'status';
        } else {
            $table_name = 'variables';
        }

        /*
         * 
         * int : 0
         * double : 1
         * text : 2
         */

        $tables = array($table_name.'_value_int__'.$id_mysql_server, $table_name.'_value_double__'.$id_mysql_server,
            $table_name.'_value_text__'.$id_mysql_server);
        //$tables = array($table_name . '_value_int', $table_name . '_value_double', $table_name . '_value_text');

        $i = 0;
        foreach ($tables as $table) {
            $sql[$i] = "INSERT INTO `".$table."` (`id_mysql_server`, `id_".$table_name."_name`,`date`, `value`) VALUES ";
            $i++;
        }

        $feed = array();
        $date = date('Y-m-d H:i:s');


        Debug::debug($all_status);


        foreach ($all_status as $name => $status) {


            $name = strtolower($name);
            //Debug::debug($data[$name]['type']);
            // case of sql_slave_skip_counter 
            if ($status === '' && in_array($data[$name]['type'], array(0, 1))) {
                $status = '0';
            }

            $feed[$data[$name]['type']][] = "(".$id_mysql_server.",".$data[$name]['id'].",'".$date."','".$db->sql_real_escape_string($status)."')";
        }


        try {
            $i = 0;
            for ($i = 0; $i < 3; $i++) {
                // dans le cas ou on a pas de donnée dans une section (double pour mysql 5.7)
                if (!empty($feed[$i])) {

                    $req = $sql[$i].implode(',', $feed[$i]).";";
                    Debug::debug(trim(SqlFormatter::highlight($req)));
                    $ret = $db->sql_query($req);
                    if (!$ret) {
                        //à changer : chopper l'exception mysql et l'afficher dans le log d'erreur de PmaControl
                        $this->logger->error(Color::getColoredString($db->sql_error(), "white", "red"));
                        //$this->stop(array(1));
                        //throw new \Exception('PMACTRL-065 : '.$ret->sql_error());
                    }
                }
            }

            $sql = "UPDATE `".$table_name."_max_date`  SET `date_previous`=`date`,`date`= '".$date."' WHERE id_mysql_server=".$id_mysql_server."";
            Debug::debug(trim(SqlFormatter::highlight($sql)));
            $db->sql_query($sql);
        } catch (Exception $ex) {

            //à changer
            $this->logger->error(Color::getColoredString("ERROR: ".$ex->getMessage(), "white", "red"));
        }


        //$sql =
        //$db->sql_query("REPLACE INTO ".$table_name."_max_date SELECT * FROM ".$table_name."_max_date WHERE ;
    }

    static private function isFloat($value)
    {
        // test before => must be numeric first
        if (strstr($value, ".")) {
            return true;
        }
        return ((int) $value != $value);
    }
    /* Type :
     *  - 0 => int
     *  - 1 => double
     *  - 2 => text
     */

    static private function getTypeOfData($value)
    {
        $val = 2;

        $is_numeric = is_numeric($value);

        if ($is_numeric === true) {
            //debug($is_numeric);
            $val = 0; // bigint unsigned

            $is_float = self::isFloat($value);



            if ($is_float) {
                $val = 1;
            }

            //case of negative int
            if ($value < 0) {
                $val = 1;
            }
        }

        return $val;
    }
    /*
     *
     * Move to test for PHPUnit
     */

    public function testData()
    {
        $this->view = false;

        $nogood = 0;

        $tests  = [1452, 0.125, 254.25, "0.0000", "0.254", "254.25", "15", "1e25",
            "ggg.ggg", "fghg", "my_cluster_test", "-1", -2, -2.5];
        $result = [0, 1, 1, 1, 1, 1, 0, 0, 2, 2, 2, 1, 1, 1];

        if (count($tests) !== count($result)) {
            throw new \Exception("PMACTRL : array not the same size");
        }

        $i = 0;
        foreach ($tests as $test) {
            $val = self::getTypeOfData($test);

            if ($val != $result[$i]) {
                echo "#".$i." -- ".$test.":".$val.":".$result[$i]." no good \n";
                $nogood++;
            } else {
                echo "#".$i." -- ".$test.":".$val.":".$result[$i]."\t => GOOOD \n";
            }
            $i++;
        }

        if (!empty($nogood)) {
            echo "##################### NO GOOD ################";
        }
    }

    public function logs()
    {
        $db = $this->di['db']->sql(DB_DEFAULT);


        // update param for the daemon
        if ($_SERVER['REQUEST_METHOD'] == "POST") {

            if (!empty($_POST['daemon_main']['refresh_time']) && !empty($_POST['daemon_main']['thread_concurency']) && !empty($_POST['daemon_main']['max_delay'])) {
                $table                      = [];
                $table['daemon_main']       = $_POST['daemon_main'];
                $table['daemon_main']['id'] = 1;
                $gg                         = $db->sql_save($table);

                if (!$gg) {
                    set_flash("error", "Error", "Impossible to update the params of Daemon");
                } else {
                    set_flash("success", "Success", "The params of Daemon has been updated");
                }
                header("location: ".LINK."Server/listing/logs");
            }
        }

        $this->di['js']->code_javascript("var objDiv = document.getElementById('data_log'); objDiv.scrollTop = objDiv.scrollHeight;");


        $sql = "SELECT * FROM `daemon_main` WHERE id =1";
        $res = $db->sql_query($sql);
        $ob  = $db->sql_fetch_object($res);


        $data['log_file'] = TMP.$ob->log_file;

        $data['log'] = __("Log file doens't exist yet !");

        if (file_exists($data['log_file'])) {

            //$ob->log_file = escapeshellarg($ob->log_file); // for the security concious (should be everyone!)
            //$data['log'] = `tail -n 10000 $ob->log_file`;
            //full php implementation
            $data['log'] = $this->tailCustom($data['log_file'], 10000);
        }

        $_GET['daemon_main']['thread_concurency'] = $ob->thread_concurency;

        $data['thread'] = array();
        for ($i = 1; $i <= 128; $i++) {
            $tmp = [];

            $tmp['id']      = $i;
            $tmp['libelle'] = $i;

            $data['thread_concurency'][] = $tmp;
        }


        $_GET['daemon_main']['refresh_time'] = $ob->refresh_time;

        $data['thread'] = array();
        for ($i = 1; $i <= 60; $i++) {
            $tmp = [];

            $tmp['id']      = $i;
            $tmp['libelle'] = $i;

            $data['refresh_time'][] = $tmp;
        }

        $_GET['daemon_main']['max_delay'] = $ob->max_delay;

        $data['thread'] = array();
        for ($i = 1; $i <= 60; $i++) {
            $tmp = [];

            $tmp['id']      = $i;
            $tmp['libelle'] = $i;

            $data['max_delay'][] = $tmp;
        }
        //$data[''] = ;



        $this->set('data', $data);
    }

    public function refreshDataSsh($param)
    {
        Debug::parseDebug($param);

        $this->view = false;
        $db         = $this->di['db']->sql(DB_DEFAULT);
        $sql        = "SELECT * FROM `mysql_server` WHERE  ssh_available =1 AND is_monitored=1";
        $res        = $db->sql_query($sql);

        Debug::debug($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            $ssh = new SSH2($ob->ip);
            $rsa = new RSA();

            Debug::debug($ob->key_private_path);

            $privatekey = file_get_contents($ob->key_private_path);

            if ($rsa->loadKey($privatekey) === false) {
                exit("private key loading failed!");
            }

            Debug::debug('Server : '.$ob->ip." : ".$ob->key_private_user." ".$ob->key_private_path);

            if (!$ssh->login($ob->key_private_user, $rsa)) {
                echo "Login Failed\n";
                continue;
            }

            Debug::debug("SSH OK;");

            $this->hardware($ssh, $ob->id);
            $this->addIpVirtuel($ssh, $ob->id);
        }
    }

    public function updateHaProxy()
    {
        $this->view = false;
        $db         = $this->di['db']->sql(DB_DEFAULT);

        $haproxys = $this->di['config']->get('haproxy');

        foreach ($haproxys as $name => $haproxy) {

            $table                                   = [];
            $talbe['haproxy_main']['hostname']       = $haproxy['hostname'];
            $talbe['haproxy_main']['ip']             = $haproxy['hostname'];
            $talbe['haproxy_main']['vip']            = $haproxy['vip'];
            $talbe['haproxy_main']['csv']            = $haproxy['csv'];
            $talbe['haproxy_main']['stats_login']    = $haproxy['csv'];
            $talbe['haproxy_main']['stats_password'] = $haproxy['csv'];

            print_r($haproxy);
        }
    }

    public function checkAllEngines()
    {

        return true; // time to fix with mariadb 10.3.2

        $this->view = false;
        $db         = $this->di['db']->sql(DB_DEFAULT);

        $sql = "select distinct a.engine from information_schema.tables a
                LEFT JOIN information_schema.engines b ON a.engine = b.engine
                where a.table_schema = database() and b.engine is null;";


        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            if ($ob->cpt !== "1") {
                return false;
            }
        }


        return true;
    }

    public function isGaleraCluster($param)
    {
        if (!empty($param)) {
            foreach ($param as $elem) {
                if ($elem == "--debug") {
                    $this->debug = true;
                    echo Color::getColoredString("DEBUG activated !", "yellow")."\n";
                }
            }
        }

        $this->view = false;
        $db         = $this->di['db']->sql(DB_DEFAULT);

        $sql = "BEGIN";
        $db->sql_query($sql);

        $sql = "DELETE FROM galera_cluster_node WHERE id_mysql_server IN (SELECT id FROM mysql_server where error= '')";
        $db->sql_query($sql);

        if (Debug::$debug) {
            echo SqlFormatter::format($sql)."\n";
        }

        $sql   = "SELECT * FROM galera_cluster_node";
        $res10 = $db->sql_query($sql);

        $nodes = array();
        while ($ob    = $db->sql_fetch_object($res10)) {
            $nodes[$ob->id_mysql_server] = $ob->id_mysql_server;
        }



        // récupération de tous les status galera available
        $fields = array("wsrep_local_state_comment", "wsrep_cluster_status", "wsrep_cluster_size",
            "wsrep_incoming_addresses");
        $fields = array("wsrep_cluster_status", "wsrep_local_state_comment", "wsrep_incoming_addresses");
        $sql    = $this->buildQuery($fields, "status");
        $res2   = $db->sql_query($sql);

        if (Debug::$debug) {
            echo SqlFormatter::format($sql)."\n";
        }
        while ($ob = $db->sql_fetch_object($res2)) {
            $status[$ob->id]['wsrep_local_state_comment'] = $ob->wsrep_local_state_comment;
            $status[$ob->id]['wsrep_cluster_status']      = $ob->wsrep_cluster_status;
            $status[$ob->id]['wsrep_incoming_addresses']  = $ob->wsrep_incoming_addresses;
        }


        $fields = array("wsrep_cluster_name", "wsrep_provider_options", "wsrep_on",
            "wsrep_sst_method", "wsrep_desync");
        $sql    = $this->buildQuery($fields, "variables");

        if (Debug::$debug) {
            echo SqlFormatter::format($sql)."\n";
        }

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            if ($ob->wsrep_on === "ON") {

                $segment = $this->extract($ob->wsrep_provider_options, "gmcast.segment");

                $sql  = "SELECT * FROM galera_cluster_main WHERE name='".$ob->wsrep_cluster_name."' AND segment ='".$segment."'";
                $res2 = $db->sql_query($sql);
                if (Debug::$debug) {
                    echo SqlFormatter::format($sql)."\n";
                }

                if ($db->sql_num_rows($res2) !== 0) {
                    while ($ob2 = $db->sql_fetch_object($res2)) {
                        $id_galera_cluster_main = $ob2->id;
                    }
                } else {

                    $sql = "INSERT INTO galera_cluster_main SET name='".$ob->wsrep_cluster_name."', segment='".$segment."'";
                    $db->sql_query($sql);
                    if (Debug::$debug) {
                        echo SqlFormatter::format($sql)."\n";
                    }
                    $id_galera_cluster_main = $db->_insert_id();
                }


                if (in_array($ob->id, $nodes)) {

                    $sql = "UPDATE galera_cluster_node SET id_galera_cluster_main =".$id_galera_cluster_main.","
                        ." comment='".$status[$ob->id]['wsrep_local_state_comment']."',"
                        ." sst_method ='".$ob->wsrep_sst_method."', "
                        ." cluster_status ='".$status[$ob->id]['wsrep_cluster_status']."', "
                        ." incoming_addresses ='".$status[$ob->id]['wsrep_incoming_addresses']."', "
                        ." desync  ='".$ob->wsrep_desync."' "
                        ."WHERE id=".$ob->id."";
                    $db->sql_query($sql);
                    if (Debug::$debug) {
                        echo SqlFormatter::format($sql)."\n";
                    }
                } else {

                    $sql = "INSERT INTO galera_cluster_node SET id_galera_cluster_main =".$id_galera_cluster_main.","
                        ." comment='".$status[$ob->id]['wsrep_local_state_comment']."',"
                        ." sst_method ='".$ob->wsrep_sst_method."', "
                        ." cluster_status ='".$status[$ob->id]['wsrep_cluster_status']."', "
                        ." incoming_addresses ='".$status[$ob->id]['wsrep_incoming_addresses']."', "
                        ." desync  ='".$ob->wsrep_desync."', "
                        ." id_mysql_server=".$ob->id."";
                    $db->sql_query($sql);
                    if (Debug::$debug) {
                        echo SqlFormatter::format($sql)."\n";
                    }
                }

                if (Debug::$debug) {
                    echo SqlFormatter::format($sql)."\n";
                }
            }
        }

        $sql = "COMMIT";
        $db->sql_query($sql);
    }

    public function saveVariables($all_variables, $id_mysql_server)
    {
        $default  = $this->di['db']->sql(DB_DEFAULT);
        $all_name = array_keys($all_variables);


        Debug::debug($all_variables);

        $sql = "SELECT * FROM variables_name";

        $index = [];
        $data  = [];

        $res = $default->sql_query($sql);

        while ($ob = $default->sql_fetch_object($res)) {
            $index[]                 = $ob->name;
            $data[$ob->name]['id']   = $ob->id;
            $data[$ob->name]['type'] = strtolower($ob->type);
        }


        Debug::debug($data);


        foreach ($all_variables as $name => $status) {

            $name = strtolower($name);

            if (!in_array($name, $index)) {
                //echo "add ".$name."\n";

                $variables_name['variables_name']['name'] = $name;
                $variables_name['variables_name']['type'] = self::getTypeOfData($status);
                //$status_name['status_name']['value'] = $status;

                $id = $default->sql_save($variables_name);
                if (!$id) {
                    debug($status_name);
                    debug($default->sql_error());

                    throw new Exception('PMACTRL : Impossible to save');
                }
            }
        }
        $this->saveValue($data, $all_variables, $id_mysql_server, 1);
    }

    private function buildQuery($fields, $table = "status")
    {

        $elems = array("int", "double", "text");

        $sqls = [];

        $j     = 0;
        $ofset = count($fields);

        foreach ($elems as $elem) {
            $sql = 'select a.ip, a.port, a.id, a.name,';

            $i   = $j;
            $tmp = [];
            foreach ($fields as $field) {
                $tmp[] = " c$i.value as $field";
                $i++;
            }

            $sql .= implode(",", $tmp);
            $sql .= " from mysql_server a ";
            $sql .= " INNER JOIN ".$table."_max_date b ON a.id = b.id_mysql_server ";

            $tmp = [];
            $i   = $j;
            foreach ($fields as $field) {
                $sql .= " INNER JOIN ".$table."_value_".$elem." c$i ON c$i.id_mysql_server = a.id AND b.date = c$i.date";
                $sql .= " INNER JOIN ".$table."_name d$i ON d$i.id = c$i.id_".$table."_name ";
                $i++;
            }

            $sql .= " WHERE 1 ";
            $tmp = [];
            $i   = $j;
            foreach ($fields as $field) {
                $sql .= " AND d".$i.".name = '".$field."' ";
                $i++;
            }

            $j      = $ofset + $j;
            $sqls[] = $sql;
        }

        $sqlret = "(".implode(") UNION (", $sqls).");";
        return $sqlret;
    }

    /**
     * Slightly modified version of http://www.geekality.net/2011/05/28/php-tail-tackling-large-files/
     * @author Torleif Berger, Lorenzo Stanco
     * @link http://stackoverflow.com/a/15025877/995958
     * @license http://creativecommons.org/licenses/by/3.0/
     */
    function tailCustom($filepath, $lines = 1, $adaptive = true)
    {
        // Open file
        $f      = @fopen($filepath, "rb");
        if ($f === false) return false;
        // Sets buffer size, according to the number of lines to retrieve.
        // This gives a performance boost when reading a few lines from the file.
        if (!$adaptive) $buffer = 4096;
        else $buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096));
        // Jump to last character
        fseek($f, -1, SEEK_END);
        // Read it and adjust line number if necessary
        // (Otherwise the result would be wrong if file doesn't end with a blank line)
        if (fread($f, 1) != "\n") $lines  -= 1;

        // Start reading
        $output = '';
        $chunk  = '';
        // While we would like more
        while (ftell($f) > 0 && $lines >= 0) {
            // Figure out how far back we should jump
            $seek   = min(ftell($f), $buffer);
            // Do the jump (backwards, relative to where we are)
            fseek($f, -$seek, SEEK_CUR);
            // Read a chunk and prepend it to our output
            $output = ($chunk  = fread($f, $seek)).$output;
            // Jump back to where we started reading
            fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
            // Decrease our line counter
            $lines  -= substr_count($chunk, "\n");
        }
        // While we have too many lines
        // (Because of buffer size we might have read too many)
        while ($lines++ < 0) {
            // Find first newline and remove all text before that
            $output = substr($output, strpos($output, "\n") + 1);
        }
        // Close file and return
        fclose($f);
        return trim($output);
    }

    public function testAllSsh($param)
    {
        $this->view = false;


        $id_loop = $param[0];

        Debug::parseDebug($param);

        $this->logger->info(str_repeat("#", 40));


        $db  = $this->di['db']->sql(DB_DEFAULT);
        $sql = "SELECT * FROM mysql_server WHERE is_monitored=1 AND key_private_path != '' and key_private_user != '';";
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

                $this->testSshServer($server['id'], $id_loop, $maxExecutionTime);
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

        $ret = SetTimeLimit::run("Agent", "trySshConnection", array($server_id, $id_loop, $debug), $max_execution_time);

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

            $sql = "UPDATE mysql_server SET ssh_available=0 where id = '".$server_id."'";
            $db->sql_query($sql);

            Debug::debug("Server ID : ".$server_id."(FAILED !)");

            $db->sql_close();

            return false;
        } else {

            $sql = "UPDATE mysql_server SET ssh_available=1 where id = '".$server_id."'";
            $db->sql_query($sql);

            Debug::debug("Server ID : ".$server_id." (answered in time)");
            //echo (Debug::$debug) ? $server['name']." OK \n" : "";
            return true;
        }
    }

    public function extract($wsrep_provider_options, $variable)
    {
        preg_match("/".preg_quote($variable)."\s*=[\s]+([\S]+);/", $wsrep_provider_options, $output_array);

        if (!empty($output_array[1])) {
            return $output_array[1];
        } else {
            return 0;
            //throw new \Exception("Impossible to find : ".$variable." in (".$wsrep_provider_options.")");
        }
    }

    public function trySshConnection($param)
    {
        $this->view = false;
        $id_server  = $param[0];

        Debug::parseDebug($param);

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server where id=".$id_server.";";

        Debug::debug(SqlFormatter::highlight($sql));

        $res = $db->sql_query($sql);

        $login_successfull = true;

        while ($ob = $db->sql_fetch_object($res)) {
            $ssh = new SSH2($ob->ip);
            $ip  = $ob->ip;
            $rsa = new RSA();


            if (!file_exists($ob->key_private_path)) {
                Debug::debug("This file doesn't exist : ".$ob->key_private_path);
                $this->logger->error("This file doesn't exist : ".$ob->key_private_path);
            } else {

                Debug::debug("This file exist : ".$ob->key_private_path);
            }

            $privatekey = file_get_contents($ob->key_private_path);

            if ($rsa->loadKey($privatekey) === false) {
                $login_successfull = false;
                Debug::debug("private key loading failed!");
            }

            if (!$ssh->login($ob->key_private_user, $rsa)) {
                Debug::debug("Login Failed");
                $login_successfull = false;
            }
        }


        $msg = ($login_successfull) ? "Successfull" : "Failed";

        Debug::debug("Connection to server:".$id_server." (".$ip.":22) : ".$msg);

        $this->logger->info("Connection to server (".$ip.":22) : ".$msg);

        $sql = "UPDATE mysql_server SET ssh_available = '".((int) $login_successfull)."' where id=".$id_server.";";

        Debug::debug(SqlFormatter::highlight($sql));
        $db->sql_query($sql);
        $db->sql_close();
    }

    public function hardware($ssh, $id_mysql_server)
    {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $memory      = $ssh->exec("grep MemTotal /proc/meminfo | awk '{print $2}'") or die("error");
        $nb_cpu      = $ssh->exec("cat /proc/cpuinfo | grep processor | wc -l");
        $brut_memory = $ssh->exec("cat /proc/meminfo | grep MemTotal");
        preg_match("/[0-9]+/", $brut_memory, $memory);

        $mem    = $memory[0];
        $memory = sprintf('%.2f', $memory[0] / 1024 / 1024)." Go";

        $freq_brut = $ssh->exec("cat /proc/cpuinfo | grep 'cpu MHz'");
        preg_match("/[0-9]+\.[0-9]+/", $freq_brut, $freq);
        $frequency = sprintf('%.2f', ($freq[0] / 1000))." GHz";


        $os          = trim($ssh->exec("lsb_release -ds 2> /dev/null"));
        $distributor = trim($ssh->exec("lsb_release -si 2> /dev/null"));

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

        $product_name = $ssh->exec("dmidecode -s system-product-name 2> /dev/null");
        $arch         = $ssh->exec("uname -m");
        $kernel       = $ssh->exec("uname -r");
        $hostname     = $ssh->exec("hostname");


        $swapiness = $ssh->exec("cat /proc/sys/vm/swappiness");

        /*
          $system = $ssh->exec("uptime");// get the uptime stats
          $uptime = explode(" ", $system); // break up the stats into an array
          $up_days = $uptime[4]; // grab the days from the array
          $hours = explode(":", $uptime[7]); // split up the hour:min in the stats

          $up_hours = $hours[0]; // grab the hours
          $mins = $hours[1]; // get the mins
          $up_mins = str_replace(",", "", $mins); // strip the comma from the mins

          echo "The server has been up for " . $up_days . " days, " . $up_hours . " hours, and " . $up_mins . " minutes.";
         */

        $sql = "UPDATE mysql_server SET operating_system='".$db->sql_real_escape_string($os)."',
                   distributor='".trim($distributor)."',
                   processor='".trim($nb_cpu)."',
                   cpu_mhz='".trim($freq[0])."',
                   product_name='".trim($product_name)."',
                   arch='".trim($arch)."',
                   kernel='".trim($kernel)."',
                   hostname='".trim($hostname)."',
                   memory_kb='".trim($mem)."',
                   swappiness='".trim($swapiness)."'
                   WHERE id=".$id_mysql_server."";

        Debug::debug(SqlFormatter::highlight($sql));


        $db->sql_query($sql);
    }

    private function addIpVirtuel($ssh, $id_mysql_server)
    {

        $db = $this->di['db']->sql(DB_DEFAULT);


        // bug il faudrait extraire les ip de la boucle local qui ne sont pas 127.0.0.1
        $cmd = "ifconfig | grep -Eo 'inet (a[d]{1,2}r:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1'";

        $cmd = "ip addr | grep 'state UP' -A2 | awk '{print $2}' | cut -f1 -d'/' | grep -Eo '([0-9]*\.){3}[0-9]*'";
        $ip  = $ssh->exec($cmd);
        Debug::debug($cmd);

        $ips_actual = explode("\n", trim($ip));

        $sql = "SELECT * FROM `virtual_ip` WHERE id_mysql_server=".$id_mysql_server.";";
        Debug::debug(SqlFormatter::highlight($sql));
        $res = $db->sql_query($sql);

        $sqls = array();
        while ($ob   = $db->sql_fetch_object($res)) {
            if (in_array($ob->ip, $ips_actual)) {
                $revert = array_flip($ips_actual);

                unset($ips_actual[$revert[$ob->ip]]);
            } else {
                $sql = "DELETE FROM `virtual_ip` WHERE `ip` IN ('".implode("','", $ips_actual)."') AND `id_mysql_server`= ".$id_mysql_server."";
                Debug::debug(SqlFormatter::highlight($sql));
                $db->sql_query($sql);
            }
        }

        if (count($ips_actual) > 0) {
            $vals = array();
            foreach ($ips_actual as $ip) {
                $vals[] = "(NULL,'".$id_mysql_server."', '".$ip."', NULL)";
            }


            $sql = "INSERT INTO `virtual_ip` VALUES ".implode(",", $vals).";";
            Debug::debug(SqlFormatter::highlight($sql));
            $db->sql_query($sql);
        }
    }

    public function processlist()
    {
        
    }

    public function check_daemon()
    {

        /*
         *  $this->shell_cmd($stdio, "echo 'xfghxfgh:452452:::xgfhxfgh,fdgwdfg,,:/home/sdffdf:/bin/bash' > /tmp/users");
          $this->shell_cmd($stdio, "newusers /tmp/users");
          $this->shell_cmd($stdio, "rm /tmp/users");
         * useradd -g www-data pmacontrol
         *
         */

        $this->view = false;

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT id, name, pid, log_file FROM daemon_main WHERE pid != 0";


        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {


            if (!$this->isRunningPid($ob->pid)) {

                $php = explode(" ", shell_exec("whereis php"))[1];


                $cmd = $php." ".GLIAL_INDEX." Agent launch ".$ob->id." >> ".TMP.$ob->log_file." & echo $!";
                $pid = shell_exec($cmd);


                $sql = "UPDATE daemon_main SET pid=".$pid." WHERE id=".$ob->id;
                $db->sql_query($sql);


                $this->logger->warning(Color::getColoredString('Daemon '.$ob->name
                        .' with pid ('.$ob->pid.') was down, crontab restart it with pid : '.$pid, "black", "yellow"));
            }
        }
    }
}
/*
 *
 *
 *
 *
 *

03 --- EF



#!/bin/bash
#this script used monitor mysql network traffic.echo sql
tcpdump -i lo -s 0 -l -w - dst port 3306 | strings | perl -e '
while(<>) { chomp; next if /^[^ ]+[ ]*$/;
    if(/^(SELECT|UPDATE|DELETE|INSERT|SET|COMMIT|ROLLBACK|CREATE|DROP|ALTER|CALL)/i)
    {
        if (defined $q) { print "$q\n"; }
        $q=$_;
    } else {
        $_ =~ s/^[ \t]+//; $q.=" $_";
    }
}'


tcpdump -i any -XX dst port 3306

tshark -i any -T fields -Y mysql.query -e mysql.query

 *
 *
 *
 *
 */