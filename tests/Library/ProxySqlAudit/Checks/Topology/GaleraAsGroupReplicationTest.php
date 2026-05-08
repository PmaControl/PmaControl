<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Checks\Topology\GaleraAsGroupReplication;
use App\Library\ProxySqlAudit\Finding;
use PHPUnit\Framework\TestCase;

final class GaleraAsGroupReplicationTest extends TestCase
{
    public function testReturnsCriticalFindingWhenBackendIsDeclaredInBothClusterTypes(): void
    {
        $db = new GaleraAsGrFakeDb([
            ['hostgroup_id' => 10, 'hostname' => '10.0.0.1', 'port' => 3306],
            ['hostgroup_id' => 20, 'hostname' => '10.0.0.2', 'port' => 3306],
        ]);

        $findings = (new GaleraAsGroupReplication())->run($db);

        $this->assertCount(1, $findings);
        $f = $findings[0];
        $this->assertSame(Finding::SEVERITY_CRITICAL, $f->severity);
        $this->assertSame('topology', $f->category);
        $this->assertSame('topology.galera-declared-as-gr', $f->checkId);
        $this->assertSame('#107', $f->ticket);
        $this->assertStringContainsString('2 backend(s)', $f->title);
        $this->assertStringContainsString('hostgroup=10', $f->evidence);
        $this->assertStringContainsString('hostgroup=20', $f->evidence);
        $this->assertNotSame('', $f->fix);
    }

    public function testReturnsEmptyListWhenNoOverlap(): void
    {
        $db = new GaleraAsGrFakeDb([]);
        $this->assertSame([], (new GaleraAsGroupReplication())->run($db));
    }

    public function testReturnsEmptyListWhenSilentQueryFails(): void
    {
        // The runtime tables may be missing on older ProxySQL builds; the
        // silent query then returns false and the check must stay quiet.
        $db = new GaleraAsGrFakeDb(returnsFalse: true);
        $this->assertSame([], (new GaleraAsGroupReplication())->run($db));
    }

    public function testEmitsTheExpectedSqlIntoTheEvidence(): void
    {
        // The fix recommendation hinges on the operator copy-pasting the
        // SQL — pin the FROM/EXISTS shape so a refactor doesn't quietly
        // shift the recommendation under their feet.
        $db = new GaleraAsGrFakeDb([
            ['hostgroup_id' => 5, 'hostname' => 'h', 'port' => 3306],
        ]);
        $f = (new GaleraAsGroupReplication())->run($db)[0];
        $this->assertStringContainsString('runtime_mysql_servers', $f->evidence);
        $this->assertStringContainsString('runtime_mysql_group_replication_hostgroups', $f->evidence);
        $this->assertStringContainsString('runtime_mysql_galera_hostgroups', $f->evidence);
    }
}

final class GaleraAsGrFakeDb
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
        if ($this->returnsFalse) {
            return false;
        }
        $this->cursor = 0;
        return 'mock-result';
    }

    public function sql_fetch_array($res, int $mode = MYSQLI_ASSOC)
    {
        if ($this->cursor >= count($this->rows)) {
            return null;
        }
        return $this->rows[$this->cursor++];
    }
}
