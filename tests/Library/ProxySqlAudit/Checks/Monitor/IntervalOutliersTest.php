<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Checks\Monitor\IntervalOutliers;
use App\Library\ProxySqlAudit\Finding;
use PHPUnit\Framework\TestCase;

final class IntervalOutliersTest extends TestCase
{
    public function testWarningPerOffendingVariable(): void
    {
        $db = new IntervalOutliersFakeDb([
            ['variable_name' => 'mysql-monitor_galera_healthcheck_interval', 'variable_value' => '50'],     // too low
            ['variable_name' => 'mysql-monitor_replication_lag_interval',     'variable_value' => '60000'], // too high
            ['variable_name' => 'mysql-monitor_ping_interval_server_msec',    'variable_value' => '1000'],  // ok
        ]);
        $findings = (new IntervalOutliers())->run($db);
        $this->assertCount(2, $findings);

        $titles = array_map(static fn ($f) => $f->title, $findings);
        $titlesJoined = implode(' | ', $titles);
        $this->assertStringContainsString('galera_healthcheck_interval = 50 ms',     $titlesJoined);
        $this->assertStringContainsString('replication_lag_interval = 60000 ms',     $titlesJoined);

        foreach ($findings as $f) {
            $this->assertSame(Finding::SEVERITY_WARNING, $f->severity);
            $this->assertSame('monitor', $f->category);
            $this->assertSame('monitor.interval-outliers', $f->checkId);
            $this->assertNull($f->ticket);
            $this->assertStringContainsString('LOAD MYSQL VARIABLES TO RUNTIME', $f->fix);
        }
    }

    public function testEmptyWhenAllValuesInsideRange(): void
    {
        $db = new IntervalOutliersFakeDb([
            ['variable_name' => 'mysql-monitor_galera_healthcheck_interval', 'variable_value' => '500'],
            ['variable_name' => 'mysql-monitor_ping_interval_server_msec',    'variable_value' => '20000'],
        ]);
        $this->assertSame([], (new IntervalOutliers())->run($db));
    }

    public function testNonNumericValuesAreSkipped(): void
    {
        $db = new IntervalOutliersFakeDb([
            ['variable_name' => 'mysql-monitor_galera_healthcheck_interval', 'variable_value' => 'OFF'],
            ['variable_name' => 'mysql-monitor_replication_lag_interval',     'variable_value' => ''],
        ]);
        $this->assertSame([], (new IntervalOutliers())->run($db));
    }

    public function testEmptyOnSilentQueryFailure(): void
    {
        $db = new IntervalOutliersFakeDb(returnsFalse: true);
        $this->assertSame([], (new IntervalOutliers())->run($db));
    }
}

final class IntervalOutliersFakeDb
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
