<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\I18n\I18n;
use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use App\Library\Security\CsrfGuard;
use App\Library\Security\InlineEditRequest;
use Glial\Security\Csrf;

/**
 * Class responsible for client workflows.
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
class Client extends Controller
{
    private const CLIENT_UPDATE_CSRF_SCOPE = 'client.update';
    private const CLIENT_UPDATE_FIELDS = ['libelle', 'logo', 'is_monitored', 'is_display'];
    private const CLIENT_UPDATE_BOOLEAN_FIELDS = ['is_monitored', 'is_display'];
    private const CLIENT_UPDATE_VALUE_MAX_BYTES = 255;
    private const CLIENT_RESERVED_ID = 99;
    private const CLIENT_DELETE_CSRF_SCOPE = 'client.delete';
    private const CLIENT_MONITORING_TOGGLE_CSRF_SCOPE = 'client.toggleMonitoring';

/**
 * Render client state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/client/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index()
    {
        $this->title  = '<span class="glyphicon glyphicon glyphicon-user"></span> '.__("Clients");
        $this->ariane = self::buildSettingsBreadcrumb(LINK, __("Settings")).' >'.$this->title;

        $this->di['js']->addJavascript(array('bootstrap-editable.min.js', 'Tree/index.js', 'Client/index.js'));

        $db             = Sgbd::sql(DB_DEFAULT);
        $sql            = "SELECT c.*, 
                COALESCE(stats.total_servers, 0) AS total_servers,
                COALESCE(stats.online_servers, 0) AS online_servers,
                COALESCE(stats.offline_servers, 0) AS offline_servers,
                COALESCE(stats.non_monitored_servers, 0) AS non_monitored_servers
            FROM client c
            LEFT JOIN (
                SELECT ms.id_client,
                    COUNT(*) AS total_servers,
                    SUM(
                        CASE
                            WHEN (CASE WHEN c2.is_monitored = 0 THEN 0 ELSE ms.is_monitored END) = 1
                                 AND COALESCE(avail.mysql_available, 0) = 1 THEN 1
                            ELSE 0
                        END
                    ) AS online_servers,
                    SUM(
                        CASE
                            WHEN (CASE WHEN c2.is_monitored = 0 THEN 0 ELSE ms.is_monitored END) = 1
                                 AND COALESCE(avail.mysql_available, 0) <> 1 THEN 1
                            ELSE 0
                        END
                    ) AS offline_servers,
                    SUM(
                        CASE
                            WHEN (CASE WHEN c2.is_monitored = 0 THEN 0 ELSE ms.is_monitored END) = 0 THEN 1
                            ELSE 0
                        END
                    ) AS non_monitored_servers
                FROM mysql_server ms
                INNER JOIN client c2 ON c2.id = ms.id_client
                LEFT JOIN (
                    SELECT tmd.id_mysql_server,
                        tvi.value AS mysql_available
                    FROM ts_variable tv
                    INNER JOIN ts_max_date tmd ON tmd.id_ts_file = tv.id_ts_file
                    LEFT JOIN ts_value_general_int tvi
                        ON tvi.id_mysql_server = tmd.id_mysql_server
                        AND tvi.id_ts_variable = tv.id
                        AND tvi.date = tmd.date
                    WHERE tv.name = 'mysql_available'
                        AND tv.`from` = 'mysql_server'
                ) avail ON avail.id_mysql_server = ms.id
                WHERE ms.is_deleted = 0
                GROUP BY ms.id_client
            ) stats ON stats.id_client = c.id
            WHERE c.is_display = 1
            ORDER BY c.libelle";
        $data['client'] = $db->sql_fetch_all($sql);
        $data['client_update_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['client_update_csrf_token'] = Csrf::issueToken($_SESSION, self::CLIENT_UPDATE_CSRF_SCOPE);
        $data['client_delete_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['client_delete_csrf_token'] = Csrf::issueToken($_SESSION, self::CLIENT_DELETE_CSRF_SCOPE);
        $data['client_monitoring_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['client_monitoring_csrf_token'] = Csrf::issueToken($_SESSION, self::CLIENT_MONITORING_TOGGLE_CSRF_SCOPE);

        $this->set('data', $data);
    }

/**
 * Create client state through `add`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for add.
 * @phpstan-return void
 * @psalm-return void
 * @see self::add()
 * @example /fr/client/add
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function add()
    {
        $this->title  = '<span class="glyphicon glyphicon glyphicon-plus"></span> '.__("Add a new client");
        $this->ariane = self::buildSettingsBreadcrumb(LINK, __("Settings")).' >'.
            '<span class="glyphicon glyphicon glyphicon-user"></span> '.__("Clients").' > '
            .$this->title;

        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            if (!empty($_POST['client']['libelle'])) {
                $db = Sgbd::sql(DB_DEFAULT);

                $client                      = [];
                $client['client']['libelle'] = $_POST['client']['libelle'];
                $client['client']['date']    = date('Y-m-d H:i:s');

                $res = $db->sql_save($client);

                if (!$res) {
                    debug($client);
                    debug($db->sql_error());
                    //die();

                    $msg   = I18n::getTranslation(__("Impossible to find the daemon with the id : ")."'".$id_daemon."'");
                    $title = I18n::getTranslation(__("Error"));
                    set_flash("error", $title, $msg);
                    header("location: ".LINK."client/add");

                    exit;
                } else {
                    $msg   = I18n::getTranslation(__("Client add"));
                    $title = I18n::getTranslation(__("Success"));
                    set_flash("success", $title, $msg);
                    header("location: ".LINK.'client/index');
                }
            }
        }
    }

    public static function buildSettingsBreadcrumb(string $link, string $settingsLabel = 'Settings'): string
    {
        return ' > <a href="'.$link.'"><span class="glyphicon glyphicon glyphicon-cog" style="font-size:12px">'
            .'</span> '.$settingsLabel.'</a>';
    }

/**
 * Update client state through `update`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for update.
 * @phpstan-return void
 * @psalm-return void
 * @see self::update()
 * @example /fr/client/update
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function update($param)
    {
        $this->view        = false;
        $this->layout_name = false;

        $outcome = self::evaluateUpdateRequest($_POST, $_SERVER, $_SESSION);
        if ($outcome['status'] !== 200) {
            self::sendClientUpdateError($outcome['status'], $outcome['body'], $outcome['headers']);
            return;
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = self::buildClientUpdateSql($outcome['update'], [$db, 'sql_real_escape_string']);
        $db->sql_query($sql);

        if ($db->sql_affected_rows() >= 0) {
            echo "OK";
        } else {
            self::sendClientUpdateError(503, "Client not updated");
        }
    }

    public static function evaluateUpdateRequest(array $post, array $server, array $session): array
    {
        $outcome = InlineEditRequest::evaluate(
            $post,
            $server,
            $session,
            self::CLIENT_UPDATE_CSRF_SCOPE,
            self::CLIENT_UPDATE_FIELDS,
            'Invalid client update payload',
            self::CLIENT_UPDATE_VALUE_MAX_BYTES
        );

        if ($outcome['status'] !== 200) {
            return $outcome;
        }

        $update = self::normalizeClientUpdate($outcome['update']);
        if ($update === null) {
            return self::buildClientUpdateOutcome(400, 'Invalid client update payload');
        }

        return self::buildClientUpdateOutcome(200, '', [], $update);
    }

    public static function normalizeUpdatePayload(array $post): ?array
    {
        return self::normalizeClientUpdate(InlineEditRequest::normalize(
            $post,
            self::CLIENT_UPDATE_FIELDS,
            self::CLIENT_UPDATE_VALUE_MAX_BYTES
        ));
    }

    public static function buildClientUpdateSql(array $update, callable $escape): string
    {
        return sprintf(
            "UPDATE client SET `%s` = '%s' WHERE id = %d",
            $update['field'],
            $escape($update['value']),
            $update['id']
        );
    }

    private static function normalizeClientUpdate(?array $update): ?array
    {
        if ($update === null || $update['id'] === self::CLIENT_RESERVED_ID) {
            return null;
        }

        if ($update['field'] === 'libelle' && trim($update['value']) === '') {
            return null;
        }

        if (in_array($update['field'], self::CLIENT_UPDATE_BOOLEAN_FIELDS, true)) {
            $boolean = filter_var($update['value'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($boolean === null) {
                return null;
            }

            $update['value'] = $boolean ? '1' : '0';
        }

        return $update;
    }

    private static function buildClientUpdateOutcome(int $statusCode, string $message, array $headers = [], ?array $update = null): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'update' => $update,
        ];
    }

    private static function sendClientUpdateError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }

        if ($message !== '') {
            echo $message;
        }
    }

/**
 * Toggle client state through `toggleMonitoring`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for toggleMonitoring.
 * @phpstan-return void
 * @psalm-return void
 * @see self::toggleMonitoring()
 * @example /fr/client/toggleMonitoring
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function toggleMonitoring($param)
    {

        $this->view        = false;
        $this->layout_name = false;

        $toggleRequest = self::evaluateMonitoringToggleRequest(
            $_POST,
            $_SERVER,
            $_SESSION,
            is_array($param) ? $param : []
        );
        if ($toggleRequest['status'] !== 200) {
            $this->respondMonitoringToggleJson([
                'success' => false,
                'error' => $toggleRequest['body'],
            ], $toggleRequest['status'], $toggleRequest['headers']);
        }

        $payload = $toggleRequest['payload'];
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "UPDATE client SET `is_monitored` = ".$payload['is_monitored']." WHERE id = ".$payload['id'];
        $db->sql_query($sql);

        $this->respondMonitoringToggleJson([
            'success' => true,
            'id' => $payload['id'],
            'is_monitored' => $payload['is_monitored'],
        ]);
    }

    public static function evaluateMonitoringToggleRequest(array $post, array $server, array $session, array $param): array
    {
        $guard = CsrfGuard::check($post, $server, $session, self::CLIENT_MONITORING_TOGGLE_CSRF_SCOPE);
        if (!$guard['allowed']) {
            return self::buildMonitoringToggleOutcome($guard['status'], $guard['body'], $guard['headers']);
        }

        try {
            $payload = self::normalizeMonitoringTogglePayload($param, $post);
        } catch (\InvalidArgumentException $exception) {
            return self::buildMonitoringToggleOutcome(400, $exception->getMessage());
        }

        return self::buildMonitoringToggleOutcome(200, '', [], $payload);
    }

    public static function isPostRequest(array $server): bool
    {
        return strtoupper((string) ($server['REQUEST_METHOD'] ?? 'GET')) === 'POST';
    }

    public static function normalizeMonitoringTogglePayload(array $param, array $post): array
    {
        $id = $post['id'] ?? $param[0] ?? null;
        $isMonitored = $post['is_monitored'] ?? $param[1] ?? null;
        $normalizedId = self::normalizeMonitoringClientId($id);

        if ($normalizedId === null) {
            throw new \InvalidArgumentException('Invalid client id');
        }

        if ($isMonitored === null || $isMonitored === '') {
            throw new \InvalidArgumentException('Invalid monitoring status');
        }

        $normalizedStatus = filter_var($isMonitored, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($normalizedStatus === null) {
            throw new \InvalidArgumentException('Invalid monitoring status');
        }

        return [
            'id' => $normalizedId,
            'is_monitored' => $normalizedStatus ? 1 : 0,
        ];
    }

    private static function normalizeMonitoringClientId($value): ?int
    {
        if (is_int($value)) {
            $id = $value;
        } elseif (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '' || !ctype_digit($trimmed)) {
                return null;
            }
            $id = (int) $trimmed;
        } else {
            return null;
        }

        if ($id <= 0 || $id === self::CLIENT_RESERVED_ID) {
            return null;
        }

        return $id;
    }

    private static function buildMonitoringToggleOutcome(int $statusCode, string $message, array $headers = [], ?array $payload = null): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'payload' => $payload,
        ];
    }

    private function respondMonitoringToggleJson(array $payload, int $statusCode = 200, array $headers = []): void
    {
        if (!headers_sent()) {
            http_response_code($statusCode);
            foreach ($headers as $name => $value) {
                header($name . ': ' . $value);
            }
            header('Content-Type: application/json; charset=UTF-8');
        }

        echo json_encode($payload);
        exit;
    }

/**
 * Delete client state through `delete`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for delete.
 * @phpstan-return void
 * @psalm-return void
 * @see self::delete()
 * @example /fr/client/delete
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function delete($param)
    {
        $this->view        = false;
        $this->layout_name = false;

        $deleteRequest = self::evaluateDeleteRequest($_POST, $_SERVER, $_SESSION, is_array($param) ? $param : []);
        if ($deleteRequest['status'] !== 200) {
            self::sendClientDeleteError($deleteRequest['status'], $deleteRequest['body'], $deleteRequest['headers']);
            return;
        }

        $id_client = $deleteRequest['id_client'];
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT
                    SUM(CASE WHEN COALESCE(is_deleted, 0) = 0 THEN 1 ELSE 0 END) AS active_servers,
                    SUM(CASE WHEN COALESCE(is_deleted, 0) = 1 THEN 1 ELSE 0 END) AS deleted_servers
                FROM mysql_server
                WHERE id_client = ".$id_client;
        $res = $db->sql_query($sql);
        $row = $db->sql_fetch_array($res, MYSQLI_ASSOC);

        $active_servers = (int) ($row['active_servers'] ?? 0);
        $deleted_servers = (int) ($row['deleted_servers'] ?? 0);

        if ($active_servers !== 0) {
            set_flash(
                "error",
                I18n::getTranslation(__("Error")),
                I18n::getTranslation(__("Impossible to delete this organization, first you have to move the serveur to an other organization"))
            );
            header("location: ".LINK."client/index");
            exit;
        }

        if ($deleted_servers !== 0) {
            $sql = "UPDATE mysql_server SET id_client = 99 WHERE id_client = ".$id_client." AND COALESCE(is_deleted, 0) = 1";
            $db->sql_query($sql);
        }

        $sql = "DELETE FROM client WHERE id = ".$id_client."";
        $db->sql_query($sql);

        set_flash("success", I18n::getTranslation(__("Success")), I18n::getTranslation(__("Organization deleted")));
        header("location: ".LINK."client/index");
        exit;
    }

    public static function isDeleteRequestAllowed(array $server): bool
    {
        return strtoupper((string) ($server['REQUEST_METHOD'] ?? 'GET')) === 'POST';
    }

    public static function evaluateDeleteRequest(array $post, array $server, array $session, array $param): array
    {
        $guard = CsrfGuard::check($post, $server, $session, self::CLIENT_DELETE_CSRF_SCOPE);
        if (!$guard['allowed']) {
            return self::buildClientDeleteOutcome($guard['status'], $guard['body'], $guard['headers']);
        }

        $idClient = self::normalizeDeleteClientId($param[0] ?? null);
        if ($idClient === null) {
            return self::buildClientDeleteOutcome(400, 'Invalid client id');
        }

        return self::buildClientDeleteOutcome(200, '', [], $idClient);
    }

    public static function normalizeDeleteClientId($value): ?int
    {
        if (is_int($value)) {
            $id = $value;
        } elseif (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '' || !ctype_digit($trimmed)) {
                return null;
            }
            $id = (int) $trimmed;
        } else {
            return null;
        }

        if ($id <= 0 || $id === self::CLIENT_RESERVED_ID) {
            return null;
        }

        return $id;
    }

    private static function buildClientDeleteOutcome(int $statusCode, string $message, array $headers = [], ?int $idClient = null): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'id_client' => $idClient,
        ];
    }

    private static function sendClientDeleteError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        header('Content-Type: text/plain; charset=UTF-8');

        if ($message !== '') {
            echo $message;
        }
    }
}
