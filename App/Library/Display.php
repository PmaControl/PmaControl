<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use \Glial\Sgbd\Sgbd;

class Display
{
    static $server = array();

    static public function server($arr)
    {
        return '<span title="'.$arr['libelle'].'" class="label label-'.$arr['class'].'">'.$arr['letter'].'</span>'
            .' <a href="">'.$arr['display_name'].'</a> <small class="text-muted">'.$arr['ip'].'</small>';
    }

    static public function srv($id_mysql_server, $withip = true, $url = '')
    {
        if (empty(self::$server)) {
            $db = Sgbd::sql(DB_DEFAULT);

            $sql = "SELECT a.*, b.libelle as organization,c.*, a.id as id_mysql_server
            FROM mysql_server a            
            INNER JOIN client b ON a.id_client = b.id
            INNER JOIN environment c ON a.id_environment = c.id";

            $res = $db->sql_query($sql);

            while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                self::$server[$arr['id_mysql_server']] = $arr;
            }
        }

        $ret = '<span title="'.self::$server[$id_mysql_server]['libelle'].'" class="label label-'.self::$server[$id_mysql_server]['class'].'">'.self::$server[$id_mysql_server]['letter'].'</span>'
            .' <a href="">'.self::$server[$id_mysql_server]['display_name'].'</a> ';

        if ($withip) {
            $ret .= '<small class="text-muted">'.self::$server[$id_mysql_server]['ip'].'</small> ';
        }

        return $ret;
    }

    static public function srvjs($id_mysql_server)
    {


        if (empty(self::$server)) {
            $db = Sgbd::sql(DB_DEFAULT);

            $sql = "SELECT a.*, b.libelle as organization,c.*, a.id as id_mysql_server
            FROM mysql_server a
            INNER JOIN client b ON a.id_client = b.id
            INNER JOIN environment c ON a.id_environment = c.id";

            $res = $db->sql_query($sql);

            while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                self::$server[$arr['id_mysql_server']] = $arr;
            }
        }

        $ret = self::$server[$id_mysql_server]['display_name'];




        return $ret;
    }
}