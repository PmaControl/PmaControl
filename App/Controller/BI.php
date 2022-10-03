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
            WHERE is_available=1 and is_proxy=0 and error = ''";

        if (!empty($environment)) {
            $sql .= " AND b.Libelle='".$environment."'";
        }

        $res = $db->sql_query($sql);

        $i  = 0;
        while ($ob = $db->sql_fetch_object($res)) {


            $link  = Mysql::getDbLink($ob->id);
            $field = $param[0];

            $sql3      = "SELECT @@global.read_only as read_only;";
            $res3      = $link->sql_query($sql3);
            $read_only = 0;
            while ($ob3       = $link->sql_fetch_object($res3)) {

                $read_only = $ob3->read_only;
            }

            if ($read_only == 1) {
                continue;
            }


            $sql2 = "SELECT TABLE_SCHEMA as TABLE_SCHEMA, TABLE_NAME as TABLE_NAME, COLUMN_NAME as COLUMN_NAME, COLUMN_TYPE as COLUMN_TYPE "
                ."FROM information_schema.COLUMNS WHERE COLUMN_NAME = '".$field."'";

            $res2 = $link->sql_query($sql2);

            while ($ob2 = $link->sql_fetch_object($res2)) {
                $i++;
                echo $i."\t".$ob->display_name."\t".$ob->ip.":".$ob->port."\t".$ob2->TABLE_SCHEMA." ".$ob2->TABLE_NAME."\n";
            }
        }
    }
}