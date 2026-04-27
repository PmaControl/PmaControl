<?php

declare(strict_types=1);

use App\Controller\Client;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class ClientMonitoringToggleTest extends TestCase
{
    public function testMonitoringToggleRequestAcceptsValidPostToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'client.toggleMonitoring');

        $outcome = Client::evaluateMonitoringToggleRequest(
            [Csrf::DEFAULT_FIELD => $token, 'id' => '12', 'is_monitored' => 'true'],
            $this->sameSitePostServer(),
            $session,
            []
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(['id' => 12, 'is_monitored' => 1], $outcome['payload']);
    }

    public function testMonitoringToggleRequestRejectsNonPost(): void
    {
        $outcome = Client::evaluateMonitoringToggleRequest([], ['REQUEST_METHOD' => 'GET'], [], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['payload']);
    }

    public function testMonitoringToggleRequestRejectsExternalOriginBeforePayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'client.toggleMonitoring');

        $outcome = Client::evaluateMonitoringToggleRequest(
            [Csrf::DEFAULT_FIELD => $token, 'id' => ['invalid'], 'is_monitored' => 'true'],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session,
            []
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['payload']);
    }

    public function testMonitoringToggleRequestRejectsMissingOriginAndReferer(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'client.toggleMonitoring');

        $outcome = Client::evaluateMonitoringToggleRequest(
            [Csrf::DEFAULT_FIELD => $token, 'id' => '12', 'is_monitored' => 'true'],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
            ],
            $session,
            []
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['payload']);
    }

    public function testMonitoringToggleRequestRejectsMissingForeignOrCrossSessionToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'client.update');
        $validToken = Csrf::issueToken($session, 'client.toggleMonitoring');
        $server = $this->sameSitePostServer();

        $missingToken = Client::evaluateMonitoringToggleRequest(['id' => '12', 'is_monitored' => 'true'], $server, $session, []);
        $foreignScope = Client::evaluateMonitoringToggleRequest(
            [Csrf::DEFAULT_FIELD => $foreignToken, 'id' => '12', 'is_monitored' => 'true'],
            $server,
            $session,
            []
        );
        $crossSession = Client::evaluateMonitoringToggleRequest(
            [Csrf::DEFAULT_FIELD => $validToken, 'id' => '12', 'is_monitored' => 'true'],
            $server,
            [],
            []
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
        $this->assertSame(403, $crossSession['status']);
        $this->assertSame('Invalid CSRF token', $crossSession['body']);
    }

    public function testMonitoringToggleRequestRejectsInvalidPayloadAfterValidCsrf(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'client.toggleMonitoring');
        $server = $this->sameSitePostServer();

        foreach ($this->invalidTogglePayloads() as $post) {
            $post[Csrf::DEFAULT_FIELD] = $token;
            $outcome = Client::evaluateMonitoringToggleRequest($post, $server, $session, []);

            $this->assertSame(400, $outcome['status']);
            $this->assertNull($outcome['payload']);
        }
    }

    public function testMonitoringToggleRequiresPostMethod(): void
    {
        $this->assertTrue(Client::isPostRequest(['REQUEST_METHOD' => 'POST']));
        $this->assertTrue(Client::isPostRequest(['REQUEST_METHOD' => 'post']));
        $this->assertFalse(Client::isPostRequest(['REQUEST_METHOD' => 'GET']));
        $this->assertFalse(Client::isPostRequest([]));
    }

    public function testNormalizeMonitoringTogglePayloadUsesPostValues(): void
    {
        $payload = Client::normalizeMonitoringTogglePayload(
            ['99', 'false'],
            ['id' => '12', 'is_monitored' => 'true']
        );

        $this->assertSame(['id' => 12, 'is_monitored' => 1], $payload);
    }

    public function testNormalizeMonitoringTogglePayloadAcceptsBooleanVariants(): void
    {
        $this->assertSame(
            ['id' => 12, 'is_monitored' => 1],
            Client::normalizeMonitoringTogglePayload([], ['id' => '12', 'is_monitored' => 1])
        );
        $this->assertSame(
            ['id' => 12, 'is_monitored' => 1],
            Client::normalizeMonitoringTogglePayload([], ['id' => '12', 'is_monitored' => 'on'])
        );
        $this->assertSame(
            ['id' => 12, 'is_monitored' => 0],
            Client::normalizeMonitoringTogglePayload([], ['id' => '12', 'is_monitored' => 0])
        );
        $this->assertSame(
            ['id' => 12, 'is_monitored' => 0],
            Client::normalizeMonitoringTogglePayload([], ['id' => '12', 'is_monitored' => 'off'])
        );
    }

    public function testNormalizeMonitoringTogglePayloadKeepsPostRouteCompatibility(): void
    {
        $payload = Client::normalizeMonitoringTogglePayload(['42', 'false'], []);

        $this->assertSame(['id' => 42, 'is_monitored' => 0], $payload);
    }

    public function testNormalizeMonitoringTogglePayloadRejectsInvalidClientId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid client id');

        Client::normalizeMonitoringTogglePayload([], ['id' => '0', 'is_monitored' => 'true']);
    }

    public function testNormalizeMonitoringTogglePayloadRejectsInvalidStatus(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid monitoring status');

        Client::normalizeMonitoringTogglePayload([], ['id' => '12', 'is_monitored' => 'maybe']);
    }

    public function testNormalizeMonitoringTogglePayloadRejectsMissingStatus(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid monitoring status');

        Client::normalizeMonitoringTogglePayload([], ['id' => '12']);
    }

    public function testMonitoringToggleUsesSharedCsrfGuardAndAjaxSendsToken(): void
    {
        $controller = (string)file_get_contents(__DIR__ . '/../../App/Controller/Client.php');
        $view = (string)file_get_contents(__DIR__ . '/../../App/view/Client/index.view.php');
        $javascript = (string)file_get_contents(__DIR__ . '/../../App/Webroot/js/Client/index.js');

        $this->assertStringContainsString("private const CLIENT_MONITORING_TOGGLE_CSRF_SCOPE = 'client.toggleMonitoring'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::CLIENT_MONITORING_TOGGLE_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, self::CLIENT_MONITORING_TOGGLE_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('self::evaluateMonitoringToggleRequest(', $controller);
        $this->assertStringContainsString('Content-Type: application/json; charset=UTF-8', $controller);

        $this->assertStringContainsString('$clientMonitoringCsrfField', $view);
        $this->assertStringContainsString('$clientMonitoringCsrfToken', $view);
        $this->assertStringContainsString('\'data-csrf-field\' => $clientMonitoringCsrfField', $view);
        $this->assertStringContainsString('\'data-csrf-token\' => $clientMonitoringCsrfToken', $view);

        $this->assertStringContainsString('var csrfField = checkbox.attr("data-csrf-field") || "_csrf_token";', $javascript);
        $this->assertStringContainsString('var csrfToken = checkbox.attr("data-csrf-token");', $javascript);
        $this->assertStringContainsString('payload[csrfField] = csrfToken;', $javascript);
        $this->assertStringContainsString('data: payload', $javascript);
    }

    private function invalidTogglePayloads(): array
    {
        return [
            ['id' => '0', 'is_monitored' => 'true'],
            ['id' => '99', 'is_monitored' => 'true'],
            ['id' => '7e0', 'is_monitored' => 'true'],
            ['id' => '-1', 'is_monitored' => 'true'],
            ['id' => 'abc', 'is_monitored' => 'true'],
            ['id' => ['12'], 'is_monitored' => 'true'],
            ['id' => '12', 'is_monitored' => 'maybe'],
            ['id' => '12'],
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
