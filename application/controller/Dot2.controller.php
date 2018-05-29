<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;
use \Glial\Form\Upload;
use \Glial\Date\Date;
use App\Library\Extraction;

//add virtual_ip
// ha proxy

class Dot2 extends Controller
{

    use \App\Library\Debug;
    use \App\Library\Filter;
    CONST NODE_AVAILABLE      = "green";
    CONST NODE_NOT_ANSWERED   = "orange";
    CONST NODE_ERROR          = "red";
//for galera
    CONST NODE_NOT_PRIMARY    = "blue"; //galera cluster
    CONST NODE_DONOR          = "cyan";
    CONST NODE_DONOR_DESYNCED = "yellow";
    CONST NODE_MANUAL_DESYNC  = "brown";
    CONST NODE_RECEIVE_SST    = "yellow";
    CONST NODE_BUG            = "pink";
    CONST REPLICATION_OK         = "green";
    CONST REPLICATION_IST        = "yellow";
    CONST REPLICATION_SST        = "grey";
    CONST REPLICATION_STOPPED    = "blue";
    CONST REPLICATION_ERROR      = "red";
    CONST REPLICATION_BLACKOUT   = "pink";
    CONST REPLICATION_DELAY      = "orange";
    CONST REPLICATION_NA         = "grey";
    CONST REPLICATION_CONNECTING = "yellow";
    CONST GALERA_SPLIT_BRAIN     = "orange";
    CONST GALERA_AVAILABLE       = "green";
    CONST GALERA_UNAVAILABLE     = "red";
    CONST GALERA_NON_PRIMARY     = "blue";

    var $maping_master           = array();
    var $master_slave            = array();
    var $galera_cluster          = array();
//var $mysql_server            = array();
    var $servers                 = array();
    var $slaves                  = array();
    var $groups                  = array();
    var $graph_node              = array();
    var $graph_edge              = array();
    var $graph_master_master     = array();
    var $graph_galera_cluster    = array();
    var $graph_ha_proxy          = array();
    var $graph_group_replication = array();
    var $serveur_affiche         = array();
    var $graph_arbitrator        = array(); // containt all id of arbitrator

    public function getMasterSlave($param)
    {

        $this->parseDebug($param);
        $this->view = false;
        $db         = $this->di['db']->sql(DB_DEFAULT);


        Extraction::setDb($db);
        $this->slaves = Extraction::display(array("slave::master_host", "slave::master_port", "slave::seconds_behind_master", "slave::slave_io_running",
                "slave::slave_sql_running", "slave::replicate_do_db", "slave::replicate_ignore_db", "slave::last_io_errno", "slave::last_io_error",
                "slave::last_sql_error", "slave::last_sql_errno"));


        $id_group  = 0;
        $tmp_group = array();
        foreach ($this->slaves as $id_mysql_server => $rosae) {

            foreach ($rosae as $connection => $slave) {

                if (!empty($this->maping_master[$slave['master_host'].":".$slave['master_port']])) {
                    $id_master = $this->maping_master[$slave['master_host'].":".$slave['master_port']];
                } else {
//$this->debug($slave['master_host'], "master_host --");
//add server generate negative id ?
                    continue;
                }

                $tmp_group[$id_group][] = $id_master;
                $tmp_group[$id_group][] = $id_mysql_server;

                $this->slaves[$id_mysql_server][$connection]['id_master'] = $id_master;



                $id_group++;
            }
        }
        $this->master_slave = $tmp_group;


        $this->debug($this->slaves, "MASTER / SLAVE");

        return $this->master_slave;
    }

