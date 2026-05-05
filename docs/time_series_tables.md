# Time-series tables — `ts_*`

## Rule

**Never query `ts_*` tables directly without first checking they exist.** They are created dynamically and may not exist on every installation. A missing table causes a fatal SQL error in the controller or library.

```sql
SELECT 1
FROM information_schema.tables
WHERE table_schema = DATABASE()
  AND table_name   = 'ts_value_general_json';
```

Only query if the row exists.

## Tables in the family

- `ts_variable` — metric definitions
- `ts_value_general_*` — general metric values (one table per type: `_int`, `_json`, …)
- `ts_value_slave_*` — slave-specific metrics
- `ts_value_digest_*` — digest / statement summary metrics
- `ts_max_date` — latest ingestion checkpoint per metric
- `ts_file` — on-disk blob archive for ts data

## Related

- Naming rules: [database_conventions.md](database_conventions.md) (`ts_*` section).
- Read/write patterns: [extraction.md](extraction.md).
- Worker that produces these rows: [worker_architecture.md](worker_architecture.md) (pool `worker_aggregate_metric`).
