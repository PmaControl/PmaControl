<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use \Glial\Sgbd\Sgbd;
use \App\Library\Debug;
use \App\Library\Mysql;

class Database
{
    /*
     * stockage temporaire de la table database_size
     */
    static $size = array();

    /*
     * Renvoi le bon tag en fonction de la taille de la base de données.
     */

    static public function getTagSize($size)
    {
        if (empty(self::$size)) {
            self::$size = array();

            $db  = Sgbd::sql(DB_DEFAULT);
            $sql = "SELECT * from `database_size` order by 1 DESC;";
            $res = $db->sql_query($sql);

            while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                self::$size[$arr['min']] = $arr;
            }
        }
        if (!empty(self::$size)) {
            $mins = array_keys(self::$size);

            foreach ($mins as $min) {
                if ($size > $min) {
                    return '<span class="label" style="color:'.self::$size[$min]['color'].'; background:'.self::$size[$min]['background'].' ;">'
                        .self::$size[$min]['label'].'</span>';
                }
            }
        }
        return "";
    }

    static public function emptyDatabase($param)
    {

        Debug::parseDebug($param);

        $id_mysql_server = $param[0];

        $sql = "SELECT S.SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA S
LEFT OUTER JOIN INFORMATION_SCHEMA.TABLES T ON S.SCHEMA_NAME = T.TABLE_SCHEMA
WHERE T.TABLE_SCHEMA IS NULL;";
    }
}