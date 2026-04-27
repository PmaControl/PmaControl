<?php

declare(strict_types=1);

use App\Controller\Client;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class ClientUpdateSecurityTest extends TestCase
{
    public function testUpdateRequestAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'client.update');

        $outcome = Client::evaluateUpdateRequest(
            [Csrf::DEFAULT_FIELD => $token, 'name' => 'libelle', 'value' => 'Voyage prive', 'pk' => '7'],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(['field' => 'libelle', 'value' => 'Voyage prive', 'id' => 7], $outcome['update']);
    }

    public function testUpdateRequestRejectsNonPost(): void
    {
        $outcome = Client::evaluateUpdateRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['update']);
    }

    public function testUpdateRequestRejectsExternalOriginBeforePayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'client.update');

        $outcome = Client::evaluateUpdateRequest(
            [Csrf::DEFAULT_FIELD => $token, 'name' => ['invalid'], 'value' => 'Owned', 'pk' => '7'],
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
        $this->assertNull($outcome['update']);
    }

    public function testUpdateRequestRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'tag.update');
        $server = $this->sameSitePostServer();

        $missingToken = Client::evaluateUpdateRequest(
            ['name' => 'libelle', 'value' => 'Voyage prive', 'pk' => '7'],
            $server,
            $session
        );
        $foreignScope = Client::evaluateUpdateRequest(
            [Csrf::DEFAULT_FIELD => $foreignToken, 'name' => 'libelle', 'value' => 'Voyage prive', 'pk' => '7'],
            $server,
            $session
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testUpdatePayloadRejectsUnknownFieldsInvalidIdsAndMalformedValues(): void
    {
        $this->assertNull(Client::normalizeUpdatePayload(['name' => 'id', 'value' => '8', 'pk' => '7']));
        $this->assertNull(Client::normalizeUpdatePayload(['name' => 'libelle` = 1 --', 'value' => 'x', 'pk' => '7']));
        $this->assertNull(Client::normalizeUpdatePayload(['name' => 'libelle', 'value' => 'x', 'pk' => '0']));
        $this->assertNull(Client::normalizeUpdatePayload(['name' => 'libelle', 'value' => 'x', 'pk' => '99']));
        $this->assertNull(Client::normalizeUpdatePayload(['name' => 'libelle', 'value' => 'x', 'pk' => '7 OR 1=1']));
        $this->assertNull(Client::normalizeUpdatePayload(['name' => 'libelle', 'value' => str_repeat('a', 256), 'pk' => '7']));
        $this->assertNull(Client::normalizeUpdatePayload(['name' => 'libelle', 'value' => '   ', 'pk' => '7']));
        $this->assertNull(Client::normalizeUpdatePayload(['name' => 'libelle', 'pk' => '7']));
        $this->assertNull(Client::normalizeUpdatePayload(['name' => ['libelle'], 'value' => 'x', 'pk' => '7']));
        $this->assertNull(Client::normalizeUpdatePayload(['name' => 'libelle', 'value' => ['x'], 'pk' => '7']));
    }

    public function testUpdatePayloadCoercesBooleanFields(): void
    {
        $this->assertSame(
            ['field' => 'is_monitored', 'value' => '1', 'id' => 7],
            Client::normalizeUpdatePayload(['name' => 'is_monitored', 'value' => 'true', 'pk' => '7'])
        );
        $this->assertSame(
            ['field' => 'is_display', 'value' => '0', 'id' => 7],
            Client::normalizeUpdatePayload(['name' => 'is_display', 'value' => 'off', 'pk' => '7'])
        );
        $this->assertNull(Client::normalizeUpdatePayload(['name' => 'is_display', 'value' => 'maybe', 'pk' => '7']));
    }

    public function testUpdatePayloadAllowsLogoAndExactMaxLengthValue(): void
    {
        $maxLengthLogo = str_repeat('a', 255);

        $this->assertSame(
            ['field' => 'logo', 'value' => $maxLengthLogo, 'id' => 7],
            Client::normalizeUpdatePayload(['name' => 'logo', 'value' => $maxLengthLogo, 'pk' => '7'])
        );
    }

    public function testUpdateSqlEscapesValueAndUsesValidatedIdentifier(): void
    {
        $sql = Client::buildClientUpdateSql(
            ['field' => 'libelle', 'value' => "Bob's client", 'id' => 7],
            static fn (string $value): string => str_replace("'", "\\'", $value)
        );

        $this->assertSame("UPDATE client SET `libelle` = 'Bob\\'s client' WHERE id = 7", $sql);
    }

    public function testExternalPostWouldPassLegacyPostGateButIsRejectedBeforeSql(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'client.update');
        $post = [Csrf::DEFAULT_FIELD => $token, 'name' => 'libelle', 'value' => 'Owned', 'pk' => '7'];
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://attacker.test',
        ];

        $this->assertTrue(strtoupper($server['REQUEST_METHOD']) === 'POST');
        $this->assertSame("UPDATE client SET `libelle` = 'Owned' WHERE id = 7", $this->legacyClientUpdateSql($post));

        $outcome = Client::evaluateUpdateRequest($post, $server, $session);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['update']);
    }

    public function testClientUpdateUsesSharedCsrfLibraryAndInlineEditSendsToken(): void
    {
        $controller = (string)file_get_contents(__DIR__ . '/../../App/Controller/Client.php');
        $view = (string)file_get_contents(__DIR__ . '/../../App/view/Client/index.view.php');
        $javascript = (string)file_get_contents(__DIR__ . '/../../App/Webroot/js/Tree/index.js');

        $this->assertStringContainsString('use App\\Library\\Security\\InlineEditRequest;', $controller);
        $this->assertStringContainsString('use Glial\\Security\\Csrf;', $controller);
        $this->assertStringContainsString("private const CLIENT_UPDATE_CSRF_SCOPE = 'client.update'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::CLIENT_UPDATE_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('InlineEditRequest::evaluate(', $controller);
        $this->assertStringContainsString('UPDATE client SET', $controller);
        $this->assertStringNotContainsString('$_POST[\'name\']', $controller);
        $this->assertStringNotContainsString('$_POST[\'value\']', $controller);

        $this->assertStringContainsString('$clientUpdateCsrfAttributes', $view);
        $this->assertStringContainsString('data-csrf-field="', $view);
        $this->assertStringContainsString('data-csrf-token="', $view);
        $this->assertStringContainsString('client/update', $view);

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

    private function legacyClientUpdateSql(array $post): string
    {
        return "UPDATE client SET `" . $post['name'] . "` = '" . $post['value'] . "' WHERE id = " . (int)$post['pk'];
    }
}
