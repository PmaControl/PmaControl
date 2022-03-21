<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use \App\Library\Mysql;
use \App\Library\Debug;

class Query extends Controller
{
    var $queries = array();

    public function getFielsWithoutDefault($id_mysql_server, $databases = "")
    {

        $db  = Mysql::getDbLink($id_mysql_server);
        $sql = <<<SQL
            SELECT
                c.TABLE_SCHEMA as db_name,
                c.TABLE_NAME as table_name,
                c.COLUMN_NAME as column_name,
                c.DATA_TYPE as data_type,
                c.COLUMN_TYPE as data_type2
            FROM information_schema.columns c
            WHERE
                NOT EXISTS(
                    SELECT 1 FROM information_schema.tables t
                    WHERE
                        t.TABLE_SCHEMA = c.TABLE_SCHEMA
                        AND t.TABLE_NAME = c.TABLE_NAME
                        AND t.TABLE_TYPE <> 'BASE TABLE'
                )
                AND c.TABLE_SCHEMA NOT IN ('information_schema', 'sys', 'performance_schema','mysql')
                AND c.COLUMN_DEFAULT IS NULL
                AND c.IS_NULLABLE = "NO"
                AND c.EXTRA <> 'auto_increment'
SQL;

        if (!empty($databases) && $databases != "ALL") {

            $dbs = explode(",", $databases);
            $sql .= " AND c.TABLE_SCHEMA IN ('".implode("','", $dbs)."') ";
        }

        $sql .= ";";

        Debug::sql($sql);
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            yield $ob;
        }
    }

    private function getDefaultValueByType($type, $typeExtra = '')
    {
        // All default values below have been tested in current engine (DEV environment)
        // They are default values forced by the current engine (DEV environment)
        switch ($type) {
            case 'year':
                return '0000';
            case 'time':
                return '00:00:00';
            case 'date':
                return '0000-00-00';
            case 'datetime':
                return '0000-00-00 00:00:00';
            case 'double':
            case 'int':
            case 'float':
            case 'smallint':
            case 'tinyint':
            case 'mediumint':
            case 'bigint':
            case 'decimal':
                return 0;
            case 'varchar':
            case 'longtext':
            case 'mediumtext':
            case 'tinytext':
            case 'text':
            case 'char':
                return '';
            case 'enum':
                Debug::debug($type,"type");
                Debug::debug($typeExtra,"typeExtra");
                if (!preg_match('/^enum\((.*)\)$/', $typeExtra, $matches)) {


                    throw new \Exception(sprintf('Could not retrieve enum list from: "%s"', (string) $typeExtra   ));
                }
                if (false === ($enum = preg_split('/,\s?/', $matches[1]))) {
                    throw new \Exception(sprintf('Could not retrieve enum items from: "%s"', $matches[1]   ));
                }
                return $enum[0];
            case 'set':
                return ''; // This is the behavior in current engine even when '' is not part of list
            case 'blob':
            case 'mediumblob':
            case 'bit':
            case 'varbinary':
                return '';
            default:
                throw new RuntimeException(sprintf(
                            'Encountered a type which is not referenced: "%s"', (string) $type
                ));
        }
    }

    /**
     * Get query
     *
     * @param string $dbName
     * @param string $tableName
     * @param string $columnName
     * @param string $defaultValue
     * @return string
     */
    private function getQuery($dbName, $tableName, $columnName, $defaultValue)
    {
        if (!isset($this->queries[$dbName][$tableName][$columnName])) {

            $quote = "'";
            if ($defaultValue == "0") {
                $quote = "";
            }

            $this->queries[$dbName][$tableName][$columnName] = sprintf(
                "ALTER TABLE `%s`.`%s` ALTER COLUMN `%s` SET DEFAULT ".$quote."%s".$quote.";", $dbName, $tableName, $columnName, $defaultValue
            );
        }
        return $this->queries[$dbName][$tableName][$columnName];
    }

    /*
     * 
     * ./glial Query setDefault 1 test1,test2
     * 
     * 1 => id of the server
     * test1 => database (coma separated), if all databases remove this paramters or set it to ALL
     * 
     */
    public function setDefault($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $list_databases  = $param[1]; // separated by coma
        $fields          = $this->getFielsWithoutDefault($id_mysql_server, $list_databases);

        foreach ($fields as $field) {

            Debug::debug($field, "field");

            $default_value = $this->getDefaultValueByType($field->data_type, $field->data_type2);
            $ret           = $this->getQuery($field->db_name, $field->table_name, $field->column_name, $default_value);

            //print_r($field);
            echo $ret."\n";
        }
    }
}