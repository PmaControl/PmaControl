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

-- ── Done ─────────────────────────────────────────────────────
