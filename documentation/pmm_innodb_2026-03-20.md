# Pmm InnoDB

## Route / Code

- Route: `/Pmm/innodb/{id_mysql_server}`
- Controller action: `App/Controller/Pmm.php::innodb()`
- View: `App/view/Pmm/innodb.view.php`
- Shared view shell: `App/view/Pmm/dashboard.view.php`
- Frontend: `App/Webroot/js/Pmm/dashboard.js`

## PMM Source

- `dashboards/MySQL/MySQL_InnoDB_Details.json`

## Split Sections Implemented

### Activity & throughput

PMM equivalent panels:

- InnoDB Activity
- Data Bandwidth

PmaControl charts:

- line: InnoDB data bandwidth
- line: InnoDB row operations

Metrics:

- `innodb_data_read`
- `innodb_data_written`
- `innodb_rows_read`
- `innodb_rows_inserted`
- `innodb_rows_updated`
- `innodb_rows_deleted`
- `innodb_history_list_length`
- `innodb_row_lock_waits`
- `innodb_row_lock_time_avg`

### Buffer pool

PMM equivalent panels:

- Buffer Pool
- Replacement Management

PmaControl charts:

- line: buffer pool pages
- line: buffer pool read pressure
- line: buffer pool hit ratio

Metrics:

- `innodb_buffer_pool_size`
- `innodb_buffer_pool_pages_total`
- `innodb_buffer_pool_pages_free`
- `innodb_buffer_pool_pages_dirty`
- `innodb_buffer_pool_read_requests`
- `innodb_buffer_pool_reads`

### Logging & checkpointing

PMM equivalent panels:

- Logging
- Checkpointing and Flushing
- Log File Usage

PmaControl charts:

- line: redo log operations
- line: redo bytes written
- line: checkpoint age

Metrics:

- `innodb_log_file_size`
- `innodb_log_buffer_size`
- `innodb_log_write_requests`
- `innodb_log_writes`
- `innodb_os_log_fsyncs`
- `innodb_os_log_written`
- `innodb_log_waits`
- `innodb_checkpoint_age`
- `innodb_checkpoint_max_age`

### Flushing & page IO

PMM equivalent panels:

- Disk IO
- Page Operations

PmaControl charts:

- line: InnoDB page operations
- line: doublewrite activity

Metrics:

- `innodb_pages_created`
- `innodb_pages_read`
- `innodb_pages_written`
- `innodb_dblwr_pages_written`
- `innodb_dblwr_writes`
- `innodb_data_fsyncs`

### Locking, purge & undo

PMM equivalent panels:

- Locking
- Undo Space and Purging
- Online Operations

PmaControl charts:

- line: history list length
- line: row lock waits
- line: undo truncations
- line: online DDL progress

Metrics:

- `innodb_history_list_length`
- `innodb_row_lock_waits`
- `innodb_undo_truncations`
- `innodb_undo_tablespaces_total`
- `innodb_onlineddl_pct_progress`
- `innodb_onlineddl_rowlog_pct_used`

### Change buffer & adaptive hash

PMM equivalent panels:

- Change Buffer
- Adaptive Hash Index

PmaControl charts:

- line: change buffer activity
- line: change buffer size
- line: adaptive hash index searches

Metrics:

- `innodb_ibuf_merges`
- `innodb_ibuf_merged_inserts`
- `innodb_ibuf_merged_deletes`
- `innodb_ibuf_merged_delete_marks`
- `innodb_ibuf_size`
- `innodb_ibuf_free_list`
- `innodb_adaptive_hash_hash_searches`
- `innodb_adaptive_hash_non_hash_searches`

## PMM Metric Origins

Main PMM origins for this dashboard:

- `mysqld_exporter`
- MySQL/MariaDB status counters from `SHOW GLOBAL STATUS`
- selected variables from `SHOW GLOBAL VARIABLES`

## Equivalent In PmaControl

PmaControl uses:

- `Extraction2::display(...)` for current values
- `Extraction2::display(..., range=true)` for historical rows
- server-side bucketing and rate reconstruction in `PmmDashboardCatalog`

## Missing

- PMM still contains more panels than this first rebuild, especially some detailed flushing, contention and object-level IO panels
- PmaControl does not yet expose every PMM InnoDB exporter metric in this screen, but the structure is ready to extend
