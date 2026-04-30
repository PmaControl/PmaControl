<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use App\Library\EngineV4;
use App\Library\Security\CsrfGuard;
use \Glial\Synapse\Controller;
use \App\Library\Debug;
use \App\Library\Microsecond;
use \Glial\I18n\I18n;
use Glial\Security\Csrf;
use \Glial\Sgbd\Sgbd;

/**
 * Class responsible for daemon workflows.
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
class Daemon extends Controller
{
    private const DAEMON_UPDATE_CSRF_SCOPE = 'daemon.update';
    private const DAEMON_UPDATE_FIELDS = ['refresh_time'];

/**
 * Render daemon state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/daemon/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index($param)
    {
        $db = Sgbd::sql(DB_DEFAULT);
        
        if (!empty($_GET['ajax']) && $_GET['ajax'] === "true") {
            $this->layout_name = false;
        }
        else {
            $this->di['js']->addJavascript(array('bootstrap-editable.min.js', 'Tree/index.js'));
    
            $this->di['js']->code_javascript('
            $(document).ready(function(){
                function refresh(){
                    var myURL = GLIAL_LINK+"worker/list"+"/ajax:true";
                    $("#worker-list").load(myURL);
                }

                var intervalId = window.setInterval(function(){
                    refresh()  
                  }, 300);
            });');

            $this->di['js']->code_javascript('
            $(document).ready(function(){
                function refresh(){
                    var myURL = GLIAL_LINK+"worker/file"+"/ajax:true";
                    $("#tmp_file").load(myURL);
                }

                var intervalId = window.setInterval(function(){
                    refresh()  
                  }, 1000);
            });');

            $this->di['js']->code_javascript('
            $(document).ready(function(){
                function refresh(){
                    var myURL = GLIAL_LINK+"worker/index"+"/ajax:true";
                    $("#worker-index").load(myURL, function(){
                        if (window.pmacontrolInitLineEdit) {
                            window.pmacontrolInitLineEdit(this);
                        }
                    });
                }

                var intervalId = window.setInterval(function(){
                    refresh()  
                  }, 100);
            });');
            
        }

        $sql = "SELECT * from daemon_main order by id";
        $res = $db->sql_query($sql);

        $data['daemon'] = [];
        while ($arr            = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['daemon'][] = $arr;
        }
        $data['daemon_update_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['daemon_update_csrf_token'] = Csrf::issueToken($_SESSION, self::DAEMON_UPDATE_CSRF_SCOPE);

        $this->set('data', $data);
    }

/**
 * Handle daemon state through `startAll`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for startAll.
 * @phpstan-return void
 * @psalm-return void
 * @see self::startAll()
 * @example /fr/daemon/startAll
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function startAll($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);
        $db->sql_query("UPDATE daemon_main SET is_enabled = 1");

        $this->manageDaemon("start");

        if (!IS_CLI) {
            $msg   = I18n::getTranslation(__("All the daemon was successfully started"));
            $title = I18n::getTranslation(__("Success"));
            set_flash("success", $title, $msg);
            header("location: ".LINK.$this->getClass()."/index");
            exit;
        }
    }

/**
 * Handle daemon state through `stopAll`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for stopAll.
 * @phpstan-return void
 * @psalm-return void
 * @see self::stopAll()
 * @example /fr/daemon/stopAll
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function stopAll($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);
        $db->sql_query("UPDATE daemon_main SET is_enabled = 0");

        $this->manageDaemon("stop");

        if (!IS_CLI) {
            $msg   = I18n::getTranslation(__("All the daemon was successfully stopped"));
            $title = I18n::getTranslation(__("Success"));
            set_flash("success", $title, $msg);
            header("location: ".LINK.$this->getClass()."/index");
            exit;
        }
    }

/**
 * Handle daemon state through `manageDaemon`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $commande Input value for `commande`.
 * @phpstan-param mixed $commande
 * @psalm-param mixed $commande
 * @return void Returned value for manageDaemon.
 * @phpstan-return void
 * @psalm-return void
 * @see self::manageDaemon()
 * @example /fr/daemon/manageDaemon
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function manageDaemon($commande)
    {
        $db = Sgbd::sql(DB_DEFAULT);
        $php = explode(" ", shell_exec("whereis php"))[1];

        if ($commande === "stop") {
            // Stop all daemons directly (kill + reset pid).
            // Does NOT change is_enabled — callers decide that.
            $res = $db->sql_query("SELECT id, pid FROM daemon_main ORDER BY id DESC");
            while ($ob = $db->sql_fetch_object($res)) {
                if (!empty($ob->pid) && $ob->pid !== "0") {
                    shell_exec("kill " . intval($ob->pid));
                }
                $db->sql_query("UPDATE daemon_main SET pid = '0' WHERE id = " . intval($ob->id));
            }

            $cmd = $php." ".GLIAL_INDEX." Worker killAll";
            Debug::debug($cmd);
            shell_exec($cmd);
        } else {
            // Start only enabled daemons
            $res = $db->sql_query("SELECT id FROM daemon_main WHERE is_enabled = 1 ORDER BY id ASC");
            while ($ob = $db->sql_fetch_object($res)) {
                $cmd = $php." ".GLIAL_INDEX." Agent start ".$ob->id;
                Debug::debug($cmd);
                shell_exec($cmd);
            }
        }
    }

/**
 * Update daemon state through `update`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @return void Returned value for update.
 * @phpstan-return void
 * @psalm-return void
 * @see self::update()
 * @example /fr/daemon/update
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function update()
    {

        $this->view        = false;
        $this->layout_name = false;

        $outcome = self::evaluateUpdateRequest($_POST, $_SERVER, $_SESSION);
        if ($outcome['status'] !== 200) {
            self::sendDaemonUpdateError($outcome['status'], $outcome['body'], $outcome['headers']);
            return;
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $db->sql_query($outcome['sql']);

        if ($db->sql_affected_rows() === 1) {
            echo "OK";
        } else {
            self::sendDaemonUpdateError(503, "Daemon not updated");
        }
    }

    public static function evaluateUpdateRequest(array $post, array $server, array $session): array
    {
        if ($failure = CsrfGuard::ensureOrFail($post, $server, $session, self::DAEMON_UPDATE_CSRF_SCOPE)) {
            return self::buildDaemonUpdateOutcome($failure['status'], $failure['body'], $failure['headers']);
        }

        $sql = self::buildDaemonUpdateSql($post);
        if ($sql === null) {
            return self::buildDaemonUpdateOutcome(400, "Invalid daemon update payload");
        }

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'sql' => $sql,
        ];
    }

    public static function buildDaemonUpdateSql(array $post): ?string
    {
        $field = (string) ($post['name'] ?? '');
        $value = (string) ($post['value'] ?? '');
        $id = (string) ($post['pk'] ?? '');

        if (! in_array($field, self::DAEMON_UPDATE_FIELDS, true)) {
            return null;
        }

        if (! ctype_digit($value) || ! ctype_digit($id) || (int) $id < 1) {
            return null;
        }

        return sprintf(
            'UPDATE daemon_main SET `%s` = %d WHERE id = %d',
            $field,
            (int) $value,
            (int) $id
        );
    }

    private static function buildDaemonUpdateOutcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'sql' => null,
        ];
    }

    private static function sendDaemonUpdateError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        header('Content-Type: text/plain; charset=UTF-8');
        echo $message;
    }

/**
 * Handle daemon state through `refresh`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for refresh.
 * @phpstan-return void
 * @psalm-return void
 * @see self::refresh()
 * @example /fr/daemon/refresh
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function refresh($param)
    {

        $this->view = false;

        Debug::parseDebug($param);

        if (Debug::$debug === true) {
            $debug = " --debug";
        } else {
            $debug = "";
        }

        // Stop all daemons without changing is_enabled
        $this->manageDaemon("stop");

        $php = explode(" ", shell_exec("whereis php"))[1];
        $cmd = $php." ".GLIAL_INDEX." control service".$debug;
        Debug::debug($cmd);
        shell_exec($cmd);

        usleep(5000);

        // Restart only daemons that were enabled before the refresh
        $this->manageDaemon("start");

        if (!IS_CLI) {
            $msg   = I18n::getTranslation(__("All lock/pid/md5 has been deleted and partions has been updated"));
            $title = I18n::getTranslation(__("Success"));

            set_flash("success", $title, $msg);
            header("location: ".LINK.$this->getClass()."/index");
            exit;
        }
    }

/**
 * Retrieve daemon state through `getStatitics`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for getStatitics.
 * @phpstan-return void
 * @psalm-return void
 * @see self::getStatitics()
 * @example /fr/daemon/getStatitics
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getStatitics($param = array())
    {

        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "select * from ts_file order by id;";
        $res = $db->sql_query($sql);

        $file = array();
        while ($ob   = $db->sql_fetch_object($res)) {
            $file[$ob->id] = $ob->file_name;
        }

        $path = TMP."tmp_file/*";

        $all_files = glob($path);

        $total = array();
        foreach ($all_files as $fullpath) {

            $filename = pathinfo($fullpath)['filename'];

            $prefix = explode('_', $filename)[0];

            if (empty($total[$prefix])) {
                $total[$prefix] = 1;
            } else {
                $total[$prefix]++;
            }
        }

        foreach ($total as $key => $nb_file) {
            if ($nb_file > 10) {

                if (!in_array($key, $file)) {
                    $sql = "INSERT INTO ts_file (file_name) VALUES ('".$key."');";
                    $db->sql_query($sql);
                }
            }
        }

        $data['registered']   = $file;
        $data['to_integrate'] = $total;

        Debug::debug($data);
    }


/**
 * Retrieve daemon state through `getAllProcessPhp`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for getAllProcessPhp.
 * @phpstan-return void
 * @psalm-return void
 * @see self::getAllProcessPhp()
 * @example /fr/daemon/getAllProcessPhp
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getAllProcessPhp($param)
    {

        $cmd = "ps -eo args= | grep '^/usr/bin/php' | sort | uniq -c | sort -nr";
    }




}
