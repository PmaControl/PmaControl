<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \App\Library\Debug;
use App\Library\Chiffrement;
use App\Library\Mysql;
use App\Library\System;
use App\Library\Diff;
//generate UUID avec PHP
//documentation ici : https://github.com/ramsey/uuid
use Ramsey\Uuid\Uuid;
use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;

//TODO : metre un  sysème de tab pour éviter d'être perdu


class Database extends Controller
{
    var $log_file = TMP."log/";

    public function index()
    {
        
    }

    public function create()
    {
        $db = Sgbd::sql(DB_DEFAULT);

        if ($_SERVER['REQUEST_METHOD'] == "POST") {


            if (!empty($_POST['database'][__FUNCTION__])) {

                $compte       = array();
                $tmp_password = array();

                $sql = "SELECT a.*,b.key FROM mysql_server a
                    INNER JOIN environment b ON a.`id_environment` = b.id

                 WHERE a.id in(".implode(",", $_POST['database']['id_mysql_server']).");";
                $res = $db->sql_query($sql);

                while ($ob = $db->sql_fetch_object($res)) {

                    $db_remote = Sgbd::sql($ob->name);
                    $databases = explode(",", $_POST['database']['name']);

                    foreach ($databases as $database) {
                        $database = trim($database);

                        if (!empty($database)) {



                            $sql = "CREATE DATABASE IF NOT EXISTS `".$database."` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
                            $db_remote->sql_query($sql);


                            //$sql = "set sql_log_bin =0;";
                            //$db_remote->sql_query($sql);




                            if (empty($_POST['database']['id_mysql_privilege'])) {

                                if (in_array($ob->key, array("prod", "preprod"))) {
                                    $droits = "SELECT, INSERT, UPDATE, DELETE";
                                } else {
                                    $droits = "ALL";
                                }
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

                            $data['compte'][] = "Server : ".$ob->ip.":".$ob->port." - ".$database.".maria.db.".$ob->key.".wideip - login : ".$user." / password : ".$password." Database : ".$database;
                        }
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

    public function refresh($param)
    {

        //Debug::$debug = true;
        Debug::parseDebug($param);


        $this->di['js']->code_javascript('$("#database-id_mysql_server__from").change(function () {
    data = $(this).val();
    $("#database-list").load(GLIAL_LINK+"common/getDatabaseByServer/" + data + "/ajax>true/",
       function(){
	$("#database-list").selectpicker("refresh");
    });
});
');

        if ($_SERVER['REQUEST_METHOD'] === "POST") {

            if (!empty($_POST['database'][__FUNCTION__])) {
                if (!empty($_POST['database']['id_mysql_server__from']) && !empty($_POST['database']['id_mysql_server__target']) && !empty($_POST['database']['list']) && !empty($_POST['database']['path'])) {

                    $id_mysql_server__source      = $_POST['database']['id_mysql_server__from'];
                    $id_mysql_server__destination = $_POST['database']['id_mysql_server__target'];
                    $databases                    = implode(',', $_POST['database']['list']);
                    $path                         = $_POST['database']['path'];



                    $debug = "";
                    if (Debug::$debug === true) {
                        $debug = "--debug";
                    }


                    $elems = array($id_mysql_server__source, $id_mysql_server__destination, $databases, $path, $debug);
                    $this->addRefresh($elems);

                    header("location: ".LINK."job/index");
                }
            }
        }


        $data['listdb1'] = array();
        $this->set('data', $data);
    }
    /*
     * example : ./glial database databaseRefresh  82 83 drupal_home '/mysql/backup'
     *
     *
     */

    public function databaseRefresh($param)
    {

        Debug::parseDebug($param);

        $id_mysql_server__source = $param[0];
        $id_mysql_server__target = $param[1];
        $databases               = explode(",", $param[2]);
        $path                    = $param[3];
        $uuid                    = $param[4];




        $directory = $path."/".uniqid();


        if (count($databases) > 1) {

            $database = "ALL";
        } else {
            $database = end($databases);
        }

        $this->databaseDump(array($id_mysql_server__source, $database, $directory));

        //shell_exec("cd ".$directory." && rename 's///g' ".);

        $metadata = file_get_contents($directory."/metadata");

        echo $metadata."\n";


        //Mysql::set_db($db);
        //$ob = Mysql::getServerInfo($id_mysql_server__source);
        //echo "CHANGE MASTER TO MASTER_HOST='".$ob->ip."', MASTER_PORT=".$ob->port.", MASTER_USER='', MASTER_PORT='',
        //    MASTER_LOG_FILE='".gg."', MASTER_LOG_POS=;\n";

        $this->databaseLoad(array($id_mysql_server__target, implode(",", $databases), $directory));

        \Glial\Synapse\FactoryController::addNode("Job", "callback", array($uuid), Glial\Synapse\FactoryController::RESULT);


        shell_exec("rm -rvf ".$directory);
    }
    /*
     * example
     *
     *
     *
     */

    public function databaseDump($param)
    {

        Debug::parseDebug($param);


        $id_mysql_server = $param[0];
        $database        = $param[1];
        $path            = $param[2];

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server WHERE id = ".$id_mysql_server.";";
        $res = $db->sql_query($sql);
        while ($ar  = $db->sql_fetch_object($res)) {
            $ob = $ar;
        }

        $db->sql_close();

        if (!empty($ob)) {
            $password = Chiffrement::decrypt($ob->passwd);
            $to_dump  = "";

            if ($database != "ALL") {
                $to_dump = " -B '".$database."' ";
            }
            $cmd = "mydumper -h ".$ob->ip." -u ".$ob->login." -p ".$password." -P ".$ob->port." ".$to_dump." -G -E -R -o ".$path." 2>&1 ";
            Debug::debug($cmd);

            $msg = shell_exec($cmd);

            echo $msg;

            return true;
        }

        throw new \Exception("PMACTRL-387 : Impossible to find the MySQL server with the id : ".$id_mysql_server);
    }
    /*
     *
     *
     * example : 
     */

    public function databaseLoad($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $databases       = $param[1];
        $path            = $param[2];

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server WHERE id = ".$id_mysql_server.";";

        $res = $db->sql_query($sql);


        while ($ar = $db->sql_fetch_object($res)) {
            $ob = $ar;
        }


        if (!empty($ob)) {

            $db_to_load = Sgbd::sql($ob->name);

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

                $cmd = "myloader -h ".$ob->ip." -u ".$ob->login." -p ".$password." -P ".$ob->port." -o $to_dump -d ".$path." 2>&1";
                Debug::debug($cmd, "cmd");
                $msg = shell_exec($cmd);

                echo $msg;
            }

            return true;
        }

        throw new \Exception("PMACTRL-387 : Impossible to find the MySQL server with the id : ".$id_mysql_server);
    }

    public function rename($param)
    {

        $this->title = '<i class="fa fa-wpforms" aria-hidden="true"></i> '.__("Rename database");

        $this->di['js']->code_javascript('$("#rename-id_mysql_server").change(function () {
    data = $(this).val();
    $("#rename-database").load(GLIAL_LINK+"common/getDatabaseByServer/" + data + "/ajax>true/",
       function(){
	$("#rename-database").selectpicker("refresh");
    });
});');

        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            if (!empty($_POST['rename']['new_name']) && !empty($_POST['rename']['database']) && !empty($_POST['rename']['id_mysql_server'])) {

                $_POST['rename']['adjust_privileges'] ?? '';

                $nb_renamed = $this->move(array($_POST['rename']['id_mysql_server'], $_POST['rename']['database'], $_POST['rename']['new_name'], $_POST['rename']['adjust_privileges']));

                header('location: '.LINK.$this->getClass().'/'.__FUNCTION__.'/renamed:tables:'.$nb_renamed);
            }
        }
    }

    public function move($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $OLD_DB          = $param[1];
        $NEW_DB          = $param[2];
        $AP              = $param[3] ?? "";


        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server where id=".$id_mysql_server;
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            $db2 = Sgbd::sql($ob->name);


            $db2->sql_select_db($OLD_DB);

            $res3 = $db2->sql_query("select DEFAULT_CHARACTER_SET_NAME from information_schema.SCHEMATA where SCHEMA_NAME= '".$OLD_DB."';");
            while ($ob3  = $db2->sql_fetch_object($res3)) {

                $db2->sql_query("CREATE DATABASE IF NOT EXISTS `".$NEW_DB."` DEFAULT CHARACTER SET ".$ob3->DEFAULT_CHARACTER_SET_NAME);
            }

            // backup trigger view

            $db2->sql_select_db($OLD_DB);

            $sql6 = "SHOW TRIGGERS FROM `".$OLD_DB."`";
            $res6 = $db2->sql_query($sql6);

            $triggers = array();
            while ($ob6      = $db2->sql_fetch_array($res6, MYSQLI_ASSOC)) {

                $sql21 = "SHOW CREATE TRIGGER `".$OLD_DB."`.`".$ob6['Trigger']."`";
                $res21 = $db2->sql_query($sql21);

                while ($ob21 = $db2->sql_fetch_array($res21, MYSQLI_ASSOC)) {

                    $triggers[$ob6['Trigger']] = str_replace('@'.$OLD_DB.'.', '@'.$NEW_DB.'.', $ob21['SQL Original Statement']).";";
                }

                $sql8 = "DROP TRIGGER `".$ob6['Trigger']."`;";
                Debug::debug($sql8);

                $db2->sql_query($sql8);
            }


            // VIEW
            //get Orderby
            // dependance des vues entre elles

            $sql20 = "SELECT  views.TABLE_NAME As `View`, tab.TABLE_NAME AS `Input`
FROM information_schema.`TABLES` AS tab
INNER JOIN information_schema.VIEWS AS views
ON views.VIEW_DEFINITION LIKE CONCAT('% `',tab.TABLE_NAME,'`%') AND tab.TABLE_SCHEMA='".$OLD_DB."' AND views.TABLE_SCHEMA='".$OLD_DB."' AND tab.TABLE_TYPE = 'VIEW'
UNION
SELECT views.TABLE_NAME As `View`, tab.TABLE_NAME AS `Input`
FROM information_schema.`TABLES` AS tab
INNER JOIN information_schema.VIEWS AS views
ON views.VIEW_DEFINITION LIKE CONCAT('%`',tab.TABLE_SCHEMA,'`.`',tab.TABLE_NAME,'`%') AND tab.TABLE_SCHEMA='".$OLD_DB."' AND views.TABLE_SCHEMA='".$OLD_DB."' AND tab.TABLE_TYPE = 'VIEW';";


            Debug::debug(SqlFormatter::format($sql20));


            $res20 = $db2->sql_query($sql20);

            $childs    = array();
            $fathers   = array();
            $relations = array();
            while ($ob20      = $db2->sql_fetch_array($res20, MYSQLI_ASSOC)) {


                $fathers[]                  = $ob20['View'];
                $childs[]                   = $ob20['Input'];
                $relations[$ob20['View']][] = $ob20['Input'];
            }

            Debug::debug($relations, "Relations");

            $level = array();
            $i     = 0;
            while ($last  = count($relations) != 0) {

                $temp = $relations;

                foreach ($temp as $father_name => $tab_father) {
                    foreach ($tab_father as $key_child => $table_child) {
                        if (!in_array($table_child, array_keys($relations))) {

                            if (empty($level[$i]) || !in_array($table_child, $level[$i])) {
                                $level[$i][] = $table_child;
                            }
                            unset($relations[$father_name][$key_child]);
                        }
                    }
                }
                $temp = $relations;

                // retirer les tableaux vides, et remplissage avec clefs
                foreach ($temp as $key => $tmp) {
                    if (count($tmp) == 0) {
                        unset($relations[$key]);
                        if (empty($level[$i + 1]) || !in_array($key, $level[$i + 1])) {
                            $level[$i + 1][] = $key;
                        }
                    }
                }

                if ($last == count($relations)) {
                    $cas_found = false;

                    //cas de deux chemins differents pour arriver à la même table enfant
                    $temp = $relations;
                    foreach ($temp as $key1 => $tab2) {
                        foreach ($tab2 as $key2 => $val) {
                            foreach ($level as $tab3) {
                                if (in_array($val, $tab3)) {
                                    unset($relations[$key1][$key2]);
                                    $cas_found = true;
                                }
                            }
                        }
                    }

                    if (!$cas_found) {
                        echo "\n";
                        debug($tab2);
                        debug($level);
                        debug($relations);
                        throw new \Exception("PMACTRL-334 Circular definition (elem <-> elem)");
                    }
                }

                sort($level[$i]);
                $i++;
            }


            Debug::debug($level);



            $sql9 = "select table_name
                FROM information_schema.tables
                where table_schema='".$OLD_DB."' AND TABLE_TYPE='VIEW';";

            Debug::debug(SqlFormatter::format($sql9));

            $res9  = $db2->sql_query($sql9);
            $views = array();
            while ($ob9   = $db2->sql_fetch_array($res9, MYSQLI_ASSOC)) {

                $sql10 = "SHOW CREATE VIEW `".$OLD_DB."`.`".$ob9['table_name']."`";
                $res10 = $db2->sql_query($sql10);

                while ($ob10 = $db2->sql_fetch_array($res10, MYSQLI_ASSOC)) {
                    $views[$ob9['table_name']] = str_replace('`'.$OLD_DB.'`', '`'.$NEW_DB.'`', $ob10['Create View']);
                }


                $sql11 = "DROP VIEW `".$OLD_DB."`.`".$ob9['table_name']."`;";
                Debug::debug($sql11);
                $db2->sql_query($sql11);
            }

            // backup functions

            $functions = array();

            $sql13 = "SHOW FUNCTION STATUS where Db='".$OLD_DB."'";
            $res13 = $db2->sql_query($sql13);

            while ($ob13 = $db2->sql_fetch_object($res13)) {

                $sql14 = "SHOW CREATE function `".$OLD_DB."`.`".$ob13->Name."`";
                $res14 = $db2->sql_query($sql14);
                while ($ob14  = $db2->sql_fetch_array($res14, MYSQLI_ASSOC)) {

                    $functions[] = $ob14['Create Function'].";";
                }


                $sql15 = "DROP function `".$OLD_DB."`.`".$ob13->Name."`;";
                Debug::debug($sql15);
                $db2->sql_query($sql15);
            }


            //procedures

            $sql17 = "SHOW PROCEDURE STATUS WHERE db = '".$OLD_DB."';";
            $res17 = $db2->sql_query($sql17);

            $procedures = array();
            while ($ob17       = $db2->sql_fetch_object($res17)) {

                $sql18 = "SHOW CREATE procedure `".$OLD_DB."`.`".$ob17->Name."`";
                $res18 = $db2->sql_query($sql18);
                while ($ob18  = $db2->sql_fetch_array($res18, MYSQLI_ASSOC)) {

                    $procedures[] = $ob18['Create Procedure'].";";
                }

                $sql18 = "DROP procedure `".$OLD_DB."`.`".$ob17->Name."`;";
                Debug::debug($sql18);
                $db2->sql_query($sql18);
            }



            // DÉPLACEMENT DES TABLES

            $sql2 = "select table_name "
                ."from information_schema.tables "
                ."where table_schema='".$OLD_DB."' AND TABLE_TYPE='BASE TABLE';";

            $res2 = $db2->sql_query($sql2);

            $nb_renamed = 0;
            while ($ob2        = $db2->sql_fetch_object($res2)) {
                //SET FOREIGN_KEY_CHECKS=0;
                $sql3 = " RENAME TABLE `".$OLD_DB."`.`".$ob2->table_name."` TO `".$NEW_DB."`.`".$ob2->table_name."`;";

                Debug::debug($sql3);
                $nb_renamed += 1;
                $db2->sql_query($sql3);
            }


            $db2->sql_select_db($NEW_DB);


            //Debug::debug($triggers);


            foreach ($functions as $function) {
                $sql16 = $function;
                //Debug::debug(SqlFormatter::format($sql16));
                $db2->sql_multi_query($sql16);
            }



            foreach ($level as $niveau) {
                foreach ($niveau as $view_name) {
                    $sql12 = $views[$view_name];

                    $db2->sql_query($sql12);
                    unset($views[$view_name]);
                }
            }


            foreach ($views as $view) {
                $sql12 = $view;
                //Debug::debug($sql12);
                $db2->sql_query($sql12);
            }

            foreach ($procedures as $procedure) {
                $sql19 = $procedure;
                //Debug::debug($sql19);
                $db2->sql_multi_query($sql19);
            }


            foreach ($triggers as $trigger) {
                $sql7 = $trigger;
                //Debug::debug($sql7);
                $db2->sql_multi_query($sql7);
            }



            $grants = $this->getChangeGrant($db2, $OLD_DB, $NEW_DB);


            foreach ($grants as $grant) {
                if (!empty($AP)) {
                    $db2->sql_query($grant);

                    echo $grant."\n";
                }
            }



            // DROP DATABASE IF NO OBJECT
            $sql4 = "select count(1) as cpt from information_schema.tables where table_schema='".$OLD_DB."';";
            $res4 = $db2->sql_query($sql4);

            while ($ob4 = $db2->sql_fetch_object($res4)) {

                if ($ob4->cpt === "0") {
                    $db2->sql_query("DROP DATABASE `".$OLD_DB."`;");
                }
            }
        }

        //Debug::debugShowQueries($this->di['db']);

        return $nb_renamed;
    }

    public function create_trigger()
    {

        $db = Sgbd::sql(DB_DEFAULT);
        $db->sql_select_db("test");

        $sql = "CREATE DEFINER=`root`@`localhost` FUNCTION `version_patch`() RETURNS tinyint(3) unsigned
    NO SQL
    SQL SECURITY INVOKER
    COMMENT '\n             Description\n             -----------\n\n             Returns the patch release version of MySQL Server.\n\n             Returns\n             -----------\n\n             TINYINT UNSIGNED\n\n             Example\n             -----------\n\n             mysql> SELECT VERSION(), sys.version_patch();\n             +--------------------------------------+---------------------+\n             | VERSION()                            | sys.version_patch() |\n             +--------------------------------------+---------------------+\n             | 5.7.9-enterprise-commercial-advanced | 9                   |\n             +--------------------------------------+---------------------+\n             1 row in set (0.00 sec)\n            '
BEGIN
    RETURN SUBSTRING_INDEX(SUBSTRING_INDEX(VERSION(), '-', 1), '.', -1);
END;";

        $db->sql_multi_query($sql);
    }
    /* move to glial */

    public function dropEmptyDb($link, $dbname)
    {
        
    }

    public function testu($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql('hb01_mariaexport01');

        $users = Mysql::exportAllUser($db);

        foreach ($users as $user) {
            $pos = strpos($user, "root");

            if ($pos === false) {
                echo $user.";\n";
            }
        }


        Debug::debug($users);
    }

    public function getChangeGrant($db_link, $OLD_DB, $NEW_DB)
    {

        $grants = array();
        $revoke = array();

        $users = Mysql::exportAllUser($db_link);
        foreach ($users as $user) {
            $pos = strpos($user, $OLD_DB);

            if ($pos !== false) {
                $revoke[] = str_replace(array(" TO ", "GRANT"), array(" FROM ", "REVOKE"), $user).";";

                $grants[] = str_replace("`".$OLD_DB."`", "`".$NEW_DB."`", $user).";";
            }
        }

        $data = array_merge($revoke, $grants);

        return $data;
    }

    public function addRefresh($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server__source = $param[0];
        $id_mysql_server__target = $param[1];
        $databases               = explode(",", $param[2]);
        $path                    = $param[3];



        $uuid = Uuid::uuid4()->toString();

        $log       = TMP."log/".$this->getClass()."-".__FUNCTION__."-".uniqid().'.log';
        $log_error = TMP."log/".$this->getClass()."-".__FUNCTION__."-".uniqid().'.error.log';


        $php = explode(" ", shell_exec("whereis php"))[1];


        $cmd = $php." ".GLIAL_INDEX." ".$this->getClass()." databaseRefresh ".$id_mysql_server__source." ".$id_mysql_server__target." '"
            .implode(",", $databases)."' '".$path."' ".$uuid." --debug > ".$log." 2> ".$log_error." & echo $!";

        Debug::debug($cmd);


        $pid = trim(shell_exec($cmd));

        Debug::debug($pid, "PID");



        \Glial\Synapse\FactoryController::addNode("fff", "add", array($uuid, $param, $pid, $log, $log_error), Glial\Synapse\FactoryController::RESULT);
        \Glial\Synapse\FactoryController::addNode("Job", "add", array($uuid, $param, $pid, $log, $log_error), Glial\Synapse\FactoryController::RESULT);


        //unlink($cmd_file);

        if (!System::isRunningPid($pid)) {
            Debug::debug($pid, "The refresh failed");


            if (file_exists($log)) {
                Debug::debug(file_get_contents($log), "Debug");
            }
        } else {
            Debug::debug($pid, "process started !");
        }
    }

    public function analyze($param)
    {


        $this->di['js']->code_javascript('$("#analyze-id_mysql_server").change(function () {
    data = $(this).val();
    $("#analyze-database").load(GLIAL_LINK+"common/getDatabaseByServer/" + data + "/ajax>true/",
       function(){
	$("#analyze-database").selectpicker("refresh");
    });
});');

        if ($_SERVER['REQUEST_METHOD'] === "POST") {

            if (!empty($_POST['database'][__FUNCTION__])) {



                $this->updateStats($param);
            }
        }
    }

