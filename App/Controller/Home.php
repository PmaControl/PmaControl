<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use App\Library\Cve\ServerCveImpactMatcher;
use App\Library\Extraction2;
use App\Library\Format;
use App\Library\OrphanRefreshScanner;
use App\Library\System;

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
        $availability = self::buildAvailabilitySummary($monitoredIds, $avail);
        $data['available'] = $availability['available'];
        $data['unavailable'] = $availability['unavailable'];
        $data['unavailable_servers'] = $availability['unavailable_servers'];

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
            "slave::seconds_behind_master", "slave::seconds_behind_source",
            "slave::last_io_error", "slave::last_sql_error",
            "slave::last_io_errno", "slave::last_sql_errno"));
        foreach ($slaveData as $id => $row) {
            if (!isset($row['@slave'])) continue;
            foreach ($row['@slave'] as $cn => $s) {
                $data['replication']['total']++;
                $data['replication'][self::classifyReplicationChannel($s)]++;
            }
        }

        // ── 5b. Replication issues dashboard (#1210) ──
        $serverNames = [];
        if (!empty($slaveData)) {
            $ids = implode(',', array_map('intval', array_keys($slaveData)));
            if ($ids !== '') {
                $resN = $db->sql_query("SELECT id, display_name, hostname FROM mysql_server WHERE id IN ($ids)");
                while ($rowN = $db->sql_fetch_array($resN, MYSQLI_ASSOC)) {
                    $serverNames[(int)$rowN['id']] = $rowN;
                }
            }
        }
        $data['replication_issues'] = self::buildReplicationIssues($slaveData, $serverNames);

        // ── 6. Daemon status ──
        $data['daemons'] = ['total' => 0, 'running' => 0, 'stopped' => 0, 'error' => 0, 'list' => []];
        $sql = "SELECT id, name, pid, class, method, refresh_time FROM daemon_main ORDER BY id";
        $res = $db->sql_query($sql);
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['daemons']['total']++;
            $status = System::resolveDaemonStatus($row);
            $data['daemons'][$status]++;
            $row['status'] = $status;
            $data['daemons']['list'][] = $row;
        }

        // ── 7. Version distribution (from Extraction2) ──
        $data['versions'] = [];
        $versionData = Extraction2::display(array("version", "version_comment"));
        foreach ($versionData as $id => $row) {
            $key = self::buildVersionDistributionKey($row['version'] ?? '', $row['version_comment'] ?? '');
            if ($key === null) continue;
            $data['versions'][$key] = ($data['versions'][$key] ?? 0) + 1;
        }
        arsort($data['versions']);
        $data['cve_park'] = self::buildCveParkData($db, $versionData);

        // ── ProxySQL audit snapshot (#929) ──
        $data['proxysql_audit'] = self::buildProxysqlAuditCard($db);

        // ── 8. Data volume (from information_schema, fast) ──
        $data['ts_rows'] = 0;
        $sql = "SELECT SUM(TABLE_ROWS) AS total FROM information_schema.tables WHERE TABLE_SCHEMA='pmacontrol' AND TABLE_NAME LIKE 'ts_value%'";
        $res = $db->sql_query($sql);
        if ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['ts_rows'] = (int)($row['total'] ?? 0);
        }

        // ── 9. Orphan /database/refresh dumps (issue #584) ──
        $data['orphan_refreshes']            = [];
        $data['orphan_refreshes_total_size'] = 0;
        try {
            $orphanReport = OrphanRefreshScanner::scan();
            $data['orphan_refreshes']            = $orphanReport['orphans'];
            $data['orphan_refreshes_total_size'] = $orphanReport['total_size_bytes'];
        } catch (\Throwable $e) {
            // Never break /home for a disk-scan failure.
        }

        // ── 9b. MaxScale offline (issue #1226) ──
        // A MaxScale is considered offline when its admin REST endpoint either
        // can't be reached (maxscale_available = 0) or replies with no service
        // data (maxscale_service_available = 0). We surface both on Home so a
        // freeze on the Architecture graph isn't silent.
        $data['maxscale_offline'] = self::buildMaxScaleOfflineCard($db);

        // ── 10. Stuck binlog analyses ──
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

    public static function buildVersionDistributionKey($version, $comment): ?string
    {
        $version = trim((string) $version);
        if ($version === '') {
            return null;
        }

        $parsed = Format::getMySQLNumVersion($version, (string) $comment);
        $fork = self::normalizeVersionForkLabel($parsed['fork'] ?? 'MySQL');
        $number = (string) ($parsed['number'] ?? $version);
        $major = explode('.', explode('-', $number)[0]);
        $shortVersion = ($major[0] ?? '?').'.'.($major[1] ?? '?');

        return $fork.' '.$shortVersion;
    }

    private static function normalizeVersionForkLabel($fork): string
    {
        switch (strtolower((string) $fork)) {
            case 'mariadb':
                return 'MariaDB';
            case 'percona':
                return 'Percona';
            case 'proxysql':
                return 'ProxySQL';
            case 'maxscale':
                return 'MaxScale';
            case 'mysql router':
                return 'MySQL Router';
            case 'singlestore':
                return 'SingleStore';
            default:
                return 'MySQL';
        }
    }

    private static function buildCveParkData($db, array $versionData): array
    {
        if (!ServerCveImpactMatcher::tablesAvailable($db)) {
            return ServerCveImpactMatcher::loadParkSummary($db);
        }

        $servers = self::loadCveServerRows($db);
        if ($servers !== []) {
            $extraByServer = [];
            foreach ($versionData as $id => $row) {
                if (is_array($row)) {
                    $extraByServer[(int)$id] = $row;
                }
            }
            ServerCveImpactMatcher::loadForServerMain($db, $servers, $extraByServer, true);
        }

        return ServerCveImpactMatcher::loadParkSummary($db);
    }

    /**
     * @return list<array<string,mixed>>
     */
    private static function loadCveServerRows($db): array
    {
        $sql = "SELECT `id`, `is_proxy`, `is_vip`"
            . " FROM `mysql_server`"
            . " WHERE `is_deleted` = 0"
            . " ORDER BY `id`";
        $res = $db->sql_query($sql);
        $servers = [];

        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $servers[] = $row;
        }

        return $servers;
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

    /**
     * Issue #1210 — finer-grained classification feeding the home
     * "Replication issues" dashboard. Returns one of:
     * io_error, sql_error, stopped, lag_critical, lag_warning, ok.
     * `ok` (lag < 60s, no error) is intentionally excluded from the
     * issues card (already covered by the KPI ring).
     */
    public static function classifyReplicationChannelDetail(array $channel): string
    {
        $io  = $channel['replica_io_running']  ?? $channel['slave_io_running']  ?? 'No';
        $sql = $channel['replica_sql_running'] ?? $channel['slave_sql_running'] ?? 'No';
        $ioErr  = trim((string) ($channel['last_io_error']  ?? ''));
        $sqlErr = trim((string) ($channel['last_sql_error'] ?? ''));

        if ($ioErr !== '') {
            return 'io_error';
        }
        if ($sqlErr !== '') {
            return 'sql_error';
        }
        if ($io !== 'Yes' && $sql !== 'Yes') {
            return 'stopped';
        }
        if ($io !== 'Yes' || $sql !== 'Yes') {
            // half-stopped without recorded error — surface as the
            // half that's down so the operator sees it.
            return ($io !== 'Yes') ? 'io_error' : 'sql_error';
        }

        $lag = self::getReplicationLag($channel);
        if ($lag !== null) {
            if ($lag > 300) {
                return 'lag_critical';
            }
            if ($lag > 60) {
                return 'lag_warning';
            }
        }

        return 'ok';
    }

    /**
     * Issue #1210 — build the per-bucket list of channels with an
     * issue. Returns an array keyed by bucket (in display order) with
     * a list of channel rows enriched with the server's display_name
     * so the view can render clickable rows without another query.
     */
    public static function buildReplicationIssues(array $slaveData, array $serverNames): array
    {
        $buckets = [
            'io_error'     => [],
            'sql_error'    => [],
            'stopped'      => [],
            'lag_critical' => [],
            'lag_warning'  => [],
        ];

        foreach ($slaveData as $serverId => $row) {
            if (!isset($row['@slave']) || !is_array($row['@slave'])) {
                continue;
            }
            $serverId = (int) $serverId;
            $hostname = (string) ($serverNames[$serverId]['display_name'] ?? $serverNames[$serverId]['hostname'] ?? ('id ' . $serverId));

            foreach ($row['@slave'] as $cn => $channel) {
                if (!is_array($channel)) {
                    continue;
                }
                $bucket = self::classifyReplicationChannelDetail($channel);
                if (!isset($buckets[$bucket])) {
                    continue;
                }

                $ioErr  = trim((string) ($channel['last_io_error']  ?? ''));
                $sqlErr = trim((string) ($channel['last_sql_error'] ?? ''));
                $errnoIo  = (int) ($channel['last_io_errno']  ?? 0);
                $errnoSql = (int) ($channel['last_sql_errno'] ?? 0);
                $message = $ioErr !== '' ? $ioErr : $sqlErr;

                $buckets[$bucket][] = [
                    'server_id'         => $serverId,
                    'hostname'          => $hostname,
                    'connection_name'   => (string) $cn,
                    'errno_io'          => $errnoIo,
                    'errno_sql'         => $errnoSql,
                    'last_io_error'     => $ioErr,
                    'last_sql_error'    => $sqlErr,
                    'message'           => $message,
                    'message_signature' => self::normalizeReplicationErrorSignature($message),
                    'lag'               => self::getReplicationLag($channel),
                ];
            }
        }

        return $buckets;
    }

    public static function normalizeReplicationErrorSignature(string $message): string
    {
        $message = trim($message);
        if ($message === '') {
            return '';
        }
        // Strip the volatile bits so 30 identical errors collapse into
        // one signature. Keep enough context to identify the failure.
        $sig = preg_replace('/\\d+/', 'N', $message);
        $sig = preg_replace('/\'[^\']*\'/', "'X'", (string) $sig);
        $sig = preg_replace('/\\s+/', ' ', (string) $sig);
        return substr((string) $sig, 0, 200);
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

    public static function buildAvailabilitySummary(array $monitoredIds, array $availabilityRows): array
    {
        $summary = [
            'available' => 0,
            'unavailable' => 0,
            'unavailable_servers' => [],
        ];

        foreach ($monitoredIds as $id => $_enabled) {
            $id = (int) $id;
            $row = $availabilityRows[$id] ?? $availabilityRows[(string) $id] ?? null;

            if (is_array($row) && (string) ($row['mysql_available'] ?? '') === '1') {
                $summary['available']++;
                continue;
            }

            $summary['unavailable']++;
            $summary['unavailable_servers'][$id] = is_array($row)
                ? ($row['mysql_error'] ?? 'Unknown')
                : (\function_exists('__') ? \__('No metric reported') : 'No metric reported');
        }

        return $summary;
    }

    /**
     * #929 — aggregate `proxysql_audit_snapshot` for the home-page card.
     *
     * Returns the totals across all ProxySQL servers, the worst-offending
     * server (max critical, then max warning) for the deep-link, and the
     * "available" flag — false when the snapshot table doesn't exist yet
     * (migration pending) so the view can render a discreet placeholder
     * instead of a hard error.
     *
     * @return array{
     *   available: bool,
     *   total_servers: int,
     *   critical: int,
     *   warning: int,
     *   info: int,
     *   worst_id: int|null,
     *   worst_name: string|null
     * }
     */
    public static function buildProxysqlAuditCard($db): array
    {
        $card = [
            'available'     => false,
            'total_servers' => 0,
            'critical'      => 0,
            'warning'       => 0,
            'info'          => 0,
            'worst_id'      => null,
            'worst_name'    => null,
        ];

        if (!method_exists($db, 'sql_query_silent')) {
            return $card;
        }

        $sql = "SELECT s.id_proxysql_server, s.findings_critical, s.findings_warning, "
             .        "s.findings_info, p.display_name "
             . "FROM proxysql_audit_snapshot s "
             . "JOIN proxysql_server p ON p.id = s.id_proxysql_server "
             . "ORDER BY s.findings_critical DESC, s.findings_warning DESC";
        $res = $db->sql_query_silent($sql);
        if ($res === false) {
            return $card;
        }

        $card['available'] = true;
        $worstScore = -1;
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $crit = (int) ($row['findings_critical'] ?? 0);
            $warn = (int) ($row['findings_warning']  ?? 0);
            $info = (int) ($row['findings_info']     ?? 0);
            $card['total_servers']++;
            $card['critical'] += $crit;
            $card['warning']  += $warn;
            $card['info']     += $info;

            // Worst-offending = highest critical, tie-broken by warning.
            // Score = crit*1e6 + warn keeps the comparison simple.
            $score = $crit * 1000000 + $warn;
            if ($score > $worstScore) {
                $worstScore = $score;
                $card['worst_id']   = (int) ($row['id_proxysql_server'] ?? 0);
                $card['worst_name'] = (string) ($row['display_name']    ?? '');
            }
        }

        return $card;
    }

    /**
     * MaxScale offline summary for /Home/index (issue #1226).
     *
     * A MaxScale is `is_maxscale = '1'` in the collected variables. Two
     * failure modes are folded together:
     *  - admin endpoint unreachable (maxscale_available = 0)
     *  - admin reachable but REST returns no service data
     *    (maxscale_service_available = 0)
     *
     * @return array{servers: list<array{id:int,display_name:string,ip:string,port:string,reason:string}>}
     */
    public static function buildMaxScaleOfflineCard($db): array
    {
        $card = ['servers' => []];

        try {
            $rows = Extraction2::display(array(
                'is_maxscale',
                'maxscale_server::maxscale_available',
                'maxscale_service_server::maxscale_service_available',
                'maxscale_server::maxscale_error',
            ));
        } catch (\Throwable $e) {
            return $card;
        }

        $offline = [];
        foreach ($rows as $id => $row) {
            if ((string)($row['is_maxscale'] ?? '') !== '1') {
                continue;
            }

            $maxscaleAvailable = $row['maxscale_available'] ?? null;
            $serviceAvailable  = $row['maxscale_service_available'] ?? null;
            $isOffline = ((string)$maxscaleAvailable === '0')
                || ((string)$serviceAvailable === '0');
            if (!$isOffline) {
                continue;
            }

            $reason = (string)$maxscaleAvailable === '0'
                ? __('MaxScale admin endpoint unreachable')
                : __('MaxScale REST returned no service data');

            $offline[(int)$id] = [
                'reason' => $reason,
                'error'  => (string)($row['maxscale_error'] ?? ''),
            ];
        }

        if (empty($offline)) {
            return $card;
        }

        $ids = implode(',', array_map('intval', array_keys($offline)));
        $sql = "SELECT id, display_name, ip, port FROM mysql_server WHERE id IN ($ids)";
        $res = $db->sql_query($sql);
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $id = (int)$row['id'];
            $card['servers'][] = [
                'id'           => $id,
                'display_name' => (string)($row['display_name'] ?? ''),
                'ip'           => (string)($row['ip'] ?? ''),
                'port'         => (string)($row['port'] ?? ''),
                'reason'       => $offline[$id]['reason'] ?? '',
                'error'        => $offline[$id]['error']  ?? '',
            ];
        }

        return $card;
    }
}
