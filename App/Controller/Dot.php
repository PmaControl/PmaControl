<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Cli\Color;
use \App\Library\Debug;
use \Glial\Sgbd\Sgbd;

//add virtual_ip
// ha proxy

class Dot extends Controller {

    use \App\Mutual\Bigdata;

    CONST COLOR_SUCCESS = "green";
    CONST COLOR_ERROR = "red";
    CONST COLOR_DELAY = "orange";
    CONST COLOR_DONOR = "#90EE90"; //liht green
    CONST COLOR_DONOR_DESYNCED = "yellow";
    CONST COLOR_ARROW_SST = "yellow";
    CONST COLOR_STOPPED = "#5c5cb8";
    CONST COLOR_BLACKOUT = "#000000";
    CONST COLOR_NO_PRIMARY = "red";
    CONST COLOR_BUG = "pink"; //this case should be never happen on Graph
    CONST COLOR_MANUAL_DESYNC = "cyan";
    CONST COLOR_CONNECTING = "green";
    CONST COLOR_SPLIT_BRAIN = "orange";
    CONST COLOR_NODE_RECEIVE_SST = "#000000";

    var $node = array();
    var $segment = array();
    var $sst = false;
    var $sst_target = array();

    /*
     *
     * Contient les serveur suceptible de recevoir le SST
     * A ne pas pas prendre en compte dans el cache des cluster & master / slave
     */
    var $exclude = array();
    var $proxy = array();
    var $proxy_sql = array();
    var $maxscale = array();
    var $ha_proxy = array();
    var $debug = false;
    var $MasterMaster = array();

    public function index() {
        $this->layout_name = 'default';
        $this->title = __("Error 404");
        $this->ariane = " > " . $this->title;
//$this->javascript = array("");
    }

    public function run() {
        $this->view = false;
        $graph = new Alom\Graphviz\Digraph('G');
    }

    /*
     * The goal is this function is to split the graph isloated to produce different dot
     * like that we can provide a better display to dend user and hide the part that they don't need
     * 
     */

    public function splitGraph($param) {

        $this->view = false;

        $db = Sgbd::sql(DB_DEFAULT);
        $ret = $this->generateGroup($param);

        $graphs = [];
        foreach ($ret['groups'] as $list) {


            $tmp = [];

            $tmp['graph'] = $this->generateGraph($list);
            $tmp['servers'] = $list;

            $graphs[] = $tmp;

            if (Debug::$debug) {
                echo str_repeat("#", 79) . "\n";
                echo "SERVER List : " . implode(",", $list) . "\n";
                echo str_repeat("#", 79) . "\n";
            }
        }

//generate standalone server

        $server_alone = $this->getServerStandAlone($ret['grouped']);

        foreach ($server_alone as $server) {
            $tmp = [];
            $tmp['graph'] = $this->generateAlone(array($server));
            $tmp['servers'] = array($server);

            $graphs[] = $tmp;
        }

//echo $graphs[0];
//print_r($graphs);

        Debug::debug($graphs);

        return $graphs;
    }

    public function checkMasterSlave() {
        
    }

    private function nodeMain($node_id, $display_name, $lines, $databases = "") {


        $node = '  ' . $node_id . ' [style="" penwidth="3" fontname="arial" label =<<table border="0" cellborder="0" cellspacing="0" cellpadding="2" bgcolor="white">';

        $node .= $this->nodeHead($display_name);
        foreach ($lines as $line) {
            $node .= $this->nodeLine($line);
        }

        if (!empty($databases)) {
            $node .= '<tr><td bgcolor="lightgrey"><table border="0" cellborder="0" cellspacing="0" cellpadding="2">';


            $node .= '<tr>'
                    . '<td bgcolor="darkgrey" color="white" align="left">M</td>'
                    . '<td bgcolor="darkgrey" color="white" align="left">S</td>'
                    . '<td bgcolor="darkgrey" color="white" align="left">' . __("Databases") . '</td>'
                    . '<td bgcolor="darkgrey" color="white" align="right">' . __("Tables") . '</td>'
                    . '<td bgcolor="darkgrey" color="white" align="right">' . __("Row") . '</td>'
                    . '</tr>';


            foreach ($databases as $database) {
                $node .= '<tr>'
                        . '<td bgcolor="darkgrey" color="white" align="left">';

                if ($database['binlog_do_db'] === "1") {
                    $node .= "&#10004;";
                }

                $node .= '</td>';
                $node .= '<td bgcolor="darkgrey" color="white" align="left">&#10006;</td>'
                        . '<td bgcolor="darkgrey" color="white" align="left">' . $database['name'] . '</td>'
                        . '<td bgcolor="darkgrey" color="white" align="right">' . $database['tables'] . '</td>'
                        . '<td bgcolor="darkgrey" color="white" align="right">' . $database['rows'] . '</td>'
                        . '</tr>';
            }
            $node .= '</table></td></tr>';
        }

        $node .= "</table>> ];\n";

        return $node;
    }

    private function nodeLine($line) {
        $line = '<tr><td bgcolor="lightgrey" align="left">' . $line . '</td></tr>';
        return $line;
    }

