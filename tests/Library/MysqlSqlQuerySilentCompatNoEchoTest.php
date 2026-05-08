<?php

declare(strict_types=1);

use App\Library\Mysql;
use PHPUnit\Framework\TestCase;

/**
 * Pin that `Mysql::sqlQuerySilentCompat` swallows whatever the
 * underlying driver echoes to stdout, even if it then throws (#891).
 *
 * Glial's `Sgbd/Sql/Mysql/Mysql.php::_query()` `echo`s the backtrace
 * files and the mysqli error message before rethrowing GLI-562.
 * That bytes-on-the-wire leak landed in the response body for the
 * MysqlServer/processlist page on a ProxySQL backend; the silent
 * wrapper must capture and discard it.
 */
final class MysqlSqlQuerySilentCompatNoEchoTest extends TestCase
{
    public function testSwallowsEchoFromSilentDriver(): void
    {
        // Driver exposes sql_query_silent that returns false but echoes
        // backtrace-like noise — the exact failure mode Glial ships.
        $db = new MysqlSqlQuerySilentCompatFakeNoisyDb(
            silentReturnsFalse: true,
            stdoutNoise: "/srv/www/glial/Glial/Sgbd/Sql/Sql.php\n"
                . "Erreur : ProxySQL Error: connection is locked to hostgroup 1 but trying to reach hostgroup 2",
        );

        ob_start();
        $result = Mysql::sqlQuerySilentCompat($db, 'SELECT 1');
        $leaked = ob_get_clean();

        $this->assertFalse($result, 'Driver returned false; silent compat must propagate that');
        $this->assertSame('', $leaked, 'No bytes from the driver may reach the response body');
    }

    public function testSwallowsEchoFromDriverWithoutSilentMethodThatThrows(): void
    {
        // No `sql_query_silent`; sql_query echoes then throws — the
        // try/catch path. Echoes still must not leak.
        $db = new MysqlSqlQuerySilentCompatFakeBareThrowingDb(
            stdoutNoise: 'GLI-562 fake echo from _query',
        );

        ob_start();
        $result = Mysql::sqlQuerySilentCompat($db, 'SELECT 1');
        $leaked = ob_get_clean();

        $this->assertFalse($result);
        $this->assertSame('', $leaked);
    }

    public function testSuccessfulSilentQueryStillReturnsResult(): void
    {
        $db = new MysqlSqlQuerySilentCompatFakeNoisyDb(silentReturnsFalse: false);

        ob_start();
        $result = Mysql::sqlQuerySilentCompat($db, 'SELECT 1');
        $leaked = ob_get_clean();

        $this->assertSame('mock-result', $result);
        $this->assertSame('', $leaked);
    }
}

final class MysqlSqlQuerySilentCompatFakeNoisyDb
{
    public function __construct(
        private readonly bool $silentReturnsFalse = false,
        private readonly string $stdoutNoise = '',
    ) {
    }

    public function sql_query_silent(string $sql, string $table = '', string $type = ''): mixed
    {
        if ($this->stdoutNoise !== '') {
            echo $this->stdoutNoise;
        }
        if ($this->silentReturnsFalse) {
            return false;
        }
        return 'mock-result';
    }
}

/**
 * No `sql_query_silent` method — exercises the try/catch fallback.
 */
final class MysqlSqlQuerySilentCompatFakeBareThrowingDb
{
    public function __construct(
        private readonly string $stdoutNoise = '',
    ) {
    }

    public function sql_query(string $sql, string $table = '', string $type = ''): mixed
    {
        if ($this->stdoutNoise !== '') {
            echo $this->stdoutNoise;
        }
        throw new \RuntimeException('GLI-562 simulated throw');
    }
}
