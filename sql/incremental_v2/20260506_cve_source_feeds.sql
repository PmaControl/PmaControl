CREATE TABLE IF NOT EXISTS `cve_feed_source` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(64) NOT NULL,
  `name` varchar(128) NOT NULL,
  `source_kind` enum('api','json','html','github_advisory','vendor_advisory') NOT NULL,
  `priority` int(11) NOT NULL DEFAULT 100,
  `url_base` varchar(512) NOT NULL,
  `url_documentation` varchar(512) DEFAULT NULL,
  `cadence_hours` smallint(5) unsigned NOT NULL DEFAULT 24,
  `parser_class` varchar(128) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `description` text NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cve_feed_source_code` (`code`),
  KEY `idx_cve_feed_source_active_priority` (`is_active`,`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `cve_feed_source_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_cve_feed_source` int(11) NOT NULL,
  `product_code` varchar(64) NOT NULL,
  `product_name` varchar(128) NOT NULL,
  `cpe_vendor` varchar(128) DEFAULT NULL,
  `cpe_product` varchar(128) DEFAULT NULL,
  `notes` text NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cve_feed_source_product` (`id_cve_feed_source`,`product_code`),
  KEY `idx_cve_feed_source_product_code` (`product_code`),
  CONSTRAINT `fk_cve_feed_source_product__cve_feed_source`
    FOREIGN KEY (`id_cve_feed_source`) REFERENCES `cve_feed_source` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `cve_feed_run` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_cve_feed_source` int(11) NOT NULL,
  `run_mode` enum('delta','full','manual','backfill') NOT NULL DEFAULT 'delta',
  `status` enum('queued','running','success','partial','failed') NOT NULL DEFAULT 'queued',
  `started_at` datetime NOT NULL DEFAULT current_timestamp(),
  `finished_at` datetime DEFAULT NULL,
  `cursor_start` varchar(255) DEFAULT NULL,
  `cursor_end` varchar(255) DEFAULT NULL,
  `records_seen` int(11) NOT NULL DEFAULT 0,
  `records_inserted` int(11) NOT NULL DEFAULT 0,
  `records_updated` int(11) NOT NULL DEFAULT 0,
  `error_message` text NULL,
  `payload_hash` char(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cve_feed_run_source_started` (`id_cve_feed_source`,`started_at`),
  KEY `idx_cve_feed_run_status` (`status`),
  CONSTRAINT `fk_cve_feed_run__cve_feed_source`
    FOREIGN KEY (`id_cve_feed_source`) REFERENCES `cve_feed_source` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `cve_source_nvd` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_cve_feed_run` bigint(20) unsigned DEFAULT NULL,
  `cve_id` varchar(32) NOT NULL,
  `source_identifier` varchar(128) DEFAULT NULL,
  `vuln_status` varchar(64) DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `last_modified_at` datetime DEFAULT NULL,
  `severity` enum('none','low','medium','high','critical','unknown') NOT NULL DEFAULT 'unknown',
  `cvss_v2_score` decimal(3,1) DEFAULT NULL,
  `cvss_v3_score` decimal(3,1) DEFAULT NULL,
  `cvss_v4_score` decimal(3,1) DEFAULT NULL,
  `cwe_json` longtext NULL,
  `cpe_match_json` longtext NULL,
  `references_json` longtext NULL,
  `raw_json` longtext NOT NULL,
  `payload_hash` char(64) NOT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 1,
  `date_collected` datetime NOT NULL DEFAULT current_timestamp(),
  `date_first_seen` datetime NOT NULL DEFAULT current_timestamp(),
  `date_last_seen` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cve_source_nvd_history` (`cve_id`,`payload_hash`),
  KEY `idx_cve_source_nvd_cve_current` (`cve_id`,`is_current`),
  KEY `idx_cve_source_nvd_modified` (`last_modified_at`),
  KEY `idx_cve_source_nvd_feed_run` (`id_cve_feed_run`),
  CONSTRAINT `fk_cve_source_nvd__cve_feed_run`
    FOREIGN KEY (`id_cve_feed_run`) REFERENCES `cve_feed_run` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `cve_source_osv` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_cve_feed_run` bigint(20) unsigned DEFAULT NULL,
  `osv_id` varchar(128) NOT NULL,
  `published_at` datetime DEFAULT NULL,
  `modified_at` datetime DEFAULT NULL,
  `withdrawn_at` datetime DEFAULT NULL,
  `schema_version` varchar(32) DEFAULT NULL,
  `summary` text NULL,
  `aliases_json` longtext NULL,
  `related_json` longtext NULL,
  `affected_json` longtext NULL,
  `severity_json` longtext NULL,
  `references_json` longtext NULL,
  `raw_json` longtext NOT NULL,
  `payload_hash` char(64) NOT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 1,
  `date_collected` datetime NOT NULL DEFAULT current_timestamp(),
  `date_first_seen` datetime NOT NULL DEFAULT current_timestamp(),
  `date_last_seen` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cve_source_osv_history` (`osv_id`,`payload_hash`),
  KEY `idx_cve_source_osv_current` (`osv_id`,`is_current`),
  KEY `idx_cve_source_osv_modified` (`modified_at`),
  KEY `idx_cve_source_osv_feed_run` (`id_cve_feed_run`),
  CONSTRAINT `fk_cve_source_osv__cve_feed_run`
    FOREIGN KEY (`id_cve_feed_run`) REFERENCES `cve_feed_run` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `cve_source_ghsa` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_cve_feed_run` bigint(20) unsigned DEFAULT NULL,
  `ghsa_id` varchar(64) NOT NULL,
  `cve_id` varchar(32) DEFAULT NULL,
  `severity` enum('low','medium','high','critical','unknown') NOT NULL DEFAULT 'unknown',
  `cvss_score` decimal(3,1) DEFAULT NULL,
  `cvss_vector` varchar(255) DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `github_reviewed_at` datetime DEFAULT NULL,
  `identifiers_json` longtext NULL,
  `cwe_json` longtext NULL,
  `vulnerabilities_json` longtext NULL,
  `references_json` longtext NULL,
  `raw_json` longtext NOT NULL,
  `payload_hash` char(64) NOT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 1,
  `date_collected` datetime NOT NULL DEFAULT current_timestamp(),
  `date_first_seen` datetime NOT NULL DEFAULT current_timestamp(),
  `date_last_seen` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cve_source_ghsa_history` (`ghsa_id`,`payload_hash`),
  KEY `idx_cve_source_ghsa_cve` (`cve_id`),
  KEY `idx_cve_source_ghsa_current` (`ghsa_id`,`is_current`),
  KEY `idx_cve_source_ghsa_feed_run` (`id_cve_feed_run`),
  CONSTRAINT `fk_cve_source_ghsa__cve_feed_run`
    FOREIGN KEY (`id_cve_feed_run`) REFERENCES `cve_feed_run` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `cve_source_cisa_kev` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_cve_feed_run` bigint(20) unsigned DEFAULT NULL,
  `cve_id` varchar(32) NOT NULL,
  `vendor_project` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `vulnerability_name` varchar(512) DEFAULT NULL,
  `date_added` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `known_ransomware_campaign_use` varchar(32) DEFAULT NULL,
  `required_action` text NULL,
  `notes` text NULL,
  `raw_json` longtext NOT NULL,
  `payload_hash` char(64) NOT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 1,
  `date_collected` datetime NOT NULL DEFAULT current_timestamp(),
  `date_first_seen` datetime NOT NULL DEFAULT current_timestamp(),
  `date_last_seen` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cve_source_cisa_kev_history` (`cve_id`,`payload_hash`),
  KEY `idx_cve_source_cisa_kev_current` (`cve_id`,`is_current`),
  KEY `idx_cve_source_cisa_kev_date_added` (`date_added`),
  KEY `idx_cve_source_cisa_kev_feed_run` (`id_cve_feed_run`),
  CONSTRAINT `fk_cve_source_cisa_kev__cve_feed_run`
    FOREIGN KEY (`id_cve_feed_run`) REFERENCES `cve_feed_run` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `cve_source_oracle_cpu` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_cve_feed_run` bigint(20) unsigned DEFAULT NULL,
  `source_key` varchar(191) NOT NULL,
  `advisory_id` varchar(128) DEFAULT NULL,
  `cpu_cycle` varchar(64) DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `cve_id` varchar(32) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `component` varchar(255) DEFAULT NULL,
  `affected_versions` text NULL,
  `fixed_versions` text NULL,
  `base_score` decimal(3,1) DEFAULT NULL,
  `cvss_vector` varchar(255) DEFAULT NULL,
  `source_url` varchar(1024) DEFAULT NULL,
  `raw_json` longtext NOT NULL,
  `payload_hash` char(64) NOT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 1,
  `date_collected` datetime NOT NULL DEFAULT current_timestamp(),
  `date_first_seen` datetime NOT NULL DEFAULT current_timestamp(),
  `date_last_seen` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cve_source_oracle_cpu_history` (`source_key`,`payload_hash`),
  KEY `idx_cve_source_oracle_cpu_cve` (`cve_id`),
  KEY `idx_cve_source_oracle_cpu_current` (`source_key`,`is_current`),
  KEY `idx_cve_source_oracle_cpu_feed_run` (`id_cve_feed_run`),
  CONSTRAINT `fk_cve_source_oracle_cpu__cve_feed_run`
    FOREIGN KEY (`id_cve_feed_run`) REFERENCES `cve_feed_run` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `cve_source_mariadb_security` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_cve_feed_run` bigint(20) unsigned DEFAULT NULL,
  `source_key` varchar(191) NOT NULL,
  `cve_id` varchar(32) DEFAULT NULL,
  `advisory_url` varchar(1024) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `branch` varchar(64) DEFAULT NULL,
  `affected_versions` text NULL,
  `fixed_versions` text NULL,
  `release_date` date DEFAULT NULL,
  `severity` enum('none','low','medium','high','critical','unknown') NOT NULL DEFAULT 'unknown',
  `raw_json` longtext NOT NULL,
  `payload_hash` char(64) NOT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 1,
  `date_collected` datetime NOT NULL DEFAULT current_timestamp(),
  `date_first_seen` datetime NOT NULL DEFAULT current_timestamp(),
  `date_last_seen` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cve_source_mariadb_security_history` (`source_key`,`payload_hash`),
  KEY `idx_cve_source_mariadb_security_cve` (`cve_id`),
  KEY `idx_cve_source_mariadb_security_current` (`source_key`,`is_current`),
  KEY `idx_cve_source_mariadb_security_feed_run` (`id_cve_feed_run`),
  CONSTRAINT `fk_cve_source_mariadb_security__cve_feed_run`
    FOREIGN KEY (`id_cve_feed_run`) REFERENCES `cve_feed_run` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `cve_source_percona_advisory` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_cve_feed_run` bigint(20) unsigned DEFAULT NULL,
  `source_key` varchar(191) NOT NULL,
  `advisory_id` varchar(128) DEFAULT NULL,
  `cve_id` varchar(32) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `affected_versions` text NULL,
  `fixed_versions` text NULL,
  `published_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `severity` enum('none','low','medium','high','critical','unknown') NOT NULL DEFAULT 'unknown',
  `source_url` varchar(1024) DEFAULT NULL,
  `raw_json` longtext NOT NULL,
  `payload_hash` char(64) NOT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 1,
  `date_collected` datetime NOT NULL DEFAULT current_timestamp(),
  `date_first_seen` datetime NOT NULL DEFAULT current_timestamp(),
  `date_last_seen` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cve_source_percona_advisory_history` (`source_key`,`payload_hash`),
  KEY `idx_cve_source_percona_advisory_cve` (`cve_id`),
  KEY `idx_cve_source_percona_advisory_current` (`source_key`,`is_current`),
  KEY `idx_cve_source_percona_advisory_feed_run` (`id_cve_feed_run`),
  CONSTRAINT `fk_cve_source_percona_advisory__cve_feed_run`
    FOREIGN KEY (`id_cve_feed_run`) REFERENCES `cve_feed_run` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `cve_source_component_ghsa` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_cve_feed_run` bigint(20) unsigned DEFAULT NULL,
  `component` enum('proxysql','maxscale','haproxy','vitess','other') NOT NULL,
  `ghsa_id` varchar(64) NOT NULL,
  `cve_id` varchar(32) DEFAULT NULL,
  `ecosystem` varchar(64) DEFAULT NULL,
  `package_name` varchar(255) DEFAULT NULL,
  `severity` enum('low','medium','high','critical','unknown') NOT NULL DEFAULT 'unknown',
  `vulnerable_version_range` text NULL,
  `patched_versions` text NULL,
  `published_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `references_json` longtext NULL,
  `raw_json` longtext NOT NULL,
  `payload_hash` char(64) NOT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 1,
  `date_collected` datetime NOT NULL DEFAULT current_timestamp(),
  `date_first_seen` datetime NOT NULL DEFAULT current_timestamp(),
  `date_last_seen` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cve_source_component_ghsa_history` (`component`,`ghsa_id`,`payload_hash`),
  KEY `idx_cve_source_component_ghsa_cve` (`cve_id`),
  KEY `idx_cve_source_component_ghsa_current` (`component`,`ghsa_id`,`is_current`),
  KEY `idx_cve_source_component_ghsa_feed_run` (`id_cve_feed_run`),
  CONSTRAINT `fk_cve_source_component_ghsa__cve_feed_run`
    FOREIGN KEY (`id_cve_feed_run`) REFERENCES `cve_feed_run` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `cve_source_aws_security_bulletin` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_cve_feed_run` bigint(20) unsigned DEFAULT NULL,
  `source_key` varchar(191) NOT NULL,
  `bulletin_id` varchar(128) DEFAULT NULL,
  `cve_id` varchar(32) DEFAULT NULL,
  `service` varchar(128) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `engine` varchar(128) DEFAULT NULL,
  `affected_engine_versions` text NULL,
  `fixed_engine_versions` text NULL,
  `published_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `source_url` varchar(1024) DEFAULT NULL,
  `raw_json` longtext NOT NULL,
  `payload_hash` char(64) NOT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 1,
  `date_collected` datetime NOT NULL DEFAULT current_timestamp(),
  `date_first_seen` datetime NOT NULL DEFAULT current_timestamp(),
  `date_last_seen` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cve_source_aws_security_bulletin_history` (`source_key`,`payload_hash`),
  KEY `idx_cve_source_aws_security_bulletin_cve` (`cve_id`),
  KEY `idx_cve_source_aws_security_bulletin_current` (`source_key`,`is_current`),
  KEY `idx_cve_source_aws_security_bulletin_feed_run` (`id_cve_feed_run`),
  CONSTRAINT `fk_cve_source_aws_security_bulletin__cve_feed_run`
    FOREIGN KEY (`id_cve_feed_run`) REFERENCES `cve_feed_run` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `cve_feed_source`
  (`code`, `name`, `source_kind`, `priority`, `url_base`, `url_documentation`, `cadence_hours`, `parser_class`, `description`)
VALUES
  ('nvd', 'NVD JSON 2.0 API', 'api', 10, 'https://services.nvd.nist.gov/rest/json/cves/2.0', 'https://nvd.nist.gov/developers/vulnerabilities', 24, 'App\\Library\\Cve\\Source\\NvdSource', 'Primary CVE, CVSS and CPE feed. Supports full historical backfill and lastModified delta runs.'),
  ('osv', 'OSV.dev API', 'api', 20, 'https://api.osv.dev/v1/query', 'https://osv.dev/docs/', 24, 'App\\Library\\Cve\\Source\\OsvSource', 'Open Source Vulnerability feed used as a second opinion for version ranges.'),
  ('ghsa', 'GitHub Security Advisories', 'github_advisory', 30, 'https://api.github.com/graphql', 'https://docs.github.com/en/graphql/reference/objects#securityadvisory', 24, 'App\\Library\\Cve\\Source\\GhsaSource', 'GitHub advisory feed for OSS packages and repository advisories.'),
  ('cisa_kev', 'CISA Known Exploited Vulnerabilities', 'json', 15, 'https://www.cisa.gov/sites/default/files/feeds/known_exploited_vulnerabilities.json', 'https://www.cisa.gov/known-exploited-vulnerabilities-catalog', 24, 'App\\Library\\Cve\\Source\\CisaKevSource', 'Known exploited vulnerability overlay used to mark CVEs exploited in the wild.'),
  ('oracle_cpu', 'Oracle Critical Patch Updates', 'html', 40, 'https://www.oracle.com/security-alerts/', 'https://www.oracle.com/security-alerts/', 24, 'App\\Library\\Cve\\Source\\OracleCpuSource', 'Vendor advisory source for Oracle MySQL products and fixed versions.'),
  ('mariadb_security', 'MariaDB Security Releases', 'vendor_advisory', 45, 'https://mariadb.com/kb/en/security/', 'https://mariadb.com/kb/en/security/', 24, 'App\\Library\\Cve\\Source\\MariaDbSecuritySource', 'Vendor advisory source for MariaDB Server and MaxScale security releases.'),
  ('percona_advisory', 'Percona Security Advisories', 'vendor_advisory', 45, 'https://www.percona.com/security/advisories', 'https://www.percona.com/security/advisories', 24, 'App\\Library\\Cve\\Source\\PerconaAdvisorySource', 'Vendor advisory source for Percona Server and related MySQL-compatible products.'),
  ('component_ghsa', 'Component GitHub Advisories', 'github_advisory', 50, 'https://api.github.com/graphql', 'https://docs.github.com/en/graphql/reference/objects#securityadvisory', 24, 'App\\Library\\Cve\\Source\\ComponentGhsaSource', 'GitHub advisory source scoped to ProxySQL, MaxScale, HAProxy and Vitess repositories/packages.'),
  ('aws_security_bulletin', 'AWS Security Bulletins', 'html', 55, 'https://aws.amazon.com/security/security-bulletins/', 'https://aws.amazon.com/security/security-bulletins/', 24, 'App\\Library\\Cve\\Source\\AwsSecurityBulletinSource', 'AWS vendor advisory source for RDS and Aurora MySQL engine security bulletins.')
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `source_kind` = VALUES(`source_kind`),
  `priority` = VALUES(`priority`),
  `url_base` = VALUES(`url_base`),
  `url_documentation` = VALUES(`url_documentation`),
  `cadence_hours` = VALUES(`cadence_hours`),
  `parser_class` = VALUES(`parser_class`),
  `is_active` = 1,
  `description` = VALUES(`description`);

INSERT INTO `cve_feed_source_product`
  (`id_cve_feed_source`, `product_code`, `product_name`, `cpe_vendor`, `cpe_product`, `notes`)
SELECT s.`id`, p.`product_code`, p.`product_name`, p.`cpe_vendor`, p.`cpe_product`, p.`notes`
FROM `cve_feed_source` s
INNER JOIN (
  SELECT 'nvd' AS `source_code`, 'mysql' AS `product_code`, 'MySQL Server' AS `product_name`, 'oracle' AS `cpe_vendor`, 'mysql' AS `cpe_product`, 'Historical coverage must include MySQL 4.1 and newer.' AS `notes`
  UNION ALL SELECT 'nvd', 'mariadb', 'MariaDB Server', 'mariadb', 'mariadb', 'Use CPE when present; vendor advisories remain authoritative for fixed versions.'
  UNION ALL SELECT 'nvd', 'percona_server', 'Percona Server for MySQL', 'percona', 'percona_server', 'Percona build suffixes require product-aware version parsing.'
  UNION ALL SELECT 'nvd', 'xtrabackup', 'Percona XtraBackup', 'percona', 'xtrabackup', 'Component CVEs must not be merged into Percona Server.'
  UNION ALL SELECT 'nvd', 'pmm', 'Percona Monitoring and Management', 'percona', 'monitoring_and_management', 'PMM CVEs must stay separate from Percona Server.'
  UNION ALL SELECT 'nvd', 'mariadb_backup', 'MariaDB Backup', 'mariadb', 'mariadb_backup', 'Backup component CVEs must stay separate from MariaDB Server.'
  UNION ALL SELECT 'nvd', 'proxysql', 'ProxySQL', 'proxysql', 'proxysql', 'CPE coverage may be incomplete; component GHSA is the fallback.'
  UNION ALL SELECT 'nvd', 'haproxy', 'HAProxy', 'haproxy', 'haproxy', 'Use CPE when available; component GHSA/vendor data may be required.'
  UNION ALL SELECT 'nvd', 'maxscale', 'MariaDB MaxScale', 'mariadb', 'maxscale', 'MariaDB advisories are preferred for fixed versions.'
  UNION ALL SELECT 'nvd', 'vitess', 'Vitess', 'vitess', 'vitess', 'Use GHSA and OSV ranges as fallback.'
  UNION ALL SELECT 'nvd', 'tidb', 'TiDB', 'pingcap', 'tidb', 'Use OSV/GHSA and PingCAP advisories as fallback.'
  UNION ALL SELECT 'nvd', 'singlestore', 'SingleStoreDB', 'singlestore', 'singlestore', 'Version comments identify SingleStore separately from MySQL.'
  UNION ALL SELECT 'nvd', 'aurora_mysql', 'Amazon Aurora MySQL', 'amazon', 'aurora_mysql', 'Do not blindly inherit MySQL upstream CVEs; AWS backports must be considered.'
  UNION ALL SELECT 'nvd', 'rds_mysql', 'Amazon RDS for MySQL', 'amazon', 'rds_mysql', 'Do not blindly inherit MySQL upstream CVEs; AWS backports must be considered.'
  UNION ALL SELECT 'nvd', 'galera', 'Galera Cluster', 'codership', 'galera_cluster', 'Track Galera provider separately from the MySQL-compatible engine.'
  UNION ALL SELECT 'osv', 'mariadb', 'MariaDB Server', NULL, NULL, 'OSV range data can enrich vendor advisories when available.'
  UNION ALL SELECT 'osv', 'vitess', 'Vitess', NULL, NULL, 'OSV is useful for Go module and OSS package ranges.'
  UNION ALL SELECT 'osv', 'tidb', 'TiDB', NULL, NULL, 'OSV is useful for OSS package ranges.'
  UNION ALL SELECT 'ghsa', 'vitess', 'Vitess', NULL, NULL, 'GitHub Security Advisories for repository/package vulnerabilities.'
  UNION ALL SELECT 'ghsa', 'tidb', 'TiDB', NULL, NULL, 'GitHub Security Advisories for repository/package vulnerabilities.'
  UNION ALL SELECT 'cisa_kev', 'all', 'All CVE products', NULL, NULL, 'Overlay source: mark CVEs known exploited regardless of product origin.'
  UNION ALL SELECT 'oracle_cpu', 'mysql', 'MySQL Server', 'oracle', 'mysql', 'Authoritative Oracle CPU source for MySQL fixed versions.'
  UNION ALL SELECT 'oracle_cpu', 'mysql_cluster', 'MySQL Cluster', 'oracle', 'mysql_cluster', 'Oracle MySQL Risk Matrix Product column tag.'
  UNION ALL SELECT 'oracle_cpu', 'mysql_client', 'MySQL Client', 'oracle', 'mysql_client', 'Oracle MySQL Risk Matrix Product column tag.'
  UNION ALL SELECT 'oracle_cpu', 'mysql_connectors', 'MySQL Connectors', 'oracle', 'mysql_connectors', 'Oracle MySQL Risk Matrix Product column tag.'
  UNION ALL SELECT 'oracle_cpu', 'mysql_enterprise_backup', 'MySQL Enterprise Backup', 'oracle', 'mysql_enterprise_backup', 'Oracle MySQL Risk Matrix Product column tag.'
  UNION ALL SELECT 'oracle_cpu', 'mysql_enterprise_firewall', 'MySQL Enterprise Firewall', 'oracle', 'mysql_enterprise_firewall', 'Oracle MySQL Risk Matrix Product column tag.'
  UNION ALL SELECT 'oracle_cpu', 'mysql_enterprise_monitor', 'MySQL Enterprise Monitor', 'oracle', 'mysql_enterprise_monitor', 'Oracle MySQL Risk Matrix Product column tag.'
  UNION ALL SELECT 'oracle_cpu', 'mysql_installer', 'MySQL Installer', 'oracle', 'mysql_installer', 'Oracle MySQL Risk Matrix Product column tag.'
  UNION ALL SELECT 'oracle_cpu', 'mysql_shell', 'MySQL Shell', 'oracle', 'mysql_shell', 'Oracle MySQL Risk Matrix Product column tag.'
  UNION ALL SELECT 'oracle_cpu', 'mysql_shell_vscode', 'MySQL Shell for VS Code', 'oracle', 'mysql_shell_vscode', 'Oracle MySQL Risk Matrix Product column tag.'
  UNION ALL SELECT 'oracle_cpu', 'mysql_workbench', 'MySQL Workbench', 'oracle', 'mysql_workbench', 'Oracle MySQL Risk Matrix Product column tag.'
  UNION ALL SELECT 'oracle_cpu', 'enterprise_manager_mysql', 'Enterprise Manager for MySQL Database', 'oracle', 'enterprise_manager_mysql', 'Oracle MySQL Risk Matrix Product column tag.'
  UNION ALL SELECT 'mariadb_security', 'mariadb', 'MariaDB Server', 'mariadb', 'mariadb', 'Authoritative MariaDB security source.'
  UNION ALL SELECT 'mariadb_security', 'maxscale', 'MariaDB MaxScale', 'mariadb', 'maxscale', 'Authoritative MaxScale security source.'
  UNION ALL SELECT 'percona_advisory', 'percona_server', 'Percona Server for MySQL', 'percona', 'percona_server', 'Authoritative Percona advisory source.'
  UNION ALL SELECT 'percona_advisory', 'xtrabackup', 'Percona XtraBackup', 'percona', 'xtrabackup', 'Authoritative Percona advisory source for XtraBackup.'
  UNION ALL SELECT 'percona_advisory', 'pmm', 'Percona Monitoring and Management', 'percona', 'monitoring_and_management', 'Authoritative Percona advisory source for PMM.'
  UNION ALL SELECT 'component_ghsa', 'proxysql', 'ProxySQL', NULL, NULL, 'Component-scoped GHSA feed.'
  UNION ALL SELECT 'component_ghsa', 'maxscale', 'MariaDB MaxScale', NULL, NULL, 'Component-scoped GHSA feed.'
  UNION ALL SELECT 'component_ghsa', 'haproxy', 'HAProxy', NULL, NULL, 'Component-scoped GHSA feed.'
  UNION ALL SELECT 'component_ghsa', 'vitess', 'Vitess', NULL, NULL, 'Component-scoped GHSA feed.'
  UNION ALL SELECT 'aws_security_bulletin', 'aurora_mysql', 'Amazon Aurora MySQL', 'amazon', 'aurora_mysql', 'AWS source is authoritative for Aurora engine fixed versions.'
  UNION ALL SELECT 'aws_security_bulletin', 'rds_mysql', 'Amazon RDS for MySQL', 'amazon', 'rds_mysql', 'AWS source is authoritative for RDS engine fixed versions.'
) p ON p.`source_code` = s.`code`
ON DUPLICATE KEY UPDATE
  `product_name` = VALUES(`product_name`),
  `cpe_vendor` = VALUES(`cpe_vendor`),
  `cpe_product` = VALUES(`cpe_product`),
  `notes` = VALUES(`notes`);
