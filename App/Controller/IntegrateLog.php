<?php

namespace App\Controller;

use App\Library\EngineV4;
use App\Library\MysqlLogCollector;
use Fuz\Component\SharedMemory\SharedMemory;
use Fuz\Component\SharedMemory\Storage\StorageFile;
use Glial\Sgbd\Sgbd;
use Glial\Synapse\Controller;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class IntegrateLog extends Controller
{
    private const MAX_FILE_AT_ONCE = 10;

    /**
     * @var mixed
     */
    private $logger;

    public function before($param)
    {
        $monolog = new Logger('IntegrateLog');
        $handler = new StreamHandler(LOG_FILE, Logger::NOTICE);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $monolog->pushHandler($handler);
        $this->logger = $monolog;
    }

    public function integrateAll($param)
    {
        return $this->evaluate($param);
    }

    public function evaluate($param)
    {
        $this->view = false;

        $db = Sgbd::sql(DB_DEFAULT);
        $parsedFiles = 0;

        $parsedFiles += $this->integrateLocalPartFiles($db, self::MAX_FILE_AT_ONCE);

        if ($parsedFiles >= self::MAX_FILE_AT_ONCE) {
            $db->sql_close();
            return true;
        }

        $files = glob(EngineV4::PATH_PIVOT_FILE . '*' . EngineV4::SEPERATOR . 'logs');
        if (empty($files)) {
            $db->sql_close();
            usleep(100000);
            return true;
        }

        array_multisort(array_map('filemtime', $files), SORT_NUMERIC, SORT_ASC, $files);

        foreach ($files as $file) {
            $storage = new StorageFile($file);
            $data = new SharedMemory($storage);
            $payload = $this->normalizeSharedMemoryPayload($data->getData());

            [$events, $cursorUpdates] = $this->extractPayload($payload);

            if (!empty($events)) {
                $this->insertRawEvents($db, $events);
                $this->updateAggregates($db, $events);
            }

            if (!empty($cursorUpdates)) {
                $this->upsertCursors($db, $cursorUpdates);
            }

            if (file_exists($file)) {
                unlink($file);
            }

            $parsedFiles++;
            if ($parsedFiles >= self::MAX_FILE_AT_ONCE) {
                break;
            }
        }

        $db->sql_close();
        return true;
    }

    private function integrateLocalPartFiles($db, int $limit): int
    {
        $files = glob(DATA . 'logs/*/*/*/*.part.*') ?: [];
        if (empty($files)) {
            return 0;
        }

        sort($files, SORT_STRING);
        $processed = 0;

        foreach ($files as $file) {
            $doneMarker = dirname($file) . '/.' . basename($file) . '.done';
            $metaPath = dirname($file) . '/.' . basename($file) . '.meta.json';

            if (file_exists($doneMarker) || !file_exists($metaPath)) {
                continue;
            }

            $meta = json_decode((string)file_get_contents($metaPath), true);
            if (!is_array($meta)) {
                touch($doneMarker);
                continue;
            }

            $content = (string)file_get_contents($file);
            $events = MysqlLogCollector::buildFileEvents(
                (int)($meta['id_mysql_server'] ?? 0),
                (string)($meta['log_type'] ?? ''),
                (string)($meta['source_name'] ?? ''),
                (int)($meta['inode'] ?? 0),
                (int)($meta['offset_start'] ?? 0),
                $content
            );

            if (!empty($events)) {
                $this->insertRawEvents($db, $events);
                $this->updateAggregates($db, $events);
            }

            touch($doneMarker);
            $processed++;

            if ($processed >= $limit) {
                break;
            }
        }

        return $processed;
    }

    /**
     * @param mixed $payload
     * @return array{0: array<int,array<string,mixed>>, 1: array<int,array<string,mixed>>}
     */
    private function extractPayload($payload): array
    {
        $events = [];
        $cursorUpdates = [];

        if (!is_array($payload)) {
            return [$events, $cursorUpdates];
        }

        foreach ($payload as $elem) {
            if (!is_array($elem)) {
                continue;
            }

            foreach ($elem as $timestamp => $servers) {
                if (!is_array($servers)) {
                    continue;
                }

                foreach ($servers as $idServer => $serverPayload) {
                    if (!is_array($serverPayload)) {
                        continue;
                    }

                    foreach (($serverPayload['events'] ?? []) as $event) {
                        if (!is_array($event)) {
                            continue;
                        }

                        if (empty($event['event_time'])) {
                            $event['event_time'] = date('Y-m-d H:i:s', (int)$timestamp);
                        }
                        $events[] = $event;
                    }

                    foreach (($serverPayload['cursor_updates'] ?? []) as $cursor) {
                        if (!is_array($cursor)) {
                            continue;
                        }
                        $cursor['id_mysql_server'] = (int)($cursor['id_mysql_server'] ?? $idServer);
                        $cursorUpdates[] = $cursor;
                    }
                }
            }
        }

        return [$events, $cursorUpdates];
    }

    /**
     * @param mixed $payload
     * @return mixed
     */
    private function normalizeSharedMemoryPayload($payload)
    {
        if (is_array($payload)) {
            return $payload;
        }

        if (is_object($payload)) {
            if (method_exists($payload, 'getData')) {
                return $payload->getData();
            }

            if (property_exists($payload, 'data')) {
                return $payload->data;
            }

            return (array)$payload;
        }

        return $payload;
    }

    /**
     * @param array<int,array<string,mixed>> $events
     */
    private function insertRawEvents($db, array $events): void
    {
        $rows = [];

        foreach ($events as $event) {
            $eventTime = $event['event_time'] ?? null;
            if (empty($eventTime)) {
                $eventTime = date('Y-m-d H:i:s');
            }

            $rows[] = sprintf(
                '(%d,"%s","%s","%s",%s,%s,%s,%s,%s,%s,%s,%s,"%s","%s",%s,"%s",NOW())',
                (int)$event['id_mysql_server'],
                $db->sql_real_escape_string((string)$event['log_type']),
                $db->sql_real_escape_string((string)$event['source_kind']),
                $db->sql_real_escape_string((string)$event['log_path']),
                $this->sqlNullableString($db, $eventTime),
                $this->sqlNullableInt($event['inode'] ?? null),
                $this->sqlNullableInt($event['offset_start'] ?? null),
                $this->sqlNullableInt($event['offset_end'] ?? null),
                $this->sqlNullableString($db, $event['user_name'] ?? null),
                $this->sqlNullableString($db, $event['host_name'] ?? null),
                $this->sqlNullableString($db, $event['process_name'] ?? null),
                $this->sqlNullableString($db, $event['level'] ?? null),
                $db->sql_real_escape_string((string)($event['error_code'] ?? '')),
                $db->sql_real_escape_string((string)$event['message']),
                $this->sqlNullableString($db, $event['meta_json'] ?? null),
                $db->sql_real_escape_string((string)$event['dedup_key'])
            );
        }

        if (empty($rows)) {
            return;
        }

        $sql = 'INSERT IGNORE INTO ssh_log_mysql_line '
            . '(`id_mysql_server`,`log_type`,`source_kind`,`log_path`,`event_time`,`inode`,`offset_start`,`offset_end`,`user_name`,`host_name`,`process_name`,`level`,`error_code`,`message`,`meta_json`,`dedup_key`,`date_inserted`) VALUES '
            . implode(",\n", $rows);

        $db->sql_query($sql);
    }

    /**
     * @param array<int,array<string,mixed>> $events
     */
    private function updateAggregates($db, array $events): void
    {
        $grouped = [
            'minute' => [],
            'hour' => [],
            'day' => [],
        ];

        foreach ($events as $event) {
            if (empty($event['event_time'])) {
                continue;
            }

            $eventTime = (string)$event['event_time'];
            $bucketMap = [
                'minute' => date('Y-m-d H:i:00', strtotime($eventTime)),
                'hour' => date('Y-m-d H:00:00', strtotime($eventTime)),
                'day' => date('Y-m-d 00:00:00', strtotime($eventTime)),
            ];

            foreach ($bucketMap as $granularity => $bucketStart) {
                $key = implode('|', [
                    (int)$event['id_mysql_server'],
                    (string)$event['log_type'],
                    $bucketStart,
                ]);

                if (!isset($grouped[$granularity][$key])) {
                    $grouped[$granularity][$key] = [
                        'id_mysql_server' => (int)$event['id_mysql_server'],
                        'log_type' => (string)$event['log_type'],
                        'bucket_start' => $bucketStart,
                        'count_total' => 0,
                    ];
                }

                $grouped[$granularity][$key]['count_total']++;
            }
        }

        foreach ($grouped as $granularity => $rows) {
            if (empty($rows)) {
                continue;
            }

            $values = [];
            foreach ($rows as $row) {
                $values[] = sprintf(
                    '(%d,"%s","%s",%d)',
                    (int)$row['id_mysql_server'],
                    $db->sql_real_escape_string((string)$row['log_type']),
                    $db->sql_real_escape_string((string)$row['bucket_start']),
                    (int)$row['count_total']
                );
            }

            $sql = 'INSERT INTO ssh_log_mysql_agg_' . $granularity . ' (`id_mysql_server`,`log_type`,`bucket_start`,`count_total`) VALUES '
                . implode(",\n", $values)
                . ' ON DUPLICATE KEY UPDATE `count_total` = `count_total` + VALUES(`count_total`)';

            $db->sql_query($sql);
        }
    }

    /**
     * @param array<int,array<string,mixed>> $cursorUpdates
     */
    private function upsertCursors($db, array $cursorUpdates): void
    {
        $values = [];

        foreach ($cursorUpdates as $cursor) {
            $values[] = sprintf(
                '(%d,"%s","%s","%s",%s,%s,%s,NOW())',
                (int)$cursor['id_mysql_server'],
                $db->sql_real_escape_string((string)$cursor['log_type']),
                $db->sql_real_escape_string((string)$cursor['source_kind']),
                $db->sql_real_escape_string((string)$cursor['source_name']),
                $this->sqlNullableInt($cursor['inode'] ?? null),
                $this->sqlNullableInt($cursor['last_offset'] ?? null),
                $this->sqlNullableString($db, $cursor['last_event_time'] ?? null)
            );
        }

        if (empty($values)) {
            return;
        }

        $sql = 'INSERT INTO ssh_log_mysql_cursor (`id_mysql_server`,`log_type`,`source_kind`,`source_name`,`inode`,`last_offset`,`last_event_time`,`updated_at`) VALUES '
            . implode(",\n", $values)
            . ' ON DUPLICATE KEY UPDATE `inode`=VALUES(`inode`), `last_offset`=VALUES(`last_offset`), `last_event_time`=VALUES(`last_event_time`), `updated_at`=NOW()';

        $db->sql_query($sql);
    }

    /**
     * @param mixed $value
     */
    private function sqlNullableInt($value): string
    {
        if ($value === null || $value === '') {
            return 'NULL';
        }

        return (string)(int)$value;
    }

    /**
     * @param mixed $value
     */
    private function sqlNullableString($db, $value): string
    {
        if ($value === null || $value === '') {
            return 'NULL';
        }

        return '"' . $db->sql_real_escape_string((string)$value) . '"';
    }
}
