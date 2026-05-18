-- Issue: "reload from master" module — operator-driven replica restoration.
-- Adds the `is_out_of_service` flag on `mysql_server` (operator-flagged HS
-- gate, see App\Library\ReloadFromMaster) and the `progress_percent`
-- column on `job` so /Job/index can render a progress bar for long-running
-- reload jobs (physical via xtrabackup / mariadb-backup or logical via
-- mariadb-dump --master-data).
--
-- `mysql_server` is system-versioned (WITH SYSTEM VERSIONING) so the
-- ALTERs must run under `system_versioning_alter_history = KEEP` —
-- same pattern as 20260518_replication_observability.sql.

SET @prev_versioning_alter := @@SESSION.system_versioning_alter_history;
SET SESSION system_versioning_alter_history = KEEP;

SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'mysql_server'
      AND column_name = 'is_out_of_service'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE `mysql_server` ADD COLUMN `is_out_of_service` TINYINT(1) NOT NULL DEFAULT 0 COMMENT ''operator-flagged HS for reload from master'' AFTER `replica_lag_sla_seconds`',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET SESSION system_versioning_alter_history = @prev_versioning_alter;

-- `job` is NOT system-versioned, plain ALTER is fine.
SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'job'
      AND column_name = 'progress_percent'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE `job` ADD COLUMN `progress_percent` INT NULL DEFAULT NULL AFTER `load_status`',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
