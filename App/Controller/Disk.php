<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use App\Library\EngineV4;
use \Glial\Synapse\Controller;
use \App\Library\Debug;
use \App\Library\Microsecond;
use \Glial\I18n\I18n;
use \Glial\Sgbd\Sgbd;
use \App\Library\Extraction2;

class Disk extends Controller
{
    public function getData($param)
    {
        Debug::parseDebug($param);

        $data = Extraction2::display(array("variables::"), array(1));

        $filtered = array_filter($data[1], function($value) {
            return is_string($value) && str_starts_with($value, '/');
        });

        Debug::debug($filtered, "GGG");
    }


    public function gg($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $data = $db->isSlave();

        echo json_encode($data, JSON_PRETTY_PRINT);
 


        //Debug::debug($data);
    }
}

