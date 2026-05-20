<?php

declare(strict_types=1);

use App\Library\FailoverPreflight;
use App\Library\PerDatabaseLag;
use App\Library\ReplicaReconnectTracker;
use App\Library\ReplicationHeartbeatCheck;
use App\Library\ReplicationMetadataDictionary;
use App\Library\ReplicationMetadataReader;
use App\Library\ReplicationRetentionForecast;
use App\Library\ReplicationStuckSqlDetector;
use App\Library\ReplicationTopologyDot;
use App\Library\ReplicationUserSslAudit;
use App\Library\SemiSyncAckSla;
use PHPUnit\Framework\TestCase;

final class ReplicationObservabilityPrimitivesTest extends TestCase
{
    public function testStuckSqlDetectorRequiresStablePositionPastThreshold(): void
    {
        $result = ReplicationStuckSqlDetector::detect([
            'Exec_Master_Log_Pos' => 10,
            'Read_Master_Log_Pos' => 200,
        ], [
            ['date' => '2026-05-18 12:00:00', 'value' => 10],
            ['date' => '2026-05-18 12:00:40', 'value' => 10],
        ], 30, strtotime('2026-05-18 12:01:00'));

        $this->assertTrue($result['stuck']);
        $this->assertSame(60, $result['age_seconds']);
    }

    public function testHeartbeatStaleWhenOlderThanTwicePeriod(): void
    {
        $result = ReplicationHeartbeatCheck::evaluate([
            'Slave_IO_Running' => 'Yes',
            'HEARTBEAT_INTERVAL' => '30',
            'LAST_HEARTBEAT_TIMESTAMP' => '2026-05-18 12:00:00',
        ], strtotime('2026-05-18 12:02:00'));

        $this->assertSame('stale', $result['status']);
        $this->assertFalse($result['healthy']);
    }

    public function testSemiSyncRatioDetectsTimeouts(): void
    {
        $result = SemiSyncAckSla::evaluate([
            'Rpl_semi_sync_master_yes_tx' => 990,
            'Rpl_semi_sync_master_no_tx' => 10,
        ]);

        $this->assertTrue($result['configured']);
        $this->assertFalse($result['healthy']);
        $this->assertSame(0.01, $result['timeout_ratio']);
    }

    public function testSslAuditWarnsWhenDisabled(): void
    {
        $result = ReplicationUserSslAudit::audit(['Master_User' => 'repl', 'Master_SSL_Allowed' => 'No']);

        $this->assertSame('warning', $result['severity']);
        $this->assertSame('repl', $result['user']);
    }

    public function testRetentionForecastComputesEta(): void
    {
        $result = ReplicationRetentionForecast::forecast([
            'generation_rate_bytes_per_day' => 10,
            'disk_free_bytes' => 100,
            'configured_days' => 7,
        ]);

        $this->assertSame(10.0, $result['eta_full_days']);
        $this->assertSame('ok', $result['status']);
    }

    public function testPerDatabaseLagFromWorkerRows(): void
    {
        $rows = PerDatabaseLag::fromWorkerRows([
            [
                'WORKER_ID' => '2',
                'APPLYING_TRANSACTION' => 'update `app`.`t` set id=1',
                'LAST_APPLIED_TRANSACTION_END_APPLY_TIMESTAMP' => '2026-05-18 12:00:00',
            ],
        ], strtotime('2026-05-18 12:00:05'));

        $this->assertSame('app', $rows[0]['database']);
        $this->assertSame(5000, $rows[0]['lag_ms']);
    }

    public function testReconnectTrackerParsesLogAndPeaks(): void
    {
        $summary = ReplicaReconnectTracker::summarize(implode("\n", [
            '2026-05-18 12:00:01 [Note] Slave I/O thread connected to master',
            '2026-05-18 12:00:10 [Warning] Slave I/O thread lost connection to master',
        ]));

        $this->assertSame(2, $summary['total']);
        $this->assertTrue($summary['storm']);
    }

    public function testMetadataDictionaryMasksPasswords(): void
    {
        $rows = ReplicationMetadataDictionary::maskSensitiveRows([
            ['USER_PASSWORD' => 'secret', 'HOST' => 'db1'],
        ]);

        $this->assertSame('****', $rows[0]['USER_PASSWORD']);
        $this->assertNotSame('', ReplicationMetadataDictionary::tooltip('HOST'));
    }

