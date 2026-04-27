<?php

declare(strict_types=1);

use App\Controller\AggregateMetric;
use PHPUnit\Framework\TestCase;

final class AggregateMetricTest extends TestCase
{
    private AggregateMetric $controller;

    protected function setUp(): void
    {
        $this->controller = new AggregateMetric('Controller', 'View', []);
    }

    public function testExpiredAggregatePartitionsUsePartitionBoundary(): void
    {
        $expired = $this->invokePrivate('getExpiredPartitionNames', [
            [
                ['PARTITION_NAME' => 'p739000', 'PARTITION_DESCRIPTION' => '739000'],
                ['PARTITION_NAME' => 'p739001', 'PARTITION_DESCRIPTION' => '739001'],
                ['PARTITION_NAME' => 'p739002', 'PARTITION_DESCRIPTION' => '739002'],
                ['PARTITION_NAME' => 'pmax', 'PARTITION_DESCRIPTION' => 'MAXVALUE'],
            ],
            739001,
        ]);

        $this->assertSame(['p739000', 'p739001'], $expired);
    }

    public function testDropPartitionSqlUsesAlterTableInsteadOfDelete(): void
    {
        $sql = $this->invokePrivate('buildDropPartitionsSql', [
            'aggregate_metric_10s',
            ['p739000', 'p739001'],
        ]);

        $this->assertSame(
            'ALTER TABLE `aggregate_metric_10s` DROP PARTITION `p739000`,`p739001`',
            $sql
        );
        $this->assertStringNotContainsString('DELETE FROM', $sql);
    }

    public function testFuturePartitionSqlAddsDailyBoundary(): void
    {
        $sql = $this->invokePrivate('buildAddPartitionSql', ['aggregate_metric_1m', 739010]);

        $this->assertSame(
            'ALTER TABLE `aggregate_metric_1m` ADD PARTITION (PARTITION `p739010` VALUES LESS THAN (739010))',
            $sql
        );
    }

    public function testMaxValuePartitionSqlReorganizesInsteadOfDeletingRows(): void
    {
        $sql = $this->invokePrivate('buildReorganizeMaxValuePartitionSql', [
            'aggregate_metric_1h',
            'pmax',
            739010,
        ]);

        $this->assertSame(
            'ALTER TABLE `aggregate_metric_1h` REORGANIZE PARTITION `pmax` INTO (PARTITION `p739010` VALUES LESS THAN (739010),PARTITION `pmax` VALUES LESS THAN MAXVALUE)',
            $sql
        );
        $this->assertStringNotContainsString('DELETE FROM', $sql);
    }

    public function testRetentionDeleteSqlRemainsAvailableAsFallback(): void
    {
        $sql = $this->invokePrivate('buildRetentionDeleteSql', ['aggregate_metric_10m', 365]);

        $this->assertSame(
            'DELETE FROM `aggregate_metric_10m` WHERE `bucket_start` < DATE_SUB(NOW(), INTERVAL 365 DAY)',
            $sql
        );
    }

    public function testPartitionRetentionPathDropsPartitionsWithoutDelete(): void
    {
        $db = new AggregateMetricFakeDb([
            ['PARTITION_NAME' => 'p739000', 'PARTITION_DESCRIPTION' => '739000'],
            ['PARTITION_NAME' => 'p739001', 'PARTITION_DESCRIPTION' => '739001'],
            ['PARTITION_NAME' => 'p739002', 'PARTITION_DESCRIPTION' => '739002'],
        ]);

        $purged = $this->invokePrivate('purgeRetentionByPartitions', [
            $db,
            'aggregate_metric_10s',
            14,
        ]);

        $queries = implode("\n", $db->queries);
        $this->assertTrue($purged);
        $this->assertStringContainsString(
            'ALTER TABLE `aggregate_metric_10s` DROP PARTITION `p739000`,`p739001`',
            $queries
        );
        $this->assertStringNotContainsString('DELETE FROM', $queries);
    }

    /**
     * @param array<int,mixed> $arguments
     */
    private function invokePrivate(string $method, array $arguments): mixed
    {
        $reflection = new ReflectionMethod(AggregateMetric::class, $method);

        return $reflection->invokeArgs($this->controller, $arguments);
    }
}

final class AggregateMetricFakeDb
{
    /**
     * @var array<int,string>
     */
    public array $queries = [];

    /**
     * @param array<int,array<string,string>> $partitions
     */
    public function __construct(private array $partitions)
    {
    }

    public function sql_query(string $sql): AggregateMetricFakeResult
    {
        $this->queries[] = $sql;

        if (str_contains($sql, 'information_schema.PARTITIONS')) {
            return new AggregateMetricFakeResult($this->partitions);
        }

        if (str_contains($sql, 'SELECT TO_DAYS')) {
            return new AggregateMetricFakeResult([['partition_number' => '739001']]);
        }

        return new AggregateMetricFakeResult([]);
    }

    public function sql_fetch_array(AggregateMetricFakeResult $result, int $mode): ?array
    {
        return array_shift($result->rows);
    }

    public function sql_real_escape_string(string $value): string
    {
        return addslashes($value);
    }
}

final class AggregateMetricFakeResult
{
    /**
     * @param array<int,array<string,string>> $rows
     */
    public function __construct(public array $rows)
    {
    }
}
