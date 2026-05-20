<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Checks\Topology\GaleraHostgroupIdCollision;
use App\Library\ProxySqlAudit\Finding;
use PHPUnit\Framework\TestCase;

final class GaleraHostgroupIdCollisionTest extends TestCase
{
    public function testCriticalWhenOneClusterRowReusesAnId(): void
    {
        $db = new GaleraHgCollideFakeDb([
            ['writer_hostgroup' => 1, 'reader_hostgroup' => 2, 'backup_writer_hostgroup' => 5, 'offline_hostgroup' => 5],
        ]);
        $f = (new GaleraHostgroupIdCollision())->run($db)[0];
        $this->assertSame(Finding::SEVERITY_CRITICAL, $f->severity);
        $this->assertSame('topology', $f->category);
        $this->assertSame('topology.galera-hostgroup-id-collision', $f->checkId);
        $this->assertNull($f->ticket);
        $this->assertStringContainsString('writer=1', $f->evidence);
        $this->assertStringContainsString('backup_writer=5 offline=5', $f->evidence);
        $this->assertStringContainsString('LOAD MYSQL SERVERS TO RUNTIME', $f->fix);
    }

    public function testReturnsEmptyWhenAllFourIdsAreDistinct(): void
    {
        $db = new GaleraHgCollideFakeDb([]);
        $this->assertSame([], (new GaleraHostgroupIdCollision())->run($db));
    }

    public function testReturnsEmptyOnSilentQueryFailure(): void
    {
        $db = new GaleraHgCollideFakeDb(returnsFalse: true);
        $this->assertSame([], (new GaleraHostgroupIdCollision())->run($db));
    }

    public function testCountsMultipleOffendingRows(): void
    {
        $db = new GaleraHgCollideFakeDb([
            ['writer_hostgroup' => 1, 'reader_hostgroup' => 1, 'backup_writer_hostgroup' => 4, 'offline_hostgroup' => 5],
            ['writer_hostgroup' => 10, 'reader_hostgroup' => 11, 'backup_writer_hostgroup' => 11, 'offline_hostgroup' => 12],
        ]);
        $f = (new GaleraHostgroupIdCollision())->run($db)[0];
        $this->assertStringContainsString('2 Galera cluster row(s)', $f->title);
    }
}

final class GaleraHgCollideFakeDb
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
