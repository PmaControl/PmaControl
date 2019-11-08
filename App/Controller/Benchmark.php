<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Security\Crypt\Crypt;
use \Glial\Cli\Color;
use \App\Library\Debug;

class Benchmark extends Controller {

    const COLOR_GREEN = "75,215,134";
    const COLOR_BLUE = "142,199,255";
    const COLOR_YELLOW = "252,215,95";
    const COLOR_RED = "255,168,168";
    const COLOR_GREY = "130,130,130";
    const SHADOW = "0.2";

    var $debug = false;
    var $count = 1;

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

    public function run($param) {
        $this->view = false;


        Debug::parseDebug($param);
        Debug::debug(Color::getColoredString("Debug enabled !", "yellow"));

        $id_benchmark_main = $param[0];

        Debug::debug("id_benchmark_main : $id_benchmark_main");

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * FROM benchmark_main a
            INNER JOIN mysql_server b ON a.id_mysql_server = b.id
        WHERE a.id = " . $id_benchmark_main;
        $res = $db->sql_query($sql);

        Debug::debug(trim(SqlFormatter::highlight($sql)));

        while ($ob = $db->sql_fetch_object($res)) {

            $sql2 = "UPDATE benchmark_main SET status = 'RUNNING',date_start = '" . date('Y-m-d H:i:s') . "' WHERE id ='" . $id_benchmark_main . "'";
            $db->sql_query($sql2);
            Debug::debug(trim(SqlFormatter::highlight($sql2)));

            $password = Crypt::decrypt($ob->passwd, CRYPT_KEY);

            $server = $this->di['db']->sql($ob->name);

            $sql = "DROP DATABASE IF EXISTS sbtest;";
            $server->sql_query($sql);

            $sql = "CREATE DATABASE sbtest;";
            $server->sql_query($sql);


            $data['sysbench'] = $this->getSysbenchVersion();
            Debug::debug($data['sysbench'], "Version sysbench");


            if (version_compare($data['sysbench'], '0.5', "=")) {

                $prepare = 'sysbench --test=/usr/local/sysbench/tests/db/oltp.lua --mysql-host=' . $ob->ip . ' --mysql-port=' . $ob->port;
                $prepare .= ' --mysql-user=' . $ob->login . ' --mysql-password=' . $password . ' '
                        . '--mysql-db=sbtest --mysql-table-engine=InnoDB '
                        . '--oltp-tables-count=' . $ob->tables_count . ' --max-time=' . $ob->max_time . ' prepare';
            } else if (version_compare($data['sysbench'], '1', ">=")) {

                $prepare = 'sysbench --mysql-host=' . $ob->ip . ' --mysql-port=' . $ob->port;
                $prepare .= ' --mysql-user=' . $ob->login . ' --mysql-password=' . $password . ' '
                        . '--mysql-db=sbtest --mysql-table-engine=InnoDB '
                        . '--oltp-tables-count=' . $ob->tables_count . ' --max-time=' . $ob->max_time . ' prepare';
            }

            Debug::debug($prepare);

            $input_lines = shell_exec($prepare);
            Debug::debug($input_lines);

            $sql = "select @@max_connections as max;";
            $res2 = $server->sql_query($sql);

            while ($ob2 = $server->sql_fetch_object($res2)) {
                $max_connections = $ob2->max;
            }







            $threads = explode(',', $ob->threads);
            foreach ($threads as $thread) {

                if ($max_connections > $thread + 1) {




                    if (version_compare($data['sysbench'], '0.5', "=")) {

                        $cmd = 'sysbench '
                                . ' --test=/usr/local/sysbench/tests/db/oltp.lua'
                                . ' --mysql-host=' . $ob->ip
                                . ' --mysql-port=' . $ob->port
                                . ' --mysql-user=' . $ob->login
                                . ' --mysql-password=' . $password
                                . ' --mysql-db=sbtest'
                                . ' --mysql-table-engine=InnoDB'
                                . ' --oltp-test-mode=' . $ob->mode
                                . ' --oltp-read-only=' . strtolower($ob->read_only)
                                . ' --oltp-tables-count=' . $ob->tables_count
                                . ' --num-threads=' . $thread
                                . ' --max-time=' . $ob->max_time . ' run';
                    } else if (version_compare($data['sysbench'], '1', ">=")) {

                        $cmd = 'sysbench '
                                . ' --mysql-host=' . $ob->ip
                                . ' --mysql-port=' . $ob->port
                                . ' --mysql-user=' . $ob->login
                                . ' --mysql-password=' . $password
                                . ' --mysql-db=sbtest'
                                . ' --mysql-table-engine=InnoDB'
                                . ' --oltp-test-mode=' . $ob->mode
                                . ' --oltp-read-only=' . strtolower($ob->read_only)
                                . ' --oltp-tables-count=' . $ob->tables_count
                                . ' --num-threads=' . $thread
                                . ' --time=' . $ob->max_time . '';
                    } else {

                        Debug::debug($data['sysbench'], "Version of sysbench not supported");
                    }



                    Debug::debug(Color::getColoredString($cmd, "yellow"));

                    $input_lines = shell_exec($cmd);
                    sleep(5);

                    Debug::debug(Color::getColoredString($input_lines, "blue"));

                    $sql = "INSERT INTO benchmark_run
                      SET id_benchmark_main = '" . $id_benchmark_main . "',
                      `date` = '" . date("Y-m-d H:i:s") . "',
                      `threads`  = '" . $thread . "',

                      `read` = '" . $this->getQueriesPerformedRead($input_lines) . "',
                      `write` = '" . $this->getQueriesPerformedWrite($input_lines) . "',
                      `other` = '" . $this->getQueriesPerformedOther($input_lines) . "',
                      `total` = '" . $this->getQueriesPerformedTotal($input_lines) . "',
                      `transaction` = '" . $this->getTransactions($input_lines) . "',
                      `error` = '" . $this->getErrors($input_lines) . "',
                      `time` = '" . $this->getTotalTime($input_lines) . "',
                      `reponse_min` = '" . $this->getReponseTimeMin($input_lines) . "',
                      `reponse_max` = '" . $this->getReponseTimeMax($input_lines) . "',
                      `reponse_avg` = '" . $this->getReponseTimeAvg($input_lines) . "',
                      `reponse_percentile95` = '" . $this->getReponseTime95percent($input_lines) . "'
                      ";

                    //better to get PANIC and FATAL error and send to log
                    if (!empty($this->getQueriesPerformedRead($input_lines))) {
                        $db->sql_query($sql);
                    }
                } else {
                    Debug::debug(Color::getColoredString("Thread canceled : " . $thread . " (max_connections : " . $max_connections . ")", "yellow"));
                }


                // $sql2 = "UPDATE benchmark_main SET status = 'COMPLETED',date_end = '".date('c')."' WHERE id ='".$id_benchmark_main."'";
                //$db->sql_query($sql2);
            }

            $sql2 = "UPDATE benchmark_main SET status = 'COMPLETED',date_end = '" . date('Y-m-d H:i:s') . "' WHERE id ='" . $id_benchmark_main . "'";
            $db->sql_query($sql2);
        }
    }

