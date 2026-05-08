<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Checks\Runtime\ZombieSessions;
use App\Library\ProxySqlAudit\Finding;
use PHPUnit\Framework\TestCase;

final class ZombieSessionsTest extends TestCase
{
    public function testWarnsOnLongRunningStatement(): void
    {
        $db = new ZombieFakeDb([[
            'SessionID' => 42, 'hostgroup' => 10, 'ThreadID' => 99,
            'ConnectionID' => 1, 'db' => 'app', 'user' => 'svc',
            'l_username' => 'svc', 'l_srv_host' => '10.0.0.1',
            'hostname' => '10.0.0.1', 'l_srv_port' => 3306, 'port' => 3306,
            'command' => 'Query', 'time_ms' => 720000,
            'info' => 'SELECT * FROM big_table',
        ]]);
        $f = (new ZombieSessions())->run($db);
        $this->assertCount(1, $f);
        $this->assertSame(Finding::SEVERITY_WARNING, $f[0]->severity);
        $this->assertSame('runtime', $f[0]->category);
        $this->assertSame('runtime.zombie-sessions', $f[0]->checkId);
        $this->assertSame('#148', $f[0]->ticket);
        $this->assertStringContainsString(
            'Zombie session SessionID=42 on 10.0.0.1:3306 (Query for 720s)',
            $f[0]->title
        );
        $this->assertStringContainsString('KILL SESSION 42', $f[0]->fix);
        $this->assertStringContainsString('KILL 99', $f[0]->fix);
        $this->assertStringContainsString('SELECT * FROM big_table', $f[0]->evidence);
    }

    public function testWarnsOnLongSleep(): void
    {
        $db = new ZombieFakeDb([[
            'SessionID' => 7, 'hostgroup' => 11, 'ThreadID' => 12,
            'ConnectionID' => 2, 'db' => 'app', 'user' => 'svc',
            'l_username' => 'svc', 'l_srv_host' => 'db1',
            'hostname' => 'db1', 'l_srv_port' => 3306, 'port' => 3306,
            'command' => 'Sleep', 'time_ms' => 480000,
            'info' => null,
        ]]);
        $f = (new ZombieSessions())->run($db)[0];
        $this->assertStringContainsString('(Sleep for 480s)', $f->title);
        $this->assertStringContainsString('KILL SESSION 7', $f->fix);
    }

    public function testEmptyWhenNoZombies(): void
    {
        $db = new ZombieFakeDb([]);
        $this->assertSame([], (new ZombieSessions())->run($db));
    }

    public function testEmptyOnSilentQueryFailure(): void
    {
        $db = new ZombieFakeDb(returnsFalse: true);
        $this->assertSame([], (new ZombieSessions())->run($db));
    }
}

final class ZombieFakeDb
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
