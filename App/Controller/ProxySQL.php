<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \App\Library\Debug;
use \App\Library\Mysql;
use \App\Library\Extraction;
use \Glial\Sgbd\Sgbd;


/*



WITH z as (select hostname, port, max(time_start_us) as time_start_us  from mysql_server_read_only_log group by hostname,port)
SELECT a.* FROM mysql_server_read_only_log a INNER JOIN z ON a.hostname = z.hostname AND a.time_start_us = z.time_start_us;

hostname,port, read_only, error
+-------------+------+------------------+-----------------+-----------+-------+
| hostname    | port | time_start_us    | success_time_us | read_only | error |
+-------------+------+------------------+-----------------+-----------+-------+
| 10.68.68.18 | 3306 | 1712519327567973 | 6710            | 0         | NULL  |
| 10.68.68.19 | 3306 | 1712519327568169 | 6603            | 1         | NULL  |
| 10.68.68.20 | 3306 | 1712519327568212 | 6532            | 1         | NULL  |
+-------------+------+------------------+-----------------+-----------+-------+


metrics classique
select * from stats.stats_memory_metrics;
select * from global_variables;
select * from stats_mysql_global;

=> explode by server 
select * from stats_mysql_connection_pool

*/

class ProxySQL extends Controller
{

    use \App\Library\Filter;
    var $clip = 0;

    //var $database = array('main', )
    //var $exclude_table = array('reset');

