<?php

declare(strict_types=1);

use App\Controller\Mysqlsys;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

if (!defined('LOG_FILE')) {
    define('LOG_FILE', sys_get_temp_dir() . '/pmacontrol-test.log');
}

final class MysqlsysUpdateConfigSecurityTest extends TestCase
{
    public function testUpdateConfigRequestAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'mysqlsys.update_config');

        $outcome = Mysqlsys::evaluateUpdateConfigRequest(
            $this->validPost($token),
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertTrue($outcome['allowed']);
        $this->assertSame(
            [
                'id_mysql_server' => 12,
                'name' => 'statement_truncate_len',
                'value' => "O'Reilly",
            ],
            $outcome['config']
        );
    }

    public function testUpdateConfigRequestRejectsNonPost(): void
    {
        $outcome = Mysqlsys::evaluateUpdateConfigRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertFalse($outcome['allowed']);
        $this->assertNull($outcome['config']);
    }

    public function testUpdateConfigRequestRejectsExternalOriginBeforePayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'mysqlsys.update_config');

        $outcome = Mysqlsys::evaluateUpdateConfigRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'pk' => ['12'],
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
        $this->assertNull($outcome['config']);
    }

    public function testUpdateConfigRequestRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'mysqlsys.install');
        $server = $this->sameSitePostServer();

        $missingToken = Mysqlsys::evaluateUpdateConfigRequest($this->validPost(null), $server, $session);
        $foreignScope = Mysqlsys::evaluateUpdateConfigRequest($this->validPost($foreignToken), $server, $session);

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertFalse($missingToken['allowed']);
        $this->assertNull($missingToken['config']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
        $this->assertFalse($foreignScope['allowed']);
        $this->assertNull($foreignScope['config']);
    }

    public function testUpdateConfigRequestRejectsInvalidPayloadAfterValidCsrf(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'mysqlsys.update_config');
        $server = $this->sameSitePostServer();

        foreach ($this->invalidPayloads($token) as $post) {
            $outcome = Mysqlsys::evaluateUpdateConfigRequest($post, $server, $session);

            $this->assertSame(400, $outcome['status']);
            $this->assertSame('Invalid MySQL-sys config payload', $outcome['body']);
            $this->assertFalse($outcome['allowed']);
            $this->assertNull($outcome['config']);
        }
    }

    public function testUpdateConfigRequestAllowsCliWithoutCsrfToken(): void
    {
        $outcome = Mysqlsys::evaluateUpdateConfigRequest(
            $this->validPost(null),
            ['REQUEST_METHOD' => 'GET'],
            [],
            true
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertTrue($outcome['allowed']);
        $this->assertSame(12, $outcome['config']['id_mysql_server']);
    }

    public function testUpdateConfigSqlEscapesNameAndValue(): void
    {
        $sql = Mysqlsys::buildUpdateConfigSql(
            [
                'id_mysql_server' => 12,
                'name' => 'statement_truncate_len',
                'value' => "O'Reilly",
            ],
            static fn($value): string => addslashes((string) $value)
        );

        $this->assertSame(
            "UPDATE sys.sys_config SET `value` = 'O\\'Reilly' WHERE `variable` = 'statement_truncate_len'",
            $sql
        );
    }

    public function testIndexViewCarriesCsrfAttributesForInlineEdit(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Mysqlsys.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Mysqlsys/index.view.php');
        $javascript = (string) file_get_contents(__DIR__ . '/../../App/Webroot/js/Tree/index.js');

        $this->assertStringContainsString('use App\\Library\\Security\\CsrfGuard;', $controller);
        $this->assertStringContainsString('use Glial\\Security\\Csrf;', $controller);
        $this->assertStringContainsString("private const MYSQLSYS_UPDATE_CONFIG_CSRF_SCOPE = 'mysqlsys.update_config'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::MYSQLSYS_UPDATE_CONFIG_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, self::MYSQLSYS_UPDATE_CONFIG_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('evaluateUpdateConfigRequest($_POST, $_SERVER, $_SESSION, IS_CLI)', $controller);

        $this->assertStringContainsString('$mysqlsysUpdateConfigCsrfAttributes', $view);
        $this->assertStringContainsString("CsrfRender::attributes(\$data, 'mysqlsys_update_config')", $view);
        $this->assertStringContainsString("data-url=\"' . LINK . 'mysqlsys/updateConfig\"", $view);

        $this->assertStringContainsString('params[csrfField] = csrfToken;', $javascript);
    }

    private function validPost(?string $token): array
    {
        $post = [
            'pk' => '12',
            'name' => ' statement_truncate_len ',
            'value' => "O'Reilly",
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
            $this->withPayloadValue($base, 'pk', '0'),
            $this->withPayloadValue($base, 'pk', '12x'),
            $this->withPayloadValue($base, 'pk', ['12']),
            $this->withPayloadValue($base, 'name', ''),
            $this->withPayloadValue($base, 'name', 'statement truncate len'),
            $this->withPayloadValue($base, 'name', 'statement_truncate_len;DROP'),
            $this->withPayloadValue($base, 'name', ['statement_truncate_len']),
            $this->withPayloadValue($base, 'name', str_repeat('n', 129)),
            $this->withPayloadValue($base, 'value', ['1000']),
            $this->withPayloadValue($base, 'value', str_repeat('v', 4097)),
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
