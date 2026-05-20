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
