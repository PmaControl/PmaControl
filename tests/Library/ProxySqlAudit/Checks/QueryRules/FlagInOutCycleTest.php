<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Checks\QueryRules\FlagInOutCycle;
use App\Library\ProxySqlAudit\Finding;
use PHPUnit\Framework\TestCase;

final class FlagInOutCycleTest extends TestCase
{
    public function testDetectsTwoNodeCycle(): void
    {
        // 0 -> 1 -> 0
        $db = new FlagCycleFakeDb([
            ['rule_id' => 10, 'flagIN' => 0, 'flagOUT' => 1],
            ['rule_id' => 20, 'flagIN' => 1, 'flagOUT' => 0],
        ]);
        $findings = (new FlagInOutCycle())->run($db);
        $this->assertCount(1, $findings);
        $f = $findings[0];
        $this->assertSame(Finding::SEVERITY_WARNING, $f->severity);
        $this->assertSame('query_rules', $f->category);
        $this->assertSame('query_rules.flag-in-out-cycle', $f->checkId);
        $this->assertNull($f->ticket);
        $this->assertStringContainsString('0 -> 1 -> 0', $f->title);
        $this->assertStringContainsString('rule_ids on the cycle: 10, 20', $f->evidence);
        $this->assertStringContainsString('LOAD MYSQL QUERY RULES TO RUNTIME', $f->fix);
    }

    public function testDetectsThreeNodeCycle(): void
    {
        // 1 -> 2 -> 3 -> 1
        $db = new FlagCycleFakeDb([
            ['rule_id' => 1, 'flagIN' => 1, 'flagOUT' => 2],
            ['rule_id' => 2, 'flagIN' => 2, 'flagOUT' => 3],
            ['rule_id' => 3, 'flagIN' => 3, 'flagOUT' => 1],
        ]);
        $f = (new FlagInOutCycle())->run($db)[0];
        $this->assertStringContainsString('1 -> 2 -> 3 -> 1', $f->title);
    }

    public function testNoCycleReturnsEmpty(): void
    {
        // Pure DAG: 0 -> 1 -> 2
        $db = new FlagCycleFakeDb([
            ['rule_id' => 1, 'flagIN' => 0, 'flagOUT' => 1],
            ['rule_id' => 2, 'flagIN' => 1, 'flagOUT' => 2],
        ]);
        $this->assertSame([], (new FlagInOutCycle())->run($db));
    }

    public function testSelfLoopIsACycle(): void
    {
        $db = new FlagCycleFakeDb([
            ['rule_id' => 5, 'flagIN' => 7, 'flagOUT' => 7],
        ]);
        $f = (new FlagInOutCycle())->run($db)[0];
        $this->assertStringContainsString('7 -> 7', $f->title);
    }

    public function testReturnsEmptyOnSilentQueryFailure(): void
    {
        $db = new FlagCycleFakeDb(returnsFalse: true);
        $this->assertSame([], (new FlagInOutCycle())->run($db));
    }

    public function testDuplicateCycleIsNotEmittedTwice(): void
    {
        // Two independent paths into the same back-edge surface as
        // ONE cycle (deduplicated by node sequence).
        $db = new FlagCycleFakeDb([
            ['rule_id' => 1, 'flagIN' => 0, 'flagOUT' => 1],
            ['rule_id' => 2, 'flagIN' => 1, 'flagOUT' => 0],
            ['rule_id' => 3, 'flagIN' => 0, 'flagOUT' => 1], // duplicates the first edge
        ]);
        $this->assertCount(1, (new FlagInOutCycle())->run($db));
    }
}

final class FlagCycleFakeDb
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
