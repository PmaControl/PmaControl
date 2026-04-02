# Pmm RocksDB

## Route / Code

- Route: `/Pmm/rocksdb/{id_mysql_server}`
- Controller action: `App/Controller/Pmm.php::rocksdb()`
- View: `App/view/Pmm/rocksdb.view.php`
- Shared view shell: `App/view/Pmm/dashboard.view.php`
- Frontend: `App/Webroot/js/Pmm/dashboard.js`

## PMM Source

- `dashboards/MySQL/MySQL_MyRocks_Details.json`

## Implemented Sections

### Read/write activity

PMM equivalent panels:

- DB Ops
- R/W

PmaControl charts:

- line: RocksDB row operations
- line: RocksDB bytes throughput

Metrics:

- `rocksdb_rows_read`
- `rocksdb_rows_inserted`
- `rocksdb_rows_updated`
- `rocksdb_rows_deleted`
- `rocksdb_bytes_read`
- `rocksdb_bytes_written`

### Cache & filters

PMM equivalent panels:

- Block cache
- index/filter hit rate
- memtable hit ratio

PmaControl charts:

- line: block cache hit ratio
- line: index cache hit ratio
- line: filter cache hit ratio
- line: memtable hit ratio

Metrics:

- `rocksdb_block_cache_hit`
- `rocksdb_block_cache_miss`
- `rocksdb_block_cache_index_hit`
- `rocksdb_block_cache_index_miss`
- `rocksdb_block_cache_filter_hit`
- `rocksdb_block_cache_filter_miss`
- `rocksdb_memtable_hit`
- `rocksdb_memtable_miss`
- `rocksdb_memtable_total`
- `rocksdb_memtable_unflushed`

### WAL & write path

PMM equivalent panels:

- WAL
- write path / flush path

PmaControl charts:

- line: WAL activity
- line: WAL bytes

Metrics:

- `rocksdb_wal_bytes`
- `rocksdb_wal_group_syncs`
- `rocksdb_wal_synced`
- `rocksdb_wal_size_limit_mb`
- `rocksdb_write_disable_wal`
- `rocksdb_flush_log_at_trx_commit`

### Stalls & locking

PMM equivalent panels:

- stalls / slowdowns / stops
- row locking

PmaControl charts:

- line: RocksDB stalls
- line: RocksDB stall time

Metrics:

- `rocksdb_stall_total_slowdowns`
- `rocksdb_stall_total_stops`
- `rocksdb_stall_micros`
- `rocksdb_row_lock_deadlocks`
- `rocksdb_row_lock_wait_timeouts`

## PMM Metric Origins

Main PMM origins:

- MyRocks exporter metrics
- MySQL/MariaDB `SHOW GLOBAL STATUS` values for MyRocks

## Equivalent In PmaControl

PmaControl already stores a useful subset of RocksDB/MyRocks status and variable metrics, enough to rebuild the main operational panels above.

## Missing

- some PMM MyRocks panels remain uncovered
  - bloom details
  - seek / reseek internals
  - more compaction internals
  - advanced cache byte movement panels
- the structure is ready to extend as soon as those metrics are promoted in the catalog
