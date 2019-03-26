<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Tag
{

    static public function insertTag($id_mysql_server, $all_tags)
    {

        if (empty($all_tags)) {
            return true;
        }


        $tags = explode(',', $all_tags);

        $db = $this->di['db']->sql(DB_DEFAULT);

        foreach ($tags as $tag) {

            $id_tag = $this->getId($tag, "tag", "name", array("font" => $this->setBackgroundColor($tag), "color" => $this->setFontColor($tag)));

            $sql = "INSERT IGNORE link__mysql_server__tag (`id_mysql_server`,`id_tag`) VALUES (".$id_mysql_server.", ".$id_tag.");";
            $db->sql_query($sql);
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
}