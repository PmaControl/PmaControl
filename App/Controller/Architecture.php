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

        $selected_clients = array();

        $raw_clients = $_GET['client']['libelle'] ?? $_SESSION['client']['libelle'] ?? array();

        if (is_string($raw_clients)) {
            $decoded = json_decode($raw_clients, true);

            if (is_array($decoded)) {
                $raw_clients = $decoded;
            } elseif ($raw_clients !== '') {
                $raw_clients = array($raw_clients);
            } else {
                $raw_clients = array();
            }
        }

        if (!is_array($raw_clients)) {
            $raw_clients = array();
        }

        foreach ($raw_clients as $id_client) {
            $id_client = (int) $id_client;
            if ($id_client > 0) {
                $selected_clients[$id_client] = $id_client;
            }
        }

        $selected_clients = array_values($selected_clients);

        /*
        $this->title  = '<i class="fa fa-object-group"></i> '.__("Architecture");
        $this->ariane = ' > <a href⁼"">'.'<span class="glyphicon glyphicon glyphicon-home" style="font-size:12px"></span> '
            .__("Dashboard").'</a> > <i class="fa fa-object-group" style="font-size:14px"></i> '.__("Architecture");
        */

        //https://masonry.desandro.com/events
        $this->di['js']->addJavascript(array("https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"));

        $this->di['js']->code_javascript('

            $(".grid").masonry({
                // options...
                itemSelector: ".grid-item",
                columnWidth: 5
            });

        ');

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT dg.*, (dg.height * dg.width) as area, dc.date_inserted as date_refresh
FROM dot3_graph dg
JOIN dot3_cluster dc ON dg.id = dc.id_dot3_graph
WHERE dc.id_dot3_information = (SELECT MAX(id_dot3_information)-1 FROM dot3_cluster)";

/*
        $sql = "WITH LatestDot3Information AS (
    SELECT MAX(id_dot3_information)-1 AS max_id_dot3_information
    FROM dot3_cluster
)
SELECT dg.*, (dg.height * dg.width) as area, dc.date_inserted as date_refresh
FROM dot3_graph dg
JOIN dot3_cluster dc ON dg.id = dc.id_dot3_graph
JOIN LatestDot3Information ldi ON dc.id_dot3_information = ldi.max_id_dot3_information
ORDER BY  height DESC, width desc;";
*/
        // Filter by selected organization if one is selected
        $filter_conditions = "";
        if (!empty($selected_clients)) {
            $id_clients = implode(',', $selected_clients);
            $filter_conditions = " AND EXISTS (
                SELECT 1 FROM dot3_cluster__mysql_server dcms
                INNER JOIN mysql_server ms ON ms.id = dcms.id_mysql_server
                WHERE dcms.id_dot3_cluster = dc.id
                AND ms.id_client IN (" . $id_clients . ")
                AND COALESCE(ms.is_deleted, 0) = 0
            )";
        }

        $sql .= $filter_conditions . " ORDER BY height DESC, width DESC;";

        $data['graphs'] = array();

        Debug::sql($sql);
        //@TODO c.height > 8   => to fix on register table architecture on Dot

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
