<?php

declare(strict_types=1);

namespace App\Library\Bootstrap;

use DateTimeZone;

/**
 * Force the PHP default timezone at bootstrap so that every PHP process serving
 * PmaControl — Apache mod_php, CLI daemons launched by Agent::launch, ad-hoc
 * scripts — stamps and reads timestamps in the same zone.
 *
 * Issue #746: when /usr/bin/php points at a PHP version whose ini file leaves
 * `date.timezone` commented (default UTC), the daemons end up writing UTC
 * timestamps into ts_value_* while Apache reads them with the local-time
 * NOW() comparison emitted by Extraction::extract. The two-hour gap silently
 * empties every "last hour" graph window. Setting the timezone explicitly
 * here removes that environmental coupling.
 *
 * The configured zone comes from the optional PMACONTROL_TIMEZONE constant
 * defined in `configuration/pmacontrol.config.php`. We validate the value
 * against PHP's built-in zone list and fall back to a known-good default
 * when the constant is missing or invalid.
 */
final class TimezoneAligner
{
    public const DEFAULT_TIMEZONE = 'Europe/Paris';

    /**
     * Decide which timezone identifier should be applied. Validates against
     * the canonical list to refuse "UTC ", "Europe/paris" (case-sensitive
     * mismatch), or any garbage that would slide through PHP's loose
     * `date_default_timezone_set` (which silently degrades to UTC on
     * unknown input).
     */
    public static function resolveConfiguredTimezone($configured): string
    {
        if (is_string($configured)) {
            $candidate = trim($configured);
            if ($candidate !== '' && in_array($candidate, DateTimeZone::listIdentifiers(), true)) {
                return $candidate;
            }
        }

        return self::DEFAULT_TIMEZONE;
    }

    /**
     * Apply the resolved timezone via date_default_timezone_set. Returns the
     * identifier that ended up being used so callers and tests can assert on
     * the effective state.
     */
    public static function apply($configured = null): string
    {
        $timezone = self::resolveConfiguredTimezone($configured);
        @date_default_timezone_set($timezone);

        return $timezone;
    }
}
