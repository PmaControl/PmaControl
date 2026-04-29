<?php

declare(strict_types=1);

use App\Library\SelectorOptions;
use PHPUnit\Framework\TestCase;

final class SelectorOptionsTest extends TestCase
{
    public function testDatabaseNamesFromConnectionMapsShowDatabasesRows(): void
    {
        $db = new SelectorOptionsFakeDb([
            'SHOW DATABASES' => [
                ['Database' => 'app'],
                ['Database' => 'logs'],
            ],
        ]);

        $this->assertSame([
            ['id' => 'app', 'libelle' => 'app'],
            ['id' => 'logs', 'libelle' => 'logs'],
        ], SelectorOptions::databaseNamesFromConnection($db));
    }

    public function testRecordedDatabasesByServerIdCastsServerIdAndUsesWhitelistedColumns(): void
    {
        $db = new SelectorOptionsFakeDb([
            'SELECT `id` AS id, `schema_name` AS libelle FROM mysql_database WHERE id_mysql_server = 12 ORDER BY `schema_name`;' => [
                ['id' => 7, 'libelle' => 'main'],
            ],
        ]);

        $this->assertSame([
            ['id' => 7, 'libelle' => 'main'],
        ], SelectorOptions::recordedDatabasesByServerId($db, '12 OR 1=1', 'id', 'schema_name', 'schema_name'));
        $this->assertSame([
            'SELECT `id` AS id, `schema_name` AS libelle FROM mysql_database WHERE id_mysql_server = 12 ORDER BY `schema_name`;',
        ], $db->queries());
    }

    public function testRecordedDatabasesByServerIdRejectsInvalidColumns(): void
    {
        $this->expectException(InvalidArgumentException::class);

        SelectorOptions::recordedDatabasesByServerId(new SelectorOptionsFakeDb(), 1, 'id', 'schema_name; DROP TABLE mysql_database');
    }

    public function testSharedDatabaseNamesByServerIdsCountsNamesPerServer(): void
    {
        $connections = [
            1 => new SelectorOptionsFakeDb([
                'SHOW DATABASES' => [
                    ['Database' => 'app'],
                    ['Database' => 'logs'],
                ],
            ]),
            2 => new SelectorOptionsFakeDb([
                'SHOW DATABASES' => [
                    ['Database' => 'app'],
                    ['Database' => 'metrics'],
                ],
            ]),
        ];

        $options = SelectorOptions::sharedDatabaseNamesByServerIds([1, 2], static function ($id) use ($connections) {
            return $connections[$id];
        });

        $this->assertSame([
            ['id' => 'app', 'libelle' => '(2/2) app'],
            ['id' => 'logs', 'libelle' => '(1/2) logs'],
            ['id' => 'metrics', 'libelle' => '(1/2) metrics'],
        ], $options);
    }

    public function testTableNamesByServerIdAndDatabaseUsesQuotedUseAndMapsTables(): void
    {
        $db = new SelectorOptionsFakeDb([], ['orders', 'users']);

        $options = SelectorOptions::tableNamesByServerIdAndDatabase(
            4,
            'main_db',
            static function () use ($db) {
                return $db;
            }
        );

        $this->assertSame([
            ['id' => 'orders', 'libelle' => 'orders'],
            ['id' => 'users', 'libelle' => 'users'],
        ], $options);
        $this->assertSame(['USE `main_db`;'], $db->queries());
    }

    public function testTableNamesByServerIdAndDatabaseRejectsUnsafeDatabaseName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        SelectorOptions::tableNamesByServerIdAndDatabase(
            4,
            'main_db; DROP DATABASE main_db',
            static function () {
                return new SelectorOptionsFakeDb();
            }
        );
    }

    public function testTimeSeriesVariablesSupportsQualifiedAndNumericIds(): void
    {
        $allDb = new SelectorOptionsFakeDb([
            'SELECT * FROM ts_variable order by `from`, `name`;' => [
                ['id' => 3, 'from' => 'mysql_global_status', 'name' => 'Threads_running'],
            ],
        ]);
        $jsonDb = new SelectorOptionsFakeDb([
            "SELECT * from ts_variable WHERE type ='JSON';" => [
                ['id' => 9, 'from' => 'sys', 'name' => 'schema_table_statistics'],
            ],
        ]);

        $this->assertSame([
            [
                'id' => 'mysql_global_status::Threads_running',
                'libelle' => 'mysql_global_status::Threads_running',
                'extra' => [
                    'data-content' => "<small class='text-muted'>mysql_global_status</small> Threads_running",
                ],
            ],
        ], SelectorOptions::timeSeriesVariables($allDb, null, 'qualified'));

        $this->assertSame([
            [
                'id' => 9,
                'libelle' => 'sys::schema_table_statistics',
                'extra' => [
                    'data-content' => "<small class='text-muted'>sys</small> schema_table_statistics",
                ],
            ],
        ], SelectorOptions::timeSeriesVariables($jsonDb, 'JSON', 'id'));
    }
}

final class SelectorOptionsFakeDb
{
    private $rowsByResult;
    private $positions = [];
    private $queries = [];
    private $tables;

    public function __construct(array $rowsByResult = [], array $tables = [])
    {
        $this->rowsByResult = $rowsByResult;
        $this->tables = $tables;
    }

    public function sql_query(string $sql)
    {
        $this->queries[] = $sql;

        return $sql;
    }

    public function sql_fetch_object($result)
    {
        $position = $this->positions[$result] ?? 0;
        $rows = $this->rowsByResult[$result] ?? [];

        if (!isset($rows[$position])) {
            return false;
        }

        $this->positions[$result] = $position + 1;

        return (object) $rows[$position];
    }

    public function getListTable(): array
    {
        return ['table' => $this->tables];
    }

    public function sql_real_escape_string(string $value): string
    {
        return addslashes($value);
    }

    public function queries(): array
    {
        return $this->queries;
    }
}
