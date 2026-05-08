<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Checks\Topology\BackendInMultipleClusterTypes;
use App\Library\ProxySqlAudit\Finding;
use PHPUnit\Framework\TestCase;

final class BackendInMultipleClusterTypesTest extends TestCase
{
    public function testCriticalWhenBackendShowsUpInMoreThanOneClusterType(): void
    {
        $db = new MultiClusterFakeDb([
            ['hostname' => 'g1', 'port' => 3306, 'in_galera' => 4, 'in_gr' => 1, 'in_repl' => 0],
        ]);
        $f = (new BackendInMultipleClusterTypes())->run($db)[0];
        $this->assertSame(Finding::SEVERITY_CRITICAL, $f->severity);
        $this->assertSame('topology', $f->category);
        $this->assertSame('topology.backend-in-multiple-cluster-types', $f->checkId);
        $this->assertNull($f->ticket);
        $this->assertStringContainsString('g1:3306', $f->evidence);
        $this->assertStringContainsString('Galera', $f->evidence);
        $this->assertStringContainsString('GroupReplication', $f->evidence);
        $this->assertStringContainsString('LOAD MYSQL SERVERS TO RUNTIME', $f->fix);
    }

    public function testReturnsEmptyWhenEveryBackendIsInExactlyOneClusterType(): void
    {
        $db = new MultiClusterFakeDb([]);
        $this->assertSame([], (new BackendInMultipleClusterTypes())->run($db));
    }

    public function testReturnsEmptyOnSilentQueryFailure(): void
    {
        $db = new MultiClusterFakeDb(returnsFalse: true);
        $this->assertSame([], (new BackendInMultipleClusterTypes())->run($db));
    }

    public function testCountsMultipleOffenders(): void
    {
        $db = new MultiClusterFakeDb([
            ['hostname' => 'a', 'port' => 3306, 'in_galera' => 1, 'in_gr' => 1, 'in_repl' => 0],
            ['hostname' => 'b', 'port' => 3306, 'in_galera' => 0, 'in_gr' => 1, 'in_repl' => 1],
        ]);
        $f = (new BackendInMultipleClusterTypes())->run($db)[0];
        $this->assertStringContainsString('2 backend(s)', $f->title);
    }
}

final class MultiClusterFakeDb
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
