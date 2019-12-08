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

class Mysql
{
    static $db;
    static $return;

    static function exportAllUser($db_link)
    {
        $sql1 = "select user, host from mysql.user;";
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
        $sql1 = "select user, host from mysql.user;";
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

    static public function onAddMysqlServer($db)
    {

        self::addMaxDate($db);
        self::generateMySQLConfig($db);

//stopAll daemon
//startAll daemon
    }

    static public function generateMySQLConfig($db)
    {

        $sql = "SELECT * FROM mysql_server a ORDER BY id_client";
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

//Debug::debug($string);
        }


        if ($config != $delta) {

            file_put_contents(ROOT."/configuration/db.config.ini.php", $config);
        } else {
            echo 'VGCFGXDNGFX';
            exit;
        }
    }

    static public function addMaxDate($db, $id_mysql_server = '')
    {
        $sql1 = "select * from ts_file;";
        $res1 = $db->sql_query($sql1);

        while ($ob1 = $db->sql_fetch_object($res1)) {


            //a optimiser !!
            //$sql = "SELECT count(1) from `ts_max_date` WHERE id"




            $sql5 = "INSERT IGNORE INTO `ts_max_date` (`id_daemon_main`, `id_mysql_server`, `date`,`date_p1`,`date_p2`,`date_p3`,`date_p4`, `id_ts_file`) "
                ."SELECT 7,id, now(), now(),now(),now(),now(), ".$ob1->id." from mysql_server";



            if (!empty($id_mysql_server)) {
                $sql5 .= " WHERE id=".$id_mysql_server."";
            }

            $sql5 .= ";";

            $db->sql_query($sql5);
        }
    }

    static public function getMaster($db, $id_mysql_server, $connection_name = '')
    {

        Extraction::setDb($db);
        $masters = Extraction::display(array("slave::master_host", "slave::master_port", "slave::connection_name"), array($id_mysql_server));

        $all_masters = array();

        foreach ($masters as $master) {

//a mapper aussi avec les ip virtuel (version enterprise)
            $sql = "SELECT id FROM mysql_server where ip='".$master[$connection_name]['master_host']."' AND port='".$master[$connection_name]['master_port']."' LIMIT 1;";

            $res = $db->sql_query($sql);

            while ($ob = $db->sql_fetch_object($res)) {
                return $ob->id;
            }
        }



        return 0;
    }

    static public function getDbLink($id_mysql_server, $link = DB_DEFAULT)
    {
        /*

          if (!is_int(($id_mysql_server))) {
          throw new \Exception("PMACTRL-855 : first parameter, id_mysql_server should be an int (".$id_mysql_server.") !");
          } */

        $dblink = Sgbd::sql($link);

        $sql = "SELECT name from mysql_server where id=".$id_mysql_server.";";
        $res = $dblink->sql_query($sql);

        while ($ob = $dblink->sql_fetch_object($res)) {
            return Sgbd::sql($ob->name);
        }

        throw new \Exception("PMACTRL-854 : impossible to find the server with id '".$id_mysql_server."'");
    }

    static function addMysqlServer($data)
    {
        Debug::debug($data);

        $db     = self::$db;
        //debug($data);
        $server = array();

        if (empty($data['fqdn'])) {
            $data['fqdn'] = $data['ip'];
        }

        $ip = System::getIp($data['fqdn']);


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
                array("key" => strtolower(str_replace(' ', '', $data['environment'])), "class" => "info", "letter" => substr(strtoupper($data['environment']), 0, 1)));
        $server['mysql_server']['name']                = "server_".uniqid();
        $server['mysql_server']['display_name']        = self::getHostname($data['display_name'], array($data['fqdn'], $data['login'], $data['password'], $data['port']));
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
        $server['mysql_server']['ssh_available']   = 0;


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


                if (!empty($data['tag'])) {

                    Debug::debug($data['tag'], "Tags");

                    Tag::set_db(self::$db);



                    Tag::insertTag($id_mysql_server, $data['tag']);
                }
            }

            Mysql::onAddMysqlServer($db);
        } else {




            unset($server['mysql_server']['passwd']);

            $msg = $server['mysql_server']['display_name']." (".$server['mysql_server']['hostname'].':'.$server['mysql_server']['port'].") : "
                .json_encode(array($db->sql_error(), $server));


            Debug::debug($msg, "FAIL INSERT");

            self::$return['mysql']['failed'][] = $msg;
        }


        return $server;
    }

    static public function set_db($db)
    {

        self::$db = $db;
    }
    /*
     * to export in Glial::MySQL ?
     *
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
        $db = self::get_db();

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

    public static function get_db()
    {
        if (!empty(self::$db)) {
            return self::$db;
        } else {
            throw new \Exception('PMACTRL-274 : DB Mysql::set_db() is not instantiate');
        }
    }

    static public function isPmaControl($ip, $port)
    {

        $db = self::get_db();


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
                Debug::error($data, "Impossible to connect to");
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
        $db = self::get_db();

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
        $db = self::get_db();

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
                    debug($query);
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
}