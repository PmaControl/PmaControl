# PMM Study: MySQL, MariaDB, ProxySQL, MySQL Router

Date: 2026-03-19  
Scope: Percona PMM open-source dashboards and collectors for MySQL, MariaDB, ProxySQL, and MySQL Router, compared with PmaControl coverage.

## Executive Summary

PMM ships a very large MySQL dashboard set built around `mysqld_exporter`, `node_exporter`, and, for some environments, cloud/container metrics. For MySQL-family services, PMM uses the same dashboard family for Oracle MySQL, Percona Server for MySQL, and MariaDB. MariaDB is detected in PMM agent code as a MySQL vendor, not as a separate dashboard family.

For ProxySQL, PMM has a dedicated dashboard family backed by `proxysql_exporter`.

For MySQL Router, PMM source does not contain a dedicated dashboard, exporter, or dashboard JSON. On that point, PmaControl already has more bespoke visibility than PMM because it collects and renders MySQL Router metadata and topology.

The main gap between PMM and PmaControl is not MySQL internals. On MySQL internals, PmaControl already covers a very large part of the ground. The main gap is system-level telemetry: PMM relies heavily on `node_exporter`, and sometimes container and cloud metrics, while PmaControl currently exposes a much smaller `ssh_stats` set.

## Methodology

This study used:

- PMM source code: `https://github.com/percona/pmm`
- Percona Grafana dashboards: `https://github.com/percona/grafana-dashboards`
- Local source inspection of:
  - `agent/utils/version/mysql.go`
  - `managed/models/agent_model.go`
  - `managed/services/victoriametrics/scrape_configs.go`
  - `build/ansible/roles/grafana/files/dashboards.yml`
  - `dashboards/MySQL/*.json`

Local source inspection confirmed:

- PMM dashboard provisioning path is file-based under Grafana.
- MySQL-family dashboards are backed by `mysqld_exporter`.
- ProxySQL dashboards are backed by `proxysql_exporter`.
- MySQL Router is absent from the PMM dashboard/exporter source tree.

## PMM Architecture For These Dashboards

### Collector Layer

Relevant PMM agent/exporter types found in source:

- `node_exporter`
- `mysqld_exporter`
- `proxysql_exporter`

Relevant files:

- `https://github.com/percona/pmm/blob/main/managed/models/agent_model.go`
- `https://github.com/percona/pmm/blob/main/managed/services/victoriametrics/scrape_configs.go`

The scrape configuration for `mysqld_exporter` is split by resolution and collector groups:

- High resolution:
  - `global_status`
  - `info_schema.innodb_metrics`
  - `custom_query.hr`
  - `standard.go`
  - `standard.process`
- Medium resolution:
  - `engine_innodb_status`
  - `info_schema.innodb_cmp`
  - `info_schema.innodb_cmpmem`
  - `info_schema.processlist`
  - `info_schema.query_response_time`
  - `perf_schema.eventswaits`
  - `perf_schema.file_events`
  - `slave_status`
  - `custom_query.mr`
- Low resolution:
  - `binlog_size`
  - `engine_tokudb_status`
  - `global_variables`
  - `heartbeat`
  - `info_schema.clientstats`
  - `info_schema.userstats`
  - `perf_schema.eventsstatements`
  - `perf_schema.file_instances`
  - `custom_query.lr`
  - `plugins`
  - and optionally table statistics and index/table wait collectors

ProxySQL is much simpler in PMM source:

- one standard exporter scrape path for `proxysql_exporter`
- dedicated dashboard logic sits in Grafana panels rather than multiple exporter profiles

### Vendor Detection

MariaDB is detected by PMM as a MySQL vendor in:

- `https://github.com/percona/pmm/blob/main/agent/utils/version/mysql.go`

Vendor enum:

- `oracle`
- `percona`
- `mariadb`

Conclusion:

- PMM does not maintain a separate MariaDB dashboard family for the core MySQL dashboards.
- It reuses the same dashboard family and adapts behavior through vendor and metric availability.

### Dashboard Provisioning

Grafana dashboard provisioning is file-based in PMM:

- `https://github.com/percona/pmm/blob/main/build/ansible/roles/grafana/files/dashboards.yml`

That file points Grafana at:

- `/usr/share/percona-dashboards/panels/pmm-app/dist/dashboards/`

## Dashboard Inventory

The following dashboards were found under `dashboards/MySQL/`.

