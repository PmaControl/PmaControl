<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Cli\Table;
use \Glial\Security\Crypt\Crypt;
use \Glial\Sgbd\Sgbd;


class Load2 extends Controller {

    var $repertoire_source = '/data/backup/ozitem';
    var $sql_server = 'default';
    var $debug = false;

    public function exec($param = array()) {
        $this->view = false;

        if (!empty($param)) {
            foreach ($param as $elem) {

                if ('--debug' === $elem) {
                    $this->debug = true;
                    break;
                }
            }
        }

        echo "Mode debug : ";
        echo ($this->debug === true) ? "ON" : "OFF";
        echo "\n";


        $fichiers_dump = glob($this->repertoire_source . "/*.sql*");
        $all_dbs2 = [];

        $i = 0;

        foreach ($fichiers_dump as $dump) {
            $i++;
            echo "file $dump\n";

            if (pathinfo($dump)['extension'] === "gz") {
                shell_exec("gunzip " . $dump);

                $dump = str_replace('.gz', '', $dump);
            }


            $ret = $this->getLogAndPos($dump);

            $tmp = array();
            $tmp['file'] = $ret['file'];
            $tmp['position'] = $ret['position'];
            $tmp['db'] = pathinfo($dump)['filename'];
            $tmp['dump'] = $dump;


            $all_dbs2[$tmp['file']][$tmp['position']] = $tmp;
        }

        ksort($all_dbs2);

        $all_dbs = [];
        foreach ($all_dbs2 as $file) {
            ksort($file);
            foreach ($file as $position) {
                $all_dbs[] = $position;
            }
        }


        $position = 0;
        $file = "";
        $i = 0;
        $j = 0;
        $old = [];
        foreach ($all_dbs as $db) {
            if ($file !== $db['file'] && $db['position'] < $position && $j !== 0) {

                //echo 'ERROR !!!!!!!!!!!'."\n";
                //echo "ecart ".$db['file'] ." == ".$file;
                //echo "ecart ".$db['position']-$position;
                //debug($db);

                debug($old);
                debug($db);
                $i++;
            }

            echo $db['file'] . "\t" . $db['position'] . "\t" . ($db['position'] - $position) . "\n";

            $position = $db['position'];
            $file = $db['file'];
            $old = $db;

            $j++;
        }
        echo "VAR : " . $i . "\n";
        $this->install($all_dbs);
    }

    public function waitPosition($db, $file, $position) {
        $db = Sgbd::sql($this->sql_server);


        $i = 0;
        do {
            if ($this->debug === true) {
                return true;
            }

            $i++;

            usleep(1000);

            $thread_slave = $db->isSlave();

            //debug($thread_slave);

            foreach ($thread_slave as $thread) {
                $Relay_Master_Log_File = $thread['Relay_Master_Log_File'];
                $Exec_Master_Log_Pos = $thread['Exec_Master_Log_Pos'];
            }
            $sql = "SHOW SLAVE STATUS;";
            $this->log($sql);


            if (!empty($thread['Last_Errno'])) {


                $sql = "SET GLOBAL SQL_SLAVE_SKIP_COUNTER=1;";
                if ($this->debug === false) {
                    $db->sql_query($sql);
                }
                $this->log($sql);


                $sql = "START SLAVE UNTIL MASTER_LOG_FILE='" . $file . "', MASTER_LOG_POS=" . $position . ";";
                if ($this->debug === false) {
                    $db->sql_query($sql);
                }
                $this->log($sql);


                echo "ERROR launch for restart : $sql\n";

                //throw new \Exception('PMACLI-037 Error : Impossible to load data !');
            }


            $tab = new Table(1);
            $tab->addHeader(array("Relay_Master_Log_File", "Exec_Master_Log_Pos"));
            $tab->addLine(array($Relay_Master_Log_File, $Exec_Master_Log_Pos));
            echo $tab->display();

            if ($i > 10) {
                sleep(1);
            }
        } while ($file != $Relay_Master_Log_File || $position != $Exec_Master_Log_Pos);
    }

