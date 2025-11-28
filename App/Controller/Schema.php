<?php

namespace App\Controller;

use App\Library\Debug;
use App\Library\Extraction2;
use Glial\Synapse\Controller;
use Glial\Sgbd\Sgbd;
use App\Controller\Telegram;

class Schema extends Controller
{
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
        if ($id_mysql_server <= 0) {
            throw new \Exception("PMACTRL-SCHEMA-020: Missing id_mysql_server for exportAll.");
        }

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

        $this->view = false;
        echo "All schemas exported for server " . $id_mysql_server . PHP_EOL;
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
