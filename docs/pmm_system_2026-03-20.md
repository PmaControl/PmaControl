# Pmm System

## Route / Code

- Route: `/Pmm/system/{id_mysql_server}`
- Controller action: `App/Controller/Pmm.php::system()`
- View: `App/view/Pmm/system.view.php`
- Shared view shell: `App/view/Pmm/dashboard.view.php`
- Frontend: `App/Webroot/js/Pmm/dashboard.js`

## PMM Source

- `dashboards/OS/Node_Summary.json`
- `dashboards/OS/CPU_Utilization_Details.json`
- `dashboards/OS/Memory_Details.json`
- `dashboards/OS/Disk_Details.json`
- `dashboards/OS/Network_Details.json`
- `dashboards/OS/Processes_Details.json`

## Implemented Sections

### CPU & load

PMM panel types:

- stat
- timeseries
- graph

Implemented in PmaControl:

- cards
  - current CPU usage
  - load average 1m / 5m / 15m
- charts
  - CPU usage
  - load average
- table
  - current CPU detail JSON snapshot

PmaControl metrics:

- `cpu_usage`
- `load_average_1_min`
- `load_average_5_min`
- `load_average_15_min`
- `cpu_detail`

### Memory

PMM panel types:

- stat
- timeseries
- graph

Implemented in PmaControl:

- cards
  - physical memory
  - memory used
  - swap total
  - swap used
- charts
  - memory utilization
  - swap utilization
- table
  - top process memory snapshot

PmaControl metrics:

- `memory_total`
- `memory_used`
- `swap_total`
- `swap_used`
- `memory_detail_kb`

### Disk & storage

PMM panel types:

- stat
- table
- timeseries

Implemented in PmaControl:

- cards
  - uptime
  - current mounts count
- table
  - current mountpoint usage snapshot

PmaControl metrics:

- `uptime`
- `disks`
- `disk_io_detail`
- `disk_reads_completed_total`
- `disk_writes_completed_total`
- `disk_read_bytes_total`
- `disk_write_bytes_total`
- `disk_io_time_ms_total`
- `disk_weighted_io_time_ms_total`

### Network

PMM panel types:

- stat
- timeseries
- graph

Collected in PmaControl:

- `network_detail`
- `network_protocol_detail`
- `network_rx_bytes_total`
- `network_tx_bytes_total`
- `network_rx_packets_total`
- `network_tx_packets_total`
- `network_rx_errors_total`
- `network_tx_errors_total`
- `network_rx_drop_total`
- `network_tx_drop_total`
- `network_tcp_retrans_segs_total`
- `network_tcp_in_errs_total`
- `network_tcp_out_rsts_total`
- `network_udp_in_errors_total`

### Processes / scheduler

PMM panel types:

- stat
- timeseries
- graph

Collected in PmaControl:

- `proc_stat_detail`
- `system_interrupts_total`
- `system_context_switches_total`
- `system_process_forks_total`
- `system_processes_running`
- `system_processes_blocked`
- `process_state_detail`
- `process_total`
- `process_running`
- `process_sleeping`
- `process_disk_sleep`
- `process_stopped`
- `process_zombie`
- `process_idle`

## PMM Metric Origins

PMM system dashboards rely mainly on:

- `node_exporter`
- PMM service inventory / summary components

## Equivalent In PmaControl

Current-value equivalents come from `ssh_stats` via `Aspirateur::getStats()` and are exposed through `Extraction2`.
PmaControl now also stores kernel snapshots from `/proc/stat`, `/proc/net/dev`, `/proc/net/snmp`, `/proc/net/netstat`, `sockstat`, `/proc/diskstats` and `ps`.

## Missing

- historical per-core CPU breakdown
- PMM-style per-device latency and queue-depth derivations still need UI formulas on top of the stored disk counters
- top-N network and process historical panels are not yet rebuilt in the UI
