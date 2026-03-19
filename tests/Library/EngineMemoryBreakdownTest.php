<?php

namespace App\Tests\Library;

use App\Library\EngineMemoryBreakdown;
use PHPUnit\Framework\TestCase;

final class EngineMemoryBreakdownTest extends TestCase
{
    public function testNormalizePerformanceSchemaEventNameReplacesEscapedSlashes(): void
    {
        $this->assertSame(
            'memory/performance_schema/table_handles',
            EngineMemoryBreakdown::normalizePerformanceSchemaEventName('memory\\/performance_schema\\/table_handles')
        );
    }

    public function testClassifyPerformanceSchemaEventMapsKnownEngines(): void
    {
        $this->assertSame('InnoDB', EngineMemoryBreakdown::classifyPerformanceSchemaEvent('memory/innodb/buf_buf_pool'));
        $this->assertSame('RocksDB', EngineMemoryBreakdown::classifyPerformanceSchemaEvent('memory/rocksdb/block_cache'));
        $this->assertSame('Performance Schema', EngineMemoryBreakdown::classifyPerformanceSchemaEvent('memory/performance_schema/table_handles'));
        $this->assertSame('Other', EngineMemoryBreakdown::classifyPerformanceSchemaEvent('memory/sql/THD::main_mem_root'));
    }

    public function testAggregatePerformanceSchemaMemoryGroupsByEngineAndSumsBytes(): void
    {
        $rows = [
            [
                'event_name' => 'memory\\/innodb\\/buf_buf_pool',
                'high_number_of_bytes_used' => '1024',
            ],
            [
                'event_name' => 'memory\\/innodb\\/dict_stats_bg',
                'high_number_of_bytes_used' => '2048',
            ],
            [
                'event_name' => 'memory\\/performance_schema\\/table_handles',
                'high_number_of_bytes_used' => '512',
            ],
        ];

        $grouped = EngineMemoryBreakdown::aggregatePerformanceSchemaMemory($rows);

        $this->assertSame(3072.0, $grouped['InnoDB']['bytes']);
        $this->assertCount(2, $grouped['InnoDB']['events']);
        $this->assertSame(512.0, $grouped['Performance Schema']['bytes']);
    }

    public function testFormatBytesHumanizesValues(): void
    {
        $this->assertSame('0 B', EngineMemoryBreakdown::formatBytes(0));
        $this->assertSame('1 KiB', EngineMemoryBreakdown::formatBytes(1024));
        $this->assertSame('1 MiB', EngineMemoryBreakdown::formatBytes(1024 * 1024));
    }

    public function testAggregateProcessMemorySeriesKeepsOthersLast(): void
    {
        $snapshots = [
            [
                'label' => '10:00:00',
                'processes' => [
                    'mariadbd' => 1000,
                    'php' => 300,
                    'sshd' => 50,
                ],
            ],
            [
                'label' => '10:05:00',
                'processes' => [
                    'mariadbd' => 1200,
                    'php' => 200,
                    'apache2' => 25,
                ],
            ],
        ];

        $result = EngineMemoryBreakdown::aggregateProcessMemorySeries($snapshots, 2);

        $this->assertSame(['10:00:00', '10:05:00'], $result['labels']);
        $this->assertSame('mariadbd', $result['datasets'][0]['label']);
        $this->assertSame('php', $result['datasets'][1]['label']);
        $this->assertSame('Others', $result['datasets'][2]['label']);
        $this->assertSame([50.0, 25.0], $result['datasets'][2]['data']);
    }

    public function testNormalizeProcessMemoryRangeAcceptsPreset(): void
    {
        $range = EngineMemoryBreakdown::normalizeProcessMemoryRange(['range' => '1h']);

        $this->assertSame('preset', $range['mode']);
        $this->assertSame('1h', $range['preset']);
        $this->assertSame('Process memory (top 20 + Others, 1h)', $range['title']);
    }

    public function testNormalizeProcessMemoryRangeAcceptsCustomRangeWithinOneDay(): void
    {
        $range = EngineMemoryBreakdown::normalizeProcessMemoryRange([
            'range_mode' => 'custom',
            'range' => '6h',
            'start' => '2026-03-18T10:00',
            'end' => '2026-03-18T18:00',
        ]);

        $this->assertSame('custom', $range['mode']);
        $this->assertSame('2026-03-18T10:00', $range['start_value']);
        $this->assertSame('2026-03-18T18:00', $range['end_value']);
    }

    public function testNormalizeProcessMemoryRangeKeepsPresetWhenDatesArePrefilled(): void
    {
        $range = EngineMemoryBreakdown::normalizeProcessMemoryRange([
            'range' => '24h',
            'range_mode' => 'preset',
            'start' => '2026-03-18T10:00',
            'end' => '2026-03-18T18:00',
        ]);

        $this->assertSame('preset', $range['mode']);
        $this->assertSame('24h', $range['preset']);
        $this->assertSame('Process memory (top 20 + Others, 24h)', $range['title']);
    }
}
