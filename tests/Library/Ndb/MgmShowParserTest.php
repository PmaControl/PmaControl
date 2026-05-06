<?php

declare(strict_types=1);

use App\Library\Ndb\MgmShowParser;
use PHPUnit\Framework\TestCase;

/**
 * Epic #799 / lot 2 — pin the parser of `ndb_mgm -e show` against the
 * real lab fixture (#798) plus the 3 degraded variants the collector
 * has to react to (data node down, whole node group lost, mgmd
 * unreachable). Every variant is encoded inline so a maintainer can
 * regenerate them by SSH-ing to the lab and re-running the same
 * `ndb_mgm` command.
 */
final class MgmShowParserTest extends TestCase
{
    public function testParsesNominalLabClusterFromIssue798(): void
    {
        $output = <<<'TEXT'
Connected to Management Server at: 10.68.68.244:1186
Cluster Configuration
---------------------
[ndbd(NDB)] 2 node(s)
id=2    @10.68.68.245  (mysql-8.4.9 ndb-8.4.9, Nodegroup: 0, *)
id=3    @10.68.68.246  (mysql-8.4.9 ndb-8.4.9, Nodegroup: 0)

[ndb_mgmd(MGM)] 1 node(s)
id=1    @10.68.68.244  (mysql-8.4.9 ndb-8.4.9)

[mysqld(API)] 1 node(s)
id=4    @10.68.68.247  (mysql-8.4.9 ndb-8.4.9)
TEXT;

        $r = MgmShowParser::parse($output);

        $this->assertTrue($r['reachable']);
        $this->assertSame('10.68.68.244:1186', $r['mgm_endpoint']);
        $this->assertSame('ndb-8.4.9', $r['cluster_version']);
        $this->assertSame([], $r['errors']);
        $this->assertCount(4, $r['nodes']);

        $byId = self::indexByNodeId($r['nodes']);

        // mgmd
        $this->assertSame('mgmd',                       $byId[1]['role']);
        $this->assertSame('10.68.68.244',               $byId[1]['ip']);
        $this->assertSame(MgmShowParser::STATUS_CONNECTED, $byId[1]['status']);
        $this->assertNull($byId[1]['node_group']);
        $this->assertFalse($byId[1]['is_primary']);
        $this->assertSame('ndb-8.4.9', $byId[1]['ndb_version']);

        // data primary
        $this->assertSame('data', $byId[2]['role']);
        $this->assertSame(0, $byId[2]['node_group']);
        $this->assertTrue($byId[2]['is_primary']);
        $this->assertSame(MgmShowParser::STATUS_STARTED, $byId[2]['status']);

        // data replica (not primary)
        $this->assertSame('data', $byId[3]['role']);
        $this->assertSame(0, $byId[3]['node_group']);
        $this->assertFalse($byId[3]['is_primary']);
        $this->assertSame(MgmShowParser::STATUS_STARTED, $byId[3]['status']);

        // sql/api
        $this->assertSame('sql', $byId[4]['role']);
        $this->assertSame('10.68.68.247', $byId[4]['ip']);
        $this->assertSame(MgmShowParser::STATUS_CONNECTED, $byId[4]['status']);
    }

    public function testFlagsSingleDataNodeDownAsNotStartedKeepingTheConfiguredIp(): void
    {
        // ndb_mgm reports the configured IP via "accepting connect from"
        // even when the data node has never (re-)joined since boot.
        $output = <<<'TEXT'
Connected to Management Server at: 10.68.68.244:1186
[ndbd(NDB)] 2 node(s)
id=2 (not connected, accepting connect from 10.68.68.245, *)
id=3    @10.68.68.246  (mysql-8.4.9 ndb-8.4.9, Nodegroup: 0)

[ndb_mgmd(MGM)] 1 node(s)
id=1    @10.68.68.244  (mysql-8.4.9 ndb-8.4.9)

[mysqld(API)] 1 node(s)
id=4    @10.68.68.247  (mysql-8.4.9 ndb-8.4.9)
TEXT;

        $r = MgmShowParser::parse($output);
        $byId = self::indexByNodeId($r['nodes']);

        $this->assertSame(MgmShowParser::STATUS_NOT_STARTED, $byId[2]['status']);
        $this->assertSame('10.68.68.245', $byId[2]['ip'], 'IP must be recovered from the "accepting connect from" hint.');
        $this->assertTrue($byId[2]['is_primary'], 'The * marker survives the disconnect — node 2 was the primary of NG0.');
        $this->assertNull($byId[2]['ndb_version']);

        // The remaining data node is still STARTED — alert level should
        // be a warning, not critical (the node group still has 1 replica).
        $this->assertSame(MgmShowParser::STATUS_STARTED, $byId[3]['status']);
    }

    public function testFlagsAllDataNodesDownNodeGroupLost(): void
    {
        $output = <<<'TEXT'
Connected to Management Server at: 10.68.68.244:1186
[ndbd(NDB)] 2 node(s)
id=2 (not connected, accepting connect from 10.68.68.245, *)
id=3 (not connected, accepting connect from 10.68.68.246)

[ndb_mgmd(MGM)] 1 node(s)
id=1    @10.68.68.244  (mysql-8.4.9 ndb-8.4.9)

[mysqld(API)] 1 node(s)
id=4 (not connected, accepting connect from 10.68.68.247)
TEXT;

        $r = MgmShowParser::parse($output);
        $byId = self::indexByNodeId($r['nodes']);

        // mgmd alone is alive
        $this->assertSame(MgmShowParser::STATUS_CONNECTED,   $byId[1]['status']);
        // both data nodes down → caller is expected to raise NDB_NODEGROUP_LOST
        $this->assertSame(MgmShowParser::STATUS_NOT_STARTED, $byId[2]['status']);
        $this->assertSame(MgmShowParser::STATUS_NOT_STARTED, $byId[3]['status']);
        // sql/api can't talk to NDB anymore → DISCONNECTED
        $this->assertSame(MgmShowParser::STATUS_DISCONNECTED, $byId[4]['status']);

        // No version anywhere on the data plane → cluster_version falls
        // back to whatever the mgmd reports.
        $this->assertSame('ndb-8.4.9', $r['cluster_version']);
    }

