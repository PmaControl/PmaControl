<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Checks\User\MonitorUserMissingReplicationGrants;
use App\Library\ProxySqlAudit\Finding;
use PHPUnit\Framework\TestCase;

final class MonitorUserMissingReplicationGrantsTest extends TestCase
{
    public function testInfoFindingListsMonitorUserAndBackendsWithGrantRecommendation(): void
    {
        $db = new MonitorGrantsFakeDb(
            ['variable_value' => 'monitor'],
            [
                ['hostname' => '10.0.0.1', 'port' => 3306],
                ['hostname' => '10.0.0.2', 'port' => 3306],
            ]
        );
        $f = (new MonitorUserMissingReplicationGrants())->run($db)[0];
        $this->assertSame(Finding::SEVERITY_INFO, $f->severity);
        $this->assertSame('user', $f->category);
        $this->assertSame('user.monitor-user-missing-replication-grants', $f->checkId);
        $this->assertNull($f->ticket);
        $this->assertStringContainsString("'monitor'", $f->title);
        $this->assertStringContainsString('REPLICATION CLIENT', $f->title);
        $this->assertStringContainsString('10.0.0.1:3306', $f->evidence);
        $this->assertStringContainsString('10.0.0.2:3306', $f->evidence);
        $this->assertStringContainsString("SHOW GRANTS FOR 'monitor'", $f->fix);
        $this->assertStringContainsString('GRANT REPLICATION CLIENT, BINLOG MONITOR, REPLICA MONITOR', $f->fix);
    }

    public function testReturnsEmptyWhenMonitorUsernameUnset(): void
    {
        $db = new MonitorGrantsFakeDb(['variable_value' => '']);
        $this->assertSame([], (new MonitorUserMissingReplicationGrants())->run($db));
    }

    public function testReturnsEmptyWhenNoBackends(): void
    {
        $db = new MonitorGrantsFakeDb(['variable_value' => 'monitor'], []);
        $this->assertSame([], (new MonitorUserMissingReplicationGrants())->run($db));
    }

    public function testReturnsEmptyOnSilentQueryFailure(): void
    {
        $db = new MonitorGrantsFakeDb(returnsFalse: true);
        $this->assertSame([], (new MonitorUserMissingReplicationGrants())->run($db));
    }
}

final class MonitorGrantsFakeDb
{
    private ?array $varRow;
    private array $backendRows;
    private bool $returnsFalse;
    private int $cursor = 0;
    private int $stage = 0;

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
