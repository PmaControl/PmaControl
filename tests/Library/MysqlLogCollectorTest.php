<?php

namespace App\Tests\Library;

use App\Library\MysqlLogCollector;
use PHPUnit\Framework\TestCase;

final class MysqlLogCollectorTest extends TestCase
{
    public function testStorageDirectoryNameMapsExpectedFolders(): void
    {
        $this->assertSame('log_error', MysqlLogCollector::getStorageDirectoryName(MysqlLogCollector::LOG_TYPE_ERROR));
        $this->assertSame('log_slow_query', MysqlLogCollector::getStorageDirectoryName(MysqlLogCollector::LOG_TYPE_SLOW_QUERY));
        $this->assertSame('sql_error_log', MysqlLogCollector::getStorageDirectoryName(MysqlLogCollector::LOG_TYPE_SQL_ERROR));
        $this->assertSame('general_log', MysqlLogCollector::getStorageDirectoryName(MysqlLogCollector::LOG_TYPE_GENERAL));
        $this->assertSame('journalctl', MysqlLogCollector::getStorageDirectoryName(MysqlLogCollector::LOG_TYPE_OOM));
    }

    public function testResolveLogSourcesKeepsGeneralLogDisabledByDefault(): void
    {
        $sources = MysqlLogCollector::resolveLogSources([
            'log_error' => '/var/log/mysql/error.log',
            'slow_query_log_file' => '/var/log/mysql/slow.log',
            'general_log_file' => '/var/log/mysql/general.log',
            'general_log' => 'OFF',
            'sql_error_log_filename' => '/var/log/mysql/sql_errors.log',
            'plugins_json' => json_encode([
                ['PLUGIN_NAME' => 'SQL_ERROR_LOG', 'PLUGIN_STATUS' => 'ACTIVE'],
            ]),
        ]);

        $types = array_column($sources, 'log_type');

        $this->assertContains(MysqlLogCollector::LOG_TYPE_ERROR, $types);
        $this->assertContains(MysqlLogCollector::LOG_TYPE_SLOW_QUERY, $types);
        $this->assertContains(MysqlLogCollector::LOG_TYPE_SQL_ERROR, $types);
        $this->assertNotContains(MysqlLogCollector::LOG_TYPE_GENERAL, $types);
        $this->assertContains(MysqlLogCollector::LOG_TYPE_OOM, $types);
    }

    public function testResolveLogSourcesRequiresActiveSqlErrorLogPlugin(): void
    {
        $sources = MysqlLogCollector::resolveLogSources([
            'sql_error_log_filename' => '/var/log/mysql/sql_errors.log',
            'plugins_json' => json_encode([
                ['PLUGIN_NAME' => 'SQL_ERROR_LOG', 'PLUGIN_STATUS' => 'DISABLED'],
            ]),
        ]);

        $types = array_column($sources, 'log_type');
        $this->assertNotContains(MysqlLogCollector::LOG_TYPE_SQL_ERROR, $types);
    }

    public function testSplitSqlErrorLogEntriesSeparatesConcatenatedEntries(): void
    {
        $blob = '2026-03-18 18:22:31 root[root] @ localhost [] WARNING 1265: first broken SQL NUL2026-03-18 18:22:31 root[root] @ localhost [] WARNING 1265: second broken SQL';

        $entries = MysqlLogCollector::splitSqlErrorLogEntries($blob);

        $this->assertCount(2, $entries);
        $this->assertStringStartsWith('2026-03-18 18:22:31 root[root] @ localhost [] WARNING 1265:', $entries[0]);
        $this->assertStringStartsWith('2026-03-18 18:22:31 root[root] @ localhost [] WARNING 1265:', $entries[1]);
    }

    public function testBuildFileEventsParsesSqlErrorEntryFields(): void
    {
        $events = MysqlLogCollector::buildFileEvents(
            1,
            MysqlLogCollector::LOG_TYPE_SQL_ERROR,
            '/var/log/mysql/sql_errors.log',
            12,
            0,
            "2026-03-18 18:22:31 root[root] @ localhost [] WARNING 1265: Data truncated for column 'x'"
        );

        $this->assertCount(1, $events);
        $this->assertSame('2026-03-18 18:22:31', $events[0]['event_time']);
        $this->assertSame('root', $events[0]['user_name']);
        $this->assertSame('localhost', $events[0]['host_name']);
        $this->assertSame('WARNING', $events[0]['level']);
        $this->assertSame('1265', $events[0]['error_code']);
    }

