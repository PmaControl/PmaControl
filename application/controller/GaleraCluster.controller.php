<?php

use \Glial\Synapse\Controller;

class GaleraCluster extends Controller {

    private function getCluster() {
        
    }

    public function testSqlLogBin() {
        
    }

    public function index() {

        $db = $this->di['db']->sql(DB_DEFAULT);


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




        foreach ($this->servers as $server) {
            if (!empty($server['wsrep_on']) && $server['wsrep_on'] === "ON") {
                //debug($server);
                //$server['wsrep_incoming_addresses']
                $tab = explode(",", $server['wsrep_incoming_addresses']);
                $to_match = $server['ip'] . ":" . $server['port'];


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
    }

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
                $arbitres[] = $this->createArbitrator($group);
            }
        }

        $all_nodes = array_merge($group_galera, $galera_nodes, $arbitres);

        //debug($all_nodes);

        $ret['all_nodes'] = $all_nodes;
        $ret['arbitres'] = $arbitres;

        return $ret;
    }

    //https://www.percona.com/blog/2012/12/19/percona-xtradb-cluster-pxc-what-about-gra_-log-files/
}
