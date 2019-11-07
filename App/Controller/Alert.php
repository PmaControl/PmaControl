<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use \App\Library\Extraction;

class Alert extends Controller {

    var $to_check = array("wsrep_cluster_size", "wsrep_cluster_name", "wsrep_on");

    public function check($date, $id_servers) {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $res = Extraction($this->to_check, $id_servers, $date);


        while ($ob = $db->sql_fetch_object($res)) {
            
        }
    }

}
