<?php

declare(strict_types=1);

use App\Service\Ndb\NdbCollector;
use App\Service\Ndb\NdbMgmShowFetcher;
use PHPUnit\Framework\TestCase;

final class NdbCollectorTest extends TestCase
{
    public function testCollectsLabClusterAndUpsertsEachNode(): void
    {
        $fakeFetcher = new NdbMgmShowFetcher(static function (): array {
            return [
                'stdout' => <<<'TXT'
Connected to Management Server at: 10.68.68.244:1186
[ndbd(NDB)] 2 node(s)
id=2    @10.68.68.245  (mysql-8.4.9 ndb-8.4.9, Nodegroup: 0, *)
id=3    @10.68.68.246  (mysql-8.4.9 ndb-8.4.9, Nodegroup: 0)

[ndb_mgmd(MGM)] 1 node(s)
id=1    @10.68.68.244  (mysql-8.4.9 ndb-8.4.9)

[mysqld(API)] 1 node(s)
id=4    @10.68.68.247  (mysql-8.4.9 ndb-8.4.9)
TXT,
                'exit_code' => 0,
            ];
        });

        $db = new NdbCollectorTestFakeDb();
        $col = new NdbCollector($fakeFetcher);

        $r = $col->collect($db, [
            'id'               => 7,
            'mgmd_host'        => '10.68.68.244',
            'mgmd_port'        => 1186,
            'ssh_target_host'  => '',
            'command_template' => '',
        ]);

        $this->assertTrue($r['reachable']);
        $this->assertSame(4, $r['parsed_nodes']);
        $this->assertSame(4, $r['upserted']);
        $this->assertSame([], $r['errors']);

        // 4 INSERT…ON DUPLICATE KEY UPDATE + 1 cluster version UPDATE.
        $this->assertCount(5, $db->queries);
        foreach (array_slice($db->queries, 0, 4) as $sql) {
            $this->assertStringContainsString('INSERT INTO `ndb_node`', $sql);
            $this->assertStringContainsString('ON DUPLICATE KEY UPDATE', $sql);
            $this->assertStringContainsString('`id_ndb_cluster`, `ndb_node_id`', $sql);
        }
        $this->assertStringContainsString("UPDATE `ndb_cluster` SET `ndb_version` = 'ndb-8.4.9' WHERE `id` = 7", $db->queries[4]);
    }

    public function testRejectsCollectionForClusterWithoutId(): void
    {
        $db = new NdbCollectorTestFakeDb();
        $r  = (new NdbCollector(new NdbMgmShowFetcher(static fn() => ['stdout' => '', 'exit_code' => 0])))
            ->collect($db, ['mgmd_host' => 'h', 'mgmd_port' => 1186]);

        $this->assertFalse($r['reachable']);
        $this->assertSame(0, $r['parsed_nodes']);
        $this->assertSame(0, $r['upserted']);
        $this->assertNotEmpty($r['errors']);
        $this->assertSame([], $db->queries);
    }

    public function testBuildUpsertSqlEmitsNullsAndPrimaryFlagCorrectly(): void
    {
        $db = new NdbCollectorTestFakeDb();

        // mgmd: no node_group, no ndb_version when disconnected
        $sql = NdbCollector::buildUpsertSql(7, [
            'ndb_node_id' => 1,
            'role'        => 'mgmd',
            'ip'          => '10.68.68.244',
            'status'      => 'CONNECTED',
            'node_group'  => null,
            'is_primary'  => false,
            'ndb_version' => null,
        ], $db);

        $this->assertStringContainsString('NULL, ', $sql); // node_group
        $this->assertStringContainsString(', NULL)', $sql); // ndb_version
        // is_primary=0 must appear, not the literal string "false"
        $this->assertStringContainsString(', 0, NULL)', $sql);

        // data primary: is_primary=1 + node_group=0 + version
        $sql = NdbCollector::buildUpsertSql(7, [
            'ndb_node_id' => 2,
            'role'        => 'data',
            'ip'          => '10.68.68.245',
            'status'      => 'STARTED',
            'node_group'  => 0,
            'is_primary'  => true,
            'ndb_version' => 'ndb-8.4.9',
        ], $db);

        $this->assertStringContainsString(", 0, 1, 'ndb-8.4.9')", $sql);
        // Sanity on the ON DUPLICATE clause — every column rewritten EXCEPT
        // ndb_version which uses COALESCE so an unparseable line during a
        // brief disconnect doesn't wipe the last known version.
        $this->assertStringContainsString(
            'COALESCE(VALUES(`ndb_version`), `ndb_version`)',
            $sql
        );
    }
}

final class NdbCollectorTestFakeDb
{
    /** @var list<string> */
    public array $queries = [];

    public function sql_query(string $sql): bool
    {
        $this->queries[] = $sql;
        return true;
    }

    public function sql_real_escape_string(string $value): string
    {
        return addslashes($value);
    }
}
