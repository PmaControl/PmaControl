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
use App\Library\Mysql;
//generate UUID avec PHP
//documentation ici : https://github.com/ramsey/uuid
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

class Database extends Controller
{
    var $log_file = TMP."log/";

    public function index()
    {

    }

    public function create()
    {
        $db = $this->di['db']->sql(DB_DEFAULT);

        if ($_SERVER['REQUEST_METHOD'] == "POST") {


            if (!empty($_POST['database'][__FUNCTION__])) {

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

        Debug::$debug = true;
        Debug::parseDebug($param);


        $this->di['js']->code_javascript('$("#database-id_mysql_server__from").change(function () {
    data = $(this).val();
    $("#database-list").load(GLIAL_LINK+"common/getDatabaseByServer/" + data + "/ajax>true/",
       function(){
	$("#database-list").selectpicker("refresh");
    });
});drupal
');

        if ($_SERVER['REQUEST_METHOD'] === "POST") {

            if (!empty($_POST['database'][__FUNCTION__])) {
                if (!empty($_POST['database']['id_mysql_server__from']) && !empty($_POST['database']['id_mysql_server__target']) && !empty($_POST['database']['list']) && !empty($_POST['database']['path'])) {

                    $id_mysql_server__source      = $_POST['database']['id_mysql_server__from'];
                    $id_mysql_server__destination = $_POST['database']['id_mysql_server__target'];
                    $databases                    = $_POST['database']['list'];
                    $path                         = $_POST['database']['path'];


                    $debug = "";
                    if (Debug::$debug === true) {
                        $debug = "--debug";
                    }


                    $php = explode(" ", shell_exec("whereis php"))[1];

                    $uuid = Uuid::uuid4()->toString();
                    $log  = $this->log_file.strtolower(__CLASS__)."-".__FUNCTION__."-".uniqid().".log";

                    $callback = $php." ".GLIAL_INDEX." job callback ".$uuid."\n";
                    $cmd      = $php." ".GLIAL_INDEX." ".__CLASS__." databaseRefresh ".$id_mysql_server__source." ".$id_mysql_server__destination." '".implode(",", $databases)."' '".$path."' >> ".$log."\n";

                    $cmd_file = TMP.$uuid.".sh";

                    file_put_contents($cmd_file, "#!/bin/sh\n".$cmd.$callback);


                    shell_exec("chmod +x ".$cmd_file);

                    debug(file_get_contents($cmd_file));

                    //su - www-data -s /bin/bas

                    $batch = "bash ".$cmd_file." & echo $!";


                    // nohup command &>/dev/null &

                    Debug::debug($batch);


                    //$pid = 54274823;
                    $pid = shell_exec($batch);


                    $db = $this->di['db']->sql(DB_DEFAULT);

                    $job                      = array();
                    $job['job']['uuid']       = $uuid;
                    $job['job']['class']      = __CLASS__;
                    $job['job']['method']     = __FUNCTION__;
                    $job['job']['param']      = json_encode($_POST);
                    $job['job']['date_start'] = date("Y-m-d H:i:s");
                    $job['job']['pid']        = $pid;
                    $job['job']['log']        = $log;
                    $job['job']['status']     = "RUNNING";


                    Debug::debug($job);

                    $db->sql_save($job);


                    //header("location: ".LINK."job/index");
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


        $directory = $path."/".uniqid();


        if (count($databases) > 1) {

            $database = "ALL";
        } else {
            $database = end($databases);
        }

        $this->databaseDump(array($id_mysql_server__source, $database, $directory));

        //shell_exec("cd ".$directory." && rename 's///g' ".);

        $this->databaseLoad(array($id_mysql_server__target, implode(",", $databases), $directory));


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

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server WHERE id = ".$id_mysql_server.";";

        $res = $db->sql_query($sql);


        while ($ar = $db->sql_fetch_object($res)) {

            $ob = $ar;
        }

        $db->sql_close();


        if (!empty($ob)) {
            $password = Chiffrement::decrypt($ob->passwd);


            $to_dump = "";

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

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server WHERE id = ".$id_mysql_server.";";

        $res = $db->sql_query($sql);


        while ($ar = $db->sql_fetch_object($res)) {
            $ob = $ar;
        }


        if (!empty($ob)) {

            $db_to_load = $this->di['db']->sql($ob->name);

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

                header('location: '.LINK.__CLASS__.'/'.__FUNCTION__.'/renamed:tables:'.$nb_renamed);
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


        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server where id=".$id_mysql_server;
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            $db2 = $this->di['db']->sql($ob->name);


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

        $db = $this->di['db']->sql(DB_DEFAULT);
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

        $db = $this->di['db']->sql('hb01_mariaexport01');

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
}