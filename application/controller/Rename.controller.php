<?php

use \Glial\Synapse\Controller;
use \App\Library\Debug;

class Rename extends Controller
{
    var $data;

    public function index($param)
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
        $AP              = $param[3];


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
ON views.VIEW_DEFINITION LIKE CONCAT('% `',tab.TABLE_NAME,'`%') AND tab.TABLE_SCHEMA='sys' AND views.TABLE_SCHEMA='sys' AND tab.TABLE_TYPE = 'VIEW'
UNION
SELECT views.TABLE_NAME As `View`, tab.TABLE_NAME AS `Input`
FROM information_schema.`TABLES` AS tab
INNER JOIN information_schema.VIEWS AS views
ON views.VIEW_DEFINITION LIKE CONCAT('%`',tab.TABLE_SCHEMA,'`.`',tab.TABLE_NAME,'`%') AND tab.TABLE_SCHEMA='sys' AND views.TABLE_SCHEMA='sys' AND tab.TABLE_TYPE = 'VIEW';";


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

    public function adjust_privileges($db_link, $OLD_DB, $NEW_DB)
    {
        //select user, host from mysql.user;




        /*
          $sql   = array();
          $sql[] = "UPDATE mysql.columns_priv set db='".$NEW_DB."' WHERE db='".$OLD_DB."';";
          $sql[] = "UPDATE mysql.procs_priv set db='".$NEW_DB."' WHERE db='".$OLD_DB."'";
          $sql[] = "UPDATE mysql.tables_priv set db='".$NEW_DB."' WHERE db='".$OLD_DB."'";
          $sql[] = "UPDATE mysql.db set db='".$NEW_DB."' WHERE db='".$OLD_DB."'";
          $sql[] = "flush privileges;";

          foreach($sql as $query)
          {
          $db_link->sql_query($sql);
          } */
    }

    public function exportAllUser($db_link)
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

    public function testu()
    {
        $db = $this->di['db']->sql('preprod_mariamasterzm01_preprod_rdc');

        $this->changeGrant($db, "mall", "mall_gg");
    }

