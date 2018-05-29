<?php
/*
 * j'ai ajouté un mail automatique en cas d'erreur ou de manque sur une PK
 */

use \Glial\Synapse\Controller;
use App\Library\Extraction;

class Slave extends Controller
{

    use \App\Library\Filter;

    public function index()
    {

        $this->title = '<i class="fa fa-sitemap"></i> '.__("Master / Slave");

        $db = $this->di['db']->sql(DB_DEFAULT);

        $this->di['js']->addJavascript(array("moment.js", "Chart.bundle.js"));
        $this->di['js']->code_javascript('
        $(function () {
  $(\'a[data-toggle="tooltip"]\').tooltip();  /* tooltip("show") */
  $(\'[data-toggle="tooltip"]\').tooltip();
})');

        Extraction::setDb($db);
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

        foreach ($slaves as $slave) {

            $this->di['js']->code_javascript('

Chart.defaults.global.legend.display = false;

var ctx = document.getElementById("myChart'.$slave['id_mysql_server'].'").getContext("2d");

var myChart'.$slave['id_mysql_server'].' = new Chart(ctx, {
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
        $db = $this->di['db']->sql(DB_DEFAULT);

        //debug($db);
    }

    public function box()
    {

        $db = $this->di['db']->sql(DB_DEFAULT);

        Extraction::setDb($db);
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


            $data['server']['slave'][$arr['id']] = $arr;
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
}
/*
 *
MariaDB [pmacontrol]> select * from ts_variable where `from` = 'slave';
+------+-------------------------------+--------+-------+
| id   | name                          | type   | from  |
+------+-------------------------------+--------+-------+
|  794 | slave_io_state                | TEXT   | slave |
|  795 | master_host                   | TEXT   | slave |
|  796 | master_user                   | TEXT   | slave |
|  797 | master_port                   | INT    | slave |
|  798 | connect_retry                 | INT    | slave |
|  799 | master_log_file               | TEXT   | slave |
|  800 | read_master_log_pos           | INT    | slave |
|  801 | relay_log_file                | TEXT   | slave |
|  802 | relay_log_pos                 | INT    | slave |
|  803 | relay_master_log_file         | TEXT   | slave |
|  804 | slave_io_running              | TEXT   | slave |
|  805 | slave_sql_running             | TEXT   | slave |
|  806 | replicate_do_db               | TEXT   | slave |
|  807 | replicate_ignore_db           | TEXT   | slave |
|  808 | last_errno                    | INT    | slave |
|  809 | skip_counter                  | INT    | slave |
|  810 | exec_master_log_pos           | INT    | slave |
|  811 | relay_log_space               | INT    | slave |
|  812 | until_condition               | TEXT   | slave |
|  813 | until_log_pos                 | INT    | slave |
|  814 | master_ssl_allowed            | TEXT   | slave |
|  815 | seconds_behind_master         | INT    | slave |
|  816 | master_ssl_verify_server_cert | TEXT   | slave |
|  817 | last_io_errno                 | INT    | slave |
|  818 | last_sql_errno                | INT    | slave |
|  819 | master_server_id              | INT    | slave |
| 1174 | slave_sql_state               | TEXT   | slave |
| 1175 | using_gtid                    | TEXT   | slave |
| 1176 | parallel_mode                 | TEXT   | slave |
| 1177 | retried_transactions          | INT    | slave |
| 1178 | max_relay_log_size            | INT    | slave |
| 1179 | executed_log_entries          | INT    | slave |
| 1180 | slave_received_heartbeats     | INT    | slave |
| 1181 | slave_heartbeat_period        | DOUBLE | slave |
| 1182 | gtid_slave_pos                | TEXT   | slave |
| 1183 | gtid_io_pos                   | TEXT   | slave |
| 1233 | sql_delay                     | INT    | slave |
| 1234 | sql_remaining_delay           | TEXT   | slave |
| 1235 | slave_sql_running_state       | TEXT   | slave |
| 1924 | last_io_error                 | TEXT   | slave |
+------+-------------------------------+--------+-------+
40 rows in set (0.002 sec)

 *
 */