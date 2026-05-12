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
        $this->layout_name = false;
        $this->view = false;
        header('Content-Type: application/json; charset=UTF-8');

        if ($failure = CsrfGuard::ensureOrFail($_POST, $_SERVER, $_SESSION, self::START_CSRF_SCOPE)) {
            http_response_code($failure['status']);
            echo json_encode(['error' => $failure['body']]);
            return;
        }

        $serverId = (int) ($param[0] ?? 0);
        $dryRun   = !empty($_POST['dry_run']) ? 1 : 0;

        if ($serverId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid id_mysql_server']);
            return;
        }

        $db = Sgbd::sql(DB_DEFAULT);

        // Guard: refuse if the server is already a relay (idempotency
        // is handled by the conversion script itself; this check just
        // saves a roundtrip).
        $res = $db->sql_query("SELECT is_binlog_relay FROM mysql_server WHERE id = {$serverId} AND is_deleted = 0");
        $srv = $res ? $db->sql_fetch_array($res, MYSQLI_ASSOC) : null;
        if (!$srv) {
            http_response_code(404);
            echo json_encode(['error' => 'Server not found']);
            return;
        }

        // Refuse if a conversion is already running for this server.
        $res = $db->sql_query(
            "SELECT id, status FROM blackhole_conversion
             WHERE id_mysql_server = {$serverId} AND status IN ('pending','running')
             ORDER BY id DESC LIMIT 1"
        );
        if ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            echo json_encode(['id' => (int) $row['id'], 'status' => $row['status'], 'reused' => true]);
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

        // Background runner: same pattern as Slave::startBinlogAnalysis.
        $logPath = ROOT . '/tmp/blackhole_conversion_' . $conversionId . '.log';
        $cmd = 'cd ' . escapeshellarg(ROOT)
             . ' && php App/Webroot/index.php Blackhole runConvertCli '
             . (int) $conversionId
             . ' > ' . escapeshellarg($logPath) . ' 2>&1 &';
        exec($cmd);

        echo json_encode(['id' => $conversionId, 'status' => 'pending']);
    }

    /**
     * GET /Blackhole/status/<conversion_id>/ — AJAX poll. Returns the
     * row's status, table counts, progress steps and error message.
     */
    public function status($param)
    {
        $this->layout_name = false;
        $this->view = false;
        header('Content-Type: application/json; charset=UTF-8');

        $conversionId = (int) ($param[0] ?? 0);
        if ($conversionId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid id']);
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
            http_response_code(404);
            echo json_encode(['error' => 'Conversion not found']);
            return;
        }

        echo json_encode([
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
        $this->layout_name = false;
        $this->view = false;
        header('Content-Type: application/json; charset=UTF-8');

        if ($failure = CsrfGuard::ensureOrFail($_POST, $_SERVER, $_SESSION, self::GREENFIELD_CSRF_SCOPE)) {
            http_response_code($failure['status']);
            echo json_encode(['error' => $failure['body']]);
            return;
        }

        $targetId = (int) ($param[0] ?? ($_POST['target_id'] ?? 0));
        $masterId = (int) ($param[1] ?? ($_POST['master_id'] ?? 0));
        $dryRun   = !empty($_POST['dry_run']) ? 1 : 0;
        $replUser = trim((string) ($_POST['replication_user'] ?? ''));

        if ($targetId <= 0 || $masterId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Both target_id and master_id are required']);
            return;
        }
        if ($targetId === $masterId) {
            http_response_code(400);
            echo json_encode(['error' => 'Target and master must be different servers']);
            return;
        }

        $db = Sgbd::sql(DB_DEFAULT);

        // Refuse on duplicate-in-flight (any conversion for this target).
        $res = $db->sql_query(
            "SELECT id, status FROM blackhole_conversion
             WHERE id_mysql_server = {$targetId} AND status IN ('pending','running')
             ORDER BY id DESC LIMIT 1"
        );
        if ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            echo json_encode(['id' => (int) $row['id'], 'status' => $row['status'], 'reused' => true]);
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

        $logPath = ROOT . '/tmp/blackhole_conversion_' . $conversionId . '.log';
        $cmd = 'cd ' . escapeshellarg(ROOT)
             . ' && php App/Webroot/index.php Blackhole runConvertCli '
             . (int) $conversionId
             . ' > ' . escapeshellarg($logPath) . ' 2>&1 &';
        exec($cmd);

        echo json_encode(['id' => $conversionId, 'status' => 'pending']);
    }

    /**
     * GET /Blackhole/probeIdle/<id_mysql_server>/ — AJAX. Returns
     * `{ idle: true }` or `{ idle: false, reason: "..." }`. Used by the
     * greenfield UI to warn before committing.
     */
    public function probeIdle($param)
    {
        $this->layout_name = false;
        $this->view = false;
        header('Content-Type: application/json; charset=UTF-8');

        $serverId = (int) ($param[0] ?? 0);
        if ($serverId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid id']);
            return;
        }
        $res = BlackholeRelay::probeIdle($serverId);
        if ($res === true) {
            echo json_encode(['idle' => true]);
        } else {
            echo json_encode(['idle' => false, 'reason' => (string) $res]);
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

        $relay = new BlackholeRelay($conversionId);
        $ok = $relay->run();

        if (PHP_SAPI === 'cli') {
            echo $ok ? "OK\n" : "FAILED\n";
        }
    }

    // ------------------------------------------------------------------
    //  Lookups
    // ------------------------------------------------------------------

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
