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
        $method->setAccessible(true);

        $sql = $method->invoke(null, [9, 10], 'srv');

        $this->assertStringContainsString('`srv`.id_environment IN (1,2)', $sql);
        $this->assertStringContainsString('`srv`.id_client IN (5)', $sql);
        $this->assertStringContainsString('`srv`.id IN (9,10)', $sql);
    }
}
