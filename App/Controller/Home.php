<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use App\Library\Extraction2;

class Home extends Controller {

    function before($param) {
    }

    function index() {
        $this->title = __("Dashboard");
        $this->ariane = "";

        $db = Sgbd::sql(DB_DEFAULT);

        // ── 1. Server inventory ──
        $data['servers'] = ['total' => 0, 'monitored' => 0, 'proxy' => 0, 'vip' => 0, 'mysql' => 0];
        $sql = "SELECT
            COUNT(*) AS total,
            SUM(is_monitored) AS monitored,
            SUM(is_proxy) AS proxy,
            SUM(is_vip) AS vip,
            SUM(CASE WHEN is_proxy=0 AND is_vip=0 THEN 1 ELSE 0 END) AS mysql
        FROM mysql_server WHERE is_deleted=0";
        $res = $db->sql_query($sql);
        if ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['servers'] = $row;
        }

        // ── 2. Clients ──
        $data['clients'] = [];
        $sql = "SELECT c.id, c.libelle AS name, c.is_monitored, COUNT(s.id) AS cnt
                FROM client c
                LEFT JOIN mysql_server s ON s.id_client=c.id AND s.is_deleted=0
                GROUP BY c.id, c.libelle, c.is_monitored
                HAVING cnt > 0
                ORDER BY cnt DESC";
        $res = $db->sql_query($sql);
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['clients'][] = $row;
        }

        // ── 3. Environments ──
        $data['environments'] = [];
        $sql = "SELECT d.libelle AS name, d.class, COUNT(s.id) AS cnt
                FROM environment d
                LEFT JOIN mysql_server s ON s.id_environment=d.id AND s.is_deleted=0
                GROUP BY d.libelle, d.class
                HAVING cnt > 0
                ORDER BY FIELD(d.class,'danger','warning','default','info','success','primary') , cnt DESC";
        $res = $db->sql_query($sql);
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['environments'][] = $row;
        }

        // ── 4. Availability (monitored servers only) ──
        $data['available'] = 0;
        $data['unavailable'] = 0;
        $data['unavailable_servers'] = [];

        // Build set of effectively monitored server IDs (server + client both monitored)
        $sqlMon = "SELECT s.id FROM mysql_server s
                   INNER JOIN client c ON c.id = s.id_client
                   WHERE s.is_deleted = 0 AND s.is_monitored = 1 AND c.is_monitored = 1";
        $resMon = $db->sql_query($sqlMon);
        $monitoredIds = [];
        while ($row = $db->sql_fetch_array($resMon, MYSQLI_ASSOC)) {
            $monitoredIds[(int)$row['id']] = true;
        }

        $avail = Extraction2::display(array("mysql_available", "mysql_error"));
        foreach ($avail as $id => $row) {
            if (!isset($monitoredIds[(int)$id])) {
                continue;
            }
            if (($row['mysql_available'] ?? '') === '1') {
                $data['available']++;
            } else {
                $data['unavailable']++;
                $data['unavailable_servers'][$id] = $row['mysql_error'] ?? 'Unknown';
            }
        }

        // Map unavailable server IDs to display names
        if (!empty($data['unavailable_servers'])) {
            $ids = implode(',', array_map('intval', array_keys($data['unavailable_servers'])));
            $sql = "SELECT id, display_name, ip, port FROM mysql_server WHERE id IN ($ids)";
            $res = $db->sql_query($sql);
            $nameMap = [];
            while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $nameMap[(int)$row['id']] = $row;
            }
            $enriched = [];
            foreach ($data['unavailable_servers'] as $id => $error) {
                $srv = $nameMap[(int)$id] ?? null;
                if ($srv) {
                    $enriched[] = ['id' => $id, 'name' => $srv['display_name'], 'ip' => $srv['ip'], 'port' => $srv['port'], 'error' => $error];
                }
            }
            $data['unavailable_servers'] = $enriched;
        }

        // ── 5. Replication summary ──
        $data['replication'] = ['ok' => 0, 'lag' => 0, 'error' => 0, 'stopped' => 0, 'total' => 0];
        $slaveData = Extraction2::display(array("slave::slave_io_running", "slave::slave_sql_running",
            "slave::seconds_behind_master", "slave::seconds_behind_source", "slave::last_io_error", "slave::last_sql_error"));
        foreach ($slaveData as $id => $row) {
            if (!isset($row['@slave'])) continue;
            foreach ($row['@slave'] as $cn => $s) {
                $data['replication']['total']++;
                $data['replication'][self::classifyReplicationChannel($s)]++;
            }
        }

        // ── 6. Daemon status ──
        $data['daemons'] = ['total' => 0, 'running' => 0, 'stopped' => 0, 'error' => 0, 'list' => []];
        $sql = "SELECT id, name, pid, class, method, refresh_time FROM daemon_main ORDER BY id";
        $res = $db->sql_query($sql);
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['daemons']['total']++;
            $status = 'stopped';
            if (!empty($row['pid'])) {
                $alive = @shell_exec("ps -p ".(int)$row['pid']." -o pid=");
                $status = (trim($alive ?? '') !== '') ? 'running' : 'error';
            }
            $data['daemons'][$status]++;
            $row['status'] = $status;
            $data['daemons']['list'][] = $row;
        }

        // ── 7. Version distribution (from Extraction2) ──
        $data['versions'] = [];
        $versionData = Extraction2::display(array("version", "version_comment"));
        foreach ($versionData as $id => $row) {
            $v = $row['version'] ?? '';
            $comment = $row['version_comment'] ?? '';
            if ($v === '') continue;
            $fork = 'MySQL';
            if (stripos($v, 'mariadb') !== false || stripos($comment, 'mariadb') !== false) $fork = 'MariaDB';
            elseif (stripos($comment, 'percona') !== false) $fork = 'Percona';
            elseif (stripos($comment, 'proxysql') !== false) $fork = 'ProxySQL';
            elseif (stripos($comment, 'maxscale') !== false) $fork = 'MaxScale';
            elseif (stripos($comment, 'router') !== false) $fork = 'MySQL Router';
            $major = explode('.', explode('-', $v)[0]);
            $shortVersion = ($major[0] ?? '?').'.'.($major[1] ?? '?');
            $key = $fork.' '.$shortVersion;
            $data['versions'][$key] = ($data['versions'][$key] ?? 0) + 1;
        }
        arsort($data['versions']);

        // ── 8. Data volume (from information_schema, fast) ──
        $data['ts_rows'] = 0;
        $sql = "SELECT SUM(TABLE_ROWS) AS total FROM information_schema.tables WHERE TABLE_SCHEMA='pmacontrol' AND TABLE_NAME LIKE 'ts_value%'";
        $res = $db->sql_query($sql);
        if ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['ts_rows'] = (int)($row['total'] ?? 0);
        }

        // ── 9. Stuck binlog analyses ──
        $data['stuck_analyses'] = [];
        $sql = "SELECT ba.id, ba.id_mysql_server, ba.created_at, ba.time_start, ba.time_end,
                       ms.display_name, ms.ip, TIMESTAMPDIFF(MINUTE, ba.created_at, NOW()) AS minutes_ago
                FROM binlog_analysis ba
                JOIN mysql_server ms ON ba.id_mysql_server = ms.id
                WHERE ba.status = 'running'
                  AND ba.created_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE)
                ORDER BY ba.created_at";
        $res = $db->sql_query($sql);
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['stuck_analyses'][] = $row;
        }

        $this->set('data', $data);
    }

    function list_server($param) {
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM mysql_server ORDER BY ip";
        $data['server'] = $db->sql_fetch_yield($sql);
        $this->set('data', $data);
    }

    public static function classifyReplicationChannel(array $channel): string
    {
        $io = $channel['replica_io_running'] ?? $channel['slave_io_running'] ?? 'No';
        $sql = $channel['replica_sql_running'] ?? $channel['slave_sql_running'] ?? 'No';
        $err = !empty($channel['last_io_error'] ?? '') || !empty($channel['last_sql_error'] ?? '');

        if ($io !== 'Yes' && $sql !== 'Yes') {
            return 'stopped';
        }

        if ($io !== 'Yes' || $sql !== 'Yes' || $err) {
            return 'error';
        }

        $lag = self::getReplicationLag($channel);
        if ($lag !== null && $lag > 0) {
            return 'lag';
        }

        return 'ok';
    }

    public static function getReplicationLag(array $channel): ?int
    {
        $sourceLag = self::normalizeReplicationLagValue($channel['seconds_behind_source'] ?? null);
        if ($sourceLag !== null) {
            return $sourceLag;
        }

        return self::normalizeReplicationLagValue($channel['seconds_behind_master'] ?? null);
    }

    private static function normalizeReplicationLagValue($lag): ?int
    {
        $lag = trim((string) $lag);

        if ($lag === '' || strtoupper($lag) === 'NULL') {
            return null;
        }

        return (int) $lag;
    }
}
