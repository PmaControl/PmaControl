<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;
use \Glial\Security\Crypt\Crypt;

class Service extends Controller {

    use \App\Library\Debug;

    public function pushServer($param) {
        $this->view = false;
        $this->layout_name = false;


        $this->parseDebug($param);

        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            echo '{"error": "Vous n\'êtes pas autorisé à acceder à la ressource requise"}' . "\n";
            exit;
        } else {



            $id_user_main = $this->checkCredentials($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);

            if ($_SERVER['REQUEST_METHOD'] === "POST") {

                if ($id_user_main !== false) {

                    if (!empty(end($_FILES)['tmp_name'])) {


                        $this->parseServer(trim(file_get_contents(end($_FILES)['tmp_name'])));
                        echo '{"authenticate": "ok"}' . "\n";
                    } else {
                        echo '{"authenticate": "ok","json":"not loaded"}' . "\n";
                    }
                } else {
                    echo '{"authenticate": "ko"}' . "\n";
                    //echo "KO\n";
                }

                $this->saveHistory($id_user_main);
            }
        }

        //echo "\n";
    }

    private function checkCredentials($user, $password) {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * FROM webservice_user WHERE user = '" . $db->sql_real_escape_string($user) . "'";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            if ($this->unCrypt($ob->password) != $password) {
                return false;
            }
        }

        return true;
    }

    private function parseServer($json) {


        $data = json_decode($json, true);



        foreach ($data as $server_type => $servers) {
            foreach ($servers as $server) {
                switch ($server_type) {
                    case 'mysql':
                        $this->addMysqlServer($server);
                        break;
                    case 'haproxy':
                        $this->addHaproxyServer($server);
                        break;
                }
            }
        }


        $this->generateMySQLConfig();

        return true;
    }

    /*
     * to move in test
     */

    public function parseTest($param) {

        $this->parseDebug($param);

        $json = file_get_contents(ROOT . "config_sample/webservice.sample.json");
        echo json_encode(json_decode($json));

        //$this->parseServer($json);
    }

    private function addMysqlServer($data) {
        $this->debug($data);

        $db = $this->di['db']->sql(DB_DEFAULT);


        $server = array();
        $server['mysql_server']['id_client'] = $this->getId($data['client'] ?? "none", "client", "libelle");





        $server['mysql_server']['id_environment'] = $this->getId($data['environment'], "environment", "key", array("libelle" => ucfirst($data['environment']), "class" => "info", "letter" => substr(strtoupper($data['environment']), 0, 1)));
        $server['mysql_server']['name'] = str_replace(array('-', '.'), "_", $data['fqdn']);
        $server['mysql_server']['display_name'] = $this->getHostname($data['display_name'], $data);
        $server['mysql_server']['ip'] = $this->getIp($data['fqdn']);
        $server['mysql_server']['hostname'] = $data['fqdn'];
        $server['mysql_server']['login'] = $data['login'];
        $server['mysql_server']['passwd'] = $this->crypt($data['password']);
        $server['mysql_server']['database'] = $data['database'] ?? "mysql";
        $server['mysql_server']['is_password_crypted'] = "1";
        $server['mysql_server']['port'] = $data['port'] ?? 3306;


        $sql = "SELECT id FROM `mysql_server` WHERE `ip`='" . $server['mysql_server']['ip'] . "' AND `port` = '" . $server['mysql_server']['port'] . "'";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            if (!empty($ob->id)) {
                $server['mysql_server']['id'] = $ob->id;
            }
        }

        $this->debug($server, "new MySQL");

        $ret = $db->sql_save($server);

        if ($ret) {

            if (empty($server['mysql_server']['id'])) {
                echo '{"' . $server['mysql_server']['hostname'] . ':' . $server['mysql_server']['port'] . '": "OK - INSERTED"}';
            } else {
                echo '{"' . $server['mysql_server']['hostname'] . ':' . $server['mysql_server']['port'] . '": "OK - UPDATED"}';
            }

            $this->onAddMysqlServer();
        } else {
            echo '{"' . $server['mysql_server']['hostname'] . ':' . $server['mysql_server']['port'] . '": "KO"}';
        }



        return $server;
    }

    private function addHaproxyServer() {
        
    }

    private function addMaxscaleServer() {
        
    }

    private function addProxysqlServer() {
        
    }

    public function generateMySQLConfig($param = '') {
        $this->view = false;

        $this->parseDebug($param);

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server a ORDER BY id_client";
        $res = $db->sql_query($sql);

        $config = ';[name_of_connection] => will be acceded in framework with $this->di[\'db\']->sql(\'name_of_connection\')->method()
;driver => list of SGBD avaible {mysql, pgsql, sybase, oracle}
;hostname => server_name of ip of server SGBD (better to put localhost or real IP)
;user => user who will be used to connect to the SGBD
;password => password who will be used to connect to the SGBD
;database => database / schema witch will be used to access to datas
';

        while ($ob = $db->sql_fetch_object($res)) {
            $string = "[" . $ob->name . "]\n";
            $string .= "driver=mysql\n";
            $string .= "hostname=" . $ob->ip . "\n";
            $string .= "port=" . $ob->port . "\n";
            $string .= "user=" . $ob->login . "\n";
            $string .= "password=" . $ob->passwd . "\n";
            $string .= "crypted=1\n";
            $string .= "database=" . $ob->database . "\n";

            $config .= $string . "\n\n";

            $this->debug($string);
        }

        file_put_contents(ROOT . "/configuration/db.config.ini.php", $config);
    }

    /*
     * to export in Glial::MySQL ?
     * 
     */

    private function getId($value, $table_name, $field, $list = array()) {

        $list_key = '';
        $list_val = '';


        if (count($list) > 0) {
            $keys = array_keys($list);
            $values = array_values($list);

            $list_key = ",`" . implode('`,`', $keys) . "`";
            $list_val = ",'" . implode("','", $values) . "'";
        }
        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "IF (SELECT 1 = 1 FROM `" . $table_name . "` WHERE `" . $field . "`='" . $db->sql_real_escape_string($value) . "') THEN
BEGIN
    SELECT `id` FROM `" . $table_name . "` WHERE `" . $field . "`='" . $db->sql_real_escape_string($value) . "';
END;
ELSE
BEGIN
    INSERT INTO `" . $table_name . "` (`" . $field . "` " . $list_key . ") VALUES('" . $db->sql_real_escape_string($value) . "' " . $list_val . ");
    SELECT LAST_INSERT_ID() AS id;
END;
END IF;";

        $this->debug(SqlFormatter::highlight($sql), "SQL");


        if ($db->sql_multi_query($sql)) {


            do {
                /* Stockage du premier jeu de résultats */
                $result = $db->sql_use_result();
                if ($result) {

                    while ($row = $db->sql_fetch_array($result, MYSQLI_ASSOC)) {

                        if (!empty($row['id'])) {
                            $id = $row['id'];
                        }
                    }
                }
            } while ($db->sql_next_result());
        }

        return $id;


        //debug($row['id']);
        //throw new \Exception('PMACTRL-059 : impossible to find table and/or field');
    }

    function testa() {
        $id = $this->getId("dgwdfg", "client", "libelle");
        debug($id);
    }

    private function getHostname($name, $data) {
        if ($name == "@hostname") {
            $db = new mysqli($data['fqdn'], $data['login'], $data['password'], "mysql", $data['port']);
            $res = $db->query('SELECT @@hostname as hostname;');

            while ($ob = $res->fetch_object()) {
                $hostname = $ob->hostname;
            }

            $db->close();
        } else {
            $hostname = $name;
        }

        return $hostname;
    }

    private function getIp($hostname) {
        $ip = shell_exec("dig +short " . $hostname);

        return trim($ip);
    }

    private function crypt($password) {
        Crypt::$key = CRYPT_KEY;
        $passwd = Crypt::encrypt($password);

        return $passwd;
    }

    private function unCrypt($password_crypted) {
        Crypt::$key = CRYPT_KEY;
        $passwd = Crypt::decrypt($password);

        return $passwd;
    }

    private function saveHistory($id_user_main) {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $logon = 1;
        if ($id_user_main === false) {
            $id_user_main = 1;
            $logon = 0;
        }

        $data = array();
        $data['webservice_history_main']['id_user_main'] = $id_user_main;
        $data['webservice_history_main']['user'] = $_SERVER['PHP_AUTH_USER'];
        $data['webservice_history_main']['password'] = $_SERVER['PHP_AUTH_PW'];
        $data['webservice_history_main']['date'] = date('Y-m-d H:i:s');
        $data['webservice_history_main']['logon'] = $logon;
        $data['webservice_history_main']['message'] = trim(file_get_contents(end($_FILES)['tmp_name']));
        $data['webservice_history_main']['remote_addr'] = $_SERVER["REMOTE_ADDR"];


        $res = $db->sql_save($data);

        if (!$res) {
            debug($data);
        }
    }

    /*     * ************************ */

    public function onAddMysqlServer() {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql5 = "INSERT IGNORE INTO `ts_max_date` (`id_daemon_main`, `id_mysql_server`, `date`, `date_previous`) SELECT 7,id, now(), now() from mysql_server;";
        $db->sql_query($sql5);
    }

}
