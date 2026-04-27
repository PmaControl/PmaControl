<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use App\Library\Tree as TreeInterval;
use App\Library\Debug;
use App\Library\Security\CsrfGuard;
use \Glial\Sgbd\Sgbd;
use Glial\Security\Csrf;

// https://codepen.io/gab/pen/Bxpwi

/**
 * Class responsible for tree workflows.
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
class Tree extends Controller
{
    private const TREE_UPDATE_CSRF_SCOPE = 'tree.update';
    private const TREE_UPDATE_FIELDS = ['icon', 'title', 'url', 'class', 'method', 'active'];

/**
 * Render tree state through `index`.
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
 * @example /fr/tree/index
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
        if (empty($param[0])) {
            $param[0] = 1;
        }

        $indexRequest = self::evaluateIndexRequest($_GET, $_SERVER);
        if ($indexRequest['status'] === 405) {
            $this->view        = false;
            $this->layout_name = false;
            self::sendTreeIndexError($indexRequest['status'], $indexRequest['body'], $indexRequest['headers']);
            return;
        }

        if ($indexRequest['menu_id'] !== null) {
            header('location: '.LINK.$this->getClass().'/'.__FUNCTION__.'/'.$indexRequest['menu_id']);
            return;
        }

        $this->di['js']->addJavascript(array('bootstrap-editable.min.js', 'Tree/index.js'));
        $this->di['js']->addJavascript(array('bootstrap-select.min.js'));

        $data['id_menu'] = $param[0];
        /*
          $this->di['js']->code_javascript('$(function () {  $(\'[data-toggle="popover"]\').popover({trigger:"hover"}) });');
          $this->di['js']->code_javascript('
          $(\'[data-toggle="popover"]\').each(function(index, element) {
          var contentElementId = $(element).data().target;
          var contentHtml = $(contentElementId).html();
          $(element).popover({
          content: contentHtml,
          trigger:"hover",
          html:true
          });
          });');
         */

        $db = Sgbd::sql(DB_DEFAULT);


        $sql = "select * from menu_group";

        $res = $db->sql_query($sql);


        $data['liste_menu'] = array();
        while ($ob                 = $db->sql_fetch_object($res)) {
            $tmp            = array();
            $tmp['id']      = $ob->id;
            $tmp['libelle'] = $ob->title;

            $data['liste_menu'][] = $tmp;
        }

        $sql2 = "SELECT * FROM menu WHERE group_id=".$data['id_menu']." ORDER BY bg ASC";
        $res2 = $db->sql_query($sql2);

        $data['menu'] = array();
        while ($ob           = $db->sql_fetch_array($res2, MYSQLI_ASSOC)) {

            $data['menu'][] = $ob;
        }

        $data['tree_update_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['tree_update_csrf_token'] = Csrf::issueToken($_SESSION, self::TREE_UPDATE_CSRF_SCOPE);

        $this->set('data', $data);
    }

    public static function evaluateIndexRequest(array $get, array $server): array
    {
        if (CsrfGuard::isPost($server)) {
            return self::buildTreeIndexOutcome(405, 'Method Not Allowed', ['Allow' => 'GET']);
        }

        $menuId = self::normalizeIndexMenuSelection($get);

        return [
            'status' => $menuId === null ? 200 : 302,
            'body' => '',
            'headers' => [],
            'menu_id' => $menuId,
        ];
    }

    public static function normalizeIndexMenuSelection(array $get): ?int
    {
        if (
            ! isset($get['menu'])
            || ! is_array($get['menu'])
            || ! array_key_exists('id', $get['menu'])
            || ! is_scalar($get['menu']['id'])
        ) {
            return null;
        }

        $id = (string) $get['menu']['id'];
        if (! ctype_digit($id) || (int) $id < 1) {
            return null;
        }

        return (int) $id;
    }

    private static function buildTreeIndexOutcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'menu_id' => null,
        ];
    }

    private static function sendTreeIndexError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        header('Content-Type: text/plain; charset=UTF-8');
        echo $message;
    }

