# PMM Dashboard Rebuild In PmaControl

Date: 2026-03-20

## Scope

This delivery rebuilds a PMM-oriented navigation inside PmaControl with dedicated routes:

- `/Pmm/index/{id_mysql_server}`
- `/Pmm/system/{id_mysql_server}`
- `/Pmm/innodb/{id_mysql_server}`
- `/Pmm/aria/{id_mysql_server}`
- `/Pmm/rocksdb/{id_mysql_server}`
- `/Pmm/proxysql/{id_mysql_server}`

## Code Structure

- Controller: `App/Controller/Pmm.php`
- Data/catalog layer: `App/Library/PmmDashboardCatalog.php`
- Shared view shell: `App/view/Pmm/dashboard.view.php`
- Shared menu: `App/view/Pmm/menu.view.php`
- Per-route views:
  - `App/view/Pmm/index.view.php`
  - `App/view/Pmm/system.view.php`
  - `App/view/Pmm/innodb.view.php`
  - `App/view/Pmm/aria.view.php`
  - `App/view/Pmm/rocksdb.view.php`
  - `App/view/Pmm/proxysql.view.php`
- Frontend rendering: `App/Webroot/js/Pmm/dashboard.js`
- Tests: `tests/Library/PmmDashboardCatalogTest.php`

## PMM Sources Reviewed

Official PMM dashboard sources inspected from Percona public Grafana dashboards:

- `dashboards/MySQL/MySQL_InnoDB_Details.json`
- `dashboards/MySQL/MySQL_MyISAM_Aria_Details.json`
- `dashboards/MySQL/MySQL_MyRocks_Details.json`
- `dashboards/MySQL/ProxySQL_Instance_Summary.json`
- `dashboards/OS/Node_Summary.json`
- `dashboards/OS/CPU_Utilization_Details.json`
- `dashboards/OS/Memory_Details.json`
- `dashboards/OS/Disk_Details.json`
- `dashboards/OS/Network_Details.json`
- `dashboards/OS/Processes_Details.json`

Repository:

- <https://github.com/percona/grafana-dashboards>

## Delivered Behavior

- A PMM-style top menu per selected server.
- Shared range selector with presets `1h / 6h / 24h` and custom range up to 24h.
- Chart.js rendering for historical charts using PmaControl time-series storage.
- Current-value cards and structured tables for metrics that PMM shows as stat/table panels.
- Clear gap notes where PMM relies on exporter time series not yet collected by PmaControl.

## Main Mapping Strategy

- PMM stat panels -> PmaControl summary cards.
- PMM timeseries panels -> Chart.js line charts.
- PMM table panels -> HTML tables.
- PMM exporter counters -> PmaControl historical counters, bucketed and converted to rates when possible.
- PMM gauges/configuration -> PmaControl current values from `Extraction2::display(...)`.

## Important Limits

### System

PmaControl currently has strong current-value system coverage, but not the full PMM/node_exporter historical detail for:

- per-core CPU series
- top-level PMM UI for interrupts / context switches, even though the raw counters are now stored in `ssh_stats`
- PMM-style per-device disk latency / queue depth derivations from the newly stored `/proc/diskstats` counters
- PMM-style network throughput / retransmission dashboards from the newly stored `/proc/net/*` counters
- top-N process state timelines from the newly stored scheduler and `ps` state snapshots

### ProxySQL

PmaControl currently exposes runtime JSON snapshots and topology/configuration, but not the full PMM ProxySQL historical exporter set for:

- frontend/backend connection histories
- query routing rates
- latency histograms / heatmaps
- memory series
- query cache efficiency time series

### MySQL Router

No official PMM MySQL Router dashboard was found in the inspected PMM source tree. The current delivery documents this gap and keeps MySQL Router under existing PmaControl screens (`MysqlRouter/*`) rather than inventing a fake PMM-equivalent page.

## Per-Screen Documentation

- [pmm_overview_2026-03-20.md](/srv/www/pmacontrol/documentation/pmm_overview_2026-03-20.md)
- [pmm_system_2026-03-20.md](/srv/www/pmacontrol/documentation/pmm_system_2026-03-20.md)
- [pmm_innodb_2026-03-20.md](/srv/www/pmacontrol/documentation/pmm_innodb_2026-03-20.md)
- [pmm_aria_2026-03-20.md](/srv/www/pmacontrol/documentation/pmm_aria_2026-03-20.md)
- [pmm_rocksdb_2026-03-20.md](/srv/www/pmacontrol/documentation/pmm_rocksdb_2026-03-20.md)
- [pmm_proxysql_2026-03-20.md](/srv/www/pmacontrol/documentation/pmm_proxysql_2026-03-20.md)

## Review Summary

Local review done after implementation:

- syntax validation on controller, library, views and JS
- direct route execution with `./glial` on all new `Pmm/*` actions
- PHPUnit on `PmmDashboardCatalogTest`

Corrections applied during review:

- added missing `Pmm::menu()` node
- decoupled `menu.view.php` from parent payload
- fixed availability normalization to avoid calling a private method
- corrected RocksDB hit-ratio formula to use `hits / (hits + misses)` instead of the InnoDB counter pattern
- replaced an unsafe “large numeric value = bytes” heuristic by a source/label-based formatter

## Validation

- `php -l App/Controller/Pmm.php`
- `php -l App/Library/PmmDashboardCatalog.php`
- `php -l App/view/Pmm/dashboard.view.php`
- `php -l App/view/Pmm/menu.view.php`
- `node -c App/Webroot/js/Pmm/dashboard.js`
- `./vendor/bin/phpunit tests/Library/PmmDashboardCatalogTest.php`
- `./glial Pmm index 1 --debug`
- `./glial Pmm system 1 --debug`
- `./glial Pmm innodb 1 --debug`
- `./glial Pmm aria 1 --debug`
- `./glial Pmm rocksdb 1 --debug`
- `./glial Pmm proxysql 1 --debug`
