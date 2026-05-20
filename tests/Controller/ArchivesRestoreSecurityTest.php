<?php

declare(strict_types=1);

use App\Controller\Archives;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class ArchivesRestoreSecurityTest extends TestCase
{
    public function testRestoreAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'archives.restore');

        $outcome = Archives::evaluateRestoreRequest($this->restorePost($token), $this->sameSitePostServer(), $session);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([
            'id_cleaner_main' => 5,
            'id_mysql_server' => 12,
            'database' => 'customer_archive',
        ], $outcome['restore']);
    }

    public function testRestoreRejectsNonPost(): void
    {
        $outcome = Archives::evaluateRestoreRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['restore']);
    }

    public function testRestoreRejectsExternalOriginBeforePayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'archives.restore');

        $outcome = Archives::evaluateRestoreRequest(
            $this->restorePost($token, ['mysql_server' => [['database' => 'customer_archive;DROP']]]),
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
        $this->assertNull($outcome['restore']);
    }

    public function testRestoreRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'backup.add');
        $server = $this->sameSitePostServer();

        $missingToken = Archives::evaluateRestoreRequest($this->restorePost(null), $server, $session);
        $foreignScope = Archives::evaluateRestoreRequest($this->restorePost($foreignToken), $server, $session);

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testRestorePayloadRejectsMalformedValues(): void
    {
        foreach ($this->invalidPosts() as $post) {
            $this->assertNull(Archives::normalizeRestorePayload($post));
        }
    }

    public function testExternalPostWouldHaveReachedLegacyRestoreButIsRejected(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'archives.restore');
        $post = $this->restorePost($token, ['mysql_server' => [['database' => 'customer_archive;DROP']]] );

        $this->assertSame(
            ['12', 'customer_archive;DROP', '5'],
            $this->legacyRestoreLoadArchiveArgs($post)
        );

        $outcome = Archives::evaluateRestoreRequest(
            $post,
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
        $this->assertNull($outcome['restore']);
    }

    public function testRestoreUsesSharedSecurityLibraryAndViewSendsTokenBeforeEffects(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Archives.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Archives/index.view.php');
        $request = (string) file_get_contents(__DIR__ . '/../../App/Library/Security/ArchiveRestoreRequest.php');

        $restoreStart = strpos($controller, 'public function restore($param)');
        $menuStart = strpos($controller, 'public function menu($param)', $restoreStart);
        $restoreBody = substr($controller, $restoreStart, $menuStart - $restoreStart);

        $this->assertStringContainsString('use App\\Library\\Security\\ArchiveRestoreRequest;', $controller);
        $this->assertStringContainsString('use App\\Library\\Archive\\ArchiveLoader;', $controller);
        $this->assertStringContainsString("private const ARCHIVES_RESTORE_CSRF_SCOPE = 'archives.restore'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::ARCHIVES_RESTORE_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('self::evaluateRestoreRequest($_POST, $_SERVER, $_SESSION)', $restoreBody);
        $this->assertLessThan(strpos($restoreBody, 'ArchiveLoader::dispatch('), strpos($restoreBody, 'self::evaluateRestoreRequest('));
        $this->assertStringContainsString('ArchiveLoader::dispatch($this, $restore);', $restoreBody);
        $this->assertStringNotContainsString('$this->load_archive(', $restoreBody);
        $this->assertStringNotContainsString('foreach ($_POST[\'mysql_server\']', $restoreBody);

        $this->assertStringContainsString('$archivesRestoreCsrfField', $view);
        $this->assertStringContainsString('$archivesRestoreCsrfToken', $view);
        $this->assertStringContainsString('type="hidden"', $view);
        $this->assertStringContainsString('method="post"', $view);

        $this->assertStringContainsString('CsrfGuard::ensureOrFail($post, $server, $session, $scope)', $request);
        $this->assertStringContainsString('PositiveIntegerSelection::normalizeList($post[\'id_cleaner_main\'], 1)', $request);
        $this->assertStringContainsString('Identifier::isDatabaseName($database)', $request);
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

    private function restorePost(?string $token, array $overrides = []): array
    {
        $post = array_replace_recursive([
            'id_cleaner_main' => '5',
            'mysql_server' => [
                [
                    'id' => '12',
                    'database' => 'customer_archive',
                ],
            ],
        ], $overrides);

        if ($token !== null) {
            $post[Csrf::DEFAULT_FIELD] = $token;
        }

        return $post;
    }

    private function invalidPosts(): array
    {
        return [
            [],
            $this->restorePost(null, ['id_cleaner_main' => '0']),
            $this->restorePost(null, ['id_cleaner_main' => '5 OR 1=1']),
            $this->restorePost(null, ['id_cleaner_main' => ['5']]),
            ['id_cleaner_main' => '5', 'mysql_server' => []],
            ['id_cleaner_main' => '5', 'mysql_server' => '12'],
            ['id_cleaner_main' => '5', 'mysql_server' => [['id' => '12']]],
            $this->restorePost(null, ['mysql_server' => [['id' => '0', 'database' => 'customer_archive']]]),
            $this->restorePost(null, ['mysql_server' => [['id' => ['12'], 'database' => 'customer_archive']]]),
            $this->restorePost(null, ['mysql_server' => [['id' => '12', 'database' => '']]]),
            $this->restorePost(null, ['mysql_server' => [['id' => '12', 'database' => 'customer archive']]]),
            $this->restorePost(null, ['mysql_server' => [['id' => '12', 'database' => 'customer_archive;DROP']]]),
            $this->restorePost(null, ['mysql_server' => [['id' => '12', 'database' => 'customer_archive', 'extra' => '1']]]),
            $this->restorePost(null, ['mysql_server' => [
                ['id' => '12', 'database' => 'customer_archive'],
                ['id' => '13', 'database' => 'customer_archive_2'],
            ]]),
        ];
    }

    private function legacyRestoreLoadArchiveArgs(array $post): array
    {
        foreach ($post['mysql_server'] as $arr) {
            if (!empty($arr['database'])) {
                return [$arr['id'], $arr['database'], $post['id_cleaner_main']];
            }
        }

        return [];
    }
}
