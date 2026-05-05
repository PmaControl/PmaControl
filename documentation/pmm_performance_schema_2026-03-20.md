# PMM Performance Schema Screen Rebuild

## Route

- `Pmm/performance_schema/{id_mysql_server}`

## PMM reference

- Dashboard inspected: `MySQL Performance Schema Details`
- Demo URL: `https://pmmdemo.percona.com/pmm-ui/graph/d/mysql-performance-schema/mysql-performance-schema-details`

## Files

- Controller: `App/Controller/Pmm.php::performance_schema()`
- Builder: `App/Library/PmmDashboardCatalog.php::buildPerformanceSchema()`
- View: `App/view/Pmm/performance_schema.view.php`

## Implemented sections

### Enablement and digest sizing

- Type: line chart + stat cards + snapshot table
- PMM source family:
  - `mysql_global_variables_*`
  - `mysql_perf_schema_*`
- PmaControl equivalents:
  - `variables::performance_schema`
  - `variables::max_digest_length`
  - `variables::performance_schema_max_digest_length`
  - `variables::performance_schema_digests_size`
  - `variables::performance_schema_session_connect_attrs_size`

### History buffers

- Type: line chart + stat cards
- PmaControl equivalents:
  - `variables::performance_schema_events_statements_history_size`
  - `variables::performance_schema_events_statements_history_long_size`
  - `variables::performance_schema_events_waits_history_size`
  - `variables::performance_schema_events_waits_history_long_size`
  - `variables::performance_schema_events_stages_history_size`
  - `variables::performance_schema_events_stages_history_long_size`

### Instrumentation capacities

- Type: line chart + snapshot table
- PmaControl equivalents:
  - `variables::performance_schema_accounts_size`
  - `variables::performance_schema_hosts_size`
  - `variables::performance_schema_users_size`
  - `variables::performance_schema_max_table_handles`
  - `variables::performance_schema_max_table_instances`
  - `variables::performance_schema_max_thread_instances`
  - `variables::performance_schema_max_file_handles`
  - `variables::performance_schema_max_file_instances`
  - `variables::performance_schema_max_statement_classes`
  - `variables::performance_schema_max_stage_classes`
  - `variables::performance_schema_max_mutex_instances`
  - `variables::performance_schema_max_rwlock_instances`
  - `variables::performance_schema_max_socket_instances`

## Gaps vs PMM

- PMM `mysql-performance-schema` also exposes exporter-derived activity views on waits and file events.
- PmaControl already stores some `performance_schema` payloads in dedicated collectors, but this screen currently rebuilds only what is safely and consistently available through the generic variable history.