### Core MySQL / MariaDB Dashboards

| Dashboard | UID | Panels | Main panel types | Main sections | Main metric families |
| --- | --- | ---: | --- | --- | --- |
| MySQL Instance Summary | `mysql-instance-summary` | 60 | graph, stat, row, text | Service Summary, Connections, Threads, Temp Objects, Sorts, Locks, Network, Memory, Query Cache | `mysql_global_status_*`, `mysql_global_variables_*`, `node_*`, `container_*`, `rdsosmetrics_*` |
| MySQL Instances Overview | `mysql-instance-overview` | 90 | stat, graph, polystat, row, barchart | Overview, Connections, Threads, Queries, InnoDB I/O, Temp Objects, Sorts, Locks, Network | `mysql_global_status_*`, `mysql_global_variables_*` |
| MySQL Instances Compare | `mysql-instance-compare` | 48 | graph, row, stat, table | Overview, Connections, Questions, Threads, Temp Objects, Sorts, Locks, Network, Memory | `mysql_global_status_*`, `mysql_global_variables_*` |
| MySQL InnoDB Details | `mysql-innodb` | 276 | stat, graph, row, text | InnoDB Activity, Disk IO, Buffer Pool, Checkpointing, Logging, Locking, Undo, AHI | `mysql_info_schema_*`, `mysql_global_status_*`, `mysql_global_variables_*`, `mysql_perf_schema_*` |
| MySQL InnoDB Compression Details | `mysql-innodb-compression` | 40 | stat, graph, row, text | Compression ops, failure rate, CPU, Buffer Pool, MySQL Summary, Node Summary | `mysql_global_status_innodb_*`, `node_*`, container/cloud metrics |
| MySQL MyISAM / Aria Details | `mysql-myisamaria` | 37 | graph, stat, row, text | MyISAM Metrics, Aria Metrics, MySQL Summary, Node Summary | `mysql_global_status_myisam_*`, `mysql_global_status_aria_*`, `node_*` |
| MySQL MyRocks Details | `mysql-myrocks` | 50 | timeseries, stat, text, row | MySQL Summary, Node Summary | `mysql_global_status_rocksdb_*`, `node_*`, container/cloud metrics |
| MySQL Performance Schema Details | `mysql-performance-schema` | 38 | graph, stat, text, row | Performance Schema waits and related summaries | `mysql_perf_schema_*`, `mysql_global_status_*`, `mysql_global_variables_*` |
| MySQL Query Response Time Details | `mysql-queryresponsetime` | 38 | graph, stat, row, text | Avg response time, distributions, read/write split | `mysql_info_schema_query_response_time_*`, `mysql_global_status_*`, `mysql_global_variables_*` |
| MySQL Replication Summary | `mysql-replicaset-summary` | 41 | stat, timeseries, text, row | Replication lag, applier/IO, binlog, summary | `mysql_slave_status_*`, `mysql_binlog_*`, `mysql_global_status_*` |
| MySQL Group Replication Summary | `mysql-group-replicaset-summary` | 20 | graph, row, state-timeline, table | Overview, Delay, Transactions, Conflicts | `mysql_perf_schema_replication_group_*` |
| MySQL Table Details | `mysql-table` | 42 | graph, stat, row, table, text | Largest tables, size, table activity, rows read/changed | `mysql_info_schema_tables_*`, `mysql_info_schema_table_statistics_*`, `mysql_global_status_*` |
| MySQL User Details | `mysql-user` | 60 | graph, stat, row, table, state-timeline, bargauge | Connections, row ops, transactions, commands | `mysql_info_schema_user_statistics_*`, `mysql_global_status_*` |
| MySQL Wait Event Analyses Details | `mysql-waitevents-analysis` | 32 | graph, stat, text, row | Wait events and related summaries | `mysql_perf_schema_events_waits_*`, `mysql_global_status_*` |
| MySQL Command/Handler Counters Compare | `mysql-commandhandler-compare` | 10 | row, text, graph | Commands and handler counters | `mysql_global_status_commands_total`, `mysql_global_status_handlers_total` |
| MySQL Amazon Aurora Details | `mysql-amazonaurora` | 6 | graph | Aurora-specific runtime metrics | `mysql_global_status_aurora*` |

### Galera / PXC Dashboards

