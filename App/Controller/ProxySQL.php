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
            $table['proysql_main']['hostname'] = $param[0];
            $table['proysql_main']['port']     = $param[1];
            $table['proysql_main']['user']     = $param[2];
            $table['proysql_main']['password'] = $param[3];

            $ret = $db->sql_save($table);

            if ($ret) {
                Debug::debug("ProxySQL Admin Module added", "[SUCCESS]");
            } else {
                Debug::debug("ProxySQL Admin Module failed to add", "[ERROR]");
            }
        }
    }
}