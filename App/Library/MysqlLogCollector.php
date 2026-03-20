<?php

namespace App\Library;

final class MysqlLogCollector
{
    public const LOG_TYPE_SQL_ERROR = 'sql_error';
    public const LOG_TYPE_SLOW_QUERY = 'slow_query';
    public const LOG_TYPE_ERROR = 'error_log';
    public const LOG_TYPE_GENERAL = 'general_log';
    public const LOG_TYPE_OOM = 'oom_killer';
    private const ERROR_LOG_ENTRY_PATTERN = '/^\d{4}-\d{2}-\d{2}\s+\d{1,2}:\d{2}:\d{2}\b/u';
    private const STORAGE_DIRECTORY_BY_LOG_TYPE = [
        self::LOG_TYPE_SQL_ERROR => 'sql_error_log',
        self::LOG_TYPE_SLOW_QUERY => 'log_slow_query',
        self::LOG_TYPE_ERROR => 'log_error',
        self::LOG_TYPE_GENERAL => 'general_log',
        self::LOG_TYPE_OOM => 'journalctl',
    ];

    /**
     * Resolve file-backed MySQL log sources from the last known server variables.
     *
     * @param array<string,mixed> $variables
     * @return array<int,array<string,mixed>>
     */
    public static function resolveLogSources(array $variables): array
    {
        $sources = [];

        $pluginEnabled = self::isSqlErrorLogPluginEnabled($variables['plugins_json'] ?? null);
        $generalLogEnabled = self::toBoolean($variables['general_log'] ?? false);

        $logError = trim((string)($variables['log_error'] ?? ''));
        if ($logError !== '' && strtolower($logError) !== 'stderr') {
            $sources[] = [
                'log_type' => self::LOG_TYPE_ERROR,
                'source_kind' => 'file',
                'source_name' => $logError,
            ];
        }

        $slowQueryFile = trim((string)($variables['slow_query_log_file'] ?? ''));
        if ($slowQueryFile !== '' && strtolower($slowQueryFile) !== 'stderr') {
            $sources[] = [
                'log_type' => self::LOG_TYPE_SLOW_QUERY,
                'source_kind' => 'file',
                'source_name' => $slowQueryFile,
            ];
        }

        $generalLogFile = trim((string)($variables['general_log_file'] ?? ''));
        if ($generalLogEnabled && $generalLogFile !== '' && strtolower($generalLogFile) !== 'stderr') {
            $sources[] = [
                'log_type' => self::LOG_TYPE_GENERAL,
                'source_kind' => 'file',
                'source_name' => $generalLogFile,
            ];
        }

        $sqlErrorLogFile = trim((string)($variables['sql_error_log_filename'] ?? ''));
        if ($pluginEnabled && $sqlErrorLogFile !== '' && strtolower($sqlErrorLogFile) !== 'stderr') {
            $sources[] = [
                'log_type' => self::LOG_TYPE_SQL_ERROR,
                'source_kind' => 'file',
                'source_name' => $sqlErrorLogFile,
            ];
        }

        // OOM journal is always a journal-backed source.
        $sources[] = [
            'log_type' => self::LOG_TYPE_OOM,
            'source_kind' => 'journal',
            'source_name' => 'journalctl-kernel-mariadb',
        ];

        return $sources;
    }

