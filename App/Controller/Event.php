<?php

namespace App\Controller;

use Glial\Synapse\Controller;
use Glial\Sgbd\Sgbd;
use App\Library\Extraction2;
use App\Library\Debug;

/**
 * Class responsible for event workflows.
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
class Event extends Controller
{
    public function list($param)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT e.*, ms.display_name as mysql_name, px.name as proxysql_name, mx.name as maxscale_name, dk.name as docker_name
        FROM event_log e
        LEFT JOIN mysql_server ms ON e.id_mysql_server = ms.id
        LEFT JOIN proxysql_server px ON e.id_proxysql_server = px.id
        LEFT JOIN maxscale_server mx ON e.id_maxscale_server = mx.id
        LEFT JOIN docker_server dk ON e.id_docker_host = dk.id
        ORDER BY (date_end IS NULL) DESC, date_end DESC
        LIMIT 200";

        $res = $db->sql_query($sql);
        $data['events'] = $db->sql_fetch_all($res, MYSQLI_ASSOC);
        $this->set('data', $data);
    }

/**
 * Handle event state through `gg`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for gg.
 * @phpstan-return void
 * @psalm-return void
 * @see self::gg()
 * @example /fr/event/gg
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function gg($param)
    {
        Debug::parseDebug($param);

        $gg = Extraction2::getLast5Value(["mysql_available"]);

        Debug::debug($gg);
        
    }


}
