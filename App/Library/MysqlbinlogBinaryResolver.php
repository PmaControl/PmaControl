<?php

declare(strict_types=1);

namespace App\Library;

/**
 * Pure-logic picker for the mysqlbinlog binary path that matches a
 * server version. (#1268)
 *
 * The mysqlbinlog tool ships with each MySQL / MariaDB release and is
 * generally only forwards-compatible: a 10.x binary can't always parse
 * a 11.x master's version banner, an 8.0 binary can't parse 8.4
 * binlog events, etc. PmaControl bundles a fleet of binaries under
 * `bin/mysqlbinlog/<arch>/` and picks one per analysis run.
 *
 * Naming conventions of the bundle:
 *   mysqlbinlog-mariadb              (legacy: any MariaDB version)
 *   mysqlbinlog-mariadb-<major.minor> (preferred: 10.11, 11.8, …)
 *   mysqlbinlog-<major.minor>         (MySQL Oracle: 5.6, 5.7, 8.0, …)
 *
 * The resolver is intentionally pure — it takes the server version
 * string + a list of available binary paths and returns the chosen
 * path (or null). Tests inject synthetic file lists; no I/O.
 */
final class MysqlbinlogBinaryResolver
{
    /**
     * @param  string $version           Server version banner, e.g.
     *                                  `'11.8.6-MariaDB-0+deb13u1 from Debian-log'`.
     * @param  list<string> $available   Absolute paths of bundled binaries
     *                                  (typically `glob('.../mysqlbinlog-*')`).
     * @return string|null               Chosen binary path, or null if
     *                                  nothing in `$available` matches.
     */
    public static function pick(string $version, array $available): ?string
    {
        if (empty($available)) {
            return null;
        }

        $majorMinor = MysqlVersion::majorMinor($version);
        $isMariaDb  = MysqlVersion::isMariaDb($version);

        // 1) Exact version-specific match wins.
        if ($majorMinor !== '') {
            $wanted = $isMariaDb
                ? 'mysqlbinlog-mariadb-' . $majorMinor
                : 'mysqlbinlog-' . $majorMinor;
            foreach ($available as $path) {
                if (basename($path) === $wanted) {
                    return $path;
                }
            }
        }

        // 2) MariaDB: fall back to the legacy generic binary.
        if ($isMariaDb) {
            foreach ($available as $path) {
                if (basename($path) === 'mysqlbinlog-mariadb') {
                    return $path;
                }
            }
            return null;
        }

        // 3) MySQL Oracle: pick the lowest version >= requested
        //    (forward-compat is more reliable than going backwards).
        //    Falls back to the highest available if nothing >= matches.
        if ($majorMinor === '') {
            return null;
        }
        $candidates = [];
        foreach ($available as $path) {
            $name = basename($path);
            if (preg_match('/^mysqlbinlog-(\d+\.\d+)$/', $name, $m)) {
                $candidates[$m[1]] = $path;
            }
        }
        if (empty($candidates)) {
            return null;
        }
        uksort($candidates, static fn ($a, $b) => version_compare($a, $b));

        foreach ($candidates as $ver => $path) {
            if (version_compare($ver, $majorMinor, '>=')) {
                return $path;
            }
        }
        return end($candidates) ?: null;
    }

    /**
     * Companion of pick(): friendly diagnostic for the operator when no
     * binary can serve `$version`. Used by the analyzer's exception
     * message so the next failure carries actionable advice instead of
     * a bare "no binary found".
     */
    public static function suggestForVersion(string $version): string
    {
        $mm = MysqlVersion::majorMinor($version);
        if (MysqlVersion::isMariaDb($version)) {
            $wanted = $mm !== '' ? "mysqlbinlog-mariadb-{$mm}" : 'mysqlbinlog-mariadb';
            return "ship a `{$wanted}` (or `mysqlbinlog-mariadb`) binary under bin/mysqlbinlog/<arch>/";
        }
        if ($mm !== '') {
            return "ship a `mysqlbinlog-{$mm}` binary under bin/mysqlbinlog/<arch>/";
        }
        return 'ship a mysqlbinlog binary that matches your server version';
    }
}
