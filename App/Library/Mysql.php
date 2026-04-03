<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use App\Library\Extraction;
use App\Library\System;
use App\Library\Tag;
use \Glial\Security\Crypt\Crypt;
use \Glial\Sgbd\Sgbd;
use \App\Library\Debug;
use App\Controller\Dot3;

/**
 * Class responsible for mysql workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Mysql
{
    public const INFORMATION_SCHEMA_TABLES_TIMEOUT_SECONDS = 10;

    public static function sqlQuerySilentCompat($db, $sql, $table = "", $type = "")
    {
        if (method_exists($db, 'sql_query_silent')) {
            return $db->sql_query_silent($sql, $table, $type);
        }

        try {
            return $db->sql_query($sql, $table, $type);
        } catch (\Throwable $e) {
            return false;
        }
    }
/**
 * Stores `$master` for master.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    static $master               = array();
/**
 * Stores `$return` for return.
 *
 * @var mixed
 * @phpstan-var mixed
 * @psalm-var mixed
 */
    static $return;
/**
 * Stores `$mysql_server` for mysql server.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    static $mysql_server         = array();
/**
 * Stores `$mysql_server_by_host` for mysql server by host.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    static $mysql_server_by_host = array();

/**
 * Stores `$db_link` for db link.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    static $db_link = array();

/**
 * Handle mysql state through `exportAllUser`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $db_link Input value for `db_link`.
 * @phpstan-param mixed $db_link
 * @psalm-param mixed $db_link
 * @return mixed Returned value for exportAllUser.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::exportAllUser()
 * @example /fr/mysql/exportAllUser
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function exportAllUser($db_link)
    {
        $sql1 = "select user as user, host as host from mysql.user;";
        $res1 = $db_link->sql_query($sql1);

        $users = array();
        while ($ob1   = $db_link->sql_fetch_object($res1)) {
            $sql2 = "SHOW GRANTS FOR '".$ob1->user."'@'".$ob1->host."'";
            $res2 = $db_link->sql_query($sql2);

            while ($ob2 = $db_link->sql_fetch_array($res2, MYSQLI_NUM)) {

                $users[] = $ob2[0];
            }
        }

        return $users;
    }

/**
 * Handle mysql state through `exportUserByUser`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $db_link Input value for `db_link`.
 * @phpstan-param mixed $db_link
 * @psalm-param mixed $db_link
 * @return mixed Returned value for exportUserByUser.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::exportUserByUser()
 * @example /fr/mysql/exportUserByUser
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function exportUserByUser($db_link)
    {
        $sql1 = "select User as user, Host as host from mysql.user;";
        $res1 = $db_link->sql_query($sql1);

        $users = array();
        while ($ob1   = $db_link->sql_fetch_object($res1)) {

            $sql2 = "SHOW GRANTS FOR '".$ob1->user."'@'".$ob1->host."'";
            $res2 = $db_link->sql_query($sql2);

            while ($ob2 = $db_link->sql_fetch_array($res2, MYSQLI_NUM)) {

                $users[$ob1->user][$ob1->host][] = $ob2[0];
            }
        }

        return $users;
    }

/**
 * Handle mysql state through `onAddMysqlServer`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param int $id_mysql_server Input value for `id_mysql_server`.
 * @phpstan-param int $id_mysql_server
 * @psalm-param int $id_mysql_server
 * @return void Returned value for onAddMysqlServer.
 * @phpstan-return void
 * @psalm-return void
 * @see self::onAddMysqlServer()
 * @example /fr/mysql/onAddMysqlServer
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function onAddMysqlServer($id_mysql_server = "")
    {


        self::addMaxDate();
        self::generateMySQLConfig();

//stopAll daemon
//startAll daemon
    }

/**
 * Handle mysql state through `generateMySQLConfig`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @return void Returned value for generateMySQLConfig.
 * @phpstan-return void
 * @psalm-return void
 * @see self::generateMySQLConfig()
 * @example /fr/mysql/generateMySQLConfig
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function generateMySQLConfig()
    {

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server a ORDER BY id";
        $res = $db->sql_query($sql);

        $config = ';[name_of_connection] => will be acceded in framework with $this->di[\'db\']->sql(\'name_of_connection\')->method()
;driver => list of SGBD avaible {mysql, pgsql, sybase, oracle}
;hostname => server_name of ip of server SGBD (better to put localhost or real IP)
;user => user who will be used to connect to the SGBD
;password => password who will be used to connect to the SGBD
;database => database / schema witch will be used to access to datas
;ssl => 1 or 0 (0 prefered faster)
;timeout => 1 for normal mysql or 11 for Proxy
';

        $delta = $config;

        while ($ob = $db->sql_fetch_object($res)) {
            $string = "[".$ob->name."]\n";
            $string .= "driver=mysql\n";
            $string .= "hostname=".$ob->ip."\n";
            $string .= "port=".$ob->port."\n";
            $string .= "user=".$ob->login."\n";
            $string .= "password=".$ob->passwd."\n";
            $string .= "crypted=1\n";
            $string .= "database=".$ob->database."\n";
            $string .= "ssl=".$ob->is_ssl."\n";
            $string .= "timeout=".$ob->timeout."\n";

            $config .= $string."\n\n";
        }

        $proxysql = self::generateProxySQLConfig();

        $config .= $proxysql;

        if ($config != $delta) {

            file_put_contents(ROOT."/configuration/db.config.ini.php", $config);
        } else {
            echo 'VGCFGXDNGFX';
            exit;
        }
    }
    /*
     * first param : id_mysql_server (coma separated) => obsolete no need param anymore
     *  
     *
     */

    static public function addMaxDate($param = array())
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "INSERT IGNORE INTO `ts_max_date` ( `id_mysql_server`, `id_ts_file`)
        SELECT a.id as id_mysql_server, b.id as id_ts_file 
        from mysql_server a
        INNER JOIN ts_file b 
        LEFT JOIN ts_max_date c ON a.id = c.id_mysql_server AND b.id = c.id_ts_file 
        WHERE c.id IS NULL";

        $db->sql_query($sql);

    }

