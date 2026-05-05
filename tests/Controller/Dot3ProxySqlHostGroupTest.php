<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Controller\Dot3;

/**
 * Tests for ProxySQL host group mapping (ticket #107).
 *
 * The bug: Graphviz.php used three successive if-blocks that overwrote
 * $correspondance_hg, so only the last non-empty family survived.
 * The fix: Dot3::getProxySqlHostGroupMap() merges all families.
 *
 * Fixture: tests/fixtures/dot3_proxysql_gr_cluster.json
 * (real snapshot from servers 181/182, reduced to this cluster only)
 */
final class Dot3ProxySqlHostGroupTest extends TestCase
{
    private static array $fixture = [];

    public static function setUpBeforeClass(): void
    {
        $path = __DIR__ . '/../fixtures/dot3_proxysql_gr_cluster.json';
        self::$fixture = json_decode(file_get_contents($path), true);
    }

    // ── getHostGroup ────────────────────────────────────────────────

    public function testGetHostGroupMapsGrFields(): void
    {
        $grHostgroups = self::$fixture['servers']['181']['mysql_group_replication_hostgroups'];

        $map = Dot3::getHostGroup($grHostgroups);

        // First GR entry: writer=10, backup_writer=20, reader=30, offline=40
        $this->assertSame('writer', $map['10']);
        $this->assertSame('backup_writer', $map['20']);
        $this->assertSame('reader', $map['30']);
        $this->assertSame('offline', $map['40']);

        // Second GR entry: writer=110, backup_writer=120, reader=130, offline=140
        $this->assertSame('writer', $map['110']);
        $this->assertSame('backup_writer', $map['120']);
        $this->assertSame('reader', $map['130']);
        $this->assertSame('offline', $map['140']);

        // Hardcoded mirroring entry
        $this->assertSame('mirroring', $map[100]);
    }

    public function testGetHostGroupIgnoresNonHostgroupKeys(): void
    {
        $grHostgroups = self::$fixture['servers']['181']['mysql_group_replication_hostgroups'];
        $map = Dot3::getHostGroup($grHostgroups);

        // Keys like active, max_writers, writer_is_also_reader, max_transactions_behind
        // should NOT appear as mapped values
        $this->assertNotContains('active', $map);
        $this->assertNotContains('max_writers', $map);
    }

    // ── getProxySqlHostGroupMap ──────────────────────────────────────

    public function testHostGroupMapFromGrOnlyServer(): void
    {
        $server = self::$fixture['servers']['181'];

        $map = Dot3::getProxySqlHostGroupMap($server);

        // All 8 hostgroups from the 2 GR entries should be mapped
        $this->assertSame('writer', $map['10']);
        $this->assertSame('backup_writer', $map['20']);
        $this->assertSame('reader', $map['30']);
        $this->assertSame('offline', $map['40']);
        $this->assertSame('writer', $map['110']);
        $this->assertSame('backup_writer', $map['120']);
        $this->assertSame('reader', $map['130']);
        $this->assertSame('offline', $map['140']);
    }

    public function testHostGroupMapReturnsEmptyForNonProxyServer(): void
    {
        $server = self::$fixture['servers']['172'];

        $map = Dot3::getProxySqlHostGroupMap($server);

        $this->assertSame([], $map);
    }

    public function testHostGroupMapReturnsEmptyForMinimalServer(): void
    {
        $server = self::$fixture['servers']['252'];

        $map = Dot3::getProxySqlHostGroupMap($server);

        $this->assertSame([], $map);
    }

    public function testHostGroupMapMergesMultipleFamilies(): void
    {
        // Simulate a ProxySQL with both replication AND GR hostgroups
        // where hostgroup 10 is "writer" in both families
        $server = [
            'mysql_replication_hostgroups' => [
                [
                    'writer_hostgroup' => '10',
                    'reader_hostgroup' => '20',
                ],
            ],
            'mysql_group_replication_hostgroups' => [
                [
                    'writer_hostgroup' => '10',
                    'backup_writer_hostgroup' => '30',
                    'reader_hostgroup' => '40',
                    'offline_hostgroup' => '50',
                ],
            ],
        ];

        $map = Dot3::getProxySqlHostGroupMap($server);

        // hostgroup 10 is "writer" in both families — should not duplicate
        $this->assertSame('writer', $map['10']);

        // hostgroup 20 is "reader" from replication
        $this->assertSame('reader', $map['20']);

        // hostgroup 30, 40, 50 come from GR only
        $this->assertSame('backup_writer', $map['30']);
        $this->assertSame('reader', $map['40']);
        $this->assertSame('offline', $map['50']);
    }

    public function testHostGroupMapMergesConflictingLabels(): void
    {
        // Same hostgroup ID with DIFFERENT roles across families
        $server = [
            'mysql_replication_hostgroups' => [
                [
                    'writer_hostgroup' => '10',
                    'reader_hostgroup' => '20',
                ],
            ],
            'mysql_galera_hostgroups' => [
                [
                    'writer_hostgroup' => '20',
                    'backup_writer_hostgroup' => '30',
                    'reader_hostgroup' => '40',
                    'offline_hostgroup' => '50',
                ],
            ],
        ];

        $map = Dot3::getProxySqlHostGroupMap($server);

        // hostgroup 20 is "reader" in replication but "writer" in galera
        // Should be merged with " / " (galera is processed before replication)
        $this->assertSame('writer / reader', $map['20']);
    }

    // ── Fallback label (was: continue → hidden row) ──────────────────

    public function testUnmappedHostgroupGetsFallbackLabel(): void
    {
        $server = self::$fixture['servers']['181'];
        $map = Dot3::getProxySqlHostGroupMap($server);

        // Hostgroup 999 does not exist in any family
        $label = $map['999'] ?? (string) 999;

        $this->assertSame('999', $label);
    }

    // ── Fixture: all mysql_servers backends have a mapped hostgroup ──

    public function testAllBackendHostgroupsAreMapped(): void
    {
        $server = self::$fixture['servers']['181'];
        $map = Dot3::getProxySqlHostGroupMap($server);

        foreach ($server['mysql_servers'] as $backend) {
            $hgId = $backend['hostgroup_id'];
            $this->assertArrayHasKey(
                $hgId,
                $map,
                "Backend {$backend['hostname']}:{$backend['port']} in hostgroup {$hgId} has no mapping"
            );
        }
    }

    // ── Fixture: backends map to "offline" (hostgroups 40, 140) ──────

    public function testFixtureBackendsAreInOfflineHostgroups(): void
    {
        $server = self::$fixture['servers']['181'];
        $map = Dot3::getProxySqlHostGroupMap($server);

        $usedHostgroups = array_unique(array_column($server['mysql_servers'], 'hostgroup_id'));

        foreach ($usedHostgroups as $hgId) {
            $this->assertSame(
                'offline',
                $map[$hgId],
                "Hostgroup {$hgId} should map to 'offline' per the GR config"
            );
        }
    }
}
