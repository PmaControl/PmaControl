<?php

declare(strict_types=1);

namespace Tests\Library\Database;

use App\Library\Database\Renamer;
use PHPUnit\Framework\TestCase;

final class RenamerTest extends TestCase
{
    public function testRenameMovesObjectsInExpectedOrder(): void
    {
        $db = new RenamerFakeDb();

        ob_start();
        $renamed = Renamer::rename($db, 'old_db', 'new_db', false, false, 12, false);
        ob_end_clean();

        $this->assertSame(1, $renamed);
        $this->assertSame([
            'query:CREATE DATABASE `new_db` DEFAULT CHARACTER SET utf8mb4',
            'select:old_db',
            'query:DROP TRIGGER `old_db`.`trg_a`;',
            'query:DROP VIEW `old_db`.`v1`;',
            'query:DROP function `old_db`.`fn_a`;',
            'query:DROP procedure `old_db`.`proc_a`;',
            'query:RENAME TABLE `old_db`.`t1` TO `new_db`.`t1`;',
            'select:new_db',
            'multi:CREATE FUNCTION `fn_a`() RETURNS int RETURN 1;',
            'query:CREATE VIEW `new_db`.`v1` AS SELECT * FROM `new_db`.`t1`',
            'multi:CREATE PROCEDURE `proc_a`() SELECT 1;',
            'multi:CREATE TRIGGER `new_db`.`trg_a` AFTER INSERT ON `new_db`.`t1` FOR EACH ROW SET @new_db.x = 1;',
            'query:DROP DATABASE `old_db`;',
        ], $db->events);
    }

    public function testRenameRejectsExistingTargetWithoutForce(): void
    {
        $db = new RenamerFakeDb(['target_exists' => true]);

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(2518);
        $this->expectExceptionMessage("The target database exist already : 'new_db'");

        Renamer::rename($db, 'old_db', 'new_db', false, false, 12, false);
    }

    public function testRenameDryRunPrintsStatementsWithoutExecutingMutations(): void
    {
        $db = new RenamerFakeDb();

        ob_start();
        $renamed = Renamer::rename($db, 'old_db', 'new_db', false, false, 12, true);
        $output = (string) ob_get_clean();

        $this->assertSame(1, $renamed);
        $this->assertSame([], $db->executedMutations);
        $this->assertStringContainsString('CREATE DATABASE `new_db` DEFAULT CHARACTER SET utf8mb4;', $output);
        $this->assertStringContainsString('DROP TRIGGER `old_db`.`trg_a`;', $output);
        $this->assertStringContainsString('RENAME TABLE `old_db`.`t1` TO `new_db`.`t1`;', $output);
        $this->assertStringContainsString('USE `new_db`;', $output);
        $this->assertStringContainsString('CREATE VIEW `new_db`.`v1` AS SELECT * FROM `new_db`.`t1`;', $output);
    }

    public function testQuoteIdentifierEscapesBackticks(): void
    {
        $this->assertSame('`db``name`', Renamer::quoteIdentifier('db`name'));
    }

    public function testSqlLiteralUsesAdapterEscaping(): void
    {
        $db = new class {
            public function sql_real_escape_string(string $value): string
            {
                return str_replace("'", "\\'", $value);
            }
        };

        $this->assertSame("'old\\'db'", Renamer::sqlLiteral($db, "old'db"));
    }
}

final class RenamerFakeDb
{
    public array $events = [];
    public array $executedMutations = [];

    private bool $targetExists;

    public function __construct(array $options = [])
    {
        $this->targetExists = !empty($options['target_exists']);
    }

    public function sql_query(string $sql): RenamerFakeResult
    {
        if ($this->isMutation($sql)) {
            $this->events[] = 'query:'.$sql;
            $this->executedMutations[] = $sql;
        }

        return new RenamerFakeResult($this->rowsFor($sql));
    }

    public function sql_multi_query(string $sql): bool
    {
        $this->events[] = 'multi:'.$sql;
        $this->executedMutations[] = $sql;

        return true;
    }

    public function sql_select_db(string $database): bool
    {
        $this->events[] = 'select:'.$database;

        return true;
    }

