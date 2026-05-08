<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Checks\Topology\GroupReplicationAsGalera;
use App\Library\ProxySqlAudit\Finding;
use PHPUnit\Framework\TestCase;

final class GroupReplicationAsGaleraTest extends TestCase
{
    public function testReturnsCriticalFindingWhenBackendIsDeclaredInBothClusterTypes(): void
    {
        $db = new GrAsGaleraFakeDb([
            ['hostgroup_id' => 100, 'hostname' => 'gr-1', 'port' => 3306],
        ]);

        $findings = (new GroupReplicationAsGalera())->run($db);

        $this->assertCount(1, $findings);
        $f = $findings[0];
        $this->assertSame(Finding::SEVERITY_CRITICAL, $f->severity);
        $this->assertSame('topology', $f->category);
        $this->assertSame('topology.gr-declared-as-galera', $f->checkId);
        $this->assertSame('#107', $f->ticket);
        $this->assertStringContainsString('1 backend(s)', $f->title);
        $this->assertStringContainsString('hostgroup=100', $f->evidence);
        $this->assertStringContainsString('gr-1:3306', $f->evidence);
    }

    public function testReturnsEmptyListWhenNoOverlap(): void
    {
        $db = new GrAsGaleraFakeDb([]);
        $this->assertSame([], (new GroupReplicationAsGalera())->run($db));
    }

    public function testReturnsEmptyListWhenSilentQueryFails(): void
    {
        $db = new GrAsGaleraFakeDb(returnsFalse: true);
        $this->assertSame([], (new GroupReplicationAsGalera())->run($db));
    }

    public function testEvidenceMentionsBothClusterTablesSoTheRecommendedFixIsTraceable(): void
    {
        $db = new GrAsGaleraFakeDb([
            ['hostgroup_id' => 7, 'hostname' => 'h', 'port' => 3306],
        ]);
        $f = (new GroupReplicationAsGalera())->run($db)[0];
        $this->assertStringContainsString('runtime_mysql_galera_hostgroups',           $f->evidence);
        $this->assertStringContainsString('runtime_mysql_group_replication_hostgroups', $f->evidence);
    }
}

final class GrAsGaleraFakeDb
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
        return 'mock-result';
    }

    public function sql_fetch_array($res, int $mode = MYSQLI_ASSOC)
    {
        if ($this->cursor >= count($this->rows)) return null;
        return $this->rows[$this->cursor++];
    }
}
