<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

class Mysql
{

    static function exportAllUser($db_link)
    {
        $sql1 = "select user, host from mysql.user;";
        $res1 = $db_link->sql_query($sql1);

        $users = array();
        while ($ob1   = $db_link->sql_fetch_object($res1)) {
            $sql2 = "SHOW GRANTS FOR '".$ob1->user."'@'".$ob1->host."'";
            $res2 = $db_link->sql_query($sql2);

            while ($ob2 = $db_link->sql_fetch_array($res2, MYSQLI_NUM)) {

                $users[] = $ob2[0];
            }
        }
        
        return $users;
    }
}