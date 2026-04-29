<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \App\Library\Debug;
use App\Library\Chiffrement;
use App\Library\Mysql;
use App\Library\System;
//generate UUID avec PHP
//documentation ici : https://github.com/ramsey/uuid
use Ramsey\Uuid\Uuid;
use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use \App\Library\Extraction;
use \App\Library\Param;
use \App\Library\Available;
use App\Library\MysqlServer;
use App\Library\SelectorOptions;
use App\Library\Database\Renamer;
use App\Library\Database\RefreshShellCommand;
use App\Library\Filesystem\SafeDirectory;
use App\Library\Security\CsrfGuard;
use App\Library\Security\GroupedFormRequest;
use App\Library\Security\Identifier;
use App\Library\Security\PositiveIntegerSelection;
use \Glial\I18n\I18n;
use \Glial\Cli\Table;
use \Glial\Synapse\FactoryController;
use Glial\Security\Csrf;

//TODO : metre un  sysème de tab pour éviter d'être perdu

/**
 * Class responsible for database workflows.
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
class Database extends Controller
{
    private const DATABASE_SIZE_UPDATE_CSRF_SCOPE = 'database.size.update';
    private const DATABASE_SIZE_UPDATE_FIELDS = ['label', 'min', 'max', 'color', 'background'];
    private const DATABASE_SIZE_TEXT_FIELD_LIMITS = [
        'label' => 3,
        'color' => 20,
        'background' => 20,
    ];
    private const DATABASE_RENAME_CSRF_SCOPE = 'database.rename';
    private const DATABASE_RENAME_RULES = [
        'id_mysql_server' => ['type' => 'int', 'required' => true, 'min' => 1],
        'database' => ['type' => 'string', 'required' => true, 'min' => 1, 'max' => 64],
        'new_name' => ['type' => 'string', 'required' => true, 'min' => 1, 'max' => 64],
        'adjust_privileges' => ['type' => 'string', 'required' => false, 'max' => 16, 'default' => ''],
    ];
    private const DATABASE_REFRESH_CSRF_SCOPE = 'database.refresh';
    private const DATABASE_REFRESH_RULES = [
        'refresh' => ['type' => 'enum', 'required' => true, 'values' => ['1']],
        'id_mysql_server__from' => ['type' => 'int', 'required' => true, 'min' => 1],
        'id_mysql_server__target' => ['type' => 'int', 'required' => true, 'min' => 1],
        'list' => [
            'type' => 'list',
            'required' => true,
            'min_items' => 1,
            'max_items' => 64,
            'item_type' => 'string',
            'item_min' => 1,
            'item_max' => 64,
            'item_pattern' => Identifier::DATABASE_NAME_PATTERN,
        ],
        'path' => ['type' => 'string', 'required' => true, 'min' => 1, 'max' => 255],
    ];
    private const DATABASE_ANALYZE_CSRF_SCOPE = 'database.analyze';
    private const DATABASE_ANALYZE_RULES = [
        'analyze' => ['type' => 'enum', 'required' => true, 'values' => ['1']],
        'id_mysql_server' => ['type' => 'int', 'required' => true, 'min' => 1],
        'database' => [
            'type' => 'list',
            'required' => true,
            'min_items' => 1,
            'max_items' => 256,
            'item_type' => 'string',
            'item_min' => 1,
            'item_max' => 64,
            'item_pattern' => Identifier::DATABASE_NAME_PATTERN,
        ],
    ];
    private const DATABASE_CREATE_CSRF_SCOPE = 'database.create';
    private const DATABASE_CREATE_RULES = [
        'create' => ['type' => 'enum', 'required' => true, 'values' => ['1']],
        'id_mysql_server' => [
            'type' => 'list',
            'required' => true,
            'min_items' => 1,
            'max_items' => 256,
            'item_type' => 'int',
            'item_min' => 1,
        ],
        'name' => ['type' => 'string', 'required' => true, 'min' => 1, 'max' => 4096],
        'user' => ['type' => 'string', 'default' => '', 'max' => 64],
        'gg' => ['type' => 'enum', 'default' => '@', 'values' => ['@']],
        'hostname' => ['type' => 'string', 'default' => '%', 'min' => 1, 'max' => 255, 'pattern' => Identifier::HOST_PATTERN],
        'id_mysql_privilege' => [
            'type' => 'list',
            'required' => false,
            'min_items' => 1,
            'max_items' => 128,
            'item_type' => 'string',
            'item_min' => 1,
            'item_max' => 25,
            'item_pattern' => Identifier::PRIVILEGE_PATTERN,
        ],
    ];

/**
 * Stores `$log_file` for log file.
 *
 * @var mixed
 * @phpstan-var mixed
 * @psalm-var mixed
 */
    var $log_file = TMP."log/";

