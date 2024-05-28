<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use JJG\Ping;
use \Glial\Sgbd\Sgbd;

/*
 * this class is made to scan network and discover MySQL Server
 * to parse result of nmap
 */


// arp-scan -l



class Scan extends Controller {

    public $data = array();
    var $port = array(22 => "SSH", 3306 => "MySQL", 3307 => "MySQL load-balanced",
        33306 => "MySQL load-balanced (Master)",
        33307 => "MySQL load-balanced (R/W Splitting)",
        33308 => "MySQL load-balanced (Read only)",
        33309 => "MySQL load-balanced (Round-Robin)",
        33310 => "MySQL load-balanced (Sharding)",
        9600 => "HAproxy stats", 4006 => "Round robin listener", 4008 => "R/W split listener", 4442 => "Debug information",
        4567 => "Galera", 4444 => "SST Gaelera", 4568 => "IST Galera", 6603 => "MaxAdmin CLI", 6033 => "MaxAdmin CLI");
    var $other = array(21 => "ftp", 23 => "telnet", 25 => "smtp", 80 => "http", 389 => "ldap",
        443 => "https", 445 => "microsfot-ds", 465 => "smtps", 2019 => "nfs");
    var $cache_file = TMP . "data/scan/";
    var $debug = false;
    var $start_date;

    //deprecated
    public function parse($input) {
        //35 & 68
        // nmap -p -sO 3306 10.0.51.1-255
        //nmap -p 3306 -sV 10.0.51.1-254
        // netstat -paunt

        $this->view = false;

        $data = [];
        foreach ($input as $server) {

            $tmp = [];
            $array = explode("\n", $server);

            $output_array = [];
            preg_match_all("/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/", $array[0], $output_array);

            if (empty($output_array[0][0])) {
                throw new \Exception("PMACTRL-065 : Impossible to find the IP", 80);
            }

            $tmp['ip'] = $output_array[0][0];
            $tmp['hostname'] = trim(str_replace(array($tmp['ip'], "()"), array("", ""), $array[0]));

            if (empty($tmp['hostname'])) {
                $tmp['hostname'] = $tmp['ip'];
            }

            unset($array[0]);
            foreach ($array as $line) {

                if (preg_match("#[0-9]+/tcp.*#", $line)) {
                    $port = explode("/", $line)[0];
                    $line = trim(str_replace($port . "/tcp", '', $line));  // upgrade with other protocol ?

                    $status = trim(explode(' ', $line)[0]);
                    $line = trim(str_replace($status, '', $line));

                    $name = trim(explode(' ', $line)[0]);
                    $line = trim(str_replace($name, '', $line));

                    $version = trim($line);

                    $tmp['port'][$port]['status'] = $status;
                    $tmp['port'][$port]['name'] = $name;
                    $tmp['port'][$port]['version'] = trim(explode("\t", $line)[0]);
                }

                if (strstr($line, 'MAC Address: ')) {
                    $line = trim(str_replace("MAC Address:", '', $line));
                    $tmp['MAC'] = explode(' ', $line)[0];
                }
            }

            $data[] = $tmp;
        }

        return $data;
    }

    public function autoDiscovering() {
        // arp -a -n => fastest way

        $this->view = false;
        $nmap = $this->generateNmap();
        $xml = shell_exec($nmap);
        $arr = $this->xmlToArray($xml);
        $data = $this->extract($res);
        $json = $this->parse($data);
        $this->data = $arr;

        return $arr;
    }

    //deprecated
    public function extract($data) {
        /*
         * remove these line when version of MySQL not know or cannot be mapped by nmap
         *
         * SF:0is\x20not\x20allowed\x20to\x20connect\x20to\x20this\x20MariaDB\x20serv
         * SF:er")%r(SMBProgNeg,4A,"F\0\0\0\xffj\x04Host\x20'10\.0\.51\.117'\x20is\x2
         */

        $res = preg_replace("/^SF.*$/m", "", $data);
        $res2 = preg_replace("/^1 service unrecognized.*$/m", "", $res);
        $res3 = preg_replace("/\n+/", "\n", $res2);

        //split by server
        $lines = explode("Nmap scan report for", $res3);

        //remove header and footer
        unset($lines[count($lines) - 1]);
        unset($lines[0]);

        return $lines;
    }

    public function xmlToArray($xml) {
        $xml = new SimpleXMLElement($xml);
        $json = json_encode($xml);
        $data = json_decode($json, true);
        return $data;
    }

    public function index() {

        $db = Sgbd::sql(DB_DEFAULT);
        $this->title = '<span class="glyphicon glyphicon-search" aria-hidden="true"></span> ' . __("Scan network");
        $this->ariane = '> <i style="font-size: 16px" class="fa fa-puzzle-piece"></i> Plugins > ' . $this->title;

        $this->di['js']->addJavascript(array("Scan/index.js"));
        $data['ip'] = $this->getIpMonitored();
        //$data['scan'] = $this->getData();


        $ips = $this->getIpMonitored();
        $data['ranges'] = $this->generateRange($ips);

        $sql = "SELECT * FROM scan";
        $data['scaned'] = $db->sql_fetch_yield($sql);


        $data['select'] = array();
        foreach ($data['ranges'] as $range) {
            $tmp = [];
            $tmp['id'] = $range;
            $tmp['libelle'] = $range . ".x";

            $data['select'][] = $tmp;
        }



        $data['port'] = $this->other + $this->port;
        ksort($data['port']);
        $this->set('data', $data);
    }

