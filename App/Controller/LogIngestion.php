<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use App\Library\LogSmartStorage;

class LogIngestion extends Controller
{
    public function dashboard($param)
    {
        $this->di['js']->addJavascript(array('moment.js', 'chart.min.js'));

        $db = Sgbd::sql(DB_DEFAULT);

        $id_mysql_server = !empty($param[0]) ? (int) $param[0] : 0;
        if (empty($id_mysql_server)) {
            $sql = "SELECT min(id) AS id_mysql_server FROM mysql_server";
            $res = $db->sql_query($sql);

            while ($ob = $db->sql_fetch_object($res)) {
                if (!empty($ob->id_mysql_server)) {
                    header('location: '.LINK.$this->getClass().'/'.__FUNCTION__.'/'.$ob->id_mysql_server);
                    exit;
                }
            }
        }

        $sql = "SELECT 
                DATE_FORMAT(bucket_hour, '%Y-%m-%d %H:00:00') AS bucket_label,
                SUM(total_events) AS total_events,
                SUM(total_error) AS total_error,
                SUM(total_warning) AS total_warning,
                SUM(total_critical) AS total_critical
            FROM log_ingestion_metric_hourly
            WHERE id_mysql_server = ".$id_mysql_server." 
            AND bucket_hour >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            GROUP BY bucket_hour
            ORDER BY bucket_hour ASC";

        $res = $db->sql_query($sql);

        $labels = array();
        $total = array();
        $error = array();
        $warning = array();
        $critical = array();

        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $labels[] = $row['bucket_label'];
            $total[] = (int) $row['total_events'];
            $error[] = (int) $row['total_error'];
            $warning[] = (int) $row['total_warning'];
            $critical[] = (int) $row['total_critical'];
        }

        $data = array(
            'id_mysql_server' => $id_mysql_server,
            'labels' => $labels,
            'total' => $total,
            'error' => $error,
            'warning' => $warning,
            'critical' => $critical,
        );

        $this->set('data', $data);
    }

    public function listener($param)
    {
        $id_mysql_server = !empty($param[0]) ? (int) $param[0] : 0;
        $source = !empty($param[1]) ? $param[1] : 'ssh';
        $message = !empty($param[2]) ? urldecode($param[2]) : '';

        if (empty($id_mysql_server) || empty($message)) {
            return;
        }

        $db = Sgbd::sql(DB_DEFAULT);

        $level = LogSmartStorage::detectLevel($message);
        $category = LogSmartStorage::detectCategory($message);
        $fingerprint = LogSmartStorage::fingerprint($source, $message);

        $escaped_source = $db->sql_real_escape_string($source);
        $escaped_message = $db->sql_real_escape_string($message);
        $escaped_level = $db->sql_real_escape_string($level);
        $escaped_category = $db->sql_real_escape_string($category);
        $escaped_fingerprint = $db->sql_real_escape_string($fingerprint);

        $sql = "INSERT INTO log_ingestion_event
            (id_mysql_server, source_name, event_level, event_category, message, message_fingerprint, event_date)
            VALUES
            (".$id_mysql_server.", '".$escaped_source."', '".$escaped_level."', '".$escaped_category."', '".$escaped_message."', '".$escaped_fingerprint."', NOW())
            ON DUPLICATE KEY UPDATE duplicate_count = duplicate_count + 1, last_seen = NOW()";

        $db->sql_query($sql);

        $sql = "INSERT INTO log_ingestion_metric_hourly
            (id_mysql_server, bucket_hour, total_events, total_error, total_warning, total_critical)
            VALUES
            (".$id_mysql_server.", DATE_FORMAT(NOW(), '%Y-%m-%d %H:00:00'), 1,
                ".($level === 'error' ? 1 : 0).",
                ".($level === 'warning' ? 1 : 0).",
                ".($level === 'critical' ? 1 : 0).")
            ON DUPLICATE KEY UPDATE
                total_events = total_events + 1,
                total_error = total_error + VALUES(total_error),
                total_warning = total_warning + VALUES(total_warning),
                total_critical = total_critical + VALUES(total_critical)";

        $db->sql_query($sql);
    }
}
