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
use App\Library\Mysql;
use \App\Library\System;

class Agent extends Controller
{

    use \App\Library\Decoupage;
    var $debug    = false;
    var $url      = "Daemon/index/";
    var $log_file = TMP."log/glial.log";
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

        Debug::parseDebug($param);


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


            if (!IS_CLI) {
                header("location: ".LINK.$this->url);
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


            $cmd = $php." ".GLIAL_INDEX." Agent launch ".$id_daemon." ".$debug." >> ".$this->log_file." & echo $!";
            Debug::debug($cmd);
            $pid = shell_exec($cmd);

            $this->logger->info("CMD : ".$cmd);


            $this->logger->info(Color::getColoredString('Started daemon with pid : '.$pid, "grey", "green"));

            $sql   = "UPDATE daemon_main SET pid ='".$pid."' WHERE id = '".$id_daemon."'";
            $db->sql_query($sql);
            $msg   = I18n::getTranslation(__("The daemon (id=".$id_daemon.") successfully started with")." pid : ".$pid);
            $title = I18n::getTranslation(__("Success"));
            set_flash("success", $title, $msg);
        } else {
            $this->logger->info(Color::getColoredString('Impossible to start daemon (Already running)', "yellow"));
            $msg   = I18n::getTranslation(__("Impossible to launch the daemon ")."(".__("Already running !").")");
            $title = I18n::getTranslation(__("Error"));
            set_flash("caution", $title, $msg);
        }


        if (!IS_CLI) {
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

        $this->logger->emergency($sql);

        if ($db->sql_num_rows($res) !== 1) {
            $msg   = I18n::getTranslation(__("Impossible to find the daemon (id=".$id_daemon.") with the id : ")."'".$id_daemon."'");
            $title = I18n::getTranslation(__("Error"));
            set_flash("error", $title, $msg);
            header("location: ".LINK.$this->url);
            exit;
        }

        $ob = $db->sql_fetch_object($res);

        if (System::isRunningPid($ob->pid)) {
            $msg   = I18n::getTranslation(__("The daemon (id=".$id_daemon.") with pid : '".$ob->pid."' successfully stopped "));
            $title = I18n::getTranslation(__("Success"));
            set_flash("success", $title, $msg);

            $cmd = "kill ".$ob->pid;
            shell_exec($cmd);
            //shell_exec("echo '[" . date("Y-m-d H:i:s") . "] DAEMON STOPPED !' >> " . $ob->log_file);

            $sql = "UPDATE daemon_main SET pid ='0' WHERE id = '".$id_daemon."'";
            $db->sql_query($sql);



            $this->logger->info(Color::getColoredString('Stopped daemon (id='.$id_daemon.') with the pid : '.$ob->pid, "grey", "red"));
        } else {

            if (!empty($pid)) {
                $this->logger->info(Color::getColoredString('Impossible to find the daemon (id='.$id_daemon.') with the pid : '.$pid, "yellow"));
            }

            $msg   = I18n::getTranslation(__("Impossible to find the daemon (id=".$id_daemon.") with the pid : ")."'".$ob->pid."'");
            $title = I18n::getTranslation(__("Daemon (id=".$id_daemon.") was already stopped or in error"));
            set_flash("caution", $title, $msg);
        }

        usleep(5000);


        if (!System::isRunningPid($ob->pid)) {
            
        } else {

            //on double UPDATE dans le cas le contrab passerait dans l'interval du sleep
            // (ce qui crée un process zombie dont on perdrait le PID vis a vis de pmacontrol)
            // impossible a killed depuis l'IHM
            $sql = "UPDATE daemon_main SET pid =".$ob->pid." WHERE id = '".$id_daemon."'";
            $db->sql_query($sql);

            $this->logger->info(Color::getColoredString('Impossible to stop daemon (id='.$id_daemon.') with pid : '.$pid, "grey", "red"));
            //throw new Exception('PMACTRL-876 : Impossible to stop daemon (id=' . $id_daemon . ') with pid : "' . $ob->pid . '"');
        }


        if (!IS_CLI) {
            header("location: ".LINK.$this->url);
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

    public function launch($params)
    {

        Debug::parseDebug($params);
        $id = $params[0];

        $debug = "";
        if (Debug::$debug === true) {
            $debug = "--debug";
        }

        if ($id == "6") {
            // to prevent inactive daemon or crontab failure (to move to right place
            $php = explode(" ", shell_exec("whereis php"))[1];
            $cmd = $php." ".GLIAL_INDEX." control service";

            $this->logger->info('Cmd : '.$cmd);
            Debug::debug($cmd);
            shell_exec($cmd);
        }

        $id_loop = 0;
        while (1) {

            $id_loop++;

            $db  = $this->di['db']->sql(DB_DEFAULT);
            $sql = "SELECT * FROM daemon_main where id=".$id;
            $res = $db->sql_query($sql);

            while ($ob = $db->sql_fetch_object($res)) {


                $php = explode(" ", shell_exec("whereis php"))[1];
                //$cmd = $php . " " . GLIAL_INDEX . " " . $ob->class . " " . $ob->method . " " . $ob->params . " " . $debug . " >> " . $this->log_file . " & echo $!";
                $cmd = $php." ".GLIAL_INDEX." ".$ob->class." ".$ob->method." ".$ob->id." ".$ob->params." loop:".$id_loop." ".$debug." 2>&1 >> ".$this->log_file."";

                $this->logger->info('Cmd loop : '.$cmd);

                Debug::debug($cmd);
                shell_exec($cmd);

                $refresh_time = $ob->refresh_time;
            }

            Debug::debug("refresh time : ".$refresh_time);


            // in case of mysql gone away, like this daemon restart when mysql is back
            $this->di['db']->sql(DB_DEFAULT)->sql_close();

            if (empty($refresh_time)) {
                $refresh_time = 60;
            }

            sleep($refresh_time);

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


        Mysql::addMaxDate($this->di['db']->sql(DB_DEFAULT));
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
    /*

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
      } */

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
    /*
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
     */

    public function check_daemon()
    {

        $this->view = false;
        $db         = $this->di['db']->sql(DB_DEFAULT);
        $sql        = "SELECT id, name, pid, log_file FROM daemon_main WHERE pid != 0";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            if (!System::isRunningPid($ob->pid)) {

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
    /*
     * 
     * 
     * Check queue
     */

    public function check_queue($param)
    {
        $this->view = false;
        $db         = $this->di['db']->sql(DB_DEFAULT);

        Debug::parseDebug($param);

        $id_daemon = $param[0];


        $sql = "SELECT * FROM daemon_main WHERE queue_key != 0 and id = ".$id_daemon;
        $res = $db->sql_query($sql);


        Debug::sql($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $queue = msg_get_queue($ob->queue_key);

            $last_value = 0;

            while (true) {

                $msg_qnum = msg_stat_queue($queue)['msg_qnum'];

                if ($msg_qnum == 0 && $last_value > 0) {


                    $time = microtime(true) - $time_start;
                    Debug::debug("All tests termined : ".round($time, 2)." sec");
                }


                if ($msg_qnum == 0 && $last_value == 0) {
                    $time_start = microtime(true);
                }

                if ($last_value !== $msg_qnum) {
                    echo "[".date("Y-m-d H:i:s")."] Nombre de message de la file d'attente : ";
                    echo Color::getColoredString($msg_qnum, "grey", "green");
                    echo "\n";
                }

                $last_value = $msg_qnum;

                usleep(50);
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
 */