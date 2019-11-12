<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \App\Library\Debug;

class GaleraCluster extends Controller {

    use \App\Library\Galera;

    public function index($param) {


        $data['galera'] = $this->getInfoGalera($param);


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
}