/**
 * Retrieve mysql state through `getMaster`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param int $id_mysql_server Input value for `id_mysql_server`.
 * @phpstan-param int $id_mysql_server
 * @psalm-param int $id_mysql_server
 * @param mixed $connection_name Input value for `connection_name`.
 * @phpstan-param mixed $connection_name
 * @psalm-param mixed $connection_name
 * @return mixed Returned value for getMaster.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getMaster()
 * @example /fr/mysql/getMaster
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function getMaster($id_mysql_server, $connection_name = '')
    {

        $db      = Sgbd::sql(DB_DEFAULT);
        $masters = Extraction::display(array("slave::master_host", "slave::master_port",
                "slave::connection_name"), array($id_mysql_server));

        //debug($masters);
        
        foreach ($masters as $master) {
            $dnsPort = $master[$connection_name]['master_host'].':'.$master[$connection_name]['master_port'];

            $id_mysql_server = self::getIdFromDns($dnsPort);
            if (!empty($id_mysql_server)) {
                return (int) $id_mysql_server;
            }

            //a mapper aussi avec les ip virtuel (version enterprise)
            $sql = "SELECT id FROM mysql_server where ip='".$master[$connection_name]['master_host']."' AND port='".$master[$connection_name]['master_port']."' LIMIT 1;";

            $res = $db->sql_query($sql);

            while ($ob = $db->sql_fetch_object($res)) {
                return $ob->id;
            }

            //if ()$master[$connection_name]['master_host']
        }

        return 0;
    }

    //deprecated
/**
 * Retrieve mysql state through `getDbLink`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param int $id_mysql_server Input value for `id_mysql_server`.
 * @phpstan-param int $id_mysql_server
 * @psalm-param int $id_mysql_server
 * @param mixed $name Input value for `name`.
 * @phpstan-param mixed $name
 * @psalm-param mixed $name
 * @return mixed Returned value for getDbLink.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @see self::getDbLink()
 * @example /fr/mysql/getDbLink
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function getDbLink($id_mysql_server, $name ='1')
    {
        if (!is_int(intval($id_mysql_server))) {
            throw new \Exception("PMACTRL-855 : first parameter, id_mysql_server should be an int (".$id_mysql_server.") !");
        }

        if (empty(self::$db_link[$id_mysql_server]))
        {
            $dblink = Sgbd::sql(DB_DEFAULT);

            $sql = "SELECT id,name from mysql_server;";
            $res = $dblink->sql_query($sql);

            while ($ob = $dblink->sql_fetch_object($res)) {
                self::$db_link[$ob->id] = $ob->name;
            }
        }

        if (empty(self::$db_link[$id_mysql_server])) {
            throw new \Exception("PMACTRL-854 : impossible to find the server with id '".$id_mysql_server."'");
        }
        else {
            return Sgbd::sql(self::$db_link[$id_mysql_server], $name);
        }
    }

    static public function shouldProtectInformationSchemaTables($id_mysql_server): bool
    {
        return is_numeric($id_mysql_server) && (int) $id_mysql_server !== 1;
    }

    static public function queryTargetsInformationSchemaTables($sql): bool
    {
        return preg_match('/information_schema\s*`?\s*\.\s*`?tables\b/i', (string) $sql) === 1
            || preg_match('/`information_schema`\s*\.\s*`tables`/i', (string) $sql) === 1;
    }

    static public function isInformationSchemaTablesTimeoutError($error): bool
    {
        $error = strtolower(trim((string) $error));

        if ($error === '') {
            return false;
        }

        return strpos($error, 'max_statement_time') !== false
            || strpos($error, 'max_execution_time') !== false
            || strpos($error, 'maximum statement execution time exceeded') !== false
            || strpos($error, 'query execution was interrupted') !== false
            || strpos($error, 'execution timeout') !== false;
    }

    static public function buildInformationSchemaTablesTimeoutMessage($context = '', $timeoutSeconds = null): string
    {
        $timeoutSeconds = (int) ($timeoutSeconds ?? self::INFORMATION_SCHEMA_TABLES_TIMEOUT_SECONDS);
        $context = trim((string) $context);

        if ($context === '') {
            $context = 'information_schema.tables';
        }

        return "[PMACONTROL-IS-TABLES-TIMEOUT] Query on ".$context." exceeded ".$timeoutSeconds."s. "
            ."Use a narrower filter or a fallback strategy (SHOW TABLES / SHOW CREATE TABLE).";
    }

    static public function protectInformationSchemaTablesQuery($db, $sql, $id_mysql_server = null, $timeoutSeconds = null)
    {
        $timeoutSeconds = (int) ($timeoutSeconds ?? self::INFORMATION_SCHEMA_TABLES_TIMEOUT_SECONDS);
        $sql = (string) $sql;

        if (!self::shouldProtectInformationSchemaTables($id_mysql_server) || !self::queryTargetsInformationSchemaTables($sql)) {
            return $sql;
        }

        if ($db->checkVersion(array('MariaDB'=> '10.1.1'))) {
            return "SET STATEMENT MAX_STATEMENT_TIME = ".$timeoutSeconds." FOR ".$sql;
        }

        if ($db->checkVersion(array('MySQL' => '5.7'))) {
            if (stripos($sql, 'MAX_EXECUTION_TIME(') !== false) {
                return $sql;
            }

            return preg_replace(
                '/^\s*SELECT\s+/i',
                'SELECT /*+ MAX_EXECUTION_TIME('.($timeoutSeconds * 1000).') */ ',
                $sql,
                1
            ) ?? $sql;
        }

        return $sql;
    }

    static public function sqlQueryWithInformationSchemaTablesTimeout($db, $sql, $id_mysql_server = null, $context = '', $silent = false)
    {
        $sql = self::protectInformationSchemaTablesQuery($db, $sql, $id_mysql_server);
        $res = $silent ? self::sqlQuerySilentCompat($db, $sql) : $db->sql_query($sql);

        if ($res === false) {
            $error = (string) $db->sql_error();

            if (self::isInformationSchemaTablesTimeoutError($error)) {
                throw new \Exception(self::buildInformationSchemaTablesTimeoutMessage($context));
            }
        }

        return $res;
    }

