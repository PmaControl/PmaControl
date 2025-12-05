<?php

/*
 * This functions are shared by controller Dot2 and GaleraCluster
 * the goal is to identfy all GaleraCluster with no configuration at all
 */

namespace App\Library;

use App\Library\Extraction;
use \App\Library\Debug;
use \Glial\Sgbd\Sgbd;

trait Galera {

    var $galera_cluster = array();
    var $servers = array();
    var $graph_arbitrator = array(); // containt all id of arbitrator
    var $maping_master = array(); 

    public function getGaleraCluster($param) {

        foreach ($this->servers as $server) {
            if (!empty($server['wsrep_on']) && $server['wsrep_on'] === "ON") {
                //debug($server);
                //$server['wsrep_incoming_addresses']
                $tab = explode(",", $server['wsrep_incoming_addresses']);
                $to_match = $server['ip'] . ":" . $server['port'];


                // the goal is to remove proxy
                if (in_array($to_match, $tab)) {
                    $this->galera_cluster[$server['wsrep_cluster_name'] . '~' . md5($server['wsrep_incoming_addresses'])][$server['id_mysql_server']] = $server;
                }
            }
        }


        //debug($this->maping_master);

        $group_galera = array();

        if (count($this->galera_cluster) > 0) {
            $group = 1;
            foreach ($this->galera_cluster as $cluster_name => $servers) {

                $incomming = array();
                $galera_nodes = array();
                foreach ($servers as $id_mysql_server => $server) {
                    //$group_galera[$group] = $id_mysql_server;
                    // to get offline node
                    $incomming[] = $server['wsrep_incoming_addresses'];



                    $tab = explode(",", $server['wsrep_incoming_addresses']);
                    $to_match = $server['ip'] . ":" . $server['port'];
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


            Debug::debug($group_galera);
        }



        return $group_galera;
    }

    /*
     * this option determine all nodes from a galera Cluster
     * it's exclude all node acceded with HAProxy or ProxySQL
     * it's Add Arbiter
     * (need add function to know the IP of Arbiter
     */

    private function getAllMemberFromGalera($incomming, $galera_nodes, $group) {
        //need dertect split brain !!
        Debug::debug($incomming);


        $all_node = array();
        foreach ($incomming as $listing) {
            $ip_port = explode(",", $listing);
            $all_node = array_merge($all_node, $ip_port);
        }

        //$all_nodes = array_merge($all_node, $galera_nodes);

        $nodes = array_unique($all_node);

        Debug::debug($nodes);


        $arbitres = array();


        $group_galera = array();

        foreach ($nodes as $node) {
            if (!empty($node)) {
                if (!empty($this->maping_master[$node])) {
                    $group_galera[] = $this->maping_master[$node];
                } else {
                    // unknow node

                    Debug::debug($node, "UNKNOW NODE");
                }
            } else {
                //arbitre
                $arbitres[] = $this->createArbitrator();
            }
        }

        $all_nodes = array_merge( 
        $group_galera, 
        $galera_nodes, 
        $arbitres);

        //debug($all_nodes);

        $ret['all_nodes'] = $all_nodes;
        $ret['arbitres'] = $arbitres;

        return $ret;
    }

    /*
     * get all variables relative to GaleraCluster
     * 
     * 
     */

    public function getInfoServer($param) {
        $db = Sgbd::sql(DB_DEFAULT);

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

    public function mappingMaster() {

        $db = Sgbd::sql(DB_DEFAULT);

        // Main servers
        $sql = "SELECT id,ip,port FROM mysql_server WHERE is_deleted=0 AND is_proxy=0";
        $res = $db->sql_query($sql);
        while ($ar = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $this->maping_master[$ar['ip'] . ":" . $ar['port']] = $ar['id'];
            $this->servers[$ar['id']] = $ar;
        }

        // Aliases from alias_dns table
        $sql_alias = "SELECT a.id_mysql_server, a.dns, a.port FROM alias_dns a
                      INNER JOIN mysql_server s ON a.id_mysql_server = s.id
                      WHERE s.is_deleted=0";
        $res_alias = $db->sql_query($sql_alias);
        while ($ar = $db->sql_fetch_array($res_alias, MYSQLI_ASSOC)) {
            $this->maping_master[$ar['dns'] . ":" . $ar['port']] = $ar['id_mysql_server'];
        }

        return $this->maping_master;
    }

    private function createArbitrator() {
        $id_arbitrator = $this->getNewId();



        //debug($this->servers[$id_arbitrator-1]);

        $this->servers[$id_arbitrator]["id_mysql_server"] = $id_arbitrator;
        $this->servers[$id_arbitrator]["wsrep_provider_options"] = "gmcast.segment = 0;";
        $this->servers[$id_arbitrator]["is_available"] = 1;
        $this->servers[$id_arbitrator]["ip"] = "n/a";
        $this->servers[$id_arbitrator]["port"] = "4567";

        $this->graph_arbitrator[] = $id_arbitrator;

        return $id_arbitrator;
    }

    private function getNewId() {

        $servers = $this->servers;

        $id = max(array_keys($servers));

        Debug::debug($id, "max id");


        $id++;

        return $id;
    }

}
