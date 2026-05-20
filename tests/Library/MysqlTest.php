<?php

declare(strict_types=1);

use App\Library\Mysql;
use PHPUnit\Framework\TestCase;

final class MysqlTest extends TestCase
{
    public function testExportAllUserFlattensAllGrantStatements(): void
    {
        $db = new FakeMysqlDb();

        $users = Mysql::exportAllUser($db);

        $this->assertSame([
            "GRANT USAGE ON *.* TO 'alice'@'%'",
            "GRANT SELECT ON db1.* TO 'alice'@'%'",
            "GRANT ALL PRIVILEGES ON *.* TO 'bob'@'localhost'",
        ], $users);
    }

    public function testExportUserByUserGroupsGrantsPerAccount(): void
    {
        $db = new FakeMysqlDb();

        $users = Mysql::exportUserByUser($db);

        $this->assertSame(
            ["GRANT USAGE ON *.* TO 'alice'@'%'", "GRANT SELECT ON db1.* TO 'alice'@'%'"],
            $users['alice']['%']
        );
        $this->assertSame(
            ["GRANT ALL PRIVILEGES ON *.* TO 'bob'@'localhost'"],
            $users['bob']['localhost']
        );
    }
}

final class FakeMysqlDb
{
    /** @var array<string,mixed> */
    private array $resultSets = [];

    public function __construct()
    {
        $this->resultSets = [
            'select user as user, host as host from mysql.user;' => [
                (object) ['user' => 'alice', 'host' => '%'],
                (object) ['user' => 'bob', 'host' => 'localhost'],
            ],
            'select User as user, Host as host from mysql.user;' => [
                (object) ['user' => 'alice', 'host' => '%'],
                (object) ['user' => 'bob', 'host' => 'localhost'],
            ],
            "SHOW GRANTS FOR 'alice'@'%'" => [
                ["GRANT USAGE ON *.* TO 'alice'@'%'"],
                ["GRANT SELECT ON db1.* TO 'alice'@'%'"],
            ],
            "SHOW GRANTS FOR 'bob'@'localhost'" => [
                ["GRANT ALL PRIVILEGES ON *.* TO 'bob'@'localhost'"],
            ],
        ];
    }

    public function sql_query(string $sql): string
    {
        return $sql;
    }

    public function sql_fetch_object(string $result): object|false
    {
        $row = array_shift($this->resultSets[$result]);

        return $row === null ? false : $row;
    }

    public function sql_fetch_array(string $result, int $mode): array|false
    {
        unset($mode);
        $row = array_shift($this->resultSets[$result]);

        return $row === null ? false : $row;
    }
}
