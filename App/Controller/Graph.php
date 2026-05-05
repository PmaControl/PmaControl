<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use App\Library\Debug;
use App\Library\Security\CsrfGuard;
use \Glial\Sgbd\Sgbd;


/**
 * Class responsible for graph workflows.
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
class Graph extends Controller {

    use \App\Mutual\Bigdata;

    private const GRAPH_INTERVALS = ['5 minute', '15 minute', '1 hour', '2 hour', '6 hour', '12 hour', '1 day', '2 day', '1 week', '2 week', '1 month'];
    private const GRAPH_DEFAULT_INTERVAL = '6 hour';

/**
 * Render graph state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/graph/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index() {

        $this->di['js']->addJavascript(array("Chart.min.js"));

        $indexRequest = self::evaluateIndexRequest($_GET, $_SERVER);
        if ($indexRequest['status'] === 405) {
            $this->view = false;
            $this->layout_name = false;
            self::sendGraphError($indexRequest['status'], $indexRequest['body'], $indexRequest['headers']);
            return;
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $filter = $indexRequest['filter'];
        // Keep Form::select pre-selection compatible until filters are passed explicitly to the view.
        $_GET['status_value_int']['date'] = $filter['interval'];
        if ($filter['id_mysql_server'] !== null) {
            $_GET['mysql_server']['id'] = (string) $filter['id_mysql_server'];
        }

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


        $interval = self::GRAPH_INTERVALS;
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


        if ($filter['id_mysql_server'] !== null && !empty($filter['interval'])) {


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

        $this->set('data', $data);
    }

    public static function evaluateIndexRequest(array $get, array $server): array
    {
        if (CsrfGuard::isPost($server)) {
            return self::buildIndexOutcome(405, 'Method Not Allowed', ['Allow' => 'GET']);
        }

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'filter' => self::normalizeIndexFilter($get),
        ];
    }

    public static function normalizeIndexFilter(array $get): array
    {
        return [
            'id_mysql_server' => self::normalizeServerId($get['mysql_server']['id'] ?? null),
            'interval' => self::normalizeInterval($get['status_value_int']['date'] ?? null),
        ];
    }

    public static function normalizeServerId($value): ?int
    {
        if (!is_scalar($value)) {
            return null;
        }

        $id = trim((string) $value);
        if (!ctype_digit($id) || (int) $id < 1) {
            return null;
        }

        return (int) $id;
    }

    public static function normalizeInterval($value): string
    {
        if (!is_scalar($value)) {
            return self::GRAPH_DEFAULT_INTERVAL;
        }

        $interval = trim((string) $value);
        if (!in_array($interval, self::GRAPH_INTERVALS, true)) {
            return self::GRAPH_DEFAULT_INTERVAL;
        }

        return $interval;
    }

    private static function buildIndexOutcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'filter' => self::normalizeIndexFilter([]),
        ];
    }

    private static function sendGraphError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $message;
    }

/**
 * Handle graph state through `cache`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for cache.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::cache()
 * @example /fr/graph/cache
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function cache() {
        $graph[1]['Title'] = "Percentage of full table scans";
        $graph[1]['Formula'][] = "(Handler_read_rnd_next + Handler_read_rnd) / (Handler_read_rnd_next + Handler_read_rnd + Handler_read_first + Handler_read_next + Handler_read_key + Handler_read_prev)";
        $graph[1]['Comment'] = "This value indicates the percentage of rows that were accessed via full table scans.";
        $graph[1]['Delta'] = true;
        $graph[1]['Percent'] = true;


        return $graph;
    }

/**
 * Handle graph state through `innodb`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for innodb.
 * @phpstan-return void
 * @psalm-return void
 * @see self::innodb()
 * @example /fr/graph/innodb
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function innodb() {
        
    }

/**
 * Handle graph state through `galera`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for galera.
 * @phpstan-return void
 * @psalm-return void
 * @see self::galera()
 * @example /fr/graph/galera
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function galera() {
        
    }

/**
 * Handle graph state through `myisam`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for myisam.
 * @phpstan-return void
 * @psalm-return void
 * @see self::myisam()
 * @example /fr/graph/myisam
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function myisam() {
        
    }

/**
 * Handle graph state through `engine`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for engine.
 * @phpstan-return void
 * @psalm-return void
 * @see self::engine()
 * @example /fr/graph/engine
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function engine() {
        
    }

/**
 * Render graph state through `main`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for main.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::main()
 * @example /fr/graph/main
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function main() {
        $graph[1]['Title'] = "Select";
        $graph[1]['Formula'][] = "Com_select";
        $graph[1]['Comment'] = "Nombre de select par seconde";
        $graph[1]['Delta'] = true;
        $graph[1]['Percent'] = false;


        return $graph;
    }

/**
 * Handle graph state through `gg`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for gg.
 * @phpstan-return void
 * @psalm-return void
 * @see self::gg()
 * @example /fr/graph/gg
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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

/**
 * Retrieve graph state through `getElems`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $string Input value for `string`.
 * @phpstan-param mixed $string
 * @psalm-param mixed $string
 * @return mixed Returned value for getElems.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getElems()
 * @example /fr/graph/getElems
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getElems($string) {
        $remove_operator = str_replace(array('+', '*', '-', '/', '(', ')'), array(' ', ' ', ' ', ' ', ' ', ' '), $string);

        $variables = trim(preg_replace('!\s+!', ' ', $remove_operator));

        $elems = explode(' ', $variables);

        array_unique($elems);

        return $elems;
    }

/**
 * Handle graph state through `generateGraph`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int|string,mixed> $data Input value for `data`.
 * @phpstan-param array<int|string,mixed> $data
 * @psalm-param array<int|string,mixed> $data
 * @return void Returned value for generateGraph.
 * @phpstan-return void
 * @psalm-return void
 * @see self::generateGraph()
 * @example /fr/graph/generateGraph
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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


/**
 * Handle graph state through `agregate`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for agregate.
 * @phpstan-return void
 * @psalm-return void
 * @see self::agregate()
 * @example /fr/graph/agregate
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function agregate($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);




        $agregateRequest = self::evaluateAgregateRequest($_GET, $_SERVER);
        if ($agregateRequest['status'] === 405) {
            $this->view = false;
            $this->layout_name = false;
            self::sendGraphError($agregateRequest['status'], $agregateRequest['body'], $agregateRequest['headers']);
            return;
        }

        if ($agregateRequest['redirect_route'] !== '') {
            header('location: '.LINK.$this->getClass().'/'.__FUNCTION__.'/'.$agregateRequest['redirect_route']);
            return;
        }

        Debug::debug($_GET);

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

    public static function evaluateAgregateRequest(array $get, array $server): array
    {
        if (CsrfGuard::isPost($server)) {
            return self::buildAgregateOutcome(405, 'Method Not Allowed', ['Allow' => 'GET']);
        }

        $filter = self::normalizeAgregateFilter($get);
        $redirectRoute = '';
        if (self::hasAgregateQueryString($server)) {
            if ($filter['mysql_cluster_id'] !== '') {
                $redirectRoute = 'mysql_cluster:id:'.$filter['mysql_cluster_id'];
            }
            if ($filter['mysql_server_ids'] !== '') {
                $redirectRoute = 'mysql_server:id:'.$filter['mysql_server_ids'];
            }
        }

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'filter' => $filter,
            'redirect_route' => $redirectRoute,
        ];
    }

    public static function normalizeAgregateFilter(array $get): array
    {
        return [
            'mysql_cluster_id' => self::normalizeAgregateIds($get['mysql_cluster']['id'] ?? null),
            'mysql_server_ids' => self::normalizeAgregateIds($get['mysql_server']['id'] ?? null),
        ];
    }

    public static function normalizeAgregateIds($value): string
    {
        $ids = [];
        foreach (self::flattenAgregateIds($value) as $id) {
            if (!ctype_digit($id) || (int) $id < 1) {
                continue;
            }

            $ids[] = (string) (int) $id;
        }

        return implode(',', array_values(array_unique($ids)));
    }

    private static function flattenAgregateIds($value): array
    {
        if (is_array($value)) {
            $ids = [];
            foreach ($value as $item) {
                $ids = array_merge($ids, self::flattenAgregateIds($item));
            }

            return $ids;
        }

        if (!is_scalar($value)) {
            return [];
        }

        return array_map('trim', explode(',', (string) $value));
    }

    private static function hasAgregateQueryString(array $server): bool
    {
        if (!isset($server['QUERY_STRING']) || !is_scalar($server['QUERY_STRING'])) {
            return false;
        }

        return trim((string) $server['QUERY_STRING']) !== '';
    }

    private static function buildAgregateOutcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'filter' => self::normalizeAgregateFilter([]),
            'redirect_route' => '',
        ];
    }

}
