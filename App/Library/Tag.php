<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use App\Library\Mysql;
use App\Library\Color;
use \Glial\Sgbd\Sgbd;

class Tag {

    static public function insertTag($id_mysql_server, $all_tags) {

        $db = Sgbd::sql(DB_DEFAULT);

        Debug::debug($all_tags);

        if (empty($all_tags)) {
            return true;
        }


        if (!is_array($all_tags)) {
            $all_tags = explode(',', $all_tags);
        }

        if (!empty($all_tags)) {
            foreach ($all_tags as $tag) {

                $id_tag = Mysql::selectOrInsert($tag, "tag", "name", array("background" => "#" . Color::setBackgroundColor($tag), "color" => "#FFFFFF"));
                //Debug::debug($id_tag,"TAG");

                $sql2 = "SELECT count(1) as cpt from link__mysql_server__tag WHERE `id_mysql_server`=" . $id_mysql_server . " AND `id_tag`=" . $id_tag . "";
                $res2 = $db->sql_query($sql2);

                while ($ob2 = $db->sql_fetch_object($res2)) {
                    $cpt = $ob2->cpt;
                }

                if ($cpt === "0") {
                    $sql = "INSERT IGNORE link__mysql_server__tag (`id_mysql_server`,`id_tag`) VALUES (" . $id_mysql_server . ", " . $id_tag . ");";
                    $res = $db->sql_query($sql);

                    if (!$res) {
                        throw new \Exception("PMACTRL-845 : Impossible to link tags");
                    }
                }
            }
        }

        /* id_mysql_server
          $sql = "DELETE FROM `link__mysql_server__tag` WHERE `id_mysql_server` = '".$id_mysql_server."'";
          $res = $db->sql_query($sql);
         */
    }

    //a deporter dans les test
    function testa() {
        $id = $this->getId("galera", "tag", "name");
        debug($id);
    }

}
