<?php

declare(strict_types=1);

use App\Controller\StorageArea;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class StorageAreaAddSecurityTest extends TestCase
{
    public function testStorageAreaAddRequestAcceptsValidRemotePost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'storage_area.add');

        $outcome = StorageArea::evaluateAddRequest(
            $this->storageAreaPost($token, ['port' => '']),
            $this->sameSitePostServer(),
            $session
        );

        $expected = $this->validStorageAreaPayload([
            'port' => 22,
            'id_geolocalisation_country' => 1,
            'id_geolocalisation_city' => 2,
            'id_ssh_key' => 7,
        ]);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(['backup_storage_area' => $expected], $outcome['storage_area']);
    }

    public function testStorageAreaAddRequestRejectsNonPost(): void
    {
        $outcome = StorageArea::evaluateAddRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
    }

    public function testStorageAreaAddRequestRejectsExternalOriginBeforePayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'storage_area.add');

        $outcome = StorageArea::evaluateAddRequest(
            [Csrf::DEFAULT_FIELD => $token, 'backup_storage_area' => 'invalid'],
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
        $this->assertNull($outcome['storage_area']);
    }

    public function testStorageAreaAddRequestRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'storage_area.update');
        $server = $this->sameSitePostServer();

        $missingToken = StorageArea::evaluateAddRequest(
            ['backup_storage_area' => $this->validStorageAreaPayload()],
            $server,
            $session
        );
        $foreignScope = StorageArea::evaluateAddRequest(
            $this->storageAreaPost($foreignToken),
            $server,
            $session
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testStorageAreaAddPayloadRejectsMalformedValues(): void
    {
        $this->assertNull(StorageArea::normalizeAddPayload([]));
        $this->assertNull(StorageArea::normalizeAddPayload(['backup_storage_area' => 'invalid']));
        $this->assertNull(StorageArea::normalizeAddPayload(['backup_storage_area' => $this->validStorageAreaPayload(['path' => ['nested']])]));
        $this->assertNull(StorageArea::normalizeAddPayload(['backup_storage_area' => $this->validStorageAreaPayload(['id_ssh_key' => '7 OR 1=1'])]));
        $this->assertNull(StorageArea::normalizeAddPayload(['backup_storage_area' => $this->validStorageAreaPayload(['id_geolocalisation_city' => '0'])]));
        $this->assertNull(StorageArea::normalizeAddPayload(['backup_storage_area' => $this->validStorageAreaPayload(['libelle' => str_repeat('a', 65)])]));
        $this->assertNull(StorageArea::normalizeAddPayload(['backup_storage_area' => $this->validStorageAreaPayload(['ip' => '255.255.255.2555'])]));
        $this->assertNull(StorageArea::normalizeAddPayload(['backup_storage_area' => $this->validStorageAreaPayload(['port' => '70000'])]));
        $this->assertNull(StorageArea::normalizeAddPayload(['backup_storage_area' => $this->validStorageAreaPayload(['port' => '22 OR 1=1'])]));
        $this->assertNull(StorageArea::normalizeAddPayload(['backup_storage_area' => $this->validStorageAreaPayload(['path' => ''])]));
        $this->assertNull(StorageArea::normalizeAddPayload(['backup_storage_area' => ['libelle' => 'Local only', 'path' => '/srv/backup', 'islocal' => '1']]));
    }

    public function testStorageAreaAddSshKeyLookupSqlUsesNormalizedInteger(): void
    {
        $this->assertSame('SELECT * FROM ssh_key WHERE id =7', StorageArea::buildStorageAreaAddSshKeySql(7));
    }

    public function testExternalPostWouldPassLegacyPostGateButIsRejectedBeforeSshKeySql(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'storage_area.add');
        $post = $this->storageAreaPost($token, ['id_ssh_key' => '7 OR 1=1']);
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://attacker.test',
        ];

        $this->assertTrue(strtoupper($server['REQUEST_METHOD']) === 'POST');
        $this->assertSame(
            'SELECT * FROM ssh_key WHERE id =7 OR 1=1',
            $this->legacyStorageAreaSshKeyLookupSql($post)
        );

        $outcome = StorageArea::evaluateAddRequest($post, $server, $session);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['storage_area']);
    }

    public function testStorageAreaAddUsesSharedCsrfGuardAndBothFormsCarryToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/StorageArea.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/StorageArea/add.view.php');
        $post = file_get_contents(__DIR__ . '/../../App/Library/Post.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);
        $this->assertIsString($post);

        $this->assertStringContainsString("private const STORAGE_AREA_ADD_CSRF_SCOPE = 'storage_area.add'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::STORAGE_AREA_ADD_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, self::STORAGE_AREA_ADD_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('buildStorageAreaAddSshKeySql($storage_area[', $controller);
        $this->assertStringNotContainsString('SELECT * FROM ssh_key WHERE id =" . $storage_area', $controller);

        $this->assertStringContainsString('$storageAreaAddCsrfInput', $view);
        $this->assertSame(2, substr_count($view, '<form action="" method="post">'));
        $this->assertSame(2, substr_count($view, '<?= $storageAreaAddCsrfInput ?>'));

        $this->assertStringContainsString('if (!is_array($elems))', $post);
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

    private function storageAreaPost(string $token, array $overrides = []): array
    {
        return [
            Csrf::DEFAULT_FIELD => $token,
            'backup_storage_area' => $this->validStorageAreaPayload($overrides),
        ];
    }

    private function validStorageAreaPayload(array $overrides = []): array
    {
        return array_replace([
            'libelle' => 'Remote backup',
            'ip' => '10.10.1.1',
            'port' => '22',
            'path' => '/srv/backup/mysql',
            'id_geolocalisation_country' => '1',
            'id_geolocalisation_city' => '2',
            'id_ssh_key' => '7',
        ], $overrides);
    }

    private function legacyStorageAreaSshKeyLookupSql(array $post): string
    {
        return 'SELECT * FROM ssh_key WHERE id =' . $post['backup_storage_area']['id_ssh_key'];
    }
}
