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

        $selected_client = null;

        // Parse URL parameters like client:libelle:["15"]
        if (!empty($param[0]) && strpos($param[0], 'client:libelle:') === 0) {
            $value = substr($param[0], strlen('client:libelle:'));
            $json = json_decode($value, true);
            if (is_array($json) && !empty($json)) {
                $selected_client = intval($json[0]);
                $_SESSION['architecture_client'] = $selected_client;
            }
        }

        // Handle organization selection
        if (!empty($_POST['client']['id'])) {
            $selected_client = intval($_POST['client']['id']);
            $_SESSION['architecture_client'] = $selected_client;
        } elseif (!empty($_SESSION['architecture_client'])) {
            $selected_client = $_SESSION['architecture_client'];
        }

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

        // Get clients for dropdown
        $sql_clients = "SELECT id, libelle FROM client WHERE is_monitored = 1 ORDER BY libelle";
        $data['client'] = $db->sql_fetch_yield($sql_clients);
        $data['selected_client'] = $selected_client;

        $sql = "SELECT dg.*, (dg.height * dg.width) as area, dc.date_inserted as date_refresh
FROM dot3_graph dg
JOIN dot3_cluster dc ON dg.id = dc.id_dot3_graph
WHERE dc.id_dot3_information = (SELECT MAX(id_dot3_information)-1 FROM dot3_cluster)";

        // Filter by selected organization if one is selected
        $filter_conditions = "";
        if (!empty($selected_client)) {
            $filter_conditions = " AND EXISTS (
                SELECT 1 FROM dot3_cluster__mysql_server dcms
                INNER JOIN mysql_server ms ON ms.id = dcms.id_mysql_server
                WHERE dcms.id_dot3_cluster = dc.id
                AND ms.id_client = " . $db->sql_real_escape_string($selected_client) . "
                AND ms.is_deleted = 0
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
