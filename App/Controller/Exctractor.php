<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Shell\Color;
use \App\Library\Debug;

//use \App\Library\System;

class Exctractor extends Controller {

    var $debug = false;
    var $url = "Daemon/index/";
    var $log_file = TMP . "log/daemon.log";
    var $logger;
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
        $logger = new Logger('Daemon');
        $file_log = $this->log_file;
        $handler = new StreamHandler($file_log, Logger::INFO);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $logger->pushHandler($handler);
        $this->logger = $logger;
    }

    public function refreshDataSsh($param) {
        Debug::parseDebug($param);

        $this->view = false;
        $db = $this->di['db']->sql(DB_DEFAULT);
        $sql = "SELECT * FROM `mysql_server` WHERE  ssh_available =1 AND is_monitored=1";
        $res = $db->sql_query($sql);

        Debug::debug($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            $ssh = new SSH2($ob->ip);
            $rsa = new RSA();

            Debug::debug($ob->key_private_path);

            $privatekey = file_get_contents($ob->key_private_path);

            if ($rsa->loadKey($privatekey) === false) {
                exit("private key loading failed!");
            }

            Debug::debug('Server : ' . $ob->ip . " : " . $ob->key_private_user . " " . $ob->key_private_path);

            if (!$ssh->login($ob->key_private_user, $rsa)) {
                echo "Login Failed\n";
                continue;
            }

            Debug::debug("SSH OK;");

            $this->hardware($ssh, $ob->id);
            $this->addIpVirtuel($ssh, $ob->id);
        }
    }

    public function testAllSsh($param) {
        $this->view = false;


        $id_loop = $param[0];

        Debug::parseDebug($param);

        $this->logger->info(str_repeat("#", 40));


        $db = $this->di['db']->sql(DB_DEFAULT);
        $sql = "SELECT * FROM mysql_server WHERE is_monitored=1 AND key_private_path != '' and key_private_user != '';";
        Debug::debug(SqlFormatter::highlight($sql));
        $res = $db->sql_query($sql);

        $server_list = array();
        while ($ob = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $server_list[] = $ob;
        }

        $sql = "SELECT * FROM daemon_main where id=4;";
        $res = $db->sql_query($sql);

        Debug::debug(SqlFormatter::highlight($sql));

        while ($ob = $db->sql_fetch_object($res)) {
            $maxThreads = $ob->thread_concurency; // check MySQL server x by x
            $maxExecutionTime = $ob->max_delay;
        }

        //to prevent any trouble with fork
        $db->sql_close();

        //$maxThreads = \Glial\System\Cpu::getCpuCores();

        $openThreads = 0;
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

            $pid = pcntl_fork();
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
                echo "[" . date('Y-m-d H:i:s') . "]" . " All tests termined\n";
            }
        } else {
            exit;
        }
    }

    public function testSshServer($server_id, $id_loop, $max_execution_time = 20) {
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
                $ret['stdout'] = "[" . date("Y-m-d H:i:s") . "]" . " Server Ssh didn't answer in time (delay max : " . $max_execution_time . " seconds)";

                Debug::debug($ret['stdout']);
            }

            //echo $sql . "\n";

            $sql = "UPDATE mysql_server SET ssh_available=0 where id = '" . $server_id . "'";
            $db->sql_query($sql);

            Debug::debug("Server ID : " . $server_id . "(FAILED !)");

            $db->sql_close();

            return false;
        } else {

            $sql = "UPDATE mysql_server SET ssh_available=1 where id = '" . $server_id . "'";
            $db->sql_query($sql);

            Debug::debug("Server ID : " . $server_id . " (answered in time)");
            //echo (Debug::$debug) ? $server['name']." OK \n" : "";
            return true;
        }
    }

    public function trySshConnection($param) {
        $this->view = false;
        $id_server = $param[0];

        Debug::parseDebug($param);

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server where id=" . $id_server . ";";

        Debug::debug(SqlFormatter::highlight($sql));

        $res = $db->sql_query($sql);

        $login_successfull = true;

        while ($ob = $db->sql_fetch_object($res)) {
            $ssh = new SSH2($ob->ip);
            $ip = $ob->ip;
            $rsa = new RSA();


            if (!file_exists($ob->key_private_path)) {
                Debug::debug("This file doesn't exist : " . $ob->key_private_path);
                $this->logger->error("This file doesn't exist : " . $ob->key_private_path);
            } else {

                Debug::debug("This file exist : " . $ob->key_private_path);
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

        Debug::debug("Connection to server:" . $id_server . " (" . $ip . ":22) : " . $msg);

        $this->logger->info("Connection to server (" . $ip . ":22) : " . $msg);

        $sql = "UPDATE mysql_server SET ssh_available = '" . ((int) $login_successfull) . "' where id=" . $id_server . ";";

        Debug::debug(SqlFormatter::highlight($sql));

        $db->sql_query($sql);
        $db->sql_close();
    }

    public function hardware($ssh, $id_mysql_server) {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $memory = $ssh->exec("grep MemTotal /proc/meminfo | awk '{print $2}'") or die("error");
        $nb_cpu = $ssh->exec("cat /proc/cpuinfo | grep processor | wc -l");
        $brut_memory = $ssh->exec("cat /proc/meminfo | grep MemTotal");
        preg_match("/[0-9]+/", $brut_memory, $memory);

        $mem = $memory[0];
        $memory = sprintf('%.2f', $memory[0] / 1024 / 1024) . " Go";

        $freq_brut = $ssh->exec("cat /proc/cpuinfo | grep 'cpu MHz'");
        preg_match("/[0-9]+\.[0-9]+/", $freq_brut, $freq);
        $frequency = sprintf('%.2f', ($freq[0] / 1000)) . " GHz";


        $os = trim($ssh->exec("lsb_release -ds 2> /dev/null"));
        $distributor = trim($ssh->exec("lsb_release -si 2> /dev/null"));

        if ($distributor === "RedHatEnterpriseServer") {
            $distributor = "RedHat";
        }


        if (empty($os)) {
            $os = trim($ssh->exec("cat /etc/centos-release 2> /dev/null"));
            $distributor = trim("Centos");
        }

        if (empty($os)) {
            $version = trim($ssh->exec("cat /etc/debian_version 2> /dev/null"));
//            1943, 1 90%

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

                $os = trim("Debian GNU/Linux " . $version . " (" . $codename . ")");
            }
        }

        $product_name = $ssh->exec("dmidecode -s system-product-name 2> /dev/null");
        $arch = $ssh->exec("uname -m");
        $kernel = $ssh->exec("uname -r");
        $hostname = $ssh->exec("hostname");


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

        $sql = "UPDATE mysql_server SET operating_system='" . $db->sql_real_escape_string($os) . "',
                   distributor='" . trim($distributor) . "',
                   processor='" . trim($nb_cpu) . "',
                   cpu_mhz='" . trim($freq[0]) . "',
                   product_name='" . trim($product_name) . "',
                   arch='" . trim($arch) . "',
                   kernel='" . trim($kernel) . "',
                   hostname='" . trim($hostname) . "',
                   memory_kb='" . trim($mem) . "',
                   swappiness='" . trim($swapiness) . "'
                   WHERE id=" . $id_mysql_server . "";

        Debug::debug(SqlFormatter::highlight($sql));


        $db->sql_query($sql);
    }

}
