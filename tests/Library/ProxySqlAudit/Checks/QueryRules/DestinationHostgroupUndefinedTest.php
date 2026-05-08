<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Checks\QueryRules\DestinationHostgroupUndefined;
use App\Library\ProxySqlAudit\Finding;
use PHPUnit\Framework\TestCase;

final class DestinationHostgroupUndefinedTest extends TestCase
{
    public function testCriticalPerOffendingRule(): void
    {
        $db = new RuleDestHgFakeDb([
            ['rule_id' => 1,  'destination_hostgroup' => 999],
            ['rule_id' => 42, 'destination_hostgroup' => 123],
        ]);
        $findings = (new DestinationHostgroupUndefined())->run($db);
        $this->assertCount(2, $findings);
        foreach ($findings as $f) {
            $this->assertSame(Finding::SEVERITY_CRITICAL, $f->severity);
            $this->assertSame('query_rules', $f->category);
            $this->assertSame('query_rules.destination-hostgroup-undefined', $f->checkId);
            $this->assertNull($f->ticket);
            $this->assertStringContainsString('LOAD MYSQL QUERY RULES TO RUNTIME', $f->fix);
        }
        $this->assertStringContainsString('rule_id=1', $findings[0]->title);
        $this->assertStringContainsString('hostgroup 999', $findings[0]->title);
        $this->assertStringContainsString('rule_id=42', $findings[1]->title);
        $this->assertStringContainsString('hostgroup 123', $findings[1]->title);
    }

    public function testEmptyWhenAllDestinationsAreDeclared(): void
    {
        $db = new RuleDestHgFakeDb([]);
        $this->assertSame([], (new DestinationHostgroupUndefined())->run($db));
    }

    public function testEmptyOnSilentQueryFailure(): void
    {
        $db = new RuleDestHgFakeDb(returnsFalse: true);
        $this->assertSame([], (new DestinationHostgroupUndefined())->run($db));
    }

    public function testFixContainsRuleIdAndHostgroupReference(): void
    {
        $db = new RuleDestHgFakeDb([
            ['rule_id' => 7, 'destination_hostgroup' => 88],
        ]);
        $f = (new DestinationHostgroupUndefined())->run($db)[0];
        $this->assertStringContainsString('WHERE rule_id=7', $f->fix);
        $this->assertStringContainsString('hostgroup 88', $f->fix);
    }
}

final class RuleDestHgFakeDb
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
