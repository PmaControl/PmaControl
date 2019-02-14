<?php

use \Glial\Synapse\Controller;
use \App\Library\Debug;
use App\Library\Mysql;

class Rename extends Controller
{
    

}
/*
 * 
 * mysql -h $h -u $u -p$p INFORMATION_SCHEMA --skip-column-names --batch  -e "select table_name from tables where table_type = 'VIEW' and table_schema = 'mall'" | xargs mysqldump -h $h -u $u -p$p mall > views.sql
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