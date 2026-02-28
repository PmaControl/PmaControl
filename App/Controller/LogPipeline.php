<?php

namespace App\Controller;

use App\Library\Debug;
use App\Library\Ssh;
use Glial\Sgbd\Sgbd;
use Glial\Synapse\Controller;

class LogPipeline extends Controller
{
    private function esc(string $value): string
    {
        return str_replace(["\\", "'"], ["\\\\", "\\'"], $value);
    }

    public function index($param = [])
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $data = [];
        $data['servers'] = [];

        $sql = "SELECT id, display_name, ip FROM mysql_server WHERE deleted = 0 ORDER BY display_name";
        $res = $db->sql_query($sql);

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['servers'][] = $arr;
        }

        $this->set('data', $data);
    }

    public function collect($param)
    {
        Debug::parseDebug($param);

        $idMysqlServer = (int) ($param[0] ?? 0);
        $logPath       = (string) ($param[1] ?? '/var/log/mysql/error.log');
        $maxLines      = (int) ($param[2] ?? 2000);

        if ($idMysqlServer <= 0) {
            echo "Missing id_mysql_server\n";
            return;
        }

        $db = Sgbd::sql(DB_DEFAULT);

        $ssh = Ssh::ssh($idMysqlServer, 'mysql');
        if ($ssh === false) {
            echo "Unable to connect over SSH for server #{$idMysqlServer}\n";
            return;
        }

        $safePath = escapeshellarg($logPath);
        $cmd      = "tail -n {$maxLines} {$safePath}";
        $content  = trim((string) $ssh->exec($cmd));

        $ingested = 0;
        $warnings = 0;

        if (!empty($content)) {
            $lines = explode("\n", $content);

            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                $eventTime = date('Y-m-d H:i:s');
                if (preg_match('/^(\\d{4}-\\d{2}-\\d{2}T\\d{2}:\\d{2}:\\d{2})/', $line, $m)) {
                    $eventTime = str_replace('T', ' ', $m[1]);
                }

                $severity = 'INFO';
                if (stripos($line, 'error') !== false) {
                    $severity = 'ERROR';
                } elseif (stripos($line, 'warn') !== false) {
                    $severity = 'WARN';
                    $warnings++;
                }

                $hash = sha1($idMysqlServer.'|'.$logPath.'|'.$line);

                $sql = "INSERT IGNORE INTO ts_log_event
                (`id_mysql_server`, `log_path`, `event_time`, `severity`, `event_hash`, `raw_line`, `created_at`)
                VALUES
                (".$idMysqlServer.", '".$this->esc($logPath)."', '".$eventTime."', '".$severity."', '".$hash."', '".$this->esc($line)."', NOW())";

                $db->sql_query($sql);
                $ingested++;
            }
        }

        $agg = "INSERT INTO ts_log_event_hourly (`bucket_time`, `id_mysql_server`, `severity`, `event_count`, `updated_at`)
        SELECT
            DATE_FORMAT(event_time, '%Y-%m-%d %H:00:00') AS bucket_time,
            id_mysql_server,
            severity,
            COUNT(*) AS event_count,
            NOW()
        FROM ts_log_event
        WHERE event_time >= NOW() - INTERVAL 24 HOUR
        GROUP BY DATE_FORMAT(event_time, '%Y-%m-%d %H:00:00'), id_mysql_server, severity
        ON DUPLICATE KEY UPDATE
            event_count = VALUES(event_count),
            updated_at = NOW()";
        $db->sql_query($agg);

        echo "Collected {$ingested} lines from {$logPath} for server #{$idMysqlServer} ({$warnings} warnings).\n";
    }

    public function api24h($param)
    {
        Debug::parseDebug($param);

        $idMysqlServer = (int) ($param[0] ?? 0);

        if ($idMysqlServer <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'missing id_mysql_server']);
            return;
        }

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT
            DATE_FORMAT(bucket_time, '%Y-%m-%d %H:00:00') AS bucket,
            severity,
            event_count
        FROM ts_log_event_hourly
        WHERE id_mysql_server = {$idMysqlServer}
          AND bucket_time >= NOW() - INTERVAL 24 HOUR
        ORDER BY bucket_time ASC";

        $res = $db->sql_query($sql);

        $series = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $bucket = $row['bucket'];
            if (!isset($series[$bucket])) {
                $series[$bucket] = ['ERROR' => 0, 'WARN' => 0, 'INFO' => 0];
            }
            $series[$bucket][$row['severity']] = (int) $row['event_count'];
        }

        $labels = array_keys($series);
        $errors = [];
        $warns  = [];
        $infos  = [];

        foreach ($series as $line) {
            $errors[] = $line['ERROR'];
            $warns[]  = $line['WARN'];
            $infos[]  = $line['INFO'];
        }

        header('Content-Type: application/json');
        echo json_encode([
            'labels' => $labels,
            'datasets' => [
                ['label' => 'ERROR', 'data' => $errors, 'borderColor' => '#d9534f', 'fill' => false],
                ['label' => 'WARN', 'data' => $warns, 'borderColor' => '#f0ad4e', 'fill' => false],
                ['label' => 'INFO', 'data' => $infos, 'borderColor' => '#5bc0de', 'fill' => false],
            ],
        ]);
    }
}
