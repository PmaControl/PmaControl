<?php

declare(strict_types=1);

use App\Controller\Database;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class DatabaseRefreshSecurityTest extends TestCase
{
    public function testRefreshRejectsMissingToken(): void
    {
        $session = [];

        $outcome = Database::evaluateRefreshRequest($this->validPost(), $this->sameSitePostServer(), $session);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid CSRF token', $outcome['body']);
        $this->assertNull($outcome['payload']);
    }

    public function testRefreshRejectsNonPostMethod(): void
    {
        $outcome = Database::evaluateRefreshRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('Method Not Allowed', $outcome['body']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['payload']);
    }

    public function testRefreshRejectsExternalOrigin(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'database.refresh');
        $post = $this->validPost();
        $post[Csrf::DEFAULT_FIELD] = $token;

        $outcome = Database::evaluateRefreshRequest($post, [
            'REQUEST_METHOD' => 'POST',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTPS' => 'on',
            'HTTP_ORIGIN' => 'https://attacker.test',
        ], $session);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
    }

    public function testRefreshAcceptsValidTokenAndNormalizesPayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'database.refresh');
        $post = $this->validPost();
        $post[Csrf::DEFAULT_FIELD] = $token;

        $outcome = Database::evaluateRefreshRequest($post, $this->sameSitePostServer(), $session);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([
            'refresh' => '1',
            'id_mysql_server__from' => 7,
            'id_mysql_server__target' => 8,
            'list' => ['app_db', 'log-db'],
            'path' => '/mysql/backup',
        ], $outcome['payload']);
    }

    public function testRefreshRejectsInvalidPayloads(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'database.refresh');

        foreach ($this->invalidPosts($token) as $post) {
            $outcome = Database::evaluateRefreshRequest($post, $this->sameSitePostServer(), $session);
            $this->assertSame(400, $outcome['status']);
            $this->assertSame('Invalid database refresh payload', $outcome['body']);
            $this->assertNull($outcome['payload']);
        }
    }

    public function testRefreshControllerAndViewUseCsrfGuard(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Database.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Database/refresh.view.php');
        $refreshStart = strpos($controller, 'public function refresh($param)');
        $databaseRefreshStart = strpos($controller, 'public function databaseRefresh($param)');

        $this->assertNotFalse($refreshStart);
        $this->assertNotFalse($databaseRefreshStart);
        $refreshBody = substr($controller, $refreshStart, $databaseRefreshStart - $refreshStart);

        $this->assertStringContainsString("private const DATABASE_REFRESH_CSRF_SCOPE = 'database.refresh'", $controller);
        $this->assertStringContainsString('GroupedFormRequest::evaluate(', $controller);
        $this->assertStringContainsString('Identifier::isDatabaseName($database)', $controller);
        $this->assertStringContainsString('Identifier::isSafeAbsolutePath($payload[\'path\'])', $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::DATABASE_REFRESH_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('evaluateRefreshRequest($_POST, $_SERVER, $_SESSION)', $refreshBody);
        $this->assertStringContainsString('$refreshRequest[\'payload\']', $refreshBody);
        $this->assertStringNotContainsString('$_POST[\'database\'][\'id_mysql_server__from\']', $refreshBody);
        $this->assertStringNotContainsString('$_POST[\'database\'][\'list\']', $refreshBody);
        $this->assertStringNotContainsString('$_POST[\'database\'][\'path\']', $refreshBody);
        $this->assertStringContainsString('name="<?= $databaseRefreshCsrfField ?>"', $view);
        $this->assertStringContainsString('value="<?= $databaseRefreshCsrfToken ?>"', $view);
        $this->assertStringContainsString('method="POST"', $view);
    }

    private function validPost(): array
    {
        return [
            'database' => [
                'refresh' => '1',
                'id_mysql_server__from' => '7',
                'id_mysql_server__target' => '8',
                'list' => [' app_db ', 'log-db'],
                'path' => '/mysql/backup',
            ],
        ];
    }

    private function invalidPosts(string $token): array
    {
        $base = $this->validPost();
        $posts = [];

        $missingGroup = [Csrf::DEFAULT_FIELD => $token];
        $missingMarker = $base;
        unset($missingMarker['database']['refresh']);
        $invalidSource = $base;
        $invalidSource['database']['id_mysql_server__from'] = '7 OR 1=1';
        $emptyList = $base;
        $emptyList['database']['list'] = [];
        $duplicateList = $base;
        $duplicateList['database']['list'] = ['app_db', 'app_db'];
        $badDatabase = $base;
        $badDatabase['database']['list'] = ["app'db"];
        $arrayDatabase = $base;
        $arrayDatabase['database']['list'] = [['app_db']];
        $relativePath = $base;
        $relativePath['database']['path'] = 'mysql/backup';
        $traversalPath = $base;
        $traversalPath['database']['path'] = '/mysql/../backup';
        $shellPath = $base;
        $shellPath['database']['path'] = "/mysql/backup';id";

        foreach (
            [
                $missingGroup,
                $missingMarker,
                $invalidSource,
                $emptyList,
                $duplicateList,
                $badDatabase,
                $arrayDatabase,
                $relativePath,
                $traversalPath,
                $shellPath,
            ] as $post
        ) {
            $post[Csrf::DEFAULT_FIELD] = $token;
            $posts[] = $post;
        }

        return $posts;
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
}