    public function sql_real_escape_string(string $value): string
    {
        return str_replace(["\\", "'"], ["\\\\", "\\'"], $value);
    }

    public function sql_num_rows(RenamerFakeResult $result): int
    {
        return count($result->rows);
    }

    public function sql_fetch_object(RenamerFakeResult $result): ?object
    {
        $row = $result->next();

        return $row === null ? null : (object) $row;
    }

    public function sql_fetch_array(RenamerFakeResult $result, int $resultType = MYSQLI_BOTH): ?array
    {
        return $result->next();
    }

    public function checkVersion(array $version): bool
    {
        return false;
    }

    public function sql_error(): string
    {
        return '';
    }

    private function rowsFor(string $sql): array
    {
        if (str_contains($sql, 'FROM `information_schema`.`SCHEMATA`') && str_contains($sql, "'new_db'")) {
            return [['cpt' => $this->targetExists ? 1 : 0]];
        }

        if (str_contains($sql, 'SELECT `DEFAULT_CHARACTER_SET_NAME` FROM `information_schema`.`SCHEMATA`')) {
            return [['DEFAULT_CHARACTER_SET_NAME' => 'utf8mb4']];
        }

        if (str_contains($sql, 'select trigger_name from information_schema.triggers')) {
            return [['trigger_name' => 'trg_a']];
        }

        if (stripos($sql, 'show function status') !== false) {
            return [['Name' => 'fn_a']];
        }

        if (stripos($sql, 'show procedure status') !== false) {
            return [['Name' => 'proc_a']];
        }

        if (str_contains($sql, 'SHOW EVENTS FROM')) {
            return [];
        }

        if (str_contains($sql, 'SHOW TRIGGERS FROM')) {
            return [['Trigger' => 'trg_a']];
        }

        if (str_contains($sql, 'SHOW CREATE TRIGGER')) {
            return [[
                'SQL Original Statement' => 'CREATE TRIGGER `old_db`.`trg_a`'
                    .' AFTER INSERT ON `old_db`.`t1` FOR EACH ROW SET @old_db.x = 1',
            ]];
        }

        if (str_contains($sql, 'information_schema.VIEWS AS views')) {
            return [];
        }

        if (str_contains($sql, "AND `TABLE_TYPE` = 'VIEW'")) {
            return [['table_name' => 'v1', 'TABLE_NAME' => 'v1']];
        }

        if (str_contains($sql, "AND TABLE_TYPE='VIEW'")) {
            return [['table_name' => 'v1', 'TABLE_NAME' => 'v1']];
        }

        if (str_contains($sql, 'SHOW CREATE VIEW')) {
            return [[
                'Create View' => 'CREATE VIEW `old_db`.`v1` AS SELECT * FROM `old_db`.`t1`',
            ]];
        }

        if (str_contains($sql, 'SHOW CREATE function')) {
            return [[
                'Create Function' => 'CREATE FUNCTION `fn_a`() RETURNS int RETURN 1',
            ]];
        }

        if (str_contains($sql, 'SHOW CREATE procedure')) {
            return [[
                'Create Procedure' => 'CREATE PROCEDURE `proc_a`() SELECT 1',
            ]];
        }

        if (str_contains($sql, "AND `TABLE_TYPE` = 'BASE TABLE'")) {
            return [['table_name' => 't1', 'TABLE_NAME' => 't1']];
        }

        if (str_contains($sql, "AND TABLE_TYPE='BASE TABLE'")) {
            return [['table_name' => 't1', 'TABLE_NAME' => 't1']];
        }

        if (str_contains($sql, 'select count(1) as cpt from information_schema.tables')) {
            return [['cpt' => '0']];
        }

        return [];
    }

    private function isMutation(string $sql): bool
    {
        return preg_match('/^\s*(CREATE DATABASE|DROP |RENAME TABLE|CREATE VIEW|CREATE FUNCTION|CREATE PROCEDURE|CREATE TRIGGER|REVOKE |GRANT )/i', $sql) === 1;
    }
}

final class RenamerFakeResult
{
    public array $rows;
    private int $position = 0;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function next(): ?array
    {
        if (!isset($this->rows[$this->position])) {
            return null;
        }

        return $this->rows[$this->position++];
    }
}
