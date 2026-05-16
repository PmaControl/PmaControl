<?php

declare(strict_types=1);

namespace Tests\Library\Audit;

use PHPUnit\Framework\TestCase;

/**
 * Issue #1209 follow-up / PR #1246 — pin the centralised version-gated
 * SQL choice in `BlackholeRelay::fetchMasterStatus()`.
 *
 * MySQL 8.4 removed `SHOW MASTER STATUS` in favour of
 * `SHOW BINARY LOG STATUS`. Project convention
 * (docs/database_conventions.md § "Version-gated SQL") is to delegate
 * to the central `ServerCapabilities::masterStatusSql($link)` helper —
 * never write a try-and-fall-back probe, never duplicate the if/else
 * gate inline at the call site.
 */
final class BlackholeRelayMasterStatusFallbackTest extends TestCase
{
    private string $source;

    protected function setUp(): void
    {
        $path = dirname(__DIR__, 3) . '/App/Library/BlackholeRelay.php';
        $contents = @file_get_contents($path);
        $this->assertNotFalse($contents, "BlackholeRelay.php must be readable");
        $this->source = $contents;
    }

    public function testFetchMasterStatusGoesThroughTheCentralHelper(): void
    {
        // The function body must call ServerCapabilities::masterStatusSql()
        // and pass the resulting SQL to sql_query_silent() — no inline
        // if/else, no probe.
        $this->assertMatchesRegularExpression(
            '/function\s+fetchMasterStatus[^{]*\{(?:(?!function\s).)*ServerCapabilities::masterStatusSql\s*\(/s',
            $this->source,
            'fetchMasterStatus() must use ServerCapabilities::masterStatusSql()'
        );
    }

    public function testFetchMasterStatusHasNoTryAndFallBackProbe(): void
    {
        // Anti-regression: ensure only one sql_query_silent() call inside
        // the function body.
        $body = '';
        if (preg_match('/function\s+fetchMasterStatus[^{]*\{((?:(?!function\s).)*)\}/s', $this->source, $m) === 1) {
            $body = $m[1];
        }
        $this->assertNotSame('', $body, 'fetchMasterStatus body must be locatable');

        $sqlQuerySilentCalls = preg_match_all('/sql_query_silent\s*\(/', $body, $matches);
        $this->assertSame(
            1,
            $sqlQuerySilentCalls,
            'fetchMasterStatus must call sql_query_silent() exactly once — no try-and-fall-back'
        );
    }

    public function testFetchMasterStatusHasNoInlineIfElseGate(): void
    {
        // The inline `if (ServerCapabilities::supports(…)) $sql = '…';`
        // pattern must NOT come back. Caught early — every call site
        // should go through the helper now.
        $body = '';
        if (preg_match('/function\s+fetchMasterStatus[^{]*\{((?:(?!function\s).)*)\}/s', $this->source, $m) === 1) {
            $body = $m[1];
        }
        $this->assertStringNotContainsString(
            "ServerCapabilities::supports",
            $body,
            'fetchMasterStatus must not duplicate the gate — use masterStatusSql() instead'
        );
    }
}
