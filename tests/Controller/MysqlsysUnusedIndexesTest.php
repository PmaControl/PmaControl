<?php

declare(strict_types=1);

use App\Controller\Mysqlsys;
use PHPUnit\Framework\TestCase;

final class MysqlsysUnusedIndexesTest extends TestCase
{
    public function testAppendSchemaUnusedIndexEstimatesAddsRowsAndSpaceGain(): void
    {
        $rows = [[
            'object_schema' => 'app',
            'object_name' => 'orders',
            'index_name' => 'idx_status',
        ]];
        $stats = [
            'app.orders' => [
                'table_rows' => 123456,
                'table_index_bytes' => 104857600,
                'secondary_index_count' => 4,
            ],
        ];

        $result = $this->invokePrivateStatic('appendSchemaUnusedIndexEstimates', [$rows, $stats]);

        $this->assertSame('123 456', $result[0]['table_rows']);
        $this->assertSame('100.00 Mo', $result[0]['table_index_size']);
        $this->assertSame('25.00 Mo', $result[0]['estimated_gain']);
    }

    public function testAppendSchemaUnusedIndexEstimatesMarksMissingStatsAsUnavailable(): void
    {
        $rows = [[
            'object_schema' => 'app',
            'object_name' => 'missing_table',
            'index_name' => 'idx_old',
        ]];

        $result = $this->invokePrivateStatic('appendSchemaUnusedIndexEstimates', [$rows, []]);

        $this->assertSame('n/a', $result[0]['table_rows']);
        $this->assertSame('n/a', $result[0]['table_index_size']);
        $this->assertSame('n/a', $result[0]['estimated_gain']);
    }

    public function testAppendSchemaUnusedIndexEstimatesAvoidsGainWithoutSecondaryIndexCount(): void
    {
        $rows = [[
            'object_schema' => 'app',
            'object_name' => 'orders',
            'index_name' => 'idx_status',
        ]];
        $stats = [
            'app.orders' => [
                'table_rows' => 0,
                'table_index_bytes' => 1048576,
                'secondary_index_count' => 0,
            ],
        ];

        $result = $this->invokePrivateStatic('appendSchemaUnusedIndexEstimates', [$rows, $stats]);

        $this->assertSame('0', $result[0]['table_rows']);
        $this->assertSame('1.00 Mo', $result[0]['table_index_size']);
        $this->assertSame('n/a', $result[0]['estimated_gain']);
    }

    private function invokePrivateStatic(string $methodName, array $arguments): mixed
    {
        $reflection = new ReflectionClass(Mysqlsys::class);
        $method = $reflection->getMethod($methodName);

        return $method->invokeArgs(null, $arguments);
    }
}
