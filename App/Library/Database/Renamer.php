<?php

declare(strict_types=1);

namespace App\Library\Database;

use App\Library\Debug;
use App\Library\Mysql;
use App\Library\Security\Identifier;
use Glial\Cli\Table;

final class Renamer
{
    public static function rename(
        $db,
        string $oldDatabase,
        string $newDatabase,
        bool $adjustPrivileges = false,
        bool $forceTarget = false,
        $idMysqlServer = null,
        bool $dryRun = false
    ): int {
        self::assertDatabaseName($oldDatabase, 'old database');
        self::assertDatabaseName($newDatabase, 'new database');

        $targetExists = false;
        $sql = "SELECT count(1) as cpt FROM `information_schema`.`SCHEMATA`"
            ." WHERE `SCHEMA_NAME` = ".self::sqlLiteral($db, $newDatabase).";";
        $res = $db->sql_query($sql);

        while ($row = $db->sql_fetch_object($res)) {
            if ((int) $row->cpt > 0) {
                if ($forceTarget === false) {
                    throw new \Exception("The target database exist already : '".$newDatabase."'", 2518);
                }

                $targetExists = true;
            }
        }

        $sql = "SELECT `DEFAULT_CHARACTER_SET_NAME` FROM `information_schema`.`SCHEMATA`"
            ." WHERE `SCHEMA_NAME` = ".self::sqlLiteral($db, $oldDatabase).";";
        $res = $db->sql_query($sql);

        if ($db->sql_num_rows($res) != 1) {
            Debug::sql($sql);
            throw new \Exception("Impossible to find the database '".$oldDatabase."' to rename", 2518);
        }

        while ($row = $db->sql_fetch_object($res)) {
            if ($targetExists === false) {
                self::execute(
                    $db,
                    "CREATE DATABASE ".self::quoteIdentifier($newDatabase)
                    ." DEFAULT CHARACTER SET ".self::sanitizeCharset($row->DEFAULT_CHARACTER_SET_NAME),
                    $dryRun
                );
            }
        }

        $db->sql_select_db($oldDatabase);
        $oldCounts = self::getObjectCounts($db, $oldDatabase, $idMysqlServer);

        $triggers = self::backupAndDropTriggers($db, $oldDatabase, $newDatabase, $dryRun);
        [$views, $viewLevels] = self::backupAndDropViews($db, $oldDatabase, $newDatabase, $idMysqlServer, $dryRun);
        $functions = self::backupAndDropFunctions($db, $oldDatabase, $dryRun);
        $procedures = self::backupAndDropProcedures($db, $oldDatabase, $dryRun);

        $sql = "SELECT `table_name` FROM `information_schema`.`tables`"
            ." WHERE `table_schema` = ".self::sqlLiteral($db, $oldDatabase)
            ." AND `TABLE_TYPE` = 'BASE TABLE';";
        Debug::debug($sql);
        $res = Mysql::sqlQueryWithInformationSchemaTablesTimeout($db, $sql, $idMysqlServer, __METHOD__);

        $renamedTables = 0;
        while ($row = $db->sql_fetch_object($res)) {
            $sql = "RENAME TABLE ".self::quoteIdentifier($oldDatabase).".".self::quoteIdentifier($row->table_name)
                ." TO ".self::quoteIdentifier($newDatabase).".".self::quoteIdentifier($row->table_name).";";
            Debug::debug($sql);
            $renamedTables++;
            self::execute($db, $sql, $dryRun);
        }

        if ($dryRun) {
            self::emitPlannedRecreates($db, $newDatabase, $functions, $views, $viewLevels, $procedures, $triggers);
            return $renamedTables;
        }

        $db->sql_select_db($newDatabase);
        self::restoreFunctions($db, $functions);
        self::restoreViews($db, $views, $viewLevels);
        self::restoreProcedures($db, $procedures);
        self::restoreTriggers($db, $triggers);

        if ($adjustPrivileges) {
            foreach (self::getChangeGrant($db, $oldDatabase, $newDatabase) as $grant) {
                $db->sql_query($grant);
                echo $grant."\n";
            }
        }

        $newCounts = self::getObjectCounts($db, $newDatabase, $idMysqlServer);
        self::displayObjectComparison($oldCounts, $newCounts, $oldDatabase, $newDatabase);

        $sql = "select count(1) as cpt from information_schema.tables"
            ." where table_schema = ".self::sqlLiteral($db, $oldDatabase).";";
        $res = Mysql::sqlQueryWithInformationSchemaTablesTimeout($db, $sql, $idMysqlServer, __METHOD__);

        while ($row = $db->sql_fetch_object($res)) {
            if ($row->cpt === "0") {
                $db->sql_query("DROP DATABASE ".self::quoteIdentifier($oldDatabase).";");
            }
        }

        return $renamedTables;
    }

