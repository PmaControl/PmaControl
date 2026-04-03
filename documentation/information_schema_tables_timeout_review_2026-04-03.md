# Information Schema Tables Timeout Review

Date: 2026-04-03

## Objective

Add a maximum execution time on code paths that query `information_schema.tables` on monitored MySQL/MariaDB servers, excluding the local PmaControl server (`id_mysql_server = 1`).

If the timeout is reached, return a clear application-level error message instead of leaving an opaque SQL failure.

## Implemented Rule

- Scope: only remote monitored servers, not `id_mysql_server = 1`
- Timeout: `10s`
- MariaDB: `SET STATEMENT MAX_STATEMENT_TIME = 10 FOR ...`
- MySQL >= 5.7: `SELECT /*+ MAX_EXECUTION_TIME(10000) */ ...`
- Error message on timeout:
  - `[PMACONTROL-IS-TABLES-TIMEOUT] Query on <context> exceeded 10s. Use a narrower filter or a fallback strategy (SHOW TABLES / SHOW CREATE TABLE).`

## Common Helper Added

Main implementation is centralized in:

- `App/Library/Mysql.php`

Added helpers:

- `Mysql::shouldProtectInformationSchemaTables()`
- `Mysql::queryTargetsInformationSchemaTables()`
- `Mysql::isInformationSchemaTablesTimeoutError()`
- `Mysql::buildInformationSchemaTablesTimeoutMessage()`
- `Mysql::protectInformationSchemaTablesQuery()`
- `Mysql::sqlQueryWithInformationSchemaTablesTimeout()`

## Patched Code Paths

Direct or execution-level protection added in:

- `App/Controller/Aspirateur.php`
- `App/Controller/Audit.php`
- `App/Controller/Cleaner.php`
- `App/Controller/Compare.php`
- `App/Controller/CompareConfig.php`
- `App/Controller/Covage.php`
- `App/Controller/Database.php`
- `App/Controller/ForeignKey.php`
- `App/Controller/Index.php`
- `App/Controller/Mysql.php`
- `App/Controller/MysqlDatabase.php`
- `App/Controller/MysqlServer.php`
- `App/Controller/MysqlTable.php`
- `App/Controller/Mysqlsys.php`
- `App/Controller/Query.php`
- `App/Controller/Spider.php`
- `App/Controller/Table.php`
- `App/Library/Graphviz.php`
- `App/Library/Mysql.php`

## Special Handling

### Aspirateur eachHour

`Aspirateur::eachHour()` now has two protections:

- For servers with more than `50` databases:
  - no query on `information_schema.tables`
  - fallback to `SHOW FULL TABLES`
  - row count / size fields intentionally left empty
  - DDL collected with `SHOW CREATE TABLE` / `SHOW CREATE VIEW`
- For smaller servers:
  - `information_schema.tables` still used
  - protected by a `10s` timeout
  - fallback to `SHOW FULL TABLES` if the `information_schema.tables` query fails

## Review Outcome

### Covered

- Direct `sql_query()` calls on `information_schema.tables` for remote server-driven controllers
- Silent existence checks through `sql_query_silent()`
- Execution-time wrapping for compare workflows where SQL text is assembled first and executed later
- Graph / schema / inventory style features that enumerate tables from remote servers

### Left Out Intentionally

- Purely local or `DB_DEFAULT` code paths tied to PmaControl itself
- `SHOW CREATE TABLE information_schema.tables`
- Commented code blocks
- SQL strings that mention `information_schema.tables` but are already protected later at execution time

### Local / Internal Examples Left Out

- `App/Controller/Percona.php`
  - works against local `DB_DEFAULT`
- commented snippets in:
  - `App/Controller/Cleaner.php`
  - `App/Controller/Aspirateur.php`
  - `App/Library/Table.php`

## Residual Review Notes

- `App/Controller/Mysql.php`
  - protection is applied by rewriting the SQL before `sql_fetch_yield()`
  - this path is protected at query level, but the user-facing error still depends on how the underlying iterator surfaces SQL errors
- some SQL builders still contain literal `information_schema.tables` strings
  - this is expected when the actual protection is injected later at execution time through the helper

## Validation Performed

- `php -l` passed on all touched PHP files
- manual scan performed over `App/` and `App/Library/` for `information_schema.tables`
- review confirmed that the main remote execution paths now go through the timeout helper or the `SHOW TABLES` fallback

## Files Produced

- Markdown: `documentation/information_schema_tables_timeout_review_2026-04-03.md`
- PDF: `documentation/information_schema_tables_timeout_review_2026-04-03.pdf`
