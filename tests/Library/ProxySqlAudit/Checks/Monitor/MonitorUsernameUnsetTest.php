<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Checks\Monitor\MonitorUsernameUnset;
use App\Library\ProxySqlAudit\Finding;
use PHPUnit\Framework\TestCase;

final class MonitorUsernameUnsetTest extends TestCase
{
    public function testCriticalWhenMysqlMonitorUsernameIsEmpty(): void
    {
        $db = new MonitorUsernameUnsetFakeDb(['variable_value' => '']);
        $f = (new MonitorUsernameUnset())->run($db)[0];
        $this->assertSame(Finding::SEVERITY_CRITICAL, $f->severity);
        $this->assertSame('monitor', $f->category);
        $this->assertSame('monitor.monitor-username-unset', $f->checkId);
        $this->assertNull($f->ticket);
        $this->assertStringContainsString('monitoring effectively disabled', $f->title);
        $this->assertStringContainsString('LOAD MYSQL VARIABLES TO RUNTIME', $f->fix);
        $this->assertStringContainsString('SAVE MYSQL VARIABLES TO DISK', $f->fix);
    }

    public function testCriticalWhenMysqlMonitorUsernameIsWhitespaceOnly(): void
    {
        $db = new MonitorUsernameUnsetFakeDb(['variable_value' => "   \t  "]);
        $this->assertCount(1, (new MonitorUsernameUnset())->run($db));
    }

    public function testEmptyWhenMysqlMonitorUsernameIsSet(): void
    {
        $db = new MonitorUsernameUnsetFakeDb(['variable_value' => 'monitor']);
        $this->assertSame([], (new MonitorUsernameUnset())->run($db));
    }

    public function testEmptyOnSilentQueryFailure(): void
    {
        $db = new MonitorUsernameUnsetFakeDb(returnsFalse: true);
        $this->assertSame([], (new MonitorUsernameUnset())->run($db));
    }
}

final class MonitorUsernameUnsetFakeDb
{
    private ?array $row;
    private bool $returnsFalse;
    private int $cursor = 0;
    public function __construct(?array $row = null, bool $returnsFalse = false)
    {
        $this->row = $row;
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
        if ($this->cursor++ === 0 && $this->row !== null) return $this->row;
        return null;
    }
}
