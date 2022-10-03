<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Shell\Color;
use \App\Library\Debug;
use \Glial\Sgbd\Sgbd;
use \App\Library\Mysql;

//use \App\Library\System;

class BI extends Controller
{
    var $server = array(3, 24, 33, 173, 167, 21, 157);

    public function searchField($param)
    {

        $field       = $param[0];
        $environment = $param[1] ?? '';

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT a.id,a.ip,a.port,a.display_name  FROM mysql_server a
            INNER JOIN environment b ON b.id = a.id_environment
            WHERE is_available=1 and error = ''";

        if (!empty($environment)) {
            $sql .= " AND b.Libelle='".$environment."'";
        }

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            $link  = Mysql::getDbLink($ob->id);
            $field = $param[0];

            $sql2 = "SELECT TABLE_SCHEMA as TABLE_SCHEMA, TABLE_NAME as TABLE_NAME, COLUMN_NAME as COLUMN_NAME, COLUMN_TYPE as COLUMN_TYPE "
                ."FROM information_schema.COLUMNS WHERE COLUMN_NAME = '".$field."'";

            $res2 = $link->sql_query($sql2);

            while ($ob2 = $link->sql_fetch_object($res2)) {
                echo $ob->display_name." ".$ob->ip.":".$ob->port." ".$ob2->TABLE_SCHEMA." ".$ob2->TABLE_NAME."\n";
            }
        }
    }
}