    /**
     * Parse a JSON payload exported from information_schema.plugins.
     *
     * @param mixed $pluginsJson
     */
    public static function isSqlErrorLogPluginEnabled($pluginsJson): bool
    {
        if (!is_string($pluginsJson) || trim($pluginsJson) === '') {
            return false;
        }

        $decoded = json_decode($pluginsJson, true);
        if (!is_array($decoded)) {
            return false;
        }

        foreach ($decoded as $row) {
            if (!is_array($row)) {
                continue;
            }

            $name = strtoupper(trim((string)($row['PLUGIN_NAME'] ?? $row['plugin_name'] ?? '')));
            $status = strtoupper(trim((string)($row['PLUGIN_STATUS'] ?? $row['plugin_status'] ?? '')));

            if ($name === 'SQL_ERROR_LOG' && in_array($status, ['ACTIVE', 'ON', 'ENABLED'], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Workaround for MDEV-39107:
     * split concatenated SQL error entries when a new log header appears inside the previous statement text.
     *
     * @return array<int,string>
     */
    public static function splitSqlErrorLogEntries(string $content): array
    {
        if ($content === '') {
            return [];
        }

        $normalized = preg_replace(
            '/(?<!^)(?<!\n)(?=(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} [^\[\r\n]+\[[^\]\r\n]*\] @ ))/m',
            "\n",
            $content
        );

        $parts = preg_split('/\r?\n/', (string)$normalized);
        $parts = array_values(array_filter(array_map('trim', $parts), static function ($line) {
            return $line !== '';
        }));

        return $parts;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public static function buildFileEvents(
        int $idMysqlServer,
        string $logType,
        string $logPath,
        int $inode,
        int $offsetStart,
        string $content
    ): array {
        if ($content === '') {
            return [];
        }

        if ($logType === self::LOG_TYPE_SQL_ERROR) {
            $entries = self::splitSqlErrorLogEntries($content);
        } elseif ($logType === self::LOG_TYPE_ERROR) {
            $entries = self::splitErrorLogEntries($content);
        } else {
            $entries = array_values(array_filter(preg_split('/\r?\n/', $content) ?: [], static function ($line) {
                return trim((string)$line) !== '';
            }));
        }

        $events = [];
        $cursor = $offsetStart;

        foreach ($entries as $entry) {
            $raw = rtrim((string)$entry, "\r\n");
            if ($raw === '') {
                continue;
            }

            $parsed = self::parseMysqlLogEntry($raw);
            $length = strlen($raw) + 1;
            $offsetEnd = $cursor + $length;

            $events[] = [
                'id_mysql_server' => $idMysqlServer,
                'log_type' => $logType,
                'source_kind' => 'file',
                'log_path' => $logPath,
                'event_time' => $parsed['event_time'],
                'inode' => $inode,
                'offset_start' => $cursor,
                'offset_end' => $offsetEnd,
                'user_name' => $parsed['user_name'],
                'host_name' => $parsed['host_name'],
                'process_name' => $parsed['process_name'],
                'level' => $parsed['level'],
                'error_code' => $parsed['error_code'],
                'message' => $parsed['message'],
                'raw_line' => $raw,
                'meta_json' => $parsed['meta_json'],
                'dedup_key' => sha1(implode('|', [
                    $idMysqlServer,
                    $logType,
                    'file',
                    $logPath,
                    (string)$inode,
                    (string)$cursor,
                    $raw,
                ])),
            ];

            $cursor = $offsetEnd;
        }

        return $events;
    }

    /**
     * MariaDB/MySQL error log entries are often multiline.
     * Start a new entry only when a timestamp header is found; otherwise append
     * the line to the current entry.
     *
     * @return array<int,string>
     */
    public static function splitErrorLogEntries(string $content): array
    {
        if ($content === '') {
            return [];
        }

        $lines = preg_split('/\r?\n/', $content) ?: [];
        $entries = [];
        $current = '';

        foreach ($lines as $line) {
            $line = rtrim((string)$line, "\r");
            if ($line === '') {
                continue;
            }

            if (preg_match(self::ERROR_LOG_ENTRY_PATTERN, $line)) {
                if ($current !== '') {
                    $entries[] = $current;
                }
                $current = $line;
                continue;
            }

            if ($current === '') {
                $current = $line;
                continue;
            }

            $current .= "\n" . $line;
        }

        if ($current !== '') {
            $entries[] = $current;
        }

        return $entries;
    }

    public static function getStorageDirectoryName(string $logType): string
    {
        return self::STORAGE_DIRECTORY_BY_LOG_TYPE[$logType] ?? $logType;
    }

    /**
     * @return array{base_dir:string,full_dir:string}
     */
    public static function ensureLocalStorageDirectories(int $idMysqlServer, string $logType, ?string $baseRoot = null): array
    {
        $root = rtrim($baseRoot ?? (DATA . 'logs'), '/');
        $baseDir = $root . '/' . $idMysqlServer . '/' . self::getStorageDirectoryName($logType);
        $fullDir = $baseDir . '/full';

        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0775, true);
        }

        if (!is_dir($fullDir)) {
            mkdir($fullDir, 0775, true);
        }

        return [
            'base_dir' => $baseDir,
            'full_dir' => $fullDir,
        ];
    }

    public static function buildInitialFullSnapshotPath(int $idMysqlServer, string $logType, string $sourceName, ?string $baseRoot = null): string
    {
        $dirs = self::ensureLocalStorageDirectories($idMysqlServer, $logType, $baseRoot);
        $basename = basename($sourceName);
        if ($basename === '' || $basename === '.' || $basename === '..') {
            $basename = self::getStorageDirectoryName($logType) . '.log';
        }

        return $dirs['full_dir'] . '/' . $basename;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public static function prepareLocalFileParts(
        int $idMysqlServer,
        string $logType,
        string $sourceName,
        int $inode,
        int $offsetStart,
        string $content
    ): array {
        if ($content === '') {
            return [];
        }

        if ($logType === self::LOG_TYPE_SQL_ERROR) {
            $entries = self::splitSqlErrorLogEntries($content);
        } elseif ($logType === self::LOG_TYPE_ERROR) {
            $entries = self::splitErrorLogEntries($content);
        } else {
            $entries = array_values(array_filter(preg_split('/\r?\n/', $content) ?: [], static function ($line) {
                return trim((string)$line) !== '';
            }));
        }

        $parts = [];
        $cursor = $offsetStart;

        foreach ($entries as $entry) {
            $raw = rtrim((string)$entry, "\r\n");
            if ($raw === '') {
                continue;
            }

            $length = strlen($raw) + 1;
            $offsetEnd = $cursor + $length;
            $parsed = self::parseMysqlLogEntry($raw);
            $eventTime = (string)($parsed['event_time'] ?? '');
            $dateKey = preg_match('/^\d{4}-\d{2}-\d{2}/', $eventTime) ? substr($eventTime, 0, 10) : date('Y-m-d');

            if (empty($parts[$dateKey])) {
                $parts[$dateKey] = [
                    'id_mysql_server' => $idMysqlServer,
                    'log_type' => $logType,
                    'source_name' => $sourceName,
                    'inode' => $inode,
                    'date_key' => $dateKey,
                    'offset_start' => $cursor,
                    'offset_end' => $offsetEnd,
                    'content' => $raw . "\n",
                ];
            } else {
                $parts[$dateKey]['offset_end'] = $offsetEnd;
                $parts[$dateKey]['content'] .= $raw . "\n";
            }

            $cursor = $offsetEnd;
        }

        return array_values($parts);
    }

    /**
     * @param array<int,array<string,mixed>> $parts
     * @return array<int,array<string,mixed>>
     */
    public static function persistLocalFileParts(array $parts, ?string $baseRoot = null): array
    {
        $written = [];

        foreach ($parts as $part) {
            $idMysqlServer = (int)($part['id_mysql_server'] ?? 0);
            $logType = (string)($part['log_type'] ?? '');
            $dateKey = (string)($part['date_key'] ?? '');

            if ($idMysqlServer <= 0 || $logType === '' || $dateKey === '' || empty($part['content'])) {
                continue;
            }

            $dirs = self::ensureLocalStorageDirectories($idMysqlServer, $logType, $baseRoot);
            $dateDir = $dirs['base_dir'] . '/' . $dateKey;
            if (!is_dir($dateDir)) {
                mkdir($dateDir, 0775, true);
            }

            $baseName = self::getStorageDirectoryName($logType);
            $existing = glob($dateDir . '/' . $baseName . '.part.*') ?: [];
            $maxPart = 0;
            foreach ($existing as $existingFile) {
                if (preg_match('/\.part\.(\d+)$/', $existingFile, $matches)) {
                    $maxPart = max($maxPart, (int)$matches[1]);
                }
            }

            $partNumber = $maxPart + 1;
            $partPath = $dateDir . '/' . $baseName . '.part.' . $partNumber;
            file_put_contents($partPath, (string)$part['content']);

            $metaPath = $dateDir . '/.' . basename($partPath) . '.meta.json';
            file_put_contents($metaPath, json_encode([
                'id_mysql_server' => $idMysqlServer,
                'log_type' => $logType,
                'source_kind' => 'file',
                'source_name' => (string)($part['source_name'] ?? ''),
                'inode' => isset($part['inode']) ? (int)$part['inode'] : null,
                'offset_start' => isset($part['offset_start']) ? (int)$part['offset_start'] : null,
                'offset_end' => isset($part['offset_end']) ? (int)$part['offset_end'] : null,
                'date_key' => $dateKey,
            ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

            $written[] = [
                'part_path' => $partPath,
                'meta_path' => $metaPath,
                'date_key' => $dateKey,
                'offset_start' => $part['offset_start'] ?? null,
                'offset_end' => $part['offset_end'] ?? null,
            ];
        }

        return $written;
    }

    /**
     * Parse OOM-related kernel/service journal output.
     *
     * @return array<int,array<string,mixed>>
     */
    public static function buildOomEvents(int $idMysqlServer, string $journalOutput): array
    {
        $lines = preg_split('/\r?\n/', $journalOutput) ?: [];
        $events = [];

        foreach ($lines as $line) {
            $line = trim((string)$line);
            if ($line === '') {
                continue;
            }

            if (!preg_match('/oom|out of memory|killed process|mariadbd|rocksdb:high/i', $line)) {
                continue;
            }

            $parsed = self::parseJournalLine($line);
            $message = $parsed['message'];

            if (!preg_match('/oom|out of memory|killed process|mariadbd|rocksdb:high/i', $message)) {
                continue;
            }

            $process = null;
            if (preg_match('/kernel:\s+([^:]+):high invoked oom-killer/i', $line, $m)) {
                $process = trim($m[1]) . ':high';
            } elseif (preg_match('/kernel:\s+(mariadbd) invoked oom-killer/i', $line, $m)) {
                $process = trim($m[1]);
            } elseif (preg_match('/Killed process \d+ \(([^)]+)\)/i', $line, $m)) {
                $process = trim($m[1]);
            }

            $events[] = [
                'id_mysql_server' => $idMysqlServer,
                'log_type' => self::LOG_TYPE_OOM,
                'source_kind' => 'journal',
                'log_path' => 'journalctl -k',
                'event_time' => $parsed['event_time'],
                'inode' => null,
                'offset_start' => null,
                'offset_end' => null,
                'user_name' => null,
                'host_name' => null,
                'process_name' => $process,
                'level' => 'OOM',
                'error_code' => null,
                'message' => $message,
                'raw_line' => $line,
                'meta_json' => json_encode([
                    'channel' => $parsed['channel'],
                    'mentions_mariadb_service' => stripos($line, 'mariadb.service') !== false,
                    'mentions_rocksdb_high' => stripos($line, 'rocksdb:high') !== false,
                    'mentions_mariadbd' => stripos($line, 'mariadbd') !== false,
                ], JSON_UNESCAPED_SLASHES),
                'dedup_key' => sha1(implode('|', [
                    $idMysqlServer,
                    self::LOG_TYPE_OOM,
                    'journal',
                    $parsed['event_time'] ?? '',
                    $line,
                ])),
            ];
        }

        return $events;
    }

    /**
     * @return array<string,mixed>
     */
    public static function parseMysqlLogEntry(string $line): array
    {
        $result = [
            'event_time' => null,
            'user_name' => null,
            'host_name' => null,
            'process_name' => null,
            'level' => null,
            'error_code' => null,
            'message' => $line,
            'meta_json' => null,
        ];

        if (preg_match(
            '/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\s+([^\[\s]+)\[([^\]]*)\]\s+@\s+([^\[]+)\[([^\]]*)\]\s+([A-Z]+)\s+([0-9]+):\s+([\s\S]*)$/u',
            $line,
            $matches
        )) {
            $result['event_time'] = $matches[1];
            $result['user_name'] = trim($matches[2]);
            $result['host_name'] = trim($matches[4]);
            $result['level'] = trim($matches[6]);
            $result['error_code'] = trim($matches[7]);
            $result['message'] = $matches[8];
            $result['meta_json'] = json_encode([
                'user_bracket' => $matches[3],
                'db_name' => $matches[5],
            ], JSON_UNESCAPED_SLASHES);
            return $result;
        }

        if (preg_match(
            '/^(\d{4}-\d{2}-\d{2}\s+\d{1,2}:\d{2}:\d{2})\s+([A-Za-z0-9]+)\s+\[([A-Za-z]+)\]\s+([\s\S]*)$/u',
            $line,
            $matches
        )) {
            $message = trim($matches[4]);
            $meta = [];
            $headerToken = trim((string)$matches[2]);
            $errorCode = null;

            if (ctype_digit($headerToken)) {
                if (strlen($headerToken) >= 6) {
                    $result['process_name'] = 'thread ' . $headerToken;
                } else {
                    $errorCode = $headerToken;
                }
            } elseif (preg_match('/^[A-Za-z0-9]{5}$/', $headerToken)) {
                $errorCode = $headerToken;
            } else {
                $meta['header_token'] = $headerToken;
            }

            if (preg_match('/^(.*?:\s*)\[(.+?)@(.+?)\](?:\[([^\]]*)\])?\s*(.*)$/u', $message, $detail)) {
                $prefix = trim((string)($detail[1] ?? ''));
                $result['user_name'] = trim((string)($detail[2] ?? ''));
                $result['host_name'] = trim((string)($detail[3] ?? ''));
                if (!empty($detail[4])) {
                    $meta['context'] = trim((string)$detail[4]);
                }
                $message = trim($prefix . ' ' . (string)($detail[5] ?? ''));
            } elseif (preg_match("/^Aborted connection\\s+([0-9]+)\\s+to db: '([^']*)'\\s+user: '([^']*)'\\s+host: '([^']*)'\\s*\\((.*)\\)$/u", $message, $detail)) {
                $meta['db_name'] = trim((string)$detail[2]);
                $meta['thread_id'] = (int)$detail[1];
                $result['user_name'] = trim((string)$detail[3]);
                $result['host_name'] = trim((string)$detail[4]);
                $result['process_name'] = 'thread ' . trim((string)$detail[1]);
                $errorCode = null;
                $message = 'Aborted connection (' . trim((string)$detail[5]) . ')';
            }

            $normalizedTime = preg_replace('/\s+/', ' ', trim($matches[1])) ?: '';
            $timestamp = strtotime($normalizedTime);

            $result['event_time'] = $timestamp !== false ? date('Y-m-d H:i:s', $timestamp) : $normalizedTime;
            $result['level'] = strtoupper(trim((string)$matches[3]));
            $result['error_code'] = $errorCode;
            $result['message'] = $message;
            $result['meta_json'] = empty($meta) ? null : json_encode($meta, JSON_UNESCAPED_SLASHES);
            return $result;
        }

        if (preg_match('/^(\d{4}-\d{2}-\d{2}\s+\d{1,2}:\d{2}:\d{2})\s+([\s\S]*)$/u', $line, $matches)) {
            $normalizedTime = preg_replace('/\s+/', ' ', trim($matches[1])) ?: '';
            $timestamp = strtotime($normalizedTime);
            $result['event_time'] = $timestamp !== false ? date('Y-m-d H:i:s', $timestamp) : $normalizedTime;
            $result['message'] = trim((string)$matches[2]);
        }

        return $result;
    }

    /**
     * @return array<string,mixed>
     */
    public static function parseJournalLine(string $line): array
    {
        if (preg_match('/^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\+\d{4}|Z)?)\s+([^\s]+)\s+([^:]+):\s*(.*)$/', $line, $m)) {
            return [
                'event_time' => self::normalizeIsoTimestamp($m[1]),
                'host_name' => $m[2],
                'channel' => trim($m[3]),
                'message' => trim($m[4]),
            ];
        }

        if (preg_match('/^[A-Za-z]{3}\s+\d+\s+\d{2}:\d{2}:\d{2}\s+([^\s]+)\s+([^:]+):\s*(.*)$/', $line, $m)) {
            return [
                'event_time' => null,
                'host_name' => $m[1],
                'channel' => trim($m[2]),
                'message' => trim($m[3]),
            ];
        }

        return [
            'event_time' => null,
            'host_name' => null,
            'channel' => 'journal',
            'message' => $line,
        ];
    }

    /**
     * @param array<int,array<string,mixed>> $events
     * @return array<string,int>
     */
    public static function aggregateCounts(array $events, string $bucket): array
    {
        $format = match ($bucket) {
            'minute' => 'Y-m-d H:i:00',
            'hour' => 'Y-m-d H:00:00',
            'day' => 'Y-m-d 00:00:00',
            default => throw new \InvalidArgumentException('Unsupported bucket: ' . $bucket),
        };

        $result = [];
        foreach ($events as $event) {
            $eventTime = $event['event_time'] ?? null;
            if (empty($eventTime)) {
                continue;
            }

            $bucketStart = date($format, strtotime((string)$eventTime));
            if (!isset($result[$bucketStart])) {
                $result[$bucketStart] = 0;
            }
            $result[$bucketStart]++;
        }

        ksort($result);
        return $result;
    }

    private static function normalizeIsoTimestamp(string $value): string
    {
        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return $value;
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    /**
     * @param mixed $value
     */
    private static function toBoolean($value): bool
    {
        $normalized = strtolower(trim((string)$value));
        return in_array($normalized, ['1', 'on', 'true', 'yes'], true);
    }
}
