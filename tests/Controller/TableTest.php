<?php

declare(strict_types=1);

use App\Controller\Table;
use PHPUnit\Framework\TestCase;

final class TableTest extends TestCase
{
    private Table $table;

    protected function setUp(): void
    {
        $this->table = new Table('Controller', 'View', []);
        Table::$hidden_edges = [];
    }

    public function testCalculerPoidsColonneSumsReferencedTables(): void
    {
        $weight = $this->table->calculerPoidsColonne(['orders', 'users'], [
            'orders' => 20,
            'users' => 5,
        ]);

        $this->assertSame(25, $weight);
    }

    public function testSplitTableByColumnBasicDistributesRoundRobin(): void
    {
        $columns = $this->table->splitTableByColumnBasic(['a', 'b', 'c', 'd', 'e'], 2);

        $this->assertSame(['a', 'c', 'e'], $columns[0]);
        $this->assertSame(['b', 'd'], $columns[1]);
    }

    public function testSplitTableByColumnBalancesHeavierTables(): void
    {
        $columns = $this->table->splitTableByColumn([
            't1' => 10,
            't2' => 9,
            't3' => 8,
            't4' => 7,
        ], 2);

        $weights = array_map(
            fn (array $column): int => $this->table->calculerPoidsColonne($column, [
                't1' => 10,
                't2' => 9,
                't3' => 8,
                't4' => 7,
            ]),
            $columns
        );

        $this->assertLessThanOrEqual(2, abs($weights[0] - $weights[1]));
    }

    public function testGenerateHiddenArrowBuildsLinksBetweenColumns(): void
    {
        $this->table->generateHiddenArrow([
            ['users', 'orders'],
            ['items', 'payments'],
        ]);

        $this->assertContains('users:title -> items:title', Table::$hidden_edges);
        $this->assertContains('orders:title -> payments:title', Table::$hidden_edges);
    }

    public function testDiluerCouleurLightensAValidHexColor(): void
    {
        $this->assertSame('#7f7f7f', $this->table->diluerCouleur('#000000', 50));
        $this->assertSame('Format de couleur invalide.', $this->table->diluerCouleur('invalid', 50));
    }
}