/**
 * Create mysql state through `addMysqlServer`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int|string,mixed> $data Input value for `data`.
 * @phpstan-param array<int|string,mixed> $data
 * @psalm-param array<int|string,mixed> $data
 * @return mixed Returned value for addMysqlServer.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::addMysqlServer()
 * @example /fr/mysql/addMysqlServer
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function addMysqlServer($data)
    {
        Debug::debug($data);

        $db     = Sgbd::sql(DB_DEFAULT);
        //debug($data);
        $server = array();

        if (empty($data['fqdn'])) {
            $data['fqdn'] = $data['hostname'] ?? ($data['ip'] ?? '');
        }

        if (!empty($data['ip'])) {
            $ip = $data['ip'];
        } else {
            $ip = System::getIp($data['fqdn']);

            if (empty($ip)) {
                $ip = $data['fqdn'];
            }
        }

        if (empty($data['password'])) {
            $data['password'] = $data['passwd'];
        }

        $port = $data['port'] ?? 3306;

        $sql = "SELECT id from mysql_server where ip='".$ip."' and port =".$port;

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $server['mysql_server']['id'] = $ob->id;
        }

        $server['mysql_server']['id_client'] = self::selectOrInsert($data['organization'] ?? "none", "client", "libelle");

        $server['mysql_server']['id_environment']      = self::selectOrInsert($data['environment'], "environment", "libelle",
                array("key" => strtolower(str_replace(' ', '', $data['environment'])),
                 "class" => "info", "letter" => substr(strtoupper($data['environment']),
                        0, 1)));
        $server['mysql_server']['name']                = "server_".uniqid();
        $server['mysql_server']['display_name']        = self::getHostname($data['display_name'],
                array($data['fqdn'], $data['login'], $data['password'], $data['port']));
        $server['mysql_server']['ip']                  = $ip;
        $server['mysql_server']['hostname']            = $data['hostname'] ?? $data['fqdn'];
        $server['mysql_server']['login']               = $data['login'];
        $server['mysql_server']['passwd']              = Crypt::encrypt($data['password'], CRYPT_KEY);
        $server['mysql_server']['database']            = $data['database'] ?? "mysql";
        $server['mysql_server']['is_password_crypted'] = "1";
        $server['mysql_server']['port']                = $port;
        $server['mysql_server']['ssh_nat']             = $data['ssh_nat'] ?? "";

        $server['mysql_server']['is_monitored']    = $data['is_monitored'] ?? "1";
        $server['mysql_server']['is_acknowledged'] = $data['is_acknowledged'] ?? 0;
        $server['mysql_server']['ssh_port']        = $data['ssh_port'] ?? 22;
        $server['mysql_server']['ssh_login']       = $data['ssh_login'] ?? "root";
        $server['mysql_server']['is_proxy']        = $data['is_proxy'] ?? 0;
        $server['mysql_server']['is_vip']          = $data['is_vip'] ?? 0;

        $sql = "SELECT id FROM `mysql_server` WHERE `ip`='".$server['mysql_server']['ip']."' AND `port` = '".$server['mysql_server']['port']."'";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            if (!empty($ob->id)) {
                $server['mysql_server']['id'] = $ob->id;
            }
        }

        Debug::debug($server, "new MySQL");

        if (self::isPmaControl($server['mysql_server']['ip'], $server['mysql_server']['port']) === true) {

            self::$return['mysql']['caution'] = "Impossible to overright the server of PmaControl (".$server['mysql_server']['ip'].":".$server['mysql_server']['port'].")";
            return false;
        }

        $id_mysql_server = $db->sql_save($server);
        if ($id_mysql_server) {
            if (empty($server['mysql_server']['id'])) {
                self::$return['mysql']['inserted'][] = $server['mysql_server']['display_name']." (".$server['mysql_server']['hostname'].':'.$server['mysql_server']['port'].")";
            } else {
                self::$return['mysql']['updated'][] = $server['mysql_server']['display_name']." (".$server['mysql_server']['hostname'].':'.$server['mysql_server']['port'].")";
            }

            if (!empty($data['tag'])) {
                Debug::debug($data['tag'], "Tags");
                Tag::insertTag($id_mysql_server, $data['tag']);
            }

            Mysql::onAddMysqlServer($id_mysql_server);
        } else {
            unset($server['mysql_server']['passwd']);

            $msg = $server['mysql_server']['display_name']." (".$server['mysql_server']['hostname'].':'.$server['mysql_server']['port'].") : "
                .json_encode(array($db->sql_error(), $server));

            Debug::debug($msg, "FAIL INSERT");

            Debug::debug($db->sql_error(), "ERROR");

            self::$return['mysql']['failed'][] = $msg;
        }

        $server['mysql_server']['id'] = $id_mysql_server;


        return $server;
    }
    /*
     * to export in Glial::MySQL ?
     *
     * if ID not found we insert this line and get back, the line
     */

    static function getId($value, $table_name, $field, $list = array())
    {
        $list_key = '';
        $list_val = '';

        if (count($list) > 0) {
            $keys = array_keys($list);

            if (in_array($field, $keys)) {
                throw new \Exception('PMACTRL-912 : This field cannot be specified twice : "'.$field.'"');
            }


            $values = array_values($list);

            $list_key = ",`".implode('`,`', $keys)."`";
            $list_val = ",'".implode("','", $values)."'";
        }
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "
IF (SELECT 1 FROM `".$table_name."` WHERE `".$field."`='".$db->sql_real_escape_string($value)."') THEN
BEGIN
    SELECT `id` FROM `".$table_name."` WHERE `".$field."`='".$db->sql_real_escape_string($value)."';
END;
ELSE
BEGIN
    INSERT INTO `".$table_name."` (`".$field."`".$list_key.") VALUES('".$db->sql_real_escape_string($value)."'".$list_val.");
    SELECT LAST_INSERT_ID() AS id;
END;
END IF;";

        Debug::sql($sql);

        if ($db->sql_multi_query($sql)) {
            $i = 1;

            do {
                $res = $db->sql_store_result();
                //Debug::debug($res);

                if ($res) {
                    while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {


                        Debug::debug($row);

                        if (!empty($row['id'])) {

                            $id_return = $row['id'];
                        }
                    }
                    $db->sql_free_result($res);
                } else {

                    if (!empty($db->sql_error())) {
                        Debug::debug($db->sql_error());
                    }
                }

                //Debug::debug($db->sql_more_results());
                if ($db->sql_more_results()) {
                    printf("-----------------\n");
                }
            } while ($db->sql_more_results() && $db->sql_next_result());

            $error = $db->sql_error();

            if ($error) {
                echo "Syntax Error: \n $error";  // display array pointer key:value
            }


            Debug::debug($id_return, "ID de retour de la table : ".$table_name." !");
            return $id_return;
        }

        throw new \Exception('PMACTRL-071 : no id returned (problem INSERT/UPDATE) [table: '.$table_name.', field: '.$field.']');

//debug($row['id']);
//throw new \Exception('PMACTRL-059 : impossible to find table and/or field');
    }

