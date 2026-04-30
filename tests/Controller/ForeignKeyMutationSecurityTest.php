<?php

declare(strict_types=1);

use App\Controller\ForeignKey;
use App\Library\Security\SafeRedirect;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

if (!defined('LINK')) {
    define('LINK', '/');
}

final class ForeignKeyMutationSecurityTest extends TestCase
{
    public function testIdMutationRejectsGetBeforePayload(): void
    {
        $outcome = ForeignKey::evaluateForeignKeyIdMutationRequest(
            [],
            ['id' => '7'],
            ['REQUEST_METHOD' => 'GET'],
            []
        );

        $this->assertFalse($outcome['allowed']);
        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['id']);
    }

    public function testIdMutationAcceptsValidPostAndNormalizesId(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, ForeignKey::FOREIGN_KEY_MUTATION_CSRF_SCOPE);

        $outcome = ForeignKey::evaluateForeignKeyIdMutationRequest(
            [],
            $this->postWithToken(['id' => '00042'], $token),
            $this->sameSitePostServer(),
            $session
        );

        $this->assertTrue($outcome['allowed']);
        $this->assertSame(200, $outcome['status']);
        $this->assertSame(42, $outcome['id']);
        $this->assertSame([42], $outcome['param']);
    }

    public function testIdMutationRejectsMissingTokenAndExternalOrigin(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, ForeignKey::FOREIGN_KEY_MUTATION_CSRF_SCOPE);

        $missingToken = ForeignKey::evaluateForeignKeyIdMutationRequest(
            [],
            ['id' => '7'],
            $this->sameSitePostServer(),
            $session
        );
        $externalOrigin = ForeignKey::evaluateForeignKeyIdMutationRequest(
            [],
            $this->postWithToken(['id' => '7'], $token),
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $externalOrigin['status']);
        $this->assertSame('Invalid request origin', $externalOrigin['body']);
    }

    public function testIdMutationRejectsInjectedRouteOrPostIds(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, ForeignKey::FOREIGN_KEY_MUTATION_CSRF_SCOPE);
        $server = $this->sameSitePostServer();

        $injectedRoute = ForeignKey::evaluateForeignKeyIdMutationRequest(
            ['1 OR 1=1'],
            $this->postWithToken(['id' => '1'], $token),
            $server,
            $session
        );
        $injectedPost = ForeignKey::evaluateForeignKeyIdMutationRequest(
            [],
            $this->postWithToken(['id' => '1 OR 1=1'], $token),
            $server,
            $session
        );
        $zeroId = ForeignKey::evaluateForeignKeyIdMutationRequest(
            [],
            $this->postWithToken(['id' => '0'], $token),
            $server,
            $session
        );

        foreach ([$injectedRoute, $injectedPost, $zeroId] as $outcome) {
            $this->assertFalse($outcome['allowed']);
            $this->assertSame(400, $outcome['status']);
            $this->assertSame('Invalid foreign-key id', $outcome['body']);
            $this->assertNull($outcome['id']);
        }
    }

    public function testIdMutationAllowsCliRouteWithoutCsrf(): void
    {
        $outcome = ForeignKey::evaluateForeignKeyIdMutationRequest(
            ['42'],
            [],
            ['REQUEST_METHOD' => 'GET'],
            [],
            true
        );

        $this->assertTrue($outcome['allowed']);
        $this->assertSame(42, $outcome['id']);
        $this->assertSame([42], $outcome['param']);
    }

    public function testContextMutationAcceptsPostAndRejectsInjectedRoute(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, ForeignKey::FOREIGN_KEY_MUTATION_CSRF_SCOPE);
        $post = $this->postWithToken([
            'id_mysql_server' => '7',
            'database' => 'customer_db',
        ], $token);

        $accepted = ForeignKey::evaluateForeignKeyContextMutationRequest(
            [],
            $post,
            $this->sameSitePostServer(),
            $session
        );
        $injectedRoute = ForeignKey::evaluateForeignKeyContextMutationRequest(
            ['7', "customer_db' OR '1'='1"],
            $post,
            $this->sameSitePostServer(),
            $session
        );

        $this->assertTrue($accepted['allowed']);
        $this->assertSame([7, 'customer_db'], $accepted['param']);
        $this->assertFalse($injectedRoute['allowed']);
        $this->assertSame(400, $injectedRoute['status']);
    }

    public function testServerDatabaseRouteRejectsSqlInjection(): void
    {
        $this->assertSame(
            ['id_mysql_server' => 7, 'database' => 'customer_db', 'param' => [7, 'customer_db']],
            ForeignKey::normalizeServerDatabaseRoute(['7', 'customer_db'])
        );
        $this->assertNull(ForeignKey::normalizeServerDatabaseRoute(['7 OR 1=1', 'customer_db']));
        $this->assertNull(ForeignKey::normalizeServerDatabaseRoute(['7', "customer_db' OR '1'='1"]));
    }

    public function testExternalRefererFallsBackToInternalUrl(): void
    {
        $server = [
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTPS' => 'on',
            'HTTP_REFERER' => 'https://evil.example/after',
        ];

        $this->assertSame(
            '/ForeignKey/index',
            SafeRedirect::refererOrFallback($server, '/ForeignKey/index')
        );
    }

    public function testControllerAndViewsUsePostCsrfAndSafeRedirect(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/ForeignKey.php');
        $mysqlController = (string) file_get_contents(__DIR__ . '/../../App/Controller/Mysql.php');
        $views = [
            (string) file_get_contents(__DIR__ . '/../../App/view/ForeignKey/virtual.view.php'),
            (string) file_get_contents(__DIR__ . '/../../App/view/ForeignKey/fill.view.php'),
            (string) file_get_contents(__DIR__ . '/../../App/view/ForeignKey/real.view.php'),
            (string) file_get_contents(__DIR__ . '/../../App/view/Mysql/mpd.view.php'),
        ];

        $this->assertStringContainsString('use App\\Library\\Security\\PositiveIntegerSelection;', $controller);
        $this->assertStringContainsString('use App\\Library\\Security\\Identifier;', $controller);
        $this->assertStringContainsString('use App\\Library\\Security\\SafeRedirect;', $controller);
        $this->assertStringContainsString('evaluateForeignKeyIdMutationRequest($param, $_POST, $_SERVER, $_SESSION, IS_CLI)', $controller);
        $this->assertStringContainsString('evaluateForeignKeyContextMutationRequest($param, $_POST, $_SERVER, $_SESSION, IS_CLI)', $controller);
        $this->assertStringNotContainsString('HTTP_REFERER', $controller);
        $this->assertStringContainsString('ForeignKey::FOREIGN_KEY_MUTATION_CSRF_SCOPE', $mysqlController);

        foreach ($views as $view) {
            $this->assertStringContainsString("CsrfRender::hiddenInput(\$data, 'foreign_key_mutation')", $view);
            $this->assertStringContainsString('method="post"', $view);
            $this->assertStringNotContainsString("<a href=\"'.LINK.'ForeignKey/addForeignKey/", $view);
            $this->assertStringNotContainsString("<a href=\"'.LINK.'ForeignKey/dropForeignKey/", $view);
            $this->assertStringNotContainsString("<a href=\"'.LINK.'ForeignKey/rmForeignKey/", $view);
            $this->assertStringNotContainsString("<a href=\"'.LINK.'ForeignKey/autoDetect/", $view);
            $this->assertStringNotContainsString("<a href=\"'.LINK.'ForeignKey/import/", $view);
        }
    }

    private function sameSitePostServer(): array
    {
        return [
            'REQUEST_METHOD' => 'POST',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTPS' => 'on',
            'HTTP_ORIGIN' => 'https://pmacontrol.test',
        ];
    }

    private function postWithToken(array $payload, string $token): array
    {
        $payload[Csrf::DEFAULT_FIELD] = $token;

        return $payload;
    }
}
