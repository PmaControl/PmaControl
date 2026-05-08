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
- `mysql_group_replication_hostgroups`
- `mysql_galera_hostgroups`

Payloads:

- `proxysql_runtime::mysql_servers`
- `proxysql_runtime::proxysql_servers`
- `proxysql_runtime::mysql_group_replication_hostgroups`
- `proxysql_runtime::mysql_galera_hostgroups`

Operational classification rule:

- MySQL Group Replication / InnoDB Cluster hostgroups must be declared in ProxySQL table `mysql_group_replication_hostgroups`.
- PXC / Galera hostgroups must be declared in ProxySQL table `mysql_galera_hostgroups`.
- Do not use a Group Replication-looking name such as `gr80` for a PXC/Galera cluster; it confuses both operators and topology rendering.

Production reference correction on `2026-05-08`:

- ProxySQL nodes: `10.68.68.134` and `10.68.68.135`.
- Group Replication cluster `10.68.68.131-133`: hostgroups `10/20/30/40`, kept in `mysql_group_replication_hostgroups`.
- PXC/Galera cluster `10.68.68.223-225`: hostgroups `110/120/130/140`, moved from `mysql_group_replication_hostgroups` to `mysql_galera_hostgroups`.
- PXC comments were renamed from `perconaGR80` to `perconaPXC`.
- ProxySQL runtime and disk state were refreshed with `LOAD MYSQL SERVERS TO RUNTIME` and `SAVE MYSQL SERVERS TO DISK`.

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
