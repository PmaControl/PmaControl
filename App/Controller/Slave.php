<?php
/*
 * j'ai ajouté un mail automatique en cas d'erreur ou de manque sur une PK
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use App\Library\Extraction;
use App\Library\Mysql;
use App\Library\System;
use App\Library\Debug;
use \Glial\Sgbd\Sgbd;

class Slave extends Controller
{

    use \App\Library\Filter;

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

        $data['hostname'] = Extraction::display(array("variables::hostname"));


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


            $data['server']['slave'][$arr['id']] = $arr;
        }

        $slaves = Extraction::extract(array("slave::seconds_behind_master"), array(), "1 hour", false, true);


//debug($slaves);

        $this->generateGraph($slaves);


        foreach ($slaves as $slave) {
            $data['graph'][$slave['id_mysql_server']] = $slave;
        }


        $this->set('data', $data);
    }

    private function generateGraph($slaves)
    {
        $this->di['js']->addJavascript(array("moment.js", "Chart.bundle.js"));

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

//debug($server);

        $data['slave'] = array();

        if ($server['is_available'] === "1") {
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
//debug($data['slave']);


        $data['replication_name'] = $replication_name;





        Extraction::setOption('groupbyday', true);


        $date        = date('Y-m-d H:i:s');
        $date_format = 'Y-m-d';

        $array_date = date_parse_from_format($date_format, $date);

        $more_days = -7;
        $next_date = date(
            $date_format, mktime(0, 0, 0, $array_date['month'], $array_date['day'] + $more_days, $array_date['year'])
        );



        $slaves = Extraction::extract(array("slave::seconds_behind_master"), array($id_mysql_server), array($next_date, $date), true, true);


        $this->generateGraphSlave($slaves);


        foreach ($slaves as $slave) {
            $data['graph'][$slave['day']] = $slave;
        }



//change master
        $sql = "SELECT a.id_mysql_server FROM link__architecture__mysql_server a
          INNER JOIN link__architecture__mysql_server b ON a.id_architecture = b.id_architecture
          WHERE b.id_mysql_server=".$id_mysql_server." and a.id_mysql_server != ".$id_mysql_server.";";

        $res = $db->sql_query($sql);

        $data['mysql_server_specify'] = array();
        while ($ob                           = $db->sql_fetch_object($res)) {
            $data['mysql_server_specify'][] = $ob->id_mysql_server;
        }



// find master
        $_GET['mysql_server']['id'] = Mysql::getMaster($id_mysql_server, $replication_name);

        $data['id_slave']              = array($id_mysql_server);
        $_GET['mysql_slave']['server'] = $id_mysql_server;






//le cas ou on arrive pas a trouver le master
        if (!empty($_GET['mysql_server']['id'])) {
            $db_master = Mysql::getDbLink($_GET['mysql_server']['id']);

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
        }

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

                    $export['slave_sql_error'] = $slave['last_sql_error'];
                    $export['slave_io_error']  = $slave['last_io_error'];


                    $export['slave_sql_errno'] = $slave['last_sql_errno'];
                    $export['slave_io_errno']  = $slave['last_io_errno'];

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

    public function updateAlias($param)
    {
        Debug::parseDebug($param);
        $db = Sgbd::sql(DB_DEFAULT);
        $list = Extraction::display(array("slave::master_host", "slave::master_port"));

        $list_host = array();
        foreach ($list as $masters) {
            foreach ($masters as $master) {

                $key = $master['master_host'].':'.$master['master_port'];

                $host[$key]  = $master;
                $list_host[] = $master['master_host'];
            }
        }

        //Debug::debug($host);
        Debug::debug($list_host);

        $sql = "SELECT dns, port, id_mysql_server FROM `alias_dns`;";
        $res = $db->sql_query($sql);

        $all_dns = array();
        while ($ob      = $db->sql_fetch_object($res)) {

            $uniq           = $ob->dns.':'.$ob->port;
            $all_dns[$uniq] = $ob->id_mysql_server;
        }

        Debug::debug($all_dns);

        $sql = "SELECT id, ip, port FROM mysql_server";
        $res = $db->sql_query($sql);
        
        $mysql_server = array();
        while ($ob  = $db->sql_fetch_object($res)) {
            $uniq                = $ob->ip.':'.$ob->port;
            $mysql_server[$uniq] = $ob->id;
        }

        foreach ($host as $dns) {
            $uniq = $dns['master_host'].':'.$dns['master_port'];

            if (!empty($mysql_server[$uniq])) {
                continue;
            }

            if (!empty($all_dns[$uniq])) {
                continue;
            }

            $ip = System::getIp($dns['master_host']);
            $uniq = $ip.':'.$dns['master_port'];

            if (!empty($mysql_server[$uniq])) {

                $alias_dns                                 = array();
                $alias_dns['alias_dns']['id_mysql_server'] = $mysql_server[$uniq];
                $alias_dns['alias_dns']['dns']             = $dns['master_host'];
                $alias_dns['alias_dns']['port']            = $dns['master_port'];
                $alias_dns['alias_dns']['destination']     = $ip;
                $db->sql_save($alias_dns);
            }
        }
    }
}