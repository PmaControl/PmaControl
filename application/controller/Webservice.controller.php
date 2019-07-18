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
use App\Library\Json;

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

                    $this->return['authenticate'] = "ok";

                    Debug::debug(end($_FILES)['tmp_name']);

                    $this->parseServer(end($_FILES)['tmp_name']);

                    //$this->pushFile(trim(file_get_contents()));
                } else {

                    $this->return['authenticate'] = "ok";
                    $this->return['error'][]      = "Impossible to load json";
                }
            } else {
                $this->return['authenticate'] = "ko";
                $this->return['error'][]      = "Unauthorized access";

                //echo "KO\n";
            }

            $db = $this->di['db']->sql(DB_DEFAULT);
            Mysql::onAddMysqlServer($db);

            $this->saveHistory($id_user_main);
        } else {

            $this->return['error'][] = "This request method is not allowed : ".$_SERVER['REQUEST_METHOD'];
        }

        echo json_encode($this->return, JSON_PRETTY_PRINT)."\n";

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


        $data = Json::getDataFromFile($filename);


        Debug::debug($data, "data");

        $db = $this->di['db']->sql(DB_DEFAULT);
        Mysql::set_db($db);


        foreach ($data as $server_type => $servers) {
            foreach ($servers as $server) {
                switch ($server_type) {
                    case 'mysql':

                        Debug::debug($server, "SERVER");


                        Mysql::addMysqlServer($server);
                        break;
                }
            }
        }

        Mysql::generateMySQLConfig($db);

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

    public function index()
    {

        //ecran pour gérer les webservice // password
    }
}