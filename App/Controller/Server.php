<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
//use \Glial\Cli\Color;
use \Glial\Security\Crypt\Crypt;
use App\Library\Extraction;
use App\Library\Extraction2;

use \App\Library\Debug;
use \App\Library\Mysql;
use \App\Library\EngineV4;

use App\Library\Chiffrement;
use \Glial\Sgbd\Sgbd;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;

/*
UPDATE performance_schema.setup_instruments
   SET ENABLED = 'YES', TIMED = 'YES'
   WHERE NAME LIKE 'statement/sql/select';

UPDATE performance_schema.setup_consumers
   SET ENABLED = 'YES' WHERE NAME IN ('events_statements_history', 'events_statements_current');


*/

class Server extends Controller
{

    use \App\Library\Filter;
    var $clip = 0;

    var $logger;

    public function before($param)
    {
        $monolog       = new Logger("Server");
        $handler      = new StreamHandler(LOG_FILE, Logger::DEBUG);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $monolog->pushHandler($handler);
        $this->logger = $monolog;
    }

//dba_source
    public function hardware()
    {
        $db           = Sgbd::sql(DB_DEFAULT);
        $this->title  = __("Hardware");
        $this->ariane = " > ".$this->title;

        $data['hardware'] = Extraction::display(array("ssh_hardware::cpu_thread_count",
                "ssh_hardware::cpu_frequency",
                "ssh_hardware::memory",
                "ssh_hardware::distributor",
                "ssh_hardware::os",
                "ssh_hardware::codename",
                "ssh_hardware::product_name",
                "ssh_hardware::arch",
                "ssh_hardware::kernel",
                "ssh_hardware::hostname",
                "ssh_hardware::swapiness"
        ));

        $data['service_ssh'] = Extraction::display(array("ssh_available"));

        $id_mysql_servers = array_keys($data['hardware']);

        if (!empty($id_mysql_servers)) {

            $sql = "SELECT c.libelle as client,d.libelle as environment,a.*
            FROM mysql_server a
            
                 INNER JOIN client c on c.id = a.id_client
                 INNER JOIN environment d on d.id = a.id_environment

         WHERE 1=1 ".self::getFilter()."
             AND a.id in (".implode(",", $id_mysql_servers).")
         order by `name`;";

//echo SqlFormatter::format($sql);

            $data['servers'] = $db->sql_fetch_yield($sql);
        } else {
            $data['servers'] = array();
        }


        $this->set('data', $data);
    }




