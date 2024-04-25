<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;


class Version extends Controller
{

    public function index()
    {

        $name         = __("Version");                   
        $this->title  = '<i class="fa fa-info-circle" style="font-size:32px"></i> '.$name;
        $this->ariane = '> <i class="fa fa-question" style="font-size:16px" aria-hidden="true"></i> Help > <i class="fa fa-info-circle" style="font-size:16px"></i> '
            .$name;


        $db = Sgbd::sql(DB_DEFAULT);
        $sql ="SELECT * FROM `version`";
        $data['version'] = $db->sql_fetch_yield($sql);

        $this->set('data', $data);


    }
}