    public function testCapturesMgmdUnreachableEnvelopeAndMarksReachableFalse(): void
    {
        // ndb_mgm prints this on stdout when it can't reach the mgmd, then
        // exits non-zero. Parser should not crash and must surface the
        // diagnostic.
        $output = <<<'TEXT'
Unable to connect with connect string: nodeid=0,10.68.68.244:1186
Retrying every 5 seconds. Attempts left: 12, failed.
* Could not contact server '10.68.68.244:1186': Connection refused
TEXT;

        $r = MgmShowParser::parse($output);

        $this->assertFalse($r['reachable'], 'No "Connected to Management Server" header AND no parsed nodes → unreachable.');
        $this->assertNull($r['mgm_endpoint']);
        $this->assertSame([], $r['nodes']);
        $this->assertCount(2, $r['errors'], 'Both the "Unable to connect" and the "* Could not contact" lines must be captured.');
        $this->assertStringContainsString('Could not contact', implode("\n", $r['errors']));
    }

    public function testMarksStartingPhaseAsStarting(): void
    {
        // Common during recovery: a data node has reconnected but is
        // still replaying its log / building indexes.
        $output = <<<'TEXT'
Connected to Management Server at: 10.68.68.244:1186
[ndbd(NDB)] 2 node(s)
id=2    @10.68.68.245  (mysql-8.4.9 ndb-8.4.9, Nodegroup: 0, *)
id=3    @10.68.68.246  (mysql-8.4.9 ndb-8.4.9, Starting Phase 50, Nodegroup: 0)

[ndb_mgmd(MGM)] 1 node(s)
id=1    @10.68.68.244  (mysql-8.4.9 ndb-8.4.9)
TEXT;

        $r = MgmShowParser::parse($output);
        $byId = self::indexByNodeId($r['nodes']);

        $this->assertSame(MgmShowParser::STATUS_STARTED,  $byId[2]['status']);
        $this->assertSame(MgmShowParser::STATUS_STARTING, $byId[3]['status']);
    }

    public function testIgnoresUnknownLineShapeWithoutCrashing(): void
    {
        // Defensive: if a future ndb-9.x adds a column or annotation we
        // don't recognise, we keep parsing the rest instead of erroring.
        $output = <<<'TEXT'
Connected to Management Server at: 10.68.68.244:1186
[ndbd(NDB)] 2 node(s)
id=2    @10.68.68.245  (mysql-8.4.9 ndb-8.4.9, Nodegroup: 0, *)
id=3 some-future-format-we-do-not-know

[ndb_mgmd(MGM)] 1 node(s)
id=1    @10.68.68.244  (mysql-8.4.9 ndb-8.4.9)
TEXT;

        $r = MgmShowParser::parse($output);
        $byId = self::indexByNodeId($r['nodes']);

        $this->assertCount(3, $r['nodes']);
        $this->assertSame(MgmShowParser::STATUS_STARTED, $byId[2]['status']);
        $this->assertSame('UNKNOWN', $byId[3]['status']);
        $this->assertSame(MgmShowParser::STATUS_CONNECTED, $byId[1]['status']);
    }

    public function testNormalizesCrlfAndStrayWhitespace(): void
    {
        $output = "Connected to Management Server at: 10.68.68.244:1186\r\n"
            . "[ndbd(NDB)] 1 node(s)\r\n"
            . "  id=2 @10.68.68.245 (ndb-8.4.9, Nodegroup: 0)\r\n"
            . "[ndb_mgmd(MGM)] 1 node(s)\r\n"
            . "id=1 @10.68.68.244 (ndb-8.4.9)\r\n";

        $r = MgmShowParser::parse($output);
        $byId = self::indexByNodeId($r['nodes']);

        $this->assertCount(2, $r['nodes']);
        $this->assertSame(MgmShowParser::STATUS_STARTED, $byId[2]['status']);
        $this->assertSame(0, $byId[2]['node_group']);
    }

    public function testSchemaContainsNdbClusterTables(): void
    {
        // Pin the lot 1 schema migration (it has to land at the same
        // time as the parser to be useful).
        $migration = (string) file_get_contents(
            dirname(__DIR__, 3) . '/sql/incremental_v2/20260507_ndb_cluster_schema.sql'
        );

        $this->assertNotSame('', $migration, 'Lot-1 migration file must be in the tree.');

        foreach (
            [
                'CREATE TABLE IF NOT EXISTS `ndb_cluster`',
                'CREATE TABLE IF NOT EXISTS `ndb_node`',
                'CREATE TABLE IF NOT EXISTS `ndb_cluster__mysql_server`',
                "ENUM('mgmd','data','sql')",
                'is_ndb_cluster_node',
                'fk_ndb_node_cluster',
                'fk_ndb_cluster_mysql_server',
            ] as $needle
        ) {
            $this->assertStringContainsString($needle, $migration);
        }
    }

    /**
     * @param list<array{ndb_node_id:int}> $nodes
     * @return array<int,array<string,mixed>>
     */
    private static function indexByNodeId(array $nodes): array
    {
        $out = [];
        foreach ($nodes as $n) {
            $out[$n['ndb_node_id']] = $n;
        }
        return $out;
    }
}