    public function updateStats($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $all_dbs         = $param[1];
        $db              = Sgbd::sql(DB_DEFAULT);
        $remote          = Mysql::getDbLink($id_mysql_server);

        //au cas ou une connexion est deja ouverte avec un autre database (pour prevenir un probleme de conflit avec pmacontrol)
        $res = $remote->sql_query("select database() as db");
        while ($ob  = $remote->sql_fetch_object($res)) {
            $init_db = $ob->db;
        }
        $databases = explode(',', $all_dbs);

        foreach ($databases as $database) {
            $remote->sql_select_db($database);
            $tables = $remote->getListTable()['table'];

            foreach ($tables as $table) {

                $sql = "ANALYZE TABLE `".$database."`.`".$table."`;";
                Debug::debug($sql);
                $remote->sql_query($sql);
            }
        }
        $remote->sql_select_db($init_db);
    }

    public function compare($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $redirect = false;
        if ($_SERVER['REQUEST_METHOD'] == "POST") {

            $id_server1 = empty($_POST['compare_main']['id_mysql_server__original']) ? "" : $_POST['compare_main']['id_mysql_server__original'];
            $id_server2 = empty($_POST['compare_main']['id_mysql_server__compare']) ? "" : $_POST['compare_main']['id_mysql_server__compare'];
            $db1        = empty($_POST['compare_main']['database__original']) ? "" : $_POST['compare_main']['database__original'];
            $db2        = empty($_POST['compare_main']['database__compare']) ? "" : $_POST['compare_main']['database__compare'];

            $out = $this->checkConfig($id_server1, $db1, $id_server2, $db2);

            if ($out !== true) {
                $extra = "";

                foreach ($out as $msg) {
                    $extra .= "<br />".__($msg);
                }

                $msg   = I18n::getTranslation(__("Please correct your paramaters !").$extra);
                $title = I18n::getTranslation(__("Error"));
                set_flash("error", $title, $msg);

                $redirect = true;
            }

            header('location: '.LINK.'database/compare/compare_main:id_mysql_server__original:'.$id_server1
                .'/compare_main:'.'id_mysql_server__compare:'.$id_server2
                .'/compare_main:'.'database__original:'.$db1
                .'/compare_main:'.'database__compare:'.$db2
            );
        }
        //134217728
        //375394272


        $this->di['js']->addJavascript(array("jquery-latest.min.js", "jquery.browser.min.js",
            "jquery.autocomplete.min.js", "bootstrap-select.min.js", "compare/index.js"));

        $sql     = "SELECT * FROM mysql_server WHERE `error` = '' order by `name`";
        $servers = $db->sql_fetch_yield($sql);

        $data['server'] = [];
        foreach ($servers as $server) {
            $tmp              = [];
            $tmp['id']        = $server['id'];
            $tmp['libelle']   = str_replace('_', '-', $server['name'])." (".$server['ip'].")";
            $data['server'][] = $tmp;
        }

        $data['listdb1'] = array();
        if (!empty($_GET['compare_main']['id_mysql_server__original'])) {
            $select1         = $this->getDatabaseByServer(array($_GET['compare_main']['id_mysql_server__original']));
            $data['listdb1'] = $select1['databases'];
        }

        $data['listdb2'] = array();
        if (!empty($_GET['compare_main']['id_mysql_server__compare'])) {
            $select1         = $this->getDatabaseByServer(array($_GET['compare_main']['id_mysql_server__compare']));
            $data['listdb2'] = $select1['databases'];
        }


        $data['display'] = false;

        if (count($data['listdb2']) != 0 && count($data['listdb1']) != 0) {
            if (!empty($_GET['compare_main']['database__original']) && !empty($_GET['compare_main']['database__compare'])) {

                $id_mysql_server_a = $_GET['compare_main']['id_mysql_server__original'];
                $database_a        = $_GET['compare_main']['database__original'];
                $id_mysql_server_b = $_GET['compare_main']['id_mysql_server__compare'];
                $database_b        = $_GET['compare_main']['database__compare'];

                $data['resultat'] = $this->analyse(array($id_mysql_server_a, $database_a, $id_mysql_server_b, $database_b));

                $data['display'] = true;

                //log
                //$this->di['log']->warning('[Compare] '.$_GET['compare_main']['id_mysql_server__original'].":".$_GET['compare_main']['database__original']." vs ".
                //    $_GET['compare_main']['id_mysql_server__compare'].":".$_GET['compare_main']['database__compare']."(".$_SERVER["REMOTE_ADDR"].")");
            }
        }

        $this->set('data', $data);
    }

