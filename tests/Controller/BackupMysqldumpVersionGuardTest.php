<?php

declare(strict_types=1);

use App\Controller\Backup;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BackupMysqldumpVersionGuardTest extends TestCase
{
    #[DataProvider('dumpSlaveSupportProvider')]
    public function testDumpSlaveSupportIsNullSafe(?string $version, bool $expected): void
    {
        $this->assertSame($expected, Backup::supportsDumpSlaveOption($version));
    }

    public static function dumpSlaveSupportProvider(): array
    {
        return [
            'null' => [null, false],
            'empty' => ['', false],
            'invalid' => ['unknown', false],
            'mysql 5.5.2' => ['5.5.2', false],
            'mysql 5.5.3 remains excluded' => ['5.5.3', false],
            'mysql 5.5.4' => ['5.5.4', true],
            'mysql 8.0' => ['8.0.36', true],
            'mariadb 10.11' => ['10.11.16-MariaDB-deb12-log', true],
        ];
    }

    #[DataProvider('dumpSlaveOptionProvider')]
    public function testDumpSlaveOptionDetection(string $extra, bool $expected): void
    {
        $this->assertSame($expected, Backup::hasDumpSlaveOption($extra));
    }

    public static function dumpSlaveOptionProvider(): array
    {
        return [
            'empty extra' => ['', false],
            'master data only' => [' --master-data=2 --single-transaction', false],
            'dump slave exact option' => [' --master-data=2 --dump-slave=2', true],
            'dump slave embedded in mysqldump flags' => [' --quick --dump-slave=2 --extended-insert', true],
        ];
    }

    public function testBackupControllerUsesMysqlVersionHelper(): void
    {
        $source = (string)file_get_contents(__DIR__.'/../../App/Controller/Backup.php');

        $this->assertStringContainsString('MysqlVersion::compare', $source);
        $this->assertStringContainsString('MYSQL_DUMP_SLAVE_MIN_VERSION = \'5.5.3\'', $source);
        $this->assertStringContainsString('$version = $db_to_backup->getVersion();', $source);
        $this->assertStringContainsString('self::supportsDumpSlaveOption($version)', $source);
        $this->assertStringContainsString('!self::hasDumpSlaveOption($extra)', $source);
        $this->assertStringNotContainsString('version_compare($version', $source);
        $this->assertStringNotContainsString('strpos("dump-slave", $extra)', $source);
    }
}