    public function getQueriesPerformedRead($input_lines) {
        preg_match_all("/queries\sperformed:[\s]+read:[\s]+([\d]+)[\s]+/Ux", $input_lines, $output_array);

        if (!empty($output_array[1][0])) {
            return $output_array[1][0];
        } else {
            return false;
        }
    }

    public function getQueriesPerformedWrite($input_lines) {
        preg_match_all("/queries\sperformed:[\s]+read:[\s]+[\d]+[\s]+write:[\s]+([\d]+)[\s]+/Ux", $input_lines, $output_array);

        if (!empty($output_array[1][0])) {
            return $output_array[1][0];
        } else {
            return false;
        }
    }

    public function getQueriesPerformedOther($input_lines) {
        preg_match_all("/queries\sperformed:[\s]+read:[\s]+[\d]+[\s]+write:[\s]+[\d]+[\s]+other:[\s]+([\d]+)[\s]+/Ux", $input_lines, $output_array);

        if (!empty($output_array[1][0])) {
            return $output_array[1][0];
        } else {
            return false;
        }
    }

    public function getQueriesPerformedTotal($input_lines) {
        preg_match_all("/queries\sperformed:[\s]+read:[\s]+[\d]+[\s]+write:[\s]+[\d]+[\s]+other:[\s]+[\d]+[\s]+total:[\s]+([\d]+)[\s]+/Ux", $input_lines, $output_array);

        if (!empty($output_array[1][0])) {
            return $output_array[1][0];
        } else {
            return false;
        }
    }

