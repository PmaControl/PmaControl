<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Checks\Runtime\ErrlogStorm;
use App\Library\ProxySqlAudit\Finding;
use PHPUnit\Framework\TestCase;

final class ErrlogStormTest extends TestCase
{
    public function testWarnsAboveThreshold(): void
    {
        $db = new ErrlogFakeDb([[
            'hostgroup' => 10, 'hostname' => '10.0.0.1', 'port' => 3306,
            'errno' => 1045, 'count_star' => 250, 'last_seen' => 1715000000,
        ]]);
        $f = (new ErrlogStorm())->run($db);
        $this->assertCount(1, $f);
        $this->assertSame(Finding::SEVERITY_WARNING, $f[0]->severity);
        $this->assertSame('runtime', $f[0]->category);
        $this->assertSame('runtime.errlog-storm', $f[0]->checkId);
        $this->assertNull($f[0]->ticket);
        $this->assertStringContainsString(
            'Backend 10.0.0.1:3306 in HG 10 errno=1045 count=250',
            $f[0]->title
        );
        $this->assertStringContainsString('stats_mysql_errors_reset', $f[0]->fix);
        $this->assertStringContainsString('errno=1045', $f[0]->fix);
        $this->assertStringContainsString('last_seen=1715000000', $f[0]->evidence);
    }

    public function testWarnsPerOffendingRow(): void
    {
        $db = new ErrlogFakeDb([
            ['hostgroup' => 10, 'hostname' => 'db1', 'port' => 3306, 'errno' => 1045, 'count_star' => 200, 'last_seen' => 0],
            ['hostgroup' => 10, 'hostname' => 'db1', 'port' => 3306, 'errno' => 1213, 'count_star' => 150, 'last_seen' => 0],
            ['hostgroup' => 11, 'hostname' => 'db2', 'port' => 3306, 'errno' => 2003, 'count_star' => 999, 'last_seen' => 0],
        ]);
        $f = (new ErrlogStorm())->run($db);
        $this->assertCount(3, $f);
    }

    public function testEmptyOnEmptyResult(): void
    {
        $db = new ErrlogFakeDb([]);
        $this->assertSame([], (new ErrlogStorm())->run($db));
    }

    public function testEmptyOnSilentQueryFailureInfoFallback(): void
    {
        // Older ProxySQL: stats_mysql_errors does not exist —
        // sql_query_silent returns false, check returns empty.
        $db = new ErrlogFakeDb(returnsFalse: true);
        $this->assertSame([], (new ErrlogStorm())->run($db));
    }
}

final class ErrlogFakeDb
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
