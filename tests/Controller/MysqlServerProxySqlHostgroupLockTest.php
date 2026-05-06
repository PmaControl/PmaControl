<?php

declare(strict_types=1);

use App\Controller\MysqlServer;
use PHPUnit\Framework\TestCase;

final class MysqlServerProxySqlHostgroupLockTest extends TestCase
{
    private function invokePrivateStatic(string $method, array $arguments = [])
    {
        $reflectionMethod = new ReflectionMethod(MysqlServer::class, $method);

        return $reflectionMethod->invokeArgs(null, $arguments);
    }

    public function testDetectsProxySqlHostgroupLockError(): void
    {
        $message = 'ProxySQL Error: connection is locked to hostgroup 1 but trying to reach hostgroup 2';

        $this->assertTrue($this->invokePrivateStatic('isProxySqlHostgroupLockError', [$message]));
        $this->assertTrue($this->invokePrivateStatic('isProxySqlHostgroupError', [$message]));
    }

    public function testDetectsProxySqlHostgroupMaxConnectTimeout(): void
    {
        $message = 'Max connect timeout reached while reaching hostgroup 10 after 11905ms';

        $this->assertTrue($this->invokePrivateStatic('isProxySqlHostgroupError', [$message]));
    }

    public function testIgnoresOtherSqlErrors(): void
    {
        $message = 'Table information_schema.innodb_trx does not exist';

        $this->assertFalse($this->invokePrivateStatic('isProxySqlHostgroupLockError', [$message]));
        $this->assertFalse($this->invokePrivateStatic('isProxySqlHostgroupError', [$message]));
    }

    public function testKeepsPrimaryResultWhenRichProcesslistQueryWorks(): void
    {
        $primary = new MysqlServerProcesslistFakeDb(['rich-result']);

        $query = $this->invokePrivateStatic(
            'executeProcesslistQuery',
            [
                $primary,
                'SELECT rich_processlist',
                42,
                function (): void {
                    $this->fail('The dedicated processlist connection should not be requested.');
                },
            ]
        );

        $this->assertSame('rich-result', $query['result']);
        $this->assertSame($primary, $query['db']);
        $this->assertSame('SELECT rich_processlist', $query['sql']);
        $this->assertSame('', $query['fallback_reason']);
        $this->assertSame(['SELECT rich_processlist'], $primary->queries);
    }

    public function testRetriesRichProcesslistQueryOnDedicatedConnection(): void
    {
        $hostgroupError = new RuntimeException(
            'ProxySQL Error: connection is locked to hostgroup 1 but trying to reach hostgroup 2'
        );
        $primary = new MysqlServerProcesslistFakeDb([$hostgroupError]);
        $processlist = new MysqlServerProcesslistFakeDb(['rich-result']);

        $query = $this->invokePrivateStatic(
            'executeProcesslistQuery',
            [
                $primary,
                'SELECT rich_processlist',
                42,
                function (int $idMysqlServer) use ($processlist) {
                    $this->assertSame(42, $idMysqlServer);

                    return $processlist;
                },
            ]
        );

        $this->assertSame('rich-result', $query['result']);
        $this->assertSame($processlist, $query['db']);
        $this->assertSame('SELECT rich_processlist', $query['sql']);
        $this->assertStringContainsString('primary_hostgroup_lock', $query['fallback_reason']);
        $this->assertSame(['SELECT rich_processlist'], $primary->queries);
        $this->assertSame(['SELECT rich_processlist'], $processlist->queries);
    }

    public function testFallsBackToShowFullProcesslistWhenDedicatedConnectionFails(): void
    {
        $hostgroupError = new RuntimeException(
            'ProxySQL Error: connection is locked to hostgroup 1 but trying to reach hostgroup 2'
        );
        $primary = new MysqlServerProcesslistFakeDb([$hostgroupError, 'fallback-result']);
        $processlist = new MysqlServerProcesslistFakeDb([new RuntimeException('processlist retry failed')]);

        $query = $this->invokePrivateStatic(
            'executeProcesslistQuery',
            [
                $primary,
                'SELECT rich_processlist',
                42,
                function () use ($processlist) {
                    return $processlist;
                },
            ]
        );

        $this->assertSame('fallback-result', $query['result']);
        $this->assertSame($primary, $query['db']);
        $this->assertSame('SHOW FULL PROCESSLIST', $query['sql']);
        $this->assertStringContainsString('processlist_connection_failed', $query['fallback_reason']);
        $this->assertSame(['SELECT rich_processlist', 'SHOW FULL PROCESSLIST'], $primary->queries);
        $this->assertSame(['SELECT rich_processlist'], $processlist->queries);
    }

    public function testReturnsFalseWhenShowFullProcesslistHitsHostgroupTimeout(): void
    {
        $hostgroupError = new RuntimeException(
            'Max connect timeout reached while reaching hostgroup 10 after 11905ms'
        );
        $primary = new MysqlServerProcesslistFakeDb([$hostgroupError, $hostgroupError]);
        $processlist = new MysqlServerProcesslistFakeDb([new RuntimeException('processlist retry failed')]);

        $query = $this->invokePrivateStatic(
            'executeProcesslistQuery',
            [
                $primary,
                'SELECT rich_processlist',
                42,
                function () use ($processlist) {
                    return $processlist;
                },
            ]
        );

        $this->assertFalse($query['result']);
        $this->assertSame('SHOW FULL PROCESSLIST', $query['sql']);
        $this->assertStringContainsString('fallback_hostgroup_unavailable', $query['fallback_reason']);
        $this->assertSame(['SELECT rich_processlist', 'SHOW FULL PROCESSLIST'], $primary->queries);
    }

    public function testBuildsOfflineDiagnosticsForProxySqlHostgroupFailure(): void
    {
        $data = ['offline_diagnostics' => []];
        $method = new ReflectionMethod(MysqlServer::class, 'addProxySqlHostgroupOfflineDiagnostics');
        $method->invokeArgs(null, [
            &$data,
            264,
            ['ip' => '10.68.68.238', 'port' => '6033'],
            'Max connect timeout reached while reaching hostgroup 10 after 11905ms',
        ]);

        $this->assertSame(0, $data['offline_diagnostics'][264]['mysql_available']);
        $this->assertSame(1, $data['offline_diagnostics'][264]['port_open']);
        $this->assertSame('10.68.68.238', $data['offline_diagnostics'][264]['ip']);
        $this->assertSame(6033, $data['offline_diagnostics'][264]['port']);
        $this->assertStringContainsString('ProxySQL backend hostgroup unavailable', $data['offline_diagnostics'][264]['mysql_error']);
    }
}

final class MysqlServerProcesslistFakeDb
{
    public array $queries = [];

    private array $responses;

    public function __construct(array $responses)
    {
        $this->responses = $responses;
    }

    public function sql_query(string $sql)
    {
        $this->queries[] = $sql;

        if ($this->responses === []) {
            return true;
        }

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        return $response;
    }
}
