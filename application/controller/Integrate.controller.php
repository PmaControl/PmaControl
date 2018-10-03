<?php

use \Glial\Synapse\Controller;
use \Glial\Cli\SetTimeLimit;
use Fuz\Component\SharedMemory\Storage\StorageFile;
use Fuz\Component\SharedMemory\SharedMemory;
use \App\Library\Debug;

//require ROOT."/application/library/Filter.php";

class Integrate extends Controller
{

    use \App\Library\Filter;
    const MAX_FILE_AT_ONCE = 20;

    var $shared;
    var $memory_file = "answer";
    var $files       = array();

    public function evaluate($param)
    {
        Debug::parseDebug($param);



        Debug::debug($param);


        if (empty($param[1])) {
            $memory_file = "answer";
        }

        $memory_file = $param[1];


        // test if valid memory file (from ts_file)
        $this->getIdMemoryFile($memory_file);





        $db         = $this->di['db']->sql(DB_DEFAULT);
        $this->view = false;

        $files = glob("/dev/shm/".$memory_file."_*");
        sleep(1);

        $variables           = $this->get_variable();
        $variables_to_insert = array();

        $insert      = array();
        $var_index   = array();
        $file_parsed = 0;

        $id_servers = array();

        foreach ($files as $file) {
            $file_parsed++;

            Debug::debug($file, " [FILE] ");

            $storage = new StorageFile($file); // to export in config ?
            $data    = new SharedMemory($storage);

            $elems = $data->getData();

            foreach ($elems as $elem) {
                foreach ($elem as $date => $server) {

                    foreach ($server as $id_server => $all_metrics) {

                        $history[$date][] = $id_server;
                        $id_servers[]     = $id_server;

                        foreach ($all_metrics as $type_metrics => $metrics) {

                            if (is_array($metrics)) {
                                $metrics = array_change_key_case($metrics);

                                foreach ($metrics as $variable => $value) {

                                    //cas spécial des thread de réplications (il peux y en avoir plusieurs)
                                    //où des HDD ? genre DF ?
                                    if (is_array($value)) {

                                        $value = array_change_key_case($value);



                                        foreach ($value as $slave_variable => $slave_value) {


                                            // on définit un nom connexion par défaut
                                            if (!empty($value['connection_name'])) {
                                                $connection_name = $value['connection_name'];
                                            } else {
                                                $connection_name = "";
                                            }

                                            //correction bidon, mais nécessaire
                                            if ($slave_variable === "seconds_behind_master" && empty($slave_value)) {
                                                $slave_value = "0";
                                            }

                                            if (empty($variables[$type_metrics][$slave_variable])) {


                                                if ($slave_value === "-1") {
                                                    continue;
                                                }


                                                // si les références n'existe pas on enregistre pas (ça évite les collisions et d'autres problèmes à gérer )
                                                // et on est pas à un run prêt, le but est de rester performant et exhaustif

                                                if (empty($var_index[$type_metrics][$slave_variable])) {
                                                    $var_index[$type_metrics][$slave_variable] = 1;
                                                    $variables_to_insert[]                     = '("'.$slave_variable.'", '.$this->getTypeOfData($slave_value).', "'.$type_metrics.'", "slave")';


                                                    debug($value);
                                                    debug($variables_to_insert);
                                                    //exit;
                                                }


                                                if ($slave_value === "") {
                                                    continue;
                                                }
                                            } else {


                                                if ($variables[$type_metrics][$slave_variable]['type'] == "TEXT") {
                                                    $slave_value = $db->sql_real_escape_string($slave_value);
                                                }

                                                $slave[$variables[$type_metrics][$slave_variable]['type']][] = '('.$id_server.','
                                                    .'"'.$connection_name.'", '
                                                    .$variables[$type_metrics][$slave_variable]['id'].', "'
                                                    .$date.'", "'
                                                    .$slave_value.'")';
                                            }
                                        } // END SLAVE
                                        //Debug::debug($slave);
                                    } else { // partie pour les données général
                                        if (!empty($variables[$type_metrics][$variable])) {

                                            if ($variables[$type_metrics][$variable]['type'] === 'INT') {
                                                if ($value === "") {
                                                    $value = "0";
                                                } elseif ($value < 0) {
                                                    continue;
                                                }
                                            }

                                            if ($variables[$type_metrics][$variable]['type'] == "TEXT") {
                                                $value = $db->sql_real_escape_string($value);
                                            }

                                            /*
                                              if ($this->convert($this->getTypeOfData($value)) != $variables[$type_metrics][$variable]['type']
                                              && $this->getTypeOfData($value) > $this->convert($variables[$type_metrics][$variable]['type'], true)) {

                                              if (in_array($variable, array("max_binlog_cache_size", "max_seeks_for_key", "max_write_lock_count")) || $this->getTypeOfData($value) != 3)
                                              {
                                              continue;
                                              }

                                              Debug::debug(PHP_INT_MAX, 'PHP_INT_MAX');
                                              Debug::debug($this->getTypeOfData($value), '$this->getTypeOfData($value)');
                                              Debug::debug($variables[$type_metrics][$variable]['type'], '$variables[$type_metrics][$variable]["type"]');
                                              Debug::debug($variable,'$variable' );
                                              Debug::debug($value, '$value');

                                              exit;
                                              } */


                                            $insert[$variables[$type_metrics][$variable]['type']][] = '('.$id_server.','
                                                .$variables[$type_metrics][$variable]['id'].', "'
                                                .$date.'", "'
                                                .$value.'")';
                                        } else {
                                            if ($value === "-1" || $value === "") {
                                                continue;
                                            }

                                            // si les références n'existe pas on enregistre pas (ça évite les collisions et d'autres problèmes à gérer )
                                            // et on est pas à un run prêt, le but est de rester performant et exaustif

                                            if (empty($var_index[$type_metrics][$variable])) {
                                                $var_index[$type_metrics][$variable] = 1;
                                                $variables_to_insert[]               = '("'.$variable.'", '.$this->getTypeOfData($value).', "'.$type_metrics.'", "general")';
                                                //$variables_to_insert[$type_metrics][$variable]['type'] = $this->getTypeOfData($value);
                                            }
                                        }
                                    }
                                }


                                // insert ?
                            }
                        }
                    }
                } // date
                //debug($ts_max_date);
                //Debug::debug($insert);
            }

            Debug::checkPoint("before insert file : ".$file);

            unlink($file);

            if ($file_parsed >= self::MAX_FILE_AT_ONCE) {
                break;
            }
        }



        if (count($variables_to_insert) > 0) {

            Debug::checkPoint("variables");
            $this->insert_variable($variables_to_insert);
        } else {
            Debug::checkPoint("values");
            $this->insert_value($insert);

            if (!empty($slave)) {
                $this->insert_slave_value($slave);
            }

            $this->putServerAvailable($id_servers);
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

        $db = $this->di['db']->sql(DB_DEFAULT);


        $sql = "SELECT * FROM `ts_variable`";

        $res = $db->sql_query($sql);

        $variables = array();
        while ($ob        = $db->sql_fetch_object($res)) {
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
        return ((int) $value != $value);  // problem avec PHP_INT_MAX
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
                throw new Exception("PMACTRL-497 : Negative value not allowed (".$value.")");
            }
        }

        return $val;
    }

