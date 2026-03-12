<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Security\Crypt\Crypt;
use \App\Library\Debug;
use App\Library\Mysql;
use App\Library\Json;
use \Glial\Sgbd\Sgbd;

/**
 * Class responsible for webservice workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Webservice extends Controller
{
/**
 * Stores `$return` for return.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    var $return = array();

/**
 * Handle webservice state through `pushServer`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for pushServer.
 * @phpstan-return void
 * @psalm-return void
 * @see self::pushServer()
 * @example /fr/webservice/pushServer
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function pushServer($param)
    {
        $this->view        = false;
        $this->layout_name = false;

        define (IS_CLI, true);

        Debug::parseDebug($param);

        $jsonData = file_get_contents('php://input');

        if (! $this->isJson($jsonData)) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 400 Bad request');
            echo "{\"error\": \"JSON malformed\", \"json\" : \"$jsonData\"}"."\n";
            exit;
        }

        $finale_name = "/tmp/tmp.".uniqid();
        file_put_contents($finale_name, json_encode(json_decode($jsonData)));
        Debug::debug($jsonData);

        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            echo '{"error": "Vous n\'êtes pas autorisé à acceder à la ressource requise, Login or password not good"}'."\n";
            exit;
        }
        
        $id_user_main = $this->checkCredentials($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        Debug::debug($id_user_main, "Authorized Access");

        if ($_SERVER['REQUEST_METHOD'] === "POST" || $_SERVER['REQUEST_METHOD'] === "post") {   
            if ($id_user_main === true) {
                $this->return['authenticate'] = "ok";
                $this->parseServer($finale_name);
            } else {
                $this->return['authenticate'] = "ko";
                $this->return['error'][]      = "Unauthorized access";
            }

            $db = Sgbd::sql(DB_DEFAULT);
            Mysql::onAddMysqlServer();

            $this->saveHistory($id_user_main, $jsonData);
        } else {

            $this->return['error'][] = "This request method is not allowed : ".$_SERVER['REQUEST_METHOD'];
        }

        header('WWW-Authenticate: Basic realm="My Realm"');
        header('HTTP/1.1 200 OK');
        header('Content-Type: application/json');
        echo json_encode($this->return, JSON_PRETTY_PRINT)."\n";
        //Debug::debug($this->return);
    }

/**
 * Handle webservice state through `importFile`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for importFile.
 * @phpstan-return void
 * @psalm-return void
 * @see self::importFile()
 * @example /fr/webservice/importFile
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function importFile($param)
    {
        Debug::parseDebug($param);

        $filename = $param[0] ?? "";
        $this->parseServer($filename);
    }

/**
 * Handle webservice state through `checkCredentials`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $user Input value for `user`.
 * @phpstan-param mixed $user
 * @psalm-param mixed $user
 * @param mixed $password Input value for `password`.
 * @phpstan-param mixed $password
 * @psalm-param mixed $password
 * @return mixed Returned value for checkCredentials.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::checkCredentials()
 * @example /fr/webservice/checkCredentials
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function checkCredentials($user, $password)
    {
        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM webservice_user WHERE user = '".$db->sql_real_escape_string($user)."' AND is_enabled=1";
        Debug::sql($sql);

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            Debug::debug($ob, "value");

            $pw_from_db = Crypt::decrypt($ob->password, CRYPT_KEY);

            Debug::debug($pw_from_db, "remote");
            Debug::debug($password, "database");

            if ($pw_from_db === $password) {
                return true;
            }
        }

        return false;
    }

/**
 * Handle webservice state through `parseServer`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $filename Input value for `filename`.
 * @phpstan-param mixed $filename
 * @psalm-param mixed $filename
 * @return mixed Returned value for parseServer.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::parseServer()
 * @example /fr/webservice/parseServer
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function parseServer($filename)
    {
        $data = Json::getDataFromFile($filename);

        Debug::debug($data, "data");

        $db = Sgbd::sql(DB_DEFAULT);

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

        Mysql::generateMySQLConfig();

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

/**
 * Update webservice state through `saveHistory`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param int $id_user_main Input value for `id_user_main`.
 * @phpstan-param int $id_user_main
 * @psalm-param int $id_user_main
 * @param mixed $json Input value for `json`.
 * @phpstan-param mixed $json
 * @psalm-param mixed $json
 * @return void Returned value for saveHistory.
 * @phpstan-return void
 * @psalm-return void
 * @see self::saveHistory()
 * @example /fr/webservice/saveHistory
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function saveHistory($id_user_main, $json)
    {
        $db = Sgbd::sql(DB_DEFAULT);

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
        $data['webservice_history_main']['message']      = $json;
        $data['webservice_history_main']['remote_addr']  = $_SERVER["REMOTE_ADDR"];

        $res = $db->sql_save($data);

        if (!$res) {
            Debug::debug($data);
        }
    }
    /*     * ************************ */

    public function addAccount($param)
    {

        Debug::parseDebug($param);

        $filename = $param[0] ?? "";

        if (!empty($filename) && file_exists($filename)) {

            $config = $this->parseConfig($filename);
        }

        if (!empty($config['webservice'])) {

            $db = Sgbd::sql(DB_DEFAULT);

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


                $id_client = Mysql::getId($user['organization'], "client", "libelle");

                $sql = "SELECT * FROM `webservice_user` WHERE `user` = '".$user['user']."' AND `host` = '".$user['host']."';";
                Debug::sql($sql);

                $res = $db->sql_query($sql);

                $data = array();

                while ($ob = $db->sql_fetch_object($res)) {
                    $data['webservice_user']['id'] = $ob->id;
                }

                $data['webservice_user']['user']      = $user['user'];
                $data['webservice_user']['password']  = Crypt::encrypt($user['password'], CRYPT_KEY);
                $data['webservice_user']['host']      = $user['host'];
                $data['webservice_user']['id_client'] = $id_client;

                Debug::debug($data, "Data to inset or refresh");

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

/**
 * Render webservice state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/webservice/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index()
    {
        //ecran pour gérer les webservice // password
    }

/**
 * Handle webservice state through `parseConfig`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $configFile Input value for `configFile`.
 * @phpstan-param mixed $configFile
 * @psalm-param mixed $configFile
 * @return mixed Returned value for parseConfig.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::parseConfig()
 * @example /fr/webservice/parseConfig
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function parseConfig($configFile)
    {
        $config = json_decode(file_get_contents($configFile), true);

        return $config;
    }

/**
 * Handle webservice state through `decrypt`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for decrypt.
 * @phpstan-return void
 * @psalm-return void
 * @see self::decrypt()
 * @example /fr/webservice/decrypt
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function decrypt($param)
    {
        echo Crypt::decrypt($param[0], CRYPT_KEY);
    }


/**
 * Handle webservice state through `isJson`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $string Input value for `string`.
 * @phpstan-param mixed $string
 * @psalm-param mixed $string
 * @return mixed Returned value for isJson.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::isJson()
 * @example /fr/webservice/isJson
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function isJson($string) {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