    public function testParseMysqlLogEntryParsesEventSchedulerErrorLogLine(): void
    {
        $parsed = MysqlLogCollector::parseMysqlLogEntry(
            "2026-03-20  1:06:08 46868 [ERROR] Event Scheduler: [root@localhost][pmacontrol.evt_purge_binlogs] Unknown column 'File_size' in 'SELECT'"
        );

        $this->assertSame('2026-03-20 01:06:08', $parsed['event_time']);
        $this->assertSame('ERROR', $parsed['level']);
        $this->assertSame('46868', $parsed['error_code']);
        $this->assertSame('root', $parsed['user_name']);
        $this->assertSame('localhost', $parsed['host_name']);
        $this->assertStringContainsString("Unknown column 'File_size' in 'SELECT'", (string)$parsed['message']);
    }

    public function testParseMysqlLogEntryParsesEventSchedulerNoteLogLine(): void
    {
        $parsed = MysqlLogCollector::parseMysqlLogEntry(
            '2026-03-20  1:16:08 103090 [Note] Event Scheduler: [root@localhost][pmacontrol.evt_purge_binlogs] At line 14 in pmacontrol.purge_binlogs_if_needed'
        );

        $this->assertSame('2026-03-20 01:16:08', $parsed['event_time']);
        $this->assertSame('NOTE', $parsed['level']);
        $this->assertNull($parsed['error_code']);
        $this->assertSame('thread 103090', $parsed['process_name']);
        $this->assertSame('root', $parsed['user_name']);
        $this->assertSame('localhost', $parsed['host_name']);
        $this->assertStringContainsString('At line 14 in pmacontrol.purge_binlogs_if_needed', (string)$parsed['message']);
    }

    public function testParseMysqlLogEntryParsesClassicMysqlErrorCode(): void
    {
        $parsed = MysqlLogCollector::parseMysqlLogEntry(
            "2026-03-20 12:00:00 1062 [ERROR] Duplicate entry 'x' for key 'PRIMARY'"
        );

        $this->assertSame('2026-03-20 12:00:00', $parsed['event_time']);
        $this->assertSame('ERROR', $parsed['level']);
        $this->assertSame('1062', $parsed['error_code']);
        $this->assertNull($parsed['process_name']);
    }

    public function testParseMysqlLogEntryParsesClassicMysqlDeadlockCode(): void
    {
        $parsed = MysqlLogCollector::parseMysqlLogEntry(
            '2026-03-20 12:00:01 1213 [ERROR] Deadlock found when trying to get lock; try restarting transaction'
        );

        $this->assertSame('1213', $parsed['error_code']);
        $this->assertNull($parsed['process_name']);
    }

    public function testParseMysqlLogEntryParsesSqlstateNumericCode(): void
    {
        $parsed = MysqlLogCollector::parseMysqlLogEntry(
            "2026-03-20 12:00:02 23000 [ERROR] Integrity constraint violation"
        );

        $this->assertSame('23000', $parsed['error_code']);
        $this->assertNull($parsed['process_name']);
    }

    public function testParseMysqlLogEntryParsesSqlstateAlphaNumericCode(): void
    {
        $parsed = MysqlLogCollector::parseMysqlLogEntry(
            '2026-03-20 12:00:03 HY000 [ERROR] Generic SQL error'
        );

        $this->assertSame('HY000', $parsed['error_code']);
        $this->assertNull($parsed['process_name']);
    }

    public function testParseMysqlLogEntryParsesSqlstateSyntaxCode(): void
    {
        $parsed = MysqlLogCollector::parseMysqlLogEntry(
            '2026-03-20 12:00:04 42000 [ERROR] You have an error in your SQL syntax'
        );

        $this->assertSame('42000', $parsed['error_code']);
        $this->assertNull($parsed['process_name']);
    }

