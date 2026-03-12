<?php

namespace App\Controller;

use \App\Library\Debug;

use \Glial\Synapse\Controller;
use \Glial\Security\Crypt\Crypt;
use \Glial\Cli\Color;
use \Glial\Sgbd\Sgbd;

use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;

/**
 * Class responsible for benchmark workflows.
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
class Benchmark extends Controller
{
    const COLOR_GREEN               = "75,215,134";
    const COLOR_BLUE                = "142,199,255";
    const COLOR_YELLOW              = "252,215,95";
    const COLOR_RED                 = "255,168,168";
    const COLOR_GREY                = "130,130,130";
    const SHADOW                    = "0.2";
    const DIRECTORY_LUA_SYSBENCH_05 = "/usr/local/sysbench/tests/db/";
    const DIRECTORY_LUA_SYSBENCH_1  = "/usr/share/sysbench/";

/**
 * Stores `$debug` for debug.
 *
 * @var bool
 * @phpstan-var bool
 * @psalm-var bool
 */
    var $debug = false;
/**
 * Stores `$count` for count.
 *
 * @var int
 * @phpstan-var int
 * @psalm-var int
 */
    var $count = 1;

/**
 * Stores `$logger` for logger.
 *
 * @var mixed
 * @phpstan-var mixed
 * @psalm-var mixed
 */
    var $logger;
    /*
     * @brand-primary: darken(#428bca, 6.5%); // #337ab7
      @brand-success: #5cb85c;
      @brand-info:    #5bc0de;
      @brand-warning: #f0ad4e;
      @brand-danger:  #d9534f;
     */
    var $colors = array(self::COLOR_BLUE, self::COLOR_RED, self::COLOR_YELLOW, self::COLOR_GREEN, self::COLOR_GREY, "114,147,203", "225,151,76",
        "132,186,91", "211,94,96", "128,133,133", "144,103,167", "171,104,87", "204,194,16");

    //var $colors = array(self::COLOR_BLUE, self::COLOR_RED, self::COLOR_YELLOW, self::COLOR_GREEN, self::COLOR_GREY);


