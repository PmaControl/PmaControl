<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Review #1023 P2 — Graphviz::resolveSqlNodeMysqlServerId used to query
 * `mysql_server.ip_real`, but `ip_real` is only an in-memory alias
 * produced by Dot3's SELECT, not a column. On clusters with multiple
 * SQL/API members the query failed (or fell back to the first member),
 * silently mapping every NDB sql/api node to the same mysql_server.
 *
 * Locks the fix in by grep-pinning the SQL the resolver emits. Going
 * through Sgbd::sql() directly is impractical from a unit test (static
 * factory backed by configuration), so we assert on the source.
 */
final class GraphvizResolveSqlNodeTest extends TestCase
{
    public function testResolverSqlDoesNotReferenceIpRealColumn(): void
    {
        $code = file_get_contents(__DIR__ . '/../../App/Library/Graphviz.php');
        $this->assertNotFalse($code);

        // Strip inline comments (which legitimately mention ip_real to
        // explain the fix) and isolate the resolver method body so the
        // assertion focuses on actual code.
        $start = strpos($code, 'function resolveSqlNodeMysqlServerId');
        $this->assertNotFalse($start, 'resolver method must exist');
        // Body ends at the next sibling `private static function` declaration.
        $end = strpos($code, 'private static function ', $start + 1);
        $this->assertNotFalse($end);
        $body = substr($code, $start, $end - $start);
        $bodyNoComments = preg_replace('~//[^\n]*~', '', $body);

        $this->assertStringNotContainsString(
            'ip_real',
            $bodyNoComments,
            'resolveSqlNodeMysqlServerId must not reference mysql_server.ip_real (alias-only column)'
        );
        $this->assertStringContainsString(
            'SELECT id FROM mysql_server',
            $bodyNoComments,
            'resolver still queries mysql_server by id'
        );
        $this->assertStringContainsString(
            "AND ip = '",
            $bodyNoComments,
            'resolver filters by the persisted ip column'
        );
    }

    public function testResolverSingleMemberShortCircuitsBeforeAnySql(): void
    {
        // Sanity-check the early-return path the SQL fix relies on:
        // single sql_member skips the SQL entirely, so no query, no
        // ip_real risk.
        $code = file_get_contents(__DIR__ . '/../../App/Library/Graphviz.php');
        $start = strpos($code, 'function resolveSqlNodeMysqlServerId');
        // Body ends at the next sibling `private static function` declaration.
        $end = strpos($code, 'private static function ', $start + 1);
        $body = substr($code, $start, $end - $start);

        $this->assertStringContainsString('count($sqlMembers) === 1', $body);
        $this->assertStringContainsString('return (int) reset($sqlMembers);', $body);
    }
}
