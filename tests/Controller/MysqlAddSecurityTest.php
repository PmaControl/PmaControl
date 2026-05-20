<?php

declare(strict_types=1);

use App\Controller\Mysql;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class MysqlAddSecurityTest extends TestCase
{
    public function testAddRequestAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'mysql.add');

        $outcome = Mysql::evaluateAddRequest(
            $this->validPost($token),
            $this->sameSitePostServer(),
            $session
        );

        $this->assertTrue($outcome['allowed']);
        $this->assertSame(200, $outcome['status']);
        $this->assertSame(
            [
                'display_name' => 'Primary DB',
                'ip' => 'db-primary.local',
                'port' => 3306,
                'login' => 'root',
                'password' => 'secret',
                'is_proxy' => 1,
                'is_vip' => 0,
                'id_client' => 2,
                'id_environement' => 3,
            ],
            $outcome['mysql_server']
        );
    }

    public function testAddRequestRejectsNonPost(): void
    {
        $outcome = Mysql::evaluateAddRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertFalse($outcome['allowed']);
        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['mysql_server']);
    }

    public function testAddRequestRejectsExternalOriginBeforePayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'mysql.add');

        $outcome = Mysql::evaluateAddRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'mysql_server' => ['ip' => ['invalid']],
            ],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session
        );

        $this->assertFalse($outcome['allowed']);
        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['mysql_server']);
    }

    public function testAddRequestRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'mysql.playskool');
        $server = $this->sameSitePostServer();

        $missingToken = Mysql::evaluateAddRequest($this->validPost(null), $server, $session);
        $foreignScope = Mysql::evaluateAddRequest($this->validPost($foreignToken), $server, $session);

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertFalse($missingToken['allowed']);
        $this->assertNull($missingToken['mysql_server']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
        $this->assertFalse($foreignScope['allowed']);
        $this->assertNull($foreignScope['mysql_server']);
    }

    public function testAddRequestRejectsInvalidPayloadAfterValidCsrf(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'mysql.add');
        $server = $this->sameSitePostServer();

        foreach ($this->invalidPayloads($token) as $post) {
            $outcome = Mysql::evaluateAddRequest($post, $server, $session);

            $this->assertFalse($outcome['allowed']);
            $this->assertSame(400, $outcome['status']);
            $this->assertSame('Invalid MySQL add payload', $outcome['body']);
            $this->assertNull($outcome['mysql_server']);
        }
    }

    public function testAddUsesSharedCsrfGuardBeforeSideEffectsAndViewSendsToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Mysql.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/Mysql/add.view.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);

        $addPosition = strpos($controller, 'public function add($param)');
        $evaluatePosition = strpos($controller, '$outcome = self::evaluateAddRequest($_POST, $_SERVER, $_SESSION);', $addPosition);
        $dbPosition = strpos($controller, '$db = Sgbd::sql(DB_DEFAULT);', $addPosition);
        $scanPosition = strpos($controller, '$this->scanPort', $addPosition);
        $testPosition = strpos($controller, '$this->testMySQL', $addPosition);
        $savePosition = strpos($controller, '$db->sql_save($table)', $addPosition);

        $this->assertStringContainsString('use App\\Library\\Security\\CsrfGuard;', $controller);
        $this->assertStringContainsString('use Glial\\Security\\Csrf;', $controller);
        $this->assertStringContainsString("private const MYSQL_ADD_CSRF_SCOPE = 'mysql.add'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::MYSQL_ADD_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::ensureOrFail($post, $server, $session, self::MYSQL_ADD_CSRF_SCOPE)', $controller);
        $this->assertIsInt($addPosition);
        $this->assertIsInt($evaluatePosition);
        $this->assertIsInt($dbPosition);
        $this->assertIsInt($scanPosition);
        $this->assertIsInt($testPosition);
        $this->assertIsInt($savePosition);
        $this->assertLessThan($dbPosition, $evaluatePosition);
        $this->assertLessThan($scanPosition, $evaluatePosition);
        $this->assertLessThan($testPosition, $evaluatePosition);
        $this->assertLessThan($savePosition, $evaluatePosition);

        $this->assertStringContainsString('$mysqlAddCsrfField', $view);
        $this->assertStringContainsString('$mysqlAddCsrfToken', $view);
        $this->assertStringContainsString('<input type="hidden" name="<?= $mysqlAddCsrfField ?>" value="<?= $mysqlAddCsrfToken ?>">', $view);
        $this->assertStringContainsString('method="post"', $view);
    }

    private function validPost(?string $token): array
    {
        $post = [
            'mysql_server' => [
                'display_name' => ' Primary DB ',
                'ip' => ' db-primary.local ',
                'port' => '3306',
                'login' => ' root ',
                'password' => ' secret ',
                'id_client' => '2',
                'id_environement' => '3',
                'is_proxy' => '1',
            ],
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
            [Csrf::DEFAULT_FIELD => $token, 'mysql_server' => 'invalid'],
            $this->withMysqlServerField($base, 'ip', ''),
            $this->withMysqlServerField($base, 'ip', 'https://db-primary.local'),
            $this->withMysqlServerField($base, 'ip', 'db-primary.local/path'),
            $this->withMysqlServerField($base, 'ip', ['db-primary.local']),
            $this->withMysqlServerField($base, 'port', '0'),
            $this->withMysqlServerField($base, 'port', '65536'),
            $this->withMysqlServerField($base, 'port', '3306x'),
            $this->withMysqlServerField($base, 'login', ''),
            $this->withMysqlServerField($base, 'password', ''),
            $this->withMysqlServerField($base, 'display_name', str_repeat('a', 256)),
            $this->withMysqlServerField($base, 'id_client', '0'),
            $this->withMysqlServerField($base, 'id_environement', 'env'),
        ];
    }

    private function withMysqlServerField(array $post, string $field, $value): array
    {
        $post['mysql_server'][$field] = $value;
        return $post;
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
