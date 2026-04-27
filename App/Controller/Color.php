<?php

namespace App\Controller;

use \Glial\I18n\I18n;
use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use App\Library\Security\CsrfGuard;
use Glial\Security\Csrf;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;


/**
 * Class responsible for color workflows.
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
class Color extends Controller
{
    private const COLOR_INDEX_CSRF_SCOPE = 'color.index';
    private const DOT_STYLE_VALUES = [
        'solid',
        'dashed',
        'dotted',
        'bold',
        'invis',
        'filled',
        'rounded',
        'diagonals',
    ];

/**
 * Render color state through `index`.
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
 * @example /fr/color/index
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
        $this->di['js']->addJavascript(array('vendor/bootstrap-colorpicker-2.3.6.min.js'));
    
        $this->di['js']->code_javascript('$(".colorpicker").colorpicker({format: "hex"});');

        $db = Sgbd::sql(DB_DEFAULT);
        $selectedType = isset($param[0]) ? (string) $param[0] : '';

        // Keep in sync with `chk_style` constraint in `dot3_legend` model
        $dotStyleValues = self::DOT_STYLE_VALUES;

        if (CsrfGuard::isPost($_SERVER)) {
            $indexRequest = self::evaluateIndexRequest($_POST, $_SERVER, $_SESSION);
            if ($indexRequest['status'] !== 200) {
                $this->view = false;
                $this->layout_name = false;
                self::sendColorIndexError($indexRequest['status'], $indexRequest['body'], $indexRequest['headers']);
                return;
            }

            $updated = 0;

            foreach ($indexRequest['rows'] as $id => $legend) {
                $font = $legend['font'];
                $color = $legend['color'];
                $background = $legend['background'];
                $style = $legend['style'];

                $sql = "UPDATE dot3_legend SET
                        `font` = '".$db->sql_real_escape_string($font)."',
                        `color` = '".$db->sql_real_escape_string($color)."',
                        `background` = '".$db->sql_real_escape_string($background)."',
                        `style` = '".$db->sql_real_escape_string($style)."'
                    WHERE id = ".$id."";

                if ($db->sql_query($sql)) {
                    $updated++;
                }
            }

            $title = I18n::getTranslation(__("Success"));
            $msg = I18n::getTranslation(__("Legend updated."));

            if ($updated === 0) {
                $title = I18n::getTranslation(__("Warning"));
                $msg = I18n::getTranslation(__("No data has been updated."));
                set_flash("caution", $title, $msg);
            } else {
                set_flash("success", $title, $msg);
            }

            header("location: ".LINK."Color/index/".$selectedType."/");
            exit;
        }

        $sql = "SELECT * FROM dot3_legend ORDER BY `order`";

        $res = $db->sql_query($sql);

        $type = [];
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC))
        {
            $type[] = $arr['type'];

            $data['legend'][$arr['type']][$arr['const']] = $arr;
        }

        $data['type'] = array_unique($type);
        sort($data['type']);

        if (empty($selectedType) && !empty($data['type'])) {
            $selectedType = $data['type'][0];
        }

        $_GET['type'] = $selectedType;
        $data['dot_style_values'] = $dotStyleValues;
        $data['color_index_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['color_index_csrf_token'] = Csrf::issueToken($_SESSION, self::COLOR_INDEX_CSRF_SCOPE);

        $this->set('data', $data);
    }

    public static function evaluateIndexRequest(array $post, array $server, array $session): array
    {
        $guard = CsrfGuard::check($post, $server, $session, self::COLOR_INDEX_CSRF_SCOPE);
        if (!$guard['allowed']) {
            return self::buildIndexOutcome($guard['status'], $guard['body'], $guard['headers']);
        }

        $rows = self::normalizeIndexPayload($post);
        if ($rows === null) {
            return self::buildIndexOutcome(400, 'Invalid color legend payload');
        }

        return self::buildIndexOutcome(200, '', [], $rows);
    }

    public static function normalizeIndexPayload(array $post): ?array
    {
        if (!isset($post['dot3_legend']) || !is_array($post['dot3_legend']) || $post['dot3_legend'] === []) {
            return null;
        }

        if (count($post['dot3_legend']) > 500) {
            return null;
        }

        $rows = [];
        foreach ($post['dot3_legend'] as $id => $legend) {
            $id = is_int($id) ? (string) $id : (string) $id;
            if (!ctype_digit($id) || (int) $id < 1 || !is_array($legend)) {
                return null;
            }

            foreach ($legend as $field => $value) {
                if (!is_string($field) || !in_array($field, ['font', 'color', 'background', 'style'], true) || !is_scalar($value)) {
                    return null;
                }
            }

            foreach (['font', 'color', 'background', 'style'] as $field) {
                if (!array_key_exists($field, $legend)) {
                    return null;
                }
            }

            $font = self::normalizeLegendText((string) $legend['font']);
            $color = self::normalizeLegendText((string) $legend['color']);
            $background = self::normalizeLegendText((string) $legend['background']);
            $style = trim((string) $legend['style']);

            if (
                $font === null
                || $color === null
                || $background === null
                || !in_array($style, self::DOT_STYLE_VALUES, true)
            ) {
                return null;
            }

            $rows[(int) $id] = [
                'font' => strtoupper($font),
                'color' => strtoupper($color),
                'background' => strtoupper($background),
                'style' => $style,
            ];
        }

        return $rows;
    }

    private static function normalizeLegendText(string $value): ?string
    {
        $value = trim($value);
        if (strlen($value) > 32 || preg_match('/[\r\n\x00]/', $value)) {
            return null;
        }

        return $value;
    }

    private static function buildIndexOutcome(int $statusCode, string $message, array $headers = [], ?array $rows = null): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    private static function sendColorIndexError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        header('Content-Type: text/plain; charset=UTF-8');
        echo $message;
    }

}
