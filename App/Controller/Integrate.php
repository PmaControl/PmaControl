<?php


namespace App\Controller;

use Exception;
use \Glial\Synapse\Controller;
use Fuz\Component\SharedMemory\Storage\StorageFile;
use Fuz\Component\SharedMemory\SharedMemory;
use \App\Library\Debug;
use \App\Controller\Aspirateur;
use \App\Library\Microsecond;
use \App\Library\EngineV4;
use \App\Library\Mysql;
use \Glial\Sgbd\Sgbd;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;

//require ROOT."/application/library/Filter.php";
// ./glial control rebuildAll --debug

class Integrate extends Controller
{
    use \App\Library\Filter;
    const MAX_FILE_AT_ONCE = 10;
    //advice *2 of or result from select count(1) from ts_file;

    const VARIABLES = "mysql_global_variable";

    var $shared;
    //var $memory_file = "answer";
    var $files = array();

    var $logger;

    // on list ici les serveurs pour lequel il faut purger les fichiers de md5 pour forcer le rafraichissement
    static $id_mysql_server__to_refresh = array();

    public function before($param)
    {
        $monolog       = new Logger("Integrate");
        $handler      = new StreamHandler(LOG_FILE, Logger::NOTICE);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $monolog->pushHandler($handler);
        $this->logger = $monolog;
    }

