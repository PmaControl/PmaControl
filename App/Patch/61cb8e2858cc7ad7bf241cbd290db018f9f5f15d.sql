CREATE TABLE IF NOT EXISTS `log_ingestion_event` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_mysql_server` INT UNSIGNED NOT NULL,
    `source_name` VARCHAR(64) NOT NULL,
    `event_level` ENUM('info', 'warning', 'error', 'critical') NOT NULL DEFAULT 'info',
    `event_category` VARCHAR(32) NOT NULL DEFAULT 'other',
    `message` TEXT NOT NULL,
    `message_fingerprint` CHAR(40) NOT NULL,
    `duplicate_count` INT UNSIGNED NOT NULL DEFAULT 1,
    `event_date` DATETIME NOT NULL,
    `last_seen` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_log_ingestion_event` (`id_mysql_server`, `source_name`, `message_fingerprint`, `event_date`),
    KEY `idx_log_ingestion_event_server_date` (`id_mysql_server`, `event_date`),
    KEY `idx_log_ingestion_event_level` (`event_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `log_ingestion_metric_hourly` (
    `id_mysql_server` INT UNSIGNED NOT NULL,
    `bucket_hour` DATETIME NOT NULL,
    `total_events` INT UNSIGNED NOT NULL DEFAULT 0,
    `total_error` INT UNSIGNED NOT NULL DEFAULT 0,
    `total_warning` INT UNSIGNED NOT NULL DEFAULT 0,
    `total_critical` INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`id_mysql_server`, `bucket_hour`),
    KEY `idx_log_ingestion_metric_hourly_bucket` (`bucket_hour`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `log_ingestion_cursor` (
    `id_mysql_server` INT UNSIGNED NOT NULL,
    `source_name` VARCHAR(64) NOT NULL,
    `remote_path` VARCHAR(255) NOT NULL,
    `inode` BIGINT UNSIGNED DEFAULT NULL,
    `last_offset` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `last_sync` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_mysql_server`, `source_name`, `remote_path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
