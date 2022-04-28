<?php

declare(ticks=1);

namespace App\Controller;

use \Glial\Synapse\Controller;
use \App\Library\Mysql;
use \App\Library\Debug;
use \Glial\Cli\SetTimeLimit;

class Query extends Controller {

    const TABLE_NAME = 'tmp_setdefault';
    const TABLE_SCHEMA = 'dba';
    const LOG_FILE = TMP . "log/query.log";

    public function getFielsWithoutDefault($id_mysql_server, $databases = "") {
        /*
         * If a field is NULLABLE we will not get it there.
         * 
         */

        $db = Mysql::getDbLink($id_mysql_server);
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
            $sql .= " AND c.TABLE_SCHEMA IN ('" . implode("','", $dbs) . "') ";
        }

        $sql .= ";";

        Debug::sql($sql);
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            yield $ob;
        }
    }

    private function getDefaultValueByType($type, $typeExtra = '') {
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
                Debug::debug($type, "type");
                Debug::debug($typeExtra, "typeExtra");
                $matches = "";
                if (!preg_match('/^enum\((.*)\)$/', $typeExtra, $matches)) {

                    throw new \Exception(sprintf('Could not retrieve enum list from: "%s"', (string) $typeExtra));
                }
                if (false === ($enum = preg_split('/,\s?/', $matches[1]))) {
                    throw new \Exception(sprintf('Could not retrieve enum items from: "%s"', $matches[1]));
                }

                return trim($enum[0], "'");
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
     * setDefault
     *
     * @param string $id_mysql_server
     * @param string $list_databases (coma separated or ALL for all databases, if omited same as ALL)
     * @return void
     * 
     * @example ./glial Query setDefault 1 test1,test2
     * 
     * 1 => id of the server
     * test1 => database (coma separated), if all databases remove this paramters or set it to ALL
     * 
     */
    public function setDefault($param) {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $list_databases = $param[1] ?? "ALL"; // separated by coma
        $fields = $this->getFielsWithoutDefault($id_mysql_server, $list_databases);

        $db = Mysql::getDbLink($id_mysql_server);

        $default = array();

        foreach ($fields as $field) {
            Debug::debug($field, "field");
            // remove default value for blob and text : https://mariadb.com/kb/en/blob/
            if (in_array($field->data_type, array('tinytext', 'text', 'mediumtext', 'longtext', 'tinyblob', 'blob', 'mediumblob', 'longblob'))) {
                if (version_compare($db->getVersion(), 10.2, '<')) {
                    continue;
                }
            }

            $default_value = $this->getDefaultValueByType($field->data_type, $field->data_type2);

            if (in_array($field->data_type, array("int", "double", "float", "smallint", "tinyint", "mediumint", "bigint", "decimal"))) {
                $quote = "";
            } else {
                $quote = "'";
            }
            //echo "--" . $field->data_type . "\n";
            $alter = "ALTER TABLE `" . $field->db_name . "`.`" . $field->table_name . "` ALTER COLUMN `" . $field->column_name . "` SET DEFAULT " . $quote . $default_value . $quote . ";";
            $default[] = $alter;
            echo $alter . "\n";
        }
        echo "Total : " . count($default) . "\n";

        return $default;
    }

    public function dropDefault($param) {

        Debug::parseDebug($param);
        $id_mysql_server = $param[0];
        $list_databases = $param[1] ?? "ALL"; // separated by coma
        $fields = $this->getFielsWithoutDefault($id_mysql_server, $list_databases);

        $db = Mysql::getDbLink($id_mysql_server);

        foreach ($fields as $field) {
            Debug::debug($field, "field");
            // remove default value for blob and text : https://mariadb.com/kb/en/blob/
            if (in_array($field->data_type, array('tinytext', 'text', 'mediumtext', 'longtext', 'tinyblob', 'blob', 'mediumblob', 'longblob'))) {
                if (version_compare($db->getVersion(), 10.2, '<')) {
                    continue;
                }
            }

            echo "ALTER TABLE `" . $field->db_name . "`.`" . $field->table_name . "` ALTER COLUMN `" . $field->column_name . "` DROP DEFAULT;\n";
        }
    }

    public function runSetDefault($param) {

        Debug::parseDebug($param);
        $id_mysql_server = $param[0];



        do {
            $defaults = $this->setDefault($param);

            pcntl_signal(SIGTERM, array($this, 'sigHandler')); //
            pcntl_signal(SIGHUP, array($this, 'sigHandler'));
            pcntl_signal(SIGUSR1, array($this, 'sigHandler')); // active / desactive debug
            pcntl_signal(SIGUSR2, array($this, 'sigHandler')); // rechargement de la configuration ?

            $run_number = $this->getMaxRun($param);
            Debug::debug($run_number, "run_number");

            foreach ($defaults as $default) {

                //begin if MariaDB > 10.1.2
                //$query = "SET STATEMENT max_statement_time=1 FOR " . $default;
                //echo Date("Y-m-d H:i:s") . " " . $query . "\n";
                //end if MariaDB > 10.1.2

                $db = Mysql::getDbLink($id_mysql_server);

                $php = explode(" ", shell_exec("whereis php"))[1];

                Debug::sql($default);
                
                $cmd = $php . " " . GLIAL_INDEX . " " . substr(__CLASS__, strrpos(__CLASS__, '\\') + 1) . " runQuery " . $id_mysql_server . " " . base64_encode($default) . " " . $run_number . " --debug >> " . self::LOG_FILE . " & echo $!";
                $pid = intval(trim(shell_exec($cmd)));

                do {

                    usleep(1000000);

                    $sql = "SELECT thread_id, TIME_TO_SEC(timediff (now(),`date`)) as sec FROM `" . self::TABLE_SCHEMA . "`.`" . self::TABLE_NAME . "` WHERE state=0 and id_mysql_server=" . $id_mysql_server;
                    $res = $db->sql_query($sql);

                    $num_rows = intval($db->sql_num_rows($res));
                    echo $num_rows." ";
                    //Debug::debug($num_rows, 'num_rows');

                    while ($ob = $db->sql_fetch_object($res)) {
                        if ($ob->sec > 10) {
                            $sql2 = "UPDATE `" . self::TABLE_SCHEMA . "`.`" . self::TABLE_NAME . "`  SET `state`=2 WHERE thread_id=" . $ob->thread_id . " and id_mysql_server=" . $id_mysql_server;
                            $db->sql_query($sql2);

                            
                            //kill mysql process
                            $sql3 = "KILL " . $ob->thread_id . ";";
                            $db->sql_query($sql3);

                            //kill php process
                            shell_exec("kill -9 " . $pid);



                            $num_rows = 0;
                        }
                    }
                } while ($num_rows !== 0);
                
                echo "\n";
            }
            
            
            $db = Mysql::getDbLink($id_mysql_server);
            

            $sql4 = "SELECT count(1) as cpt FROM `" . self::TABLE_SCHEMA . "`.`" . self::TABLE_NAME . "` WHERE `state`=2 and run_number=" . $run_number . " and `id_mysql_server`=" . $id_mysql_server;
            Debug::sql($sql4);

            $cpt = 0;
            $res4 = $db->sql_query($sql4);
            while ($ob4 = $db->sql_fetch_object($res4)) {
                $cpt = $ob4->cpt;
            }
        } while ($cpt > 0);
    }

    public function runQuery($param) {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $query = base64_decode($param[1]);
        $run_number = intval($param[2]);

        $db = Mysql::getDbLink($id_mysql_server);

        // to be sure no other process working
        $sql3 = "SELECT count(1) as cpt FROM `" . self::TABLE_SCHEMA . "`.`" . self::TABLE_NAME . "` WHERE state=0 AND id_mysql_server=" . $id_mysql_server;
        $res3 = $db->sql_query($sql3);
        while ($ob3 = $db->sql_fetch_object($res3)) {
            if ($ob3->cpt > 0) {
                throw new \Exception("One query already working to prevent any problem we kill this one");
            }
        }

        $thread_id = $db->sql_thread_id();
        $sql = "INSERT INTO `" . self::TABLE_SCHEMA . "`.`" . self::TABLE_NAME . "` (`id_mysql_server`,`run_number`,`date`,`query`,`thread_id`,`state`) "
                . "VALUES (" . $id_mysql_server . "," . $run_number . ", '" . date("Y-m-d H:i:s") . "','" . $db->sql_real_escape_string($query) . "'," . $thread_id . ", 0)";
        $db->sql_query($sql);

        Debug::debug($thread_id, "THREAD_ID");
        Debug::sql($query);

        $db->sql_query($query);

        $sql2 = "UPDATE `" . self::TABLE_SCHEMA . "`.`" . self::TABLE_NAME . "`  SET `state`=1 WHERE thread_id=" . $thread_id;
        $db->sql_query($sql2);
        $db->sql_close();
    }

    public function createWorkTable($param) {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];

        $db = Mysql::getDbLink($id_mysql_server);

        $sql4 = "SELECT count(1) as cpt FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . self::TABLE_SCHEMA . "'";
        Debug::sql($sql4);
        $res4 = $db->sql_query($sql4);

        $db_exist = false;
        while ($ob4 = $db->sql_fetch_object($res4)) {
            $db_exist = $ob4->cpt;
        }

        if ($db_exist === "0") {

            $sql = "CREATE DATABASE IF NOT EXISTS `" . self::TABLE_SCHEMA . "`;";
            Debug::sql($sql);
            $db->sql_query($sql);
        }

        $sql2 = "select count(1) as cpt FROM information_schema.tables where table_name = '" . self::TABLE_NAME . "' and table_schema='" . self::TABLE_SCHEMA . "';";
        Debug::sql($sql2);
        $res2 = $db->sql_query($sql2);

        while ($ob2 = $db->sql_fetch_object($res2)) {
            if ($ob2->cpt > 0) {
                return true;
                //throw new \Exception("One treatement already working, wait it's finish or delete table with ./glial " . substr(__CLASS__, strrpos(__CLASS__, '\\') + 1) . " deleteWorkTable " . $id_mysql_server);
            }
        }

        $sql3 = '
        CREATE TABLE `' . self::TABLE_SCHEMA . '`.`' . self::TABLE_NAME . '` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL,
  `run_number` int(11) NOT NULL DEFAULT 0,
  `date` datetime NOT NULL,
  `query` text NOT NULL,
  `thread_id` int(11) NOT NULL,
  `state` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;';
        Debug::debug($sql3);
        $db->sql_query($sql3);
    }

    public function deleteWorkTable($param) {

        Debug::parseDebug($param);

        $id_mysql_server = $param[0];

        $db = Mysql::getDbLink($id_mysql_server);
        $sql = "select count(1) as cpt FROM information_schema.tables where table_name = '" . self::TABLE_NAME . "' and table_schema='" . self::TABLE_SCHEMA . "';";
        Debug::debug($sql);
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            if ($ob->cpt === "1") {
                $sql = "DROP TABLE `" . self::TABLE_SCHEMA . "`.`" . self::TABLE_NAME . "`";
                Debug::sql($sql);
                $db->sql_query($sql);
            } else {
                throw new \Exception("We cannot found the table to drop '`" . self::TABLE_SCHEMA . "`.`" . self::TABLE_NAME . "`'");
            }
        }
    }

// gestionnaire de signaux système
    private function sigHandler($signo) {
        switch ($signo) {
            case SIGTERM:
                echo "Reçu le signe SIGTERM...\n";
                exit;
                break;

            case SIGUSR1:
                break;

            case SIGUSR2:
                break;

            case SIGHUP:

                // gestion du redémarrage
                //ne marche pas au second run pourquoi ?
                echo "Reçu le signe SIGHUP...\n";
                $this->sighup();

                break;

            default:

                echo "RECU LE SIGNAL : " . $signo;
// gestion des autres signaux
        }
    }

    public function getMaxRun($param) {

        $id_mysql_server = $param[0];
        $db = Mysql::getDbLink($id_mysql_server);
        $this->createWorkTable($param);

        $sql = "SELECT max(run_number) as netxtrun FROM `" . self::TABLE_SCHEMA . "`.`" . self::TABLE_NAME . "` WHERE id_mysql_server=" . $id_mysql_server;
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $current_run = $ob->netxtrun + 1;
        }

        Debug::debug($current_run, "current_run");
        return $current_run;
    }

}
