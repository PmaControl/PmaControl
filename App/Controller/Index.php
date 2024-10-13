<?php

namespace App\Controller;

use App\Library\Graphviz;
use Glial\Synapse\Controller;
use Glial\Synapse\FactoryController;
use \Glial\Sgbd\Sgbd;
use \App\Library\Debug;
use \App\Library\Mysql;


class Index extends Controller {


    static $redundant_indexes = array();
    static $unused_indexes = array();

/*
    To execute once a day
*/
    public function buildCash($param)
    {

        Debug::parseDebug($param);
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "TRUNCATE TABLE `index_stats`";
        $db->sql_query($sql);
        

        $sql = "SELECT * FROM mysql_database where schema_name NOT IN ('mysql', 'information_schema', 'performance_schema') ;";
        Debug::sql($sql);

        $res = $db->sql_query($sql);
        while($ob = $db->sql_fetch_object($res))
        {
            $db2 = Mysql::getDbLink($ob->id_mysql_server, "IMPORT");
            $db2->sql_select_db($ob->schema_name);

            $tables = $db2->getListTable();

            //Debug::debug($tables, "table");

            foreach($tables['table'] as $table)
            {
                echo "$table\n";

                $sql3 = "SELECT
                        s.INDEX_NAME,
                        GROUP_CONCAT(s.COLUMN_NAME ORDER BY s.SEQ_IN_INDEX) AS COLUMNS,
                        GROUP_CONCAT(s.CARDINALITY) AS CARDINALITY,
                        GROUP_CONCAT(c.DATA_TYPE) AS DATA_TYPE,
                        GROUP_CONCAT(c.CHARACTER_MAXIMUM_LENGTH) AS CHARACTER_MAXIMUM_LENGTH,
                        
                        SUM(
                            CASE 
                                WHEN c.DATA_TYPE = 'tinyint' THEN 1
                                WHEN c.DATA_TYPE = 'smallint' THEN 2
                                WHEN c.DATA_TYPE = 'mediumint' THEN 3
                                WHEN c.DATA_TYPE = 'int' THEN 4
                                WHEN c.DATA_TYPE = 'bigint' THEN 8
                                WHEN c.DATA_TYPE = 'float' THEN 4
                                WHEN c.DATA_TYPE = 'double' THEN 8
                                WHEN c.DATA_TYPE = 'date' THEN 3
                                WHEN c.DATA_TYPE = 'datetime' THEN 8
                                WHEN c.DATA_TYPE = 'timestamp' THEN 4
                                WHEN c.DATA_TYPE = 'varchar' THEN 2 * c.CHARACTER_MAXIMUM_LENGTH
                                WHEN c.DATA_TYPE = 'char' THEN 2* c.CHARACTER_MAXIMUM_LENGTH
                                ELSE 0
                            END
                        ) AS INDEX_SIZE_BYTES
                    FROM
                        information_schema.STATISTICS s
                    JOIN
                        information_schema.COLUMNS c
                    ON
                        s.TABLE_SCHEMA = c.TABLE_SCHEMA
                        AND s.TABLE_NAME = c.TABLE_NAME
                        AND s.COLUMN_NAME = c.COLUMN_NAME
                    WHERE
                        s.TABLE_SCHEMA = '".$ob->schema_name."'
                        AND s.TABLE_NAME = '".$table."'
                    GROUP BY
                        s.INDEX_NAME
                    ORDER BY
                        (s.INDEX_NAME = 'PRIMARY') DESC, s.INDEX_NAME;";

                $res3 = $db2->sql_query($sql3);

                $PK_SIZE = 0;
                while ($ob3 = $db2->sql_fetch_object($res3))
                {

                    Debug::debug($ob3, "stats");
                    $sql5 ="SELECT table_rows as cpt FROM information_schema.tables WHERE table_schema = '".$ob->schema_name."' AND table_name = '".$table."'";
                    $res5 = $db2->sql_query($sql5);
                    while ($ob5 = $db2->sql_fetch_object($res5)) {
                        $table_rows = $ob5->cpt;
                    }

                    // if less than 100 000 doing count(1)

                    // case InnoDB
                    if ($ob3->INDEX_NAME == "PRIMARY")
                    {
                        $PK_SIZE = $ob3->INDEX_SIZE_BYTES;
                        $total_size = $ob3->INDEX_SIZE_BYTES * $table_rows;
                    }
                    else
                    {
                        $total_size = ($ob3->INDEX_SIZE_BYTES+ $PK_SIZE) * $table_rows;
                        $ob3->INDEX_SIZE_BYTES = $ob3->INDEX_SIZE_BYTES+ $PK_SIZE;
                    }

                    $redundant_indexes = $this->IsRedundantIndexes($ob->id_mysql_server,$ob->schema_name, $table, $ob3->INDEX_NAME );
                    $unused_indexes = $this->IsUnusedIndexes($ob->id_mysql_server,$ob->schema_name, $table, $ob3->INDEX_NAME);

                    Debug::debug($total_size, "TOTAL SIZE");


                    $sql4 = "INSERT INTO `index_stats` VALUES(NULL,".$ob->id_mysql_server.", ".$ob->id.",0, '".$ob->schema_name."','".$table."', '".$ob3->INDEX_NAME."', '".$ob3->COLUMNS."',
                     '".$ob3->CARDINALITY."', '".$ob3->DATA_TYPE."', '".$ob3->CHARACTER_MAXIMUM_LENGTH."', ".$ob3->INDEX_SIZE_BYTES.", ".$total_size.", 
                     $redundant_indexes, $unused_indexes );";

                    Debug::sql($sql4);

                    $db->sql_query($sql4);
                }
            }

            $db2->sql_close();
        }
    }

    public function IsRedundantIndexes($id_mysql_server, $schema_name, $table_name, $index_name)
    {
        $db = Mysql::getDbLink($id_mysql_server, "IMPORT");

        if (empty(self::$redundant_indexes[$id_mysql_server]))
        {
            $sql = "SELECT * FROM sys.schema_redundant_indexes";
            $res = $db->sql_query($sql);

            while($ob = $db->sql_fetch_object($res, MYSQLI_ASSOC)) {
                self::$redundant_indexes[$id_mysql_server][$ob->table_schema][$ob->table_name][$ob->redundant_index_name] = 1;
            }
        }

        //Debug::debug(self::$redundant_indexes, "REDUNDANT INDEX");

        if (! empty(self::$redundant_indexes[$id_mysql_server][$schema_name][$table_name][$index_name]))
        {
            return self::$redundant_indexes[$id_mysql_server][$schema_name][$table_name][$index_name];
        }else
        {
            return 0;
        }
    }


    public function IsUnusedIndexes($id_mysql_server, $schema_name, $table_name, $index_name)
    {
        $db = Mysql::getDbLink($id_mysql_server, "IMPORT");

        if (empty(self::$unused_indexes[$id_mysql_server]))
        {
            $sql = "SELECT * FROM sys.schema_unused_indexes";
            $res = $db->sql_query($sql);

            while($ob = $db->sql_fetch_object($res, MYSQLI_ASSOC)) {
                self::$unused_indexes[$id_mysql_server][$ob->object_schema][$ob->object_name][$ob->index_name] = 1;
            }
        }

        //Debug::debug(self::$redundant_indexes, "REDUNDANT INDEX");

        if (! empty(self::$unused_indexes[$id_mysql_server][$schema_name][$table_name][$index_name]))
        {
            return self::$unused_indexes[$id_mysql_server][$schema_name][$table_name][$index_name];
        }else
        {
            return 0;
        }
    }



}
