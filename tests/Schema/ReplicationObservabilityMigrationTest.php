<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ReplicationObservabilityMigrationTest extends TestCase
{
    private string $sql;

    protected function setUp(): void
    {
        $path = __DIR__ . '/../../sql/incremental_v2/20260518_replication_observability.sql';
        $this->assertFileExists($path);
        $this->sql = (string)file_get_contents($path);
    }

    public function testMigrationAddsErrantGtidCacheColumns(): void
    {
        foreach (['errant_gtid_set', 'errant_gtid_sampled_at', 'errant_gtid_ignore', 'replica_lag_sla_seconds'] as $column) {
            $this->assertStringContainsString($column, $this->sql);
        }
        $this->assertStringContainsString('system_versioning_alter_history', $this->sql);
    }

    public function testMigrationRegistersTimeSeriesVariables(): void
    {
        $this->assertStringContainsString("'gtid_executed', 'TEXT', 'slave'", $this->sql);
        $this->assertStringContainsString("'rpl_semi_sync_master_avg_wait_time', 'INT', 'status'", $this->sql);
        $this->assertStringContainsString("'replication_applier_status_by_worker', 'JSON', 'slave'", $this->sql);
    }

    public function testMigrationCreatesAnnotationTable(): void
    {
        $this->assertStringContainsString('CREATE TABLE IF NOT EXISTS `replication_annotation`', $this->sql);
        $this->assertStringContainsString('KEY `idx_server_time`', $this->sql);
        $this->assertStringContainsString("ENUM('deploy','migration','tuning','incident','other')", $this->sql);
    }
}
