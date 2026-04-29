<?php

declare(strict_types=1);

use App\Controller\Mysqlsys;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MysqlsysIndexFilterTest extends TestCase
{
    public function testIndexRequestAllowsGet(): void
    {
        $outcome = Mysqlsys::evaluateIndexRequest(['REQUEST_METHOD' => 'GET']);

        $this->assertTrue($outcome['allowed']);
        $this->assertSame(200, $outcome['status']);
        $this->assertSame([], $outcome['headers']);
    }

    public function testIndexRequestRejectsPost(): void
    {
        $outcome = Mysqlsys::evaluateIndexRequest(['REQUEST_METHOD' => 'POST']);

        $this->assertFalse($outcome['allowed']);
        $this->assertSame(405, $outcome['status']);
        $this->assertSame('GET', $outcome['headers']['Allow']);
        $this->assertSame('Method Not Allowed', $outcome['body']);
    }

    public function testIndexMysqlServerIdNormalizesGetParameter(): void
    {
        $this->assertSame(12, Mysqlsys::normalizeIndexMysqlServerId(['mysql_server' => ['id' => '12']]));
        $this->assertSame(12, Mysqlsys::normalizeIndexMysqlServerId(['mysql_server' => ['id' => ' 12 ']]));
        $this->assertNull(Mysqlsys::normalizeIndexMysqlServerId([]));
        $this->assertNull(Mysqlsys::normalizeIndexMysqlServerId(['mysql_server' => ['id' => '0']]));
        $this->assertNull(Mysqlsys::normalizeIndexMysqlServerId(['mysql_server' => ['id' => '12 OR 1=1']]));
        $this->assertNull(Mysqlsys::normalizeIndexMysqlServerId(['mysql_server' => ['id' => ['12']]]));
    }

    #[DataProvider('mysqlSysUnsupportedVersionProvider')]
    public function testMysqlSysUnsupportedVersionIsNullSafe(?string $version, bool $expected): void
    {
        $this->assertSame($expected, Mysqlsys::isMysqlSysUnsupportedVersion($version));
    }

    public static function mysqlSysUnsupportedVersionProvider(): array
    {
        return [
            'null' => [null, false],
            'empty' => ['', false],
            'invalid' => ['unknown', false],
            'mysql 5.5' => ['5.5.62-log', true],
            'mysql 5.6 boundary' => ['5.6', true],
            'mysql 5.6.0 normalized boundary' => ['5.6.0', false],
            'mysql 5.6 patch' => ['5.6.51-log', false],
            'mysql 5.7' => ['5.7.44-log', false],
            'mysql 8.0 percona suffix' => ['8.0.36-28', false],
            'mariadb 10.11' => ['10.11.16-MariaDB-deb12-log', false],
        ];
    }

    public function testIndexControllerDropsPostRedirectAndUsesNormalizedGetSelection(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Mysqlsys.php');

        $this->assertIsString($controller);
        $this->assertStringContainsString('evaluateIndexRequest($_SERVER)', $controller);
        $this->assertStringContainsString('$selectedMysqlServerId = self::normalizeIndexMysqlServerId($_GET);', $controller);
        $this->assertStringContainsString('$data[\'selected_mysql_server_id\'] = $selectedMysqlServerId;', $controller);
        $this->assertStringContainsString('$data[\'selected_mysql_server_found\'] = false;', $controller);
        $this->assertStringContainsString('$data[\'variables\'] = \'\';', $controller);
        $this->assertStringContainsString('$data[\'mysqlsys_version_unsupported\'] = false;', $controller);
        $this->assertStringContainsString('$data[\'selected_mysql_server_found\'] = true;', $controller);
        $this->assertStringContainsString('(int) $ob->id === $selectedMysqlServerId', $controller);
        $this->assertStringContainsString('$id_mysql_server = $selectedMysqlServerId;', $controller);
        $this->assertStringContainsString('self::isMysqlSysUnsupportedVersion($data[\'variables\'])', $controller);
        $this->assertStringNotContainsString('$_POST[\'mysql_server\'][\'id\']', $controller);
        $this->assertStringNotContainsString('REQUEST_METHOD\'] == "POST"', $controller);
    }

    public function testIndexViewUsesGetFormAndPreservesServerInReportingLinks(): void
    {
        $view = file_get_contents(__DIR__ . '/../../App/view/Mysqlsys/index.view.php');

        $this->assertIsString($view);
        $this->assertStringContainsString('method="get"', $view);
        $this->assertStringContainsString('$selectedMysqlServerId = $data[\'selected_mysql_server_id\'] ?? null;', $view);
        $this->assertStringContainsString('$selectedMysqlServerFound = !empty($data[\'selected_mysql_server_found\']);', $view);
        $this->assertStringContainsString('$mysqlsysVersionUnsupported = !empty($data[\'mysqlsys_version_unsupported\']);', $view);
        $this->assertStringContainsString('if ($selectedMysqlServerId !== null && $selectedMysqlServerFound)', $view);
        $this->assertStringContainsString('function remove($array, $selectedMysqlServerId = null)', $view);
        $this->assertStringContainsString('$params[] = \'mysql_server:id:\' . (int) $selectedMysqlServerId;', $view);
        $this->assertStringContainsString('$url = remove(array("mysqlsys"), $selectedMysqlServerId);', $view);
        $this->assertStringContainsString('data-pk="\' . (int) $selectedMysqlServerId', $view);
        $this->assertStringContainsString('} elseif ($mysqlsysVersionUnsupported) {', $view);
        $this->assertStringNotContainsString('version_compare(', $view);
        $this->assertStringNotContainsString('method="POST"', $view);
    }
}
