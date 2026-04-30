<?php

declare(strict_types=1);

use App\Controller\ProxySQL;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class ProxySqlUpdateFieldSecurityTest extends TestCase
{
    public function testUpdateFieldRequestAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'proxysql.update_field');

        $outcome = ProxySQL::evaluateUpdateFieldRequest(
            ['5', 'mysql_users'],
            $this->validPost($token),
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertTrue($outcome['allowed']);
        $this->assertSame(
            [
                'id_proxysql_server' => 5,
                'table' => 'mysql_users',
                'field' => 'active',
                'value' => '1',
                'pk' => "username = 'app' AND frontend = '1'",
            ],
            $outcome['update']
        );
    }

    public function testUpdateFieldRequestRejectsNonPost(): void
    {
        $outcome = ProxySQL::evaluateUpdateFieldRequest([], [], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertFalse($outcome['allowed']);
        $this->assertNull($outcome['update']);
    }

    public function testUpdateFieldRequestRejectsExternalOriginBeforePayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'proxysql.update_field');

        $outcome = ProxySQL::evaluateUpdateFieldRequest(
            ['5', 'mysql_users'],
            [Csrf::DEFAULT_FIELD => $token, 'name' => ['invalid'], 'value' => '1', 'pk' => "username = 'app'"],
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
        $this->assertNull($outcome['update']);
    }

    public function testUpdateFieldRequestRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'proxysql.update');
        $server = $this->sameSitePostServer();

        $missingToken = ProxySQL::evaluateUpdateFieldRequest(['5', 'mysql_users'], $this->validPost(null), $server, $session);
        $foreignScope = ProxySQL::evaluateUpdateFieldRequest(['5', 'mysql_users'], $this->validPost($foreignToken), $server, $session);

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertFalse($missingToken['allowed']);
        $this->assertNull($missingToken['update']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
        $this->assertFalse($foreignScope['allowed']);
        $this->assertNull($foreignScope['update']);
    }

    public function testUpdateFieldPayloadRejectsInvalidValues(): void
    {
        $post = $this->validPost('token');

        $this->assertNull(ProxySQL::normalizeUpdateFieldPayload([], $post));
        $this->assertNull(ProxySQL::normalizeUpdateFieldPayload(['0', 'mysql_users'], $post));
        $this->assertNull(ProxySQL::normalizeUpdateFieldPayload(['5 OR 1=1', 'mysql_users'], $post));
        $this->assertNull(ProxySQL::normalizeUpdateFieldPayload(['5', 'runtime_mysql_users'], $post));
        $this->assertNull(ProxySQL::normalizeUpdateFieldPayload(['5', 'mysql_users;DROP'], $post));
        $this->assertNull(ProxySQL::normalizeUpdateFieldPayload(['5', 'mysql_users'], array_replace($post, ['name' => 'active-state'])));
        $this->assertNull(ProxySQL::normalizeUpdateFieldPayload(['5', 'mysql_users'], array_replace($post, ['name' => 'active` = 0 --'])));
        $this->assertNull(ProxySQL::normalizeUpdateFieldPayload(['5', 'mysql_users'], array_replace($post, ['name' => ['active']])));
        $this->assertNull(ProxySQL::normalizeUpdateFieldPayload(['5', 'mysql_users'], array_replace($post, ['value' => ['1']])));
        $this->assertNull(ProxySQL::normalizeUpdateFieldPayload(['5', 'mysql_users'], array_replace($post, ['value' => str_repeat('a', 4097)])));
        $this->assertNull(ProxySQL::normalizeUpdateFieldPayload(['5', 'mysql_users'], array_replace($post, ['pk' => 'username = app'])));
        $this->assertNull(ProxySQL::normalizeUpdateFieldPayload(['5', 'mysql_users'], array_replace($post, ['pk' => "username = 'app'; DROP TABLE mysql_users"])));
        $this->assertNull(ProxySQL::normalizeUpdateFieldPayload(['5', 'mysql_users'], array_replace($post, ['pk' => ['username']])));
    }

    public function testUpdateFieldSqlEscapesValueAndKeepsValidatedIdentifiers(): void
    {
        $sql = ProxySQL::buildUpdateFieldSql(
            [
                'id_proxysql_server' => 5,
                'table' => 'mysql_users',
                'field' => 'password',
                'value' => "pa'ss",
                'pk' => "username = 'app'",
            ],
            static fn (string $value): string => str_replace("'", "\\'", $value)
        );

        $this->assertSame("UPDATE `mysql_users` SET `password` = 'pa\\'ss' WHERE username = 'app';", $sql);
    }

    public function testExternalPostWouldPassLegacyPostButIsRejectedBeforeSql(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'proxysql.update_field');
        $post = $this->validPost($token);
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://attacker.test',
        ];

        $this->assertTrue(strtoupper($server['REQUEST_METHOD']) === 'POST');
        $this->assertSame(
            "UPDATE `mysql_users` SET `active` = '1' WHERE username = 'app' AND frontend = '1';",
            $this->legacyUpdateFieldSql(['5', 'mysql_users'], $post)
        );

        $outcome = ProxySQL::evaluateUpdateFieldRequest(['5', 'mysql_users'], $post, $server, $session);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertFalse($outcome['allowed']);
        $this->assertNull($outcome['update']);
    }

    public function testUpdateFieldUsesSharedCsrfGuardAndInlineEditSendsToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/ProxySQL.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/ProxySQL/config.view.php');
        $javascript = file_get_contents(__DIR__ . '/../../App/Webroot/js/Tree/index.js');

        $this->assertIsString($controller);
        $this->assertIsString($view);
        $this->assertIsString($javascript);

        $this->assertStringContainsString('use App\\Library\\Security\\CsrfGuard;', $controller);
        $this->assertStringContainsString('use Glial\\Security\\Csrf;', $controller);
        $this->assertStringContainsString("private const PROXYSQL_UPDATE_FIELD_CSRF_SCOPE = 'proxysql.update_field'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::PROXYSQL_UPDATE_FIELD_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, self::PROXYSQL_UPDATE_FIELD_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString("array('bootstrap-editable.min.js', 'Tree/index.js')", $controller);
        $this->assertStringNotContainsString('$(".line-edit").editable();', $controller);

        $this->assertStringContainsString('$proxySqlUpdateFieldCsrfAttributes', $view);
        $this->assertStringContainsString("CsrfRender::attributes(\$data, 'proxysql_update_field')", $view);
        $this->assertStringContainsString("LINK.'ProxySQL/updateField/'", $view);

        $this->assertStringContainsString('params[csrfField] = csrfToken;', $javascript);
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

    private function validPost(?string $token): array
    {
        $post = [
            'name' => 'active',
            'value' => '1',
            'pk' => "username = 'app' AND frontend = '1'",
        ];

        if ($token !== null) {
            $post[Csrf::DEFAULT_FIELD] = $token;
        }

        return $post;
    }

    private function legacyUpdateFieldSql(array $param, array $post): string
    {
        return "UPDATE `".$param[1]."` SET `".$post['name']."` = '".$post['value']."' WHERE ".$post['pk'].";";
    }
}