    public function testMetadataReaderSkipsUnavailablePerformanceSchemaTables(): void
    {
        $db = new ReplicationMetadataReaderFakeDb(
            ['mysql.slave_master_info' => true],
            ['mysql.slave_master_info' => [['User_password' => 'secret', 'Host' => 'db1']]]
        );

        $payload = ReplicationMetadataReader::read($db, [
            'performance_schema.replication_connection_configuration',
            'mysql.slave_master_info',
        ]);

        $this->assertFalse($payload['performance_schema.replication_connection_configuration']['available']);
        $this->assertSame('table_not_available', $payload['performance_schema.replication_connection_configuration']['reason']);
        $this->assertTrue($payload['mysql.slave_master_info']['available']);
        $this->assertSame('****', $payload['mysql.slave_master_info']['rows'][0]['User_password']);
        $this->assertContains(
            "SELECT * FROM `mysql`.`slave_master_info` LIMIT 200",
            $db->queries
        );
        foreach ($db->queries as $query) {
            $this->assertStringNotContainsString(
                'SELECT * FROM `performance_schema`.`replication_connection_configuration`',
                $query
            );
        }
    }

    public function testFailoverPreflightBlocksErrantGtid(): void
    {
        $result = FailoverPreflight::evaluate([
            'Slave_IO_Running' => 'Yes',
            'Slave_SQL_Running' => 'Yes',
            'Seconds_Behind_Master' => '0',
            'Relay_Log_Purge' => 'ON',
        ], [
            'errant_gtid_set' => '0-99-42:1-3',
            'filters' => ['rows' => []],
            'ssl' => ['healthy' => true, 'message' => 'ok'],
            'heartbeat' => ['healthy' => true, 'message' => 'ok'],
        ]);

        $this->assertSame('blocked', $result['verdict']);
    }

    public function testTopologyDotContainsEdge(): void
    {
        $dot = ReplicationTopologyDot::build([
            ['source_id' => 1, 'source_name' => 'm', 'replica_id' => 2, 'replica_name' => 'r', 'channel' => 'c1', 'lag' => 0],
        ]);

        $this->assertStringContainsString('s1 -> s2', $dot);
        $this->assertStringContainsString('c1 lag=0s', $dot);
    }
}

final class ReplicationMetadataReaderFakeResult
{
    /**
     * @param list<array<string,mixed>> $rows
     */
    public function __construct(private array $rows)
    {
    }

    /**
     * @return array<string,mixed>|false
     */
    public function next(): array|false
    {
        $row = current($this->rows);
        if ($row === false) {
            return false;
        }
        next($this->rows);

        return $row;
    }
}

final class ReplicationMetadataReaderFakeDb
{
    /** @var list<string> */
    public array $queries = [];

    /**
     * @param array<string,bool> $available
     * @param array<string,list<array<string,mixed>>> $rows
     */
    public function __construct(private array $available, private array $rows)
    {
    }

    public function sql_real_escape_string(string $value): string
    {
        return addslashes($value);
    }

    public function sql_query_silent(string $query): ReplicationMetadataReaderFakeResult|false
    {
        $this->queries[] = $query;

        if (preg_match("/^SHOW TABLES FROM `([^`]+)` LIKE '([^']+)'$/", $query, $m)) {
            $qualified = $m[1] . '.' . stripslashes($m[2]);
            if (!($this->available[$qualified] ?? false)) {
                return new ReplicationMetadataReaderFakeResult([]);
            }

            return new ReplicationMetadataReaderFakeResult([[$m[2] => $m[2]]]);
        }

        if (preg_match('/^SELECT \* FROM `([^`]+)`\.`([^`]+)` LIMIT 200$/', $query, $m)) {
            $qualified = $m[1] . '.' . $m[2];

            return new ReplicationMetadataReaderFakeResult($this->rows[$qualified] ?? []);
        }

        return false;
    }

    /**
     * @return array<string,mixed>|false
     */
    public function sql_fetch_array(ReplicationMetadataReaderFakeResult $result, int $mode): array|false
    {
        return $result->next();
    }
}
