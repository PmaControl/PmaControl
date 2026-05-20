<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Review #1023 P2 — a SQL/API-less NDB cluster (mgmd + data only) was
 * unset()'d from `self::$ndb_cluster` before reaching `buildNdbCluster`,
 * so the orphan rendering path was dead code despite Graphviz already
 * supporting that case.
 *
 * The test pins three contracts on `App/Controller/Dot3.php`:
 *
 *  1. `discoverNdbClusters` no longer drops empty SQL/API entries;
 *  2. `buildNdbCluster` keeps an `$isOrphan` short-circuit so the
 *     orphan render is reached when SQL/API is empty;
 *  3. `$build_ndb_cluster_orphan_rendered` is reset at run-level
 *     (in `getGroup`) so the orphan does not duplicate across pages.
 */
final class Dot3NdbOrphanClusterTest extends TestCase
{
    private string $code;

    protected function setUp(): void
    {
        $code = file_get_contents(__DIR__ . '/../../App/Controller/Dot3.php');
        $this->assertNotFalse($code);
        $this->code = $code;
    }

    public function testGenerateGroupNdbClusterDoesNotUnsetEmptySqlMemberLists(): void
    {
        $start = strpos($this->code, 'function generateGroupNdbCluster(');
        $this->assertNotFalse($start, 'generateGroupNdbCluster() must exist');
        $end = strpos($this->code, "    public function ", $start + 10);
        $this->assertNotFalse($end);
        $body = substr($this->code, $start, $end - $start);

        // The old bug was an `unset($tmp_group[$key])` inside the
        // foreach that normalises member lists. Make sure no such
        // unset remains.
        $this->assertDoesNotMatchRegularExpression(
            '/foreach\s*\(\s*\$tmp_group[^}]+unset\s*\(\s*\$tmp_group\[[^]]+\]\s*\)/s',
            $body,
            'generateGroupNdbCluster must not unset empty SQL/API member lists (review #1023 P2)'
        );
    }

    public function testBuildNdbClusterHandlesOrphanShortCircuit(): void
    {
        $start = strpos($this->code, 'public function buildNdbCluster');
        $this->assertNotFalse($start);
        $end = strpos($this->code, "\n    }\n", $start + 10);
        $this->assertNotFalse($end);
        $body = substr($this->code, $start, $end - $start);

        $this->assertStringContainsString(
            '$isOrphan = empty($cluster_members);',
            $body,
            'buildNdbCluster must explicitly handle the orphan (no SQL/API) case'
        );
        $this->assertStringContainsString(
            'self::$build_ndb_cluster_orphan_rendered',
            $body,
            'buildNdbCluster must consult / mark the orphan-rendered set'
        );
        // Render-once gate: orphan already emitted in this run → skip.
        $this->assertMatchesRegularExpression(
            '/if\s*\(\s*\$isOrphan\s*&&\s*!empty\(\s*self::\$build_ndb_cluster_orphan_rendered/s',
            $body,
            'buildNdbCluster must skip already-rendered orphans this run'
        );
    }

    public function testGetGroupResetsOrphanRenderedSetAtRunLevel(): void
    {
        $start = strpos($this->code, 'public function getGroup');
        $this->assertNotFalse($start, 'getGroup() must exist');
        $body = substr($this->code, $start, 4000);

        // The reset must live next to the existing per-run state
        // resets so the orphan tracker zeroes out at the start of
        // each Dot3 generation, not between per-group renders.
        $this->assertMatchesRegularExpression(
            '/self::\$ndb_cluster\s*=\s*array\(\)\s*;\s*\n\s*self::\$build_ndb_cluster_orphan_rendered\s*=\s*array\(\)\s*;/',
            $body,
            'getGroup must reset $build_ndb_cluster_orphan_rendered at run start'
        );
    }
}
