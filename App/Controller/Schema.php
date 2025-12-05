<?php

namespace App\Controller;

use App\Library\Debug;
use App\Library\Extraction2;
use Glial\Synapse\Controller;
use Glial\Sgbd\Sgbd;
use App\Controller\Telegram;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\DiffOnlyOutputBuilder;

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

        $resTables = $mysql->sql_query("SHOW FULL TABLES");
        $tables = [];
        while ($row = $mysql->sql_fetch_array($resTables, MYSQLI_ASSOC)) {
            $tableName = $row[$tableColumn] ?? null;
            $tableType = strtoupper($row[$typeColumn] ?? '');

            if ($tableName === null) {
                continue;
            }

            if (in_array($tableType, $allowedTypes, true)) {
                $tables[] = $tableName;
            }
        }

        if (empty($tables)) {
            return;
        }

        $basePath = DATA . "model/" . $id_mysql_server . "/" . $database;
        $dirCreated = $this->ensureDirectory($basePath);

        if ($dirCreated) {
            $this->initializeGitRepository($basePath);
        } else {
            $this->ensureGitRepository($basePath);
        }

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

            $targetFile = $basePath . "/" . $table . ".sql";
            if (file_put_contents($targetFile, $createStatement . PHP_EOL) === false) {
                throw new \Exception("PMACTRL-SCHEMA-006: Unable to write schema file " . $targetFile . ".");
            }
        }

        $this->cleanupObsoleteSchemas($basePath, $tables);
        $serverMeta = [
            'display_name' => $server['display_name'] ?? ($server['name'] ?? ''),
            'ip' => $server['ip'] ?? '',
            'port' => $server['port'] ?? '',
        ];

        $this->commitSchemaSnapshot($id_mysql_server, $database, $basePath, $serverMeta);

        $this->view = false;
        echo "Schema exported in " . $basePath . PHP_EOL;
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

        $statusOutput = trim(shell_exec("cd " . escapeshellarg($path) . " && git status --porcelain") ?? '');
        if ($statusOutput === '') {
            return;
        }

        $changeSummary = $this->parseGitStatus($statusOutput);

        shell_exec("cd " . escapeshellarg($path) . " && git add -A");

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

    private function cleanupObsoleteSchemas(string $path, array $currentTables): void
    {
        $existingFiles = glob(rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.sql') ?: [];
        $currentFiles = array_map(
            function ($table) use ($path) {
                return rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $table . '.sql';
            },
            $currentTables
        );

        foreach ($existingFiles as $file) {
            if (!in_array($file, $currentFiles, true)) {
                unlink($file);
            }
        }
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

            $table = pathinfo($file, PATHINFO_FILENAME);

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
        $sql = "SELECT id FROM mysql_server WHERE is_deleted = 0 and is_proxy=0";
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
        $files = glob($databasePath . DIRECTORY_SEPARATOR . '*.sql') ?: [];
        $objects = [];

        foreach ($files as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            $objects[$name] = $file;
        }

        ksort($objects);
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
}