/**
 * Render database state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/database/index
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

        $data['menu']['TABLE']['name']  = __("Create database");
        $data['menu']['TABLE']['icone'] = '<i class="fa fa-table"></i>';
        $data['menu']['VIEW']['name']   = __("Rename database");
        $data['menu']['VIEW']['icone']  = '<i class="fa fa-eye"></i>';

        $data['menu']['TRIGGER']['name']  = __("Refresh database");
        $data['menu']['TRIGGER']['icone'] = '<i class="fa fa-random"></i>';

        $data['menu']['FUNCTION']['name']  = __("Compare database");
        $data['menu']['FUNCTION']['icone'] = '<i class="fa fa-cog"></i>';

        $data['menu']['PROCEDURE']['name']  = __("Analyze tables");
        $data['menu']['PROCEDURE']['icone'] = '<i class="fa fa-cogs"></i>';

        $data['menu']['EVENT']['name']  = __("Compare table");
        $data['menu']['EVENT']['icone'] = '<i class="fa fa-calendar"></i>';

        $this->set('data', $data);
    }

/**
 * Create database state through `create`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for create.
 * @phpstan-return void
 * @psalm-return void
 * @see self::create()
 * @example /fr/database/create
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function create()
    {
        $db = Sgbd::sql(DB_DEFAULT);
        $data['database_create_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['database_create_csrf_token'] = Csrf::issueToken($_SESSION, self::DATABASE_CREATE_CSRF_SCOPE);

        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $createRequest = self::evaluateCreateRequest($_POST, $_SERVER, $_SESSION);
            if ($createRequest['status'] !== 200) {
                $this->view = false;
                $this->layout_name = false;
                self::sendDatabaseCreateError($createRequest['status'], $createRequest['body'], $createRequest['headers']);
                return;
            }

            $create = $createRequest['payload'];
            $compte       = array();
            $tmp_password = array();

            $sql = "SELECT a.*,b.key FROM mysql_server a
                    INNER JOIN environment b ON a.`id_environment` = b.id

                 WHERE a.id in(".implode(",", $create['id_mysql_server']).");";
            $res = $db->sql_query($sql);

            while ($ob = $db->sql_fetch_object($res)) {

                $db_remote = Sgbd::sql($ob->name);

                foreach ($create['databases'] as $database) {

                    $sql = "CREATE DATABASE IF NOT EXISTS `".$database."` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
                    $db_remote->sql_query($sql);

//$sql = "set sql_log_bin =0;";
//$db_remote->sql_query($sql);

                    if (empty($create['id_mysql_privilege'])) {

                        if (in_array($ob->key, array("prod", "preprod"))) {
                            $droits = "SELECT, INSERT, UPDATE, DELETE";
                        } else {
                            $droits = "ALL";
                        }
                    } else {
                        $droits = implode(', ', $create['id_mysql_privilege']);
                    }

                    $user = $create['user'] === '' ? $database : $create['user'];
                    $hostname = $create['hostname'];

                    if (empty($tmp_password[$user][$database])) {
                        $password = $this->generatePassword(20);
                    } else {
                        $password = $tmp_password[$user][$database];
                    }

                    $sql = "GRANT ".$droits." ON `".$database."`.* TO '".$user."'@'".$hostname."' IDENTIFIED BY '".$password."'";
                    $db_remote->sql_query($sql);

                    $data['compte'][] = "Server : ".$ob->ip.":".$ob->port." - ".$database.".maria.db.".$ob->key.".wideip - login : ".$user." / password : ".$password." Database : ".$database;
                }
            }
        }

//a déporté dans une librairy ?
        $sql = "SELECT * FROM mysql_privilege ORDER BY `type`, `privilege`";
        $res = Mysql::sqlQueryWithInformationSchemaTablesTimeout($db, $sql, null, __METHOD__);

        $data['mysql_privilege'] = array();
        while ($ob                      = $db->sql_fetch_object($res)) {
            $tmp            = array();
            $tmp['id']      = $ob->privilege;
            $tmp['libelle'] = $ob->privilege;

            $data['mysql_privilege'][] = $tmp;
        }


//fin de la déportation
        $this->set('data', $data);
    }

    public static function evaluateCreateRequest(array $post, array $server, array $session): array
    {
        $request = GroupedFormRequest::evaluate(
            $post,
            $server,
            $session,
            self::DATABASE_CREATE_CSRF_SCOPE,
            'database',
            self::DATABASE_CREATE_RULES,
            'Invalid database create payload'
        );

        if ($request['status'] !== 200) {
            return self::buildDatabaseCreateOutcome($request['status'], $request['body'], $request['headers']);
        }

        $payload = $request['payload'];
        $databases = Identifier::normalizeDatabaseNameList($payload['name']);
        if ($databases === null || !Identifier::isAccountName($payload['user']) || !Identifier::isHostName($payload['hostname'])) {
            return self::buildDatabaseCreateOutcome(400, 'Invalid database create payload');
        }

        foreach ($payload['id_mysql_privilege'] ?? [] as $privilege) {
            if (!Identifier::isPrivilegeName($privilege)) {
                return self::buildDatabaseCreateOutcome(400, 'Invalid database create payload');
            }
        }

        $payload['databases'] = $databases;
        $payload['id_mysql_privilege'] = $payload['id_mysql_privilege'] ?? [];
        unset($payload['name'], $payload['create'], $payload['gg']);

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'payload' => $payload,
        ];
    }

    private static function buildDatabaseCreateOutcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'payload' => null,
        ];
    }

    private static function sendDatabaseCreateError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        header('Content-Type: text/plain; charset=UTF-8');
        echo $message;
    }

/**
 * Handle database state through `generatePassword`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $length Input value for `length`.
 * @phpstan-param mixed $length
 * @psalm-param mixed $length
 * @return mixed Returned value for generatePassword.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::generatePassword()
 * @example /fr/database/generatePassword
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function generatePassword($length = 32)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $count = mb_strlen($chars);

        for ($i = 0, $result = ''; $i < $length; $i++) {
            $index  = rand(0, $count - 1);
            $result .= mb_substr($chars, $index, 1);
        }

        return $result;
    }

/**
 * Handle database state through `refresh`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for refresh.
 * @phpstan-return void
 * @psalm-return void
 * @see self::refresh()
 * @example /fr/database/refresh
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function refresh($param)
    {

//Debug::$debug = true;
        Debug::parseDebug($param);
        $data['database_refresh_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['database_refresh_csrf_token'] = Csrf::issueToken($_SESSION, self::DATABASE_REFRESH_CSRF_SCOPE);

        // Issue #567: pre-select every database returned by the AJAX call
        // EXCEPT the four MySQL system schemas. The user can still uncheck
        // anything; this just makes the common case "refresh all user data"
        // a one-click flow instead of N clicks.
        $systemSchemasJson = json_encode(MysqlServer::SYSTEM_SCHEMAS);
        $this->di['js']->code_javascript('$("#database-id_mysql_server__from").change(function () {
    data = $(this).val();
    $("#database-list").load(GLIAL_LINK+"common/getDatabaseByServer/" + data + "/ajax>true/",
       function(){
        var SYSTEM_SCHEMAS = '.$systemSchemasJson.';
        var preselected = $("#database-list option").map(function () {
            return this.value;
        }).get().filter(function (db) {
            return db !== "" && SYSTEM_SCHEMAS.indexOf(db.toLowerCase()) === -1;
        });
        $("#database-list").selectpicker("refresh");
        $("#database-list").selectpicker("val", preselected);
    });
});
');

        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $refreshRequest = self::evaluateRefreshRequest($_POST, $_SERVER, $_SESSION);
            if ($refreshRequest['status'] !== 200) {
                $this->view = false;
                $this->layout_name = false;
                self::sendDatabaseRefreshError($refreshRequest['status'], $refreshRequest['body'], $refreshRequest['headers']);
                return;
            }

            $refresh = $refreshRequest['payload'];
            $debug = "";
            if (Debug::$debug === true) {
                $debug = "--debug";
            }

            $elems = array(
                $refresh['id_mysql_server__from'],
                $refresh['id_mysql_server__target'],
                implode(',', $refresh['list']),
                $refresh['path'],
                $debug,
            );

            // Issue #570: addRefresh() lazily autoloads vendor classes (e.g.
            // Ramsey\Uuid\UuidFactory). In dev mode (display_errors=1 forced
            // by Bootstrap.php) any deprecation/warning emitted during the
            // autoload would be flushed to the response, causing the
            // header() below to fail with "headers already sent" and stranding
            // the user on /database/refresh instead of /job/index.
            // Buffer the addRefresh() output so any warning is contained.
            ob_start();
            $this->addRefresh($elems);
            ob_end_clean();

            $this->view = false;
            $this->layout_name = false;
            header("location: ".LINK."job/index");
            return;
        }


        $data['listdb1'] = array();
        $this->set('data', $data);
    }

    public static function evaluateRefreshRequest(array $post, array $server, array $session): array
    {
        $request = GroupedFormRequest::evaluate(
            $post,
            $server,
            $session,
            self::DATABASE_REFRESH_CSRF_SCOPE,
            'database',
            self::DATABASE_REFRESH_RULES,
            'Invalid database refresh payload'
        );

        if ($request['status'] !== 200) {
            return self::buildDatabaseRefreshOutcome($request['status'], $request['body'], $request['headers']);
        }

        $payload = $request['payload'];
        foreach ($payload['list'] as $database) {
            if (!Identifier::isDatabaseName($database)) {
                return self::buildDatabaseRefreshOutcome(400, 'Invalid database refresh payload');
            }
        }

        if (!Identifier::isSafeAbsolutePath($payload['path'])) {
            return self::buildDatabaseRefreshOutcome(400, 'Invalid database refresh payload');
        }

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'payload' => $payload,
        ];
    }

    private static function buildDatabaseRefreshOutcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'payload' => null,
        ];
    }

    private static function sendDatabaseRefreshError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $message;
    }

    /*
     * example : ./glial database databaseRefresh  82 83 drupal_home '/mysql/backup'
     *
     *
     */

    public function databaseRefresh($param)
    {

        Debug::parseDebug($param);

        $id_mysql_server__source = PositiveIntegerSelection::normalizeSingle($param[0] ?? null);
        $id_mysql_server__target = PositiveIntegerSelection::normalizeSingle($param[1] ?? null);
        $rawDatabases            = $param[2] ?? null;
        $databases               = is_scalar($rawDatabases)
            ? Identifier::normalizeDatabaseNameList((string) $rawDatabases)
            : null;
        $path                    = $param[3] ?? null;
        $uuid                    = is_scalar($param[4] ?? null) ? trim((string) $param[4]) : '';
        $directory               = SafeDirectory::buildTemporaryChildPath($path, 'pmacontrol-refresh-');

        if (
            $id_mysql_server__source === null
            || $id_mysql_server__target === null
            || $databases === null
            || $directory === null
            || !Uuid::isValid($uuid)
        ) {
            throw new \InvalidArgumentException('Invalid database refresh CLI payload');
        }

        if (count($databases) > 1) {

            $database = "ALL";
        } else {
            $database = end($databases);
        }

        try {
            $this->databaseDump(array($id_mysql_server__source, $database, $directory));

//shell_exec("cd ".$directory." && rename 's///g' ".);

            $metadata = file_get_contents($directory."/metadata");

            echo $metadata."\n";

//Mysql::set_db($db);
//$ob = Mysql::getServerInfo($id_mysql_server__source);
//echo "CHANGE MASTER TO MASTER_HOST='".$ob->ip."', MASTER_PORT=".$ob->port.", MASTER_USER='', MASTER_PORT='',
//    MASTER_LOG_FILE='".gg."', MASTER_LOG_POS=;\n";

            $this->databaseLoad(array($id_mysql_server__target, implode(",", $databases), $directory));

            FactoryController::addNode("Job", "callback", array($uuid), FactoryController::RESULT);
        } finally {
            SafeDirectory::removeTree($directory);
        }

    }
    /*
     * example
     *
     *
     *
     */

    public function databaseDump($param)
    {

        Debug::parseDebug($param);

        $id_mysql_server = PositiveIntegerSelection::normalizeSingle($param[0] ?? null);
        $database        = is_scalar($param[1] ?? null) ? trim((string) $param[1]) : '';
        $path            = is_scalar($param[2] ?? null) ? trim((string) $param[2]) : '';

        if (
            $id_mysql_server === null
            || !Identifier::isSafeAbsolutePath($path)
            || ($database !== 'ALL' && !Identifier::isDatabaseName($database))
        ) {
            throw new \InvalidArgumentException('Invalid database dump CLI payload');
        }

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server WHERE id = ".$id_mysql_server.";";
        $res = Mysql::sqlQueryWithInformationSchemaTablesTimeout($db, $sql, $id_mysql_server, __METHOD__);
        while ($ar  = $db->sql_fetch_object($res)) {
            $ob = $ar;
        }

        $db->sql_close();

        if (!empty($ob)) {
            $password = Chiffrement::decrypt($ob->passwd);
            $cmd = RefreshShellCommand::buildDumpCommand(
                (string) $ob->ip,
                (string) $ob->login,
                (string) $password,
                (int) $ob->port,
                $database,
                $path
            );
            Debug::debug($cmd);

            $msg = shell_exec($cmd);

            echo $msg;

            return true;
        }

        throw new \Exception("PMACTRL-387 : Impossible to find the MySQL server with the id : ".$id_mysql_server);
    }
    /*
     *
     *
     * example :
     */

    public function databaseLoad($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = PositiveIntegerSelection::normalizeSingle($param[0] ?? null);
        $databases       = is_scalar($param[1] ?? null) ? trim((string) $param[1]) : '';
        $path            = is_scalar($param[2] ?? null) ? trim((string) $param[2]) : '';

        if (
            $id_mysql_server === null
            || !Identifier::isSafeAbsolutePath($path)
            || ($databases !== 'ALL' && Identifier::normalizeDatabaseNameList($databases) === null)
        ) {
            throw new \InvalidArgumentException('Invalid database load CLI payload');
        }

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server WHERE id = ".$id_mysql_server.";";

        $res = $db->sql_query($sql);

        while ($ar = $db->sql_fetch_object($res)) {
            $ob = $ar;
        }


        if (!empty($ob)) {

            $db_to_load = Sgbd::sql($ob->name);

            $password = Chiffrement::decrypt($ob->passwd);

            if ($databases != "ALL") {

                $db_to_import = Identifier::normalizeDatabaseNameList($databases);
                $specify_db   = true;
            } else {

                RefreshShellCommand::removeMysqlMetadataFiles($path);

                $specify_db   = false;
                $db_to_import = array('NA');
            }

            foreach ($db_to_import as $db_to_load) {

                $to_dump = "";
                if ($specify_db === true) {

                    if (empty($db_to_load)) {
                        continue;
                    }

                    // Issue #579: must be -s/--source-db (filter dump by source DB),
                    // NOT -B/--database (which renames everything in the dump dir
                    // into a single target and cross-loads other DBs).
                    $to_dump = $db_to_load;
                } else {
                    $to_dump = 'ALL';
                }

                $cmd = RefreshShellCommand::buildLoadCommand(
                    (string) $ob->ip,
                    (string) $ob->login,
                    (string) $password,
                    (int) $ob->port,
                    $to_dump,
                    $path
                );
                Debug::debug($cmd, "cmd");
                $msg = shell_exec($cmd);

                echo $msg;
            }

            return true;
        }

        throw new \Exception("PMACTRL-387 : Impossible to find the MySQL server with the id : ".$id_mysql_server);
    }

