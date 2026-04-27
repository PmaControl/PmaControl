<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use \App\Library\Json;

use App\Library\Debug;
//require ROOT."/application/library/Filter.php";

/**
 * Class responsible for architecture workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Architecture extends Controller
{

    use \App\Library\Filter;

/**
 * Render architecture state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/architecture/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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
        $this->di['js']->addJavascript(array("vendor/masonry-layout-4.2.2.pkgd.min.js"));

        $this->di['js']->code_javascript('

            $(".grid").masonry({
                // options...
                itemSelector: ".grid-item",
                columnWidth: 5
            });

        ');
        



        $db = Sgbd::sql(DB_DEFAULT);

        $selected_clients = self::removeRedundantGraphClientFilter($db, $selected_clients);
        $sql = self::buildGraphSql($selected_clients);

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

    /**
     * Build the Architecture graph query.
     *
     * @param array<int,int> $selected_clients
     */
    private static function buildGraphSql(array $selected_clients): string
    {
        $sql = "SELECT dg.id, dg.svg, dg.height, dg.width, (dg.height * dg.width) as area, dc.date_inserted as date_refresh
FROM dot3_cluster dc
JOIN dot3_graph dg ON dg.id = dc.id_dot3_graph
WHERE dc.id_dot3_information = (SELECT MAX(id_dot3_information) FROM dot3_cluster)";

        if (!empty($selected_clients)) {
            $id_clients = implode(',', $selected_clients);
            $sql .= " AND EXISTS (
                SELECT 1 FROM dot3_cluster__mysql_server dcms
                INNER JOIN mysql_server ms ON ms.id = dcms.id_mysql_server
                WHERE dcms.id_dot3_cluster = dc.id
                AND ms.id_client IN (" . $id_clients . ")
                AND ms.is_deleted = 0
            )";
        }

        return $sql . " ORDER BY dg.height DESC, dg.width DESC;";
    }

    /**
     * @param mixed $db
     * @param array<int,int> $selected_clients
     * @return array<int,int>
     */
    private static function removeRedundantGraphClientFilter($db, array $selected_clients): array
    {
        if (empty($selected_clients)) {
            return $selected_clients;
        }

        $res = $db->sql_query("SELECT DISTINCT id_client FROM mysql_server WHERE is_deleted = 0");
        $active_clients = array();
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $id_client = (int) $arr['id_client'];
            if ($id_client > 0) {
                $active_clients[$id_client] = $id_client;
            }
        }

        if (empty($active_clients)) {
            return $selected_clients;
        }

        $selected_lookup = array_flip($selected_clients);
        foreach ($active_clients as $id_client) {
            if (!isset($selected_lookup[$id_client])) {
                return $selected_clients;
            }
        }

        return array();
    }

/**
 * Handle architecture state through `view`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for view.
 * @phpstan-return void
 * @psalm-return void
 * @see self::view()
 * @example /fr/architecture/view
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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
