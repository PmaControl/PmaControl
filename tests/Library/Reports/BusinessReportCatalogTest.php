<?php

namespace Tests\Library\Reports;

use App\Library\Reports\BusinessReportCatalog;
use PHPUnit\Framework\TestCase;

final class BusinessReportCatalogTest extends TestCase
{
    public function testNormalizeReportDefaultsToOverview(): void
    {
        $this->assertSame('overview', BusinessReportCatalog::normalizeReport('unknown'));
        $this->assertSame('server_load', BusinessReportCatalog::normalizeReport('server_load'));
    }

    public function testBuildFromRowsRanksReportsAndBuildsCharts(): void
    {
        $payload = BusinessReportCatalog::buildFromRows(
            [
                ['id' => 1, 'display_name' => 'db-a', 'client' => 'Client A', 'environment' => 'Prod', 'ip' => '10.0.0.1', 'port' => 3306],
                ['id' => 2, 'display_name' => 'db-b', 'client' => 'Client B', 'environment' => 'Preprod', 'ip' => '10.0.0.2', 'port' => 3306],
            ],
            [
                ['id_mysql_server' => 1, 'name' => 'cpu_usage', 'value' => 25.5, 'bucket_start' => '2026-04-30 10:00:00'],
                ['id_mysql_server' => 1, 'name' => 'memory_used', 'value' => 50, 'bucket_start' => '2026-04-30 10:00:00'],
                ['id_mysql_server' => 1, 'name' => 'memory_total', 'value' => 100, 'bucket_start' => '2026-04-30 10:00:00'],
                ['id_mysql_server' => 2, 'name' => 'cpu_usage', 'value' => 85.2, 'bucket_start' => '2026-04-30 10:00:00'],
                ['id_mysql_server' => 2, 'name' => 'seconds_behind_source', 'value' => 420, 'bucket_start' => '2026-04-30 10:00:00'],
            ],
            [
                ['id_mysql_server' => 1, 'display_name' => 'db-a', 'client' => 'Client A', 'environment' => 'Prod', 'schema_name' => 'app', 'tables_count' => 12, 'rows_count' => 5000, 'data_bytes' => 1073741824, 'index_bytes' => 536870912, 'total_bytes' => 1610612736],
            ],
            [
                ['id' => 1, 'libelle' => 'backup-a', 'ip' => '10.0.1.10', 'port' => 2049, 'path' => '/srv/backup', 'date' => '2026-04-30 09:00:00', 'size' => 1000, 'used' => 870, 'available' => 130, 'percent' => 87, 'backup' => 800],
            ],
            [
                ['id_mysql_server' => 1, 'display_name' => 'db-a', 'client' => 'Client A', 'environment' => 'Prod', 'schema_name' => 'app', 'count_star' => 100, 'no_index_used' => 9, 'no_good_index_used' => 3, 'last_seen' => '2026-04-30 08:00:00', 'query_sample_text' => 'SELECT * FROM orders WHERE email = ?'],
            ],
            'overview',
            10
        );

        $this->assertSame('overview', $payload['selected_report']['slug']);
        $this->assertCount(6, $payload['summary_cards']);

        $serverLoad = $this->sectionBySlug($payload, 'server_load');
        $this->assertSame('db-b', $serverLoad['tables'][0]['rows'][0][0]);
        $this->assertSame('85.20%', $serverLoad['tables'][0]['rows'][0][3]);
        $this->assertSame('reports-server-load', $serverLoad['charts'][0]['id']);

        $lag = $this->sectionBySlug($payload, 'replication_lag');
        $this->assertSame('db-b', $lag['tables'][0]['rows'][0][0]);
        $this->assertSame('7m 0s', $lag['tables'][0]['rows'][0][3]);

        $database = $this->sectionBySlug($payload, 'database_size');
        $this->assertSame('1.50 GiB', $database['tables'][0]['rows'][0][8]);

        $digest = $this->sectionBySlug($payload, 'unused_indexes');
        $digestScore = (int)str_replace(',', '', $digest['tables'][0]['rows'][0][5])
            + (int)str_replace(',', '', $digest['tables'][0]['rows'][0][6]);
        $this->assertSame(12, $digestScore);
    }

    public function testSpecificReportOnlyReturnsSelectedSection(): void
    {
        $payload = BusinessReportCatalog::buildFromRows([], [], [], [], [], 'storage_capacity', 5);

        $this->assertSame('storage_capacity', $payload['selected_report']['slug']);
        $this->assertCount(1, $payload['sections']);
        $this->assertSame('storage_capacity', $payload['sections'][0]['slug']);
    }

    public function testMetricSqlPinsVariablesToExpectedCollectors(): void
    {
        $reflection = new \ReflectionMethod(BusinessReportCatalog::class, 'metricSql');

        $sql = (string)$reflection->invoke(null);

        $this->assertStringContainsString("v.`from` = 'ssh_stats'", $sql);
        $this->assertStringContainsString("v.`from` = 'slave'", $sql);
        $this->assertStringContainsString("'memory_used'", $sql);
        $this->assertStringContainsString("'seconds_behind_source'", $sql);
    }

    private function sectionBySlug(array $payload, string $slug): array
    {
        foreach ($payload['sections'] as $section) {
            if (($section['slug'] ?? '') === $slug) {
                return $section;
            }
        }

        $this->fail('Section not found: ' . $slug);
    }
}
