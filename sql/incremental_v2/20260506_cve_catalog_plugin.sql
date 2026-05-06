CREATE TABLE IF NOT EXISTS `cve_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_code` varchar(64) NOT NULL,
  `product_name` varchar(128) NOT NULL,
  `product_family` enum('mysql_like','proxy','router','cloud','component') NOT NULL DEFAULT 'mysql_like',
  `icon_class` varchar(128) NOT NULL DEFAULT 'fa fa-shield',
  `color` char(7) NOT NULL DEFAULT '#334155',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cve_product_code` (`product_code`),
  KEY `idx_cve_product_family` (`product_family`,`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `cve_catalog` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cve_id` varchar(32) NOT NULL,
  `title` varchar(512) DEFAULT NULL,
  `summary` text NULL,
  `severity` enum('none','low','medium','high','critical','unknown') NOT NULL DEFAULT 'unknown',
  `cvss_v2_score` decimal(3,1) DEFAULT NULL,
  `cvss_v3_score` decimal(3,1) DEFAULT NULL,
  `cvss_v4_score` decimal(3,1) DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `last_modified_at` datetime DEFAULT NULL,
  `known_exploited` tinyint(1) NOT NULL DEFAULT 0,
  `known_ransomware_campaign_use` varchar(32) DEFAULT NULL,
  `cwe_json` longtext NULL,
  `references_json` longtext NULL,
  `source_codes_json` longtext NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cve_catalog_cve_id` (`cve_id`),
  KEY `idx_cve_catalog_severity` (`severity`,`published_at`),
  KEY `idx_cve_catalog_known_exploited` (`known_exploited`,`severity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `cve_product_affected_version` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_cve_catalog` bigint(20) unsigned NOT NULL,
  `id_cve_product` int(11) NOT NULL,
  `product_code` varchar(64) NOT NULL,
  `version_text` text NULL,
  `version_start_including` varchar(64) DEFAULT NULL,
  `version_start_excluding` varchar(64) DEFAULT NULL,
  `version_end_including` varchar(64) DEFAULT NULL,
  `version_end_excluding` varchar(64) DEFAULT NULL,
  `fixed_version` text NULL,
  `source_code` varchar(64) NOT NULL,
  `source_url` varchar(1024) DEFAULT NULL,
  `match_method` enum('cpe','vendor_advisory','ghsa','osv','aws','oracle_cpu','heuristic') NOT NULL DEFAULT 'heuristic',
  `match_confidence` enum('high','medium','low') NOT NULL DEFAULT 'medium',
  `raw_match_json` longtext NULL,
  `match_hash` char(64) NOT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 1,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cve_product_affected_version` (`id_cve_catalog`,`id_cve_product`,`source_code`,`match_hash`),
  KEY `idx_cve_product_affected_product` (`product_code`,`is_current`),
  KEY `idx_cve_product_affected_cve` (`id_cve_catalog`,`is_current`),
  CONSTRAINT `fk_cve_product_affected_version__catalog`
    FOREIGN KEY (`id_cve_catalog`) REFERENCES `cve_catalog` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cve_product_affected_version__product`
    FOREIGN KEY (`id_cve_product`) REFERENCES `cve_product` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `cve_server_cache` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL,
  `id_cve_catalog` bigint(20) unsigned NOT NULL,
  `id_cve_product` int(11) NOT NULL,
  `product_code` varchar(64) NOT NULL,
  `server_version` varchar(128) DEFAULT NULL,
  `server_version_comment` text NULL,
  `match_method` enum('exact_version','version_range','manual_rule','heuristic') NOT NULL DEFAULT 'heuristic',
  `match_confidence` enum('high','medium','low') NOT NULL DEFAULT 'medium',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `date_calculated` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cve_server_cache` (`id_mysql_server`,`id_cve_catalog`,`id_cve_product`),
  KEY `idx_cve_server_cache_mysql_server` (`id_mysql_server`,`is_active`),
  KEY `idx_cve_server_cache_cve` (`id_cve_catalog`,`is_active`),
  KEY `idx_cve_server_cache_product` (`product_code`,`is_active`),
  CONSTRAINT `fk_cve_server_cache__catalog`
    FOREIGN KEY (`id_cve_catalog`) REFERENCES `cve_catalog` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cve_server_cache__product`
    FOREIGN KEY (`id_cve_product`) REFERENCES `cve_product` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `cve_product`
  (`product_code`, `product_name`, `product_family`, `icon_class`, `color`)
VALUES
  ('mysql', 'MySQL Server', 'mysql_like', 'fa fa-database', '#e97b00'),
  ('mysql_cluster', 'MySQL Cluster', 'component', 'fa fa-object-group', '#d97706'),
  ('mysql_client', 'MySQL Client', 'component', 'fa fa-terminal', '#b45309'),
  ('mysql_connectors', 'MySQL Connectors', 'component', 'fa fa-plug', '#92400e'),
  ('mysql_enterprise_backup', 'MySQL Enterprise Backup', 'component', 'fa fa-archive', '#a16207'),
  ('mysql_enterprise_firewall', 'MySQL Enterprise Firewall', 'component', 'fa fa-shield', '#b91c1c'),
  ('mysql_enterprise_monitor', 'MySQL Enterprise Monitor', 'component', 'fa fa-line-chart', '#1d4ed8'),
  ('mysql_installer', 'MySQL Installer', 'component', 'fa fa-download', '#0f766e'),
  ('mysql_shell', 'MySQL Shell', 'component', 'fa fa-terminal', '#0369a1'),
  ('mysql_shell_vscode', 'MySQL Shell for VS Code', 'component', 'fa fa-code', '#2563eb'),
  ('mysql_workbench', 'MySQL Workbench', 'component', 'fa fa-wrench', '#7c2d12'),
  ('enterprise_manager_mysql', 'Enterprise Manager for MySQL Database', 'component', 'fa fa-desktop', '#4338ca'),
  ('mariadb', 'MariaDB Server', 'mysql_like', 'fa fa-database', '#003545'),
  ('percona', 'Percona Server', 'mysql_like', 'fa fa-database', '#c3281c'),
  ('xtrabackup', 'Percona XtraBackup', 'component', 'fa fa-archive', '#6b21a8'),
  ('pmm', 'Percona Monitoring and Management', 'component', 'fa fa-line-chart', '#7f1d1d'),
  ('mariadb_backup', 'MariaDB Backup', 'component', 'fa fa-archive', '#0f766e'),
  ('proxysql', 'ProxySQL', 'proxy', 'fa fa-random', '#1f2937'),
  ('maxscale', 'MariaDB MaxScale', 'proxy', 'fa fa-exchange', '#00a7c8'),
  ('haproxy', 'HAProxy', 'proxy', 'fa fa-share-alt', '#2563eb'),
  ('vitess', 'Vitess', 'mysql_like', 'fa fa-sitemap', '#7c3aed'),
  ('tidb', 'TiDB', 'mysql_like', 'fa fa-database', '#0f766e'),
  ('singlestore', 'SingleStoreDB', 'mysql_like', 'fa fa-bolt', '#111827'),
  ('aurora_mysql', 'Amazon Aurora MySQL', 'cloud', 'fa fa-cloud', '#ff9900'),
  ('rds_mysql', 'Amazon RDS for MySQL', 'cloud', 'fa fa-cloud', '#527fff'),
  ('galera', 'Galera Cluster', 'component', 'fa fa-object-group', '#16a34a')
ON DUPLICATE KEY UPDATE
  `product_name` = VALUES(`product_name`),
  `product_family` = VALUES(`product_family`),
  `icon_class` = VALUES(`icon_class`),
  `color` = VALUES(`color`),
  `is_active` = 1;

INSERT INTO `plugin_main`
  (`nom`, `description`, `auteur`, `image`, `fichier`, `date_installation`, `md5_zip`, `version`, `est_actif`, `type_licence`)
VALUES
  ('cve-inventory', 'Internal CVE inventory plugin for MySQL-like products and proxy components.', 'PmaControl', '{LINK}App/Webroot/image/icon/pmacontrol_b.svg', '', NOW(), '', 'v1.0.0', 1, 'GPL-3.0')
ON DUPLICATE KEY UPDATE
  `description` = VALUES(`description`),
  `auteur` = VALUES(`auteur`),
  `image` = VALUES(`image`),
  `est_actif` = 1,
  `type_licence` = VALUES(`type_licence`);

INSERT INTO `plugin_menu` (`id_plugin_main`, `url`)
SELECT pm.`id`, '{LINK}cve/index'
FROM `plugin_main` pm
WHERE pm.`nom` = 'cve-inventory'
  AND pm.`version` = 'v1.0.0'
  AND NOT EXISTS (
      SELECT 1
      FROM `plugin_menu` existing
      WHERE existing.`id_plugin_main` = pm.`id`
        AND existing.`url` = '{LINK}cve/index'
  );

SET @cve_plugin_menu_exists := (
    SELECT COUNT(*)
    FROM `menu`
    WHERE `group_id` = 1
      AND `title` = 'CVE Inventory'
      AND `class` = 'cve'
      AND `method` = 'index'
);

SET @cve_plugin_parent_id := (
    SELECT child.`id`
    FROM `menu` child
    INNER JOIN `menu` root ON root.`id` = child.`parent_id`
    WHERE child.`group_id` = 1
      AND child.`title` = 'Plugins'
      AND child.`url` = ''
      AND child.`active` = 1
      AND root.`parent_id` IS NULL
    ORDER BY child.`bg`
    LIMIT 1
);

SET @cve_plugin_insert_at := (
    SELECT `bd`
    FROM `menu`
    WHERE `id` = @cve_plugin_parent_id
);

UPDATE `menu`
SET `bd` = `bd` + 2
WHERE @cve_plugin_menu_exists = 0
  AND @cve_plugin_parent_id IS NOT NULL
  AND `group_id` = 1
  AND `bd` >= @cve_plugin_insert_at;

UPDATE `menu`
SET `bg` = `bg` + 2
WHERE @cve_plugin_menu_exists = 0
  AND @cve_plugin_parent_id IS NOT NULL
  AND `group_id` = 1
  AND `bg` > @cve_plugin_insert_at;

INSERT INTO `menu` (`title`, `bg`, `bd`, `parent_id`, `group_id`, `icon`, `url`, `class`, `method`, `active`)
SELECT 'CVE Inventory',
       @cve_plugin_insert_at,
       @cve_plugin_insert_at + 1,
       @cve_plugin_parent_id,
       1,
       '<i class="fa fa-shield" aria-hidden="true"></i>',
       '{LINK}cve/index',
       'cve',
       'index',
       1
FROM DUAL
WHERE @cve_plugin_menu_exists = 0
  AND @cve_plugin_parent_id IS NOT NULL
  AND @cve_plugin_insert_at IS NOT NULL;
