<?php

declare(strict_types=1);

namespace App\Library\Audit;

use Glial\Sgbd\Sgbd;

/**
 * GeoIP lookup for the audit module (#1235 post-MVP #4).
 *
 * Resolves an IPv4 / IPv6 to (country_iso, city) via the existing
 * `data_geoip` + `data_geoip_city` range tables (see docs/data_geoip.md).
 * Loopback / private / reserved ranges short-circuit to NULL.
 *
 * Optimised for the drain's batch use case: one batch INSERT-into-temp
 * resolve, results memoised on a per-process LRU.
 */
final class GeoIpResolver
{
    /** @var array<string,array{0:?string,1:?string}> */
    private static $cache = [];
    private const CACHE_SIZE = 4096;

    /**
     * Returns [country_iso, city] for the given IP, or [null, null] for
     * private / reserved ranges or DB miss.
     *
     * @return array{0:?string,1:?string}
     */
    public static function resolve(string $ip): array
    {
        if ($ip === '' || self::isPrivate($ip)) {
            return [null, null];
        }
        if (isset(self::$cache[$ip])) {
            return self::$cache[$ip];
        }
        $db = Sgbd::sql(DB_DEFAULT);
        $esc = $db->sql_real_escape_string($ip);

        $country = null;
        $city = null;

        // Try city first (richer); fall back to country if no city row.
        $sqlCity = "SELECT country_iso, city FROM data_geoip_city "
                 . "WHERE network_start <= INET6_ATON('$esc') AND network_end >= INET6_ATON('$esc') "
                 . "ORDER BY network_start DESC LIMIT 1";
        $res = @$db->sql_query($sqlCity);
        if ($res) {
            $row = $db->sql_fetch_array($res, MYSQLI_ASSOC);
            if ($row) {
                $country = (string) ($row['country_iso'] ?? '') ?: null;
                $city    = (string) ($row['city'] ?? '') ?: null;
            }
        }
        if ($country === null) {
            $sqlCountry = "SELECT country_iso FROM data_geoip "
                        . "WHERE network_start <= INET6_ATON('$esc') AND network_end >= INET6_ATON('$esc') "
                        . "ORDER BY network_start DESC LIMIT 1";
            $res = @$db->sql_query($sqlCountry);
            if ($res) {
                $row = $db->sql_fetch_array($res, MYSQLI_ASSOC);
                if ($row) {
                    $country = (string) ($row['country_iso'] ?? '') ?: null;
                }
            }
        }
        if (count(self::$cache) >= self::CACHE_SIZE) {
            // Simple eviction: drop the first 25 % of entries.
            self::$cache = array_slice(self::$cache, (int) (self::CACHE_SIZE * 0.25), null, true);
        }
        self::$cache[$ip] = [$country, $city];
        return self::$cache[$ip];
    }

    /**
     * Loopback / RFC1918 / link-local / unique-local — never in GeoIP.
     */
    public static function isPrivate(string $ip): bool
    {
        $bin = @inet_pton($ip);
        if ($bin === false) {
            return false;
        }
        $isV6 = strlen($bin) === 16;
        if ($isV6) {
            // ::1 loopback
            if ($bin === "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\1") return true;
            // fe80::/10  link-local
            if ((ord($bin[0]) === 0xfe) && ((ord($bin[1]) & 0xc0) === 0x80)) return true;
            // fc00::/7   unique local
            if ((ord($bin[0]) & 0xfe) === 0xfc) return true;
            return false;
        }
        // 127/8
        if (ord($bin[0]) === 127) return true;
        // 10/8
        if (ord($bin[0]) === 10) return true;
        // 172.16/12
        if (ord($bin[0]) === 172 && (ord($bin[1]) & 0xf0) === 16) return true;
        // 192.168/16
        if (ord($bin[0]) === 192 && ord($bin[1]) === 168) return true;
        // 169.254/16 link-local
        if (ord($bin[0]) === 169 && ord($bin[1]) === 254) return true;
        return false;
    }
}