    public function analyse($param)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $id_mysql_server_a = $param[0];
        $database_a        = $param[1];
        $id_mysql_server_b = $param[2];
        $database_b        = $param[3];


        $db_a = Mysql::getDbLink($id_mysql_server_a);
        $db_b = Mysql::getDbLink($id_mysql_server_b);

        $ob_a          = $db_a->sql_fetch_object($db_a->sql_query("SELECT database() as db"));
        $db_name_a_ori = $ob_a->db;

        $ob_b          = $db_b->sql_fetch_object($db_b->sql_query("SELECT database() as db"));
        $db_name_b_ori = $ob_b->db;

        $db_a->sql_select_db($database_a);
        $db_b->sql_select_db($database_b);


        $objects = array("TABLE", "VIEW", "TRIGGER", "FUNCTION", "PROCEDURE", "EVENT");

        $result_a = array();
        $result_b = array();



        foreach ($objects as $object) {
            $data_a[$object]   = Mysql::getListObject($db_a, $database_a, $object);
            $result_a[$object] = Mysql::getStructure($db_a, $database_a, $data_a[$object], $object);

            $data_b[$object]   = Mysql::getListObject($db_b, $database_b, $object);
            $result_b[$object] = Mysql::getStructure($db_b, $database_b, $data_b[$object], $object);

            $data_a[$object] = array_flip($data_a[$object]);
            $data_b[$object] = array_flip($data_b[$object]);


            ksort($data_a[$object]);
            ksort($data_b[$object]);
        }