    public function getIdTsFile($ts_file)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM ts_file;";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $this->files[$ob->file_name] = $ob->id;
        }

        if (empty($this->files[$ts_file]))
        {
            $this->logger->notice("Insertion of new type of DATA : $ts_file ");
            $sql = "INSERT IGNORE INTO ts_file (file_name) VALUES ('".$ts_file."')";
            $id_ts_file = $db->sql_query($sql);
            $this->files[$ts_file] = $id_ts_file;

            Mysql::addMaxDate($param = array());
        }

        return $this->files[$ts_file];
    }


    private function get_variable()
    {
        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM `ts_variable`";

        $res = $db->sql_query($sql);

        $variables = array();
        while ($ob = $db->sql_fetch_object($res)) {
            $variables[$ob->from][$ob->name]['id']   = $ob->id;
            $variables[$ob->from][$ob->name]['type'] = $ob->type;
        }

        return $variables;
    }

    static private function isFloat($value)
    {
        // test before => must be numeric first
        if (strstr($value, ".")) {
            return true;
        }
        return ((int) $value != $value); // problem avec PHP_INT_MAX
    }
    /* Type :
     *  - 1 => int
     *  - 2 => double
     *  - 3 => text
     */

    static private function getTypeOfData($value)
    {
        //Debug::debug($value, "VALUE");

        $val = 3;
        $is_numeric = is_numeric($value);

        if ($is_numeric === true) {
            //debug($is_numeric);
            $val = 1; // bigint unsigned

            $is_float = self::isFloat($value);

            if ($is_float) {
                $val = 2;
            }

            //case of negative int (not allowed)
            if ($value < 0) {
                throw new \Exception("PMACTRL-497 : Negative value not allowed (" . $value . ")");
            }
        }

        if ($val === 3)
        {
            if (!empty($value) && self::isJson($value) ) {
                $val = 4;
            }
        }

        return self::convert($val);
    }

    private function insert_variable($variables_to_insert)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        // insert IGNORE in case of first save have 2 slave
        //$this->logger->warning("Insert new value :".json_encode($variables_to_insert));

        $sql = "INSERT IGNORE INTO ts_variable (`id_ts_file`, `name`,`type`,`from`,`radical`) 
        VALUES " . implode(",", $variables_to_insert) . ";";
        $res = $db->sql_query($sql);

        //self::$id_mysql_server__to_refresh
        EngineV4::cleanMd5(self::$id_mysql_server__to_refresh);

        if (!$res) {
            throw new \Exception("PMACTRL-994 : Impossible to insert value in ts_variable");
        }
    }

    private function insert_value($values)
    {
        //Debug::debug($values);

        if (count($values) == 0) {
            return 1;
        }

        $db = Sgbd::sql(DB_DEFAULT);

        Debug::checkPoint("start save");

        foreach ($values as $type => $elems) {

            $time_start = microtime(true);

            $sql = "INSERT INTO `ts_value_general_" . strtolower($type) . "` (`id_mysql_server`,`id_ts_variable`,`date`, `value`) VALUES " . implode(",", $elems) . ";";
            //Debug::debug(count($elems), "type : $type");
            $db->sql_query($sql);

            

            $time_end = microtime(true);
            $time = $time_end - $time_start;

            Debug::debug("[ts_value_general_". strtolower($type) . "] (count : ".count($elems).") insert in $time seconds");

            Debug::checkPoint("saved " . $type . " elems : " . count($elems));
        }
    }


    private function insert_slave_value($values, $val = "slave")
    {
        Debug::debug($val, "VAL");

        if (count($values) == 0) {
            return 1;
        }

        $db = Sgbd::sql(DB_DEFAULT);

        Debug::checkPoint("start save");

        foreach ($values as $type => $elems) {

            $time_start = microtime(true);


            switch($val)
            {
                case 'slave': 
                    $extra_field = 'connection_name';
                    break;

                case 'digest':
                    $extra_field = 'id_ts_mysql_query';
                    break;
            }

            $sql = "INSERT INTO `ts_value_" . $val . "_" . strtolower($type) . "` 
            (`id_mysql_server`,`".$extra_field."` ,`id_ts_variable`,`date`, `value`) 
            VALUES " . implode(",\n", $elems) . ";";

            Debug::sql($sql);

            
            $gg = $db->sql_query($sql);

            $time_end = microtime(true);
            $time = $time_end - $time_start;

            Debug::debug("[ts_value_" . $val . "_" . strtolower($type) . "] (count : ".count($elems).") insert in $time seconds");

            /*
              if (!$gg) {
              debug($db->sql_error());
              } */

            Debug::checkPoint("saved " . $type . " elems : " . count($elems));
        }
    }

    private function linkServerVariable($history, $memory_file)
    {
        $db = Sgbd::sql(DB_DEFAULT);


        $data = array();
        foreach ($history as $date => $entries) {
            foreach ($entries as $entry) {
                $id_ts_file = $entry['id_ts_file'];
                $id_server = $entry['id_server'];
        
                // Initialiser le sous-tableau si nécessaire
                if (!isset($data[$date][$id_ts_file])) {
                    $data[$date][$id_ts_file] = [];
                }
        
                // Ajouter l'id_server au tableau correspondant
                $data[$date][$id_ts_file][] = $id_server;
            }
        }

        $sql3 = array();
        foreach ($data as $date => $elems) {

            foreach($elems as $id_ts_file => $id_servers)
            {
                //upgrade by date for server & id_ts_file in same time
                $sql = "UPDATE `ts_max_date`  SET `date_p4`=`date_p3`,`date_p3`=`date_p2`,`date_p2`=`date_p1`,`date_p1`=`date`,`date`= '" . $date . "'
                WHERE `id_mysql_server` IN (" .  implode(', ',$id_servers) . ") AND `id_ts_file`=" . $id_ts_file . ";";

                Debug::sql($sql);
                $db->sql_query($sql);

                foreach ($id_servers as $id_server) {
                    $sql3[] = "(" . $id_server . ", " . $id_ts_file . ", '" . $date . "')";
                }
            }
        }

        $sql3 = array_unique($sql3);
        
        //probability to have at asame second ? => it's happened
        $sql2 = "INSERT INTO `ts_date_by_server` (`id_mysql_server`,`id_ts_file`, `date`) VALUES ";
        $sql4 = $sql2 . implode(",\n", $sql3) . ";";
        //$this->logger->emergency($sql4);
        //Debug::sql($sql4);

        $db->sql_query($sql4);
    }

    private static function convert($id, $revert = false)
    {
        $gg[1] = "INT";
        $gg[2] = "DOUBLE";
        $gg[3] = "TEXT";
        $gg[4] = "JSON";

        if ($revert === true) {
            $gg = array_flip($gg);
        }

        return $gg[$id];
    }

    private function getIdMemoryFile($memory_file)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        //on met en cache
        if (empty($this->files)) {

            $sql = "SELECT * FROM ts_file";
            $res = $db->sql_query($sql);
            //Debug::debug($sql);

            while ($ob = $db->sql_fetch_object($res)) {
                $this->files[$ob->file_name] = $ob->id;
            }
            //Debug::debug($this->files);
        }

        if (empty($this->files[$memory_file])) {
            //INSERT MEMORY FILE there

            $this->logger->error('Impossible to find this file name : "'.$memory_file.'"');
            //throw new \Exception('PMACTRL-098 : Impossible to find this file name : "'.$memory_file.'"');
        }
        $id_file_name = $this->files[$memory_file];

        return $id_file_name;
    }