    /*
      public function listing($param)
      {
      // doc : http://silviomoreto.github.io/bootstrap-select/examples/#standard-select-boxes
      $this->di['js']->addJavascript(array('bootstrap-select.min.js'));

      $db = Sgbd::sql(DB_DEFAULT);

      $sql = "SELECT * FROM daemon_main WHERE id=1";
      $res = $db->sql_query($sql);

      while ($ob = $db->sql_fetch_object($res)) {

      $data['pid']      = $ob->pid;
      $data['date']     = $ob->date;
      $data['log_file'] = $ob->log_file;
      }
      $data['client']                = $this->getClients();
      $data['environment']           = $this->getEnvironments();
      $data['menu']['main']['name']  = __('Servers');
      $data['menu']['main']['icone'] = '<i class="fa fa-server" aria-hidden="true" style="font-size:14px"></i>';
      //$data['menu']['main']['icone'] = '<span class="glyphicon glyphicon-th-large" style="font-size:12px"></span>';
      $data['menu']['main']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/main';

      $data['menu']['hardware']['name']  = __('Hardware');
      $data['menu']['hardware']['icone'] = '<span class="glyphicon glyphicon-hdd" style="font-size:12px"></span>';
      $data['menu']['hardware']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/hardware';

      $data['menu']['database']['name']  = __('Databases');
      $data['menu']['database']['icone'] = '<i class="fa fa-database fa-lg" style="font-size:14px"></i>';
      $data['menu']['database']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/database';

      $data['menu']['statistics']['name']  = __('Statistics');
      $data['menu']['statistics']['icone'] = '<span class="glyphicon glyphicon-signal" style="font-size:12px"></span>';
      $data['menu']['statistics']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/statistics';

      $data['menu']['memory']['name']  = __('Memory');
      $data['menu']['memory']['icone'] = '<span class="glyphicon glyphicon-floppy-disk" style="font-size:12px"></span>';
      $data['menu']['memory']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/memory';

      $data['menu']['index']['name']  = __('Index');
      $data['menu']['index']['icone'] = '<span class="glyphicon glyphicon-th-list" style="font-size:12px"></span>';
      $data['menu']['index']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/index';

      $data['menu']['system']['name']  = __('System');
      $data['menu']['system']['icone'] = '<span class="glyphicon glyphicon-cog" style="font-size:12px"></span>';
      $data['menu']['system']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/system';

      $data['menu']['logs']['name']  = __('Logs');
      $data['menu']['logs']['icone'] = '<span class="glyphicon glyphicon-list-alt" style="font-size:12px"></span>';
      $data['menu']['logs']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/index';

      $data['menu']['id']['name']  = __('Graphs');
      $data['menu']['id']['icone'] = '<i class="fa fa-line-chart" aria-hidden="true"></i>';
      $data['menu']['id']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/id';

      $data['menu']['cache']['name']  = __('Cache');
      $data['menu']['cache']['icone'] = '<span class="glyphicon glyphicon-floppy-disk" style="font-size:12px"></span>';
      $data['menu']['cache']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/cache';


      $data['menu_select']['main']['name']  = __('Servers');
      $data['menu_select']['main']['icone'] = '<span class="glyphicon glyphicon-th-large" style="font-size:12px"></span>';
      $data['menu_select']['main']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/main';

      $data['menu_select']['hardware']['name']  = __('Hardware');
      $data['menu_select']['hardware']['icone'] = '<span class="glyphicon glyphicon-hdd" style="font-size:12px"></span>';
      $data['menu_select']['hardware']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/hardware';


      $data['menu_select']['database']['name']  = __('Databases');
      $data['menu_select']['database']['icone'] = '<i class="fa fa-database fa-lg" style="font-size:14px"></i>';
      $data['menu_select']['database']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/database';

      $data['menu_select']['statistics']['name']  = __('Statistics');
      $data['menu_select']['statistics']['icone'] = '<span class="glyphicon glyphicon-signal" style="font-size:12px"></span>';
      $data['menu_select']['statistics']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/statistics';

      $data['menu_select']['memory']['name']  = __('Memory');
      $data['menu_select']['memory']['icone'] = '<span class="glyphicon glyphicon-floppy-disk" style="font-size:12px"></span>';
      $data['menu_select']['memory']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/memory';

      $data['menu_select']['index']['name']  = __('Index');
      $data['menu_select']['index']['icone'] = '<span class="glyphicon glyphicon-th-list" style="font-size:12px"></span>';
      $data['menu_select']['index']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/index';


      $data['menu_select']['system']['name']  = __('System');
      $data['menu_select']['system']['icone'] = '<span class="glyphicon glyphicon-cog" style="font-size:12px"></span>';
      $data['menu_select']['system']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/system';

      $data['menu_select']['logs']['name']  = __('Logs');
      $data['menu_select']['logs']['icone'] = '<span class="glyphicon glyphicon-list-alt" style="font-size:12px"></span>';
      $data['menu_select']['logs']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/index';


      $data['menu_select']['id']['name']  = __('Server');
      $data['menu_select']['id']['icone'] = '<span class="glyphicon glyphicon-list-alt" style="font-size:12px"></span>';
      $data['menu_select']['id']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/id';



      if (!empty($param[0])) {
      if (in_array($param[0], array("main", "database", "statistics", "logs", "memory", "index", "hardware", "system", "id", "cache"))) {
      $_GET['path'] = LINK.$this->getClass().'/'.__FUNCTION__.'/'.$param[0];
      }
      }

      if (empty($_GET['path']) && empty($param[0])) {
      $_GET['path'] = $data['menu']['main']['path'];
      $param[0]     = 'main';
      }

      if (empty($_GET['path'])) {
      $_GET['path'] = 'main';
      }


      //@TODO bug with item not selected (empty) need put case by default

      $this->title = '<span class="glyphicon glyphicon glyphicon-home"></span> '.__("Dashboard");
      //$this->ariane = ' > <a href⁼"">'.'<span class="glyphicon glyphicon glyphicon-home" style="font-size:12px"></span> '.__("Dashboard").'</a> > '.$data['menu'][$param[0]]['icone'].' '.$data['menu'][$param[0]]['name'];

      $this->set('data', $data);
      }
      /*
     */