/**
 * Prepare benchmark state through `before`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for before.
 * @phpstan-return void
 * @psalm-return void
 * @see self::before()
 * @example /fr/benchmark/before
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function before($param)
    {
        
        $monolog       = new Logger("Benchmark");
        $handler      = new StreamHandler(LOG_FILE, Logger::NOTICE);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $monolog->pushHandler($handler);
        $this->logger = $monolog;
    }



/**
 * Handle benchmark state through `run`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for run.
 * @phpstan-return void
 * @psalm-return void
 * @see self::run()
 * @example /fr/benchmark/run
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function run($param)
    {
        $this->view = false;

        Debug::parseDebug($param);
        Debug::debug(Color::getColoredString("Debug enabled !", "yellow"));

        $id_benchmark_main = $param[0];

        Debug::debug("id_benchmark_main : $id_benchmark_main");

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM benchmark_main a
            INNER JOIN mysql_server b ON a.id_mysql_server = b.id
        WHERE a.id = ".$id_benchmark_main;
        $res = $db->sql_query($sql);

        Debug::sql($sql);

        $directory_lua = $this->getDirectoryLua($this->getSysbenchVersion());

        while ($ob = $db->sql_fetch_object($res)) {

            $sql2 = "UPDATE benchmark_main SET status = 'RUNNING',date_start = '".date('Y-m-d H:i:s')."' WHERE id ='".$id_benchmark_main."'";
            $db->sql_query($sql2);
            Debug::sql($sql2);

            $password = Crypt::decrypt($ob->passwd, CRYPT_KEY);
            $server   = Sgbd::sql($ob->name);

            $sql = "DROP DATABASE IF EXISTS sbtest;";
            $server->sql_query($sql);

            $sql = "CREATE DATABASE sbtest;";
            $server->sql_query($sql);




            $data['sysbench'] = $this->getSysbenchVersion();
            Debug::debug($data['sysbench'], "Version sysbench");

            

            if (version_compare($data['sysbench'], '0.5', "=")) {

                $prepare = 'sysbench --test='.$directory_lua.$ob->mode.' --mysql-host='.$ob->ip.' --mysql-port='.$ob->port;
                $prepare .= ' --mysql-user='.$ob->login.' --mysql-password='.$password.' '
                    .'--mysql-db=sbtest --mysql-table-engine=InnoDB '
                    .'--oltp-tables-count='.$ob->tables_count.' --max-time='.$ob->max_time.' prepare';
            } else if (version_compare($data['sysbench'], '1', ">=")) {



                $prepare = '
                sysbench \
--db-driver=mysql \
--mysql-user='.$ob->login.' \
--mysql_password='.$password.' \
--mysql-db=sbtest \
--mysql-host='.$ob->ip.' \
--mysql-port='.$ob->port.' \
--tables='.$ob->tables_count.' \
--table-size=10000 \
'.$directory_lua.$ob->mode.' prepare';
            }

            $this->logger->notice("prepare : ".str_replace("\\", "",$prepare));

            Debug::debug($prepare, "PREPARE");

            $input_lines = shell_exec($prepare);
            Debug::debug($input_lines);

            $sql  = "select @@max_connections as max;";
            $res2 = $server->sql_query($sql);

            while ($ob2 = $server->sql_fetch_object($res2)) {
                $max_connections = $ob2->max;
            }



            $threads = explode(',', $ob->threads);
            foreach ($threads as $thread) {

                if ($max_connections > $thread + 1) {

                    if (version_compare($data['sysbench'], '0.5', "=")) {

                        $cmd = 'sysbench '
                            .' --test='.$directory_lua.$ob->mode
                            .' --mysql-host='.$ob->ip
                            .' --mysql-port='.$ob->port
                            .' --mysql-user='.$ob->login
                            .' --mysql-password='.$password
                            .' --mysql-db=sbtest'
                            .' --mysql-table-engine=InnoDB'
                            .' --oltp-test-mode='.$ob->mode
                            .' --oltp-tables-count='.$ob->tables_count
                            .' --num-threads='.$thread
                            .' --max-time='.$ob->max_time.' run';
                    } else if (version_compare($data['sysbench'], '1', ">=")) {


                        $cmd = 'sysbench \
--db-driver=mysql \
--mysql-user='.$ob->login.' \
--mysql_password='.$password.' \
--mysql-db=sbtest \
--mysql-host='.$ob->ip.' \
--mysql-port='.$ob->port.' \
--tables='.$ob->tables_count.' \
--table-size=10000 \
--threads='.$thread.' \
--events=0 \
--time='.$ob->max_time.' \
'.$directory_lua.$ob->mode.' run';
                    } else {

                        Debug::debug($data['sysbench'], "Version of sysbench not supported");
                    }

                    $this->logger->notice("RUNNING : ".str_replace("\\", "",$cmd));


                    Debug::debug(Color::getColoredString($cmd, "yellow"));

                    $input_lines = shell_exec($cmd);
                    sleep(20);

                    Debug::debug(Color::getColoredString($input_lines, "blue"));

                    $sql = "INSERT INTO benchmark_run
                      SET `id_benchmark_main` = '".$id_benchmark_main."',
                      `date` = '".date("Y-m-d H:i:s")."',
                      `threads`  = '".$thread."',
                      `mode`  = 'mode',
                      `read` = '".$this->getQueriesPerformedRead($input_lines)."',
                      `write` = '".$this->getQueriesPerformedWrite($input_lines)."',
                      `other` = '".$this->getQueriesPerformedOther($input_lines)."',
                      `total` = '".$this->getQueriesPerformedTotal($input_lines)."',
                      `transaction` = '".$this->getTransactions($input_lines)."',
                      `error` = '".$this->getErrors($input_lines)."',
                      `time` = '".$this->getTotalTime($input_lines)."',
                      `reponse_min` = '".$this->getReponseTimeMin($input_lines)."',
                      `reponse_max` = '".$this->getReponseTimeMax($input_lines)."',
                      `reponse_avg` = '".$this->getReponseTimeAvg($input_lines)."',
                      `reponse_percentile95` = '".$this->getReponseTime95percent($input_lines)."',
                      `result`= '".$db->sql_real_escape_string($input_lines)."';";

                    Debug::sql($sql);

                    //better to get PANIC and FATAL error and send to log

                    $db->sql_query($sql);
                } else {
                    Debug::debug(Color::getColoredString("Thread canceled : ".$thread." (max_connections : ".$max_connections.")", "yellow"));
                }


                // $sql2 = "UPDATE benchmark_main SET status = 'COMPLETED',date_end = '".date('c')."' WHERE id ='".$id_benchmark_main."'";
                //$db->sql_query($sql2);
            }

            $sql2 = "UPDATE benchmark_main SET status = 'COMPLETED',date_end = '".date('Y-m-d H:i:s')."' WHERE id ='".$id_benchmark_main."'";
            $db->sql_query($sql2);
        }
    }

/**
 * Retrieve benchmark state through `getQueriesPerformedRead`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $input_lines Input value for `input_lines`.
 * @phpstan-param mixed $input_lines
 * @psalm-param mixed $input_lines
 * @return mixed Returned value for getQueriesPerformedRead.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getQueriesPerformedRead()
 * @example /fr/benchmark/getQueriesPerformedRead
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getQueriesPerformedRead($input_lines)
    {
        preg_match_all("/queries\sperformed:[\s]+read:[\s]+([\d]+)[\s]+/Ux", $input_lines, $output_array);

        if (isset($output_array[1][0])) {
            return $output_array[1][0];
        } else {
            return false;
        }
    }

/**
 * Retrieve benchmark state through `getQueriesPerformedWrite`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $input_lines Input value for `input_lines`.
 * @phpstan-param mixed $input_lines
 * @psalm-param mixed $input_lines
 * @return mixed Returned value for getQueriesPerformedWrite.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getQueriesPerformedWrite()
 * @example /fr/benchmark/getQueriesPerformedWrite
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getQueriesPerformedWrite($input_lines)
    {
        preg_match_all("/queries\sperformed:[\s]+read:[\s]+[\d]+[\s]+write:[\s]+([\d]+)[\s]+/Ux", $input_lines, $output_array);

        if (isset($output_array[1][0])) {
            return $output_array[1][0];
        } else {
            return false;
        }
    }

/**
 * Retrieve benchmark state through `getQueriesPerformedOther`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $input_lines Input value for `input_lines`.
 * @phpstan-param mixed $input_lines
 * @psalm-param mixed $input_lines
 * @return mixed Returned value for getQueriesPerformedOther.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getQueriesPerformedOther()
 * @example /fr/benchmark/getQueriesPerformedOther
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getQueriesPerformedOther($input_lines)
    {
        preg_match_all("/queries\sperformed:[\s]+read:[\s]+[\d]+[\s]+write:[\s]+[\d]+[\s]+other:[\s]+([\d]+)[\s]+/Ux", $input_lines, $output_array);

        if (isset($output_array[1][0])) {
            return $output_array[1][0];
        } else {
            return false;
        }
    }

/**
 * Retrieve benchmark state through `getQueriesPerformedTotal`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $input_lines Input value for `input_lines`.
 * @phpstan-param mixed $input_lines
 * @psalm-param mixed $input_lines
 * @return mixed Returned value for getQueriesPerformedTotal.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getQueriesPerformedTotal()
 * @example /fr/benchmark/getQueriesPerformedTotal
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getQueriesPerformedTotal($input_lines)
    {
        preg_match_all("/queries\sperformed:[\s]+read:[\s]+[\d]+[\s]+write:[\s]+[\d]+[\s]+other:[\s]+[\d]+[\s]+total:[\s]+([\d]+)[\s]+/Ux", $input_lines,
            $output_array);

        if (isset($output_array[1][0])) {
            return $output_array[1][0];
        } else {
            return false;
        }
    }

/**
 * Retrieve benchmark state through `getTransactions`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $input_lines Input value for `input_lines`.
 * @phpstan-param mixed $input_lines
 * @psalm-param mixed $input_lines
 * @return mixed Returned value for getTransactions.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getTransactions()
 * @example /fr/benchmark/getTransactions
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getTransactions($input_lines)
    {
        preg_match_all("/transactions:[\s]+([\d]+)[\s]+/Ux", $input_lines, $output_array);

        if (isset($output_array[1][0])) {
            return $output_array[1][0];
        } else {
            return false;
        }
    }

/**
 * Retrieve benchmark state through `getErrors`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $input_lines Input value for `input_lines`.
 * @phpstan-param mixed $input_lines
 * @psalm-param mixed $input_lines
 * @return mixed Returned value for getErrors.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getErrors()
 * @example /fr/benchmark/getErrors
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getErrors($input_lines)
    {
        preg_match_all("/ignored\serrors:[\s]+([\d]+)[\s]+/Ux", $input_lines, $output_array);

        if (isset($output_array[1][0])) {
            return $output_array[1][0];
        } else {
            return false;
        }
    }

/**
 * Retrieve benchmark state through `getTotalTime`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $input_lines Input value for `input_lines`.
 * @phpstan-param mixed $input_lines
 * @psalm-param mixed $input_lines
 * @return mixed Returned value for getTotalTime.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getTotalTime()
 * @example /fr/benchmark/getTotalTime
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getTotalTime($input_lines)
    {
        preg_match_all("/total\stime:[\s]+([\S]+)[\s]+/Ux", $input_lines, $output_array);

        if (isset($output_array[1][0])) {
            return str_replace("s", "", $output_array[1][0]);
        } else {
            return false;
        }
    }

/**
 * Retrieve benchmark state through `getReponseTimeMin`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $input_lines Input value for `input_lines`.
 * @phpstan-param mixed $input_lines
 * @psalm-param mixed $input_lines
 * @return mixed Returned value for getReponseTimeMin.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getReponseTimeMin()
 * @example /fr/benchmark/getReponseTimeMin
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getReponseTimeMin($input_lines)
    {
        preg_match_all("/min:[\s]+([\S]+)[\s]+/Ux", $input_lines, $output_array);

        if (isset($output_array[1][0])) {
            return str_replace("ms", "", $output_array[1][0]);
        } else {
            return false;
        }
    }

/**
 * Retrieve benchmark state through `getReponseTimeMax`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $input_lines Input value for `input_lines`.
 * @phpstan-param mixed $input_lines
 * @psalm-param mixed $input_lines
 * @return mixed Returned value for getReponseTimeMax.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getReponseTimeMax()
 * @example /fr/benchmark/getReponseTimeMax
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getReponseTimeMax($input_lines)
    {
        preg_match_all("/max:[\s]+([\S]+)[\s]+/Ux", $input_lines, $output_array);

        if (isset($output_array[1][0])) {
            return str_replace("ms", "", $output_array[1][0]);
        } else {
            return false;
        }
    }

/**
 * Retrieve benchmark state through `getReponseTimeAvg`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $input_lines Input value for `input_lines`.
 * @phpstan-param mixed $input_lines
 * @psalm-param mixed $input_lines
 * @return mixed Returned value for getReponseTimeAvg.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getReponseTimeAvg()
 * @example /fr/benchmark/getReponseTimeAvg
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getReponseTimeAvg($input_lines)
    {
        preg_match_all("/avg:[\s]+([\S]+)[\s]+/Ux", $input_lines, $output_array);

        if (!empty($output_array[1][0])) {

            return str_replace("ms", "", $output_array[1][0]);
        } else {
            return false;
        }
    }

/**
 * Retrieve benchmark state through `getReponseTime95percent`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $input_lines Input value for `input_lines`.
 * @phpstan-param mixed $input_lines
 * @psalm-param mixed $input_lines
 * @return mixed Returned value for getReponseTime95percent.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getReponseTime95percent()
 * @example /fr/benchmark/getReponseTime95percent
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getReponseTime95percent($input_lines)
    {
        $output_array = array();
        preg_match_all("/95th\spercentile:[\s]+([\S]+)[\s]+/Ux", $input_lines, $output_array);
        //Debug::debug($output_array, "output 95percent");

        if (!empty($output_array[1][0])) {
            return str_replace("ms", "", $output_array[1][0]);
        } else {
            return false;
        }
    }

/**
 * Handle benchmark state through `testMoc`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for testMoc.
 * @phpstan-return void
 * @psalm-return void
 * @see self::testMoc()
 * @example /fr/benchmark/testMoc
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function testMoc($param)
    {
        Debug::parseDebug($param);

        $this->view = false;
        $data       = $this->moc3();

        echo "Nombre de read : ".$this->getQueriesPerformedRead($data)."\n";
        echo "Nombre de write : ".$this->getQueriesPerformedWrite($data)."\n";
        echo "Nombre de other : ".$this->getQueriesPerformedOther($data)."\n";
        echo "Nombre de total : ".$this->getQueriesPerformedTotal($data)."\n";

        echo "Nombre de transaction  : ".$this->getTransactions($data)."\n";
        echo "Nombre de time : ".$this->getTotalTime($data)."\n";
        echo "Nombre de min : ".$this->getReponseTimeMin($data)."\n";
        echo "Nombre de max : ".$this->getReponseTimeMax($data)."\n";
        echo "Nombre de avg : ".$this->getReponseTimeAvg($data)."\n";
        echo "Nombre de percent95 : ".$this->getReponseTime95percent($data)."\n";

// 13 benchmark_thread
    }

/**
 * Handle benchmark state through `moc`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for moc.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::moc()
 * @example /fr/benchmark/moc
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function moc()
    {
        return "LTP test statistics:
    queries performed:
        read:                            140098
        write:                           40010
        other:                           20007
        total:                           200115
    transactions:                        10000  (343.26 per sec.)
    read/write requests:                 180108 (6182.33 per sec.)
    other operations:                    20007  (686.75 per sec.)
    ignored errors:                      7      (0.24 per sec.)
    reconnects:                          0      (0.00 per sec.)

General statistics:
    total time:                          29.1327s
    total number of events:              10000
    total time taken by event execution: 1861.1741s
    response time:
         min:                                 74.15ms
         avg:                                186.12ms
         max:                                634.61ms
         approx.  95 percentile:             285.37ms

Threads fairness:
    events (avg/stddev):           156.2500/2.89
    execution time (avg/stddev):   29.0808/0.04";
    }

/**
 * Handle benchmark state through `moc2`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for moc2.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::moc2()
 * @example /fr/benchmark/moc2
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function moc2()
    {
        return "WARNING: --max-time is deprecated, use --time instead
sysbench 1.0.18 (using system LuaJIT 2.1.0-beta3)

Running the test with following options:
Number of threads: 1
Initializing random number generator from current time


Initializing worker threads...

Threads started!

SQL statistics:
    queries performed:
        read:                            26068
        write:                           7448
        other:                           3724
        total:                           37240
    transactions:                        1862   (62.04 per sec.)
    queries:                             37240  (1240.84 per sec.)
    ignored errors:                      0      (0.00 per sec.)
    reconnects:                          0      (0.00 per sec.)

General statistics:
    total time:                          30.0062s
    total number of events:              1862

Latency (ms):
         min:                                   11.51
         avg:                                   16.11
         max:                                   30.56
         95th percentile:                       19.29
         sum:                                29995.47

Threads fairness:
    events (avg/stddev):           1862.0000/0.00
    execution time (avg/stddev):   29.9955/0.00";
    }

/**
 * Handle benchmark state through `moc3`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for moc3.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::moc3()
 * @example /fr/benchmark/moc3
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function moc3()
    {
        return "sysbench 1.0.18 (using system LuaJIT 2.1.0-beta3)

Running the test with following options:
Number of threads: 13
Initializing random number generator from current time


Initializing worker threads...

Threads started!

SQL statistics:
    queries performed:
        read:                            0
        write:                           508442
        other:                           0
        total:                           508442
    transactions:                        508442 (50831.13 per sec.)
    queries:                             508442 (50831.13 per sec.)
    ignored errors:                      0      (0.00 per sec.)
    reconnects:                          0      (0.00 per sec.)

General statistics:
    total time:                          10.0013s
    total number of events:              508442

Latency (ms):
         min:                                    0.10
         avg:                                    0.26
         max:                                  160.70
         95th percentile:                        0.41
         sum:                               129757.40

Threads fairness:
    events (avg/stddev):           39110.9231/124.82
    execution time (avg/stddev):   9.9813/0.00
";
    }

/**
 * Render benchmark state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/benchmark/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index($param)
    {


        Debug::parseDebug($param);

        $this->title  = '<i class="fa fa-tachometer"></i> '.__("Benchmark");
        $this->ariane = '> <a href="'.LINK.'plugins"><i class="fa fa-puzzle-piece"></i> '.__("Plugins").'</a> > '.$this->title;

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT count(1) as cpt from benchmark_main where status != 'COMPLETED'";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $cpt = $ob->cpt;
        }

        $badge = "";
        if (!empty($cpt)) {
            $badge = ' <span class="badge">'.$cpt.'</span>';
        }

        $data['menu']['bench']['name']  = __('Make a new benchmark');
        $data['menu']['bench']['icone'] = '<i class="fa fa-clock-o" aria-hidden="true"></i>';
        $data['menu']['bench']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/bench';

        $data['menu']['current']['name']  = __('Currents').$badge;
        $data['menu']['current']['icone'] = '<i class="fa fa-refresh fa-spin" aria-hidden="true"></i>';
        $data['menu']['current']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/current';

        //$data['menu']['config']['name']  = __('Configuration');
        //$data['menu']['config']['icone'] = '<i class="fa fa-wrench" aria-hidden="true"></i>';
        //$data['menu']['config']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/config';

        //$data['menu']['script']['name']  = __('Scripts');
        //$data['menu']['script']['icone'] = '<i class="fa fa-gears" aria-hidden="true"></i>';
        //$data['menu']['script']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/script';

        $data['menu']['graph']['name']  = __('Graphs');
        $data['menu']['graph']['icone'] = '<i class="fa fa-area-chart" aria-hidden="true"></i>';
        $data['menu']['graph']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/graph';

        if (!empty($param[0])) {
            if (in_array($param[0], array_keys($data['menu']))) {
                $_GET['path'] = LINK.$this->getClass().'/'.__FUNCTION__.'/'.$param[0];
            }
        }

        if (empty($_GET['path']) && empty($param[0])) {
            $_GET['path'] = $data['menu']['graph']['path'];
            $param[0]     = 'graph';
        }

        if (empty($_GET['path'])) {
            $_GET['path'] = 'graph';
        }

        $this->set("data", $data);
    }

/**
 * Handle benchmark state through `testError`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $input_lines Input value for `input_lines`.
 * @phpstan-param mixed $input_lines
 * @psalm-param mixed $input_lines
 * @return mixed Returned value for testError.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::testError()
 * @example /fr/benchmark/testError
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function testError($input_lines)
    {
        $pos = strpos($input_lines, "FATAL:");

        if ($pos !== false) {
            return true;
        }
        return false;
    }



/**
 * Handle benchmark state through `install`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for install.
 * @phpstan-return void
 * @psalm-return void
 * @see self::install()
 * @example /fr/benchmark/install
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function install()
    {

    }

/**
 * Handle benchmark state through `uninstall`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for uninstall.
 * @phpstan-return void
 * @psalm-return void
 * @see self::uninstall()
 * @example /fr/benchmark/uninstall
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function uninstall()
    {

    }

/**
 * Handle benchmark state through `graph`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for graph.
 * @phpstan-return void
 * @psalm-return void
 * @see self::graph()
 * @example /fr/benchmark/graph
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function graph()
    {
        $db = Sgbd::sql(DB_DEFAULT);
        $this->di['js']->addJavascript(array("chart.min.js"));

        $data = array();

        if ($_SERVER['REQUEST_METHOD'] == "POST") {

            if (!empty($_POST['benchmark'])) {

                if (!empty($_POST['benchmark_main']['id'])) {
                    $ret = "";
                    $ret .= "benchmark/index/benchmark_main:id:".json_encode($_POST['benchmark_main']['id']);
                } else {

                    $ret = "";
                    $ret .= "benchmark/index/";
                }

                header("location: ".LINK.$ret);

                exit;
            }
        }

        if (!empty($_GET['benchmark_main']['id'])) {
            $id_to_take = implode(",", json_decode($_GET['benchmark_main']['id']));
        } else {
            $sql = "SELECT max(id) as id FROM `benchmark_main` WHERE status = 'COMPLETED'";
            $res = $db->sql_query($sql);

            while ($ob = $db->sql_fetch_object($res)) {
                $id_to_take                   = $ob->id;
                $_GET['benchmark_main']['id'] = json_encode(array($id_to_take));
            }
        }

        if (empty($id_to_take)) {
            $this->set("data", $data);
            return;
        }

        $sql = "select a.*,b.display_name, b.port from `benchmark_main` a
         INNER JOIN mysql_server b ON a.id_mysql_server = b.id
         ORDER BY a.date_end DESC LIMIT 100";
        $res = $db->sql_query($sql);

        $data['select_bench'] = array();

        while ($ob = $db->sql_fetch_object($res)) {

            $tmp = array();

            $tmp['id']      = $ob->id;
            $tmp['libelle'] = $ob->display_name.":".$ob->port." (".$ob->date_end.")";

            $data['select_bench'][] = $tmp;
        }

        $sql = "SELECT a.display_name, a.port, b.`date`,b.id
            FROM mysql_server a
            INNER JOIN `benchmark_main` b ON a.id = b.id_mysql_server
            WHERE b.id IN (".$id_to_take.")
            ";
        $res = $db->sql_query($sql);

        $benchmark = array();
        while ($ob        = $db->sql_fetch_object($res)) {
            $benchmark[$ob->id] = $ob->display_name.":".$ob->port." (".$ob->date.")";
        }

        $sql = "SELECT id_benchmark_main,
            GROUP_CONCAT(a.threads) as thread,
            GROUP_CONCAT(ROUND(`read`/`time`,2)) as `read`,
            GROUP_CONCAT(ROUND(`write`/`time`,2)) as `write`,
            GROUP_CONCAT(reponse_avg) as `reponse_avg`,
            GROUP_CONCAT(ROUND(`transaction`/`time`,2)) as `transaction`,
            GROUP_CONCAT(a.error) as `error`,
            GROUP_CONCAT(ROUND(`read`/(`read`+`write`)*100,2)) as `ratio`
            FROM benchmark_run a
            WHERE `id_benchmark_main` in (".$id_to_take.") 
            GROUP BY id_benchmark_main;";
            
        $res = $db->sql_query($sql);

        $threads      = [];
        $reads        = [];
        $write        = [];
        $reponse_time = [];
        $transaction  = [];
        $error        = [];

        while ($ob = $db->sql_fetch_object($res)) {

            $threads[]                                                 = $ob->thread;
            $results["Reads by second"][$ob->id_benchmark_main]        = $ob->read;
            $results["Writes by second"][$ob->id_benchmark_main]       = $ob->write;
            $results["Response Time (ms)"][$ob->id_benchmark_main]     = $ob->reponse_avg;
            $results["Transactions by second"][$ob->id_benchmark_main] = $ob->transaction;
            $results["Errors"][$ob->id_benchmark_main]                 = $ob->error;
            $results["Ratio"][$ob->id_benchmark_main]                  = $ob->ratio;
        }

        $val     = 0;
        $absisse = "";
        foreach ($threads as $thread) {
            if (strlen($thread) > strlen($absisse)) {
                $absisse = $thread;
            }
        }

        $i = 1;

        $tmp =array();

        if (!empty($results)) {
            foreach ($results as $title => $result) {

                $js = 'var ctx'.$i.' = document.getElementById("graph'.$i.'");

            var myChart'.$i.' = new Chart(ctx'.$i.', {
                type: "line",
                data: {
                    labels: ['.$absisse.'],
                    datasets: [';

                $j   = 0;
                $tmp = array();
                foreach ($result as $server => $res_by_server) {

                    $j = $j % count($this->colors);

                    $tmp[] = '
                {
                    label: "'.$benchmark[$server].'",
                    data: ['.$res_by_server.'],
                    backgroundColor: "rgba('.$this->colors[$j].','.self::SHADOW.')",
                    borderColor: "rgba('.$this->colors[$j].',1)",
                }';

                    $j++;
                }

                $js .= implode(',', $tmp);

                $js .= ']
                },
                options: {
                    title: {
                        display: true,
                        text: "'.__($title).'",
                        position: "top",
                        padding: "10"
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero:false
                            }
                        }]
                    },
                    pointDot : false,
                }
            });';

                $this->di['js']->code_javascript($js);
                $i++;
            }

            $this->set("data", $data);
        }
    }

/**
 * Handle benchmark state through `config`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for config.
 * @phpstan-return void
 * @psalm-return void
 * @see self::config()
 * @example /fr/benchmark/config
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function config()
    {
        $db = Sgbd::sql(DB_DEFAULT);
    }

/**
 * Handle benchmark state through `bench`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for bench.
 * @phpstan-return void
 * @psalm-return void
 * @see self::bench()
 * @example /fr/benchmark/bench
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function bench($param)
    {

        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        if (! IS_CLI && $_SERVER['REQUEST_METHOD'] == "POST") {

            if (!empty($_POST['benchmark'])) {
                if (!empty($_POST['mysql_server']['id'])) {

                    //boucler sur tous les cas à prévoir
                    foreach ($_POST['benchmark_main']['mode'] as $mode) {
                        foreach ($_POST['mysql_server']['id'] as $id_mysql_server) {

                            if (empty($id_mysql_server)) {
                                continue;
                            }

                            $sql = "INSERT INTO benchmark_main
                            SET id_mysql_server = '".$id_mysql_server."',
                            id_user_main = '".$this->di['auth']->getuser()->id."',
                            date = '".date("Y-m-d H:i:s")."',
                            sysbench_version = '".$this->getSysbenchVersion()."',
                            threads = '".implode(',', $_POST['benchmark_main']['threads'])."',
                            tables_count = '".$_POST['benchmark_main']['tables_count']."',
                            table_size = '".$_POST['benchmark_main']['tables_count']."',
                            mode = '".$mode."',
                            max_time = '".$_POST['benchmark_main']['max_time']."',
                            status = 'NOT STARTED',
                            date_start='0000-00-00 00:00:00',
                            date_end='0000-00-00 00:00:00',
                            progression=0
                            ";

                            $db->sql_query($sql);
                        }
                    }

                    $sql = "SELECT * FROM benchmark_config where id=1";
                    $res = $db->sql_query($sql);

                    // system de queue
                    while ($ob = $db->sql_fetch_object($res)) {

                        $start_queue = true;
                        if (!empty($ob->pid)) {
                            $cmd   = "ps -p ".$ob->pid;
                            $alive = shell_exec($cmd);

                            if (strpos($alive, $ob->pid) !== false) {
                                $start_queue = false;
                            }
                        }

                        if ($start_queue) {

                            $php = explode(" ", shell_exec("whereis php"))[1];
                            $cmd = $php." ".GLIAL_INDEX." Benchmark queue >> /tmp/queue & echo $!";

                            $pid = 0;
                            $pid = trim(shell_exec($cmd));

                            $this->logger->warning("STARTED QUEUE : $pid");

                            $sql = "UPDATE `benchmark_config` SET pid = '".$pid."' WHERE id = 1";
                            $db->sql_query($sql);

                            set_flash("success", "Daemon", "Benchmark started in background, check onglet current");
                        } else {
                            set_flash("caution", "Daemon", "Daemon already started, benchmark added in queue, check onglet current");
                        }
                    }
                } else {
                    set_flash("error", "Server", "Please select the server(s) you want to bench");

                    header("location: ".LINK.$this->getClass()."/index/".__FUNCTION__);
                }
            }
        }

        // version de sysbench
        $data['sysbench'] = $this->getSysbenchVersion();


        // chargement de la config
        $sql = "SELECT * FROM benchmark_config where id=1";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            $_GET['benchmark_main']['threads']      = json_encode(explode(",", $ob->threads));
            $_GET['benchmark_main']['tables_count'] = $ob->tables_count;
            $_GET['benchmark_main']['table_size ']  = $ob->table_size;
            $_GET['benchmark_main']['max_time']     = $ob->max_time;
            $_GET['benchmark_main']['mode']         = $ob->mode;
            //$_GET['benchmark_main']['read_only']    = $ob->read_only;
        }

        //debug($_GET['benchmark_main']);
        // get server available
        // génération des <select></select>
        //TODO : add filter on error where 1=1
        $sql             = "SELECT * FROM mysql_server a WHERE 1=1 ".$this->getFilter()." order by a.name ASC";
        $res             = $db->sql_query($sql);
        $data['servers'] = array();
        while ($ob              = $db->sql_fetch_object($res)) {
            $tmp               = [];
            $tmp['id']         = $ob->id;
            $tmp['libelle']    = $ob->name." (".$ob->ip.")";
            $data['servers'][] = $tmp;
        }

        $data['treads'] = array();

        for ($i = 0; $i < 1024; $i++) {
            $tmp              = array();
            $tmp['id']        = ($i + 1);
            $tmp['libelle']   = ($i + 1);
            $data['treads'][] = $tmp;
        }


        $data['tables_count'] = array();

        for ($i = 0; $i < 100; $i++) {
            $tmp                    = array();
            $tmp['id']              = ($i + 1);
            $tmp['libelle']         = ($i + 1);
            $data['tables_count'][] = $tmp;
        }

        $modes = $this->getLua();

        $data['test_mode'] = array();
        foreach ($modes as $mode) {
            $tmp                 = array();
            $tmp['id']           = $mode;
            $tmp['libelle']      = $mode;
            $data['test_mode'][] = $tmp;
        }


        /*
        $read_o = array("ON", "OFF");

        $data['read_only'] = array();
        foreach ($read_o as $mode) {
        $tmp                 = array();
        $tmp['id']           = $mode;
        $tmp['libelle']      = $mode;
        $data['read_only'][] = $tmp;
        } */

        $data['max_time'] = array();

        for ($i = 0; $i < 300; $i++) {
            $tmp                = array();
            $tmp['id']          = ($i );
            $tmp['libelle']     = ($i );
            $data['max_time'][] = $tmp;
        }

    
    
        $this->set("data", $data);
    }

