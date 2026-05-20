CREATE TABLE `vip_server` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL COMMENT 'VIP (mysql_server.id)',
  `dns` varchar(255) NOT NULL COMMENT 'copie mysql_server.ip (host VIP)',
  `ip` varchar(45) DEFAULT NULL COMMENT 'IP résolue (IPv4/IPv6)',
  `id_mysql_server__actual` int(11) DEFAULT NULL,
  `id_mysql_server__previous` int(11) DEFAULT NULL,
  `date__actual` datetime DEFAULT NULL COMMENT 'date de détection du changement actual',
  `date__previous` datetime DEFAULT NULL COMMENT 'date de détection du changement previous',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_infra_vip_route__id_mysql_server` (`id_mysql_server`),
  KEY `idx_infra_vip_route__dns` (`dns`),
  KEY `idx_infra_vip_route__actual` (`id_mysql_server__actual`),
  KEY `idx_infra_vip_route__previous` (`id_mysql_server__previous`),
  CONSTRAINT `fk_infra_vip_route__vip`
    FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_infra_vip_route__actual`
    FOREIGN KEY (`id_mysql_server__actual`) REFERENCES `mysql_server` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_infra_vip_route__previous`
    FOREIGN KEY (`id_mysql_server__previous`) REFERENCES `mysql_server` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci WITH SYSTEM VERSIONING
 PARTITION BY SYSTEM_TIME
PARTITIONS 2;
