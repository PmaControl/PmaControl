<?php

declare(strict_types=1);

use App\Library\Security\ClusterDataCheckRequest;
use App\Library\Security\SqlReadOnlyGuard;
use Glial\Security\Csrf;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ClusterDataCheckRequestReadOnlyTest extends TestCase
{
    #[DataProvider('allowedReadOnlySqlProvider')]
    public function testReadOnlyGuardAcceptsReadStatements(string $sql): void
    {
        $this->assertTrue(SqlReadOnlyGuard::isReadOnly($sql));
    }

    public static function allowedReadOnlySqlProvider(): array
    {
        return [
            'select' => ['SELECT 1'],
            'select final semicolon' => ['SELECT 1;'],
            'select with semicolon in string' => ["SELECT '; DROP TABLE t' AS literal"],
            'show variables' => ['SHOW VARIABLES'],
            'explain select' => ['EXPLAIN SELECT * FROM mysql_server'],
            'describe table' => ['DESCRIBE mysql_server'],
            'desc table' => ['DESC mysql_server'],
            'with select' => ['WITH cte AS (SELECT 1 AS id) SELECT id FROM cte'],
        ];
    }

    #[DataProvider('blockedSqlProvider')]
    public function testReadOnlyGuardRejectsWriteOrUnsafeStatements(string $sql): void
    {
        $this->assertFalse(SqlReadOnlyGuard::isReadOnly($sql));
    }

    public static function blockedSqlProvider(): array
    {
        return [
            'update' => ['UPDATE mysql_server SET name = name'],
            'insert' => ['INSERT INTO mysql_server(id) VALUES (1)'],
            'delete' => ['DELETE FROM mysql_server'],
            'replace' => ['REPLACE INTO mysql_server(id) VALUES (1)'],
            'drop' => ['DROP TABLE mysql_server'],
            'create' => ['CREATE TABLE injected(id INT)'],
            'alter' => ['ALTER TABLE mysql_server ADD injected INT'],
            'truncate' => ['TRUNCATE TABLE mysql_server'],
            'rename' => ['RENAME TABLE mysql_server TO mysql_server_old'],
            'grant' => ['GRANT ALL ON *.* TO attacker'],
            'revoke' => ['REVOKE ALL PRIVILEGES ON *.* FROM app'],
            'set' => ['SET GLOBAL read_only = OFF'],
            'use' => ['USE mysql'],
            'kill' => ['KILL 123'],
            'lock' => ['LOCK TABLES mysql_server WRITE'],
            'unlock' => ['UNLOCK TABLES'],
            'load data' => ['LOAD DATA INFILE "/tmp/x" INTO TABLE mysql_server'],
            'flush' => ['FLUSH PRIVILEGES'],
            'reset' => ['RESET MASTER'],
            'optimize' => ['OPTIMIZE TABLE mysql_server'],
            'repair' => ['REPAIR TABLE mysql_server'],
            'analyze' => ['ANALYZE TABLE mysql_server'],
            'call' => ['CALL dangerous_procedure()'],
            'handler' => ['HANDLER mysql_server OPEN'],
            'sleep function' => ['SELECT SLEEP(60)'],
            'benchmark function' => ['SELECT BENCHMARK(1000000, SHA1("x"))'],
            'get lock function' => ['SELECT GET_LOCK("pmacontrol", 60)'],
            'replication wait function' => ['SELECT WAIT_FOR_EXECUTED_GTID_SET("0-1-1", 60)'],
            'stacked statement' => ['SELECT 1; DROP TABLE mysql_server'],
            'double semicolon' => ['SELECT 1;;'],
            'leading semicolon' => ['; SELECT 1'],
            'c comment' => ['/*x*/ SELECT 1'],
            'mysql conditional comment' => ['/*!50000 UPDATE mysql_server SET name = name */'],
            'dash comment' => ["-- x\nSELECT 1"],
            'hash comment' => ["# x\nSELECT 1"],
            'select into outfile' => ['SELECT * FROM mysql_server INTO OUTFILE "/tmp/leak"'],
            'select into dumpfile' => ['SELECT * FROM mysql_server INTO DUMPFILE "/tmp/leak"'],
            'select for update' => ['SELECT * FROM mysql_server FOR UPDATE'],
            'lock in share mode' => ['SELECT * FROM mysql_server LOCK IN SHARE MODE'],
            'mixed case write' => ['  UpDaTe mysql_server SET name = name'],
            'quoted identifier does not rescue write verb' => ['SELECT `name` FROM mysql_server; UPDATE mysql_server SET name = name'],
        ];
    }

    public function testEvaluateReturnsUnprocessableEntityForReadOnlyPolicyViolations(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'check_data_on_cluster.index');

        $outcome = ClusterDataCheckRequest::evaluate(
            $this->validPost($token, ['sql' => 'DROP TABLE mysql.user']),
            $this->sameSitePostServer(),
            $session,
            'check_data_on_cluster.index'
        );

        $this->assertSame(422, $outcome['status']);
        $this->assertSame('Statement must be read-only', $outcome['body']);
        $this->assertNull($outcome['selection']);
    }

    public function testEvaluateKeepsReadOnlyStatementsExecutable(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'check_data_on_cluster.index');

        $outcome = ClusterDataCheckRequest::evaluate(
            $this->validPost($token, ['sql' => ' SELECT 1; ']),
            $this->sameSitePostServer(),
            $session,
            'check_data_on_cluster.index'
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('SELECT 1;', $outcome['selection']['sql']);
    }

    public function testEvaluateEnforcesReducedSqlLength(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'check_data_on_cluster.index');
        $sql = 'SELECT ' . str_repeat('1', ClusterDataCheckRequest::MAX_SQL_BYTES);

        $outcome = ClusterDataCheckRequest::evaluate(
            $this->validPost($token, ['sql' => $sql]),
            $this->sameSitePostServer(),
            $session,
            'check_data_on_cluster.index'
        );

        $this->assertSame(400, $outcome['status']);
        $this->assertSame('Invalid cluster data check payload', $outcome['body']);
    }

    private function validPost(string $token, array $overrides = []): array
    {
        return array_replace_recursive([
            'mysql_cluster' => [
                'id' => '3,4',
                'database' => 'main_db',
            ],
            'sql' => 'SHOW VARIABLES',
            Csrf::DEFAULT_FIELD => $token,
        ], $overrides);
    }

    private function sameSitePostServer(): array
    {
        return [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://pmacontrol.test',
        ];
    }
}
