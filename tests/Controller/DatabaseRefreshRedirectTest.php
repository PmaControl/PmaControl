<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Issue #570 — when Database::refresh handles a valid POST it must redirect
 * to /job/index without first flushing the response body. The original
 * trigger was a PHP 8.4+ deprecation rendered by ramsey/uuid 3.x while
 * autoloading UuidFactory inside addRefresh(); the deprecation hit the
 * response stream first and broke header(). Two layers of defense:
 *   1. composer.json bumps ramsey/uuid to ^4.7 (no more deprecation).
 *   2. refresh() wraps addRefresh() with ob_start/ob_end_clean and stops
 *      rendering the view (return + view=false) before calling header().
 *
 * Inspecting the controller source is the same pattern already used by
 * tests/Controller/DatabaseRefreshSecurityTest.php and
 * tests/Controller/DatabaseRefreshPreselectTest.php.
 */
final class DatabaseRefreshRedirectTest extends TestCase
{
    private string $controller;

    protected function setUp(): void
    {
        $this->controller = (string) file_get_contents(
            __DIR__ . '/../../App/Controller/Database.php'
        );
        $this->assertNotSame('', $this->controller, 'Database.php must be readable');
    }

    public function testComposerPinsRamseyUuidToV4OrAbove(): void
    {
        $composer = json_decode(
            (string) file_get_contents(__DIR__ . '/../../composer.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        $constraint = $composer['require']['ramsey/uuid'] ?? '';

        $this->assertNotSame('', $constraint, 'ramsey/uuid must be pinned in composer.json');
        $this->assertDoesNotMatchRegularExpression(
            '/^[~^]?3(\.|$)/',
            $constraint,
            'ramsey/uuid 3.x ships an implicitly-nullable parameter that is fatal-deprecated on PHP 8.4+'
        );
        $this->assertMatchesRegularExpression(
            '/^[~^]?(4|5|6)/',
            $constraint,
            'ramsey/uuid must be on 4.x or above (4.7+ supports PHP 8.4)'
        );
    }

    public function testRefreshActionBuffersAddRefreshBeforeRedirect(): void
    {
        // Slice the refresh() body so we only test that action.
        $start = strpos($this->controller, 'public function refresh($param)');
        $this->assertNotFalse($start, 'Database::refresh must exist');

        $end = strpos($this->controller, 'evaluateRefreshRequest(array $post', $start);
        $this->assertNotFalse($end, 'evaluateRefreshRequest must follow refresh()');

        $body = substr($this->controller, $start, $end - $start);

        $obStart  = strpos($body, 'ob_start()');
        $addCall  = strpos($body, '$this->addRefresh(');
        $obClean  = strpos($body, 'ob_end_clean()');
        $header   = strpos($body, 'header("location: ".LINK."job/index"');

        $this->assertNotFalse($obStart, 'refresh() must call ob_start() before addRefresh() (#570)');
        $this->assertNotFalse($addCall, 'refresh() must call $this->addRefresh()');
        $this->assertNotFalse($obClean, 'refresh() must call ob_end_clean() to discard any deprecation output');
        $this->assertNotFalse($header, 'refresh() must redirect to /job/index after a valid POST');

        $this->assertLessThan(
            $addCall,
            $obStart,
            'ob_start() must run before addRefresh() — otherwise lazy-autoload deprecations leak to the response'
        );
        $this->assertLessThan(
            $obClean,
            $addCall,
            'ob_end_clean() must run after addRefresh() to discard the buffered output'
        );
        $this->assertLessThan(
            $header,
            $obClean,
            'header(Location:) must run AFTER ob_end_clean() — otherwise the buffer flushes through the redirect'
        );
    }

    public function testRefreshActionStopsRenderingTheViewAfterRedirect(): void
    {
        // After header(Location:) we MUST disable view+layout and return so
        // the rest of the action body (Form rendering + DB queries) doesn't
        // run and stack a body on top of the 302.
        $start = strpos($this->controller, 'public function refresh($param)');
        $end   = strpos($this->controller, 'evaluateRefreshRequest(array $post', $start);
        $body  = substr($this->controller, $start, $end - $start);

        $headerPos = strpos($body, 'header("location: ".LINK."job/index"');
        $this->assertNotFalse($headerPos, 'redirect to /job/index must exist');

        $afterHeader = substr($body, $headerPos);

        $this->assertStringContainsString(
            '$this->view = false;',
            substr($body, 0, $headerPos + 200),
            'refresh() must set $this->view = false on the redirect path'
        );
        $this->assertStringContainsString(
            '$this->layout_name = false;',
            substr($body, 0, $headerPos + 200),
            'refresh() must set $this->layout_name = false on the redirect path'
        );
        $this->assertMatchesRegularExpression(
            '/header\("location: "\s*\.\s*LINK\s*\."job\/index"\);\s*return;/s',
            $afterHeader,
            'refresh() must return immediately after header(Location:) — otherwise the action keeps running and renders extra body'
        );
    }
}
