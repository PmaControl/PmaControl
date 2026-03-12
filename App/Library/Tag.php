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

/**
 * Class responsible for tag workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Tag {

/**
 * Handle tag state through `insertTag`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param int $id_mysql_server Input value for `id_mysql_server`.
 * @phpstan-param int $id_mysql_server
 * @psalm-param int $id_mysql_server
 * @param mixed $all_tags Input value for `all_tags`.
 * @phpstan-param mixed $all_tags
 * @psalm-param mixed $all_tags
 * @return mixed Returned value for insertTag.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @see self::insertTag()
 * @example /fr/tag/insertTag
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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

}