        $all_objects = array_merge_recursive($data_a, $data_b);


        $data = array();

        foreach ($all_objects as $type_object => $elems) {
            foreach ($elems as $elem => $order) {


                //remplir à vide si jamais un élément s n'est pas définis d'un coté ou de l'autre
                $result_a[$type_object][$elem] = $result_a[$type_object][$elem] ?? "";
                $result_b[$type_object][$elem] = $result_b[$type_object][$elem] ?? "";


                if ($result_b[$type_object][$elem] !== $result_a[$type_object][$elem]) {
                    //$diff = Diff::compare($result_a[$type_object][$elem], $result_b[$type_object][$elem]);

                    $data[$type_object][$elem][0] = $result_a[$type_object][$elem];
                    $data[$type_object][$elem][1] = $result_b[$type_object][$elem];
                }
            }
        }



        $db_a->sql_select_db($db_name_a_ori);
        $db_b->sql_select_db($db_name_b_ori);


        return $data;
    }

    public function before($param)
    {
        Debug::parseDebug($param);
    }

    private function checkConfig($id_server1, $db1, $id_server2, $db2)
    {
        $db    = Sgbd::sql(DB_DEFAULT);
        $error = array();

        $sql = "SELECT id,name FROM mysql_server WHERE id = '".$db->sql_real_escape_string($id_server1)."';";
        $res = $db->sql_query($sql);
        if ($db->sql_num_rows($res) == 1) {
            while ($ob = $db->sql_fetch_object($res)) {
                $db_name_ori = $ob->name;
            }
        } else {
            $error[] = "The server original is unknow";
            unset($_GET['compare_main']['id_mysql_server__original']);
        }

        $sql  = "SELECT id,name FROM mysql_server WHERE id = '".$db->sql_real_escape_string($id_server2)."';";
        $res2 = $db->sql_query($sql);

        if ($db->sql_num_rows($res2) == 1) {
            while ($ob = $db->sql_fetch_object($res2)) {
                $db_name_cmp = $ob->name;
            }
        } else {
            $error[] = "The server to compare is unknow";
            unset($_GET['compare_main']['id_mysql_server__compare']);
        }

        if (count($error) !== 0) {
            return $error;
        }

        $db_ori = Sgbd::sql($db_name_ori);
        $sql    = "select count(1) as cpt from information_schema.SCHEMATA where SCHEMA_NAME = '".$db_ori->sql_real_escape_string($db1)."';";
        $res3   = $db_ori->sql_query($sql);
        $ob     = $db_ori->sql_fetch_object($res3);
        if ($ob->cpt != 1) {
            $error[] = "The database '".$db1."' original doesn't exist on server original : '".$db_name_ori."'";
        }

        $db_cmp = Sgbd::sql($db_name_cmp);
        $sql    = "select count(1) as cpt from information_schema.SCHEMATA where SCHEMA_NAME = '".$db_cmp->sql_real_escape_string($db2)."';";
        $res4   = $db_cmp->sql_query($sql);
        $ob     = $db_cmp->sql_fetch_object($res4);
        if ($ob->cpt != 1) {
            $error[] = "The database '".$db2."' original doesn't exist on server original : '".$db_name_cmp."'";
        }

        if ($id_server1 == $id_server2 && $db1 == $db2) {
            $error[] = "The databases to compare cannot be the same on same server";
        }

        if (count($error) === 0) {
            return true;
        } else {
            return $error;
        }
    }
    /*
     * used for load database from get have to delete it and find a better solution
     *
     *
     */

    function getDatabaseByServer($param)
    {
        if (IS_AJAX) {
            $this->layout_name = false;
        }

        $id_mysql_server = $param[0];

        $db_to_get_db = Mysql::getDbLink($id_mysql_server);

        $sql  = "SHOW DATABASES";
        $res2 = $db_to_get_db->sql_query($sql);

        $data['databases'] = [];
        while ($ob                = $db_to_get_db->sql_fetch_object($res2)) {
            $tmp                 = [];
            $tmp['id']           = $ob->Database;
            $tmp['libelle']      = $ob->Database;
            $data['databases'][] = $tmp;
        }

        $this->set("data", $data);
        return $data;
    }
}