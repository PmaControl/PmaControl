# Pmm ProxySQL

## Route / Code

- Route: `/Pmm/proxysql/{id_mysql_server}`
- Controller action: `App/Controller/Pmm.php::proxysql()`
- View: `App/view/Pmm/proxysql.view.php`
- Shared view shell: `App/view/Pmm/dashboard.view.php`
- Frontend: `App/Webroot/js/Pmm/dashboard.js`

## PMM Source

- `dashboards/MySQL/ProxySQL_Instance_Summary.json`

## Implemented Sections

### Runtime snapshot

PMM equivalent panels:

- ProxySQL Instance Stats
- current config / runtime context

PmaControl blocks:

- summary cards
- global variables table

Metrics / payloads:

- `proxysql_available`
- `proxysql_connect_error`
- `proxysql_runtime::global_variables`

### Backend topology

PMM equivalent panels:

- Hostgroup Size
- Endpoint Status

PmaControl tables:

- `mysql_servers`
- `proxysql_servers`

Payloads:

- `proxysql_runtime::mysql_servers`
- `proxysql_runtime::proxysql_servers`

### Routing configuration

PMM equivalent panels:

- query routing context
- users / runtime config context

PmaControl tables:

- `mysql_query_rules`
- `mysql_users`

Payloads:

- `proxysql_runtime::mysql_query_rules`
- `proxysql_runtime::mysql_users`

## PMM Metric Origins

PMM ProxySQL summary uses ProxySQL exporter metrics for:

- client connections
- backend connections
- query routing
- query latency
- memory
- query cache

## Equivalent In PmaControl

PmaControl currently reconstructs the static/runtime topology side from ProxySQL admin payloads already stored by the aspirator.

## Missing

The following PMM historical areas are not yet available in PmaControl:

- frontend connection history
- backend connection pool history
- queries routed over time
- latency heatmap / detailed latency panels
- memory series
- query cache efficiency series

This page is therefore intentionally marked as a partial PMM-equivalent implementation.
