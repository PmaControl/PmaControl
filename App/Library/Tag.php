<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


namespace App\Library;


use App\Library\Mysql;
use App\Library\Color;

class Tag
{

    static $db;

    static public function insertTag($id_mysql_server, $all_tags)
    {

        //Debug::debug($tags);

        if (empty($all_tags)) {
            return true;
        }

        $tags = explode(',', $all_tags);

        foreach ($tags as $tag) {

            $id_tag = Mysql::selectOrInsert($tag, "tag", "name", array("background" => "#".Color::setBackgroundColor($tag), "color" => "#FFFFFF"));

            //Debug::debug($id_tag,"TAG");


            $sql = "INSERT IGNORE link__mysql_server__tag (`id_mysql_server`,`id_tag`) VALUES (".$id_mysql_server.", ".$id_tag.");";
            $res = self::$db->sql_query($sql);

            if (! $res)
            {
                throw new \Exception("PMACTRL-845 : Impossible to link tags");
            }
        }

        /* id_mysql_server
          $sql = "DELETE FROM `link__mysql_server__tag` WHERE `id_mysql_server` = '".$id_mysql_server."'";
          $res = $db->sql_query($sql);
         */
    }

    //a deporter dans les test
    function testa()
    {
        $id = $this->getId("galera", "tag", "name");
        debug($id);
    }

    static public function set_db($db)
    {

        self::$db = $db;
    }
}