# docs/ — per-topic references

One topic per file. Load only what the current task needs.

## Framework & request flow
- [glial_framework.md](glial_framework.md) — URL grammar, ACL cache invalidation, AJAX/JSON flags, views, inline JS, cache-bust

## Conventions
- [coding_style.md](coding_style.md) — PSR-12, namespaces, POST-Redirect-GET, file layout
- [csrf.md](csrf.md) — CSRF token and same-site Origin/Referer guard for mutating POSTs
- [security_cookies.md](security_cookies.md) — session/auth cookie hardening and HTTPS/proxy deployment settings
- [issue_workflow.md](issue_workflow.md) — tracked issue workflow with two-pass review, comments, PR and merge links
- [commits.md](commits.md) — commit format, PR guidelines, tooling visibility, secrets handling
- [build_test.md](build_test.md) — composer, PHPUnit, CLI invocation, local dev server, test conventions
- [plugin_packages.md](plugin_packages.md) — `plugin.json` manifest files, copy plans, SQL/data and install scripts

## Database
- [database_conventions.md](database_conventions.md) — table / column / FK / data type naming
- [rename_database_cli.md](rename_database_cli.md) — `bin/rename_database.php` modes, flags and safety rules
- [time_series_tables.md](time_series_tables.md) — `ts_*` tables existence check
- [group_replication.md](group_replication.md) — InnoDB cluster, MySQL vs MariaDB replication syntax
- [ndb_cluster.md](ndb_cluster.md) — MySQL NDB Cluster onboarding, collectors, alerts, troubleshooting
- [data_geoip.md](data_geoip.md) — GeoIP country / city lookup tables
- [worker_architecture.md](worker_architecture.md) — daemon / worker / System V queue architecture

## Proxies
- [proxysql_audit.md](proxysql_audit.md) — `/ProxySQL/audit/<id>/` configuration-audit framework, severity vocabulary, how to add a check (EPIC #895)

## Frontend & analytics
- [chart_js.md](chart_js.md) — Chart.js 4.5.1 v4 API quirks
- [extraction.md](extraction.md) — `App\Library\Extraction` time-series helper

## Pages
- [slave_show_page.md](slave_show_page.md) — `/slave/show/<id>/<conn>/` page anatomy, status tiles, live 5 s polling, GTID button logic, parallel-threads slider
- [home_replication_dashboard.md](home_replication_dashboard.md) — `/Home/index` "Replication issues" card: 5 fine buckets + signature dedup (#1210)
- [binlog_relay_blackhole.md](binlog_relay_blackhole.md) — `/Blackhole/index` Tools page + CLI: convert a slave into a BLACKHOLE binlog relay (epic #1212)
- [plugin_catalog.md](plugin_catalog.md) — `/MysqlServer/plugins/<id>/` tab: live engines + plugins from Aspirateur, family-filtered catalog, SuperAdmin Install / Uninstall SONAME

## Runtime
- [logs.md](logs.md) — application log files under `tmp/log/`
- [mysql_logs_import.md](mysql_logs_import.md) — `/MysqlServer/logs/<id>/` pipeline: SSH tail → `data/logs/` chunks → `IntegrateLog` → `ssh_log_mysql_*` tables → UI
- [tips.md](tips.md) — small gotchas (grep -a on binlog, stale browser cache, …)

## Installation
- [install_ubuntu2604.md](install_ubuntu2604.md) — Ubuntu 26.04 installer variables, re-run behavior, CI target
- [install_debian13.md](install_debian13.md) — Debian 13 installer and network hardening variables