    public function main()
    {
        $db = Sgbd::sql(DB_DEFAULT);

        if (!empty($_GET['ajax']) && $_GET['ajax'] === "true") {
            $this->layout_name = false;
        }
        else {
            $this->di['js']->addJavascript(array('clipboard.min.js', 'Client/index.js', 'Server/main.js'));
            $this->di['js']->code_javascript('(function() {
                new Clipboard(".copy-button");
            })();');
    
            $this->di['js']->code_javascript('
            $(document).ready(function()
            {
                var intervalId;
                var refreshInterval = 1000; // Intervalle par défaut de 3 secondes

                function refresh()
                {
                    var myURL = GLIAL_LINK+GLIAL_URL+"/ajax:true";
                    $("#servermain").load(myURL);
                    (function() {

                        //duplicate :/
                        new Clipboard(".copy-button");

                        $(".general_log").change(function () {
                            id_mysql_server = $(this).attr("data-id");
                            url = GLIAL_LINK + "server/toggleGeneralLog/" + id_mysql_server;
                        
                            if (this.checked) {
                                $.get(url + "/true/");
                            } else
                            {
                                $.get(url + "/false/");
                            }
                        });
                    })();


                }

                function setRefreshInterval(newInterval) {
                    refreshInterval = newInterval;
                    clearInterval(intervalId);
                    intervalId = setInterval(refresh, refreshInterval);
                }

                function stopRefresh() {
                    clearInterval(intervalId);
                    intervalId = null;
                }
    
                intervalId = setInterval(refresh, refreshInterval);

                // Rendre les fonctions accessibles globalement
                window.setRefreshInterval = setRefreshInterval;
                window.stopRefresh = stopRefresh;

    
            });'); /***/

        }

        $sql = "SELECT a.*, c.libelle as client,c.is_monitored as client_monitored, d.libelle as environment,d.`class`
            FROM mysql_server a
                 INNER JOIN client c on c.id = a.id_client
                 INNER JOIN environment d on d.id = a.id_environment
                 WHERE 1 ".self::getFilter()."
                 ORDER by a.id, a.is_monitored DESC, c.is_monitored DESC, a.`is_acknowledged`, 
                 FIND_IN_SET(d.`id`, '1,19,2,16,3,7,4,2,6,8,5,17,18'), a.ip, a.display_name;";

        $res = $db->sql_query($sql);

        $servers = array();
        while ($arr     = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['servers'][] = $arr;
            $servers[]         = $arr['id'];

        }

        $data['extra'] = Extraction2::display(array("version", "version_comment", "mysql_ping","time_server","wsrep_cluster_status",
         "mysql_available", "mysql_server::mysql_error" ,"general_log", "wsrep_on", "is_proxysql", "performance_schema", "read_only", "query_latency_1m"));

        $data['last_date'] = Extraction2::display(array("mysql_server::"));

        // get Tag
        $sql          = "SELECT * FROM link__mysql_server__tag a
            INNER JOIN tag b ON b.id =a.id_tag ORDER BY b.name";
        $res          = $db->sql_query($sql);
        $data['tags'] = array();
        while ($arr          = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['tag'][$arr['id_mysql_server']][] = $arr;
        }

        $data['processing'] = $this->getDaemonRunning(array());


        //debug($data);

        $this->set('data', $data);
    }

    public function database()
    {
        $db = Sgbd::sql(DB_DEFAULT);

        //TODO add available === 1
        $sql = "SELECT a.id,a.name,a.ip,a.port,
			GROUP_CONCAT('',b.name) as dbs,
			GROUP_CONCAT('',b.id) as id_db,
			GROUP_CONCAT('',b.data_length) as data_length,
			GROUP_CONCAT('',b.data_free) as data_free,
			GROUP_CONCAT('',b.index_length) as index_length,
			GROUP_CONCAT('',b.collation_name) as collation_name,
			GROUP_CONCAT('',b.character_set_name) as character_set_name,
			GROUP_CONCAT('',b.binlog_do_db) as binlog_do_db,
			GROUP_CONCAT('',b.tables) as `tables`,
			GROUP_CONCAT('',b.rows) as `rows`
			FROM mysql_server a
			INNER JOIN mysql_database b ON b.id_mysql_server = a.id
                        ".$this->getFilter()."
			GROUP BY a.id";

        $data['servers'] = $db->sql_fetch_yield($sql);
        $this->set('data', $data);
    }

    public function statistics()
    {
        $db = Sgbd::sql(DB_DEFAULT);

//echo \SqlFormatter::format($sql);


        $data['servers'] = Extraction::display(array("status::com_select", "status::com_update", "status::com_insert", "status::com_delete",
                "status::threads_connected", "status::uptime", "status::com_commit", "status::com_rollback", "status::com_begin", "status::com_replace",
                "variables::hostname","variables::is_proxysql"));

        $data['mysql'] = $this->getServer();

        /*
          $sql = "SELECT id, ip, port from mysql_server a";
          $res = $db->sql_query($sql);

          while ($ob = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
          $data['mysql'][$ob['id']] = $ob;
          } */


        $this->set('data', $data);
    }

    public function logs()
    {
//used with recursive
    }

