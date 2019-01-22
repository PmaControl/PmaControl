<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;
use \App\Library\Debug;
use App\Library\Chiffrement;
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
    $("#database-list").load(GLIAL_LINK+"common/getDatabaseByServer/" + data + "/ajax>true/",
       function(){
	$("#database-list").selectpicker("refresh");
    });
});

');

        if ($_SERVER['REQUEST_METHOD'] === "POST") {


            if (!empty($_POST['database']['id_mysql_server__from']) && !empty($_POST['database']['id_mysql_server__target']) && !empty($_POST['database']['list'])) {

            }
        }





        $data['listdb1'] = array();




        $this->set('data', $data);
    }

    public function databaseRefresh($param)
    {

        Debug::parseDebug($param);


        $id_mysql_server__source = $param[0];
        $id_mysql_server__target = $param[1];
        $databases               = explode(",", $param[2]);
        $path                    = $param[3];


        $directory = $path."/".uniqid();


        if (count($databases) > 1) {

            $database = "ALL";
        } else {
            $database = end($databases);
        }

        $this->database_dump($id_mysql_server__source, $database, $directory);




        //shell_exec("cd ".$directory." && rename 's///g' ".);



        $this->database_load($id_mysql_server__target, implode(",", $databases), $directory);
    }

    public function databaseDump($param)
    {

        Debug::parseDebug($param);


        $id_mysql_server = $param[0];
        $database        = $param[1];
        $path            = $param[2];

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server WHERE id = ".$id_mysql_server.";";

        $res = $db->sql_query($sql);


        while ($ob = $db->sql_fetch_object($res)) {

            $password = Chiffrement::decrypt($ob->passwd);


            $to_dump = "";

            if ($database != "ALL") {
                $to_dump = " -B '".$database."' ";
            }
            $cmd = "mydumper -h ".$ob->ip." -u ".$ob->login." -p ".$password." ".$to_dump." -G -E -R -o ".$path." ";
            shell_exec($cmd);

            return true;
        }

        throw new \Exception("PMACTRL-387 : Impossible to find the MySQL server with the id : ".$id_mysql_server);
    }

    public function databaseLoad($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $databases       = $param[1];
        $path            = $param[2];

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server WHERE id = ".$id_mysql_server.";";

        $res = $db->sql_query($sql);


        while ($ob = $db->sql_fetch_object($res)) {

            $password = Chiffrement::decrypt($ob->passwd);


            if ($databases != "ALL") {

                $db_to_import = explode(",", $databases);
                $specify_db   = true;
            } else {

                shell_exec("rm ".$path."/mysql.*.sql");

                $specify_db   = false;
                $db_to_import = array('NA');
            }


            foreach ($db_to_import as $db_to_load) {


                $to_dump = "";
                if ($specify_db === true) {

                    if (empty($db_to_load)) {
                        continue;
                    }

                    $to_dump = '-B '.$db_to_load;
                }

                $cmd = "myloader -h ".$ob->ip." -u ".$ob->login." -p ".$password." $to_dump -d ".$path." ";
                Debug::debug($cmd, "cmd");
                shell_exec($cmd);
            }


            return true;
        }

        throw new \Exception("PMACTRL-387 : Impossible to find the MySQL server with the id : ".$id_mysql_server);
    }
}