<?php

use \Glial\Synapse\Controller;

class Rename extends Controller
{

    public function index($param)
    {

        $this->di['js']->code_javascript('$("#rename-id_mysql_server").change(function () {
    data = $(this).val();
    $("#rename-database").load(GLIAL_LINK+"common/getDatabaseByServer/" + data + "/ajax>true/",
       function(){
	$("#rename-database").selectpicker("refresh");
    });
});');

        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            if (!empty($_POST['rename']['new_name']) && !empty($_POST['rename']['database']) && !empty($_POST['rename']['id_mysql_server'])) {

                $db = $this->di['db']->sql(DB_DEFAULT);

                $sql = "SELECT * FROM mysql_server where id=".$_POST['rename']['id_mysql_server'];

                $res = $db->sql_query($sql);

                while ($ob = $db->sql_fetch_object($res)) {

                    $db2 = $this->di['db']->sql($ob->name);

                    $res3 = $db2->sql_query("select DEFAULT_CHARACTER_SET_NAME from information_schema.SCHEMATA where SCHEMA_NAME= '".$_POST['rename']['database']."';");
                    while ($ob3 = $db2->sql_fetch_object($res3)) {
                        
                        $db2->sql_query("CREATE DATABASE IF NOT EXISTS `".$_POST['rename']['new_name']."` DEFAULT CHARACTER SET ".$ob3->DEFAULT_CHARACTER_SET_NAME);
                    }

                    //mysqldump <old_schema_name> -d -t -R -E > stored_routines_triggers_events.out

                    $sql2 = "select table_name from information_schema.tables where table_schema='".$_POST['rename']['database']."' AND TABLE_TYPE='BASE TABLE';";

                    $res2 = $db2->sql_query($sql2);


                    $nb_renamed = 0;
                    while ($ob2 = $db2->sql_fetch_object($res2)) {
                        $sql3 = "RENAME TABLE `".$_POST['rename']['database']."`.`".$ob2->table_name."` TO `".$_POST['rename']['new_name']."`.`".$ob2->table_name."`;";

                        $nb_renamed += 1;
                        $db2->sql_query($sql3);
                    }
                }
            }


            // DROP DATABASE IF NO OBJECT
            $sql4 = "select count(1) as cpt from information_schema.tables where table_schema='".$_POST['rename']['database']."';";
            $res4 = $db2->sql_query($sql4);

            while ($ob4 = $db2->sql_fetch_object($res4)) {

                if($ob4->cpt === "0")
                {
                    $db2->sql_query("DROP DATABASE `".$_POST['rename']['database']."`;");
                }
            }



            header('location: '.LINK.__CLASS__.'/'.__FUNCTION__.'/renamed:tables:'.$nb_renamed);

        }
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