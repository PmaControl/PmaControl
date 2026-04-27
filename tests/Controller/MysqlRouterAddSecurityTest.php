<?php

declare(strict_types=1);

use App\Controller\MysqlRouter;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class MysqlRouterAddSecurityTest extends TestCase
{
    public function testAddRequestAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'mysqlrouter.add');

        $outcome = MysqlRouter::evaluateAddRequest(
            $this->validPost($token),
            $this->sameSitePostServer(),
            $session
        );

        $this->assertTrue($outcome['allowed']);
        $this->assertSame(200, $outcome['status']);
        $this->assertSame(
            [
                'hostname' => 'router-admin.local',
                'port' => 8443,
                'login' => 'admin',
                'password' => 'secret',
                'display_name' => 'Production Router',
                'is_ssl' => 1,
            ],
            $outcome['router']
        );
    }

    public function testAddRequestRejectsNonPost(): void
    {
        $outcome = MysqlRouter::evaluateAddRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertFalse($outcome['allowed']);
        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['router']);
    }

    public function testAddRequestRejectsExternalOriginBeforePayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'mysqlrouter.add');

        $outcome = MysqlRouter::evaluateAddRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'mysqlrouter_server' => ['hostname' => ['invalid']],
            ],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session
        );

        $this->assertFalse($outcome['allowed']);
        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['router']);
    }

    public function testAddRequestRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'proxysql.add');
        $server = $this->sameSitePostServer();

        $missingToken = MysqlRouter::evaluateAddRequest($this->validPost(null), $server, $session);
        $foreignScope = MysqlRouter::evaluateAddRequest($this->validPost($foreignToken), $server, $session);

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertFalse($missingToken['allowed']);
        $this->assertNull($missingToken['router']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
        $this->assertFalse($foreignScope['allowed']);
        $this->assertNull($foreignScope['router']);
    }

    public function testAddRequestRejectsInvalidPayloadAfterValidCsrf(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'mysqlrouter.add');
        $server = $this->sameSitePostServer();

        foreach ($this->invalidPayloads($token) as $post) {
            $outcome = MysqlRouter::evaluateAddRequest($post, $server, $session);

            $this->assertFalse($outcome['allowed']);
            $this->assertSame(400, $outcome['status']);
            $this->assertSame('Invalid MySQL Router add payload', $outcome['body']);
            $this->assertNull($outcome['router']);
        }
    }

    public function testAddPayloadNormalizesSslAndInsertParameters(): void
    {
        $post = $this->validPost('token');
        $post['mysqlrouter_server']['is_ssl'] = 'off';
        unset($post[Csrf::DEFAULT_FIELD]);

        $router = MysqlRouter::normalizeAddPayload($post);

        $this->assertSame(0, $router['is_ssl']);
        $this->assertSame(
            ['router-admin.local', 8443, 'admin', 'secret', 'Production Router', 0],
            MysqlRouter::buildInsertParamFromRouter($router)
        );
    }

    public function testAddUsesSharedCsrfGuardBeforeInsertAndViewSendsToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/MysqlRouter.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/MysqlRouter/add.view.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);

        $addPosition = strpos($controller, 'public function add()');
        $evaluatePosition = strpos(
            $controller,
            '$outcome = self::evaluateAddRequest($_POST, $_SERVER, $_SESSION);',
            $addPosition
        );
        $insertPosition = strpos($controller, '$this->insertMysqlRouterAdmin($param);', $addPosition);

        $this->assertStringContainsString('use App\\Library\\Security\\CsrfGuard;', $controller);
        $this->assertStringContainsString('use Glial\\Security\\Csrf;', $controller);
        $this->assertStringContainsString("private const MYSQLROUTER_ADD_CSRF_SCOPE = 'mysqlrouter.add'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::MYSQLROUTER_ADD_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, self::MYSQLROUTER_ADD_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString("header('location: ' . LINK . 'MysqlRouter/index');", $controller);
        $this->assertIsInt($addPosition);
        $this->assertIsInt($evaluatePosition);
        $this->assertIsInt($insertPosition);
        $this->assertLessThan($insertPosition, $evaluatePosition);

        $this->assertStringContainsString('$mysqlRouterAddCsrfField', $view);
        $this->assertStringContainsString('$mysqlRouterAddCsrfToken', $view);
        $this->assertStringContainsString('<form method="post" action="\' . LINK . \'MysqlRouter/add"', $view);
        $this->assertStringContainsString('type="hidden" name="\' . $mysqlRouterAddCsrfField', $view);
        $this->assertStringContainsString('name="mysqlrouter_server[hostname]"', $view);
        $this->assertStringContainsString('name="mysqlrouter_server[port]"', $view);
    }

    private function validPost(?string $token): array
    {
        $post = [
            'mysqlrouter_server' => [
                'hostname' => ' router-admin.local ',
                'port' => '8443',
                'login' => ' admin ',
                'password' => ' secret ',
                'display_name' => ' Production Router ',
                'is_ssl' => '1',
            ],
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
            [Csrf::DEFAULT_FIELD => $token, 'mysqlrouter_server' => 'invalid'],
            $this->withRouterField($base, 'hostname', ''),
            $this->withRouterField($base, 'hostname', 'http://router-admin.local'),
            $this->withRouterField($base, 'hostname', 'router-admin.local/path'),
            $this->withRouterField($base, 'hostname', ['router-admin.local']),
            $this->withRouterField($base, 'hostname', str_repeat('a', 256)),
            $this->withRouterField($base, 'port', '0'),
            $this->withRouterField($base, 'port', '65536'),
            $this->withRouterField($base, 'port', '8443x'),
            $this->withRouterField($base, 'login', ''),
            $this->withRouterField($base, 'password', ''),
            $this->withRouterField($base, 'display_name', ''),
            $this->withRouterField($base, 'is_ssl', 'maybe'),
        ];
    }

    private function withRouterField(array $post, string $field, $value): array
    {
        $post['mysqlrouter_server'][$field] = $value;
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
