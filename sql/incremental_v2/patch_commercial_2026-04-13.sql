-- ============================================================
-- PmaControl patch — commercial branch — 2026-04-13
-- Covers: GeoIP tables, Group Replication menu, ts_file table
-- ============================================================

-- ── 1. GeoIP country table ──────────────────────────────────

CREATE TABLE IF NOT EXISTS `data_geoip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `network_start` varbinary(16) NOT NULL,
  `network_end` varbinary(16) NOT NULL,
  `country_iso` char(2) NOT NULL DEFAULT '',
  `country_name` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  INDEX `idx_network_start` (`network_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── 2. GeoIP city table ─────────────────────────────────────

CREATE TABLE IF NOT EXISTS `data_geoip_city` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `network_start` varbinary(16) NOT NULL,
  `network_end` varbinary(16) NOT NULL,
  `country_iso` char(2) NOT NULL DEFAULT '',
  `country_name` varchar(100) NOT NULL DEFAULT '',
  `region_iso` varchar(10) NOT NULL DEFAULT '',
  `region_name` varchar(100) NOT NULL DEFAULT '',
  `city` varchar(100) NOT NULL DEFAULT '',
  `postal` varchar(20) NOT NULL DEFAULT '',
  `latitude` decimal(8,4) DEFAULT NULL,
  `longitude` decimal(8,4) DEFAULT NULL,
  `time_zone` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  INDEX `idx_network_start` (`network_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── 3. Group Replication menu entry ─────────────────────────
-- Insert only if not already present

-- Make room in the nested set: shift bg/bd >= 75 by +2
UPDATE `menu` SET `bd` = `bd` + 2 WHERE `bd` >= 75
  AND NOT EXISTS (SELECT 1 FROM (SELECT id FROM `menu` WHERE title = 'Group Replication' AND class = 'InnoDBCluster') t);
UPDATE `menu` SET `bg` = `bg` + 2 WHERE `bg` >= 75
  AND NOT EXISTS (SELECT 1 FROM (SELECT id FROM `menu` WHERE title = 'Group Replication' AND class = 'InnoDBCluster') t);

-- Insert the menu entry
INSERT INTO `menu` (`title`, `bg`, `bd`, `parent_id`, `icon`, `class`, `method`, `active`)
SELECT 'Group Replication', 75, 76,
       (SELECT `id` FROM `menu` WHERE `title` = 'Architecture' LIMIT 1),
       '<i class="fa fa-database"></i>', 'InnoDBCluster', 'index', 1
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `menu` WHERE `title` = 'Group Replication' AND `class` = 'InnoDBCluster'
);

-- ── 4. ts_file table (if not exists) ────────────────────────

CREATE TABLE IF NOT EXISTS `ts_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── 5. binlog_analysis table ─────────────────────────────────

CREATE TABLE IF NOT EXISTS `binlog_analysis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL COMMENT 'slave server id',
  `id_mysql_server_master` int(11) NOT NULL COMMENT 'master server id',
  `connection_name` varchar(100) NOT NULL DEFAULT '' COMMENT 'replication channel name',
  `status` enum('pending','running','done','error') NOT NULL DEFAULT 'pending',
  `progress` text DEFAULT NULL COMMENT 'JSON array of step updates',
  `error_message` text DEFAULT NULL,
  `time_start` datetime NOT NULL COMMENT 'start of selected time range',
  `time_end` datetime NOT NULL COMMENT 'end of selected time range',
  `binlog_files` text DEFAULT NULL COMMENT 'JSON list of binlog files analyzed',
  `mysql_version` varchar(50) DEFAULT NULL COMMENT 'master MySQL/MariaDB version',
  `server_id` int(10) unsigned DEFAULT NULL COMMENT 'master server_id from binlog',
  `total_size_bytes` bigint(20) unsigned DEFAULT 0,
  `duration_seconds` int(10) unsigned DEFAULT 0 COMMENT 'time range covered in seconds',
  `total_transactions` int(10) unsigned DEFAULT 0,
  `total_inserts` int(10) unsigned DEFAULT 0,
  `total_updates` int(10) unsigned DEFAULT 0,
  `total_deletes` int(10) unsigned DEFAULT 0,
  `total_ddl` int(10) unsigned DEFAULT 0,
  `ddl_details` text DEFAULT NULL COMMENT 'JSON [{type, database, table, statement}]',
  `max_txn_size_bytes` int(10) unsigned DEFAULT 0,
  `large_txn_100k` int(10) unsigned DEFAULT 0 COMMENT 'transactions > 100KB',
  `large_txn_500k` int(10) unsigned DEFAULT 0 COMMENT 'transactions > 500KB',
  `peak_txn_per_sec` int(10) unsigned DEFAULT 0,
  `min_txn_per_sec` int(10) unsigned DEFAULT 0,
  `avg_txn_per_sec` decimal(10,1) DEFAULT 0.0,
  `sequential_pct` decimal(5,1) DEFAULT 0.0 COMMENT 'percent non-parallelizable transactions',
  `max_parallelism` int(10) unsigned DEFAULT 0,
  `parallelism_distribution` text DEFAULT NULL COMMENT 'JSON {group_size: count}',
  `parallelism_per_second` text DEFAULT NULL COMMENT 'JSON [{ts, txn_count}] for parallelism graph',
  `top_tables` text DEFAULT NULL COMMENT 'JSON [{table, inserts, updates, deletes}]',
  `databases_count` int(10) unsigned DEFAULT 0,
  `volume_per_second` text DEFAULT NULL COMMENT 'JSON [{ts, bytes, txn}]',
  `recommendations` text DEFAULT NULL COMMENT 'JSON array of recommendation strings',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_slave` (`id_mysql_server`),
  KEY `idx_master` (`id_mysql_server_master`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── Done ─────────────────────────────────────────────────────
