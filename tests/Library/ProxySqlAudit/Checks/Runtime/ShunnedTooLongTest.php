<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Checks\Runtime\ShunnedTooLong;
use App\Library\ProxySqlAudit\Finding;
use PHPUnit\Framework\TestCase;

final class ShunnedTooLongTest extends TestCase
{
    public function testWarnsOnShunned(): void
    {
        $db = new ShunnedFakeDb([
            ['hostgroup_id' => 10, 'hostname' => '10.0.0.1', 'port' => 3306, 'status' => 'SHUNNED'],
        ]);
        $f = (new ShunnedTooLong())->run($db);
        $this->assertCount(1, $f);
        $this->assertSame(Finding::SEVERITY_WARNING, $f[0]->severity);
        $this->assertSame('runtime', $f[0]->category);
        $this->assertSame('runtime.shunned-too-long', $f[0]->checkId);
        $this->assertNull($f[0]->ticket);
        $this->assertStringContainsString('10.0.0.1:3306 in HG 10 is SHUNNED', $f[0]->title);
        $this->assertStringContainsString("WHERE hostname='10.0.0.1' AND port=3306 AND hostgroup_id=10", $f[0]->fix);
        $this->assertStringContainsString('LOAD MYSQL SERVERS TO RUNTIME', $f[0]->fix);
    }

    public function testWarnsOnShunnedReplicationLag(): void
    {
        $db = new ShunnedFakeDb([
            ['hostgroup_id' => 11, 'hostname' => 'db-replica', 'port' => 3306, 'status' => 'SHUNNED_REPLICATION_LAG'],
        ]);
        $f = (new ShunnedTooLong())->run($db)[0];
        $this->assertStringContainsString('SHUNNED_REPLICATION_LAG', $f->title);
        $this->assertStringContainsString('db-replica:3306 in HG 11', $f->title);
    }

    public function testEmptyWhenNoShunned(): void
    {
        $db = new ShunnedFakeDb([]);
        $this->assertSame([], (new ShunnedTooLong())->run($db));
    }

    public function testEmptyOnSilentQueryFailure(): void
    {
        $db = new ShunnedFakeDb(returnsFalse: true);
        $this->assertSame([], (new ShunnedTooLong())->run($db));
    }
}

final class ShunnedFakeDb
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
