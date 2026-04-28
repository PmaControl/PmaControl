<?php

declare(strict_types=1);

use App\Library\Kpi\Dot3KpiRecorder;
use PHPUnit\Framework\TestCase;

final class Dot3RunLoggingTest extends TestCase
{
    public function testMeasureGroupsCountsUniqueNodesAndTreeEdges(): void
    {
        $metrics = Dot3KpiRecorder::measureGroups([
            [1, 2],
            [2, 3, 3],
            [4, 5, 6],
            [],
        ]);

        $this->assertSame(6, $metrics['nodes']);
        $this->assertSame(4, $metrics['edges']);
    }

    public function testBuildStartRunSqlStoresStartedAt(): void
    {
        $sql = Dot3KpiRecorder::buildStartRunSql($this->fakeDb(), [
            'started_at' => '2026-04-28 14:00:00.000001',
        ]);

        $this->assertStringContainsString('INSERT INTO `dot3_run`', $sql);
        $this->assertStringContainsString("'2026-04-28 14:00:00.000001'", $sql);
    }

    public function testBuildFinishRunPayloadAndSqlStoreTotals(): void
    {
        $payload = Dot3KpiRecorder::buildFinishRunPayload([
            'id' => 10,
            'started_at' => 100.0,
            'ended_at' => 101.5,
            'groups_total' => 3,
            'nodes_total' => 12,
            'edges_total' => 9,
            'dot_size_bytes' => 2048,
            'svg_size_bytes' => 4096,
        ]);

        $this->assertIsArray($payload);
        $this->assertSame(1500, $payload['duration_ms']);

        $sql = Dot3KpiRecorder::buildFinishRunSql($this->fakeDb(), $payload);
        $this->assertStringContainsString('`duration_ms` = 1500', $sql);
        $this->assertStringContainsString('`groups_total` = 3', $sql);
        $this->assertStringContainsString('`dot_size_bytes` = 2048', $sql);
        $this->assertStringContainsString('WHERE `id` = 10', $sql);
    }

    public function testBuildRecordGroupSqlAllowsMysqlRouter(): void
    {
        $payload = Dot3KpiRecorder::buildGroupPayload([
            'id_dot3_run' => 10,
            'group_kind' => 'mysqlrouter',
            'duration_ms' => 12,
            'nodes' => 4,
            'edges' => 3,
        ]);

        $this->assertIsArray($payload);
        $sql = Dot3KpiRecorder::buildRecordGroupSql($this->fakeDb(), $payload);

        $this->assertStringContainsString('INSERT INTO `dot3_run_group`', $sql);
        $this->assertStringContainsString("'mysqlrouter'", $sql);
        $this->assertStringContainsString('ON DUPLICATE KEY UPDATE', $sql);
    }

    public function testBuildGroupPayloadRejectsUnknownKind(): void
    {
        $this->assertNull(Dot3KpiRecorder::buildGroupPayload([
            'id_dot3_run' => 10,
            'group_kind' => 'unknown',
        ]));
    }

    public function testMeasureGraphFilesUsesDotLengthAndSvgFileSize(): void
    {
        $svg = tempnam(sys_get_temp_dir(), 'pmacontrol-dot3-svg-');
        $this->assertIsString($svg);

        try {
            file_put_contents($svg, '<svg>ok</svg>');
            $sizes = Dot3KpiRecorder::measureGraphFiles($svg, 'digraph G { a -> b; }');

            $this->assertSame(strlen('digraph G { a -> b; }'), $sizes['dot_size_bytes']);
            $this->assertSame(strlen('<svg>ok</svg>'), $sizes['svg_size_bytes']);
        } finally {
            if (is_file($svg)) {
                unlink($svg);
            }
        }
    }

    public function testDot3RunUsesKpiRecorderHooks(): void
    {
        $source = file_get_contents(__DIR__.'/../../App/Controller/Dot3.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('Dot3KpiRecorder::startRun', $source);
        $this->assertStringContainsString('Dot3KpiRecorder::finishRun', $source);
        $this->assertStringContainsString('Dot3KpiRecorder::recordGroup', $source);
        $this->assertStringContainsString('generateMeasuredGroup', $source);
        $this->assertStringContainsString('Dot3KpiRecorder::measureGraphFiles', $source);
    }

    public function testMysqlRouterEnumPatchAddsValueAtEnd(): void
    {
        $sql = file_get_contents(__DIR__.'/../../sql/incremental_v2/20260428_kpi_dot3_mysqlrouter_group.sql');

        $this->assertIsString($sql);
        $this->assertStringContainsString("'imported','mysqlrouter'", $sql);
    }

    private function fakeDb(): object
    {
        return new class {
            public function sql_real_escape_string(string $value): string
            {
                return str_replace("'", "\\'", $value);
            }
        };
    }
}
