<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Checks\Hostgroup\WriterCountExceedsMaxWriters;
use App\Library\ProxySqlAudit\Finding;
use PHPUnit\Framework\TestCase;

final class WriterCountExceedsMaxWritersTest extends TestCase
{
    public function testWarningPerOverflowingWriterHostgroup(): void
    {
        $db = new WriterMaxFakeDb([
            ['writer_hostgroup' => 1, 'writers' => 3, 'max_writers' => 1],
            ['writer_hostgroup' => 2, 'writers' => 5, 'max_writers' => 2],
        ]);
        $findings = (new WriterCountExceedsMaxWriters())->run($db);
        $this->assertCount(2, $findings);
        foreach ($findings as $f) {
            $this->assertSame(Finding::SEVERITY_WARNING, $f->severity);
            $this->assertSame('hostgroup', $f->category);
            $this->assertSame('hostgroup.writer-count-exceeds-max-writers', $f->checkId);
            $this->assertNull($f->ticket);
            $this->assertStringContainsString('LOAD MYSQL SERVERS TO RUNTIME', $f->fix);
        }
        $this->assertStringContainsString('hostgroup 1', $findings[0]->title);
        $this->assertStringContainsString('3 ONLINE writers', $findings[0]->title);
        $this->assertStringContainsString('max_writers=1', $findings[0]->title);
    }

    public function testReturnsEmptyWhenAllWriterHostgroupsAreWithinMax(): void
    {
        $db = new WriterMaxFakeDb([]);
        $this->assertSame([], (new WriterCountExceedsMaxWriters())->run($db));
    }

    public function testReturnsEmptyOnSilentQueryFailure(): void
    {
        $db = new WriterMaxFakeDb(returnsFalse: true);
        $this->assertSame([], (new WriterCountExceedsMaxWriters())->run($db));
    }

    public function testEvidenceContainsTheNumericalContext(): void
    {
        $db = new WriterMaxFakeDb([
            ['writer_hostgroup' => 7, 'writers' => 4, 'max_writers' => 3],
        ]);
        $f = (new WriterCountExceedsMaxWriters())->run($db)[0];
        $this->assertStringContainsString('online_writers=4', $f->evidence);
        $this->assertStringContainsString('max_writers=3', $f->evidence);
    }
}

final class WriterMaxFakeDb
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
