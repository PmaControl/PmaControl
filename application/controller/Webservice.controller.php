<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;
use \Glial\Security\Crypt\Crypt;
use \App\Library\Debug;
use App\Library\Mysql;

class Webservice extends Controller
{
    var $return = array();

    public function pushServer($param)
    {
        $this->view        = false;
        $this->layout_name = false;

        Debug::parseDebug($param);


        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            echo '{"error": "Vous n\'êtes pas autorisé à acceder à la ressource requise"}'."\n";
            exit;
        }

        $id_user_main = $this->checkCredentials($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);

        if ($_SERVER['REQUEST_METHOD'] === "POST" || $_SERVER['REQUEST_METHOD'] === "post") {

            if ($id_user_main !== false) {

                if (!empty(end($_FILES)['tmp_name'])) {


                    $this->return['authenticate']          = "ok";
                    $this->parseServer(end($_FILES)['tmp_name']);


                    //$this->pushFile(trim(file_get_contents()));


                    
                    
                } else {

                    $this->return['authenticate']  = "ok";
                    $this->return['error'][] = "Impossible to load json";
                }
            } else {
                $this->return['authenticate'] = "ko";
                $this->return['error'][] = "Unauthorized access";

                //echo "KO\n";
            }

            $db = $this->di['db']->sql(DB_DEFAULT);
            Mysql::onAddMysqlServer($db);

            $this->saveHistory($id_user_main);
        } else {

            $this->return['error'][] = "This request method is not allowed : ".$_SERVER['REQUEST_METHOD'];
        }


        echo json_encode($this->return,JSON_PRETTY_PRINT)."\n";