/**
 * Handle mysql state through `isPmaControl`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $ip Input value for `ip`.
 * @phpstan-param mixed $ip
 * @psalm-param mixed $ip
 * @param mixed $port Input value for `port`.
 * @phpstan-param mixed $port
 * @psalm-param mixed $port
 * @return mixed Returned value for isPmaControl.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::isPmaControl()
 * @example /fr/mysql/isPmaControl
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function isPmaControl($ip, $port)
    {
        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM mysql_server where name='".DB_DEFAULT."'";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            if ($ob->ip === $ip && $ob->port == $port) {
                return true;
            }
        }

        return false;
    }

/**
 * Retrieve mysql state through `getHostname`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $name Input value for `name`.
 * @phpstan-param mixed $name
 * @psalm-param mixed $name
 * @param array<int|string,mixed> $data Input value for `data`.
 * @phpstan-param array<int|string,mixed> $data
 * @psalm-param array<int|string,mixed> $data
 * @return mixed Returned value for getHostname.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getHostname()
 * @example /fr/mysql/getHostname
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function getHostname($name, $data)
    {

        if (empty($name) || $name == "@hostname") {

            Debug::debug($data);

            $db = mysqli_init();
            if (!$db) {
                die('mysqli_init failed');
            }

            if ($db->real_connect($data['0'], $data['1'], $data['2'], "mysql", $data['3'])) {
                $res = $db->query('SELECT @@hostname as hostname;');

                while ($ob = $res->fetch_object()) {
                    $hostname = $ob->hostname;
                }

                $db->close();
            } else {
                Debug::error($data);
                return false;
            }
        } else {
            $hostname = $name;
        }

        return $hostname;
    }
    /*
     *
     *
     * V2 sans PROCEDURE
     * (plus lente)
     * return id on select or inserted row
     */

    static function selectOrInsert($value, $table_name, $field, $list = array())
    {
        $list_key = '';
        $list_val = '';

        if (count($list) > 0) {
            $keys = array_keys($list);

            if (in_array($field, $keys)) {
                throw new \Exception('PMACTRL-912 : This field cannot be specified twice : "'.$field.'"');
            }

            $values = array_values($list);

            $list_key = ",`".implode('`,`', $keys)."`";
            $list_val = ",'".implode("','", $values)."'";
        }
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT `id` FROM `".$table_name."` WHERE `".$field."`='".$db->sql_real_escape_string($value)."';";
        $res = $db->sql_query($sql);

        Debug::debug($sql);

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            if (!empty($arr['id'])) {
                return $arr['id'];
            }
        }

        $sql2 = "INSERT INTO `".$table_name."` (`".$field."` ".$list_key.") VALUES('".$db->sql_real_escape_string($value)."' ".$list_val.");";

        Debug::sql($sql2);

        $res = $db->sql_query($sql2);
        if (!$res) {
            throw new \Exception("PMACTRL-518 : ".$db->sql_error());
        }

        $id = $db->sql_insert_id();

        Debug::debug($id, "id");

        if (empty($id)) {
            throw new \Exception('PMACTRL-519 : empty id after insert !  [table: '.$table_name.', field: '.$field.']');
        }

        return $id;
    }

