<?php

declare(strict_types=1);

use App\Controller\ProxySQL;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

if (!function_exists('__')) {
    function __($value)
    {
        return $value;
    }
}

if (!defined('LINK')) {
    define('LINK', '/');
}

if (!defined('LOG_FILE')) {
    define('LOG_FILE', sys_get_temp_dir() . '/pmacontrol-test.log');
}

final class ProxySqlAddSecurityTest extends TestCase
{
    public function testAddRequestAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'proxysql.add');

        $outcome = ProxySQL::evaluateAddRequest(
            $this->validPost($token),
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertTrue($outcome['allowed']);
        $this->assertSame(
            ['db.example.local', '6032', 'admin', 'secret value', 'Proxy Admin'],
            $outcome['proxysql']
        );
    }

    public function testAddRequestRejectsNonPost(): void
    {
        $outcome = ProxySQL::evaluateAddRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertFalse($outcome['allowed']);
        $this->assertNull($outcome['proxysql']);
    }

    public function testAddRequestRejectsExternalOriginBeforePayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'proxysql.add');

        $outcome = ProxySQL::evaluateAddRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'proxysql_server' => 'invalid',
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
        $this->assertNull($outcome['proxysql']);
    }

    public function testAddRequestRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'proxysql.update');
        $server = $this->sameSitePostServer();

        $missingToken = ProxySQL::evaluateAddRequest($this->validPost(null), $server, $session);
        $foreignScope = ProxySQL::evaluateAddRequest($this->validPost($foreignToken), $server, $session);

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertFalse($missingToken['allowed']);
        $this->assertNull($missingToken['proxysql']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
        $this->assertFalse($foreignScope['allowed']);
        $this->assertNull($foreignScope['proxysql']);
    }

    public function testAddRequestRejectsInvalidPayloadAfterValidCsrf(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'proxysql.add');
        $server = $this->sameSitePostServer();

        foreach ($this->invalidPayloads($token) as $post) {
            $outcome = ProxySQL::evaluateAddRequest($post, $server, $session);

            $this->assertSame(400, $outcome['status']);
            $this->assertSame('Invalid ProxySQL add payload', $outcome['body']);
            $this->assertFalse($outcome['allowed']);
            $this->assertNull($outcome['proxysql']);
        }
    }

    public function testAddRequestAllowsCliWithoutCsrfToken(): void
    {
        $outcome = ProxySQL::evaluateAddRequest(
            $this->validPost(null),
            ['REQUEST_METHOD' => 'GET'],
            [],
            true
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertTrue($outcome['allowed']);
        $this->assertSame(['db.example.local', '6032', 'admin', 'secret value', 'Proxy Admin'], $outcome['proxysql']);
    }

    public function testAddViewCarriesCsrfToken(): void
    {
        $view = file_get_contents(__DIR__ . '/../../App/view/ProxySQL/add.view.php');

        $this->assertIsString($view);
        $this->assertStringContainsString('$proxySqlAddCsrfField', $view);
        $this->assertStringContainsString('$proxySqlAddCsrfToken', $view);
        $this->assertStringContainsString('<form action="" method="post">', $view);
        $this->assertStringContainsString('<input type="hidden" name="', $view);
        $this->assertStringContainsString('value="<?= $proxySqlAddCsrfToken ?>">', $view);
    }

    public function testAddActionUsesScopedCsrfGuardBeforeProxySqlWrite(): void
    {
        $source = (string) file_get_contents(__DIR__ . '/../../App/Controller/ProxySQL.php');

        $this->assertStringContainsString("private const PROXYSQL_ADD_CSRF_SCOPE = 'proxysql.add'", $source);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::PROXYSQL_ADD_CSRF_SCOPE)', $source);
        $this->assertStringContainsString('CsrfGuard::ensureOrFail($post, $server, $session, self::PROXYSQL_ADD_CSRF_SCOPE)', $source);
        $this->assertStringContainsString('evaluateAddRequest($_POST, $_SERVER, $_SESSION, IS_CLI)', $source);
        $this->assertStringContainsString('$this->insertProxySqlAdmin($outcome[\'proxysql\'])', $source);
    }

    private function validPost(?string $token): array
    {
        $post = [
            'proxysql_server' => [
                'display_name' => ' Proxy Admin ',
                'hostname' => ' db.example.local ',
                'port' => '6032',
                'login' => ' admin ',
                'password' => 'secret value',
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
            [Csrf::DEFAULT_FIELD => $token, 'proxysql_server' => 'invalid'],
            $this->withProxySqlValue($base, 'hostname', ''),
            $this->withProxySqlValue($base, 'hostname', 'db.example.local;DROP'),
            $this->withProxySqlValue($base, 'hostname', 'db example local'),
            $this->withProxySqlValue($base, 'hostname', ['db.example.local']),
            $this->withProxySqlValue($base, 'hostname', str_repeat('h', 256)),
            $this->withProxySqlValue($base, 'port', '0'),
            $this->withProxySqlValue($base, 'port', '70000'),
            $this->withProxySqlValue($base, 'port', '6032x'),
            $this->withProxySqlValue($base, 'login', ''),
            $this->withProxySqlValue($base, 'login', ['admin']),
            $this->withProxySqlValue($base, 'login', str_repeat('l', 129)),
            $this->withProxySqlValue($base, 'password', ''),
            $this->withProxySqlValue($base, 'password', ['secret']),
            $this->withProxySqlValue($base, 'password', str_repeat('p', 1025)),
            $this->withProxySqlValue($base, 'display_name', ''),
            $this->withProxySqlValue($base, 'display_name', ['Proxy Admin']),
            $this->withProxySqlValue($base, 'display_name', str_repeat('d', 256)),
        ];
    }

    private function withProxySqlValue(array $post, string $field, $value): array
    {
        $post['proxysql_server'][$field] = $value;
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
