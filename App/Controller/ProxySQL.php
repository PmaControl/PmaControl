<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \App\Library\Debug;
use \App\Library\Mysql;
use \App\Library\Extraction;
use \Glial\Sgbd\Sgbd;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;

/*
❌
ⓘ
🛈
✅
፨
⚠ⓘ

UPDATE global_variables SET variable_value='admin:password;radmin:radmin' WHERE variable_name='admin-admin_credentials';
LOAD ADMIN VARIABLES TO RUNTIME;
SAVE ADMIN VARIABLES TO DISK;

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

    const DB_STATS = 'stats';

    var $clip = 0;

    //var $database = array('main', )
    //var $exclude_table = array('reset');

    var $logger;

    static $log;

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

            if (Mysql::test($_POST['proxysql_server']['hostname'], $_POST['proxysql_server']['port'], $_POST['proxysql_server']['login'], $_POST['proxysql_server']['password'], "main")) {

                $param = array();
                $param[0] = $_POST['proxysql_server']['hostname'];
                $param[1] = $_POST['proxysql_server']['port'];
                $param[2] = $_POST['proxysql_server']['login'];
                $param[3] = $_POST['proxysql_server']['password'];

                $this->addProxyAdmin($param);

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
        $port = $param[1];
        $user = $param[2];
        $password = $param[3];

        $link = mysqli_connect($hostname . ":" . $port, $user, trim($password), "mysql");

        if ($link) {

            $sql = "select @@version_comment limit 1";
            $res = mysqli_query($link, $sql);
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

            $proxysql_admin = "tmp" . uniqid();
            $config[$proxysql_admin]['driver'] = "mysql";
            $config[$proxysql_admin]['hostname'] = $param[0];
            $config[$proxysql_admin]['port'] = $param[1];
            $config[$proxysql_admin]['user'] = $param[2];
            $config[$proxysql_admin]['password'] = $param[3];
            $config[$proxysql_admin]['crypted'] = "0";
            $config[$proxysql_admin]['database'] = "main";

            Debug::debug($config, "CONFIG");

            Sgbd::setConfig($config);

            $proxy_admin = Sgbd::sql($proxysql_admin);

            $sql = "select * from runtime_global_variables where variable_name IN('admin-cluster_username','admin-cluster_password', 'mysql-interfaces',"
                . "'mysql-monitor_username', 'mysql-monitor_password' );";

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

                $table = array();
                $table['proxysql_server']['id_mysql_server'] = 0;
                $table['proxysql_server']['hostname'] = $arr2['hostname'];
                $table['proxysql_server']['port'] = $config[$proxysql_admin]['port'];
                $table['proxysql_server']['login'] = $config[$proxysql_admin]['user'];
                $table['proxysql_server']['password'] = $config[$proxysql_admin]['password'];
                $table['proxysql_server']['date_inserted'] = date('Y-m-d H:i:s');

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

                $tmp = array();
                $tmp['mysql_server']['hostname'] = $arr2['hostname'];
                $tmp['mysql_server']['port'] = $port;
                $tmp['mysql_server']['login'] = $variable['mysql-monitor_username'];
                $tmp['mysql_server']['password'] = $variable['mysql-monitor_password'];
                $tmp['mysql_server']['type'] = "proxysql";

                $server_mysql[$arr2['hostname']] = $tmp;

                /*                 * *************************************** */
            }

            Debug::debug($server_mysql, "ALLLLLLLLLLLLLLLLLLLLLLLLLLLL");

            //get all mysql_server from ProxySQL Admin module
            $sql3 = "select * from runtime_mysql_servers";
            $res3 = $proxy_admin->sql_query($sql3);

            while ($ob3 = $proxy_admin->sql_fetch_object($res3, MYSQLI_ASSOC)) {


                $tmp = array();
                $tmp['mysql_server']['hostname'] = $ob3->hostname;
                $tmp['mysql_server']['port'] = $ob3->port;
                $tmp['mysql_server']['login'] = $variable['mysql-monitor_username'];
                $tmp['mysql_server']['password'] = $variable['mysql-monitor_password'];
                $tmp['mysql_server']['type'] = "mysql";

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

                    $data['fqdn'] = $server['mysql_server']['hostname'];
                    $data['port'] = $server['mysql_server']['port'];
                    $data['login'] = $server['mysql_server']['login'];
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
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

            $arr['servers'] = array();
            $arr['servers'] = self::getServers($arr['hostname'], $arr['port'], $arr['login'], $arr['password']);

            if (!empty($arr['id_mysql_server'])) {
                $var = Extraction::display(array('mysql_server::mysql_available', 'mysql_server::mysql_error'), array($arr['id_mysql_server']));
                $arr['mysql_available'] = $var[$arr['id_mysql_server']]['']['mysql_available'];

            }

            $arr['mysql_error'] = $var[$arr['id_mysql_server']]['']['mysql_error'] ?? "";

            $data['proxysql_error'] = $this->getErrorConnect(array($arr['id']));
            $data['proxysql'][] = $arr;
        }

        $this->set('data', $data);
    }




    static function getServers($hostname, $port, $login, $password)
    {
        $link = mysqli_connect($hostname . ":" . $port, $login, trim($password));

        $sql = "select * from runtime_mysql_servers;";
        $res = mysqli_query($link, $sql);

        $ret = array();

        while ($arr = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
            $ret[] = $arr;
        }

        mysqli_close($link);

        return $ret;
    }


    /*
    USerd to link front end server 

    */
    static public function associate($param)
    {
        Debug::parseDebug($param);
        $id_proysql_server = (int) $param[0] ?? 0;

        $db = Sgbd::sql(DB_DEFAULT);

        if (!empty($id_proysql_server)) {
            $sql = "SELECT * FROM `proxysql_server` WHERE `id`=" . $id_proysql_server . ";";
        } else {
            $sql = "SELECT * FROM `proxysql_server` WHERE `id_mysql_server` IS NULL;";
        }
        Debug::sql($sql);

        $res = $db->sql_query($sql);
        while ($ob = $db->sql_fetch_object($res)) {
            $proxy_admin = Sgbd::sql('proxysql_' . $ob->id);
            $ip = $ob->hostname;
            $id_proxysql = $ob->id;

            $sql = "SELECT * FROM global_variables WHERE variable_name IN ('mysql-monitor_username', 'mysql-monitor_password', 'mysql-interfaces');";
            $res = $proxy_admin->sql_query($sql);
            while ($arr = $proxy_admin->sql_fetch_array($res, MYSQLI_ASSOC)) {
                Debug::debug($arr, "global variable PROXYSQL");

                switch ($arr['variable_name']) {
                    case 'mysql-interfaces':
                        $port = explode(":", $arr['variable_value'])[1];
                        break;

                    case 'mysql-monitor_username':
                        $mysql_user = $arr['variable_value'];
                        break;

                    case 'mysql-monitor_password':
                        $mysql_password = $arr['variable_value'];
                        break;
                }
            }

            if (!empty($ip) && !empty($port)) {
                // on cherche le proxy
                $id_mysql_server = Mysql::getIdMysqlServerFromIpPort($ip, $port);
                Debug::debug($id_mysql_server, "id_mysql_server");

                if ($id_mysql_server === false) {
                    // on cherche un des noeud online du cluster
                    $sql3 = "SELECT DISTINCT hostgroup_id,hostname,port FROM runtime_mysql_servers;";
                    $res3 = $proxy_admin->sql_query($sql3);
                    Debug::sql($sql3);

                    while ($arr3 = $proxy_admin->sql_fetch_array($res3, MYSQLI_ASSOC)) {
                        Debug::debug($arr, "list of mysql_server");

                        $mysql_server_hostname = $arr3['hostname'];
                        $mysql_server_port = $arr3['port'];

                        self::$log->emergency("GET ID FROM => hostname : $mysql_server_hostname, port : $mysql_server_port");

                        Debug::debug($mysql_server_hostname, "HOSTNAME");
                        Debug::debug($mysql_server_port, "PORT");

                        $id_mysql_server__origin = Mysql::getIdMysqlServerFromIpPort($mysql_server_hostname, $mysql_server_port);

                        if (empty($id_mysql_server__origin)) {
                            continue;
                        }

                        $sql2 = "SELECT c.libelle as organization, b.libelle as environment
                        FROM mysql_server a 
                        INNER JOIN environment b on a.id_environment = b.id
                        INNER JOIN client c ON c.id = a.id_client WHERE a.id = " . $id_mysql_server__origin . ";";

                        $res2 = $db->sql_query($sql2);

                        while ($ob2 = $db->sql_fetch_object($res2)) {
                            $data = array();
                            $data['environment'] = $ob2->environment;
                            $data['organization'] = $ob2->organization;

                            $data['fqdn'] = $ip;
                            $data['login'] = $mysql_user;
                            $data['password'] = $mysql_password;
                            $data['port'] = $port;

                            $data['display_name'] = "ProxySQL";

                            Mysql::addMysqlServer($data);
                        }

                    }
                }

                //proxy linked
                $id_mysql_server = Mysql::getIdMysqlServerFromIpPort($ip, $port);

                if ($id_mysql_server !== false) {
                    $sql4 = "UPDATE `proxysql_server` SET `id_mysql_server`=" . $id_mysql_server . " WHERE `id`=" . $id_proxysql . ";";
                    Debug::sql($sql4);
                    $db->sql_query($sql4);
                }
            }
        }
    }

    public function statistic($param)
    {
        Debug::parseDebug($param);

        $id_proxysql = $param[0];
        $db = Sgbd::sql("proxysql_" . $id_proxysql);

        $exclude_table = array('mysql_server_connect_log', 'mysql_server_ping_log', 'mysql_server_replication_lag_log');

        $sql = "show tables in " . self::DB_STATS . ";";
        $res = $db->sql_query($sql);

        $data['tables'] = array();
        while ($ob = $db->sql_fetch_object($res)) {
            //we want escape reset table
            if (substr($ob->tables, -6) === "_reset") {
                continue;
            }

            if (in_array($ob->tables, $exclude_table)) {
                continue;
            }

            $sql2 = "SELECT * FROM `" . self::DB_STATS . "`.`" . $ob->tables . "`;";
            Debug::sql($sql2);

            $res2 = $db->sql_query($sql2);
            while ($arr = $db->sql_fetch_array($res2, MYSQLI_ASSOC)) {
                $data['tables'][$ob->tables][] = $arr;
            }

            if (empty($data['tables'][$ob->tables])) {
                $sql3 = "SHOW CREATE TABLE `" . self::DB_STATS . "`.`" . $ob->tables . "`;";
                $res3 = $db->sql_query($sql3);
                while ($arr = $db->sql_fetch_array($res3, MYSQLI_ASSOC)) {
                    $data['tables'][$ob->tables][] = $arr;
                }
            }
        }
        Debug::debug($data['tables']);
        $this->set('data', $data);
    }


    static public function getErrorConnect($param)
    {
        Debug::parseDebug($param);

        $id_proxysql_server = $param[0] ?? "";

        if (empty($id_proxysql_server)) {
            throw new \Exception('getErrorConnect should have id_proxysql_server in parameter');
        }

        $db = Sgbd::sql('proxysql_' . $id_proxysql_server);
        /*

        get all last error for each server in last : (can be 3 differents errors max by server)
        - mysql_server_replication_lag_log
        - mysql_server_connect_log
        - mysql_server_ping_log
        
        +--------------+------+----------------------------------------------------------------------------------------------------+
        | hostname     | port | error                                                                                              |
        +--------------+------+----------------------------------------------------------------------------------------------------+
        | 10.68.68.18  | 3306 | Access denied; you need (at least one of) the SUPER, SLAVE MONITOR privilege(s) for this operation |
        | 10.68.68.20  | 3306 | Access denied; you need (at least one of) the SUPER, SLAVE MONITOR privilege(s) for this operation |
        | 10.68.68.202 | 3306 | Access denied for user 'monitor'@'10.68.68.73' (using password: YES)                               |
        +--------------+------+----------------------------------------------------------------------------------------------------+
        3 rows in set (0,001 sec)
        */

        $sql = "WITH a as (SELECT hostname, port, MAX(time_start_us) AS max_time
        FROM mysql_server_connect_log
        GROUP BY hostname, port),
        c as (SELECT hostname, port, MAX(time_start_us) AS max_time
        FROM mysql_server_ping_log
        GROUP BY hostname, port),
        e as (SELECT hostname, port, MAX(time_start_us) AS max_time
        FROM mysql_server_replication_lag_log
        GROUP BY hostname, port)
    SELECT b.hostname, b.port, b.connect_error as error, 'mysql_server_connect_log' as origin FROM mysql_server_connect_log b 
    INNER JOIN a ON a.hostname = b.hostname 
    AND a.port = b.port 
    AND a.max_time = b.time_start_us WHERE b.connect_error is not null
    UNION
    SELECT d.hostname, d.port, d.ping_error as error, 'mysql_server_ping_log' as origin FROM mysql_server_ping_log d
    INNER JOIN c ON c.hostname = d.hostname 
    AND c.port = d.port 
    AND c.max_time = d.time_start_us WHERE d.ping_error is not null
    UNION
    SELECT f.hostname, f.port, f.error as error, 'mysql_server_replication_lag_log' as origin FROM mysql_server_replication_lag_log f
    INNER JOIN e ON e.hostname = f.hostname 
    AND e.port = f.port AND f.error != 'Lost connection to server during query'
    AND e.max_time = f.time_start_us WHERE f.error is not null;";

        //  f.error != 'Lost connection to server during query'  => in case of Master going off an error is keeped is query failed when trying to get slave status;

        $res = $db->sql_query($sql);

        $table = array();
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $table[] = $arr;
        }

        Debug::debug($table, "ProxySQL ERROR(S)");

        return $table;
    }



    public function import($param)
    {
        Debug::parseDebug($param);
        $id_proxysql_server = $param[0] ?? "";

        if (empty($id_proxysql_server)) {
            throw new \Exception(__FUNCTION__ . ' should have id_proxysql_server in parameter');
        }

        $db = Sgbd::sql('proxysql_' . $id_proxysql_server);
        $default = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM proxysql_server WHERE id=" . $id_proxysql_server;
        $res = $default->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $id_mysql_server = $ob->id_mysql_server;
        }

        $import = array();
        $import['table']['proxysql_connect_error'] = $this->getErrorConnect($param);
        //$import['pair']['proxysql_stats_mysql_global'] = $this->getErrorConnect($param);
        //$import['uniq']['key2::key3??']['proxysql_stats_mysql_global'] = $this->getErrorConnect($param);

        $db->sql_close();

        return $import;
    }

    public function auto($param)
    {

        Debug::parseDebug($param);
        $id_proxysql_server = $param[0] ?? "";

    }


    public function config($param)
    {
        Debug::parseDebug($param);
        $id_proxysql_server = $param[0] ?? "";

        if (empty($id_proxysql_server)) {
            throw new \Exception(__FUNCTION__ . ' should have id_proxysql_server in parameter');
        }

        $data = array();
        $db = Sgbd::sql('proxysql_' . $id_proxysql_server);

        $sqls = array();
        //admin_variables
        $sqls[] = "SELECT * FROM {PREFIX}global_variables WHERE variable_name LIKE 'admin%' ORDER BY variable_name ASC;";
        //mysql_variables
        $sqls[] = "SELECT * FROM {PREFIX}global_variables WHERE variable_name NOT LIKE 'admin%' ORDER BY variable_name ASC;";
        $sqls[] = "SELECT * FROM {PREFIX}mysql_query_rules ORDER BY rule_id ASC;";

        $sqls[] = "SELECT * FROM {PREFIX}mysql_servers ORDER BY hostgroup_id, hostname, port;";
        $sqls[] = "SELECT * FROM {PREFIX}mysql_users ORDER BY default_hostgroup, username ASC;";
        $sqls[] = "SELECT * FROM {PREFIX}proxysql_servers ORDER BY hostname ASC, port ASC;";


        $data['table'] = array();

        foreach ($sqls as $sql) {
            $prefix = array('', 'runtime_');
            foreach ($prefix as $opt) {
                $sql_finale = str_replace('{PREFIX}', $opt, $sql);
                $output_array = array();
                preg_match('/FROM\s+(\S+)/', $sql_finale, $output_array);

                $table_name = $output_array[1];

                if ($opt === '') {
                    $data['table'][] = $table_name;
                }

                $res = $db->sql_query($sql_finale);

                while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                    $data['tables'][$table_name][] = $arr;
                }
            }
        }

        $this->set('data', $data);
    }


    public function before($param)
    {
        $monolog = new Logger("ProxySQL");
        $handler = new StreamHandler(LOG_FILE, Logger::NOTICE);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $monolog->pushHandler($handler);
        $this->logger = $monolog;
        self::$log;
    }

}