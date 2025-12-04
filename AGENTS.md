# Repository Guidelines

## Project Structure & Module Organization
PmaControl's PHP sources live under `App/` (controllers, models, views) and `Glial/` (shared framework). Web assets and front controllers are under `App/Webroot/`. Configuration templates sit in `config_sample/`; copy only the files you need into `configuration/` and keep secrets out of version control. CLI utilities, cron helpers, and agents live in `bin/`, `script/`, and the root shell scripts (`loop.sh`, `wakeup.sh`). Database schema migrations and seed data are in `sql/`. Automated tests and fixtures live exclusively in `tests/`. Runtime caches and state (`data/`, `tmp/`) must never be checked in.

## Build, Test, and Development Commands
Run `composer install --no-interaction` after cloning to pull PHP 8.2-compatible dependencies. Use `composer dump-autoload` if you add new PSR-4 classes. Execute `./vendor/bin/phpunit` (or `./vendor/bin/phpunit --testsuite "PmaControl Test Suite"`) to run the test suite defined in `phpunit.xml`. For quick UI checks, `php -S 0.0.0.0:8080 -t App/Webroot` spins up the admin UI without touching Apache/Nginx. Scripts like `install.sh` and `loop.sh` wrap provisioning tasks; run them from the repo root so relative paths resolve.

## Coding Style & Naming Conventions
Follow PSR-12: 4-space indentation, braces on the next line, and strict types where possible. Classes belong under `App\` or `Glial\` namespaces to match Composer autoloading. Name controllers `SomethingController`, service classes `*Manager`, and tests `*Test`. Prefer dependency injection over locating globals. Run `vendor/bin/phpcbf` (if installed) before opening a PR; otherwise ensure `php -l` stays clean.

## Testing Guidelines
All new logic requires PHPUnit coverage under `tests/`, mirroring the namespace of the code under test. Use descriptive test names (`testSyncJobFailsWithoutCredentials`). Keep fixtures small and reusable; store SQL samples in `tests/fixtures`. Aim to keep the suite under five minutes locally—split slow integration tests into separate cases invoked via `--filter`.

## Commit & Pull Request Guidelines
Commits in this repo use short, imperative descriptions (`add schema history`, `patch ProxySQL for IPv6`). Group related changes per commit and avoid WIP messages. Pull requests must describe the intent, list the commands/tests you ran, link to any tracked issues, and include screenshots for UI tweaks. Mention configuration or schema changes explicitly so operators can rehearse upgrades.

## Security & Configuration Tips
Never commit concrete credentials from `config_sample/`; create environment-specific copies under `configuration/` and rely on `.gitignore`. Treat `data/`, `tmp/`, and backup outputs as ephemeral. When touching SSH or backup code, validate permissions inside `bin/` scripts and document any new required Linux capabilities in the PR.
