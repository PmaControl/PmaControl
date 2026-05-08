<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Checks\Hostgroup\OrphanHostgroup;
use App\Library\ProxySqlAudit\Finding;
use PHPUnit\Framework\TestCase;

final class OrphanHostgroupTest extends TestCase
{
    public function testWarningWhenOrphanHostgroupsExist(): void
    {
        $db = new OrphanHgFakeDb([
            ['hg' => 5, 'source' => 'galera_offline'],
            ['hg' => 99, 'source' => 'group_replication'],
        ]);
        $f = (new OrphanHostgroup())->run($db)[0];
        $this->assertSame(Finding::SEVERITY_WARNING, $f->severity);
        $this->assertSame('hostgroup', $f->category);
        $this->assertSame('hostgroup.orphan-hostgroup', $f->checkId);
        $this->assertNull($f->ticket);
        $this->assertStringContainsString('hostgroup=5 declared by galera_offline', $f->evidence);
        $this->assertStringContainsString('hostgroup=99 declared by group_replication', $f->evidence);
        $this->assertStringContainsString('LOAD MYSQL SERVERS TO RUNTIME', $f->fix);
    }

    public function testReturnsEmptyWhenEveryDeclaredHostgroupIsPopulated(): void
    {
        $db = new OrphanHgFakeDb([]);
        $this->assertSame([], (new OrphanHostgroup())->run($db));
    }

    public function testReturnsEmptyOnSilentQueryFailure(): void
    {
        $db = new OrphanHgFakeDb(returnsFalse: true);
        $this->assertSame([], (new OrphanHostgroup())->run($db));
    }

    public function testTitleCountsOffenders(): void
    {
        $db = new OrphanHgFakeDb([
            ['hg' => 1, 'source' => 'galera_writer'],
            ['hg' => 2, 'source' => 'galera_reader'],
            ['hg' => 3, 'source' => 'replication_writer'],
        ]);
        $f = (new OrphanHostgroup())->run($db)[0];
        $this->assertStringContainsString('3 orphan hostgroup(s)', $f->title);
    }
}

final class OrphanHgFakeDb
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
