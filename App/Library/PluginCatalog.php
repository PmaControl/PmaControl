<?php
/**
 * PluginCatalog — knowledge base of common MySQL / MariaDB plugins
 * (storage engines and feature plugins) with installation hints.
 *
 * Used by the /MysqlServer/plugins page (#1212 follow-up) to render
 * a per-server matrix that contrasts:
 *   - what's actually loaded on the target (live PLUGINS / ENGINES),
 *   - what's hot-installable via INSTALL SONAME (no restart),
 *   - what requires `apt install …` plus a service restart before
 *     INSTALL SONAME becomes possible.
 *
 * The catalog matches the MariaDB Server 11.x package set on Debian
 * 12 (`apt-cache search mariadb-plugin-…`) plus the few core plugins
 * that ship with `mariadb-server` itself. MySQL columns are kept so
 * a future MySQL host can be served from the same matrix; rows that
 * do not exist on a given family carry `availability = na` and the
 * view filters them out.
 */

namespace App\Library;

class PluginCatalog
{
    public const KIND_ENGINE = 'engine';
    public const KIND_PLUGIN = 'plugin';

    public const AVAILABILITY_CORE    = 'core';     // shipped with the server package — INSTALL SONAME works directly
    public const AVAILABILITY_PACKAGE = 'package';  // requires an OS package install + restart
    public const AVAILABILITY_NA      = 'na';       // not available on this family

