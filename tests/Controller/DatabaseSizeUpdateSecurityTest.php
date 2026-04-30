<?php

declare(strict_types=1);

use App\Controller\Database;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class DatabaseSizeUpdateSecurityTest extends TestCase
{
    public function testDatabaseSizeUpdateRequestAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'database.size.update');

        $outcome = Database::evaluateSizeUpdateRequest(
            [Csrf::DEFAULT_FIELD => $token, 'name' => 'min', 'value' => '100M', 'pk' => '7'],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(['field' => 'min', 'value' => 104857600, 'id' => 7], $outcome['update']);
    }

    public function testDatabaseSizeUpdateRequestRejectsNonPost(): void
    {
        $outcome = Database::evaluateSizeUpdateRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
    }

    public function testDatabaseSizeUpdateRequestRejectsExternalOriginBeforeToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'database.size.update');

        $outcome = Database::evaluateSizeUpdateRequest(
            [Csrf::DEFAULT_FIELD => $token, 'name' => 'label', 'value' => 'XL', 'pk' => '7'],
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

    public function testDatabaseSizeUpdateRequestRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'tag.update');
        $server = $this->sameSitePostServer();

        $missingToken = Database::evaluateSizeUpdateRequest(
            ['name' => 'label', 'value' => 'XL', 'pk' => '7'],
            $server,
            $session
        );
        $foreignScope = Database::evaluateSizeUpdateRequest(
            [Csrf::DEFAULT_FIELD => $foreignToken, 'name' => 'label', 'value' => 'XL', 'pk' => '7'],
            $server,
            $session
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testDatabaseSizeUpdatePayloadRejectsUnknownFieldsAndInvalidValues(): void
    {
        $this->assertNull(Database::normalizeSizeUpdatePayload(['name' => 'name', 'value' => 'XL', 'pk' => '7']));
        $this->assertNull(Database::normalizeSizeUpdatePayload(['name' => 'label` = 1 --', 'value' => 'XL', 'pk' => '7']));
        $this->assertNull(Database::normalizeSizeUpdatePayload(['name' => 'label', 'value' => 'XL', 'pk' => '0']));
        $this->assertNull(Database::normalizeSizeUpdatePayload(['name' => 'label', 'value' => 'XL', 'pk' => '7 OR 1=1']));
        $this->assertNull(Database::normalizeSizeUpdatePayload(['name' => 'label', 'value' => 'XXXX', 'pk' => '7']));
        $this->assertNull(Database::normalizeSizeUpdatePayload(['name' => 'color', 'value' => str_repeat('a', 21), 'pk' => '7']));
        $this->assertNull(Database::normalizeSizeUpdatePayload(['name' => 'min', 'value' => '-1', 'pk' => '7']));
        $this->assertNull(Database::normalizeSizeUpdatePayload(['name' => 'max', 'value' => '10Z', 'pk' => '7']));
        $this->assertNull(Database::normalizeSizeUpdatePayload(['name' => 'max', 'value' => (string) PHP_INT_MAX . '0', 'pk' => '7']));
        $this->assertNull(Database::normalizeSizeUpdatePayload(['name' => 'label', 'pk' => '7']));
        $this->assertNull(Database::normalizeSizeUpdatePayload(['name' => ['label'], 'value' => 'XL', 'pk' => '7']));
        $this->assertNull(Database::normalizeSizeUpdatePayload(['name' => 'label', 'value' => ['XL'], 'pk' => '7']));
    }

    public function testDatabaseSizeBytesAcceptsRawBytesAndDisplayUnits(): void
    {
        $this->assertSame(0, Database::normalizeDatabaseSizeBytes('0'));
        $this->assertSame(104857600, Database::normalizeDatabaseSizeBytes('104857600'));
        $this->assertSame(104857600, Database::normalizeDatabaseSizeBytes('100M'));
        $this->assertSame(1073741824, Database::normalizeDatabaseSizeBytes('1G'));
        $this->assertSame(1610612736, Database::normalizeDatabaseSizeBytes('1.5G'));
        $this->assertNull(Database::normalizeDatabaseSizeBytes(''));
        $this->assertNull(Database::normalizeDatabaseSizeBytes('-1'));
        $this->assertNull(Database::normalizeDatabaseSizeBytes('10Z'));
    }

    public function testDatabaseSizeUpdateSqlTargetsDatabaseSizeAndEscapesText(): void
    {
        $labelSql = Database::buildDatabaseSizeUpdateSql(
            ['field' => 'label', 'value' => "X'L", 'id' => 7],
            static fn (string $value): string => str_replace("'", "\\'", $value)
        );
        $minSql = Database::buildDatabaseSizeUpdateSql(
            ['field' => 'min', 'value' => 104857600, 'id' => 7],
            static fn (string $value): string => $value
        );

        $this->assertSame("UPDATE database_size SET `label` = 'X\\'L' WHERE id = 7", $labelSql);
        $this->assertSame("UPDATE database_size SET `min` = 104857600 WHERE id = 7", $minSql);
    }

    public function testExternalPostWouldPassLegacyPostGateButIsRejectedBeforeSql(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'database.size.update');
        $post = [Csrf::DEFAULT_FIELD => $token, 'name' => 'label', 'value' => 'XL', 'pk' => '7'];
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://attacker.test',
        ];

        $this->assertTrue(strtoupper($server['REQUEST_METHOD']) === 'POST');
        $this->assertSame(
            "UPDATE database_size SET `label` = 'XL' WHERE id = 7",
            Database::buildDatabaseSizeUpdateSql(Database::normalizeSizeUpdatePayload($post), static fn (string $value): string => $value)
        );

        $outcome = Database::evaluateSizeUpdateRequest($post, $server, $session);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['update']);
    }

    public function testDatabaseSizeUpdateUsesDedicatedEndpointAndSharedInlineEditToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Database.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/Database/size.view.php');
        $javascript = file_get_contents(__DIR__ . '/../../App/Webroot/js/Tree/index.js');

        $this->assertIsString($controller);
        $this->assertIsString($view);
        $this->assertIsString($javascript);

        $this->assertStringContainsString('use App\\Library\\Security\\CsrfGuard;', $controller);
        $this->assertStringContainsString("private const DATABASE_SIZE_UPDATE_CSRF_SCOPE = 'database.size.update'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::DATABASE_SIZE_UPDATE_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, self::DATABASE_SIZE_UPDATE_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString("array('bootstrap-editable.min.js', 'Tree/index.js')", $controller);
        $this->assertStringNotContainsString('Tag::TAG_UPDATE_CSRF_SCOPE', $controller);

        $this->assertStringContainsString('$databaseSizeUpdateCsrfAttributes', $view);
        $this->assertStringContainsString("CsrfRender::attributes(\$data, 'database_size_update')", $view);
        $this->assertStringContainsString('database/sizeUpdate', $view);
        $this->assertStringNotContainsString('tag/update', $view);
        $this->assertStringContainsString('data-name="label"', $view);
        $this->assertStringContainsString('data-name="min"', $view);
        $this->assertStringContainsString('data-name="max"', $view);
        $this->assertStringContainsString('params[csrfField] = csrfToken;', $javascript);
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
