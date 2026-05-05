CREATE TABLE IF NOT EXISTS `webservice_auth_failure` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `remote_addr` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `failure_count` int(11) NOT NULL DEFAULT 0,
  `window_started_at` datetime NOT NULL,
  `last_failed_at` datetime NOT NULL,
  `blocked_until` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_webservice_auth_failure_user_remote` (`user`, `remote_addr`),
  KEY `idx_webservice_auth_failure_blocked_until` (`blocked_until`),
  KEY `idx_webservice_auth_failure_last_failed_at` (`last_failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