    public function main($param)
    {

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM `proxysql_server` ORDER BY display_name";
        $res = $db->sql_query($sql);

        $data = array();

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['proxysql'][] = $arr;
        }
        $this->set('data', $data);
    }
    /*
     * Add proxySQL (Admin interface)
     *
     *
     */

    public function add()
    {
        
        $db = Sgbd::sql(DB_DEFAULT);

        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            debug($_POST);

            if (Mysql::test($_POST['proxysql_server']['hostname'],$_POST['proxysql_server']['port'], $_POST['proxysql_server']['login'], $_POST['proxysql_server']['password']))
            {
                debug('login ok');
                debug($_POST);

                $db->sql_save($_POST);
            }
        }
    }
    /*
     * Test if it's ProxySQL Admin Module
     * return true or false
     *
     */

    public function testProxySQLAdmin($param)
    {
        Debug::parseDebug($param);

        $hostname = $param[0];
        $port     = $param[1];
        $user     = $param[2];
        $password = $param[3];

        $link = mysqli_connect($hostname.":".$port, $user, trim($password), "mysql");

        if ($link) {

            $sql  = "select @@version_comment limit 1";
            $res  = mysqli_query($link, $sql);
            while ($data = mysqli_fetch_array($res, MYSQLI_NUM)) {

                mysqli_close($link);
                if ($data[0] === "(ProxySQL Admin Module)") {
                    Debug::debug("Connection successfull, ProxySQL Admin Module detected", "[SUCCESS]");

                    return true;
                } else {
                    Debug::debug("Connection successfull, but it's not a ProxySQL Admin Module", "[ERROR]");
                    return false;
                }
            }
        } else {
            Debug::debug("Connection failed", "[ERROR]");
            return false;
        }
    }

    public function addProxyAdmin($param)
    {
        Debug::parseDebug($param);

        if ($this->testProxySQLAdmin($param)) {
            $db = Sgbd::sql(DB_DEFAULT);

            //add proxy => mysql_server
            //add admin => mysql_server
            //add proxysql admin module

            $proxysql_admin                      = "tmp".uniqid();
            $config[$proxysql_admin]['driver']   = "mysql";
            $config[$proxysql_admin]['hostname'] = $param[0];
            $config[$proxysql_admin]['port']     = $param[1];
            $config[$proxysql_admin]['user']     = $param[2];
            $config[$proxysql_admin]['password'] = $param[3];
            $config[$proxysql_admin]['crypted']  = "0";
            $config[$proxysql_admin]['database'] = "main";

            Debug::debug($config, "CONFIG");

            Sgbd::setConfig($config);

            $proxy_admin = Sgbd::sql($proxysql_admin);

            $sql = "select * from runtime_global_variables where variable_name IN('admin-cluster_username','admin-cluster_password', 'mysql-interfaces',"
                ."'mysql-monitor_username', 'mysql-monitor_password' );";

            Debug::sql($sql);

            $res = $proxy_admin->sql_query($sql);

            $variable = array();

            while ($ob = $proxy_admin->sql_fetch_object($res)) {
                $variable[$ob->variable_name] = $ob->variable_value;
            }

            Debug::debug($variable, "VARIABLE");

            // test if cluster proxySQL
            $sql2 = "select * from runtime_proxysql_servers;";
            $res2 = $proxy_admin->sql_query($sql2);

            $proxysql_to_add = array();

            $server_mysql = array();

            while ($arr2 = $proxy_admin->sql_fetch_array($res2, MYSQLI_ASSOC)) {
                //on execute sur les autre proxSQLAdmin qu'on a trouvé

                Debug::debug($arr2, "List PROXYSQL");
                Debug::debug($config[$proxysql_admin]['hostname'], "TO COMPARE");

                $table                                     = array();
                $table['proxysql_main']['id_mysql_server'] = 0;
                $table['proxysql_main']['hostname']        = $arr2['hostname'];
                $table['proxysql_main']['port']            = $config[$proxysql_admin]['port'];
                $table['proxysql_main']['login']           = $config[$proxysql_admin]['user'];
                $table['proxysql_main']['password']        = $config[$proxysql_admin]['password'];
                $table['proxysql_main']['date_inserted']   = date('Y-m-d H:i:s');

                $proxysql_to_add[] = $table;

                if ($arr2['hostname'] !== $config[$proxysql_admin]['hostname']) {
                    //$this->addProxyAdmin(array($arr2['hostname'], $arr2['port'], $config[$proxysql_admin]['user'], $config[$proxysql_admin]['password']));
                    //need be carefull if 2 IP and we access by an other one can generate and other one
                }

                /*                 * ************************************ */

                $elems = explode(':', $variable['mysql-interfaces']);

                $host = $elems[0];
                $port = $elems[1];

                if ($host === "0.0.0.0") {
                    //same host we take the one of ProySQL Admin
                    $host = $config[$proxysql_admin]['hostname'];
                } else if ($host === "::1") {
                    $host = $config[$proxysql_admin]['hostname'];
                }

                $tmp                             = array();
                $tmp['mysql_server']['hostname'] = $arr2['hostname'];
                $tmp['mysql_server']['port']     = $port;
                $tmp['mysql_server']['login']    = $variable['mysql-monitor_username'];
                $tmp['mysql_server']['password'] = $variable['mysql-monitor_password'];
                $tmp['mysql_server']['type']     = "proxysql";

                $server_mysql[$arr2['hostname']] = $tmp;

                /*                 * *************************************** */
            }

            Debug::debug($server_mysql, "ALLLLLLLLLLLLLLLLLLLLLLLLLLLL");

            //get all mysql_server from ProxySQL Admin module
            $sql3 = "select * from runtime_mysql_servers";
            $res3 = $proxy_admin->sql_query($sql3);

            while ($ob3 = $proxy_admin->sql_fetch_object($res3, MYSQLI_ASSOC)) {


                $tmp                             = array();
                $tmp['mysql_server']['hostname'] = $ob3->hostname;
                $tmp['mysql_server']['port']     = $ob3->port;
                $tmp['mysql_server']['login']    = $variable['mysql-monitor_username'];
                $tmp['mysql_server']['password'] = $variable['mysql-monitor_password'];
                $tmp['mysql_server']['type']     = "mysql";

                $server_mysql[$ob3->hostname] = $tmp;
            }

            Debug::debug($server_mysql, "MYSQL SERVER");

            foreach ($server_mysql as $server) {


                Debug::debug($server['mysql_server'], "mysql server");

                Mysql::testMySQL(array($server['mysql_server']['hostname'], $server['mysql_server']['port'], $server['mysql_server']['login'], $server['mysql_server']['password']));

                // Add server mysql to mysql_server and config file

                $found = Mysql::getIdMySqlServer(array($server['mysql_server']['hostname'], $server['mysql_server']['port']));
                Debug::debug($found, "MYSQL_SERVER");

                if ($found === false) {
                    //add mysql_server

                    $data['fqdn']     = $server['mysql_server']['hostname'];
                    $data['port']     = $server['mysql_server']['port'];
                    $data['login']    = $server['mysql_server']['login'];
                    $data['password'] = $server['mysql_server']['password'];

                    Mysql::addMysqlServer($data);
                }
            }

            //$ret = $db->sql_save($table);

            foreach ($proxysql_to_add as $key => $proxysql) {
                //if (!empty($server_mysql[$proxysql['proxysql_main']['hostname'].":".$proxysql['proxysql_main']['port']])) {
                Debug::debug($proxysql);
                $server = Mysql::getIdMySqlServer(array($proxysql['proxysql_main']['hostname'], $port));

                $proxysql_to_add[$key]['proxysql_main']['id_mysql_server'] = $server['id'];
                //}

                $ret = $db->sql_save($proxysql_to_add[$key]);

                if ($ret) {
                    Debug::debug("ProxySQL Admin Module added", "[SUCCESS]");
                } else {
                    Debug::debug("ProxySQL Admin Module failed to add", "[ERROR]");
                }
            }

            Debug::debug($proxysql_to_add, "PROXYSQL");
        }
    }

    public function index()
    {

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM proxysql_server;";

        $res = $db->sql_query($sql);

        $data = array();
        while ($arr  = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            
            $arr['servers'] = self::getServers($arr['hostname'], $arr['port'], $arr['login'] , $arr['password']);
            $var = Extraction::display(array('mysql_server::mysql_available', 'mysql_server::mysql_error'), array($arr['id_mysql_server']));
            $arr['mysql_available'] = $var[$arr['id_mysql_server']]['']['mysql_available'];
            $arr['mysql_error'] = $var[$arr['id_mysql_server']]['']['mysql_error'];
            $data['proxysql'][] = $arr;

        }


        $this->set('data', $data);
    }

    public function createTable($param)
    {
        Debug::parseDebug($param);

        $id_proxysql_server = $param[0];

        $sql = "SHOW TABLES like 'main%';";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

        }
    }


    static function getServers($hostname, $port, $login , $password)
    {
        $link = mysqli_connect($hostname.":".$port, $login, trim($password));

        $sql = "select * from runtime_mysql_servers;";
        $res = mysqli_query($link, $sql);
        
        $ret = array();

        while($arr = mysqli_fetch_array($res, MYSQLI_ASSOC)){
            $ret[] = $arr;
        }

        mysqli_close($link);

        return $ret;
    }


    /*
    USerd to link front end server 

    */
    public function associate($param)
    {
        Debug::parseDebug($param);
        $id_proysql_server = (int) $param[0] ?? 0;

        $db = Sgbd::sql(DB_DEFAULT);

        if (! empty($id_proysql_server)) {
            $sql = "SELECT * FROM `proxysql_server` WHERE `id`=".$id_proysql_server.";";
        }
        else {
            $sql = "SELECT * FROM `proxysql_server` WHERE `id_mysql_server` IS NULL;";
        }
        Debug::sql($sql);

        $res = $db->sql_query($sql);
        while($ob = $db->sql_fetch_object($res))
        {
            $proxy_admin = Sgbd::sql('proxysql_'.$ob->id);
            $ip = $ob->hostname;

            $sql2 = "SELECT * FROM `runtime_global_variables` where `variable_name` = 'mysql-interfaces';";
            Debug::sql($sql2);
            $res2 = $proxy_admin->sql_query($sql2);

            while($ob2 = $proxy_admin->sql_fetch_object($res2)) {
                $port = (int) explode(":", $ob2->variable_value)[1];
            }

            if (!empty($ip) && !empty($port))
            {
                $sql3 = "SELECT `id` FROM `mysql_server` WHERE `ip`='".$ip."' and `port`=".$port." ans is_proxy=1;";
                Debug::sql($sql3);
                $res3 = $db->sql_query($sql3);

                while($ob3 = $db->sql_fetch_object($res3)) {
                    $sql4 = "UPDATE `proxysql_server` SET `id_mysql_server`=".$ob3->id." WHERE `id`=".$ob->id.";";
                    Debug::sql($sql4);
                    $db->sql_query($sql4);
                }
            }
        }
    }
}