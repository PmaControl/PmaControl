<?php

declare(strict_types=1);

use App\Controller\Database;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class DatabaseCreateSecurityTest extends TestCase
{
    public function testCreateRejectsMissingToken(): void
    {
        $session = [];

        $outcome = Database::evaluateCreateRequest($this->validPost(), $this->sameSitePostServer(), $session);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid CSRF token', $outcome['body']);
        $this->assertNull($outcome['payload']);
    }

    public function testCreateRejectsExternalOrigin(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'database.create');
        $post = $this->validPost();
        $post[Csrf::DEFAULT_FIELD] = $token;

        $outcome = Database::evaluateCreateRequest($post, [
            'REQUEST_METHOD' => 'POST',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTPS' => 'on',
            'HTTP_ORIGIN' => 'https://attacker.test',
        ], $session);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
    }

    public function testCreateRejectsNonPostMethod(): void
    {
        $outcome = Database::evaluateCreateRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
    }

    public function testCreateAcceptsValidTokenAndNormalizesPayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'database.create');
        $post = $this->validPost();
        $post[Csrf::DEFAULT_FIELD] = $token;

        $outcome = Database::evaluateCreateRequest($post, $this->sameSitePostServer(), $session);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([
            'id_mysql_server' => [7, 8],
            'user' => 'app_user',
            'hostname' => '10.0.0.%',
            'id_mysql_privilege' => ['SELECT', 'CREATE TEMPORARY TABLES'],
            'databases' => ['app_db', 'log-db'],
        ], $outcome['payload']);
    }

    public function testCreateDefaultsUserHostAndPrivileges(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'database.create');
        $post = $this->validPost();
        unset($post['database']['user'], $post['database']['hostname'], $post['database']['id_mysql_privilege']);
        $post[Csrf::DEFAULT_FIELD] = $token;

        $outcome = Database::evaluateCreateRequest($post, $this->sameSitePostServer(), $session);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('', $outcome['payload']['user']);
        $this->assertSame('%', $outcome['payload']['hostname']);
        $this->assertSame([], $outcome['payload']['id_mysql_privilege']);
    }

    public function testCreateRejectsInvalidPayloads(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'database.create');

        foreach ($this->invalidPosts($token) as $post) {
            $outcome = Database::evaluateCreateRequest($post, $this->sameSitePostServer(), $session);
            $this->assertSame(400, $outcome['status']);
            $this->assertSame('Invalid database create payload', $outcome['body']);
            $this->assertNull($outcome['payload']);
        }
    }

    public function testCreateControllerAndViewUseCsrfGuard(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Database.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Database/create.view.php');
        $createStart = strpos($controller, 'public function create()');
        $passwordStart = strpos($controller, 'function generatePassword');

        $this->assertNotFalse($createStart);
        $this->assertNotFalse($passwordStart);
        $createBody = substr($controller, $createStart, $passwordStart - $createStart);

        $this->assertStringContainsString("private const DATABASE_CREATE_CSRF_SCOPE = 'database.create'", $controller);
        $this->assertStringContainsString('GroupedFormRequest::evaluate(', $controller);
        $this->assertStringContainsString('Identifier::normalizeDatabaseNameList($payload[\'name\'])', $controller);
        $this->assertStringContainsString('ON `".$database."`.*', $controller);
        $this->assertStringContainsString('evaluateCreateRequest($_POST, $_SERVER, $_SESSION)', $createBody);
        $this->assertStringContainsString('$createRequest[\'payload\']', $createBody);
        $this->assertStringNotContainsString('$_POST[\'database\'][\'id_mysql_server\']', $createBody);
        $this->assertStringNotContainsString('$_POST[\'database\'][\'name\']', $createBody);
        $this->assertStringNotContainsString('$_POST[\'database\'][\'id_mysql_privilege\']', $createBody);
        $this->assertStringContainsString('name="<?= $databaseCreateCsrfField ?>"', $view);
        $this->assertStringContainsString('value="<?= $databaseCreateCsrfToken ?>"', $view);
        $this->assertStringContainsString('method="POST"', $view);
    }

    private function validPost(): array
    {
        return [
            'database' => [
                'create' => '1',
                'id_mysql_server' => ['7', '8'],
                'name' => ' app_db, log-db ',
                'user' => ' app_user ',
                'gg' => '@',
                'hostname' => ' 10.0.0.% ',
                'id_mysql_privilege' => ['SELECT', 'CREATE TEMPORARY TABLES'],
            ],
        ];
    }

    private function invalidPosts(string $token): array
    {
        $base = $this->validPost();
        $missingGroup = [Csrf::DEFAULT_FIELD => $token];
        $invalidServer = $base;
        $invalidServer['database']['id_mysql_server'] = ['7 OR 1=1'];
        $emptyDatabases = $base;
        $emptyDatabases['database']['name'] = ' , ';
        $badDatabase = $base;
        $badDatabase['database']['name'] = "app'db";
        $duplicateDatabase = $base;
        $duplicateDatabase['database']['name'] = 'app_db,app_db';
        $badUser = $base;
        $badUser['database']['user'] = "app'user";
        $badHost = $base;
        $badHost['database']['hostname'] = "host'owned";
        $badPrivilege = $base;
        $badPrivilege['database']['id_mysql_privilege'] = ['SELECT, DROP'];

        $posts = [$missingGroup, $invalidServer, $emptyDatabases, $badDatabase, $duplicateDatabase, $badUser, $badHost, $badPrivilege];
        foreach ($posts as &$post) {
            $post[Csrf::DEFAULT_FIELD] = $token;
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
