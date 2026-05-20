<?php

declare(strict_types=1);

use App\Controller\Query;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class QueryDefaultVersionGuardTest extends TestCase
{
    #[DataProvider('textBlobDefaultSupportProvider')]
    public function testTextBlobDefaultSupportIsNullSafe(?string $version, bool $expected): void
    {
        $this->assertSame($expected, Query::supportsTextBlobDefaults($version));
    }

    public static function textBlobDefaultSupportProvider(): array
    {
        return [
            'null' => [null, false],
            'empty' => ['', false],
            'invalid' => ['unknown', false],
            'mariadb 10.1' => ['10.1.48-MariaDB', false],
            'mariadb 10.2 shorthand' => ['10.2', true],
            'mariadb 10.2.0' => ['10.2.0-MariaDB-log', true],
            'mariadb 10.11' => ['10.11.16-MariaDB-deb12-log', true],
            'percona 8.0' => ['8.0.36-28', false],
            'mysql 8.0' => ['8.0.36', false],
        ];
    }

    public function testQueryControllerUsesMysqlVersionHelper(): void
    {
        $source = (string)file_get_contents(__DIR__.'/../../App/Controller/Query.php');

        $this->assertStringContainsString('MysqlVersion::atLeast', $source);
        $this->assertStringContainsString('TEXT_BLOB_DEFAULT_MIN_VERSION = \'10.2\'', $source);
        $this->assertStringContainsString('!self::supportsTextBlobDefaults($db->getVersion())', $source);
        $this->assertStringNotContainsString('version_compare(', $source);
        $this->assertStringNotContainsString('10.2, \'<\'', $source);
    }
}
