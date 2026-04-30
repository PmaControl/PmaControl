<?php

declare(strict_types=1);

use App\Controller\Pmm;
use PHPUnit\Framework\TestCase;

final class PmmPromotionTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
    }

    private function resolveDashboardServerId(array $param, ?callable $fallbackResolver = null): ?int
    {
        $method = new ReflectionMethod(Pmm::class, 'resolveDashboardServerId');

        return $method->invoke(null, $param, $fallbackResolver);
    }

    private function getDefaultDashboardServerSql(string $filterWhere = ''): string
    {
        $method = new ReflectionMethod(Pmm::class, 'getDefaultDashboardServerSql');

        return $method->invoke(null, $filterWhere);
    }

    public function testPmmMenuMigrationPromotesDashboardsUnderMainDashboard(): void
    {
        $migration = (string) file_get_contents($this->root . '/sql/incremental_v2/20260430_pmm_dashboard_menu.sql');

        $this->assertStringContainsString("'PMM Dashboards'", $migration);
        $this->assertStringContainsString("`title` = 'Dashboard'", $migration);
        $this->assertStringContainsString("'{LINK}Pmm/index'", $migration);
        $this->assertStringContainsString("'Pmm'", $migration);
        $this->assertStringContainsString("'index'", $migration);
        $this->assertStringContainsString('@pmm_menu_exists = 0', $migration);
        $this->assertStringContainsString('@pmm_group_id', $migration);
        $this->assertStringContainsString('`group_id` = @pmm_group_id', $migration);
        $this->assertSame(1, substr_count($migration, 'INSERT INTO `menu`'));
    }

    public function testPmmAclKeepsDashboardsReadonlyAndExportCliOnly(): void
    {
        $acl = (string) file_get_contents($this->root . '/config_sample/acl.config.ini');

        foreach ([
            'Pmm/index',
            'Pmm/menu',
            'Pmm/innodb',
            'Pmm/galera',
            'Pmm/myisam',
            'Pmm/aria',
            'Pmm/binlog',
            'Pmm/system',
            'Pmm/performance_schema',
            'Pmm/rocksdb',
            'Pmm/proxysql',
        ] as $route) {
            $this->assertStringContainsString('ReadOnly[] = "' . $route . '"', $acl, $route);
        }

        $this->assertStringNotContainsString('ReadOnly[] = "Pmm/export"', $acl);
    }

    public function testDashboardServerResolutionUsesRouteIdBeforeFallback(): void
    {
        $this->assertSame(12, $this->resolveDashboardServerId(['12'], static fn () => 99));
        $this->assertSame(12, $this->resolveDashboardServerId([12], static fn () => 99));
    }

    public function testDashboardServerResolutionFallsBackWithoutHardcodedServerOne(): void
    {
        $this->assertSame(42, $this->resolveDashboardServerId([], static fn () => 42));
        $this->assertSame(42, $this->resolveDashboardServerId(['0'], static fn () => 42));
        $this->assertSame(42, $this->resolveDashboardServerId(['invalid'], static fn () => 42));
        $this->assertNull($this->resolveDashboardServerId([], static fn () => null));
    }

    public function testDefaultDashboardServerSqlTargetsMonitoredNonDeletedServers(): void
    {
        $sql = $this->getDefaultDashboardServerSql();

        $this->assertStringContainsString('FROM mysql_server a', $sql);
        $this->assertStringContainsString('a.is_deleted = 0', $sql);
        $this->assertStringContainsString('a.is_monitored = 1', $sql);
        $this->assertStringContainsString('ORDER BY a.display_name, a.id', $sql);
        $this->assertStringContainsString('LIMIT 1', $sql);
    }

    public function testDefaultDashboardServerSqlCanApplyTenantFilter(): void
    {
        $sql = $this->getDefaultDashboardServerSql(' AND `a`.id_client IN (3)');

        $this->assertStringContainsString('AND `a`.id_client IN (3)', $sql);
        $this->assertStringContainsString('ORDER BY a.display_name, a.id', $sql);
    }

    public function testPmmViewsDoNotDefaultToMysqlServerOne(): void
    {
        $menuView = (string) file_get_contents($this->root . '/App/view/Pmm/menu.view.php');
        $partialView = (string) file_get_contents($this->root . '/App/view/Pmm/_dashboard_partial.view.php');
        $serverMenuView = (string) file_get_contents($this->root . '/App/view/MysqlServer/menu.view.php');

        $this->assertStringContainsString('? (int)$param[0] : 0', $menuView);
        $this->assertStringContainsString('if ($serverId < 1)', $menuView);
        $this->assertStringContainsString('Select a monitored MySQL server', $menuView);
        $this->assertStringNotContainsString('?? 1', $menuView);

        $this->assertStringContainsString('$serverLabel = $serverId > 0', $partialView);
        $this->assertStringContainsString('No server selected', $partialView);
        $this->assertStringContainsString('$menu[\'Pmm\'][\'index\'] = __(\'PMM\');', $serverMenuView);
    }
}
