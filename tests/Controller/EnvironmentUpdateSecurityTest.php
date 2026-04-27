<?php

declare(strict_types=1);

use App\Controller\Environment;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class EnvironmentUpdateSecurityTest extends TestCase
{
    public function testUpdateRequestAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'environment.update');

        $outcome = Environment::evaluateUpdateRequest(
            [Csrf::DEFAULT_FIELD => $token, 'name' => 'libelle', 'value' => 'Production', 'pk' => '7'],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(['field' => 'libelle', 'value' => 'Production', 'id' => 7], $outcome['update']);
    }

    public function testUpdateRequestRejectsNonPost(): void
    {
        $outcome = Environment::evaluateUpdateRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['update']);
    }

    public function testUpdateRequestRejectsExternalOriginBeforePayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'environment.update');

        $outcome = Environment::evaluateUpdateRequest(
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

        $missingToken = Environment::evaluateUpdateRequest(
            ['name' => 'libelle', 'value' => 'Production', 'pk' => '7'],
            $server,
            $session
        );
        $foreignScope = Environment::evaluateUpdateRequest(
            [Csrf::DEFAULT_FIELD => $foreignToken, 'name' => 'libelle', 'value' => 'Production', 'pk' => '7'],
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
        $this->assertNull(Environment::normalizeUpdatePayload(['name' => 'id', 'value' => '8', 'pk' => '7']));
        $this->assertNull(Environment::normalizeUpdatePayload(['name' => 'libelle` = 1 --', 'value' => 'x', 'pk' => '7']));
        $this->assertNull(Environment::normalizeUpdatePayload(['name' => 'libelle', 'value' => 'x', 'pk' => '0']));
        $this->assertNull(Environment::normalizeUpdatePayload(['name' => 'libelle', 'value' => 'x', 'pk' => '7 OR 1=1']));
        $this->assertNull(Environment::normalizeUpdatePayload(['name' => 'libelle', 'value' => str_repeat('a', 256), 'pk' => '7']));
        $this->assertNull(Environment::normalizeUpdatePayload(['name' => 'libelle', 'pk' => '7']));
        $this->assertNull(Environment::normalizeUpdatePayload(['name' => ['libelle'], 'value' => 'x', 'pk' => '7']));
        $this->assertNull(Environment::normalizeUpdatePayload(['name' => 'libelle', 'value' => ['x'], 'pk' => '7']));
    }

    public function testUpdatePayloadAllowsEditableFieldsAndEmptyValue(): void
    {
        $this->assertSame(
            ['field' => 'letter', 'value' => '', 'id' => 7],
            Environment::normalizeUpdatePayload(['name' => 'letter', 'value' => '', 'pk' => '7'])
        );
        $this->assertSame(
            ['field' => 'class', 'value' => 'primary', 'id' => 7],
            Environment::normalizeUpdatePayload(['name' => 'class', 'value' => 'primary', 'pk' => '7'])
        );
    }

    public function testUpdateSqlEscapesValueAndUsesValidatedIdentifier(): void
    {
        $sql = Environment::buildEnvironmentUpdateSql(
            ['field' => 'libelle', 'value' => "Bob's env", 'id' => 7],
            static fn (string $value): string => str_replace("'", "\\'", $value)
        );

        $this->assertSame("UPDATE environment SET `libelle` = 'Bob\\'s env' WHERE id = 7", $sql);
    }

    public function testExternalPostWouldPassLegacyPostGateButIsRejectedBeforeSql(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'environment.update');
        $post = [Csrf::DEFAULT_FIELD => $token, 'name' => 'libelle', 'value' => 'Owned', 'pk' => '7'];
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://attacker.test',
        ];

        $this->assertTrue(strtoupper($server['REQUEST_METHOD']) === 'POST');
        $this->assertSame("UPDATE environment SET `libelle` = 'Owned' WHERE id = 7", $this->legacyEnvironmentUpdateSql($post));

        $outcome = Environment::evaluateUpdateRequest($post, $server, $session);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['update']);
    }

    public function testEnvironmentUpdateUsesSharedCsrfLibraryAndInlineEditSendsToken(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Environment.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Environment/index.view.php');
        $javascript = (string) file_get_contents(__DIR__ . '/../../App/Webroot/js/Tree/index.js');

        $this->assertStringContainsString('use App\\Library\\Security\\InlineEditRequest;', $controller);
        $this->assertStringContainsString("private const ENVIRONMENT_UPDATE_CSRF_SCOPE = 'environment.update'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::ENVIRONMENT_UPDATE_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('InlineEditRequest::evaluate(', $controller);
        $this->assertStringContainsString('UPDATE environment SET', $controller);
        $this->assertStringNotContainsString('$_POST[\'name\']', $controller);
        $this->assertStringNotContainsString('$_POST[\'value\']', $controller);

        $this->assertStringContainsString('$environmentUpdateCsrfAttributes', $view);
        $this->assertStringContainsString('data-csrf-field="', $view);
        $this->assertStringContainsString('data-csrf-token="', $view);
        $this->assertStringContainsString("data-pk=\"' . (int) \$env['id'] . '\"", $view);
        $this->assertStringContainsString('environment/update', $view);

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

    private function legacyEnvironmentUpdateSql(array $post): string
    {
        return "UPDATE environment SET `" . $post['name'] . "` = '" . $post['value'] . "' WHERE id = " . $post['pk'];
    }
}
