<?php

declare(strict_types=1);

namespace App\Library;

final class ReplicationMetadataDictionary
{
    private const TOOLTIPS = [
        'CHANNEL_NAME' => 'Replication channel name; empty string means the default channel.',
        'HOST' => 'Configured source host for the replication connection.',
        'PORT' => 'Configured source TCP port.',
        'USER' => 'Replication user configured for the channel.',
        'USER_NAME' => 'Replication user configured for the channel.',
        'USER_PASSWORD' => 'Stored replication password; always masked by PmaControl.',
        'NETWORK_INTERFACE' => 'Optional local network interface used for source connections.',
        'AUTO_POSITION' => 'Whether GTID auto-positioning is enabled.',
        'SSL_ALLOWED' => 'Whether SSL/TLS is required, allowed or disabled for the channel.',
        'SSL_CA_FILE' => 'Certificate authority file used to verify the source certificate.',
        'SSL_CERTIFICATE' => 'Client certificate configured for the replication connection.',
        'SSL_KEY' => 'Client key configured for the replication connection.',
        'HEARTBEAT_INTERVAL' => 'Expected heartbeat period in seconds.',
        'TLS_VERSION' => 'Configured TLS protocol versions.',
        'TLS_CIPHERSUITES' => 'Configured TLS ciphersuites.',
        'THREAD_ID' => 'Performance Schema thread id for the replication worker.',
        'SERVICE_STATE' => 'ON/OFF execution state of the replication thread.',
        'LAST_ERROR_NUMBER' => 'Last replication error number reported by the thread.',
        'LAST_ERROR_MESSAGE' => 'Last replication error message reported by the thread.',
        'LAST_ERROR_TIMESTAMP' => 'Timestamp of the last replication error.',
        'WORKER_ID' => 'Parallel applier worker id.',
        'APPLYING_TRANSACTION' => 'GTID currently being applied by the worker.',
        'LAST_APPLIED_TRANSACTION' => 'Most recent GTID applied by the worker.',
        'LAST_HEARTBEAT_TIMESTAMP' => 'Last heartbeat received from the source.',
        'COUNT_RECEIVED_HEARTBEATS' => 'Number of heartbeats received on the channel.',
        'RELAY_LOG_FILE' => 'Current relay log file.',
        'RELAY_LOG_POS' => 'Current relay log position.',
        'MASTER_LOG_NAME' => 'Source binary log file recorded in mysql.slave_master_info.',
        'MASTER_LOG_POS' => 'Source binary log position recorded in mysql.slave_master_info.',
    ];

    public static function tooltip(string $column): string
    {
        $column = strtoupper($column);

        return self::TOOLTIPS[$column] ?? 'Replication metadata column collected from MySQL/MariaDB.';
    }

    /**
     * @param list<array<string,mixed>> $rows
     * @return list<array<string,mixed>>
     */
    public static function maskSensitiveRows(array $rows): array
    {
        return array_map(static function (array $row): array {
            foreach ($row as $key => $value) {
                if (preg_match('/PASSWORD|SECRET|PRIVATE_KEY/i', (string)$key)) {
                    $row[$key] = '****';
                }
            }
            return $row;
        }, $rows);
    }

    /**
     * @return array<string,string>
     */
    public static function tooltipMapForRows(array $rows): array
    {
        $map = [];
        foreach ($rows as $row) {
            foreach (array_keys($row) as $column) {
                $map[(string)$column] = self::tooltip((string)$column);
            }
        }

        return $map;
    }
}
