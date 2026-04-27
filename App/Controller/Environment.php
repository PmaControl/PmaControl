<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use App\Library\Post;
use App\Library\Security\InlineEditRequest;
use \Glial\Sgbd\Sgbd;
use Glial\Security\Csrf;


/**
 * Class responsible for environment workflows.
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
class Environment extends Controller {
    private const ENVIRONMENT_UPDATE_CSRF_SCOPE = 'environment.update';
    private const ENVIRONMENT_UPDATE_FIELDS = ['libelle', 'key', 'class', 'letter'];
    private const ENVIRONMENT_UPDATE_VALUE_MAX_BYTES = 255;

/**
 * Render environment state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/environment/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index() {
        $this->title = '<i class="fa fa-th-large" aria-hidden="true"></i> ' . __("Environment");


        $this->di['js']->addJavascript(array('bootstrap-editable.min.js', 'Tree/index.js'));
        $this->di['js']->addJavascript(array('bootstrap-select.min.js'));


        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM environment order by `id`";

        $res = $db->sql_query($sql);

        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['env'][] = $row;
        }

        $data['environment_update_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['environment_update_csrf_token'] = Csrf::issueToken($_SESSION, self::ENVIRONMENT_UPDATE_CSRF_SCOPE);

        $this->set('data', $data);
    }

/**
 * Update environment state through `update`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @return void Returned value for update.
 * @phpstan-return void
 * @psalm-return void
 * @see self::update()
 * @example /fr/environment/update
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function update() {

        $this->view = false;
        $this->layout_name = false;

        $outcome = self::evaluateUpdateRequest($_POST, $_SERVER, $_SESSION);
        if ($outcome['status'] !== 200) {
            self::sendEnvironmentUpdateError($outcome['status'], $outcome['body'], $outcome['headers']);
            return;
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = self::buildEnvironmentUpdateSql($outcome['update'], [$db, 'sql_real_escape_string']);
        $db->sql_query($sql);

        if ($db->sql_affected_rows() === 1) {
            echo "OK";
        } else {
            self::sendEnvironmentUpdateError(503, "Environment not updated");
        }
    }

    public static function evaluateUpdateRequest(array $post, array $server, array $session): array
    {
        return InlineEditRequest::evaluate(
            $post,
            $server,
            $session,
            self::ENVIRONMENT_UPDATE_CSRF_SCOPE,
            self::ENVIRONMENT_UPDATE_FIELDS,
            "Invalid environment update payload",
            self::ENVIRONMENT_UPDATE_VALUE_MAX_BYTES
        );
    }

    public static function normalizeUpdatePayload(array $post): ?array
    {
        return InlineEditRequest::normalize($post, self::ENVIRONMENT_UPDATE_FIELDS, self::ENVIRONMENT_UPDATE_VALUE_MAX_BYTES);
    }

    public static function buildEnvironmentUpdateSql(array $update, callable $escape): string
    {
        return sprintf(
            "UPDATE environment SET `%s` = '%s' WHERE id = %d",
            $update['field'],
            $escape($update['value']),
            $update['id']
        );
    }

    private static function sendEnvironmentUpdateError(int $statusCode, string $message, array $headers = []): void
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
 * Create environment state through `add`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for add.
 * @phpstan-return void
 * @psalm-return void
 * @see self::add()
 * @example /fr/environment/add
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function add($param) {
        $this->di['js']->addJavascript(array("bootstrap-select.min.js"));
        $db = Sgbd::sql(DB_DEFAULT);

        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $variable['environment'] = $_POST['environment'];

            //if ((empty($variable['environment']['libelle']))||(empty($variable['environment']['libelle']))||(empty($variable['environment']['libelle']))||(empty($variable['environment']['libelle'])))

            $return = $db->sql_save($variable);
            if (!$return) {
                $error = $db->sql_error();
                $_SESSION['ERROR'] = $error;

                $msg = "<ul><li>" . implode("</li><li>", $error['environment']) . "</li></ul>";

                set_flash("error", "Error", $msg);

                header("location: " . LINK . "environment/add/" . Post::getToPost());
            } else {
                debug($db->sql_error());
                //header("location: ".LINK."environment/index/");
            }
        }

        $colors = array("danger", "warning", "default", "info", "success", "primary");

        $data['colors'] = array();
        foreach ($colors as $color) {
            $temp = [];
            $temp['id'] = $color;
            $temp['libelle'] = $color;

            $temp['extra'] = array("data-content" => "<span title='" . $color . "' class='label label-" . $color . "'>" . strtoupper($color) . "</span>");

            $data['colors'][] = $temp;
        }

        $this->set('data', $data);
    }


/**
 * Delete environment state through `delete`.
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
 * @example /fr/environment/delete
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function delete($param) {
        
        $this->view = false;
        $id_environment = $param[0];

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "DELETE FROM environment WHERE id = '".$id_environment."' and id > 6";
        $db->sql_query($sql);

        header("location:". LINK . "environment/index/");
    }

}
