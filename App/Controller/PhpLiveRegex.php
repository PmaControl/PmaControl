<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use App\Library\Security\CsrfGuard;
use \Glial\Synapse\Controller;
use Glial\Security\Csrf;

/**
 * Class responsible for php live regex workflows.
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
class PhpLiveRegex extends Controller
{
    private const PHPLIVEREGEX_EVALUATE_CSRF_SCOPE = 'phpliveregex.evaluate';
    private const PHPLIVEREGEX_REGEX_MAX_LENGTH = 4096;
    private const PHPLIVEREGEX_OPTIONS_MAX_LENGTH = 16;
    private const PHPLIVEREGEX_REPLACEMENT_MAX_LENGTH = 8192;
    private const PHPLIVEREGEX_EXAMPLES_MAX_LENGTH = 65535;

/**
 * Render php live regex state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/phpliveregex/index
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


        $this->di['js']->addJavascript(array($this->getClass().'/index.js'));
        $this->set('data', array(
            'phpliveregex_evaluate_csrf_field' => Csrf::DEFAULT_FIELD,
            'phpliveregex_evaluate_csrf_token' => Csrf::issueToken($_SESSION, self::PHPLIVEREGEX_EVALUATE_CSRF_SCOPE),
        ));
    }

/**
 * Handle php live regex state through `evaluate`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for evaluate.
 * @phpstan-return void
 * @psalm-return void
 * @see self::evaluate()
 * @example /fr/phpliveregex/evaluate
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function evaluate($param)
    {
        $this->view        = false;
        $this->layout_name = false;

        $outcome = self::evaluateRequest($_POST, $_SERVER, $_SESSION, IS_CLI);
        if (!$outcome['allowed']) {
            http_response_code($outcome['status']);
            foreach ($outcome['headers'] as $name => $value) {
                header($name . ': ' . $value);
            }
            echo $outcome['body'];
            return;
        }

        $payload = $outcome['payload'];
        $fcts = $this->pregView($payload['regex_1'], $payload['regex_2'], $payload['replacement'], $payload['examples']);



        $ret = array();
        foreach ($fcts as $name => $data) {
            $ret[$name] = '<input class="form-control" onClick="this.focus();this.select();" type="text" value="'.$data['cmd'].'" readonly="">';
            $ret[$name] .= '<pre>'.print_r($data['data'], true).'</pre>';
        }

        echo json_encode($ret);

        //echo '{"preg_match":"<input class="form-control" onClick="this.focus();this.select();" type="text" value="preg_match(&quot;\/(.*), (.*)\/&quot;, $input_line, $output_array);" readonly=""><div class="data-structure"><!-- ref#0 --><div><div class="ref"><span data-input><\/span><span data-output><span data-array>array<\/span><i>(<\/i><span data-gLabel>3<\/span><span data-toggle data-exp><\/span><span data-group><span data-table><span data-row><span data-cell><span data-key data-tip="0">0<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="1">last_name, first_name<\/span><\/span><\/span><span data-row><span data-cell><span data-key data-tip="0">1<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="2">last_name<\/span><\/span><\/span><span data-row><span data-cell><span data-key data-tip="0">2<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="3">first_name<\/span><\/span><\/span><\/span><\/span><i>)<\/i><\/span><div><span data-row><span data-cell><span data-title>Key: integer<\/span><\/span><\/span><\/div><div><span data-row><span data-cell><span data-title>string(21)<\/span><\/span><\/span><\/div><div><span data-row><span data-cell><span data-title>string(9)<\/span><\/span><\/span><\/div><div><span data-row><span data-cell><span data-title>string(10)<\/span><\/span><\/span><\/div><\/div><\/div><!-- \/ref#1 --><!-- ref#1 --><div><div class="ref"><span data-input><\/span><span data-output><span data-array>array<\/span><i>(<\/i><span data-gLabel>3<\/span><span data-toggle data-exp><\/span><span data-group><span data-table><span data-row><span data-cell><span data-key data-tip="0">0<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="1">bjorge, philip<\/span><\/span><\/span><span data-row><span data-cell><span data-key data-tip="0">1<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="2">bjorge<\/span><\/span><\/span><span data-row><span data-cell><span data-key data-tip="0">2<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="2">philip<\/span><\/span><\/span><\/span><\/span><i>)<\/i><\/span><div><span data-row><span data-cell><span data-title>Key: integer<\/span><\/span><\/span><\/div><div><span data-row><span data-cell><span data-title>string(14)<\/span><\/span><\/span><\/div><div><span data-row><span data-cell><span data-title>string(6)<\/span><\/span><\/span><\/div><\/div><\/div><!-- \/ref#2 --><!-- ref#2 --><div><div class="ref"><span data-input><\/span><span data-output><span data-array>array<\/span><i>(<\/i><span data-gLabel>3<\/span><span data-toggle data-exp><\/span><span data-group><span data-table><span data-row><span data-cell><span data-key data-tip="0">0<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="1">kardashian, kim<\/span><\/span><\/span><span data-row><span data-cell><span data-key data-tip="0">1<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="2">kardashian<\/span><\/span><\/span><span data-row><span data-cell><span data-key data-tip="0">2<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="3">kim<\/span><\/span><\/span><\/span><\/span><i>)<\/i><\/span><div><span data-row><span data-cell><span data-title>Key: integer<\/span><\/span><\/span><\/div><div><span data-row><span data-cell><span data-title>string(15)<\/span><\/span><\/span><\/div><div><span data-row><span data-cell><span data-title>string(10)<\/span><\/span><\/span><\/div><div><span data-row><span data-cell><span data-title>string(3)<\/span><\/span><\/span><\/div><\/div><\/div><!-- \/ref#3 --><!-- ref#3 --><div><div class="ref"><span data-input><\/span><span data-output><span data-array>array<\/span><i>(<\/i><span data-gLabel>3<\/span><span data-toggle data-exp><\/span><span data-group><span data-table><span data-row><span data-cell><span data-key data-tip="0">0<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="1">mercury, freddie<\/span><\/span><\/span><span data-row><span data-cell><span data-key data-tip="0">1<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="2">mercury<\/span><\/span><\/span><span data-row><span data-cell><span data-key data-tip="0">2<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="2">freddie<\/span><\/span><\/span><\/span><\/span><i>)<\/i><\/span><div><span data-row><span data-cell><span data-title>Key: integer<\/span><\/span><\/span><\/div><div><span data-row><span data-cell><span data-title>string(16)<\/span><\/span><\/span><\/div><div><span data-row><span data-cell><span data-title>string(7)<\/span><\/span><\/span><\/div><\/div><\/div><!-- \/ref#4 --><!-- ref#4 --><div><div class="ref"><span data-input><\/span><span data-output><span data-array>array<\/span><i>(<\/i><i>)<\/i><\/span><\/div><\/div><!-- \/ref#5 --><!-- ref#5 --><div><div class="ref"><span data-input><\/span><span data-output><span data-array>array<\/span><i>(<\/i><i>)<\/i><\/span><\/div><\/div><!-- \/ref#6 --><\/div><p><strong>note:<\/strong> preg_match is run on each line of input.<\/p>","preg_match_all":"<input class="form-control" onClick="this.focus();this.select();" type="text" value="preg_match_all(&quot;\/(.*), (.*)\/&quot;, $input_lines, $output_array);" readonly=""><div class="data-structure"><!-- ref#6 --><div><div class="ref"><span data-input><\/span><span data-output><span data-array>array<\/span><i>(<\/i><span data-gLabel>3<\/span><span data-toggle data-exp><\/span><span data-group><span data-table><span data-row><span data-cell><span data-key data-tip="0">0<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-array>array<\/span><i>(<\/i><span data-gLabel>4<\/span><span data-toggle data-exp><\/span><span data-group><span data-table><span data-row><span data-cell><span data-key data-tip="0">0<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="1">last_name, first_name<\/span><\/span><\/span><span data-row><span data-cell><span data-key data-tip="0">1<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="2">bjorge, philip<\/span><\/span><\/span><span data-row><span data-cell><span data-key data-tip="0">2<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="3">kardashian, kim<\/span><\/span><\/span><span data-row><span data-cell><span data-key data-tip="0">3<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="4">mercury, freddie<\/span><\/span><\/span><\/span><\/span><i>)<\/i><\/span><\/span><span data-row><span data-cell><span data-key data-tip="0">1<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-array>array<\/span><i>(<\/i><span data-gLabel>4<\/span><span data-toggle data-exp><\/span><span data-group><span data-table><span data-row><span data-cell><span data-key data-tip="0">0<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="5">last_name<\/span><\/span><\/span><span data-row><span data-cell><span data-key data-tip="0">1<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="6">bjorge<\/span><\/span><\/span><span data-row><span data-cell><span data-key data-tip="0">2<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="7">kardashian<\/span><\/span><\/span><span data-row><span data-cell><span data-key data-tip="0">3<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="8">mercury<\/span><\/span><\/span><\/span><\/span><i>)<\/i><\/span><\/span><span data-row><span data-cell><span data-key data-tip="0">2<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-array>array<\/span><i>(<\/i><span data-gLabel>4<\/span><span data-toggle data-exp><\/span><span data-group><span data-table><span data-row><span data-cell><span data-key data-tip="0">0<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="7">first_name<\/span><\/span><\/span><span data-row><span data-cell><span data-key data-tip="0">1<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="6">philip<\/span><\/span><\/span><span data-row><span data-cell><span data-key data-tip="0">2<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="9">kim<\/span><\/span><\/span><span data-row><span data-cell><span data-key data-tip="0">3<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="8">freddie<\/span><\/span><\/span><\/span><\/span><i>)<\/i><\/span><\/span><\/span><\/span><i>)<\/i><\/span><div><span data-row><span data-cell><span data-title>Key: integer<\/span><\/span><\/span><\/div><div><span data-row><span data-cell><span data-title>string(21)<\/span><\/span><\/span><\/div><div><span data-row><span data-cell><span data-title>string(14)<\/span><\/span><\/span><\/div><div><span data-row><span data-cell><span data-title>string(15)<\/span><\/span><\/span><\/div><div><span data-row><span data-cell><span data-title>string(16)<\/span><\/span><\/span><\/div><div><span data-row><span data-cell><span data-title>string(9)<\/span><\/span><\/span><\/div><div><span data-row><span data-cell><span data-title>string(6)<\/span><\/span><\/span><\/div><div><span data-row><span data-cell><span data-title>string(10)<\/span><\/span><\/span><\/div><div><span data-row><span data-cell><span data-title>string(7)<\/span><\/span><\/span><\/div><div><span data-row><span data-cell><span data-title>string(3)<\/span><\/span><\/span><\/div><\/div><\/div><!-- \/ref#7 --><\/div>","preg_replace":"<input class="form-control" onClick="this.focus();this.select();" type="text" value="preg_replace(&quot;\/(.*), (.*)\/&quot;, &quot;$0 --&gt; $2 $1&quot;, $input_lines);" readonly=""><div class="data-structure"><!-- ref#7 --><div><div class="ref"><span data-input><\/span><span data-output><span data-string data-tip="0">last_name, first_name --&gt; first_name last_name\nbjorge, philip --&gt; philip bjorge\nkardashian, kim --&gt; kim kardashian\nmercury, freddie --&gt; freddie mercury\n\nxfgnxfgnxfgnfgnngf<\/span><\/span><div><span data-row><span data-cell><span data-title>string(171)<\/span><\/span><\/span><\/div><\/div><\/div><!-- \/ref#8 --><\/div>","preg_filter":"","preg_grep":"<input class="form-control" onClick="this.focus();this.select();" type="text" value="preg_grep(&quot;\/(.*), (.*)\/&quot;, explode(&quot;\\n&quot;, $input_lines));" readonly=""><div class="data-structure"><!-- ref#8 --><div><div class="ref"><span data-input><\/span><span data-output><span data-array>array<\/span><i>(<\/i><span data-gLabel>4<\/span><span data-toggle data-exp><\/span><span data-group><span data-table><span data-row><span data-cell><span data-key data-tip="0">0<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="1">last_name, first_name<\/span><\/span><\/span><span data-row><span data-cell><span data-key data-tip="0">1<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="2">bjorge, philip<\/span><\/span><\/span><span data-row><span data-cell><span data-key data-tip="0">2<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="3">kardashian, kim<\/span><\/span><\/span><span data-row><span data-cell><span data-key data-tip="0">3<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="4">mercury, freddie<\/span><\/span><\/span><\/span><\/span><i>)<\/i><\/span><div><span data-row><span data-cell><span data-title>Key: integer<\/span><\/span><\/span><\/div><div><span data-row><span data-cell><span data-title>string(21)<\/span><\/span><\/span><\/div><div><span data-row><span data-cell><span data-title>string(14)<\/span><\/span><\/span><\/div><div><span data-row><span data-cell><span data-title>string(15)<\/span><\/span><\/span><\/div><div><span data-row><span data-cell><span data-title>string(16)<\/span><\/span><\/span><\/div><\/div><\/div><!-- \/ref#9 --><\/div>","preg_split":"<input class="form-control" onClick="this.focus();this.select();" type="text" value="preg_split(&quot;\/(.*), (.*)\/&quot;, $input_line);" readonly=""><div class="data-structure"><!-- ref#9 --><div><div class="ref"><span data-input><\/span><span data-output><span data-array>array<\/span><i>(<\/i><span data-gLabel>2<\/span><span data-toggle data-exp><\/span><span data-group><span data-table><span data-row><span data-cell><span data-key data-tip="0">0<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="1"><\/span><\/span><\/span><span data-row><span data-cell><span data-key data-tip="0">1<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="1"><\/span><\/span><\/span><\/span><\/span><i>)<\/i><\/span><div><span data-row><span data-cell><span data-title>Key: integer<\/span><\/span><\/span><\/div><div><span data-row><span data-cell><span data-title>string(0)<\/span><\/span><\/span><\/div><\/div><\/div><!-- \/ref#10 --><!-- ref#10 --><div><div class="ref"><span data-input><\/span><span data-output><span data-array>array<\/span><i>(<\/i><span data-gLabel>2<\/span><span data-toggle data-exp><\/span><span data-group><span data-table><span data-row><span data-cell><span data-key data-tip="0">0<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="1"><\/span><\/span><\/span><span data-row><span data-cell><span data-key data-tip="0">1<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="1"><\/span><\/span><\/span><\/span><\/span><i>)<\/i><\/span><div><span data-row><span data-cell><span data-title>Key: integer<\/span><\/span><\/span><\/div><div><span data-row><span data-cell><span data-title>string(0)<\/span><\/span><\/span><\/div><\/div><\/div><!-- \/ref#11 --><!-- ref#11 --><div><div class="ref"><span data-input><\/span><span data-output><span data-array>array<\/span><i>(<\/i><span data-gLabel>2<\/span><span data-toggle data-exp><\/span><span data-group><span data-table><span data-row><span data-cell><span data-key data-tip="0">0<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="1"><\/span><\/span><\/span><span data-row><span data-cell><span data-key data-tip="0">1<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="1"><\/span><\/span><\/span><\/span><\/span><i>)<\/i><\/span><div><span data-row><span data-cell><span data-title>Key: integer<\/span><\/span><\/span><\/div><div><span data-row><span data-cell><span data-title>string(0)<\/span><\/span><\/span><\/div><\/div><\/div><!-- \/ref#12 --><!-- ref#12 --><div><div class="ref"><span data-input><\/span><span data-output><span data-array>array<\/span><i>(<\/i><span data-gLabel>2<\/span><span data-toggle data-exp><\/span><span data-group><span data-table><span data-row><span data-cell><span data-key data-tip="0">0<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="1"><\/span><\/span><\/span><span data-row><span data-cell><span data-key data-tip="0">1<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="1"><\/span><\/span><\/span><\/span><\/span><i>)<\/i><\/span><div><span data-row><span data-cell><span data-title>Key: integer<\/span><\/span><\/span><\/div><div><span data-row><span data-cell><span data-title>string(0)<\/span><\/span><\/span><\/div><\/div><\/div><!-- \/ref#13 --><!-- ref#13 --><div><div class="ref"><span data-input><\/span><span data-output><span data-array>array<\/span><i>(<\/i><span data-gLabel>1<\/span><span data-toggle data-exp><\/span><span data-group><span data-table><span data-row><span data-cell><span data-key data-tip="0">0<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="1"><\/span><\/span><\/span><\/span><\/span><i>)<\/i><\/span><div><span data-row><span data-cell><span data-title>Key: integer<\/span><\/span><\/span><\/div><div><span data-row><span data-cell><span data-title>string(0)<\/span><\/span><\/span><\/div><\/div><\/div><!-- \/ref#14 --><!-- ref#14 --><div><div class="ref"><span data-input><\/span><span data-output><span data-array>array<\/span><i>(<\/i><span data-gLabel>1<\/span><span data-toggle data-exp><\/span><span data-group><span data-table><span data-row><span data-cell><span data-key data-tip="0">0<\/span><\/span><span data-cell><i>=&gt;<\/i><\/span><span data-cell><span data-string data-tip="1">xfgnxfgnxfgnfgnngf<\/span><\/span><\/span><\/span><\/span><i>)<\/i><\/span><div><span data-row><span data-cell><span data-title>Key: integer<\/span><\/span><\/span><\/div><div><span data-row><span data-cell><span data-title>string(18)<\/span><\/span><\/span><\/div><\/div><\/div><!-- \/ref#15 --><\/div><p><strong>note:<\/strong> preg_split_result is run on each line of input.<\/p>"}';
    }

    public static function evaluateRequest(array $post, array $server, array $session, bool $isCli = false): array
    {
        if (!$isCli) {
            $guard = CsrfGuard::check($post, $server, $session, self::PHPLIVEREGEX_EVALUATE_CSRF_SCOPE);
            if (!$guard['allowed']) {
                return [
                    'allowed' => false,
                    'status' => $guard['status'],
                    'body' => $guard['body'],
                    'headers' => $guard['headers'],
                    'payload' => null,
                ];
            }
        }

        $payload = self::normalizeEvaluatePayload($post);
        if ($payload === null) {
            return [
                'allowed' => false,
                'status' => 400,
                'body' => 'Invalid PHP live regex payload',
                'headers' => [],
                'payload' => null,
            ];
        }

        return [
            'allowed' => true,
            'status' => 200,
            'body' => '',
            'headers' => [],
            'payload' => $payload,
        ];
    }

    public static function normalizeEvaluatePayload(array $post): ?array
    {
        foreach (['regex_1', 'regex_2', 'replacement', 'examples'] as $field) {
            if (!array_key_exists($field, $post) || !is_scalar($post[$field])) {
                return null;
            }
        }

        $regex = self::normalizeEvaluateText($post['regex_1'], self::PHPLIVEREGEX_REGEX_MAX_LENGTH);
        $options = self::normalizeEvaluateOptions($post['regex_2']);
        $replacement = self::normalizeEvaluateText($post['replacement'], self::PHPLIVEREGEX_REPLACEMENT_MAX_LENGTH);
        $examples = self::normalizeEvaluateText($post['examples'], self::PHPLIVEREGEX_EXAMPLES_MAX_LENGTH);

        if ($regex === null || $options === null || $replacement === null || $examples === null) {
            return null;
        }

        return [
            'regex_1' => $regex,
            'regex_2' => $options,
            'replacement' => $replacement,
            'examples' => $examples,
        ];
    }

    private static function normalizeEvaluateText($value, int $maxLength): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $text = (string) $value;
        if (strlen($text) > $maxLength) {
            return null;
        }

        return $text;
    }

    private static function normalizeEvaluateOptions($value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $options = trim((string) $value);
        if (
            strlen($options) > self::PHPLIVEREGEX_OPTIONS_MAX_LENGTH
            || preg_match('/^[imsxADUuJ]*$/', $options) !== 1
        ) {
            return null;
        }

        return $options;
    }

/**
 * Handle php live regex state through `pregView`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $regex Input value for `regex`.
 * @phpstan-param mixed $regex
 * @psalm-param mixed $regex
 * @param mixed $options Input value for `options`.
 * @phpstan-param mixed $options
 * @psalm-param mixed $options
 * @param mixed $replace Input value for `replace`.
 * @phpstan-param mixed $replace
 * @psalm-param mixed $replace
 * @param array<int|string,mixed> $data Input value for `data`.
 * @phpstan-param array<int|string,mixed> $data
 * @psalm-param array<int|string,mixed> $data
 * @return mixed Returned value for pregView.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::pregView()
 * @example /fr/phpliveregex/pregView
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function pregView($regex, $options, $replace, $data)
    {
        // to prevent debug, fucking our json
        $_GET['ajax'] = true;

        $preg['preg_match']['cmd']     = "preg_match('/".htmlentities(str_replace("'", "\'", $regex))."/".$options."', \$input_line, \$output_array);";
        $preg['preg_match_all']['cmd'] = "preg_match_all('/".htmlentities(str_replace("'", "\'", $regex))."/".$options."', \$input_line, \$output_array);";
        $preg['preg_replace']['cmd']   = "\$result = preg_replace('/".htmlentities(str_replace("'", "\'", $regex))."/".$options."','".$replace."' ,\$input_line);";
        $preg['preg_grep']['cmd']      = "preg_replace('/".str_replace("'", "\'", $regex)."/".$options."','\$replace' ,\$input_line);";
        $preg['preg_split']['cmd']     = "preg_replace('/".str_replace("'", "\'", $regex)."/".$options."','\$replace' ,\$input_line);";



        $lines = explode("\n", $data);


        $preg['preg_match']['data'] = '';

        foreach ($lines as $line) {

            $output_array = array();
            preg_match("/".$regex."/".$options, $line, $output_array);


            $gg = array_map("htmlentities", $output_array);

            $preg['preg_match']['data'] .= print_r($gg, true);
        }

        $output_array                   = array();
        preg_match_all("/".$regex."/".$options, $data, $output_array);
        $preg['preg_match_all']['data'] = $output_array;



        foreach ($preg['preg_match_all']['data'] as $key => $value) {
            $preg['preg_match_all']['data'][$key] = array_map("htmlentities", $preg['preg_match_all']['data'][$key]);
        }




        $out = preg_replace('/'.$regex.'/'.$options, $replace, $data);

        $preg['preg_replace']['data'] = htmlentities($out);


        $preg['preg_grep']['data']  = "dfgdfsgf";
        $preg['preg_split']['data'] = "dfgdfsgf";


        return $preg;
    }
}