/**
 * Retrieve mysql state through `getServerInfo`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param int $id_mysql_server Input value for `id_mysql_server`.
 * @phpstan-param int $id_mysql_server
 * @psalm-param int $id_mysql_server
 * @return mixed Returned value for getServerInfo.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getServerInfo()
 * @example /fr/mysql/getServerInfo
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function getServerInfo($id_mysql_server)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server WHERE id = ".$id_mysql_server.";";
        $res = $db->sql_query($sql);

        while ($ar = $db->sql_fetch_object($res)) {
            $ob = $ar;
        }

        return $ob;
    }

/**
 * Handle mysql state through `execMulti`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $queries Input value for `queries`.
 * @phpstan-param mixed $queries
 * @psalm-param mixed $queries
 * @param mixed $db_link Input value for `db_link`.
 * @phpstan-param mixed $db_link
 * @psalm-param mixed $db_link
 * @return mixed Returned value for execMulti.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @see self::execMulti()
 * @example /fr/mysql/execMulti
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function execMulti($queries, $db_link)
    {
        if (!is_array($queries)) {
            throw new \Exception("PMACTRL-652 : first parameter should be an array !");
        }

        $query = implode("", $queries);
        $ret   = [];
        $i     = 0;

        if ($db_link->sql_multi_query($query)) {
            foreach ($queries as $table => $elem) {
                $result = $db_link->sql_store_result();

                if (!$result) {
                    printf("Error: %s\n", mysqli_error($db_link->link));
                    Debug::debug($query);
                    exit();
                }

                while ($row = $db_link->sql_fetch_array($result, MYSQLI_ASSOC)) {
                    $ret[$table][] = $row;
                }
                if ($db_link->sql_more_results()) {
                    $db_link->sql_next_result();
                }
            }
        }
        return $ret;
    }

/**
 * Retrieve mysql state through `getListObject`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $db_link Input value for `db_link`.
 * @phpstan-param mixed $db_link
 * @psalm-param mixed $db_link
 * @param array<int|string,mixed> $database Input value for `database`.
 * @phpstan-param array<int|string,mixed> $database
 * @psalm-param array<int|string,mixed> $database
 * @param mixed $type_object Input value for `type_object`.
 * @phpstan-param mixed $type_object
 * @psalm-param mixed $type_object
 * @return mixed Returned value for getListObject.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @see self::getListObject()
 * @example /fr/mysql/getListObject
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function getListObject($db_link, $database, $type_object, $id_mysql_server = null)
    {
        $query['TRIGGER']['query']   = "select trigger_schema, trigger_name, action_statement from `information_schema`.`triggers` where trigger_schema ='{DB}';";
        $query['FUNCTION']['query']  = "show function status WHERE Db ='{DB}';";
        $query['PROCEDURE']['query'] = "show procedure status WHERE Db ='{DB}';";
        $query['TABLE']['query']     = "select TABLE_NAME from `information_schema`.`tables` where `TABLE_SCHEMA` = '{DB}' AND `TABLE_TYPE`='BASE TABLE' order by TABLE_NAME;";
        $query['VIEW']['query']      = "select TABLE_NAME from `information_schema`.`tables` where `TABLE_SCHEMA` = '{DB}' AND `TABLE_TYPE`='VIEW' order by TABLE_NAME;";
        $query['EVENT']['query']     = "SHOW EVENTS FROM `{DB}`;";

        $query['TRIGGER']['field']   = "trigger_name";
        $query['FUNCTION']['field']  = "Name";
        $query['PROCEDURE']['field'] = "Name";
        $query['TABLE']['field']     = "TABLE_NAME";
        $query['VIEW']['field']      = "TABLE_NAME";
        $query['EVENT']['field']     = "Name";
        /*
         * //$query['ALL'] = true; => TO DO
         */

        if (!in_array($type_object, array_keys($query))) {
            throw new \Exception("PMACTRL-095 : this type of object is not supported : '".$type_object."'", 80);
        }

        //to prevent if a DB don't have a type of object
        $data = array();

        $sql = str_replace('{DB}', $database, $query[$type_object]['query']);
        $res = self::sqlQueryWithInformationSchemaTablesTimeout($db_link, $sql, $id_mysql_server, __METHOD__);

        while ($row = $db_link->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data[] = $row[$query[$type_object]['field']];
        }

        ksort($data);
        return $data;
    }
    /*
     * @author : Aurélien LEQUOY
     * @desctiotion : return current database
     * @version 1.0
     *
     */

    static public function getCurrentDb($db)
    {
        $ob_a = $db->sql_fetch_object($db->sql_query("SELECT database() as db"));
        return $ob_a->db;
    }

/**
 * Retrieve mysql state through `getStructure`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $db_link Input value for `db_link`.
 * @phpstan-param mixed $db_link
 * @psalm-param mixed $db_link
 * @param array<int|string,mixed> $database Input value for `database`.
 * @phpstan-param array<int|string,mixed> $database
 * @psalm-param array<int|string,mixed> $database
 * @param array<int|string,mixed> $data Input value for `data`.
 * @phpstan-param array<int|string,mixed> $data
 * @psalm-param array<int|string,mixed> $data
 * @param mixed $object Input value for `object`.
 * @phpstan-param mixed $object
 * @psalm-param mixed $object
 * @return mixed Returned value for getStructure.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getStructure()
 * @example /fr/mysql/getStructure
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function getStructure($db_link, $database, $data, $object)
    {
        $query['TRIGGER']['query']   = "SHOW CREATE TRIGGER `{DB}`.`{OBJECT}`;";
        $query['FUNCTION']['query']  = "SHOW CREATE FUNCTION `{DB}`.`{OBJECT}`;";
        $query['PROCEDURE']['query'] = "SHOW CREATE PROCEDURE `{DB}`.`{OBJECT}`;";
        $query['TABLE']['query']     = "SHOW CREATE TABLE `{DB}`.`{OBJECT}`;";
        $query['VIEW']['query']      = "SHOW CREATE VIEW `{DB}`.`{OBJECT}`;";
        $query['EVENT']['query']     = "SHOW CREATE EVENT `{DB}`.`{OBJECT}`;";

        $query['TRIGGER']['field']   = "SQL Original Statement";
        $query['FUNCTION']['field']  = "Create Function";
        $query['PROCEDURE']['field'] = "Create Procedure";
        $query['TABLE']['field']     = "Create Table";
        $query['VIEW']['field']      = "Create View";
        $query['EVENT']['field']     = "Create Event";

        $query['TRIGGER']['drop']   = "DROP TRIGGER `{OBJECT}`";
        $query['FUNCTION']['drop']  = "DROP FUNCTION `{OBJECT}`";
        $query['PROCEDURE']['drop'] = "DROP PROCEDURE `{OBJECT}`";
        $query['TABLE']['drop']     = "DROP TABLE `{OBJECT}`";
        $query['VIEW']['drop']      = "DROP VIEW `{OBJECT}`";
        $query['EVENT']['drop']     = "DROP EVENT `{OBJECT}`";

        $query['TABLE']['name'] = "Table";

        $queries = array();
        foreach ($data as $elem) {

            $tmp            = str_replace(array('{DB}', '{OBJECT}'), array($database, $elem), $query[$object]['query']);
            $queries[$elem] = $tmp;
        }

        $ret = self::execMulti($queries, $db_link);

        $resultat = array();
        foreach ($ret as $elem => $row) {
            $arr = $row[0];

            $struc = $arr[$query[$object]['field']];

            if ($object === "TABLE") {
                $struc = preg_replace('/(\sAUTO_INCREMENT=[0-9]+)/', '', $struc);
            }
            //$arr[$query[$object]['name']]
            $resultat[$elem] = $struc;
        }

        //Debug::debug($ret);

        return $resultat;
    }

    /**
     * Retourne la liste des requêtes SHOW CREATE pour toutes les routines d'un serveur.
     *
     * Usage:
     *   Mysql::getRoutineShowCreateQueries([id_mysql_server, 'PROCEDURE']);
     *   Mysql::getRoutineShowCreateQueries([id_mysql_server, 'FUNCTION']);
     */
    static public function getRoutineShowCreateQueries(array $param): array
    {
        Debug::parseDebug($param);

        $id_mysql_server = isset($param[0]) ? (int)$param[0] : 0;
        $routineType = strtoupper($param[1] ?? 'PROCEDURE');
        $schemaFilter = $param[2] ?? '';

        if ($id_mysql_server <= 0) {
            throw new \Exception("PMACTRL-ROUTINE-001: id_mysql_server invalide.");
        }

        $allowedTypes = ['PROCEDURE', 'FUNCTION'];
        if (!in_array($routineType, $allowedTypes, true)) {
            throw new \Exception(
                "PMACTRL-ROUTINE-002: routine_type invalide (" . $routineType . "). Attendu: " . implode(', ', $allowedTypes)
            );
        }

        $db = Mysql::getDbLink($id_mysql_server);

        $schemaClause = '';
        if (!empty($schemaFilter)) {
            $schemaClause = " AND ROUTINE_SCHEMA='" . $db->sql_real_escape_string($schemaFilter) . "'";
        }

        $sql = "SELECT CONCAT(\"SHOW CREATE " . $routineType . " `\", ROUTINE_SCHEMA, \"`.`\", ROUTINE_NAME, \"`;\") AS show_create\n"
            . "FROM INFORMATION_SCHEMA.ROUTINES\n"
            . "WHERE ROUTINE_TYPE='" . $db->sql_real_escape_string($routineType) . "'"
            . $schemaClause
            . "\nORDER BY ROUTINE_SCHEMA, ROUTINE_NAME;";

        Debug::sql($sql);

        $res = $db->sql_query($sql);
        $queries = [];

        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            if (!empty($row['show_create'])) {
                $queries[] = $row['show_create'];
            }
        }

        return $queries;
    }

    /**
     * Récupère le id_mysql_server depuis un slave avec mater_host // master_port
     * Si besoin on lie la table mysql_server avec alias_dns, dans les cas ou la réplication se fait par un VIP, DNS ou fqdn
     * @author Aurélien LEQUOY <aurelien.lequoy@esysteme.com>
     * @license GNU/GPL
     * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
     * @param string
     * @description construct the object and set the connection available
     * @access public
     * @example new Sgbd(array from \Glial\Synapse\Config);
     * @package Sgbd
     * @See Glial\Sgbd\Sgbd->sql()
     * @version 1.0
     */
    static public function getIdFromDns(string $dns_port)
    {
        if (empty(self::$master[$dns_port])) {

            $db = Sgbd::sql(DB_DEFAULT);

            $sql = "SELECT ip, port, id as id_mysql_server FROM mysql_server a
                 UNION select dns as ip, port, id_mysql_server from alias_dns PARTITION (pn) b;";

            $res = $db->sql_query($sql);

            while ($ob = $db->sql_fetch_object($res)) {
                self::$master[$ob->ip.':'.$ob->port] = $ob->id_mysql_server;
            }
        }

        if (!empty(self::$master[$dns_port])) {
            return self::$master[$dns_port];
        }

        return false;
    }

