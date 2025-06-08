<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use \App\Library\Json;

use App\Library\Debug;
//require ROOT."/application/library/Filter.php";

class Architecture extends Controller
{

    use \App\Library\Filter;

    public function index($param)
    {
        Debug::parseDebug($param);


        /*
        $this->title  = '<i class="fa fa-object-group"></i> '.__("Architecture");
        $this->ariane = ' > <a href⁼"">'.'<span class="glyphicon glyphicon glyphicon-home" style="font-size:12px"></span> '
            .__("Dashboard").'</a> > <i class="fa fa-object-group" style="font-size:14px"></i> '.__("Architecture");
        */

        $this->di['js']->addJavascript(array("https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"));
        
        $this->di['js']->code_javascript('

            $(".grid").masonry({
                // options...
                itemSelector: ".grid-item",
                columnWidth: 5
            });

        ');


        
        $db = Sgbd::sql(DB_DEFAULT);


        $sql = "WITH LatestDot3Information AS (
    SELECT MAX(id_dot3_information) AS max_id_dot3_information
    FROM dot3_cluster
)
SELECT dg.*, (dg.height * dg.width) as area, dc.date_inserted as date_refresh
FROM dot3_graph dg
JOIN dot3_cluster dc ON dg.id = dc.id_dot3_graph
JOIN LatestDot3Information ldi ON dc.id_dot3_information = ldi.max_id_dot3_information-1
ORDER BY  height DESC, width desc
;";



        $data['graphs'] = array();

        Debug::debug($sql);
        //@TODO c.height > 8   => to fix on register table architecture on Dot

        /*         * *
         * SELECT c.id,c.display FROM mysql_server a
          INNER JOIN link__architecture__mysql_server b ON a.id = b.id_mysql_server
          INNER JOIN architecture c ON c.id = b.id_architecture
          WHERE 1 GROUP BY c.id
         */
        $res = $db->sql_query($sql);
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC))
        {
            $data['graphs'][] = $arr;
        }
        

        $this->set('data', $data);


    }

    public function view($param)
    {
        if ($_SERVER['REQUEST_METHOD'] === "POST") {

            debug($_POST);
            debug($_FILES);
            
            if (!empty($_FILES['import']['tmp_name']['file'])) {
                $file     = $_FILES['export']['tmp_name']['file'];
            }

            //debug(json_decode($json,JSON_PRETTY_PRINT));

            //$data = Json::isJson($json);
        }

    }
}
