<?php

declare(strict_types=1);

use App\Controller\Query;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class QueryRunQuerySecurityTest extends TestCase
{
    public function testHttpRunQueryRequestIsRejected(): void
    {
        $outcome = Query::evaluateRunQueryRequest(['1', base64_encode('DROP TABLE mysql.user'), '1'], false);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Query runner is CLI-only.', $outcome['body']);
        $this->assertNull($outcome['payload']);
    }

    public function testCliRunQueryRequestNormalizesIntegerArguments(): void
    {
        $outcome = Query::evaluateRunQueryRequest(['0012', '34', '5'], true);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([
            'id_mysql_server' => 12,
            'job_id' => 34,
            'run_number' => 5,
        ], $outcome['payload']);
    }

    #[DataProvider('invalidRunQueryArgumentsProvider')]
    public function testCliRunQueryRequestRejectsInvalidArguments(array $param): void
    {
        $outcome = Query::evaluateRunQueryRequest($param, true);

        $this->assertSame(400, $outcome['status']);
        $this->assertStringContainsString('Query runQuery <id_mysql_server> <job_id> <run_number>', $outcome['body']);
        $this->assertNull($outcome['payload']);
    }

    public static function invalidRunQueryArgumentsProvider(): array
    {
        return [
            'missing all' => [[]],
            'sql in server id' => [['1 OR 1=1', '2', '3']],
            'zero server id' => [['0', '2', '3']],
            'base64 sql instead of job id' => [['1', base64_encode('SELECT SLEEP(1)'), '3']],
            'negative run number' => [['1', '2', '-3']],
            'empty run number' => [['1', '2', '']],
        ];
    }

    #[DataProvider('allowedSetDefaultQueryProvider')]
    public function testSetDefaultWorkerAllowListAcceptsGeneratedQueries(string $query): void
    {
        $this->assertTrue(Query::isSetDefaultWorkerQueryAllowed($query));
    }

    public static function allowedSetDefaultQueryProvider(): array
    {
        return [
            'numeric default' => ['ALTER TABLE `app`.`users` ALTER COLUMN `id_status` SET DEFAULT 0;'],
            'empty string default' => ['ALTER TABLE `app`.`users` ALTER COLUMN `name` SET DEFAULT \'\';'],
            'date default' => ['ALTER TABLE `app`.`events` ALTER COLUMN `created_at` SET DEFAULT \'0000-00-00 00:00:00\';'],
            'hyphenated identifier from information schema' => ['ALTER TABLE `app-prod`.`table-1` ALTER COLUMN `status` SET DEFAULT \'enabled\';'],
        ];
    }

    #[DataProvider('rejectedSetDefaultQueryProvider')]
    public function testSetDefaultWorkerAllowListRejectsArbitrarySql(string $query): void
    {
        $this->assertFalse(Query::isSetDefaultWorkerQueryAllowed($query));
    }

    public static function rejectedSetDefaultQueryProvider(): array
    {
        return [
            'drop table' => ['DROP TABLE mysql.user;'],
            'multi statement' => ['ALTER TABLE `app`.`users` ALTER COLUMN `name` SET DEFAULT \'\'; SELECT SLEEP(1);'],
            'wrong alter operation' => ['ALTER TABLE `app`.`users` ADD COLUMN `owned` int;'],
            'line comment' => ['ALTER TABLE `app`.`users` ALTER COLUMN `name` SET DEFAULT \'x\'; -- comment'],
            'block comment' => ['ALTER TABLE `app`.`users` ALTER COLUMN `name` SET DEFAULT \'x\' /* comment */;'],
            'semicolon inside default' => ['ALTER TABLE `app`.`users` ALTER COLUMN `name` SET DEFAULT \'x;y\';'],
            'quote inside default' => ['ALTER TABLE `app`.`users` ALTER COLUMN `name` SET DEFAULT \'O\\\'Reilly\';'],
        ];
    }

    public function testRunQueryCommandUsesOnlyWorkerIntegerArguments(): void
    {
        $command = Query::buildRunQueryCommand(12, 34, 5);
        $joined = implode(' ', $command);

        $this->assertSame(PHP_BINARY, $command[0]);
        $this->assertContains('Query', $command);
        $this->assertContains('runQuery', $command);
        $this->assertContains('12', $command);
        $this->assertContains('34', $command);
        $this->assertContains('5', $command);
        $this->assertStringNotContainsString('ALTER TABLE', $joined);
        $this->assertStringNotContainsString(base64_encode('ALTER TABLE `app`.`users`'), $joined);
        $this->assertStringNotContainsString(';', $joined);
    }

    public function testRunQuerySourceKeepsGuardBeforeDatabaseAccess(): void
    {
        $source = (string)file_get_contents(__DIR__ . '/../../App/Controller/Query.php');
        $runQueryPosition = strpos($source, 'public function runQuery($param)');
        $guardPosition = strpos($source, "evaluateRunQueryRequest(\$param, defined('IS_CLI') && IS_CLI === true)", $runQueryPosition);
        $dbPosition = strpos($source, '$db = Mysql::getDbLink($id_mysql_server);', $runQueryPosition);

        $this->assertIsInt($runQueryPosition);
        $this->assertIsInt($guardPosition);
        $this->assertIsInt($dbPosition);
        $this->assertLessThan($dbPosition, $guardPosition);
        $this->assertStringNotContainsString('base64_decode($param[1])', $source);
        $this->assertStringNotContainsString('base64_encode($default)', $source);
        $this->assertStringNotContainsString('whereis php', $source);
    }
}