/**
 * Handle database state through `rename`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for rename.
 * @phpstan-return void
 * @psalm-return void
 * @see self::rename()
 * @example /fr/database/rename
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function rename($param)
    {

        $this->title = '<i class="fa fa-wpforms" aria-hidden="true"></i> '.__("Rename database");
        $data['database_rename_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['database_rename_csrf_token'] = Csrf::issueToken($_SESSION, self::DATABASE_RENAME_CSRF_SCOPE);
        $this->set("data", $data);

        $this->di['js']->code_javascript('$("#rename-id_mysql_server").change(function () {
    data = $(this).val();
    $("#rename-database").load(GLIAL_LINK+"common/getDatabaseByServer/" + data + "/ajax>true/",
       function(){
	$("#rename-database").selectpicker("refresh");
    });
});');

        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $renameRequest = self::evaluateRenameRequest($_POST, $_SERVER, $_SESSION);
            if ($renameRequest['status'] !== 200) {
                $this->view = false;
                $this->layout_name = false;
                self::sendDatabaseRenameError($renameRequest['status'], $renameRequest['body'], $renameRequest['headers']);
                return;
            }

            $rename = $renameRequest['payload'];
            $nb_renamed = $this->move(array($rename['id_mysql_server'], $rename['database'], $rename['new_name'], $rename['adjust_privileges']));

            header('location: '.LINK.$this->getClass().'/'.__FUNCTION__.'/renamed:tables:'.$nb_renamed);
        }
    }

    public static function evaluateRenameRequest(array $post, array $server, array $session): array
    {
        $request = GroupedFormRequest::evaluate(
            $post,
            $server,
            $session,
            self::DATABASE_RENAME_CSRF_SCOPE,
            'rename',
            self::DATABASE_RENAME_RULES,
            'Invalid database rename payload'
        );

        if ($request['status'] !== 200) {
            return self::buildDatabaseRenameOutcome($request['status'], $request['body'], $request['headers']);
        }

        $payload = $request['payload'];
        if (
            ! self::isSafeDatabaseRenameName($payload['database'])
            || ! self::isSafeDatabaseRenameName($payload['new_name'])
        ) {
            return self::buildDatabaseRenameOutcome(400, 'Invalid database rename payload');
        }

        $payload['adjust_privileges'] = $payload['adjust_privileges'] === '' ? '' : '1';

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'payload' => $payload,
        ];
    }

    private static function isSafeDatabaseRenameName(string $name): bool
    {
        return Identifier::isDatabaseName($name);
    }

    private static function buildDatabaseRenameOutcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'payload' => null,
        ];
    }

    private static function sendDatabaseRenameError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $message;
    }

    /**
     * @author Aurélien LEQUOY <aurelien.lequoy@esysteme.com>
     * @license GNU/GPL
     * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
     * @param string name of connection
     * @return true or false
     * @description rename database and rename all dependencies
     * @access public
     * @example
     * @package PmaControl
     * @since 2.0.20 add option '--force' force migration of obeject if the target database exist
     * @version 1.3
     */
    public function move($param)
    {
        Debug::parseDebug($param);

        $FORCE_TARGET = Param::option($param, "--force");

        if (count($param) < 3) {
            echo "Number of param invalid\n";
            echo "usage : \n";
            echo "pmacontrol database move id_or_display_name old_database_name new_database_name\n";
            exit;
        }

        $id_mysql_server = $param[0];
        $OLD_DB          = $param[1];
        $NEW_DB          = $param[2];
        $AP              = $param[3] ?? "";
        if ($AP === "--force") {
            $AP = "";
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $serverRef = $db->sql_real_escape_string((string) $id_mysql_server);

        $sql = "SELECT * FROM `mysql_server` where `id`='".$serverRef."'"
            ." UNION ALL "
            ."SELECT * FROM `mysql_server` where `display_name`='".$serverRef."'";

        Debug::sql($sql);

        $res = $db->sql_query($sql);

        $nb_renamed = 0;
        while ($ob = $db->sql_fetch_object($res)) {
            $db2 = Sgbd::sql($ob->name);

            $nb_renamed = Renamer::rename($db2, $OLD_DB, $NEW_DB, !empty($AP), $FORCE_TARGET, $id_mysql_server);
        }

        return $nb_renamed;
    }

/**
 * Create database state through `create_trigger`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for create_trigger.
 * @phpstan-return void
 * @psalm-return void
 * @see self::create_trigger()
 * @example /fr/database/create_trigger
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function create_trigger()
    {

        $db = Sgbd::sql(DB_DEFAULT);
        $db->sql_select_db("test");

        $sql = "CREATE DEFINER=`root`@`localhost` FUNCTION `version_patch`() RETURNS tinyint(3) unsigned
    NO SQL
    SQL SECURITY INVOKER
    COMMENT '\n             Description\n             -----------\n\n             Returns the patch release version of MySQL Server.\n\n             Returns\n             -----------\n\n             TINYINT UNSIGNED\n\n             Example\n             -----------\n\n             mysql> SELECT VERSION(), sys.version_patch();\n             +--------------------------------------+---------------------+\n             | VERSION()                            | sys.version_patch() |\n             +--------------------------------------+---------------------+\n             | 5.7.9-enterprise-commercial-advanced | 9                   |\n             +--------------------------------------+---------------------+\n             1 row in set (0.00 sec)\n            '
BEGIN
    RETURN SUBSTRING_INDEX(SUBSTRING_INDEX(VERSION(), '-', 1), '.', -1);
END;";

        $db->sql_multi_query($sql);
    }
    /* move to glial */

    public function dropEmptyDb($link, $dbname)
    {

    }

/**
 * Handle database state through `testu`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for testu.
 * @phpstan-return void
 * @psalm-return void
 * @see self::testu()
 * @example /fr/database/testu
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function testu($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql('hb01_mariaexport01');

        $users = Mysql::exportAllUser($db);

        foreach ($users as $user) {
            $pos = strpos($user, "root");

            if ($pos === false) {
                echo $user.";\n";
            }
        }


        Debug::debug($users);
    }

/**
 * Retrieve database state through `getChangeGrant`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $db_link Input value for `db_link`.
 * @phpstan-param mixed $db_link
 * @psalm-param mixed $db_link
 * @param mixed $OLD_DB Input value for `OLD_DB`.
 * @phpstan-param mixed $OLD_DB
 * @psalm-param mixed $OLD_DB
 * @param mixed $NEW_DB Input value for `NEW_DB`.
 * @phpstan-param mixed $NEW_DB
 * @psalm-param mixed $NEW_DB
 * @return mixed Returned value for getChangeGrant.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getChangeGrant()
 * @example /fr/database/getChangeGrant
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getChangeGrant($db_link, $OLD_DB, $NEW_DB)
    {
        return Renamer::getChangeGrant($db_link, (string) $OLD_DB, (string) $NEW_DB);
    }

/**
 * Create database state through `addRefresh`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for addRefresh.
 * @phpstan-return void
 * @psalm-return void
 * @see self::addRefresh()
 * @example /fr/database/addRefresh
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function addRefresh($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server__source = PositiveIntegerSelection::normalizeSingle($param[0] ?? null);
        $id_mysql_server__target = PositiveIntegerSelection::normalizeSingle($param[1] ?? null);
        $rawDatabases            = $param[2] ?? null;
        $databases               = is_scalar($rawDatabases)
            ? Identifier::normalizeDatabaseNameList((string) $rawDatabases)
            : null;
        $path                    = SafeDirectory::normalizeBaseDirectory($param[3] ?? null);

        if (
            $id_mysql_server__source === null
            || $id_mysql_server__target === null
            || $databases === null
            || $path === null
        ) {
            throw new \InvalidArgumentException('Invalid database refresh job payload');
        }

        $uuid = Uuid::uuid4()->toString();

        $log       = TMP."log/".$this->getClass()."-".__FUNCTION__."-".uniqid().'.log';
        $log_error = TMP."log/".$this->getClass()."-".__FUNCTION__."-".uniqid().'.error.log';

        $cmd = RefreshShellCommand::buildWorkerCommand(
            PHP_BINARY,
            GLIAL_INDEX,
            $this->getClass(),
            $id_mysql_server__source,
            $id_mysql_server__target,
            $databases,
            $path,
            $uuid,
            $log,
            $log_error,
            Debug::$debug === true
        );

        Debug::debug($cmd);

        $pid = trim(shell_exec($cmd));

        Debug::debug($pid, "PID");

        //FactoryController::addNode("fff", "add", array($uuid, $param, $pid, $log, $log_error), FactoryController::RESULT);
        FactoryController::addNode("Job", "add", array($uuid, $param, $pid, $log, $log_error), FactoryController::RESULT);

//unlink($cmd_file);

        if (!System::isRunningPid($pid)) {
            Debug::debug($pid, "The refresh failed");

            if (file_exists($log)) {
                Debug::debug(file_get_contents($log), "Debug");
            }
        } else {
            Debug::debug($pid, "process started !");
        }
    }

/**
 * Handle database state through `analyze`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for analyze.
 * @phpstan-return void
 * @psalm-return void
 * @see self::analyze()
 * @example /fr/database/analyze
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function analyze($param)
    {


        $data['database_analyze_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['database_analyze_csrf_token'] = Csrf::issueToken($_SESSION, self::DATABASE_ANALYZE_CSRF_SCOPE);

        $this->di['js']->code_javascript('$("#analyze-id_mysql_server").change(function () {
    data = $(this).val();
    $("#analyze-database").load(GLIAL_LINK+"common/getDatabaseByServer/" + data + "/ajax>true/",
       function(){
	$("#analyze-database").selectpicker("refresh");
    });
	});');

        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $analyzeRequest = self::evaluateAnalyzeRequest($_POST, $_SERVER, $_SESSION);
            if ($analyzeRequest['status'] !== 200) {
                $this->view = false;
                $this->layout_name = false;
                self::sendDatabaseAnalyzeError($analyzeRequest['status'], $analyzeRequest['body'], $analyzeRequest['headers']);
                return;
            }

            $analyze = $analyzeRequest['payload'];
            $this->updateStats(array($analyze['id_mysql_server'], implode(',', $analyze['database'])));
        }

        $data['listdb1'] = array();
        $this->set('data', $data);
    }

    public static function evaluateAnalyzeRequest(array $post, array $server, array $session): array
    {
        $request = GroupedFormRequest::evaluate(
            $post,
            $server,
            $session,
            self::DATABASE_ANALYZE_CSRF_SCOPE,
            'analyze',
            self::DATABASE_ANALYZE_RULES,
            'Invalid database analyze payload'
        );

        if ($request['status'] !== 200) {
            return self::buildDatabaseAnalyzeOutcome($request['status'], $request['body'], $request['headers']);
        }

        $payload = $request['payload'];
        foreach ($payload['database'] as $database) {
            if (!Identifier::isDatabaseName($database)) {
                return self::buildDatabaseAnalyzeOutcome(400, 'Invalid database analyze payload');
            }
        }

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'payload' => $payload,
        ];
    }

    private static function buildDatabaseAnalyzeOutcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'payload' => null,
        ];
    }

    private static function sendDatabaseAnalyzeError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        header('Content-Type: text/plain; charset=UTF-8');
        echo $message;
    }
    /*
     *
     * UPDATE STATISTICS
     * ANALYZE TABLE `TABLE_SCHEMA`.`TABLE_NAME`;
     *
     *
     */

    public function updateStats($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $all_dbs         = $param[1];
        $db              = Sgbd::sql(DB_DEFAULT);
        $remote          = Mysql::getDbLink($id_mysql_server);

//au cas ou une connexion est deja ouverte avec un autre database (pour prevenir un probleme de conflit avec pmacontrol)
        $res = $remote->sql_query("select database() as db");
        while ($ob  = $remote->sql_fetch_object($res)) {
            $init_db = $ob->db;
        }

        $databases = explode(',', $all_dbs);

        if ($all_dbs === "all" || $all_dbs === "ALL") {
            $sql = "SHOW DATABASES;";
            $res = $remote->sql_query($sql);

            $all_dbs = array();

            while ($ob = $remote->sql_fetch_object($res)) {

                if (in_array($ob->Database, array('information_schema', 'mysql', 'performance_schema'))) {
                    continue;
                }
                $all_dbs[] = $ob->Database;
            }
            $databases = $all_dbs;
        }

        Debug::debug($databases);

        $sqls = array();
        foreach ($databases as $database) {
            $remote->sql_select_db($database);
            $tables = $remote->getListTable()['table'];

            //UPGRADE PIXI
            $sql2    = "SET SQL_LOG_BIN=0;";
            $remote->sql_query($sql2);

            foreach ($tables as $table) {
                // upgrade
                
                $sql    = "ANALYZE TABLE `".$database."`.`".$table."`;";
                $sqls[] = "<li>".$sql."</li>";
                Debug::debug($sql);
                $res    = $remote->sql_query($sql);

                while ($arr = $remote->sql_fetch_array($res, MYSQLI_ASSOC)) {
                    if ($arr['Msg_text'] !== "OK" && $arr['Msg_text'] !== "Table is already up to date") {

                        $sqls[] = "<li>".$sql."</li>";
                        Debug::debug($arr['Msg_text'], "ERROR");
//exit(1);
                    }
                }
            }
        }
        $remote->sql_select_db($init_db);

        if (IS_CLI === false) {

            $extra = "<ul>";
            $extra .= implode('', $sqls);
            $extra .= "</ul>";

            $msg   = I18n::getTranslation(__("Analyze made :").$extra);
            $title = I18n::getTranslation(__("Success"));
            set_flash("success", $title, $msg);
            header('location:'.LINK.'database/index');
        }
    }

/**
 * Handle database state through `compare`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for compare.
 * @phpstan-return void
 * @psalm-return void
 * @see self::compare()
 * @example /fr/database/compare
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function compare($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $dataRequest = self::evaluateDataRequest($_GET, $_SERVER);
        if ($dataRequest['status'] !== 200) {
            $this->view = false;
            $this->layout_name = false;
            self::sendDatabaseDataError($dataRequest['status'], $dataRequest['body'], $dataRequest['headers']);
            return;
        }

        $selection = $dataRequest['selection'];
        self::applyDataSelectionToGet($selection);
//134217728
//375394272


        $this->di['js']->addJavascript(array("jquery-latest.min.js", "jquery.browser.min.js",
            "jquery.autocomplete.min.js", "bootstrap-select.min.js", "compare/index.js"));

        $sql     = "SELECT * FROM mysql_server WHERE `id` in(".Available::getMySQL().") order by `name`";
        $servers = $db->sql_fetch_yield($sql);

        $data['server'] = [];
        foreach ($servers as $server) {
            $tmp              = [];
            $tmp['id']        = $server['id'];
            $tmp['libelle']   = str_replace('_', '-', $server['name'])." (".$server['ip'].")";
            $data['server'][] = $tmp;
        }

        $data['listdb1'] = array();
        if ($selection['id_mysql_server__original'] !== null) {
            $select1         = $this->getDatabaseByServer(array($selection['id_mysql_server__original']));
            $data['listdb1'] = $select1['databases'];
        }

        $data['listdb2'] = array();
        if ($selection['id_mysql_server__compare'] !== null) {
            $select1         = $this->getDatabaseByServer(array($selection['id_mysql_server__compare']));
            $data['listdb2'] = $select1['databases'];
        }


        $data['display'] = false;

        if (count($data['listdb2']) != 0 && count($data['listdb1']) != 0) {
            if ($selection['database__original'] !== null && $selection['database__compare'] !== null) {

                $id_mysql_server_a = $selection['id_mysql_server__original'];
                $database_a        = $selection['database__original'];
                $id_mysql_server_b = $selection['id_mysql_server__compare'];
                $database_b        = $selection['database__compare'];

                $data['resultat'] = $this->analyse(array($id_mysql_server_a, $database_a, $id_mysql_server_b, $database_b));

                $data['display'] = true;

//log
//$this->di['log']->warning('[Compare] '.$_GET['compare_main']['id_mysql_server__original'].":".$_GET['compare_main']['database__original']." vs ".
//    $_GET['compare_main']['id_mysql_server__compare'].":".$_GET['compare_main']['database__compare']."(".$_SERVER["REMOTE_ADDR"].")");
            }
        }

        $this->set('data', $data);
    }

/**
 * Handle database state through `analyse`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for analyse.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::analyse()
 * @example /fr/database/analyse
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function analyse($param)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $id_mysql_server_a = $param[0];
        $database_a        = $param[1];
        $id_mysql_server_b = $param[2];
        $database_b        = $param[3];

        $db_a = Mysql::getDbLink($id_mysql_server_a);
        $db_b = Mysql::getDbLink($id_mysql_server_b);

        $ob_a          = $db_a->sql_fetch_object($db_a->sql_query("SELECT database() as db"));
        $db_name_a_ori = $ob_a->db;

        $ob_b          = $db_b->sql_fetch_object($db_b->sql_query("SELECT database() as db"));
        $db_name_b_ori = $ob_b->db;

        $db_a->sql_select_db($database_a);
        $db_b->sql_select_db($database_b);

        $objects = array("TABLE", "VIEW", "TRIGGER", "FUNCTION", "PROCEDURE", "EVENT");

        $result_a = array();
        $result_b = array();

        foreach ($objects as $object) {
            $data_a[$object]   = Mysql::getListObject($db_a, $database_a, $object, $id_mysql_server_a);
            $result_a[$object] = Mysql::getStructure($db_a, $database_a, $data_a[$object], $object);

            $data_b[$object]   = Mysql::getListObject($db_b, $database_b, $object, $id_mysql_server_b);
            $result_b[$object] = Mysql::getStructure($db_b, $database_b, $data_b[$object], $object);

            $data_a[$object] = array_flip($data_a[$object]);
            $data_b[$object] = array_flip($data_b[$object]);

            ksort($data_a[$object]);
            ksort($data_b[$object]);
        }

        $all_objects = array_merge_recursive($data_a, $data_b);

        $data = array();

        foreach ($all_objects as $type_object => $elems) {
            foreach ($elems as $elem => $order) {

//remplir à vide si jamais un élément s n'est pas définis d'un coté ou de l'autre
                $result_a[$type_object][$elem] = $result_a[$type_object][$elem] ?? "";
                $result_b[$type_object][$elem] = $result_b[$type_object][$elem] ?? "";

//on transforme les retour chariot windows en retour chariot linux
                $res_a = str_replace("\r\n", "\n", $result_a[$type_object][$elem]);
                $res_b = str_replace("\r\n", "\n", $result_b[$type_object][$elem]);

//pour retirer les commentaires SQL dans les function et les procedure
                if (in_array($type_object, array('FUNCTION', 'PROCEDURE'))) {
                    $res_a = preg_replace('/(--[^\r\n]*)|(\#[^\r\n]*)|(\/\*[\w\W]*?(?=\*\/)\*\/)/ms', '', $res_a);
                    $res_b = preg_replace('/(--[^\r\n]*)|(\#[^\r\n]*)|(\/\*[\w\W]*?(?=\*\/)\*\/)/ms', '', $res_b);
                }

//et on convertit en utf8, car on peu avoir des caractère accentuer en latin1 qui vont faire crasher le diff
//$res_a = utf8_encode($res_a);
//$res_b = utf8_encode($res_b);
// On met en forme les vue pour faire apparaitre les differences (sur plusieurs lignes au lieu d'une seule)
                if ($type_object === 'VIEW') {
                    $res_a = \SqlFormatter::format($res_a, false);
                    $res_b = \SqlFormatter::format($res_b, false);
                }

                if ($res_a !== $res_b) {
                    $data[$type_object][$elem][0] = trim($res_a);
                    $data[$type_object][$elem][1] = trim($res_b);
                }
            }
        }

        $db_a->sql_select_db($db_name_a_ori);
        $db_b->sql_select_db($db_name_b_ori);

        return $data;
    }

/**
 * Prepare database state through `before`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for before.
 * @phpstan-return void
 * @psalm-return void
 * @see self::before()
 * @example /fr/database/before
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function before($param)
    {
        Debug::parseDebug($param);
    }

/**
 * Handle database state through `checkConfig`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param int $id_server1 Input value for `id_server1`.
 * @phpstan-param int $id_server1
 * @psalm-param int $id_server1
 * @param mixed $db1 Input value for `db1`.
 * @phpstan-param mixed $db1
 * @psalm-param mixed $db1
 * @param int $id_server2 Input value for `id_server2`.
 * @phpstan-param int $id_server2
 * @psalm-param int $id_server2
 * @param mixed $db2 Input value for `db2`.
 * @phpstan-param mixed $db2
 * @psalm-param mixed $db2
 * @return mixed Returned value for checkConfig.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::checkConfig()
 * @example /fr/database/checkConfig
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function checkConfig($id_server1, $db1, $id_server2, $db2)
    {
        $db    = Sgbd::sql(DB_DEFAULT);
        $error = array();

        $sql = "SELECT id,name FROM mysql_server WHERE id = '".$db->sql_real_escape_string($id_server1)."';";
        $res = Mysql::sqlQueryWithInformationSchemaTablesTimeout($db, $sql, $id_mysql_server, __METHOD__);
        if ($db->sql_num_rows($res) == 1) {
            while ($ob = $db->sql_fetch_object($res)) {
                $db_name_ori = $ob->name;
            }
        } else {
            $error[] = "The server original is unknow";
            unset($_GET['compare_main']['id_mysql_server__original']);
        }

        $sql  = "SELECT id,name FROM mysql_server WHERE id = '".$db->sql_real_escape_string($id_server2)."';";
        $res2 = $db->sql_query($sql);

        if ($db->sql_num_rows($res2) == 1) {
            while ($ob = $db->sql_fetch_object($res2)) {
                $db_name_cmp = $ob->name;
            }
        } else {
            $error[] = "The server to compare is unknow";
            unset($_GET['compare_main']['id_mysql_server__compare']);
        }

        if (count($error) !== 0) {
            return $error;
        }

        $db_ori = Sgbd::sql($db_name_ori);
        $sql    = "select count(1) as cpt from information_schema.SCHEMATA where SCHEMA_NAME = '".$db_ori->sql_real_escape_string($db1)."';";
        $res3   = $db_ori->sql_query($sql);
        $ob     = $db_ori->sql_fetch_object($res3);
        if ($ob->cpt != 1) {
            $error[] = "The database '".$db1."' original doesn't exist on server original : '".$db_name_ori."'";
        }

        $db_cmp = Sgbd::sql($db_name_cmp);
        $sql    = "select count(1) as cpt from information_schema.SCHEMATA where SCHEMA_NAME = '".$db_cmp->sql_real_escape_string($db2)."';";
        $res4   = $db_cmp->sql_query($sql);
        $ob     = $db_cmp->sql_fetch_object($res4);
        if ($ob->cpt != 1) {
            $error[] = "The database '".$db2."' original doesn't exist on server original : '".$db_name_cmp."'";
        }

        if ($id_server1 == $id_server2 && $db1 == $db2) {
            $error[] = "The databases to compare cannot be the same on same server";
        }

        if (count($error) === 0) {
            return true;
        } else {
            return $error;
        }
    }
    /*
     * used for load database from get have to delete it and find a better solution
     *
     *
     */

    function getDatabaseByServer($param)
    {
        if (IS_AJAX) {
            $this->layout_name = false;
        }

        $id_mysql_server = $param[0];

        $db_to_get_db = Mysql::getDbLink($id_mysql_server);

        $data['databases'] = SelectorOptions::databaseNamesFromConnection($db_to_get_db);

        $this->set("data", $data);
        return $data;
    }

/**
 * Handle database state through `show`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for show.
 * @phpstan-return void
 * @psalm-return void
 * @see self::show()
 * @example /fr/database/show
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function show($param)
    {

        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT b.*,a.display_name FROM mysql_server a
        INNER JOIN mysql_database b ON a.id = b.id_mysql_server WHERE is_proxy = 0 and schema_name !='';";

        $res = $db->sql_query($sql);

        $data['database'] = array();
        while($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC))
        {
            $data['database'][] = $arr;
        }

        $this->set("data", $data);
    }

/**
 * Handle database state through `size`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for size.
 * @phpstan-return void
 * @psalm-return void
 * @see self::size()
 * @example /fr/database/size
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function size($param)
    {
        $this->di['js']->addJavascript(array('bootstrap-editable.min.js', 'Tree/index.js'));

        $db  = Sgbd::sql(DB_DEFAULT);
        $res = $db->sql_query("SELECT * FROM database_size order by `min`;");

        $data['color'] = array();
        while ($ob            = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['color'][] = $ob;
        }

        $data['database_size_update_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['database_size_update_csrf_token'] = Csrf::issueToken($_SESSION, self::DATABASE_SIZE_UPDATE_CSRF_SCOPE);

        $this->set("data", $data);
    }

    public function sizeUpdate(): void
    {
        $this->view = false;
        $this->layout_name = false;

        $outcome = self::evaluateSizeUpdateRequest($_POST, $_SERVER, $_SESSION);
        if ($outcome['status'] !== 200) {
            self::sendDatabaseSizeUpdateError($outcome['status'], $outcome['body'], $outcome['headers']);
            return;
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = self::buildDatabaseSizeUpdateSql($outcome['update'], [$db, 'sql_real_escape_string']);
        $db->sql_query($sql);

        if ($db->sql_affected_rows() === 1) {
            echo "OK";
        } else {
            self::sendDatabaseSizeUpdateError(503, "Database size not updated");
        }
    }

    public static function evaluateSizeUpdateRequest(array $post, array $server, array $session): array
    {
        $guard = CsrfGuard::check($post, $server, $session, self::DATABASE_SIZE_UPDATE_CSRF_SCOPE);
        if (!$guard['allowed']) {
            return self::buildDatabaseSizeUpdateOutcome($guard['status'], $guard['body'], $guard['headers']);
        }

        $update = self::normalizeSizeUpdatePayload($post);
        if ($update === null) {
            return self::buildDatabaseSizeUpdateOutcome(400, "Invalid database size update payload");
        }

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'update' => $update,
        ];
    }

    public static function normalizeSizeUpdatePayload(array $post): ?array
    {
        if (
            ! array_key_exists('name', $post)
            || ! array_key_exists('pk', $post)
            || ! array_key_exists('value', $post)
            || ! is_scalar($post['name'])
            || ! is_scalar($post['pk'])
            || ! is_scalar($post['value'])
        ) {
            return null;
        }

        $field = (string) $post['name'];
        if (! in_array($field, self::DATABASE_SIZE_UPDATE_FIELDS, true)) {
            return null;
        }

        $id = self::normalizeUnsignedInteger((string) $post['pk']);
        if ($id === null || $id < 1) {
            return null;
        }

        if ($field === 'min' || $field === 'max') {
            $value = self::normalizeDatabaseSizeBytes((string) $post['value']);
            if ($value === null) {
                return null;
            }
        } else {
            $value = self::normalizeDatabaseSizeTextField($field, $post['value']);
            if ($value === null) {
                return null;
            }
        }

        return [
            'field' => $field,
            'value' => $value,
            'id' => $id,
        ];
    }

    public static function buildDatabaseSizeUpdateSql(array $update, callable $escape): string
    {
        $value = $update['value'];
        if (in_array($update['field'], ['min', 'max'], true)) {
            $sqlValue = (string) $value;
        } else {
            $sqlValue = "'" . $escape((string) $value) . "'";
        }

        return sprintf(
            "UPDATE database_size SET `%s` = %s WHERE id = %d",
            $update['field'],
            $sqlValue,
            $update['id']
        );
    }

    public static function normalizeDatabaseSizeBytes(string $value): ?int
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $integer = self::normalizeUnsignedInteger($value);
        if ($integer !== null) {
            return $integer;
        }

        if (! preg_match('/^([0-9]+(?:\.[0-9]+)?)\s*([KMGTPE])$/i', $value, $matches)) {
            return null;
        }

        $units = ['K' => 1, 'M' => 2, 'G' => 3, 'T' => 4, 'P' => 5, 'E' => 6];
        $number = (float) $matches[1];
        $unit = strtoupper($matches[2]);
        $bytes = $number * (1024 ** $units[$unit]);

        if (! is_finite($bytes) || $bytes < 0 || $bytes > PHP_INT_MAX) {
            return null;
        }

        return (int) round($bytes);
    }

    private static function normalizeUnsignedInteger(string $value): ?int
    {
        $value = trim($value);
        if ($value === '' || ! ctype_digit($value)) {
            return null;
        }

        $normalized = ltrim($value, '0');
        if ($normalized === '') {
            return 0;
        }

        $max = (string) PHP_INT_MAX;
        if (strlen($normalized) > strlen($max) || (strlen($normalized) === strlen($max) && strcmp($normalized, $max) > 0)) {
            return null;
        }

        return (int) $normalized;
    }

    private static function normalizeDatabaseSizeTextField(string $field, $value): ?string
    {
        if (! is_scalar($value) || ! isset(self::DATABASE_SIZE_TEXT_FIELD_LIMITS[$field])) {
            return null;
        }

        $text = trim((string) $value);
        if ($text === '' || strlen($text) > self::DATABASE_SIZE_TEXT_FIELD_LIMITS[$field]) {
            return null;
        }

        return $text;
    }

    private static function buildDatabaseSizeUpdateOutcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'update' => null,
        ];
    }

    private static function sendDatabaseSizeUpdateError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        header('Content-Type: text/plain; charset=UTF-8');
        echo $message;
    }

/**
 * Handle database state through `data`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for data.
 * @phpstan-return void
 * @psalm-return void
 * @see self::data()
 * @example /fr/database/data
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function data($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);
        $dataRequest = self::evaluateDataRequest($_GET, $_SERVER);
        if ($dataRequest['status'] !== 200) {
            $this->view = false;
            $this->layout_name = false;
            self::sendDatabaseDataError($dataRequest['status'], $dataRequest['body'], $dataRequest['headers']);
            return;
        }

        $selection = $dataRequest['selection'];
        self::applyDataSelectionToGet($selection);
//134217728
//375394272


        $this->di['js']->addJavascript(array("jquery-latest.min.js", "jquery.browser.min.js",
            "jquery.autocomplete.min.js", "bootstrap-select.min.js", "database/data.js"));

        $sql     = "SELECT * FROM mysql_server WHERE `id` in(".Available::getMySQL().") order by `name`";
        $servers = $db->sql_fetch_yield($sql);

        $data['server'] = [];
        foreach ($servers as $server) {
            $tmp              = [];
            $tmp['id']        = $server['id'];
            $tmp['libelle']   = str_replace('_', '-', $server['name'])." (".$server['ip'].")";
            $data['server'][] = $tmp;
        }

        $data['listdb1'] = array();
        if ($selection['id_mysql_server__original'] !== null) {
            $select1         = $this->getDatabaseByServer(array($selection['id_mysql_server__original']));
            $data['listdb1'] = $select1['databases'];
        }

        $data['listdb2'] = array();
        if ($selection['id_mysql_server__compare'] !== null) {
            $select1         = $this->getDatabaseByServer(array($selection['id_mysql_server__compare']));
            $data['listdb2'] = $select1['databases'];
        }


        $data['display'] = false;

        if (count($data['listdb2']) != 0 && count($data['listdb1']) != 0) {
            if ($selection['database__original'] !== null && $selection['database__compare'] !== null) {

                $id_mysql_server_a = $selection['id_mysql_server__original'];
                $database_a        = $selection['database__original'];
                $id_mysql_server_b = $selection['id_mysql_server__compare'];
                $database_b        = $selection['database__compare'];

                $data['resultat'] = $this->analyse(array($id_mysql_server_a, $database_a, $id_mysql_server_b, $database_b));

                $data['display'] = true;

//log
//$this->di['log']->warning('[Compare] '.$_GET['compare_main']['id_mysql_server__original'].":".$_GET['compare_main']['database__original']." vs ".
//    $_GET['compare_main']['id_mysql_server__compare'].":".$_GET['compare_main']['database__compare']."(".$_SERVER["REMOTE_ADDR"].")");
            }
        }

        $this->set('data', $data);
    }

    public static function evaluateDataRequest(array $get, array $server): array
    {
        $method = strtoupper((string) ($server['REQUEST_METHOD'] ?? 'GET'));
        if ($method !== 'GET' && $method !== 'HEAD') {
            return self::buildDatabaseDataOutcome(405, 'Method Not Allowed', ['Allow' => 'GET, HEAD']);
        }

        $selection = self::normalizeDataSelection($get);
        if ($selection === null) {
            return self::buildDatabaseDataOutcome(400, 'Invalid database data selection');
        }

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'selection' => $selection,
        ];
    }

    public static function normalizeDataSelection(array $get): ?array
    {
        $selection = [
            'id_mysql_server__original' => null,
            'id_mysql_server__compare' => null,
            'database__original' => null,
            'database__compare' => null,
        ];

        if (!isset($get['compare_main'])) {
            return $selection;
        }
        if (!is_array($get['compare_main'])) {
            return null;
        }

        foreach ($get['compare_main'] as $field => $value) {
            if (!is_string($field) || !array_key_exists($field, $selection) || !is_scalar($value)) {
                return null;
            }

            $text = trim((string) $value);
            if ($text === '') {
                continue;
            }

            if ($field === 'id_mysql_server__original' || $field === 'id_mysql_server__compare') {
                if (!ctype_digit($text) || (int) $text < 1) {
                    return null;
                }

                $selection[$field] = (int) $text;
                continue;
            }

            if (!Identifier::isDatabaseName($text)) {
                return null;
            }

            $selection[$field] = $text;
        }

        return $selection;
    }

    private static function applyDataSelectionToGet(array $selection): void
    {
        $compareMain = [];
        foreach ($selection as $field => $value) {
            if ($value !== null) {
                $compareMain[$field] = (string) $value;
            }
        }

        if ($compareMain === []) {
            unset($_GET['compare_main']);
            return;
        }

        // Form::select reads $_GET directly to restore selected values in this legacy view.
        $_GET['compare_main'] = $compareMain;
    }

    private static function buildDatabaseDataOutcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'selection' => null,
        ];
    }

    private static function sendDatabaseDataError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        header('Content-Type: text/plain; charset=UTF-8');
        echo $message;
    }

/**
 * Handle database state through `dataCompate`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for dataCompate.
 * @phpstan-return void
 * @psalm-return void
 * @see self::dataCompate()
 * @example /fr/database/dataCompate
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function dataCompate($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server__ori = $param[0];
        $database__ori        = $param[1];
        $table__ori           = $param[2];
        $id_mysql_server__cmp = $param[3];
        $database__cmp        = $param[4];
        $table__cmp           = $param[5];

        $db = Mysql::getDbLink($id_mysql_server__ori);

        $table1 = $this->getTableInfo(array($id_mysql_server__ori, $database__ori, $table__ori));
        $table2 = $this->getTableInfo(array($id_mysql_server__cmp, $database__cmp, $table__cmp));

        $ret = array();

        $ret['count_table_1'] = $table1['count'];
        $ret['count_table_2'] = $table2['count'];

        // data identique des 2 coté !
        $sql = "SELECT COUNT(1) as cpt FROM `".$database__ori."`.`".$table__ori."` a
                INNER JOIN `".$database__cmp."`.`".$table__cmp."` b ON ";

        $sql .= " 1=1 ";

        foreach ($table1['column_name'] as $column_name) {
            $sql .= " AND a.`".$column_name."` = b.`".$column_name."`";
        }
        $sql .= ";\n";
        //Debug::sql($sql);
        $res = $db->sql_query($sql);
        while ($ob  = $db->sql_fetch_object($res)) {
            $ret['identical'] = $ob->cpt;
        }

        //
        $sql2 = "SELECT count(1) as cpt from `".$database__ori."`.`".$table__ori."` a
LEFT JOIN `".$database__cmp."`.`".$table__cmp."` b ON 1=1";

        Debug::debug($table1['primary_key'], "pk");

        foreach ($table1['primary_key'] as $pk) {

            $primary_key = $pk;
            $sql2        .= " AND a.`".$pk."` = b.`".$pk."`";
        }

        $sql2 .= " WHERE b.`".$primary_key."` IS NULL;";

        $res2 = $db->sql_query($sql2);
        while ($ob2  = $db->sql_fetch_object($res2)) {
            $ret['only in table 1'] = $ob2->cpt;
        }

        Debug::sql($sql2);
        //
        $sql3 = "SELECT count(1) as cpt from `".$database__cmp."`.`".$table__cmp."` b
LEFT JOIN `".$database__ori."`.`".$table__ori."` a ON 1=1";

        foreach ($table1['primary_key'] as $pk) {

            $primary_key = $pk;
            $sql3        .= " AND a.`".$pk."` = b.`".$pk."`";
        }

        $sql3 .= " WHERE a.`".$primary_key."` IS NULL;";

        $res3 = $db->sql_query($sql3);
        while ($ob3  = $db->sql_fetch_object($res3)) {
            $ret['only in table 2'] = $ob3->cpt;
        }

        //Debug::sql($sql3);
        //line missing
        $sql4 = "WITH pk as (SELECT * from  (
            SELECT a.`".implode("`,a.`", $table1['primary_key'])."`, 'a' as `from` FROM `".$database__ori."`.`".$table__ori."` a
                UNION ALL
            SELECT b.`".implode("`,b.`", $table1['primary_key'])."`, 'b' as `from` FROM `".$database__cmp."`.`".$table__cmp."` b
                ) z
                GROUP BY ".implode(",", $table1['primary_key'])." HAVING count(1) = 1)
                  SELECT d.*, '1' as `from` FROM pk c
                  INNER JOIN `".$database__cmp."`.`".$table__cmp."` d ON 1=1";

        foreach ($table1['primary_key'] as $pk) {

            $primary_key = $pk;
            $sql4        .= " AND c.`".$pk."` = d.`".$pk."`";
        }

        $sql4 .= "   UNION ALL
                  SELECT e.*, '2' as `from` FROM pk c INNER JOIN `".$database__ori."`.`".$table__ori."` e ON 1=1";
        foreach ($table1['primary_key'] as $pk) {

            $primary_key = $pk;
            $sql4        .= " AND c.`".$pk."` = e.`".$pk."`";
        }

        $sql4 .= ";";

        //Debug::sql($sql4);

        $table = new Table(2);
        $table->addHeader(array_merge($table1['column_name'], array("from")));

        $res4 = $db->sql_query($sql4);

        $ret['line_missing'] = array();
        while ($arr4                = $db->sql_fetch_array($res4, MYSQLI_ASSOC)) {

            $table->addLine($arr4);
            //$ret['line_missing'][] = $arr4;
        }


        // data differents

        $sql5 = "WITH `all` as (SELECT a.`".implode("`,a.`", $table1['primary_key'])."` FROM `".$database__ori."`.`".$table__ori."` a
                INNER JOIN `".$database__cmp."`.`".$table__cmp."` b ON ";

        $sql5 .= " 1=1 ";

        foreach ($table1['primary_key'] as $pk) {
            $sql5 .= " AND a.`".$pk."` = b.`".$pk."`";
        }

        $sql5 .= " WHERE ";

        $tmp5 = array();
        foreach ($table1['column_name'] as $column_name) {

            //on retire les champs de la PK
            if (in_array($column_name, $table1['primary_key'])) {
                continue;
            }

            $tmp5[] = " a.`".$column_name."` !=  b.`".$column_name."`";
        }

        $sql5 .= implode(" OR ", $tmp5);

        $sql5 .= ")
        SELECT y.*,z.* FROM `all` as x
        INNER JOIN `".$database__ori."`.`".$table__ori."` y ON 1=1";
        foreach ($table1['primary_key'] as $pk) {
            $primary_key = $pk;
            $sql5        .= " AND x.`".$pk."` = y.`".$pk."`";
        }
        $sql5 .= "\nINNER JOIN `".$database__cmp."`.`".$table__cmp."` z ON 1=1";
        foreach ($table1['primary_key'] as $pk) {
            $primary_key = $pk;
            $sql5        .= " AND x.`".$pk."` = z.`".$pk."`";
        }


        Debug::sql($sql5);

        $middle = count($table1['column_name']) / 2;

        $key_pos = array_flip($table1['column_name']);

        foreach ($table1['primary_key'] as $pk) {
            if (!empty($key_pos[$pk])) {

            } else {
                echo "Impossible to find PK";
                exit;
            }
        }



        Debug::debug($gggg, "PKKKKKKKKKKKKKKKKKKKK");

        $res5 = $db->sql_query($sql5);
        while ($arr5 = $db->sql_fetch_array($res5, MYSQLI_NUM)) {



            for ($i = 0; $i <= $middle; $i++) {
                if ($arr5[$i] !== $arr5[($i + $middle)]) {

                }
            }

            //$ret['diff'][] = $ob5;
        }





        //echo $table->display();
        Debug::debug($ret
        );
    }

/**
 * Retrieve database state through `getTableInfo`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for getTableInfo.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getTableInfo()
 * @example /fr/database/getTableInfo
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getTableInfo($param)
    {

        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $database        = $param[1];
        $table           = $param[2];

        $ret['table_schema'] = $database;
        $ret['table_name']   = $table;

        $db = Mysql::getDbLink($id_mysql_server);

        $sql = "SELECT COUNT(*) as cpt FROM information_schema.tables WHERE table_schema = '".$database."' AND table_name = '".$table."';
            ";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            if ($ob->cpt !== "1") {
                Debug::debug($ob->cpt, '[ERROR] : Should be equal to 1, found table :');
                return false;
            }
        }

        $sql2 = "SELECT COLUMN_NAME as column_name FROM INFORMATION_SCHEMA.COLUMNS "
            ."WHERE TABLE_SCHEMA = '".$database."' AND TABLE_NAME = '".$table."' ORDER BY ORDINAL_POSITION;
            ";
        $res2 = $db->sql_query($sql2);

        while ($ob2 = $db->sql_fetch_object($res2)) {
            $ret['column_name'][] = $ob2->column_name;
        }

        $ret['primary_key'] = $this->getPrimaryKey($param);

        $ret['number_fields'] = count($ret['column_name']);

        $sql3 = "SELECT count(1) as cpt from `".$database."`.`".$table."`;
            ";
        $res3 = $db->sql_query($sql3);
        while ($ob3  = $db->sql_fetch_object($res3)) {
            $ret['count'] = $ob3->cpt;
        }



        Debug::debug($ret, "[TABLE]");

        return $ret;
    }

/**
 * Retrieve database state through `getPrimaryKey`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for getPrimaryKey.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getPrimaryKey()
 * @example /fr/database/getPrimaryKey
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getPrimaryKey($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $database        = $param[1];
        $table           = $param[2];

        $db = Mysql::getDbLink($id_mysql_server);

        $sql = "SHOW INDEX FROM `".$database."`.`".$table."` WHERE `Key_name` = 'PRIMARY';";
        $res = $db->sql_query($sql);

        if ($db->sql_num_rows($res) == "0") {
            return false;
            //throw new \Exception("DATA-COPARE-067 : this table '".$table."' haven't primary key !");
        } else {

            $primary_key = array();

            while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $primary_key[] = $arr['Column_name'];
            }
        }

        return $primary_key;
    }

    public function empty($param)
    {
        Debug::parseDebug($param);
        $data = array();

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT id,display_name FROM mysql_server";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $data['empty'][$ob->display_name] = Mysql::getEmptyDatabase(array($ob->id));
        }

        $this->set('data', $data);
    }

/**
 * Retrieve database state through `getObject`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for getObject.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @see self::getObject()
 * @example /fr/database/getObject
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getObject($param)
    {

        Debug::parseDebug($param);

        if (count($param) < 2) {
            echo "Number of param invalid\n";
            echo "usage : \n";
            echo "pmacontrol database getObject id_mysql_server database\n";
            exit;
        }

        $id_mysql_server = $param[0];
        $database        = $param[1];

        $db = Sgbd::sql(DB_DEFAULT, "dhgsrht");
        $serverRef = $db->sql_real_escape_string((string) $id_mysql_server);

        $sql = "SELECT * FROM `mysql_server` where `id`='".$serverRef."'"
            ." UNION ALL "
            ."SELECT * FROM `mysql_server` where `display_name`='".$serverRef."'";

        $res = $db->sql_query($sql);

        $db2 = null;
        while ($ob = $db->sql_fetch_object($res)) {
            $db2 = Sgbd::sql($ob->name);
        }

        if ($db2 === null) {
            throw new \Exception("Impossible to find the mysql server '".$id_mysql_server."'", 4576);
        }

        $data = Renamer::getObjectCounts($db2, (string) $database, $id_mysql_server);
        Debug::debug($data);

        return $data;
    }

/**
 * Handle database state through `dot`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for dot.
 * @phpstan-return void
 * @psalm-return void
 * @see self::dot()
 * @example /fr/database/dot
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function dot($param)
    {
        Debug::parseDebug($param);

        $data['database'] = Extraction::display(array("databases::databases", "variables::is_proxysql", "master::binlog_do_db", "master::binlog_ignore_db"));

        Debug::debug($data);

        foreach ($data['database'] as $id_mysql_server => $elems) {
            foreach ($elems as $databases) {
                if ($databases['is_proxysql'] === "1") {
                    continue;
                }


                $binlog_do_db     = explode(",", $databases['binlog_do_db']);
                $binlog_ignore_db = explode(",", $databases['binlog_ignore_db']);

                $dbs = json_decode($databases['databases'], true);

                //Debug::debug($dbs);

                foreach ($dbs as $schema => $db_attr) {

                    $DB[$id_mysql_server][$schema]['M'] = '-';
                    if (in_array($schema, $binlog_do_db)) {
                        $DB[$id_mysql_server][$schema]['M'] = 'V';
                    }

                    if (in_array($schema, $binlog_ignore_db)) {
                        $DB[$id_mysql_server][$schema]['M'] = 'X';
                    }


                    /*
                      foreach ($db_attr['engine'] as $engine => $row_formats) {
                      foreach ($row_formats as $row_format => $details) {




                      $total_data[]  = $details['size_data'];
                      $total_index[] = $details['size_index'];
                      $total_free[]  = $details['size_free'];
                      $total_table[] = $details['tables'];
                      $total_row[]   = $details['rows'];
                      }
                      } */
                }
            }
        }

        Debug::debug($DB, 'DB');
    }
}
