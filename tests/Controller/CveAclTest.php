<?php

declare(strict_types=1);

use App\Controller\Cve;
use App\Controller\MysqlServer;
use PHPUnit\Framework\TestCase;

final class CveAclTest extends TestCase
{
    public function testCveIndexRouteExistsAndIsWhitelisted(): void
    {
        $root = dirname(__DIR__, 2);
        $sampleAcl = (string) file_get_contents($root . '/config_sample/acl.config.ini');
        $method = new ReflectionMethod(Cve::class, 'index');

        $this->assertTrue($method->isPublic());
        $this->assertStringContainsString('Member[] = "Cve/index"', $sampleAcl);
        $this->assertStringContainsString('ReadOnly[] = "Cve/index"', $sampleAcl);
    }

    public function testCveShowRouteExistsAndIsWhitelisted(): void
    {
        $root = dirname(__DIR__, 2);
        $sampleAcl = (string) file_get_contents($root . '/config_sample/acl.config.ini');
        $method = new ReflectionMethod(Cve::class, 'show');

        $this->assertTrue($method->isPublic());
        $this->assertStringContainsString('Member[] = "Cve/show"', $sampleAcl);
        $this->assertStringContainsString('ReadOnly[] = "Cve/show"', $sampleAcl);
    }

    public function testCveExclusionsRouteExistsAndIsSuperAdminOnly(): void
    {
        $root = dirname(__DIR__, 2);
        $sampleAcl = (string) file_get_contents($root . '/config_sample/acl.config.ini');
        $method = new ReflectionMethod(Cve::class, 'exclusions');

        $this->assertTrue($method->isPublic());
        $this->assertStringContainsString('SuperAdministrator[] = "Cve/exclusions"', $sampleAcl);
        $this->assertStringNotContainsString('Member[] = "Cve/exclusions"', $sampleAcl);
        $this->assertStringNotContainsString('ReadOnly[] = "Cve/exclusions"', $sampleAcl);
    }

    public function testMysqlServerCveTabRouteExistsAndIsWhitelisted(): void
    {
        $root = dirname(__DIR__, 2);
        $sampleAcl = (string) file_get_contents($root . '/config_sample/acl.config.ini');
        $method = new ReflectionMethod(MysqlServer::class, 'cve');
        $menu = (string) file_get_contents($root . '/App/view/MysqlServer/menu.view.php');

        $this->assertTrue($method->isPublic());
        $this->assertStringContainsString('Member[] = "MysqlServer/cve"', $sampleAcl);
        $this->assertStringContainsString('ReadOnly[] = "MysqlServer/cve"', $sampleAcl);
        $this->assertStringContainsString("\$menu['MysqlServer']['cve']", $menu);
    }
}
