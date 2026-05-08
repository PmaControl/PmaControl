# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

PmaControl is a MySQL/MariaDB supervision platform in PHP 8.2 on the **Glial** framework (`/srv/www/glial/Glial/`, Composer `glial/glial 5.1.*`). Application code lives under `App/{Controller,view,Library,Webroot}/`. Only load the references below when the current task actually needs them.

## Branch and PR Target

The active integration target is `master`. Create issue branches from an up-to-date `origin/master`, open PRs against `master`, and merge through PRs only. Do not target `commercial` for new issue work unless the user explicitly overrides this rule for a specific task.

## Per-topic references — `docs/`

| Load when working on… | File |
|---|---|
| Adding / renaming a controller, action, or view (ACL cache); AJAX/JSON endpoints; URL routing; inline JS | [docs/glial_framework.md](docs/glial_framework.md) |
| PSR-12 style, namespaces, POST-Redirect-GET, directory layout | [docs/coding_style.md](docs/coding_style.md) |
| Adding or reviewing a browser POST that mutates state; CSRF token and Origin/Referer guard | [docs/csrf.md](docs/csrf.md) |
| Processing tracked issues with the Codex/Claude two-pass workflow, issue comments, commit refs and PR refs | [docs/issue_workflow.md](docs/issue_workflow.md) |
| Composer, PHPUnit, CLI invocation, local dev server, test conventions | [docs/build_test.md](docs/build_test.md) |
| Commit messages, PRs, tooling-visibility rules, secrets / config handling | [docs/commits.md](docs/commits.md) |
| Table / column / FK / data-type naming | [docs/database_conventions.md](docs/database_conventions.md) |
| Querying `ts_*` time-series tables | [docs/time_series_tables.md](docs/time_series_tables.md) |
| Group Replication / InnoDB cluster; MySQL vs MariaDB replication syntax | [docs/group_replication.md](docs/group_replication.md) |
| MySQL NDB Cluster — schema, collectors (`ndb_mgm` + `ndbinfo`), dot3 rendering, alerts | [docs/ndb_cluster.md](docs/ndb_cluster.md) |
| GeoIP country / city lookups | [docs/data_geoip.md](docs/data_geoip.md) |
| Daemon / worker / System V queue architecture | [docs/worker_architecture.md](docs/worker_architecture.md) |
| Chart.js 4.5.1 API quirks | [docs/chart_js.md](docs/chart_js.md) |
| `App\Library\Extraction` time-series helper | [docs/extraction.md](docs/extraction.md) |
| Runtime log file locations | [docs/logs.md](docs/logs.md) |
| Small gotchas (`grep -a` on binlog, stale browser cache, worker hot-reload, …) | [docs/tips.md](docs/tips.md) |

Full topic index: [docs/README.md](docs/README.md).

## Deep references — `documentation/`

Full index (every file grouped by topic): [documentation/README.md](documentation/README.md).

Quick jumps:

- [documentation/controller/README.md](documentation/controller/README.md) — one page per controller (122)
- [documentation/Controller~Library/README.md](documentation/Controller~Library/README.md) — per-class method reference for `App/Controller` + `App/Library`
- [documentation/domains/README.md](documentation/domains/README.md) — in-depth domain notes (backup, cleaner, aspirateur, schema, dot3)

## Entry points

| Path | Purpose |
|---|---|
| `App/{Controller,view,Library,Webroot}/` | Application code |
| `/srv/www/glial/Glial/` | Framework (Composer `glial/glial`) |
| `bin/`, `script/`, `*.sh` (`loop.sh`, `wakeup.sh`, …) | CLI helpers and daemons |
| `sql/incremental_v2/` | Schema migrations |
| `tests/` | PHPUnit tests, mirroring the namespace under test |
| `config_sample/` → `configuration/` | Config templates → live config (gitignored) |
| `data/`, `tmp/` | Ephemeral runtime state (gitignored) |

## Local MySQL access

On this host, the `mysql` CLI authenticates without a password (socket auth). For ad-hoc inspection run `mysql pmacontrol -e "SELECT …"` directly — no need to parse `configuration/db.config.ini.php` or bootstrap the app. The encrypted passwords in that file are only required when connecting *as the PmaControl app user*.
