<?php

namespace App\Controller;

use \Glial\I18n\I18n;
use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;

class Cluster extends Controller
{
    public function svg($param)
    {
        $id_mysql_server = $param[0] ?? "";
        
        if (empty($id_mysql_server)) {
            $id_mysql_server = 1;
            
            header("location: ".LINK."Cluster/svg/1/");
        }

        $_GET['mysql_server']['id'] = $id_mysql_server;

        if (!empty($_GET['ajax']) && $_GET['ajax'] === "true"){
            $this->layout_name = false;
        }

        $this->di['js']->code_javascript('
        $(document).ready(function()
        {
            function refresh()
            {
                var myURL = GLIAL_LINK+GLIAL_URL+"ajax:true";
                $("#graph").load(myURL);
            }

            var intervalId = window.setInterval(function(){
                // call your function here
                refresh()  
              }, 1000);

        })');



        $db = Sgbd::sql(DB_DEFAULT);

        $data = array();

        $sql = "SELECT c.svg FROM dot3_cluster__mysql_server a
        INNER JOIN dot3_cluster b ON a.id_dot3_cluster = b.id
        INNER JOIN dot3_graph c ON b.id_dot3_graph = c.id
        INNER JOIN dot3_information d ON b.id_dot3_information = d.id
        WHERE a.id_mysql_server = ".$id_mysql_server." AND d.id in (select max(id) from dot3_information);
        ";

        $res = $db->sql_query($sql);
        while ($ob = $db->sql_fetch_object($res)) {
            $data['svg'] = $ob->svg;
        }

        $data['param'] = $param;
        $this->set('data',$data);
    }

}