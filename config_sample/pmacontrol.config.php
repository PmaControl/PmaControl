<?php
if (! defined('PMACONTROL_PASSWD'))
{
    define('PMACONTROL_PASSWD', 'rlF3sznwaOV3fw9guowRryKEm0rTvflxiRi7IN3uGgNZMFI2UmtGRU9VTm5OMlJN');
}

if (! defined('PMACONTROL_TRUSTED_ORIGIN'))
{
    // Example: https://pmacontrol.example.com or https://pmacontrol.example.com:8443
    // Leave empty to compare Origin/Referer with the current request host.
    define('PMACONTROL_TRUSTED_ORIGIN', '');
}

if (! defined('PMACONTROL_TIMEZONE'))
{
    // Issue #746: PHP timezone applied to every PmaControl process (Apache and
    // CLI daemons) so timestamps written by the daemons match the filters
    // emitted by Apache. Must be a value from PHP's `DateTimeZone::listIdentifiers()`
    // (e.g. "Europe/Paris", "UTC"). Empty/invalid falls back to
    // App\Library\Bootstrap\TimezoneAligner::DEFAULT_TIMEZONE.
    define('PMACONTROL_TIMEZONE', 'Europe/Paris');
}
