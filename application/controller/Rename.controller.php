<?php

use \Glial\Synapse\Controller;
use \App\Library\Debug;

class Rename extends Controller
{

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

                $nb_renamed = $this->move(array($_POST['rename']['id_mysql_server'], $_POST['rename']['database'], $_POST['rename']['new_name']));

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
                $triggers[$ob6['Trigger']] = $ob6;

                $sql8 = "drop trigger `".$ob6['Trigger']."`;";
                Debug::debug($sql8);

                $db2->sql_query($sql8);
            }

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
                    $views[] = str_replace('`'.$OLD_DB.'`','`'.$NEW_DB.'`',$ob10);
                }


                $sql11 = "drop view `".$OLD_DB."`.`".$ob9['table_name']."`;";
                Debug::debug($sql11);
                $db2->sql_query($sql11);
            }



            // backup functions

            $functions = array();
            /*
            $sql13 = "SHOW FUNCTION STATUS where Db='".$OLD_DB."'";
            $res13 = $db2->sql_query($sql13);

            while ($ob13      = $db2->sql_fetch_object($res13)) {

                $sql14 = "SHOW CREATE function `".$OLD_DB."`.`".$ob13->Name."`";
                $res14 = $db2->sql_query($sql14);
                while ($ob14  = $db2->sql_fetch_array($res14, MYSQLI_ASSOC)) {

                    $functions[] = $ob14['Create Function'].";";
                }


                $sql15 = "DROP function `".$OLD_DB."`.`".$ob13->Name."`;";
                Debug::debug($sql15);
                $db2->sql_query($sql15);
            }
*/

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



//mysqldump <old_schema_name> -d -t -R -E > stored_routines_triggers_events.out

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
                Debug::debug(SqlFormatter::format($sql16));
                $db2->sql_multi_query($sql16);
            }


            foreach ($views as $view) {
                $sql12 = $view['Create View'];
                Debug::debug($sql12);
                $db2->sql_query($sql12);
            }




            foreach ($procedures as $procedure) {
                $sql19 = $procedure;
                Debug::debug($sql19);
                $db2->sql_multi_query($sql19);
            }


            foreach ($triggers as $trigger) {
                $sql7 = "CREATE TRIGGER `".$trigger['Trigger']."` ".$trigger['Timing']." ".$trigger['Event']." ON ".$trigger['Table']." FOR EACH ROW ".$trigger['Statement'].";";
                Debug::debug($sql7);
                $db2->sql_multi_query($sql7);
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
}
/*
 *
 * #!/bin/bash
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
  character_set=`mysql -h $1 -e "show create database $2G" -sss | grep ^Create | awk -F'CHARACTER SET ' '{print $2}' | awk '{print $1}'`
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
SELECT  views.TABLE_NAME As `View`, tab.TABLE_NAME AS `Input`
FROM information_schema.`TABLES` AS tab
INNER JOIN information_schema.VIEWS AS views
ON views.VIEW_DEFINITION LIKE CONCAT('%`',tab.TABLE_NAME,'`%')
 */