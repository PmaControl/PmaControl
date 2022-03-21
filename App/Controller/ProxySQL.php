<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
//use \Glial\Cli\Color;
use \Glial\Security\Crypt\Crypt;
use App\Library\Extraction;
use \App\Library\Debug;
use \App\Library\Mysql;
use App\Library\Chiffrement;
use \Glial\Sgbd\Sgbd;
use \App\Library\Mysql as Mysql2;

class ProxySQL extends Controller
{

    use \App\Library\Filter;
    var $clip = 0;

    public function main()
    {

        $db           = Sgbd::sql(DB_DEFAULT);
        $this->title  = __("Hardware");
        $this->ariane = " > ".$this->title;
    }
    /*
     * Add proxySQL (Admin interface)
     *
     *
     */

    public function add()
    {
        $db           = Sgbd::sql(DB_DEFAULT);
        $this->title  = __("Hardware");
        $this->ariane = " > ".$this->title;
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
            Sgbd::setConfig($config);

            $proxy_admin = Sgbd::sql($proxysql_admin);

            $sql = "select * from runtime_global_variables where variable_name IN('admin-cluster_username','admin-cluster_password', 'mysql-interfaces',"
                ."'mysql-monitor_username', 'mysql-monitor_password' );";

            $res = $proxy_admin->sql_query($sql);

            $variable = array();

            while ($ob = $proxy_admin->sql_fetch_object($res)) {
                $variable[$ob->variable_name] = $ob->variable_value;
            }

            print_r($variable);

            // test if cluster proxySQL
            $sql2 = "select * from runtime_proxysql_servers;";
            $res2 = $proxy_admin->sql_query($sql2);

            while ($arr2 = $proxy_admin->sql_fetch_array($res2, MYSQLI_ASSOC)) {
                //on execute sur les autre proxSQLAdmin qu'on a trouvé

                if ($arr2['hostname'] === $config[$proxysql_admin]['hostname'])
                {
                    continue; 
                    //need be carefull if 2 IP and we access by an other one can generate and other one
                }

                $this->addProxyAdmin(array($arr2['hostname'],$arr2['port'], $config[$proxysql_admin]['user'], $config[$proxysql_admin]['password'] ));
            }

            $server_mysql = array();


            $elems = explode (':',$variable['mysql-interfaces']);

            $host = $elems[0];
            $port = $elems[1];


            if ($host === "0.0.0.0")
            {
                //same host we take the one of ProySQL Admin
                $host = $config[$proxysql_admin]['hostname'];
            }
            else if ($host === "::1")
            {
                 $host = $config[$proxysql_admin]['hostname'];
            }

            $tmp                             = array();
            $tmp['mysql_server']['hostname'] = $host;
            $tmp['mysql_server']['port']     = $port;
            $tmp['mysql_server']['login']    = $variable['mysql-monitor_username'];
            $tmp['mysql_server']['password'] = $variable['mysql-monitor_password'];
            $tmp['mysql_server']['type']     = "proxysql";


            $server_mysql[$host.":".$port] = $tmp;


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

                $server_mysql[$ob3->hostname.":".$ob3->port] = $tmp;
            }

            print_r($server_mysql);

            foreach($server_mysql as $server)
            {

                Mysql::testMySQL($server['hostname'] );


                // Add server mysql to mysql_server and config file
                
            }



            //$ret = $db->sql_save($table);

            if ($ret) {
                Debug::debug("ProxySQL Admin Module added", "[SUCCESS]");
            } else {
                Debug::debug("ProxySQL Admin Module failed to add", "[ERROR]");
            }
        }
    }
}