| Dashboard | UID | Panels | Main panel types | Main sections | Main metric families |
| --- | --- | ---: | --- | --- | --- |
| PXC Galera Cluster Summary | `pxc-cluster-summary` | 16 | timeseries, graph | Cluster-level wsrep and EVS | `mysql_global_status_wsrep_*`, `mysql_galera_*` |
| PXC Galera Node Summary | `pxc-node-summary` | 17 | timeseries, stat, graph | Node-level wsrep, queues, conflicts, flow control | `mysql_global_status_wsrep_*`, `mysql_galera_*` |
| PXC Galera Nodes Compare | `pxc-nodes-compare` | 28 | row, timeseries, stat | General metrics, queues, conflicts, writesets, network | `mysql_global_status_wsrep_*`, `mysql_galera_*` |

### ProxySQL Dashboard

| Dashboard | UID | Panels | Main panel types | Main sections | Main metric families |
| --- | --- | ---: | --- | --- | --- |
| ProxySQL Instance Summary | `proxysql-instance-summary` | 46 | timeseries, row, stat, graph, heatmap, state-timeline, table | Instance Stats, Connections, Pool Usage, Query Latency, Query Cache, Memory, Node Summary | `proxysql_*`, `node_*` |

### MySQL Router

Source search over both PMM repos returned no MySQL Router dashboard, exporter, or route-specific dashboard JSON.

Conclusion:

- PMM open source, as inspected here, has no dedicated MySQL Router monitoring dashboard comparable to its MySQL or ProxySQL coverage.

## Visualization Patterns Used By PMM

Across the inspected dashboards, PMM uses these visualization families:

- line/time-series graphs
- stat cards
- rows/section containers
- tables
- state timeline
- heatmap
- bar chart / bar gauge
- polystat
- text panels for documentation or caveats

Observed dashboard sizing by complexity:

- small: 6 to 20 panels
- medium: 30 to 60 panels
- large: 90 panels
- very large: 276 panels for `MySQL InnoDB Details`

Observed pattern:

- dashboard “size” is mostly driven by panel count and section depth, not by a radically different per-panel model.
- `InnoDB Details` is the deepest, most decomposed dashboard by far.

## Metric Inventory

### High-Level Count

Across the inspected dashboard set, the source expressions reference:

- 569 unique metric identifiers

Breakdown by prefix:

- `mysql_*`: 484
- `proxysql_*`: 41
- `node_*`: 24
- `container_*`: 15
- `rdsosmetrics_*`: 2
- `azure_*`: 2
- `aws_*`: 1

Interpretation:

- PMM’s MySQL-family dashboards are mainly built on database metrics.
- The next biggest contribution is system metrics via `node_exporter`.
- Cloud/container metrics are conditional enrichments, not the core.

### Main Metric Families And Their Meaning

#### MySQL / MariaDB Internal Metrics

- `mysql_global_status_*`
  - source: `SHOW GLOBAL STATUS`
  - examples: connections, temp tables, bytes sent/received, InnoDB counters, wsrep counters
- `mysql_global_variables_*`
  - source: `SHOW GLOBAL VARIABLES`
  - examples: buffer sizes, cache settings, plugin options
- `mysql_info_schema_*`
  - source: `INFORMATION_SCHEMA`
  - examples: table stats, user stats, query response time, InnoDB metrics, process list
- `mysql_perf_schema_*`
  - source: `PERFORMANCE_SCHEMA`
  - examples: waits, events statements, replication group metrics
- `mysql_slave_status_*`
  - source: replication metadata, equivalent to replica status fields
- `mysql_binlog_*`
  - source: binlog collectors
- `mysql_galera_*` and `mysql_global_status_wsrep_*`
  - source: Galera / wsrep status and variables

#### ProxySQL Metrics

- `proxysql_mysql_status_*`
- `proxysql_connection_pool_*`
- `proxysql_stats_memory_*`
- `proxysql_mysql_command_*`

Source:

- ProxySQL stats tables exported by `proxysql_exporter`

#### System Metrics

- `node_*`
  - source: `node_exporter`
  - CPU, memory, filesystem, network, load, processes, boot time
- `container_*`
  - source: container/cadvisor style exporters or PMM environment integration
- `rdsosmetrics_*`, `aws_*`, `azure_*`
  - source: cloud-provider integrations

### Sample Metric Names

Representative sample from the extracted inventory:

