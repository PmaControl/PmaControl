<?php

namespace App\Library;

//use App\Library\Extraction;
use App\Library\Extraction2;


class Available
{
    const MYSQL_AVAILABLE = "mysql_available";
    const PERFORMANCE_SCHEMA = "performance_schema";
    static array $mysql_available = array();

    static array $performance_schema = array();

    static array $engines = array();


    public static function getMySQL($id_mysql_server = 0)
    {
        if (empty(self::$mysql_available))
        {
            $servers = Extraction2::display(array(self::MYSQL_AVAILABLE));
            foreach($servers as $id_server => $server){
                if ($server[self::MYSQL_AVAILABLE] === "1"){
                    self::$mysql_available[$id_server] = $id_server;
                }
            }
        }

        if ($id_mysql_server === 0){
            //add fake id=0 to prevent no one server available
            if (count(self::$mysql_available) === 0) {
                $list = "0";
            }
            else {
                $list = implode(',' ,self::$mysql_available);
            }
            return $list;
        }
        else{
            if (in_array($id_mysql_server, self::$mysql_available)){
                return true;
            }
            else {
                return false;
            }
        }
    }

    public static function getPS($id_mysql_server = 0)
    {
        if (empty(self::$performance_schema))
        {
            $servers = Extraction2::display(array(self::PERFORMANCE_SCHEMA));

            Debug::debug($servers);

            foreach($servers as $id_server => $server){
                if ($server[self::PERFORMANCE_SCHEMA] === "ON"){
                    self::$performance_schema[$id_server] = $id_server;
                }
            }
        }

        if ($id_mysql_server === 0){
            //add fake id=0 to prevent no one server available
            if (count(self::$performance_schema) === 0) {
                $list = "0";
            }
            else {
                $list = implode(',' ,self::$performance_schema);
            }
            return $list;
        }
        else{
            if (in_array($id_mysql_server, self::$performance_schema)){
                return true;
            }
            else {
                return false;
            }
        }
    }


    public static function hasEngine($id_mysql_server, $engine)
    {
        $engine = strtoupper($engine);

        if (empty(self::$engines[$id_mysql_server]))
        {
            $db = Mysql::getDbLink($id_mysql_server, "TEST_ENGINE_".$id_mysql_server);
            $sql = "SELECT UPPER(ENGINE) as engine from information_schema.engines WHERE SUPPORT in ('YES', 'DEFAULT');";

            $res = $db->sql_query($sql);
            while($ob = $db->sql_fetch_object($res))
            {
                self::$engines[$id_mysql_server][$ob->engine] = 1;
            }
            $db->sql_close($db);
        }
        else
        {
            if (empty(self::$engines[$id_mysql_server][$engine]))
            {
                return false;
            }
            else{
                return true;
            }
        }
    }

}