    private function insert_variable($variables_to_insert)
    {
        //Debug::debug($variables_to_insert, "dfgfgdg");
        //Debug::debug($variables_to_insert);
        $db = $this->di['db']->sql(DB_DEFAULT);

        // insert IGNORE in case of first save have 2 slave
        $sql = "INSERT IGNORE INTO ts_variable (`name`,`type`,`from`,`radical`) VALUES ".implode(",", $variables_to_insert).";";
        $db->sql_query($sql);
    }

    private function insert_value($values)
    {
        //Debug::debug($values);

        if (count($values) == 0) {
            return 1;
        }

        $db = $this->di['db']->sql(DB_DEFAULT);

        Debug::checkPoint("start save");

        foreach ($values as $type => $elems) {
            $sql = "INSERT INTO `ts_value_general_".strtolower($type)."` (`id_mysql_server`,`id_ts_variable`,`date`, `value`) VALUES ".implode(",", $elems).";";
            $db->sql_query($sql);

            Debug::checkPoint("saved ".$type." elems : ".count($elems));
        }
    }

    public function reset()
    {
        $this->view = false;
        $db         = $this->di['db']->sql(DB_DEFAULT);

        foreach (array('int', 'double', 'text') as $type) {
            $db->sql_query("TRUNCATE TABLE `ts_value_general_".strtolower($type)."`;");
        }
    }

