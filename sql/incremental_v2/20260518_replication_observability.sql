-- Issue #1280 — replication observability primitives.
-- Adds GTID drift cache, per-server lag SLA, replication annotations and
-- time-series variables consumed by /slave/show.

SET @prev_versioning_alter := @@SESSION.system_versioning_alter_history;
SET SESSION system_versioning_alter_history = KEEP;

SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'mysql_server'
      AND column_name = 'errant_gtid_set'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE `mysql_server` ADD COLUMN `errant_gtid_set` TEXT NULL AFTER `is_acknowledged`',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'mysql_server'
      AND column_name = 'errant_gtid_sampled_at'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE `mysql_server` ADD COLUMN `errant_gtid_sampled_at` DATETIME NULL AFTER `errant_gtid_set`',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'mysql_server'
      AND column_name = 'errant_gtid_ignore'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE `mysql_server` ADD COLUMN `errant_gtid_ignore` TINYINT(1) NOT NULL DEFAULT 0 AFTER `errant_gtid_sampled_at`',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'mysql_server'
      AND column_name = 'replica_lag_sla_seconds'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE `mysql_server` ADD COLUMN `replica_lag_sla_seconds` INT NOT NULL DEFAULT 30 AFTER `errant_gtid_ignore`',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET SESSION system_versioning_alter_history = @prev_versioning_alter;

INSERT IGNORE INTO `ts_variable` (`id_ts_file`, `name`, `type`, `from`, `radical`, `is_derived`, `is_dynamic`)
VALUES
    (3756, 'gtid_executed', 'TEXT', 'slave', 'slave', 1, 1),
    (3756, 'rpl_semi_sync_master_avg_wait_time', 'INT', 'status', 'general', 1, 1),
    (3756, 'replication_applier_status_by_worker', 'JSON', 'slave', 'slave', 1, 1);

CREATE TABLE IF NOT EXISTS `replication_annotation` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `id_mysql_server` INT NOT NULL,
    `connection_name` VARCHAR(64) NOT NULL DEFAULT '',
    `annotation_type` ENUM('deploy','migration','tuning','incident','other') NOT NULL DEFAULT 'other',
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `annotation_time` DATETIME NOT NULL,
    `created_by` VARCHAR(64) NOT NULL DEFAULT '',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_server_time` (`id_mysql_server`, `annotation_time`),
    KEY `idx_connection_time` (`id_mysql_server`, `connection_name`, `annotation_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
