# Pmm Binlog

## Route / Code

- Route: `/Pmm/binlog/{id_mysql_server}`
- Controller action: `App/Controller/Pmm.php::binlog()`
- View: `App/view/Pmm/binlog.view.php`
- Shared view shell: `App/view/Pmm/dashboard.view.php`
- Frontend: `App/Webroot/js/Pmm/dashboard.js`

## Source

- PMM replication / binary log related dashboards and panels
- PMM `MySQL Replication Summary`
- PmaControl `mysql_binlog::*` collector

## Implemented Sections

### Capacity & retention

- cards
  - total binlog size
  - number of files
  - max binlog size
  - expire logs seconds
- charts
  - total binlog size
  - number of binlog files
- table
  - current runtime snapshot

Metrics:

- `mysql_binlog::binlog_total_size`
- `mysql_binlog::binlog_nb_files`
- `mysql_binlog::binlog_file_last`
- `variables::log_bin`
- `variables::sync_binlog`
- `variables::binlog_format`
- `variables::binlog_row_image`
- `variables::binlog_checksum`
- `variables::max_binlog_size`
- `variables::binlog_expire_logs_seconds`
- `variables::log_bin_basename`
- `status::gtid_current_pos`
- `status::gtid_binlog_pos`

### Replication summary

- cards
  - `connection_name`
  - `master_host:master_port`
  - `seconds_behind_master`
  - `slave_io_running`
  - `slave_sql_running`
  - `using_gtid`
- charts
  - replication lag from `seconds_behind_master`
- table
  - current source/channel state
  - last IO/SQL error codes and messages

Metrics:

- `slave::connection_name`
- `slave::master_host`
- `slave::master_port`
- `slave::seconds_behind_master`
- `slave::slave_io_running`
- `slave::slave_sql_running`
- `slave::using_gtid`
- `slave::last_io_errno`
- `slave::last_io_error`
- `slave::last_sql_errno`
- `slave::last_sql_error`

### Caches & buffers

- cards
  - `binlog_cache_size`
  - `binlog_stmt_cache_size`
  - `binlog_space_limit`

## Missing

- PMM-style binlog cache usage / disk spill counters are not yet exposed here unless collected separately
- relay log size / relay log growth are not yet exposed here
- applier throughput / worker internals remain outside this screen
