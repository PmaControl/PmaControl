<?php


/*
 * if you use a direrct DNS set : define('WWW_ROOT', "/");
 * 
 * if you dev in local or other use : define('WWW_ROOT', "/path_to_the_final_directory/");
 * 
 * example : http://127.0.0.1/directory/myapplication/ => define('WWW_ROOT', "/directory/myapplication/");
 * 
 * Don't forget the final "/"
 */


if (! defined('WWW_ROOT'))
{
    define('WWW_ROOT', "/pmacontrol/");
}

/*
 * Cookie security is read before the full configuration loader runs, because
 * PHPSESSID must be configured before session_start().
 *
 * PMACONTROL_COOKIE_SECURE:
 * - "auto" detects HTTPS from the request.
 * - "true" forces Secure cookies, useful once HTTP is closed or redirected.
 * - "false" is only for local development without TLS.
 *
 * PMACONTROL_COOKIE_TRUSTED_PROXIES is a comma-separated list of reverse proxy
 * IPs/CIDRs allowed to provide X-Forwarded-Proto=https.
 */
if (! defined('PMACONTROL_COOKIE_SECURE')) {
    define('PMACONTROL_COOKIE_SECURE', 'auto');
}

if (! defined('PMACONTROL_COOKIE_SAMESITE')) {
    define('PMACONTROL_COOKIE_SAMESITE', 'Lax');
}

if (! defined('PMACONTROL_COOKIE_TRUSTED_PROXIES')) {
    define('PMACONTROL_COOKIE_TRUSTED_PROXIES', '');
}
