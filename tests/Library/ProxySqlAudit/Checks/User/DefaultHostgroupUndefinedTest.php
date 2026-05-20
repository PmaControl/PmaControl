<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Checks\User\DefaultHostgroupUndefined;
use App\Library\ProxySqlAudit\Finding;
use PHPUnit\Framework\TestCase;

final class DefaultHostgroupUndefinedTest extends TestCase
{
    public function testCriticalPerOffendingUser(): void
    {
        $db = new DefaultHgUndefFakeDb([
            ['username' => 'a', 'default_hostgroup' => 999],
            ['username' => 'b', 'default_hostgroup' => 1234],
        ]);
        $findings = (new DefaultHostgroupUndefined())->run($db);
        $this->assertCount(2, $findings);
        foreach ($findings as $f) {
            $this->assertSame(Finding::SEVERITY_CRITICAL, $f->severity);
            $this->assertSame('user', $f->category);
            $this->assertSame('user.default-hostgroup-undefined', $f->checkId);
            $this->assertNull($f->ticket);
            $this->assertStringContainsString('LOAD MYSQL USERS TO RUNTIME', $f->fix);
        }
        $this->assertStringContainsString("'a'", $findings[0]->title);
        $this->assertStringContainsString('default_hostgroup=999', $findings[0]->title);
        $this->assertStringContainsString("'b'", $findings[1]->title);
        $this->assertStringContainsString('default_hostgroup=1234', $findings[1]->title);
    }

    public function testEmptyWhenEveryDefaultHostgroupIsDeclared(): void
    {
        $db = new DefaultHgUndefFakeDb([]);
        $this->assertSame([], (new DefaultHostgroupUndefined())->run($db));
    }

    public function testEmptyOnSilentQueryFailure(): void
    {
        $db = new DefaultHgUndefFakeDb(returnsFalse: true);
        $this->assertSame([], (new DefaultHostgroupUndefined())->run($db));
    }

    public function testFixContainsTheUsernameAndHostgroupId(): void
    {
        $db = new DefaultHgUndefFakeDb([
            ['username' => 'shard_app', 'default_hostgroup' => 77],
        ]);
        $f = (new DefaultHostgroupUndefined())->run($db)[0];
        $this->assertStringContainsString("WHERE username='shard_app'", $f->fix);
        $this->assertStringContainsString('hostgroup 77', $f->fix);
    }
}

final class DefaultHgUndefFakeDb
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
