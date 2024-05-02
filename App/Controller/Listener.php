<?php

namespace App\Controller;

use \Glial\Synapse\Controller;

use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;
use \App\Library\Debug;
use \App\Library\EngineV4;
use \Glial\Sgbd\Sgbd;
use \App\Library\Extraction;
use \App\Library\Extraction2;



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


        $this->check(array());
    }

    public function check($param)
    {
        Debug::parseDebug($param);


        $db = Sgbd::sql(DB_DEFAULT);

        $sql ="SELECT id_ts_file, id_mysql_server from ts_max_date where last_date_listener != date;";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res, MYSQLI_ASSOC)) {
            $this->getUpdateToDo($ob->id_mysql_server,$ob->id_ts_file);
        }
    }


    public function getUpdateTodo($id_mysql_server, $id_ts_file)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql ="SELECT 
        a.id_mysql_server, 
        a.id_ts_file, 
        MIN(a.date) AS min_date, 
        MAX(a.date) AS max_date, 
        COUNT(*) AS cpt,
        file_name as ts_file
    FROM 
        `ts_date_by_server` a
    INNER JOIN (
        SELECT id_mysql_server, id_ts_file, last_date_listener 
        FROM `ts_max_date` x
        WHERE x.id_ts_file=".$id_ts_file." AND `last_date_listener` != `date`
        AND x.id_mysql_server = ".$id_mysql_server."
    ) `b` ON a.id_mysql_server = b.id_mysql_server AND a.id_ts_file = b.id_ts_file
    INNER JOIN `ts_file` c ON c.id = a.id_ts_file
    WHERE 
        a.date > b.last_date_listener
        and a.id_mysql_server = ".$id_mysql_server."
        GROUP BY id_mysql_server,id_ts_file";

        Debug::sql($sql);

        $res = $db->sql_query($sql);

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $this->dispatch($arr);
        }
    }

    private function dispatch($arr )
    {

        //A migrer avec un system de queue

        switch($arr['ts_file'])
        {
            case EngineV4::FILE_MYSQL_DATABASE:
                $this->updateDatabase($arr);
                break;

            case EngineV4::FILE_MYSQL_VARIABLE:
                $this->afterUpdateVariable($arr);
                break;

            default:

                break;
        }
        
        $this->updateListener($arr);

    }


    public function updateListener($param)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "BEGIN;";
        $db->sql_query($sql);

        $sql = "UPDATE ts_date_by_server SET is_listened=1 
        WHERE id_mysql_server=".$param['id_mysql_server']." AND id_ts_file=".$param['id_ts_file']." AND `date`='".$param['min_date']."';";
        $db->sql_query($sql);
        Debug::sql($sql);

        $sql ="UPDATE ts_max_date SET last_date_listener='".$param['min_date']."' 
        WHERE id_mysql_server=".$param['id_mysql_server']." AND id_ts_file=".$param['id_ts_file'].";";
        $db->sql_query($sql);
        Debug::sql($sql);

        $sql = "COMMIT;";
        $db->sql_query($sql);
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
            After one of these event we should refresh
        */
        
        $db = Sgbd::sql(DB_DEFAULT);

        $sql2 = "SELECT schema_name FROM mysql_database WHERE id_mysql_server=".$param['id_mysql_server'];
        Debug::debug($sql2, "SQL");
        $res2 = $db->sql_query($sql2);

        $dbs = array();
        while($ob2 = $db->sql_fetch_object($res2))    {
            $dbs[] = $ob2->schema_name;
        }

        $res = Extraction::display(array('mysql_database::database'), array($param['id_mysql_server']), array($param['min_date']));
        
        Debug::debug($res, 'dgrqdgrdg');
        $data = json_decode($this->extract($res)['database'], true);

        Debug::debug($res, "DATABASE ***************************");

        foreach($data as $elem) {
            $elem['id_mysql_server'] = $param['id_mysql_server'];

            unset($elem['catalog_name']);
            unset($elem['sql_path']);
            unset($elem['schema_comment']);

            $elem['collation_name'] = $elem['default_collation_name'];
            $elem['character_set_name'] = $elem['default_character_set_name'];

            unset($elem['default_collation_name']);
            unset($elem['default_character_set_name']);
            
            $this->updateElem("mysql_database", $elem);

            if (in_array($elem['schema_name'], $dbs)){
                unset($dbs[array_search($elem['schema_name'], $dbs)]);
            }
        }

        if (count($dbs ) > 1 ) {
            $sql3 = "DELETE FROM mysql_database WHERE id_mysql_server = ".$param['id_mysql_server']." 
            AND schema_name IN ('".implode("','",$dbs)."')";
            //Debug::sql($sql3);
            $db->sql_query($sql3);
        }

        //Debug::debug($data, "RESULTAT");
        //Debug::debug($dbs, "#################################");
    }


    /* get third level in array */
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

        //Debug::debug($param, "PARAM");
        
        foreach ($param as $key => $elem ) {
            $arg[] = " `".$key."` = '".$db->sql_real_escape_string($elem) ."'";
        }

        $params = implode(' AND ', $arg);

        $sql ="SELECT count(1) as cpt FROM `".$table_name."` WHERE ".$params;
        //Debug::debug($sql);

        $res = $db->sql_query($sql);
        while($ob = $db->sql_fetch_object($res))
        {
            //Debug::debug($ob->cpt , "CPT");
            
            if ($ob->cpt == "0")
            {
                $keys = array_keys($param);
                $values = array_values($param);
                
                //Debug::debug($keys);
                //Debug::debug($values);

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



    //after upgrading mysql_global_variable
    public function afterUpdateVariable($param)
    {
        $extract = Extraction::display(array('variables::'), array($param['id_mysql_server']), array($param['min_date']));
        $data[$param['id_mysql_server']] = $extract[$param['id_mysql_server']][''];

        if (!empty($data[$param['id_mysql_server']]['date']))
        {
            unset($data[$param['id_mysql_server']]['date']);
        }
        //Debug::debug($data , "VARIABLES");

        //to upgrade 
        //        => SELECT if different update and then update

        $db  = Sgbd::sql(DB_DEFAULT);


        $sql = "SELECT * FROM `global_variable` WHERE `id_mysql_server` IN (" . implode(',', array_keys($data)) . ");";
        //$this->logger->debug("SQL : $sql");
        
        Debug::sql($sql);
        $res = $db->sql_query($sql);

        $in_base = array();
        while ($ob = $db->sql_fetch_object($res)) {
            $in_base[$ob->id_mysql_server][$ob->variable_name] = $ob->value;
        }
        //$in_base[1]['jexistedansmesreve'] = "dream!";

        foreach ($data as $id_mysql_server => $err) {

            if (empty($in_base[$id_mysql_server])) {
                $insert[$id_mysql_server] = $data[$id_mysql_server];
                continue;
            }

            //move to special function

            //INSERT
            $insert[$id_mysql_server] = array_diff_key($data[$id_mysql_server], $in_base[$id_mysql_server]);

            //$this->logger->debug("INSERT : ".print_r($insert[$id_mysql_server]));

            //DELETE
            $delete[$id_mysql_server] = array_diff_key($in_base[$id_mysql_server], $data[$id_mysql_server]);

            //UPDATE
            $val_a[$id_mysql_server]  = array_diff_assoc($data[$id_mysql_server], $in_base[$id_mysql_server]);
            //$this->logger->debug("val A : ".print_r($val_a[$id_mysql_server]));

            $val_b[$id_mysql_server]  = array_diff_assoc($in_base[$id_mysql_server], $data[$id_mysql_server]);
            //$this->logger->debug("val B : ".print_r($val_a[$id_mysql_server]));


            $update[$id_mysql_server] = array_intersect_key($val_a[$id_mysql_server], $val_b[$id_mysql_server]);

            //$this->logger->notice("Variable has been updated : ".print_r($update[$id_mysql_server]));
        }

        //insert
        if (!empty($insert) && count($insert) > 0) {
            Debug::debug($insert, "TO INSERT");
            $elem_ins = array();
            foreach ($insert as $id_mysql_server => $variables) {
                foreach ($variables as $variable => $value) {
                    $elem_ins[] = '(' . $id_mysql_server . ',"' . $variable . '", "' . $db->sql_real_escape_string($value) . '")';
                }
            }

            if (!empty($elem_ins)) {
                $sql = "INSERT INTO global_variable (`id_mysql_server`,`variable_name`,`value`) VALUES " . implode(",", $elem_ins) . ";";
                Debug::sql($sql);
                //$this->logger->debug("INSERT SQL : $sql");
                $db->sql_query($sql);
            }
        }

        //delete
        if (!empty($delete) && count($delete) > 0) {
            Debug::debug($delete, "TO DELETE");
            $elem_del = array();
            foreach ($delete as $id_mysql_server => $variables) {
                foreach ($variables as $variable => $value) {
                    $elem_del[] = 'SELECT id FROM global_variable WHERE id_mysql_server=' . $id_mysql_server . ' AND `variable_name` ="' . $variable . '"';
                }
            }
            if (!empty($elem_del)) {
                $sql = "DELETE FROM global_variable WHERE id IN (" . implode(" UNION ", $elem_del) . ");";
                Debug::sql($sql);
                //$this->logger->debug("DELETE SQL : $sql");
                $db->sql_query($sql);
            }
        }

        //update
        if (!empty($update) && count($update) > 0) {
            Debug::debug($update, "TO UPDATE");
            $elem_upt = array();
            foreach ($update as $id_mysql_server => $variables) {
                foreach ($variables as $variable => $value) {
                    $elem_upt[] = '(' . $id_mysql_server . ',"' . $variable . '", "' . $db->sql_real_escape_string($value) . '")';
                }
                $var_to_update = array_keys($variables);
                if (count($var_to_update) > 0)
                {
                    $this->logger->notice("Variables to update (id_mysql_server: $id_mysql_server) : ".implode(',', $var_to_update));
                }
            }
            if (!empty($elem_upt)) {
                $sql = "INSERT INTO global_variable (`id_mysql_server`,`variable_name`,`value`) VALUES " . implode(",", $elem_upt) . " ON DUPLICATE KEY UPDATE `value`=VALUES(`value`);";
                Debug::sql($sql);
                //$this->logger->debug("UPDATE SQL : $sql");
                $db->sql_query($sql);
            }
        }
        //end to move
    }



    public function resetAll($param)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "UPDATE ts_max_date SET last_date_listener=date";
        $db->sql_query($sql);
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