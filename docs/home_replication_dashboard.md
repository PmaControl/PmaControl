# Home dashboard — Replication issues card (#1210)

The `/Home/index` dashboard shows a **Replication issues** card immediately after the unavailable-servers alert. It complements the existing 4-counter KPI ring (`ok` / `lag` / `error` / `stopped`) by listing *which* channels are non-ok and *why*.

## Buckets

`Home::classifyReplicationChannelDetail()` maps each channel to one of:

| Bucket | Trigger |
|--------|---------|
| `io_error` | `last_io_error` non-empty (or IO half-stopped without a recorded error) |
| `sql_error` | `last_sql_error` non-empty (or SQL half-stopped without a recorded error) |
| `stopped` | both IO + SQL threads not running and no error recorded |
| `lag_critical` | both threads running, lag > 300 s |
| `lag_warning` | both threads running, 60 s < lag ≤ 300 s |
| `ok` | lag ≤ 60 s, no error — **excluded** from this card (already in the KPI ring) |

Variable names are resolved with the usual `replica_*` ↔ `slave_*` fallback so MariaDB and MySQL 8.x share one code path.

## Signature dedup

`Home::normalizeReplicationErrorSignature()` strips digits and quoted literals so e.g. *“Got fatal error 1236 … pos 4567”* and *“Got fatal error 9999 … pos 88888”* share one signature. 30 channels with the same templated error collapse to a single expandable row instead of 30 lines.

## Data path

`Home::buildReplicationIssues()` piggy-backs on the `Extraction2::display()` call already done for the KPI ring — it just adds `last_io_errno` / `last_sql_errno` to the column list and joins `display_name` from `mysql_server` in one indexed `IN (...)` query. No extra time-series fetch.

Each affected channel is rendered as a clickable link to `/slave/show/<id>/<connection_name>/`.

## Key references

- Controller: `App/Controller/Home.php` — `index()`, `classifyReplicationChannelDetail()`, `buildReplicationIssues()`, `normalizeReplicationErrorSignature()`
- View: `App/view/Home/index.view.php` (block guarded by `$replIssuesTotal > 0`)
- Tests: `tests/Controller/HomeReplicationIssuesTest.php`