public function integrateAll($param)
{
    Debug::parseDebug($param);


    Debug::debug($param, "PARAM");
    // $param[0] = separator_type OPTIONAL


    // mysql_binlog mysql_global mysql_global_variable mysql_innodb_metrics mysql_processlist mysql_schemata mysql_server mysql_variable_gtid
    // ssh_server information_schema__metadata_lock_info 
    // maxscale_filters maxscale_listeners maxscale_maxscale maxscale_monitors maxscale_server maxscale_servers maxscale_service_server maxscale_services maxscale_sessions maxscale_users
    // ssh_hardware ssh_stats is_tables information_schema__plugins mysql_table
    // digest


    
    $files = $param[0] ?? '';
    $loop = $param[1] ?? "loop:0";

    $db = Sgbd::sql(DB_DEFAULT);

    $start = microtime(true);
    $date = new \DateTime();
    $date_start = $date->format('Y-m-d H:i:s.u');

    $this->logger->info('[Start] IntegrateAll ' . $date_start . ' (sep=' . $files . ')');

    // on passe uniquement le separator
    $this->evaluate([$files]);

    $date_end = date('Y-m-d H:i:s');
    $end = microtime(true);

    $duration_ms = round(($end - $start) *1000);

    $this->logger->info('[END] IntegrateAll ' . $date_end . ' duration=' . $duration_ms . 'ms');


    $loop = explode(':', $loop)[1];

    // INSERT EXEC TIME
    $sql = "INSERT INTO integrate_all_run_time 
               (`loop`,`files`, `date_start`, `duration`)
            VALUES (
            $loop,
               '" . $files . "',
               '" . $date_start . "',
               ".$duration_ms."
            )";

    $db->sql_query($sql);
    $db->sql_close();

    return true;
}
    // move to other place ?
    static function isJson($string) {
        
        if ($string == "NULL") {
            return false;
        }

        if (is_array($string))
        {
            Debug::debug($string);
            throw new Exception("ERROR should be a string !");  
        }
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }


    public function evaluate($param)
    {

        Debug::parseDebug($param);
        $TIME = time();

        $ts_file_list = $param[0] ?? '';

        $db         = Sgbd::sql(DB_DEFAULT);
        $this->view = false;


        $ts_file = explode(',',$ts_file_list);

        $files = [];
        foreach($ts_file as $file)
        {

            $file_match = EngineV4::PATH_PIVOT_FILE ."*".EngineV4::SEPERATOR."$file";
            $part_file = glob($file_match);

            $files = array_merge($files, $part_file);
        }

        Debug::debug($files, "FILES BEFORE");
        
        if (empty($files)) {
            usleep(100);
            return true;
        }

        array_multisort(array_map('filemtime', $files), SORT_NUMERIC, SORT_ASC, $files);
        Debug::debug($files, "FILES SORTED");

        $variables           = $this->get_variable();
        $variables_to_insert = array();

        $insert        = array();
        $var_index     = array();
        $file_parsed   = 0;
        $id_servers    = array();
        $history       = array();
        $slave         = array();
        $digest_insert = array();

        foreach ($files as $file) {

            Debug::debug($file);
            $elems = explode('/', $file);
            $file_name = end($elems);

            $_elems    = explode(EngineV4::SEPERATOR, $file_name);
            $ts_file   = end($_elems);
            $timestamp = $_elems[0];

            $id_ts_file  = $this->getIdTsFile($ts_file);
            $memory_file = $ts_file;

            Debug::debug("$timestamp :: $ts_file");

            if ($TIME == $timestamp) {
                unset($files[$id_ts_file]);
                continue;
            }

            Debug::debug("$id_ts_file => $file");
            $file_parsed++;

            $storage = new StorageFile($file);
            $data    = new SharedMemory($storage);
            $elems   = $data->getData();

            foreach ($elems as $elem) {
                foreach ($elem as $date => $server) {
                    $date = date('Y-m-d H:i:s', $date);

                    foreach ($server as $id_server => $all_metrics) {

                        $history[$date][] = array('id_server' => $id_server, 'id_ts_file' => $id_ts_file);
                        $id_servers[]     = $id_server;

                        if (!empty($all_metrics)) {
                            foreach ($all_metrics as $type_metrics => $metrics) {
                                //Debug::debug("*** ts_file:$date > id_mysql_server:$id_server > from:$type_metrics  ***");

                                if (is_array($metrics)) {
                                    $metrics = array_change_key_case($metrics);
                                    //Debug::debug($metrics,"metrics");

                                    foreach ($metrics as $variable => $value) {

                                        if (is_array($value))
                                        {
                                            
                                            $value = array_change_key_case($value);
                                            //Debug::debug($value);
                                        }

                                        // === SLAVE SECTION ===
                                        if (is_array($value) &&  isset($value['connection_name'])   ) {
                                            

                                            if (empty($value['seconds_behind_master']))
                                            {
                                                $value['seconds_behind_master'] = "0";
                                            }

                                            foreach ($value as $slave_variable => $slave_value) {

                                                $connection_name = $value['connection_name'] ?? "";

                                                if (empty($variables[$type_metrics][$slave_variable])) {

                                                    if ($slave_value === "-1") continue;

                                                    if (empty($var_index[$type_metrics][$slave_variable])) {
                                                        $var_index[$type_metrics][$slave_variable] = 1;
                                                        $variables_to_insert[] = '(' . $id_ts_file . ',"' . $slave_variable . '", "' . $this->getTypeOfData($slave_value) . '", "' . $type_metrics . '", "slave")';
                                                        self::$id_mysql_server__to_refresh[$id_ts_file][] = $id_server;
                                                    }

                                                    if ($slave_value === "") continue;
                                                } else {
                                                    $varType = $variables[$type_metrics][$slave_variable]['type'];

                                                    if ($varType == "TEXT") {
                                                        if (is_null($slave_value)) $slave_value = '';
                                                        $slave_value = $db->sql_real_escape_string($slave_value);
                                                        $slave[$varType][] = '(' . $id_server . ','
                                                            . '"' . $connection_name . '", '
                                                            . $variables[$type_metrics][$slave_variable]['id'] . ', "'
                                                            . $date . '", "'
                                                            . $slave_value . '")';
                                                    } else {
                                                        if ($slave_value == "") $slave_value = 'NULL';
                                                        if ($varType == "DOUBLE" && $slave_value === "") $slave_value = 0;
                                                        $slave[$varType][] = '(' . $id_server . ','
                                                            . '"' . $connection_name . '", '
                                                            . $variables[$type_metrics][$slave_variable]['id'] . ', "'
                                                            . $date . '", '
                                                            . $slave_value . ')';
                                                    }
                                                }
                                            } // END SLAVE
                                        }

                                        // === DIGEST SECTION ===
                                        elseif (is_array($value)) {

                                            
                                            foreach ($value as $digest_variable => $digest_value) {

                                                $id_ts_mysql_query = $value['id_ts_mysql_query'] ?? "";

                                                if (empty($variables[$type_metrics][$digest_variable])) {

                                                    if ($digest_value === "-1") continue;

                                                    if (empty($var_index[$type_metrics][$digest_variable])) {
                                                        $var_index[$type_metrics][$digest_variable] = 1;

                                                        //(`id_ts_file`, `name`,`type`,`from`,`radical`)
                                                        $variables_to_insert[] = '(' . $id_ts_file . ',"' . $digest_variable . '", "' . $this->getTypeOfData($digest_value) . '", "' . $type_metrics . '", "digest")';
                                                        self::$id_mysql_server__to_refresh[$id_ts_file][] = $id_server;
                                                    }

                                                    if ($digest_value === "") continue;
                                                } else {
                                                    $varType = $variables[$type_metrics][$digest_variable]['type'];

                                                    if (in_array($varType, ["TEXT", "JSON"])) {
                                                        if (is_null($digest_value)) $digest_value = '';
                                                        $digest_value = $db->sql_real_escape_string($digest_value);
                                                        $digest_insert[$varType][] = '(' . $id_server . ','
                                                            . '"' . $id_ts_mysql_query . '", '
                                                            . $variables[$type_metrics][$digest_variable]['id'] . ', "'
                                                            . $date . '", "'
                                                            . $digest_value . '")';
                                                    } else {
                                                        if ($digest_value === "") $digest_value = 'NULL';
                                                        if ($varType == "DOUBLE" && $digest_value === "") $digest_value = 0;
                                                        $digest_insert[$varType][] = '(' . $id_server . ','
                                                            . '"' . $id_ts_mysql_query . '", '
                                                            . $variables[$type_metrics][$digest_variable]['id'] . ', "'
                                                            . $date . '", '
                                                            . $digest_value . ')';
                                                    }
                                                }
                                            } // END DIGEST
                                        }

                                        // === GENERAL SECTION ===
                                        else {

                                            if (!empty($variables[$type_metrics][$variable])) {

                                                if ($memory_file === self::VARIABLES) {
                                                    $mysql_variable[$id_server][strtolower($variable)] = $value;
                                                }

                                                if ($variables[$type_metrics][$variable]['type'] === 'INT') {
                                                    if ($value === "") {
                                                        $value = "0";
                                                    } elseif ($value < 0) {
                                                        continue;
                                                    }
                                                } elseif (in_array($variables[$type_metrics][$variable]['type'], array("TEXT", "JSON"))) {
                                                    $value = $db->sql_real_escape_string($value);
                                                } elseif ($variables[$type_metrics][$variable]['type'] == "DOUBLE") {
                                                    if ($value === "") {
                                                        $value = 0;
                                                    }
                                                }

                                                $insert[$variables[$type_metrics][$variable]['type']][] = '(' . $id_server . ','
                                                    . $variables[$type_metrics][$variable]['id'] . ', "'
                                                    . $date . '", "'
                                                    . $value . '")';

                                                //Debug::debug($insert, "$type_metrics - $variable INSERTTTTTTTTTTTTTTTTTTT");
                                            } else {

                                                if ($value === "-1" || $value === "") {
                                                    continue;
                                                }

                                                //Debug::debug($this->getTypeOfData($value), "TYPE : $variable");

                                                if (empty($var_index[$type_metrics][$variable])) {
                                                    Debug::debug($insert, "val to insert in ts_variable");
                                                    $var_index[$type_metrics][$variable] = 1;
                                                    $variables_to_insert[] = '(' . $id_ts_file . ',"' . $variable . '", "' . $this->getTypeOfData($value) . '", "' . $type_metrics . '", "general")';
                                                    self::$id_mysql_server__to_refresh[$id_ts_file][] = $id_server;
                                                }
                                            }
                                        }
                                    } // end foreach $metrics
                                }
                            }
                        }
                    } // date
                }
            }

            Debug::checkPoint("before insert file : " . $file);

            if (file_exists($file)) {
                unlink($file);
            } else {
                $this->logger->emergency('Two process in same time for integrate the same data, please remove one');
                throw new \Exception("PMACTRL-647 : deux intégrateurs lancés en même temps (supprimer le mauvais)");
            }

            /*
            if ($file_parsed >= 2) {
                break;
            }*/

            if ($file_parsed >= self::MAX_FILE_AT_ONCE) {
                break;
            }

        }

        if (count($files) === 0) {
            usleep(300000);
            return true;
        }

        if (count($variables_to_insert) > 0) {
            Debug::checkPoint("variables");
            Debug::debug($variables_to_insert, "variables_to_insert");
            $this->insert_variable($variables_to_insert);
        } else {
            Debug::checkPoint("values");
            $this->insert_value($insert);

            if (!empty($slave)) {
                $this->insert_slave_value($slave, "slave");
            }

            if (!empty($digest_insert)) {
                $this->insert_slave_value($digest_insert, "digest");
            }
        }

        if (!empty($history)) {
            $this->linkServerVariable($history, $memory_file);
        }

        Debug::debugQueriesOff();
    }

    public function purgeAll($param)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql ="SET FOREIGN_KEY_CHECKS=0;";
        Debug::sql($sql);
        $db->sql_query($sql);

        $sql ="TRUNCATE TABLE listener_main";
        Debug::sql($sql);
        $db->sql_query($sql);


        $sql ="SET FOREIGN_KEY_CHECKS=1;";
        Debug::sql($sql);
        $db->sql_query($sql);

    }

}