    //deprecated ?
    public function __sleep() {
        return array('data');
    }

    public function getData($refresh = false) {
        $path_to_acl_tmp = TMP . "data/scan.ser";

        if (!$refresh) {
            if (file_exists($path_to_acl_tmp)) {
                if (is_file($path_to_acl_tmp)) {
                    $s = file_get_contents($path_to_acl_tmp);
                    $tmp = unserialize($s);
                    $this->data = $tmp->data;
                    return $this->data;
                }
            }
        }

        $data = $this->autoDiscovering();

        file_put_contents($path_to_acl_tmp, serialize($this));

        return $data;
    }

    public function generateNmap($range) {
        $this->view = false;
        $this->layout = false;

        $port_to_scan = $this->port + $this->other;
        $ports = "";
        foreach ($port_to_scan as $port => $service_name) {
            $ports .= 'T:' . $port . ',';
        }
        $ports = substr($ports, 0, -1);


        $ext = '.0/24';
        $nmap = "nmap -p " . $ports . " -oX - " . implode($ext . ' ', $range) . $ext . ""; // ".implode(' ', $uniq_ip);

        return trim($nmap);
    }

    public function getIpMonitored() {
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT `ip` from `mysql_server`";
        $res = $db->sql_query($sql);
        $data['ip'] = array();
        while ($ob = $db->sql_fetch_object($res)) {
            $data['ip'][] = $ob->ip;
        }
        return $data['ip'];
    }

    public function refresh() {
        $this->getData(true);

        header('location: ' . LINK . "scan/index");
    }

    public function generateRange($ips) {
        $range = [];
        $uniq_ip = [];
        foreach ($ips as $ip) {
            if ($ip === "127.0.0.1" || $ip === "localhost") {
                continue;
            }

            $tmp = explode('.', $ip);
            unset($tmp[3]);
            $new_range = implode('.', $tmp);
            if (!in_array($new_range, $range) && ($tmp[0] == "10" || ($tmp[0] == "192" && $tmp[1] == "168"))) {
                $range[] = $new_range;
            }

            /*
              if ($tmp[0] != "10" && ($tmp[0] != "192" || $tmp[0] != "168")) {
              $uniq_ip[] = $ip;
              } */
        }


        return $range;
    }

    public function scanner() {

        shell_exec("mkdir -p " . $this->cache_file);


        $this->view = false;
        $ips = $this->getIpMonitored();
        $ranges = $this->generateRange($ips);

        foreach ($ranges as $range) {

            $nmap = $this->generateNmap(array($range));

            $xml = shell_exec($nmap);
            $obj = new SimpleXMLElement($xml);
            $json = json_encode($obj);

            echo $nmap . "\n";
            file_put_contents($this->cache_file . "$range.json", $json);
        }
    }

    public function scanner2($ranges) {
        $ports = $this->other + $this->port;

        foreach ($ranges as $range) {
            foreach ($ports as $port) {
                
            }
        }


        // fsockopen ( string $hostname [, int $port = -1 [, int &$errno [, string &$errstr [, float $timeout = ini_get("default_socket_timeout")]]]] )
    }

    public function pingAll($param) {
        if (!empty($param)) {
            foreach ($param as $elem) {
                if ($elem == "--debug") {
                    $this->debug = true;
                    echo Color::getColoredString("DEBUG activated !", "yellow") . "\n";
                }
            }
        }

        $this->start_date = date('Y-m-d H:i:s');

        shell_exec("mkdir -p " . $this->cache_file);
        shell_exec("cd " . $this->cache_file . " && rm -f *.json");


        if (Debug::$debug) {
            echo "[" . date('Y-m-d H:i:s') . "]" . " Start all tests\n";
        }

        $this->view = false;
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM daemon_main where id=2";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $maxThreads = $ob->thread_concurency; // check MySQL server x by x
            $maxExecutionTime = $ob->max_delay;
        }



        //$maxThreads = \Glial\System\Cpu::getCpuCores();

        $openThreads = 0;
        $child_processes = array();

        $ips = $this->getIpMonitored();
        $ranges = $this->generateRange($ips);
        $listIps = $this->generateListIps($ranges);

        //to prevent any trouble with fork
        $db->sql_close();

        if (Debug::$debug) {
            echo "[" . date('Y-m-d H:i:s') . "]" . " Number of ranges : " . count($ranges) . "\n";
            echo "[" . date('Y-m-d H:i:s') . "]" . " Number of IPs to scans : " . count($listIps) . "\n";
        }