    public function getGaleraCluster($param)
    {
        foreach ($this->servers as $server) {
            if (!empty($server['wsrep_on']) && $server['wsrep_on'] === "ON") {
                //debug($server);
                //$server['wsrep_incoming_addresses']
                $tab      = explode(",", $server['wsrep_incoming_addresses']);
                $to_match = $server['ip'].":".$server['port'];


                // the goal is to remove proxy
                if (in_array($to_match, $tab)) {
                    $this->galera_cluster[$server['wsrep_cluster_name']][$server['id_mysql_server']] = $server;
                }
            }
        }


        //debug($this->maping_master);

        $group_galera = array();

        if (count($this->galera_cluster) > 0) {
            $group = 1;
            foreach ($this->galera_cluster as $cluster_name => $servers) {

                $incomming    = array();
                $galera_nodes = array();
                foreach ($servers as $id_mysql_server => $server) {
                    //$group_galera[$group] = $id_mysql_server;
                    // to get offline node
                    $incomming[] = $server['wsrep_incoming_addresses'];



                    $tab      = explode(",", $server['wsrep_incoming_addresses']);
                    $to_match = $server['ip'].":".$server['port'];
                    // the goal is to remove proxy
                    if (in_array($to_match, $tab)) {
                        $galera_nodes[] = $id_mysql_server;
                    }
                }

                $nodes = $this->getAllMemberFromGalera($incomming, $galera_nodes, $group);

                if (count($nodes['all_nodes']) > 0) {
                    $group_galera[$group] = $nodes['all_nodes'];
                }

                foreach ($nodes['all_nodes'] as $id_arbitre) {
                    $this->galera_cluster[$cluster_name][$id_arbitre] = $this->servers[$id_arbitre];
                }




                $group++;
            }


            $this->debug($group_galera);
        }



        return $group_galera;
    }

    private function getAllMemberFromGalera($incomming, $galera_nodes, $group)
    {
        //need dertect split brain !!
        $this->debug($incomming);





        $all_node = array();
        foreach ($incomming as $listing) {
            $ip_port  = explode(",", $listing);
            $all_node = array_merge($all_node, $ip_port);
        }

        //$all_nodes = array_merge($all_node, $galera_nodes);

        $nodes = array_unique($all_node);

        $this->debug($nodes);


        $arbitres = array();


        $group_galera = array();

        foreach ($nodes as $node) {
            if (!empty($node)) {
                if (!empty($this->maping_master[$node])) {
                    $group_galera[] = $this->maping_master[$node];
                } else {
                    // unknow node

                    $this->debug($node, "UNKNOW NODE");
                }
            } else {
                //arbitre
                $arbitres[] = $this->createArbitrator($group);
            }
        }

        $all_nodes = array_merge($group_galera, $galera_nodes, $arbitres);

        //debug($all_nodes);

        $ret['all_nodes'] = $all_nodes;
        $ret['arbitres']  = $arbitres;

        return $ret;
    }

    public function mappingMaster()
    {

        $db  = $this->di['db']->sql(DB_DEFAULT);
        $sql = "SELECT id,ip,port,is_available FROM mysql_server";

        $res = $db->sql_query($sql);

        while ($ar = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $this->maping_master[$ar['ip'].":".$ar['port']] = $ar['id'];
            $this->servers[$ar['id']]                       = $ar;
        }

        return $this->maping_master;
    }

    public function getInfoServer($param)
    {
        $db = $this->di['db']->sql(DB_DEFAULT);

        Extraction::setDb($db);

//binlog-do-db binlog-ignore-db <= to extract from my.cnf ?




        $temp = Extraction::display(array("variables::hostname", "variables::binlog_format", "variables::time_zone", "variables::version",
                "variables::system_time_zone",
                "variables::wsrep_cluster_name", "variables::wsrep_provider_options", "variables::wsrep_on", "variables::wsrep_sst_method", "variables::wsrep_desync",
                "status::wsrep_cluster_status", "status::wsrep_local_state_comment", "status::wsrep_incoming_addresses", "status::wsrep_cluster_size"));

        //debug($temp);

        foreach ($temp as $id_mysql_server => $servers) {

            $server = $servers[''];
            if (!empty($this->servers[$id_mysql_server])) {
                $this->servers[$id_mysql_server] = array_merge($server, $this->servers[$id_mysql_server]);
            } else {
                $this->servers[$id_mysql_server] = $server;
            }
        }
    }

    public function generateGroup($groups)
    {
        $this->parseDebug($param);
        $this->view = false;


//$masterSlave = $this->getMasterSlave($param);
        $groups = $this->array_merge_group($groups);


        $this->debug($groups, "groups");

        $result['groups']  = $groups;
        $result['grouped'] = $this->array_values_recursive($result['groups']);

//$this->debug($result);
        $this->groups = $result;

        return $this->groups;
    }
    /*
     * merge des groups de value, pour faire des regroupement avec les groupes qui ont les même id et retire les doublons
     */

