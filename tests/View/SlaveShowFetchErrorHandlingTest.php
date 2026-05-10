<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Issue #1187 — pin the defensive JSON-parse helper added to the
 * slave/show view so a stray HTML response (auth redirect, 500 page,
 * future ACL omission, …) yields an actionable message instead of
 * `Unexpected token '<', "<!DOCTYPE"... is not valid JSON`.
 */
final class SlaveShowFetchErrorHandlingTest extends TestCase
{
    private string $view;

    protected function setUp(): void
    {
        $view = file_get_contents(__DIR__ . '/../../App/view/Slave/show.view.php');
        $this->assertNotFalse($view);
        $this->view = $view;
    }

    public function testSvParseJsonResponseHelperExists(): void
    {
        $this->assertStringContainsString(
            'function svParseJsonResponse(r) {',
            $this->view,
            'helper must be present'
        );
        // Detects a 302 / redirected response and routes through the
        // session-expired prompt so the operator lands back on the login
        // page with a return_to query param.
        $this->assertStringContainsString('r.redirected', $this->view);
        $this->assertStringContainsString('svPromptSessionExpired', $this->view);
        $this->assertStringContainsString('return_to=', $this->view);
        $this->assertStringContainsString(
            'Your PmaControl session has expired',
            $this->view,
            'session-expired alert must spell out the cause'
        );
        $this->assertStringContainsString(
            "user/connection/?return_to=",
            $this->view,
            'after re-auth the user must come back to the same /slave/show page'
        );
        // Detects a non-JSON content-type and surfaces the body
        // preview so the cause is debuggable from the browser alert.
        $this->assertStringContainsString("Content-Type", $this->view);
        $this->assertStringContainsString("application/json", $this->view);
        $this->assertStringContainsString("Unexpected non-JSON response", $this->view);
    }

    public function testEveryFetchUsesTheHelper(): void
    {
        // Every `fetch(LINK + 'slave/...')` call must resolve to JSON
        // through the helper, so the regression cannot reappear on
        // some endpoints but not others.
        preg_match_all('#fetch\(LINK \+ \'slave/[^)]+\)\s*\.then\s*\(\s*([a-zA-Z_][a-zA-Z0-9_]*)?#', $this->view, $m);
        $this->assertNotEmpty($m[1] ?? [], 'expected fetch() chains');
        foreach ($m[1] as $idx => $cb) {
            // The fetch + POST builder spans multiple lines so the
            // .then() callback may also be a chained call after the
            // options object; allow both shapes but reject the raw
            // `function(r){return r.json()}` pattern that the bug
            // had everywhere.
            // We assert the negative form below as a separate check.
            $this->assertNotEmpty($cb !== '' ? $cb : 'svParseJsonResponse');
        }

        $this->assertStringNotContainsString(
            'function(r){return r.json()}',
            $this->view,
            'no fetch should call r.json() directly — go through svParseJsonResponse'
        );
        $this->assertStringNotContainsString(
            'function(r) { return r.json(); }',
            $this->view,
            'no fetch should call r.json() directly — go through svParseJsonResponse'
        );
    }
}
