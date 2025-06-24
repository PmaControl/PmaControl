<?php

namespace App\Controller;

use App\Library\EngineV4;

use Exception;
use \Glial\Cli\Color;
use \Glial\Security\Crypt\Crypt;
use \Glial\I18n\I18n;
use \Glial\Synapse\FactoryController;
use \Glial\Synapse\Controller;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;
use \App\Library\Debug;
use \App\Library\Mysql;
use \App\Library\Microsecond;
use \App\Library\System;
use \Glial\Sgbd\Sgbd;

class Agent extends Controller {

    use \App\Library\Decoupage;

    var $debug = false;
    var $url = "Daemon/index/";
    var $logger;
    var $log_file = LOG_FILE;
    var $loop = 0;
    
    /*
     * (PmaControl 0.8)<br/>
     * @author Aurélien LEQUOY, <aurelien.lequoy@esysteme.com>
     * @return boolean Success
     * @package Controller
     * @since 0.8 First time this was introduced.
     * @description log error & start / stop daemon
     * @access public
     */

    public function before($param) {
        $logger = new Logger("Agent");
        $handler = new StreamHandler(LOG_FILE, Logger::WARNING);
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

    public function start($param) {
        if (empty($param[0])) {
            Throw new \Exception("No idea set for this Daemon", 80);
        }

        Debug::parseDebug($param);

        $id_daemon = $param[0];
        $db = Sgbd::sql(DB_DEFAULT);
        $this->view = false;
        $this->layout_name = false;

        $sql = "SELECT * FROM daemon_main where id ='" . $id_daemon . "'";
        $res = $db->sql_query($sql);

        if ($db->sql_num_rows($res) !== 1) {
            $msg = I18n::getTranslation(__("Impossible to find the daemon with the id : ") . "'" . $id_daemon . "'");
            $title = I18n::getTranslation(__("Error"));
            set_flash("error", $title, $msg);


            if (!IS_CLI) {
                header("location: " . LINK . $this->url);
            }

            exit;
        }

        $ob = $db->sql_fetch_object($res);

        if ($ob->pid === "0") {
            $php = explode(" ", shell_exec("whereis php"))[1];
            //todo add error flux in the log

            $debug = "";
            if (Debug::$debug === true) {
                $debug = "--debug";
            }

            $cmd = $php . " " . GLIAL_INDEX . " Agent launch " . $id_daemon . " " . $debug . " >> " . $this->log_file . " & echo $!";
            Debug::debug($cmd);
            $this->logger->debug("$cmd");
            $pid = trim(shell_exec($cmd));

            $this->logger->debug("CMD : " . $cmd);
            $this->logger->info('Started daemon with pid : ' . $pid);

            $sql = "UPDATE daemon_main SET pid ='" . $pid . "' WHERE id = " . $id_daemon . ";";
            $db->sql_query($sql);
            $msg = I18n::getTranslation(__("The daemon ")."(id=" . $id_daemon . ") ".__("successfully started with pid:") . " " . $pid);
            $title = I18n::getTranslation(__("Success"));
            set_flash("success", $title, $msg);
        } else {
            $this->logger->warning('Impossible to start daemon (Already running)');
            $msg = I18n::getTranslation(__("Impossible to launch the daemon ") . "(" . __("Already running !") . ")");
            $title = I18n::getTranslation(__("Error"));
            set_flash("caution", $title, $msg);
        }


        if (!IS_CLI) {
            header("location: " . LINK . $this->url);
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

    function stop($param) {
        $id_daemon = $param[0];


        $db = Sgbd::sql(DB_DEFAULT);
        $this->view = false;
        $this->layout_name = false;

        $sql = "SELECT * FROM daemon_main where id ='" . $id_daemon . "'";
        $res = $db->sql_query($sql);

        $this->logger->notice($sql);

        if ($db->sql_num_rows($res) !== 1) {
            $msg = I18n::getTranslation(__("Impossible to find the daemon")." (id=" . $id_daemon . ")");
            
            $title = I18n::getTranslation(__("Error"));
            set_flash("error", $title, $msg);
            header("location: " . LINK . $this->url);
            exit;
        }

        $ob = $db->sql_fetch_object($res);

        if (System::isRunningPid($ob->pid)) {
            $msg = I18n::getTranslation(__("The daemon (id=" . $id_daemon . ") with pid : '" . $ob->pid . "' successfully stopped "));
            $title = I18n::getTranslation(__("Success"));
            set_flash("success", $title, $msg);

            $cmd = "kill " . $ob->pid;
            shell_exec($cmd);
            //shell_exec("echo '[" . date("Y-m-d H:i:s") . "] DAEMON STOPPED !' >> " . $ob->log_file);

            $sql = "UPDATE daemon_main SET pid ='0' WHERE id = '" . $id_daemon . "'";
            $db->sql_query($sql);

            $this->logger->info('Stopped daemon (id=' . $id_daemon . ') with the pid : ' . $ob->pid);
        } else {

            if (!empty($pid)) {
                $this->logger->info('Impossible to find the daemon (id=' . $id_daemon . ') with the pid : ' . $pid);
            }

            $sql = "UPDATE daemon_main SET pid ='0' WHERE id = '" . $id_daemon . "'";
            $db->sql_query($sql);

            $msg = I18n::getTranslation(__("Impossible to find the daemon (id=" . $id_daemon . ") with the pid : ") . "'" . $ob->pid . "'");
            $title = I18n::getTranslation(__("Daemon (id=" . $id_daemon . ") was already stopped or in error"));
            set_flash("caution", $title, $msg);
        }

        usleep(5000);


        if (!System::isRunningPid($ob->pid)) {
            
        } else {

            //on double UPDATE dans le cas le contrab passerait dans l'interval du sleep
            // (ce qui crée un process zombie dont on perdrait le PID vis a vis de pmacontrol)
            // impossible a killed depuis l'IHM
            $sql = "UPDATE daemon_main SET pid =" . $ob->pid . " WHERE id = '" . $id_daemon . "'";
            $db->sql_query($sql);

            $this->logger->warning('Impossible to stop daemon (id=' . $id_daemon . ') with pid : ' . $pid);
            //throw new \Exception('PMACTRL-876 : Impossible to stop daemon (id=' . $id_daemon . ') with pid : "' . $ob->pid . '"');
        }


        if (!IS_CLI) {
            header("location: " . LINK . $this->url);
        }
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

    public function launch($params) {

        Debug::parseDebug($params);
        $id = $params[0];

        $debug = "";
        if (Debug::$debug === true) {
            $debug = "--debug";
        }

        if ($id == "11") {
            // to prevent inactive daemon or crontab failure (to move to right place)
            $php = explode(" ", shell_exec("whereis php"))[1];
            $cmd = $php . " " . GLIAL_INDEX . " control service";

            $this->logger->info('Cmd : ' . $cmd);
            Debug::debug($cmd);
            shell_exec($cmd);
        }

        $interval = 1;
        $nextRuntime = microtime(true) + $interval;

        $id_loop = 0;
        while (true) {
            $id_loop++;



            $time_start = microtime(true);



            $db = Sgbd::sql(DB_DEFAULT);
            $sql = "SELECT * FROM daemon_main where id=" . $id;
            $res = $db->sql_query($sql);

            while ($ob = $db->sql_fetch_object($res)) {

                $php = explode(" ", shell_exec("whereis php"))[1];
                $cmd = $php . " " . GLIAL_INDEX . " " . $ob->class . " " . $ob->method . " " . $ob->params . " loop:" . $id_loop . " " . $debug . " 2>&1 >> " . $this->log_file . " & echo $!";

                //FactoryController::addNode($ob->class, $ob->method, explode(',',$ob->params));
                //$pid=43563456375635673;

                $pid = shell_exec($cmd);
                $this->logger->debug("[".Microsecond::date()."] {pid:".trim($pid)."} " . $ob->class . "/". $ob->method . ":" . $ob->id . " " . $ob->params . "\t[loop:" . $id_loop."]" );

                $refresh_time = (int) $ob->refresh_time;
            }

            // in case of mysql gone away, like this daemon restart when mysql is back
            Sgbd::sql(DB_DEFAULT)->sql_close();
            
            $timeToWait = $nextRuntime - microtime(true);


            $time_end = microtime(true);
            $time = $time_end - $time_start;

            $this->logger->debug("[Daemon : $id] made run in $time seconds");

            if ($time > 1 && $id == 7)
            {
                $this->logger->warning("[Daemon : $id] made run in $time seconds, mabe increase value between 2 run of fetching data, if happen too often");
                $timeToWait = 0;
            }


            if ($timeToWait > 0) {
                usleep((int)($timeToWait * 1000000));  // Attendre le reste de la seconde en microsecondes
            }
            $nextRuntime += $refresh_time;

            $refresh_time--;



            //to prevent mysql gone away or everything else in long process
            $db->sql_close();
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

    public function updateServerList() {
        $this->view = false;
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM `mysql_server`";
        $servers_mysql = $db->sql_fetch_yield($sql);
        $all_server = array();
        foreach ($servers_mysql as $mysql) {
            $all_server[$mysql['name']] = $mysql;
        }
        Crypt::$key = CRYPT_KEY;

        $all = array();
        foreach (Sgbd::getAll() as $server) {

            $all[] = $server;
            $info_server = Sgbd::getParam($server);
            $data = array();

            if (!empty($all_server[$server])) {
                $data['mysql_server']['id'] = $all_server[$server]['id'];

                unset($all_server[$server]);
            } else {
                echo "Add : " . $server . " to monitoring\n";

                //to update
                $data['mysql_server']['id_client'] = 1;
                $data['mysql_server']['id_environment'] = 1;
            }
            
            
            //to do check if proxysql / maxscale
            $data['mysql_server']['id_mysql_type'] = 1; 

            $data['mysql_server']['name'] = $server;
            $data['mysql_server']['display_name'] = $server;
            $data['mysql_server']['ip'] = $info_server['hostname'];
            $data['mysql_server']['login'] = $info_server['user'];

            if (!empty($info_server['crypted']) && $info_server['crypted'] == 1) {
                $passwd = $info_server['password'];
                $data['mysql_server']['is_password_crypted'] = 1;
            } else {
                $passwd = Crypt::encrypt($info_server['password']);
                $data['mysql_server']['is_password_crypted'] = 0;
            }

            $data['mysql_server']['passwd'] = $passwd;
            $data['mysql_server']['port'] = empty($info_server['port']) ? 3306 : $info_server['port'];
            $data['mysql_server']['date_refresh'] = date('Y-m-d H:i:s');
            $data['mysql_server']['database'] = $info_server['database'];

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
                //throw new Exception(''. $db->sql_error());
            } else {

                //$this->OnAddServer(array($id_mysql_server));
                //echo $data['mysql_server']['name'] . PHP_EOL;
            }
        }

        foreach ($all_server as $to_delete) {
            $sql = "DELETE FROM `mysql_server` WHERE id=" . $to_delete['id'] . "";
            $db->sql_query($sql);

            echo "[Warning] Removed : " . $to_delete['name'] . " from monitoring\n";
        }

        Mysql::addMaxDate();
    }



    public function logs($param) {
        $db = Sgbd::sql(DB_DEFAULT);


        $id_daemon = $param[0] ?? "SELECT min(id) FROM `daemon_main`;"; //7 => integrate/integrateAll

        // update param for the daemon
        $this->di['js']->code_javascript('$("#data_log").css("max-height", $(window).height()-200);');
        $this->di['js']->code_javascript("var objDiv = document.getElementById('data_log'); objDiv.scrollTop = objDiv.scrollHeight;");
        


        $data = array();

        $sql = "SELECT * FROM `daemon_main` WHERE id in ($id_daemon)";
        $res = $db->sql_query($sql);
        while($ob = $db->sql_fetch_object($res))
        {

            $data['log_file'] = TMP ."log/glial.log";

            //debug($data);

            $data['log'] = __("Log file doens't exist yet !");
    
            if (file_exists($data['log_file'])) {
    
                //$ob->log_file = escapeshellarg($ob->log_file); // for the security concious (should be everyone!)
                //$data['log'] = `tail -n 10000 $ob->log_file`;
                //full php implementation
                
            
                
                $data['log'] = $this->tailCustom($data['log_file'], 100);
            }
    

            /*
            $_GET['daemon_main']['thread_concurency'] = $ob->thread_concurency;
    


            $data['thread'] = array();
            for ($i = 1; $i <= 128; $i++) {
                $tmp = [];
    
                $tmp['id'] = $i;
                $tmp['libelle'] = $i;
    
                $data['thread_concurency'][] = $tmp;
            }
    
            $_GET['daemon_main']['refresh_time'] = $ob->refresh_time;
    
            $data['thread'] = array();
            for ($i = 1; $i <= 60; $i++) {
                $tmp = [];
    
                $tmp['id'] = $i;
                $tmp['libelle'] = $i;
    
                $data['refresh_time'][] = $tmp;
            }
    
            $_GET['daemon_main']['max_delay'] = $ob->max_delay;
    
            $data['thread'] = array();
            for ($i = 1; $i <= 60; $i++) {
                $tmp = [];
    
                $tmp['id'] = $i;
                $tmp['libelle'] = $i;
    
                $data['max_delay'][] = $tmp;
            }
            /******/

            //debug($data);
            //$data[''] = ;


        }

        

        $this->set('data', $data);
    }




    /**
     * Slightly modified version of http://www.geekality.net/2011/05/28/php-tail-tackling-large-files/
     * @author Torleif Berger, Lorenzo Stanco
     * @link http://stackoverflow.com/a/15025877/995958
     * @license http://creativecommons.org/licenses/by/3.0/
     */
    function tailCustom($filepath, $lines = 1, $adaptive = true) {
        // Open file
        $f = @fopen($filepath, "rb");
        if ($f === false)
            return false;
        // Sets buffer size, according to the number of lines to retrieve.
        // This gives a performance boost when reading a few lines from the file.
        if (!$adaptive)
            $buffer = 4096;
        else
            $buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096));
        // Jump to last character
        fseek($f, -1, SEEK_END);
        // Read it and adjust line number if necessary
        // (Otherwise the result would be wrong if file doesn't end with a blank line)
        if (fread($f, 1) != "\n")
            $lines -= 1;

        // Start reading
        $output = '';
        $chunk = '';
        // While we would like more
        while (ftell($f) > 0 && $lines >= 0) {
            // Figure out how far back we should jump
            $seek = min(ftell($f), $buffer);
            // Do the jump (backwards, relative to where we are)
            fseek($f, -$seek, SEEK_CUR);
            // Read a chunk and prepend it to our output
            $output = ($chunk = fread($f, $seek)) . $output;
            // Jump back to where we started reading
            fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
            // Decrease our line counter
            $lines -= substr_count($chunk, "\n");
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

    public function check_daemon() {

        $this->view = false;
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT id, name, pid FROM daemon_main WHERE pid != 0";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            if (!System::isRunningPid($ob->pid)) {

                $php = explode(" ", shell_exec("whereis php"))[1];

                $cmd = $php . " " . GLIAL_INDEX . " Agent launch " . $ob->id . " >> " . TMP . "worker.log" . " & echo $!";
                $pid = shell_exec($cmd);

                $sql = "UPDATE daemon_main SET pid=" . $pid . " WHERE id=" . $ob->id;
                $db->sql_query($sql);

                $this->logger->warning(Color::getColoredString('Daemon ' . $ob->name
                                . ' with pid (' . $ob->pid . ') was down, crontab restart it with pid : ' . $pid, "black", "yellow"));


                // we should delete file pid there
            }
        }

    }

    /*
     * 
     * 
     * Check queue
     */

    public function check_queue($param) {
        $this->view = false;
        $db = Sgbd::sql(DB_DEFAULT);

        Debug::parseDebug($param);

        $queue = intval($param[0]);

        Debug::debug($queue, "Numéro de la queue");


        $queue = msg_get_queue($queue);

        $last_value = 0;

        while (true) {

            $msg_qnum = msg_stat_queue($queue)['msg_qnum'];

            if ($msg_qnum == 0 && $last_value > 0) {


                $time = microtime(true) - $time_start;
                Debug::debug("All tests termined : " . round($time, 2) . " sec");
            }


            if ($msg_qnum == 0 && $last_value == 0) {
                $time_start = microtime(true);
            }

            if ($last_value !== $msg_qnum) {
                echo "[" . date("Y-m-d H:i:s") . "] Nombre de message de la file d'attente : ";
                echo Color::getColoredString($msg_qnum, "grey", "green");
                echo "\n";
            }

            $last_value = $msg_qnum;

            usleep(50);
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


tshark -i any -d tcp.port==3306,mysql -T fields -Y mysql.query -e mysql.query 'port 3306'

tshark -i any -T fields -Y mysql.query -e mysql.query
tshark -i any -d tcp.port==3306,mysql  'port 3306'



 *
 */