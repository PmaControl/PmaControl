<?php

declare(strict_types=1);

use App\Controller\Client;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class ClientAddSecurityTest extends TestCase
{
    public function testAddRequestAcceptsValidPostToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'client.add');

        $outcome = Client::evaluateAddRequest(
            [Csrf::DEFAULT_FIELD => $token, 'client' => ['libelle' => ' Voyage prive ']],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(['libelle' => 'Voyage prive'], $outcome['client']);
    }

    public function testAddRequestRejectsNonPost(): void
    {
        $outcome = Client::evaluateAddRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['client']);
    }

    public function testAddRequestRejectsExternalOriginBeforePayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'client.add');

        $outcome = Client::evaluateAddRequest(
            [Csrf::DEFAULT_FIELD => $token, 'client' => ['libelle' => ['invalid']]],
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
        $this->assertNull($outcome['client']);
    }

    public function testAddRequestRejectsMissingOriginAndReferer(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'client.add');

        $outcome = Client::evaluateAddRequest(
            [Csrf::DEFAULT_FIELD => $token, 'client' => ['libelle' => 'VP']],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
            ],
            $session
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['client']);
    }

    public function testAddRequestRejectsMissingForeignOrCrossSessionToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'client.update');
        $validToken = Csrf::issueToken($session, 'client.add');
        $server = $this->sameSitePostServer();

        $missingToken = Client::evaluateAddRequest(['client' => ['libelle' => 'VP']], $server, $session);
        $foreignScope = Client::evaluateAddRequest(
            [Csrf::DEFAULT_FIELD => $foreignToken, 'client' => ['libelle' => 'VP']],
            $server,
            $session
        );
        $crossSession = Client::evaluateAddRequest(
            [Csrf::DEFAULT_FIELD => $validToken, 'client' => ['libelle' => 'VP']],
            $server,
            []
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
        $this->assertSame(403, $crossSession['status']);
        $this->assertSame('Invalid CSRF token', $crossSession['body']);
    }

    public function testAddPayloadRejectsInvalidLibelleValues(): void
    {
        foreach ($this->invalidPayloads() as $post) {
            $this->assertNull(Client::normalizeAddPayload($post));
        }
    }

    public function testAddPayloadAcceptsExactMaxLengthLibelle(): void
    {
        $libelle = str_repeat('a', 255);

        $this->assertSame(['libelle' => $libelle], Client::normalizeAddPayload(['client' => ['libelle' => $libelle]]));
    }

    public function testExternalPostWouldPassLegacyPostGateButIsRejectedBeforeSqlSave(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'client.add');
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://attacker.test',
        ];

        $this->assertTrue(Client::isPostRequest($server));
        $this->assertSame(['libelle' => 'Owned'], Client::normalizeAddPayload(['client' => ['libelle' => 'Owned']]));

        $outcome = Client::evaluateAddRequest(
            [Csrf::DEFAULT_FIELD => $token, 'client' => ['libelle' => 'Owned']],
            $server,
            $session
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['client']);
    }

    public function testClientAddUsesSharedCsrfGuardAndFormSendsToken(): void
    {
        $controller = (string)file_get_contents(__DIR__ . '/../../App/Controller/Client.php');
        $view = (string)file_get_contents(__DIR__ . '/../../App/view/Client/add.view.php');

        $this->assertStringContainsString("private const CLIENT_ADD_CSRF_SCOPE = 'client.add'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::CLIENT_ADD_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::ensureOrFail($post, $server, $session, self::CLIENT_ADD_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('self::evaluateAddRequest($_POST, $_SERVER, $_SESSION)', $controller);
        $this->assertStringContainsString('sql_save($client)', $controller);

        $this->assertStringContainsString('$clientAddCsrfField', $view);
        $this->assertStringContainsString('$clientAddCsrfToken', $view);
        $this->assertStringContainsString('name="\'.$clientAddCsrfField.\'"', $view);
        $this->assertStringContainsString('value="\'.$clientAddCsrfToken.\'"', $view);
        $this->assertStringContainsString('method="post"', $view);
    }

    private function invalidPayloads(): array
    {
        return [
            [],
            ['client' => 'VP'],
            ['client' => []],
            ['client' => ['libelle' => '']],
            ['client' => ['libelle' => '   ']],
            ['client' => ['libelle' => str_repeat('a', 256)]],
            ['client' => ['libelle' => "bad\nname"]],
            ['client' => ['libelle' => ['VP']]],
        ];
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
