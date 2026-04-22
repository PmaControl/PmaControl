# Database naming conventions

## Tables

| Pattern | Usage | Example |
|---|---|---|
| `snake_case` | All table names | `mysql_server`, `binlog_analysis` |
| `link__a__b` | Many-to-many join tables (double underscore) | `link__mysql_server__tag` |
| `ts_value_{radical}_{type}` | Time-series value tables | `ts_value_general_int` |
| `ts_*` | Time-series metadata | `ts_variable`, `ts_max_date`, `ts_file` |
| `data_*` | Cached lookup data | `data_geoip`, `data_geoip_city` |

## Columns

| Pattern | Usage | Example |
|---|---|---|
| `id` | Primary key (auto_increment) | `id` |
| `id_{table}` | Foreign key to `{table}.id` | `id_mysql_server`, `id_client` |
| `id_{table}__{role}` | FK with role qualifier (double underscore) | `id_mysql_server__master` |
| `is_{flag}` | Boolean flag (0/1 int) | `is_deleted`, `is_monitored`, `is_proxy` |
| `snake_case` | All other columns | `display_name`, `created_at` |

### Foreign key naming — double underscore for roles

When a table references the same parent table more than once, use a **double underscore** to separate the table name from the role:

```
id_mysql_server          — FK to mysql_server (the main/default reference)
id_mysql_server__master  — FK to mysql_server (the master role)
id_mysql_server__source  — FK to mysql_server (the source role)
```

Single underscore is reserved for the table name itself (`mysql_server` → `id_mysql_server`). The double underscore makes the role suffix unambiguous.

**Wrong**: `id_mysql_server_master` (ambiguous — is it FK to `mysql_server_master` table or to `mysql_server` with role `master`?)
**Right**: `id_mysql_server__master`

## Foreign keys

All `id_{table}` columns should have an explicit `FOREIGN KEY` constraint when **both** the child and referenced tables are non-partitioned. FK constraints cannot be created when:
- The referenced table is partitioned (MariaDB/MySQL limitation)
- The referenced table uses `SYSTEM VERSIONING` with partitions (e.g. `mysql_server`)
- The child table is range-partitioned (e.g. `ts_value_*`)

In those cases, enforce referential integrity at the application level and document it with a comment on the column.

```sql
CREATE TABLE binlog_analysis (
  ...
  id_mysql_server INT NOT NULL,
  id_mysql_server__master INT NOT NULL,
  ...
  CONSTRAINT fk_binlog_analysis__mysql_server
    FOREIGN KEY (id_mysql_server) REFERENCES mysql_server(id),
  CONSTRAINT fk_binlog_analysis__mysql_server__master
    FOREIGN KEY (id_mysql_server__master) REFERENCES mysql_server(id)
);
```

FK constraint naming: `fk_{child_table}__{parent_table}[__{role}]`

## Data types

| Type | Usage |
|---|---|
| `int(11)` | IDs, counters, flags |
| `tinyint(1)` | Booleans (`is_*` columns) |
| `bigint(20) unsigned` | Large counters (bytes, row counts) |
| `varchar(N)` | Variable-length strings |
| `text` | JSON blobs, error messages, long content |
| `datetime` | Timestamps (prefer `DEFAULT current_timestamp()`) |
| `enum(...)` | Fixed set of states (`status` columns) |
| `varbinary(16)` | IP addresses stored via `INET6_ATON()` |

## Engine and charset

- Default engine: `InnoDB` (or `RocksDB` for append-heavy tables like logs)
- Default charset: `utf8mb4 COLLATE utf8mb4_general_ci`
- IP columns: `latin1` or `latin1_bin` for binary comparison