    public function testParseMysqlLogEntryParsesAbortedConnectionAsThreadIdNotErrorCode(): void
    {
        $parsed = MysqlLogCollector::parseMysqlLogEntry(
            "2026-03-20  1:08:05 1214 [Warning] Aborted connection 947512 to db: 'pmacontrol' user: 'pmacontrol' host: '127.0.0.1' (Got timeout reading communication packets)"
        );

        $this->assertSame('2026-03-20 01:08:05', $parsed['event_time']);
        $this->assertSame('WARNING', $parsed['level']);
        $this->assertSame('thread 947512', $parsed['process_name']);
        $this->assertNull($parsed['error_code']);
        $this->assertSame('pmacontrol', $parsed['user_name']);
        $this->assertSame('127.0.0.1', $parsed['host_name']);
        $this->assertStringContainsString('Got timeout reading communication packets', (string)$parsed['message']);

        $meta = json_decode((string)$parsed['meta_json'], true);
        $this->assertSame(947512, $meta['thread_id']);
        $this->assertSame('pmacontrol', $meta['db_name']);
    }

    public function testParseMysqlLogEntryParsesAbortedConnectionWithSameCodeAndThreadId(): void
    {
        $parsed = MysqlLogCollector::parseMysqlLogEntry(
            "2026-03-19 23:49:21 760423 [Warning] Aborted connection 760423 to db: 'pmacontrol' user: 'pmacontrol' host: '127.0.0.1' (Got an error reading communication packets)"
        );

        $this->assertSame('2026-03-19 23:49:21', $parsed['event_time']);
        $this->assertSame('WARNING', $parsed['level']);
        $this->assertSame('thread 760423', $parsed['process_name']);
        $this->assertNull($parsed['error_code']);
        $this->assertSame('pmacontrol', $parsed['user_name']);
        $this->assertSame('127.0.0.1', $parsed['host_name']);
        $this->assertStringContainsString('Got an error reading communication packets', (string)$parsed['message']);

        $meta = json_decode((string)$parsed['meta_json'], true);
        $this->assertSame(760423, $meta['thread_id']);
    }

    public function testBuildFileEventsPropagatesAbortedConnectionThreadIdToProcessColumn(): void
    {
        $events = MysqlLogCollector::buildFileEvents(
            1,
            MysqlLogCollector::LOG_TYPE_ERROR,
            '/srv/mysql/log/error.log',
            12,
            0,
            "2026-03-20  0:32:33 947515 [Warning] Aborted connection 947515 to db: 'pmacontrol' user: 'pmacontrol' host: '127.0.0.1' (Got timeout reading communication packets)"
        );

        $this->assertCount(1, $events);
        $this->assertSame('thread 947515', $events[0]['process_name']);
        $this->assertNull($events[0]['error_code']);

        $meta = json_decode((string)$events[0]['meta_json'], true);
        $this->assertSame('pmacontrol', $meta['db_name']);
    }

    public function testParseMysqlLogEntryParsesAbortedConnectionDatabaseName(): void
    {
        $parsed = MysqlLogCollector::parseMysqlLogEntry(
            "2026-03-20  0:32:33 947557 [Warning] Aborted connection 947557 to db: 'pmacontrol' user: 'pmacontrol' host: '127.0.0.1' (Got timeout reading communication packets)"
        );

        $meta = json_decode((string)$parsed['meta_json'], true);

        $this->assertSame('thread 947557', $parsed['process_name']);
        $this->assertSame('pmacontrol', $parsed['user_name']);
        $this->assertSame('127.0.0.1', $parsed['host_name']);
        $this->assertSame('pmacontrol', $meta['db_name']);
        $this->assertSame(947557, $meta['thread_id']);
    }

    public function testParseMysqlLogEntryTreatsSixDigitHeaderAsProcess(): void
    {
        $parsed = MysqlLogCollector::parseMysqlLogEntry(
            '2026-03-20 12:00:05 947557 [Warning] Generic message'
        );

        $this->assertSame('thread 947557', $parsed['process_name']);
        $this->assertNull($parsed['error_code']);
    }

