# Rename Database CLI

`bin/rename_database.php` renames a MySQL/MariaDB database with the same engine used by
`Database::move()`: tables, views, triggers, functions, procedures, optional grants and the
final consistency check are handled by `App\Library\Database\Renamer`.

## Modes

PmaControl server mode resolves the target connection from `mysql_server`:

```bash
php bin/rename_database.php --server-id=12 --old=old_db --new=new_db
php bin/rename_database.php 12 old_db new_db
```

Direct mode connects to a server without a `mysql_server` row:

```bash
php bin/rename_database.php --host=127.0.0.1:3306 --user=root --password=secret --old=old_db --new=new_db
MYSQL_PWD=secret php bin/rename_database.php 127.0.0.1:3306 root old_db new_db
```

Prefer `MYSQL_PWD` for interactive and operational use. `--password=...` is accepted for
automation, but command-line arguments can be visible in shell history and
`/proc/<pid>/cmdline` while the command is running.

IPv6 addresses with a port must be bracketed:

```bash
MYSQL_PWD=secret php bin/rename_database.php '[::1]:3306' root old_db new_db
```

## Flags

- `--adjust-privileges` rewrites grants that reference the old database.
- `--force` allows moving objects into an existing target database.
- `--dry-run` prints the planned mutating SQL without executing it.
- `--help` prints the command usage.

## Safety Rules

- Database names are validated with `App\Library\Security\Identifier::isDatabaseName()`.
- Hosts, ports and users are validated before a connection is opened.
- The password is used only for connection setup. It is not forwarded to `Database::move()` or
  `Debug::parseDebug()`.
- The HTTP endpoint `Database::rename($param)` remains separate and keeps its CSRF checks.