- `mysql_global_status_aborted_clients`
- `mysql_global_status_aborted_connects`
- `mysql_global_status_bytes_received`
- `mysql_global_status_bytes_sent`
- `mysql_global_status_created_tmp_disk_tables`
- `mysql_global_status_created_tmp_tables`
- `mysql_global_status_innodb_buffer_pool_read_requests`
- `mysql_global_status_innodb_buffer_pool_reads`
- `mysql_global_status_innodb_checkpoint_age`
- `mysql_global_status_innodb_data_reads`
- `mysql_global_status_innodb_data_writes`
- `mysql_global_status_innodb_log_writes`
- `mysql_global_status_wsrep_cluster_size`
- `mysql_global_status_wsrep_flow_control_paused_ns`
- `mysql_global_status_wsrep_local_recv_queue`
- `mysql_perf_schema_replication_group_member_info`
- `mysql_perf_schema_replication_group_worker_lag_in_seconds`
- `mysql_info_schema_user_statistics_connected_time_seconds_total`
- `mysql_info_schema_query_response_time_seconds_total_bucket`
- `proxysql_mysql_status_active_transactions`
- `proxysql_connection_pool_status`
- `proxysql_stats_memory_metrics_memory_bytes`
- `node_memory_MemAvailable_bytes`
- `node_cpu_seconds_total`
- `node_filesystem_free_bytes`

## How PMM Calculates Or Derives Information

PMM dashboards use PromQL expressions on top of raw exporter metrics. The main transformation patterns are:

- rate-based derivation:
  - `rate(counter[interval])`
  - for throughput, queries/sec, bytes/sec, commands/sec
- cumulative-to-point conversion:
  - `increase(counter[interval])`
- ratios and percentages:
  - buffer hit rates
  - cache efficiency
  - lock contention ratios
  - replication delay relationships
- stacked series:
  - memory breakdown
  - command mix
  - connection states
- histogram/heatmap panels:
  - query latency distributions
  - ProxySQL latency heatmaps
- state timelines:
  - Group Replication member state
  - some status-oriented views

The calculation logic is therefore split:

- exporter side:
  - converts SQL/system data into Prometheus metrics
- Grafana side:
  - derives rates, percentages, aggregations, quantiles, histograms

## PmaControl Coverage Versus PMM

### 1. MySQL / MariaDB Internal Metrics

Coverage in PmaControl is strong.

Observed in local code:

- variables and status extraction via `Extraction2`
- deep topology and cluster rendering in `Dot3`
- Galera fields already ingested and rendered
- Group Replication fields already ingested and rendered
- ProxySQL runtime data ingested separately
- MySQL Router metadata and routes ingested separately
- SSH-side machine metrics ingested into `ssh_stats`

Conclusion:

- On core MySQL and MariaDB internals, PmaControl is already very close to PMM in raw data availability.
- The main remaining work is visualization depth and aggregation style, not basic data absence.

### 2. InnoDB

PmaControl likely has most of the raw ingredients:

- `SHOW GLOBAL STATUS`
- `SHOW GLOBAL VARIABLES`
- InnoDB-related status fields
- additional detail pages already exist in the repo

PMM advantage:

- the `InnoDB Details` dashboard is far more decomposed and curated
- it turns many raw counters into specialized panels with dedicated explanations

Conclusion:

- PmaControl seems data-rich enough for a large part of this area
- PMM is ahead mainly in dashboard productization

### 3. Performance Schema, Query Response Time, User and Table Statistics

PmaControl can cover part of this area if the underlying server features are enabled and collected.

Key point:

- PMM expects dedicated exporter collectors such as `perf_schema.eventswaits`, `info_schema.userstats`, `info_schema.tablestats`, `info_schema.query_response_time`
- PmaControl has comparable extraction ability in many cases, but not always the same normalized time-series model already packaged into dashboards

Conclusion:

- mostly feasible in PmaControl
- but less standardized and less packaged today than PMM

### 4. Replication, Group Replication, Galera

PmaControl is already strong here.

Observed:

- classic replication fields
- Group Replication metadata
- Galera / wsrep status, provider info, cluster topology
- advanced DOT rendering for Galera and Group Replication

Conclusion:

- PmaControl is competitive or stronger on topology rendering
- PMM is stronger on time-series panel density and ready-made dashboards

### 5. ProxySQL

PmaControl currently has:

- runtime ProxySQL tables
- ProxySQL association logic
- ProxySQL topology rendering
- some error/runtime extraction