    public function testParseMysqlLogEntryParsesVersionBannerLine(): void
    {
        $parsed = MysqlLogCollector::parseMysqlLogEntry(
            "2026-03-20  1:21:48 Version: '10.11.16-MariaDB-deb12-log'  socket: '/var/run/mysqld/mysqld.sock'  port: 3306  mariadb.org binary distribution"
        );

        $this->assertSame('2026-03-20 01:21:48', $parsed['event_time']);
        $this->assertNull($parsed['level']);
        $this->assertNull($parsed['error_code']);
        $this->assertStringContainsString("Version: '10.11.16-MariaDB-deb12-log'", (string)$parsed['message']);
    }

    public function testSplitErrorLogEntriesKeepsMultilineMariaDbEntryTogether(): void
    {
        $blob = implode("\n", [
            '2026-03-20  1:21:48 103090 [Note] InnoDB: Pending Write: 0',
            'Pending Read : 0',
            '-------------------',
            '2026-03-20  1:22:01 103091 [ERROR] next entry',
        ]) . "\n";

        $entries = MysqlLogCollector::splitErrorLogEntries($blob);

        $this->assertCount(2, $entries);
        $this->assertStringContainsString("Pending Read : 0\n-------------------", $entries[0]);
        $this->assertStringStartsWith('2026-03-20  1:22:01 103091 [ERROR] next entry', $entries[1]);
    }

    public function testParseMysqlLogEntryParsesMultilineInnodbNoteWithCodeZero(): void
    {
        $line = implode("\n", [
            '2026-03-06 20:05:33 0 [Note] InnoDB: LSN age parameters',
            '-------------------',
            'Current Age   : 5604 : 0%',
            'Max Age(Async): 703904948',
            'Max Age(Sync) : 804462797',
            'Capacity      : 894633984',
            '-------------------',
        ]);

        $parsed = MysqlLogCollector::parseMysqlLogEntry($line);

        $this->assertSame('2026-03-06 20:05:33', $parsed['event_time']);
        $this->assertSame('NOTE', $parsed['level']);
        $this->assertSame('0', $parsed['error_code']);
        $this->assertStringContainsString('InnoDB: LSN age parameters', (string)$parsed['message']);
        $this->assertStringContainsString('Capacity      : 894633984', (string)$parsed['message']);
    }

    public function testBuildOomEventsCapturesRocksdbHighAndMariadbdLines(): void
    {
        $journal = implode("\n", [
            '2026-03-18T04:06:54+0100 ist-pmacontrol kernel: rocksdb:high invoked oom-killer: gfp_mask=0xcc0(GFP_KERNEL), order=0, oom_score_adj=0',
            '2026-03-18T04:06:54+0100 ist-pmacontrol kernel: Memory cgroup out of memory: Killed process 3844940 (mariadbd) total-vm:15421732kB, anon-rss:10404520kB',
        ]);

        $events = MysqlLogCollector::buildOomEvents(1, $journal);

        $this->assertCount(2, $events);
        $this->assertSame(MysqlLogCollector::LOG_TYPE_OOM, $events[0]['log_type']);
        $this->assertSame('rocksdb:high', $events[0]['process_name']);
        $this->assertSame('mariadbd', $events[1]['process_name']);
    }

    public function testAggregateCountsGroupsByDay(): void
    {
        $events = [
            ['event_time' => '2026-03-18 04:06:54'],
            ['event_time' => '2026-03-18 05:06:54'],
            ['event_time' => '2026-03-19 04:02:34'],
        ];

        $result = MysqlLogCollector::aggregateCounts($events, 'day');

        $this->assertSame([
            '2026-03-18 00:00:00' => 2,
            '2026-03-19 00:00:00' => 1,
        ], $result);
    }

    public function testPrepareLocalFilePartsSplitsByDateForErrorLog(): void
    {
        $content = implode("\n", [
            '2026-03-19 23:59:58 10 [ERROR] first',
            '2026-03-20  0:00:01 11 [Warning] second',
        ]) . "\n";

        $parts = MysqlLogCollector::prepareLocalFileParts(
            1,
            MysqlLogCollector::LOG_TYPE_ERROR,
            '/srv/mysql/log/error.log',
            42,
            0,
            $content
        );

        $this->assertCount(2, $parts);
        $this->assertSame('2026-03-19', $parts[0]['date_key']);
        $this->assertSame('2026-03-20', $parts[1]['date_key']);
        $this->assertSame('/srv/mysql/log/error.log', $parts[0]['source_name']);
    }
}
