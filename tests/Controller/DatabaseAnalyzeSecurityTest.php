<?php

declare(strict_types=1);

use App\Controller\Database;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class DatabaseAnalyzeSecurityTest extends TestCase
{
    public function testAnalyzeRejectsMissingToken(): void
    {
        $outcome = Database::evaluateAnalyzeRequest($this->validPost(), $this->sameSitePostServer(), []);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid CSRF token', $outcome['body']);
        $this->assertNull($outcome['payload']);
    }

    public function testAnalyzeRejectsNonPostMethod(): void
    {
        $outcome = Database::evaluateAnalyzeRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('Method Not Allowed', $outcome['body']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['payload']);
    }

    public function testAnalyzeRejectsExternalOrigin(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'database.analyze');
        $post = $this->validPost();
        $post[Csrf::DEFAULT_FIELD] = $token;

        $outcome = Database::evaluateAnalyzeRequest($post, [
            'REQUEST_METHOD' => 'POST',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTPS' => 'on',
            'HTTP_ORIGIN' => 'https://attacker.test',
        ], $session);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
    }

    public function testAnalyzeAcceptsValidTokenAndNormalizesPayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'database.analyze');
        $post = $this->validPost();
        $post[Csrf::DEFAULT_FIELD] = $token;

        $outcome = Database::evaluateAnalyzeRequest($post, $this->sameSitePostServer(), $session);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([
            'analyze' => '1',
            'id_mysql_server' => 7,
            'database' => ['app_db', 'log-db'],
        ], $outcome['payload']);
    }

    public function testAnalyzeRejectsInvalidPayloads(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'database.analyze');

        foreach ($this->invalidPosts($token) as $post) {
            $outcome = Database::evaluateAnalyzeRequest($post, $this->sameSitePostServer(), $session);

            $this->assertSame(400, $outcome['status']);
            $this->assertSame('Invalid database analyze payload', $outcome['body']);
            $this->assertNull($outcome['payload']);
        }
    }

    public function testAnalyzeControllerAndViewUseCsrfGuard(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Database.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Database/analyze.view.php');
        $analyzeStart = strpos($controller, 'public function analyze($param)');
        $updateStatsStart = strpos($controller, 'public function updateStats($param)');

        $this->assertNotFalse($analyzeStart);
        $this->assertNotFalse($updateStatsStart);
        $analyzeBody = substr($controller, $analyzeStart, $updateStatsStart - $analyzeStart);

        $this->assertStringContainsString("private const DATABASE_ANALYZE_CSRF_SCOPE = 'database.analyze'", $controller);
        $this->assertStringContainsString('GroupedFormRequest::evaluate(', $controller);
        $this->assertStringContainsString('Identifier::isDatabaseName($database)', $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::DATABASE_ANALYZE_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('evaluateAnalyzeRequest($_POST, $_SERVER, $_SESSION)', $analyzeBody);
        $this->assertStringContainsString('$analyzeRequest[\'payload\']', $analyzeBody);
        $this->assertStringNotContainsString('$_POST[\'database\'][__FUNCTION__]', $analyzeBody);
        $this->assertStringNotContainsString('$_POST[\'analyze\'][\'id_mysql_server\']', $analyzeBody);
        $this->assertStringContainsString('use App\\Library\\Security\\CsrfRender;', $view);
        $this->assertStringContainsString("CsrfRender::hiddenInput(\$data, 'database_analyze')", $view);
        $this->assertStringContainsString('Form::input("analyze", "analyze"', $view);
        $this->assertStringContainsString('method="POST"', $view);
    }

    private function validPost(): array
    {
        return [
            'analyze' => [
                'analyze' => '1',
                'id_mysql_server' => '7',
                'database' => [' app_db ', 'log-db'],
            ],
        ];
    }

    private function invalidPosts(string $token): array
    {
        $base = $this->validPost();
        $posts = [];

        $missingGroup = [Csrf::DEFAULT_FIELD => $token];
        $oldMarkerGroup = $base;
        unset($oldMarkerGroup['analyze']['analyze']);
        $oldMarkerGroup['database']['analyze'] = '1';
        $invalidServer = $base;
        $invalidServer['analyze']['id_mysql_server'] = '7 OR 1=1';
        $emptyList = $base;
        $emptyList['analyze']['database'] = [];
        $duplicateList = $base;
        $duplicateList['analyze']['database'] = ['app_db', 'app_db'];
        $badDatabase = $base;
        $badDatabase['analyze']['database'] = ["app'db"];
        $arrayDatabase = $base;
        $arrayDatabase['analyze']['database'] = [['app_db']];
        $unknownField = $base;
        $unknownField['analyze']['unexpected'] = '1';

        foreach (
            [
                $missingGroup,
                $oldMarkerGroup,
                $invalidServer,
                $emptyList,
                $duplicateList,
                $badDatabase,
                $arrayDatabase,
                $unknownField,
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