    private function insert_slave_value($values, $val = "slave")
    {
        //Debug::debug($values);

        if (count($values) == 0) {
            return 1;
        }

        $db = $this->di['db']->sql(DB_DEFAULT);

        Debug::checkPoint("start save");

        foreach ($values as $type => $elems) {

            if (Debug::$debug) {

                foreach ($elems as $elem) {
                    $sql = "INSERT INTO `ts_value_".$val."_".strtolower($type)."` (`id_mysql_server`,`connection_name` ,`id_ts_variable`,`date`, `value`) VALUES ".$elem.";";
                    $gg  = $db->sql_query($sql);
                }
            } else {

                $sql = "INSERT INTO `ts_value_".$val."_".strtolower($type)."` (`id_mysql_server`,`connection_name` ,`id_ts_variable`,`date`, `value`) VALUES ".implode(",", $elems).";";
                $gg  = $db->sql_query($sql);
            }
            if (!$gg) {
                debug($db->sql_error());
            }

            Debug::checkPoint("saved ".$type." elems : ".count($elems));
        }
    }

    private function putServerAvailable($id_servers)
    {
        $db = $this->di['db']->sql(DB_DEFAULT);


        $ids = implode(",", array_unique($id_servers));

        if (!empty($ids)) {
            //il faut ajouter le non primary pour les neud galera qui prenne pas de query
            $sql = "UPDATE mysql_server SET is_available = 1, error = '',is_acknowledged=0  WHERE id in (".$ids.")";
            $db->sql_query($sql);
        }
    }

    private function linkServerVariable($history, $memory_file)
    {
        $db = $this->di['db']->sql(DB_DEFAULT);



        $id_file_name = $this->getIdMemoryFile($memory_file);



        $sql3 = array();
        foreach ($history as $date => $is_servers) {

            //Debug::debug($is_servers);


            $sql = "UPDATE `ts_max_date`  SET `date_p4`=`date_p3`,`date_p3`=`date_p2`,`date_p2`=`date_p1`,`date_p1`=`date`,`date`= '".$date."' WHERE `id_mysql_server` IN (".implode(",", $is_servers).") AND `id_ts_file`=".$id_file_name.";";

            Debug::sql($sql);
            $db->sql_query($sql);




            foreach ($is_servers as $id_server) {
                $sql3[] = "(".$id_server.", ".$id_file_name.", '".$date."')";
            }
        }

        $sql2 = "INSERT INTO `ts_date_by_server` (`id_mysql_server`,`id_ts_file`, `date`) VALUES ";
        $sql4 = $sql2.implode(",", $sql3).";";

        Debug::sql($sql4);


        $db->sql_query($sql4);
    }

    private function convert($id, $revert = false)
    {
        $gg[1] = "INT";
        $gg[2] = "DOUBLE";
        $gg[3] = "TEXT";

        if ($revert === true) {
            $gg = array_flip($gg);
        }

        return $gg[$id];
    }

    private function getIdMemoryFile($memory_file)
    {

        $db = $this->di['db']->sql(DB_DEFAULT);



        //on met en cache
        if (empty($this->files)) {

            $sql = "SELECT * FROM ts_file";
            $res = $db->sql_query($sql);

            while ($ob = $db->sql_fetch_object($res)) {
                $this->files[$ob->file_name] = $ob->id;
            }
        }

        Debug::debug($this->files);


        if (empty($this->files[$memory_file])) {
            throw new Exception('PMACTRL-098 : Impossible to find this file name : "'.$memory_file.'"');
        }
        $id_file_name = $this->files[$memory_file];



        return $id_file_name;
    }
}