    private function buildQuery($fields)
    {
        $sql = 'select a.ip, a.port, a.id, a.name,';

        $i   = 0;
        $tmp = [];
        foreach ($fields as $field) {
            $tmp[] = " c$i.value as $field";
            $i++;
        }

        $sql .= implode(",", $tmp);
        $sql .= " from mysql_server a ";
        $sql .= " INNER JOIN status_max_date b ON a.id = b.id_mysql_server ";

        $tmp = [];
        $i   = 0;
        foreach ($fields as $field) {
            $sql .= " LEFT JOIN status_value_int c$i ON c$i.id_mysql_server = a.id AND b.date = c$i.date";
            $sql .= " LEFT JOIN status_name d$i ON d$i.id = c$i.id_status_name ";
            $i++;
        }

        $sql .= " WHERE 1 ".$this->getFilter()."";

        $tmp = [];
        $i   = 0;
        foreach ($fields as $field) {
            $sql .= " AND d$i.name = '".$field."'  ";
            $i++;
        }

        $sql .= ";";
        return $sql;
    }

    public function memory()
    {
        $this->title  = __("Memory");
        $this->ariane = " > ".__("Tools Box")." > ".$this->title;

        $db = Sgbd::sql(DB_DEFAULT);

        $data['variables'] = Extraction::display(array("variables::innodb_buffer_pool_size", "variables::innodb_additional_mem_pool_size",
                "variables::innodb_log_buffer_size", "variables::key_buffer_size", "variables::read_buffer_size",
                "variables::query_cache_size", "variables::tmp_table_size", "variables::max_connections", "status::max_used_connections",
                "variables::sort_buffer_size", "variables::read_rnd_buffer_size", "variables::join_buffer_size", "variables::thread_stack",
                "variables::binlog_cache_size", "variables::innodb_buffer_pool_chunk_size", "variables::innodb_buffer_pool_instances", 
                "ssh_stats::memory_total", "databases::databases"));

        $data['database'] = Extraction::getSizeByEngine($data['variables']);

        $this->set('data', $data);
    }

