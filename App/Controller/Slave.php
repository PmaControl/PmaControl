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
use \App\Library\DryRun;

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

        $more_days = -5;
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
        $this->view = false;

        $id_mysql_server = $param[0];
        $connection_name = $param[1];

        $db = Mysql::getDbLink($id_mysql_server);

        //MySQL 
        $sql = "START SLAVE FOR CHANNEL ''";  

        //MariaDB
        $sql = "START SLAVE '".$connection_name."'";  

        if (empty($connection_name)) {
            $sql = "START SLAVE;";
        }

        $db->sql_query($sql);

        $title = "Success";
        $msg = $sql;
        set_flash("success", $title, $msg);

        if (! IS_CLI){
            header('location: '.LINK.'slave/show/'.$id_mysql_server.'/'.$connection_name.'/');
        }
    }

    public function stopSlave($param)
    {

        $this->view = false;

        $id_mysql_server = $param[0];
        $connection_name = $param[1];

        $db = Mysql::getDbLink($id_mysql_server);

        //MySQL 
        $sql = "STOP SLAVE FOR CHANNEL ''";  

        //MariaDB
        $sql = "STOP SLAVE '".$connection_name."'";  

        if (empty($connection_name)) {
            $sql = "STOP SLAVE;";
        }

        $db->sql_query($sql);

        $title = "Success";
        $msg = $sql;
        set_flash("success", $title, $msg);

        if (! IS_CLI){
            header('location: '.LINK.'slave/show/'.$id_mysql_server.'/'.$connection_name.'/');
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
            throw new \Exception("number of cluster");
        }
        $connection_name = $output_array[1].'g'.$output_array[2];

        $sql2 = "CHANGE MASTER '' TO MASTER_HOST='', MASTER_USER='', MASTER_PASSWORD=''";

        $sql3 = "CHANGE MASTER '' TO MASTER_LOG_FILE='".$master_log_file."', MASTER_LOG_POS='.$master_log_pos.'";

        //test if old_master is on proxysql
    }


    public function activateGtid($param)
    {
        $id_mysql_server = $param[0];
        $connection_name = $param[1];

        $this->view = false;

        $db = Mysql::getDbLink($id_mysql_server);

        if (! empty($connection_name))
        {
            $connection_name = " '$connection_name' ";
        }


        $sql = "STOP SLAVE $connection_name;";
        $db->sql_query($sql);

        $sql = "CHANGE MASTER $connection_name TO MASTER_USE_GTID = slave_pos;";
        $db->sql_query($sql);

        $sql = "START SLAVE $connection_name;";
        $db->sql_query($sql);


        $title = "Success";
        $msg = $sql;
        set_flash("success", $title, "GTID Activated");

        header('location: '.LINK.'slave/show/'.$id_mysql_server.'/'.$connection_name.'/');
        
    }


    public function deactivateGtid($param)
    {
        $id_mysql_server = $param[0];
        $connection_name = $param[1];

        $this->view = false;

        $db = Mysql::getDbLink($id_mysql_server);

        if (! empty($connection_name))
        {
            $connection_name = " '$connection_name' ";
        }


        $sql = "STOP SLAVE $connection_name;";
        $db->sql_query($sql);

        $sql = "CHANGE MASTER $connection_name TO MASTER_USE_GTID = no;";
        $db->sql_query($sql);

        $sql = "START SLAVE $connection_name;";
        $db->sql_query($sql);


        $title = "Success";
        $msg = $sql;
        set_flash("success", $title, "GTID Deactivated");

        header('location: '.LINK.'slave/show/'.$id_mysql_server.'/'.$connection_name.'/');
        
    }


    public function skipCounter($param)
    {
        $this->view = false;

        $id_mysql_server = $param[0];
        $connection_name = $param[1];

        $db = Mysql::getDbLink($id_mysql_server);


        if (! empty($connection_name))
        {
            $connection_name = " '$connection_name' ";
        }

        $sql = "STOP SLAVE $connection_name;";
        $db->sql_query($sql);

        $sql = "SET GLOBAL sql_slave_skip_counter=1;";
        $db->sql_query($sql);

        $sql = "START SLAVE $connection_name;";
        $db->sql_query($sql);


        if (! IS_CLI){
            usleep(5000);
            header('location: '.LINK.'slave/show/'.$id_mysql_server.'/'.$connection_name.'/');
        }

    }


    /**
     * Extrait le fichier binaire, la position et le nom de la base de données à partir d'une ligne de log.
     *
     * @param string $line Ligne de log au format "mariadb-bin.009564 2735165 /srv/backup/..."
     * @return array|null Tableau associatif avec les clés 'binlog_file', 'binlog_pos' et 'db_name', ou null si la ligne est invalide.
     */
    function extractBinlogInfo($line) {
        // Utilisation d'une expression régulière pour extraire les informations
        $pattern = '/^([^\s]+)\s+(\d+)\s+.+\/([^\/]+)_\d{4}-\d{2}-\d{2}_\d{2}h\d{2}m\.[^\.]+\.sql\.gz$/';
        if (preg_match($pattern, trim($line), $matches)) {
            return [
                'binlog_file' => $matches[1],
                'binlog_pos'  => $matches[2],
                'db_name'     => $matches[3],
            ];
        }
        return [];
    }



    /**
     * Regroupe les informations de binlog par fichier et position, avec un GROUP_CONCAT sur les noms de bases.
     *
     * @param array $extractedData Tableau d'informations extraites (binlog_file, binlog_pos, db_name).
     * @return array Tableau regroupé par binlog_file et binlog_pos, avec les db_name concaténés.
     */
    function groupBinlogInfoByPosition(array $extractedData) {
        $grouped = [];

        foreach ($extractedData as $entry) {
            $key = $entry['binlog_file'] . '|' . $entry['binlog_pos'];
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'binlog_file' => $entry['binlog_file'],
                    'binlog_pos'  => $entry['binlog_pos'],
                    'db_names'    => [],
                ];
            }
            $grouped[$key]['db_names'][] = $entry['db_name'];
        }

        // Concaténation des noms de bases avec une virgule
        foreach ($grouped as &$group) {
            $group['db_names'] = implode(', ', $group['db_names']);
        }

        //Debug::debug($grouped, "GROUPED");

        // Réindexer le tableau pour une sortie plus propre
        return array_values($grouped);
    }



    function processBinlogFile($param) {

        Debug::parseDebug($param);

        $filePath = $param[0] ?? '';

        if (!file_exists($filePath)) {
            throw new \Exception("Le fichier $filePath n'existe pas.");
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $extractedData = [];

        foreach ($lines as $line) {
            $info = $this->extractBinlogInfo($line);
            if ($info !== null) {
                $extractedData[] = $info;
            }
        }

        return $this->groupBinlogInfoByPosition($extractedData);
    }


    public function generateCmd($param)
    {
        Debug::parseDebug($param);
        $DRY_RUN = DryRun::parseDryRun($param);

        Debug::debug($DRY_RUN, "--dry-run");

        $elems = $this->processBinlogFile($param);

        $id_mysql_server__master = $param[1] ?? '';
        $id_mysql_server__slave = $param[2] ?? '';

        $master_host = $param[3] ?? '';

        $user = "replication_pmacontrol";
        $password = $this->generateSecurePassword();

        $master = "MASTER";
        $slave = "SLAVE";
        if (! $DRY_RUN) {
            $master = Mysql::getDbLink($id_mysql_server__master);
            $slave = Mysql::getDbLink($id_mysql_server__slave);
        }

        function execute($sql, $db, $DRY_RUN)
        {
            if ($DRY_RUN === true) {
                //echo "$db > $sql\n";
                Debug::sql($sql);
            }
            else{
                echo "[".date("Y-m-d H:i:s")."] $sql";
                $db->sql_query($sql);
            }
        }



        $sql = "GRANT REPLICATION SLAVE, REPLICATION CLIENT ON *.* TO '".$user."'@'%' IDENTIFIED BY '".$password."';";
        execute($sql, $master, $DRY_RUN);


        $databases = "";
        $i = 0;

        foreach($elems as $elem)
        {
            $i++;

            if ($i === 1)
            {
                $databases .=  $elem['db_names'];
                
                $sql = "RESET SLAVE ALL;";
                execute($sql, $slave, $DRY_RUN);

                $sql = "CHANGE MASTER TO MASTER_HOST='".$master_host."', MASTER_USER='".$user."', MASTER_PASSWORD='".$password."', 
                MASTER_SSL=0, MASTER_SSL_VERIFY_SERVER_CERT=0,
                MASTER_LOG_FILE='".$elem['binlog_file']."', MASTER_LOG_POS=".$elem['binlog_pos'].";";
                execute($sql, $slave, $DRY_RUN);
                

                $sql  = "SET GLOBAL replicate_do_db='".$databases."';";
                execute($sql, $slave, $DRY_RUN);
            }
            else{
                $databases .=  ",".$elem['db_names'];
                
                $sql = "START SLAVE UNTIL MASTER_LOG_FILE='".$elem['binlog_file']."', MASTER_LOG_POS=".$elem['binlog_pos'].";";
                execute($sql, $slave, $DRY_RUN);
                

                if ($DRY_RUN === false) {
                    $this->waitForSlavePosition([$id_mysql_server__slave,$elem['binlog_file'], $elem['binlog_pos'] ]);
                }
                $sql = "STOP SLAVE;";
                execute($sql, $slave, $DRY_RUN);


                $sql = "SET GLOBAL replicate_do_db='".$databases."';";
                execute($sql, $slave, $DRY_RUN);

            }


        }

        $sql = "STOP SLAVE;";
        execute($sql, $slave, $DRY_RUN);

        $sql = "SET GLOBAL replicate_do_db='';";
        execute($sql, $slave, $DRY_RUN);
        $sql = "START SLAVE;";
        execute($sql, $slave, $DRY_RUN);

        
    }



    /**
     * Génère un mot de passe aléatoire sécurisé, avec au moins une minuscule, une majuscule, un chiffre et un caractère spécial,
     * et sans guillemets simples ni doubles.
     *
     * @param int $length Longueur du mot de passe (par défaut : 16).
     * @return string Mot de passe généré.
     */
    function generateSecurePassword($length = 16) {
        // Définition des ensembles de caractères
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $digits = '0123456789';
        $specialChars = '!@#$%^&*()_+-=[]{}|;:,.<>?';

        // On s'assure que chaque type de caractère est présent
        $password = [
            $lowercase[random_int(0, strlen($lowercase) - 1)],
            $uppercase[random_int(0, strlen($uppercase) - 1)],
            $digits[random_int(0, strlen($digits) - 1)],
            $specialChars[random_int(0, strlen($specialChars) - 1)]
        ];

        // Remplissage du reste du mot de passe avec tous les caractères autorisés
        $allChars = $lowercase . $uppercase . $digits . $specialChars;
        $allCharsLength = strlen($allChars);

        for ($i = 4; $i < $length; $i++) {
            $password[] = $allChars[random_int(0, $allCharsLength - 1)];
        }

        // Mélange des caractères pour éviter une séquence prévisible
        shuffle($password);

        // Conversion du tableau en chaîne
        return implode('', $password);
    }

    /**
     * Vérifie si le slave a atteint la position spécifiée dans le binlog.
     *
     * @param string $masterLogFile Fichier de binlog à atteindre.
     * @param int $masterLogPos Position dans le binlog à atteindre.
     * @param int $timeout Délai maximal d'attente en secondes (par défaut : 3600).
     * @param int $interval Intervalle entre les vérifications en secondes (par défaut : 5).
     * @return bool True si la position est atteinte, false si le délai est dépassé.
     * @throws Exception En cas d'erreur lors de la vérification du statut du slave.
     */
    function waitForSlavePosition($param) {

        Debug::parseDebug($param);
        Debug::debug($param);

        $startTime = time();
        $timeout = 3600;

        $id_mysql_server = $param[0] ?? "";
        $binlog_file = $param[1];
        $binlog_pos = $param[2];
        
        while (true) {
            $db = Mysql::getDbLink($id_mysql_server, "SLAVE");
            // Vérification du timeout
            if (time() - $startTime > $timeout) {
                return false;
            }

            // Exécution de SHOW SLAVE STATUS
            $result = $db->sql_query("SHOW SLAVE STATUS");
            if (!$result) {
                throw new \Exception("Erreur lors de l'exécution de SHOW SLAVE STATUS : " . $db->sql_error());
            }

            $slaveStatus = array_change_key_case($db->sql_fetch_array($result, MYSQLI_ASSOC));
            $db->sql_free_result($result);
            // Vérifie qu'on n'a pas dépassé le fichier binlog ciblé
            
            
            
            if (
                isset($slaveStatus['relay_master_log_file']) &&
                strnatcmp($slaveStatus['relay_master_log_file'], $binlog_file) > 0
            ) {
                $db->sql_close();
                throw new \Exception(
                    "waitForSlavePosition aborted: slave binlog file {$slaveStatus['relay_master_log_file']} exceeded requested {$binlog_file}"
                );
            }

            // Vérification si la position est atteinte
            if (
                isset($slaveStatus['relay_master_log_file'], $slaveStatus['exec_master_log_pos']) &&
                $slaveStatus['relay_master_log_file'] === $binlog_file &&
                $slaveStatus['exec_master_log_pos'] >= $binlog_pos
            ) {
                Debug::debug("relay_master_log_file : ".$slaveStatus['relay_master_log_file']." - exec_master_log_pos : ".$slaveStatus['exec_master_log_pos'], "");

                $db->sql_close();
                return true;
            }


            $slaveSqlError   = trim($slaveStatus['last_sql_error'] ?? '');
            $slaveIoError    = trim($slaveStatus['last_io_error'] ?? '');
            $slaveSqlErrno   = (int)($slaveStatus['last_sql_errno'] ?? 0);
            $slaveIoErrno    = (int)($slaveStatus['last_io_errno'] ?? 0);

            $hasSqlError = $slaveSqlErrno !== 0 || $slaveSqlError !== '';
            $hasIoError  = $slaveIoErrno !== 0 || $slaveIoError !== '';

            if ($hasSqlError || $hasIoError) {
                Debug::debug($slaveStatus, "Replication error detected, aborting wait");
                $message = sprintf(
                    "SQL[%d/%s] IO[%d/%s]",
                    $slaveSqlErrno,
                    $slaveSqlError !== '' ? $slaveSqlError : 'No error',
                    $slaveIoErrno,
                    $slaveIoError !== '' ? $slaveIoError : 'No error'
                );
                Debug::debug($message, "Replication error message");
                $db->sql_close();
                throw new \Exception("waitForSlavePosition aborted due to replication error: " . $message);
            }

            Debug::debug("master_log_file : ".$slaveStatus['master_log_file']." - exec_master_log_pos : ".$slaveStatus['exec_master_log_pos'], "");
            unset($slaveStatus);

            

            // Attente avant la prochaine vérification
            sleep(1);
        }

        
    }


}
