<?php

declare(strict_types=1);

use App\Controller\Mysql;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class MysqlPlayskoolSecurityTest extends TestCase
{
    public function testPlayskoolRequestAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'mysql.playskool');

        $outcome = Mysql::evaluatePlayskoolRequest(
            $this->validPost($token),
            $this->sameSitePostServer(),
            $session,
            $this->availableDbs()
        );

        $this->assertTrue($outcome['allowed']);
        $this->assertSame(200, $outcome['status']);
        $this->assertSame(
            [
                'dbs' => ['main-db'],
                'login' => 'app',
                'password' => 'secret',
                'sql' => "SELECT 'ok'",
            ],
            $outcome['request']
        );
    }

    public function testPlayskoolRequestRejectsNonPost(): void
    {
        $outcome = Mysql::evaluatePlayskoolRequest([], ['REQUEST_METHOD' => 'GET'], [], $this->availableDbs());

        $this->assertFalse($outcome['allowed']);
        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['request']);
    }

    public function testPlayskoolRequestRejectsExternalOriginBeforePayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'mysql.playskool');

        $outcome = Mysql::evaluatePlayskoolRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'db' => ['evil-db' => ['on']],
            ],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session,
            $this->availableDbs()
        );

        $this->assertFalse($outcome['allowed']);
        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['request']);
    }

    public function testPlayskoolRequestRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'mysqlrouter.add');
        $server = $this->sameSitePostServer();

        $missingToken = Mysql::evaluatePlayskoolRequest($this->validPost(null), $server, $session, $this->availableDbs());
        $foreignScope = Mysql::evaluatePlayskoolRequest($this->validPost($foreignToken), $server, $session, $this->availableDbs());

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertFalse($missingToken['allowed']);
        $this->assertNull($missingToken['request']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
        $this->assertFalse($foreignScope['allowed']);
        $this->assertNull($foreignScope['request']);
    }

    public function testPlayskoolRequestRejectsInvalidPayloadAfterValidCsrf(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'mysql.playskool');
        $server = $this->sameSitePostServer();

        foreach ($this->invalidPayloads($token) as $post) {
            $outcome = Mysql::evaluatePlayskoolRequest($post, $server, $session, $this->availableDbs());

            $this->assertFalse($outcome['allowed']);
            $this->assertSame(400, $outcome['status']);
            $this->assertSame('Invalid MySQL playskool payload', $outcome['body']);
            $this->assertNull($outcome['request']);
        }
    }

    public function testPlayskoolCommandsEscapeShellArgumentsAndAvoidPasswordFlag(): void
    {
        $commands = Mysql::buildPlayskoolCommands([
            'dbs' => ['main-db'],
            'login' => "app'; id",
            'password' => "sec'ret",
            'sql' => "SELECT 'ok';",
        ]);

        $this->assertSame(
            [
                'MYSQL_PWD=' . escapeshellarg("sec'ret")
                    . ' mysql -h ' . escapeshellarg('main-db')
                    . ' -u ' . escapeshellarg("app'; id")
                    . ' -e ' . escapeshellarg("SELECT 'ok';")
                    . ' > ' . escapeshellarg('main-db.log'),
            ],
            $commands
        );
        $this->assertStringNotContainsString(' -p', $commands[0]);
    }

    public function testPlayskoolUsesSharedCsrfGuardAndViewSendsToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Mysql.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/Mysql/playskool.view.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);

        $this->assertStringContainsString('use App\\Library\\Security\\CsrfGuard;', $controller);
        $this->assertStringContainsString('use Glial\\Security\\Csrf;', $controller);
        $this->assertStringContainsString("private const MYSQL_PLAYSKOOL_CSRF_SCOPE = 'mysql.playskool'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::MYSQL_PLAYSKOOL_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, self::MYSQL_PLAYSKOOL_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('evaluatePlayskoolRequest($_POST, $_SERVER, $_SESSION, $data[\'dbs\'])', $controller);
        $this->assertStringContainsString('buildPlayskoolCommands($outcome[\'request\'])', $controller);

        $this->assertStringContainsString('$mysqlPlayskoolCsrfField', $view);
        $this->assertStringContainsString('$mysqlPlayskoolCsrfToken', $view);
        $this->assertStringContainsString('method="post"', $view);
        $this->assertStringContainsString('type="hidden"', $view);
        $this->assertStringContainsString('htmlspecialchars((string)$command, ENT_QUOTES, \'UTF-8\')', $view);
        $this->assertStringNotContainsString('$data[\'ret\']', $view);
    }

    private function validPost(?string $token): array
    {
        $post = [
            'db' => ['main-db' => 'on'],
            'login' => ' app ',
            'password' => ' secret ',
            'sql' => " SELECT 'ok' ",
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
            [Csrf::DEFAULT_FIELD => $token],
            array_replace($base, ['db' => 'main-db']),
            array_replace($base, ['db' => ['unknown-db' => 'on']]),
            array_replace($base, ['db' => ['main-db' => ['on']]]),
            array_replace($base, ['login' => '']),
            array_replace($base, ['login' => ['app']]),
            array_replace($base, ['login' => str_repeat('a', 256)]),
            array_replace($base, ['password' => '']),
            array_replace($base, ['sql' => '']),
            array_replace($base, ['sql' => ['SELECT 1']]),
            array_replace($base, ['sql' => str_repeat('s', 65536)]),
        ];
    }

    private function availableDbs(): array
    {
        return ['main_db', 'replica_db'];
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
