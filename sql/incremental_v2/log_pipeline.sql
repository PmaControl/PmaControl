CREATE TABLE IF NOT EXISTS `ts_log_event` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL,
  `log_path` varchar(255) NOT NULL,
  `event_time` datetime NOT NULL,
  `severity` enum('ERROR','WARN','INFO') NOT NULL DEFAULT 'INFO',
  `event_hash` char(40) NOT NULL,
  `raw_line` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_event` (`id_mysql_server`,`event_hash`),
  KEY `idx_server_time` (`id_mysql_server`,`event_time`),
  KEY `idx_severity_time` (`severity`,`event_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `ts_log_event_hourly` (
  `bucket_time` datetime NOT NULL,
  `id_mysql_server` int(11) NOT NULL,
  `severity` enum('ERROR','WARN','INFO') NOT NULL,
  `event_count` int(11) NOT NULL DEFAULT 0,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`bucket_time`,`id_mysql_server`,`severity`),
  KEY `idx_server_bucket` (`id_mysql_server`,`bucket_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
