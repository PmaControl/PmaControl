<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use App\Library\Debug;
use \Glial\Sgbd\Sgbd;


class Graph extends Controller {

    use \App\Mutual\Bigdata;

    public function index() {

        $this->di['js']->addJavascript(array("Chart.min.js"));


        $db = Sgbd::sql(DB_DEFAULT);

        if ($_SERVER['REQUEST_METHOD'] === "POST" && !empty($_POST['mysql_server']['id']) && !empty($_POST['status_value_int']['date'])) {
            $sql = "SELECT * FROM mysql_server where id='" . $_POST['mysql_server']['id'] . "'";
            $res = $db->sql_query($sql);
            while ($ob = $db->sql_fetch_object($res)) {
                $id_mysql_server = $ob->id;

                /* header('location: '.LINK.$this->getClass()
                  .'/index/mysql_server:id:'.$id_mysql_server
                  .'/status_value_int:date:'.$_POST['status_value_int']['date']);
                 */
            }
        } else {

            // get server available
            $sql = "SELECT * FROM mysql_server a WHERE error = '' " . $this->getFilter() . " order by a.name ASC";
            $res = $db->sql_query($sql);
            $data['servers'] = array();
            while ($ob = $db->sql_fetch_object($res)) {
                $tmp = [];
                $tmp['id'] = $ob->id;
                $tmp['libelle'] = $ob->name . " (" . $ob->ip . ")";
                $data['servers'][] = $tmp;
            }


            $interval = array('5 minute', '15 minute', '1 hour', '2 hour', '6 hour', '12 hour', '1 day', '2 day', '1 week', '2 week', '1 month');
            $libelles = array('5 minutes', '15 minutes', '1 hour', '2 hours', '6 hours', '12 hours', '1 day', '2 days', '1 week', '2 weeks',
                '1 month');
            $elems = array(60 * 5, 60 * 15, 3600, 3600 * 2, 3600 * 6, 3600 * 12, 3600 * 24, 3600 * 48, 3600 * 24 * 7, 3600 * 24 * 14, 3600 * 24 * 30);

            $data['interval'] = array();
            $i = 0;
            foreach ($libelles as $libelle) {
                $tmp = [];
                $tmp['id'] = $interval[$i];
                $tmp['libelle'] = $libelle;
                $data['interval'][] = $tmp;
                $i++;
            }


            if (empty($_GET['status_value_int']['date'])) {
                $_GET['status_value_int']['date'] = "6 hour";
            }


            if (!empty($_GET['mysql_server']['id']) && !empty($_GET['status_value_int']['date'])) {


                $cache = $this->cache();
                $main = $this->main();


                $graphs = array_merge($cache, $main);


                debug($graphs);
                //foreach()
            }

            /*
              $sql = "SELECT * FROM status_value_int a

              WHERE a.id_mysql_server = ".$_GET['mysql_server']['id']."
              AND a.id_status_name = '".$_GET['status_name']['id']."'
              and a.`date` > date_sub(now(), INTERVAL ".$_GET['status_value_int']['date'].") ORDER BY a.`date` ASC;";


              $data['sql']   = $sql;
              $data['graph'] = $db->sql_fetch_yield($sql);
              $dates         = [];
              $val           = [];






              $i = 0;

              $old_date = "";
              $points   = [];

              foreach ($data['graph'] as $value) {

              if (empty($old_date) && $_GET['status_value_int']['derivate'] == "1") {

              $old_date  = $value['date'];
              $old_value = $value['value'];
              continue;
              } elseif ($_GET['status_value_int']['derivate'] == "1") {

              $datetime1 = strtotime($old_date);
              $datetime2 = strtotime($value['date']);

              $secs = $datetime2 - $datetime1; // == <seconds between the two times>
              //echo $datetime1. ' '.$datetime2 . ' : '. $secs." ".$value['value'] ." - ". $old_value." => ".($value['value']- $old_value)/ $secs."<br>";

              $derivate = round(($value['value'] - $old_value) / $secs, 2);

              if ($derivate < 0) {
              $derivate = 0;
              }

              $val[] = $derivate;

              //$points[] = "{ x: " . $datetime2 . ", y :" . $derivate . "}";
              } else {
              $val[] = $value['value'];
              }



              //$points[] = "{ x: " . $datetime2 . "000, y :" . $derivate . "}";

              $dates[] = $value['date'];

              $old_date  = $value['date'];
              $old_value = $value['value'];
              }
              } else {
              if (empty($data['servers'])) $data['servers']  = "";
              if (empty($data['status'])) $data['status']   = "";
              if (empty($data['interval'])) $data['interval'] = "";
              if (empty($data['derivate'])) $data['derivate'] = "";


              $data['fields_required'] = 1;
              }

             */
        }

        $this->set('data', $data);
    }

