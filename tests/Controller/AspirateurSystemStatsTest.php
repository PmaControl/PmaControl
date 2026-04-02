<?php

namespace Tests\Controller;

use App\Controller\Aspirateur;
use PHPUnit\Framework\TestCase;

class AspirateurSystemStatsTest extends TestCase
{
    private function invokePrivate(object $object, string $method, array $arguments = [])
    {
        $reflection = new \ReflectionClass($object);
        $instanceMethod = $reflection->getMethod($method);
        $instanceMethod->setAccessible(true);

        return $instanceMethod->invokeArgs($object, $arguments);
    }

    private function newAspirateur(): Aspirateur
    {
        $reflection = new \ReflectionClass(Aspirateur::class);

        return $reflection->newInstanceWithoutConstructor();
    }

    public function testParseProcSystemCountersReturnsExpectedScalarCounters(): void
    {
        $aspirateur = $this->newAspirateur();
        $raw = <<<STAT
cpu  122 0 44 999 10 0 2 0 0 0
cpu0 61 0 22 499 5 0 1 0 0 0
intr 123456 1 2 3 4
ctxt 654321
processes 456789
procs_running 3
procs_blocked 1
softirq 98765 0 1 2 3
STAT;

        $stats = $this->invokePrivate($aspirateur, 'parseProcSystemCounters', [$raw]);

        $this->assertMatchesRegularExpression('/^\{.*"intr":\[[0-9,]+\].*"ctxt":654321.*"softirq":\[[0-9,]+\].*\}$/s', (string)$stats['proc_stat_detail']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['system_interrupts_total']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['system_context_switches_total']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['system_process_forks_total']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['system_processes_running']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['system_processes_blocked']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['system_softirq_total']);
    }

    public function testGetNetworkStatsReturnsExpectedCountersAndJsonPayloads(): void
    {
        $aspirateur = $this->newAspirateur();
        $ssh = new class {
            public function exec(string $command): string
            {
                if ($command === 'cat /proc/net/dev') {
                    return <<<DEV
Inter-|   Receive                                                |  Transmit
 face |bytes    packets errs drop fifo frame compressed multicast|bytes    packets errs drop fifo colls carrier compressed
    lo: 1000 10 0 0 0 0 0 0 1000 10 0 0 0 0 0 0
  eth0: 123456 1200 2 3 0 0 0 4 654321 1300 5 6 0 0 0 0
DEV;
                }

                if ($command === 'cat /proc/net/snmp') {
                    return <<<SNMP
Tcp: RtoAlgorithm RtoMin RtoMax MaxConn ActiveOpens PassiveOpens AttemptFails EstabResets CurrEstab InSegs OutSegs RetransSegs InErrs OutRsts
Tcp: 1 200 120000 -1 10 20 1 2 3 100 200 7 8 9
Udp: InDatagrams NoPorts InErrors OutDatagrams RcvbufErrors SndbufErrors
Udp: 100 0 4 120 0 0
SNMP;
                }

                if ($command === 'cat /proc/net/netstat') {
                    return <<<NETSTAT
TcpExt: SyncookiesSent SyncookiesRecv
TcpExt: 0 0
NETSTAT;
                }

                if ($command === 'cat /proc/net/sockstat 2>/dev/null') {
                    return <<<SOCK
sockets: used 377
TCP: inuse 45 orphan 0 tw 2 alloc 54 mem 4
UDP: inuse 7 mem 1
SOCK;
                }

                if ($command === 'cat /proc/net/sockstat6 2>/dev/null') {
                    return "TCP6: inuse 1\nUDP6: inuse 0\n";
                }

                return '';
            }
        };

        $stats = $this->invokePrivate($aspirateur, 'getNetworkStats', [$ssh]);

        $this->assertMatchesRegularExpression('/^\{.*"eth0":\{.*"rx_bytes":123456.*"tx_bytes":654321.*\}\}$/s', (string)$stats['network_detail']);
        $this->assertMatchesRegularExpression('/^\{.*"snmp":\{.*"Tcp":\{.*"RetransSegs":7.*\}\}.*\}$/s', (string)$stats['network_protocol_detail']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['network_rx_bytes_total']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['network_tx_bytes_total']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['network_rx_packets_total']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['network_tx_packets_total']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['network_rx_errors_total']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['network_tx_errors_total']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['network_rx_drop_total']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['network_tx_drop_total']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['network_tcp_retrans_segs_total']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['network_tcp_in_errs_total']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['network_tcp_out_rsts_total']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['network_udp_in_errors_total']);
    }

