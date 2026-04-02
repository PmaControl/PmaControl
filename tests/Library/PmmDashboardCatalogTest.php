<?php

namespace Tests\Library;

use App\Library\PmmDashboardCatalog;
use PHPUnit\Framework\TestCase;

class PmmDashboardCatalogTest extends TestCase
{
    public function testDashboardListContainsExpectedScreens(): void
    {
        $dashboards = PmmDashboardCatalog::getDashboards();

        $this->assertArrayHasKey('overview', $dashboards);
        $this->assertArrayHasKey('system', $dashboards);
        $this->assertArrayHasKey('innodb', $dashboards);
        $this->assertArrayHasKey('binlog', $dashboards);
        $this->assertArrayHasKey('galera', $dashboards);
        $this->assertArrayHasKey('performance_schema', $dashboards);
        $this->assertArrayHasKey('aria', $dashboards);
        $this->assertArrayHasKey('myisam', $dashboards);
        $this->assertArrayHasKey('rocksdb', $dashboards);
        $this->assertArrayHasKey('proxysql', $dashboards);
    }

    public function testNormalizeRangeDefaultsTo24Hours(): void
    {
        $range = PmmDashboardCatalog::normalizeRange([]);

        $this->assertSame('24h', $range['preset']);
        $this->assertSame('preset', $range['range_mode']);
        $this->assertSame(900, $range['bucket_seconds']);
        $this->assertLessThanOrEqual(86400, $range['end']->getTimestamp() - $range['start']->getTimestamp());
    }

    public function testNormalizeRangeAcceptsCustomWindowWithin24Hours(): void
    {
        $range = PmmDashboardCatalog::normalizeRange([
            'range_mode' => 'custom',
            'start' => '2026-03-20T00:00',
            'end' => '2026-03-20T06:00',
        ]);

        $this->assertSame('custom', $range['range_mode']);
        $this->assertSame('2026-03-20T00:00', $range['start_value']);
        $this->assertSame('2026-03-20T06:00', $range['end_value']);
    }
}
