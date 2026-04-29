<?php

namespace App\Controller;

use Glial\I18n\I18n;
use Glial\Synapse\Controller;
use App\Library\Http\HttpOutcome;
use App\Library\Http\HttpResponse;
use App\Library\Security\CsrfGuard;
use App\Library\Post;
use \Glial\Sgbd\Sgbd;
use Glial\Security\Csrf;

/*
 * Module pour gérer les tag sur les equipements pour les régrouper
 *
 *
 */
class Tag extends Controller {
    private const TAG_ADD_CSRF_SCOPE = 'tag.add';
    private const TAG_ADD_FIELDS = ['name', 'color', 'background'];
    private const TAG_ADD_FIELD_LIMITS = [
        'name' => 50,
        'color' => 20,
        'background' => 20,
    ];
    public const TAG_UPDATE_CSRF_SCOPE = 'tag.update';
    private const TAG_UPDATE_FIELDS = ['name', 'color', 'background'];

/**
 * Render tag state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $params Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $params
 * @psalm-param array<int,mixed> $params
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/tag/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function index($params) {


        /*
          $this->di['js']->addJavascript('https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js', 'colorpicker/Colorpicker.js');

          $this->di['js']->code_javascript("$(function () {
          // Basic instantiation:
          $('#demo').colorpicker();
          });");
         */

        $this->di['js']->addJavascript(array('bootstrap-editable.min.js', 'Tree/index.js'));

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM tag order by name";

        $res = $db->sql_query($sql);


        $data['tags'] = array();
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['tags'][] = $arr;
        }

        $data['tag_update_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['tag_update_csrf_token'] = Csrf::issueToken($_SESSION, self::TAG_UPDATE_CSRF_SCOPE);

        $this->set('data', $data);
    }

/**
 * Create tag state through `add`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for add.
 * @phpstan-return void
 * @psalm-return void
 * @see self::add()
 * @example /fr/tag/add
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function add() {
        if (CsrfGuard::isPost($_SERVER)) {
            $this->view = false;
            $this->layout_name = false;

            $outcome = self::evaluateAddRequest($_POST, $_SERVER, $_SESSION);
            if ($outcome['status'] !== 200) {
                self::sendTagAddError($outcome['status'], $outcome['body'], $outcome['headers']);
                return;
            }

            $db = Sgbd::sql(DB_DEFAULT);
            $save['tag'] = $outcome['tag'];

            $id_tag = $db->sql_save($save);

            if ($id_tag) {
                $msg = I18n::getTranslation(__("The tag has been added"));
                $title = I18n::getTranslation(__("Success"));
                set_flash("success", $title, $msg);

                $method = "index";
            } else {
                $msg = I18n::getTranslation(__("Impossible to add this tag : " . $db->sql_error()));
                $title = I18n::getTranslation(__("Error"));
                set_flash("error", $title, $msg);

                $method = __FUNCTION__;
            }


            header('location: ' . LINK .$this->getClass(). '/' . $method . '/' . Post::getToPost());
            return;
        }

        $data['tag_add_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['tag_add_csrf_token'] = Csrf::issueToken($_SESSION, self::TAG_ADD_CSRF_SCOPE);
        $this->set('data', $data);
    }

    public static function evaluateAddRequest(array $post, array $server, array $session): array
    {
        $guard = CsrfGuard::check($post, $server, $session, self::TAG_ADD_CSRF_SCOPE);
        if (!$guard['allowed']) {
            return HttpOutcome::fromGuard($guard, ['tag' => null]);
        }

        $tag = self::normalizeAddPayload($post);
        if ($tag === null) {
            return HttpOutcome::error(400, 'Invalid tag add payload', [], ['tag' => null]);
        }

        return HttpOutcome::ok(['tag' => $tag]);
    }

    public static function normalizeAddPayload(array $post): ?array
    {
        if (! isset($post['tag']) || ! is_array($post['tag']) || $post['tag'] === []) {
            return null;
        }

        $tag = [
            'color' => '',
            'background' => '',
        ];

        foreach ($post['tag'] as $field => $value) {
            if (! is_string($field) || ! in_array($field, self::TAG_ADD_FIELDS, true) || ! is_scalar($value)) {
                return null;
            }

            $text = trim((string) $value);
            if (strlen($text) > self::TAG_ADD_FIELD_LIMITS[$field]) {
                return null;
            }

            $tag[$field] = $text;
        }

        if (! isset($tag['name']) || $tag['name'] === '') {
            return null;
        }

        return [
            'name' => $tag['name'],
            'color' => $tag['color'],
            'background' => $tag['background'],
        ];
    }

    private static function sendTagAddError(int $statusCode, string $message, array $headers = []): void
    {
        HttpResponse::sendError($statusCode, $message, $headers);
    }

/**
 * Update tag state through `update`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @return void Returned value for update.
 * @phpstan-return void
 * @psalm-return void
 * @see self::update()
 * @example /fr/tag/update
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
            self::sendTagUpdateError($outcome['status'], $outcome['body'], $outcome['headers']);
            return;
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = self::buildTagUpdateSql($outcome['update'], [$db, 'sql_real_escape_string']);
        $db->sql_query($sql);

        if ($db->sql_affected_rows() === 1) {
            echo "OK";
        } else {
            self::sendTagUpdateError(503, "Tag not updated");
        }
    }

    public static function evaluateUpdateRequest(array $post, array $server, array $session): array
    {
        $guard = CsrfGuard::check($post, $server, $session, self::TAG_UPDATE_CSRF_SCOPE);
        if (!$guard['allowed']) {
            return HttpOutcome::fromGuard($guard, ['update' => null]);
        }

        $update = self::normalizeUpdatePayload($post);
        if ($update === null) {
            return HttpOutcome::error(400, "Invalid tag update payload", [], ['update' => null]);
        }

        return HttpOutcome::ok(['update' => $update]);
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

        if (! in_array($field, self::TAG_UPDATE_FIELDS, true)) {
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

    public static function buildTagUpdateSql(array $update, callable $escape): string
    {
        return sprintf(
            "UPDATE tag SET `%s` = '%s' WHERE id = %d",
            $update['field'],
            $escape($update['value']),
            $update['id']
        );
    }

    private static function sendTagUpdateError(int $statusCode, string $message, array $headers = []): void
    {
        HttpResponse::sendError($statusCode, $message, $headers);
    }

}
