# Cookie security

PmaControl configures browser cookies before `session_start()`, so the cookie
security knobs must be available before `App/Webroot/Bootstrap.php` runs.

For a deployed instance, configure them either as environment variables or in
`configuration/webroot.config.php`. Do not put them only in later configuration
files: the PHP session cookie has already been prepared by then.

## Settings

- `PMACONTROL_COOKIE_SECURE`
  - `auto`: default. Detect HTTPS from `HTTPS=on`, `SERVER_PORT=443`, or a
    trusted reverse proxy.
  - `true`: force the `Secure` attribute. Use after direct HTTP access has been
    closed or redirected.
  - `false`: only for local development without TLS.
- `PMACONTROL_COOKIE_SAMESITE`
  - `Lax`: default.
  - `Strict`: stricter browser navigation policy.
  - `None`: allowed, but `Secure` is forced because browsers require it.
- `PMACONTROL_COOKIE_TRUSTED_PROXIES`
  - Comma-separated list of reverse proxy IPs or CIDRs allowed to provide
    `X-Forwarded-Proto=https`.
  - Keep it empty on direct Apache/PHP deployments.

Environment variables override constants defined in `webroot.config.php`.

## Existing install migration

Copy the `PMACONTROL_COOKIE_*` block from `config_sample/webroot.config.php` to
the deployed `configuration/webroot.config.php`, or set equivalent environment
variables in the web server/PHP-FPM service.

For a reverse proxy terminating TLS, set the proxy IP/CIDR:

```php
define('PMACONTROL_COOKIE_SECURE', 'auto');
define('PMACONTROL_COOKIE_SAMESITE', 'Lax');
define('PMACONTROL_COOKIE_TRUSTED_PROXIES', '127.0.0.1,10.0.0.0/24');
```

If PmaControl is still reachable through clear HTTP, cookies cannot be protected
against network interception. Close port 80 or redirect it to HTTPS, then use
`PMACONTROL_COOKIE_SECURE=true` if the HTTPS detection path is not reliable.
