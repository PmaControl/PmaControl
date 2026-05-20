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

## Persistent Login Tokens

Persistent authentication uses the `pmacontrol_persistent_auth` cookie. The
cookie contains a random selector and verifier; only the verifier hash is stored
server-side in `user_persistent_auth_session`.

On successful password authentication PmaControl creates a fresh persistent
session, expires the legacy Glial login/password cookies, and stores only
server-side metadata: `id_user_main`, hashed User-Agent/IP fingerprints, creation
time, sliding expiry, absolute expiry, last use, and revocation time. On cookie
authentication the verifier is rotated atomically and the previous cookie value
stops working.

Logout revokes the current persistent session and expires both the opaque cookie
and the legacy Glial cookies. Existing legacy cookies are accepted only for a
progressive migration path: when Glial authenticates one, PmaControl immediately
issues the opaque token and deletes the legacy cookies.

The cookie is emitted through `CookieSecurity`, so it inherits `HttpOnly`,
`Secure` when the request is HTTPS or trusted proxy settings require it, and the
configured `SameSite` policy. Defaults are 14 days sliding validity, 30 days
absolute validity, and at most 10 active persistent sessions per user.

Because the verifier rotates on each authenticated cookie request, two concurrent
requests with the same old cookie can invalidate the session if one loses the
compare-and-swap rotation. The user must log in again; this is the replay-safety
tradeoff for persistent-token theft detection.
