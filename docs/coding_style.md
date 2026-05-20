# Coding style & layout

## Style

- **PSR-12**: 4-space indent, braces on the next line, strict types where possible.
- `php -l` must stay clean. Run `vendor/bin/phpcbf` if installed.
- Namespaces: `App\Controller`, `App\Library`, `App\model`, `Glial\…`. Classes live under `App/` and `Glial/` per Composer PSR-4.
- Controllers are named `SomethingController`, service classes `*Manager`, tests `*Test`.
- Prefer dependency injection over globals / service locators.

## Directory layout

| Path | Purpose |
|---|---|
| `App/Controller/` | Request controllers |
| `App/view/{Controller}/{action}.view.php` | Views |
| `App/Library/` | Reusable classes / traits |
| `App/Webroot/` | Front controller, public JS/CSS, `Router.php` |
| `Glial/` | Shared framework (also mirrored at `/srv/www/glial/`) |
| `bin/`, `script/`, root `*.sh` (`loop.sh`, `wakeup.sh`, …) | CLI utilities, cron helpers |
| `sql/incremental_v2/` | Schema migrations |
| `tests/` | PHPUnit tests, mirroring the namespace of code under test |
| `config_sample/` | Config templates (committed) |
| `configuration/` | Live config (gitignored — never commit) |
| `data/`, `tmp/` | Ephemeral runtime state (gitignored) |

## Navigation & form flows — POST-Redirect-GET

Any server-rendered form that **mutates state** or computes a new page state must end in a `GET` redirect (POST-Redirect-GET). Never leave the UI on a POST URL — back/refresh must stay predictable.

Use plain `GET` directly for pure selection, filtering, search, and navigation forms.
