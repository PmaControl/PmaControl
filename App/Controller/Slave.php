<?php
/*
 * j'ai ajouté un mail automatique en cas d'erreur ou de manque sur une PK
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use App\Library\Extraction;
use App\Library\Mysql;
use App\Library\Debug;
use \Glial\Sgbd\Sgbd;
use \App\Library\Chiffrement;
use \App\Library\Available;

class Slave extends Controller
{

    use \App\Library\Filter;
    const BACKUP_TEMP = "/backup/";

    public function index()
    {

        $this->title = '<i class="fa fa-sitemap"></i> '.__("Master / Slave");

        $db = Sgbd::sql(DB_DEFAULT);

        $this->di['js']->code_javascript('
        $(function () {
  $(\'a[data-toggle="tooltip"]\').tooltip();  /* tooltip("show") */
  $(\'[data-toggle="tooltip"]\').tooltip();
})');

        $data['slave'] = Extraction::display(array("slave::master_host", "slave::master_port", "slave::seconds_behind_master", "slave::slave_io_running",
                "slave::slave_sql_running", "slave::replicate_do_db", "slave::replicate_ignore_db", "slave::last_io_errno", "slave::last_io_error",
                "slave::last_sql_error", "slave::last_sql_errno"));


                die();

        /* besoin de testé avec les thread (trouver autre chose)
          //order by master host
          function invenDescSort($item1, $item2)
          {
          if (substr($item1['']['master_host'], 6)
          == substr($item2['']['master_host'], 6)) return 0;
          return (substr($item1['']['master_host'], 6)
          < substr($item2['']['master_host'], 6)) ? 1 : -1;
          }
          usort($data['slave'], 'invenDescSort');
         */

        $data['info_server'] = Extraction::display(array("variables::hostname", "variables::is_proxysql", "mysql_available"));

        

        $sql = "SELECT a.*, c.libelle as client,d.libelle as environment,d.`class`  FROM mysql_server a
                 INNER JOIN client c on c.id = a.id_client
                 INNER JOIN environment d on d.id = a.id_environment
                 WHERE 1 ".self::getFilter()."
                 ORDER by `name`;";

        $res = $db->sql_query($sql);

        $data['server'] = array();
        while ($arr            = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {


            $data['server']['master'][$arr['ip'].':'.$arr['port']] = $arr;
            $data['server']['master'][$arr['id']]                  = $arr;
            $data['server']['slave'][$arr['id']]                   = $arr;
        }

        /*
          $res2 = Extraction::extract(array("slave::seconds_behind_master"), array(), "1 hour", false, true);
          $slaves = array();
          while($slave = $db->sql_fetch_array($res2, MYSQLI_ASSOC))
          {
          $slaves[] = $slave;
          }
          $this->generateGraph($slaves);
         */

        $slaves = Extraction::extract(array("slave::seconds_behind_master"), array(), "1 hour", false, true);
        $this->generateGraph($slaves);

        if (!empty($slaves)) {
            foreach ($slaves as $slave) {
                $data['graph'][$slave['id_mysql_server']][$slave['connection_name']]             = $slave;
                $data['graph'][$slave['id_mysql_server']][$slave['connection_name']]['id_graph'] = $slave['id_mysql_server'].crc32($slave['connection_name']);
                $data['server']['idgraph'][$slave['id_mysql_server']][$slave['connection_name']] = $slave['id_mysql_server'].crc32($slave['connection_name']);
            }
        }

        $this->set('data', $data);
    }

    private function generateGraph($slaves)
    {
        $this->di['js']->addJavascript(array("moment.js", "Chart.bundle.js"));

        if (!empty($slaves)) {
            foreach ($slaves as $slave) {

                $this->di['js']->code_javascript('

Chart.defaults.global.legend.display = false;

var ctx = document.getElementById("myChart'.$slave['id_mysql_server'].crc32($slave['connection_name']).'").getContext("2d");

var myChart'.$slave['id_mysql_server'].crc32($slave['connection_name']).' = new Chart(ctx, {
    type: "line",
    data: {
        labels: [],
        datasets: [{
            fill: true,
            fillColor : "rgba(255,255, 0, 1)",
            data: ['.$slave['graph'].'],
                borderColor: "rgba(0,0, 0, 1)",
                borderWidth: 2,
             pointRadius :0,
             lineTension: 0,
        },
]
    },

    gridLines: {
            display: false,
            drawBorder: false
    },
    options: {
        gridLines: {
                display: false,
                drawBorder: false
        },
        drawBorder: false,


         tooltips: {
            enabled: false
         },

        bezierCurve: false,

        scales: {
            xAxes: [{

                gridLines : {
                    display : false,
                    drawBorder: false

                },
                type: "time",
                display: false,
                scaleLabel: {
                  display: false,

                },
                distribution: "linear",


            }],
             yAxes: [{
             display: false,
             gridLines : {
                    display : false,
                    drawBorder: false
                },
            ticks: {
                    beginAtZero:true
                }

    }]
        }
    }
});



');
            }
        }
    }

    public function show($param)
    {

        $this->title = '<i class="fa fa-sitemap"></i> '.__("Slave status");

        $db = Sgbd::sql(DB_DEFAULT);

//debug($db);

        $id_mysql_server  = $param[0];
        $replication_name = $param[1];

        $data['id_mysql_server']  = $id_mysql_server;
        $data['replication_name'] = $replication_name;

        $sql = "SELECT * from mysql_server where id = ".$id_mysql_server.";";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $server = $ob;
        }

        $data['slave'] = array();

        $data['server'] = Extraction::display(array("mysql_server::mysql_available"));

        //debug($data['server'][$server['id']]['']['mysql_available']);

        if ($data['server'][$server['id']]['']['mysql_available'] === "1") {
            $link_slave = Sgbd::sql($server['name']);

            
            $slaves = $link_slave->isSlave();

            
            if (count($slaves) === 1) {
                $slave = end($slaves);
            } else {

                foreach ($slaves as $option) {
                    if ($option['Connection_name'] === $replication_name) {
                        $slave = $option;
                    }
                }
            }

            $data['slave'] = $slave;
        }
        ksort($data['slave']);

        $data['replication_name'] = $replication_name;
        Extraction::setOption('groupbyday', true);

        $date        = date('Y-m-d H:i:s');
        $date_format = 'Y-m-d';

        $array_date = date_parse_from_format($date_format, $date);

        $more_days = -1;
        $next_date = date(
            $date_format, mktime(0, 0, 0, $array_date['month'], $array_date['day'] + $more_days, $array_date['year'])
        );

        $slaves = Extraction::extract(array("slave::seconds_behind_master"), array($id_mysql_server), array($next_date, $date), true, true);
        
        $this->generateGraphSlave($slaves);

        foreach ($slaves as $slave) {
            $data['graph'][$slave['day']] = $slave;
        }

        $sql = "WITH LastCluster AS (
        SELECT id_dot3_cluster
        FROM dot3_cluster__mysql_server
        WHERE id_mysql_server = ".$id_mysql_server."
        ORDER BY date_inserted DESC
        LIMIT 1
        )
        SELECT id_mysql_server
        FROM dot3_cluster__mysql_server
        WHERE id_dot3_cluster = (SELECT id_dot3_cluster FROM LastCluster);";
        


