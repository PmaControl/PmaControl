<?php

declare(strict_types=1);

use App\Controller\Error as ErrorController;
use PHPUnit\Framework\TestCase;

final class SuperAdminMenuTest extends TestCase
{
    public function testSuperAdminErrorMenuMigrationIsWiredBeforeLogout(): void
    {
        $root = dirname(__DIR__, 2);
        $migration = (string) file_get_contents($root . '/sql/incremental_v2/20260504_superadmin_error_menu.sql');

        $this->assertStringContainsString("'SuperAdmin'", $migration);
        $this->assertStringContainsString("'Logout'", $migration);
        $this->assertStringContainsString('@superadmin_insert_at', $migration);
        $this->assertStringContainsString('`bd` >= @superadmin_insert_at', $migration);
        $this->assertStringContainsString('`bg` >= @superadmin_insert_at', $migration);
        $this->assertStringContainsString('<i class="fa fa-user-secret" aria-hidden="true"></i>', $migration);

        $this->assertStringContainsString("'Errors'", $migration);
        $this->assertStringContainsString('<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>', $migration);
        $this->assertStringContainsString("'{LINK}error/index'", $migration);
        $this->assertStringContainsString("'error'", $migration);
        $this->assertStringContainsString("'index'", $migration);
        $this->assertSame(2, substr_count($migration, 'INSERT INTO `menu`'));

        $this->assertStringContainsString("'Developer'", $migration);
        $this->assertStringContainsString('@superadmin_developer_insert_at', $migration);
        $this->assertStringContainsString('@superadmin_move_developer', $migration);
        $this->assertStringContainsString('`bg` = -`bg`', $migration);
        $this->assertStringContainsString('`parent_id` = @superadmin_menu_id', $migration);
    }

    public function testErrorIndexRouteExistsForSuperAdminMenu(): void
    {
        $method = new ReflectionMethod(ErrorController::class, 'index');

        $this->assertTrue($method->isPublic());
    }
}