    public function cache() {
        $graph[1]['Title'] = "Percentage of full table scans";
        $graph[1]['Formula'][] = "(Handler_read_rnd_next + Handler_read_rnd) / (Handler_read_rnd_next + Handler_read_rnd + Handler_read_first + Handler_read_next + Handler_read_key + Handler_read_prev)";
        $graph[1]['Comment'] = "This value indicates the percentage of rows that were accessed via full table scans.";
        $graph[1]['Delta'] = true;
        $graph[1]['Percent'] = true;


        return $graph;
    }

    public function innodb() {
        
    }

    public function galera() {
        
    }

    public function myisam() {
        
    }

    public function engine() {
        
    }

    public function main() {
        $graph[1]['Title'] = "Select";
        $graph[1]['Formula'][] = "Com_select";
        $graph[1]['Comment'] = "Nombre de select par seconde";
        $graph[1]['Delta'] = true;
        $graph[1]['Percent'] = false;


        return $graph;
    }

    public function gg($param) {
        Debug::parseDebug($param);
        $this->view = false;

        $cache = $this->cache();
        $main = $this->main();


        $graphs = array_merge($cache, $main);



        $var_to_get = array();

        foreach ($graphs as $graph) {
            foreach ($graph['Formula'] as $formula) {
                Debug::debug($formula);
                $var_to_get = array_merge($var_to_get, $this->getElems($formula));
            }
        }


        array_unique($var_to_get);

        $sql = $this->buildQuery($var_to_get, "status", 671);


        Debug::debug(SqlFormatter::format($sql));
    }

    public function getElems($string) {
        $remove_operator = str_replace(array('+', '*', '-', '/', '(', ')'), array(' ', ' ', ' ', ' ', ' ', ' '), $string);

        $variables = trim(preg_replace('!\s+!', ' ', $remove_operator));

        $elems = explode(' ', $variables);

        array_unique($elems);

        return $elems;
    }

    public function generateGraph($data) {
        $date = implode('","', $dates);
        $vals = implode(',', $val);
        //$arr_points = implode(',', $points);


        $this->di['js']->code_javascript('
var ctx = document.getElementById("myChart");

var myChart = new Chart(ctx, {
    type: "line",
    data: {
        labels: ["' . $date . '"],
        datasets: [{
            label: "' . ucwords($name) . '",
            data: [' . $vals . '],
                borderWidth: 1,
             pointBackgroundColor: "#000",
             pointRadius :0
        }]
    },
    options: {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero:false
                }
            }]
        },
        pointDot : false,
    }
});
');
    }


    public function agregate($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);




        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            if (!empty($_POST['mysql_cluster']['id'])) {

                header('location: '.LINK.$this->getClass().'/'.__FUNCTION__.'/mysql_cluster:id:'.$_POST['mysql_cluster']['id']);
            }

            if (!empty($_POST['mysql_server']['id'])) {

                header('location: '.LINK.$this->getClass().'/'.__FUNCTION__.'/mysql_server:id:'.implode(',', $_POST['mysql_server']['id']));
            }
        } else {

            Debug::debug($_GET);
        }

        //generate liste of cluster (for select)
        $sql = "select group_concat(a.id_mysql_server) as id_mysql_servers, group_concat(b.display_name) as display_name
            from link__architecture__mysql_server a
            INNER JOIN mysql_server b ON a.id_mysql_server= b.id
            group by id_architecture having count(1) > 1;";

        $res = $db->sql_query($sql);

        $data['grappe'] = array();
        while ($ob             = $db->sql_fetch_object($res)) {

            $id_mysql_server_splited = explode(",", $ob->id_mysql_servers);
            $libelle                 = array();

            foreach ($id_mysql_server_splited as $id_mysql_server) {
                $pretty_server = str_replace('"', "'", \App\Library\Display::srv($id_mysql_server));
                $libelle[]     = strip_tags($pretty_server, '<span><small>');
            }

            $item = implode(' , ', $libelle);

            $tmp            = array();
            $tmp['id']      = $ob->id_mysql_servers;
            $tmp['extra']   = array("data-content" => $item);
            $tmp['libelle'] = $ob->display_name;


            $data['grappe'][] = $tmp;
        }

        $this->set('data', $data);


    }

}