/**
 * Handle mysql state through `testMySQL`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for testMySQL.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::testMySQL()
 * @example /fr/mysql/testMySQL
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function testMySQL($param)
    {
        Debug::parseDebug($param);

        $hostname = $param[0];
        $port     = $param[1];
        $user     = $param[2];
        $password = $param[3];

        $link = mysqli_connect($hostname.":".$port, $user, trim($password), "mysql");

        if ($link) {
            Debug::debug("Connection sucessfull");

            mysqli_close($link);
            return true;
        } else {
            $error = 'Connect Error ('.mysqli_connect_errno().') '.mysqli_connect_error();
            Debug::debug($error, "[ERROR]");
            return $error;
        }
    }




/**
 * Retrieve mysql state through `getRealForeignKey`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for getRealForeignKey.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getRealForeignKey()
 * @example /fr/mysql/getRealForeignKey
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function getRealForeignKey($param)
    {

        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $database        = $param[1];

        $db = Mysql::getDbLink($id_mysql_server);

        $sql = "SELECT CONSTRAINT_SCHEMA as constraint_schema,TABLE_NAME as constraint_table,COLUMN_NAME as constraint_column,"
            ." REFERENCED_TABLE_SCHEMA as referenced_schema, REFERENCED_TABLE_NAME as referenced_table,REFERENCED_COLUMN_NAME as referenced_column"
            ." FROM `information_schema`.`KEY_COLUMN_USAGE` "
            ."WHERE `CONSTRAINT_SCHEMA` ='".$database."' "
            ."AND `REFERENCED_TABLE_SCHEMA`='".$database."' "
            ."AND `REFERENCED_TABLE_NAME` IS NOT NULL  ";

        Debug::sql($sql);

        $res = $db->sql_query($sql);

        $foreign_key = array();

        while ($ob = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $md5 = md5($ob['constraint_schema'].$ob['constraint_table'].$ob['constraint_column']);

            $foreign_key[$md5] = $ob;
        }

        //Debug::debug($foreign_key);
        Debug::debug(count($foreign_key));

        return $foreign_key;
    }

/**
 * Retrieve mysql state through `getEmptyDatabase`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for getEmptyDatabase.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getEmptyDatabase()
 * @example /fr/mysql/getEmptyDatabase
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function getEmptyDatabase($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];

        $db = Mysql::getDbLink($id_mysql_server);

        $sql = "SELECT S.SCHEMA_NAME as schema_name FROM INFORMATION_SCHEMA.SCHEMATA S
            LEFT OUTER JOIN INFORMATION_SCHEMA.TABLES T ON S.SCHEMA_NAME = T.TABLE_SCHEMA
            WHERE T.TABLE_SCHEMA IS NULL;";

        $res = $db->sql_query($sql);

        $emptydb = array();
        while ($ob      = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $emptydb[] = $ob;
        }

        $db->sql_close();
        return $emptydb;
    }
    /*
     * Retourne les informations en fonction d'un id_mysql_server
     *
     */

    static public function getInfoServer($param)
    {

        $id_mysql_server = $param[0];

        if (empty(static::$mysql_server[$id_mysql_server])) {

            $db  = Sgbd::sql(DB_DEFAULT);
            $sql = "SELECT * FROM mysql_server";
            $res = $db->sql_query($sql);

            while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                static::$mysql_server[$arr['id']] = $arr;
            }
        }

        return static::$mysql_server[$id_mysql_server];
    }

