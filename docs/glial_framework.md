# Glial framework conventions

Glial is the custom MVC framework under `/srv/www/glial/Glial/` (installed via Composer `glial/glial 5.1.*`).

On this host `vendor/glial/glial` is a symlink to the live checkout:

```
vendor/glial/glial -> /srv/www/glial
```

So edits in `/srv/www/glial/Glial/` are picked up immediately by PmaControl — no `composer update` needed. Reverse is also true: a broken commit in `/srv/www/glial/` breaks PmaControl's test suite.

## URL grammar

The Router (`App/Webroot/Router.php`) parses URLs as:

```
/{lang}/{controller}/{action}/{param1}/{param2}/key:value/...
```

- Positional segments are passed to the action as `$param` array.
- `key:value` segments populate `$_GET` (e.g. `/ajax:true` sets `$_GET['ajax'] = 'true'`).
- The legacy `/key>value` separator sets framework constants but is older style — prefer `key:value`.

## ACL / routing cache — IMPORTANT

After creating **or renaming** any controller, public action, or view, delete:

```bash
rm -f /srv/www/pmacontrol/tmp/acl/acl.ser
```

Otherwise the new route returns 404 / permission denied until the cache rebuilds. Do it before testing and before committing.

## AJAX / JSON endpoints

Two things are needed — **both** required:

1. **URL must include `/ajax:true`** in the path:
   ```
   /pmacontrol/en/Foo/bar/ajax:true
   /pmacontrol/en/Foo/bar/arg1/arg2/ajax:true?page=2
   ```
   Do **not** rely on `X-Requested-With` alone. Without `/ajax:true` the framework may return a full HTML layout, breaking `JSON.parse()` with `Unexpected token '<'`.

2. **Controller must skip layout and view**:
   ```php
   $this->layout_name = false;   // skip HTML layout wrapper
   $this->view = false;          // skip .view.php require
   header('Content-Type: application/json; charset=UTF-8');
   echo json_encode($payload);
   exit;
   ```
   Setting only one wraps JSON in HTML (layout) or crashes on missing `.view.php`. `$this->layout = false` alone is not enough in this codebase — the effective switch is `layout_name = false`.

## Views

- Live in `App/view/{Controller}/{action}.view.php`.
- Load JS libraries via `$this->di['js']->addJavascript([...])`.
- Inline JS via `$this->di['js']->code_javascript('...')` is rendered only by the layout footer — **not** available in AJAX mode. AJAX views must include their own `<script>` tags.

## Frontend cache-busting

When updating a legacy JS file that is heavily cached by browsers, bust the cache:

```html
<script src="Server/state.js?v=<?= filemtime(...) ?>"></script>
```

Symptom of a stale cache: old client logic generates an invalid URL (e.g. `Server/undefined`), hits a 404 HTML page, passed to `JSON.parse()`, fails with `Unexpected token '<'`.

## CLI invocation

The same controllers are reachable from CLI:

```bash
php App/Webroot/index.php <controller> <action> [param1 param2 ...]
```
