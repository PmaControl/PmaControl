<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Cli\Table;
use \Glial\Security\Crypt\Crypt;
use \Glial\Sgbd\Sgbd;


/**
 * Class responsible for load workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Load extends Controller {

/**
 * Stores `$repertoire_source` for repertoire source.
 *
 * @var string
 * @phpstan-var string
 * @psalm-var string
 */
    var $repertoire_source = '/data/backup/ozitem';
/**
 * Stores `$sql_server` for sql server.
 *
 * @var string
 * @phpstan-var string
 * @psalm-var string
 */
    var $sql_server = 'default';
/**
 * Stores `$debug` for debug.
 *
 * @var bool
 * @phpstan-var bool
 * @psalm-var bool
 */
    var $debug = false;

/**
 * Handle load state through `exec`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for exec.
 * @phpstan-return void
 * @psalm-return void
 * @see self::exec()
 * @example /fr/load/exec
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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

/**
 * Handle load state through `waitPosition`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param mixed $db Input value for `db`.
 * @phpstan-param mixed $db
 * @psalm-param mixed $db
 * @param mixed $file Input value for `file`.
 * @phpstan-param mixed $file
 * @psalm-param mixed $file
 * @param mixed $position Input value for `position`.
 * @phpstan-param mixed $position
 * @psalm-param mixed $position
 * @return mixed Returned value for waitPosition.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::waitPosition()
 * @example /fr/load/waitPosition
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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

/**
 * Handle load state through `install`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $db_order Input value for `db_order`.
 * @phpstan-param mixed $db_order
 * @psalm-param mixed $db_order
 * @return void Returned value for install.
 * @phpstan-return void
 * @psalm-return void
 * @see self::install()
 * @example /fr/load/install
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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

/**
 * Handle load state through `log`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param mixed $sql Input value for `sql`.
 * @phpstan-param mixed $sql
 * @psalm-param mixed $sql
 * @return void Returned value for log.
 * @phpstan-return void
 * @psalm-return void
 * @see self::log()
 * @example /fr/load/log
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function log($sql) {
        echo \SqlFormatter::highlight($sql);
    }

/**
 * Retrieve load state through `getLogAndPos`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param mixed $filename Input value for `filename`.
 * @phpstan-param mixed $filename
 * @psalm-param mixed $filename
 * @return mixed Returned value for getLogAndPos.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @see self::getLogAndPos()
 * @example /fr/load/getLogAndPos
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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

/**
 * Handle load state through `cmd`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param mixed $cmd Input value for `cmd`.
 * @phpstan-param mixed $cmd
 * @psalm-param mixed $cmd
 * @return mixed Returned value for cmd.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @see self::cmd()
 * @example /fr/load/cmd
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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