    public function getTransactions($input_lines) {
        preg_match_all("/transactions:[\s]+([\d]+)[\s]+/Ux", $input_lines, $output_array);

        if (!empty($output_array[1][0])) {
            return $output_array[1][0];
        } else {
            return false;
        }
    }

    public function getErrors($input_lines) {
        preg_match_all("/ignored\serrors:[\s]+([\d]+)[\s]+/Ux", $input_lines, $output_array);

        if (isset($output_array[1][0])) {
            return $output_array[1][0];
        } else {
            return false;
        }
    }

    public function getTotalTime($input_lines) {
        preg_match_all("/total\stime:[\s]+([\S]+)[\s]+/Ux", $input_lines, $output_array);

        if (!empty($output_array[1][0])) {
            return str_replace("s", "", $output_array[1][0]);
        } else {
            return false;
        }
    }

    public function getReponseTimeMin($input_lines) {
        preg_match_all("/min:[\s]+([\S]+)[\s]+/Ux", $input_lines, $output_array);

        if (!empty($output_array[1][0])) {
            return str_replace("ms", "", $output_array[1][0]);
        } else {
            return false;
        }
    }

    public function getReponseTimeMax($input_lines) {
        preg_match_all("/max:[\s]+([\S]+)[\s]+/Ux", $input_lines, $output_array);

        if (!empty($output_array[1][0])) {
            return str_replace("ms", "", $output_array[1][0]);
        } else {
            return false;
        }
    }

    public function getReponseTimeAvg($input_lines) {
        preg_match_all("/avg:[\s]+([\S]+)[\s]+/Ux", $input_lines, $output_array);

        if (!empty($output_array[1][0])) {

            return str_replace("ms", "", $output_array[1][0]);
        } else {
            return false;
        }
    }

    public function getReponseTime95percent($input_lines) {
        preg_match_all("/95\spercentile:[\s]+([\S]+)[\s]+/Ux", $input_lines, $output_array);

        if (!empty($output_array[1][0])) {
            str_replace("ms", "", $output_array[1][0]);
        } else {
            return false;
        }
    }

    public function testMoc() {
        $this->view = false;
        $data = $this->moc();


        echo "Nombre de read : " . $this->getQueriesPerformedRead($data) . "\n";
        echo "Nombre de write : " . $this->getQueriesPerformedWrite($data) . "\n";
        echo "Nombre de other : " . $this->getQueriesPerformedOther($data) . "\n";
        echo "Nombre de total : " . $this->getQueriesPerformedTotal($data) . "\n";


        echo "Nombre de transaction  : " . $this->getTransactions($data) . "\n";
        echo "Nombre de time : " . $this->getTotalTime($data) . "\n";
        echo "Nombre de min : " . $this->getReponseTimeMin($data) . "\n";
        echo "Nombre de max : " . $this->getReponseTimeMax($data) . "\n";
        echo "Nombre de avg : " . $this->getReponseTimeAvg($data) . "\n";
        echo "Nombre de percent95 : " . $this->getReponseTime95percent($data) . "\n";

// 13 benchmark_thread
    }

