<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Checks\Hostgroup\AllServersShunnedTooLong;
use App\Library\ProxySqlAudit\Finding;
use PHPUnit\Framework\TestCase;

final class AllServersShunnedTooLongTest extends TestCase
{
    public function testWarningPerHostgroupWithLongShunnedFleet(): void
    {
        $db = new AllShunnedFakeDb([
            ['hostgroup_id' => 10, 'first_offline_at' => '2026-01-01 12:00:00', 'minutes_offline' => 17],
            ['hostgroup_id' => 20, 'first_offline_at' => '2026-01-01 11:30:00', 'minutes_offline' => 47],
        ]);
        $findings = (new AllServersShunnedTooLong())->run($db);
        $this->assertCount(2, $findings);
        foreach ($findings as $f) {
            $this->assertSame(Finding::SEVERITY_WARNING, $f->severity);
            $this->assertSame('hostgroup', $f->category);
            $this->assertSame('hostgroup.all-servers-shunned-too-long', $f->checkId);
            $this->assertNull($f->ticket);
            $this->assertStringContainsString('LOAD MYSQL SERVERS TO RUNTIME', $f->fix);
        }
        $this->assertStringContainsString('Hostgroup 10', $findings[0]->title);
        $this->assertStringContainsString('17 minute(s)', $findings[0]->title);
        $this->assertStringContainsString('Hostgroup 20', $findings[1]->title);
        $this->assertStringContainsString('47 minute(s)', $findings[1]->title);
    }

    public function testReturnsEmptyWhenNoHostgroupQualifies(): void
    {
        $db = new AllShunnedFakeDb([]);
        $this->assertSame([], (new AllServersShunnedTooLong())->run($db));
    }

    public function testReturnsEmptyOnSilentQueryFailure(): void
    {
        $db = new AllShunnedFakeDb(returnsFalse: true);
        $this->assertSame([], (new AllServersShunnedTooLong())->run($db));
    }

    public function testEvidenceContainsFirstOfflineAtAndMinutes(): void
    {
        $db = new AllShunnedFakeDb([
            ['hostgroup_id' => 5, 'first_offline_at' => '2026-04-01 03:14:15', 'minutes_offline' => 9000],
        ]);
        $f = (new AllServersShunnedTooLong())->run($db)[0];
        $this->assertStringContainsString('first_offline_at=2026-04-01 03:14:15', $f->evidence);
        $this->assertStringContainsString('minutes_offline=9000',                  $f->evidence);
    }
}

final class AllShunnedFakeDb
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
