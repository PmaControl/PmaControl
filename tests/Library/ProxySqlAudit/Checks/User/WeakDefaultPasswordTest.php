<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Checks\User\WeakDefaultPassword;
use App\Library\ProxySqlAudit\Finding;
use PHPUnit\Framework\TestCase;

final class WeakDefaultPasswordTest extends TestCase
{
    public function testWarningPerOffendingUser(): void
    {
        $db = new WeakPwdFakeDb([
            ['username' => 'a'],
            ['username' => 'b'],
        ]);
        $findings = (new WeakDefaultPassword())->run($db);
        $this->assertCount(2, $findings);
        foreach ($findings as $f) {
            $this->assertSame(Finding::SEVERITY_WARNING, $f->severity);
            $this->assertSame('user', $f->category);
            $this->assertSame('user.weak-default-password', $f->checkId);
            $this->assertNull($f->ticket);
            $this->assertStringContainsString('LOAD MYSQL USERS TO RUNTIME', $f->fix);
        }
        $this->assertStringContainsString("'a'", $findings[0]->title);
        $this->assertStringContainsString("'b'", $findings[1]->title);
    }

    public function testEmptyWhenAllPasswordsAreNonDefault(): void
    {
        $db = new WeakPwdFakeDb([]);
        $this->assertSame([], (new WeakDefaultPassword())->run($db));
    }

    public function testEmptyOnSilentQueryFailure(): void
    {
        $db = new WeakPwdFakeDb(returnsFalse: true);
        $this->assertSame([], (new WeakDefaultPassword())->run($db));
    }

    public function testEvidenceContainsTheUsername(): void
    {
        $db = new WeakPwdFakeDb([['username' => 'pmacontrol']]);
        $f = (new WeakDefaultPassword())->run($db)[0];
        $this->assertStringContainsString("user='pmacontrol'", $f->evidence);
        $this->assertStringContainsString("WHERE username='pmacontrol'", $f->fix);
    }
}

final class WeakPwdFakeDb
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
