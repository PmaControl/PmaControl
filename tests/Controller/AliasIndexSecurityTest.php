<?php

declare(strict_types=1);

use App\Controller\Alias;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class AliasIndexSecurityTest extends TestCase
{
    public function testIndexPostAcceptsValidSameSiteToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'alias.index');

        $outcome = Alias::evaluateIndexPostRequest(
            $this->aliasPost($token),
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([
            'dns' => 'db01.internal',
            'port' => 3306,
            'id_mysql_server' => 42,
        ], $outcome['alias']);
    }

    public function testIndexPostRejectsNonPost(): void
    {
        $outcome = Alias::evaluateIndexPostRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['alias']);
    }

    public function testIndexPostRejectsExternalOriginBeforePayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'alias.index');

        $outcome = Alias::evaluateIndexPostRequest(
            [Csrf::DEFAULT_FIELD => $token, 'alias_dns' => 'invalid'],
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
        $this->assertNull($outcome['alias']);
    }

    public function testIndexPostRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'client.add');
        $server = $this->sameSitePostServer();

        $missingToken = Alias::evaluateIndexPostRequest($this->aliasPost(''), $server, $session);
        $foreignScope = Alias::evaluateIndexPostRequest($this->aliasPost($foreignToken), $server, $session);

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testIndexPayloadRejectsMalformedValues(): void
    {
        foreach ($this->invalidPayloads() as $post) {
            $this->assertNull(Alias::normalizeIndexPayload($post));
        }
    }

    public function testExternalPostWouldPassLegacyPostGateButIsRejectedBeforeUpsert(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'alias.index');
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://attacker.test',
        ];

        $this->assertSame('POST', $server['REQUEST_METHOD']);
        $this->assertSame([
            'dns' => 'db01.internal',
            'port' => 3306,
            'id_mysql_server' => 42,
        ], Alias::normalizeIndexPayload($this->aliasPost($token)));

        $outcome = Alias::evaluateIndexPostRequest($this->aliasPost($token), $server, $session);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['alias']);
    }

    public function testAliasIndexUsesSharedCsrfGuardAndFormsSendToken(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Alias.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Alias/index.view.php');

        $this->assertStringContainsString("private const ALIAS_INDEX_CSRF_SCOPE = 'alias.index'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::ALIAS_INDEX_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::ensureOrFail($post, $server, $session, self::ALIAS_INDEX_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('self::evaluateIndexPostRequest($_POST, $_SERVER, $_SESSION)', $controller);
        $this->assertStringContainsString('self::upsertAliasDnsFromRow($indexRequest[\'alias\'])', $controller);

        $this->assertStringContainsString('$aliasIndexCsrfField', $view);
        $this->assertStringContainsString('$aliasIndexCsrfToken', $view);
        $this->assertStringContainsString('<input type="hidden" name="', $view);
        $this->assertStringContainsString('" value="', $view);
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

    private function aliasPost(string $token, array $overrides = []): array
    {
        return [
            Csrf::DEFAULT_FIELD => $token,
            'alias_dns' => array_replace([
                'dns' => 'db01.internal',
                'port' => '3306',
                'id_mysql_server' => '42',
            ], $overrides),
        ];
    }

    private function invalidPayloads(): array
    {
        return [
            [],
            ['alias_dns' => 'invalid'],
            ['alias_dns' => []],
            ['alias_dns' => ['dns' => '', 'port' => '3306', 'id_mysql_server' => '42']],
            ['alias_dns' => ['dns' => str_repeat('a', 201), 'port' => '3306', 'id_mysql_server' => '42']],
            ['alias_dns' => ['dns' => "db01\ninternal", 'port' => '3306', 'id_mysql_server' => '42']],
            ['alias_dns' => ['dns' => 'db01.internal', 'port' => '0', 'id_mysql_server' => '42']],
            ['alias_dns' => ['dns' => 'db01.internal', 'port' => '70000', 'id_mysql_server' => '42']],
            ['alias_dns' => ['dns' => 'db01.internal', 'port' => '3306 OR 1=1', 'id_mysql_server' => '42']],
            ['alias_dns' => ['dns' => 'db01.internal', 'port' => '3306', 'id_mysql_server' => '0']],
            ['alias_dns' => ['dns' => 'db01.internal', 'port' => '3306', 'id_mysql_server' => '42 OR 1=1']],
        ];
    }
}
