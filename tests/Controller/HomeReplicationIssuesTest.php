<?php

declare(strict_types=1);

use App\Controller\Home;
use PHPUnit\Framework\TestCase;

/**
 * Issue #1210 — pin the contract for the home replication issues
 * dashboard: 5 fine-grained buckets, dedup signature, and the
 * builder shape consumed by the view.
 */
final class HomeReplicationIssuesTest extends TestCase
{
    public function testIoErrorBucketsOnLastIoError(): void
    {
        self::assertSame('io_error', Home::classifyReplicationChannelDetail([
            'slave_io_running'  => 'No',
            'slave_sql_running' => 'Yes',
            'last_io_error'     => 'Got fatal error 1236 from master',
        ]));
    }

    public function testSqlErrorBucketsOnLastSqlError(): void
    {
        self::assertSame('sql_error', Home::classifyReplicationChannelDetail([
            'slave_io_running'  => 'Yes',
            'slave_sql_running' => 'No',
            'last_sql_error'    => 'Duplicate entry for key PRIMARY',
        ]));
    }

    public function testStoppedBucketsOnBothNoWithoutError(): void
    {
        self::assertSame('stopped', Home::classifyReplicationChannelDetail([
            'slave_io_running'  => 'No',
            'slave_sql_running' => 'No',
            'last_io_error'     => '',
            'last_sql_error'    => '',
        ]));
    }

    public function testHalfStoppedWithoutErrorSurfacesAsTheDownThread(): void
    {
        self::assertSame('io_error', Home::classifyReplicationChannelDetail([
            'slave_io_running'  => 'No',
            'slave_sql_running' => 'Yes',
            'last_io_error'     => '',
            'last_sql_error'    => '',
        ]));
        self::assertSame('sql_error', Home::classifyReplicationChannelDetail([
            'slave_io_running'  => 'Yes',
            'slave_sql_running' => 'No',
            'last_io_error'     => '',
            'last_sql_error'    => '',
        ]));
    }

    public function testLagBucketsOnThresholds(): void
    {
        self::assertSame('lag_critical', Home::classifyReplicationChannelDetail([
            'slave_io_running'      => 'Yes',
            'slave_sql_running'     => 'Yes',
            'seconds_behind_master' => '301',
        ]));
        self::assertSame('lag_warning', Home::classifyReplicationChannelDetail([
            'slave_io_running'      => 'Yes',
            'slave_sql_running'     => 'Yes',
            'seconds_behind_master' => '120',
        ]));
        self::assertSame('ok', Home::classifyReplicationChannelDetail([
            'slave_io_running'      => 'Yes',
            'slave_sql_running'     => 'Yes',
            'seconds_behind_master' => '5',
        ]));
        self::assertSame('ok', Home::classifyReplicationChannelDetail([
            'slave_io_running'      => 'Yes',
            'slave_sql_running'     => 'Yes',
            'seconds_behind_master' => '0',
        ]));
    }

    public function testReplicaSyntaxFieldsAlsoRecognised(): void
    {
        // MySQL 8.0.22+ uses Replica_*/Source_* — same buckets.
        self::assertSame('lag_critical', Home::classifyReplicationChannelDetail([
            'replica_io_running'    => 'Yes',
            'replica_sql_running'   => 'Yes',
            'seconds_behind_source' => '999',
        ]));
        self::assertSame('io_error', Home::classifyReplicationChannelDetail([
            'replica_io_running'  => 'No',
            'replica_sql_running' => 'Yes',
            'last_io_error'       => 'Network is unreachable',
        ]));
    }

    public function testNormalizeSignatureCollapsesDigitsAndQuotes(): void
    {
        $a = Home::normalizeReplicationErrorSignature("Got fatal error 1236 from master at log file 'mysql-bin.001234' position 67890123");
        $b = Home::normalizeReplicationErrorSignature("Got fatal error 9999 from master at log file 'mysql-bin.999999' position 1");
        self::assertSame($a, $b, 'identical templated errors must collapse to the same signature');
        self::assertNotSame('', $a);
    }

    public function testBuilderGroupsChannelsByBucketAndAttachesHostname(): void
    {
        $slaveData = [
            7 => ['@slave' => [
                ''     => ['slave_io_running' => 'Yes', 'slave_sql_running' => 'Yes', 'seconds_behind_master' => '5'],
                'src2' => ['slave_io_running' => 'No',  'slave_sql_running' => 'Yes', 'last_io_error' => 'Network is unreachable'],
            ]],
            8 => ['@slave' => [
                '' => ['slave_io_running' => 'Yes', 'slave_sql_running' => 'No', 'last_sql_error' => "Duplicate entry '42-X' for key 'PRIMARY'"],
            ]],
            9 => ['@slave' => [
                '' => ['slave_io_running' => 'Yes', 'slave_sql_running' => 'Yes', 'seconds_behind_source' => '450'],
            ]],
        ];
        $names = [
            7 => ['display_name' => 'srv-7',  'hostname' => 'host-7'],
            8 => ['display_name' => 'srv-8',  'hostname' => 'host-8'],
            9 => ['display_name' => 'srv-9',  'hostname' => 'host-9'],
        ];

        $out = Home::buildReplicationIssues($slaveData, $names);

        // Bucket order is the display order — must always include all 5 keys.
        self::assertSame(
            ['io_error', 'sql_error', 'stopped', 'lag_critical', 'lag_warning'],
            array_keys($out)
        );

        self::assertCount(1, $out['io_error']);
        self::assertSame(7, $out['io_error'][0]['server_id']);
        self::assertSame('srv-7', $out['io_error'][0]['hostname']);
        self::assertSame('src2', $out['io_error'][0]['connection_name']);

        self::assertCount(1, $out['sql_error']);
        self::assertSame(8, $out['sql_error'][0]['server_id']);

        self::assertCount(1, $out['lag_critical']);
        self::assertSame(9, $out['lag_critical'][0]['server_id']);
        self::assertSame(450, $out['lag_critical'][0]['lag']);

        self::assertSame([], $out['stopped']);
        self::assertSame([], $out['lag_warning']);

        // The "ok" channel on server 7 must NOT appear in any bucket.
        $allRows = array_merge(...array_values($out));
        foreach ($allRows as $row) {
            self::assertFalse($row['server_id'] === 7 && $row['connection_name'] === '',
                'ok channels must not show up in the issues dashboard');
        }
    }

    public function testBuilderEmitsSignatureForDedup(): void
    {
        $slaveData = [
            10 => ['@slave' => ['' => ['slave_io_running' => 'No', 'slave_sql_running' => 'Yes', 'last_io_error' => 'Got fatal error 1236 at pos 4567']]],
            11 => ['@slave' => ['' => ['slave_io_running' => 'No', 'slave_sql_running' => 'Yes', 'last_io_error' => 'Got fatal error 9999 at pos 88888']]],
        ];
        $names = [
            10 => ['display_name' => 'a'],
            11 => ['display_name' => 'b'],
        ];
        $out = Home::buildReplicationIssues($slaveData, $names);
        self::assertCount(2, $out['io_error']);
        self::assertSame(
            $out['io_error'][0]['message_signature'],
            $out['io_error'][1]['message_signature'],
            'two errors that differ only in numeric positions must share a signature'
        );
    }
}
