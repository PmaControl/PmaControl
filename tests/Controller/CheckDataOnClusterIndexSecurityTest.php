<?php

declare(strict_types=1);

use App\Controller\CheckDataOnCluster;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class CheckDataOnClusterIndexSecurityTest extends TestCase
{
    public function testIndexRequestAllowsGetRenderWithoutExecutableSelection(): void
    {
        $outcome = CheckDataOnCluster::evaluateIndexRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([
            'ids' => [],
            'id_list' => '',
            'database' => '',
            'sql' => '',
        ], $outcome['selection']);
    }

    public function testLegacyGetPayloadDoesNotBecomeExecutableSelection(): void
    {
        $outcome = CheckDataOnCluster::evaluateIndexRequest(
            $this->validPost(null),
            ['REQUEST_METHOD' => 'GET'],
            []
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([], $outcome['selection']['ids']);
        $this->assertSame('', $outcome['selection']['sql']);
    }

    public function testIndexRequestAcceptsValidPostToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'check_data_on_cluster.index');

        $outcome = CheckDataOnCluster::evaluateIndexRequest(
            $this->validPost($token),
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([
            'ids' => [3, 4],
            'id_list' => '3,4',
            'database' => 'main_db',
            'sql' => 'SHOW VARIABLES',
        ], $outcome['selection']);
    }

    public function testIndexRequestRejectsUnsupportedMethods(): void
    {
        foreach (['PUT', 'DELETE', 'PATCH'] as $method) {
            $outcome = CheckDataOnCluster::evaluateIndexRequest([], ['REQUEST_METHOD' => $method], []);

            $this->assertSame(405, $outcome['status']);
            $this->assertSame('Method Not Allowed', $outcome['body']);
            $this->assertSame('GET, HEAD, POST', $outcome['headers']['Allow']);
            $this->assertNull($outcome['selection']);
        }
    }

    public function testIndexRequestRejectsExternalOriginBeforePayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'check_data_on_cluster.index');

        $outcome = CheckDataOnCluster::evaluateIndexRequest(
            [Csrf::DEFAULT_FIELD => $token, 'mysql_cluster' => ['id' => ['invalid']]],
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
        $this->assertNull($outcome['selection']);
    }

    public function testIndexRequestRejectsMissingForeignOrCrossSessionToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'check_config.index');
        $validToken = Csrf::issueToken($session, 'check_data_on_cluster.index');
        $server = $this->sameSitePostServer();

        $missingToken = CheckDataOnCluster::evaluateIndexRequest($this->validPost(null), $server, $session);
        $foreignScope = CheckDataOnCluster::evaluateIndexRequest($this->validPost($foreignToken), $server, $session);
        $crossSession = CheckDataOnCluster::evaluateIndexRequest($this->validPost($validToken), $server, []);

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
        $this->assertSame(403, $crossSession['status']);
        $this->assertSame('Invalid CSRF token', $crossSession['body']);
    }

    public function testIndexRequestRejectsInvalidPayloadAfterValidCsrf(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'check_data_on_cluster.index');
        $server = $this->sameSitePostServer();

        foreach ($this->invalidPayloads($token) as $post) {
            $outcome = CheckDataOnCluster::evaluateIndexRequest($post, $server, $session);

            $this->assertSame(400, $outcome['status']);
            $this->assertSame('Invalid cluster data check payload', $outcome['body']);
            $this->assertNull($outcome['selection']);
        }
    }

    public function testIndexRequestRejectsWriteSqlAfterValidCsrf(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'check_data_on_cluster.index');
        $server = $this->sameSitePostServer();

        foreach ($this->writeSqlPayloads($token) as $post) {
            $outcome = CheckDataOnCluster::evaluateIndexRequest($post, $server, $session);

            $this->assertSame(422, $outcome['status']);
            $this->assertSame('Statement must be read-only', $outcome['body']);
            $this->assertNull($outcome['selection']);
        }
    }

    public function testIndexUsesPostCsrfWithoutExecutingSqlFromGet(): void
    {
        $controller = (string)file_get_contents(__DIR__ . '/../../App/Controller/CheckDataOnCluster.php');
        $view = (string)file_get_contents(__DIR__ . '/../../App/view/CheckDataOnCluster/index.view.php');

        $this->assertStringContainsString("private const INDEX_CSRF_SCOPE = 'check_data_on_cluster.index'", $controller);
        $this->assertStringContainsString('ClusterDataCheckRequest::evaluate($post, $server, $session, self::INDEX_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('self::evaluateIndexRequest($_POST, $_SERVER, $_SESSION)', $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::INDEX_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('$selection[\'id_list\']', $controller);
        $this->assertStringContainsString('$db_link->sql_query($selection[\'sql\'])', $controller);
        $this->assertStringContainsString('SET SESSION TRANSACTION READ ONLY', $controller);
        $this->assertStringContainsString('START TRANSACTION READ ONLY', $controller);
        $this->assertStringContainsString('ROLLBACK', $controller);
        $this->assertStringContainsString('SET SESSION TRANSACTION READ WRITE', $controller);
        $this->assertStringNotContainsString('sql_query($_GET[\'sql\'])', $controller);
        $this->assertStringNotContainsString('urlencode($_POST[\'sql\'])', $controller);
        $this->assertStringNotContainsString('$_POST[\'mysql_cluster\'][\'id\']', $controller);

        $this->assertStringContainsString('$checkDataOnClusterCsrfField', $view);
        $this->assertStringContainsString('$checkDataOnClusterCsrfToken', $view);
        $this->assertStringContainsString('<form method="post" action="">', $view);
        $this->assertStringContainsString('type="hidden"', $view);
        $this->assertStringContainsString('htmlspecialchars((string) $sql', $view);
    }

    private function validPost(?string $token, array $overrides = []): array
    {
        $post = array_replace_recursive([
            'mysql_cluster' => [
                'id' => '3,4',
                'database' => ' main_db ',
            ],
            'sql' => ' SHOW VARIABLES ',
        ], $overrides);

        if ($token !== null) {
            $post[Csrf::DEFAULT_FIELD] = $token;
        }

        return $post;
    }

    private function invalidPayloads(string $token): array
    {
        return [
            [Csrf::DEFAULT_FIELD => $token],
            $this->validPost($token, ['mysql_cluster' => 'invalid']),
            $this->validPost($token, ['mysql_cluster' => ['id' => '3 OR 1=1']]),
            $this->validPost($token, ['mysql_cluster' => ['id' => '0,4']]),
            $this->validPost($token, ['mysql_cluster' => ['id' => '3,3']]),
            $this->validPost($token, ['mysql_cluster' => ['database' => 'main.prod']]),
            $this->validPost($token, ['mysql_cluster' => ['unexpected' => '1']]),
            $this->validPost($token, ['sql' => '']),
            $this->validPost($token, ['sql' => ['SHOW VARIABLES']]),
            $this->validPost($token, ['sql' => "SELECT " . chr(0)]),
        ];
    }

    private function writeSqlPayloads(string $token): array
    {
        return [
            $this->validPost($token, ['sql' => 'UPDATE mysql_server SET name = name']),
            $this->validPost($token, ['sql' => 'DROP TABLE mysql.user']),
            $this->validPost($token, ['sql' => 'SELECT 1; DROP TABLE mysql.user']),
            $this->validPost($token, ['sql' => '/*x*/ UPDATE mysql_server SET name = name']),
            $this->validPost($token, ['sql' => 'SELECT * FROM mysql_server FOR UPDATE']),
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
