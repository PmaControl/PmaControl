<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Checks\Cluster\ProxySqlServersDuplicate;
use App\Library\ProxySqlAudit\Finding;
use PHPUnit\Framework\TestCase;

final class ProxySqlServersDuplicateTest extends TestCase
{
    public function testWarningPerDuplicatePair(): void
    {
        $db = new ProxySrvDupFakeDb([
            ['hostname' => '10.0.0.1', 'port' => 6032, 'n' => 2],
            ['hostname' => '10.0.0.2', 'port' => 6032, 'n' => 3],
        ]);
        $findings = (new ProxySqlServersDuplicate())->run($db);
        $this->assertCount(2, $findings);
        foreach ($findings as $f) {
            $this->assertSame(Finding::SEVERITY_WARNING, $f->severity);
            $this->assertSame('cluster', $f->category);
            $this->assertSame('cluster.proxysql-servers-duplicate', $f->checkId);
            $this->assertNull($f->ticket);
            $this->assertStringContainsString('LOAD PROXYSQL SERVERS TO RUNTIME', $f->fix);
        }
        $this->assertStringContainsString('2 rows for 10.0.0.1:6032', $findings[0]->title);
        $this->assertStringContainsString('3 rows for 10.0.0.2:6032', $findings[1]->title);
    }

    public function testFixCarriesHostnameAndPort(): void
    {
        $db = new ProxySrvDupFakeDb([
            ['hostname' => 'px1.example', 'port' => 6032, 'n' => 2],
        ]);
        $f = (new ProxySqlServersDuplicate())->run($db)[0];
        $this->assertStringContainsString("hostname='px1.example'", $f->fix);
        $this->assertStringContainsString('port=6032', $f->fix);
        $this->assertStringContainsString("VALUES ('px1.example', 6032, 0, '')", $f->fix);
    }

    public function testEmptyWhenNoDuplicates(): void
    {
        $db = new ProxySrvDupFakeDb([]);
        $this->assertSame([], (new ProxySqlServersDuplicate())->run($db));
    }

    public function testEmptyOnSilentQueryFailure(): void
    {
        $db = new ProxySrvDupFakeDb(returnsFalse: true);
        $this->assertSame([], (new ProxySqlServersDuplicate())->run($db));
    }
}

final class ProxySrvDupFakeDb
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
