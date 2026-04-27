<?php

declare(strict_types=1);

use App\Controller\Server;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class ServerPasswordSecurityTest extends TestCase
{
    public function testPasswordRequestAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'server.password');

        $outcome = Server::evaluatePasswordRequest(
            ['7'],
            $this->validPost($token),
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(
            [
                'id_server' => 7,
                'login' => 'replication',
                'passwd' => 'secret',
            ],
            $outcome['password']
        );
    }

    public function testPasswordRequestRejectsNonPost(): void
    {
        $outcome = Server::evaluatePasswordRequest([], [], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['password']);
    }

    public function testPasswordRequestRejectsExternalOriginBeforePayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'server.password');

        $outcome = Server::evaluatePasswordRequest(
            ['7'],
            [Csrf::DEFAULT_FIELD => $token, 'mysql_server' => ['login' => ['invalid']]],
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
        $this->assertNull($outcome['password']);
    }

    public function testPasswordRequestRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'server.settings');
        $server = $this->sameSitePostServer();

        $missingToken = Server::evaluatePasswordRequest(['7'], $this->validPost(null), $server, $session);
        $foreignScope = Server::evaluatePasswordRequest(['7'], $this->validPost($foreignToken), $server, $session);

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testPasswordPayloadRejectsInvalidValues(): void
    {
        $post = $this->validPost('token');

        $this->assertNull(Server::normalizePasswordPayload([], $post));
        $this->assertNull(Server::normalizePasswordPayload(['0'], $post));
        $this->assertNull(Server::normalizePasswordPayload(['7 OR 1=1'], $post));
        $this->assertNull(Server::normalizePasswordPayload(['7'], []));
        $this->assertNull(Server::normalizePasswordPayload(['7'], ['mysql_server' => 'invalid']));
        $this->assertNull(Server::normalizePasswordPayload(['7'], $this->withMysqlField($post, 'login', ['root'])));
        $this->assertNull(Server::normalizePasswordPayload(['7'], $this->withMysqlField($post, 'passwd', ['secret'])));
        $this->assertNull(Server::normalizePasswordPayload(['7'], $this->withMysqlField($post, 'login', '   ')));
        $this->assertNull(Server::normalizePasswordPayload(['7'], $this->withMysqlField($post, 'passwd', '')));
        $this->assertNull(Server::normalizePasswordPayload(['7'], $this->withMysqlField($post, 'login', str_repeat('a', 129))));
        $this->assertNull(Server::normalizePasswordPayload(['7'], $this->withMysqlField($post, 'passwd', str_repeat('a', 1025))));
    }

    public function testPasswordPayloadTrimsLoginButKeepsPasswordValue(): void
    {
        $request = Server::normalizePasswordPayload(
            ['7'],
            ['mysql_server' => ['login' => '  replication  ', 'passwd' => ' secret ']]
        );

        $this->assertSame('replication', $request['login']);
        $this->assertSame(' secret ', $request['passwd']);
    }

    public function testExternalPostWouldPassLegacyMethodGateButIsRejectedBeforeEncryption(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'server.password');
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://attacker.test',
        ];

        $this->assertTrue(strtoupper($server['REQUEST_METHOD']) === 'POST');

        $outcome = Server::evaluatePasswordRequest(['7'], $this->validPost($token), $server, $session);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['password']);
    }

    public function testPasswordUsesSharedCsrfGuardAndFormCarriesToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Server.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/Server/password.view.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);

        $guardPosition = strpos($controller, '$outcome = self::evaluatePasswordRequest($param, $_POST, $_SERVER, $_SESSION);');
        $encryptPosition = strpos($controller, 'Chiffrement::encrypt($passwordRequest[\'passwd\'])');

        $this->assertStringContainsString("private const SERVER_PASSWORD_CSRF_SCOPE = 'server.password'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::SERVER_PASSWORD_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, self::SERVER_PASSWORD_CSRF_SCOPE)', $controller);
        $this->assertIsInt($guardPosition);
        $this->assertIsInt($encryptPosition);
        $this->assertLessThan($encryptPosition, $guardPosition);

        $this->assertStringContainsString('$serverPasswordCsrfField', $view);
        $this->assertStringContainsString('$serverPasswordCsrfToken', $view);
        $this->assertStringContainsString('<input type="hidden" name="<?= $serverPasswordCsrfField ?>" value="<?= $serverPasswordCsrfToken ?>">', $view);
        $this->assertStringContainsString('method="POST"', $view);
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
            'mysql_server' => [
                'login' => 'replication',
                'passwd' => 'secret',
            ],
        ];

        if ($token !== null) {
            $post[Csrf::DEFAULT_FIELD] = $token;
        }

        return $post;
    }

    private function withMysqlField(array $post, string $field, $value): array
    {
        $post['mysql_server'][$field] = $value;
        return $post;
    }
}
