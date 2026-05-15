<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;

/**
 * SuperAdmin audit log (#1235).
 *
 * `/AuditLog/index`         — list users by activity (last 24 h)
 * `/AuditLog/user/<id>/`    — per-user timeline (request_log joined with
 *                             auth_event + subprocess_log + client_metrics
 *                             for the same user)
 * `/AuditLog/correlation/<request_uid>/`
 *                           — request → sub-process chain for one request
 * `/AuditLog/ip/<ip>/`      — all users that hit pmacontrol from that IP
 * `/AuditLog/timeline`      — AJAX JSON for DataTable rows (paged)
 *
 * Wildcard ACL: only `SuperAdministrator[] = "*"` so no lower role lands here.
 *
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @license GPL-3.0
 */
class AuditLog extends Controller
{
    public function before($param) {}

    /**
     * GET /AuditLog/index — landing page: top users by hits 24 h.
     */
    public function index()
    {
        $this->title  = __('Audit Log');
        $this->ariane = __('SuperAdmin') . ' / Audit';

        $db = Sgbd::sql(DB_DEFAULT);

        $data = [
            'top_users'      => self::topUsers($db),
            'totals_24h'     => self::totalsLast24h($db),
            'auth_failures'  => self::recentAuthFailures($db, 20),
            'top_routes'     => self::topRoutes($db),
            'top_ips'        => self::topIps($db),
            'now'            => time(),
        ];
        $this->set('data', $data);
    }

    /**
     * GET /AuditLog/user/<id_user_main>/ — per-user timeline.
     */
    public function user($param)
    {
        $idUser = (int) ($param[0] ?? 0);
        if ($idUser <= 0) {
            header('Location: ' . LINK . 'AuditLog/index');
            exit;
        }
        $this->title  = __('Audit') . ' — user #' . $idUser;
        $this->ariane = __('SuperAdmin') . ' / Audit / user';

        $db = Sgbd::sql(DB_DEFAULT);

        $data = [
            'id_user_main' => $idUser,
            'user_row'     => self::userInfo($db, $idUser),
            'timeline'     => self::userTimeline($db, $idUser, 200),
            'totals'       => self::userTotals($db, $idUser),
            'now'          => time(),
        ];
        $this->set('data', $data);
    }

    /**
     * GET /AuditLog/correlation/<request_uid>/ — one request_uid chain.
     */
    public function correlation($param)
    {
        $uid = preg_replace('/[^a-f0-9]/i', '', (string) ($param[0] ?? ''));
        if (strlen($uid) !== 32) {
            header('Location: ' . LINK . 'AuditLog/index');
            exit;
        }
        $this->title  = __('Audit') . ' — request ' . substr($uid, 0, 8) . '…';
        $this->ariane = __('SuperAdmin') . ' / Audit / correlation';

        $db = Sgbd::sql(DB_DEFAULT);
        $uidEsc = $db->sql_real_escape_string($uid);

        $request = null;
        $res = $db->sql_query("SELECT * FROM request_log WHERE request_uid = '$uidEsc' LIMIT 1");
        if ($res) {
            $request = $db->sql_fetch_array($res, MYSQLI_ASSOC) ?: null;
        }
        $authEvents = [];
        $res = $db->sql_query("SELECT * FROM auth_event WHERE request_uid = '$uidEsc' ORDER BY date");
        if ($res) {
            while ($r = $db->sql_fetch_array($res, MYSQLI_ASSOC)) { $authEvents[] = $r; }
        }
        $subprocs = [];
        $res = $db->sql_query("SELECT * FROM subprocess_log WHERE request_uid = '$uidEsc' ORDER BY date_start");
        if ($res) {
            while ($r = $db->sql_fetch_array($res, MYSQLI_ASSOC)) { $subprocs[] = $r; }
        }
        $clientMetrics = null;
        $res = $db->sql_query("SELECT * FROM audit_client_metrics WHERE request_uid = '$uidEsc' LIMIT 1");
        if ($res) {
            $clientMetrics = $db->sql_fetch_array($res, MYSQLI_ASSOC) ?: null;
        }

        $this->set('data', [
            'uid'            => $uid,
            'request'        => $request,
            'auth_events'    => $authEvents,
            'subprocess'     => $subprocs,
            'client_metrics' => $clientMetrics,
        ]);
    }

    /**
     * GET /AuditLog/ip/<ip>/ — IP-pivot timeline.
     */
    public function ip($param)
    {
        $ip = (string) ($param[0] ?? '');
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            header('Location: ' . LINK . 'AuditLog/index');
            exit;
        }
        $this->title  = __('Audit') . ' — IP ' . $ip;
        $this->ariane = __('SuperAdmin') . ' / Audit / IP';

        $db = Sgbd::sql(DB_DEFAULT);
        $ipEsc = $db->sql_real_escape_string($ip);