        foreach ($listIps as $ip) {
            //echo str_repeat("#", count($child_processes)) . "\n";

            $pid = pcntl_fork();
            $child_processes[$pid] = 1;

            if ($pid == -1) {
                throw new \Exception('PMACTRL-057 : Couldn\'t fork thread !', 80);
            } else if ($pid) {

                if (count($child_processes) > $maxThreads) {
                    $childPid = pcntl_wait($status);
                    unset($child_processes[$childPid]);
                }
                $father = true;
            } else {

                // one thread to test each ping
                $this->testPing($ip, $maxExecutionTime);
                $father = false;
                //we want that child exit the foreach
                break;
            }
            usleep(100);
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

    function testPing($host, $timeout) {
        $this->view = false;
        $ping = new Ping($host);
        $latency = $ping->ping();
        if ($latency !== false) {
            $data['date'] = date('Y-m-d H:i:s');
            $data['latency'] = $latency;
            $json = json_encode($data);
            //store in file and not in DB to prevent max connection
            file_put_contents($this->cache_file . $host . ".json", $json);
        }
    }

    public function generateListIps($ranges) {
        $ips = [];

        foreach ($ranges as $range) {
            for ($i = 1; $i < 255; $i++) {
                $ips[] = $range . "." . $i;
            }
        }

        return $ips;
    }

    public function collectHosts() {
        $list_file = glob($this->cache_file . "*.json");
        $hosts = [];
        foreach ($list_file as $filename) {
            $ip = pathinfo($filename)['filename'];

            $hosts[] = $ip;
        }

        return $hosts;
    }

    public function scanAllPort($combi) {

        echo "[" . date('Y-m-d H:i:s') . "]" . " Start all tests\n";


        $this->view = false;
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM daemon_main where id=3";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $maxThreads = $ob->thread_concurency; // check MySQL server x by x
            $maxExecutionTime = $ob->max_delay;
        }

        $openThreads = 0;
        $child_processes = array();


        //to prevent any trouble with fork
        $db->sql_close();

        if (Debug::$debug) {
            echo "[" . date('Y-m-d H:i:s') . "]" . " Number of combi : " . count($combi) . "\n";
        }

        foreach ($combi as $possibility) {
            //echo str_repeat("#", count($child_processes)) . "\n";

            $pid = pcntl_fork();
            $child_processes[$pid] = 1;

            if ($pid == -1) {
                throw new \Exception('PMACTRL-057 : Couldn\'t fork thread !', 80);
            } else if ($pid) {

                if (count($child_processes) > $maxThreads) {
                    $childPid = pcntl_wait($status);
                    unset($child_processes[$childPid]);
                }
                $father = true;
            } else {

                // one thread to test each ping
                $this->scanPort($possibility['ip'], $possibility['port'], $maxExecutionTime);
                $father = false;
                //we want that child exit the foreach
                break;
            }
            usleep(100);
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

            //collect port scan
        } else {
            exit;
        }
    }

    public function generatePortForScan($ips) {
        $ports = $this->other + $this->port;
        $combi = [];
        foreach ($ips as $ip) {
            foreach ($ports as $port => $name) {
                $tmp = [];
                $tmp['ip'] = $ip;
                $tmp['port'] = $port;
                $combi[] = $tmp;
            }
        }

        return $combi;
    }

    public function scanPort($ip, $port, $maxExecutionTime) {
        $connection = @fsockopen($ip, $port);

        if (is_resource($connection)) {
            file_put_contents($this->cache_file . $ip . ":" . $port . ".port", "time?");
            fclose($connection);
        }
    }

    public function scanAll($param) {
        $this->pingAll($param);
        $hosts = $this->collectHosts();
        $combi = $this->generatePortForScan($hosts);
        $this->scanAllPort($combi);
        $this->save();
    }

    public function save() {
        $db = Sgbd::sql(DB_DEFAULT);
        $list_file = glob($this->cache_file . "*.json");
        $port_open = glob($this->cache_file . "*.port");

        $ips = [];
        foreach ($port_open as $filename) {
            $to_split = pathinfo($filename)['filename'];

            $elems = explode(":", $to_split);

            $ips[$elems[0]][$elems[1]] = 1;
        }

        $db->sql_query("START TRANSACTION;");

        foreach ($list_file as $filename) {
            $ip = pathinfo($filename)['filename'];
            $json = file_get_contents($filename);
            $data = json_decode($json, true);

            if (empty($ips[$ip])) {
                $data2 = '';
            } else {
                $data2 = json_encode($ips[$ip]);
            }

            $db->sql_query("REPLACE INTO `scan`  (`ip`,`ms`,`date`,`data`) "
                    . "VALUES ('" . $ip . "', '" . $data['latency'] . "', '" . $data['date'] . "', '" . $data2 . "');");
        }

        $sql = "DELETE FROM `scan` WHERE `date` < '" . $this->start_date . "'";
        $db->sql_query($sql);
        $db->sql_query("COMMIT;");
    }

}