    public function testGetDiskIoStatsReturnsExpectedCountersAndJsonPayloads(): void
    {
        $aspirateur = $this->newAspirateur();
        $ssh = new class {
            public function exec(string $command): string
            {
                if ($command === 'cat /proc/diskstats') {
                    return <<<DISK
   8       0 sda 100 2 200 30 300 4 500 60 2 90 120 0 0 0 0 0 0
   8       1 sda1 1 0 2 0 3 0 4 0 0 0 0 0 0 0 0 0 0
 259       0 nvme0n1 500 0 4000 200 700 0 8000 300 0 100 500 0 0 0 0 0 0
DISK;
                }

                return '';
            }
        };

        $stats = $this->invokePrivate($aspirateur, 'getDiskIoStats', [$ssh]);

        $this->assertMatchesRegularExpression('/^\{.*"sda":\{.*"read_bytes":102400.*\}.*"nvme0n1":\{.*"write_bytes":4096000.*\}.*\}$/s', (string)$stats['disk_io_detail']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['disk_reads_completed_total']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['disk_writes_completed_total']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['disk_read_bytes_total']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['disk_write_bytes_total']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['disk_io_time_ms_total']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['disk_weighted_io_time_ms_total']);
    }

    public function testParseProcessStatesReturnsExpectedStateCounters(): void
    {
        $aspirateur = $this->newAspirateur();
        $raw = "R\nS\nS\nD\nT\nZ\nI\n";

        $stats = $this->invokePrivate($aspirateur, 'parseProcessStates', [$raw]);

        $this->assertMatchesRegularExpression('/^\{.*"R":1.*"S":2.*"D":1.*"T":1.*"Z":1.*"I":1.*\}$/s', (string)$stats['process_state_detail']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['process_total']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['process_running']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['process_sleeping']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['process_disk_sleep']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['process_stopped']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['process_zombie']);
        $this->assertMatchesRegularExpression('/^\d+$/', (string)$stats['process_idle']);
    }

    public function testParseDfDisksReturnsInformationSchemaCompatibleKeys(): void
    {
        $aspirateur = $this->newAspirateur();
        $raw = <<<DF
Filesystem     Type     1B-blocks        Used    Available Use% Mounted on
/dev/vda1      ext4   21474836480  5368709120  16106127360  25% /
/dev/vdb1      xfs   107374182400 21474836480  85899345920  20% /srv/mysql
DF;

        $rows = $this->invokePrivate($aspirateur, 'parseDfDisks', [$raw]);

        $this->assertCount(2, $rows);
        $this->assertMatchesRegularExpression('/^\\/dev\\/[a-z0-9]+$/i', (string)$rows[0]['Filesystem']);
        $this->assertMatchesRegularExpression('/^[a-z0-9_-]+$/i', (string)$rows[0]['Type']);
        $this->assertMatchesRegularExpression('/^\\d+$/', (string)$rows[0]['Total']);
        $this->assertMatchesRegularExpression('/^\\d+$/', (string)$rows[0]['Size']);
        $this->assertMatchesRegularExpression('/^\\d+$/', (string)$rows[0]['Used']);
        $this->assertMatchesRegularExpression('/^\\d+$/', (string)$rows[0]['Avail']);
        $this->assertMatchesRegularExpression('/^\\d+$/', (string)$rows[0]['Available']);
        $this->assertMatchesRegularExpression('/^\\d+%$/', (string)$rows[0]['Use%']);
        $this->assertMatchesRegularExpression('/^\\//', (string)$rows[0]['Mounted']);
        $this->assertMatchesRegularExpression('/^\\//', (string)$rows[0]['Mounted on']);
    }
}
