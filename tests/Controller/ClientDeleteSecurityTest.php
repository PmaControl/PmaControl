<?php

declare(strict_types=1);

use App\Controller\Client;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class ClientDeleteSecurityTest extends TestCase
{
    public function testDeleteRequestAcceptsValidPostToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'client.delete');

        $outcome = Client::evaluateDeleteRequest(
            [Csrf::DEFAULT_FIELD => $token],
            $this->sameSitePostServer(),
            $session,
            ['7']
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(7, $outcome['id_client']);
    }

    public function testDeleteRequestAcceptsIntegerRouteId(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'client.delete');

        $outcome = Client::evaluateDeleteRequest(
            [Csrf::DEFAULT_FIELD => $token],
            $this->sameSitePostServer(),
            $session,
            [7]
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(7, $outcome['id_client']);
    }

    public function testDeleteRequestRejectsNonPost(): void
    {
        $outcome = Client::evaluateDeleteRequest([], ['REQUEST_METHOD' => 'GET'], [], ['7']);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['id_client']);
    }

    public function testDeleteRequestRejectsExternalOriginBeforeIdValidation(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'client.delete');

        $outcome = Client::evaluateDeleteRequest(
            [Csrf::DEFAULT_FIELD => $token],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session,
            ['99']
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['id_client']);
    }

    public function testDeleteRequestRejectsMissingOriginAndReferer(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'client.delete');

        $outcome = Client::evaluateDeleteRequest(
            [Csrf::DEFAULT_FIELD => $token],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
            ],
            $session,
            ['7']
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['id_client']);
    }

    public function testDeleteRequestRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'client.update');
        $server = $this->sameSitePostServer();

        $missingToken = Client::evaluateDeleteRequest([], $server, $session, ['7']);
        $foreignScope = Client::evaluateDeleteRequest([Csrf::DEFAULT_FIELD => $foreignToken], $server, $session, ['7']);

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testDeleteRequestRejectsTokenFromAnotherSession(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'client.delete');

        $outcome = Client::evaluateDeleteRequest(
            [Csrf::DEFAULT_FIELD => $token],
            $this->sameSitePostServer(),
            [],
            ['7']
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid CSRF token', $outcome['body']);
        $this->assertNull($outcome['id_client']);
    }

    public function testDeleteRequestRejectsInvalidOrReservedIdsAfterValidCsrf(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'client.delete');
        $server = $this->sameSitePostServer();

        foreach ([[], ['0'], ['99'], ['7 OR 1=1'], [['7']]] as $param) {
            $outcome = Client::evaluateDeleteRequest([Csrf::DEFAULT_FIELD => $token], $server, $session, $param);

            $this->assertSame(400, $outcome['status']);
            $this->assertSame('Invalid client id', $outcome['body']);
            $this->assertNull($outcome['id_client']);
        }
    }

    public function testExternalPostWouldPassLegacyPostGateButIsRejectedBeforeSql(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'client.delete');
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://attacker.test',
        ];

        $this->assertTrue(Client::isDeleteRequestAllowed($server));
        $this->assertSame(7, Client::normalizeDeleteClientId('7'));

        $outcome = Client::evaluateDeleteRequest([Csrf::DEFAULT_FIELD => $token], $server, $session, ['7']);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['id_client']);
    }

    public function testClientDeleteUsesSharedCsrfGuardAndFormSendsToken(): void
    {
        $controller = (string)file_get_contents(__DIR__ . '/../../App/Controller/Client.php');
        $view = (string)file_get_contents(__DIR__ . '/../../App/view/Client/index.view.php');

        $this->assertStringContainsString('use App\\Library\\Security\\CsrfGuard;', $controller);
        $this->assertStringContainsString("private const CLIENT_DELETE_CSRF_SCOPE = 'client.delete'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::CLIENT_DELETE_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::ensureOrFail($post, $server, $session, self::CLIENT_DELETE_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('self::evaluateDeleteRequest($_POST, $_SERVER, $_SESSION, is_array($param) ? $param : [])', $controller);
        $this->assertStringContainsString('private const CLIENT_RESERVED_ID = 99', $controller);

        $this->assertStringContainsString('$clientDeleteCsrfField', $view);
        $this->assertStringContainsString('$clientDeleteCsrfToken', $view);
        $this->assertStringContainsString('name="<?= $clientDeleteCsrfField ?>"', $view);
        $this->assertStringContainsString('value="<?= $clientDeleteCsrfToken ?>"', $view);
        $this->assertStringContainsString('client/delete/', $view);
        $this->assertStringContainsString("header('Content-Type: text/plain; charset=UTF-8');", $controller);
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
