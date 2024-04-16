<?php

namespace App\Controller;

use \Glial\Synapse\Controller;

use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;
use \App\Library\Debug;

use \Glial\Sgbd\Sgbd;
use \App\Library\Extraction;



class Listener extends Controller
{

    public function before($param)
    {
        $monolog       = new Logger("Listener");
        $handler      = new StreamHandler(LOG_FILE, Logger::DEBUG);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $monolog->pushHandler($handler);
        $this->logger = $monolog;
    }

    public function checkAll($param)
    {
        Debug::parseDebug($param);

        $id_ts_file = $param[0] ?? 9;

        $db = Sgbd::sql(DB_DEFAULT);

        $sql ="SELECT 
        a.id_mysql_server, 
        a.id_ts_file, 
        MIN(a.date) AS min_date, 
        MAX(a.date) AS max_date, 
        COUNT(*) AS cpt
    FROM 
        ts_date_by_server a
    INNER JOIN (
        SELECT id_mysql_server, id_ts_file, last_date_listener 
        FROM ts_max_date 
        WHERE id_ts_file = 9
    ) b ON a.id_mysql_server = b.id_mysql_server AND a.id_ts_file = b.id_ts_file
    WHERE 
        a.date > b.last_date_listener
    GROUP BY 
        a.id_mysql_server, 
        a.id_ts_file;";


        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            $this->updateDatabase(array($ob->id_mysql_server, $ob->id_ts_file, $ob->min_date));
            $this->updateListener(array($ob->id_mysql_server, $ob->id_ts_file, $ob->min_date));
        }

