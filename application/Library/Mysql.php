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

    static public function addMaxDate($db)
    {
        $sql1 = "select * from ts_file;";
        $res1 = $db->sql_query($sql1);

        while ($ob1 = $db->sql_fetch_object($res1)) {
            $sql5 = "INSERT IGNORE INTO `ts_max_date` (`id_daemon_main`, `id_mysql_server`, `date`,`date_p1`,`date_p2`,`date_p3`,`date_p4`, `id_ts_file`) "
                ."SELECT 7,id, now(), now(),now(),now(),now(), ".$ob1->id." from mysql_server;";
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

    static public function getDbLink($dblink, $id_mysql_server)
    {

        $sql = "SELECT name from mysql_server where id=".$id_mysql_server.";";
        $res = $dblink->sql_query($sql);

        while ($ob = $dblink->sql_fetch_object($res)) {
            $name = $ob->name;
        }

        return $name;
    }

    static function addMysqlServer($data)
    {
        Debug::debug($data);

        $db = self::$db;



	debug($data);

        $server                              = array();
        $server['mysql_server']['id_client'] = self::selectOrInsert($data['organization'] ?? "none", "client", "libelle");



        $server['mysql_server']['id_environment']      = self::selectOrInsert($data['environment'], "environment", "libelle",
                array("key" => strtolower(str_replace(' ', '', $data['environment'])), "class" => "info", "letter" => substr(strtoupper($data['environment']), 0, 1)));
        $server['mysql_server']['name']                = "server_".uniqid();
        $server['mysql_server']['display_name']        = self::getHostname($data['display_name'], $data);
        $server['mysql_server']['ip']                  = System::getIp($data['fqdn']);
        $server['mysql_server']['hostname']            = $data['fqdn'];
        $server['mysql_server']['login']               = $data['login'];
        $server['mysql_server']['passwd']              = Crypt::encrypt($data['password'], CRYPT_KEY);
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

        //Debug::debug($server, "new MySQL");


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

            self::$return['mysql']['failed'][] = $server['mysql_server']['display_name']." (".$server['mysql_server']['hostname'].':'.$server['mysql_server']['port'].") : "
                .json_encode(array($db->sql_error(), $server));
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
SET autocommit = 0;
IF (SELECT 1 FROM `".$table_name."` WHERE `".$field."`='".$db->sql_real_escape_string($value)."') THEN
BEGIN
    SELECT `id` FROM `".$table_name."` WHERE `".$field."`='".$db->sql_real_escape_string($value)."';
END;
ELSE
BEGIN
    INSERT INTO `".$table_name."` (`".$field."` ".$list_key.") VALUES('".$db->sql_real_escape_string($value)."' ".$list_val.");
    SELECT LAST_INSERT_ID() AS id;
END;
END IF;
SET autocommit = 1;";

        echo "\n\n\n\n\n\n";
        Debug::sql($sql);

        if ($db->sql_multi_query($sql)) {

            $i = 1;




            do {
                $res = $db->sql_store_result();

                Debug::debug($res);


                if ($res) {
                    while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {


                        Debug::debug($row);

                        if (!empty($row['id'])) {
                            return $row['id'];
                        }
                    }
                    $db->sql_free_result($res);
                } else {

                    Debug::debug($db->sql_error());
                }




                Debug::debug($db->sql_more_results());
                if ($db->sql_more_results()) {
                    printf("-----------------\n");
                }
            } while ($db->sql_more_results() && $db->sql_next_result());
            /*             * ** */


            /*
              do {
              //echo "<br><br>", key($queries), ": ", current($queries);  // display key:value @ pointer

              if ($result = mysqli_store_result($mysqli)) {   // if a result set
              while ($rows = mysqli_fetch_assoc($result)) {
              echo "<br>Col = {$rows["Col"]}";
              }
              mysqli_free_result($result);
              }
              echo "<br>Rows = ", mysqli_affected_rows($mysqli); // acts like num_rows on SELECTs
              } while (next($queries) && mysqli_more_results($mysqli) && mysqli_next_result($mysqli));
              /* */



            if ($error = $db->sql_error()) {
                echo "Syntax Error: \n $error";  // display array pointer key:value
            }


            /*
              do {


              if ($i > 1) {
              $db->sql_next_result();
              }

              if ($res = $db->sql_store_result()) {
              while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {


              debug($row);
              if (!empty($row['id'])) {
              return $row['id'];
              }
              }
              }
              if ($db->sql_more_results()) {
              printf("-----------------\n");
              }




              $i++;
              } while ($db->sql_more_results());
             *
             *
             */
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
            throw new Exception('PMACTRL-274 : DB Mysql::set_db() is not instantiate');
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

            debug($data);

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

        Debug::sql($sql);

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

        while ($ar  = $db->sql_fetch_object($res)) {
            $ob = $ar;
        }

        

        return $ob;
    }
}