    public static function getObjectCounts($db, string $database, $idMysqlServer = null): array
    {
        self::assertDatabaseName($database, 'database');

        $sql = "SELECT `DEFAULT_CHARACTER_SET_NAME` FROM `information_schema`.`SCHEMATA`"
            ." WHERE `SCHEMA_NAME` = ".self::sqlLiteral($db, $database).";";
        $res = $db->sql_query($sql);

        if ($db->sql_num_rows($res) != 1) {
            Debug::sql($sql);
            throw new \Exception("Impossible to find the database '".$database."' to rename", 4576);
        }

        $queries = [
            'TRIGGER' => "select trigger_name from information_schema.triggers where trigger_schema = {DB}",
            'FUNCTION' => "show function status WHERE Db = {DB};",
            'PROCEDURE' => "show procedure status WHERE Db = {DB}",
            'TABLE' => "select TABLE_NAME from information_schema.tables where TABLE_SCHEMA = {DB}"
                ." AND TABLE_TYPE='BASE TABLE' order by TABLE_NAME;",
            'VIEW' => "select TABLE_NAME from information_schema.tables where TABLE_SCHEMA = {DB}"
                ." AND TABLE_TYPE='VIEW' order by TABLE_NAME;",
            'EVENT' => "SHOW EVENTS FROM ".self::quoteIdentifier($database),
        ];

        $data = ['result' => []];
        foreach ($queries as $key => $template) {
            $sql = str_replace('{DB}', self::sqlLiteral($db, $database), $template);
            $res = Mysql::sqlQueryWithInformationSchemaTablesTimeout($db, $sql, $idMysqlServer, __METHOD__);
            $data['result'][$key] = $db->sql_num_rows($res);
        }

        return $data;
    }

    public static function quoteIdentifier(string $identifier): string
    {
        return '`'.str_replace('`', '``', $identifier).'`';
    }

    public static function sqlLiteral($db, string $value): string
    {
        if (!method_exists($db, 'sql_real_escape_string')) {
            throw new \InvalidArgumentException('Database adapter must expose sql_real_escape_string().');
        }

        return "'".$db->sql_real_escape_string($value)."'";
    }

    private static function backupAndDropTriggers($db, string $oldDatabase, string $newDatabase, bool $dryRun): array
    {
        $sql = "SHOW TRIGGERS FROM ".self::quoteIdentifier($oldDatabase);
        $res = $db->sql_query($sql);

        $triggers = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $name = (string) $row['Trigger'];
            $sql = "SHOW CREATE TRIGGER ".self::quoteIdentifier($oldDatabase).".".self::quoteIdentifier($name);
            Debug::sql($sql);
            $createRes = $db->sql_query($sql);

            while ($createRow = $db->sql_fetch_array($createRes, MYSQLI_ASSOC)) {
                $statement = str_replace('@'.$oldDatabase.'.', '@'.$newDatabase.'.', $createRow['SQL Original Statement']);
                $triggers[$name] = str_replace(
                    self::quoteIdentifier($oldDatabase).'.',
                    self::quoteIdentifier($newDatabase).'.',
                    $statement
                ).";";
            }

            $sql = "DROP TRIGGER ".self::quoteIdentifier($oldDatabase).".".self::quoteIdentifier($name).";";
            Debug::debug($sql);
            self::execute($db, $sql, $dryRun);
        }

