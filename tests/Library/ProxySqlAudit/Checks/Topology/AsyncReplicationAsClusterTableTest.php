<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Checks\Topology\AsyncReplicationAsClusterTable;
use App\Library\ProxySqlAudit\Finding;
use PHPUnit\Framework\TestCase;

final class AsyncReplicationAsClusterTableTest extends TestCase
{
    public function testReturnsWarningListingEveryClusterHostgroupBackend(): void
    {
        $db = new AsyncAsClusterFakeDb([
            ['hostname' => 'g1', 'port' => 3306],
            ['hostname' => 'g2', 'port' => 3306],
        ]);
        $findings = (new AsyncReplicationAsClusterTable())->run($db);

        $this->assertCount(1, $findings);
        $f = $findings[0];
        $this->assertSame(Finding::SEVERITY_WARNING, $f->severity);
        $this->assertSame('topology', $f->category);
        $this->assertSame('topology.async-declared-as-cluster', $f->checkId);
        $this->assertNull($f->ticket);
        $this->assertStringContainsString('g1:3306', $f->evidence);
        $this->assertStringContainsString('g2:3306', $f->evidence);
        $this->assertStringContainsString('LOAD MYSQL SERVERS TO RUNTIME', $f->fix);
    }

    public function testReturnsEmptyWhenNoClusterHostgroupBackends(): void
    {
        $db = new AsyncAsClusterFakeDb([]);
        $this->assertSame([], (new AsyncReplicationAsClusterTable())->run($db));
    }

    public function testReturnsEmptyOnSilentQueryFailure(): void
    {
        $db = new AsyncAsClusterFakeDb(returnsFalse: true);
        $this->assertSame([], (new AsyncReplicationAsClusterTable())->run($db));
    }

    public function testRowsWithMissingHostnameOrZeroPortAreSkipped(): void
    {
        $db = new AsyncAsClusterFakeDb([
            ['hostname' => '',   'port' => 3306],
            ['hostname' => 'g',  'port' => 0   ],
            ['hostname' => 'ok', 'port' => 3306],
        ]);
        $f = (new AsyncReplicationAsClusterTable())->run($db)[0];
        $this->assertStringContainsString('ok:3306', $f->evidence);
        $this->assertStringNotContainsString(':0', $f->evidence);
    }
}

final class AsyncAsClusterFakeDb
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
        return 'mock-result';
    }

    public function sql_fetch_array($res, int $mode = MYSQLI_ASSOC)
    {
        if ($this->cursor >= count($this->rows)) return null;
        return $this->rows[$this->cursor++];
    }
}
