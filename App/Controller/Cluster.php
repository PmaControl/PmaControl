<?php

namespace App\Controller;

use \Glial\I18n\I18n;
use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;


class Cluster extends Controller
{

    var $looger;

    public function before($param)
    {
        $monolog       = new Logger("Cluter");
        $handler      = new StreamHandler(LOG_FILE, Logger::NOTICE);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $monolog->pushHandler($handler);
        $this->logger = $monolog;
    }

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

        $db = Sgbd::sql(DB_DEFAULT, "SVG");

        $data = array();

        $sub_query = "select max(z.id) from dot3_cluster__mysql_server z where z.id_mysql_server=".$id_mysql_server;

        $sql = "SELECT c.svg FROM dot3_cluster__mysql_server a
        INNER JOIN dot3_cluster b ON a.id_dot3_cluster = b.id
        INNER JOIN dot3_graph c ON b.id_dot3_graph = c.id
        WHERE a.id_mysql_server = ".$id_mysql_server." AND a.id in (".$sub_query.");";

        //$this->logger->warning($sql);
        //$sql ="select max(id) from dot3_cluster x INNER JOIN dot3_cluster__mysql_server y ON x.id_dot3_cluster = y.id WHERE y.id=".$id_mysql_server."";

        //select max(x.id) from dot3_cluster x INNER JOIN dot3_cluster__mysql_server y ON y.id_dot3_cluster = x.id WHERE y.id=65;
        $res = $db->sql_query($sql);
        
        //$this->logger->warning($db->sql_num_rows($res));
        
        while ($ob = $db->sql_fetch_object($res)) {
            $this->di['js']->code_javascript('
            $(document).ready(function()
            {
                function refresh()
                {
                    var myURL = GLIAL_LINK+GLIAL_URL+"ajax:true";
                    $.ajax({
                        url: myURL,
                        type: "GET",
                        success: function(data) {
                            // Vérifier si les données ne sont pas vides
                            if (data.trim().length > 0) {
                                $("#graph").html(data);
                            } else {
                                console.log("Aucune donnée reçue.");
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log("Erreur lors du chargement des données : ", error);
                        }
                    });
                }
    
                var intervalId = window.setInterval(function(){
                    // call your function here
                    refresh()  
                  }, 1200);
    
            })');
            $data['svg'] = $ob->svg;
        }

        $data['param'] = $param;
        $this->set('data',$data);
    }
}