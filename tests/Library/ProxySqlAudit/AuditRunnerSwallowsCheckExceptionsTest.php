<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;
use App\Library\ProxySqlAudit\Runner;
use PHPUnit\Framework\TestCase;

final class AuditRunnerSwallowsCheckExceptionsTest extends TestCase
{
    public function testRunnerInvokesEveryCheckInRegistryOrder(): void
    {
        $findings = Runner::runAll(new AuditRunnerFakeDb(), [], [
            AuditRunnerCheckFakeCritical::class,
            AuditRunnerCheckFakeWarning::class,
        ]);

        $this->assertCount(2, $findings);
        $this->assertSame('critical', $findings[0]->severity);
        $this->assertSame('warning', $findings[1]->severity);
        $this->assertSame('topology', $findings[0]->category);
    }

    public function testThrowingCheckBecomesSyntheticInfoFinding(): void
    {
        $findings = Runner::runAll(new AuditRunnerFakeDb(), [], [
            AuditRunnerCheckFakeCritical::class,
            AuditRunnerCheckFakeThrowing::class,
            AuditRunnerCheckFakeWarning::class,
        ]);

        $this->assertCount(3, $findings, 'one bad check must not bury the others');
        $this->assertSame('critical', $findings[0]->severity);
        $this->assertSame('info', $findings[1]->severity);
        $this->assertSame('meta', $findings[1]->category);
        $this->assertStringContainsString('Audit check threw', $findings[1]->title);
        $this->assertStringContainsString('boom', $findings[1]->title);
        $this->assertSame('warning', $findings[2]->severity);
    }

    public function testNonAuditCheckClassReturnsSyntheticFailure(): void
    {
        $findings = Runner::runAll(new AuditRunnerFakeDb(), [], [
            stdClass::class,
        ]);

        $this->assertCount(1, $findings);
        $this->assertSame('info', $findings[0]->severity);
        $this->assertStringContainsString('does not implement AuditCheck', $findings[0]->title);
    }

    public function testEmptyRegistryReturnsNoFindings(): void
    {
        $this->assertSame([], Runner::runAll(new AuditRunnerFakeDb(), [], []));
    }

    public function testNonFindingResultsFromCheckAreFiltered(): void
    {
        // A misbehaving check that yields scalars instead of Findings
        // shouldn't crash the runner — just drop the offending entries.
        $findings = Runner::runAll(new AuditRunnerFakeDb(), [], [
            AuditRunnerCheckFakeMixedReturns::class,
        ]);

        $this->assertCount(1, $findings);
        $this->assertSame('warning', $findings[0]->severity);
    }
}

final class AuditRunnerFakeDb
{
}

final class AuditRunnerCheckFakeCritical implements AuditCheck
{
    public function id(): string { return 'fake.critical'; }
    public function category(): string { return 'topology'; }
    public function run(object $db, array $ctx = []): array
    {
        return [new Finding(Finding::SEVERITY_CRITICAL, 'topology', 'fake.critical', 'fake critical')];
    }
}

final class AuditRunnerCheckFakeWarning implements AuditCheck
{
    public function id(): string { return 'fake.warning'; }
    public function category(): string { return 'hostgroup'; }
    public function run(object $db, array $ctx = []): array
    {
        return [new Finding(Finding::SEVERITY_WARNING, 'hostgroup', 'fake.warning', 'fake warning')];
    }
}

final class AuditRunnerCheckFakeThrowing implements AuditCheck
{
    public function id(): string { return 'fake.throwing'; }
    public function category(): string { return 'topology'; }
    public function run(object $db, array $ctx = []): array
    {
        throw new RuntimeException('boom — admin connection refused');
    }
}

final class AuditRunnerCheckFakeMixedReturns implements AuditCheck
{
    public function id(): string { return 'fake.mixed'; }
    public function category(): string { return 'monitor'; }
    public function run(object $db, array $ctx = []): array
    {
        // @phpstan-ignore-next-line — intentionally returning a non-Finding to exercise filtering
        return [
            'not a finding',
            42,
            new Finding(Finding::SEVERITY_WARNING, 'monitor', 'fake.mixed', 'kept'),
            null,
        ];
    }
}
