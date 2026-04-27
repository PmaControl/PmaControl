<?php

declare(strict_types=1);

use App\Controller\PhpLiveRegex;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

if (!defined('LOG_FILE')) {
    define('LOG_FILE', sys_get_temp_dir() . '/pmacontrol-test.log');
}

final class PhpLiveRegexEvaluateSecurityTest extends TestCase
{
    public function testEvaluateRequestAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'phpliveregex.evaluate');

        $outcome = PhpLiveRegex::evaluateRequest(
            $this->validPost($token),
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertTrue($outcome['allowed']);
        $this->assertSame(
            [
                'regex_1' => '(.*), (.*)',
                'regex_2' => 'im',
                'replacement' => '$2 $1',
                'examples' => "last, first\nsecond, line",
            ],
            $outcome['payload']
        );
    }

    public function testEvaluateRequestRejectsNonPost(): void
    {
        $outcome = PhpLiveRegex::evaluateRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertFalse($outcome['allowed']);
        $this->assertNull($outcome['payload']);
    }

    public function testEvaluateRequestRejectsExternalOriginBeforePayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'phpliveregex.evaluate');

        $outcome = PhpLiveRegex::evaluateRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'regex_1' => ['invalid'],
            ],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertFalse($outcome['allowed']);
        $this->assertNull($outcome['payload']);
    }

    public function testEvaluateRequestRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'proxysql.add');
        $server = $this->sameSitePostServer();

        $missingToken = PhpLiveRegex::evaluateRequest($this->validPost(null), $server, $session);
        $foreignScope = PhpLiveRegex::evaluateRequest($this->validPost($foreignToken), $server, $session);

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertFalse($missingToken['allowed']);
        $this->assertNull($missingToken['payload']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
        $this->assertFalse($foreignScope['allowed']);
        $this->assertNull($foreignScope['payload']);
    }

    public function testEvaluateRequestRejectsInvalidPayloadAfterValidCsrf(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'phpliveregex.evaluate');
        $server = $this->sameSitePostServer();

        foreach ($this->invalidPayloads($token) as $post) {
            $outcome = PhpLiveRegex::evaluateRequest($post, $server, $session);

            $this->assertSame(400, $outcome['status']);
            $this->assertSame('Invalid PHP live regex payload', $outcome['body']);
            $this->assertFalse($outcome['allowed']);
            $this->assertNull($outcome['payload']);
        }
    }

    public function testEvaluateRequestAllowsCliWithoutCsrfToken(): void
    {
        $outcome = PhpLiveRegex::evaluateRequest(
            $this->validPost(null),
            ['REQUEST_METHOD' => 'GET'],
            [],
            true
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertTrue($outcome['allowed']);
        $this->assertSame('(.*), (.*)', $outcome['payload']['regex_1']);
    }

    public function testIndexViewExposesCsrfTokenToJavascript(): void
    {
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/PhpLiveRegex/index.view.php');

        $this->assertStringContainsString('$phpLiveRegexCsrfField', $view);
        $this->assertStringContainsString('$phpLiveRegexCsrfToken', $view);
        $this->assertStringContainsString('id="php-live-regex"', $view);
        $this->assertStringContainsString('data-csrf-field="<?= $phpLiveRegexCsrfField ?>"', $view);
        $this->assertStringContainsString('data-csrf-token="<?= $phpLiveRegexCsrfToken ?>"', $view);
    }

    public function testJavascriptAddsCsrfTokenToAjaxPost(): void
    {
        $javascript = (string) file_get_contents(__DIR__ . '/../../App/Webroot/js/PhpLiveRegex/index.js');

        $this->assertStringContainsString('function getPhpLiveRegexPayload()', $javascript);
        $this->assertStringContainsString('document.getElementById("php-live-regex")', $javascript);
        $this->assertStringContainsString('getAttribute("data-csrf-field")', $javascript);
        $this->assertStringContainsString('getAttribute("data-csrf-token")', $javascript);
        $this->assertStringContainsString('payload[csrfField] = csrfToken;', $javascript);
        $this->assertStringContainsString('getPhpLiveRegexPayload()', $javascript);
    }

    public function testControllerUsesScopedCsrfGuardBeforeRegexEvaluation(): void
    {
        $source = (string) file_get_contents(__DIR__ . '/../../App/Controller/PhpLiveRegex.php');

        $this->assertStringContainsString("private const PHPLIVEREGEX_EVALUATE_CSRF_SCOPE = 'phpliveregex.evaluate'", $source);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::PHPLIVEREGEX_EVALUATE_CSRF_SCOPE)', $source);
        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, self::PHPLIVEREGEX_EVALUATE_CSRF_SCOPE)', $source);
        $this->assertStringContainsString('evaluateRequest($_POST, $_SERVER, $_SESSION, IS_CLI)', $source);
        $this->assertStringContainsString('$fcts = $this->pregView($payload[\'regex_1\']', $source);
        $this->assertStringNotContainsString('file_put_contents("/tmp/gg"', $source);
    }

    private function validPost(?string $token): array
    {
        $post = [
            'regex_1' => '(.*), (.*)',
            'regex_2' => ' im ',
            'replacement' => '$2 $1',
            'examples' => "last, first\nsecond, line",
        ];

        if ($token !== null) {
            $post[Csrf::DEFAULT_FIELD] = $token;
        }

        return $post;
    }

    private function invalidPayloads(string $token): array
    {
        $base = $this->validPost($token);

        return [
            [Csrf::DEFAULT_FIELD => $token],
            $this->withPayloadValue($base, 'regex_1', ['(.*)']),
            $this->withPayloadValue($base, 'regex_1', str_repeat('r', 4097)),
            $this->withPayloadValue($base, 'regex_2', ['i']),
            $this->withPayloadValue($base, 'regex_2', 'e'),
            $this->withPayloadValue($base, 'regex_2', str_repeat('i', 17)),
            $this->withPayloadValue($base, 'replacement', ['replace']),
            $this->withPayloadValue($base, 'replacement', str_repeat('r', 8193)),
            $this->withPayloadValue($base, 'examples', ['example']),
            $this->withPayloadValue($base, 'examples', str_repeat('e', 65536)),
        ];
    }

    private function withPayloadValue(array $post, string $field, $value): array
    {
        $post[$field] = $value;
        return $post;
    }

    private function sameSitePostServer(): array
    {
        return [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://pmacontrol.test',
        ];
    }
}
