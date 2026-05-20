<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use App\Library\Tree as TreeInterval;
use App\Library\Debug;
use App\Library\Security\CsrfGuard;
use App\Library\Security\InlineEditRequest;
use App\Library\Security\PositiveIntegerSelection;
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
    private const TREE_ADD_CSRF_SCOPE = 'tree.add';
    private const TREE_ADD_FIELDS = ['title', 'url', 'icon', 'class', 'method'];
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
        $idMenu = self::normalizeRoutePositiveInteger($param, 0, 1);
        if ($idMenu === null) {
            $this->rejectInvalidTreeRoute();
            return;
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

        $data['id_menu'] = $idMenu;
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

        return PositiveIntegerSelection::normalizeSingle($get['menu']['id']);
    }

    public static function normalizeRoutePositiveInteger($param, int $offset, ?int $fallback = null): ?int
    {
        $routeParams = self::normalizeRouteParameters($param);
        if (! array_key_exists($offset, $routeParams) || $routeParams[$offset] === '') {
            return $fallback;
        }

        return PositiveIntegerSelection::normalizeSingle($routeParams[$offset]);
    }

    /**
     * @return int|string|null Returns the legacy string NULL for root node creation.
     */
    public static function normalizeRouteTreeParentId($param, int $offset)
    {
        $routeParams = self::normalizeRouteParameters($param);
        if (! array_key_exists($offset, $routeParams)) {
            return null;
        }

        if (is_scalar($routeParams[$offset]) && strtoupper(trim((string) $routeParams[$offset])) === 'NULL') {
            return 'NULL';
        }

        return self::normalizeRoutePositiveInteger($routeParams, $offset);
    }

    private static function normalizeRouteParameters($param): array
    {
        if (is_array($param)) {
            return $param;
        }

        if ($param === null || $param === '') {
            return [];
        }

        if (is_scalar($param)) {
            return [$param];
        }

        return [];
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

    private function rejectInvalidTreeRoute(): void
    {
        $this->view        = false;
        $this->layout_name = false;

        self::sendTreeRouteError(400, 'Invalid tree route');
    }

    private static function sendTreeRouteError(int $statusCode, string $message): void
    {
        http_response_code($statusCode);
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
        $id_menu = self::normalizeRoutePositiveInteger($param, 0);
        $id      = self::normalizeRoutePositiveInteger($param, 1);
        if ($id_menu === null || $id === null) {
            $this->rejectInvalidTreeRoute();
            return;
        }

        $db = Sgbd::sql(DB_DEFAULT);

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
        $id_menu   = self::normalizeRoutePositiveInteger($param, 0);
        $id_parent = self::normalizeRouteTreeParentId($param, 1);
        if ($id_menu === null || $id_parent === null) {
            $this->rejectInvalidTreeRoute();
            return;
        }

        if (CsrfGuard::isPost($_SERVER)) {
            $this->view        = false;
            $this->layout_name = false;

            $outcome = self::evaluateAddRequest($_POST, $_SERVER, $_SESSION);
            if ($outcome['status'] !== 200) {
                self::sendTreeAddError($outcome['status'], $outcome['body'], $outcome['headers']);
                return;
            }

            $db = Sgbd::sql(DB_DEFAULT);
            $tree = new TreeInterval($db, "menu", array("id_parent" => "parent_id"), array("group_id" => $id_menu));
            $tree->add($outcome['menu'], $id_parent);
            header("location: ".LINK."tree/index/".$id_menu);
            return;
        }

        $data['tree_add_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['tree_add_csrf_token'] = Csrf::issueToken($_SESSION, self::TREE_ADD_CSRF_SCOPE);

        $this->set('data', $data);
    }

    public static function evaluateAddRequest(array $post, array $server, array $session): array
    {
        if ($failure = CsrfGuard::ensureOrFail($post, $server, $session, self::TREE_ADD_CSRF_SCOPE)) {
            return self::buildTreeAddOutcome($failure['status'], $failure['body'], $failure['headers']);
        }

        $menu = self::normalizeAddPayload($post);
        if ($menu === null) {
            return self::buildTreeAddOutcome(400, 'Invalid tree add payload');
        }

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'menu' => $menu,
        ];
    }

    public static function normalizeAddPayload(array $post): ?array
    {
        if (! isset($post['menu']) || ! is_array($post['menu']) || $post['menu'] === []) {
            return null;
        }

        $menu = [];
        foreach ($post['menu'] as $field => $value) {
            if (! is_string($field) || ! in_array($field, self::TREE_ADD_FIELDS, true) || ! is_scalar($value)) {
                return null;
            }

            $menu[$field] = (string) $value;
        }

        return $menu === [] ? null : $menu;
    }

    private static function buildTreeAddOutcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'menu' => null,
        ];
    }

    private static function sendTreeAddError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        header('Content-Type: text/plain; charset=UTF-8');
        echo $message;
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

        $id_menu = self::normalizeRoutePositiveInteger($param, 0);
        $id      = self::normalizeRoutePositiveInteger($param, 1);
        if ($id_menu === null || $id === null) {
            $this->rejectInvalidTreeRoute();
            return;
        }
        $db      = Sgbd::sql(DB_DEFAULT);
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
        if ($failure = CsrfGuard::ensureOrFail($post, $server, $session, self::TREE_UPDATE_CSRF_SCOPE)) {
            return self::buildTreeUpdateOutcome($failure['status'], $failure['body'], $failure['headers']);
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
        return InlineEditRequest::normalize($post, self::TREE_UPDATE_FIELDS, PHP_INT_MAX);
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


        $id_menu = self::normalizeRoutePositiveInteger($param, 0);
        $id      = self::normalizeRoutePositiveInteger($param, 1);
        if ($id_menu === null || $id === null) {
            $this->rejectInvalidTreeRoute();
            return;
        }

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
        $id_menu = self::normalizeRoutePositiveInteger($param, 0);
        $id      = self::normalizeRoutePositiveInteger($param, 1);
        if ($id_menu === null || $id === null) {
            $this->rejectInvalidTreeRoute();
            return;
        }
        $db      = Sgbd::sql(DB_DEFAULT);
        $tree    = new TreeInterval($db, "menu", array("id_parent" => "parent_id"), array("group_id" => $id_menu));

        $tree->left($id);
    }
}
