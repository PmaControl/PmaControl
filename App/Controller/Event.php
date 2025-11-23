<?php

namespace App\Controller;

use Glial\Synapse\Controller;
use Glial\Sgbd\Sgbd;
use App\Library\Extraction2;
use App\Library\Debug;

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

    public function gg($param)
    {
        Debug::parseDebug($param);

        $gg = Extraction2::getLast5Value(["mysql_available"]);

        Debug::debug($gg);
        
    }


}