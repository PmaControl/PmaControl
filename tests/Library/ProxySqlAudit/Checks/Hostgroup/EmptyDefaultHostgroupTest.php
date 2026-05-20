<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Checks\Hostgroup\EmptyDefaultHostgroup;
use App\Library\ProxySqlAudit\Finding;
use PHPUnit\Framework\TestCase;

final class EmptyDefaultHostgroupTest extends TestCase
{
    public function testCriticalPerOffendingUserWithIncidentReference(): void
    {
        $db = new EmptyDefaultHgFakeDb([
            ['username' => 'pmacontrol', 'default_hostgroup' => 5, 'servers' => 0],
            ['username' => 'app',         'default_hostgroup' => 0, 'servers' => 0],
        ]);

        $findings = (new EmptyDefaultHostgroup())->run($db);

        $this->assertCount(2, $findings);
        foreach ($findings as $f) {
            $this->assertSame(Finding::SEVERITY_CRITICAL, $f->severity);
            $this->assertSame('hostgroup', $f->category);
            $this->assertSame('hostgroup.empty-default-hostgroup', $f->checkId);
            $this->assertSame('#148', $f->ticket);
            $this->assertStringContainsString('LOAD MYSQL USERS TO RUNTIME', $f->fix);
        }
        $this->assertStringContainsString("'pmacontrol'", $findings[0]->title);
        $this->assertStringContainsString('hostgroup 5',  $findings[0]->title);
        $this->assertStringContainsString("'app'",        $findings[1]->title);
        $this->assertStringContainsString('hostgroup 0',  $findings[1]->title);
    }

    public function testReturnsEmptyWhenAllUsersRouteToPopulatedHostgroups(): void
    {
        $db = new EmptyDefaultHgFakeDb([]);
        $this->assertSame([], (new EmptyDefaultHostgroup())->run($db));
    }

    public function testReturnsEmptyOnSilentQueryFailure(): void
    {
        $db = new EmptyDefaultHgFakeDb(returnsFalse: true);
        $this->assertSame([], (new EmptyDefaultHostgroup())->run($db));
    }

    public function testEvidenceContainsTheUsernameAndHostgroupId(): void
    {
        $db = new EmptyDefaultHgFakeDb([
            ['username' => 'shard_writer', 'default_hostgroup' => 42, 'servers' => 0],
        ]);
        $f = (new EmptyDefaultHostgroup())->run($db)[0];
        $this->assertStringContainsString("user='shard_writer'",       $f->evidence);
        $this->assertStringContainsString('default_hostgroup=42',      $f->evidence);
        $this->assertStringContainsString("WHERE username='shard_writer'", $f->fix);
    }
}

final class EmptyDefaultHgFakeDb
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
