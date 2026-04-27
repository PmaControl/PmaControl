<?php

declare(strict_types=1);

use App\Controller\Format;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class FormatIndexSecurityTest extends TestCase
{
    public function testIndexPostRejectsMissingToken(): void
    {
        $session = [];

        $outcome = Format::evaluateIndexPost(
            ['sql' => 'SELECT 1'],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid CSRF token', $outcome['body']);
        $this->assertSame('', $outcome['hash']);
    }

    public function testIndexPostAcceptsValidTokenAndSqlPayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'format.index');
        $sql = 'SELECT * FROM mysql_server WHERE id = 1';

        $outcome = Format::evaluateIndexPost(
            [Csrf::DEFAULT_FIELD => $token, 'sql' => $sql],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame($sql, $outcome['sql']);
        $this->assertSame(md5($sql), $outcome['hash']);
    }

    public function testIndexPostRejectsTokenFromAnotherScope(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'graph.index');

        $outcome = Format::evaluateIndexPost(
            [Csrf::DEFAULT_FIELD => $token, 'sql' => 'SELECT 1'],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid CSRF token', $outcome['body']);
    }

    public function testIndexPostRejectsExternalOrigin(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'format.index');

        $outcome = Format::evaluateIndexPost(
            [Csrf::DEFAULT_FIELD => $token, 'sql' => 'SELECT 1'],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTPS' => 'on',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
    }

    public function testIndexPostRejectsInvalidSqlPayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'format.index');

        $outcome = Format::evaluateIndexPost(
            [Csrf::DEFAULT_FIELD => $token, 'sql' => ['SELECT 1']],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(422, $outcome['status']);
        $this->assertSame('Invalid SQL payload', $outcome['body']);
    }

    public function testIndexPostRejectsTooLargeSqlPayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'format.index');

        $outcome = Format::evaluateIndexPost(
            [Csrf::DEFAULT_FIELD => $token, 'sql' => str_repeat('a', 1048577)],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(413, $outcome['status']);
        $this->assertSame('SQL payload too large', $outcome['body']);
    }

    public function testSqlStorageIsNamespacedAndLegacyReadStillWorks(): void
    {
        $session = [
            md5('legacy') => 'legacy',
        ];
        $hash = md5('SELECT 1');

        Format::storeSqlInSession($session, $hash, 'SELECT 1', 1000);

        $this->assertSame('SELECT 1', Format::getStoredSql($session, $hash));
        $this->assertSame('legacy', Format::getStoredSql($session, md5('legacy')));
        $this->assertIsArray($session['format_sql'][$hash]);
        $this->assertSame('SELECT 1', $session['format_sql'][$hash]['sql']);
    }

    public function testStoredSqlReadIgnoresInvalidNamespacedSession(): void
    {
        $session = [
            'format_sql' => 'invalid',
            md5('legacy') => 'legacy',
        ];

        $this->assertSame('legacy', Format::getStoredSql($session, md5('legacy')));
    }

    public function testIndexDataReadsStoredSqlAndRejectsInvalidHash(): void
    {
        $session = [];
        $hash = md5('SELECT 1');
        Format::storeSqlInSession($session, $hash, 'SELECT 1', 1000);

        $data = Format::buildIndexData([$hash], $session);
        $invalidData = Format::buildIndexData(['not-a-hash'], $session);

        $this->assertSame('SELECT 1', $data['sql']);
        $this->assertArrayHasKey('sql_formated', $data);
        $this->assertSame([], $invalidData);
    }

    public function testIndexUsesSharedCsrfGuardAndViewCarriesEscapedToken(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Format.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Format/index.view.php');

        $this->assertStringContainsString('use App\\Library\\Security\\CsrfGuard;', $controller);
        $this->assertStringContainsString('use Glial\\Security\\Csrf;', $controller);
        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, self::FORMAT_INDEX_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::FORMAT_INDEX_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('name="<?= $formatIndexCsrfField ?>"', $view);
        $this->assertStringContainsString('value="<?= $formatIndexCsrfToken ?>"', $view);
        $this->assertStringContainsString("\$formatSql = htmlspecialchars((string) (\$data['sql'] ?? ''),", $view);
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
