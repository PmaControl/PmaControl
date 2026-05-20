<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Checks\Monitor\PtHeartbeatMisconfig;
use App\Library\ProxySqlAudit\Finding;
use PHPUnit\Framework\TestCase;

final class PtHeartbeatMisconfigTest extends TestCase
{
    public function testInfoWhenPtHeartbeatEnabledWithATable(): void
    {
        $db = new PtHeartbeatFakeDb([
            ['variable_name' => 'mysql-monitor_replication_lag_use_percona_heartbeat', 'variable_value' => 'true'],
            ['variable_name' => 'mysql-monitor_replication_lag_table',                  'variable_value' => 'percona.heartbeat'],
        ]);
        $f = (new PtHeartbeatMisconfig())->run($db)[0];
        $this->assertSame(Finding::SEVERITY_INFO, $f->severity);
        $this->assertSame('monitor', $f->category);
        $this->assertSame('monitor.pt-heartbeat-misconfig', $f->checkId);
        $this->assertNull($f->ticket);
        $this->assertStringContainsString("'percona.heartbeat'", $f->title);
        $this->assertStringContainsString("'percona.heartbeat'", $f->evidence);
        $this->assertStringContainsString('SELECT 1 FROM percona.heartbeat', $f->fix);
        $this->assertStringContainsString('pt-heartbeat --update', $f->fix);
    }

    public function testEmptyWhenPtHeartbeatDisabled(): void
    {
        $db = new PtHeartbeatFakeDb([
            ['variable_name' => 'mysql-monitor_replication_lag_use_percona_heartbeat', 'variable_value' => 'false'],
            ['variable_name' => 'mysql-monitor_replication_lag_table',                  'variable_value' => 'percona.heartbeat'],
        ]);
        $this->assertSame([], (new PtHeartbeatMisconfig())->run($db));
    }

    public function testEmptyWhenEnabledButNoTableConfigured(): void
    {
        $db = new PtHeartbeatFakeDb([
            ['variable_name' => 'mysql-monitor_replication_lag_use_percona_heartbeat', 'variable_value' => 'true'],
            ['variable_name' => 'mysql-monitor_replication_lag_table',                  'variable_value' => ''],
        ]);
        $this->assertSame([], (new PtHeartbeatMisconfig())->run($db));
    }

    public function testEmptyOnSilentQueryFailure(): void
    {
        $db = new PtHeartbeatFakeDb(returnsFalse: true);
        $this->assertSame([], (new PtHeartbeatMisconfig())->run($db));
    }

    public function testRecognisesAllTruthyAliases(): void
    {
        foreach (['true', '1', 'ON', 'yes'] as $truthy) {
            $db = new PtHeartbeatFakeDb([
                ['variable_name' => 'mysql-monitor_replication_lag_use_percona_heartbeat', 'variable_value' => $truthy],
                ['variable_name' => 'mysql-monitor_replication_lag_table',                  'variable_value' => 'p.heartbeat'],
            ]);
            $this->assertCount(1, (new PtHeartbeatMisconfig())->run($db), "value=$truthy");
        }
    }
}

final class PtHeartbeatFakeDb
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
