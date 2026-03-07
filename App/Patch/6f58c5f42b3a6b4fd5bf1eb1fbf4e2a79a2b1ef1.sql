CREATE TABLE IF NOT EXISTS `ssh_log_watch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL,
  `log_path` varchar(255) NOT NULL,
  `last_inode` varchar(32) NOT NULL DEFAULT '',
  `last_mtime` varchar(32) NOT NULL DEFAULT '',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `retention_days` int(11) NOT NULL DEFAULT 7,
  `date_last_collected` datetime DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_watch` (`id_mysql_server`,`log_path`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `ssh_log_event` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_ssh_log_watch` int(11) NOT NULL,
  `id_mysql_server` int(11) NOT NULL,
  `event_date` datetime NOT NULL,
  `bucket_minute` datetime NOT NULL,
  `level` enum('DEBUG','INFO','NOTICE','WARNING','ERROR','CRITICAL','ALERT','EMERGENCY') NOT NULL DEFAULT 'INFO',
  `message` varchar(4096) NOT NULL,
  `message_hash` char(40) NOT NULL,
  `count_seen` int(11) NOT NULL DEFAULT 1,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_event_smart_storage` (`id_ssh_log_watch`,`message_hash`,`bucket_minute`),
  KEY `idx_dashboard` (`id_mysql_server`,`event_date`,`level`),
  CONSTRAINT `ssh_log_event_ibfk_1` FOREIGN KEY (`id_ssh_log_watch`) REFERENCES `ssh_log_watch` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `ssh_log_watch` (`id_mysql_server`,`log_path`)
SELECT `id`, '/var/log/syslog' FROM `mysql_server`;
