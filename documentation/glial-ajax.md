# Glial AJAX / JSON endpoints

Date: 2026-03-19

## Problem observed

On `Server/state`, the browser raised:

- `Unexpected token '<', "<!DOCTYPE "... is not valid JSON`

and the returned HTML showed:

- `Error 404`
- `Page not found ... "Server/undefined"`

This means the browser was not always hitting a valid JSON endpoint. In practice,
two distinct issues were involved:

1. Glial legacy AJAX endpoints require the explicit route suffix `ajax:true`
2. the browser could keep an old cached `state.js`, still using stale logic

## How Glial actually handles layout

In `/srv/www/glial/Glial/Synapse/FactoryController.php`, layout rendering is skipped
when `layout_name` is falsy.

In `/srv/www/glial/Glial/Synapse/Controller.php`, `setLayout()` still includes:

```php
include APP_DIR.DS."layout".DS.$this->layout_name.".layout.php";
```

So for JSON endpoints, the reliable pattern is:

```php
$this->layout_name = false;
$this->layout = false;
$this->view = false;
header('Content-Type: application/json; charset=UTF-8');
echo json_encode($payload);
exit;
```

Important:

- `layout = false` alone is not enough in this codebase
- the effective Glial switch is `layout_name = false`

## Required route pattern

For legacy AJAX calls in PmaControl, the URL should explicitly include:

```text
/ajax:true
```

Example:

```text
/pmacontrol/en/server/stateLive/ajax:true
```

This pattern is already used in many legacy screens:

- `Server/main`
- `Daemon/*`
- `Common/*`
- `ProxySQL/*`

## Why `Server/undefined` happened

When `window.serverStateConfig.liveUrl` was missing or the browser kept an older
cached version of `state.js`, the client could generate an invalid request and end
up on a Glial 404 page, rendered as full HTML.

That HTML then got passed to `JSON.parse(...)`, causing:

```text
Unexpected token '<'
```

## Fix applied for `Server/state`

The screen now uses all of the following:

1. JSON endpoints with:
   - `layout_name = false`
   - `layout = false`
   - `view = false`
2. explicit AJAX URLs:
   - `server/stateInitial/ajax:true`
   - `server/stateLive/ajax:true`
3. `X-Requested-With: XMLHttpRequest`
4. client-side validation before `JSON.parse(...)`
5. cache-busted asset URLs for this screen:
   - `chart-4.5.1.umd.min.js?v=<filemtime>`
   - `Server/state.js?v=<filemtime>`

## Rule to keep in mind

For every new JSON/AJAX endpoint in this codebase:

- always add `/ajax:true` in frontend URLs
- always set `layout_name = false`
- also set `view = false`
- do not rely only on `layout = false`
- if debugging feels inconsistent, suspect stale browser cache on legacy JS files
