<?php

namespace App\Controller;

use App\Library\Extraction;
use App\Library\Mysql;
use \Glial\Synapse\Controller;
use \App\Library\Debug;
use \Glial\Sgbd\Sgbd;


class GaleraCluster extends Controller {

    use \App\Library\Galera;

    public function index($param) {

        $data['galera'] = $this->getInfoGalera($param);
        $segments = Extraction::display(array("wsrep_provider_options"));

        foreach($segments as $segment)
        {
            if (!empty($segment['']['wsrep_provider_options']))
            {
                preg_match('/ gmcast.segment\s?\=\s?([0-9]+)\;/', $segment['']['wsrep_provider_options'], $output_array);

                if (isset($output_array[1]))
                {
                    $data['segment'][$segment['']['id_mysql_server']] = $output_array[1];
                }
            }
        }
        
        $this->set('data', $data);
    }

    /*
     * 
     * meta function to call all sub function
     */

    public function getInfoGalera($param) {


        Debug::parseDebug($param);

        $this->mappingMaster();

        $this->getInfoServer($param);
        $galera = $this->getGaleraCluster($param);

        Debug::debug($this->galera_cluster);


        return $this->galera_cluster;
    }

    //https://www.percona.com/blog/2012/12/19/percona-xtradb-cluster-pxc-what-about-gra_-log-files/


    public function setNodeAsPrimary ($param)   {

        Debug::parseDebug($param);

        $id_mysql_server = $param['0'];

        $db  = Mysql::getDbLink($id_mysql_server);

        $sql = "SET GLOBAL wsrep_provider_options='pc.bootstrap=true';";
        
        // Write something to log
        $db->sql_query($sql);

        
        if (IS_CLI === false) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        }
    }
}
