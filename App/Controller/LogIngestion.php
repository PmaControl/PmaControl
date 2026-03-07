<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use \App\Library\LogIngestionManager;

class LogIngestion extends Controller
{
    public function collect($param)
    {
        $this->view = false;

        if (! empty($param[0])) {
            LogIngestionManager::collectForServer((int) $param[0]);
            return;
        }

        LogIngestionManager::collectAll();
    }

    public function dashboard24h($param)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $id_mysql_server = ! empty($param[0]) ? (int) $param[0] : 0;

        if (empty($id_mysql_server)) {
            $sql = "SELECT id FROM mysql_server ORDER BY id ASC LIMIT 1";
            $res = $db->sql_query($sql);
            while ($ob = $db->sql_fetch_object($res)) {
                $id_mysql_server = (int) $ob->id;
            }
        }

        $this->di['js']->addJavascript(array('chart.min.js'));

        $sql = "SELECT DATE_FORMAT(event_date, '%Y-%m-%d %H:00:00') AS bucket_hour, level, SUM(count_seen) AS total\n                FROM ssh_log_event\n                WHERE id_mysql_server = ".$id_mysql_server."\n                AND event_date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)\n                GROUP BY 1,2\n                ORDER BY 1 ASC";

        $res = $db->sql_query($sql);

        $series = array();
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $series[] = $row;
        }

        $data = array();
        $data['id_mysql_server'] = $id_mysql_server;
        $data['series'] = $series;

        $this->set('data', $data);
    }
}
