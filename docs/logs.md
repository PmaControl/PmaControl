# Application logs

All logs live under `tmp/log/`. Several files are large and rotated (`*.log.1`).

| File | Purpose |
|---|---|
| `error_php.log` | PHP warnings / errors — **main diagnostic file** |
| `glial.log`, `glial.log.1` | Framework-level logs (very large, rotated) |
| `sql.log`, `sql.log.1` | All SQL queries and errors (very large, rotated) |
| `worker_mysql_log.log` | `worker_mysql_log` (error log collection) output |
| `worker_aggregate_metric.log` | `worker_aggregate_metric` (rollup) output |
| `worker_ssh.log` | SSH worker |
| `worker_proxysql.log` | ProxySQL worker |
| `worker_maxscale.log` | MaxScale worker |
| `worker_mysqlrouter.log` | MySQL Router worker |

## Tailing a fresh error

```bash
tail -f tmp/log/error_php.log
```

When a worker misbehaves, start with the per-worker file, then fall back to `glial.log` / `sql.log` for framework-level context. Worker architecture overview: [worker_architecture.md](worker_architecture.md).