//change master


        $res = $db->sql_query($sql);

        $data['mysql_server_specify'] = array();
        while ($ob                           = $db->sql_fetch_object($res)) {
            $data['mysql_server_specify'][] = $ob->id_mysql_server;
        }

// find master
        $_GET['mysql_server']['id'] = Mysql::getMaster($id_mysql_server, $replication_name);

        $data['id_slave']              = array($id_mysql_server);
        $_GET['mysql_slave']['server'] = $id_mysql_server;


       // debug($_GET['mysql_server']['id']);
       
//le cas ou on arrive pas a trouver le master

/*
if (!empty($_GET['mysql_server']['id'])) {
    $db_master = Mysql::getDbLink($_GET['mysql_server']['id']);
    //exit;
    $sql  = "show databases;";
    $res7 = $db_master->sql_query($sql);

    $data['db_on_master'] = array();
    $i                    = 0;
    while ($ob7                  = $db->sql_fetch_object($res7)) {
        if (in_array($ob7->Database, array('information_schema', 'performance_schema', 'mysql', 'sys'))) {
            continue;
        }

        if ($i > 1) {
            $data['db_on_master'][] = "...";
            break;
        }

        $data['db_on_master'][] = $ob7->Database;
        $i++;
    }
}*/
        
//gtid
// https://mariadb.com/fr/node/493
// https://mariadb.com/kb/en/library/gtid/

        $data['class']    = $this->getClass();
        $data['function'] = __FUNCTION__;

        $this->set('data', $data);
    }

    public function box()
    {

        $db = Sgbd::sql(DB_DEFAULT);

        $data['slave'] = Extraction::display(array("slave::master_host", "slave::master_port", "slave::seconds_behind_master", "slave::slave_io_running",
                "slave::slave_sql_running", "slave::last_io_errno", "slave::last_io_error",
                "slave::last_sql_error", "slave::last_sql_errno"));

        $sql = "SELECT a.*, c.libelle as client,d.libelle as environment,d.`class`,a.is_available  FROM mysql_server a
                 INNER JOIN client c on c.id = a.id_client
                 INNER JOIN environment d on d.id = a.id_environment
                 WHERE 1 ".self::getFilter()."
                 ORDER by `name`;";

        $res = $db->sql_query($sql);

        $data['server'] = array();
        while ($arr            = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['server']['master'][$arr['ip'].':'.$arr['port']] = $arr;
            $data['server']['master'][$arr['id']]                  = $arr;
            $data['server']['slave'][$arr['id']]                   = $arr;
        }

//debug($data['slave']);

        $data['box'] = array();

        foreach ($data['slave'] as $id_mysql_server => $slaves) {
            foreach ($slaves as $connect_name => $slave) {
                if ($slave['slave_sql_running'] !== "Yes" || $slave['slave_io_running'] !== "Yes" || $slave['seconds_behind_master'] !== "0"
                ) {
                    $export = array();

                    if (empty($data['server']['master'][$slave['master_host'].':'.$slave['master_port']])) {
                        $data['server']['master'][$slave['master_host'].':'.$slave['master_port']]['display_name'] = "Unknow ".$slave['master_host'].':'.$slave['master_port'];

                        if ($slave['slave_io_running'] === 'Yes') {
                            $data['server']['master'][$slave['master_host'].':'.$slave['master_port']]['is_available'] = "1";
                        } else {
                            $data['server']['master'][$slave['master_host'].':'.$slave['master_port']]['is_available'] = "0";
                        }
                    }

                    $export['master']            = $data['server']['master'][$slave['master_host'].':'.$slave['master_port']];
                    $export['slave']             = $data['server']['slave'][$id_mysql_server];
                    $export['connect']           = $connect_name;
                    $export['seconds']           = $slave['seconds_behind_master'];
                    $export['slave_sql_running'] = $slave['slave_sql_running'];
                    $export['slave_io_running']  = $slave['slave_io_running'];
                    $export['slave_sql_error']   = $slave['last_sql_error'];
                    $export['slave_io_error']    = $slave['last_io_error'];
                    $export['slave_sql_errno']   = $slave['last_sql_errno'];
                    $export['slave_io_errno']    = $slave['last_io_errno'];

                    $data['box'][] = $export;
                }
            }
        }


        $this->set('data', $data);
//debug($data['box']);
    }

    private function generateGraphSlave($slaves)
    {
        $this->di['js']->addJavascript(array("moment.js", "Chart.bundle.js"));

        foreach ($slaves as $slave) {

            $this->di['js']->code_javascript('

Chart.defaults.global.legend.display = false;

var ctx = document.getElementById("myChart'.$slave['id_mysql_server'].crc32($slave['day']).'").getContext("2d");

var myChart'.$slave['id_mysql_server'].crc32($slave['connection_name']).' = new Chart(ctx, {

    type: "line",
    data: {
        datasets: [{
            label: "'.__('Second behind master').'",
            data: ['.$slave['graph'].'],
                borderWidth: 1,
             pointRadius :1,
             lineTension: 0

        },
]
    },
    options: {
        bezierCurve: false,
        title: {
            display: true,
            text: "Replication : '.$slave['day'].'",
            position: "top",
            padding: "0"
        },
        pointDot : false,
        scales: {
            xAxes: [{

                type: "time",
                display: true,
                scaleLabel: {
                  display: true,
                  labelString: "Date",
                },
                distribution: "linear",
                time: {

                    min: new Date("'.$slave['day'].' 00:00:00"),
                    max: new Date("'.$slave['day'].' 23:59:59"),
                    tooltipFormat: "dddd YYYY-MM-DD, HH:mm:ss",
                    displayFormats: {
          minute: "HH:mm"
        }

                }

            }],
             yAxes: [{
             ticks: {
         beginAtZero:false,
      },

      scaleLabel: {
        display: true,
        labelString: "Second behind master",

      }

    }]
        }
    }
});


');
        }
    }

    public function startSlave($param)
    {
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $db = Mysql::getDbLink();
        }
    }

    public function setSlave($param)
    {

        //add param force
        Debug::parseDebug($param);

        Debug::debug($param);

        $id_mysql_server__source = $param[0];
        $id_mysql_server__target = $param[1];
        $databases               = $param[2];

        $databases = explode(',', $databases);

        Debug::debug($databases, "database");

        //db source add account for replication
        $cmd2 = "openssl rand -base64 32";
        Debug::debug($cmd2);

        $slave_password = trim(shell_exec($cmd2));
        $slave_user     = "replication";

        $db_source = Mysql::getDbLink($id_mysql_server__source);
        $db_source->sql_query("GRANT REPLICATION SLAVE, BINLOG MONITOR ON *.* TO `".$slave_user."`@`%` IDENTIFIED BY '".$slave_password."'");
        $db_source->sql_close();
        //end create user replication


        $db        = Sgbd::sql(DB_DEFAULT);
        $source    = $this->getInfoServer($id_mysql_server__source);
        $db_passwd = Chiffrement::decrypt($source->passwd);
        $db->sql_close();

        foreach ($databases as $database) {

            $dir = self::BACKUP_TEMP.$source->display_name."/".$database;
            shell_exec("mkdir -p ".$dir);
            $cmd = "mydumper -c -ERG --trx-consistency-only -h ".$source->ip." -u ".$source->login." -p ".$db_passwd." -B ".$database." -o '".$dir."'";
            Debug::debug($cmd, "Mydumper");

            sleep(1);
            shell_exec($cmd);

            //$pid_array[] = $this->runInBackground($cmd, );
        }

        sleep(1);

        $slaves = Mysql::getSlave(array($id_mysql_server__target));

        $servers = array_merge($slaves['slave'], array($id_mysql_server__target));

        $pid_array = array();
        foreach ($servers as $server) {

            $target = $this->getInfoServer($server);

            $db_passwd = Chiffrement::decrypt($target->passwd);

            foreach ($databases as $database) {


                $dir = self::BACKUP_TEMP.$source->display_name."/".$database;

                $cmd = "myloader -h ".$target->ip." -u ".$target->login." -p ".$db_passwd." -o -d '".$dir."'";
                Debug::debug($cmd);

                $pid_array[] = $this->runInBackground($cmd, "/tmp/".$target->ip."-".$database.'.log');
            }

            Debug::debug("------");
        }


        Debug::debug($pid_array, 'pid');

        do {

            sleep(1);

            $finished = true;
            foreach ($pid_array as $pid) {
                if ($this->isProcessRunning($pid) === false) {
                    $finished = false;
                    Debug($pid, "finished");
                }
            }
        } while ($finished);

        Debug::debug("All myloader finished");

        $db_target = Mysql::getDbLink($id_mysql_server__target);

        $sql = "STOP SLAVE;";
        Debug::sql($sql);
        $db_target->sql_query($sql);
        $sql = "RESET SLAVE ALL;";
        Debug::sql($sql);
        $db_target->sql_query($sql);

        $i = 0;
        foreach ($servers as $server) {

            $i++;
            // $dir_backup = self::BACKUP_TEMP.$source->display_name." / ".$database.";
            $dir = self::BACKUP_TEMP.$source->display_name."/".$database;
            Debug::debug($dir_backup, "backup_dir");

            $master_info = $this->getMasterInfo(array($dir.'/metadata'));

            if ($i === 1) {

                $sql = "CHANGE MASTER TO MASTER_HOST='".$source->ip."',MASTER_USER='".$slave_user."', MASTER_PASSWORD='".$slave_password."', MASTER_LOG_FILE='".$master_info['master_log_file']."', MASTER_LOG_POS=".$master_info['master_log_pos'].";";
            } else {
                $sql = "START SLAVE UNTIL MASTER_LOG_FILE='".$master_info['master_log_file']."', MASTER_LOG_POS=".$master_info['master_log_pos'].";";
            }

            Debug::sql($sql);
            $db_target->sql_query($sql);
        }

        $sql = "STOP SLAVE;";
        Debug::sql($sql);
        $db_target->sql_query($sql);

        $sql = "START SLAVE;";
        Debug::sql($sql);
        $db_target->sql_query($sql);

        // set up replication
    }

    private function getInfoServer($id_mysql_server)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server where id=".$id_mysql_server.";";
        Debug::sql($sql);

        $res = $db->sql_query($sql);

        $data = false;
        while ($ob   = $db->sql_fetch_object($res)) {
            $data = $ob;
        }

        return $data;
    }

    function runInBackground($command, $log, $priority = 0)
    {
        if ($priority) {
            $PID = trim(shell_exec("nohup nice -n $priority $command > $log 2>&1 & echo $!"));
        } else {
            $PID = trim(shell_exec("nohup $command > $log 2>&1 & echo $!"));
        }


        return($PID);
    }

    function isProcessRunning($PID)
    {

        if ($PID == 0) {
            return false;
        }
        if ($PID == "") {
            return false;
        }

        $cmd = "ps -p $PID 2>&1 ";
        Debug::debug($cmd);
        exec($cmd, $state);

        Debug::debug($state, "STATE");
        Debug::debug(count($state), "STATE");
        var_dump(count($state) >= 2);
        return ( count($state) >= 2);
    }

    // /srv/backup/export-20220927-162928
    /*
     * lis les paramètre du fichier metadata (master info)
     *
     *
     */
    function getMasterInfo($param)
    {
        Debug::parseDebug($param);

        $file_metadata = $param[0];

        $metadata = file_get_contents($file_metadata);

        $output_array = array();
        preg_match_all('/SHOW MASTER STATUS\:\s+Log:\s(\S+)\s+Pos:\s(\S+)/', $metadata, $output_array);

        $data = array();

        if (!empty($output_array[1][0])) {
            $data['master_log_file'] = $output_array[1][0];
        }

        if (!empty($output_array[2][0])) {
            $data['master_log_pos'] = $output_array[2][0];
        }

        Debug::debug($data, "MASTER STATUS");

        return $data;
    }

    function switchOver($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server__old_master = $param[0];
        $id_mysql_server__new_master = $param[1];

        $slaves_old_master = Mysql::getSlave(array($id_mysql_server__old_master));
        $slaves_new_master = Mysql::getSlave(array($id_mysql_server__new_master));

        if (!in_array($id_mysql_server__new_master, $slaves_old_master['id_mysql_server'])) {
            throw new \Exception("Cannot find the new master in salve of old one");
        }

        $old_master = getDbLink($id_mysql_server__old_master);
        $new_master = getDbLink($id_mysql_server__new_master);

        $sql = "SHOW MASTER STATUS";
        Debug::debug($sql);

        $res = $new_master->sql_query($sql);

        while ($arr = $new_master->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $master_log_file = $arr['File'];
            $master_log_pos  = $arr['Position'];
        }

        $name = "gcp-prod-oos-sql-001-mariadb-g04-003.gcp.dlns.io";

        $output_array = array();
        preg_match('/(.*)-g([0-9]{2})\-([0-9]{3})/', $name, $output_array);

        if (empty($output_array[1])) {
            throw new \Exception("Impossible to find name");
        }

        if (empty($output_array[2])) {
            throw new \Exception("number of clsuter");
        }
        $connection_name = $output_array[1].'g'.$output_array[2];

        $sql2 = "CHANGE MASTER '' TO MASTER_HOST='', MASTER_USER='', MASTER_PASSWORD=''";

        $sql3 = "CHANGE MASTER '' TO MASTER_LOG_FILE='".$master_log_file."', MASTER_LOG_POS='.$master_log_pos.'";

        //test if old_master is on proxysql
    }
}