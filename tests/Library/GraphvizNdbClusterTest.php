<?php

declare(strict_types=1);

use App\Library\Graphviz;
use PHPUnit\Framework\TestCase;

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}
if (!defined('ROOT')) {
    define('ROOT', dirname(__DIR__, 2));
}
if (!defined('LINK')) {
    define('LINK', '/');
}
if (!function_exists('__')) {
    function __($value)
    {
        return $value;
    }
}

/**
 * Epic #799 / lot 5 — pin the structural shape of the NDB cluster
 * Graphviz output. We do not parse the DOT — we assert that:
 *
 *   - the three role bands are emitted (Management / Data / SQL),
 *   - the SQL/API node id is reused (joins the existing mysql_server),
 *   - mgmd → data dashed orange edges exist (arbitrate),
 *   - data ↔ data same-NG replica edges exist (solid green),
 *   - sql → data edges exist (solid blue, SQL plane),
 *   - the cluster href points to /NdbCluster/index/<id>.
 */
final class GraphvizNdbClusterTest extends TestCase
{
    public function testNominalClusterEmitsThreeBandsAndAllEdgeFamilies(): void
    {
        $cluster = $this->buildNominalCluster();

        $dot = Graphviz::generateNdbCluster([1 => $cluster]);

        $this->assertNotSame('', $dot);

        // Title and href to the dedicated page
        $this->assertStringContainsString('NDB &middot; ndb84', $dot);
        $this->assertStringContainsString('href = "/NdbCluster/index/1"', $dot);

        // Three role bands
        $this->assertStringContainsString('label = "Management"', $dot);
        $this->assertStringContainsString('label = "Data nodes"', $dot);
        $this->assertStringContainsString('label = "SQL / API"', $dot);

        // Standalone NDB members get dedicated graphviz node ids
        $this->assertStringContainsString('ndb_mgmd_1_1', $dot);
        $this->assertStringContainsString('ndb_data_1_2', $dot);
        $this->assertStringContainsString('ndb_data_1_3', $dot);

        // SQL/API member is referenced by its mysql_server id (267)
        $this->assertMatchesRegularExpression('/267\s*;/', $dot);

        // mgmd -> data : dashed orange (arbitrate)
        $this->assertMatchesRegularExpression(
            '/ndb_mgmd_1_1\s*->\s*ndb_data_1_2[^\n]*color="#fb8c00"[^\n]*dashed/',
            $dot
        );

        // data <-> data same NG : solid green (replica)
        $this->assertMatchesRegularExpression(
            '/ndb_data_1_2\s*->\s*ndb_data_1_3[^\n]*color="#1b5e20"[^\n]*replica/',
            $dot
        );

        // sql -> data : solid blue (SQL plane)
        $this->assertMatchesRegularExpression(
            '/267\s*->\s*ndb_data_1_2[^\n]*color="#1976d2"/',
            $dot
        );
    }

    public function testEmptyInputReturnsEmptyString(): void
    {
        $this->assertSame('', Graphviz::generateNdbCluster([]));
    }

    public function testClusterWithoutSqlMemberStillRendersMgmdAndData(): void
    {
        $cluster = $this->buildNominalCluster();
        $cluster['sql_members'] = [];
        $cluster['nodes']['sql'] = [];
        $cluster['totals']['sql'] = 0;
        $cluster['totals']['sql_up'] = 0;

        $dot = Graphviz::generateNdbCluster([1 => $cluster]);

        $this->assertStringContainsString('label = "Management"', $dot);
        $this->assertStringContainsString('label = "Data nodes"', $dot);
        $this->assertStringNotContainsString('label = "SQL / API"', $dot);
        // No SQL plane edges when there is no SQL/API member.
        $this->assertStringNotContainsString('-> ndb_data_1_2 [color="#1976d2"', $dot);
    }

    private function buildNominalCluster(): array
    {
        return [
            'id_cluster'     => 1,
            'name'           => 'ndb84',
            'ndb_version'    => 'ndb-8.4.9',
            'no_of_replicas' => 2,
            'sql_members'    => [267],
            'totals'         => [
                'mgmd' => 1, 'mgmd_up' => 1,
                'data' => 2, 'data_up' => 2,
                'sql'  => 1, 'sql_up'  => 1,
            ],
            'node_groups'    => [
                0 => ['total' => 2, 'up' => 2],
            ],
            'nodes' => [
                'mgmd' => [
                    [
                        'ndb_node_id' => 1, 'role' => 'mgmd',
                        'hostname' => 'ndb84-mgm-1', 'ip' => '10.68.68.244',
                        'port' => 1186, 'node_group' => null, 'is_primary' => 0,
                        'status' => 'CONNECTED', 'ndb_version' => 'ndb-8.4.9',
                        'uptime_sec' => 7200,
                        'memory_used_mb' => null, 'memory_total_mb' => null,
                        'data_memory_used_mb' => null, 'index_memory_used_mb' => null,
                    ],
                ],
                'data' => [
                    [
                        'ndb_node_id' => 2, 'role' => 'data',
                        'hostname' => 'ndb84-data-1', 'ip' => '10.68.68.245',
                        'port' => null, 'node_group' => 0, 'is_primary' => 1,
                        'status' => 'STARTED', 'ndb_version' => 'ndb-8.4.9',
                        'uptime_sec' => 7100,
                        'memory_used_mb' => 410, 'memory_total_mb' => 1024,
                        'data_memory_used_mb' => 380, 'index_memory_used_mb' => 30,
                    ],
                    [
                        'ndb_node_id' => 3, 'role' => 'data',
                        'hostname' => 'ndb84-data-2', 'ip' => '10.68.68.246',
                        'port' => null, 'node_group' => 0, 'is_primary' => 0,
                        'status' => 'STARTED', 'ndb_version' => 'ndb-8.4.9',
                        'uptime_sec' => 7100,
                        'memory_used_mb' => 410, 'memory_total_mb' => 1024,
                        'data_memory_used_mb' => 380, 'index_memory_used_mb' => 30,
                    ],
                ],
                'sql' => [
                    [
                        'ndb_node_id' => 4, 'role' => 'sql',
                        'hostname' => 'ndb84-sql-1', 'ip' => '10.68.68.247',
                        'port' => 3306, 'node_group' => null, 'is_primary' => 0,
                        'status' => 'CONNECTED', 'ndb_version' => 'ndb-8.4.9',
                        'uptime_sec' => 7000,
                        'memory_used_mb' => null, 'memory_total_mb' => null,
                        'data_memory_used_mb' => null, 'index_memory_used_mb' => null,
                    ],
                ],
            ],
            'config' => 'NDB_CLUSTER_OK',
        ];
    }
}
