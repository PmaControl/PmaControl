<?php

namespace App\Library;

use \Glial\Sgbd\Sgbd;

class Proxy
{

    static public function getDbLink($id_proxysql_server)
    {

        if (!is_int(intval($id_proxysql_server))) {
            throw new \Exception("PMACTRL-856 : first parameter, id_proxysql_server should be an int (".$id_proxysql_server.") !");
        }

        $dblink = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT id from proxysql_server where id=".$id_proxysql_server.";";
        $res = $dblink->sql_query($sql);

        while ($ob = $dblink->sql_fetch_object($res)) {
            return Sgbd::sql("proxysql_".$ob->id);
        }

        throw new \Exception("PMACTRL-854 : impossible to find the server ProxySQL with id '".$id_proxysql_server."'");
    }
}