<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;

class Alias extends Controller {

    public function index() {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM alias_dns a
        ORDER BY dns";

        $res = $db->sql_query($sql);


        $data['alia_dns'] = array();

        while ($ob = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['alia_dns'][] = $ob;
        }

        $this->set('data', $data);
    }

}