PMM adds:

- dedicated exporter normalization
- richer panel family for pool usage, latency distributions, heatmaps, query cache, memory, command rates

Conclusion:

- PmaControl has the runtime information needed for control-plane visibility
- PMM is richer for time-series observability and latency distribution views

### 6. MySQL Router

This is the clearest asymmetry.

PMM:

- no dedicated MySQL Router dashboard found
- no exporter path found

PmaControl:

- has `mysqlrouter_routes`
- has `mysqlrouter_metadata_config`
- has `mysqlrouter_metadata_status`
- has architecture/topology rendering for MySQL Router

Conclusion:

- for MySQL Router, PmaControl already has a custom observability advantage

### 7. System Metrics

This is where PMM is materially ahead.

PMM system coverage comes from:

- `node_exporter`
- optional `container_*`
- optional cloud metrics

Typical PMM system metrics:

- CPU by mode
- memory breakdown
- filesystem usage
- filesystem throughput
- network throughput
- load average
- process pressure
- boot time

PmaControl system coverage observed in `ssh_stats` is much smaller:

- `memory_total`
- `memory_used`
- `memory_free`
- `memory_shared`
- `memory_buff_cache`
- `memory_available`
- `swap_total`
- `swap_used`
- `swap_free`
- `cpu_usage`
- `cpu_detail`
- `disks`
- `ips`
- `memory_detail_kb`
- some MySQL datadir and SST-specific fields

Conclusion:

- PMM has much richer operating-system telemetry than PmaControl
- this is the biggest measurable gap

## What PmaControl Already Has Versus What Is Missing

### Already Strong

- MySQL variables/status
- replication
- Galera
- Group Replication
- ProxySQL runtime state
- MySQL Router topology/metadata
- server availability views
- custom architecture rendering

### Weaker Than PMM

- system metrics breadth
- prebuilt dashboard density
- histogram / heatmap usage
- normalized exporter-style metric naming for all subsystems
- long-tail OS and cloud metrics

### Not Present In PMM But Present In PmaControl

- custom MySQL Router monitoring and topology
- custom DOT/architecture views tuned to mixed MySQL ecosystems

## Recommendations

### Priority 1

Add a stronger system metric model in PmaControl.

Best path:

- either ingest `node_exporter` style metrics
- or significantly expand `ssh_stats`

Minimum target:

- CPU by mode
- network rx/tx by interface
- disk read/write bytes and IOPS
- filesystem usage per mount
- memory breakdown compatible with PMM-style panels

### Priority 2

Build curated dashboard families rather than isolated pages.

Good candidates:

- MySQL Instance Summary
- InnoDB Details
- Replication Summary
- Group Replication Summary
- ProxySQL Instance Summary
- MySQL Router Summary, where PMM has no answer

### Priority 3

Normalize collector-to-dashboard mapping.

PMM is strong because each panel family maps to a known exporter metric family. PmaControl would benefit from:

- a stable internal metric dictionary
- explicit source annotation for each graph
- reusable aggregations for rate/ratio/histogram behavior

## Final Assessment

If the question is "Does PMM have more MySQL/MariaDB internals than PmaControl?", the answer is: not by a large margin in raw data.

If the question is "Does PMM package those metrics better into ready-made dashboards?", the answer is clearly yes.

If the question is "Where is PmaControl objectively behind PMM?", the answer is:

- system metrics
- histogram/heatmap-oriented observability
- exporter-style normalization

If the question is "Where is PmaControl already ahead of PMM?", the answer is:

- MySQL Router visibility
- custom mixed-topology and architecture rendering

## Sources

- PMM main repo: `https://github.com/percona/pmm`
- PMM dashboards repo: `https://github.com/percona/grafana-dashboards`
- MySQL vendor detection: `https://github.com/percona/pmm/blob/main/agent/utils/version/mysql.go`
- Agent model types: `https://github.com/percona/pmm/blob/main/managed/models/agent_model.go`
- VictoriaMetrics scrape config generation: `https://github.com/percona/pmm/blob/main/managed/services/victoriametrics/scrape_configs.go`
- Grafana dashboard provisioning: `https://github.com/percona/pmm/blob/main/build/ansible/roles/grafana/files/dashboards.yml`
- Dashboard folder inspected: `https://github.com/percona/grafana-dashboards/tree/master/dashboards/MySQL`

