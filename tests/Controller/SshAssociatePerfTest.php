<?php

declare(strict_types=1);

use App\Controller\Ssh;
use PHPUnit\Framework\TestCase;

/**
 * Issue #1205 — pin the orchestrator perf knobs:
 *  - NB_WORKER raised from 10 → 30 to amortize the ~166 jobs / click.
 *  - ASSOCIATE_TCP_TIMEOUT_S = 0.5 (was hard-coded 1.0) — halves the
 *    cost of the ~48 TCP-unreachable hosts that ate a full second each.
 *  - ASSOCIATE_SSH_TIMEOUT_S = 5 — applied via Net\SSH2::setTimeout()
 *    after construction so login() can never hang indefinitely.
 *  - Orchestrator emits an info log with elapsed wall-clock + jobs +
 *    workers so future regressions are traceable.
 */
final class SshAssociatePerfTest extends TestCase
{
    private string $controller;

    protected function setUp(): void
    {
        $c = file_get_contents(__DIR__ . '/../../App/Controller/Ssh.php');
        self::assertNotFalse($c);
        $this->controller = $c;
    }

    public function testWorkerCountRaisedTo30(): void
    {
        self::assertSame(30, Ssh::NB_WORKER, 'NB_WORKER must be 30 to amortize ~166 jobs/click');
    }

    public function testTcpTimeoutLoweredToHalfSecond(): void
    {
        self::assertSame(0.5, Ssh::ASSOCIATE_TCP_TIMEOUT_S);
    }

    public function testSshTimeoutCappedAtFiveSeconds(): void
    {
        self::assertSame(5, Ssh::ASSOCIATE_SSH_TIMEOUT_S);
    }

    public function testTcpTimeoutConstantIsUsedInFsockopen(): void
    {
        self::assertStringContainsString(
            '@fsockopen($server[\'ip\'], (int) $server[\'ssh_port\'], $errno, $errstr, self::ASSOCIATE_TCP_TIMEOUT_S)',
            $this->controller,
            'fsockopen must use the ASSOCIATE_TCP_TIMEOUT_S constant, not a hard-coded 1.0'
        );
        self::assertStringNotContainsString(
            '@fsockopen($server[\'ip\'], (int) $server[\'ssh_port\'], $errno, $errstr, 1.0)',
            $this->controller,
            'no remaining hard-coded 1.0s timeout for fsockopen'
        );
    }

    public function testSshTimeoutAppliedAfterConstruction(): void
    {
        // setTimeout() must be called on the SSH2 instance immediately
        // after construction so login() respects the cap.
        self::assertStringContainsString(
            '$ssh->setTimeout(self::ASSOCIATE_SSH_TIMEOUT_S);',
            $this->controller
        );
        $ctorPos = strpos($this->controller, 'new SSH2($server[\'ip\']');
        $timeoutPos = strpos($this->controller, '$ssh->setTimeout(self::ASSOCIATE_SSH_TIMEOUT_S)');
        self::assertNotFalse($ctorPos);
        self::assertNotFalse($timeoutPos);
        self::assertGreaterThan($ctorPos, $timeoutPos,
            'setTimeout must be applied AFTER new SSH2 so it covers login()');
    }

    public function testOrchestratorLogsElapsedAndJobCount(): void
    {
        // The orchestrator must emit a single info line at the end with
        // elapsed wall-clock + jobs + workers so future slowness is
        // diagnosable from the log alone.
        self::assertStringContainsString(
            '$associate_started_at = microtime(true);',
            $this->controller,
            'orchestrator must capture the start timestamp'
        );
        self::assertStringContainsString(
            '$elapsed = microtime(true) - $associate_started_at;',
            $this->controller
        );
        self::assertStringContainsString(
            'Ssh::associate id_ssh_key=%d jobs=%d workers=%d elapsed=%.2fs',
            $this->controller,
            'orchestrator info log must include id_ssh_key, jobs, workers, elapsed'
        );
    }
}
