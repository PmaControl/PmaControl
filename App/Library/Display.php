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
    static $server      = array();
    static $ts_variable = array();

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

        $url1 = '';
        $url2 = '';

        if (!empty($url)) {
            $url1 = '<a href="'.$url.'">';
            $url2 = '</a>';
        }

        $ret = '<span title="'.self::$server[$id_mysql_server]['libelle'].'" class="label label-'.self::$server[$id_mysql_server]['class'].'">'.self::$server[$id_mysql_server]['letter'].'</span>'
            ." ".$url1.self::$server[$id_mysql_server]['display_name'].$url2.' ';

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

    static public function icon($icon)
    {
        return str_replace(array('[IMG]', '{IMG}'), IMG, $icon);
    }

    static public function icon32($icon)
    {
        $icon = preg_replace('/height="(\d+)"/', 'height="32"', $icon);
        $icon = preg_replace('/width="(\d+)"/', 'width="32"', $icon);

        return str_replace(array('[IMG]', '{IMG}'), IMG, $icon);
    }

    static public function ts_variable($id_ts_variable)
    {
        if (empty(self::$ts_variable)) {
            $db = Sgbd::sql(DB_DEFAULT);

            $sql = "SELECT id, name from ts_variable";

            $res = $db->sql_query($sql);

            while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                self::$ts_variable[$arr['id']] = $arr;
            }
        }

        $ret = self::$ts_variable[$id_ts_variable]['name'];

        return $ret;
    }
}