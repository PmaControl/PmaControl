<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;


class Version extends Controller
{

    public function index($param)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql ="SELECT * FROM `version`";


    }
}