    public function install($db_order) {

        $db = Sgbd::sql($this->sql_server);

        $i = 0;
        foreach ($db_order as $timestamp => $arr) {
            /*
              if ($arr['db'] == "PRODUCTION") {
              continue;
              } */
            $sql = "STOP SLAVE;";

            if ($this->debug === false) {
                $db->sql_query($sql);
            }

            $this->log($sql);
            if ($i !== 0) {
                $sql = "START SLAVE UNTIL MASTER_LOG_FILE='" . $arr['file'] . "', MASTER_LOG_POS=" . $arr['position'] . ";";
                if ($this->debug === false) {
                    $db->sql_query($sql);
                }
                $this->log($sql);
                // wait file and position
                $this->waitPosition($db, $arr['file'], $arr['position']);
            }
            if ($arr['db'] != "mysql") {
                $sql = "DROP DATABASE IF EXISTS `" . $arr['db'] . "`;";
                if ($this->debug === false) {
                    $db->sql_query($sql);
                }
                $this->log($sql);
                $sql = "CREATE DATABASE `" . $arr['db'] . "`;";
                if ($this->debug === false) {
                    $db->sql_query($sql);
                }
                $this->log($sql);
            } else {
                continue;
            }
            $db->sql_close(); // to prevent, MySQL has gone away !
            $db->_param['port'] = empty($db->_param['port']) ? 3306 : $db->_param['port'];


            Crypt::$key = CRYPT_KEY;


            if (!empty($db->_param['crypted']) && $db->_param['crypted'] == 1) {
                $passwd = Crypt::uncrypt($db->_param['password']);
            } else {
                $passwd = $db->_param['password'];
            }



            $cmd = "pv " . $arr['dump'] . " | mysql -h " . $db->_param['hostname'] . " -u " . $db->_param['user'] . " -P " . $db->_param['port'] . " -p" . $passwd . " " . $arr['db'] . "";
            //echo $cmd."\n";
            $this->cmd($cmd);
            $db = Sgbd::sql($this->sql_server);
            if ($i === 0) {
                $sql = "CHANGE MASTER TO MASTER_LOG_FILE='" . $arr['file'] . "', MASTER_LOG_POS=" . $arr['position'] . ";";
                if ($this->debug === false) {
                    $db->sql_query($sql);
                }
                $this->log($sql);
            }
            $replicate_do_db[] = $arr['db'];
            $sql = "SET GLOBAL replicate_do_db = '" . implode(",", $replicate_do_db) . "';";
            if ($this->debug === false) {
                $db->sql_query($sql);
            }
            $this->log($sql);
            $i++;
        }
        $sql = "START SLAVE";
        if ($this->debug === false) {
            $db->sql_query($sql);
        }
        $this->log($sql);
    }

    public function log($sql) {
        echo \SqlFormatter::highlight($sql);
    }

    private function getLogAndPos($filename) {
        $handle = fopen($filename, "r");
        if ($handle) {
            $i = 0;
            while (($buffer = fgets($handle, 4096)) !== false) {
                if (strpos($buffer, "CHANGE MASTER") !== false) {
                    $ret = [];
                    $ret['file'] = explode("'", explode("MASTER_LOG_FILE='", $buffer)[1])[0];
                    $ret['position'] = substr(trim(explode("=", $buffer)[2]), 0, -1);
                    return $ret;
                }
                $i++;
                if ($i > 30) {
                    throw new \Exception('PMACTRL-006 Impossible to find \'CHANGE MASTER\' in header of \'' . $filename . '\'');
                }
            }
            if (!feof($handle)) {
                echo "Erreur: fgets() a échoué\n";
            }
            fclose($handle);
        }
    }

    public function cmd($cmd) {
        $code_retour = 0;

        if ($this->debug === false) {
            passthru($cmd, $code_retour);
        } else {
            echo $cmd . "\n";
        }

        if ($code_retour !== 0) {
            throw new \Exception('the following command failed : "' . $cmd . '"');
        } else {
            return true;
        }
    }

}