    /**
     * @return array<int, array>
     */
    public static function all(): array
    {
        // The catalog reflects what `mariadb-server` + the optional
        // `mariadb-plugin-*` packages ship on Debian 12 (see
        // /usr/lib/mysql/plugin/*.so). Once a package is installed on
        // disk, the `.so` is present and INSTALL SONAME is hot — we
        // therefore mark these `core` from the runtime's point of
        // view, even though they originate from a separate package.
        // The MySQL column is kept for future-proofing; the view
        // drops it from the rendered matrix.
        return [
            // ── Storage engines — core (mariadb-server / mysql-server) ───
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
                'description' => 'Remote MySQL-protocol tables (FederatedX in MariaDB).',
                'mariadb' => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'ha_federatedx', 'package' => 'mariadb-server'],
                'mysql'   => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'ha_federated',  'package' => 'mysql-server'],
            ],
            [
                'name' => 'SPHINX',
                'kind' => self::KIND_ENGINE,
                'description' => 'SphinxSE — query a sphinxsearch daemon as a MySQL table.',
                'mariadb' => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'ha_sphinx',     'package' => 'mariadb-server'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA],
            ],
            [
                'name' => 'HANDLERSOCKET',
                'kind' => self::KIND_ENGINE,
                'description' => 'NoSQL-style direct access bypassing the SQL layer.',
                'mariadb' => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'handlersocket', 'package' => 'mariadb-server'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA],
            ],

            // ── Storage engines — packaged separately on Debian 12 ──────
            [
                'name' => 'ROCKSDB',
                'kind' => self::KIND_ENGINE,
                'description' => 'LSM-tree engine (MyRocks) — write-heavy / SSD-friendly workloads.',
                'mariadb' => ['availability' => self::AVAILABILITY_PACKAGE, 'soname' => 'ha_rocksdb',   'package' => 'mariadb-plugin-rocksdb'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA],
            ],
            [
                'name' => 'SPIDER',
                'kind' => self::KIND_ENGINE,
                'description' => 'Sharded / horizontal partitioning across remote servers.',
                'mariadb' => ['availability' => self::AVAILABILITY_PACKAGE, 'soname' => 'ha_spider',    'package' => 'mariadb-plugin-spider',
                              'install_hint' => 'Run install_spider.sql after the package install.'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA],
            ],
            [
                'name' => 'COLUMNSTORE',
                'kind' => self::KIND_ENGINE,
                'description' => 'Columnar engine for analytical workloads.',
                'mariadb' => ['availability' => self::AVAILABILITY_PACKAGE, 'soname' => 'ha_columnstore', 'package' => 'mariadb-plugin-columnstore',
                              'install_hint' => 'Heavy package — pulls additional dependencies.'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA],
            ],
            [
                'name' => 'CONNECT',
                'kind' => self::KIND_ENGINE,
                'description' => 'External-data engine — CSV, ODBC, JSON, etc.',
                'mariadb' => ['availability' => self::AVAILABILITY_PACKAGE, 'soname' => 'ha_connect',   'package' => 'mariadb-plugin-connect'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA],
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
                'mysql'   => ['availability' => self::AVAILABILITY_NA],
            ],
            [
                'name' => 'S3',
                'kind' => self::KIND_ENGINE,
                'description' => 'Read-only tables backed by an S3 bucket — cold archive.',
                'mariadb' => ['availability' => self::AVAILABILITY_PACKAGE, 'soname' => 'ha_s3',        'package' => 'mariadb-plugin-s3'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA],
            ],

            // ── Auth / security plugins (MariaDB core, shipped in mariadb-server) ──
            [
                'name' => 'auth_ed25519',
                'kind' => self::KIND_PLUGIN,
                'description' => 'Ed25519 client authentication (MariaDB-native, more secure than mysql_native_password).',
                'mariadb' => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'auth_ed25519',   'package' => 'mariadb-server'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA],
            ],
            [
                'name' => 'auth_pam',
                'kind' => self::KIND_PLUGIN,
                'description' => 'PAM authentication — delegate to /etc/pam.d.',
                'mariadb' => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'auth_pam',       'package' => 'mariadb-server'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA],
            ],
            [
                'name' => 'file_key_management',
                'kind' => self::KIND_PLUGIN,
                'description' => 'Encryption-at-rest with file-based keys (innodb_encrypt_tables backend).',
                'mariadb' => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'file_key_management', 'package' => 'mariadb-server'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA],
            ],
            [
                'name' => 'password_reuse_check',
                'kind' => self::KIND_PLUGIN,
                'description' => 'Reject password reuse on ALTER USER.',
                'mariadb' => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'password_reuse_check', 'package' => 'mariadb-server'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA],
            ],
            [
                'name' => 'server_audit',
                'kind' => self::KIND_PLUGIN,
                'description' => 'Audit log of every query and connection.',
                'mariadb' => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'server_audit', 'package' => 'mariadb-server'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA,
                              'install_hint' => 'Use audit_log on MySQL (Enterprise) or Percona/McAfee plugin.'],
            ],
            [
                'name' => 'simple_password_check',
                'kind' => self::KIND_PLUGIN,
                'description' => 'Min length / character mix policy on user passwords.',
                'mariadb' => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'simple_password_check', 'package' => 'mariadb-server'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA,
                              'install_hint' => 'Use validate_password on MySQL.'],
            ],
            [
                'name' => 'cracklib_password_check',
                'kind' => self::KIND_PLUGIN,
                'description' => 'cracklib-based password strength check.',
                'mariadb' => ['availability' => self::AVAILABILITY_PACKAGE, 'soname' => 'cracklib_password_check', 'package' => 'mariadb-plugin-cracklib-password-check'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA],
            ],
            [
                'name' => 'auth_gssapi',
                'kind' => self::KIND_PLUGIN,
                'description' => 'Kerberos / GSSAPI authentication (server side).',
                'mariadb' => ['availability' => self::AVAILABILITY_PACKAGE, 'soname' => 'auth_gssapi',  'package' => 'mariadb-plugin-gssapi-server'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA],
            ],
            [
                'name' => 'hashicorp_key_management',
                'kind' => self::KIND_PLUGIN,
                'description' => 'Encryption-at-rest key management via HashiCorp Vault.',
                'mariadb' => ['availability' => self::AVAILABILITY_PACKAGE, 'soname' => 'hashicorp_key_management', 'package' => 'mariadb-plugin-hashicorp-key-management'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA],
            ],
            [
                'name' => 'query_response_time',
                'kind' => self::KIND_PLUGIN,
                'description' => 'INFORMATION_SCHEMA.QUERY_RESPONSE_TIME histogram.',
                'mariadb' => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'query_response_time', 'package' => 'mariadb-server'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA],
            ],

            // ── INFORMATION_SCHEMA / observability plugins (MariaDB core) ──
            [
                'name' => 'disks',
                'kind' => self::KIND_PLUGIN,
                'description' => 'INFORMATION_SCHEMA.DISKS — disk usage of mounted filesystems.',
                'mariadb' => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'disks',          'package' => 'mariadb-server'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA],
            ],
            [
                'name' => 'locales',
                'kind' => self::KIND_PLUGIN,
                'description' => 'INFORMATION_SCHEMA.LOCALES — server locale catalogue.',
                'mariadb' => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'locales',        'package' => 'mariadb-server'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA],
            ],
            [
                'name' => 'metadata_lock_info',
                'kind' => self::KIND_PLUGIN,
                'description' => 'INFORMATION_SCHEMA.METADATA_LOCK_INFO — visibility on MDL waits.',
                'mariadb' => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'metadata_lock_info', 'package' => 'mariadb-server'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA],
            ],
            [
                'name' => 'query_cache_info',
                'kind' => self::KIND_PLUGIN,
                'description' => 'INFORMATION_SCHEMA.QUERY_CACHE_INFO — cached entries inspection.',
                'mariadb' => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'query_cache_info', 'package' => 'mariadb-server'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA],
            ],
            [
                'name' => 'wsrep_info',
                'kind' => self::KIND_PLUGIN,
                'description' => 'INFORMATION_SCHEMA.WSREP_* — Galera membership and stats.',
                'mariadb' => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'wsrep_info',     'package' => 'mariadb-server'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA],
            ],
            [
                'name' => 'sql_errlog',
                'kind' => self::KIND_PLUGIN,
                'description' => 'Log every server-side SQL error to a file.',
                'mariadb' => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'sql_errlog',     'package' => 'mariadb-server'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA],
            ],
            [
                'name' => 'type_mysql_json',
                'kind' => self::KIND_PLUGIN,
                'description' => 'MySQL-compatible JSON column type (replaces MariaDB LONGTEXT-with-CHECK).',
                'mariadb' => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'type_mysql_json','package' => 'mariadb-server'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA],
            ],

            // ── Compression providers (MariaDB plugin packages) ─────────
            [
                'name' => 'provider_bzip2',
                'kind' => self::KIND_PLUGIN,
                'description' => 'BZip2 compression provider for the server + storage engines.',
                'mariadb' => ['availability' => self::AVAILABILITY_PACKAGE, 'soname' => 'provider_bzip2', 'package' => 'mariadb-plugin-provider-bzip2'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA],
            ],
            [
                'name' => 'provider_lz4',
                'kind' => self::KIND_PLUGIN,
                'description' => 'LZ4 compression provider for the server + storage engines.',
                'mariadb' => ['availability' => self::AVAILABILITY_PACKAGE, 'soname' => 'provider_lz4',  'package' => 'mariadb-plugin-provider-lz4'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA],
            ],
            [
                'name' => 'provider_lzma',
                'kind' => self::KIND_PLUGIN,
                'description' => 'LZMA compression provider for the server + storage engines.',
                'mariadb' => ['availability' => self::AVAILABILITY_PACKAGE, 'soname' => 'provider_lzma', 'package' => 'mariadb-plugin-provider-lzma'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA],
            ],
            [
                'name' => 'provider_lzo',
                'kind' => self::KIND_PLUGIN,
                'description' => 'LZO compression provider for the server + storage engines.',
                'mariadb' => ['availability' => self::AVAILABILITY_PACKAGE, 'soname' => 'provider_lzo',  'package' => 'mariadb-plugin-provider-lzo'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA],
            ],
            [
                'name' => 'provider_snappy',
                'kind' => self::KIND_PLUGIN,
                'description' => 'Snappy compression provider for the server + storage engines.',
                'mariadb' => ['availability' => self::AVAILABILITY_PACKAGE, 'soname' => 'provider_snappy','package' => 'mariadb-plugin-provider-snappy'],
                'mysql'   => ['availability' => self::AVAILABILITY_NA],
            ],

            // ── MySQL-only feature plugins ──────────────────────────────
            [
                'name' => 'validate_password',
                'kind' => self::KIND_PLUGIN,
                'description' => 'Password strength policy (MySQL).',
                'mariadb' => ['availability' => self::AVAILABILITY_NA],
                'mysql'   => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'validate_password', 'package' => 'mysql-server'],
            ],
            [
                'name' => 'group_replication',
                'kind' => self::KIND_PLUGIN,
                'description' => 'Multi-primary group replication (MySQL InnoDB Cluster).',
                'mariadb' => ['availability' => self::AVAILABILITY_NA,
                              'install_hint' => 'MariaDB uses Galera (wsrep) instead.'],
                'mysql'   => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'group_replication','package' => 'mysql-server'],
            ],
            [
                'name' => 'clone',
                'kind' => self::KIND_PLUGIN,
                'description' => 'Native instance clone (MySQL 8+).',
                'mariadb' => ['availability' => self::AVAILABILITY_NA,
                              'install_hint' => 'Use mariabackup on MariaDB.'],
                'mysql'   => ['availability' => self::AVAILABILITY_CORE,    'soname' => 'mysql_clone',       'package' => 'mysql-server'],
            ],
            [
                'name' => 'audit_log',
                'kind' => self::KIND_PLUGIN,
                'description' => 'Audit log (MySQL Enterprise / Percona).',
                'mariadb' => ['availability' => self::AVAILABILITY_NA,
                              'install_hint' => 'Use server_audit on MariaDB.'],
                'mysql'   => ['availability' => self::AVAILABILITY_PACKAGE, 'soname' => 'audit_log',    'package' => 'percona-audit-log-plugin (or MySQL Enterprise)'],
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
     * Subset of the catalog where the given family is supported
     * (availability != 'na'). Used by the matrix view to hide MySQL
     * rows on a MariaDB host and vice versa.
     *
     * Sorted: storage engines first, then plugins, each block sorted
     * alphabetically by name. Stable visual order on every refresh.
     *
     * @return array<int, array>
     */
    public static function forFamily(string $family): array
    {
        $rows = array_values(array_filter(self::all(), static function ($entry) use ($family) {
            return ($entry[$family]['availability'] ?? self::AVAILABILITY_NA)
                !== self::AVAILABILITY_NA;
        }));
        return self::sortEnginesFirstThenPlugins($rows);
    }

    /**
     * Sort the given catalog list so that storage engines come first
     * (alphabetical), then plugins (alphabetical). Used by the matrix
     * view; exposed publicly so callers that don't filter by family
     * (the `unknown`-family fallback in MysqlServer::plugins) can get
     * the same ordering.
     *
     * @param array<int, array> $rows
     * @return array<int, array>
     */
    public static function sortEnginesFirstThenPlugins(array $rows): array
    {
        usort($rows, static function ($a, $b) {
            // Engines (kind=engine) sort weight 0; plugins weight 1.
            $wa = $a['kind'] === self::KIND_ENGINE ? 0 : 1;
            $wb = $b['kind'] === self::KIND_ENGINE ? 0 : 1;
            if ($wa !== $wb) return $wa <=> $wb;
            return strcasecmp((string) $a['name'], (string) $b['name']);
        });
        return $rows;
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
