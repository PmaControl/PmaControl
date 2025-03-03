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

class Mysql
{
    static $master               = array();
    static $return;
    static $mysql_server         = array();
    static $mysql_server_by_host = array();

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

    static public function onAddMysqlServer($id_mysql_server = "")
    {


        self::addMaxDate();
        self::generateMySQLConfig();

//stopAll daemon
//startAll daemon
    }

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

        $sql = "INSERT IGNORE INTO `ts_max_date` ( `id_mysql_server`, `date`,`date_p1`,`date_p2`,`date_p3`,`date_p4`, `id_ts_file`)
        SELECT a.id as id_mysql_server,now(), now(),now(),now(),now(), b.id as id_ts_file 
        from mysql_server a
        INNER JOIN ts_file b 
        LEFT JOIN ts_max_date c ON a.id = c.id_mysql_server AND b.id = c.id_ts_file 
        WHERE c.id IS NULL";

        $db->sql_query($sql);

    }

    static public function getMaster($id_mysql_server, $connection_name = '')
    {

        $db      = Sgbd::sql(DB_DEFAULT);
        $masters = Extraction::display(array("slave::master_host", "slave::master_port",
                "slave::connection_name"), array($id_mysql_server));

        //debug($masters);
        
        foreach ($masters as $master) {

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

    static public function getDbLink($id_mysql_server, $name ='1')
    {
        if (!is_int(intval($id_mysql_server))) {
            throw new \Exception("PMACTRL-855 : first parameter, id_mysql_server should be an int (".$id_mysql_server.") !");
        }

        $dblink = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT name from mysql_server where id=".$id_mysql_server.";";
        $res = $dblink->sql_query($sql);

        while ($ob = $dblink->sql_fetch_object($res)) {
            return Sgbd::sql($ob->name, $name);
        }

        throw new \Exception("PMACTRL-854 : impossible to find the server with id '".$id_mysql_server."'");
    }

    static function addMysqlServer($data)
    {
        Debug::debug($data);

        $db     = Sgbd::sql(DB_DEFAULT);
        //debug($data);
        $server = array();

        if (empty($data['fqdn'])) {
            $data['fqdn'] = $data['ip'];
        }

        $ip = System::getIp($data['fqdn']);

        if (empty($ip)) {
            $ip = $data['fqdn'];
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
        $server['mysql_server']['hostname']            = $data['fqdn'];
        $server['mysql_server']['login']               = $data['login'];
        $server['mysql_server']['passwd']              = Crypt::encrypt($data['password'], CRYPT_KEY);
        $server['mysql_server']['database']            = $data['database'] ?? "mysql";
        $server['mysql_server']['is_password_crypted'] = "1";
        $server['mysql_server']['port']                = $port;

        $server['mysql_server']['is_monitored']    = $data['is_monitored'] ?? "1";
        $server['mysql_server']['is_acknowledged'] = $data['is_acknowledged'] ?? 0;
        $server['mysql_server']['ssh_port']        = $data['ssh_port'] ?? 22;
        $server['mysql_server']['ssh_login']       = $data['ssh_login'] ?? "root";
        $server['mysql_server']['is_proxy']        = $data['is_proxy'] ?? 0;
        

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

    static public function getListObject($db_link, $database, $type_object)
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
        $res = $db_link->sql_query($sql);

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
     * Récupère le id_mysql_server depuis un slave avec mater_host // master_port
     * Si besoin on lie la table mysql_server avec alias_dns, dans les cas ou la réplication se fait par un VIP, DNS ou fqdn
     * @author Aurélien LEQUOY <aurelien.lequoy@esysteme.com>
     * @license GNU/GPL
     * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
     * @param array
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
                 UNION select dns as ip, port, id_mysql_server from alias_dns b;";

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
            throw new  \Exception('PMACTRL-249 impossible to match server_id with id_mysql_server');
        }

        return $data;
    }

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


    static function getIdMysqlServerFromIpPort($ip , $port)
    {
        // with cache ?
        
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT a.id as id_mysql_server, a.ip, a.port as virtual_port, a.display_name, a.is_proxy, a.ip as ip_real, a.port as port_real
        FROM mysql_server a 
        WHERE ip = '".$ip."' AND port='".$port."'
        UNION select b.id_mysql_server, b.dns as ip, b.port, c.display_name, c.is_proxy, c.ip as ip_real, c.port as port_real
        from alias_dns b 
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
    

    static function getNameMysqlServerFromIpPort($ip , $port)
    {
        // with cache ?
        
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT a.name, a.id as id_mysql_server, a.ip, a.port as virtual_port, a.display_name, a.is_proxy, a.ip as ip_real, a.port as port_real
        FROM mysql_server a 
        WHERE ip = '".$ip."' AND port='".$port."'
        UNION select c.name, b.id_mysql_server, b.dns as ip, b.port, c.display_name, c.is_proxy, c.ip as ip_real, c.port as port_real
        from alias_dns b 
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