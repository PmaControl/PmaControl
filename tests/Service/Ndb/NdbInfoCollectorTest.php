<?php

declare(strict_types=1);

use App\Service\Ndb\NdbInfoCollector;
use PHPUnit\Framework\TestCase;

final class NdbInfoCollectorTest extends TestCase
{
    public function testAggregatesMemoryusageAndUptimeIntoNdbNodeUpdates(): void
    {
        $sqlNode = new NdbInfoCollectorFakeDb([
            'SELECT node_id, memory_type, used, total FROM ndbinfo.memoryusage' => [
                ['node_id' => 2, 'memory_type' => 'Data memory',         'used' => 200 * 1024 * 1024, 'total' => 1024 * 1024 * 1024],
                ['node_id' => 2, 'memory_type' => 'Index memory',        'used' =>  50 * 1024 * 1024, 'total' => 256  * 1024 * 1024],
                ['node_id' => 2, 'memory_type' => 'Long message buffer', 'used' =>   2 * 1024 * 1024, 'total' =>  64  * 1024 * 1024],
                ['node_id' => 3, 'memory_type' => 'Data memory',         'used' => 180 * 1024 * 1024, 'total' => 1024 * 1024 * 1024],
            ],
            'SELECT node_id, uptime, status FROM ndbinfo.nodes' => [
                ['node_id' => 2, 'uptime' => 7200, 'status' => 'STARTED'],
                ['node_id' => 3, 'uptime' => 7100, 'status' => 'STARTED'],
            ],
        ]);
        $pmac = new NdbInfoCollectorFakeDb();

        $r = (new NdbInfoCollector())->collect($sqlNode, $pmac, 7);

        $this->assertSame([], $r['errors']);
        // 2 memory updates + 2 uptime updates
        $this->assertSame(4, $r['updated']);
        $this->assertCount(4, $pmac->queries);

        // Memory aggregation for node 2: 200 + 50 + 2 = 252 MB used / 1344 MB total
        $this->assertStringContainsString(
            "`memory_used_mb` = 252, `memory_total_mb` = 1344, `data_memory_used_mb` = 200, `index_memory_used_mb` = 50",
            $pmac->queries[0]
        );
        $this->assertStringContainsString('`id_ndb_cluster` = 7 AND `ndb_node_id` = 2', $pmac->queries[0]);

        // Uptime line lands as a separate UPDATE
        $this->assertStringContainsString(
            'UPDATE `ndb_node` SET `uptime_sec` = 7200',
            $pmac->queries[2]
        );
    }

    public function testCapturesNdbinfoErrorsWithoutCrashing(): void
    {
        $sqlNode = new NdbInfoCollectorFakeDb([
            // Throws on the memoryusage query, succeeds (empty) on nodes
            'SELECT node_id, memory_type, used, total FROM ndbinfo.memoryusage' => 'THROW',
            'SELECT node_id, uptime, status FROM ndbinfo.nodes' => [],
        ]);
        $pmac = new NdbInfoCollectorFakeDb();

        $r = (new NdbInfoCollector())->collect($sqlNode, $pmac, 7);

        $this->assertCount(1, $r['errors']);
        $this->assertStringContainsString('ndbinfo.memoryusage failed', $r['errors'][0]);
        $this->assertSame(0, $r['updated']);
    }

    public function testBuildMemoryUpdateSqlPinsTheExactColumnSet(): void
    {
        $sql = NdbInfoCollector::buildMemoryUpdateSql(
            7,
            2,
            ['used_bytes' => 200 * 1024 * 1024, 'total_bytes' => 1024 * 1024 * 1024,
             'data_used_bytes' => 200 * 1024 * 1024, 'index_used_bytes' => 50 * 1024 * 1024],
            new NdbInfoCollectorFakeDb()
        );

        // Must update ALL 4 memory columns, scoped to (cluster, node).
        foreach (['memory_used_mb', 'memory_total_mb', 'data_memory_used_mb', 'index_memory_used_mb'] as $col) {
            $this->assertStringContainsString('`' . $col . '` =', $sql);
        }
        $this->assertStringContainsString('WHERE `id_ndb_cluster` = 7 AND `ndb_node_id` = 2', $sql);
    }
}

final class NdbInfoCollectorFakeDb
{
    /** @var array<string,array<int,array<string,mixed>>|string> */
    private array $responses;

    /** @var list<string> */
    public array $queries = [];

    /** @var array<int,array<int,array<string,mixed>>> indexed by result-id */
    private array $resultBacks = [];
    private int $nextResultId = 1;

    public function __construct(array $responses = [])
    {
        $this->responses = $responses;
    }

    public function sql_query(string $sql)
    {
        if (!isset($this->responses[$sql])) {
            // Pretend WRITE: just record and return true.
            $this->queries[] = $sql;
            return true;
        }

        $resp = $this->responses[$sql];
        if ($resp === 'THROW') {
            throw new RuntimeException('mocked failure for ' . $sql);
        }

        $rid = $this->nextResultId++;
        $this->resultBacks[$rid] = $resp;
        return $rid;
    }

    public function sql_fetch_array($result, int $mode)
    {
        if (!isset($this->resultBacks[$result])) {
            return null;
        }
        $row = array_shift($this->resultBacks[$result]);
        return $row ?: null;
    }

    public function sql_real_escape_string(string $v): string
    {
        return addslashes($v);
    }
}
