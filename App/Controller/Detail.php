<?php
//https://nagix.github.io/chartjs-plugin-streaming/samples/interactions.html

namespace App\Controller;

use \Glial\Synapse\Controller;
use App\Library\Extraction;
use App\Library\Display;
use App\Library\Debug;
use \Glial\Sgbd\Sgbd;

class Detail extends Controller
{

    public function index($param)
    {
        $this->di['js']->addJavascript(array("moment.js", "Chart.bundle.js", "hammer.min.js", "chartjs-plugin-zoom.js")); //, "hammer.min.js", "chartjs-plugin-zoom.js")
        $db = Sgbd::sql(DB_DEFAULT);


        $id_mysql_server = $param[0];


        


        
    }
}