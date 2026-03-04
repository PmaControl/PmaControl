<?php

namespace App\Controller;

use App\Library\Debug;
use App\Library\Extraction2;
use App\Library\Mysql;
use Glial\Synapse\Controller;
use Glial\Sgbd\Sgbd;
use App\Controller\Telegram;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\DiffOnlyOutputBuilder;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Schema extends Controller
{
    private $differ;

    /**
     * Export the schema (SHOW CREATE TABLE) for every table found in a database.
     *
     * Usage:
     *   ./glial schema export <id_mysql_server> <database>
     *   or http://.../schema/export/<id_mysql_server>/<database>
     *
     * @param array $param [id_mysql_server, database_name]
     *
     * @throws \Exception when mandatory parameters are missing or database cannot be inspected.
     */
    public function export(array $param): void
    {
        Debug::parseDebug($param);

        $id_mysql_server = isset($param[0]) ? (int)$param[0] : 0;
        $database        = $param[1] ?? '';

        if (empty($database))
        {
            return;
        }

        if ($id_mysql_server <= 0 || empty($database)) {
            throw new \Exception(
                "PMACTRL-SCHEMA-001: Missing parameters. Expected id_mysql_server ($id_mysql_server) and database name ($database)."
            );
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT id, name, display_name, ip, port FROM mysql_server WHERE id = " . $db->sql_real_escape_string($id_mysql_server) . " LIMIT 1;";
        $res = $db->sql_query($sql);
        $server = $db->sql_fetch_array($res, MYSQLI_ASSOC);

        if (empty($server)) {
            throw new \Exception("PMACTRL-SCHEMA-002: Unable to find mysql_server with id " . $id_mysql_server . ".");
        }

        $targetConnection = $server['name'];
        $mysql = Sgbd::sql($targetConnection, "SCHEMA");

        $mysql->sql_select_db($database);

        $tableColumn = "Tables_in_" . $database;
        $typeColumn  = "Table_type";
        $allowedTypes = array('BASE TABLE', 'SEQUENCE');
        $viewTypes = array('VIEW');

        $resTables = $mysql->sql_query("SHOW FULL TABLES");
        $tables = [];
        $views = [];
        while ($row = $mysql->sql_fetch_array($resTables, MYSQLI_ASSOC)) {
            $tableName = $row[$tableColumn] ?? null;
            $tableType = strtoupper($row[$typeColumn] ?? '');

            if ($tableName === null) {
                continue;
            }

            if (in_array($tableType, $allowedTypes, true)) {
                $tables[] = $tableName;
            } elseif (in_array($tableType, $viewTypes, true)) {
                $views[] = $tableName;
            }
        }

        $basePath = DATA . "model/" . $id_mysql_server . "/databases/" . $database;
        $this->ensureDirectory($basePath);
        $this->ensureSchemaDirectoryStructure($basePath);

        foreach ($tables as $table) {
            $tableEscaped = str_replace('`', '``', $table);
            $sqlShow = "SHOW CREATE TABLE `" . $tableEscaped . "`";
            $resShow = $mysql->sql_query($sqlShow);
            $createRow = $mysql->sql_fetch_array($resShow, MYSQLI_ASSOC);

            if (empty($createRow['Create Table'])) {
                Debug::debug($createRow, "SHOW CREATE TABLE result");
                throw new \Exception("PMACTRL-SCHEMA-005: Unable to fetch CREATE TABLE for " . $table . ".");
            }

            $createStatement = rtrim($createRow['Create Table']);
            $createStatement = $this->removeAutoIncrement($createStatement);
            if (substr($createStatement, -1) !== ';') {
                $createStatement .= ';';
            }

            $targetFile = $basePath . "/schema/tables/" . $table . ".sql";
            if (file_put_contents($targetFile, $createStatement . PHP_EOL) === false) {
                throw new \Exception("PMACTRL-SCHEMA-006: Unable to write schema file " . $targetFile . ".");
            }
        }

        foreach ($views as $view) {
            $viewEscaped = str_replace('`', '``', $view);
            $sqlShow = "SHOW CREATE VIEW `" . $viewEscaped . "`";
            $resShow = $mysql->sql_query($sqlShow);
            $createRow = $mysql->sql_fetch_array($resShow, MYSQLI_ASSOC);

            $createStatement = $this->extractCreateViewStatement($createRow);
            if ($createStatement === '') {
                Debug::debug($createRow, "SHOW CREATE VIEW result");
                throw new \Exception("PMACTRL-SCHEMA-007: Unable to fetch CREATE VIEW for " . $view . ".");
            }

            $createStatement = rtrim($this->ensureCreateOrReplaceView($createStatement));
            if (substr($createStatement, -1) !== ';') {
                $createStatement .= ';';
            }

            $targetFile = $basePath . "/schema/views/" . $view . ".sql";
            if (file_put_contents($targetFile, $createStatement . PHP_EOL) === false) {
                throw new \Exception("PMACTRL-SCHEMA-008: Unable to write view file " . $targetFile . ".");
            }
        }

        $procedures = $this->exportRoutines($id_mysql_server, $database, $basePath, 'PROCEDURE');
        $functions  = $this->exportRoutines($id_mysql_server, $database, $basePath, 'FUNCTION');
        $triggers   = $this->exportTriggers($id_mysql_server, $database, $basePath);
        $events     = $this->exportEvents($id_mysql_server, $database, $basePath);

        $this->cleanupObsoleteSchemaFiles($basePath, 'schema/tables', $tables);
        $this->cleanupObsoleteSchemaFiles($basePath, 'schema/views', $views);
        $this->cleanupObsoleteSchemaFiles($basePath, 'routines/procedures', $procedures);
        $this->cleanupObsoleteSchemaFiles($basePath, 'routines/functions', $functions);
        $this->cleanupObsoleteSchemaFiles($basePath, 'triggers', $triggers);
        $this->cleanupObsoleteSchemaFiles($basePath, 'events', $events);
        $serverMeta = [
            'display_name' => $server['display_name'] ?? ($server['name'] ?? ''),
            'ip' => $server['ip'] ?? '',
            'port' => $server['port'] ?? '',
        ];

        $this->commitSubDirectorySnapshot($basePath, 'schema', $database, $serverMeta, 'Schema');
        $this->commitSubDirectorySnapshot($basePath, 'routines', $database, $serverMeta, 'Routines');
        $this->commitSubDirectorySnapshot($basePath, 'triggers', $database, $serverMeta, 'Triggers');
        $this->commitSubDirectorySnapshot($basePath, 'events', $database, $serverMeta, 'Events');
        $this->commitSubDirectorySnapshot($basePath, 'data', $database, $serverMeta, 'Data');
        $this->commitSubDirectorySnapshot($basePath, '00-pre', $database, $serverMeta, 'Pre');
        $this->commitSubDirectorySnapshot($basePath, '99-post', $database, $serverMeta, 'Post');

        $this->view = false;
        echo "Schema exported in " . $basePath . PHP_EOL;
    }

    /**
     * Generate an import script for selected schema objects.
     * Usage: ./glial schema importScript <id_mysql_server> <database> [--tables] [--views] [--procedures] [--functions] [--triggers] [--events] [--all] [--output=<file>]
     */
    public function importScript(array $param): void
    {
        Debug::parseDebug($param);

        $id_mysql_server = isset($param[0]) ? (int)$param[0] : 0;
        $database = $param[1] ?? '';

        if ($id_mysql_server <= 0 || $database === '') {
            throw new \Exception("PMACTRL-SCHEMA-200: Expected id_mysql_server and database name.");
        }

        $options = $this->parseImportScriptOptions(array_slice($param, 2));
        if ($options['all'] || !$options['has_selection']) {
            $options['tables'] = true;
            $options['views'] = true;
            $options['procedures'] = true;
            $options['functions'] = true;
            $options['triggers'] = true;
            $options['events'] = true;
        }

        $basePath = DATA . "model/" . $id_mysql_server . "/databases/" . $database;
        if (!is_dir($basePath)) {
            throw new \Exception("PMACTRL-SCHEMA-201: Model path not found: " . $basePath);
        }

        $chunks = [];
        if ($options['tables']) {
            $chunks[] = $this->buildImportTables($basePath);
        }
        if ($options['views']) {
            $chunks[] = $this->buildImportViews($basePath);
        }
        if ($options['functions']) {
            $chunks[] = $this->buildImportFunctions($basePath);
        }
        if ($options['procedures']) {
            $chunks[] = $this->buildImportProcedures($basePath);
        }
        if ($options['triggers']) {
            $chunks[] = $this->buildImportTriggers($basePath);
        }
        if ($options['events']) {
            $chunks[] = $this->buildImportEvents($basePath);
        }

        $script = $this->concatImportChunks($chunks);
        if ($script === '') {
            $script = "";
        }

        $this->view = false;
        if (!empty($options['output'])) {
            if (file_put_contents($options['output'], $script) === false) {
                throw new \Exception("PMACTRL-SCHEMA-202: Unable to write import script to " . $options['output']);
            }
            echo "Import script written to " . $options['output'] . PHP_EOL;
            return;
        }

        echo $script;
    }

    private function parseImportScriptOptions(array $args): array
    {
        $options = [
            'tables' => false,
            'views' => false,
            'procedures' => false,
            'functions' => false,
            'triggers' => false,
            'events' => false,
            'all' => false,
            'output' => '',
            'has_selection' => false,
        ];

        for ($i = 0; $i < count($args); $i++) {
            $arg = (string)$args[$i];

            if ($arg === '--tables') {
                $options['tables'] = true;
                $options['has_selection'] = true;
                continue;
            }

            if ($arg === '--views') {
                $options['views'] = true;
                $options['has_selection'] = true;
                continue;
            }

            if ($arg === '--procedures') {
                $options['procedures'] = true;
                $options['has_selection'] = true;
                continue;
            }

            if ($arg === '--functions') {
                $options['functions'] = true;
                $options['has_selection'] = true;
                continue;
            }

            if ($arg === '--triggers') {
                $options['triggers'] = true;
                $options['has_selection'] = true;
                continue;
            }

            if ($arg === '--events') {
                $options['events'] = true;
                $options['has_selection'] = true;
                continue;
            }

            if ($arg === '--all') {
                $options['all'] = true;
                continue;
            }

            if (strpos($arg, '--output=') === 0) {
                $options['output'] = substr($arg, strlen('--output='));
                continue;
            }

            if ($arg === '--output' && isset($args[$i + 1])) {
                $options['output'] = (string)$args[$i + 1];
                $i++;
            }
        }

        return $options;
    }

    private function buildImportTables(string $basePath): string
    {
        $files = $this->loadSqlFiles($basePath . '/schema/tables');
        return $this->buildSqlSection('Tables', $files);
    }

    private function buildImportViews(string $basePath): string
    {
        $files = $this->loadSqlFiles($basePath . '/schema/views');
        return $this->buildSqlSection('Views', $files);
    }

    private function buildImportProcedures(string $basePath): string
    {
        $files = $this->loadSqlFiles($basePath . '/routines/procedures');
        return $this->buildRoutineSection('Procedures', $files);
    }

    private function buildImportFunctions(string $basePath): string
    {
        $files = $this->loadSqlFiles($basePath . '/routines/functions');
        return $this->buildRoutineSection('Functions', $files);
    }

    private function buildImportTriggers(string $basePath): string
    {
        $files = $this->loadSqlFiles($basePath . '/triggers');
        return $this->buildSqlSection('Triggers', $files);
    }

    private function buildImportEvents(string $basePath): string
    {
        $files = $this->loadSqlFiles($basePath . '/events');
        return $this->buildSqlSection('Events', $files);
    }

    private function concatImportChunks(array $chunks): string
    {
        $chunks = array_values(array_filter($chunks, function ($chunk): bool {
            return trim((string)$chunk) !== '';
        }));

        if (empty($chunks)) {
            return '';
        }

        return rtrim(implode("\n\n", $chunks)) . "\n";
    }

    private function loadSqlFiles(string $path): array
    {
        if (!is_dir($path)) {
            return [];
        }

        $files = glob(rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.sql') ?: [];
        sort($files, SORT_STRING);
        return $files;
    }

    private function buildSqlSection(string $label, array $files): string
    {
        if (empty($files)) {
            return '';
        }

        $parts = ["-- " . $label];
        foreach ($files as $file) {
            $content = $this->readSqlFile($file);
            if ($content === '') {
                continue;
            }
            $parts[] = $content;
        }

        if (count($parts) === 1) {
            return '';
        }

        return rtrim(implode("\n\n", $parts)) . "\n";
    }

    private function buildRoutineSection(string $label, array $files): string
    {
        if (empty($files)) {
            return '';
        }

        $parts = ["-- " . $label, 'DELIMITER //'];
        foreach ($files as $file) {
            $content = $this->formatRoutineFile($file);
            if ($content === '') {
                continue;
            }
            $parts[] = $content;
        }
        $parts[] = 'DELIMITER ;';

        if (count($parts) <= 2) {
            return '';
        }

        return rtrim(implode("\n\n", $parts)) . "\n";
    }

    private function readSqlFile(string $path): string
    {
        if (!is_readable($path)) {
            return '';
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return '';
        }

        return trim($this->normalizeLineEndings($content));
    }

    private function formatRoutineFile(string $path): string
    {
        $content = $this->readSqlFile($path);
        if ($content === '') {
            return '';
        }

        $lines = explode("\n", $content);
        $firstIndex = null;
        foreach ($lines as $index => $line) {
            if (trim($line) !== '') {
                $firstIndex = $index;
                break;
            }
        }

        if ($firstIndex !== null) {
            $firstLine = rtrim($lines[$firstIndex]);
            if (substr($firstLine, -1) === ';') {
                $lines[$firstIndex] = substr($firstLine, 0, -1) . '//';
            }
        }

        $content = implode("\n", $lines);
        $content = rtrim($content);
        if (substr($content, -1) === ';') {
            $content = substr($content, 0, -1) . '//';
        }

        return $content;
    }

    private function exportRoutines(int $id_mysql_server, string $database, string $basePath, string $routineType): array
    {
        $routineType = strtoupper($routineType);
        $queries = Mysql::getRoutineShowCreateQueries([$id_mysql_server, $routineType, $database]);

        if (empty($queries)) {
            return [];
        }

        $folder = $routineType === 'FUNCTION' ? 'routines/functions' : 'routines/procedures';
        $names = [];

        foreach ($queries as $sqlShow) {
            $resShow = $this->executeRoutineShowCreate($id_mysql_server, $sqlShow, $routineType);
            if (empty($resShow['definition'])) {
                Debug::debug($resShow, "SHOW CREATE " . $routineType . " result");
                throw new \Exception("PMACTRL-SCHEMA-110: Unable to fetch CREATE " . $routineType . " for routine.");
            }

            $routineName = $resShow['name'] ?? '';
            if ($routineName === '' || $routineName === 'unknown') {
                throw new \Exception("PMACTRL-SCHEMA-112: Unable to resolve routine name for " . $routineType . ".");
            }

            $statement = $this->normalizeLineEndings(rtrim($resShow['definition']));
            if (substr($statement, -1) !== ';') {
                $statement .= ';';
            }

            $databaseEscaped = str_replace('`', '``', $database);
            $routineEscaped = str_replace('`', '``', $routineName);
            $dropStatement = sprintf(
                'DROP %s IF EXISTS `%s`.`%s`;',
                $routineType,
                $databaseEscaped,
                $routineEscaped
            );

            $names[] = $routineName;

            $statement = $dropStatement . PHP_EOL . $statement;
            $targetFile = $basePath . '/' . $folder . '/' . $routineName . '.sql';
            if (file_put_contents($targetFile, $statement . PHP_EOL) === false) {
                throw new \Exception("PMACTRL-SCHEMA-111: Unable to write routine file " . $targetFile . ".");
            }
            $this->runDos2Unix($targetFile);
        }

        return $names;
    }

    private function exportTriggers(int $id_mysql_server, string $database, string $basePath): array
    {
        $db = Mysql::getDbLink($id_mysql_server);
        $sql = "SHOW TRIGGERS FROM `" . $db->sql_real_escape_string($database) . "`";
        $res = $db->sql_query($sql);

        $triggers = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            if (empty($row['Trigger'])) {
                continue;
            }

            $triggerName = $row['Trigger'];
            $tableName = $row['Table'] ?? '';
            $sqlShow = "SHOW CREATE TRIGGER `" . str_replace('`', '``', $database) . "`.`" . str_replace('`', '``', $triggerName) . "`";
            $resShow = $db->sql_query($sqlShow);
            $createRow = $db->sql_fetch_array($resShow, MYSQLI_ASSOC);

            if (empty($createRow['SQL Original Statement'])) {
                Debug::debug($createRow, "SHOW CREATE TRIGGER result");
                throw new \Exception("PMACTRL-SCHEMA-120: Unable to fetch CREATE TRIGGER for " . $triggerName . ".");
            }

            $statement = rtrim($createRow['SQL Original Statement']);
            if (substr($statement, -1) !== ';') {
                $statement .= ';';
            }

            $databaseEscaped = str_replace('`', '``', $database);
            $triggerEscaped = str_replace('`', '``', $triggerName);
            $dropStatement = sprintf(
                'DROP TRIGGER IF EXISTS `%s`.`%s`;',
                $databaseEscaped,
                $triggerEscaped
            );

            $filePrefix = $tableName !== '' ? $tableName . '__' : '';
            $fileName = $filePrefix . $triggerName;
            $triggers[] = $fileName;

            $targetFile = $basePath . '/triggers/' . $fileName . '.sql';
            $payload = $dropStatement . PHP_EOL . $statement . PHP_EOL;
            if (file_put_contents($targetFile, $payload) === false) {
                throw new \Exception("PMACTRL-SCHEMA-121: Unable to write trigger file " . $targetFile . ".");
            }
        }

        return $triggers;
    }

    private function exportEvents(int $id_mysql_server, string $database, string $basePath): array
    {
        $db = Mysql::getDbLink($id_mysql_server);
        $sql = "SHOW EVENTS FROM `" . $db->sql_real_escape_string($database) . "`";
        $res = $db->sql_query($sql);

        $events = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $eventName = $row['Name'] ?? '';
            if ($eventName === '') {
                continue;
            }

            $sqlShow = "SHOW CREATE EVENT `" . str_replace('`', '``', $database) . "`.`" . str_replace('`', '``', $eventName) . "`";
            $resShow = $db->sql_query($sqlShow);
            $createRow = $db->sql_fetch_array($resShow, MYSQLI_ASSOC);

            if (empty($createRow['Create Event'])) {
                Debug::debug($createRow, "SHOW CREATE EVENT result");
                throw new \Exception("PMACTRL-SCHEMA-122: Unable to fetch CREATE EVENT for " . $eventName . ".");
            }

            $statement = rtrim($createRow['Create Event']);
            if (substr($statement, -1) !== ';') {
                $statement .= ';';
            }

            $databaseEscaped = str_replace('`', '``', $database);
            $eventEscaped = str_replace('`', '``', $eventName);
            $dropStatement = sprintf(
                'DROP EVENT IF EXISTS `%s`.`%s`;',
                $databaseEscaped,
                $eventEscaped
            );

            $events[] = $eventName;
            $targetFile = $basePath . '/events/' . $eventName . '.sql';
            $payload = $dropStatement . PHP_EOL . $statement . PHP_EOL;
            if (file_put_contents($targetFile, $payload) === false) {
                throw new \Exception("PMACTRL-SCHEMA-123: Unable to write event file " . $targetFile . ".");
            }
        }

        return $events;
    }

    private function executeRoutineShowCreate(int $id_mysql_server, string $sqlShow, string $routineType): array
    {
        $db = Mysql::getDbLink($id_mysql_server);
        $resShow = $db->sql_query($sqlShow);
        $row = $db->sql_fetch_array($resShow, MYSQLI_ASSOC) ?: [];

        $definitionKey = $routineType === 'FUNCTION' ? 'Create Function' : 'Create Procedure';

        return [
            'definition' => $row[$definitionKey] ?? '',
            'name' => $this->extractRoutineName($row, $definitionKey, $routineType),
        ];
    }

    private function extractRoutineName(array $row, string $definitionKey, string $routineType): string
    {
        $candidates = [];
        if ($routineType === 'FUNCTION') {
            $candidates[] = 'Function';
        } elseif ($routineType === 'PROCEDURE') {
            $candidates[] = 'Procedure';
        }
        $candidates[] = 'Name';

        foreach ($candidates as $key) {
            if (!empty($row[$key])) {
                return $row[$key];
            }
        }

        $definition = $row[$definitionKey] ?? '';
        if ($definition !== '' && preg_match('/`([^`]+)`\.`([^`]+)`/i', $definition, $matches)) {
            return $matches[2];
        }

        return 'unknown';
    }

    private function ensureDirectory(string $path): bool
    {
        if (is_dir($path)) {
            return false;
        }

        if (!mkdir($path, 0775, true) && !is_dir($path)) {
            throw new \Exception("PMACTRL-SCHEMA-004: Unable to create directory " . $path . ".");
        }

        return true;
    }

    private function ensureSchemaDirectoryStructure(string $basePath): void
    {
        $subDirs = [
            '00-pre',
            'schema/tables',
            'schema/views',
            'routines/procedures',
            'routines/functions',
            'events',
            'triggers',
            'data',
            '99-post',
        ];

        foreach ($subDirs as $dir) {
            $fullPath = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $dir;
            if (!is_dir($fullPath)) {
                if (!mkdir($fullPath, 0775, true) && !is_dir($fullPath)) {
                    throw new \Exception("PMACTRL-SCHEMA-013: Unable to create directory " . $fullPath . ".");
                }
            }
        }
    }

    private function initializeGitRepository(string $path): void
    {
        if (!is_dir($path)) {
            throw new \Exception("PMACTRL-SCHEMA-012: Directory not found: " . $path);
        }

        $this->ensureGitSafeDirectory($path);

        $cmd = "cd " . escapeshellarg($path) . " && git init 2>&1";
        shell_exec($cmd);
    }

    private function ensureGitRepository(string $path): void
    {
        $gitDir = $path . DIRECTORY_SEPARATOR . '.git';

        if (!is_dir($gitDir)) {
            $this->initializeGitRepository($path);
        } else {
            $this->ensureGitSafeDirectory($path);
        }
    }

    private function commitSchemaSnapshot(int $idMysqlServer, string $database, string $path, array $serverMeta): void
    {
        $this->ensureGitRepository($path);

        $exclude = [
            'schema',
            'routines',
            'triggers',
            'events',
            'data',
            '00-pre',
            '99-post',
        ];

        $statusOutput = $this->getGitStatus($path, $exclude);
        if ($statusOutput === '') {
            return;
        }

        $changeSummary = $this->parseGitStatus($statusOutput);

        foreach ($exclude as $folder) {
            shell_exec(
                "cd " . escapeshellarg($path) . " && git reset --quiet -- \"" . $folder . "\" 2>/dev/null"
            );
        }

        shell_exec("cd " . escapeshellarg($path) . " && git add -A");

        foreach ($exclude as $folder) {
            shell_exec(
                "cd " . escapeshellarg($path) . " && git reset --quiet -- \"" . $folder . "\" 2>/dev/null"
            );
        }

        $stagedChanges = trim(shell_exec("cd " . escapeshellarg($path) . " && git diff --cached --name-only") ?? '');
        if ($stagedChanges === '') {
            return;
        }

        $snapshotNumber = $this->getNextSnapshotNumber($path);
        $message = sprintf(
            "Schema snapshot %s #%d - %s",
            $database,
            $snapshotNumber,
            date('Y-m-d H:i:s')
        );

        shell_exec("cd " . escapeshellarg($path) . " && git commit -m " . escapeshellarg($message));

        $this->notifySchemaChange($database, $snapshotNumber, $changeSummary, $serverMeta);
    }

    private function getGitStatus(string $path, array $exclude = []): string
    {
        $cmd = "cd " . escapeshellarg($path) . " && git status --porcelain";
        if (!empty($exclude)) {
            $cmd .= " -- .";
            foreach ($exclude as $folder) {
                $cmd .= " ':(exclude)" . $folder . "'";
            }
        }

        return trim(shell_exec($cmd) ?? '');
    }

    private function commitSubDirectorySnapshot(
        string $basePath,
        string $subDirectory,
        string $database,
        array $serverMeta,
        string $label
    ): void {
        $subPath = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $subDirectory;
        if (!is_dir($subPath)) {
            return;
        }

        $this->ensureGitRepository($subPath);

        $statusOutput = trim(shell_exec("cd " . escapeshellarg($subPath) . " && git status --porcelain") ?? '');
        if ($statusOutput === '') {
            return;
        }

        $changeSummary = $this->parseGitStatus($statusOutput);

        shell_exec("cd " . escapeshellarg($subPath) . " && git add -A");

        $snapshotNumber = $this->getNextSnapshotNumber($subPath);
        $message = sprintf(
            "%s snapshot %s #%d - %s",
            $label,
            $database,
            $snapshotNumber,
            date('Y-m-d H:i:s')
        );

        shell_exec("cd " . escapeshellarg($subPath) . " && git commit -m " . escapeshellarg($message));

        $this->notifySchemaChange($database, $snapshotNumber, $changeSummary, $serverMeta);
    }

    private function ensureGitSafeDirectory(string $path): void
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR);
        $safeList = shell_exec("git config --global --get-all safe.directory 2>/dev/null");
        $alreadySafe = false;

        if (!empty($safeList)) {
            foreach (explode("\n", trim($safeList)) as $line) {
                if ($line === $path) {
                    $alreadySafe = true;
                    break;
                }
            }
        }

        if (!$alreadySafe) {
            shell_exec("git config --global --add safe.directory " . escapeshellarg($path));
        }
    }

    private function removeAutoIncrement(string $createStatement): string
    {
        return preg_replace('/\sAUTO_INCREMENT=\d+/i', '', $createStatement);
    }

    private function getNextSnapshotNumber(string $path): int
    {
        $log = trim(shell_exec("cd " . escapeshellarg($path) . " && git log -1 --pretty=%B 2>/dev/null") ?? '');

        if ($log === '') {
            return 1;
        }

        if (preg_match_all('/#(\d+)/', $log, $matches) && !empty($matches[1])) {
            $last = (int)end($matches[1]);
            return $last + 1;
        }

        return 1;
    }

    private function cleanupObsoleteSchemaFiles(string $path, string $folder, array $currentObjects): void
    {
        $folder = trim($folder, '/');
        $existingFiles = glob(rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $folder . '/*.sql') ?: [];
        $currentFiles = array_map(
            function ($object) use ($path, $folder) {
                return rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $folder . '/' . $object . '.sql';
            },
            $currentObjects
        );

        foreach ($existingFiles as $file) {
            if (!in_array($file, $currentFiles, true)) {
                unlink($file);
            }
        }
    }

    private function extractCreateViewStatement(array $createRow): string
    {
        foreach ($createRow as $key => $value) {
            if (stripos($key, 'create view') !== false) {
                return (string)$value;
            }
        }

        return '';
    }

    private function ensureCreateOrReplaceView(string $statement): string
    {
        $trimmed = ltrim($statement);
        if (stripos($trimmed, 'create or replace') === 0) {
            return $statement;
        }

        return preg_replace('/^\s*CREATE\s+/i', 'CREATE OR REPLACE ', $statement, 1) ?? $statement;
    }

    private function normalizeLineEndings(string $sql): string
    {
        return str_replace(["\r\n", "\r"], "\n", $sql);
    }

    private function runDos2Unix(string $path): void
    {
        $binary = trim(shell_exec('command -v dos2unix 2>/dev/null') ?? '');
        if ($binary === '') {
            return;
        }

        shell_exec($binary . ' ' . escapeshellarg($path));
    }

    private function parseGitStatus(string $status): array
    {
        $changes = [
            'added' => [],
            'modified' => [],
            'deleted' => [],
        ];

        $lines = array_filter(array_map('rtrim', explode("\n", $status)));
        foreach ($lines as $line) {
            if (strlen($line) < 4) {
                continue;
            }

            $code = substr($line, 0, 2);
            $file = trim(substr($line, 2));

            if (strpos($file, ' -> ') !== false) {
                $parts = explode(' -> ', $file);
                $file = end($parts);
            }

            if (substr($file, -4) !== '.sql') {
                continue;
            }

            $file = str_replace('\\', '/', $file);
            $table = preg_replace('/\.sql$/i', '', $file);

            if ($code === '??' || strpos($code, 'A') !== false) {
                $changes['added'][] = $table;
            } elseif (strpos($code, 'D') !== false) {
                $changes['deleted'][] = $table;
            } else {
                $changes['modified'][] = $table;
            }
        }

        foreach ($changes as $key => $list) {
            $changes[$key] = array_values(array_unique($list));
        }

        return $changes;
    }

    private function notifySchemaChange(string $database, int $snapshotNumber, array $changes, array $serverMeta): void
    {
        if (empty($changes['added']) && empty($changes['modified']) && empty($changes['deleted'])) {
            return;
        }

        $serverLabel = $this->formatServerLabel($serverMeta);

        $lines = [];
        $lines[] = sprintf("📦 Schema change on %s", $serverLabel);
        $lines[] = sprintf(
            "Database : 🗄️ <b>%s</b> - <i>Version #%d</i>",
            htmlspecialchars($database, ENT_QUOTES, 'UTF-8'),
            $snapshotNumber
        );
        

        if (!empty($changes['added'])) {
            $lines[] = "Add: " . $this->formatChangeList($changes['added']);
        }

        if (!empty($changes['modified'])) {
            $lines[] = "Mod: " . $this->formatChangeList($changes['modified']);
        }

        if (!empty($changes['deleted'])) {
            $lines[] = "Del: " . $this->formatChangeList($changes['deleted']);
        }

        $message = implode("\n", $lines);
        //Telegram::broadcast($message);
    }

    private function formatChangeList(array $items): string
    {
        $maxItems = 10;
        $count = count($items);

        if ($count > $maxItems) {
            $display = array_slice($items, 0, $maxItems);
            $display[] = sprintf("... (%d total)", $count);
            return implode(', ', $display);
        }

        return implode(', ', $items);
    }

    private function formatServerLabel(array $meta): string
    {
        $ip = $meta['ip'] ?? '';
        $port = $meta['port'] ?? '';
        $display = $meta['display_name'] ?? '';

        $host = trim($ip) !== '' ? $ip : 'unknown';
        $socket = $host;
        if (!empty($port)) {
            $socket .= ':' . $port;
        }

        if (!empty($display)) {
            $displaySafe = htmlspecialchars($display, ENT_QUOTES, 'UTF-8');
            $socketSafe = htmlspecialchars($socket, ENT_QUOTES, 'UTF-8');
            return sprintf("🖥️ <b>%s</b> (%s)", $displaySafe, $socketSafe);
        }

        return "🖥️ " . htmlspecialchars($socket, ENT_QUOTES, 'UTF-8');
    }

    private function isUnknownDatabaseError(\Throwable $exception): bool
    {
        if ($exception instanceof \mysqli_sql_exception && (int)$exception->getCode() === 1049) {
            return true;
        }

        return stripos($exception->getMessage(), 'unknown database') !== false;
    }

    public function exportAll(array $param): void
    {
        Debug::parseDebug($param);

        $id_mysql_server = isset($param[0]) ? (int)$param[0] : 0;
        $serverIds = $this->getEligibleServerIds([$id_mysql_server]);

        if (empty($serverIds)) {
            throw new \Exception("PMACTRL-SCHEMA-020: No eligible mysql_server found for exportAll.");
        }

        foreach ($serverIds as $serverId) {
            $this->exportSchemasForServer($serverId);
        }

        $this->view = false;
    }

    private function exportSchemasForServer(int $id_mysql_server): void
    {
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "
            SELECT schema_name
            FROM mysql_database
            WHERE id_mysql_server = " . $db->sql_real_escape_string($id_mysql_server) . "
            AND schema_name NOT IN ('information_schema', 'performance_schema')
        ";
        $res = $db->sql_query($sql);

        $databases = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $databases[] = $row['schema_name'];
        }

        if (empty($databases)) {
            throw new \Exception("PMACTRL-SCHEMA-021: No databases found for server #" . $id_mysql_server . ".");
        }

        foreach ($databases as $database) {
            try {
                $this->export([$id_mysql_server, $database]);
            } catch (\Throwable $exception) {
                if ($this->isUnknownDatabaseError($exception)) {
                    Debug::debug(
                        [
                            'id_mysql_server' => $id_mysql_server,
                            'database' => $database,
                            'error' => $exception->getMessage(),
                        ],
                        'PMACTRL-SCHEMA-MISSING-DB'
                    );
                    continue;
                }

                throw $exception;
            }
        }

        echo "All schemas exported for server " . $id_mysql_server . PHP_EOL;
    }

    public function getEligibleServerIds($param): array
    {
        debug::parseDebug($param);

        $requestedServerId = $param[0] ?? 0;

        if ($requestedServerId > 0) {
            return [$requestedServerId];
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT id FROM mysql_server WHERE is_deleted = 0 and is_proxy=0 and is_proxy=0";
        $res = $db->sql_query($sql);

        $serverIds = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $serverIds[] = (int)$row['id'];
        }

        if (empty($serverIds)) {
            return [];
        }

        $metrics = Extraction2::display(["mysql_available"], $serverIds);
        if (empty($metrics)) {
            return [];
        }

        $eligible = [];
        foreach ($metrics as $serverId => $values) {
            if ($this->toInt($values['mysql_available'] ?? 0) === 1) {
                $eligible[] = (int)$serverId;
            }
        }

        Debug::debug($eligible);

        return $eligible;
    }

    /**
     * Continuously monitor DDL counters and trigger exports at a fixed interval.
     * Usage: ./glial schema watchLoop [id_mysql_server] [interval_seconds]
     */
    public function watchLoop(array $param): void
    {
        Debug::parseDebug($param);

        $offset = 0;
        $id_mysql_server = null;

        if (!empty($param[$offset]) && is_numeric($param[$offset])) {
            $id_mysql_server = (int)$param[$offset];
            $offset++;
        }

        $interval = isset($param[$offset]) ? (int)$param[$offset] : 60;
        if ($interval < 5) {
            $interval = 5;
        }

        $this->view = false;
        while (true) {
            $args = [];
            if ($id_mysql_server !== null) {
                $args[] = $id_mysql_server;
            }

            $this->watch($args);
            Debug::debug($interval, "SLEEP");
            sleep($interval);
        }
    }

    private function loadDdlState(): array
    {
        $file = $this->getDdlStateFile();
        if (!file_exists($file)) {
            return [];
        }

        $json = file_get_contents($file);
        if ($json === false) {
            return [];
        }

        $data = json_decode($json, true);
        return is_array($data) ? $data : [];
    }

    private function saveDdlState(array $state): void
    {
        $file = $this->getDdlStateFile();
        file_put_contents($file, json_encode($state, JSON_PRETTY_PRINT));
    }

    private function getDdlStateFile(): string
    {
        return DATA . "schema_watch_state.json";
    }

    private function toInt($value): int
    {
        if (is_array($value) && isset($value['value'])) {
            $value = $value['value'];
        }

        return (int)$value;
    }

    private function computeDelta(int $previous, int $current): int
    {
        if ($current < $previous) {
            return $current;
        }

        return $current - $previous;
    }

    /**
     * Compare the exported schema models of two servers.
     * Usage: ./glial schema compareModels <id_mysql_server_left> <id_mysql_server_right>
     */
    public function compareModels(array $param): void
    {
        Debug::parseDebug($param);

        $leftId  = isset($param[0]) ? (int)$param[0] : 0;
        $rightId = isset($param[1]) ? (int)$param[1] : 0;

        if ($leftId <= 0 || $rightId <= 0) {
            throw new \Exception("PMACTRL-SCHEMA-040: Expected two mysql_server ids to compare.");
        }

        if ($leftId === $rightId) {
            throw new \Exception("PMACTRL-SCHEMA-041: Provided ids must reference two different servers.");
        }

        $comparison = $this->diffModelServers($leftId, $rightId);

        $this->view = false;
        echo $this->formatModelComparison($comparison) . PHP_EOL;
    }

    private function diffModelServers(int $leftId, int $rightId, array $options = []): array
    {
        $ignoreColumnOrder = !empty($options['ignore_column_order']);
        $leftPath  = $this->getModelServerPath($leftId);
        $rightPath = $this->getModelServerPath($rightId);

        $leftDatabases  = $this->listModelDatabases($leftPath);
        $rightDatabases = $this->listModelDatabases($rightPath);

        $leftNames  = array_keys($leftDatabases);
        $rightNames = array_keys($rightDatabases);

        sort($leftNames);
        sort($rightNames);

        $leftOnly  = array_values(array_diff($leftNames, $rightNames));
        $rightOnly = array_values(array_diff($rightNames, $leftNames));
        $common    = array_values(array_intersect($leftNames, $rightNames));

        $diffPerDb = [];
        foreach ($common as $database) {
            $diff = $this->diffModelDatabase(
                $leftDatabases[$database],
                $rightDatabases[$database],
                $ignoreColumnOrder
            );
            if (!empty($diff['left_only']) || !empty($diff['right_only']) || !empty($diff['different'])) {
                $diffPerDb[$database] = $diff;
            }
        }

        ksort($diffPerDb);

        return [
            'left' => $leftId,
            'right' => $rightId,
            'databases' => [
                'left_only' => $leftOnly,
                'right_only' => $rightOnly,
                'differences' => $diffPerDb,
            ],
        ];
    }

    private function getModelServerPath(int $serverId): string
    {
        $path = rtrim(DATA, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "model" . DIRECTORY_SEPARATOR . $serverId;
        $databasesPath = $path . DIRECTORY_SEPARATOR . 'databases';
        if (is_dir($databasesPath)) {
            return $databasesPath;
        }

        if (!is_dir($path)) {
            throw new \Exception("PMACTRL-SCHEMA-042: No model found for server #" . $serverId . " in " . $path . ".");
        }

        return $path;
    }

    private function listModelDatabases(string $serverPath): array
    {
        $paths = glob($serverPath . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [];
        $result = [];

        foreach ($paths as $path) {
            $name = basename($path);
            if ($name === '.' || $name === '..' || $name === '.git') {
                continue;
            }

            $result[$name] = $path;
        }

        ksort($result);
        return $result;
    }

    private function listModelObjects(string $databasePath): array
    {
        $objects = $this->listModelSqlFiles($databasePath);

        ksort($objects);
        return $objects;
    }

    private function listModelSqlFiles(string $databasePath): array
    {
        if (!is_dir($databasePath)) {
            return [];
        }

        $objects = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($databasePath, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }

            if (strtolower($fileInfo->getExtension()) !== 'sql') {
                continue;
            }

            $fullPath = $fileInfo->getPathname();
            if (strpos($fullPath, DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR) !== false) {
                continue;
            }

            $relative = ltrim(str_replace($databasePath, '', $fullPath), DIRECTORY_SEPARATOR);
            $objects[$relative] = $fullPath;
        }

        return $objects;
    }

    private function diffModelDatabase(string $leftDatabasePath, string $rightDatabasePath, bool $ignoreColumnOrder = false): array
    {
        $leftObjects  = $this->listModelObjects($leftDatabasePath);
        $rightObjects = $this->listModelObjects($rightDatabasePath);

        $leftNames  = array_keys($leftObjects);
        $rightNames = array_keys($rightObjects);

        $leftOnly  = array_values(array_diff($leftNames, $rightNames));
        $rightOnly = array_values(array_diff($rightNames, $leftNames));
        $common    = array_values(array_intersect($leftNames, $rightNames));

        $different = [];
        foreach ($common as $object) {
            $leftFile = $leftObjects[$object];
            $rightFile = $rightObjects[$object];

            if (!$this->areModelFilesIdentical($leftFile, $rightFile, $ignoreColumnOrder)) {
                $different[] = [
                    'name' => $object,
                    'left' => $leftFile,
                    'right' => $rightFile,
                ];
            }
        }

        return [
            'left_only' => $leftOnly,
            'right_only' => $rightOnly,
            'different' => $different,
        ];
    }

    private function formatModelComparison(array $comparison): string
    {
        $lines = [];
        $lines[] = sprintf(
            "Comparaison des exports pour les serveurs #%d et #%d",
            $comparison['left'],
            $comparison['right']
        );

        $dbInfo = $comparison['databases'];

        if (empty($dbInfo['left_only']) && empty($dbInfo['right_only']) && empty($dbInfo['differences'])) {
            $lines[] = "Aucune différence détectée.";
            return implode("\n", $lines);
        }

        if (!empty($dbInfo['left_only'])) {
            $lines[] = "Bases seulement sur le serveur gauche : " . implode(', ', $dbInfo['left_only']);
        }

        if (!empty($dbInfo['right_only'])) {
            $lines[] = "Bases seulement sur le serveur droit : " . implode(', ', $dbInfo['right_only']);
        }

        foreach ($dbInfo['differences'] as $database => $diff) {
            $lines[] = "Base " . $database . " :";

            if (!empty($diff['left_only'])) {
                $lines[] = "  Objets manquants à droite : " . implode(', ', $diff['left_only']);
            }

            if (!empty($diff['right_only'])) {
                $lines[] = "  Objets manquants à gauche : " . implode(', ', $diff['right_only']);
            }

            if (!empty($diff['different'])) {
                $objectNames = array_map(
                    function ($item) {
                        return $item['name'];
                    },
                    $diff['different']
                );

                $lines[] = "  Objets divergents : " . implode(', ', $objectNames);
            }
        }

        return implode("\n", $lines);
    }

    public function compareModelsUi(array $param): void
    {
        $this->title = __("Compare schema exports");

        $leftId = isset($_GET['schema_compare']['id_mysql_server__left']) ? (int)$_GET['schema_compare']['id_mysql_server__left'] : 0;
        $rightId = isset($_GET['schema_compare']['id_mysql_server__right']) ? (int)$_GET['schema_compare']['id_mysql_server__right'] : 0;
        $ignoreColumnOrder = !empty($_GET['schema_compare']['ignore_column_order']);

        $data = [
            'left_id' => $leftId,
            'right_id' => $rightId,
            'ignore_column_order' => $ignoreColumnOrder,
            'errors' => [],
            'comparison' => null,
            'detailed' => [],
            'diff_css' => $this->getDiffTableCss(),
        ];

        if ($leftId > 0 && $rightId > 0 && $leftId !== $rightId) {
            try {
                $comparison = $this->diffModelServers(
                    $leftId,
                    $rightId,
                    ['ignore_column_order' => $ignoreColumnOrder]
                );
                $data['comparison'] = $comparison;
                $data['detailed'] = $this->buildComparisonDetails(
                    $comparison['databases']['differences'] ?? [],
                    $ignoreColumnOrder
                );
            } catch (\Throwable $exception) {
                $data['errors'][] = $exception->getMessage();
            }
        } elseif ($leftId !== 0 || $rightId !== 0) {
            $data['errors'][] = __("Please select two different servers.");
        }

        $this->set('data', $data);
    }

    private function buildComparisonDetails(array $differences, bool $ignoreColumnOrder): array
    {
        $result = [];

        foreach ($differences as $database => $diff) {
            $entry = [
                'name' => $database,
                'left_only' => $diff['left_only'] ?? [],
                'right_only' => $diff['right_only'] ?? [],
                'objects' => [],
            ];

            foreach ($diff['different'] as $objectDiff) {
                $entry['objects'][] = [
                    'name' => $objectDiff['name'],
                    'diff' => $this->renderDiffTable(
                        $objectDiff['left'],
                        $objectDiff['right'],
                        $ignoreColumnOrder
                    ),
                ];
            }

            $result[] = $entry;
        }

        return $result;
    }

    private function renderDiffTable(string $leftFile, string $rightFile, bool $ignoreColumnOrder = false): string
    {
        $left = $this->readFileContent($leftFile);
        $right = $this->readFileContent($rightFile);

        if ($ignoreColumnOrder) {
            $left = $this->normalizeCreateTableStatement($left);
            $right = $this->normalizeCreateTableStatement($right);
        }

        $diff = $this->getDiffer()->diffToArray($left, $right);

        $rows = [];
        $oldLine = 1;
        $newLine = 1;

        foreach ($diff as $edit) {
            [$line, $type] = $edit;

            if ($type === Differ::DIFF_LINE_END_WARNING || $type === Differ::NO_LINE_END_EOF_WARNING) {
                continue;
            }

            $class = $this->getDiffClass($type);
            $prefix = $this->getDiffPrefix($type);

            $rows[] = sprintf(
                '<tr><td class="ln %s">%s</td><td class="ln %s">%s</td><td class="%s">%s</td></tr>',
                $class,
                $type === Differ::ADDED ? '' : $oldLine++,
                $class,
                $type === Differ::REMOVED ? '' : $newLine++,
                $class,
                htmlspecialchars($prefix . $line)
            );
        }

        return '<table class="diff-table">' . implode('', $rows) . '</table>';
    }

    private function getDiffClass(int $type): string
    {
        if ($type === Differ::ADDED) {
            return 'add';
        }

        if ($type === Differ::REMOVED) {
            return 'del';
        }

        return 'same';
    }

    private function getDiffPrefix(int $type): string
    {
        if ($type === Differ::ADDED) {
            return '+ ';
        }

        if ($type === Differ::REMOVED) {
            return '- ';
        }

        return '  ';
    }

    private function readFileContent(string $path): string
    {
        if (!is_readable($path)) {
            return '';
        }

        $content = file_get_contents($path);
        return $content === false ? '' : $content;
    }

    private function areModelFilesIdentical(string $fileA, string $fileB, bool $ignoreColumnOrder = false): bool
    {
        if (!is_readable($fileA) || !is_readable($fileB)) {
            return false;
        }

        if ($ignoreColumnOrder === false) {
            return md5_file($fileA) === md5_file($fileB);
        }

        $contentA = $this->normalizeCreateTableStatement($this->readFileContent($fileA));
        $contentB = $this->normalizeCreateTableStatement($this->readFileContent($fileB));

        return md5($contentA) === md5($contentB);
    }

    private function normalizeCreateTableStatement(string $sql): string
    {
        $sql = str_replace(["\r\n", "\r"], "\n", trim($sql));

        if ($sql === '' || stripos($sql, 'CREATE TABLE') === false) {
            return $sql;
        }

        $openParen = strpos($sql, '(');
        $closeParen = strrpos($sql, ')');

        if ($openParen === false || $closeParen === false || $closeParen <= $openParen) {
            return $sql;
        }

        $prefix = substr($sql, 0, $openParen);
        $body = substr($sql, $openParen + 1, $closeParen - $openParen - 1);
        $suffix = trim(substr($sql, $closeParen + 1));

        $definitions = $this->splitSqlDefinitionList($body);

        if (empty($definitions)) {
            return $sql;
        }

        $columns = [];
        $indexes = [];
        $others = [];

        foreach ($definitions as $definition) {
            $clean = trim($definition);
            if ($clean === '') {
                continue;
            }

            if ($this->isColumnDefinitionLine($clean)) {
                $columns[] = [
                    'name' => strtolower($this->extractColumnName($clean)),
                    'definition' => $clean,
                ];
                continue;
            }

            if ($this->isIndexDefinitionLine($clean)) {
                $indexes[] = [
                    'name' => strtolower($this->extractIndexName($clean)),
                    'definition' => $clean,
                ];
                continue;
            }

            $others[] = $clean;
        }

        if (!empty($columns)) {
            usort(
                $columns,
                function (array $left, array $right): int {
                    return $left['name'] <=> $right['name'];
                }
            );
        }

        $orderedColumns = array_map(
            function (array $column): string {
                return $column['definition'];
            },
            $columns
        );

        if (!empty($indexes)) {
            usort(
                $indexes,
                function (array $left, array $right): int {
                    return $left['name'] <=> $right['name'];
                }
            );
        }

        $orderedIndexes = array_map(
            function (array $index): string {
                return $index['definition'];
            },
            $indexes
        );

        $normalizedBody = implode(",\n    ", array_merge($orderedColumns, $orderedIndexes, $others));

        $normalized = rtrim($prefix) . " (\n    " . $normalizedBody . "\n)";
        if ($suffix !== '') {
            $normalized .= "\n" . $suffix;
        }

        return $normalized;
    }

    private function splitSqlDefinitionList(string $body): array
    {
        $length = strlen($body);
        $entries = [];
        $current = '';
        $depth = 0;
        $inSingle = false;
        $inDouble = false;
        $inBacktick = false;

        for ($i = 0; $i < $length; $i++) {
            $char = $body[$i];
            $next = $body[$i + 1] ?? '';

            if ($char === "'" && !$inDouble && !$inBacktick) {
                if ($inSingle) {
                    if ($next === "'") {
                        $current .= "''";
                        $i++;
                        continue;
                    }

                    if (!$this->isEscapedByBackslash($body, $i)) {
                        $inSingle = false;
                    }
                } elseif (!$this->isEscapedByBackslash($body, $i)) {
                    $inSingle = true;
                }
            } elseif ($char === '"' && !$inSingle && !$inBacktick) {
                if ($inDouble) {
                    if (!$this->isEscapedByBackslash($body, $i)) {
                        $inDouble = false;
                    }
                } elseif (!$this->isEscapedByBackslash($body, $i)) {
                    $inDouble = true;
                }
            } elseif ($char === '`' && !$inSingle && !$inDouble) {
                $inBacktick = !$inBacktick;
            }

            if (!$inSingle && !$inDouble && !$inBacktick) {
                if ($char === '(') {
                    $depth++;
                } elseif ($char === ')' && $depth > 0) {
                    $depth--;
                }
            }

            if ($char === ',' && !$inSingle && !$inDouble && !$inBacktick && $depth === 0) {
                $trimmed = trim($current);
                if ($trimmed !== '') {
                    $entries[] = $trimmed;
                }
                $current = '';
                continue;
            }

            $current .= $char;
        }

        $trimmed = trim($current);
        if ($trimmed !== '') {
            $entries[] = $trimmed;
        }

        return $entries;
    }

    private function isColumnDefinitionLine(string $definition): bool
    {
        $definition = ltrim($definition);
        return isset($definition[0]) && $definition[0] === '`';
    }

    private function isIndexDefinitionLine(string $definition): bool
    {
        $definition = ltrim($definition);
        if ($definition === '') {
            return false;
        }

        $upper = strtoupper($definition);
        $patterns = [
            'PRIMARY KEY',
            'UNIQUE KEY',
            'FULLTEXT KEY',
            'SPATIAL KEY',
            'KEY ',
            'CONSTRAINT',
            'FOREIGN KEY',
        ];

        foreach ($patterns as $pattern) {
            if (strpos($upper, $pattern) === 0) {
                return true;
            }
        }

        return false;
    }

    private function extractColumnName(string $definition): string
    {
        if (preg_match('/^`([^`]+)`/', $definition, $matches)) {
            return $matches[1];
        }

        return $definition;
    }

    private function extractIndexName(string $definition): string
    {
        $definition = ltrim($definition);

        if (stripos($definition, 'PRIMARY KEY') === 0) {
            return 'primary';
        }

        if (preg_match('/^CONSTRAINT\s+`([^`]+)`/i', $definition, $matches)) {
            return $matches[1];
        }

        if (preg_match('/^(?:UNIQUE|FULLTEXT|SPATIAL)?\s*KEY\s+`([^`]+)`/i', $definition, $matches)) {
            return $matches[1];
        }

        if (preg_match('/^FOREIGN KEY\s+`([^`]+)`/i', $definition, $matches)) {
            return $matches[1];
        }

        return strtolower(preg_replace('/\s+/', ' ', $definition));
    }

    private function isEscapedByBackslash(string $subject, int $position): bool
    {
        $backslashes = 0;
        for ($i = $position - 1; $i >= 0; $i--) {
            if ($subject[$i] === '\\') {
                $backslashes++;
            } else {
                break;
            }
        }

        return ($backslashes % 2) === 1;
    }

    private function getDiffTableCss(): string
    {
        return '<style>
.diff-table { width: 100%; border-collapse: collapse; font-family: "Monaspace Neon", ui-monospace, SFMono-Regular, "SF Mono", Menlo,monospace; }
.diff-table td { padding: 4px; vertical-align: top; white-space: pre; }
.diff-table .add { background: rgba(172, 238, 187, 0.7); color: #22863a; }
.diff-table .del { background: rgba(238, 199, 206, 0.7); color: #b31d28; }
.diff-table .ln.add { background: rgba(172, 238, 187, 1); }
.diff-table .ln.del { background: rgba(238, 199, 206, 1); }
.diff-table .same { background: #f6f8fa; color: #24292e; }
.diff-table .ln { width:40px; text-align:right; color:#999; }
</style>';
    }

    private function getDiffer(): Differ
    {
        if (!$this->differ instanceof Differ) {
            $this->differ = new Differ(new DiffOnlyOutputBuilder());
        }

        return $this->differ;
    }

    public function watch(array $param): void
    {
        Debug::parseDebug($param);

        $serverFilter = [];
        if (!empty($param[0]) && is_numeric($param[0])) {
            $serverFilter = [(int)$param[0]];
        }

        $metrics = Extraction2::display(
            ["com_create_table", "com_drop_table", "com_alter_table", "mysql_available"],
            $serverFilter
        );

        if (empty($metrics)) {
            throw new \Exception("PMACTRL-SCHEMA-030: Extraction2::display returned no data.");
        }

        $state = $this->loadDdlState();
        $triggered = [];

        foreach ($metrics as $serverId => $values) {
            $serverId = (int)$serverId;
            $available = $this->toInt($values['mysql_available'] ?? 0);
            if ($available === 0) {
                continue;
            }

            $current = [
                'create' => $this->toInt($values['com_create_table'] ?? 0),
                'drop'   => $this->toInt($values['com_drop_table'] ?? 0),
                'alter'  => $this->toInt($values['com_alter_table'] ?? 0),
            ];

            if (!isset($state[$serverId])) {
                $state[$serverId] = $current;
                continue;
            }

            $deltaCreate = $this->computeDelta($state[$serverId]['create'], $current['create']);
            $deltaDrop   = $this->computeDelta($state[$serverId]['drop'], $current['drop']);
            $deltaAlter  = $this->computeDelta($state[$serverId]['alter'], $current['alter']);

            if (($deltaCreate + $deltaDrop + $deltaAlter) > 0) {
                $this->exportAll([$serverId]);
                $triggered[] = $serverId;
            }

            $state[$serverId] = $current;
        }

        $this->saveDdlState($state);

        $this->view = false;
        if (empty($triggered)) {
            echo "No DDL detected.\n";
        } else {
            echo "DDL detected on servers: " . implode(', ', $triggered) . PHP_EOL;
        }
    }

    /**
     * One-shot migration to move legacy exports into the new directory layout.
     * Usage: ./glial schema migration [id_mysql_server]
     */
    public function migration(array $param): void
    {
        Debug::parseDebug($param);

        $serverFilter = null;
        $basePath = rtrim(DATA, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'model';

        if (!empty($param[0]) && is_numeric($param[0])) {
            $serverFilter = (int)$param[0];
        }

        if (!empty($param[1]) && is_string($param[1])) {
            $basePath = rtrim($param[1]);
        }

        $summary = $this->runMigration($basePath, $serverFilter);

        $this->view = false;
        echo $this->formatMigrationSummary($summary);
    }

    /**
     * Parcourt /srv/www/pmacontrol/data/model et lance la migration
     * pour chaque serveur trouvé.
     * Usage: ./glial schema migrationAll
     */
    public function migrationAll(array $param): void
    {
        Debug::parseDebug($param);

        $basePath = '/srv/www/pmacontrol/data/model';
        $summary = $this->runMigration($basePath, null);

        $this->view = false;
        echo $this->formatMigrationSummary($summary);
    }

    /**
     * One-shot: move the database-level git repo into schema/.git
     * Usage: ./glial schema migrateSchemaRepo <id_mysql_server> <database>
     */
    public function migrateSchemaRepo(array $param): void
    {
        Debug::parseDebug($param);

        $id_mysql_server = isset($param[0]) ? (int)$param[0] : 0;
        $database = $param[1] ?? '';

        if ($id_mysql_server <= 0 || $database === '') {
            throw new \Exception("PMACTRL-SCHEMA-060: Expected id_mysql_server and database.");
        }

        $basePath = DATA . "model/" . $id_mysql_server . "/databases/" . $database;
        $sourceGit = $basePath . DIRECTORY_SEPARATOR . '.git';
        $targetGit = $basePath . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . '.git';

        if (!is_dir($sourceGit)) {
            throw new \Exception("PMACTRL-SCHEMA-061: source .git not found at " . $sourceGit);
        }

        $this->ensureSchemaDirectoryStructure($basePath);

        if (is_dir($targetGit)) {
            throw new \Exception("PMACTRL-SCHEMA-062: target .git already exists at " . $targetGit);
        }

        $this->moveDirectory($sourceGit, $targetGit);

        $this->view = false;
        echo "Moved repo: " . $sourceGit . " -> " . $targetGit . PHP_EOL;
    }

    /**
     * One-shot: migrate all database-level git repos into schema/.git.
     * Usage: ./glial schema migrateSchemaReposAll [id_mysql_server]
     */
    public function migrateSchemaReposAll(array $param): void
    {
        Debug::parseDebug($param);

        $serverFilter = null;
        if (!empty($param[0]) && is_numeric($param[0])) {
            $serverFilter = (int)$param[0];
        }

        $basePath = rtrim(DATA, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'model';
        if (!is_dir($basePath)) {
            throw new \Exception("PMACTRL-SCHEMA-070: data/model directory not found: " . $basePath);
        }

        $servers = glob($basePath . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [];
        $migrated = 0;
        $skipped = 0;

        foreach ($servers as $serverPath) {
            $serverId = basename($serverPath);
            if (!ctype_digit($serverId)) {
                continue;
            }

            if ($serverFilter !== null && (int)$serverId !== $serverFilter) {
                continue;
            }

            $databasesRoot = $serverPath . DIRECTORY_SEPARATOR . 'databases';
            if (!is_dir($databasesRoot)) {
                $databasesRoot = $serverPath;
            }

            $databaseDirs = glob($databasesRoot . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [];
            foreach ($databaseDirs as $databasePath) {
                $databaseName = basename($databasePath);
                if ($databaseName === '.' || $databaseName === '..' || $databaseName === '.git') {
                    continue;
                }

                $sourceGit = $databasePath . DIRECTORY_SEPARATOR . '.git';
                if (!is_dir($sourceGit)) {
                    $skipped++;
                    continue;
                }

                $this->ensureSchemaDirectoryStructure($databasePath);
                $targetGit = $databasePath . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . '.git';

                if (is_dir($targetGit)) {
                    $skipped++;
                    continue;
                }

                $this->moveDirectory($sourceGit, $targetGit);
                $migrated++;
            }
        }

        $this->view = false;
        echo "Schema repo migration finished. Migrated: " . $migrated . ", skipped: " . $skipped . PHP_EOL;
    }

    /**
     * List skipped repositories for schema repo migration.
     * Usage: ./glial schema listSchemaRepoSkips [id_mysql_server]
     */
    public function listSchemaRepoSkips(array $param): void
    {
        Debug::parseDebug($param);

        $serverFilter = null;
        if (!empty($param[0]) && is_numeric($param[0])) {
            $serverFilter = (int)$param[0];
        }

        $basePath = rtrim(DATA, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'model';
        if (!is_dir($basePath)) {
            throw new \Exception("PMACTRL-SCHEMA-080: data/model directory not found: " . $basePath);
        }

        $servers = glob($basePath . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [];
        $skipped = [
            'missing_git' => [],
            'already_migrated' => [],
        ];
        $perServer = [];

        foreach ($servers as $serverPath) {
            $serverId = basename($serverPath);
            if (!ctype_digit($serverId)) {
                continue;
            }

            if ($serverFilter !== null && (int)$serverId !== $serverFilter) {
                continue;
            }

            $databasesRoot = $serverPath . DIRECTORY_SEPARATOR . 'databases';
            if (!is_dir($databasesRoot)) {
                $databasesRoot = $serverPath;
            }

            $databaseDirs = glob($databasesRoot . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [];
            foreach ($databaseDirs as $databasePath) {
                $databaseName = basename($databasePath);
                if ($databaseName === '.' || $databaseName === '..' || $databaseName === '.git') {
                    continue;
                }

                $sourceGit = $databasePath . DIRECTORY_SEPARATOR . '.git';
                $targetGit = $databasePath . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . '.git';

                if (!is_dir($sourceGit)) {
                    if (is_dir($targetGit)) {
                        $skipped['already_migrated'][] = $serverId . '/' . $databaseName;
                        $perServer[$serverId]['already_migrated'][] = $databaseName;
                    } else {
                        $skipped['missing_git'][] = $serverId . '/' . $databaseName;
                        $perServer[$serverId]['missing_git'][] = $databaseName;
                    }
                    continue;
                }

                if (is_dir($targetGit)) {
                    $skipped['already_migrated'][] = $serverId . '/' . $databaseName;
                    $perServer[$serverId]['already_migrated'][] = $databaseName;
                }
            }
        }

        $this->view = false;
        echo "Skipped (missing .git):\n";
        echo empty($skipped['missing_git']) ? "- none\n" : "- " . implode("\n- ", $skipped['missing_git']) . "\n";
        echo "\nSkipped (already migrated):\n";
        echo empty($skipped['already_migrated']) ? "- none\n" : "- " . implode("\n- ", $skipped['already_migrated']) . "\n";

        if (!empty($perServer)) {
            ksort($perServer);
            echo "\nSummary per server:\n";
            foreach ($perServer as $serverId => $entries) {
                $missingCount = isset($entries['missing_git']) ? count($entries['missing_git']) : 0;
                $migratedCount = isset($entries['already_migrated']) ? count($entries['already_migrated']) : 0;
                echo "- " . $serverId . ": missing .git=" . $missingCount . ", already migrated=" . $migratedCount . "\n";
            }
        }
    }

    private function runMigration(string $basePath, ?int $serverFilter): array
    {
        if (!is_dir($basePath)) {
            throw new \Exception("PMACTRL-SCHEMA-050: data/model directory not found: " . $basePath);
        }

        $servers = glob(rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [];

        $summary = [
            'servers' => 0,
            'databases' => 0,
            'tables' => 0,
            'skipped' => 0,
        ];

        foreach ($servers as $serverPath) {
            $serverId = basename($serverPath);
            if (!ctype_digit($serverId)) {
                continue;
            }

            if ($serverFilter !== null && (int)$serverId !== $serverFilter) {
                continue;
            }

            $summary['servers']++;
            $databasesRoot = $serverPath . DIRECTORY_SEPARATOR . 'databases';
            if (!is_dir($databasesRoot)) {
                mkdir($databasesRoot, 0775, true);
            }

            $databaseDirs = glob($serverPath . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [];
            foreach ($databaseDirs as $databasePath) {
                $databaseName = basename($databasePath);
                if ($databaseName === 'databases' || $databaseName === '.git') {
                    continue;
                }

                $summary['databases']++;
                $newBasePath = $databasesRoot . DIRECTORY_SEPARATOR . $databaseName;
                if (!is_dir($newBasePath)) {
                    mkdir($newBasePath, 0775, true);
                }

                $this->ensureSchemaDirectoryStructure($newBasePath);

                $legacyGit = $databasePath . DIRECTORY_SEPARATOR . '.git';
                $newGit = $newBasePath . DIRECTORY_SEPARATOR . '.git';
                if (is_dir($legacyGit) && !is_dir($newGit)) {
                    $this->moveDirectory($legacyGit, $newGit);
                }

                $legacyTables = glob($databasePath . DIRECTORY_SEPARATOR . '*.sql') ?: [];
                foreach ($legacyTables as $legacyTable) {
                    $fileName = basename($legacyTable);
                    $targetPath = $newBasePath . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'tables' . DIRECTORY_SEPARATOR . $fileName;

                    if (file_exists($targetPath)) {
                        if (md5_file($legacyTable) === md5_file($targetPath)) {
                            unlink($legacyTable);
                        } else {
                            $legacyTarget = $newBasePath . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'tables' . DIRECTORY_SEPARATOR
                                . basename($fileName, '.sql') . '.legacy.sql';
                            $this->moveFile($legacyTable, $legacyTarget);
                        }
                    } else {
                        $this->moveFile($legacyTable, $targetPath);
                    }

                    $summary['tables']++;
                }

                $remaining = array_diff(scandir($databasePath) ?: [], ['.', '..']);
                if (empty($remaining)) {
                    rmdir($databasePath);
                } else {
                    $summary['skipped'] += count($remaining);
                }
            }
        }

        return $summary;
    }

    private function formatMigrationSummary(array $summary): string
    {
        return sprintf(
            "Migration finished. Servers: %d, databases: %d, tables moved: %d, remaining entries: %d\n",
            $summary['servers'] ?? 0,
            $summary['databases'] ?? 0,
            $summary['tables'] ?? 0,
            $summary['skipped'] ?? 0
        );
    }

    private function moveFile(string $source, string $destination): void
    {
        $targetDir = dirname($destination);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        if (@rename($source, $destination)) {
            return;
        }

        if (copy($source, $destination)) {
            unlink($source);
            return;
        }

        throw new \RuntimeException('Unable to move file ' . $source . ' -> ' . $destination);
    }

    private function moveDirectory(string $source, string $destination): void
    {
        if (@rename($source, $destination)) {
            return;
        }

        $this->copyDirectory($source, $destination);
        $this->removeDirectory($source);
    }

    private function copyDirectory(string $source, string $destination): void
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0775, true);
        }

        $items = scandir($source) ?: [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $src = $source . DIRECTORY_SEPARATOR . $item;
            $dst = $destination . DIRECTORY_SEPARATOR . $item;

            if (is_dir($src)) {
                $this->copyDirectory($src, $dst);
            } else {
                copy($src, $dst);
            }
        }
    }

    private function removeDirectory(string $path): void
    {
        $items = scandir($path) ?: [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $target = $path . DIRECTORY_SEPARATOR . $item;
            if (is_dir($target)) {
                $this->removeDirectory($target);
            } else {
                unlink($target);
            }
        }

        rmdir($path);
    }
}
