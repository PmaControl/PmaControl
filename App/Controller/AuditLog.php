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
    private const EXPORT_CSRF_SCOPE  = 'auditlog.export';
    private const FORGET_CSRF_SCOPE  = 'auditlog.forget';
    private const SETTINGS_CSRF_SCOPE = 'auditlog.settings';

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
            'top_users'       => self::topUsers($db),
            'totals_24h'      => self::totalsLast24h($db),
            'auth_failures'   => self::recentAuthFailures($db, 20),
            'top_routes'      => self::topRoutes($db),
            'top_ips'         => self::topIps($db),
            'hits_per_hour'   => self::hitsPerHour24h($db),
            'auth_per_hour'   => self::authPerHour24h($db),
            'now'             => time(),
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
     * GET  /AuditLog/settings/<id>/  — render the per-user preferences form.
     * POST /AuditLog/settings/<id>/  — persist extended_logging + retention overrides
     *                                  + notes into audit_subject_preference.
     *
     * #1235 post-MVP #9.
     */
    public function settings($param)
    {
        $idUser = (int) ($param[0] ?? 0);
        if ($idUser <= 0) {
            header('Location: ' . LINK . 'AuditLog/index');
            exit;
        }
        $db = Sgbd::sql(DB_DEFAULT);
        $isPost = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST';
        if ($isPost) {
            if ($failure = \App\Library\Security\CsrfGuard::ensureOrFail($_POST, $_SERVER, $_SESSION, self::SETTINGS_CSRF_SCOPE)) {
                http_response_code($failure['status']);
                echo $failure['body'];
                return;
            }
            $extended = !empty($_POST['extended_logging']) ? 1 : 0;
            $retReq   = isset($_POST['retention_request_d']) && $_POST['retention_request_d'] !== ''
                ? (int) $_POST['retention_request_d'] : null;
            $retAuth  = isset($_POST['retention_auth_d']) && $_POST['retention_auth_d'] !== ''
                ? (int) $_POST['retention_auth_d'] : null;
            $notes    = trim((string) ($_POST['notes'] ?? ''));
            if (strlen($notes) > 255) $notes = substr($notes, 0, 255);

            $notesSql = $notes === '' ? 'NULL' : "'" . $db->sql_real_escape_string($notes) . "'";
            $retReqSql  = $retReq === null  ? 'NULL' : (int) $retReq;
            $retAuthSql = $retAuth === null ? 'NULL' : (int) $retAuth;

            $db->sql_query(
                "INSERT INTO audit_subject_preference "
              . "(id_user_main, extended_logging, retention_request_d, retention_auth_d, notes) VALUES "
              . "($idUser, $extended, $retReqSql, $retAuthSql, $notesSql) "
              . "ON DUPLICATE KEY UPDATE "
              . "extended_logging = $extended, "
              . "retention_request_d = $retReqSql, "
              . "retention_auth_d = $retAuthSql, "
              . "notes = $notesSql"
            );
            header('Location: ' . LINK . 'AuditLog/settings/' . $idUser . '/?saved=1');
            exit;
        }
        $pref = null;
        $res = $db->sql_query("SELECT * FROM audit_subject_preference WHERE id_user_main = $idUser");
        if ($res) {
            $pref = $db->sql_fetch_array($res, MYSQLI_ASSOC) ?: null;
        }
        $this->title  = __('Audit') . ' — settings user #' . $idUser;
        $this->ariane = __('SuperAdmin') . ' / Audit / settings';
        $this->set('data', [
            'id_user_main' => $idUser,
            'pref'         => $pref,
            'csrf_field'   => \App\Library\Security\Csrf::DEFAULT_FIELD,
            'csrf_token'   => \App\Library\Security\Csrf::issueToken($_SESSION, self::SETTINGS_CSRF_SCOPE),
            'saved'        => !empty($_GET['saved']),
        ]);
    }

    /**
     * GET  /AuditLog/exportUser/<id>/      — render the export form (with CSRF).
     * POST /AuditLog/exportUser/<id>/      — stream a CSV bundle of all rows
     *                                       attributable to id_user_main = <id>.
     *
     * GDPR right-to-portability (#1235 post-MVP #8).
     */
    public function exportUser($param)
    {
        $idUser = (int) ($param[0] ?? 0);
        if ($idUser <= 0) {
            header('Location: ' . LINK . 'AuditLog/index');
            exit;
        }
        $isPost = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST';
        if ($isPost) {
            if ($failure = \App\Library\Security\CsrfGuard::ensureOrFail($_POST, $_SERVER, $_SESSION, self::EXPORT_CSRF_SCOPE)) {
                http_response_code($failure['status']);
                echo $failure['body'];
                return;
            }
            $this->streamExportCsv($idUser);
            // Mark in audit_subject_preference so the operator can see
            // the last time the subject's data was exported.
            $this->stampSubjectPreference($idUser, ['last_export_at' => date('Y-m-d H:i:s.') . sprintf('%03d', (int) (microtime(true) * 1000) % 1000)]);
            return;
        }
        $this->title  = __('Audit') . ' — export user #' . $idUser;
        $this->ariane = __('SuperAdmin') . ' / Audit / export';
        $this->set('data', [
            'id_user_main' => $idUser,
            'csrf_field'   => \App\Library\Security\Csrf::DEFAULT_FIELD,
            'csrf_token'   => \App\Library\Security\Csrf::issueToken($_SESSION, self::EXPORT_CSRF_SCOPE),
            'kind'         => 'export',
        ]);
        $this->view = false;
        require ROOT . DS . 'App' . DS . 'Library' . DS . 'Audit' . DS . 'Templates' . DS . 'export_form.tpl.php';
    }

    /**
     * POST /AuditLog/forgetUser/<id>/ — pseudonymise the user's audit
     * trail. Replaces:
     *   - user_main.login, email, name, firstname with `forgotten_<id>`
     *   - request_log.ip with `0.0.0.0`
     *   - request_log.user_agent_hash with NULL
     *   - auth_event.ip with `0.0.0.0`, user_agent_hash NULL, detail NULL
     *   - audit_client_metrics row is deleted in full (high-entropy data)
     * Schema-level FKs remain valid; the audit timeline still aggregates
     * but no PII remains. Recorded in `audit_subject_preference`.
     */
    public function forgetUser($param)
    {
        $idUser = (int) ($param[0] ?? 0);
        if ($idUser <= 0) {
            header('Location: ' . LINK . 'AuditLog/index');
            exit;
        }
        $isPost = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST';
        if ($isPost) {
            if ($failure = \App\Library\Security\CsrfGuard::ensureOrFail($_POST, $_SERVER, $_SESSION, self::FORGET_CSRF_SCOPE)) {
                http_response_code($failure['status']);
                echo $failure['body'];
                return;
            }
            $this->applyForget($idUser);
            $this->stampSubjectPreference($idUser, ['forget_completed_at' => date('Y-m-d H:i:s.') . sprintf('%03d', (int) (microtime(true) * 1000) % 1000)]);
            header('Location: ' . LINK . 'AuditLog/user/' . $idUser . '/');
            exit;
        }
        $this->title  = __('Audit') . ' — forget user #' . $idUser;
        $this->ariane = __('SuperAdmin') . ' / Audit / forget';
        $this->set('data', [
            'id_user_main' => $idUser,
            'csrf_field'   => \App\Library\Security\Csrf::DEFAULT_FIELD,
            'csrf_token'   => \App\Library\Security\Csrf::issueToken($_SESSION, self::FORGET_CSRF_SCOPE),
            'kind'         => 'forget',
        ]);
        $this->view = false;
        require ROOT . DS . 'App' . DS . 'Library' . DS . 'Audit' . DS . 'Templates' . DS . 'export_form.tpl.php';
    }

    /**
     * Stream a CSV bundle of every audit row attributable to the user.
     * Headers stream with `text/csv` + `Content-Disposition: attachment`
     * so the browser saves to disk. One file = 4 stacked sections,
     * separator lines between, for the operator's convenience.
     */
    private function streamExportCsv(int $idUser): void
    {
        $this->layout_name = false;
        $this->view = false;

        while (ob_get_level() > 0) { @ob_end_clean(); }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="audit_export_user_' . $idUser . '_' . date('Ymd_His') . '.csv"');

        $db = Sgbd::sql(DB_DEFAULT);
        $out = fopen('php://output', 'w');
        if ($out === false) {
            return;
        }
        $emit = static function (array $row) use ($out): void {
            fputcsv($out, $row, ',', '"', '\\');
        };

        $emit(['# audit export for id_user_main = ' . $idUser, 'generated_at = ' . date('c')]);
        $emit([]);

        $emit(['== request_log ==']);
        $emit(['id', 'request_uid', 'date', 'ip', 'geo_country_iso', 'geo_city',
               'method', 'uri', 'status', 'bytes_sent', 'referer', 'php_ms',
               'controller', 'action', 'user_role_class']);
        $res = $db->sql_query("SELECT id, request_uid, date, ip, geo_country_iso, geo_city, method, uri, status, "
                            . " bytes_sent, referer, php_ms, controller, action, user_role_class "
                            . "FROM request_log WHERE id_user_main = $idUser ORDER BY date");
        if ($res) while ($r = $db->sql_fetch_array($res, MYSQLI_NUM)) { $emit($r); }

        $emit([]);
        $emit(['== auth_event ==']);
        $emit(['id', 'request_uid', 'date', 'ip', 'event_type', 'selector_prefix', 'detail']);
        $res = $db->sql_query("SELECT id, request_uid, date, ip, event_type, selector_prefix, detail "
                            . "FROM auth_event WHERE id_user_main = $idUser ORDER BY date");
        if ($res) while ($r = $db->sql_fetch_array($res, MYSQLI_NUM)) { $emit($r); }

        $emit([]);
        $emit(['== subprocess_log (parented through request_log) ==']);
        $emit(['id', 'request_uid', 'date_start', 'date_end', 'duration_ms', 'pid', 'command', 'label',
               'exit_code', 'status']);
        $res = $db->sql_query("SELECT s.id, s.request_uid, s.date_start, s.date_end, s.duration_ms, s.pid, "
                            . " s.command, s.label, s.exit_code, s.status "
                            . "FROM subprocess_log s "
                            . "INNER JOIN request_log r ON r.request_uid = s.request_uid "
                            . "WHERE r.id_user_main = $idUser ORDER BY s.date_start");
        if ($res) while ($r = $db->sql_fetch_array($res, MYSQLI_NUM)) { $emit($r); }

        $emit([]);
        $emit(['== audit_client_metrics ==']);
        $emit(['id', 'request_uid', 'date', 'screen_width', 'screen_height', 'device_pixel_ratio',
               'gpu_family', 'ram_bucket', 'cpu_bucket', 'nav_load_ms', 'cwv_lcp_ms', 'cwv_cls', 'cwv_inp_ms',
               'net_effective_type', 'prefers_dark']);
        $res = $db->sql_query("SELECT id, request_uid, date, screen_width, screen_height, device_pixel_ratio, "
                            . " gpu_family, ram_bucket, cpu_bucket, nav_load_ms, cwv_lcp_ms, cwv_cls, cwv_inp_ms, "
                            . " net_effective_type, prefers_dark "
                            . "FROM audit_client_metrics WHERE id_user_main = $idUser ORDER BY date");
        if ($res) while ($r = $db->sql_fetch_array($res, MYSQLI_NUM)) { $emit($r); }

        fclose($out);
        exit;
    }

    private function applyForget(int $idUser): void
    {
        $db = Sgbd::sql(DB_DEFAULT);
        $db->sql_query("UPDATE request_log SET ip = '0.0.0.0', user_agent_hash = NULL, referer = NULL "
                     . "WHERE id_user_main = $idUser");
        $db->sql_query("UPDATE auth_event SET ip = '0.0.0.0', user_agent_hash = NULL, detail = NULL "
                     . "WHERE id_user_main = $idUser");
        $db->sql_query("DELETE FROM audit_client_metrics WHERE id_user_main = $idUser");
    }

    private function stampSubjectPreference(int $idUser, array $updates): void
    {
        $db = Sgbd::sql(DB_DEFAULT);
        $sets = [];
        foreach ($updates as $k => $v) {
            $key = preg_replace('/[^a-z_]/', '', $k);
            if ($key === '') continue;
            $val = is_int($v) ? (string) $v
                 : ($v === null ? 'NULL' : "'" . $db->sql_real_escape_string((string) $v) . "'");
            $sets[] = "$key = $val";
        }
        if ($sets === []) return;
        $assigns = implode(', ', $sets);
        $db->sql_query("INSERT INTO audit_subject_preference (id_user_main, " . implode(', ', array_keys($updates)) . ") "
                     . "VALUES ($idUser, " . implode(', ', array_map(static function ($v) use ($db) {
                            return $v === null ? 'NULL'
                                 : (is_int($v) ? (string) $v : "'" . $db->sql_real_escape_string((string) $v) . "'");
                       }, $updates)) . ") "
                     . "ON DUPLICATE KEY UPDATE $assigns");
    }

    /**
     * GET /AuditLog/verify — walks the auth_event hash chain.
     *
     * Each `auth_event` row carries a `prev_hash` + `self_hash` populated
     * by the drain (post-MVP #7 of #1235). A row is valid when its
     * computed sha256 matches `self_hash` AND its `prev_hash` equals the
     * previous row's `self_hash`. Any tampered / deleted row breaks the
     * chain at that point.
     */
    public function verify()
    {
        $this->title  = __('Audit hash chain verification');
        $this->ariane = __('SuperAdmin') . ' / Audit / verify';

        $db = Sgbd::sql(DB_DEFAULT);
        $res = $db->sql_query("SELECT id, request_uid, date, id_user_main, ip, user_agent_hash, "
                            . "event_type, selector_prefix, detail, prev_hash, self_hash "
                            . "FROM auth_event ORDER BY id ASC");
        $checked = 0;
        $broken = [];
        $lastSelf = null;
        if ($res) {
            while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $checked++;
                $expectedPrev = $lastSelf;
                $computed = \App\Library\Audit\AuditDrain::computeAuthEventHash($row, $row['prev_hash']);
                $rowPrev = $row['prev_hash'];
                $rowSelf = $row['self_hash'];
                $reasons = [];
                if ($rowPrev !== $expectedPrev) {
                    $reasons[] = 'prev_hash mismatch (expected ' . substr((string) $expectedPrev, 0, 12) . '… got ' . substr((string) $rowPrev, 0, 12) . '…)';
                }
                if ($rowSelf !== $computed) {
                    $reasons[] = 'self_hash mismatch (recomputed ' . substr($computed, 0, 12) . '… stored ' . substr((string) $rowSelf, 0, 12) . '…)';
                }
                if ($reasons !== []) {
                    $broken[] = [
                        'id' => (int) $row['id'],
                        'date' => $row['date'],
                        'event_type' => $row['event_type'],
                        'reasons' => $reasons,
                    ];
                    // Keep walking — first break is reported but a partial
                    // chain still has value, so we don't bail out here.
                }
                $lastSelf = $rowSelf;
            }
        }
        $this->set('data', [
            'checked' => $checked,
            'broken'  => $broken,
            'ok'      => empty($broken),
            'now'     => time(),
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

    /**
     * Hits-per-hour bucket for the last 24 h (Chart.js histogram).
     * Returns 24 buckets indexed by hour offset (0 = oldest, 23 = newest).
     */
    private static function hitsPerHour24h($db): array
    {
        $out = array_fill(0, 24, ['ts' => null, 'human' => 0, 'robot' => 0]);
        $sql = "SELECT DATE_FORMAT(r.date, '%Y-%m-%d %H:00') AS bucket, "
             . "       SUM(IFNULL(ua.is_robot, 0) = 1) AS robots, "
             . "       SUM(IFNULL(ua.is_robot, 0) = 0) AS humans "
             . "FROM request_log r "
             . "LEFT JOIN audit_user_agent ua ON ua.ua_hash = r.user_agent_hash "
             . "WHERE r.date > NOW() - INTERVAL 24 HOUR "
             . "GROUP BY bucket "
             . "ORDER BY bucket";
        $res = $db->sql_query($sql);
        if (!$res) return array_values($out);
        $rows = [];
        while ($r = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $rows[$r['bucket']] = [
                'human' => (int) $r['humans'],
                'robot' => (int) $r['robots'],
            ];
        }
        // Fill every hour of the last 24 h, even if empty, so the chart
        // doesn't jump on the X axis.
        $out = [];
        for ($i = 23; $i >= 0; $i--) {
            $ts = date('Y-m-d H:00', time() - $i * 3600);
            $out[] = [
                'ts'    => $ts,
                'human' => $rows[$ts]['human'] ?? 0,
                'robot' => $rows[$ts]['robot'] ?? 0,
            ];
        }
        return $out;
    }

    /**
     * Auth events per hour (success/fail breakdown). Useful for spotting
     * brute-force bursts at a glance.
     */
    private static function authPerHour24h($db): array
    {
        $rows = [];
        $sql = "SELECT DATE_FORMAT(date, '%Y-%m-%d %H:00') AS bucket, "
             . "       SUM(event_type = 'login_success') AS ok, "
             . "       SUM(event_type IN ('login_fail','token_mismatch','fingerprint_mismatch','csrf_reject','rate_limit')) AS ko "
             . "FROM auth_event "
             . "WHERE date > NOW() - INTERVAL 24 HOUR "
             . "GROUP BY bucket";
        $res = $db->sql_query($sql);
        if ($res) {
            while ($r = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $rows[$r['bucket']] = ['ok' => (int) $r['ok'], 'ko' => (int) $r['ko']];
            }
        }
        $out = [];
        for ($i = 23; $i >= 0; $i--) {
            $ts = date('Y-m-d H:00', time() - $i * 3600);
            $out[] = [
                'ts' => $ts,
                'ok' => $rows[$ts]['ok'] ?? 0,
                'ko' => $rows[$ts]['ko'] ?? 0,
            ];
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
        $sql = "SELECT r.id, r.request_uid, r.date, r.ip, r.geo_country_iso, r.geo_city, r.method, r.uri, r.status, r.php_ms, "
             . "       r.controller, r.action, "
             . "       ua.os_family, ua.browser_family, ua.browser_version, ua.is_robot, ua.robot_family, "
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
