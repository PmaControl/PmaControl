CREATE TABLE IF NOT EXISTS `user_persistent_auth_session` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_user_main` int(11) NOT NULL,
  `selector` char(32) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `user_agent_hash` char(64) DEFAULT NULL,
  `ip_hash` char(64) DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `date_last_used` datetime DEFAULT NULL,
  `date_expires` datetime NOT NULL,
  `date_absolute_expires` datetime NOT NULL,
  `date_revoked` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_persistent_auth_selector` (`selector`),
  KEY `idx_user_persistent_auth_user` (`id_user_main`),
  KEY `idx_user_persistent_auth_expires` (`date_expires`),
  KEY `idx_user_persistent_auth_absolute_expires` (`date_absolute_expires`),
  KEY `idx_user_persistent_auth_active` (`id_user_main`,`date_revoked`,`date_expires`),
  CONSTRAINT `user_persistent_auth_session_ibfk_1`
    FOREIGN KEY (`id_user_main`) REFERENCES `user_main` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
