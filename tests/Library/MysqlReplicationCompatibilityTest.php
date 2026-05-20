<?php

declare(strict_types=1);

use Glial\Sgbd\Sql\Mysql\Mysql;
use PHPUnit\Framework\TestCase;

final class MysqlReplicationCompatibilityTest extends TestCase
{
    public function testNormalizeReplicationStatusRowAddsLegacyAliasesForMysql80ReplicaFields(): void
    {
        $row = Mysql::normalizeReplicationStatusRow([
            'Source_Host' => 'mysql-primary.example.net',
            'Source_Port' => '3310',
            'Replica_IO_Running' => 'Yes',
            'Replica_SQL_Running' => 'Yes',
            'Replica_IO_State' => 'Waiting for source to send event',
            'Replica_SQL_Running_State' => 'Replica has read all relay log',
            'Source_Log_File' => 'binlog.000123',
            'Read_Source_Log_Pos' => '456',
            'Relay_Source_Log_File' => 'binlog.000122',
            'Exec_Source_Log_Pos' => '321',
            'Seconds_Behind_Source' => '4',
            'Channel_Name' => 'channel_1',
        ]);

        $this->assertSame('mysql-primary.example.net', $row['Master_Host']);
        $this->assertSame('3310', $row['Master_Port']);
        $this->assertSame('Yes', $row['Slave_IO_Running']);
        $this->assertSame('Yes', $row['Slave_SQL_Running']);
        $this->assertSame('Waiting for source to send event', $row['Slave_IO_State']);
        $this->assertSame('Replica has read all relay log', $row['Slave_SQL_Running_State']);
        $this->assertSame('binlog.000123', $row['Master_Log_File']);
        $this->assertSame('456', $row['Read_Master_Log_Pos']);
        $this->assertSame('binlog.000122', $row['Relay_Master_Log_File']);
        $this->assertSame('321', $row['Exec_Master_Log_Pos']);
        $this->assertSame('4', $row['Seconds_Behind_Master']);
        $this->assertSame('channel_1', $row['Connection_name']);
    }

    public function testNormalizeReplicationStatusRowAddsSourceAliasesForLegacyFields(): void
    {
        $row = Mysql::normalizeReplicationStatusRow([
            'Master_Host' => '10.0.0.10',
            'Master_Port' => '3306',
            'Slave_IO_Running' => 'Connecting',
            'Slave_SQL_Running' => 'No',
        ]);

        $this->assertSame('10.0.0.10', $row['Source_Host']);
        $this->assertSame('3306', $row['Source_Port']);
        $this->assertSame('Connecting', $row['Replica_IO_Running']);
        $this->assertSame('No', $row['Replica_SQL_Running']);
    }
}