/**
 * Handle benchmark state through `current`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for current.
 * @phpstan-return void
 * @psalm-return void
 * @see self::current()
 * @example /fr/benchmark/current
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function current()
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT *,a.id as id_benchmark_main FROM benchmark_main a
             INNER JOIN mysql_server b on a.id_mysql_server = b.id
             ORDER BY a.id desc";

        $res = $db->sql_query($sql);

        $data['current'] = array();
        while ($ob              = $db->sql_fetch_array($res)) {
            $data['current'][] = $ob;
        }


        $sql = "SELECT b.id_benchmark_main, count(1) as cpt FROM benchmark_main a
            INNER JOIN benchmark_run b ON a.id = b.id_benchmark_main WHERE a.status = 'RUNNING' GROUP BY b.id_benchmark_main;";
        $res = $db->sql_query($sql);

        $data['running'] = array();
        while ($ob              = $db->sql_fetch_object($res)) {
            $data['running'][$ob->id_benchmark_main] = $ob->cpt;
        }



        $this->set('data', $data);
    }

    // to move
/**
 * Retrieve benchmark state through `getFilter`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for getFilter.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getFilter()
 * @example /fr/benchmark/getFilter
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function getFilter()
    {

        $where = "";

        if (!empty($_GET['environment']['libelle'])) {
            $environment = $_GET['environment']['libelle'];
        }
        if (!empty($_SESSION['environment']['libelle']) && empty($_GET['environment']['libelle'])) {
            $environment                    = $_SESSION['environment']['libelle'];
            $_GET['environment']['libelle'] = $environment;
        }

        if (!empty($_SESSION['client']['libelle'])) {
            $client = $_SESSION['client']['libelle'];
        }
        if (!empty($_GET['client']['libelle']) && empty($_GET['client']['libelle'])) {
            $client                    = $_GET['client']['libelle'];
            $_GET['client']['libelle'] = $client;
        }


        if (!empty($environment)) {
            $where .= " AND a.id_environment IN (".implode(',', json_decode($environment, true)).")";
        }

        if (!empty($client)) {
            $where .= " AND a.id_client IN (".implode(',', json_decode($client, true)).")";
        }


        return $where;
    }

/**
 * Handle benchmark state through `queue`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for queue.
 * @phpstan-return void
 * @psalm-return void
 * @see self::queue()
 * @example /fr/benchmark/queue
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function queue($param)
    {
        /*
         *
         * status :
         * NOT STARTED
         * LAUNCHED
         * STARTED
         * ERROR
         * COMPLETED
         */

        $this->view = false;

        $db = Sgbd::sql(DB_DEFAULT);

        Debug::parseDebug($param);

        //$sql3 = "UPDATE `benchmark_main` SET `status` = 'NOT STARTED' WHERE `status` = 'LAUNCHED';";
        //$db->sql_query($sql3);
        //Debug::debug(trim(SqlFormatter::highlight($sql3)));


        $sql = "SELECT * FROM `benchmark_main` WHERE `status` = 'NOT STARTED' limit 1;";
        $res = $db->sql_query($sql);
        Debug::sql($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $id_benchmark_main = $ob->id;
        }

        if (!empty($id_benchmark_main)) {

            do {
                //to prevent any trouble with fork, or long time bench
                $db->sql_close();

                $debug = '';
                if (Debug::$debug) {
                    $debug = "--debug";
                }

                $php = explode(" ", shell_exec("whereis php"))[1];
                $cmd = $php." ".GLIAL_INDEX." Benchmark run ".$id_benchmark_main." ".$debug."";

                Debug::debug($cmd);

                $err = 1;
                echo passthru($cmd, $err);

                Debug::debug("return : ".$err);

                $db  = Sgbd::sql(DB_DEFAULT);
                $res = $db->sql_query($sql);
                while ($ob  = $db->sql_fetch_object($res)) {
                    $id_benchmark_main = $ob->id;

                    $sql2 = "UPDATE benchmark_main SET status = 'LAUNCHED' WHERE id ='".$id_benchmark_main."';";
                    $db->sql_query($sql2);

                    Debug::sql($sql2);
                }
            } while ($db->sql_num_rows($res) > 0);
        } else {
            Debug::debug("No bench to do !");
        }

        $sql = "UPDATE `benchmark_config` SET pid = '0' WHERE id = 1";
        $db->sql_query($sql);
    }

