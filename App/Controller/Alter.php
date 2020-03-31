<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use App\Library\Debug;
use App\Library\Extraction;
use App\Library\System;

class Alter extends Controller
{

    public function dropsp($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * from mysql_server where id_client = 5 and display_name like 'pmaria%'";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {


            Debug::debug($ob->display_name, 'Display name');
            $remote = Sgbd::sql($ob->name);

            $remote->sql_query("DROP PROCEDURE IF EXISTS criteoadmin_db.mask_users;");
            $remote->sql_query("DROP PROCEDURE IF EXISTS criteoadmin_db.unmask_users;");
//            $remote->sql_query("SET sql_log_bin=0;");
//            $remote->sql_query("ALTER TABLE heartbeat.heartbeat CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;");
//            ALTER TABLE heartbeat.heartbeat CHARACTER SET utf8 COLLATE utf8_general_ci;
//            ALTER TABLE heartbeat.heartbeat CONVERT TO utf8 SET utf8 COLLATE utf8_general_ci;
        }
    }

    public function slave($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * from mysql_server where id_client = 5 and display_name like 'pmaria%'";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {


            Debug::debug($ob->display_name, 'Display name');
            $remote = Sgbd::sql($ob->name);

            $gg = $remote->isSlave();
//Debug::debug($gg);

            if ($gg != false) {
                Debug::debug("slave");

                $remote->sql_query("STOP SLAVE;");
                $remote->sql_query("SET GLOBAL read_only = 1;");
                $remote->sql_query("SET GLOBAL replicate_wild_ignore_table = '';");
                $remote->sql_query("START SLAVE;");
            }

//$remote->sql_query("DROP PROCEDURE IF EXISTS criteoadmin_db.mask_users;");
//$remote->sql_query("DROP PROCEDURE IF EXISTS criteoadmin_db.unmask_users;");
//            $remote->sql_query("SET sql_log_bin=0;");
//            $remote->sql_query("ALTER TABLE heartbeat.heartbeat CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;");
//            ALTER TABLE heartbeat.heartbeat CHARACTER SET utf8 COLLATE utf8_general_ci;
//            ALTER TABLE heartbeat.heartbeat CONVERT TO utf8 SET utf8 COLLATE utf8_general_ci;
        }
    }

    public function user($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * from mysql_server where id_client = 5 and display_name like 'pmaria%'";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {


            Debug::debug($ob->display_name, 'Display name');
            $remote = Sgbd::sql($ob->name);

            $gg = $remote->isSlave();


            if ($gg === false) {
                Debug::debug("isMAster");
                $sql2 = "SELECT user,host from mysql.user where user not in ('adminprod', 'root')";


                $res2 = $remote->sql_query($sql2);

                while ($ob2 = $remote->sql_fetch_object($res2)) {

                    $sql3 = "SHOW GRANTS FOR '".$ob2->user."'@'".$ob2->host."';\n";
                    $res3 = $remote->sql_query($sql3);

                    while ($ob3 = $remote->sql_fetch_array($res3, MYSQLI_NUM)) {

                        $remote->sql_query($ob3[0]);
                    }
                }
            }
        }
    }

    public function dropRoot($param)
    {
        Debug::parseDebug($param);
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * from mysql_server where id_client = 5 and display_name like 'pmaria%'";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            Debug::debug($ob->display_name, 'Display name');
            $remote = Sgbd::sql($ob->name);
            $sql2 = "SELECT user,host from mysql.user where user = 'root' and host like 'pma%';";

            $res2 = $remote->sql_query($sql2);

            while ($ob2 = $remote->sql_fetch_object($res2)) {

                Debug::debug($ob2->host);
                $remote->sql_query("SET SQL_LOG_BIN=0;");
                $remote->sql_query("DROP USER 'root'@'".$ob2->host."';");
            }
        }
    }
}