<?php

declare(strict_types=1);

use App\Controller\Dot3;
use PHPUnit\Framework\TestCase;

final class Dot3CompletionMarkerTest extends TestCase
{
    public function testBuildMarkSvgGeneratedSqlUsesSnapshotIdAsReadyMarker(): void
    {
        $this->assertSame(
            'UPDATE dot3_information SET is_svg_generated = 516776 WHERE id = 516776',
            self::buildMarkSvgGeneratedSql(516776)
        );
    }

    public function testShouldMarkSvgGeneratedRequiresPersistedGraphs(): void
    {
        $this->assertTrue(self::shouldMarkSvgGenerated(516776, 1));
        $this->assertFalse(self::shouldMarkSvgGenerated(516776, 0));
        $this->assertFalse(self::shouldMarkSvgGenerated(0, 1));
    }

    public function testBuildMarkSvgGeneratedStatementsCommitsAfterUpdate(): void
    {
        $statements = self::buildMarkSvgGeneratedStatements(516776);

        $this->assertSame(
            [
                'UPDATE dot3_information SET is_svg_generated = 516776 WHERE id = 516776',
                'COMMIT',
            ],
            $statements,
            'is_svg_generated must be COMMITted explicitly: saveGraph() leaves AUTOCOMMIT=0'
            . ' on the shared "RUN" connection and Dot3 cycles are one-shot PHP processes,'
            . ' so without COMMIT the marker is rolled back on exit and the architecture'
            . ' page never sees a "ready" snapshot.'
        );
    }

    public function testMarkerStatementsIssuedAgainstSimulatedAutocommitOffSessionPersist(): void
    {
        $session = new class {
            public bool $autocommit = false;
            public bool $inTransaction = true;
            /** @var array<int,array{id:int,is_svg_generated:int}> */
            public array $rows = [
                ['id' => 516776, 'is_svg_generated' => 0],
            ];
            /** @var array<int,array{id:int,is_svg_generated:int}> */
            public array $committed = [
                ['id' => 516776, 'is_svg_generated' => 0],
            ];

            public function exec(string $sql): void
            {
                if (preg_match('/^UPDATE dot3_information SET is_svg_generated = (\d+) WHERE id = (\d+)$/', $sql, $m)) {
                    foreach ($this->rows as &$row) {
                        if ($row['id'] === (int) $m[2]) {
                            $row['is_svg_generated'] = (int) $m[1];
                        }
                    }
                    $this->inTransaction = true;
                    return;
                }
                if ($sql === 'COMMIT') {
                    $this->committed = $this->rows;
                    $this->inTransaction = false;
                    return;
                }
                throw new RuntimeException('Unsupported SQL: ' . $sql);
            }

            public function processExit(): void
            {
                if ($this->inTransaction) {
                    $this->rows = $this->committed; // MySQL rolls back uncommitted work on disconnect
                }
            }
        };

        foreach (self::buildMarkSvgGeneratedStatements(516776) as $sql) {
            $session->exec($sql);
        }

        $session->processExit();

        $this->assertSame(
            516776,
            $session->rows[0]['is_svg_generated'],
            'Without an explicit COMMIT in the marker, the leftover AUTOCOMMIT=0 state from'
            . ' saveGraph() causes the UPDATE to be rolled back on process exit.'
        );
    }

    private static function buildMarkSvgGeneratedSql(int $idDot3Information): string
    {
        $reflection = new ReflectionClass(Dot3::class);
        $method = $reflection->getMethod('buildMarkSvgGeneratedSql');

        return $method->invoke(null, $idDot3Information);
    }

    /**
     * @return array<int,string>
     */
    private static function buildMarkSvgGeneratedStatements(int $idDot3Information): array
    {
        $reflection = new ReflectionClass(Dot3::class);
        $method = $reflection->getMethod('buildMarkSvgGeneratedStatements');

        return $method->invoke(null, $idDot3Information);
    }

    private static function shouldMarkSvgGenerated(int $idDot3Information, int $savedGraphs): bool
    {
        $reflection = new ReflectionClass(Dot3::class);
        $method = $reflection->getMethod('shouldMarkSvgGenerated');

        return $method->invoke(null, $idDot3Information, $savedGraphs);
    }
}
