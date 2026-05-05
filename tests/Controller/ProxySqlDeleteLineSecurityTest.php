<?php

declare(strict_types=1);

use App\Controller\ProxySQL;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class ProxySqlDeleteLineSecurityTest extends TestCase
{
    public function testDeleteLineRequestAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'proxysql.delete_line');

        $outcome = ProxySQL::evaluateDeleteLineRequest(
            [],
            $this->validPost($token),
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertTrue($outcome['allowed']);
        $this->assertSame(
            [
                'id_proxysql_server' => 5,
                'current' => 'MYSQL_USERS',
                'table' => 'mysql_users',
                'pk' => "username = 'app' AND frontend = '1'",
            ],
            $outcome['delete']
        );
    }

    public function testDeleteLineRequestRejectsNonPost(): void
    {
        $outcome = ProxySQL::evaluateDeleteLineRequest(
            ['5', 'mysql_users', base64_encode("username = 'app'")],
            [],
            ['REQUEST_METHOD' => 'GET'],
            []
        );

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertFalse($outcome['allowed']);
        $this->assertNull($outcome['delete']);
    }

    public function testDeleteLineRequestRejectsExternalOriginBeforePayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'proxysql.delete_line');

        $outcome = ProxySQL::evaluateDeleteLineRequest(
            [],
            array_replace($this->validPost($token), ['table' => 'mysql_users;DROP']),
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
        $this->assertNull($outcome['delete']);
    }

    public function testDeleteLineRequestRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'proxysql.update_field');
        $server = $this->sameSitePostServer();

        $missingToken = ProxySQL::evaluateDeleteLineRequest([], $this->validPost(null), $server, $session);
        $foreignScope = ProxySQL::evaluateDeleteLineRequest([], $this->validPost($foreignToken), $server, $session);

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertFalse($missingToken['allowed']);
        $this->assertNull($missingToken['delete']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
        $this->assertFalse($foreignScope['allowed']);
        $this->assertNull($foreignScope['delete']);
    }

    public function testDeleteLinePayloadRejectsInvalidValues(): void
    {
        $post = $this->validPost('token');

        $this->assertNull(ProxySQL::normalizeDeleteLinePayload([], []));
        $this->assertNull(ProxySQL::normalizeDeleteLinePayload([], array_replace($post, ['id_proxysql_server' => '0'])));
        $this->assertNull(ProxySQL::normalizeDeleteLinePayload([], array_replace($post, ['id_proxysql_server' => '5 OR 1=1'])));
        $this->assertNull(ProxySQL::normalizeDeleteLinePayload([], array_replace($post, ['id_proxysql_server' => ['5']])));
        $this->assertNull(ProxySQL::normalizeDeleteLinePayload([], array_replace($post, ['current' => 'MYSQL USERS'])));
        $this->assertNull(ProxySQL::normalizeDeleteLinePayload([], array_replace($post, ['current' => ['MYSQL_USERS']])));
        $this->assertNull(ProxySQL::normalizeDeleteLinePayload([], array_replace($post, ['table' => 'global_variables'])));
        $this->assertNull(ProxySQL::normalizeDeleteLinePayload([], array_replace($post, ['table' => 'runtime_mysql_users'])));
        $this->assertNull(ProxySQL::normalizeDeleteLinePayload([], array_replace($post, ['table' => 'mysql_users;DROP'])));
        $this->assertNull(ProxySQL::normalizeDeleteLinePayload([], array_replace($post, ['table' => ['mysql_users']])));
        $this->assertNull(ProxySQL::normalizeDeleteLinePayload([], array_replace($post, ['pk' => '1=1'])));
        $this->assertNull(ProxySQL::normalizeDeleteLinePayload([], array_replace($post, ['pk' => "username = 'app' OR 1=1"])));
        $this->assertNull(ProxySQL::normalizeDeleteLinePayload([], array_replace($post, ['pk' => "username = 'app'; DROP TABLE mysql_users"])));
        $this->assertNull(ProxySQL::normalizeDeleteLinePayload([], array_replace($post, ['pk' => ['username']])));
        $this->assertNull(ProxySQL::normalizeDeleteLinePayload([], array_replace($post, ['pk' => str_repeat('a', 2049)])));
    }

    public function testDeleteLineSqlUsesValidatedTableAndPredicate(): void
    {
        $sql = ProxySQL::buildDeleteLineSql(
            [
                'id_proxysql_server' => 5,
                'current' => 'MYSQL_USERS',
                'table' => 'mysql_users',
                'pk' => "username = 'app' AND frontend = '1'",
            ]
        );

        $this->assertSame("DELETE FROM `mysql_users` WHERE username = 'app' AND frontend = '1';", $sql);
    }

    public function testLegacyGetWouldBuildSqlButIsRejectedBeforePayload(): void
    {
        $where = "username = 'app' OR 1=1";

        $this->assertSame(
            "DELETE FROM `mysql_users` WHERE username = 'app' OR 1=1;",
            $this->legacyDeleteLineSql(['5', 'mysql_users', base64_encode($where)])
        );

        $outcome = ProxySQL::evaluateDeleteLineRequest(
            ['5', 'mysql_users', base64_encode($where)],
            [],
            ['REQUEST_METHOD' => 'GET'],
            []
        );

        $this->assertSame(405, $outcome['status']);
        $this->assertFalse($outcome['allowed']);
        $this->assertNull($outcome['delete']);
    }

    public function testDeleteLineUsesSharedSecurityHelpersAndViewSendsPostForm(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/ProxySQL.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/ProxySQL/config.view.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);

        $this->assertStringContainsString('use App\\Library\\Security\\CsrfGuard;', $controller);
        $this->assertStringContainsString('use App\\Library\\Security\\PositiveIntegerSelection;', $controller);
        $this->assertStringContainsString('use App\\Library\\Security\\SafeRedirect;', $controller);
        $this->assertStringContainsString("private const PROXYSQL_DELETE_LINE_CSRF_SCOPE = 'proxysql.delete_line'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::PROXYSQL_DELETE_LINE_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::ensureOrFail($post, $server, $session, self::PROXYSQL_DELETE_LINE_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('PositiveIntegerSelection::normalizeSingle($value)', $controller);
        $this->assertStringContainsString("Identifier::quoteStrictSqlIdentifier((string) \$delete['table'])", $controller);
        $this->assertStringContainsString('SafeRedirect::refererOrFallback(', $controller);
        $this->assertStringNotContainsString('base64_decode($param[2])', $controller);

        $this->assertStringContainsString('$proxySqlDeleteLineCsrfInput', $view);
        $this->assertStringContainsString("CsrfRender::hiddenInput(\$data, 'proxysql_delete_line')", $view);
        $this->assertStringContainsString("LINK.'ProxySQL/deleteLine'", $view);
        $this->assertStringContainsString('<form method="post" action="', $view);
        $this->assertStringContainsString('name="pk"', $view);
        $this->assertStringNotContainsString('base64_encode($full_pk)', $view);
        $this->assertDoesNotMatchRegularExpression('/<a[^\\n]+ProxySQL\\/deleteLine/', $view);
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
            'id_proxysql_server' => '5',
            'current' => 'MYSQL_USERS',
            'table' => 'mysql_users',
            'pk' => "username = 'app' AND frontend = '1'",
        ];

        if ($token !== null) {
            $post[Csrf::DEFAULT_FIELD] = $token;
        }

        return $post;
    }

    private function legacyDeleteLineSql(array $param): string
    {
        return "DELETE FROM `".$param[1]."` WHERE ".base64_decode($param[2]).";";
    }
}
