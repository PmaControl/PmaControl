<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Issue #567 — /database/refresh must pre-select every database returned by
 * the AJAX endpoint EXCEPT the four MySQL system schemas.
 *
 * The pre-selection is wired in the inline JS that Database::refresh() emits
 * via $this->di['js']->code_javascript(...). This test inspects the source
 * of Database::refresh and asserts that the JS string contains:
 *   - the four system schema names in a SYSTEM_SCHEMAS array
 *   - a call to selectpicker("val", preselected) AFTER selectpicker("refresh")
 *   - a case-insensitive comparison (.toLowerCase())
 *
 * Inspecting the controller source is the same pattern already used by
 * tests/Controller/DatabaseRefreshSecurityTest.php and
 * tests/Controller/CommonClientEnvironmentFilterTest.php.
 */
final class DatabaseRefreshPreselectTest extends TestCase
{
    private string $controller;

    protected function setUp(): void
    {
        $this->controller = (string) file_get_contents(
            __DIR__ . '/../../App/Controller/Database.php'
        );
        $this->assertNotSame('', $this->controller, 'Database.php must be readable');
    }

    public function testRefreshActionEmbedsSystemSchemasFromSharedConstant(): void
    {
        // The inline JS must derive its list from MysqlServer::SYSTEM_SCHEMAS
        // so future drift on either side propagates automatically.
        $this->assertStringContainsString(
            'json_encode(MysqlServer::SYSTEM_SCHEMAS)',
            $this->controller,
            'Database::refresh must serialize MysqlServer::SYSTEM_SCHEMAS to JS — never hardcode the list'
        );
        $this->assertStringContainsString(
            'use App\\Library\\MysqlServer;',
            $this->controller,
            'Database.php must import MysqlServer to access the SYSTEM_SCHEMAS constant'
        );
    }

    public function testRefreshActionPreselectsAfterSelectpickerRefresh(): void
    {
        // The order matters — selectpicker("refresh") must run BEFORE
        // selectpicker("val", ...) otherwise bootstrap-select discards the
        // selection. We grab the relevant slice of the controller and check
        // the relative position of the two calls.
        $start = strpos($this->controller, 'public function refresh($param)');
        $this->assertNotFalse($start, 'Database::refresh must exist');

        $end = strpos($this->controller, 'evaluateRefreshRequest(array $post', $start);
        $this->assertNotFalse($end, 'Database::evaluateRefreshRequest must follow refresh()');

        $body = substr($this->controller, $start, $end - $start);

        $refreshCall  = strpos($body, 'selectpicker("refresh")');
        $valCall      = strpos($body, 'selectpicker("val", preselected)');

        $this->assertNotFalse($refreshCall, 'refresh() must call selectpicker("refresh")');
        $this->assertNotFalse($valCall, 'refresh() must call selectpicker("val", preselected) to apply the pre-selection');
        $this->assertLessThan(
            $valCall,
            $refreshCall,
            'selectpicker("refresh") must run before selectpicker("val", preselected) — bootstrap-select otherwise drops the selection'
        );
    }

    public function testRefreshActionFiltersDatabasesCaseInsensitively(): void
    {
        // SHOW DATABASES on a case-insensitive collation may return 'MYSQL'
        // or 'mysql'; the JS filter must lowercase before comparing.
        $this->assertStringContainsString(
            '.toLowerCase()',
            $this->controller,
            'Database::refresh JS must compare database names case-insensitively'
        );
    }

    public function testRefreshActionDropsBlankOptionFromPreselection(): void
    {
        // bootstrap-select renders a blank "Nothing selected" placeholder
        // when the source returns 0 rows. Don't include it in the
        // pre-selected array (would auto-pick the placeholder).
        $this->assertStringContainsString(
            'db !== ""',
            $this->controller,
            'Database::refresh JS must skip the empty placeholder option when building preselected'
        );
    }
}
