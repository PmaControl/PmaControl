# CSRF and Same-Site POST Guard

Every browser POST that mutates state must use the shared guard before any side effect:

```php
$guard = CsrfGuard::check($_POST, $_SERVER, $_SESSION, $scope);
if (!$guard['allowed']) {
    // Return $guard['status'], $guard['body'] and $guard['headers'].
}
```

The guard verifies the request in this order:

1. the method must be `POST`;
2. `Origin` must match the trusted PmaControl origin when present;
3. when `Origin` is absent, `Referer` must match the trusted PmaControl origin;
4. the CSRF token must match the session token for the endpoint scope.

The same-origin comparison includes scheme, host and port. Missing `Origin` and `Referer`, `Origin: null`, protocol-relative referers such as `//host/path`, relative referers and external hosts are rejected.

Set `PMACONTROL_TRUSTED_ORIGIN` in `configuration/pmacontrol.config.php` when PmaControl is behind a reverse-proxy or when `HTTP_HOST` is not guaranteed by the front server. Leave it empty only when the front server guarantees the incoming host and scheme. The value must contain the canonical origin only, for example `https://pmacontrol.example.com:8443`, without an application path.

Machine-to-machine endpoints must not bypass this by accident. They need either their own authenticated API flow or a documented exemption in the ticket that introduces the endpoint. `/Webservice/pushServer` is intentionally exempt from browser CSRF tokens because it is a JSON API authenticated with HTTP Basic credentials, not a session-backed browser form; its preconditions are checked through `ApiRequestGuard` before import.

`/Api/config` is intentionally read-only (`GET`) until it gets a dedicated API authentication flow. `POST`, `PUT`, `PATCH` and `DELETE` return `405 Method not allowed` with `Allow: GET`; do not re-open them as CSRF-exempt session-backed mutations.

## View rendering

Controllers expose CSRF values to views with the standard keys `<scope>_csrf_field` and `<scope>_csrf_token`.
Render them through `App\Library\Security\CsrfRender` so output escaping stays centralized:

```php
<?= CsrfRender::hiddenInput($data, 'database_create') ?>
```

For inline edit or AJAX attributes, use the leading-space attribute helper:

```php
<td<?= CsrfRender::attributes($data, 'tree_update') ?>></td>
```

`CsrfRender` only renders existing values; it does not issue tokens and does not validate requests.

Follow [issue_workflow.md](issue_workflow.md) for issue handling, branching, comments, reviews, PRs and closure.
