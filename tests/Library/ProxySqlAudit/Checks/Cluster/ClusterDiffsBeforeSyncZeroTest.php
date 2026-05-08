<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Checks\Cluster\ClusterDiffsBeforeSyncZero;
use App\Library\ProxySqlAudit\Finding;
use PHPUnit\Framework\TestCase;

final class ClusterDiffsBeforeSyncZeroTest extends TestCase
{
    public function testWarnsOnSingleOffender(): void
    {
        $db = new DiffsBeforeSyncFakeDb([
            ['variable_name' => 'admin-cluster_mysql_servers_diffs_before_sync', 'variable_value' => '0'],
        ]);
        $f = (new ClusterDiffsBeforeSyncZero())->run($db);
        $this->assertCount(1, $f);
        $this->assertSame(Finding::SEVERITY_WARNING, $f[0]->severity);
        $this->assertSame('cluster', $f[0]->category);
        $this->assertSame('cluster.diffs-before-sync-zero', $f[0]->checkId);
        $this->assertNull($f[0]->ticket);
        $this->assertStringContainsString(
            'admin-cluster_mysql_servers_diffs_before_sync=0',
            $f[0]->title
        );
        $this->assertStringContainsString(
            "WHERE variable_name='admin-cluster_mysql_servers_diffs_before_sync'",
            $f[0]->fix
        );
        $this->assertStringContainsString('LOAD ADMIN VARIABLES TO RUNTIME', $f[0]->fix);
    }

    public function testWarnsPerOffendingVariable(): void
    {
        $db = new DiffsBeforeSyncFakeDb([
            ['variable_name' => 'admin-cluster_mysql_servers_diffs_before_sync',     'variable_value' => '0'],
            ['variable_name' => 'admin-cluster_mysql_users_diffs_before_sync',       'variable_value' => '0'],
            ['variable_name' => 'admin-cluster_mysql_query_rules_diffs_before_sync', 'variable_value' => '0'],
        ]);
        $f = (new ClusterDiffsBeforeSyncZero())->run($db);
        $this->assertCount(3, $f);
    }

    public function testEmptyWhenAllNonZero(): void
    {
        $db = new DiffsBeforeSyncFakeDb([]);
        $this->assertSame([], (new ClusterDiffsBeforeSyncZero())->run($db));
    }

    public function testEmptyOnSilentQueryFailure(): void
    {
        $db = new DiffsBeforeSyncFakeDb(returnsFalse: true);
        $this->assertSame([], (new ClusterDiffsBeforeSyncZero())->run($db));
    }
}

final class DiffsBeforeSyncFakeDb
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
