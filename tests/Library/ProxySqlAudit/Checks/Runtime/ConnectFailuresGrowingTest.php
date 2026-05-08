<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Checks\Runtime\ConnectFailuresGrowing;
use App\Library\ProxySqlAudit\Finding;
use PHPUnit\Framework\TestCase;

final class ConnectFailuresGrowingTest extends TestCase
{
    public function testWarnsAboveThreshold(): void
    {
        $db = new ConnFailFakeDb([
            ['hostgroup' => 10, 'srv_host' => '10.0.0.1', 'srv_port' => 3306, 'ConnFAIL' => 200, 'ConnERR' => 7],
        ]);
        $f = (new ConnectFailuresGrowing())->run($db);
        $this->assertCount(1, $f);
        $this->assertSame(Finding::SEVERITY_WARNING, $f[0]->severity);
        $this->assertSame('runtime', $f[0]->category);
        $this->assertSame('runtime.connect-failures-growing', $f[0]->checkId);
        $this->assertNull($f[0]->ticket);
        $this->assertStringContainsString(
            'Backend 10.0.0.1:3306 in HG 10 has ConnFAIL=200 (>100)',
            $f[0]->title
        );
        $this->assertStringContainsString(
            'stats_mysql_connection_pool_reset',
            $f[0]->fix
        );
        $this->assertStringContainsString('ConnERR=7', $f[0]->evidence);
    }

    public function testEmptyOnEmptyResult(): void
    {
        // The WHERE clause excludes rows ≤ threshold; the check trusts
        // the SQL to filter. Empty result → empty findings.
        $db = new ConnFailFakeDb([]);
        $this->assertSame([], (new ConnectFailuresGrowing())->run($db));
    }

    public function testEmptyOnSilentQueryFailure(): void
    {
        $db = new ConnFailFakeDb(returnsFalse: true);
        $this->assertSame([], (new ConnectFailuresGrowing())->run($db));
    }

    public function testWarnsPerOffendingBackend(): void
    {
        $db = new ConnFailFakeDb([
            ['hostgroup' => 10, 'srv_host' => 'db1', 'srv_port' => 3306, 'ConnFAIL' => 150, 'ConnERR' => 0],
            ['hostgroup' => 11, 'srv_host' => 'db2', 'srv_port' => 3306, 'ConnFAIL' => 999, 'ConnERR' => 12],
        ]);
        $f = (new ConnectFailuresGrowing())->run($db);
        $this->assertCount(2, $f);
        $this->assertStringContainsString('ConnFAIL=150', $f[0]->title);
        $this->assertStringContainsString('ConnFAIL=999', $f[1]->title);
    }
}

final class ConnFailFakeDb
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
