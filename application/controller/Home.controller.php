<?php

use \Glial\Synapse\Controller;

class Home extends Controller
{

    function before($param)
    {
        $this->di['js']->addJavascript(array("jquery-latest.min.js","bootstrap.min.js","http://getbootstrap.com/assets/js/docs.min.js"));
    }

    function index()
    {
        $this->layout_name = 'pmacontrol';


        $this->title = __("Home");
        $this->ariane = " > " . __("Welcome to PmaControl !");

        
        
        $data['link'] = [
          '0' => ['url' => 'sql', 'name' => __("Synchronise"), 'icon' => 'glyphicon glyphicon-refresh'],
          '2' => ['url' => 'sql', 'name' => __("Configuration"), 'icon' => 'glyphicon glyphicon-file'],
          '3' => ['url' => 'sql', 'name' => __("Status"), 'icon' => 'glyphicon glyphicon-adjust'],
          '4' => ['url' => 'sql', 'name' => __("Variables"), 'icon' => 'glyphicon glyphicon-th-list'],
          '5' => ['url' => 'sql', 'name' => __("Events"), 'icon' => 'glyphicon glyphicon-list'],
        ];
        
        $this->set('data',$data);
        
        
        //$this->javascript = array("");
    }

    function list_server($param)
    {
        $db = $this->di['db']->sql(DB_DEFAULT);
        $sql = "SELECT * FROM mysql_server ORDER BY ip";
        $data['server'] = $db->sql_fetch_yield($sql);

        $this->set('data', $data);
    }

}