/**
 * Create mysql state through `createSelectAccount`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for createSelectAccount.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::createSelectAccount()
 * @example /fr/mysql/createSelectAccount
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function createSelectAccount($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $user            = $param[1];

        $bytes = openssl_random_pseudo_bytes(20);
        $pass  = bin2hex($bytes);

        $account['user']     = $user;
        $account['password'] = $pass;

        $db = Mysql::getDbLink($id_mysql_server);

        $sql = "GRANT SELECT ON *.* TO '".$account['user']."'@'%' IDENTIFIED BY '".$account['password']."';";
        $db->sql_query($sql);

        Debug::debug($account);

        return $account;
    }
    /*
     * Get id_mysql_server if exist
     *
     *
     */

    static public function getIdMySqlServer($param)
    {
        Debug::parseDebug($param);

        $hostname = $param[0];
        $port     = $param[1];

        if (empty($mysql_server_by_host[$hostname.':'.$port])) {
            $db = Sgbd::sql(DB_DEFAULT);

            $sql = "SELECT * FROM mysql_server;";
            $res = $db->sql_query($sql);

            while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $mysql_server_by_host[$arr['ip'].':'.$arr['port']] = $arr;
            }
        }

        if (!empty($mysql_server_by_host[$hostname.':'.$port])) {
            return $mysql_server_by_host[$hostname.':'.$port];
        } else {
            return false;
        }
    }

