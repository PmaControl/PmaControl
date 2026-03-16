<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Synapse\Config;
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
 * Export mysql_server entries with decrypted passwords.
 *
 * Usage:
 *   ./glial webservice exportMysqlServerPlain
 *   ./glial webservice exportMysqlServerPlain /tmp/mysql_server.json
 *
 * @param array<int,mixed> $param
 * @return void
 */
    public function exportMysqlServerPlain($param)
    {
        $this->assertCliRootOnly();

        $outputFile = $param[0] ?? '';
        $db         = Sgbd::sql(DB_DEFAULT);
        $sql        = "SELECT id, display_name, ip, hostname, login, passwd, port, database, is_monitored, is_acknowledged, ssh_port, ssh_login, is_proxy, is_vip
                       FROM mysql_server
                       WHERE is_deleted = 0
                       ORDER BY id";
        $res        = $db->sql_query($sql);
        $servers    = array();

        while ($ob = $db->sql_fetch_object($res)) {
            $servers[] = array(
                'display_name' => $ob->display_name,
                'ip'           => $ob->ip,
                'hostname'     => $ob->hostname,
                'login'        => $ob->login,
                'passwd'       => Crypt::decrypt($ob->passwd, CRYPT_KEY),
                'port'         => (int) $ob->port,
                'database'     => $ob->database,
                'is_monitored' => (int) $ob->is_monitored,
                'is_acknowledged' => (int) $ob->is_acknowledged,
                'ssh_port'     => (int) $ob->ssh_port,
                'ssh_login'    => $ob->ssh_login,
                'is_proxy'     => (int) $ob->is_proxy,
                'is_vip'       => (int) $ob->is_vip,
            );
        }

        $json = json_encode($servers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \Exception("PMACTRL-WS-EXPORT-001: Failed to encode mysql_server export.");
        }

        if ($outputFile !== '') {
            if (file_put_contents($outputFile, $json . PHP_EOL) === false) {
                throw new \Exception("PMACTRL-WS-EXPORT-002: Failed to write export file: " . $outputFile);
            }

            echo "Exported " . count($servers) . " mysql_server entries to " . $outputFile . PHP_EOL;
            return;
        }

        echo $json . PHP_EOL;
    }

/**
 * Import mysql_server entries from a JSON file with plain-text passwords.
 *
 * Accepted formats:
 *   [ {...}, {...} ]
 *   { "mysql": [ {...}, {...} ] }
 *
 * Usage:
 *   ./glial webservice importMysqlServerPlain /tmp/mysql_server.json
 *
 * @param array<int,mixed> $param
 * @return void
 */
    public function importMysqlServerPlain($param)
    {
        $this->assertCliRootOnly();

        $filename = $param[0] ?? '';

        if ($filename === '' || !file_exists($filename)) {
            throw new \Exception("PMACTRL-WS-IMPORT-001: JSON file not found: " . $filename);
        }

        $payload = json_decode(file_get_contents($filename), true);
        if (!is_array($payload)) {
            throw new \Exception("PMACTRL-WS-IMPORT-002: Invalid JSON payload in " . $filename);
        }

        $servers = $this->normalizeMysqlServerImportPayload($payload);
        $count   = 0;

        foreach ($servers as $server) {
            if (!is_array($server)) {
                continue;
            }

            $fqdn = !empty($server['hostname']) ? $server['hostname'] : ($server['ip'] ?? '');
            $port = empty($server['port']) ? 3306 : (int) $server['port'];

            $data = array(
                'fqdn'            => $fqdn,
                'ip'              => $server['ip'] ?? '',
                'hostname'        => $server['hostname'] ?? $fqdn,
                'display_name'    => $server['display_name'] ?? '@hostname',
                'login'           => $server['login'] ?? 'root',
                'password'        => $server['passwd'] ?? ($server['password'] ?? ''),
                'port'            => $port,
                'database'        => $server['database'] ?? 'mysql',
                'organization'    => $server['organization'] ?? 'none',
                'environment'     => $server['environment'] ?? 'Production',
                'is_monitored'    => $server['is_monitored'] ?? 1,
                'is_acknowledged' => $server['is_acknowledged'] ?? 0,
                'ssh_port'        => $server['ssh_port'] ?? 22,
                'ssh_login'       => $server['ssh_login'] ?? 'root',
                'ssh_nat'         => $server['ssh_nat'] ?? '',
                'is_proxy'        => $server['is_proxy'] ?? 0,
                'is_vip'          => $server['is_vip'] ?? 0,
            );

            if (empty($data['fqdn']) || empty($data['login']) || $data['password'] === '') {
                throw new \Exception("PMACTRL-WS-IMPORT-003: Missing required fields for one mysql_server entry.");
            }

            Mysql::addMysqlServer($data);
            $count++;
        }

        Mysql::onAddMysqlServer();
        $this->refreshLoadedDbConfiguration();

        echo "Imported " . $count . " mysql_server entries from " . $filename . PHP_EOL;
    }

/**
 * Alias for CLI import:
 *   pmacontrol webservice import /root/monfichier.json
 *
 * @param array<int,mixed> $param
 * @return void
 */
    public function import($param)
    {
        $this->importMysqlServerPlain($param);
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

    protected function assertCliRootOnly()
    {
        if (!IS_CLI) {
            throw new \Exception("PMACTRL-WS-CLI-001: This action is available only in CLI.");
        }

        if ($this->getEffectiveUserId() !== 0) {
            throw new \Exception("PMACTRL-WS-CLI-002: This action must be executed as root.");
        }
    }

    protected function normalizeMysqlServerImportPayload(array $payload): array
    {
        if (isset($payload['mysql']) && is_array($payload['mysql'])) {
            return $payload['mysql'];
        }

        return $payload;
    }

    protected function refreshLoadedDbConfiguration()
    {
        $config = new Config();
        $config->load(CONFIG);
        Sgbd::setConfig($config->get("db"));
    }

    protected function getEffectiveUserId(): int
    {
        if (function_exists('posix_geteuid')) {
            return posix_geteuid();
        }

        return 0;
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