        //echo "\n";
    }

    public function importFile($param)
    {

        Debug::parseDebug($param);

        $filename = $param[0] ?? "";

        $this->parseServer($filename);
    }

    private function checkCredentials($user, $password)
    {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * FROM webservice_user WHERE user = '".$db->sql_real_escape_string($user)."'";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            if ($this->unCrypt($ob->password) != $password) {
                return false;
            }
        }

        return true;
    }

    private function parseServer($filename)
    {


        $data = $this->parseConfig($filename);



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


        Mysql::generateMySQLConfig($this->di['db']->sql(DB_DEFAULT));

        return true;
    }
    /*
     * to move in test
     */

    public function parseTest($param)
    {

        Debug::parseDebug($param);

        $json = file_get_contents(ROOT."config_sample/webservice.sample.json");
        echo json_encode(json_decode($json));

        //$this->parseServer($json);
    }

    private function addMysqlServer($data)
    {
        //Debug::debug($data);

        $db = $this->di['db']->sql(DB_DEFAULT);


        $server                              = array();
        $server['mysql_server']['id_client'] = $this->getId($data['organization'] ?? "none", "client", "libelle");



        $server['mysql_server']['id_environment']      = $this->getId($data['environment'], "environment", "key",
            array("libelle" => ucfirst($data['environment']), "class" => "info", "letter" => substr(strtoupper($data['environment']), 0, 1)));
        $server['mysql_server']['name']                = str_replace(array('-', '.'), "_", $data['fqdn']);
        $server['mysql_server']['display_name']        = $this->getHostname($data['display_name'], $data);
        $server['mysql_server']['ip']                  = $this->getIp($data['fqdn']);
        $server['mysql_server']['hostname']            = $data['fqdn'];
        $server['mysql_server']['login']               = $data['login'];
        $server['mysql_server']['passwd']              = $this->crypt($data['password']);
        $server['mysql_server']['database']            = $data['database'] ?? "mysql";
        $server['mysql_server']['is_password_crypted'] = "1";
        $server['mysql_server']['port']                = $data['port'] ?? 3306;


        $sql = "SELECT id FROM `mysql_server` WHERE `ip`='".$server['mysql_server']['ip']."' AND `port` = '".$server['mysql_server']['port']."'";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            if (!empty($ob->id)) {
                $server['mysql_server']['id'] = $ob->id;
            }
        }

        Debug::debug($server, "new MySQL");


        if ($this->isPmaControl($server['mysql_server']['ip'], $server['mysql_server']['port']) === true) {
            

            $this->return['mysql']['caution'] = "Impossible to overright the server of PmaControl (".$server['mysql_server']['ip'].":".$server['mysql_server']['port'].")";

            return false;
        }



        $id_mysql_server = $db->sql_save($server);

        if ($id_mysql_server) {

            if (empty($server['mysql_server']['id'])) {

                $this->return['mysql']['inserted'][] = $server['mysql_server']['display_name']." (".$server['mysql_server']['hostname'].':'.$server['mysql_server']['port'].")";
            } else {

                $this->return['mysql']['updated'][] = $server['mysql_server']['display_name']." (".$server['mysql_server']['hostname'].':'.$server['mysql_server']['port'].")";



                if (!empty($server['mysql_server']['tag'])) {
                    $this->insertTag($id_mysql_server, $server['mysql_server']['tag']);
                }
            }

            Mysql::onAddMysqlServer($this->di['db']->sql(DB_DEFAULT));
        } else {

            unset($server['mysql_server']['passwd']);
       
            $this->return['mysql']['failed'][] = $server['mysql_server']['display_name']." (".$server['mysql_server']['hostname'].':'.$server['mysql_server']['port'].") : ".json_encode(array($db->sql_error(),$server));
        }


        return $server;
    }

    private function addHaproxyServer()
    {
        
    }

    private function addMaxscaleServer()
    {
        
    }

    private function addProxysqlServer()
    {
        
    }
    /*
     * to export in Glial::MySQL ?
     * 
     */

    private function getId($value, $table_name, $field, $list = array())
    {

        $list_key = '';
        $list_val = '';


        if (count($list) > 0) {
            $keys   = array_keys($list);
            $values = array_values($list);

            $list_key = ",`".implode('`,`', $keys)."`";
            $list_val = ",'".implode("','", $values)."'";
        }
        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "IF (SELECT 1 = 1 FROM `".$table_name."` WHERE `".$field."`='".$db->sql_real_escape_string($value)."') THEN
BEGIN
    SELECT `id` FROM `".$table_name."` WHERE `".$field."`='".$db->sql_real_escape_string($value)."';
END;
ELSE
BEGIN
    INSERT INTO `".$table_name."` (`".$field."` ".$list_key.") VALUES('".$db->sql_real_escape_string($value)."' ".$list_val.");
    SELECT LAST_INSERT_ID() AS id;
END;
END IF;";

        Debug::debug(SqlFormatter::highlight($sql), "SQL");


        if ($db->sql_multi_query($sql)) {


            $i = 1;
            do {

                if ($i != 1) {
                    $db->sql_next_result();
                }
                $i++;


                /* Stockage du premier jeu de résultats */
                $result = $db->sql_use_result();
                if ($result) {

                    while ($row = $db->sql_fetch_array($result, MYSQLI_ASSOC)) {

                        if (!empty($row['id'])) {
                            $id = $row['id'];
                        }
                    }
                }
            } while ($db->sql_more_results());
        }

        return $id;


        //debug($row['id']);
        //throw new \Exception('PMACTRL-059 : impossible to find table and/or field');
    }

    function testa()
    {
        $id = $this->getId("dgwdfg", "client", "libelle");
        debug($id);
    }

    private function getHostname($name, $data)
    {


        if (Debug::$debug) {
            return $name;
        }



        if ($name == "@hostname") {


            $db = new mysqli($data['fqdn'], $data['login'], $data['password'], "mysql", $data['port']);

            if ($db) {
                $res = $db->query('SELECT @@hostname as hostname;');

                while ($ob = $res->fetch_object()) {
                    $hostname = $ob->hostname;
                }

                $db->close();
            }
        } else {
            $hostname = $name;
        }

        return $hostname;
    }

    private function getIp($hostname)
    {
        if (filter_var($hostname, FILTER_VALIDATE_IP)) {
            return trim($hostname);
        }

        $ip = shell_exec("dig +short ".$hostname);

        return trim($ip);
    }

    private function crypt($password)
    {
        Crypt::$key = CRYPT_KEY;
        $passwd     = Crypt::encrypt($password);

        return $passwd;
    }

    private function unCrypt($password_crypted)
    {
        Crypt::$key = CRYPT_KEY;
        $passwd     = Crypt::decrypt($password_crypted);

        return $passwd;
    }

    private function saveHistory($id_user_main)
    {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $logon = 1;
        if ($id_user_main === false) {
            $id_user_main = 1;
            $logon        = 0;
        }

        $data                                            = array();
        $data['webservice_history_main']['id_user_main'] = $id_user_main;
        $data['webservice_history_main']['user']         = $_SERVER['PHP_AUTH_USER'];
        $data['webservice_history_main']['password']     = $_SERVER['PHP_AUTH_PW'];
        $data['webservice_history_main']['date']         = date('Y-m-d H:i:s');
        $data['webservice_history_main']['logon']        = $logon;
        $data['webservice_history_main']['message']      = trim(file_get_contents(end($_FILES)['tmp_name']));
        $data['webservice_history_main']['remote_addr']  = $_SERVER["REMOTE_ADDR"];


        $res = $db->sql_save($data);

        if (!$res) {
            debug($data);
        }
    }
    /*     * ************************ */

    public function parseConfig($configFile)
    {

        if (empty($configFile) || !file_exists($configFile)) {
            throw new \Exception('PMACTRL-255 : The file '.$configFile.' doesn\'t exit !');
        }

        $file   = file_get_contents($configFile);
        $config = json_decode($file, true);


        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return $config;
                break;
            case JSON_ERROR_DEPTH:
                $error = 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $error = 'Unknown error';
                break;
        }


        throw new \Exception("PMACTRL-254 : JSON : ".$error, 80);
    }

    public function addAccount($param)
    {
        $filename = $param[0] ?? "";

        if (!empty($filename) && file_exists($filename)) {

            $config = $this->parseConfig($filename);
        }

        if (!empty($config['webservice'])) {

            $db = $this->di['db']->sql(DB_DEFAULT);

            foreach ($config['webservice'] as $user) {

                if (!is_array($user)) {
                    throw new \InvalidArgumentException('PMACTRL-029 : user\'s account should be in array and not set directly !');
                }

                $to_check = array('user', 'password', 'host', 'organization');

                foreach ($to_check as $val) {
                    if (!isset($user[$val])) {
                        throw new \InvalidArgumentException('PMACTRL-028 : webservice.'.$val.' is empty in config file :'.$filename);
                    }
                }

                $id_client = $this->getId($user['organization'], "client", "libelle");

                $sql = "SELECT * FROM `webservice_user` WHERE `user` = '".$user['user']."' AND `host` = '".$user['host']."'";
                $res = $db->sql_query($sql);

                $data = array();

                while ($ob = $db->sql_fetch_object($res)) {
                    $data['webservice_user']['id'] = $ob->id;
                }

                $data['webservice_user']['user']      = $user['user'];
                $data['webservice_user']['password']  = $this->crypt($user['password']);
                $data['webservice_user']['host']      = $user['host'];
                $data['webservice_user']['id_client'] = $id_client;

                $res = $db->sql_save($data);

                if (!$res) {
                    debug($data);
                    throw new \Exception('save failed !', 80);
                }
            }
        } else {
            //show that we don't sert webservice
        }
    }

    private function insertTag($id_mysql_server, $all_tags)
    {


        if (empty($all_tags)) {
            return true;
        }


        $tags = explode(',', $all_tags);

        $db = $this->di['db']->sql(DB_DEFAULT);

        foreach ($tags as $tag) {

            $id_tag = $this->getId($tag, "tag", "name", array("font" => $this->setBackgroundColor($tag), "color" => $this->setFontColor($tag)));

            $sql = "INSERT IGNORE link__mysql_server__tag (`id_mysql_server`,`id_tag`) VALUES (".$id_mysql_server.", ".$id_tag.");";
            $db->sql_query($sql);
        }

        /* id_mysql_server
          $sql = "DELETE FROM `link__mysql_server__tag` WHERE `id_mysql_server` = '".$id_mysql_server."'";
          $res = $db->sql_query($sql);
         */
    }

    private function setFontColor($type)
    {
        $hex = substr(md5($type), 0, 6);

        return $hex;

        //return $hex['background'];
    }

    private function setBackgroundColor($type)
    {
        $hex = $this->setFontColor($type);

        $ret = hex2bin('ffffff') ^ hex2bin($hex);

        return bin2hex($ret);

        //return $hex['background'];
    }

    public function testColor($param)
    {
        $text = $param[0];


        echo $text." : #".$this->setFontColor($text)." : #".$this->setBackgroundColor($text)."\n";
    }

    public function isPmaControl($ip, $port)
    {

        $db = $this->di['db']->sql(DB_DEFAULT);


        $sql = "SELECT * FROM mysql_server where name='".DB_DEFAULT."'";


        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            if ($ob->ip === $ip && $ob->port == $port) {
                return true;
            }
        }

        return false;
    }
}