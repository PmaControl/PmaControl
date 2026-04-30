<?php

declare(strict_types=1);

use App\Library\Util;
use PHPUnit\Framework\TestCase;

final class UtilTest extends TestCase
{
    protected function tearDown(): void
    {
        $_GET = [];
        $_SESSION = [];
    }

    public function testGetControllerReturnsShortClassName(): void
    {
        $this->assertSame('Api', Util::getController('App\\Controller\\Api'));
        $this->assertSame('Mysql', Util::getController('App\\Library\\Mysql'));
    }

    public function testPrivateGetFilterBuildsClientAndEnvironmentClauses(): void
    {
        $_GET['environment']['libelle'] = json_encode([1, 2], JSON_THROW_ON_ERROR);
        $_SESSION['client']['libelle'] = json_encode([5], JSON_THROW_ON_ERROR);

        $reflection = new ReflectionClass(Util::class);
        $method = $reflection->getMethod('getFilter');

        $sql = $method->invoke(null, [9, 10], 'srv');

        $this->assertStringContainsString('`srv`.id_environment IN (1,2)', $sql);
        $this->assertStringContainsString('`srv`.id_client IN (5)', $sql);
        $this->assertStringContainsString('`srv`.id IN (9,10)', $sql);
    }

    public function testPrivateGetFilterReadsClientFromGetAndRejectsInjectedServerIds(): void
    {
        $_GET['client']['libelle'] = json_encode([5], JSON_THROW_ON_ERROR);

        $reflection = new ReflectionClass(Util::class);
        $method = $reflection->getMethod('getFilter');

        $validSql = $method->invoke(null, [], 'srv');
        $this->assertStringContainsString('`srv`.id_client IN (5)', $validSql);

        $invalidSql = $method->invoke(null, ['9) OR 1=1 -- '], 'srv');
        $this->assertSame(' AND 0=1', $invalidSql);
        $this->assertStringNotContainsString('OR 1=1', $invalidSql);
    }

    public function testUtilUsesSharedServerFilterWhere(): void
    {
        $source = (string) file_get_contents(__DIR__ . '/../../App/Library/Util.php');

        $this->assertStringContainsString('use App\\Library\\Security\\ServerFilterWhere;', $source);
        $this->assertStringContainsString('ServerFilterWhere::build($_GET, $_SESSION, $id_mysql_server, $alias)', $source);
        $this->assertStringNotContainsString('json_decode($client, true)', $source);
    }
}