    public function moc() {
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

    public function index($param) {

        $this->title = '<i class="fa fa-tachometer"></i> ' . __("Benchmark");
        $this->ariane = '> <a href="' . LINK . 'plugins"><i class="fa fa-puzzle-piece"></i> ' . __("Plugins") . '</a> > ' . $this->title;

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT count(1) as cpt from benchmark_main where status != 'COMPLETED'";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $cpt = $ob->cpt;
        }


        $badge = "";
        if (!empty($cpt)) {
            $badge = ' <span class="badge">' . $cpt . '</span>';
        }

        $data['menu']['bench']['name'] = __('Make a new benchmark');
        $data['menu']['bench']['icone'] = '<i class="fa fa-clock-o" aria-hidden="true"></i>';
        $data['menu']['bench']['path'] = LINK .$this->getClass(). '/' . __FUNCTION__ . '/bench';

        $data['menu']['current']['name'] = __('Currents') . $badge;
        $data['menu']['current']['icone'] = '<i class="fa fa-refresh fa-spin" aria-hidden="true"></i>';
        $data['menu']['current']['path'] = LINK .$this->getClass(). '/' . __FUNCTION__ . '/current';

        $data['menu']['config']['name'] = __('Configuration');
        $data['menu']['config']['icone'] = '<i class="fa fa-wrench" aria-hidden="true"></i>';
        $data['menu']['config']['path'] = LINK .$this->getClass(). '/' . __FUNCTION__ . '/config';

        $data['menu']['graph']['name'] = __('Graphs');
        $data['menu']['graph']['icone'] = '<i class="fa fa-area-chart" aria-hidden="true"></i>';
        $data['menu']['graph']['path'] = LINK .$this->getClass(). '/' . __FUNCTION__ . '/graph';


        if (!empty($param[0])) {
            if (in_array($param[0], array('bench', 'current', 'config', 'graph'))) {
                $_GET['path'] = LINK .$this->getClass(). '/' . __FUNCTION__ . '/' . $param[0];
            }
        }

        if (empty($_GET['path']) && empty($param[0])) {
            $_GET['path'] = $data['menu']['graph']['path'];
            $param[0] = 'graph';
        }

        if (empty($_GET['path'])) {
            $_GET['path'] = 'graph';
        }

        $this->set("data", $data);
    }

    public function testError($input_lines) {
        $pos = strpos($input_lines, "FATAL:");

        if ($pos !== false) {
            return true;
        }
        return false;
    }

    public function saveVariables($id_benchmark_main) {
        $name_server = $param[0];
        $id_server = $param[1];

        $mysql_tested = $this->di['db']->sql($name_server);

        $db = $this->di['db']->sql(DB_DEFAULT);

        $variables = $mysql_tested->getVariables();
    }

    public function install() {
        
    }

    public function uninstall() {
        
    }

