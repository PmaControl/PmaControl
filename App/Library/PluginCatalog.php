<?php
/**
 * PluginCatalog — knowledge base of common MySQL / MariaDB plugins
 * (storage engines and feature plugins) with installation hints.
 *
 * Used by the /MysqlServer/plugins page (#1212 follow-up) to render
 * a per-server matrix that contrasts:
 *   - what's actually loaded on the target (live PLUGINS / ENGINES),
 *   - what could be loaded **hot** via INSTALL SONAME (no package
 *     install, no restart),
 *   - what requires `apt install …` (or equivalent) plus a service
 *     restart before INSTALL SONAME becomes possible.
 *
 * The catalog is intentionally hand-curated and short — it covers
 * the engines / plugins PmaControl has historical opinions about,
 * not every plugin in the world.
 */

namespace App\Library;

class PluginCatalog
{
    public const KIND_ENGINE = 'engine';
    public const KIND_PLUGIN = 'plugin';

    public const AVAILABILITY_CORE    = 'core';     // shipped with the server package, just INSTALL SONAME
    public const AVAILABILITY_PACKAGE = 'package';  // requires an OS package install + restart
    public const AVAILABILITY_NA      = 'na';       // not available on this server family

    /**
     * Catalog entry shape:
     * [
     *   'name'        => 'BLACKHOLE',
     *   'kind'        => self::KIND_ENGINE | self::KIND_PLUGIN,
     *   'description' => 'short user-facing description',
     *   'mariadb'     => [
     *     'availability' => self::AVAILABILITY_CORE | _PACKAGE | _NA,
     *     'soname'       => 'ha_blackhole',     // for INSTALL SONAME
     *     'package'      => 'mariadb-server',   // apt package
     *     'install_hint' => '…',                // operator note
     *   ],
     *   'mysql'       => [ same shape ],
     * ]
     *
     * @return array<int, array>
     */
    public static function all(): array
    {
        return [
            // ── Storage engines ───────────────────────────────────────
            [
                'name' => 'BLACKHOLE',
                'kind' => self::KIND_ENGINE,
                'description' => 'Discards every write; used as a binlog relay with log_slave_updates=ON (#1212).',
                'mariadb' => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'ha_blackhole', 'package' => 'mariadb-server'],
                'mysql'   => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'ha_blackhole', 'package' => 'mysql-server'],
            ],
            [
                'name' => 'ARCHIVE',
                'kind' => self::KIND_ENGINE,
                'description' => 'Append-only compressed table — historical / log archives.',
                'mariadb' => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'ha_archive',   'package' => 'mariadb-server'],
                'mysql'   => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'ha_archive',   'package' => 'mysql-server'],
            ],
            [
                'name' => 'FEDERATED',
                'kind' => self::KIND_ENGINE,
                'description' => 'Remote MySQL/MariaDB tables via the MySQL protocol (single-server).',
                'mariadb' => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'ha_federatedx', 'package' => 'mariadb-server'],
                'mysql'   => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'ha_federated',  'package' => 'mysql-server'],
            ],
            [
                'name' => 'ROCKSDB',
                'kind' => self::KIND_ENGINE,
                'description' => 'LSM-tree engine (MyRocks) — write-heavy / SSD-friendly workloads.',
                'mariadb' => ['availability' => self::AVAILABILITY_PACKAGE, 'soname' => 'ha_rocksdb',   'package' => 'mariadb-plugin-rocksdb'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA,      'soname' => '',             'package' => '',
                              'install_hint' => 'MyRocks is shipped by Percona Server, not stock MySQL Community.'],
            ],
            [
                'name' => 'SPIDER',
                'kind' => self::KIND_ENGINE,
                'description' => 'Sharded / horizontal partitioning across remote servers.',
                'mariadb' => ['availability' => self::AVAILABILITY_PACKAGE, 'soname' => 'ha_spider',    'package' => 'mariadb-plugin-spider',
                              'install_hint' => 'Run install_spider.sql after the package install.'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA,      'soname' => '',             'package' => '',
                              'install_hint' => 'SPIDER is MariaDB-only.'],
            ],
            [
                'name' => 'COLUMNSTORE',
                'kind' => self::KIND_ENGINE,
                'description' => 'Columnar engine for analytical workloads.',
                'mariadb' => ['availability' => self::AVAILABILITY_PACKAGE, 'soname' => 'ha_columnstore', 'package' => 'mariadb-plugin-columnstore',
                              'install_hint' => 'Heavy package — pulls additional dependencies.'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA,      'soname' => '',               'package' => ''],
            ],
            [
                'name' => 'CONNECT',
                'kind' => self::KIND_ENGINE,
                'description' => 'External-data engine — CSV, ODBC, JSON, etc.',
                'mariadb' => ['availability' => self::AVAILABILITY_PACKAGE, 'soname' => 'ha_connect',   'package' => 'mariadb-plugin-connect'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA,      'soname' => '',             'package' => ''],
            ],
            [
                'name' => 'MROONGA',
                'kind' => self::KIND_ENGINE,
                'description' => 'Full-text search engine (CJK-friendly).',
                'mariadb' => ['availability' => self::AVAILABILITY_PACKAGE, 'soname' => 'ha_mroonga',   'package' => 'mariadb-plugin-mroonga'],
                'mysql'   => ['availability' => self::AVAILABILITY_PACKAGE, 'soname' => 'ha_mroonga',   'package' => 'mysql-mroonga'],
            ],
            [
                'name' => 'OQGRAPH',
                'kind' => self::KIND_ENGINE,
                'description' => 'Graph computation engine (shortest path, etc.).',
                'mariadb' => ['availability' => self::AVAILABILITY_PACKAGE, 'soname' => 'ha_oqgraph',   'package' => 'mariadb-plugin-oqgraph'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA,      'soname' => '',             'package' => ''],
            ],
            [
                'name' => 'S3',
                'kind' => self::KIND_ENGINE,
                'description' => 'Read-only tables backed by an S3 bucket — cold archive.',
                'mariadb' => ['availability' => self::AVAILABILITY_PACKAGE, 'soname' => 'ha_s3',        'package' => 'mariadb-plugin-s3'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA,      'soname' => '',             'package' => ''],
            ],

            // ── Feature plugins (non-engine) ──────────────────────────
            [
                'name' => 'server_audit',
                'kind' => self::KIND_PLUGIN,
                'description' => 'Audit log of every query and connection (MariaDB).',
                'mariadb' => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'server_audit', 'package' => 'mariadb-server'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA,      'soname' => '',             'package' => '',
                              'install_hint' => 'Use audit_log on MySQL (Enterprise) or McAfee/Percona audit plugin.'],
            ],
            [
                'name' => 'audit_log',
                'kind' => self::KIND_PLUGIN,
                'description' => 'Audit log (MySQL Enterprise / Percona).',
                'mariadb' => ['availability' => self::AVAILABILITY_NA,      'soname' => '',             'package' => '',
                              'install_hint' => 'Use server_audit on MariaDB.'],
                'mysql'   => ['availability' => self::AVAILABILITY_PACKAGE, 'soname' => 'audit_log',    'package' => 'percona-audit-log-plugin (or MySQL Enterprise)'],
            ],
            [
                'name' => 'query_response_time',
                'kind' => self::KIND_PLUGIN,
                'description' => 'INFORMATION_SCHEMA.QUERY_RESPONSE_TIME histogram.',
                'mariadb' => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'query_response_time', 'package' => 'mariadb-server'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA,      'soname' => '',                    'package' => ''],
            ],
            [
                'name' => 'simple_password_check',
                'kind' => self::KIND_PLUGIN,
                'description' => 'Min length / character mix policy on user passwords (MariaDB).',
                'mariadb' => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'simple_password_check', 'package' => 'mariadb-server'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA,      'soname' => '',                      'package' => '',
                              'install_hint' => 'Use validate_password on MySQL.'],
            ],
            [
                'name' => 'validate_password',
                'kind' => self::KIND_PLUGIN,
                'description' => 'Password strength policy (MySQL).',
                'mariadb' => ['availability' => self::AVAILABILITY_NA,      'soname' => '',                  'package' => ''],
                'mysql'   => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'validate_password', 'package' => 'mysql-server'],
            ],
            [
                'name' => 'group_replication',
                'kind' => self::KIND_PLUGIN,
                'description' => 'Multi-primary group replication (MySQL InnoDB Cluster).',
                'mariadb' => ['availability' => self::AVAILABILITY_NA,      'soname' => '',                  'package' => '',
                              'install_hint' => 'MariaDB uses Galera (wsrep) instead.'],
                'mysql'   => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'group_replication','package' => 'mysql-server'],
            ],
            [
                'name' => 'clone',
                'kind' => self::KIND_PLUGIN,
                'description' => 'Native instance clone (MySQL 8+).',
                'mariadb' => ['availability' => self::AVAILABILITY_NA,      'soname' => '',                  'package' => '',
                              'install_hint' => 'Use mariabackup on MariaDB.'],
                'mysql'   => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'mysql_clone',       'package' => 'mysql-server'],
            ],
            [
                'name' => 'cracklib_password_check',
                'kind' => self::KIND_PLUGIN,
                'description' => 'cracklib-based password strength check (MariaDB).',
                'mariadb' => ['availability' => self::AVAILABILITY_PACKAGE, 'soname' => 'cracklib_password_check', 'package' => 'mariadb-plugin-cracklib-password-check'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA,      'soname' => '',                        'package' => ''],
            ],
        ];
    }

    /**
     * Look up the catalog entry for a given plugin / engine name
     * (case-insensitive). Returns `null` if not in the catalog.
     */
    public static function find(string $name): ?array
    {
        $needle = strtoupper($name);
        foreach (self::all() as $entry) {
            if (strtoupper($entry['name']) === $needle) return $entry;
        }
        return null;
    }

    /**
     * Identify the server family from a version string. Used to pick
     * the right catalog column on the matrix and the install path.
     * Returns 'mariadb' | 'mysql' | 'unknown'.
     */
    public static function detectFamily(string $versionString): string
    {
        $v = strtolower($versionString);
        if (strpos($v, 'mariadb') !== false) return 'mariadb';
        if ($v !== '' && (strpos($v, 'mysql') !== false || preg_match('/^\d+\.\d+\.\d+/', $v))) {
            return 'mysql';
        }
        return 'unknown';
    }
}
