<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use Fuz\Component\SharedMemory\Storage\StorageFile;
use Fuz\Component\SharedMemory\SharedMemory;
use \App\Library\Debug;
use \App\Controller\Aspirateur;
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

    /* not used ? have to remove */
    const VARIABLES = "variable";
    const ANSWER = "answer"; //merge of SHOW GLOBAL STATUS / SHOW MASTER STATUS / SHOW SLAVE HOSTS / SHOW SLAVE STATUS
    const SYSTEM = "ssh_stats";


    var $shared;
    var $memory_file = "answer";
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

    public function evaluate($param)
    {
        Debug::parseDebug($param);
        Debug::debug($param);

        if (empty($param[0])) {
            $memory_file       = "answer";
            $this->memory_file = "answer";
        } else {
            $memory_file       = $param[0];
            $this->memory_file = $param[0];
        }

        // test if valid memory file (from ts_file)
        $id_ts_file = $this->getIdMemoryFile($memory_file);


        Debug::debug($id_ts_file, "id_ts_file");

        $db         = Sgbd::sql(DB_DEFAULT);
        $this->view = false;

        $files = glob(TMP . "tmp_file/" . $memory_file . "_*");


        if (empty($files)) {
            return true;
        } else if (count($files) > 20) {
            usleep(500);
        } else {
            sleep(1);
        }

        array_multisort(array_map('filemtime', $files), SORT_NUMERIC, SORT_ASC, $files);

        Debug::debug($files);

        $variables           = $this->get_variable();
        $variables_to_insert = array();

        $insert      = array();
        $var_index   = array();
        $file_parsed = 0;

        $id_servers = array();
        $history    = array();

        if ($memory_file === self::VARIABLES) {
            $mysql_variable = array();
        }

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

                        if (! empty($all_metrics))
                        {

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

                                                    $variables_to_insert[] = '(' . $id_ts_file . ',"' . $slave_variable . '", "' . $this->getTypeOfData($value) . '", "' . $type_metrics . '", "slave")';

                                                    Debug::debug($value);
                                                    Debug::debug($variables_to_insert);

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
                                            }

                                            if ($variables[$type_metrics][$variable]['type'] == "TEXT") {
                                                $value = $db->sql_real_escape_string($value);
                                            }

                                            $insert[$variables[$type_metrics][$variable]['type']][] = '(' . $id_server . ','
                                                . $variables[$type_metrics][$variable]['id'] . ', "'
                                                . $date . '", "'
                                                . $value . '")';
                                        } else {
                                            if ($value === "-1" || $value === "") {
                                                continue;
                                            }

                                            // si les références n'existe pas on enregistre pas (ça évite les collisions et d'autres problèmes à gérer )
                                            // et on est pas à un run prêt, le but est de rester performant et exaustif

                                            if (empty($var_index[$type_metrics][$variable])) {
                                                Debug::debug($insert, "val to insert in ts_variable");

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

            switch ($this->memory_file) {
                case self::ANSWER:
                    //$this->putServerMySQLAvailable($id_servers);
                    break;

                case self::SYSTEM:
                    //$this->putServerSshAvailable($id_servers);
                    break;

                case self::VARIABLES:
                    $this->feedMysqlVariable($mysql_variable);
                    break;
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

        return self::convert($val);
    }

    private function insert_variable($variables_to_insert)
    {
        //Debug::debug($variables_to_insert, "dfgfgdg");
        //Debug::debug($variables_to_insert);
        $db = Sgbd::sql(DB_DEFAULT);



        // insert IGNORE in case of first save have 2 slave
        $this->logger->warning("Insert new value :".json_encode($variables_to_insert));
        $sql = "INSERT IGNORE INTO ts_variable (`id_ts_file`, `name`,`type`,`from`,`radical`) VALUES " . implode(",", $variables_to_insert) . ";";
        $res = $db->sql_query($sql);

        if (! empty(self::$id_mysql_server__to_refresh[4]))
        Aspirateur::cleanMd5(self::$id_mysql_server__to_refresh[4]);


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
            $db->sql_query($sql);

            Debug::checkPoint("saved " . $type . " elems : " . count($elems));
        }
    }


    /*
    public function reset()
    {
        $this->view = false;
        $db         = Sgbd::sql(DB_DEFAULT);

        foreach (array('int', 'double', 'text') as $type) {
            $db->sql_query("TRUNCATE TABLE `ts_value_general_" . strtolower($type) . "`;");
        }
    }*/

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

            Debug::sql($sql);
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

        $id_file_name = $this->getIdMemoryFile($memory_file);

        $sql3 = array();
        foreach ($history as $date => $is_servers) {
            //Debug::debug($is_servers);

            $sql = "UPDATE `ts_max_date`  SET `date_p4`=`date_p3`,`date_p3`=`date_p2`,`date_p2`=`date_p1`,`date_p1`=`date`,`date`= '" . $date . "'
                WHERE `id_mysql_server` IN (" . implode(",", $is_servers) . ") AND `id_ts_file`=" . $id_file_name . ";";

            Debug::sql($sql);
            $db->sql_query($sql);

            foreach ($is_servers as $id_server) {
                $sql3[] = "(" . $id_server . ", " . $id_file_name . ", '" . $date . "')";
            }
        }

        $sql2 = "INSERT INTO `ts_date_by_server` (`id_mysql_server`,`id_ts_file`, `date`) VALUES ";
        $sql4 = $sql2 . implode(",\n", $sql3) . ";";

        Debug::sql($sql4);

        $db->sql_query($sql4);
    }

    private static function convert($id, $revert = false)
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
        $db = Sgbd::sql(DB_DEFAULT);

        //on met en cache
        if (empty($this->files)) {

            $sql = "SELECT * FROM ts_file";
            $res = $db->sql_query($sql);

            while ($ob = $db->sql_fetch_object($res)) {
                $this->files[$ob->file_name] = $ob->id;
            }
            Debug::debug($this->files);
        }

        if (empty($this->files[$memory_file])) {
            $this->logger->error('Impossible to find this file name : "'.$memory_file.'"');
            //throw new \Exception('PMACTRL-098 : Impossible to find this file name : "'.$memory_file.'"');
        }
        $id_file_name = $this->files[$memory_file];

        return $id_file_name;
    }

    public function integrateAll($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM `ts_file` order by id desc;";
        $res = $db->sql_query($sql);
        Debug::sql($sql);

        $this->logger->notice('[Start] IntegrateAll '.date('Y-m-d H:i:s'));
        while ($ob = $db->sql_fetch_object($res)) {
            $this->evaluate(array($ob->file_name));
        }
        $this->logger->notice('[END] IntegrateAll '.date('Y-m-d H:i:s'));
    }



    // deport to listener

    public function feedMysqlVariable($data)
    {
        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM `global_variable` WHERE `id_mysql_server` IN (" . implode(',', array_keys($data)) . ");";
        Debug::sql($sql);
        $res = $db->sql_query($sql);

        $in_base = array();
        while ($ob = $db->sql_fetch_object($res)) {
            $in_base[$ob->id_mysql_server][$ob->variable_name] = $ob->value;
        }
        $in_base[1]['jexistedansmesreve'] = "dream!";

        foreach ($data as $id_mysql_server => $err) {

            if (empty($in_base[$id_mysql_server])) {
                $insert[$id_mysql_server] = $data[$id_mysql_server];
                continue;
            }

            //INSERT
            $insert[$id_mysql_server] = array_diff_key($data[$id_mysql_server], $in_base[$id_mysql_server]);

            //DELETE
            $delete[$id_mysql_server] = array_diff_key($in_base[$id_mysql_server], $data[$id_mysql_server]);

            //UPDATE
            $val_a[$id_mysql_server]  = array_diff($data[$id_mysql_server], $in_base[$id_mysql_server]);
            $val_b[$id_mysql_server]  = array_diff($in_base[$id_mysql_server], $data[$id_mysql_server]);
            $update[$id_mysql_server] = array_intersect_key($val_a[$id_mysql_server], $val_b[$id_mysql_server]);
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
            }
            if (!empty($elem_upt)) {
                $sql = "INSERT INTO global_variable (`id_mysql_server`,`variable_name`,`value`) VALUES " . implode(",", $elem_upt) . " ON DUPLICATE KEY UPDATE `value`=VALUES(`value`);";
                Debug::sql($sql);
                $db->sql_query($sql);
            }
        }
    }
}