    private function array_merge_group($array)
    {

//$this->debug($array);

        $all_values  = $this->array_values_recursive($array);
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

    private function array_values_recursive($ary)
    {
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

    public function generateGraph($group)
    {
//label=\"Step 2\";
        $graph = "digraph PmaControl {";
        $graph .= "rankdir=LR; splines=ortho;";
        $graph .= " graph [fontname = \"helvetica\"];
 node [fontname = \"helvetica\"];
 edge [fontname = \"helvetica\"];
 node [shape=rect style=filled fontsize=8 fontname=\"arial\" ranksep=0 concentrate=true splines=true overlap=false];\n";




        foreach ($group as $id_mysql_server) {
            $graph .= $this->generateNode($id_mysql_server);
        }
//cas des multi master


        $graph .= $this->generateRankForMM($group);


        $graph .= $this->generateEdge($group);


        $graph .= $this->generateGaleraCluster($group);



        /*
          $gg2 = $this->groupEdgeSegment($list_id);
          $gg = $this->generateCluster($list_id);
          // on genere les fleches avant afin de determiner les double flèches et pas refaire les calculs une deuxieme fois
          $graph .= $this->generateEdge($list_id);

          //$graph .= $this->generateEdge($list_id);
          // on genere les fleches avant afin de determiner les double flèches et pas refaire les calculs une deuxieme fois
          //$graph .= $this->generateEdge($list_id);
          //$gg2 = $this->generateMerge($list_id);

          if ($this->sst) {
          $graph .= $this->generateEdgeSst();
          }
         */

//$graph .= $this->getHaProxy($list_id);
//        $graph .= $gg;
//        $graph .= $gg2;


        $graph .= '}';

        return $graph;
    }
    /*     * **** generate table ******* */

    public function pushServer()
    {
//debug($this->servers);


        foreach ($this->servers as $id_mysql_server => $servers) {
//$server = $servers[''];
//$this->graph_node[$id_mysql_server] = $server;

            if ($this->servers[$id_mysql_server]['is_available'] === "1") {
                $this->graph_node[$id_mysql_server]['color'] = self::NODE_AVAILABLE;
            } else if ($this->servers[$id_mysql_server]['is_available'] === "0") {
                $this->graph_node[$id_mysql_server]['color'] = self::NODE_ERROR;
            } else if ($this->servers[$id_mysql_server]['is_available'] === "2") {
                $this->graph_node[$id_mysql_server]['color'] = self::NODE_NOT_ANSWERED;
            }
        }

        $this->debug($this->graph_node, "GENERATE NODE");


//$this->debug($this->graph_node);
    }

    public function pushUpdateMS()
    {
        foreach ($this->slaves as $connections) {
            foreach ($connections as $slave) {


                //debug($slave);
//$this->graph_edge[$slave['id_mysql_server']][$slave['id_master']] = $slave;
                //si le master est pas dans le monitoring on le saute
                if (empty($slave['id_master'])) {
                    continue;
                }


                $this->graph_edge[$slave['id_mysql_server']][$slave['id_master']]['style'] = "filled";

                if ($slave['seconds_behind_master'] === "0" && $slave['slave_io_running'] === "Yes" && $slave['slave_sql_running'] === "Yes") {
                    $this->graph_edge[$slave['id_mysql_server']][$slave['id_master']]['color'] = self::REPLICATION_OK;
                } else if ($slave['seconds_behind_master'] !== "0" && $slave['slave_io_running'] === "Yes" && $slave['slave_sql_running'] === "Yes") {
                    $this->graph_edge[$slave['id_mysql_server']][$slave['id_master']]['color'] = self::REPLICATION_DELAY;
                    $this->graph_edge[$slave['id_mysql_server']][$slave['id_master']]['label'] = $slave['seconds_behind_master'];
                } elseif ($slave['slave_io_running'] === "No" && $slave['slave_sql_running'] === "No") {
                    $this->graph_edge[$slave['id_mysql_server']][$slave['id_master']]['color'] = self::REPLICATION_STOPPED;
                    $this->graph_edge[$slave['id_mysql_server']][$slave['id_master']]['label'] = "STOPPED";
                } else {
                    $this->graph_edge[$slave['id_mysql_server']][$slave['id_master']]['label'] = "BUG ?";
                    $this->graph_edge[$slave['id_mysql_server']][$slave['id_master']]['color'] = self::REPLICATION_BLACKOUT;
                }



                if ($slave['last_io_errno'] !== "0") {
                    $this->graph_edge[$slave['id_mysql_server']][$slave['id_master']]['color'] = self::REPLICATION_ERROR;
                    $this->graph_edge[$slave['id_mysql_server']][$slave['id_master']]['label'] = $slave['last_io_errno'];
                }

                if ($slave['last_sql_errno'] !== "0") {
                    $this->graph_edge[$slave['id_mysql_server']][$slave['id_master']]['color'] = self::REPLICATION_ERROR;
                    $this->graph_edge[$slave['id_mysql_server']][$slave['id_master']]['label'] = $slave['last_sql_errno'];
                }

                if ($slave['slave_io_running'] == "Connecting") {
                    $this->graph_edge[$slave['id_mysql_server']][$slave['id_master']]['style'] = "dashed";
                    $this->graph_edge[$slave['id_mysql_server']][$slave['id_master']]['color'] = self::REPLICATION_NA;
                }

//debug($this->graph_edge[$slave['id_mysql_server']][$slave['id_master']]['color']);
            }
        }
    }

    public function pushMasterMaster()
    {
        $couples = array();

        foreach ($this->slaves as $gg) {
            foreach ($gg as $server) {


                if (empty($server['id_master'])) {
                    continue;
                }

                $tmp       = array($server['id_master'], $server['id_mysql_server']);
                $couples[] = min($tmp).":".max($tmp);
            }
        }


        $cpl_count = array_count_values($couples);


        $this->debug($cpl_count, "cpl_count");

        $paires = array();
        foreach ($cpl_count as $key => $val) {
            if ($val > 1) {
                $paires[] = $key;
            }
        }


        $this->debug($paires, "paires");

        foreach ($paires as $val) {
            $new_master_master[] = explode(":", $val);
        }


        if (!empty($new_master_master)) {

            $new_master_master = $this->array_merge_group($new_master_master);


            $this->debug($new_master_master, "new_master_master");


            //$new_master_master = array();
            //$this->debug($new_master_master, "MASTER / MASTER");

            $this->graph_master_master = $new_master_master;
        } else {
            $this->graph_master_master = array();
        }
    }

    public function run($param)
    {
        $this->parseDebug($param);
        $this->debugQueriesOff();

        $this->view = false;

// extract data
        $this->mappingMaster($param);
        $this->getInfoServer($param);

//$this->debug($this->servers, "\$this->servers");

        $master_slave   = $this->getMasterSlave($param);
        $galera_cluster = $this->getGaleraCluster($param);

        $all_groups = array_merge($master_slave, $galera_cluster);

        $this->generateGroup($all_groups);

        //$this->debug($this->galera_cluster);

        $this->checkPoint("Split Graph");


// format and push to pivot
        $this->pushServer();
        $this->pushUpdateMS();
        $this->pushMasterMaster();

        $this->pushGaleraCluster();

//$this->debug($this->slaves, "graph_edge");
//$this->debug($this->graph_edge, "graph_edge");
//exit;
//generate and save graph
//e
//cho $this->debugShowQueries();
//        debug($this->servers);

        $this->checkPoint("Push data");

        $this->generateAllGraph();
        $this->checkPoint("generateAllGraph");
    }

    public function generateAllGraph()
    {


        foreach ($this->groups['groups'] as $group) {

            //debug($group);
            $code2D = $this->generateGraph($group);
            $view2D = $this->getRenderer($code2D);
            $this->saveCache($code2D, $view2D, $group);
        }
    }

    public function getRenderer($dot)
    {
        file_put_contents(TMP."tmp.dot", $dot);

        $cmd = "dot ".TMP."/tmp.dot -Tsvg -o ".TMP."/image.svg";
//$cmd = "neato ".TMP."/tmp.dot -Tsvg -o ".TMP."/image.svg";
        shell_exec($cmd);

        return file_get_contents(TMP."/image.svg");
    }

    public function saveCache($code2D, $view2D, $group)
    {



        $servers = implode(",", $group);

//$this->debug($servers);


        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "BEGIN";
        $db->sql_query($sql);


        $sql = "DELETE b FROM `link__architecture__mysql_server` a
        INNER JOIN `architecture` b ON b.id = a.id_architecture
        WHERE a.`id_mysql_server` IN (".$servers.");";
        $db->sql_query($sql);

        preg_match_all("/width=\"([0-9]+)pt\"\sheight=\"([0-9]+)pt\"/", $view2D, $output);

        $sql = "INSERT INTO `architecture` (`date`, `data`, `display`,`height`,`width`)
            VALUES ('".date('Y-m-d H:i:s')."','".$db->sql_real_escape_string($code2D)."','".$db->sql_real_escape_string($view2D)."',"
            .$output[2][0]." ,".$output[1][0].")";
        $res = $db->sql_query($sql);


        $sql = "SELECT max(id) as last FROM architecture;";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $id_architecture = $ob->last;
        }

        foreach ($group as $id_mysql_server) {

            if (!in_array($id_mysql_server, $this->graph_arbitrator)) {
                $sql = "INSERT INTO `link__architecture__mysql_server` (`id_architecture`, `id_mysql_server`) VALUES ('".$id_architecture."','".$id_mysql_server."');";
                $db->sql_query($sql);
            }
        }


        $sql = "COMMIT;";
        $db->sql_query($sql);
    }

    private function nodeLine($line)
    {
        $line = '<tr><td bgcolor="lightgrey" align="left">'.$line.'</td></tr>';
        return $line;
    }

    private function nodeHead($display_name)
    {
        $line = '<tr><td bgcolor="black" color="white" align="center"><font color="white">'.$display_name.'</font></td></tr>';
        return $line;
    }

    public function generateNode($id_mysql_server)
    {

        if (in_array($id_mysql_server, $this->graph_arbitrator)) {
            $data = $this->generateArbitrator($id_mysql_server);
        } else {
            $data = $this->generateServer($id_mysql_server);
        }

        return $data;
    }

    private function generateArbitrator($id_mysql_server)
    {
        $server = $this->servers[$id_mysql_server];


        $node = "";
        $node .= "node [color = \"".self::NODE_AVAILABLE."\"];\n";
        $node .= '  '.$id_mysql_server.' [style="" penwidth="3" fontname="arial" label =<<table border="0" cellborder="0" cellspacing="0" cellpadding="2" bgcolor="white">';
        $node .= $this->nodeHead("Arbitrator");


        $lines   = array();
        $lines[] = "IP : n/a:".$server['port'];

        foreach ($lines as $line) {
            $node .= $this->nodeLine($line);
        }

        $node .= "</table>> ];\n";


        return $node;
    }

    private function generateServer($id_mysql_server)
    {

        $server = $this->servers[$id_mysql_server];


        $node = "";


        /*
          $databases = [];
          while ($ob        = $db->sql_fetch_object($res2)) {
          $databases[$ob->id_mysql_server][$ob->id_db]['name']             = $ob->name;
          $databases[$ob->id_mysql_server][$ob->id_db]['tables']           = $ob->tables;
          $databases[$ob->id_mysql_server][$ob->id_db]['rows']             = number_format($ob->rows, 0, '.', ' ');
          $databases[$ob->id_mysql_server][$ob->id_db]['size']             = $ob->data_length + $ob->data_free + $ob->index_length;
          $databases[$ob->id_mysql_server][$ob->id_db]['binlog_do_db']     = $ob->binlog_do_db;
          $databases[$ob->id_mysql_server][$ob->id_db]['binlog_ignore_db'] = $ob->binlog_ignore_db;
          }
         */


        if ($id_mysql_server == "66") {
            $this->debug($this->graph_node[$id_mysql_server]['color'], "COLOR NODE");
        }

        $node .= "node [color = \"".$this->graph_node[$id_mysql_server]['color']."\"];\n";
        $node .= '  '.$id_mysql_server.' [style="" penwidth="3" fontname="arial" label =<<table border="0" cellborder="0" cellspacing="0" cellpadding="2" bgcolor="white">';


        $node .= $this->nodeHead($server['hostname']);

        $lines[] = "IP : ".$server['ip'].":".$server['port'];
        $lines[] = "Date : ".$server['date'];
        $lines[] = "Time zone : ".$server['system_time_zone'];
        $lines[] = "Binlog : ".$server['binlog_format'];
        $lines[] = $this->formatVersion($server['version']);

        foreach ($lines as $line) {
            $node .= $this->nodeLine($line);
        }

        /*
          if (!empty($databases)) {
          $node .= '<tr><td bgcolor="lightgrey"><table border="0" cellborder="0" cellspacing="0" cellpadding="2">';


          $node .= '<tr>'
          .'<td bgcolor="darkgrey" color="white" align="left">M</td>'
          .'<td bgcolor="darkgrey" color="white" align="left">S</td>'
          .'<td bgcolor="darkgrey" color="white" align="left">'.__("Databases").'</td>'
          .'<td bgcolor="darkgrey" color="white" align="right">'.__("Tables").'</td>'
          .'<td bgcolor="darkgrey" color="white" align="right">'.__("Row").'</td>'
          .'</tr>';


          foreach ($databases as $database) {
          $node .= '<tr>'
          .'<td bgcolor="darkgrey" color="white" align="left">';

          if ($database['binlog_do_db'] === "1") {
          $node .= "&#10004;";
          }

          $node .= '</td>';
          $node .= '<td bgcolor="darkgrey" color="white" align="left">&#10006;</td>'
          .'<td bgcolor="darkgrey" color="white" align="left">'.$database['name'].'</td>'
          .'<td bgcolor="darkgrey" color="white" align="right">'.$database['tables'].'</td>'
          .'<td bgcolor="darkgrey" color="white" align="right">'.$database['rows'].'</td>'
          .'</tr>';
          }
          $node .= '</table></td></tr>';
          }

         */

        $node .= "</table>> ];\n";

        return $node;
    }

    private function formatVersion($version)
    {


        if (strpos($version, "-")) {
            $number   = explode("-", $version)[0];
            $fork_sql = explode("-", $version)[1];
        } else {
            $number = $version;
        }

        $name = 'MySQL';

        switch (strtolower($fork_sql)) {

            case 'mysql':
                $name = 'MySQL';
                break;

            case 'mariadb':
                $name = 'MariaDB';
                break;

            case 'percona':
                $name = 'Percona';
                break;
        }

        return $name." ".$number;
    }

    public function generateEdge($group)
    {
        $edge  = [];
        $label = " ";
        $style = "filled";


        $edges = "";






        //debug($this->graph_edge);

        foreach ($this->graph_edge as $id_slave => $masters) {
            foreach ($masters as $id_master => $val) {

                if (in_array($id_slave, $group)) {

                    if (empty($val['label'])) {
                        $val['label'] = " ";
                    }


                    // si le serveur est HS on surcharge la replication

                    if ($this->servers[$id_slave]['is_available'] === "0") {
                        $val['color'] = self::REPLICATION_ERROR;
                        $val['label'] = "HS";
                    }


                    if (!empty($val['style'])) {
                        $style = $val['style'];
                    }


                    $edge = $id_master." -> ".$id_slave
                        ." [ arrowsize=\"1.5\" style=".$style.",penwidth=\"2\" fontname=\"arial\" fontsize=8 color =\""
                        .$val['color']."\" label=\"".$val['label']."\"  edgetarget=\"".LINK."mysql/thread/"
                        ."dsGDG"."/\" edgeURL=\"".LINK."mysql/thread/"
                        ."sfghwdgf"."/"."thread"."\"];\n";

                    $edges .= $edge;
                }
            }
        }

        return $edges;
    }

    public function extractProviderOption($wsrep_provider_options, $variable)
    {
        preg_match("/".preg_quote($variable)."\s*=[\s]+([\S]+);/", $wsrep_provider_options, $output_array);

        if (!empty($output_array[1])) {
            return $output_array[1];
        } else {
            return 0;
            //throw new \Exception("Impossible to find : ".$variable." in (".$wsrep_provider_options.")");
        }
    }

    public function pushGaleraCluster()
    {

        //debug($this->graph_node);

        $this->debug($this->galera_cluster, "galera_cluster");

        foreach ($this->galera_cluster as $cluster_name => $members) {
            foreach ($members as $id_mysql_server => $member) {
                $segment = $this->extractProviderOption($member['wsrep_provider_options'], "gmcast.segment");

                $this->graph_galera_cluster[$cluster_name][$segment][] = $id_mysql_server;




                if (!empty($member['wsrep_local_state_comment']) && $member['wsrep_local_state_comment'] === "Donor/Desynced") {
                    $this->getSstJoiner($member);
                }


                // if donor
                // $this->graph_node[$id_mysql_server]['color']

                /*
                 *         if ($ob['comment'] === "Synced") {
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
                 */
            }
        }

        $this->debug($this->graph_galera_cluster, "Galera Cluster");
        //$graph_galera_cluster;

        return $this->graph_galera_cluster;
    }

    public function generateGaleraCluster($group)
    {
        $galera = "";

        foreach ($this->graph_galera_cluster as $cluster_name => $segments) {


            ksort($segments);

            $list_servers = array();
            $cluster      = "";
            $cluster      .= 'subgraph cluster_'.str_replace('-', '', $cluster_name).' {'."\n";
            $cluster      .= 'rankdir="LR";';
            $cluster      .= 'style=solid;penwidth=2; color="'.self::GALERA_AVAILABLE.'";'."\n";
            $cluster      .= 'label = "Galera : '.$cluster_name.'";';

            foreach ($segments as $segment => $nodes) {

                $cluster .= 'subgraph cluster_'.str_replace('-', '', $cluster_name)."_".$segment." {\n";
                $cluster .= 'label = "Segment : '.$segment.'";'."\n";
                $cluster .= 'rankdir="LR";';
                $cluster .= 'rank="same"; penwidth=2;';
                $cluster .= 'color="'.self::GALERA_AVAILABLE.'";style=dashed;penwidth=2;fontname="arial";'."\n";

                if (count($nodes) > 1) {
                    $cluster .= implode("->", $nodes)."[style=invis];\n"; // [constraint=false]
                } else {
                    $cluster .= end($nodes).";\n"; // [constraint=false]
                }

                foreach ($nodes as $id_mysql_server) {

                    //$cluster .= $id_mysql_server.";\n";
                }
                $cluster .= ' }'."\n";
            }

            $cluster .= ' }'."\n";


            if (in_array($id_mysql_server, $group)) {
                $galera .= $cluster;
            }
        }


        return $galera;


//$ret .= $this->display_segment($galera, $name_galera);


        if (count($galeras[$name_galera]) > 1) {
            $ret .= $this->display_segment($galera, $name_galera);
        } else {

            $this->debug(end($galera));
            $ret .= $this->display_node_galera(end($galera));
        }
    }

    private function generate_segment($galera, $name_galera)
    {
        $ret = "";

        foreach ($galera as $segment_name => $segment) {

            $ret .= 'subgraph cluster_'.str_replace('-', '', $name_galera)."_".$segment_name." {\n";
            $ret .= 'label = "Segment : '.$segment_name.'";'."\n";
            $ret .= 'color='.self::COLOR_SUCCESS.';style=dashed;penwidth=2;fontname="arial";'."\n";


            $ret .= $this->display_node_galera($segment);
            $ret .= ' }'."\n";
        }

        return $ret;
    }

    private function getNewId()
    {

        $servers = $this->servers;

        $id = max(array_keys($servers));

        $this->debug($id, "max id");


        $id++;

        return $id;
    }
    /*
     * shape=rect style=filled fontsize=8 fontname=\"arial\" ranksep=0 concentrate=true splines=true overlap=false
     */

    private function createArbitrator()
    {
        $id_arbitrator = $this->getNewId();



        //debug($this->servers[$id_arbitrator-1]);

        $this->servers[$id_arbitrator]["id_mysql_server"]        = $id_arbitrator;
        $this->servers[$id_arbitrator]["wsrep_provider_options"] = "gmcast.segment = 0;";
        $this->servers[$id_arbitrator]["is_available"]           = 1;
        $this->servers[$id_arbitrator]["ip"]                     = "n/a";
        $this->servers[$id_arbitrator]["port"]                   = "4567";

        $this->graph_arbitrator[] = $id_arbitrator;

        return $id_arbitrator;
    }

    private function getSstJoiner($data)
    {
        $all_ip_port = explode(",", $data['wsrep_incoming_addresses']);


        //debug($this->maping_master);

        $this->debug($all_ip_port);


        foreach ($all_ip_port as $key => $ip_port) {
            if (empty($ip_port)) { // on retire le ou les arbitres
                unset($all_ip_port[$key]);
                continue;
            }

            $row = $this->servers[$this->maping_master[$ip_port]];

            if ($row["is_available"] === "1") { // on retire tous les noeuds en etat de marche pour identifier le "JOINER"
                unset($all_ip_port[$key]);
            }
        }


        $row = $this->servers[$this->maping_master[end($all_ip_port)]];


        $this->graph_edge[$row['id_mysql_server']][$data['id_mysql_server']]['color'] = self::REPLICATION_SST;
        $this->graph_edge[$row['id_mysql_server']][$data['id_mysql_server']]['label'] = "SST";

        if ($data['wsrep_sst_method'] === "xtrabackup-v2" || $data['wsrep_sst_method'] === "mariabackup") {
            $this->graph_node[$data['id_mysql_server']]['color'] = self::NODE_DONOR;
        } else {
            $this->graph_node[$data['id_mysql_server']]['color'] = self::NODE_DONOR_DESYNCED;
        }



        $this->graph_node[$row['id_mysql_server']]['color'] = self::NODE_RECEIVE_SST;

        $this->debug($row);
    }

    public function generateRankForMM($group)
    {
        $graph = '';

        foreach ($this->graph_master_master as $key => $mastermaster) {
            foreach ($mastermaster as $id_server) {
                if (in_array($id_server, $group)) {
                    $graph .= "{rank = same; ".implode(";", $mastermaster).";}\n";
                    break;
                }
            }
        }

        return $graph;
    }

    public function legend()
    {

        $sql = "SELECT * FROM `architecture_legend` order by `order`;";

        $db = $this->di['db']->sql(DB_DEFAULT);

        $res = $db->sql_query($sql);

        $edges = array();
        while ($arr   = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $edges[] = $arr;
        }

        $legend = 'digraph {
  rankdir=LR
  graph [fontname = "helvetica"];
 node [fontname = "helvetica"];
 edge [fontname = "helvetica"];

  node [shape=plaintext fontsize=12];

  subgraph cluster_01 {
    
    label = "Repplication : Legend";
    
    key [label=<<table border="0" cellpadding="2" cellspacing="0" cellborder="0">';

        $i = 1;
        foreach ($edges as $edge) {
            $legend .= '<tr><td align="right" port="i'.$i.'">'.$edge['name'].'</td></tr>'."\n";
            $i++;
        }
        $legend .= '</table>>]
    key2 [label=<<table border="0" cellpadding="2" cellspacing="0" cellborder="0">'."\n";


        $i = 1;
        foreach ($edges as $edge) {
            $legend .= '<tr><td port="i'.$i.'">&nbsp;</td></tr>'."\n";
            $i++;
        }


        $legend .= '</table>>]'."\n";

        $i = 1;
        foreach ($edges as $edge) {
            $legend .= 'key:i'.$i.':e -> key2:i'.$i.':w [color='.$edge['color'].' arrowsize="1.5" style='.$edge['style'].',penwidth="2"]'."\n";
            $i++;
        }

        /*
          key:i1:e -> key2:i1:w [color=blue]
          key:i2:e -> key2:i2:w [color=gray]
          key:i3:e -> key2:i3:w [color=peachpuff3]
          key:i4:e -> key2:i4:w [color=turquoise4, style=dotted]
         */
        $legend .= '
  }
}';


        //echo str_replace("\n", "<br />",htmlentities($legend));

        $data['legend'] = $this->getRenderer($legend);

        $this->set('data', $data);

        //https://dreampuf.github.io/GraphvizOnline/
    }
}