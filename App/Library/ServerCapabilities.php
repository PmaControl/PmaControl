<?php

declare(strict_types=1);

namespace App\Library;

use InvalidArgumentException;

final class ServerCapabilities
{
    private const MATRIX = [
        'information_schema_max_statement_time' => [
            'MariaDB' => '10.1.1',
        ],
        'select_max_execution_time_hint' => [
            'MySQL' => '5.7',
            'Percona' => '5.7',
            'Percona Server' => '5.7',
        ],
        'information_schema_tables_temporary_column' => [
            'MariaDB' => '10.3',
            'MySQL' => '5.7',
            'Percona' => '5.7',
            'Percona Server' => '5.7',
        ],
        'processlist_supported' => [
            'MySQL' => '5.1',
            'MariaDB' => '5.1',
            'Percona' => '5.1',
            'Percona Server' => '5.1',
        ],
        'mysql8_processlist_trx_columns' => [
            'MySQL' => '8.0',
        ],
        'show_binary_log_status' => [
            'MySQL' => '8.4',
            'Percona' => '8.4',
            'Percona Server' => '8.4',
        ],
        'mysql_user_is_role_column' => [
            'MariaDB' => '10.0',
        ],
        'performance_schema_processlist_modern' => [
            'MySQL' => '8.0',
            'Percona' => '8.0',
            'Percona Server' => '8.0',
        ],
        'percona_processlist_56' => [
            'Percona' => '5.6',
            'Percona Server' => '5.6',
        ],
        'percona_processlist_57' => [
            'Percona' => '5.7',
            'Percona Server' => '5.7',
        ],
        'mariadb_skip_replication_variable' => [
            'MariaDB' => '5.5.21',
        ],
        // SHOW REPLICA STATUS / FOR CHANNEL '...' was introduced in
        // MySQL 8.0.22 (and the matching Percona Server release).
        // MariaDB has no such keyword — it always uses SLAVE / SHOW
        // ALL SLAVES STATUS — so it's intentionally absent here. (#830)
        'show_replica_status_syntax' => [
            'MySQL' => '8.0.22',
            'Percona' => '8.0.22',
            'Percona Server' => '8.0.22',
        ],
    ];

    public static function supports(object $db, string $feature): bool
    {
        if (!array_key_exists($feature, self::MATRIX)) {
            throw new InvalidArgumentException("Unknown server capability: ".$feature);
        }

        return (bool) $db->checkVersion(self::MATRIX[$feature]);
    }
}
