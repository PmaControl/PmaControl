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
    private const START_CSRF_SCOPE       = 'blackhole.convert.start';
    private const GREENFIELD_CSRF_SCOPE  = 'blackhole.greenfield.start';
    private const SWEEP_CSRF_SCOPE       = 'blackhole.sweep';
    private const KILL_CSRF_SCOPE        = 'blackhole.kill';
    private const MARK_FAILED_CSRF_SCOPE = 'blackhole.mark_failed';

    /**
     * SIGTERM grace window before escalating to SIGKILL (in seconds).
     * Conversions are I/O-bound on ALTER statements, so we give the
     * runner a few seconds to unwind its finally-block (restore
     * sql_log_bin=1) before forcing it down.
     */
    private const KILL_GRACE_SECONDS = 3;

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
        $data['sweep_token']        = Csrf::issueToken($_SESSION, self::SWEEP_CSRF_SCOPE);
        $data['kill_token']         = Csrf::issueToken($_SESSION, self::KILL_CSRF_SCOPE);
        $data['mark_failed_token']  = Csrf::issueToken($_SESSION, self::MARK_FAILED_CSRF_SCOPE);
        $data['default_parallelism'] = self::defaultParallelism();

        $this->set('data', $data);
    }

    /**
     * POST /Blackhole/sweepEngines/<id_mysql_server>/ — AJAX. ALTERs every
     * non-BLACKHOLE base table on the relay back to BLACKHOLE. Required
     * after the master replicates a DDL with an explicit ENGINE=InnoDB
     * (or similar): the slave SQL thread applies it verbatim because the
     * master-side sql_mode (NO_ENGINE_SUBSTITUTION) is binlogged per
     * event, so neither `default_storage_engine` nor `enforce_storage_engine`
     * on the relay can override it at apply time. The sweep is the
     * one-shot recovery — it drops FKs first (1217 guard) then runs the
     * ALTERs under sql_log_bin=0 so downstream consumers don't see the
     * conversion churn. Returns JSON `{ converted, skipped, errors }`.
     */
    public function sweepEngines($param)
    {
        $this->bhBeginJsonResponse();

        if ($failure = CsrfGuard::ensureOrFail($_POST, $_SERVER, $_SESSION, self::SWEEP_CSRF_SCOPE)) {
            $this->bhSendJson(['error' => $failure['body']], $failure['status']);
            return;
        }

        $serverId = (int) ($param[0] ?? 0);
        if ($serverId <= 0) {
            $this->bhSendJson(['error' => 'Invalid id_mysql_server'], 400);
            return;
        }

        $result = BlackholeRelay::sweepRelay($serverId);
        $this->bhSendJson($result + ['ok' => empty($result['errors'])]);
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
        $parallelism = self::clampParallelism((int) ($_POST['parallelism'] ?? self::defaultParallelism()));
        $db->sql_query(
            "INSERT INTO blackhole_conversion
                (id_mysql_server, status, dry_run, started_by, parallelism, progress, error_message, created_at)
             VALUES
                ({$serverId}, 'pending', {$dryRun}, '{$startedBy}', {$parallelism}, '[]', '', NOW())"
        );
        $conversionId = (int) $db->sql_insert_id();

        $pid = self::forkConversionRunner($conversionId);
        self::persistRunnerPid($db, $conversionId, $pid);
        self::registerJobRow($db, $conversionId, [$conversionId], $pid, $startedBy);

        $this->bhSendJson(['id' => $conversionId, 'status' => 'pending', 'pid' => $pid, 'parallelism' => $parallelism]);
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
        $replPass = (string) ($_POST['replication_password'] ?? '');

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
        // Encrypt the replication password the same way mysql_server.passwd
        // is stored, so it never lands in the DB or progress JSON plaintext.
        // Empty input → empty column → BlackholeRelay falls back to the
        // master's stored password (legacy behavior).
        $replPassStored = '';
        if ($replPass !== '') {
            $replPassStored = \Glial\Security\Crypt\Crypt::encrypt($replPass);
        }
        $replPassEsc = $db->sql_real_escape_string($replPassStored);
        $parallelism = self::clampParallelism((int) ($_POST['parallelism'] ?? self::defaultParallelism()));
        $db->sql_query(
            "INSERT INTO blackhole_conversion
                (id_mysql_server, id_mysql_server__master, replication_user, replication_password,
                 status, dry_run, provision_mode, started_by, parallelism, progress, error_message, created_at)
             VALUES
                ({$targetId}, {$masterId}, '{$replEsc}', '{$replPassEsc}',
                 'pending', {$dryRun}, 'greenfield', '{$startedBy}', {$parallelism}, '[]', '', NOW())"
        );
        $conversionId = (int) $db->sql_insert_id();

        $pid = self::forkConversionRunner($conversionId);
        self::persistRunnerPid($db, $conversionId, $pid);
        self::registerJobRow($db, $conversionId, [$conversionId], $pid, $startedBy);

        $this->bhSendJson(['id' => $conversionId, 'status' => 'pending', 'pid' => $pid, 'parallelism' => $parallelism]);
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
     * CLI: `php App/Webroot/index.php Blackhole runConvertWorker <conv_id> <worker_idx> <total>`.
     * Spawned by BlackholeRelay::convertTablesParallel(). Reads the
     * shared tables list, processes its round-robin slice, exits.
     * Not exposed over HTTP — it skips CSRF and writes directly to
     * blackhole_conversion rows.
     */
    public function runConvertWorker($param)
    {
        $this->layout_name = false;
        $this->view = false;

        // CLI-only: it bumps tables_converted unauthenticated. The
        // parent dispatch always invokes us through `php
        // App/Webroot/index.php`, so PHP_SAPI is 'cli' for any real
        // caller. Over HTTP we 404 to keep the surface minimal.
        if (PHP_SAPI !== 'cli') {
            http_response_code(404);
            return;
        }

        $conversionId = (int) ($param[0] ?? 0);
        $workerIdx    = (int) ($param[1] ?? 0);
        $totalWorkers = (int) ($param[2] ?? 1);

        if ($conversionId <= 0 || $totalWorkers <= 0 || $workerIdx < 0 || $workerIdx >= $totalWorkers) {
            fwrite(STDERR, "usage: runConvertWorker <conversion_id> <worker_idx> <total_workers>\n");
            return;
        }

        $relay = new BlackholeRelay($conversionId);
        $n = $relay->runWorkerSlice($workerIdx, $totalWorkers);

        if (PHP_SAPI === 'cli') {
            echo "worker {$workerIdx}/{$totalWorkers} converted={$n}\n";
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

        // Mirror our pid onto the conversion row too. The fork stamped
        // it from the parent webserver process; if we got here via a
        // /job/index restart the column would otherwise stay stale and
        // the liveness check on /Blackhole/index would lie.
        self::persistRunnerPid(Sgbd::sql(DB_DEFAULT), $conversionId, (int) getmypid());

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
                       bc.tables_total, bc.tables_converted, bc.tables_blackhole_after,
                       bc.parallelism, bc.pid, bc.pid_started_at,
                       bc.error_message, bc.created_at, bc.completed_at,
                       ms.display_name, ms.name AS server_name
                FROM blackhole_conversion bc
                LEFT JOIN mysql_server ms ON ms.id = bc.id_mysql_server
                ORDER BY bc.id DESC
                LIMIT " . (int) $limit;
        $res = $db->sql_query($sql);
        $out = [];
        while ($r = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $r['liveness'] = self::pidLiveness((int) ($r['pid'] ?? 0), (int) $r['id']);
            $out[] = $r;
        }
        return $out;
    }

    // ------------------------------------------------------------------
    //  PID liveness + Kill / Mark-failed (Issue #1250)
    // ------------------------------------------------------------------

    /**
     * Default worker count for the parallel ALTER loop. Reads `nproc`
     * once per request; clamps to [1,32]. Operators can override by
     * passing `parallelism` in the start POST.
     */
    private static function defaultParallelism(): int
    {
        static $cached = null;
        if ($cached !== null) return $cached;
        $n = (int) @shell_exec('nproc 2>/dev/null');
        if ($n <= 0) {
            $n = (int) @ini_get('precision') > 0 ? 1 : 1; // fallback when shell_exec is disabled
        }
        return $cached = self::clampParallelism($n);
    }

    private static function clampParallelism(int $n): int
    {
        if ($n < 1)  return 1;
        if ($n > 32) return 32;
        return $n;
    }

    /**
     * Stamp the conversion row with the runner pid (and wall-clock
     * start). Idempotent: running it from both the forking webserver
     * and the runner itself just overwrites with the same value.
     */
    private static function persistRunnerPid($db, int $conversionId, int $pid): void
    {
        if ($pid <= 0) return;
        $db->sql_query(
            "UPDATE blackhole_conversion
             SET pid = {$pid}, pid_started_at = NOW()
             WHERE id = " . (int) $conversionId
        );
    }

    /**
     * Returns a structured liveness verdict for `(pid, conversionId)`:
     *   ['state' => 'alive'|'dead'|'mismatch'|'unknown',
     *    'pid'   => int,
     *    'cmdline' => string|null,
     *    'matches' => bool]
     *
     * `state = 'mismatch'` means the pid is alive but `/proc/<pid>/cmdline`
     * doesn't carry our `Blackhole runConvertCli <id>` marker — Linux
     * recycled the pid. Treated like 'dead' for status purposes, but
     * Kill refuses (we'd murder an unrelated process).
     */
    private static function pidLiveness(int $pid, int $conversionId): array
    {
        if ($pid <= 0) {
            return ['state' => 'unknown', 'pid' => 0, 'cmdline' => null, 'matches' => false];
        }
        $cmdline = self::readProcCmdline($pid);
        if ($cmdline === null) {
            return ['state' => 'dead', 'pid' => $pid, 'cmdline' => null, 'matches' => false];
        }
        $matches = self::cmdlineMatchesRunner($cmdline, $conversionId);
        if (!$matches) {
            return ['state' => 'mismatch', 'pid' => $pid, 'cmdline' => $cmdline, 'matches' => false];
        }
        return ['state' => 'alive', 'pid' => $pid, 'cmdline' => $cmdline, 'matches' => true];
    }

    private static function readProcCmdline(int $pid): ?string
    {
        $path = '/proc/' . (int) $pid . '/cmdline';
        if (!@is_readable($path)) return null;
        $raw = @file_get_contents($path);
        if ($raw === false || $raw === '') return null;
        // /proc cmdline arg separator is NUL; flatten for grep/display.
        return trim(str_replace("\0", ' ', $raw));
    }

    private static function cmdlineMatchesRunner(string $cmdline, int $conversionId): bool
    {
        // Two acceptable shapes:
        //   php App/Webroot/index.php Blackhole runConvertCli <id>
        //   php-fpm: idle  (only when restarted via /job/index — handled by job class match)
        // The first is the canonical fork. Be strict about the id so a
        // recycled pid running a *different* Blackhole conversion can
        // still be detected.
        if (strpos($cmdline, 'Blackhole') === false) return false;
        if (strpos($cmdline, 'runConvertCli') === false) return false;
        // Require the conversion id as a standalone token at end of line
        // or followed by whitespace, never as a substring of a longer
        // number (id=12 vs id=120).
        return (bool) preg_match('/\bBlackhole\s+runConvertCli\s+' . (int) $conversionId . '\b/', $cmdline);
    }

    /**
     * POST /Blackhole/kill/<conversion_id>/ — AJAX. Send SIGTERM, wait
     * KILL_GRACE_SECONDS, escalate to SIGKILL if still alive. Refuses
     * when the cmdline check doesn't match (recycled pid guard).
     * Returns JSON `{ ok: true, escalated: bool }` on success.
     */
    public function kill($param)
    {
        $this->bhBeginJsonResponse();

        if ($failure = CsrfGuard::ensureOrFail($_POST, $_SERVER, $_SESSION, self::KILL_CSRF_SCOPE)) {
            $this->bhSendJson(['error' => $failure['body']], $failure['status']);
            return;
        }

        $conversionId = (int) ($param[0] ?? 0);
        if ($conversionId <= 0) {
            $this->bhSendJson(['error' => 'Invalid id'], 400);
            return;
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $res = $db->sql_query("SELECT id, status, pid FROM blackhole_conversion WHERE id = {$conversionId}");
        $row = $res ? $db->sql_fetch_array($res, MYSQLI_ASSOC) : null;
        if (!$row) {
            $this->bhSendJson(['error' => 'Conversion not found'], 404);
            return;
        }
        if (!in_array($row['status'], ['pending', 'running'], true)) {
            $this->bhSendJson(['error' => 'Conversion is not running (status=' . $row['status'] . ')'], 409);
            return;
        }

        $pid = (int) $row['pid'];
        $liveness = self::pidLiveness($pid, $conversionId);
        if ($liveness['state'] !== 'alive') {
            $this->bhSendJson([
                'error'    => 'Refusing to kill — cmdline check failed (state=' . $liveness['state'] . ')',
                'liveness' => $liveness,
            ], 409);
            return;
        }

        if (!function_exists('posix_kill')) {
            $this->bhSendJson(['error' => 'posix extension unavailable on this host'], 500);
            return;
        }

        @posix_kill($pid, defined('SIGTERM') ? SIGTERM : 15);
        $escalated = false;
        $deadline = time() + self::KILL_GRACE_SECONDS;
        while (time() < $deadline) {
            usleep(200000); // 200 ms
            if (self::readProcCmdline($pid) === null) break;
        }
        if (self::readProcCmdline($pid) !== null) {
            @posix_kill($pid, defined('SIGKILL') ? SIGKILL : 9);
            $escalated = true;
            // brief settle so the next status poll sees the dead pid
            usleep(300000);
        }

        // Flip the conversion row so the UI stops showing "running"
        // forever. The runner's finally() may have restored sql_log_bin
        // already, but we can't tell from here — operator should
        // re-run a Force-BLACKHOLE sweep before resuming traffic.
        $db->sql_query(
            "UPDATE blackhole_conversion
             SET status = 'failed',
                 completed_at = NOW(),
                 error_message = " . ($escalated
                    ? "CONCAT('killed (SIGKILL) by ', '" . $db->sql_real_escape_string((string) ($_SESSION['login'] ?? '?')) . "')"
                    : "CONCAT('killed (SIGTERM) by ', '" . $db->sql_real_escape_string((string) ($_SESSION['login'] ?? '?')) . "')") . "
             WHERE id = " . (int) $conversionId
        );
        // Match the parallel job row too so /job/index agrees.
        $db->sql_query(
            "UPDATE job SET status = 'ERROR', date_end = NOW()
             WHERE pid = {$pid} AND status = 'RUNNING'"
        );

        $this->bhSendJson(['ok' => true, 'escalated' => $escalated, 'liveness' => $liveness]);
    }

    /**
     * POST /Blackhole/markFailed/<conversion_id>/ — AJAX. Cleanup for
     * an orphaned 'running' row whose pid is gone (or whose cmdline
     * doesn't match). Refuses if the runner is still actually alive
     * and matches — the operator must Kill it first.
     */
    public function markFailed($param)
    {
        $this->bhBeginJsonResponse();

        if ($failure = CsrfGuard::ensureOrFail($_POST, $_SERVER, $_SESSION, self::MARK_FAILED_CSRF_SCOPE)) {
            $this->bhSendJson(['error' => $failure['body']], $failure['status']);
            return;
        }

        $conversionId = (int) ($param[0] ?? 0);
        if ($conversionId <= 0) {
            $this->bhSendJson(['error' => 'Invalid id'], 400);
            return;
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $res = $db->sql_query("SELECT id, status, pid FROM blackhole_conversion WHERE id = {$conversionId}");
        $row = $res ? $db->sql_fetch_array($res, MYSQLI_ASSOC) : null;
        if (!$row) {
            $this->bhSendJson(['error' => 'Conversion not found'], 404);
            return;
        }
        if (!in_array($row['status'], ['pending', 'running'], true)) {
            $this->bhSendJson(['error' => 'Conversion already terminal (status=' . $row['status'] . ')'], 409);
            return;
        }

        $pid = (int) $row['pid'];
        $liveness = self::pidLiveness($pid, $conversionId);
        if ($liveness['state'] === 'alive') {
            $this->bhSendJson([
                'error'    => 'Runner is still alive — Kill it first instead of marking failed',
                'liveness' => $liveness,
            ], 409);
            return;
        }

        $reason = $db->sql_real_escape_string('runner died: ' . $liveness['state']
            . ($liveness['pid'] ? ' (pid ' . $liveness['pid'] . ')' : '')
            . ' — marked failed by ' . (string) ($_SESSION['login'] ?? '?'));
        $db->sql_query(
            "UPDATE blackhole_conversion
             SET status = 'failed', completed_at = NOW(), error_message = '{$reason}'
             WHERE id = " . (int) $conversionId
        );
        if ($pid > 0) {
            $db->sql_query("UPDATE job SET status = 'ERROR', date_end = NOW() WHERE pid = {$pid} AND status = 'RUNNING'");
        }

        $this->bhSendJson(['ok' => true, 'liveness' => $liveness]);
    }
}
