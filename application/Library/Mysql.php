<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use App\Library\Extraction;
use \Glial\Security\Crypt\Crypt;

class Mysql
{
    static $db;

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

    static function getIp($hostname)
    {
        $ip = shell_exec("dig +short ".$hostname);

        return trim($ip);
    }

    static function crypt($password)
    {
        Crypt::$key = CRYPT_KEY;
        $passwd     = Crypt::encrypt($password);

        return $passwd;
    }

    static function unCrypt($password_crypted)
    {
        Crypt::$key = CRYPT_KEY;
        $passwd     = Crypt::decrypt($password_crypted);

        return $passwd;
    }

    static function addMysqlServer($data)
    {
        //Debug::debug($data);

        $db = self::$db;


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

            Mysql::onAddMysqlServer($db);
        } else {

            unset($server['mysql_server']['passwd']);

            $this->return['mysql']['failed'][] = $server['mysql_server']['display_name']." (".$server['mysql_server']['hostname'].':'.$server['mysql_server']['port'].") : ".json_encode(array($db->sql_error(),
                    $server));
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
            $keys   = array_keys($list);
            $values = array_values($list);

            $list_key = ",`".implode('`,`', $keys)."`";
            $list_val = ",'".implode("','", $values)."'";
        }
        $db = self::get_db();

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

    public static function get_db()
    {
        if (!empty(self::$db)) {
            return self::$db;
        } else {
            throw new Exception('PMACTRL-274 : DB Mysql::set_db() is not instantiate');
        }
    }

    public function isPmaControl($ip, $port)
    {

        $db  = $this->di['db']->sql(DB_DEFAULT);
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
}