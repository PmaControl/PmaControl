<?php

declare(strict_types=1);

use App\Controller\Home;
use PHPUnit\Framework\TestCase;

final class HomeProxysqlAuditCardTest extends TestCase
{
    public function testEmptyShapeWhenSnapshotTableMissing(): void
    {
        // Older schema: sql_query_silent returns false → table absent.
        $db = new HomeAuditFakeDb(returnsFalse: true);
        $card = Home::buildProxysqlAuditCard($db);
        $this->assertFalse($card['available']);
        $this->assertSame(0, $card['total_servers']);
        $this->assertSame(0, $card['critical']);
        $this->assertSame(0, $card['warning']);
        $this->assertSame(0, $card['info']);
        $this->assertNull($card['worst_id']);
        $this->assertNull($card['worst_name']);
    }

    public function testAggregatesCountsAndPicksWorstByCritical(): void
    {
        $db = new HomeAuditFakeDb([
            ['id_proxysql_server' => 1, 'findings_critical' => 0, 'findings_warning' => 5, 'findings_info' => 1, 'display_name' => 'px1'],
            ['id_proxysql_server' => 2, 'findings_critical' => 4, 'findings_warning' => 1, 'findings_info' => 0, 'display_name' => 'px2'],
            ['id_proxysql_server' => 3, 'findings_critical' => 2, 'findings_warning' => 9, 'findings_info' => 7, 'display_name' => 'px3'],
        ]);
        $card = Home::buildProxysqlAuditCard($db);
        $this->assertTrue($card['available']);
        $this->assertSame(3, $card['total_servers']);
        $this->assertSame(6,  $card['critical']);
        $this->assertSame(15, $card['warning']);
        $this->assertSame(8,  $card['info']);
        $this->assertSame(2,     $card['worst_id']);   // most criticals
        $this->assertSame('px2', $card['worst_name']);
    }

    public function testWorstFallsBackToWarningWhenNoCriticals(): void
    {
        $db = new HomeAuditFakeDb([
            ['id_proxysql_server' => 1, 'findings_critical' => 0, 'findings_warning' => 2, 'findings_info' => 0, 'display_name' => 'px1'],
            ['id_proxysql_server' => 2, 'findings_critical' => 0, 'findings_warning' => 7, 'findings_info' => 0, 'display_name' => 'px2'],
        ]);
        $card = Home::buildProxysqlAuditCard($db);
        $this->assertSame(2,     $card['worst_id']);
        $this->assertSame('px2', $card['worst_name']);
    }

    public function testEmptyResultStillMarksAvailable(): void
    {
        // No snapshot rows yet (aspirateur hasn't run) — the table exists
        // but is empty. We still flag available=true so the operator can
        // see the card-absent state was not a hard failure.
        $db = new HomeAuditFakeDb([]);
        $card = Home::buildProxysqlAuditCard($db);
        $this->assertTrue($card['available']);
        $this->assertSame(0, $card['total_servers']);
        $this->assertNull($card['worst_id']);
    }
}

final class HomeAuditFakeDb
{
    private array $rows;
    private bool $returnsFalse;
    private int $cursor = 0;
    public function __construct(array $rows = [], bool $returnsFalse = false)
    {
        $this->rows = $rows;
        $this->returnsFalse = $returnsFalse;
    }
    public function sql_query_silent(string $sql)
    {
        if ($this->returnsFalse) return false;
        $this->cursor = 0;
        return 'mock';
    }
    public function sql_fetch_array($res, int $mode = MYSQLI_ASSOC)
    {
        if ($this->cursor >= count($this->rows)) return null;
        return $this->rows[$this->cursor++];
    }
}