/**
 * Retrieve mysql state through `getRoles`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for getRoles.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getRoles()
 * @example /fr/mysql/getRoles
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function getRoles($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $db              = Mysql::getDbLink($id_mysql_server);

        //$sql = "SELECT `ROLE_NAME` as role_name from `information_schema`.`APPLICABLE_ROLES`;";
        $sql = "SELECT `user` as role_name from `mysql`.`user` where is_role='Y';";
        Debug::sql($sql);

        $res = $db->sql_query($sql);

        $data = array();
        while ($ob   = $db->sql_fetch_object($res)) {


            Debug::debug($ob);
            $data[] = $ob->role_name;
        }

        return $data;
    }

/**
 * Retrieve mysql state through `getCreateRoles`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for getCreateRoles.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getCreateRoles()
 * @example /fr/mysql/getCreateRoles
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function getCreateRoles($param)
    {

        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $db              = Mysql::getDbLink($id_mysql_server);

        $roles = self::getRoles(array($id_mysql_server));

        $export = array();

        Debug::debug($roles, "ROLE");

        foreach ($roles as $role) {


            $export[] = "DROP ROLE IF EXISTS '".$role."';";
            $export[] = "CREATE OR REPLACE ROLE '".$role."';";

            $sql = "SHOW GRANTS FOR `".$role."`;";
            Debug::sql($sql);
            $res = $db->sql_query($sql);

            while ($arr = $db->sql_fetch_array($res, MYSQLI_NUM)) {

                $output_array = array();
                preg_match_all('/\((.*)\)/', $arr[0], $output_array);

                Debug::debug($output_array);

                if (!empty($output_array[1][0])) {
                    Debug::debug($output_array[1][0]);

                    $out = '(`'.str_replace(', ', '`, `', $output_array[1][0]).'`)';

                    Debug::debug($out, 'out');

                    $escape = str_replace($output_array[0][0], $out, $arr[0]);
                } else {
                    $escape = $arr[0];
                }

                $export[] = $escape.";";
            }
        }
        //die();
        return $export;
    }

/**
 * Retrieve mysql state through `getSlave`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for getSlave.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @see self::getSlave()
 * @example /fr/mysql/getSlave
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function getSlave($param)
    {
        $id_mysql_server = $param[0];

        $db = self::getDbLink($id_mysql_server);

        $sql = "SHOW SLAVE HOSTS";
        $res = $db->sql_query($sql);

        $server_id_ref = array();
        while ($ob            = $db->sql_fetch_object($res)) {

            $server_id_ref[] = $ob->Server_id;
        }

        $data                    = array();
        $data['server_id']       = $server_id_ref;
        $data['id_mysql_server'] = array();

        $all_id = Extraction::display(array("variables::server_id"));

        $data['slave'] = array();

        foreach ($all_id as $servers) {
            foreach ($servers as $slave) {
                $server_id_to_compare[$slave['server_id']] = $slave['id_mysql_server'];
                $data['id_mysql_server'][]                 = $slave['id_mysql_server'];

                if (in_array($slave['server_id'], $server_id_ref)) {
                    $data['slave'][] = $slave['id_mysql_server'];
                }
            }
        }

        $data['candidate'] = $server_id_to_compare;

        Debug::debug($data);

        if (count($data['slave']) !== count($data['server_id'])) {
            throw new \Exception('PMACTRL-249 impossible to match server_id with id_mysql_server');
        }

        return $data;
    }

/**
 * Handle mysql state through `generateProxySQLConfig`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for generateProxySQLConfig.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::generateProxySQLConfig()
 * @example /fr/mysql/generateProxySQLConfig
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function generateProxySQLConfig()
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM proxysql_server a ORDER BY id";
        $res = $db->sql_query($sql);

        $config = "";

        while ($ob = $db->sql_fetch_object($res)) {
            $string = "[proxysql_".$ob->id."]\n";
            $string .= "driver=mysql\n";
            $string .= "hostname=".$ob->hostname."\n";
            $string .= "port=".$ob->port."\n";
            $string .= "user=".$ob->login."\n";
            $string .= "password='".Crypt::encrypt($ob->password, CRYPT_KEY)."'\n";
            $string .= "crypted=1\n";
            $string .= "database=main\n";

            $config .= $string."\n\n";
        }

        return $config;
    }

/**
 * Handle mysql state through `execute`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param int $id_mysql_server Input value for `id_mysql_server`.
 * @phpstan-param int $id_mysql_server
 * @psalm-param int $id_mysql_server
 * @param mixed $file_name Input value for `file_name`.
 * @phpstan-param mixed $file_name
 * @psalm-param mixed $file_name
 * @return mixed Returned value for execute.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::execute()
 * @example /fr/mysql/execute
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function execute($id_mysql_server, $file_name)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql ="SELECT * FROM mysql_server WHERE id=".$id_mysql_server;
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res))
        {
            $cmd = "mysql -h " . $ob->ip . " -u " . $ob->login . " -P " . $ob->port 
            . " -p" . Crypt::decrypt($ob->passwd) . " ".$ob->database." < " . $file_name . " 2>&1";
            $ret = shell_exec($cmd);

            return $ret;
        }
    }

/**
 * Handle mysql state through `test`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $hostname Input value for `hostname`.
 * @phpstan-param mixed $hostname
 * @psalm-param mixed $hostname
 * @param mixed $port Input value for `port`.
 * @phpstan-param mixed $port
 * @psalm-param mixed $port
 * @param mixed $user Input value for `user`.
 * @phpstan-param mixed $user
 * @psalm-param mixed $user
 * @param mixed $password Input value for `password`.
 * @phpstan-param mixed $password
 * @psalm-param mixed $password
 * @return mixed Returned value for test.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::test()
 * @example /fr/mysql/test
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function test($hostname, $port, $user, $password)
    {
        $link = mysqli_connect($hostname.":".$port, $user, trim($password), "mysql");

        if ($link) {
            mysqli_close($link);
            return true;
        } else {
            return 'Connect Error ('.mysqli_connect_errno().') '.mysqli_connect_error();
        }
    }

/**
 * Handle mysql state through `test2`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $hostname Input value for `hostname`.
 * @phpstan-param mixed $hostname
 * @psalm-param mixed $hostname
 * @param mixed $port Input value for `port`.
 * @phpstan-param mixed $port
 * @psalm-param mixed $port
 * @param mixed $user Input value for `user`.
 * @phpstan-param mixed $user
 * @psalm-param mixed $user
 * @param mixed $password Input value for `password`.
 * @phpstan-param mixed $password
 * @psalm-param mixed $password
 * @return mixed Returned value for test2.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::test2()
 * @example /fr/mysql/test2
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function test2($hostname, $port, $user, $password)
    {
        $link = mysqli_init();
        mysqli_options($link, MYSQLI_OPT_CONNECT_TIMEOUT, 15);
        mysqli_real_connect($link,$hostname.":".$port, $user, $password);

        if ($link) {
            mysqli_query($link, "SHOW GLOBAL VARIABLES;");
            mysqli_close($link);
            return true;
        } else {
            return 'Connect Error ('.mysqli_connect_errno().') '.mysqli_connect_error();
        }
    }


/**
 * Retrieve mysql state through `getIdMySQLFromGalera`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $wsrep_incoming_addresses Input value for `wsrep_incoming_addresses`.
 * @phpstan-param mixed $wsrep_incoming_addresses
 * @psalm-param mixed $wsrep_incoming_addresses
 * @return mixed Returned value for getIdMySQLFromGalera.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getIdMySQLFromGalera()
 * @example /fr/mysql/getIdMySQLFromGalera
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function getIdMySQLFromGalera($wsrep_incoming_addresses)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $data = Dot3::getIdMysqlServerFromGalera($wsrep_incoming_addresses);


        foreach($data as $server)
        {
  
            $elems = explode(':', $server);
            $tabl_sql[] = "(SELECT id FROM mysql_server WHERE ip='".$elems[0]."' and port =".$elems[1].")";
        }

        $sql = implode( " UNION ", $tabl_sql);


        $ids = array();
        $res = $db->sql_query($sql);
        while($ob = $db->sql_fetch_object($res)) {
            $ids[] = $ob->id;
        }

        return $ids;

    }


/**
 * Retrieve mysql state through `getIdMysqlServerFromIpPort`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $ip Input value for `ip`.
 * @phpstan-param mixed $ip
 * @psalm-param mixed $ip
 * @param mixed $port Input value for `port`.
 * @phpstan-param mixed $port
 * @psalm-param mixed $port
 * @return mixed Returned value for getIdMysqlServerFromIpPort.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getIdMysqlServerFromIpPort()
 * @example /fr/mysql/getIdMysqlServerFromIpPort
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function getIdMysqlServerFromIpPort($ip , $port)
    {
        // with cache ?
        
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT a.id as id_mysql_server, a.ip, a.port as virtual_port, a.display_name, a.is_proxy, a.ip as ip_real, a.port as port_real
        FROM mysql_server a 
        WHERE ip = '".$ip."' AND port='".$port."'
        UNION select b.id_mysql_server, b.dns as ip, b.port, c.display_name, c.is_proxy, c.ip as ip_real, c.port as port_real
        from alias_dns PARTITION (pn) b 
        INNER JOIN mysql_server c ON b.id_mysql_server =c.id
        WHERE b.dns = '".$ip."' AND b.port='".$port."'";

        Debug::sql($sql);

        $res = $db->sql_query($sql);
        while ($ob = $db->sql_fetch_object($res)) {
            $id = $ob->id_mysql_server;
            return $id;
        }

        return false;
    }
    

/**
 * Retrieve mysql state through `getNameMysqlServerFromIpPort`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $ip Input value for `ip`.
 * @phpstan-param mixed $ip
 * @psalm-param mixed $ip
 * @param mixed $port Input value for `port`.
 * @phpstan-param mixed $port
 * @psalm-param mixed $port
 * @return mixed Returned value for getNameMysqlServerFromIpPort.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getNameMysqlServerFromIpPort()
 * @example /fr/mysql/getNameMysqlServerFromIpPort
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function getNameMysqlServerFromIpPort($ip , $port)
    {
        // with cache ?
        
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT a.name, a.id as id_mysql_server, a.ip, a.port as virtual_port, a.display_name, a.is_proxy, a.ip as ip_real, a.port as port_real
        FROM mysql_server a 
        WHERE ip = '".$ip."' AND port='".$port."'
        UNION select c.name, b.id_mysql_server, b.dns as ip, b.port, c.display_name, c.is_proxy, c.ip as ip_real, c.port as port_real
        from alias_dns PARTITION (pn) b 
        INNER JOIN mysql_server c ON b.id_mysql_server =c.id
        WHERE b.dns = '".$ip."' AND b.port='".$port."';";

        Debug::sql($sql);

        $res = $db->sql_query($sql);
        while ($ob = $db->sql_fetch_object($res)) {
            return $ob->name;
        }

        return false;
    }


}