        return $triggers;
    }

    private static function backupAndDropViews(
        $db,
        string $oldDatabase,
        string $newDatabase,
        $idMysqlServer,
        bool $dryRun
    ): array {
        $oldLiteral = self::sqlLiteral($db, $oldDatabase);
        $sql = "SELECT views.TABLE_NAME As `View`, tab.TABLE_NAME AS `Input`
FROM information_schema.`TABLES` AS tab
INNER JOIN information_schema.VIEWS AS views
ON views.VIEW_DEFINITION LIKE CONCAT('% `',tab.TABLE_NAME,'`%')"
            ." AND tab.TABLE_SCHEMA=".$oldLiteral
            ." AND views.TABLE_SCHEMA=".$oldLiteral
            ." AND tab.TABLE_TYPE = 'VIEW'
UNION
SELECT views.TABLE_NAME As `View`, tab.TABLE_NAME AS `Input`
FROM information_schema.`TABLES` AS tab
INNER JOIN information_schema.VIEWS AS views
ON views.VIEW_DEFINITION LIKE CONCAT('%`',tab.TABLE_SCHEMA,'`.`',tab.TABLE_NAME,'`%')"
            ." AND tab.TABLE_SCHEMA=".$oldLiteral
            ." AND views.TABLE_SCHEMA=".$oldLiteral
            ." AND tab.TABLE_TYPE = 'VIEW';";

        $res = $db->sql_query($sql);

        $relations = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $relations[$row['View']][] = $row['Input'];
        }

        Debug::debug($relations, "Relations");
        $levels = self::resolveViewLevels($relations);
        Debug::debug($levels, "LEVEL");

        $orderBy = self::buildViewOrderBy($db, $levels);
        $sql = "select `table_name` FROM `information_schema`.`tables`"
            ." where `table_schema` = ".$oldLiteral
            ." AND `TABLE_TYPE` = 'VIEW'"
            ." ORDER BY FIELD(`table_name`, ".$orderBy.") DESC, `table_name`;";
        Debug::sql($sql);

        $res = Mysql::sqlQueryWithInformationSchemaTablesTimeout($db, $sql, $idMysqlServer, __METHOD__);
        $views = [];
        $dropStatements = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $name = (string) $row['table_name'];
            $sql = "SHOW CREATE VIEW ".self::quoteIdentifier($oldDatabase).".".self::quoteIdentifier($name);
            Debug::sql($sql);
            $createRes = $db->sql_query($sql);

            while ($createRow = $db->sql_fetch_array($createRes, MYSQLI_ASSOC)) {
                $views[$name] = str_replace(
                    self::quoteIdentifier($oldDatabase),
                    self::quoteIdentifier($newDatabase),
                    $createRow['Create View']
                );
            }

            $dropStatements[] = "DROP VIEW ".self::quoteIdentifier($oldDatabase).".".self::quoteIdentifier($name).";";
        }

        foreach ($dropStatements as $dropStatement) {
            Debug::debug($dropStatement);
            self::execute($db, $dropStatement, $dryRun);
        }

        return [$views, $levels];
    }

    private static function backupAndDropFunctions($db, string $oldDatabase, bool $dryRun): array
    {
        $sql = "SHOW FUNCTION STATUS where Db = ".self::sqlLiteral($db, $oldDatabase);
        Debug::debug($sql);
        $res = $db->sql_query($sql);

        $functions = [];
        while ($row = $db->sql_fetch_object($res)) {
            $name = (string) $row->Name;
            $sql = "SHOW CREATE function ".self::quoteIdentifier($oldDatabase).".".self::quoteIdentifier($name);
            Debug::debug($sql);
            $createRes = $db->sql_query($sql);
            while ($createRow = $db->sql_fetch_array($createRes, MYSQLI_ASSOC)) {
                $functions[] = $createRow['Create Function'].";";
            }

            $sql = "DROP function ".self::quoteIdentifier($oldDatabase).".".self::quoteIdentifier($name).";";
            Debug::debug($sql);
            self::execute($db, $sql, $dryRun);
        }

        return $functions;
    }

    private static function backupAndDropProcedures($db, string $oldDatabase, bool $dryRun): array
    {
        $sql = "SHOW PROCEDURE STATUS WHERE db = ".self::sqlLiteral($db, $oldDatabase).";";
        Debug::debug($sql);
        $res = $db->sql_query($sql);

        $procedures = [];
        while ($row = $db->sql_fetch_object($res)) {
            $name = (string) $row->Name;
            $sql = "SHOW CREATE procedure ".self::quoteIdentifier($oldDatabase).".".self::quoteIdentifier($name);
            $createRes = $db->sql_query($sql);
            while ($createRow = $db->sql_fetch_array($createRes, MYSQLI_ASSOC)) {
                $procedures[] = $createRow['Create Procedure'].";";
            }

            $sql = "DROP procedure ".self::quoteIdentifier($oldDatabase).".".self::quoteIdentifier($name).";";
            Debug::debug($sql);
            self::execute($db, $sql, $dryRun);
        }

        return $procedures;
    }

    private static function restoreFunctions($db, array $functions): void
    {
        foreach ($functions as $function) {
            $db->sql_multi_query($function);
        }
    }

    private static function restoreViews($db, array $views, array $levels): void
    {
        foreach ($levels as $level) {
            foreach ($level as $viewName) {
                if (!isset($views[$viewName])) {
                    continue;
                }

                $db->sql_query($views[$viewName]);
                unset($views[$viewName]);
            }
        }

        foreach ($views as $view) {
            Debug::sql($view);
            $db->sql_query($view);
        }
    }

    private static function restoreProcedures($db, array $procedures): void
    {
        foreach ($procedures as $procedure) {
            Debug::sql($procedure);
            $db->sql_multi_query($procedure);
        }
    }

    private static function restoreTriggers($db, array $triggers): void
    {
        foreach ($triggers as $trigger) {
            Debug::sql($trigger);
            $db->sql_multi_query($trigger);
        }
    }

    public static function getChangeGrant($db, string $oldDatabase, string $newDatabase): array
    {
        $grants = [];
        $revoke = [];

        foreach (Mysql::exportAllUser($db) as $user) {
            if (strpos($user, self::quoteIdentifier($oldDatabase).'.') !== false) {
                $revoke[] = str_replace([' TO ', 'GRANT'], [' FROM ', 'REVOKE'], $user).";";
                $grants[] = str_replace(
                    self::quoteIdentifier($oldDatabase),
                    self::quoteIdentifier($newDatabase),
                    $user
                ).";";
            }
        }

        $data = array_merge($revoke, $grants);
        Debug::debug($data, "GRANTS");

        return $data;
    }

    private static function displayObjectComparison(array $oldCounts, array $newCounts, string $oldDatabase, string $newDatabase): void
    {
        $hasMismatch = false;
        $table = new Table(0);
        $table->addHeader(["Object", $oldDatabase, $newDatabase]);

        foreach ($newCounts['result'] as $key => $count) {
            $oldCount = $oldCounts['result'][$key] ?? 0;
            $table->addLine([$key, $oldCount, $count]);

            if ($oldCount != $count) {
                $hasMismatch = true;
            }
        }

        echo $table->Display();

        if ($hasMismatch) {
            throw new \Exception('We forgot to migrate objects ! (we did not drop old DB)', 5174);
        }
    }

    private static function resolveViewLevels(array $relations): array
    {
        $levels = [];
        $i = 0;
        while ($last = count($relations) != 0) {
            $temp = $relations;

            foreach ($temp as $fatherName => $children) {
                foreach ($children as $keyChild => $childTable) {
                    if (!in_array($childTable, array_keys($relations), true)) {
                        if (empty($levels[$i]) || !in_array($childTable, $levels[$i], true)) {
                            $levels[$i][] = $childTable;
                        }

                        unset($relations[$fatherName][$keyChild]);
                    }
                }
            }

            $temp = $relations;
            foreach ($temp as $key => $children) {
                if (count($children) == 0) {
                    unset($relations[$key]);
                    if (empty($levels[$i + 1]) || !in_array($key, $levels[$i + 1], true)) {
                        $levels[$i + 1][] = $key;
                    }
                }
            }

            if ($last == count($relations)) {
                $caseFound = false;
                $temp = $relations;
                foreach ($temp as $key1 => $children) {
                    foreach ($children as $key2 => $value) {
                        foreach ($levels as $level) {
                            if (in_array($value, $level, true)) {
                                unset($relations[$key1][$key2]);
                                $caseFound = true;
                            }
                        }
                    }
                }

                if (!$caseFound) {
                    echo "\n";
                    debug($children ?? []);
                    debug($levels);
                    debug($relations);
                    throw new \Exception("PMACTRL-334 Circular definition (elem <-> elem)");
                }
            }

            sort($levels[$i]);
            $i++;
        }

        return $levels;
    }

    private static function buildViewOrderBy($db, array $levels): string
    {
        $names = [];
        foreach ($levels as $level) {
            foreach ($level as $name) {
                $names[] = self::sqlLiteral($db, (string) $name);
            }
        }

        if ($names === []) {
            return "''";
        }

        return implode(', ', array_unique($names));
    }

    private static function emitPlannedRecreates(
        $db,
        string $newDatabase,
        array $functions,
        array $views,
        array $levels,
        array $procedures,
        array $triggers
    ): void {
        echo "USE ".self::quoteIdentifier($newDatabase).";\n";

        foreach ($functions as $function) {
            self::execute($db, $function, true, true);
        }

        foreach ($levels as $level) {
            foreach ($level as $viewName) {
                if (!isset($views[$viewName])) {
                    continue;
                }

                self::execute($db, $views[$viewName], true);
                unset($views[$viewName]);
            }
        }

        foreach ($views as $view) {
            self::execute($db, $view, true);
        }

        foreach ($procedures as $procedure) {
            self::execute($db, $procedure, true, true);
        }

        foreach ($triggers as $trigger) {
            self::execute($db, $trigger, true, true);
        }
    }

    private static function execute($db, string $sql, bool $dryRun, bool $multi = false): void
    {
        if ($dryRun) {
            echo rtrim($sql, " \t\n\r;").";\n";
            return;
        }

        if ($multi) {
            $db->sql_multi_query($sql);
            return;
        }

        $db->sql_query($sql);
    }

    private static function assertDatabaseName(string $database, string $label): void
    {
        if (!Identifier::isDatabaseName($database)) {
            throw new \InvalidArgumentException('Invalid '.$label.' name');
        }
    }

    private static function sanitizeCharset(string $charset): string
    {
        $charset = preg_replace('/[^A-Za-z0-9_]/', '', $charset) ?: 'utf8mb4';

        return $charset;
    }
}