        $rows = [];
        $res = $db->sql_query(
            "SELECT r.*, ua.os_family, ua.browser_family, ua.browser_version, ua.is_robot, ua.robot_family, "
          . " ua.device_type, u.login AS user_login "
          . "FROM request_log r "
          . "LEFT JOIN audit_user_agent ua ON ua.ua_hash = r.user_agent_hash "
          . "LEFT JOIN user_main u ON u.id = r.id_user_main "
          . "WHERE r.ip = '$ipEsc' "
          . "ORDER BY r.date DESC LIMIT 200"
        );
        if ($res) {
            while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) { $rows[] = $row; }
        }

        $this->set('data', [
            'ip'   => $ip,
            'rows' => $rows,
        ]);
    }

    /**
     * POST /AuditLog/recordClientMetrics/ajax:true/
     *
     * Receives the JSON payload emitted by clientMetrics.js via sendBeacon.
     * Origin check + request_uid validation; spool to NDJSON for the drain.
     * Returns 204 (no body — beacon discards anything anyway).
     */
    public function recordClientMetrics()
    {
        $this->layout_name = false;
        $this->view = false;

        // Origin check — sendBeacon doesn't carry CSRF tokens, so we
        // authenticate the source by Origin/Referer matching the host.
        $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
        $origin = (string) ($_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '');
        if ($host !== '' && $origin !== '' && !str_contains($origin, $host)) {
            http_response_code(403);
            return;
        }

        $raw = file_get_contents('php://input') ?: '';
        if ($raw === '' || strlen($raw) > 16384) {
            http_response_code(400);
            return;
        }
        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            http_response_code(400);
            return;
        }
        $uid = (string) ($payload['request_uid'] ?? '');
        if (!preg_match('/^[a-f0-9]{32}$/i', $uid)) {
            http_response_code(400);
            return;
        }

        // Stamp the authenticated user id from the session so the drain
        // doesn't have to trust the client's "id_user_main".
        if (isset($_SITE['IdUser']) && (int) $_SITE['IdUser'] > 0) {
            $payload['id_user_main'] = (int) $_SITE['IdUser'];
        }
        $payload['date'] = date('Y-m-d H:i:s.') . sprintf('%03d', (int) (microtime(true) * 1000) % 1000);
        $payload['payload_size_bytes'] = strlen($raw);

        // Append to the client_metrics spool dir.
        $dir = (defined('TMP') ? TMP : '/srv/www/pmacontrol/tmp/') . 'audit/client_metrics/';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $path = $dir . date('Y-m-d') . '.ndjson';
        $line = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($line !== false) {
            $fh = @fopen($path, 'a');
            if ($fh !== false) {
                @fwrite($fh, $line . "\n");
                @fclose($fh);
            }
        }
        http_response_code(204);
    }

    // ---- helpers ----

    private static function topUsers($db, int $limit = 30): array
    {
        $out = [];
        $sql = "SELECT r.id_user_main, u.login, COUNT(*) AS hits, "
             . "       MAX(r.date) AS last_seen, "
             . "       COUNT(DISTINCT r.ip) AS distinct_ips "
             . "FROM request_log r "
             . "LEFT JOIN user_main u ON u.id = r.id_user_main "
             . "WHERE r.date > NOW() - INTERVAL 24 HOUR AND r.id_user_main IS NOT NULL "
             . "GROUP BY r.id_user_main, u.login "
             . "ORDER BY hits DESC LIMIT $limit";
        $res = $db->sql_query($sql);
        if ($res) {
            while ($r = $db->sql_fetch_array($res, MYSQLI_ASSOC)) { $out[] = $r; }
        }
        return $out;
    }

    private static function totalsLast24h($db): array
    {
        $out = ['hits' => 0, 'auth_events' => 0, 'distinct_users' => 0, 'distinct_ips' => 0, 'robots' => 0];
        $res = $db->sql_query(
            "SELECT COUNT(*) AS hits, COUNT(DISTINCT id_user_main) AS users, COUNT(DISTINCT ip) AS ips "
          . "FROM request_log WHERE date > NOW() - INTERVAL 24 HOUR"
        );
        if ($res && $row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $out['hits']           = (int) $row['hits'];
            $out['distinct_users'] = (int) $row['users'];
            $out['distinct_ips']   = (int) $row['ips'];
        }
        $res = $db->sql_query("SELECT COUNT(*) AS n FROM auth_event WHERE date > NOW() - INTERVAL 24 HOUR");
        if ($res && $row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $out['auth_events'] = (int) $row['n'];
        }
        $res = $db->sql_query(
            "SELECT COUNT(*) AS n FROM request_log r "
          . "JOIN audit_user_agent ua ON ua.ua_hash = r.user_agent_hash "
          . "WHERE r.date > NOW() - INTERVAL 24 HOUR AND ua.is_robot = 1"
        );
        if ($res && $row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $out['robots'] = (int) $row['n'];
        }
        return $out;
    }

    private static function recentAuthFailures($db, int $limit = 20): array
    {
        $out = [];
        $sql = "SELECT ae.*, u.login "
             . "FROM auth_event ae "
             . "LEFT JOIN user_main u ON u.id = ae.id_user_main "
             . "WHERE ae.event_type IN ('login_fail','token_mismatch','fingerprint_mismatch','expired','unknown_selector','csrf_reject','rate_limit') "
             . "  AND ae.date > NOW() - INTERVAL 7 DAY "
             . "ORDER BY ae.date DESC LIMIT $limit";
        $res = $db->sql_query($sql);
        if ($res) {
            while ($r = $db->sql_fetch_array($res, MYSQLI_ASSOC)) { $out[] = $r; }
        }
        return $out;
    }

    private static function topRoutes($db, int $limit = 15): array
    {
        $out = [];
        $sql = "SELECT controller, action, COUNT(*) AS hits, "
             . "       ROUND(AVG(php_ms)) AS avg_ms, MAX(php_ms) AS max_ms "
             . "FROM request_log "
             . "WHERE date > NOW() - INTERVAL 24 HOUR AND controller IS NOT NULL "
             . "GROUP BY controller, action "
             . "ORDER BY hits DESC LIMIT $limit";
        $res = $db->sql_query($sql);
        if ($res) {
            while ($r = $db->sql_fetch_array($res, MYSQLI_ASSOC)) { $out[] = $r; }
        }
        return $out;
    }

    private static function topIps($db, int $limit = 15): array
    {
        $out = [];
        $sql = "SELECT ip, COUNT(*) AS hits, COUNT(DISTINCT id_user_main) AS users, MAX(date) AS last_seen "
             . "FROM request_log "
             . "WHERE date > NOW() - INTERVAL 24 HOUR "
             . "GROUP BY ip ORDER BY hits DESC LIMIT $limit";
        $res = $db->sql_query($sql);
        if ($res) {
            while ($r = $db->sql_fetch_array($res, MYSQLI_ASSOC)) { $out[] = $r; }
        }
        return $out;
    }

    private static function userInfo($db, int $idUser): ?array
    {
        $res = $db->sql_query("SELECT id, login, name, firstname, email, id_group FROM user_main WHERE id = $idUser");
        if (!$res) return null;
        return $db->sql_fetch_array($res, MYSQLI_ASSOC) ?: null;
    }

    private static function userTimeline($db, int $idUser, int $limit = 200): array
    {
        $out = [];
        $sql = "SELECT r.id, r.request_uid, r.date, r.ip, r.method, r.uri, r.status, r.php_ms, "
             . "       r.controller, r.action, "
             . "       ua.os_family, ua.browser_family, ua.browser_version, ua.is_robot, "
             . "       ua.device_type "
             . "FROM request_log r "
             . "LEFT JOIN audit_user_agent ua ON ua.ua_hash = r.user_agent_hash "
             . "WHERE r.id_user_main = $idUser "
             . "ORDER BY r.date DESC LIMIT $limit";
        $res = $db->sql_query($sql);
        if (!$res) return $out;
        $prev = null;
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $row['delta_s'] = ($prev !== null)
                ? max(0, strtotime($prev) - strtotime($row['date']))
                : null;
            $prev = $row['date'];
            $out[] = $row;
        }
        return $out;
    }

    private static function userTotals($db, int $idUser): array
    {
        $out = ['hits_24h' => 0, 'hits_7d' => 0, 'distinct_ips_7d' => 0, 'last_seen' => null, 'logins' => 0, 'failures' => 0];
        $res = $db->sql_query(
            "SELECT COUNT(*) AS h24, MAX(date) AS last_seen FROM request_log "
          . "WHERE id_user_main = $idUser AND date > NOW() - INTERVAL 24 HOUR"
        );
        if ($res && $r = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $out['hits_24h']  = (int) $r['h24'];
            $out['last_seen'] = $r['last_seen'];
        }
        $res = $db->sql_query(
            "SELECT COUNT(*) AS h7, COUNT(DISTINCT ip) AS ips FROM request_log "
          . "WHERE id_user_main = $idUser AND date > NOW() - INTERVAL 7 DAY"
        );
        if ($res && $r = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $out['hits_7d']       = (int) $r['h7'];
            $out['distinct_ips_7d'] = (int) $r['ips'];
        }
        $res = $db->sql_query(
            "SELECT "
          . "  SUM(event_type = 'login_success') AS logins, "
          . "  SUM(event_type IN ('login_fail','token_mismatch','fingerprint_mismatch','csrf_reject')) AS failures "
          . "FROM auth_event WHERE id_user_main = $idUser AND date > NOW() - INTERVAL 7 DAY"
        );
        if ($res && $r = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $out['logins']   = (int) ($r['logins'] ?? 0);
            $out['failures'] = (int) ($r['failures'] ?? 0);
        }
        return $out;
    }
}