/**
 * Handle benchmark state through `debug`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param mixed $string Input value for `string`.
 * @phpstan-param mixed $string
 * @psalm-param mixed $string
 * @return void Returned value for debug.
 * @phpstan-return void
 * @psalm-return void
 * @see self::debug()
 * @example /fr/benchmark/debug
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function debug($string)
    {
        if (Debug::$debug) {
            $calledFrom = debug_backtrace();
            $file       = pathinfo(substr(str_replace(ROOT, '', $calledFrom[0]['file']), 1))["basename"];
            $line       = $calledFrom[0]['line'];

            $file = explode(".", $file)[0];

            echo "#".$this->count++."\t";
            echo $file.":".$line."\t";
            echo Color::getColoredString("[".date('Y-m-d H:i:s')."]", "purple")." ";
            echo $string."\n";
        }
    }

/**
 * Retrieve benchmark state through `getSysbenchVersion`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for getSysbenchVersion.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getSysbenchVersion()
 * @example /fr/benchmark/getSysbenchVersion
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getSysbenchVersion($param = array())
    {

        Debug::parseDebug($param);

        $data['sysbench'] = "N/A";
        $version          = shell_exec("sysbench --version 2> /dev/null");

        if (!empty($version)) {
            $elems            = explode(" ", $version);
            $data['sysbench'] = trim($elems[1]);
        }

        Debug::debug($data['sysbench'], "Version sysbench");

        return $data['sysbench'];
    }

/**
 * Retrieve benchmark state through `getScriptLua`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for getScriptLua.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @see self::getScriptLua()
 * @example /fr/benchmark/getScriptLua
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getScriptLua($param)
    {
        Debug::parseDebug($param);

        if (!is_array($param)) {
            throw new \Exception('getScriptLua : Should be an array', 5491);
        }

        $direstory_script = $param[0];

        $cmd = "ls ".$direstory_script." | grep -v oltp_common.lua";
        Debug::debug($cmd, 'cmd');

        $lua = shell_exec($cmd);

        Debug::debug($lua, "lua");

        $lua = explode("\n", trim($lua));

        Debug::debug($lua, "lua");

        //echo file_get_contents($direstory_script."oltp_point_select.lua");


        return $lua;
    }

/**
 * Retrieve benchmark state through `getDirectoryLua`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for getDirectoryLua.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getDirectoryLua()
 * @example /fr/benchmark/getDirectoryLua
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getDirectoryLua($param)
    {
        Debug::parseDebug($param);
        $version = $param[0];

        $ret = '';
        if (version_compare($version, '0.5', "=")) {
            $ret = self::DIRECTORY_LUA_SYSBENCH_05;
        } else if (version_compare($version, '1', ">=")) {
            $ret = self::DIRECTORY_LUA_SYSBENCH_1;
        } else {
            //Throw new \Exception("This verion of sysbench is not supported yet");
        }

        if (!is_dir($ret)) {
            //Throw new \Exception("The directory does not exist : $ret", 1548);
        }

        Debug::debug($ret, "directory lua");

        return $ret;
    }


    /*
    public function script($param)
    {
        Debug::parseDebug($param);

        $data['test_mode'] = array();
        foreach ($modes as $mode) {
            $tmp                 = array();
            $tmp['id']           = $mode;
            $tmp['libelle']      = $mode;
            $data['test_mode'][] = $tmp;
        }

        $this->set('data', $data);
    }*/

    public function getLua()
    {
        return $this->getScriptLua(array($this->getDirectoryLua($this->getSysbenchVersion())));
    }
}
