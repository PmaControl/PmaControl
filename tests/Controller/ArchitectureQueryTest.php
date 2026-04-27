<?php

declare(strict_types=1);

use App\Controller\Architecture;
use PHPUnit\Framework\TestCase;

final class ArchitectureQueryTest extends TestCase
{
    public function testGraphSqlUsesLatestDot3StateWithoutWindowFunction(): void
    {
        $sql = self::buildGraphSql([]);

        $this->assertStringContainsString('SELECT dg.id, dg.svg, dg.height, dg.width', $sql);
        $this->assertStringContainsString(
            'dc.id_dot3_information = (SELECT MAX(id_dot3_information) FROM dot3_cluster)',
            $sql
        );
        $this->assertStringContainsString('ORDER BY dg.height DESC, dg.width DESC', $sql);
        $this->assertStringNotContainsString('dg.*', $sql);
        $this->assertStringNotContainsString('DENSE_RANK', $sql);
        $this->assertStringNotContainsString('snapshot_rank', $sql);
    }

    public function testGraphSqlUsesSelectedClientFilterWithoutCoalesce(): void
    {
        $sql = self::buildGraphSql([1, 8, 99]);

        $this->assertStringContainsString('EXISTS (', $sql);
        $this->assertStringContainsString('ms.id_client IN (1,8,99)', $sql);
        $this->assertStringContainsString('ms.is_deleted = 0', $sql);
        $this->assertStringNotContainsString('COALESCE(ms.is_deleted', $sql);
    }

    public function testRedundantGraphClientFilterIsRemovedWhenAllActiveClientsAreSelected(): void
    {
        $selectedClients = self::removeRedundantGraphClientFilter(
            self::fakeDbWithActiveClients([1, 2, 3]),
            [1, 2, 3, 99]
        );

        $this->assertSame([], $selectedClients);
    }

    public function testGraphClientFilterIsKeptForPartialSelection(): void
    {
        $selectedClients = self::removeRedundantGraphClientFilter(
            self::fakeDbWithActiveClients([1, 2, 3]),
            [1, 3]
        );

        $this->assertSame([1, 3], $selectedClients);
    }

    /**
     * @param array<int,int> $selectedClients
     */
    private static function buildGraphSql(array $selectedClients): string
    {
        $reflection = new ReflectionClass(Architecture::class);
        $method = $reflection->getMethod('buildGraphSql');

        return $method->invoke(null, $selectedClients);
    }

    /**
     * @param array<int,int> $selectedClients
     * @return array<int,int>
     */
    private static function removeRedundantGraphClientFilter($db, array $selectedClients): array
    {
        $reflection = new ReflectionClass(Architecture::class);
        $method = $reflection->getMethod('removeRedundantGraphClientFilter');

        return $method->invoke(null, $db, $selectedClients);
    }

    /**
     * @param array<int,int> $activeClients
     */
    private static function fakeDbWithActiveClients(array $activeClients): object
    {
        return new class ($activeClients) {
            /**
             * @var array<int,array{id_client:int}>
             */
            private array $rows;

            /**
             * @param array<int,int> $activeClients
             */
            public function __construct(array $activeClients)
            {
                $this->rows = array_map(
                    static fn (int $idClient): array => ['id_client' => $idClient],
                    $activeClients
                );
            }

            public function sql_query(string $sql): string
            {
                return $sql;
            }

            /**
             * @return array{id_client:int}|false
             */
            public function sql_fetch_array(string $res, int $mode)
            {
                return array_shift($this->rows) ?: false;
            }
        };
    }
}