    public function index()
    {
        $this->title  = __("Index usage");
        $this->ariane = " > ".__("Tools Box")." > ".$this->title;

        $db = Sgbd::sql(DB_DEFAULT);

        $this->di['js']->code_javascript('
        $(function () {
  $(\'[data-toggle="tooltip"]\').tooltip();
})');

        $data['status'] = Extraction::display(array("status::handler_read_rnd_next", "status::handler_read_rnd", "status::handler_read_first",
                "status::handler_read_next", "status::handler_read_key", "status::handler_read_prev"));

        $data['mysql'] = $this->getServer();

        $this->set('data', $data);
    }
    /*
     *
     * graph with Chart.js
     *
     */

    public function id($param)
    {

        /*
          select avg(Column), convert((min(datetime) div 500)*500 + 230, datetime) as time
          from Databasename.tablename
          where datetime BETWEEN '2012-09-08 00:00:00' AND '2012-09-08 15:30:00'
          group by datetime div 500
         *
         * select avg(Column), convert((min(datetime) div 500)*500 + 230, datetime) as time
          from Databasename.tablename
          where datetime BETWEEN '2012-09-08 00:00:00' AND '2012-09-08 15:30:00'
          group by datetime div 500
         */
//$this->di['js']->addJavascript(array("Chart.Core.js", "Chart.Scatter.min.js"));

        $this->di['js']->addJavascript(array('bootstrap-select.min.js'));
        $this->di['js']->addJavascript(array("moment.js", "chart.min.js"));
        $db = Sgbd::sql(DB_DEFAULT);

        $data = array();

        if ($_SERVER['REQUEST_METHOD'] === "POST" && !empty($_POST['mysql_server'])) {

            $sql = "SELECT * FROM mysql_server where id='".$_POST['mysql_server']['id']."'";
            $res = $db->sql_query($sql);
            while ($ob  = $db->sql_fetch_object($res)) {
                $id_mysql_server = $ob->id;

                header('location: '.LINK.$this->getClass()
                    .'/'.__FUNCTION__.'/mysql_server:id:'.$id_mysql_server
                    .'/ts_variable:name:'.$_POST['ts_variable']['name']
                    .'/ts_variable:date:'.$_POST['ts_variable']['date']
                    .'/ts_variable:derivate:'.$_POST['ts_variable']['derivate']);
                    exit;
            }
        } else {

            // get server available
            //TODO : add available ===1
            $sql             = "SELECT * FROM mysql_server a WHERE 1=1 ".$this->getFilter()." order by a.name ASC";
            $res             = $db->sql_query($sql);
            $data['servers'] = array();
            while ($ob              = $db->sql_fetch_object($res)) {
                $tmp               = [];
                $tmp['id']         = $ob->id;
                $tmp['libelle']    = $ob->name." (".$ob->ip.")";
                $data['servers'][] = $tmp;
            }

            // get variable available
            $sql            = "SELECT name,`from` FROM ts_variable 
            WHERE `type` in('INT','DOUBLE') AND `from` NOT IN ('variables', 'slave')
            order by `from`,name ASC";
            $res            = $db->sql_query($sql);
            $data['status'] = array();
            while ($ob             = $db->sql_fetch_object($res)) {
                $tmp              = [];
                $tmp['id']        = $ob->name;
                $tmp['libelle']   = '<i style="color:#bbbbbb">'.$ob->from ."</i> ". $ob->name;
                $data['status'][] = $tmp;
            }

            $interval = array('5-minute', '15-minute', '1-hour', '2-hour', '6-hour', '12-hour', '1-day', '2-day', '1-week', '2-week', '1-month');
            $libelles = array('5 minutes', '15 minutes', '1 hour', '2 hours', '6 hours', '12 hours', '1 day', '2 days', '1 week', '2 weeks',
                '1 month');
            $elems    = array(60 * 5, 60 * 15, 3600, 3600 * 2, 3600 * 6, 3600 * 12, 3600 * 24, 3600 * 48, 3600 * 24 * 7, 3600 * 24 * 14, 3600 * 24 * 30);

            $data['interval'] = array();
            $i                = 0;
            foreach ($libelles as $libelle) {
                $tmp                = [];
                $tmp['id']          = $interval[$i];
                $tmp['libelle']     = $libelle;
                $data['interval'][] = $tmp;
                $i++;
            }

            $data['derivate'][0]['id']      = 1;
            $data['derivate'][0]['libelle'] = __("Yes");
            $data['derivate'][1]['id']      = 2;
            $data['derivate'][1]['libelle'] = __("No");

            if (empty($_GET['ts_variable']['date'])) {
                $_GET['ts_variable']['date'] = "1 hour";
            }

            if (!empty($_GET['mysql_server']['id']) && !empty($_GET['ts_variable']['name']) && !empty($_GET['ts_variable']['date']) && !empty($_GET['ts_variable']['derivate'])
            ) {
                
                
                $gg = Extraction::extract(array("version"), array(1));

                $res = Extraction::extract(array($_GET['ts_variable']['name']), array($_GET['mysql_server']['id']), str_replace("-"," ",$_GET['ts_variable']['date']));


                $data['date_max'] = date('Y-m-d H:i:s');
                $res10 = $db->sql_query("SELECT NOW() as maintenant;");
                while($ob = $db->sql_fetch_object($res10)) {
                    $data['date_max'] = $ob->maintenant;
                }

                //debug($res);
                /*
                  $sql = "SELECT * FROM status_value_int a
                  WHERE a.id_mysql_server = " . $_GET['mysql_server']['id'] . "
                  AND a.id_status_name = '" . $_GET['status_name']['id'] . "'
                  and a.`date` > date_sub(now(), INTERVAL " . $_GET['status_value_int']['date'] . ") ORDER BY a.`date` ASC;";
                  $data['sql'] = $sql; */


                $data['graph'] = array();
                if ($res !== false) {
                    while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                        $data['graph'][] = $arr;
                    }
                }

                $dates = [];
                $val   = [];

                /*
                  $sql2 = "SELECT name FROM ts_variables WHERE from ='status' and id= '" . $_GET['status_name']['name'] . "'";
                  //debug($sql2);
                  $res2 = $db->sql_query($sql2);
                  while ($ob2 = $db->sql_fetch_object($res2)) {
                  $name = $ob2->name;
                  } */

                $name = $_GET['ts_variable']['name'];
                $i    = 0;

                $old_date = "";
                $point    = [];

                if (!empty($data['graph'])) {

                    foreach ($data['graph'] as $value) {

                        if (empty($old_date) && $_GET['ts_variable']['derivate'] == "1") {

                            $old_date  = $value['date'];
                            $old_value = $value['value'];
                            continue;
                        } elseif ($_GET['ts_variable']['derivate'] == "1") {

                            $datetime1 = strtotime($old_date);
                            $datetime2 = strtotime($value['date']);
                            $secs = $datetime2 - $datetime1; // == <seconds between the two times>
//echo $datetime1. ' '.$datetime2 . ' : '. $secs." ".$value['value'] ." - ". $old_value." => ".($value['value']- $old_value)/ $secs."<br>";
                            //to prevent divide by zero
                            if ($secs == 0) {
                                continue;
                            }

                            $derivate = round(($value['value'] - $old_value) / $secs, 2);

                            if ($derivate < 0) {
                                $derivate = 0;
                            }

                            $val = $derivate;

//$points[] = "{ x: " . $datetime2 . ", y :" . $derivate . "}";
                        } else {
                            $val = $value['value'];
                        }

                        $point[] = "{ x: new Date('".$value['date']."'), y: ".$val."}";
                        $dates[] = $value['date'];

                        $old_date  = $value['date'];
                        $old_value = $value['value'];
                    }
                }

//$point2[] = "{ x: new Date('2017-10-28 00:38:34'), y: 50}";
//$point2[] = "{ x: new Date('2017-10-28 00:37:34'), y: 40}";
//$date = implode('","', $dates);
//$vals = implode(',', $val);
                $points = implode(',', $point);
//$points2 = implode(',', $point2);

                $this->di['js']->code_javascript('
var ctx = document.getElementById("myChart").getContext("2d");


var myChart = new Chart(ctx, {
    type: "line",
    data: {
        datasets: [{
            label: "'.$name.'",
            data: ['.$points.'],
                borderWidth: 1,
             pointRadius :0,
             lineTension: 0

        },
]
    },
    options: {
        bezierCurve: false,
        title: {
            display: true,
            text: " ",
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

                    max: new Date("'.$data['date_max'].'"),   /* date(Y-m-d H:i:s) : '.date("Y-m-d H:i:s").' => problem with timezone why ? */
                    tooltipFormat: "dddd YYYY-MM-DD, HH:mm:ss",
                    displayFormats: {
          minute: "dddd YYYY-MM-DD, HH:mm"
        }

                }

            }],
             yAxes: [{


      scaleLabel: {
        display: true,
        labelString: "Queries by second",

      }

    }]
        }
    }
});



');

                /*
                  Chart.defaults.global.responsive = true;
                  Chart.defaults.global.animation = false;

                  $(function () {




                  var data3 = [
                  {
                  label: "temperature",
                  strokeColor: "rgba(0,0,0,0.5)",
                  data: [
                  '.$points.',



                  ]
                  }];

                  var ctx3 = document.getElementById("myChart").getContext("2d");
                  var myDateLineChart = new Chart(ctx3).Scatter(data3 ,{
                  bezierCurve: false,
                  bezierCurveTension: 0,
                  showTooltips: true,
                  scaleShowHorizontalLines: true,
                  scaleShowLabels: true,
                  scaleType: "date",
                  pointBackgroundColor: "rgba(0,0,0,0.3)",
                  borderWidth: 1,
                  pointDot : false,
                  pointRadius :1,
                  fill: true,
                  useUtc: false,
                  beginAtZero:true,
                  backgroundColor:"#00f",

                  datasetStrokeWidth:1,

                  fillColor: "rgba(151,187,205,0.2)",
                  strokeColor: "rgba(151,187,205,1)",
                  pointColor: "rgba(151,187,205,1)",
                  pointStrokeColor: "#fff",
                  pointHighlightFill: "#fff",
                  pointHighlightStroke: "rgba(151,187,205,0.8)",
                  scaleDateFormat: "mmm d",
                  scaleTimeFormat: "HH:MM",
                  scaleDateTimeFormat: "mmm d, yyyy, hh:MM",
                  pointDot: true,
                  pointDotStrokeWidth: 1,
                  pointDotRadius: 0,
                  pointHitDetectionRadius: 2,

                  });





                  });
                  ');
                  /*
                 *
                 */
            } else {
                if (empty($data['servers'])) $data['servers']  = "";
                if (empty($data['status'])) $data['status']   = "";
                if (empty($data['interval'])) $data['interval'] = "";
                if (empty($data['derivate'])) $data['derivate'] = "";


                $data['fields_required'] = 1;
            }
        }

