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
    const MAX_FILE_AT_ONCE = 20;
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

    public function evaluate($param)
    {
        $TIME = time();
        
        //Debug::debug($id_ts_file, "id_ts_file");

        $db         = Sgbd::sql(DB_DEFAULT);
        $this->view = false;

        $files = glob(EngineV4::PATH_PIVOT_FILE ."*".EngineV4::SEPERATOR."*");
        Debug::debug($files, "FILES BEFORE");
        
        if (empty($files)) {
            usleep(100);
            return true;
        }

        array_multisort(array_map('filemtime', $files), SORT_NUMERIC, SORT_ASC, $files);

        //$this->logger->emergency(print_r($files));
        Debug::debug($files, "FILES SORTED");

        $variables           = $this->get_variable();
        $variables_to_insert = array();

        $insert      = array();
        $var_index   = array();
        $file_parsed = 0;

        $id_servers = array();
        $history    = array();

        foreach ($files as $file) {
            
            Debug::debug($file);
            
            $elems = explode('/', $file);
            $file_name = end($elems);

            $_elems = explode(EngineV4::SEPERATOR, $file_name);
            $ts_file = end($_elems);

            $timestamp = $_elems[0];

            $id_ts_file = $this->getIdTsFile($ts_file);
            $memory_file = $ts_file;
            

            Debug::debug("$timestamp :: $ts_file");


            //if ($TIME == $timestamp || $TIME2 == $timestamp){
            if ($TIME == $timestamp){
                //$this->logger->warning("##### We don't take this file :".$file_name. " => $TIME");
                unset($files[$id_ts_file]);
                continue;
            }
            Debug::debug("$id_ts_file => $file");

            
            //$this->logger->notice("We occupy with file ".$file);

            $file_parsed++;
            Debug::debug($file, " [FILE] ");

            $storage = new StorageFile($file); // to export in config ?
            $data    = new SharedMemory($storage);

            $elems = $data->getData();

            //Debug::debug($elems,"data");

            foreach ($elems as $elem) {

                //sort by microsecond do we really need ?
                foreach ($elem as $date => $server) {

                    //$date = Microsecond::tsToDate($date);
                    $date = date('Y-m-d H:i:s', $date);

                    foreach ($server as $id_server => $all_metrics) {
                        $history[$date][] = array('id_server' => $id_server, 'id_ts_file' => $id_ts_file);
                        $id_servers[]     = $id_server;

                        if (! empty($all_metrics)){
                            foreach ($all_metrics as $type_metrics => $metrics) {

                                Debug::warning("*** ts_file:$date > id_mysql_server:$id_server > from:$type_metrics  ***");

                                if (is_array($metrics)) {
                                    $metrics = array_change_key_case($metrics);

                                    //Debug::debug($metrics,"metrics");

                                    foreach ($metrics as $variable => $value) {

                                        //Debug::debug($variable, 'variable');

                                        //cas spécial des thread de réplications (il peux y en avoir plusieurs)
                                        //où des HDD ? genre DF ?
                                        if (is_array($value)) {

                                            //Debug::debug($value, "TABLE SLAVE");
                                            $value = array_change_key_case($value);
                                            foreach ($value as $slave_variable => $slave_value) {

                                                // on définit un nom connexion par défaut
                                                if (!empty($value['connection_name'])) {
                                                    $connection_name = $value['connection_name'];
                                                } else {
                                                    $connection_name = "";
                                                }

                                                if (empty($variables[$type_metrics][$slave_variable])) {

                                                    if ($slave_value === "-1") {
                                                        continue;
                                                    }

                                                    // si les références n'existe pas on enregistre pas (ça évite les collisions et d'autres problèmes à gérer )
                                                    // et on est pas à un run prêt, le but est de rester performant et exhaustif

                                                    if (empty($var_index[$type_metrics][$slave_variable])) {
                                                        $var_index[$type_metrics][$slave_variable] = 1;
                                                        //$variables_to_insert[]                     = '("'.$slave_variable.'", '.$this->getTypeOfData($slave_value).', "'.$type_metrics.'", "slave")';
                                                        //  '('.$id_ts_file.',"'.$variable.'", '.$this->getTypeOfData($value).', "'.$type_metrics.'", "general")';

                                                        
                                                        $variables_to_insert[] = '(' . $id_ts_file . ',"' . $slave_variable . '", "' . $this->getTypeOfData($slave_value) . '", "' . $type_metrics . '", "slave")';
                                                        self::$id_mysql_server__to_refresh[$id_ts_file][] = $id_server;
                                                        //exit;
                                                    }

                                                    if ($slave_value === "") {
                                                        continue;
                                                    }
                                                } else {
                                                    if ($variables[$type_metrics][$slave_variable]['type'] == "TEXT") {

                                                        if (is_null($slave_value)) {
                                                            $slave_value = '';
                                                        }

                                                        $slave_value = $db->sql_real_escape_string($slave_value);

                                                        $slave[$variables[$type_metrics][$slave_variable]['type']][] = '(' . $id_server . ','
                                                            . '"' . $connection_name . '", '
                                                            . $variables[$type_metrics][$slave_variable]['id'] . ', "'
                                                            . $date . '", "'
                                                            . $slave_value . '")';
                                                    } else {

                                                        if ($slave_value == "") {
                                                            $slave_value = 'NULL';
                                                        }

                                                        if ($variables[$type_metrics][$slave_variable]['type'] == "DOUBLE") {

                                                            if ($slave_value === "") {
                                                                $slave_value = 0;
                                                            }

                                                            $slave[$variables[$type_metrics][$slave_variable]['type']][] = '(' . $id_server . ','
                                                                . '"' . $connection_name . '", '
                                                                . $variables[$type_metrics][$slave_variable]['id'] . ', "'
                                                                . $date . '", "'
                                                                . $slave_value . '")';
                                                        } else {

                                                            $slave[$variables[$type_metrics][$slave_variable]['type']][] = '(' . $id_server . ','
                                                                . '"' . $connection_name . '", '
                                                                . $variables[$type_metrics][$slave_variable]['id'] . ', "'
                                                                . $date . '", '
                                                                . $slave_value . ')';
                                                        }
                                                    }
                                                }
                                            } // END SLAVE
                                            //Debug::debug($slave);
                                        } else { // partie pour les données général


                                            //Debug::debug($variables[$type_metrics][$variable], "variable $type_metrics][$variable");

                                            if (!empty($variables[$type_metrics][$variable])) {


                                                //Debug::debug($memory_file,"MEMORY_FILE");
                                                //Debug::debug($value,"MEMORY_FILE");
                                                
                                                
                                                if ($memory_file === self::VARIABLES) {
                                                    $mysql_variable[$id_server][strtolower($variable)] = $value;
                                                }

                                                if ($variables[$type_metrics][$variable]['type'] === 'INT') {
                                                    if ($value === "") {
                                                        $value = "0";
                                                    } elseif ($value < 0) {
                                                        continue;
                                                    }
                                                }elseif (in_array($variables[$type_metrics][$variable]['type'], array("TEXT", "JSON"))) {
                                                    $value = $db->sql_real_escape_string($value);
                                                }
                                                elseif($variables[$type_metrics][$variable]['type'] == "DOUBLE") {
                                                    if ($value === ""){ //fix for slave_heartbeat_period with Percona 5.6
                                                        $value = 0;
                                                    }
                                                }
                                                $insert[$variables[$type_metrics][$variable]['type']][] = '(' . $id_server . ','
                                                    . $variables[$type_metrics][$variable]['id'] . ', "'
                                                    . $date . '", "'
                                                    . $value . '")';

                                                    //Debug::debug($insert, "INSERTTTTTTTTTTTTTTTTTTT");
                                            } else {
                                                //if empty we connot detemine type
                                                if ($value === "-1" || $value === "") {
                                                    continue;
                                                }

                                                // si les références n'existe pas on enregistre pas (ça évite les collisions et d'autres problèmes à gérer )
                                                // et on est pas à un run prêt, le but est de rester performant et exaustif

                                                Debug::debug($this->getTypeOfData($value), "TYPE : $variable");

                                                if (empty($var_index[$type_metrics][$variable])) {
                                                    //Debug::debug($insert, "val to insert in ts_variable");
                                                    $var_index[$type_metrics][$variable] = 1;
                                                    $variables_to_insert[]               = '(' . $id_ts_file . ',"' . $variable . '", "' . $this->getTypeOfData($value) . '", "' . $type_metrics . '", "general")';
                                                    self::$id_mysql_server__to_refresh[$id_ts_file][] = $id_server;
                                                }
                                            }
                                        }
                                    } //end variable
                                }
                            }
                        }
                    }
                } // date
            }

            Debug::checkPoint("before insert file : " . $file);

            if (file_exists($file)) {
                unlink($file);
            } else {
                $this->logger->emergency('Two process in same time for integrate data, please remove one');
                throw new \Exception("PMACTRL-647 : deux integrateur lancer en même temps (suprimer le pas bon)");
            }

            if ($file_parsed >= self::MAX_FILE_AT_ONCE) {
                break;
            }
        }

        if (count($files) === 0)
        {
            usleep(300000); // 0.3 sec
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
                $this->insert_slave_value($slave);
            }
        }

        if (!empty($history)) {
            $this->linkServerVariable($history, $memory_file);
        }

        Debug::debugQueriesOff();

        // end files
        //Debug::checkPoint("end method ");
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

        $sql = "INSERT IGNORE INTO ts_variable (`id_ts_file`, `name`,`type`,`from`,`radical`) VALUES " . implode(",", $variables_to_insert) . ";";
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
            $sql = "INSERT INTO `ts_value_general_" . strtolower($type) . "` (`id_mysql_server`,`id_ts_variable`,`date`, `value`) VALUES " . implode(",", $elems) . ";";
            //Debug::debug(count($elems), "type : $type");
            $db->sql_query($sql);

            Debug::checkPoint("saved " . $type . " elems : " . count($elems));
        }
    }


    private function insert_slave_value($values, $val = "slave")
    {
        //Debug::debug($values);

        if (count($values) == 0) {
            return 1;
        }

        $db = Sgbd::sql(DB_DEFAULT);

        Debug::checkPoint("start save");

        foreach ($values as $type => $elems) {

            $sql = "INSERT INTO `ts_value_" . $val . "_" . strtolower($type) . "` (`id_mysql_server`,`connection_name` ,`id_ts_variable`,`date`, `value`) VALUES " . implode(",\n", $elems) . ";";

            //Debug::sql($sql);
            $gg = $db->sql_query($sql);
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

        $this->logger->info('[Start] IntegrateAll '.date('Y-m-d H:i:s'));
        
        $this->evaluate($param);
        
        $this->logger->info('[END] IntegrateAll '.date('Y-m-d H:i:s'));
    }



    // deport to listener

    public function feedMysqlVariable($data)
    {

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
            Debug::debug($insert);
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
            Debug::debug($delete);
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
            Debug::debug($update);
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
}