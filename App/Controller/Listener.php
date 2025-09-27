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

use \App\Library\Mysql;

class Listener extends Controller
{
    var $logger;

    static $database = array();

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

        $sql ="SELECT id_ts_file, id_mysql_server, TIMESTAMPDIFF(SECOND,  `last_date_listener`, `date`) from ts_max_date where last_date_listener != date ORDER BY 3 DESC;";
        //Debug::sql($sql);
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res, MYSQLI_ASSOC)) {
            $this->getUpdateToDo(array($ob->id_mysql_server,$ob->id_ts_file));
        }
    }


    public function getUpdateTodo($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $id_ts_file = $param[1];

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT 
        a.id_mysql_server, 
        a.id_ts_file, 
        MIN(a.date) AS min_date, 
        MAX(a.date) AS max_date, 
        COUNT(*) AS cpt, 
        c.file_name AS ts_file
        FROM ts_date_by_server a
        INNER JOIN ts_file c ON c.id = a.id_ts_file
        WHERE a.id_mysql_server = ".$id_mysql_server." 
        AND a.id_ts_file = ".$id_ts_file."
        AND a.date > (SELECT last_date_listener 
        FROM ts_max_date 
        WHERE id_ts_file = ".$id_ts_file." 
            AND id_mysql_server = ".$id_mysql_server." 
            AND last_date_listener != date LIMIT 1)
        GROUP BY a.id_mysql_server, a.id_ts_file;";


        //Debug::sql($sql);

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
                //$this->detectProxy($arr);
                break;

            case "ps_events_statements_summary_by_digest":
                $this->collectQuery($arr);
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
        //Debug::sql($sql);

        $sql ="UPDATE ts_max_date SET last_date_listener='".$param['min_date']."' 
        WHERE id_mysql_server=".$param['id_mysql_server']." AND id_ts_file=".$param['id_ts_file'].";";
        $db->sql_query($sql);
        //Debug::sql($sql);

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

        Debug::debug($param);

        $from = 'mysql_database';
        $name = 'database';

        $res = Extraction::display(array($from.'::'.$name), array($param['id_mysql_server']), array($param['min_date']));

        // if we cannot find last entry we order delete of MD5 and simple return

        if (empty($res))
        {
            $sql = "select * from ts_variable where name ='".$name."' and `from`='".$from."';";

            $res123 = $db->sql_query($sql);

            while($ob = $db->sql_fetch_object($res123))
            {
                $id_ts_file= $ob->id_ts_file;
            }

            $purge[$id_ts_file] = array();
            $purge[$id_ts_file][] = $param['id_mysql_server'];
                                                        
            EngineV4::cleanMd5($purge);

            // write something to log
            return;
        }

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
        Debug::parseDebug($param);


        if (!empty($param[0])) {
            $param['id_mysql_server'] = $param[0];
        }

        if (!empty($param[1])) {
            $param['min_date'] = $param[1];
        }

        Debug::debug($param);

        $extract = Extraction2::display(array('variables::'), array($param['id_mysql_server']), array($param['min_date']));
        
        if (! empty($extract))
        {
            //Debug::debug($extract, "EXTRACT");
            $data[$param['id_mysql_server']] = $extract[$param['id_mysql_server']];

        }
        
        if (!empty($data[$param['id_mysql_server']]['date']))
        {
            unset($data[$param['id_mysql_server']]['date']);
        }
        Debug::debug($data , "VARIABLES");

        //to upgrade 
        //        => SELECT if different update and then update


        if (! empty($data))
        {

            $db  = Sgbd::sql(DB_DEFAULT);
            $sql = "SELECT * FROM `global_variable` WHERE `id_mysql_server` IN (" . implode(',', array_keys($data)) . ");";
            Debug::sql($sql);
            $res = $db->sql_query($sql);

            $in_base = array();
            while ($ob = $db->sql_fetch_object($res)) {
                $in_base[$ob->id_mysql_server][$ob->variable_name] = $ob->value;
            }
            //$in_base[1]['jexistedansmesreve'] = "dream!";
            Debug::debug("OK !");

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
    }



    public function resetAll($param)
    {
        Debug::parseDebug($parma);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "UPDATE ts_max_date SET last_date_listener=date";
        debug::sql($sql);
        $db->sql_query($sql);
    }

    /* le but de cette fonction est de checker le status des listener pour vérifier que tout ce comporte correctement. */

    public function index()
    {
        



    }

    public function status($param)
    {
  
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql ="select b.id as id_mysql_server, b.display_name, b.id, c.file_name , `date`, last_date_listener, TIMESTAMPDIFF(SECOND,  `last_date_listener`, `date`) AS diff_seconds
        from ts_max_date a 
        inner join mysql_server b on a.id_mysql_server = b.id 
        INNER JOIN ts_file c on c.id = a.id_ts_file 
        WHERE b.id = 1
        order by 5 desc, display_name, file_name;";
        Debug::sql($sql);

        $res = $db->sql_query($sql);

        $data['listener'] = array();

        while ($arr = $db->sql_fetch_array($res , MYSQLI_ASSOC))
        {
            $data['listener'][] = $arr;
        }
        $cmd = "cd ".TMP.'md5 && find . -type f ! -name ".*" -printf "%M %u %g %TY-%Tm-%Td %TH:%TM:%TS %p\n" | sed \'s/\.[0-9]*//\'';

        Debug::debug($cmd);
        $text = shell_exec($cmd );

        $data['md5'] = $this->splitAndFormat($text);
        Debug::debug($data);

        $this->set('data', $data);

    }


    function splitAndFormat($text)
    {
        $data = array();

        $text = trim($text);

        $lines = explode("\n", $text);

        foreach($lines as $line)
        {
            $elems = explode(" ",$line );

            $get = explode("/" , $elems[5]);
            $parts = explode("::", $get[1]);

            $id_mysql_server = explode(".",$parts[1])[0];
            $file_name = $parts[0];

            $elem = array();
            $elem['chmod'] = $elems[0];
            $elem['owner'] = $elems[1];
            $elem['group'] = $elems[2];
            $elem['datetime'] = $elems[3]." ".$elems[4];
            $elem['file_name'] = $file_name;
            $elem['id_mysql_server'] = $id_mysql_server;
            $data[$id_mysql_server][$file_name] = $elem;
        }

        Debug::debug($data);

        return $data;
    }


    public function test5( $param )
    {
        Debug::parseDebug($param);

        $gg = Extraction2::display(array("variables::"), array(1), array("2025-03-29 20:40:20"));
        //$gg = Extraction2::getQuery(array(array("variables::"), 1, "2025-03-29 20:40:20"));

        Debug::debug($gg);
    }


    /*
    ps_events_statements_summary_by_digest
    */
    public function collectQuery($param)
    {

        $db = Sgbd::sql(DB_DEFAULT);

        //Debug::debug($param,"ICI");

        $id_mysql_server = $param['id_mysql_server'];
        $date= $param['min_date'];

        $queries = Extraction2::display(array("performance_schema::events_statements_summary_by_digest"), 
        array($id_mysql_server), array($date));

        if (empty($queries[$id_mysql_server]['events_statements_summary_by_digest']['data'])) {
            return true;
        }

        //Debug::debug($queries, "query");

        $param['queries'] = $queries[$id_mysql_server]['events_statements_summary_by_digest']['data'];
        
        $id_query = $this->insertNewQuery($param);

        if (!empty($id_query)) // case P_S not activated or INNODB not activated
        {
            $register = $this->selectIdfromDigest(array($id_query));
        }
        
        
        $i = 0;

        $SQL = [];
        $keys = [];

        foreach($queries[$id_mysql_server]['events_statements_summary_by_digest']['data'] as $query)
        {
            $i++;
            //Debug::debug($query);

            $data_lower = array_change_key_case($query, CASE_LOWER);


            //SCHEMA_NAME
            $id_mysql_database = $this->getIdDatabase(array($id_mysql_server, $data_lower['schema_name']));

            if (empty($id_mysql_database))
            {
                $this->logger->warning("Couldn't find database  (id_mysql_server: $id_mysql_server) : ".implode(',', $data_lower));
                continue;
            }

            $id_mysql_query = $register[$data_lower['digest']];

            if (empty($id_mysql_query))
            {
                $this->logger->warning("Couldn't find id_mysql_query  (id_mysql_server : $id_mysql_server - id_mysql_query: $id_mysql_query) : ".implode(',', $data_lower));
                continue;
            }
            
            $result = array_filter($data_lower, function($value, $key) {
                return (strpos($key, 'sum') === 0 || strpos($key, 'count_star') === 0);
            }, ARRAY_FILTER_USE_BOTH);

            $result['id_mysql_query'] = $id_mysql_query;
            $result['id_mysql_server'] = $id_mysql_server;
            $result['id_mysql_database'] = $id_mysql_database;
            
            //$result['date'] = $date;

            
            if ($i ===1 ) {
                $keys = array_keys($result);
            }

            $val = array_values($result);
            //Debug::debug($result);

            $SQL[] = "('".$date."' ,".implode(",", $val).")";
        }

        $fields  = 'INSERT INTO ts_mysql_query (`date`, `'. implode('`,`', $keys).'`) VALUES ';

        $sql = $fields.implode(",",$SQL).";";
        $db->sql_query($sql);

        //Debug::debug($sql);
    }

    public function insertNewQuery($param)
    {
        $id_mysql_server = $param['id_mysql_server'];
        $date= $param['min_date'];
        $queries = $param['queries'];


        

        $db = Sgbd::sql(DB_DEFAULT);

        $id_query = $this->getDigestFromDate(array($id_mysql_server, $date));

        if (empty($id_query)){
            return $id_query;
        }

        $nb_id = count($id_query);

        $list = implode("','", $id_query);

        $sql2 = "SELECT count(1) as cpt FROM mysql_query WHERE digest_mariadb IN ('".$list."')";
        //Debug::sql($sql2);
        $res2 = $db->sql_query($sql2);


        $register = $this->selectIdfromDigest(array($id_query));
        $keys = array_keys($register);

        while ($ob2 = $db->sql_fetch_object($res2))
        {
            if ($nb_id > $ob2->cpt)
            {
                foreach($queries as $query)
                {
                    if (in_array($query['DIGEST'], $keys)) {
                        continue;
                    }

                    $this->logger->warning('DIGEST_TEST : '.$query['DIGEST_TEXT']);


                    $size = mb_strlen($query['DIGEST_TEXT']);

                    if ($size === 32)
                    {

                    }
                    else if ($size === 64){
                        
                    }

                    $sql3 = "INSERT IGNORE INTO mysql_query (digest_text_md5, digest_mariadb, query_mariadb,digest_mysql,query_mysql)
                    VALUES ('".self::getHash(array($query['DIGEST_TEXT']))."', 
                    '".substr($query['DIGEST'],0,32)."','".$db->sql_real_escape_string($query['DIGEST_TEXT'])."',
                    '".$query['DIGEST']."','".$db->sql_real_escape_string($query['DIGEST_TEXT'])."')";
                    Debug::sql($sql3);
                    $db->sql_query($sql3);
                }
            }
        }

        return $id_query;
    }

    public function selectIdfromDigest($param)
    {
        Debug::parseDebug($param);


        $id_query = $param[0];

        $db = Sgbd::sql(DB_DEFAULT);

        $list = implode("','", $id_query);

        $sql = "SELECT id, digest_mariadb FROM mysql_query WHERE digest_mariadb IN ('{$list}')
        UNION ALL 
        SELECT id, digest_mysql FROM mysql_query WHERE digest_mysql IN ('{$list}');";
        $res = $db->sql_query($sql);

        $data = [];
        while ($ob = $db->sql_fetch_object($res)) {
            $data[$ob->digest_mariadb] = $ob->id;
        }

        //Debug::debug($data);

        return $data;
    }

    public function getDigestFromDate($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $date= $param[1];

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT JSON_KEYS(value, '$.data') AS digests, date FROM ts_value_general_json 
        WHERE id_mysql_server = ".$id_mysql_server." 
        AND id_ts_variable IN (SELECT id from ts_variable WHERE name = 'events_statements_summary_by_digest') 
        AND  date = '".$date."' LIMIT 1";
        //Debug::sql($sql);

        $res = $db->sql_query($sql);

        while($ob = $db->sql_fetch_object($res)) {
            $data = json_decode($ob->digests);
        }

        //Debug::debug($data);

        return $data;

    }


    public function getIdDatabase($param)
    {
        Debug::parseDebug($param);
        $id_mysql_server = $param[0];
        $database = $param[1];


        if (!empty(self::$database[$id_mysql_server][$database]))
        {
            return self::$database[$id_mysql_server][$database];
        }
        else
        {
            $db = Sgbd::sql(DB_DEFAULT);

            if (empty($database))
            {
                $database = 'NONE';
            }

            $sql = "SELECT id from mysql_database where id_mysql_server=$id_mysql_server and schema_name='".$database."';";
            $res = $db->sql_query($sql);

            while($ob = $db->sql_fetch_object($res))
            {
                self::$database[$id_mysql_server][$database] = $ob->id;
                //Debug::debug($ob->id, "id_mysql_database");
                return $ob->id;
            }

            $sql ="INSERT INTO mysql_database SET schema_name='$database', id_mysql_server=$id_mysql_server";
            $db->sql_query($sql);
            return false;
        }
    }

    /*  
        Get the first id_mysql_server who got MySQL 8.4 and available server
    */

    static public function getIdMySql84($param)
    {
        Debug::parseDebug($param);
        $res = Extraction2::display(array('version','mysql_server::mysql_available'));

        //Debug::debug($res);

        foreach($res as $id_mysql_server => $server)
        {
            $pos = strpos($server['version'], "8.4.");

            if ($pos !== false) {
                if ($server['mysql_available'] === "1" ) {

                    Debug::debug($id_mysql_server);
                    return $id_mysql_server;
                }
            }
        }
        return null;
    }


    static public function getDigest($param)
    {
        Debug::parseDebug($param);
        $query = $param[0];

        if (in_array($query, array("SHOW SLAVE STATUS")))
        {
            return NULL;
        }
        

        $id_mysql_server = self::getIdMySql84($param);

        if ($id_mysql_server === false) {
            return null;
        }

        $db = Mysql::getDbLink($id_mysql_server);

        // replace ? by any value, to make query valid
        $query = str_replace('LIMIT ?', 'LIMIT 1', $query);
        $query = str_replace('?', '"34"', $query);

        $sql = "SELECT STATEMENT_DIGEST('".$db->sql_real_escape_string($query)."');";
        $res = $db->sql_query($sql);

        while ($arr = $db->sql_fetch_array($res, MYSQLI_NUM))
        {
            Debug::debug($arr);
            return $arr[0];
        }

        return null;
    }


    static public function getHash($param)
    {
        Debug::parseDebug($param);
        $query = $param[0];

        return md5($query);
    }


    static public function getIdMariaDb12($param)
    {
        Debug::parseDebug($param);
        $res = Extraction2::display(array('version','mysql_server::mysql_available'));

        //Debug::debug($res);

        foreach($res as $id_mysql_server => $server)
        {
            $pos = strpos($server['version'], "12.0.");

            if ($pos !== false) {
                if ($server['mysql_available'] === "1" ) {

                    Debug::debug($id_mysql_server);
                    return $id_mysql_server;
                }
            }
        }
        return null;
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

/*
UPDATE performance_schema.setup_consumers 
SET enabled = 'YES' 
WHERE name IN ('events_statements_history', 'events_statements_history_long');

UPDATE performance_schema.setup_instruments SET enabled = 'YES', timed = 'YES' WHERE name LIKE 'statement/%';
*/



/*
SELECT t.*
FROM ts_value_general_int t
JOIN (
  -- Pour chaque minute, on récupère la plus petite date (le premier enregistrement de la minute)
  SELECT
    DATE_FORMAT(date, '%Y-%m-%d %H:%i:00') AS minute,
    MIN(date) AS first_date
  FROM ts_value_general_int
  WHERE id_ts_variable = 136
    AND id_mysql_server = 1
    AND date > '2025-06-21'
  GROUP BY minute
) first_per_minute ON t.date = first_per_minute.first_date
WHERE t.id_ts_variable = 136
  AND t.id_mysql_server = 1
ORDER BY t.date DESC
LIMIT 100;

*/