        if ($db->sql_num_rows($res ) === 0){
            Debug::debug("sleep 1");
            sleep(1);
        }
    }

    public function updateListener($param)
    {
        $id_mysql_server = $param[0];
        $id_ts_file = $param[1];
        $date = $param[2];

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "UPDATE ts_date_by_server SET is_listened=1 
        WHERE id_mysql_server=".$id_mysql_server." AND id_ts_file=".$id_ts_file." 
        AND `date`='".$date."';";
        $db->sql_query($sql);
        Debug::sql($sql);

        $sql ="UPDATE ts_max_date SET last_date_listener='".$date."' 
        WHERE id_mysql_server=".$id_mysql_server." AND id_ts_file=".$id_ts_file.";";
        $db->sql_query($sql);
        Debug::sql($sql);

    }

    /* move to database */

    public function updateDatabase($param)
    {

        /*
        MariaDB [(none)]> show global status like 'com%db%';
        +----------------------+-------+
        | Variable_name        | Value |
        +----------------------+-------+
        | Com_alter_db         | 0     |
        | Com_alter_db_upgrade | 0     |
        | Com_change_db        | 0     |
        | Com_create_db        | 0     |
        | Com_drop_db          | 0     |
        +----------------------+-------+
        */

        Debug::parseDebug($param);
        Debug::debug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $id_mysql_server = $param[0];
        $id_ts_file = $param[1];
        $date = $param[2];

        $sql2 = "SELECT schema_name FROM mysql_database WHERE id_mysql_server=".$id_mysql_server;
        //Debug::debug($sql2, "")
        $res2 = $db->sql_query($sql2);

        $dbs = array();
        while($ob2 = $db->sql_fetch_object($res2))    {
            $dbs[] = $ob2->schema_name;
        }

        Debug::debug($dbs, "DB in mysql_database");

        $res = Extraction::display(array('schema_list'), array($id_mysql_server), array($date));
        $data = json_decode($this->extract($res)['schema_list'], true);

        foreach($data as $elem) {
            $elem['id_mysql_server'] = $id_mysql_server;

            $this->updateElem("mysql_database", $elem);
            Debug::debug($elem, "ELEM");
            if (in_array($elem['schema_name'], $dbs)){
                unset($dbs[array_search($elem['schema_name'], $dbs)]);
            }
        }

        if (count($dbs ) > 1 ) {
            $sql3 = "DELETE FROM mysql_database WHERE id_mysql_server = ".$id_mysql_server." 
            AND schema_name IN ('".implode("','",$dbs)."')";
            Debug::sql($sql3);
            $db->sql_query($sql3);
        }

        Debug::debug($data, "RESULTAT");
        Debug::debug($dbs, "#################################");
    }


    public function extract($data)
    {
        $level1 = end($data);
        $level2 = end($level1);

        return $level2;
    }


    /*
        if not found we replace
    */
    public function updateElem($table_name, $param)
    {
        $arg = array();
        $db = Sgbd::sql(DB_DEFAULT);

        Debug::debug($param, "PARAM");
        
        foreach ($param as $key => $elem ) {
            $arg[] = " `".$key."` = '".$db->sql_real_escape_string($elem) ."'";
        }

        $params = implode(' AND ', $arg);

        $sql ="SELECT count(1) as cpt FROM `".$table_name."` WHERE ".$params;
        Debug::debug($sql);

        $res = $db->sql_query($sql);
        while($ob = $db->sql_fetch_object($res))
        {
            Debug::debug($ob->cpt , "CPT");
            
            if ($ob->cpt == "0")
            {
                $keys = array_keys($param);
                $values = array_values($param);
                
                Debug::debug($keys);
                Debug::debug($values);

                $param1 = '`'.implode('`,`', $keys).'`';
                $param2 = "'".implode("','", $values)."'";

                $sql = "REPLACE INTO `".$table_name."` (".$param1.") VALUES (".$param2.");";
                Debug::sql($sql);
                $db->sql_query($sql);
            }
        }
    }


    public function test1($param)
    {
        Debug::parseDebug($param);
        $id_mysql_server = $param[0];
        $res = Extraction::display(array('schema_list'), array($id_mysql_server));
        Debug::debug($res);
    }

    public function test2($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT 
        a.id_mysql_server, 
        a.id_ts_file, 
        MIN(a.date) AS min_date, 
        MAX(a.date) AS max_date, 
        COUNT(*) AS cpt
    FROM 
        ts_date_by_server a
    INNER JOIN (
        SELECT id_mysql_server, id_ts_file, last_date_listener 
        FROM ts_max_date 
        WHERE id_ts_file = 9
    ) b ON a.id_mysql_server = b.id_mysql_server AND a.id_ts_file = b.id_ts_file
    WHERE 
        a.date > b.last_date_listener
    GROUP BY 
        a.id_mysql_server,
        a.id_ts_file;";

        $res = $db->sql_query($sql);

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            Debug::debug($arr);
        }
    }

    public function test4($param)
    {
        Debug::parseDebug($param);
        $db = Sgbd::sql(DB_DEFAULT);

        $id_mysql_server = $param[0];

        $sql2 = "SELECT `schema_name` FROM `mysql_database` WHERE `id_mysql_server`=".$id_mysql_server.";";
        Debug::sql($sql2);
        $res2 = $db->sql_query($sql2);

        $dbs = array();
        while($arr = $db->sql_fetch_array($res2, MYSQLI_ASSOC))
        {
            $dbs[] = $arr;
        }

        Debug::debug($dbs, "mysql_database");
    }
}

/****
 * 
 * CREATE TABLE `listener` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_ts_file` int(11) NOT NULL,
  `date_previous_execution` datetime NOT NULL,
  `execution_time` double NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_ts_file` (`id_ts_file`),
  CONSTRAINT `listener_ibfk_1` FOREIGN KEY (`id_ts_file`) REFERENCES `ts_file` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
 
SELECT id_mysql_server, id_ts_file, min(date) as min_date, max(date) as max_date
count(1)
from `ts_date_by_server` a 
WHERE a.date > (select b.date_previous_execution from listener b where id_ts_file = 9) and a.id_ts_file = 9
GROUP BY id_mysql_server, id_ts_file
*/