        $this->set('data', $data);
    }

    public function settings()
    {

        $db = Sgbd::sql(DB_DEFAULT);

        if ($_SERVER['REQUEST_METHOD'] == "POST") {

            if (!empty($_POST['settings'])) {


                foreach ($_POST['id'] as $key => $value) {

                    if (empty($_POST['mysql_server'][$key]['is_monitored'])) {
                        $_POST['mysql_server'][$key]['is_monitored'] = 0;
                    } else {
                        $_POST['mysql_server'][$key]['is_monitored'] = 1;
                    }

                    $server_main                                   = array();
                    $server_main['mysql_server']['id']             = $value;
                    $server_main['mysql_server']['display_name']   = $_POST['mysql_server'][$key]['display_name'];
                    $server_main['mysql_server']['id_client']      = $_POST['mysql_server'][$key]['id_client'];
                    $server_main['mysql_server']['id_environment'] = $_POST['mysql_server'][$key]['id_environment'];
                    $server_main['mysql_server']['is_monitored']   = $_POST['mysql_server'][$key]['is_monitored'];

                    $ret = $db->sql_save($server_main);

                    if (!$ret) {
                        
                        print_r($db->sql_error());
                    }
                }

                if (!empty($_POST['link__mysql_server_tag'])) {

                    $sql = "BEGIN";
                    $db->sql_query($sql);

                    try {

                        $sql = "delete from link__mysql_server__tag where id_mysql_server in (".implode(",", $_POST['id']).")";
                        $db->sql_query($sql);
                        foreach ($_POST['link__mysql_server_tag'] as $key => $servers) {

                            foreach ($servers as $tags) {
                                foreach ($tags as $tag) {

                                    $sql = "INSERT INTO link__mysql_server__tag (`id_mysql_server`, `id_tag`) VALUES ('".$_POST['id'][$key]."','".$tag."')";
                                    $db->sql_query($sql);
                                }
                            }
                        }
                        $sql = "COMMIT";
                        $db->sql_query($sql);
                    } catch (\Exception $ex) {
                        $sql = "ROLLBACK";
                        $db->sql_query($sql);
                    }
                }

                header("location: ".LINK."Server/settings");
                exit;
            }
        }

        $this->title  = '<i class="fa fa-server"></i> '.__("Servers");
        $this->ariane = ' > <a href⁼"">'.'<i class="fa fa-cog" style="font-size:14px"></i> '
            .__("Settings").'</a> > <i class="fa fa-server"  style="font-size:14px"></i> '.__("Servers");

        $data['ssh']   = Extraction::display(array("ssh_available"));
        $data['mysql'] = Extraction::display(array("mysql_available"));

        $sql             = "SELECT * FROM mysql_server a WHERE 1=1 ".self::getFilter()." ORDER by name";
        $data['servers'] = $db->sql_fetch_yield($sql);

        $data['clients']      = $this->getClients();
        $data['environments'] = $this->getEnvironments();

        // tag
        $sql         = "SELECT * FROM tag order by name";
        $res         = $db->sql_query($sql);
        $data['tag'] = array();
        while ($ob          = $db->sql_fetch_object($res)) {
            $tmp            = array();
            $tmp['id']      = $ob->id;
            $tmp['libelle'] = $ob->name;
            $tmp['extra']   = array("data-content" => "<span title='".$ob->name."' class='label' style='color:".$ob->color."; background:".$ob->background."'>".$ob->name."</span>");

            $data['tag'][] = $tmp;
        }

        $sql = "SELECT * FROM link__mysql_server__tag";
        $res = $db->sql_query($sql);
        while ($ob  = $db->sql_fetch_object($res)) {

            $data['tag_selected'][$ob->id_mysql_server][] = $ob->id_tag;
        }

        $this->set('data', $data);
    }

    public function getClients()
    {

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * from client order by libelle";
        $res = $db->sql_query($sql);

        $data['client'] = array();

        while ($ob = $db->sql_fetch_object($res)) {
            $tmp            = [];
            $tmp['id']      = $ob->id;
            $tmp['libelle'] = $ob->libelle;

            $data['client'][] = $tmp;
        }

        return $data['client'];
    }

    public function getEnvironments()
    {

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * from environment order by libelle";
        $res = $db->sql_query($sql);

        $data['environment'] = array();
        while ($ob                  = $db->sql_fetch_object($res)) {
            $tmp            = [];
            $tmp['id']      = $ob->id;
            $tmp['libelle'] = $ob->libelle;

            $data['environment'][] = $tmp;
        }


        return $data['environment'];
    }

    public function add()
    {
        $db = Sgbd::sql(DB_DEFAULT);
    }

    public function cache()
    {
        $db = Sgbd::sql(DB_DEFAULT);

// Qcache_hits / (Qcache_hits + Com_select )
    }

    public function passwd($param)
    {

        $this->di['js']->addJavascript(array('clipboard.min.js'));

        /**
         * @todo Add a new version code_javascript  => Once
         */
        /*
          $this->di['js']->code_javascript('(function() {
          new Clipboard(".copy-button");
          })();');
         */

        Crypt::$key       = CRYPT_KEY;
        $data['password'] = Crypt::decrypt($param[0]);
        $this->set('data', $data);
    }

    public function show($param)
    {
        $db = Sgbd::sql(DB_DEFAULT);
    }

    public function updateHostname()
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $data['hostname'] = Extraction::display(array("variables::hostname"));

        foreach ($data['hostname'] as $id_mysql_server => $servers) {
            $server = $servers[''];

            echo $server['hostname']."\n";
            $sql = "UPDATE mysql_server SET display_name ='".$server['hostname']."' WHERE id = ".$id_mysql_server;
            $db->sql_query($sql);
        }
    }

    public function box()
    {
        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * from mysql_server where is_available = 0;";
        $res = $db->sql_query($sql);

        $data = array();

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {


            if (strstr($arr['error'], 'Call Stack:')) {
                //echo end(explode("\n", $server['error']));
                preg_match_all("/\[[\s0-9:_-]+\]\[ERROR\](.*)/", $arr['error'], $output_array);

                if (!empty($output_array[0][0])) {
                    $arr['error'] = $output_array[0][0];
                }

                //echo $server['error'];
            }


            $data['box'][] = $arr;
        }


        $this->set('data', $data);
    }

    public function password($param)
    {
        $id_server = $param[0];

        if (empty($id_server)) {
            throw new \Exception("PMACTRL-748 : Impossible to get id_server, wrong URL ?");
        }



        $db = Sgbd::sql(DB_DEFAULT);

        if ($_SERVER['REQUEST_METHOD'] === "POST") {

            if (!empty($_POST['mysql_server']['passwd'])) {
                $server['mysql_server']['passwd'] = Chiffrement::encrypt($_POST['mysql_server']['passwd']);
                $server['mysql_server']['login']  = $_POST['mysql_server']['login'];
                $server['mysql_server']['id']     = $id_server;

                $ret = $db->sql_save($server);

                if ($ret) {

                    Mysql::onAddMysqlServer();

                    set_flash("success", "Success", "Password updated !");

                    header("location: ".LINK.$this->getClass().'/settings');
                } else {
                    set_flash("error", "Error", "Password not updated !");

                    header("location: ".LINK.$this->getClass().'/'.__FUNCTION__.'/'.$id_server);
                }
            }
        }

        $sql = "SELECT * FROM mysql_server WHERE id =".$id_server;

        $res = $db->sql_query($sql);

        $data['server'] = array();
        while ($ob             = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['server'] = $ob;
        }


        $this->set('data', $data);
    }

    public function acknowledge($param)
    {
        $this->view = false;

        $id_server = $param[0];

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "UPDATE mysql_server set is_acknowledged='".($this->di['auth']->getUser()->id)."' WHERE id=".$id_server.";";
        $db->sql_query($sql);

        header("location: ".LINK.$this->getClass()."/main/");
    }

    public function remove($param)
    {

        Debug::parseDebug($param);
        $this->view = false;
        $id_server  = $param[0];

        $db = Sgbd::sql(DB_DEFAULT);

        // pour eviter d'effacer la base de PmaControl !!!
        $sql = "SELECT * FROM mysql_server WHERE id=".$id_server.";";
        $res = $db->sql_query($sql);
        Debug::sql($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            if ($ob->name != DB_DEFAULT) {
                $sql = "DELETE FROM mysql_server WHERE id=".$id_server.";";
                $db->sql_query($sql);
                Debug::sql($sql);
            }
        }

        header("location: ".LINK.$this->getClass()."/settings/");
    }

    public function acknowledgedBy($param)
    {
        
    }

    public function toggleGeneralLog($param)
    {
        $this->view = false;

        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $general_log     = $param[1];

        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM `mysql_server` WHERE `id`=".$id_mysql_server.";";
        Debug::sql($sql);

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $name = $ob->name;
        }

        $remote = Sgbd::sql($name);

        if ($general_log === "true") {

            $sql2 = "SET GLOBAL `general_log`=ON;";
        } else {
            $sql2 = "SET GLOBAL `general_log`=OFF;";
        }
        Debug::sql($sql2);

        $remote->sql_query($sql2);
        $data['return'] = 'ok';

        echo json_encode($data);
    }

    /*
        To move in Worker 
    */

    public function getDaemonRunning($param)
    {
        Debug::parseDebug($param);
        $lock_directory = EngineV4::PATH_LOCK."*.".EngineV4::EXT_LOCK;

        $elems          = array();
        foreach (glob($lock_directory) as $filename) {

            Debug::debug($filename, "filename");
            if (file_exists($filename)) {
                $json         = file_get_contents($filename);
                $data         = json_decode($json, true);
                $data['time'] = round(microtime(true) - $data['microtime'], 2);

                if ($data['time'] > 1) {
                    $elems[$data['id']] = $data;
                }
            }
        }

        return $elems;
    }

    public function retract($param)
    {
        $this->view = false;

        $id_server = $param[0];

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "UPDATE mysql_server set is_acknowledged='0' WHERE id=".$id_server.";";
        $db->sql_query($sql);

        header("location: ".LINK.$this->getClass()."/main/");
    }
    
}