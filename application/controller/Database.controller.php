<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;
use \App\Library\Debug;
use \Glial\I18n\I18n;

class Database extends Controller
{

    public function index()
    {

    }

    public function create()
    {
        $db = $this->di['db']->sql(DB_DEFAULT);

        if ($_SERVER['REQUEST_METHOD'] == "POST") {

            $compte       = array();
            $tmp_password = array();

            $sql = "SELECT * FROM mysql_server WHERE id in(".implode(",", $_POST['database']['id_mysql_server']).");";
            $res = $db->sql_query($sql);

            while ($ob = $db->sql_fetch_object($res)) {

                $db_remote = $this->di['db']->sql($ob->name);
                $databases = explode(",", $_POST['database']['name']);

                foreach ($databases as $database) {
                    $database = trim($database);

                    if (!empty($database)) {

                        $sql = "CREATE DATABASE `".$database."` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
                        $db_remote->sql_query($sql);

                        $sql = "set sql_log_bin =0;";
                        $db_remote->sql_query($sql);

                        if (empty($_POST['database']['id_mysql_privilege'])) {
                            $droits = "SELECT, INSERT, UPDATE, DELETE";
                        } else {
                            $droits = implode(', ', $_POST['database']['id_mysql_privilege']);
                        }

                        if (empty($_POST['database']['user'])) {
                            $user = $database;
                        } else {
                            $user = $_POST['database']['user'];
                        }

                        if (empty($_POST['database']['hostname'])) {
                            $hostname = "%";
                        } else {
                            $hostname = $_POST['database']['hostname'];
                        }

                        if (empty($tmp_password[$user][$database])) {
                            $password = $this->generatePassword(20);
                        } else {
                            $password = $tmp_password[$user][$database];
                        }

                        $sql = "GRANT ".$droits." ON ".$database.".* TO '".$user."'@'".$hostname."' IDENTIFIED BY '".$password."'";
                        $db_remote->sql_query($sql);

                        $data['compte'][] = "Server : ".$ob->ip.":".$ob->port." login : ".$user." / password : ".$password;
                    }
                }
            }
        }

        //a déporté dans une librairy ?
        $sql = "SELECT * FROM mysql_privilege ORDER BY `type`, `privilege`";
        $res = $db->sql_query($sql);

        $data['mysql_privilege'] = array();
        while ($ob                      = $db->sql_fetch_object($res)) {
            $tmp            = array();
            $tmp['id']      = $ob->privilege;
            $tmp['libelle'] = $ob->privilege;

            $data['mysql_privilege'][] = $tmp;
        }







        //fin de la déportation
        $this->set('data', $data);
    }

    function generatePassword($length = 32)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $count = mb_strlen($chars);

        for ($i = 0, $result = ''; $i < $length; $i++) {
            $index  = rand(0, $count - 1);
            $result .= mb_substr($chars, $index, 1);
        }

        return $result;
    }

    public function refresh()
    {


        $this->di['js']->code_javascript('$("#database-id_mysql_server__from").change(function () {
    data = $(this).val();
    $("#id_mysql_server__from-database").load(GLIAL_LINK+"common/getDatabaseByServer/" + data + "/ajax>true/",
       function(){
	$("#id_mysql_server__from-database").selectpicker("refresh");
    });
});


$("#database-id_mysql_server__target").change(function () {
    data = $(this).val();
    $("#id_mysql_server__target-database").load(GLIAL_LINK+"common/getDatabaseByServer/" + data + "/ajax>true/",
       function(){
	$("#id_mysql_server__target-database").selectpicker("refresh");
    });
});



');
        $data['listdb1'] = array();

        $this->set('data', $data);
    }
}