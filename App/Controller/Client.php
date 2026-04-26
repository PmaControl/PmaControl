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

        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $db = Sgbd::sql(DB_DEFAULT);

            $allowedFields = ['libelle', 'logo', 'is_monitored', 'is_display'];
            $field = $_POST['name'] ?? '';
            if (!in_array($field, $allowedFields, true)) {
                header("HTTP/1.0 400 Bad Request");
                echo "Invalid field";
                return;
            }

            $pk = (int)($_POST['pk'] ?? 0);
            $value = $db->sql_real_escape_string($_POST['value'] ?? '');

            $sql = "UPDATE client SET `$field` = '$value' WHERE id = $pk";
            $db->sql_query($sql);

            if ($db->sql_affected_rows() >= 0) {
                echo "OK";
            } else {
                header("HTTP/1.0 503 Internal Server Error");
            }
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

        if (!self::isPostRequest($_SERVER)) {
            $this->respondMonitoringToggleJson([
                'success' => false,
                'error' => 'Method not allowed',
            ], 405);
        }

        try {
            $payload = self::normalizeMonitoringTogglePayload(is_array($param) ? $param : [], $_POST);
        } catch (\InvalidArgumentException $exception) {
            $this->respondMonitoringToggleJson([
                'success' => false,
                'error' => $exception->getMessage(),
            ], 400);
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "UPDATE client SET `is_monitored` = ".$payload['is_monitored']." WHERE id = ".$payload['id'];
        $db->sql_query($sql);

        $this->respondMonitoringToggleJson([
            'success' => true,
            'id' => $payload['id'],
            'is_monitored' => $payload['is_monitored'],
        ]);
    }

    public static function isPostRequest(array $server): bool
    {
        return strtoupper((string) ($server['REQUEST_METHOD'] ?? 'GET')) === 'POST';
    }

    public static function normalizeMonitoringTogglePayload(array $param, array $post): array
    {
        $id = $post['id'] ?? $param[0] ?? null;
        $isMonitored = $post['is_monitored'] ?? $param[1] ?? null;

        if (!is_numeric($id) || (int) $id <= 0) {
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
            'id' => (int) $id,
            'is_monitored' => $normalizedStatus ? 1 : 0,
        ];
    }

    private function respondMonitoringToggleJson(array $payload, int $statusCode = 200): void
    {
        if (!headers_sent()) {
            http_response_code($statusCode);
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

        if (!self::isDeleteRequestAllowed($_SERVER)) {
            set_flash("error", __("Error"), __("Invalid request method"));
            header("location: ".LINK."client/index");
            exit;
        }

        $id_client = (int) ($param[0] ?? 0);
        if ($id_client <= 0) {
            set_flash("error", __("Error"), __("Invalid client id"));
            header("location: ".LINK."client/index");
            exit;
        }

        if ($id_client == "99") {
            set_flash("error", __("Error"), __("Invalid client id"));
            header("location: ".LINK."client/index");
            exit;
        }

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
}