    private function nodeHead($display_name) {
        $line = '<tr><td bgcolor="black" color="white" align="center"><font color="white">' . $display_name . '</font></td></tr>';
        return $line;
    }

    public function generateAlone($list_id) {
//label=\"Step 2\";
        $graph = "digraph PmaControl {
rankdir=LR;
 graph [fontname = \"helvetica\"];
 node [fontname = \"helvetica\"];
 edge [fontname = \"helvetica\"];
 node [shape=rect style=filled fontsize=8 fontname=\"arial\" ranksep=0 concentrate=true splines=true overlap=false];\n";


        $graph .= $this->generateNode($list_id);
        $graph .= '}';

        return $graph;
    }

    public function generateGraph($list_id) {
//label=\"Step 2\";
        $graph = "digraph PmaControl {";
        $graph .= "rankdir=LR; splines=ortho;";
        $graph .= " graph [fontname = \"helvetica\"];
 node [fontname = \"helvetica\"];
 edge [fontname = \"helvetica\"];
 node [shape=rect style=filled fontsize=8 fontname=\"arial\" ranksep=0 concentrate=true splines=true overlap=false];\n";




        $gg2 = $this->groupEdgeSegment($list_id);
        $gg = $this->generateCluster($list_id);


        // on genere les fleches avant afin de determiner les double flèches et pas refaire les calculs une deuxieme fois
        $graph .= $this->generateEdge($list_id);

        $graph .= $this->generateNode($list_id);






        //$graph .= $this->generateEdge($list_id);
// on genere les fleches avant afin de determiner les double flèches et pas refaire les calculs une deuxieme fois
//$graph .= $this->generateEdge($list_id);
//$gg2 = $this->generateMerge($list_id);


        if ($this->sst) {
            $graph .= $this->generateEdgeSst();
        }

        $graph .= $this->getHaProxy($list_id);

        $graph .= $gg;
        $graph .= $gg2;


        $graph .= '}';

        /*
          if (!empty($gg2)) {
          echo $graph;
          } */


        return $graph;
    }

    public function generateNode($list_id) {

//Debug::debug($this->proxy);
//les proxy sont quand même prise en compte et retirer après au niveau de la query
//$list_id = array_diff($list_id, $this->proxy);


        $db = Sgbd::sql(DB_DEFAULT);
        $id_mysql_servers = implode(',', $list_id);

        $sql = "SELECT *,b.id as id_db FROM mysql_server a
            INNER JOIN mysql_database b ON b.id_mysql_server = a.id
                WHERE a.id IN (" . $id_mysql_servers . ") ;";

//Debug::debug(SqlFormatter::highlight($sql));

        $res2 = $db->sql_query($sql);

        $databases = [];
        while ($ob = $db->sql_fetch_object($res2)) {

            $databases[$ob->id_mysql_server][$ob->id_db]['name'] = $ob->name;
            $databases[$ob->id_mysql_server][$ob->id_db]['tables'] = $ob->tables;
            $databases[$ob->id_mysql_server][$ob->id_db]['rows'] = number_format($ob->rows, 0, '.', ' ');
            $databases[$ob->id_mysql_server][$ob->id_db]['size'] = $ob->data_length + $ob->data_free + $ob->index_length;
            $databases[$ob->id_mysql_server][$ob->id_db]['binlog_do_db'] = $ob->binlog_do_db;
            $databases[$ob->id_mysql_server][$ob->id_db]['binlog_ignore_db'] = $ob->binlog_ignore_db;
        }

        $sql = "SELECT a.*,b.*,GROUP_CONCAT(c.ip) AS vip FROM mysql_server a
            INNER JOIN mysql_replication_stats b ON b.id_mysql_server = a.id
            LEFT JOIN `virtual_ip` c ON c.id_mysql_server = a.id
            
                WHERE a.id IN (" . $id_mysql_servers . ")
             GROUP BY a.id;";

//Debug::debug(SqlFormatter::highlight($sql));

        $res3 = $db->sql_query($sql);

        $ret = "";

        while ($ob = $db->sql_fetch_object($res3)) {
            $lines = ['IP : ' . $ob->ip];

            $tmp_db = "";


            if (!empty($ob->vip)) {
                $lines[] = "vIP : " . $ob->vip;
            }

            $lines = array_merge($lines, array($ob->version, "Time zone : " . $ob->time_zone, "Binlog format : " . $ob->binlog_format));

            if (!empty($databases[$ob->id_mysql_server]) && count($databases[$ob->id_mysql_server]) > 0) {
                $tmp_db = $databases[$ob->id_mysql_server];
            }


            $open = false;
            foreach ($this->MasterMaster as $key => $master) {


                if (in_array($ob->id_mysql_server, $master)) {
                    $ret .= "subgraph mastermaster_" . $key . " { \n";
                    $ret .= "rank = same;";
                    $open = true;
                }
            }


            $ret .= $this->getColorNode($ob);
            $ret .= $this->nodeMain($ob->id_mysql_server, $ob->name, $lines, $tmp_db);
            if ($open) {
                $ret .= "}\n";
            }
        }
        return $ret;
    }

    public function generateEdge($list_id) {
        $db = Sgbd::sql(DB_DEFAULT);
        $id_mysql_servers = implode(',', $list_id);

        $sql = "(SELECT a.*, b.*, c.*, d.id as id_master, a.id as id_slave FROM mysql_server a
            INNER JOIN mysql_replication_stats b ON b.id_mysql_server = a.id
            INNER JOIN mysql_replication_thread c ON c.id_mysql_replication_stats = b.id
            INNER JOIN mysql_server d ON d.ip = c.master_host AND d.port = a.port
                WHERE a.id IN (" . $id_mysql_servers . ") " . $this->getFilter() . ")";


// cas des vip

        $sql .= " UNION ";
        $sql .= "(SELECT a.*, b.*, c.*, d.id_mysql_server as id_master, a.id as id_slave FROM mysql_server a
            INNER JOIN mysql_replication_stats b ON b.id_mysql_server = a.id
            INNER JOIN mysql_replication_thread c ON c.id_mysql_replication_stats = b.id
            INNER JOIN `virtual_ip` d ON d.ip = c.master_host
                WHERE a.id IN (" . $id_mysql_servers . ") " . $this->getFilter() . ")";

        $sql .= " UNION ";
        $sql .= "(SELECT a.*, b.*, c.*, d.id_mysql_server as id_master, a.id as id_slave FROM mysql_server a
            INNER JOIN mysql_replication_stats b ON b.id_mysql_server = a.id
            INNER JOIN mysql_replication_thread c ON c.id_mysql_replication_stats = b.id
            INNER JOIN `virtual_ip` d ON d.hostname = c.master_host
                WHERE a.id IN (" . $id_mysql_servers . ") " . $this->getFilter() . ")";


        if (Debug::$debug) {
            echo SqlFormatter::format($sql);
        }

        $res = $db->sql_query($sql);

        $ret = "";


        $link = array();
        while ($ob = $db->sql_fetch_object($res)) {

            if (empty($this->exclude[$ob->id_mysql_server])) {


                if ($ob->id_master > $ob->id_slave) {
                    $link[] = $ob->id_slave . ":" . $ob->id_master;
                } else {
                    $link[] = $ob->id_master . ":" . $ob->id_slave;
                }

                $ret .= $this->getColorEdge($ob);
            }
//$master[$ob->id_master] = $ob->id_slave;
        }


//detection Master/Master
        $sum = array_count_values($link);

        //Debug::debug($sum);

        foreach ($sum as $key => $val) {
            if ($val === 2) {
                $this->MasterMaster[] = explode(":", $key);
            }
        }

        //Debug::debug($this->MasterMaster);


        return $ret;
    }

    public function getColorEdge($ob) {
        $edge = [];
        $label = " ";
        $style = "filled";

        if ($ob->thread_io === "Yes" && $ob->thread_sql === "Yes" && $ob->time_behind === "0") {
            $edge['color'] = self::COLOR_SUCCESS;
        } elseif ($ob->thread_io === "Yes" && $ob->thread_sql === "Yes" && $ob->time_behind !== "0") {
            $edge['color'] = self::COLOR_DELAY;
            $label = $ob->time_behind . " sec";
        } else if (($ob->last_io_errno !== "0" || $ob->last_sql_errno !== "0") && ( $ob->thread_io == "Yes" || $ob->thread_sql == "Yes")) {
            $edge['color'] = self::COLOR_ERROR;
        } else if ($ob->last_io_errno !== "0" || $ob->last_sql_errno !== "0" && $ob->thread_io == "No" && $ob->thread_sql == "No") {
            $edge['color'] = self::COLOR_BLACKOUT;
        } else if ($ob->thread_io == "0" && $ob->thread_sql == "0") {
            $edge['color'] = self::COLOR_STOPPED;
        } else if ($ob->thread_io == "0" && $ob->thread_sql == "Connecting") { // replace int by sth else
            $edge['color'] = self::COLOR_CONNECTING;
            $style = "dotted";
        } else {
            $edge['color'] = "pink";
        }

        return " " . $ob->id_master . " -> " . $ob->id_slave
                . " [ arrowsize=\"1.5\" style=" . $style . ",penwidth=\"2\" fontname=\"arial\" fontsize=8 color =\""
                . $edge['color'] . "\" label=\"" . $label . "\"  edgetarget=\"" . LINK . "mysql/thread/"
                . str_replace('_', '-', $ob->name) . "/\" edgeURL=\"" . LINK . "mysql/thread/"
                . str_replace('_', '-', $ob->name) . "/" . $ob->thread_name . "\"];\n";
        ;
    }

    public function generateEdgeSst() {

        $ret = "";
        foreach ($this->exclude as $key => $value) {
            $label = "SST";

            $ret .= " " . $value . " -> " . $key
                    . " [ arrowsize=\"1.5\" ,penwidth=\"2\" rank=same; fontname=\"arial\" fontsize=8 color =\""
                    . self::COLOR_ARROW_SST . "\" label=\"" . $label . " 50%\"];\n";
        }

        $this->sst = false;

        return $ret;
    }

    public function getColorNode($object) {

//COLOR_DONOR

        if ($object->is_available) {

            if (!empty($this->node[$object->id_mysql_server])) {
                $color_node = $this->node[$object->id_mysql_server];
            } else {
                $color_node = self::COLOR_SUCCESS;
            }
        } else {
            $color_node = self::COLOR_ERROR;
        }

        if (!empty($this->exclude[$object->id_mysql_server])) {
            $color_node = self::COLOR_NODE_RECEIVE_SST;
        }

        //Debug::debug($color_node);

        return "node [color = \"" . $color_node . "\"];\n";
    }

    public function generateGroup($param) {

        Debug::parseDebug($param);
        $this->view = false;
        $db = Sgbd::sql(DB_DEFAULT);

        $tmp_group = [];

//case of Master / Slave
        $sql = "SELECT a.*, b.*, c.*, d.id as id_master, a.id as id_slave FROM mysql_server a
            INNER JOIN mysql_replication_stats b ON b.id_mysql_server = a.id
            INNER JOIN mysql_replication_thread c ON c.id_mysql_replication_stats = b.id
            INNER JOIN mysql_server d ON d.ip = c.master_host AND d.port = a.port WHERE 1 " . $this->getFilter();

        if (Debug::$debug) {
            debug($sql);
        }

        $res = $db->sql_query($sql);

        $id_group = 0;

        $tmp_group = [];
        while ($ob = $db->sql_fetch_object($res)) {

            $tmp_group[$id_group][] = $ob->id_master;
            $tmp_group[$id_group][] = $ob->id_slave;
            $id_group++;
        }
        $master_slave = $tmp_group;

//case of Galera Cluster
        $sql = "SELECT * FROM galera_cluster_node";
        $ret = "";

        $res = $db->sql_query($sql);

        $tmp_group = [];

        while ($ob = $db->sql_fetch_object($res)) {

            $tmp_group[$ob->id_galera_cluster_main][] = $ob->id_mysql_server;
            $grouped[] = $ob->id_mysql_server;
        }

        $galera = $tmp_group;

        Debug::debug($galera);


// cas des SST (regrouper les serveurs en cours de transfert avec le cluster auquel il est censé être rataché)
        $sst = [];
        $sql = "SELECT * FROM galera_cluster_node b WHERE b.comment = 'Donor/Desynced'";
        $res = $db->sql_query($sql);
        while ($ob = $db->sql_fetch_object($res)) {

            $id_mysql_server = $this->getDestinationSst($ob);

            $sst[$ob->id_mysql_server] = $id_mysql_server;
        }

// cas des segments (cluster lier : un galera cluster de 3 noeuds sur 2 continents)
        $sql = "SELECT group_concat(b.id_mysql_server) as id_mysql_server
            FROM `galera_cluster_main` a
            INNER JOIN galera_cluster_node b ON a.id = b.id_galera_cluster_main
            GROUP BY a.name";


        $tmp_group = [];
        $res = $db->sql_query($sql);
        while ($ob = $db->sql_fetch_object($res)) {

            $tmp = explode(',', $ob->id_mysql_server);


//ratachement des serveur en cours de SST
            foreach ($sst as $key => $value) {
                if (in_array($key, $tmp)) {
                    $tmp[] = $value;
                }
            }

            $tmp_group[] = $tmp;
        }
        $segments = $tmp_group;


//case des ha_proxy

        $sql = "SELECT a.ip as haproxy, a.vip, a.hostname, b.id, b.port port_input, group_concat(c.ip, ':', c.port) as allip FROM haproxy_main a
             INNER JOIN `haproxy_main_input` b ON a.id = b.id_haproxy_main
             INNER JOIN `haproxy_main_output` c ON b.id = c.id_haproxy_input
             GROUP BY a.`ip`, a.vip, b.id, b.port";

        $res = $db->sql_query($sql);

        $tmp_group = [];

        $i = 0;
        while ($ob = $db->sql_fetch_object($res)) {

            $servers = explode(',', $ob->allip);


            $this->ha_proxy[$ob->id]["ip"] = $ob->haproxy;
            $this->ha_proxy[$ob->id]["vip"] = $ob->vip;
            $this->ha_proxy[$ob->id]["port"] = $ob->port_input;
            $this->ha_proxy[$ob->id]["id"] = $ob->id;
            $this->ha_proxy[$ob->id]["hostname"] = $ob->hostname;


            $sqls = array();
            foreach ($servers as $server) {
                $split = explode(':', $server);

                $sqls[] = "SELECT id FROM mysql_server WHERE ip= '" . $split[0] . "' AND port = " . $split[1] . "";
            }

            $sql2 = "(" . implode("\n) UNION (", $sqls) . ")";
            Debug::debug(SqlFormatter::highlight($sql2));
            $res2 = $db->sql_query($sql2);


            while ($ob2 = $db->sql_fetch_object($res2)) {
                $tmp_group[$i][] = $ob2->id;

                $this->ha_proxy[$ob->id]['linked'][] = $ob2->id;
                $this->ha_proxy[$ob->id]['master'][] = $ob2->id;
            }


            $sql3 = "SELECT a.id FROM mysql_server a
            INNER JOIN mysql_replication_stats b ON a.id = b.id_mysql_server
            INNER JOIN mysql_replication_thread c ON b.id = c.id_mysql_replication_stats
            WHERE (c.master_host = '" . $ob->haproxy . "' OR c.master_host = '" . $ob->vip . "') AND c.master_port = " . $ob->port_input . "
            ";

            Debug::debug(SqlFormatter::highlight($sql3));
            $res3 = $db->sql_query($sql3);


            while ($ob3 = $db->sql_fetch_object($res3)) {
                $tmp_group[$i][] = $ob3->id;

                $this->ha_proxy[$ob->id]['linked'][] = $ob3->id;
                $this->ha_proxy[$ob->id]['slave'][] = $ob3->id;
            }

            $i++;
        }


        $ha_proxy = $tmp_group;

        Debug::debug($ha_proxy);




// cas des ip virtuel
        $vips = $this->getGroupVip();





//cas des serveurs avec plusieurs instances
        $sql = "SELECT group_concat(id) as allid from mysql_server GROUP BY ip having count(1) > 1;";
        $res = $db->sql_query($sql);

        $tmp_group = [];
        while ($ob = $db->sql_fetch_object($res)) {
            $tmp_group[] = explode(',', $ob->allid);
        }
        $instance = $tmp_group;

//Debug::debug($master_slave);
//Debug::debug($ha_proxy);


        $groups = $this->array_merge_group(array_merge($galera, $instance, $master_slave, $segments, $ha_proxy, $vips));

        $result['groups'] = $groups;
        $result['grouped'] = $this->array_values_recursive($result['groups']);

        //Debug::debug($result['groups']);

        return $result;
    }

    public function renderer($param) {
        $data['groups'] = $this->splitGraph($param);
        $data['svg'] = [];

        foreach ($data['groups'] as $dot) {


            $data['svg'][] = $this->dotToSvg($dot['graph']);
        }

        $this->set('data', $data);
        return $data['svg'];
    }

    public function dotToSvg($dot) {
        file_put_contents(TMP . "tmp.dot", $dot);

        $cmd = "dot " . TMP . "/tmp.dot -Tsvg -o " . TMP . "/image.svg";
//$cmd = "neato ".TMP."/tmp.dot -Tsvg -o ".TMP."/image.svg";
        shell_exec($cmd);

        return file_get_contents(TMP . "/image.svg");
    }

    public function getServerStandAlone($grouped) {
        $this->view = false;
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT id from mysql_server a WHERE 1 " . $this->getFilter();
        $res = $db->sql_query($sql);
        $all = [];
        while ($ob = $db->sql_fetch_object($res)) {

            $all[] = $ob->id;
        }

        $this->server_alone = array_diff($all, $grouped);
        return $this->server_alone;
    }

//to mutualize
//considere mysql_server a
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
            $where .= " AND a.id_environment = '" . $environment . "'";
        }

        if (!empty($client)) {
            $where .= " AND a.id_client = '" . $client . "'";
        }

        if (!empty($this->exclude)) {
            $tab = array_flip($this->exclude);
            $where .= " AND a.id NOT IN  (" . implode(',', $tab) . ")";
        }

        return $where;
    }

//each minutes ?
    public function generateCache($param) {
        if (!empty($param)) {
            foreach ($param as $elem) {
                if ($elem == "--debug") {
                    $this->debug = true;
                    echo Color::getColoredString("DEBUG activated !", "yellow") . "\n";
                }
            }
        }

        $db = Sgbd::sql(DB_DEFAULT);

        $this->view = false;
        $graphs = $this->splitGraph($param);


        $sql = "BEGIN";
        $db->sql_query($sql);

        $sql = "DELETE FROM link__architecture__mysql_server WHERE 1";
        $db->sql_query($sql);

        $sql = "DELETE FROM architecture WHERE 1";
        $db->sql_query($sql);


//@TODO : we parse more graph that we should to do
//echo "Nombre de graphs : ".count($graphs)."\n";

        foreach ($graphs as $graph) {
            $date = date('Y-m-d H:i:s');

            $svg = $this->dotToSvg($graph['graph']);

            preg_match_all("/width=\"([0-9]+)pt\"\sheight=\"([0-9]+)pt\"/", $svg, $output);

            $sql = "INSERT INTO architecture (`date`, `data`, `display`,`height`,`width`)
            VALUES ('" . $date . "','" . $db->sql_real_escape_string($graph['graph']) . "','" . $db->sql_real_escape_string($svg) . "',"
                    . $output[2][0] . " ," . $output[1][0] . ")";

            $db->sql_query($sql);

            $sql = "SELECT max(id) as last FROM architecture";
            $res = $db->sql_query($sql);

            while ($ob = $db->sql_fetch_object($res)) {
                $id_architecture = $ob->last;
            }


//@TODO : we parse more graph that we should to do
//echo "Nombre de graphs : ".count($graphs)."\n";
            if (!is_array($graph['servers'])) {
                continue;
            }

//Debug::debug($graph['servers']);


            foreach ($graph['servers'] as $id_mysql_server) {
                /* $table =[];
                  $table['link__architecture__mysql_server']['id_architecture'] = $id_architecture;
                  $table['link__architecture__mysql_server']['id_mysql_server'] = $id_mysql_server;
                 */
                $sql = "INSERT INTO link__architecture__mysql_server (`id_architecture`, `id_mysql_server`) VALUES ('" . $id_architecture . "','" . $id_mysql_server . "')";
                $db->sql_query($sql);
            }
        }

        $sql = "COMMIT";
        $db->sql_query($sql);

//Debug::debug($this->segment);
    }

    public function generateCluster($list_id) {
        $db = Sgbd::sql(DB_DEFAULT);
        $this->view = false;
        $id_mysql_servers = implode(',', $list_id);

        $sql = "SELECT *,a.id as id_galera_cluster_main FROM galera_cluster_main a
             INNER JOIN galera_cluster_node b ON a.id = b.id_galera_cluster_main
             WHERE b.id_mysql_server IN (" . $id_mysql_servers . ")  ORDER BY a.name,a.segment;";


//Debug::debug(SqlFormatter::highlight($sql));



        $res = $db->sql_query($sql);
        $galera_name[] = array();


        $current_cluster = "";
        $super_cluster_open = false;

        $segments = array();


        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $galeras[$arr['name']][$arr['segment']][$arr['id_mysql_server']] = $arr;
        }


        $ret = "";

        if (!empty($galeras)) {


            Debug::debug($galeras);

            foreach ($galeras as $name_galera => $galera) {

                $ret .= 'subgraph cluster_' . str_replace('-', '', $name_galera) . ' {' . "\n";
                $ret .= 'rankdir="LR";';
                $ret .= 'style=dashed;penwidth=1.5; color=lightgrey;' . "\n";
                $ret .= 'label = "Galera : ' . $name_galera . '";';


//$ret .= $this->display_segment($galera, $name_galera);


                if (count($galeras[$name_galera]) > 1) {
                    $ret .= $this->display_segment($galera, $name_galera);
                } else {

                    Debug::debug(end($galera));
                    $ret .= $this->display_node_galera(end($galera));
                }

                $ret .= ' }' . "\n";
            }
        }


        /*
          foreach ($segments as $segment) {
          $ret .= implode(" -> ", $segment).";";
          } */


//Debug::debug($ret);

        return $ret;
    }

    private function display_segment($galera, $name_galera) {
        $ret = "";

        foreach ($galera as $segment_name => $segment) {

            $ret .= 'subgraph cluster_' . str_replace('-', '', $name_galera) . "_" . $segment_name . " {\n";
            $ret .= 'label = "Segment : ' . $segment_name . '";' . "\n";
            $ret .= 'color=' . self::COLOR_SUCCESS . ';style=dashed;penwidth=1.5;fontname="arial";' . "\n";


            $ret .= $this->display_node_galera($segment);
            $ret .= ' }' . "\n";
        }

        return $ret;
    }

    private function display_node_galera($segment) {

        Debug::debug($segment);

        $ret = "";

        foreach ($segment as $id_mysql_server => $server) {



            if ($server['comment'] === "Donor/Desynced" && $server['desync'] === "OFF") {
                $this->getDestinationSst($server);
            }

            $this->node[$server['id_mysql_server']] = $this->getColorGalera($server);
            $ret .= '' . $server['id_mysql_server'] . ';';
        }
        return $ret;
    }

    private function array_values_recursive($ary) {
        $lst = array();
        foreach (array_keys($ary) as $k) {
            $v = $ary[$k];
            if (is_scalar($v)) {
                $lst[] = $v;
            } elseif (is_array($v)) {
                $lst = array_merge($lst, $this->array_values_recursive($v)
                );
            }
        }
        return $lst;
    }

    /*
     * merge des groups de value, pour faire des regroupement avec les groupes qui ont les même id et retire les doublons
     */

    private function array_merge_group($array) {

        Debug::debug($array);

        $all_values = $this->array_values_recursive($array);
        $group_merge = [];
        foreach ($all_values as $value) {
            $tmp = [];
            foreach ($array as $key => $sub_group) {
                if (in_array($value, $sub_group)) {
                    $tmp = array_merge($sub_group, $tmp);
                    unset($array[$key]);
                }
            }
            $array[] = array_unique($tmp);
        }
//@TODO : Improvement because we parse all_value and we delete all array from orgin no need to continue;
        return $array;
    }

    private function generateMerge($list_id) {
        $db = Sgbd::sql(DB_DEFAULT);
        $this->view = false;

        $id_mysql_servers = implode(',', $list_id);

        $sql = "SELECT group_concat(id) as id_all,ip,id FROM mysql_server a
             WHERE a.id IN (" . $id_mysql_servers . ") " . $this->getFilter() . " GROUP BY ip having count(1)>1;";

        $ret = "";


//echo  $sql."\n";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $ret .= 'subgraph cluster_' . $ob->id . ' {';
//$ret .= 'rankdir="LR";';
            $ret .= 'color=blue;fontname="arial";';
            $ret .= 'label = "IP : ' . $ob->ip . '";';

            $ids = explode(",", $ob->id_all);
            foreach ($ids as $id) {
                $ret .= '' . $id . ';';
            }
            $ret .= ' }' . "\n";
        }

        return $ret;
    }

    public function getColorGalera($ob) {

//COLOR_DONOR
        if ($ob['comment'] === "Synced") {
            $color_node = self::COLOR_SUCCESS;
        } else if ($ob['desync'] == "ON") {
            $color_node = self::COLOR_MANUAL_DESYNC;
        } else if ($ob['comment'] == "Donor/Desynced" && stristr($ob['comment'], "xtrabackup")) {
            $color_node = self::COLOR_DONOR;
        } else if ($ob['comment'] == "Donor/Desynced" && stristr($ob['comment'], "xtrabackup") === false) {
            $color_node = self::COLOR_DONOR_DESYNCED;
        } else if ($ob['comment'] == "Joined") {
            $color_node = self::COLOR_ERROR;
        } else if ($ob['comment'] == "Joining") {
            $color_node = self::COLOR_ERROR;
        } else if ($ob['cluster_status'] == "non-Primary") {
            $color_node = self::COLOR_SPLIT_BRAIN;
        } else {
            $color_node = self::COLOR_BUG;
        }

        return $color_node;
    }

    public function getDestinationSst($ob) {


        $db = Sgbd::sql(DB_DEFAULT);

        $addrs = explode(",", $ob['incoming_addresses']);

        $couple = [];
        foreach ($addrs as $addr) {
            $part = explode(":", $addr);
            $couple[] = "SELECT * FROM mysql_server WHERE `ip`='" . $part[0] . "' AND port='" . $part[1] . "' and error != ''";
        }
        $sql = "(" . implode("\n) UNION (", $couple) . ");";
        Debug::debug(SqlFormatter::highlight($sql));
        $res = $db->sql_query($sql);


        $i = 0;
        while ($ob2 = $db->sql_fetch_object($res)) {

//	    if(in_array($ob2->id, $this->sst_target)	

            $this->exclude[$ob2->id] = $ob['id_mysql_server'];
            $id_mysql_server = $ob2->id;
            $i++;
        }

        if ($i > 1) {
            throw new \Exception("Warning : SST more than 1 candidate", 80);
        }

        return $id_mysql_server;
    }

    public function groupEdgeSegment($list_id) {
        $ret = '';
        $id_mysql_servers = implode(',', $list_id);

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT a.name,a.segment as segment, a.id as id,group_concat(b.id_mysql_server) as id_mysql_server
            FROM `galera_cluster_main` a
            INNER JOIN galera_cluster_node b ON b.id_galera_cluster_main = a.id
            WHERE a.name in(SELECT name FROM `galera_cluster_main` GROUP BY name having count(1) > 1)
            AND b.id_mysql_server IN (" . $id_mysql_servers . ")
            GROUP BY a.name,a.segment";

        if (Debug::$debug) {

//echo SqlFormatter::format($sql);
        }

        $res = $db->sql_query($sql);


        $val = array();
        while ($ob = $db->sql_fetch_object($res)) {

            $this->segment[$ob->name][$ob->id]['segment'] = $ob->segment;
            $this->segment[$ob->name][$ob->id]['id_mysql_server'] = $ob->id_mysql_server;

            $median_id = $this->median(explode(",", $ob->id_mysql_server));

            $val[] = $median_id;
            $cluster_name[$median_id] = 'cluster_' . str_replace('-', '', $ob->name) . $ob->segment;
        }

        $nb_segments = count($val);

        for ($pn = 1; $pn < $nb_segments; $pn++) {
            for ($on = $pn + 1; $on <= $nb_segments; $on++) {
//printf("%u link %u\n", $pn, $on);

                $contrainte = '';

                $gg = $val[$pn - 1] . ' -> ' . $val[$on - 1] . " [arrowsize=0, color=green, penwidth=0, " . $contrainte . " dir=both,ltail="
                        . $cluster_name[$val[$pn - 1]] . ",lhead=" . $cluster_name[$val[$on - 1]] . "];\n";

//debug($gg);

                $ret .= $gg;

                /* */
//$ret .= $cluster_name[$val[$on - 1]].' -> '.$cluster_name[$val[$pn - 1]]." [arrowsize=\"1.5\", penwidth=\"2\", dir=both]\n";
            }
        }


        return $ret;
    }

    public function median($arr) {
//Sort Array Numerically, because Median calculation has to have a set in order
        sort($arr, SORT_NUMERIC);

//Get Total Amount Of Elements
        $count = count($arr);

//Need to get the mid point of array.  Use Floor because we always want to round down.
        $mid = floor($count / 2);
        return $arr[$mid];
    }

    public function getHaProxy($list_id) {

        //Debug::debug($this->ha_proxy);

        $ret = "";

        foreach ($this->ha_proxy as $id_haproxy => $proxy) {

            Debug::debug($proxy);

            $inter = array_intersect($proxy['linked'], $list_id);

            if (count($inter) != 0) {

                Debug::debug($inter);

                $ret .= $this->nodeProxy($id_haproxy);
            }
        }

        return $ret;
    }

    private function nodeProxy($id_haproxy) {

        $node = '  HA' . $id_haproxy . ' [style="" penwidth="3" fontname="arial" label =<<table border="0" cellborder="0" cellspacing="0" cellpadding="2" bgcolor="white">';

        $node .= $this->nodeHead("HA_PROXY_" . $id_haproxy);


        /*
         *             $this->ha_proxy[$ob->id]["ip"]   = $ob->haproxy;
          $this->ha_proxy[$ob->id]["vip"]  = $ob->vip;
          $this->ha_proxy[$ob->id]["port"] = $ob->port_input;
          $this->ha_proxy[$ob->id]["id"] = $ob->id;
          $this->ha_proxy[$ob->id]["hostname"] = $ob->hostname;
         */

        $lines = array(
            "Hostname : " . $this->ha_proxy[$id_haproxy]['hostname'],
            "IP : " . $this->ha_proxy[$id_haproxy]['ip'],
            "vIP : " . $this->ha_proxy[$id_haproxy]['vip'],
            "Port : " . $this->ha_proxy[$id_haproxy]['port']);

        foreach ($lines as $line) {
            $node .= $this->nodeLine($line);
        }

        /*
          if (!empty($databases)) {
          $node .= '<tr><td bgcolor="lightgrey"><table border="0" cellborder="0" cellspacing="0" cellpadding="2">';
          foreach ($databases as $database) {
          $node .= '<tr>'
          .'<td bgcolor="darkgrey" color="white" align="left">'.$database['name'].'</td>'
          .'<td bgcolor="darkgrey" color="white" align="right">'.$database['tables'].'</td>'
          .'<td bgcolor="darkgrey" color="white" align="right">'.$database['rows'].'</td>'
          .'</tr>';
          }
          $node .= '</table></td></tr>';
          } */

        $node .= "</table>> ];\n";


        $edge['color'] = "green";
        $style = "filled";

        foreach ($this->ha_proxy[$id_haproxy]['slave'] as $key => $value) {

            $node .= " " . 'HA' . $id_haproxy . " -> " . $value
                    . " [ arrowsize=\"1.5\" style=" . $style . ",penwidth=\"2\" fontname=\"arial\" fontsize=8 color =\""
                    . $edge['color'] . "\" ];\n";
            ;
        }

        $i = 0;

        foreach ($this->ha_proxy[$id_haproxy]['master'] as $key => $value) {

            if ($i == 0) {
                $style = "filled";
                $edge['color'] = "green";
            } else {
                $edge['color'] = "blue";
                $style = "dashed";
            }


            $node .= " " . $value . " -> HA" . $id_haproxy
                    . " [ dir=both arrowsize=\"1.5\" style=" . $style . ",penwidth=\"2\" fontname=\"arial\" fontsize=8 color =\""
                    . $edge['color'] . "\" ];\n";
            ;

            $i++;
        }


        return $node;
    }

    function getDataDir($param) {
        $this->view = false;

        Debug::parseDebug($param);


        $sql = $this->buildQuery(array("datadir"), "variables", 635);


        Debug::debug(SqlFormatter::format($sql));
    }

    public function getGroupVip() {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "(SELECT a.id_mysql_server as id1, c.id_mysql_server as id2 FROM `virtual_ip` a
            INNER JOIN mysql_replication_thread b ON a.ip = b.master_host
            INNER JOIN mysql_replication_stats c ON b.id_mysql_replication_stats = c.id)
            UNION (SELECT a.id_mysql_server as id1, c.id_mysql_server as id2 FROM `virtual_ip` a
            INNER JOIN mysql_replication_thread b ON a.hostname = b.master_host
            INNER JOIN mysql_replication_stats c ON b.id_mysql_replication_stats = c.id);";

        $res = $db->sql_query($sql);

        $tmp_group = [];

        while ($ob = $db->sql_fetch_object($res)) {
            $tmp_group[] = array($ob->id1, $ob->id2);
        }

        return $tmp_group;
    }

    public function getMasterMAster() {
        
    }

}

/*
 *
 * $array = array('Alpha', 'Beta', 'Gamma', 'Sigma');

  function depth_picker($arr, $temp_string, &$collect) {
  if ($temp_string != "")
  $collect []= $temp_string;

  for ($i=0; $i<sizeof($arr);$i++) {
  $arrcopy = $arr;
  $elem = array_splice($arrcopy, $i, 1); // removes and returns the i'th element
  if (sizeof($arrcopy) > 0) {
  depth_picker($arrcopy, $temp_string ." " . $elem[0], $collect);
  } else {
  $collect []= $temp_string. " " . $elem[0];
  }
  }
  }

  $collect = array();
  depth_picker($array, "", $collect);
  print_r($collect);
 */