/**
 * Delete tree state through `delete`.
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
 * @example /fr/tree/delete
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
        $db = Sgbd::sql(DB_DEFAULT);

        $id_menu = $param[0];
        $id      = $param[1];

        $tree = new TreeInterval($db, "menu", array(), array("group_id" => $id_menu));
        $tree->delete($id);

        header("location: ".LINK."tree/index/".$id_menu);
    }

/**
 * Create tree state through `add`.
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
 * @example /fr/tree/add
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function add($param)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $id_menu   = $param[0];
        $id_parent = $param[1];

        $tree = new TreeInterval($db, "menu", array("id_parent" => "parent_id"), array("group_id" => $id_menu));

        if ($_SERVER['REQUEST_METHOD'] === "POST") {

            $tree->add($_POST['menu'], $id_parent);
            header("location: ".LINK."tree/index/".$id_menu);
        }
    }

/**
 * Handle tree state through `up`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for up.
 * @phpstan-return void
 * @psalm-return void
 * @see self::up()
 * @example /fr/tree/up
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function up($param)
    {

        Debug::parseDebug($param);

        $db      = Sgbd::sql(DB_DEFAULT);
        $id_menu = $param[0];
        $id      = $param[1];
        $tree    = new TreeInterval($db, "menu", array("id_parent" => "parent_id"), array("group_id" => $id_menu));

        $tree->up($id);

        Debug::debugShowQueries($db); // <= ici

        header("location: ".LINK."tree/index/".$id_menu);
    }

/**
 * Update tree state through `update`.
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
 * @example /fr/tree/update
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
            self::sendTreeUpdateError($outcome['status'], $outcome['body'], $outcome['headers']);
            return;
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = self::buildTreeUpdateSql($outcome['update'], [$db, 'sql_real_escape_string']);
        $db->sql_query($sql);

        if ($db->sql_affected_rows() === 1) {
            echo "OK";
        } else {
            self::sendTreeUpdateError(503, "Tree menu not updated");
        }
    }

    public static function evaluateUpdateRequest(array $post, array $server, array $session): array
    {
        $guard = CsrfGuard::check($post, $server, $session, self::TREE_UPDATE_CSRF_SCOPE);
        if (!$guard['allowed']) {
            return self::buildTreeUpdateOutcome($guard['status'], $guard['body'], $guard['headers']);
        }

        $update = self::normalizeUpdatePayload($post);
        if ($update === null) {
            return self::buildTreeUpdateOutcome(400, "Invalid tree update payload");
        }

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'update' => $update,
        ];
    }

    public static function normalizeUpdatePayload(array $post): ?array
    {
        if (
            ! array_key_exists('name', $post)
            || ! array_key_exists('pk', $post)
            || ! array_key_exists('value', $post)
            || ! is_scalar($post['name'])
            || ! is_scalar($post['pk'])
            || ! is_scalar($post['value'])
        ) {
            return null;
        }

        $field = (string) $post['name'];
        $id = (string) $post['pk'];

        if (! in_array($field, self::TREE_UPDATE_FIELDS, true)) {
            return null;
        }

        if (! ctype_digit($id) || (int) $id < 1) {
            return null;
        }

        return [
            'field' => $field,
            'value' => (string) $post['value'],
            'id' => (int) $id,
        ];
    }

    public static function buildTreeUpdateSql(array $update, callable $escape): string
    {
        return sprintf(
            "UPDATE menu SET `%s` = '%s' WHERE id = %d",
            $update['field'],
            $escape($update['value']),
            $update['id']
        );
    }

    private static function buildTreeUpdateOutcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'update' => null,
        ];
    }

    private static function sendTreeUpdateError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        header('Content-Type: text/plain; charset=UTF-8');
        echo $message;
    }

/**
 * Retrieve tree state through `getCountFather`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for getCountFather.
 * @phpstan-return void
 * @psalm-return void
 * @see self::getCountFather()
 * @example /fr/tree/getCountFather
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getCountFather($param)
    {


        $id_menu = $param[0];
        $id      = $param[1];

        $db = Sgbd::sql(DB_DEFAULT);

        $tree = new TreeInterval($db, "menu", array("id_parent" => "parent_id"), array("group_id" => $id_menu));

        $data['cpt'] = $tree->countFather($id);


        $this->set('data', $data);
    }

/**
 * Handle tree state through `left`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for left.
 * @phpstan-return void
 * @psalm-return void
 * @see self::left()
 * @example /fr/tree/left
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function left($param)
    {
        $id_menu = $param[0];
        $id      = $param[1];
        $db      = Sgbd::sql(DB_DEFAULT);
        $tree    = new TreeInterval($db, "menu", array("id_parent" => "parent_id"), array("group_id" => $id_menu));

        $tree->left($id);
    }
}
