<?php

declare(strict_types=1);

use App\Controller\Aspirateur;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AspirateurVersionCompareCallSiteTest extends TestCase
{
    #[DataProvider('hardenedSourceFilesProvider')]
    public function testHardenedSourceFilesNoLongerCallVersionCompareDirectly(string $path): void
    {
        $source = (string) file_get_contents(__DIR__.'/../../'.$path);

        $this->assertStringNotContainsString('version_compare(', $source);
    }

    public static function hardenedSourceFilesProvider(): array
    {
        return [
            'Aspirateur controller' => ['App/Controller/Aspirateur.php'],
            'Mysql library charset guard' => ['App/Library/Mysql.php'],
            'Binlog analyzer version guards' => ['App/Library/BinlogAnalyzer.php'],
        ];
    }

    #[DataProvider('schemaCommentProvider')]
    public function testSchemaCommentGuardUsesMysqlVersionHelper(
        string $version,
        string $comment,
        bool $expected
    ): void {
        $query = $this->invokeAspirateur('getSchemaQuery', [$version, $comment]);

        $this->assertSame($expected, strpos($query, 'SCHEMA_COMMENT AS schema_comment') !== false);
    }

    public static function schemaCommentProvider(): array
    {
        return [
            'unknown version' => ['', 'MariaDB Server', false],
            'mariadb 10.4' => ['10.4.34-MariaDB', '', false],
            'mariadb 10.5' => ['10.5.29-MariaDB', '', true],
            'mariadb from comment' => ['10.11.16', 'MariaDB Server', true],
            'mysql 8.4' => ['8.4.8', 'MySQL Community Server', false],
        ];
    }

    #[DataProvider('masterStatusProvider')]
    public function testMasterStatusCommandGuardUsesMysqlVersionHelper(
        string $version,
        string $comment,
        bool $isSingleStore,
        ?string $expected
    ): void {
        $this->assertSame(
            $expected,
            $this->invokeAspirateur('getMasterStatusCommand', [$version, $comment, $isSingleStore])
        );
    }

    public static function masterStatusProvider(): array
    {
        return [
            'unknown version' => ['', '', false, null],
            'mysql 8.0' => ['8.0.44', 'MySQL Community Server', false, 'SHOW MASTER STATUS'],
            'mysql 8.4' => ['8.4.8', 'MySQL Community Server', false, 'SHOW BINARY LOG STATUS'],
            'percona 8.4' => ['8.4.8-8', 'Percona Server', false, 'SHOW BINARY LOG STATUS'],
            'mariadb 11.8' => ['11.8.6-MariaDB-deb13', '', false, 'SHOW MASTER STATUS'],
            'singlestore flag' => ['8.0.34', 'SingleStore DB', true, null],
            'singlestore comment' => ['8.0.34', 'SingleStore DB', false, null],
        ];
    }

    #[DataProvider('groupReplicationProvider')]
    public function testGroupReplicationProbeGuardUsesMysqlVersionHelper(
        string $version,
        string $comment,
        bool $expected
    ): void {
        $this->assertSame(
            $expected,
            $this->invokeAspirateur('shouldProbeGroupReplication', [$version, $comment])
        );
    }

    public static function groupReplicationProvider(): array
    {
        return [
            'unknown version' => ['', '', false],
            'mysql 5.7.16' => ['5.7.16-log', 'MySQL Community Server', false],
            'mysql 5.7.17' => ['5.7.17-log', 'MySQL Community Server', true],
            'mysql 8.4' => ['8.4.8', 'MySQL Community Server', true],
            'mariadb' => ['10.11.16-MariaDB-deb12-log', '', false],
            'singlestore' => ['8.0.34', 'SingleStore DB', false],
        ];
    }

    /**
     * @param array<int, mixed> $arguments
     * @return mixed
     */
    private function invokeAspirateur(string $methodName, array $arguments)
    {
        $reflection = new ReflectionClass(Aspirateur::class);
        $method = $reflection->getMethod($methodName);
        $controller = $reflection->newInstanceWithoutConstructor();

        return $method->invokeArgs($controller, $arguments);
    }
}
