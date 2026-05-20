<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use App\Library\Database\RefreshArtifact;
use App\Library\Http\HttpResponse;
use App\Library\Mydumper;
use App\Library\Security\CsrfGuard;
use App\Library\Security\PositiveIntegerSelection;
use App\Library\ShellCommand;
use App\Library\System;
use App\Library\Debug;
use Glial\Security\Csrf;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;
use \Glial\Sgbd\Sgbd;


/**
 * Class responsible for job workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Job extends Controller {

    public const JOB_RESTART_CSRF_SCOPE = 'job.restart';
    private const RELAUNCHABLE_COMMANDS = [
        'Backup' => ['runBackup'],
        'Database' => ['addRefresh'],
        // #1212 — BLACKHOLE relay conversion (Pipeline A or B); restart
        // re-runs the same conversion_id and is idempotent (BLACKHOLE
        // → BLACKHOLE ALTERs are no-ops, the SHOW VARIABLES guarantee
        // re-asserts sql_log_bin = OFF on every iteration).
        'Blackhole' => ['runConvertCli'],
    ];

/**
 * Render job state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/job/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    /**
     * AJAX poll endpoint used by App/Webroot/js/Job/index.js to drive
     * the percent-bar updates on long-running reload jobs.
     *
     * URL: /Job/progress/<job_id>/ajax:true/
     * Output: {"id":N,"status":"RUNNING","progress_percent":42,"dump_status":"stream_backup"}
     *
     * Wrapped in try/catch so any failure produces an HTTP 500 JSON
     * payload rather than a debug-footer that would corrupt the JS
     * parser.
     */
    public function progress($param) {
        $this->layout_name = false;
        $this->view = false;
        header('Content-Type: application/json');
        try {
            $id = (int) ($param[0] ?? 0);
            $db = Sgbd::sql(DB_DEFAULT);
            $sql = "SELECT id, status, progress_percent, dump_status, load_status, date_end
                      FROM `job` WHERE id = ".$id." LIMIT 1";
            $res = $db->sql_query($sql);
            $out = ['id' => $id, 'status' => null, 'progress_percent' => null];
            while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $out = [
                    'id'               => (int) $row['id'],
                    'status'           => (string) $row['status'],
                    'progress_percent' => $row['progress_percent'] === null ? null : (int) $row['progress_percent'],
                    'dump_status'      => $row['dump_status'],
                    'load_status'      => $row['load_status'],
                    'date_end'         => $row['date_end'],
                ];
            }
            echo json_encode($out);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function index() {
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * from `job` ORDER BY date_start DESC LIMIT 20;";
        $res = $db->sql_query($sql);

        $converter = new AnsiToHtmlConverter();

        $data['jobs'] = array();
        while ($ob = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

            if (!System::isRunningPid($ob['pid']) && $ob['status'] === "RUNNING") {
                $gg = array();
                $gg['job']['id'] = $ob['id'];
                $gg['job']['status'] = "INTERRUPTED";
                $gg['job']['date_end'] = date("Y-m-d H:i:s");

                $res2 = $db->sql_save($gg);

                if ($res2) {
                    $ob['status'] = $gg['job']['status'];
                    $ob['date_end'] = $gg['job']['date_end'];
                }
            }

            if (file_exists($ob['log'])) {
                $log = file_get_contents($ob['log']);
            } else {
                $log = "";
            }
            $log = Mydumper::redactPasswords($log);
            $log = Mydumper::ParseLog($converter->convert($log));
            $ob['log_msg'] = $log;

            if (file_exists($ob['error'])) {
                $error = file_get_contents($ob['error']);
            } else {
                $error = "";
            }
            $error = Mydumper::redactPasswords($error);
            $error = Mydumper::ParseLog($converter->convert($error));
            $ob['error_msg'] = $error;

            // Issue #583: surface a single boolean for the view, so it
            // doesn't have to combine row-level checks with a filesystem
            // probe on its own.
            $ob['can_restart_load_only'] = RefreshArtifact::isResumable($ob)
                && is_dir((string) ($ob['artifact_path'] ?? ''));

            $data['jobs'][] = $ob;
        }

        $data['job_restart_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['job_restart_csrf_token'] = Csrf::issueToken($_SESSION, self::JOB_RESTART_CSRF_SCOPE);

        $this->set('data', $data);
    }

/**
 * Handle job state through `callback`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for callback.
 * @phpstan-return void
 * @psalm-return void
 * @see self::callback()
 * @example /fr/job/callback
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function callback($param) {

        usleep(1000);
//au cas ou le script est ultra rapide et le callback vient avant la création de la ligne

        Debug::parseDebug($param);
        $uuid = $param[0];
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * from `job` where `uuid`='" . $uuid . "';";
        Debug::sql($sql);
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $log = file_get_contents($ob->error);

            if (empty($log)) {
                $status = "SUCCESS";
            } else {
                $status = "ERROR";
            }

            $upt['job']['id'] = $ob->id;
            $upt['job']['status'] = $status;
            $upt['job']['date_end'] = date('Y-m-d H:i:s');

            $db->sql_save($upt);
        }
    }

/**
 * Create job state through `add`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for add.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::add()
 * @example /fr/job/add
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function add($param) {

        Debug::parseDebug($param);

        $uuid = $param[0];
        $parametre = $param[1];
        $pid = $param[2];
        $log = $param[3];
        $log_error = $param[4];



        $called_from = debug_backtrace();

        $job = array();
        $job['job']['uuid'] = $uuid;
        $job['job']['class'] = $called_from[3]['class'];
        $job['job']['method'] = $called_from[3]['function'];
        $job['job']['param'] = json_encode($parametre);
        $job['job']['date_start'] = date("Y-m-d H:i:s");
        $job['job']['pid'] = $pid;
        $job['job']['log'] = $log;
        $job['job']['error'] = $log_error;
        $job['job']['status'] = "RUNNING";


        Debug::debug($job);

        $db = Sgbd::sql(DB_DEFAULT);
        $id_job = $db->sql_save($job);


        return $id_job;
    }

/**
 * Handle job state through `gg`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for gg.
 * @phpstan-return void
 * @psalm-return void
 * @see self::gg()
 * @example /fr/job/gg
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function gg($param) {

        $this->add($param);
    }

/**
 * Handle job state through `restart`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for restart.
 * @phpstan-return void
 * @psalm-return void
 * @see self::restart()
 * @example /fr/job/restart
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function restart($param) {

        $this->view = false;
        $this->layout_name = false;

        $outcome = self::evaluateRestartRequest($param, $_POST ?? [], $_SERVER ?? [], $_SESSION ?? [], IS_CLI);
        if ($outcome['status'] !== 200) {
            self::sendRestartError($outcome['status'], $outcome['body'], $outcome['headers']);
            return;
        }

        $id_job = $outcome['id_job'];
        $debug = $outcome['debug'] || Debug::$debug === true;

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM job WHERE id=" . $id_job;
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            if (System::isRunningPid($ob->pid) !== true) {
                if (!self::isRelaunchableCommand((string) $ob->class, (string) $ob->method)) {
                    $msg = __("This job command is not allowed to restart");
                    set_flash("error", __("Error"), $msg);
                    error_log('[PmaControl] Job restart denied for job id ' . $id_job . ' command '
                        . (string) $ob->class . '/' . (string) $ob->method . ' requested by ' . $this->currentActorForLog());
                    continue;
                }

                $params = self::decodeRestartParameters((string) $ob->param);
                if ($params === null) {
                    $msg = __("This job payload cannot be restarted");
                    set_flash("error", __("Error"), $msg);
                    error_log('[PmaControl] Job restart denied for unparsable payload on job id ' . $id_job
                        . ' requested by ' . $this->currentActorForLog());
                    continue;
                }

                $php = explode(" ", shell_exec("whereis php"))[1];

                $cmd = self::buildRestartCommand(
                    $php,
                    GLIAL_INDEX,
                    (string) $ob->class,
                    (string) $ob->method,
                    $params,
                    $debug
                );
                if ($cmd === null) {
                    $msg = __("This job command is not allowed to restart");
                    set_flash("error", __("Error"), $msg);
                    continue;
                }
                Debug::debug($cmd);
                error_log('[PmaControl] Job restart requested by ' . $this->currentActorForLog()
                    . ' for job id ' . $id_job . ' command ' . (string) $ob->class . '/' . (string) $ob->method);

                $pid = trim(shell_exec($cmd));


                Debug::debug($pid, "PID");
            }
        }

        header("location: " . LINK .$this->getClass(). '/index');
    }

    public static function evaluateRestartRequest(array $param, array $post, array $server, array $session, bool $isCli = false): array
    {
        $idJob = PositiveIntegerSelection::normalizeSingle($param[0] ?? null);
        if ($idJob === null) {
            return self::buildRestartOutcome(400, 'Invalid job id', [], null, false);
        }

        $debug = in_array('--debug', array_map('strval', array_slice($param, 1)), true);

        if ($isCli) {
            return self::buildRestartOutcome(200, '', [], $idJob, $debug);
        }

        if ($failure = CsrfGuard::ensureOrFail($post, $server, $session, self::JOB_RESTART_CSRF_SCOPE)) {
            return self::buildRestartOutcome($failure['status'], $failure['body'], $failure['headers'], null, $debug);
        }

        return self::buildRestartOutcome(200, '', [], $idJob, $debug);
    }

    private static function buildRestartOutcome(
        int $statusCode,
        string $message,
        array $headers = [],
        ?int $idJob = null,
        bool $debug = false
    ): array {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'id_job' => $idJob,
            'debug' => $debug,
        ];
    }

    public static function isRelaunchableCommand(string $class, string $method): bool
    {
        $controller = self::normalizeControllerName($class);

        return in_array($method, self::RELAUNCHABLE_COMMANDS[$controller] ?? [], true);
    }

    /**
     * @param array<int,mixed> $params
     */
    public static function buildRestartCommand(
        string $php,
        string $index,
        string $class,
        string $method,
        array $params,
        bool $debug = false
    ): ?string {
        $controller = self::normalizeControllerName($class);
        if (!self::isRelaunchableCommand($controller, $method)) {
            return null;
        }

        $args = [$php, $index, $controller, $method];
        foreach ($params as $param) {
            if (!is_scalar($param) && $param !== null) {
                return null;
            }
            $args[] = (string) $param;
        }

        if ($debug) {
            $args[] = '--debug';
        }

        return ShellCommand::fromArguments($args)->toString();
    }

    private static function normalizeControllerName(string $class): string
    {
        $class = trim($class, '\\');
        $parts = explode('\\', $class);

        return (string) end($parts);
    }

    /**
     * @return list<string|null>|null
     */
    private static function decodeRestartParameters(string $json): ?array
    {
        $params = json_decode($json, true);
        if (!is_array($params) || array_values($params) !== $params) {
            return null;
        }

        foreach ($params as $param) {
            if (!is_scalar($param) && $param !== null) {
                return null;
            }
        }

        return $params;
    }

    private static function sendRestartError(int $statusCode, string $message, array $headers = []): void
    {
        HttpResponse::sendError($statusCode, $message, $headers);
    }

    private function currentActorForLog(): string
    {
        if (IS_CLI) {
            return 'CLI';
        }

        if (isset($this->di['auth']) && is_object($this->di['auth']) && method_exists($this->di['auth'], 'getUser')) {
            $user = $this->di['auth']->getUser();
            if (is_object($user)) {
                $name = trim((string) ($user->firstname ?? '') . ' ' . (string) ($user->name ?? ''));
                $id = isset($user->id) ? (int) $user->id : 0;

                if ($name !== '' || $id > 0) {
                    return ($name !== '' ? $name . ' ' : '') . '(id:' . $id . ')';
                }
            }
        }

        return 'HTTP ' . (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    }

}
