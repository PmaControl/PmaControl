# Build, test, run

## Install

```bash
composer install --no-interaction
composer dump-autoload                 # after adding new PSR-4 classes
```

Target: PHP 8.2+. Required extensions: `curl`, `gd`, `mbstring`, `dom`, `gmp`, `mysqlnd`, `openssl`, `pcntl`, `posix`, `sodium`, `ssh2`. System packages: MariaDB 10.11 (or compatible MySQL), `graphviz`, `curl`, `dos2unix`. Web server: Apache 2.4 / Nginx with PHP 8.2, DocumentRoot on `App/Webroot/`.

## Test

```bash
./vendor/bin/phpunit                                     # full suite (phpunit.xml)
./vendor/bin/phpunit --testsuite "PmaControl Test Suite"
./vendor/bin/phpunit --filter testSyncJobFailsWithoutCredentials
./vendor/bin/phpunit tests/Library/MysqlVersionCompatibilityTest.php
```

### PHPUnit conventions

- Tests mirror the namespace of the code under test (`tests/Controller/…`, `tests/Library/…`).
- Descriptive test names: `testSyncJobFailsWithoutCredentials`.
- Small reusable fixtures in `tests/fixtures/`. Store SQL samples there.
- Aim to keep the full suite under five minutes. Split slow integration tests into separate cases reachable via `--filter`.

## Run (local dev)

```bash
php -S 0.0.0.0:8080 -t App/Webroot                       # quick UI check
```

## CLI invocation

Controllers and actions are reachable from the shell:

```bash
php App/Webroot/index.php <controller> <action> [params...]
```

MySQL on localhost is reachable without a password: `mysql pmacontrol -e "..."`.

## Provisioning

`install.sh`, `loop.sh`, `wakeup.sh` and scripts under `bin/` / `script/` must be run from the repo root so relative paths resolve.
