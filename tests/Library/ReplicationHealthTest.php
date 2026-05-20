<?php

declare(strict_types=1);

use App\Library\ReplicationFiltersAudit;
use App\Library\ReplicationHealth;
use PHPUnit\Framework\TestCase;

final class ReplicationHealthTest extends TestCase
{
    public function testHealthyReplicaScoresNinetyOrMore(): void
    {
        $score = ReplicationHealth::evaluate([
            'Slave_IO_Running' => 'Yes',
            'Slave_SQL_Running' => 'Yes',
            'Seconds_Behind_Master' => '1',
            'Relay_Log_Purge' => 'ON',
            'Master_SSL_Allowed' => 'Yes',
        ], [
            'errant_gtid_set' => '',
            'filters' => ['rows' => []],
            'semi_sync' => ['healthy' => true, 'message' => 'ok'],
            'ssl' => ['healthy' => true, 'message' => 'ok'],
            'stuck_sql' => ['stuck' => false, 'message' => 'ok'],
            'heartbeat' => ['healthy' => true, 'message' => 'ok'],
        ]);

        $this->assertSame('healthy', $score['status']);
        $this->assertGreaterThanOrEqual(90, $score['score']);
        $this->assertTrue($score['lights']['gtid']['ok']);
    }

    public function testErrantGtidAndSqlErrorAreCritical(): void
    {
        $score = ReplicationHealth::evaluate([
            'Slave_IO_Running' => 'Yes',
            'Slave_SQL_Running' => 'No',
            'Last_SQL_Error' => 'duplicate key',
            'Seconds_Behind_Master' => '0',
            'Relay_Log_Purge' => 'ON',
        ], [
            'errant_gtid_set' => '0-99-42:1-3',
            'filters' => ['rows' => []],
            'semi_sync' => ['healthy' => true, 'message' => 'ok'],
            'ssl' => ['healthy' => true, 'message' => 'ok'],
            'stuck_sql' => ['stuck' => true, 'message' => 'same position'],
            'heartbeat' => ['healthy' => true, 'message' => 'ok'],
        ]);

        $this->assertSame('critical', $score['status']);
        $this->assertFalse($score['lights']['sql']['ok']);
        $this->assertFalse($score['lights']['gtid']['ok']);
        $this->assertFalse($score['lights']['workers']['ok']);
    }

    public function testFilterAuditFlagsStatementBinlogFormat(): void
    {
        $audit = ReplicationFiltersAudit::audit([
            'Replicate_Do_DB' => 'app',
        ], ['binlog_format' => 'STATEMENT']);

        $this->assertTrue($audit['active']);
        $this->assertSame('critical', $audit['rows'][0]['severity']);
    }
}
