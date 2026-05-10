<?php

declare(strict_types=1);

use App\Library\SlaveDurability;
use PHPUnit\Framework\TestCase;

/**
 * Issue #1196 follow-up — pin the contract that
 * Slave::buildDurabilityRows now pairs every slave row with a
 * matching master row (the operator wants master-vs-slave columns
 * for every variable, not only for binlog group-commit).
 *
 * The helper itself is private so we exercise the *shape contract*
 * via grep on the source: assert that the foreach produces a row
 * whose top-level fields stay the slave values (so the picker code
 * keeps working unchanged) AND that a `master` sub-array is always
 * appended (with an `unknown` cell when the master is not monitored).
 */
final class SlaveDurabilityRowsPairingTest extends TestCase
{
    private string $code;

    protected function setUp(): void
    {
        $code = file_get_contents(__DIR__ . '/../../App/Controller/Slave.php');
        $this->assertNotFalse($code);
        $this->code = $code;
    }

    public function testBuildDurabilityRowsAcceptsOptionalMasterId(): void
    {
        $this->assertMatchesRegularExpression(
            '/private static function buildDurabilityRows\(int \$idMysqlServer, int \$parallelThreads, \?int \$idMaster = null\)/',
            $this->code,
            'buildDurabilityRows must accept an optional $idMaster'
        );
    }

    public function testBuildDurabilityRowsCallsSlaveDurabilityRowsTwice(): void
    {
        $start = strpos($this->code, 'function buildDurabilityRows(');
        $this->assertNotFalse($start);
        $end = strpos($this->code, "\n    }\n", $start + 50);
        $this->assertNotFalse($end);
        $body = substr($this->code, $start, $end - $start);

        // Two independent classifications, one per server.
        $this->assertSame(
            2,
            substr_count($body, '\\App\\Library\\SlaveDurability::rows('),
            'helper must classify slave AND master values separately'
        );
        // Master uses parallel_threads = 0 (we cannot prove the master
        // runs parallel from here, so do not surface a hard risk).
        $this->assertStringContainsString("['parallel_threads' => 0]", $body);
    }

    public function testBuildDurabilityRowsAppendsMasterCellPerRow(): void
    {
        $start = strpos($this->code, 'function buildDurabilityRows(');
        $end = strpos($this->code, "\n    }\n", $start + 50);
        $body = substr($this->code, $start, $end - $start);

        // Each slave row is augmented with a `master` key.
        $this->assertStringContainsString("'master' => \$mr ? [", $body);
        // When the master row is missing, fall back to a dedicated
        // "unknown / not monitored" cell so the view never sees a
        // missing key.
        $this->assertStringContainsString('$unknownCell', $body);
        $this->assertStringContainsString("'tooltip' => 'Master not monitored", $body);
    }

    public function testBuildDurabilityRowsCalledAfterMasterIdIsKnown(): void
    {
        // The early empty initialisation must precede the rebuild
        // (which uses $master_id), so the view never reads a stale
        // value and we don't pay the rebuild on /slave/show/<id>/__new__/.
        $earlyInit = strpos($this->code, "\$data['durability_rows'] = [];");
        $rebuild   = strpos($this->code, "\$data['durability_rows'] = self::buildDurabilityRows(");
        $masterIdResolved = strpos($this->code, "\$data['master_id'] = \$master_id ?? 0;");
        $this->assertNotFalse($earlyInit);
        $this->assertNotFalse($rebuild);
        $this->assertNotFalse($masterIdResolved);
        $this->assertLessThan($masterIdResolved, $earlyInit, 'early init must precede master_id resolution');
        $this->assertLessThan($rebuild, $masterIdResolved, 'master_id must be known before the rebuild');
    }
}
