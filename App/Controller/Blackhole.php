<?php
/**
 * BLACKHOLE binlog-relay controller — Issue #1212.
 *
 * Tools → BLACKHOLE menu entry. Lists supervised MySQL servers, lets
 * the operator convert a candidate slave into a BLACKHOLE binlog
 * relay, and streams live progress to the browser.
 *
 * Shares the same core (App\Library\BlackholeRelay) as the CLI runner
 * `php App/Webroot/index.php Blackhole runConvertCli <conversion_id>`.
 *
 * @category PmaControl
 * @package App
 * @subpackage Controller
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use \Glial\Security\Csrf;
use App\Library\Security\CsrfGuard;
use App\Library\BlackholeRelay;

class Blackhole extends Controller
{
    private const START_CSRF_SCOPE      = 'blackhole.convert.start';
    private const GREENFIELD_CSRF_SCOPE = 'blackhole.greenfield.start';

    public function before($param)
    {
    }

    /**
     * GET /Blackhole/index — list candidate servers, current relays
     * and recent conversion runs.
     */
    public function index()
    {
        $this->title  = __('BLACKHOLE binlog relay');
        $this->ariane = __('Tools') . ' / BLACKHOLE';

        $db = Sgbd::sql(DB_DEFAULT);

        $data = [];
        $data['servers']            = BlackholeRelay::listCandidateServers();
        $data['idle_targets']       = BlackholeRelay::listIdleTargets();
        $data['master_candidates']  = BlackholeRelay::listMasterCandidates();
        $data['conversions']        = $this->loadRecentConversions($db, 25);
        $data['csrf_field']         = Csrf::DEFAULT_FIELD;
        $data['csrf_token']         = Csrf::issueToken($_SESSION, self::START_CSRF_SCOPE);
        $data['greenfield_token']   = Csrf::issueToken($_SESSION, self::GREENFIELD_CSRF_SCOPE);

        $this->set('data', $data);
    }

    /**
     * POST /Blackhole/startConvert/<id_mysql_server>/ — AJAX. Creates a
     * `blackhole_conversion` row and kicks off the CLI runner in the
     * background. Returns JSON `{ id, status }`.
     */
    public function startConvert($param)
    {
        $this->bhBeginJsonResponse();

        if ($failure = CsrfGuard::ensureOrFail($_POST, $_SERVER, $_SESSION, self::START_CSRF_SCOPE)) {
            $this->bhSendJson(['error' => $failure['body']], $failure['status']);
            return;
        }

        $serverId = (int) ($param[0] ?? 0);
        $dryRun   = !empty($_POST['dry_run']) ? 1 : 0;

        if ($serverId <= 0) {
            $this->bhSendJson(['error' => 'Invalid id_mysql_server'], 400);
            return;
        }

        $db = Sgbd::sql(DB_DEFAULT);

        $res = $db->sql_query("SELECT is_binlog_relay FROM mysql_server WHERE id = {$serverId} AND is_deleted = 0");
        $srv = $res ? $db->sql_fetch_array($res, MYSQLI_ASSOC) : null;
        if (!$srv) {
            $this->bhSendJson(['error' => 'Server not found'], 404);
            return;
        }

        // Refuse if a conversion is already running for this server.
        $res = $db->sql_query(
            "SELECT id, status FROM blackhole_conversion
             WHERE id_mysql_server = {$serverId} AND status IN ('pending','running')
             ORDER BY id DESC LIMIT 1"
        );
        if ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $this->bhSendJson(['id' => (int) $row['id'], 'status' => $row['status'], 'reused' => true]);
            return;
        }

        $startedBy = $db->sql_real_escape_string((string) ($_SESSION['login'] ?? 'cli'));
        $db->sql_query(
            "INSERT INTO blackhole_conversion
                (id_mysql_server, status, dry_run, started_by, progress, error_message, created_at)
             VALUES
                ({$serverId}, 'pending', {$dryRun}, '{$startedBy}', '[]', '', NOW())"
        );
        $conversionId = (int) $db->sql_insert_id();

        $pid = self::forkConversionRunner($conversionId);
        self::registerJobRow($db, $conversionId, [$conversionId], $pid, $startedBy);

        $this->bhSendJson(['id' => $conversionId, 'status' => 'pending']);
    }

    /**
     * GET /Blackhole/status/<conversion_id>/ — AJAX poll. Returns the
     * row's status, table counts, progress steps and error message.
     */
    public function status($param)
    {
        $this->bhBeginJsonResponse();

        $conversionId = (int) ($param[0] ?? 0);
        if ($conversionId <= 0) {
            $this->bhSendJson(['error' => 'Invalid id'], 400);
            return;
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $res = $db->sql_query(
            "SELECT bc.*, ms.display_name, ms.name AS server_name
             FROM blackhole_conversion bc
             JOIN mysql_server ms ON ms.id = bc.id_mysql_server
             WHERE bc.id = {$conversionId}"
        );
        $row = $res ? $db->sql_fetch_array($res, MYSQLI_ASSOC) : null;
        if (!$row) {
            $this->bhSendJson(['error' => 'Conversion not found'], 404);
            return;
        }

        $this->bhSendJson([
            'id'                      => (int) $row['id'],
            'id_mysql_server'         => (int) $row['id_mysql_server'],
            'id_mysql_server__master' => (int) ($row['id_mysql_server__master'] ?? 0),
            'server_name'             => $row['display_name'] ?: $row['server_name'],
            'status'                  => $row['status'],
            'provision_mode'          => (string) ($row['provision_mode'] ?? 'convert'),
            'dry_run'                 => (int) $row['dry_run'] === 1,
            'tables_total'            => (int) $row['tables_total'],
            'tables_converted'        => (int) $row['tables_converted'],
            'progress'                => json_decode($row['progress'] ?: '[]', true),
            'error_message'           => $row['error_message'],
            'created_at'              => $row['created_at'],
            'completed_at'            => $row['completed_at'],
        ]);
    }

    /**
     * POST /Blackhole/startGreenfield/<id_target>/<id_master>/ — AJAX.
     * Creates a greenfield `blackhole_conversion` row and kicks off
     * the background CLI runner. Returns JSON `{ id, status }`.
     */
    public function startGreenfield($param)
    {
        $this->bhBeginJsonResponse();

        if ($failure = CsrfGuard::ensureOrFail($_POST, $_SERVER, $_SESSION, self::GREENFIELD_CSRF_SCOPE)) {
            $this->bhSendJson(['error' => $failure['body']], $failure['status']);
            return;
        }

        $targetId = (int) ($param[0] ?? ($_POST['target_id'] ?? 0));
        $masterId = (int) ($param[1] ?? ($_POST['master_id'] ?? 0));
        $dryRun   = !empty($_POST['dry_run']) ? 1 : 0;
        $replUser = trim((string) ($_POST['replication_user'] ?? ''));

        if ($targetId <= 0 || $masterId <= 0) {
            $this->bhSendJson(['error' => 'Both target_id and master_id are required'], 400);
            return;
        }
        if ($targetId === $masterId) {
            $this->bhSendJson(['error' => 'Target and master must be different servers'], 400);
            return;
        }

        $db = Sgbd::sql(DB_DEFAULT);

        $res = $db->sql_query(
            "SELECT id, status FROM blackhole_conversion
             WHERE id_mysql_server = {$targetId} AND status IN ('pending','running')
             ORDER BY id DESC LIMIT 1"
        );
        if ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $this->bhSendJson(['id' => (int) $row['id'], 'status' => $row['status'], 'reused' => true]);
            return;
        }

        $startedBy = $db->sql_real_escape_string((string) ($_SESSION['login'] ?? 'cli'));
        $replEsc   = $db->sql_real_escape_string($replUser);
        $db->sql_query(
            "INSERT INTO blackhole_conversion
                (id_mysql_server, id_mysql_server__master, replication_user,
                 status, dry_run, provision_mode, started_by, progress, error_message, created_at)
             VALUES
                ({$targetId}, {$masterId}, '{$replEsc}',
                 'pending', {$dryRun}, 'greenfield', '{$startedBy}', '[]', '', NOW())"
        );
        $conversionId = (int) $db->sql_insert_id();

        $pid = self::forkConversionRunner($conversionId);
        self::registerJobRow($db, $conversionId, [$conversionId], $pid, $startedBy);

        $this->bhSendJson(['id' => $conversionId, 'status' => 'pending']);
    }

    /**
     * GET /Blackhole/probeIdle/<id_mysql_server>/ — AJAX. Returns
     * `{ idle: true }` or `{ idle: false, reason: "..." }`. Used by the
     * greenfield UI to warn before committing.
     */
    public function probeIdle($param)
    {
        $this->bhBeginJsonResponse();

        $serverId = (int) ($param[0] ?? 0);
        if ($serverId <= 0) {
            $this->bhSendJson(['error' => 'Invalid id'], 400);
            return;
        }
        $res = BlackholeRelay::probeIdle($serverId);
        if ($res === true) {
            $this->bhSendJson(['idle' => true]);
        } else {
            $this->bhSendJson(['idle' => false, 'reason' => (string) $res]);
        }
    }

    /**
     * CLI: `php App/Webroot/index.php Blackhole runConvertCli <id>`.
     * Loaded by the background fork from startConvert() and also
     * usable standalone from a shell.
     */
    public function runConvertCli($param)
    {
        $this->layout_name = false;
        $this->view = false;

        $conversionId = (int) ($param[0] ?? 0);
        if ($conversionId <= 0) {
            fwrite(STDERR, "usage: runConvertCli <conversion_id>\n");
            return;
        }

        // The fork from startConvert/startGreenfield wrote a `job` row
        // pointing at our pid+log so /job/index can surface us. When
        // restarted from /job/index, no row exists yet for this pid —
        // create one so the second run is also visible.
        self::ensureJobRowForCurrentRun($conversionId);

        $relay = new BlackholeRelay($conversionId);
        $ok = $relay->run();

        self::finalizeJobRowForCurrentRun($conversionId, $ok);

        if (PHP_SAPI === 'cli') {
            echo $ok ? "OK\n" : "FAILED\n";
        }
    }

    // ------------------------------------------------------------------
    //  Job framework integration (#1212 — relauncher + /job/index log)
    // ------------------------------------------------------------------

    /**
     * Background-fork the runner, capturing the pid via `echo $!`
     * (same trick as Slave::runInBackground). Returns 0 if no pid
     * could be parsed — the conversion still ran, we just couldn't
     * register a job row.
     */
    private static function forkConversionRunner(int $conversionId): int
    {
        $logPath = self::logPathForConversion($conversionId);
        $cmd = 'cd ' . escapeshellarg(ROOT)
             . ' && nohup php App/Webroot/index.php Blackhole runConvertCli '
             . (int) $conversionId
             . ' > ' . escapeshellarg($logPath) . ' 2>&1 & echo $!';
        return (int) trim((string) shell_exec($cmd));
    }

    /**
     * Insert a `job` row so the run shows up on /job/index. We mirror
     * Job::add()'s shape: `class` is the FQCN the framework expects so
     * the restart path resolves it via normalizeControllerName().
     */
    private static function registerJobRow($db, int $conversionId, array $params, int $pid, string $startedBy): void
    {
        if ($pid <= 0) return; // can't track a pid we didn't get
        $logPath = self::logPathForConversion($conversionId);
        $uuid = bin2hex(random_bytes(16));
        $uuid = substr($uuid, 0, 8) . '-' . substr($uuid, 8, 4) . '-' . substr($uuid, 12, 4)
              . '-' . substr($uuid, 16, 4) . '-' . substr($uuid, 20, 12);
        $paramJson = $db->sql_real_escape_string(json_encode($params));
        $logEsc    = $db->sql_real_escape_string($logPath);
        $uuidEsc   = $db->sql_real_escape_string($uuid);
        $db->sql_query(
            "INSERT INTO job (uuid, class, method, param, date_start, pid, log, error, status)
             VALUES ('{$uuidEsc}', 'App\\\\Controller\\\\Blackhole', 'runConvertCli',
                     '{$paramJson}', NOW(), {$pid}, '{$logEsc}', '', 'RUNNING')"
        );
    }

    /**
     * Called from runConvertCli() at startup. If the runner was
     * launched via /job/index restart (i.e. without going through
     * startConvert / startGreenfield), no `job` row exists yet for
     * the current pid — register one so /job/index sees this restart.
     */
    private static function ensureJobRowForCurrentRun(int $conversionId): void
    {
        $pid = (int) getmypid();
        if ($pid <= 0) return;
        $db = Sgbd::sql(DB_DEFAULT);
        $res = $db->sql_query("SELECT id FROM job WHERE pid = {$pid} AND status = 'RUNNING' LIMIT 1");
        if ($res && $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            return; // already registered (the parent fork inserted it)
        }
        self::registerJobRow($db, $conversionId, [$conversionId], $pid, 'cli-restart');
    }

    /**
     * Mark the matching `job` row as SUCCESS / ERROR + date_end so
     * /job/index stops reporting "RUNNING" once we exit.
     */
    private static function finalizeJobRowForCurrentRun(int $conversionId, bool $ok): void
    {
        $pid = (int) getmypid();
        if ($pid <= 0) return;
        $db = Sgbd::sql(DB_DEFAULT);
        $status = $ok ? 'SUCCESS' : 'ERROR';
        $db->sql_query(
            "UPDATE job
             SET status = '{$status}', date_end = NOW()
             WHERE pid = {$pid} AND status = 'RUNNING'"
        );
    }

    private static function logPathForConversion(int $conversionId): string
    {
        return ROOT . '/tmp/log/blackhole_conversion_' . $conversionId . '.log';
    }

    // ------------------------------------------------------------------
    //  Lookups
    // ------------------------------------------------------------------

    /**
     * Belt-and-braces JSON-response wrapper. Discards every existing
     * output buffer Glial set up for us and starts a fresh one so any
     * stray notice / warning / debug echo emitted by Sgbd, Extraction,
     * or anything else downstream is swallowed before our JSON body
     * lands on the wire. Pairs with `bhSendJson()` which `ob_clean`s
     * one last time and `exit;`s.
     *
     * Two earlier bugs taught us this is non-negotiable for AJAX
     * endpoints in Glial:
     *   - #1219: a trailing `<div id="glial-debug-footer">…` from
     *     Bootstrap.php corrupted the JSON unless the URL ended in
     *     `/ajax:true/`.
     *   - #1222: a leading `<br /> <font…>` from a PHP warning
     *     surfaced as `Unexpected token '<', "<br /> <fo"...` on the
     *     plugin Install button. The fix is `display_errors=0`
     *     server-side + `ob_start/ob_clean` to discard whatever did
     *     leak through.
     */
    private function bhBeginJsonResponse(): void
    {
        $this->layout_name = false;
        $this->view = false;
        while (ob_get_level() > 0) { @ob_end_clean(); }
        ob_start();
        @ini_set('display_errors', '0');
        header('Content-Type: application/json; charset=UTF-8');
        // Shutdown handler: if a fatal kills the action between here
        // and bhSendJson(), emit a deterministic JSON error so the
        // client sees something actionable instead of "Unexpected
        // end of JSON input" with zero hint.
        register_shutdown_function(static function () {
            $err = error_get_last();
            if (!$err) return;
            if (!in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) return;
            if (headers_sent()) {
                while (ob_get_level() > 0) { @ob_end_clean(); }
                echo json_encode([
                    'error' => 'Server-side fatal during JSON response — see Apache error_log',
                    'fatal' => $err['message'],
                    'at'    => basename((string) $err['file']) . ':' . $err['line'],
                ]);
            }
        });
    }

    private function bhSendJson(array $payload, int $status = 200): void
    {
        if ($status !== 200) {
            http_response_code($status);
        }
        while (ob_get_level() > 0) { @ob_end_clean(); }
        echo json_encode($payload);
        exit;
    }

    private function loadRecentConversions($db, int $limit): array
    {
        $sql = "SELECT bc.id, bc.id_mysql_server, bc.status, bc.dry_run,
                       bc.tables_total, bc.tables_converted,
                       bc.error_message, bc.created_at, bc.completed_at,
                       ms.display_name, ms.name AS server_name
                FROM blackhole_conversion bc
                LEFT JOIN mysql_server ms ON ms.id = bc.id_mysql_server
                ORDER BY bc.id DESC
                LIMIT " . (int) $limit;
        $res = $db->sql_query($sql);
        $out = [];
        while ($r = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $out[] = $r;
        }
        return $out;
    }
}