    public function getChangeGrant($db_link, $OLD_DB, $NEW_DB)
    {

        $grants = array();
        $revoke = array();

        $users = $this->exportAllUser($db_link);
        foreach ($users as $user) {
            $pos = strpos($user, $OLD_DB);

            if ($pos !== false) {
                $revoke[] = str_replace("GRANT", "REVOKE", $user).";";
                $grants[] = str_replace("`".$OLD_DB."`", "`".$NEW_DB."`", $user).";";
            }
        }

        $data = array_merge($revoke, $grants);

        return $data;
    }
}
/*
 *
#!/bin/bash
# Copyright 2013 Percona LLC and/or its affiliates
set -e
if [ -z "$3" ]; then
echo "rename_db <server> <database> <new_database>"
exit 1
fi
db_exists=`mysql -h $1 -e "show databases like '$3'" -sss`
if [ -n "$db_exists" ]; then
echo "ERROR: New database already exists $3"
exit 1
fi
TIMESTAMP=`date +%s`
character_set=`mysql -h $1 -e "show create database $2" -sss | grep ^Create | awk -F'CHARACTER SET ' '{print $2}' | awk '{print $1}'`
TABLES=`mysql -h $1 -e "select TABLE_NAME from information_schema.tables where table_schema='$2' and TABLE_TYPE='BASE TABLE'" -sss`
STATUS=$?
if [ "$STATUS" != 0 ] || [ -z "$TABLES" ]; then
echo "Error retrieving tables from $2"
exit 1
fi
echo "create database $3 DEFAULT CHARACTER SET $character_set"
mysql -h $1 -e "create database $3 DEFAULT CHARACTER SET $character_set"
TRIGGERS=`mysql -h $1 $2 -e "show triggersG" | grep Trigger: | awk '{print $2}'`
VIEWS=`mysql -h $1 -e "select TABLE_NAME from information_schema.tables where table_schema='$2' and TABLE_TYPE='VIEW'" -sss`
if [ -n "$VIEWS" ]; then
mysqldump -h $1 $2 $VIEWS > /tmp/${2}_views${TIMESTAMP}.dump
fi
mysqldump -h $1 $2 -d -t -R -E > /tmp/${2}_triggers${TIMESTAMP}.dump
for TRIGGER in $TRIGGERS; do
echo "drop trigger $TRIGGER"
mysql -h $1 $2 -e "drop trigger $TRIGGER"
done
for TABLE in $TABLES; do
echo "rename table $2.$TABLE to $3.$TABLE"
mysql -h $1 $2 -e "SET FOREIGN_KEY_CHECKS=0; rename table $2.$TABLE to $3.$TABLE"
done
if [ -n "$VIEWS" ]; then
echo "loading views"
mysql -h $1 $3 < /tmp/${2}_views${TIMESTAMP}.dump
fi
echo "loading triggers, routines and events"
mysql -h $1 $3 < /tmp/${2}_triggers${TIMESTAMP}.dump
TABLES=`mysql -h $1 -e "select TABLE_NAME from information_schema.tables where table_schema='$2' and TABLE_TYPE='BASE TABLE'" -sss`
if [ -z "$TABLES" ]; then
echo "Dropping database $2"
mysql -h $1 $2 -e "drop database $2"
fi
if [ `mysql -h $1 -e "select count(*) from mysql.columns_priv where db='$2'" -sss` -gt 0 ]; then
COLUMNS_PRIV="    UPDATE mysql.columns_priv set db='$3' WHERE db='$2';"
fi
if [ `mysql -h $1 -e "select count(*) from mysql.procs_priv where db='$2'" -sss` -gt 0 ]; then
PROCS_PRIV="    UPDATE mysql.procs_priv set db='$3' WHERE db='$2';"
fi
if [ `mysql -h $1 -e "select count(*) from mysql.tables_priv where db='$2'" -sss` -gt 0 ]; then
TABLES_PRIV="    UPDATE mysql.tables_priv set db='$3' WHERE db='$2';"
fi
if [ `mysql -h $1 -e "select count(*) from mysql.db where db='$2'" -sss` -gt 0 ]; then
DB_PRIV="    UPDATE mysql.db set db='$3' WHERE db='$2';"
fi
if [ -n "$COLUMNS_PRIV" ] || [ -n "$PROCS_PRIV" ] || [ -n "$TABLES_PRIV" ] || [ -n "$DB_PRIV" ]; then
echo "IF YOU WANT TO RENAME the GRANTS YOU NEED TO RUN ALL OUTPUT BELOW:"
if [ -n "$COLUMNS_PRIV" ]; then echo "$COLUMNS_PRIV"; fi
if [ -n "$PROCS_PRIV" ]; then echo "$PROCS_PRIV"; fi
if [ -n "$TABLES_PRIV" ]; then echo "$TABLES_PRIV"; fi
if [ -n "$DB_PRIV" ]; then echo "$DB_PRIV"; fi
echo "    flush privileges;"
fi
 */



/*
 *
 *
 *
 * dependance des objets entre eux, a traité par récursif
SELECT  views.TABLE_SCHEMA, views.TABLE_NAME As `View`,tab.TABLE_SCHEMA, tab.TABLE_NAME AS `Input`, tab.TABLE_TYPE
FROM information_schema.`TABLES` AS tab
INNER JOIN information_schema.VIEWS AS views
ON views.VIEW_DEFINITION LIKE CONCAT('% `',tab.TABLE_NAME,'`%') AND tab.TABLE_SCHEMA='sys' AND views.TABLE_SCHEMA='sys' AND tab.TABLE_TYPE = 'VIEW'
UNION
SELECT  views.TABLE_SCHEMA, views.TABLE_NAME As `View`,tab.TABLE_SCHEMA, tab.TABLE_NAME AS `Input`, tab.TABLE_TYPE
FROM information_schema.`TABLES` AS tab
INNER JOIN information_schema.VIEWS AS views
ON views.VIEW_DEFINITION LIKE CONCAT('%`',tab.TABLE_SCHEMA,'`.`',tab.TABLE_NAME,'`%') AND tab.TABLE_SCHEMA='sys' AND views.TABLE_SCHEMA='sys' AND tab.TABLE_TYPE = 'VIEW';



 *  */