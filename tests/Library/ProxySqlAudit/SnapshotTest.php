<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Finding;
use App\Library\ProxySqlAudit\Snapshot;
use PHPUnit\Framework\TestCase;

final class SnapshotTest extends TestCase
{
    public function testCountBySeverityHandlesAllThree(): void
    {
        $findings = [
            new Finding(Finding::SEVERITY_CRITICAL, 'cat', 'a', 't', 'e', 'f', null),
            new Finding(Finding::SEVERITY_CRITICAL, 'cat', 'b', 't', 'e', 'f', null),
            new Finding(Finding::SEVERITY_WARNING,  'cat', 'c', 't', 'e', 'f', null),
            new Finding(Finding::SEVERITY_INFO,     'cat', 'd', 't', 'e', 'f', null),
        ];
        $this->assertSame(
            ['critical' => 2, 'warning' => 1, 'info' => 1],
            Snapshot::countBySeverity($findings)
        );
    }

    public function testCountBySeverityIgnoresNonFindings(): void
    {
        // Defensive: silently skip unexpected entries rather than blow up
        // the persistence step.
        $findings = [
            new Finding(Finding::SEVERITY_WARNING, 'cat', 'a', 't', 'e', 'f', null),
            'not-a-finding',
            42,
        ];
        $this->assertSame(
            ['critical' => 0, 'warning' => 1, 'info' => 0],
            Snapshot::countBySeverity($findings)
        );
    }

    public function testPersistEmitsUpsertWithCounts(): void
    {
        $db = new SnapshotFakeDb();
        Snapshot::persist($db, 17, ['critical' => 3, 'warning' => 2, 'info' => 1], null);

        $this->assertCount(1, $db->queries);
        $sql = $db->queries[0];
        $this->assertStringContainsString('INSERT INTO proxysql_audit_snapshot', $sql);
        $this->assertStringContainsString('VALUES (17, 3, 2, 1, NOW(), NULL)', $sql);
        $this->assertStringContainsString('ON DUPLICATE KEY UPDATE', $sql);
        $this->assertStringContainsString('findings_critical = VALUES(findings_critical)', $sql);
    }

    public function testPersistEscapesAndQuotesLastError(): void
    {
        $db = new SnapshotFakeDb();
        Snapshot::persist($db, 5, ['critical' => 0, 'warning' => 0, 'info' => 0], "boom 'quote'");
        $sql = $db->queries[0];
        // The fake's escape strips single quotes; verify the value is quoted.
        $this->assertStringContainsString("NOW(), 'boom quote'", $sql);
    }
}

final class SnapshotFakeDb
{
    /** @var list<string> */
    public array $queries = [];

    public function sql_query_silent(string $sql)
    {
        $this->queries[] = $sql;
        return 'mock';
    }

    public function sql_real_escape_string(string $s): string
    {
        // Trivial: drop quotes/backslashes/nul.
        return str_replace(["'", "\\", "\0"], '', $s);
    }
}
