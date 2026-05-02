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

    public function testForeignKeyRouteQueriesEscapeDatabaseValues(): void
    {
        $db = $this->escapingDb();

        $cache = ForeignKey::buildForeignKeyCacheSelectSql($db, 'foreign_key_virtual', 7, "app'db", true);
        $prefix = ForeignKey::buildForeignKeyRemovePrefixSelectSql($db, 7, "app'db");
        $table = ForeignKey::buildTableExistSql($db, "app'db", "orders' UNION SELECT password FROM user_main--");

        foreach ([$cache, $prefix, $table] as $query) {
            $this->assertStringContainsString("app''db", $query);
            $this->assertStringNotContainsString("app'db", $query);
        }

        $this->assertStringContainsString('ORDER BY id_mysql_server, constraint_schema,constraint_table, constraint_column', $cache);
        $this->assertStringContainsString("database_name='app''db'", $prefix);
        $this->assertStringContainsString("TABLE_NAME`) = LOWER('orders'' UNION SELECT password FROM user_main--')", $table);
    }

    public function testForeignKeyRouteHandlersUseNormalizedAndEscapedQueries(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/ForeignKey.php');

        $fillBody = $this->methodBody($controller, 'public function fill($param)', 'public function getPrefix($param)');
        $prefixBody = $this->methodBody($controller, 'public function getPrefix($param)', 'public function getIdPosition($param)');
        $combinationBody = $this->methodBody($controller, 'public function getConbinaison($param)', 'public function getDatabase');
        $tableExistBody = $this->methodBody($controller, 'public function isTableExist($param)', 'public function cleanUp($param)');
        $realBody = $this->methodBody($controller, 'public function getRealForeignKey($param)', 'public function importRealForeignKey($param)');
        $virtualBody = $this->methodBody($controller, 'public function virtual($param)', 'public function real($param)');
        $realViewBody = $this->methodBody($controller, 'public function real($param)', 'public function proposal($param)');
        $proposalBody = $this->methodBody($controller, 'public function proposal($param)', 'public function blackList($param)');
        $blacklistBody = $this->methodBody($controller, 'public function blackList($param)', 'public function custom($param)');

        foreach ([$fillBody, $prefixBody, $combinationBody, $tableExistBody, $realBody, $virtualBody, $realViewBody, $proposalBody, $blacklistBody] as $body) {
            $this->assertStringContainsString('$route = self::normalizeServerDatabaseRoute($param);', $body);
            $this->assertStringNotContainsString('$id_mysql_server = $param[0];', $body);
            $this->assertStringNotContainsString('$database_name   = $param[1];', $body);
            $this->assertStringNotContainsString('$database        = $param[1];', $body);
        }

        $this->assertStringContainsString('self::buildForeignKeyCacheSelectSql($db, \'foreign_key_virtual\', $id_mysql_server, $database)', $fillBody);
        $this->assertStringContainsString('self::buildForeignKeyRemovePrefixSelectSql($db, $id_mysql_server, $database_name)', $prefixBody);
        $this->assertStringContainsString('self::buildTableExistSql($db, $database_name, $table_name)', $tableExistBody);
        $this->assertStringContainsString('self::buildForeignKeyCacheSelectSql($db, \'foreign_key_real\', $id_mysql_server, $database)', $realBody);
        $this->assertStringContainsString('self::buildForeignKeyCacheSelectSql($db, \'foreign_key_virtual\', $id_mysql_server, $database, true)', $virtualBody);
        $this->assertStringContainsString('self::buildForeignKeyCacheSelectSql($db, \'foreign_key_real\', $id_mysql_server, $database, true)', $realViewBody);
        $this->assertStringContainsString('self::buildForeignKeyCacheSelectSql($db, \'foreign_key_proposal\', $id_mysql_server, $database, true)', $proposalBody);
        $this->assertStringContainsString('self::buildForeignKeyCacheSelectSql($db, \'foreign_key_blacklist\', $id_mysql_server, $database, true)', $blacklistBody);
        $this->assertStringContainsString('$databaseSql = $db->sql_real_escape_string($database);', $controller);
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

    private function escapingDb(): object
    {
        return new class {
            public function sql_real_escape_string($value): string
            {
                return str_replace("'", "''", (string) $value);
            }
        };
    }

    private function methodBody(string $source, string $startNeedle, string $endNeedle): string
    {
        $start = strpos($source, $startNeedle);
        $end = strpos($source, $endNeedle, $start === false ? 0 : $start);

        $this->assertIsInt($start);
        $this->assertIsInt($end);

        return substr($source, $start, $end - $start);
    }
}
