<?php

namespace App\Library;

/**
 * Column tooltips for MySQL sys schema views, performance_schema tables,
 * and information_schema tables. Used to display help text in table headers.
 *
 * Sources:
 * - https://dev.mysql.com/doc/refman/8.0/en/sys-schema.html
 * - https://dev.mysql.com/doc/refman/8.0/en/performance-schema.html
 * - https://mariadb.com/kb/en/performance-schema/
 */
class SysTooltips
{
    private static $tooltips = [
        // ── sys.statement_analysis / x$statement_analysis ──
        'query'                    => 'Normalized SQL statement (digest text). Parameters replaced with "?".',
        'db'                       => 'Default database when the statement was executed.',
        'full_scan'                => 'Number of times the statement performed a full table scan.',
        'exec_count'               => 'Total number of times the statement has been executed.',
        'err_count'                => 'Total number of errors produced by this statement.',
        'warn_count'               => 'Total number of warnings produced by this statement.',
        'total_latency'            => 'Total cumulative wait time for all executions of this statement.',
        'max_latency'              => 'Maximum single-execution wait time observed.',
        'avg_latency'              => 'Average wait time per execution.',
        'lock_latency'             => 'Total time spent waiting for table locks.',
        'rows_sent'                => 'Total rows returned to the client.',
        'rows_sent_avg'            => 'Average rows returned per execution.',
        'rows_examined'            => 'Total rows examined by the storage engine.',
        'rows_examined_avg'        => 'Average rows examined per execution.',
        'rows_affected'            => 'Total rows modified (INSERT/UPDATE/DELETE).',
        'rows_affected_avg'        => 'Average rows modified per execution.',
        'tmp_tables'               => 'Number of internal temporary tables created.',
        'tmp_disk_tables'          => 'Number of internal temporary tables written to disk (too large for memory).',
        'rows_sorted'              => 'Total rows sorted.',
        'sort_merge_passes'        => 'Number of merge passes by the sort algorithm.',
        'digest'                   => 'SHA-256 hash of the normalized statement. Unique identifier for the digest.',
        'first_seen'               => 'Timestamp when this statement was first observed.',
        'last_seen'                => 'Timestamp of the most recent execution.',

        // ── sys.schema_table_statistics ──
        'table_schema'             => 'Database (schema) name.',
        'table_name'               => 'Table name.',
        'total_latency'            => 'Total latency for all I/O on this table (read + write + misc).',
        'rows_fetched'             => 'Total rows read from the table.',
        'fetch_latency'            => 'Total latency for read operations.',
        'rows_inserted'            => 'Total rows inserted.',
        'insert_latency'           => 'Total latency for insert operations.',
        'rows_updated'             => 'Total rows updated.',
        'update_latency'           => 'Total latency for update operations.',
        'rows_deleted'             => 'Total rows deleted.',
        'delete_latency'           => 'Total latency for delete operations.',
        'io_read_requests'         => 'Number of I/O read requests for the table files.',
        'io_read'                  => 'Total bytes read from the table files.',
        'io_read_latency'          => 'Total latency for file read operations.',
        'io_write_requests'        => 'Number of I/O write requests.',
        'io_write'                 => 'Total bytes written to the table files.',
        'io_write_latency'         => 'Total latency for file write operations.',
        'io_misc_requests'         => 'Number of miscellaneous I/O requests (open, close, flush).',
        'io_misc_latency'          => 'Total latency for misc I/O operations.',

        // ── sys.schema_index_statistics ──
        'index_name'               => 'Name of the index.',
        'select_count'             => 'Number of times this index was used for a SELECT.',
        'select_latency'           => 'Total latency for SELECT using this index.',
        'insert_count'             => 'Number of times rows were inserted via this index.',
        'insert_latency'           => 'Total latency for INSERT operations on this index.',
        'update_count'             => 'Number of times this index was used for an UPDATE.',
        'update_latency'           => 'Total latency for UPDATE operations.',
        'delete_count'             => 'Number of times this index was used for a DELETE.',
        'delete_latency'           => 'Total latency for DELETE operations.',

        // ── sys.schema_unused_indexes ──
        'object_schema'            => 'Database containing the table.',
        'object_name'              => 'Table containing the unused index.',
        'index_name'               => 'Name of the index that has not been used since last restart.',

        // ── sys.memory_by_thread_by_current_bytes ──
        'thread_id'                => 'Performance Schema internal thread identifier.',
        'user'                     => 'User@host for the connection, or background thread name.',
        'current_count_used'       => 'Number of memory allocations currently active (not freed).',
        'current_allocated'        => 'Total bytes currently allocated by this thread.',
        'current_avg_alloc'        => 'Average bytes per active allocation.',
        'current_max_alloc'        => 'Largest single active allocation by this thread.',
        'total_allocated'          => 'Cumulative bytes allocated over the lifetime of this thread.',

        // ── sys.memory_global_by_current_bytes ──
        'event_name'               => 'Memory instrument name (e.g. memory/innodb/buf_buf_pool).',
        'current_count'            => 'Number of allocations currently active.',
        'current_alloc'            => 'Total bytes currently allocated for this instrument.',
        'high_count'               => 'High-water mark of active allocations.',
        'high_alloc'               => 'High-water mark of total bytes allocated.',

        // ── sys.innodb_lock_waits ──
        'wait_started'             => 'Timestamp when the lock wait began.',
        'wait_age'                 => 'How long the transaction has been waiting for the lock.',
        'wait_age_secs'            => 'Wait time in seconds.',
        'locked_table'             => 'Schema.table that is locked.',
        'locked_index'             => 'Index involved in the lock.',
        'locked_type'              => 'Lock type: RECORD, TABLE, etc.',
        'waiting_trx_id'           => 'Transaction ID of the waiting (blocked) transaction.',
        'waiting_trx_started'      => 'Start time of the waiting transaction.',
        'waiting_trx_age'          => 'Age of the waiting transaction.',
        'waiting_trx_rows_locked'  => 'Number of rows locked by the waiting transaction.',
        'waiting_trx_rows_modified'=> 'Number of rows modified by the waiting transaction.',
        'waiting_pid'              => 'Process ID of the waiting connection.',
        'waiting_query'            => 'Current query of the waiting connection.',
        'waiting_lock_id'          => 'Internal lock ID being waited for.',
        'waiting_lock_mode'        => 'Lock mode requested (S, X, IS, IX, etc.).',
        'blocking_trx_id'          => 'Transaction ID of the blocking (holding) transaction.',
        'blocking_trx_started'     => 'Start time of the blocking transaction.',
        'blocking_trx_age'         => 'Age of the blocking transaction.',
        'blocking_trx_rows_locked' => 'Number of rows locked by the blocking transaction.',
        'blocking_trx_rows_modified'=> 'Number of rows modified by the blocking transaction.',
        'blocking_pid'             => 'Process ID of the blocking connection.',
        'blocking_query'           => 'Current (or last) query of the blocking connection.',
        'blocking_lock_id'         => 'Internal lock ID held by the blocking transaction.',
        'blocking_lock_mode'       => 'Lock mode held (S, X, IS, IX, etc.).',
        'sql_kill_blocking_query'  => 'Ready-to-run KILL QUERY command for the blocking connection.',
        'sql_kill_blocking_connection' => 'Ready-to-run KILL command for the blocking connection.',

        // ── sys.host_summary ──
        'host'                     => 'Client hostname or IP address.',
        'statements'               => 'Total statements executed from this host.',
        'statement_latency'        => 'Total latency for all statements from this host.',
        'statement_avg_latency'    => 'Average latency per statement.',
        'table_scans'              => 'Number of full table scans performed.',
        'file_ios'                 => 'Total file I/O events.',
        'file_io_latency'          => 'Total latency for file I/O.',
        'current_connections'      => 'Number of connections currently open.',
        'total_connections'        => 'Total connections since server start.',
        'unique_users'             => 'Number of distinct users from this host.',
        'current_memory'           => 'Memory currently allocated for this host.',
        'total_memory_allocated'   => 'Cumulative memory allocated for this host.',

        // ── sys.user_summary ──
        'unique_hosts'             => 'Number of distinct hosts this user connected from.',

        // ── PROCESSLIST / sys.processlist ──
        'thd_id'                   => 'Internal thread ID (performance_schema).',
        'conn_id'                  => 'Connection ID (matches SHOW PROCESSLIST Id).',
        'command'                  => 'Current command type (Query, Sleep, Binlog Dump, etc.).',
        'state'                    => 'Current execution state (Sending data, Sorting result, etc.).',
        'time'                     => 'Seconds the thread has been in its current state.',
        'current_statement'        => 'Statement currently being executed (if any).',
        'last_statement'           => 'Last statement executed by this thread.',
        'last_statement_latency'   => 'Execution time of the last statement.',
        'progress'                 => 'Completion percentage for ALTER TABLE and similar operations.',
        'lock_latency'             => 'Time spent waiting for locks during the current statement.',
        'trx_latency'              => 'Duration of the current transaction.',
        'trx_state'                => 'Transaction state: ACTIVE, COMMITTED, ROLLED BACK.',
        'program_name'             => 'Client program name (if reported).',

        // ── sys.io_global_by_file_by_bytes ──
        'file'                     => 'Full file path.',
        'count_read'               => 'Number of read operations on this file.',
        'total_read'               => 'Total bytes read from this file.',
        'avg_read'                 => 'Average bytes per read operation.',
        'count_write'              => 'Number of write operations.',
        'total_write'              => 'Total bytes written.',
        'avg_write'                => 'Average bytes per write operation.',
        'total'                    => 'Total bytes (read + write).',
        'write_pct'                => 'Percentage of I/O that is write activity.',

        // ── sys.schema_auto_increment_columns ──
        'auto_increment_ratio'     => 'Current AUTO_INCREMENT value / max possible value. Near 1.0 = about to overflow.',
        'max_value'                => 'Maximum possible value for this column type.',
        'column_name'              => 'Name of the AUTO_INCREMENT column.',
        'data_type'                => 'Data type of the column (int, bigint, etc.).',
        'column_type'              => 'Full column definition including unsigned, etc.',

        // ── SHOW SLAVE / REPLICA STATUS (used in slave views) ──
        'Slave_IO_Running'         => 'Whether the I/O thread is running (reading binlogs from master).',
        'Slave_SQL_Running'        => 'Whether the SQL thread is running (applying events).',
        'Seconds_Behind_Master'    => 'Replication lag in seconds. NULL if threads are stopped.',
        'Master_Log_File'          => 'Current binlog file being read from master by the I/O thread.',
        'Read_Master_Log_Pos'      => 'Position in the master binlog up to which the I/O thread has read.',
        'Relay_Master_Log_File'    => 'Binlog file for the most recently applied event by the SQL thread.',
        'Exec_Master_Log_Pos'      => 'Position in the binlog up to which the SQL thread has applied.',

        // ── sys.sys_config ──
        'variable'                 => 'Configuration variable name for the sys schema.',
        'value'                    => 'Current configured value.',
        'set_time'                 => 'Timestamp when this variable was last modified.',
        'set_by'                   => 'User who last modified this variable.',
    ];

    /**
     * Get tooltip text for a column name.
     * Returns empty string if no tooltip is defined.
     */
    public static function get(string $column): string
    {
        $col = strtolower(trim($column));
        return self::$tooltips[$col] ?? '';
    }

    /**
     * Render a <th> with optional tooltip.
     * Returns HTML like: <th title="description">column_name</th>
     */
    public static function th(string $column): string
    {
        $tip = self::get($column);
        if ($tip !== '') {
            return '<th title="' . htmlspecialchars($tip, ENT_QUOTES) . '" style="cursor:help;border-bottom:1px dotted #94a3b8">'
                . htmlspecialchars($column) . ' <i class="fa fa-info-circle" style="font-size:10px;color:#94a3b8"></i></th>';
        }
        return '<th>' . htmlspecialchars($column) . '</th>';
    }
}
