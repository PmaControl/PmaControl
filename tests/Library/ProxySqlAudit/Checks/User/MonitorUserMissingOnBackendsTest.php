<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Checks\User\MonitorUserMissingOnBackends;
use App\Library\ProxySqlAudit\Finding;
use PHPUnit\Framework\TestCase;

final class MonitorUserMissingOnBackendsTest extends TestCase
{
    public function testInfoFindingListsMonitorUserAndBackends(): void
    {
        $db = new MonitorUserFakeDb(
            ['variable_value' => 'monitor'],
            [
                ['hostname' => '10.0.0.1', 'port' => 3306],
                ['hostname' => '10.0.0.2', 'port' => 3306],
            ]
        );
        $f = (new MonitorUserMissingOnBackends())->run($db)[0];
        $this->assertSame(Finding::SEVERITY_INFO, $f->severity);
        $this->assertSame('user', $f->category);
        $this->assertSame('user.monitor-user-missing-on-backends', $f->checkId);
        $this->assertNull($f->ticket);
        $this->assertStringContainsString("'monitor'", $f->title);
        $this->assertStringContainsString('10.0.0.1:3306', $f->evidence);
        $this->assertStringContainsString('10.0.0.2:3306', $f->evidence);
        $this->assertStringContainsString("WHERE user='monitor'", $f->fix);
        $this->assertStringContainsString('GRANT REPLICATION CLIENT', $f->fix);
    }

    public function testReturnsEmptyWhenMonitorUsernameUnset(): void
    {
        // #916 owns the unset case; #912 must not double-report.
        $db = new MonitorUserFakeDb(['variable_value' => '']);
        $this->assertSame([], (new MonitorUserMissingOnBackends())->run($db));
    }

    public function testReturnsEmptyWhenNoBackends(): void
    {
        $db = new MonitorUserFakeDb(['variable_value' => 'monitor'], []);
        $this->assertSame([], (new MonitorUserMissingOnBackends())->run($db));
    }

    public function testReturnsEmptyOnSilentQueryFailure(): void
    {
        $db = new MonitorUserFakeDb(returnsFalse: true);
        $this->assertSame([], (new MonitorUserMissingOnBackends())->run($db));
    }
}

final class MonitorUserFakeDb
{
    private ?array $varRow;
    private array $backendRows;
    private bool $returnsFalse;
    private int $cursor = 0;
    private int $stage = 0; // 0=variable, 1=backends

    public function __construct(?array $varRow = null, array $backendRows = [], bool $returnsFalse = false)
    {
        $this->varRow = $varRow;
        $this->backendRows = $backendRows;
        $this->returnsFalse = $returnsFalse;
    }

    public function sql_query_silent(string $sql)
    {
        if ($this->returnsFalse) return false;
        $this->cursor = 0;
        $this->stage = stripos($sql, 'runtime_global_variables') !== false ? 0 : 1;
        return 'mock';
    }

    public function sql_fetch_array($res, int $mode = MYSQLI_ASSOC)
    {
        if ($this->stage === 0) {
            if ($this->cursor++ === 0 && $this->varRow !== null) return $this->varRow;
            return null;
        }
        if ($this->cursor >= count($this->backendRows)) return null;
        return $this->backendRows[$this->cursor++];
    }
}
