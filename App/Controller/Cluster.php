<?php

namespace App\Controller;

use \Glial\I18n\I18n;
use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;

use \App\Library\Debug;

class Cluster extends Controller
{

    var $logger;

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
            exit;
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
        $this->set('param', $param);
    }

    /*

    Enterprise
    */
   public function replay($param)
   {
        $id_mysql_server = $param[0];

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT date(min(date_inserted)) as date_min, date(max(date_inserted)) as date_max 
        FROM dot3_cluster__mysql_server where id_mysql_server = ".$id_mysql_server.";";

        $res = $db->sql_query($sql);

        $data = [];

        $data['param'] = $param;
        $data['id_mysql_server'] = $id_mysql_server;

        while($ob = $db->sql_fetch_object($res))
        {
            $data['date_min'] = $ob->date_min;
            $data['date_max'] = $ob->date_max;
        }

        $data['list_min'] = [];

        $sql2 = "SELECT DATE_ADD('{$data['date_min']}', INTERVAL seq DAY) AS generated_date
        FROM (
        SELECT @row := @row + 1 AS seq
        FROM (
            SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
            UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
        ) t1,
        (
            SELECT 0 UNION ALL SELECT 10 UNION ALL SELECT 20 UNION ALL SELECT 30 UNION ALL SELECT 40
            UNION ALL SELECT 50 UNION ALL SELECT 60
        ) t2,
        (SELECT @row := -1) AS init
        ) AS seq_gen
        WHERE DATE_ADD('{$data['date_min']}', INTERVAL seq DAY) <= '{$data['date_max']}';";

        Debug::debug($sql2);

        $res2 = $db->sql_query($sql2);
        while($ob2 = $db->sql_fetch_object($res2))
        {

            $tmp            = [];
            $tmp['id']      = $ob2->generated_date;
            $tmp['libelle']   = $ob2->generated_date;

            $data['list_min'][] = $tmp;
        }

        $data['options'] = array("data-style" => "btn-info","data-width" => "auto", "all_selectable"=> "false");


        $this->set('data', $data);

   }

   public function history($param)
   {

        $db = Sgbd::sql(DB_DEFAULT);

        $id_mysql_server = $param[0];

        if ($_SERVER['REQUEST_METHOD'] == "POST") {

            debug($_POST); 
            header("location: ".LINK."Cluster/history/".$id_mysql_server."/".$_POST['dot3_cluster__mysql_server']['date_min']."/".$_POST['dot3_cluster__mysql_server']['date_max']);
            exit;
        }

        $date_min = $param[1];
        $date_max = $param[2];

        $sub_query = "SELECT z.id from dot3_cluster__mysql_server z where z.id_mysql_server={$id_mysql_server} 
        AND date_inserted BETWEEN '".$date_min."' AND '".$date_max."'";

        $sql = "SELECT c.svg,c.date_inserted FROM dot3_cluster__mysql_server a
        INNER JOIN dot3_cluster b ON a.id_dot3_cluster = b.id
        INNER JOIN dot3_graph c ON b.id_dot3_graph = c.id
        WHERE a.id_mysql_server = {$id_mysql_server} AND a.id in ({$sub_query}) 
        GROUP BY c.date_inserted,c.svg";

        $res = $db->sql_query($sql);
        
        //$this->logger->warning($db->sql_num_rows($res));
        $data = [];
        $data['param'] = $param;

        $i = 0;
        while ($ob = $db->sql_fetch_object($res)) {

            //remove debug
            if (stripos($ob->svg, 'Debug') !== false) {
                continue;
            }

            $data['svg'][$i]['svg'] = $ob->svg;
            $data['svg'][$i]['date_inserted'] = $ob->date_inserted;
            $i++;
        }

        $this->set('data', $data);
   }
}