    public function graph() {

        $db = $this->di['db']->sql(DB_DEFAULT);
        $this->di['js']->addJavascript(array("Chart.min.js"));

        $data = array();

        if ($_SERVER['REQUEST_METHOD'] == "POST") {

            if (!empty($_POST['benchmark'])) {

                if (!empty($_POST['benchmark_main']['id'])) {
                    $ret = "";
                    $ret .= "benchmark/index/benchmark_main:id:" . json_encode($_POST['benchmark_main']['id']);
                } else {

                    $ret = "";
                    $ret .= "benchmark/index/";
                }

                header("location: " . LINK . $ret);

                exit;
            }
        }




        if (!empty($_GET['benchmark_main']['id'])) {
            $id_to_take = implode(",", json_decode($_GET['benchmark_main']['id']));
        } else {
            $sql = "SELECT max(id) as id FROM `benchmark_main` WHERE status = 'COMPLETED'";
            $res = $db->sql_query($sql);

            while ($ob = $db->sql_fetch_object($res)) {
                $id_to_take = $ob->id;
                $_GET['benchmark_main']['id'] = json_encode(array($id_to_take));
            }
        }




        if (empty($id_to_take)) {
            $this->set("data", $data);
            return;
        }


        $sql = "select a.*,b.display_name from `benchmark_main` a
         INNER JOIN mysql_server b ON a.id_mysql_server = b.id
         ORDER BY a.date_end DESC LIMIT 100";
        $res = $db->sql_query($sql);


        $data['select_bench'] = array();

        while ($ob = $db->sql_fetch_object($res)) {

            $tmp = array();

            $tmp['id'] = $ob->id;
            $tmp['libelle'] = $ob->display_name . " (" . $ob->date_end . ")";

            $data['select_bench'][] = $tmp;
        }

        $sql = "SELECT a.display_name, b.`date`,b.id
            FROM mysql_server a
            INNER JOIN `benchmark_main` b ON a.id = b.id_mysql_server
            WHERE b.id IN (" . $id_to_take . ")
            ";
        $res = $db->sql_query($sql);

        $benchmark = array();
        while ($ob = $db->sql_fetch_object($res)) {
            $benchmark[$ob->id] = $ob->display_name . " (" . $ob->date . ")";
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
            where `id_benchmark_main` in (" . $id_to_take . ") GROUP BY id_benchmark_main;";


        $res = $db->sql_query($sql);

        $threads = [];
        $reads = [];
        $write = [];
        $reponse_time = [];
        $transaction = [];
        $error = [];

        while ($ob = $db->sql_fetch_object($res)) {

            $threads[] = $ob->thread;
            $results["Reads by second"][$ob->id_benchmark_main] = $ob->read;
            $results["Writes by second"][$ob->id_benchmark_main] = $ob->write;
            $results["Response Time (ms)"][$ob->id_benchmark_main] = $ob->reponse_avg;
            $results["Transactions by second"][$ob->id_benchmark_main] = $ob->transaction;
            $results["Errors"][$ob->id_benchmark_main] = $ob->error;
            $results["Ratio"][$ob->id_benchmark_main] = $ob->ratio;
        }

        $val = 0;
        $absisse = "";
        foreach ($threads as $thread) {
            if (strlen($thread) > strlen($absisse)) {
                $absisse = $thread;
            }
        }

        $i = 1;


        if (!empty($results)) {
            foreach ($results as $title => $result) {

                $js = 'var ctx' . $i . ' = document.getElementById("graph' . $i . '");

            var myChart' . $i . ' = new Chart(ctx' . $i . ', {
                type: "line",
                data: {
                    labels: [' . $absisse . '],
                    datasets: [';

                $j = 0;
                $tmp = '';
                foreach ($result as $server => $res_by_server) {

                    $j = $j % count($this->colors);

                    $tmp[] .= '
                {
                    label: "' . $benchmark[$server] . '",
                    data: [' . $res_by_server . '],
                    backgroundColor: "rgba(' . $this->colors[$j] . ',' . self::SHADOW . ')",
                    borderColor: "rgba(' . $this->colors[$j] . ',1)",
                }';

                    $j++;
                }

                $js .= implode(',', $tmp);

                $js .= ']
                },
                options: {
                    title: {
                        display: true,
                        text: "' . $title . '",
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

    public function config() {
        $db = $this->di['db']->sql(DB_DEFAULT);
    }

    public function bench($param) {

        Debug::parseDebug($param);


        $db = $this->di['db']->sql(DB_DEFAULT);

        if ($_SERVER['REQUEST_METHOD'] == "POST") {

            if (!empty($_POST['benchmark'])) {
                if (!empty($_POST['mysql_server']['id'])) {

                    //boucler sur tous les cas à prévoir

                    foreach ($_POST['mysql_server']['id'] as $id_mysql_server) {

                        $sql = "INSERT INTO benchmark_main
                    SET id_mysql_server = '" . $id_mysql_server . "',
                    id_user_main = '" . $this->di['auth']->getuser()->id . "',    
                    date = '" . date("Y-m-d H:i:s") . "',
                    sysbench_version = '" . shell_exec("sysbench --version") . "',
                    threads = '" . implode(',', $_POST['benchmark_main']['threads']) . "',
                    tables_count = '" . $_POST['benchmark_main']['tables_count'] . "',
                    table_size = '" . $_POST['benchmark_main']['tables_count'] . "',
                    mode = '" . $_POST['benchmark_main']['mode'] . "',
                    read_only = '" . $_POST['benchmark_main']['read_only'] . "',
                    max_time = '" . $_POST['benchmark_main']['max_time'] . "',
                    status = 'NOT STARTED',
                    date_start='0000-00-00 00:00:00',
                    date_end='0000-00-00 00:00:00',
                    progression=0
                    ";

                        $db->sql_query($sql);
                    }

                    $sql = "SELECT * FROM benchmark_config where id=1";

                    $res = $db->sql_query($sql);


                    // system de queue
                    while ($ob = $db->sql_fetch_object($res)) {

                        $start_queue = true;
                        if (!empty($ob->pid)) {
                            $cmd = "ps -p " . $ob->pid;
                            $alive = shell_exec($cmd);

                            if (strpos($alive, $ob->pid) !== false) {
                                $start_queue = false;
                            }
                        }

                        if ($start_queue) {

                            $php = explode(" ", shell_exec("whereis php"))[1];
                            $cmd = $php . " " . GLIAL_INDEX . " Benchmark queue >> /tmp/queue & echo $!";

                            $pid = 0;
                            $pid = shell_exec($cmd);

                            $sql = "UPDATE `benchmark_config` SET pid = '" . $pid . "' WHERE id = 1";
                            $db->sql_query($sql);


                            set_flash("success", "Daemon", "Benchmark started in background, check onglet current");
                        } else {
                            set_flash("caution", "Daemon", "Daemon already started, benchmark added in queue, check onglet current");
                        }
                    }
                } else {
                    set_flash("error", "Server", "Please select the server(s) you want to bench");


                    header("location: " . LINK .$this->getClass(). "/index/" . __FUNCTION__);
                }
            }
        }


        // version de sysbench
        $data['sysbench'] = $this->getSysbenchVersion();

        // chargement de la config
        $sql = "SELECT * FROM benchmark_config where id=1";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            $_GET['benchmark_main']['threads'] = json_encode(explode(",", $ob->threads));
            $_GET['benchmark_main']['tables_count'] = $ob->tables_count;
            $_GET['benchmark_main']['table_size '] = $ob->table_size;
            $_GET['benchmark_main']['max_time'] = $ob->max_time;
            $_GET['benchmark_main']['mode'] = $ob->mode;
            $_GET['benchmark_main']['read_only'] = $ob->read_only;
        }

//debug($_GET['benchmark_main']);
// get server available
        // génération des <select></select>
        $sql = "SELECT * FROM mysql_server a WHERE error = '' " . $this->getFilter() . " order by a.name ASC";
        $res = $db->sql_query($sql);
        $data['servers'] = array();
        while ($ob = $db->sql_fetch_object($res)) {
            $tmp = [];
            $tmp['id'] = $ob->id;
            $tmp['libelle'] = $ob->name . " (" . $ob->ip . ")";
            $data['servers'][] = $tmp;
        }

        $data['treads'] = array();

        for ($i = 0; $i < 1024; $i++) {
            $tmp = array();
            $tmp['id'] = ($i + 1);
            $tmp['libelle'] = ($i + 1);
            $data['treads'][] = $tmp;
        }


        $data['tables_count'] = array();

        for ($i = 0; $i < 100; $i++) {
            $tmp = array();
            $tmp['id'] = ($i + 1);
            $tmp['libelle'] = ($i + 1);
            $data['tables_count'][] = $tmp;
        }

        $modes = array("simple", "complex", "nontrx");

        $data['test_mode'] = array();
        foreach ($modes as $mode) {
            $tmp = array();
            $tmp['id'] = $mode;
            $tmp['libelle'] = $mode;
            $data['test_mode'][] = $tmp;
        }

        $read_o = array("ON", "OFF");

        $data['read_only'] = array();
        foreach ($read_o as $mode) {
            $tmp = array();
            $tmp['id'] = $mode;
            $tmp['libelle'] = $mode;
            $data['read_only'][] = $tmp;
        }

        $data['max_time'] = array();

        for ($i = 0; $i < 300; $i++) {
            $tmp = array();
            $tmp['id'] = ($i );
            $tmp['libelle'] = ($i );
            $data['max_time'][] = $tmp;
        }


        $this->set("data", $data);
    }

    public function current() {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT *,a.id as id_benchmark_main FROM benchmark_main a
             INNER JOIN mysql_server b on a.id_mysql_server = b.id
             ORDER BY a.id desc limit 50";

        $res = $db->sql_query($sql);

        $data['current'] = array();
        while ($ob = $db->sql_fetch_array($res)) {
            $data['current'][] = $ob;
        }


        $sql = "SELECT b.id_benchmark_main, count(1) as cpt FROM benchmark_main a
            INNER JOIN benchmark_run b ON a.id = b.id_benchmark_main WHERE a.status = 'RUNNING' GROUP BY b.id_benchmark_main;";
        $res = $db->sql_query($sql);


        $data['running'] = array();
        while ($ob = $db->sql_fetch_object($res)) {
            $data['running'][$ob->id_benchmark_main] = $ob->cpt;
        }



        $this->set('data', $data);
    }

    // to move
    private function getFilter() {

        $where = "";


        if (!empty($_GET['environment']['libelle'])) {
            $environment = $_GET['environment']['libelle'];
        }
        if (!empty($_SESSION['environment']['libelle']) && empty($_GET['environment']['libelle'])) {
            $environment = $_SESSION['environment']['libelle'];
            $_GET['environment']['libelle'] = $environment;
        }

        if (!empty($_SESSION['client']['libelle'])) {
            $client = $_SESSION['client']['libelle'];
        }
        if (!empty($_GET['client']['libelle']) && empty($_GET['client']['libelle'])) {
            $client = $_GET['client']['libelle'];
            $_GET['client']['libelle'] = $client;
        }


        if (!empty($environment)) {
            $where .= " AND a.id_environment IN (" . implode(',', json_decode($environment, true)) . ")";
        }

        if (!empty($client)) {
            $where .= " AND a.id_client IN (" . implode(',', json_decode($client, true)) . ")";
        }


        return $where;
    }

    public function queue($param) {
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

        $db = $this->di['db']->sql(DB_DEFAULT);

        Debug::parseDebug($param);


        //$sql3 = "UPDATE `benchmark_main` SET `status` = 'NOT STARTED' WHERE `status` = 'LAUNCHED';";
        //$db->sql_query($sql3);
        //Debug::debug(trim(SqlFormatter::highlight($sql3)));


        $sql = "SELECT * FROM `benchmark_main` WHERE `status` = 'NOT STARTED' limit 1;";
        $res = $db->sql_query($sql);
        Debug::debug(trim(SqlFormatter::highlight($sql)));

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
                $cmd = $php . " " . GLIAL_INDEX . " Benchmark run " . $id_benchmark_main . " " . $debug . "";


                Debug::debug($cmd);

                $err = 1;
                echo passthru($cmd, $err);

                Debug::debug("return : " . $err);


                $db = $this->di['db']->sql(DB_DEFAULT);
                $res = $db->sql_query($sql);
                while ($ob = $db->sql_fetch_object($res)) {
                    $id_benchmark_main = $ob->id;

                    $sql2 = "UPDATE benchmark_main SET status = 'LAUNCHED' WHERE id ='" . $id_benchmark_main . "';";
                    $db->sql_query($sql2);

                    Debug::debug(trim(SqlFormatter::highlight($sql2)));
                }
            } while ($db->sql_num_rows($res) > 0);
        } else {
            Debug::debug("No bench to do !");
        }




        $sql = "UPDATE `benchmark_config` SET pid = '0' WHERE id = 1";
        $db->sql_query($sql);
    }

    public function debug($string) {
        if (Debug::$debug) {
            $calledFrom = debug_backtrace();
            $file = pathinfo(substr(str_replace(ROOT, '', $calledFrom[0]['file']), 1))["basename"];
            $line = $calledFrom[0]['line'];

            $file = explode(".", $file)[0];

            echo "#" . $this->count++ . "\t";
            echo $file . ":" . $line . "\t";
            echo \Glial\Cli\Color::getColoredString("[" . date('Y-m-d H:i:s') . "]", "purple") . " ";
            echo $string . "\n";
        }
    }

    public function getSysbenchVersion() {
        $data['sysbench'] = "N/A";
        $version = shell_exec("sysbench --version 2> /dev/null");

        if (!empty($version)) {
            $elems = explode(" ", $version);
            $data['sysbench'] = trim($elems[1]);
        }

        return $data['sysbench'];
    }

}
