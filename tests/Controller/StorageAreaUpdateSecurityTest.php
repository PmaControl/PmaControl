<?php

declare(strict_types=1);

use App\Controller\StorageArea;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class StorageAreaUpdateSecurityTest extends TestCase
{
    public function testStorageAreaUpdateRequestAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'storage_area.update');

        $outcome = StorageArea::evaluateUpdateRequest(
            [Csrf::DEFAULT_FIELD => $token, 'name' => 'libelle', 'value' => 'Remote backup', 'pk' => '7'],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(['field' => 'libelle', 'value' => 'Remote backup', 'id' => 7], $outcome['update']);
    }

    public function testStorageAreaUpdateRequestRejectsNonPost(): void
    {
        $outcome = StorageArea::evaluateUpdateRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
    }

    public function testStorageAreaUpdateRequestRejectsExternalOriginBeforeToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'storage_area.update');

        $outcome = StorageArea::evaluateUpdateRequest(
            [Csrf::DEFAULT_FIELD => $token, 'name' => 'libelle', 'value' => 'Remote backup', 'pk' => '7'],
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
        $this->assertNull($outcome['update']);
    }

    public function testStorageAreaUpdateRequestRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'tag.update');
        $server = $this->sameSitePostServer();

        $missingToken = StorageArea::evaluateUpdateRequest(
            ['name' => 'libelle', 'value' => 'Remote backup', 'pk' => '7'],
            $server,
            $session
        );
        $foreignScope = StorageArea::evaluateUpdateRequest(
            [Csrf::DEFAULT_FIELD => $foreignToken, 'name' => 'libelle', 'value' => 'Remote backup', 'pk' => '7'],
            $server,
            $session
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testStorageAreaUpdatePayloadRejectsUnknownFieldsInvalidIdsAndMalformedValues(): void
    {
        $this->assertNull(StorageArea::normalizeUpdatePayload(['name' => 'path', 'value' => '/backup', 'pk' => '7']));
        $this->assertNull(StorageArea::normalizeUpdatePayload(['name' => 'ip', 'value' => '127.0.0.1', 'pk' => '7']));
        $this->assertNull(StorageArea::normalizeUpdatePayload(['name' => 'id_ssh_key', 'value' => '1', 'pk' => '7']));
        $this->assertNull(StorageArea::normalizeUpdatePayload(['name' => 'libelle` = 1 --', 'value' => 'x', 'pk' => '7']));
        $this->assertNull(StorageArea::normalizeUpdatePayload(['name' => 'libelle', 'value' => 'x', 'pk' => '0']));
        $this->assertNull(StorageArea::normalizeUpdatePayload(['name' => 'libelle', 'value' => 'x', 'pk' => '7 OR 1=1']));
        $this->assertNull(StorageArea::normalizeUpdatePayload(['name' => 'libelle', 'value' => str_repeat('a', 65), 'pk' => '7']));
        $this->assertNull(StorageArea::normalizeUpdatePayload(['name' => 'libelle', 'pk' => '7']));
        $this->assertNull(StorageArea::normalizeUpdatePayload(['name' => ['libelle'], 'value' => 'x', 'pk' => '7']));
        $this->assertNull(StorageArea::normalizeUpdatePayload(['name' => 'libelle', 'value' => ['x'], 'pk' => '7']));
    }

    public function testStorageAreaUpdatePayloadAllowsEmptyNameForLegacyCompatibility(): void
    {
        $this->assertSame(
            ['field' => 'libelle', 'value' => '', 'id' => 7],
            StorageArea::normalizeUpdatePayload(['name' => 'libelle', 'value' => '', 'pk' => '7'])
        );
    }

    public function testStorageAreaUpdateSqlTargetsStorageAreaAndEscapesValue(): void
    {
        $sql = StorageArea::buildStorageAreaUpdateSql(
            ['field' => 'libelle', 'value' => "Bob's backup", 'id' => 7],
            static fn (string $value): string => str_replace("'", "\\'", $value)
        );

        $this->assertSame("UPDATE backup_storage_area SET `libelle` = 'Bob\\'s backup' WHERE id = 7", $sql);
        $this->assertStringNotContainsString('UPDATE menu', $sql);
    }

    public function testExternalPostWouldPassLegacyPostGateButIsRejectedBeforeSql(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'storage_area.update');
        $post = [Csrf::DEFAULT_FIELD => $token, 'name' => 'libelle', 'value' => 'Owned', 'pk' => '7'];
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://attacker.test',
        ];

        $this->assertTrue(strtoupper($server['REQUEST_METHOD']) === 'POST');
        $this->assertSame("UPDATE menu SET `libelle` = 'Owned' WHERE id = 7", $this->legacyStorageAreaUpdateSql($post));

        $outcome = StorageArea::evaluateUpdateRequest($post, $server, $session);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['update']);
    }

    public function testStorageAreaUpdateUsesSharedCsrfLibraryAndInlineEditSendsToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/StorageArea.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/StorageArea/listStorage.view.php');
        $javascript = file_get_contents(__DIR__ . '/../../App/Webroot/js/Tree/index.js');

        $this->assertIsString($controller);
        $this->assertIsString($view);
        $this->assertIsString($javascript);

        $this->assertStringContainsString('use Glial\\Security\\Csrf;', $controller);
        $this->assertStringContainsString('use App\\Library\\Security\\CsrfGuard;', $controller);
        $this->assertStringContainsString("private const STORAGE_AREA_UPDATE_CSRF_SCOPE = 'storage_area.update'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::STORAGE_AREA_UPDATE_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::ensureOrFail($post, $server, $session, self::STORAGE_AREA_UPDATE_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('private const STORAGE_AREA_UPDATE_FIELDS', $controller);
        $this->assertStringContainsString("array('bootstrap-editable.min.js', 'Tree/index.js')", $controller);
        $this->assertStringContainsString('UPDATE backup_storage_area', $controller);
        $this->assertStringNotContainsString('UPDATE menu SET `', $controller);

        $this->assertStringContainsString('$storageAreaUpdateCsrfAttributes', $view);
        $this->assertStringContainsString("CsrfRender::attributes(\$data, 'storage_area_update')", $view);
        $this->assertStringContainsString('storagearea/update', $view);
        $this->assertStringContainsString("data-pk=\"<?= (int) \$storage['id_backup_storage_area'] ?>\"", $view);
        $this->assertStringNotContainsString("data-pk=\"<?= \$storage['id'] ?>\"", $view);

        $this->assertStringContainsString('params[csrfField] = csrfToken;', $javascript);
    }

    public function testLegacyStorageAreaInlineEditScriptIsRemoved(): void
    {
        $this->assertFileDoesNotExist(__DIR__ . '/../../App/Webroot/js/StorageArea/index.js');
    }

    public function testNoControllerLoadsLegacyStorageAreaInlineEditScript(): void
    {
        $controllerFiles = glob(__DIR__ . '/../../App/Controller/*.php') ?: [];

        $this->assertNotEmpty($controllerFiles);

        foreach ($controllerFiles as $controllerFile) {
            $source = (string) file_get_contents($controllerFile);

            $this->assertStringNotContainsString(
                'StorageArea/index.js',
                $source,
                basename($controllerFile) . ' must use Tree/index.js for CSRF-aware inline editing'
            );

            if (basename($controllerFile) === 'StorageArea.php') {
                $sourceWithoutWhitespace = preg_replace('/\s+/', '', $source) ?? $source;

                $this->assertStringNotContainsString(
                    "getClass().'/index.js'",
                    $sourceWithoutWhitespace,
                    'StorageArea.php must not load a controller-relative index.js'
                );
                $this->assertStringNotContainsString(
                    'getClass()."/index.js"',
                    $sourceWithoutWhitespace,
                    'StorageArea.php must not load a controller-relative index.js'
                );
            }
        }
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

    private function legacyStorageAreaUpdateSql(array $post): string
    {
        return "UPDATE menu SET `" . $post['name'] . "` = '" . $post['value'] . "' WHERE id = " . $post['pk'];
    }
}
