<?php

declare(strict_types=1);

use App\Controller\Audit;
use App\Library\Security\ServerIdSelection;
use PHPUnit\Framework\TestCase;

final class AuditGetConfigSecurityTest extends TestCase
{
    public function testConfigDiffAcceptsOnlyNormalizedServerSelections(): void
    {
        $this->assertSame([1, 2], ServerIdSelection::normalizeList('1,2'));
        $this->assertSame([3, 4], ServerIdSelection::normalizeList('[3,4]'));

        foreach ($this->invalidSelections() as $selection) {
            $this->assertNull(ServerIdSelection::normalizeList($selection));
        }
    }

    public function testConfigDiffSqlUsesOnlyNormalizedIntegerIds(): void
    {
        $sql = Audit::buildConfigDiffSql([1, 2]);

        $this->assertStringContainsString('CASE WHEN id_mysql_server = 1 THEN value END', $sql);
        $this->assertStringContainsString('AS value_server1', $sql);
        $this->assertStringContainsString('CASE WHEN id_mysql_server = 2 THEN value END', $sql);
        $this->assertStringContainsString('AS value_server2', $sql);
        $this->assertStringContainsString('WHERE id_mysql_server IN (1,2)', $sql);
        $this->assertStringNotContainsString('UNION', $sql);
        $this->assertStringNotContainsString('OR 1=1', $sql);
        $this->assertStringNotContainsString('1)', $sql);
    }

    public function testGetConfigUsesSharedNormalizerBeforeBuildingSql(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Audit.php');
        $getConfigStart = strpos($controller, 'public function getConfig($param)');
        $builderStart = strpos($controller, 'public static function buildConfigDiffSql', $getConfigStart);

        $this->assertNotFalse($getConfigStart);
        $this->assertNotFalse($builderStart);
        $getConfigBody = substr($controller, $getConfigStart, $builderStart - $getConfigStart);

        $this->assertStringContainsString('use App\\Library\\Security\\ServerIdSelection;', $controller);
        $this->assertStringContainsString('ServerIdSelection::normalizeList($param[0] ?? null)', $getConfigBody);
        $this->assertStringContainsString('self::buildConfigDiffSql($id_mysql_servers)', $getConfigBody);
        $this->assertStringContainsString('Invalid audit config selection', $getConfigBody);
        $this->assertStringNotContainsString('explode(\',\',$param[0])', $getConfigBody);
        $this->assertStringNotContainsString('WHERE id_mysql_server IN (".$param[0].")', $getConfigBody);
        $this->assertStringNotContainsString('id_mysql_server = ".$id_mysql_server', $getConfigBody);
    }

    private function invalidSelections(): array
    {
        return [
            '',
            '0',
            '-1',
            '1,1',
            '1) UNION SELECT 1,2',
            '1,2 OR 1=1',
            ['1', ['2']],
        ];
    }
}
