<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use App\Library\Security\CsrfGuard;
use Glial\Cli\Crontab as CliCrontab;
use Glial\Security\Csrf;


/**
 * Class responsible for crontab workflows.
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
class Crontab extends Controller {
    private const ADMIN_CRONTAB_ADD_CSRF_SCOPE = 'crontab.admin_crontab.add';
    private const ADMIN_CRONTAB_DELETE_CSRF_SCOPE = 'crontab.admin_crontab.delete';
    private const ADMIN_CRONTAB_CRON_FIELDS = ['minute', 'hour', 'dayofmonth', 'month', 'dayofweek'];

/**
 * Stores `$module_group` for module group.
 *
 * @var string
 * @phpstan-var string
 * @psalm-var string
 */
    public $module_group = "Administration";
/**
 * Stores `$debut` for debut.
 *
 * @var string
 * @phpstan-var string
 * @psalm-var string
 */
    var $debut = '#Les lignes suivantes sont gerees automatiquement via un script PHP. - Merci de ne pas editer manuellement';
/**
 * Stores `$fin` for fin.
 *
 * @var string
 * @phpstan-var string
 * @psalm-var string
 */
    var $fin = '#Les lignes suivantes ne sont plus gerees automatiquement';

/**
 * Render crontab state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/crontab/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function index() {
        
    }

/**
 * Handle crontab state through `admin_crontab`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for admin_crontab.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::admin_crontab()
 * @example /fr/crontab/admin_crontab
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function admin_crontab() {
        $module['picture'] = "administration/iconAttendance.gif";
        $module['name'] = __("Crontab");
        $module['description'] = __("Manage all yours jobs");

        //if (from() !== "administration.controller.php") {


        $this->javascript = array("jquery.1.3.2.js");

        if (CsrfGuard::isPost($_SERVER)) {
            if (!empty($_POST['crontab']['command'])) {
                $request = self::evaluateAdminCrontabAddRequest($_POST, $_SERVER, $_SESSION);
                if ($request['status'] !== 200) {
                    $this->view        = false;
                    $this->layout_name = false;
                    self::sendAdminCrontabError($request['status'], $request['body'], $request['headers']);
                    return $module;
                }

                $payload = $request['payload'];
                $regexp = CliCrontab::buildRegexp();

                $ligne = $payload['line'];

                if (preg_match("/$regexp/", $ligne)) {
                    set_flash("success", "Added", "This tasks has beend added in the crontab");

                    CliCrontab::insert($payload['minute'], $payload['hour'], $payload['dayofmonth'], $payload['month'], $payload['dayofweek'],
                            $payload['command'], "commentaire =)");

                    header("location: " . $_SERVER['REQUEST_URI']);
                    die();
                } else {
                    set_flash("error", "Error", "This crontab is not valid : " . $ligne);

                    header("location: " . LINK .$this->getClass(). "/" . __FUNCTION__);

                    die();
                }
            }

            if (!empty($_POST['crontab']['delete'])) {
                $request = self::evaluateAdminCrontabDeleteRequest($_POST, $_SERVER, $_SESSION);
                if ($request['status'] !== 200) {
                    $this->view        = false;
                    $this->layout_name = false;
                    self::sendAdminCrontabError($request['status'], $request['body'], $request['headers']);
                    return $module;
                }

                set_flash("success", "Removed", "This task has been removed");
                CliCrontab::delete($request['payload']['delete']);
                header("location: " . $_SERVER['REQUEST_URI']);
                die();
            } else {
                $this->view        = false;
                $this->layout_name = false;
                self::sendAdminCrontabError(400, 'Invalid crontab action');
                return $module;
            }
        }

        //$this->layout_name = "admin";


        $this->title = __("Crontab");
        $this->ariane = "> <a href=\"" . LINK . "administration/\">" . __("Administration") . "</a> > " . $this->title;
        $data = $this->view();
        $data['crontab_add_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['crontab_add_csrf_token'] = Csrf::issueToken($_SESSION, self::ADMIN_CRONTAB_ADD_CSRF_SCOPE);
        $data['crontab_delete_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['crontab_delete_csrf_token'] = Csrf::issueToken($_SESSION, self::ADMIN_CRONTAB_DELETE_CSRF_SCOPE);
        $this->set("data", $data);
        //}

        return $module;
    }

    public static function evaluateAdminCrontabAddRequest(array $post, array $server, array $session): array
    {
        $guard = CsrfGuard::check($post, $server, $session, self::ADMIN_CRONTAB_ADD_CSRF_SCOPE);
        if (! $guard['allowed']) {
            return self::buildAdminCrontabOutcome($guard['status'], $guard['body'], $guard['headers']);
        }

        if (! isset($post['crontab']) || ! is_array($post['crontab'])) {
            return self::buildAdminCrontabOutcome(400, 'Invalid crontab payload');
        }

        $payload = [];
        foreach (self::ADMIN_CRONTAB_CRON_FIELDS as $field) {
            if (! array_key_exists($field, $post['crontab']) || ! is_scalar($post['crontab'][$field])) {
                return self::buildAdminCrontabOutcome(400, 'Invalid crontab payload');
            }

            $value = trim((string) $post['crontab'][$field]);
            if ($value === '' || preg_match('/[\s\r\n]/', $value) || strlen($value) > 64) {
                return self::buildAdminCrontabOutcome(400, 'Invalid crontab schedule');
            }

            $payload[$field] = $value;
        }

        if (! array_key_exists('command', $post['crontab']) || ! is_scalar($post['crontab']['command'])) {
            return self::buildAdminCrontabOutcome(400, 'Invalid crontab payload');
        }

        $command = trim((string) $post['crontab']['command']);
        if ($command === '' || preg_match('/[\r\n]/', $command) || strlen($command) > 4096) {
            return self::buildAdminCrontabOutcome(400, 'Invalid crontab command');
        }

        $payload['command'] = $command;
        $payload['line'] = $payload['minute'] . ' ' . $payload['hour'] . ' ' . $payload['dayofmonth'] . ' ' . $payload['month'] . ' ' . $payload['dayofweek'] . ' ' . $payload['command'];

        return self::buildAdminCrontabOutcome(200, '', [], $payload);
    }

    public static function evaluateAdminCrontabDeleteRequest(array $post, array $server, array $session): array
    {
        $guard = CsrfGuard::check($post, $server, $session, self::ADMIN_CRONTAB_DELETE_CSRF_SCOPE);
        if (! $guard['allowed']) {
            return self::buildAdminCrontabOutcome($guard['status'], $guard['body'], $guard['headers']);
        }

        if (
            ! isset($post['crontab'])
            || ! is_array($post['crontab'])
            || ! array_key_exists('delete', $post['crontab'])
            || ! is_scalar($post['crontab']['delete'])
        ) {
            return self::buildAdminCrontabOutcome(400, 'Invalid crontab payload');
        }

        $delete = trim((string) $post['crontab']['delete']);
        if (! ctype_digit($delete) || (int) $delete < 1) {
            return self::buildAdminCrontabOutcome(400, 'Invalid crontab delete id');
        }

        return self::buildAdminCrontabOutcome(200, '', [], ['delete' => (int) $delete]);
    }

    private static function buildAdminCrontabOutcome(int $statusCode, string $message, array $headers = [], ?array $payload = null): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'payload' => $payload,
        ];
    }

    private static function sendAdminCrontabError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        header('Content-Type: text/plain; charset=UTF-8');
        echo $message;
    }

/**
 * Handle crontab state through `view`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for view.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::view()
 * @example /fr/crontab/view
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function view() {
        $isSection = false;
        exec('crontab -l', $oldCrontab);  /* on récupère l'ancienne crontab dans $oldCrontab */

        $tab = array();

        foreach ($oldCrontab as $index => $ligne) /* copie $oldCrontab dans $newCrontab et ajoute le nouveau script */ {
            if ($ligne == $this->debut) {
                $isSection = true;
                continue;
            }

            if ($ligne == $this->fin) {
                $isSection = false;
                break;
            }

            if ($isSection) {
                $elem = explode(" ", $ligne);

                if ($elem[0] === "#") {
                    $id = $elem[1];
                    continue;
                }

                $tab[$id] = $ligne;
            }
        }



        return ($tab);
    }

/**
 * Handle crontab state through `monitor`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for monitor.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::monitor()
 * @example /fr/crontab/monitor
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function monitor($param) {
        $this->view = false;

        $php = explode(" ", shell_exec("whereis php"))[1];

        $cmd = $php . " " . GLIAL_INDEX . " " . implode(" ", $param);
        passthru($cmd, $code_retour);


        return $code_retour;
    }

}
