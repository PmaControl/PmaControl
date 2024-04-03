ALTER TABLE `ts_max_date` ADD `last_date_listener` DATETIME NULL DEFAULT CURRENT_TIMESTAMP AFTER `id_ts_file`;


ALTER TABLE ts_max_date ADD UNIQUE KEY `id_mysql_server_3` (`id_mysql_server`,`id_ts_file`,`date`,`last_date_listener`);


DROP TABLE IF EXISTS `mysql_database`;


CREATE TABLE `mysql_database` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL,
  `schema_name` varchar(64) NOT NULL,
  `tables` int(11) NOT NULL,
  `rows` bigint(20) NOT NULL,
  `data_length` bigint(20) NOT NULL,
  `data_free` bigint(20) NOT NULL,
  `index_length` bigint(20) NOT NULL,
  `collation_name` varchar(64) NOT NULL,
  `character_set_name` varchar(32) NOT NULL,
  `binlog_do_db` int(11) NOT NULL,
  `binlog_ignore_db` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `schema_name` (`schema_name`,`id_mysql_server`),
  KEY `id_mysql_server` (`id_mysql_server`),
  CONSTRAINT `mysql_database_ibfk_1` FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci WITH SYSTEM VERSIONING;




--ALTER TABLE `foreign_key_virtual` ADD `date_inserted` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `is_automatic`;

DROP TABLE IF EXISTS `foreign_key_virtual`;

CREATE TABLE `foreign_key_virtual` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL DEFAULT 0,
  `constraint_schema` varchar(64) NOT NULL DEFAULT '',
  `constraint_table` varchar(64) NOT NULL DEFAULT '',
  `constraint_column` varchar(64) NOT NULL DEFAULT '',
  `id_mysql_server__link` int(11) NOT NULL,
  `referenced_schema` varchar(64) NOT NULL DEFAULT '',
  `referenced_table` varchar(64) NOT NULL DEFAULT '',
  `referenced_column` varchar(64) NOT NULL DEFAULT '',
  `is_automatic` int(11) NOT NULL DEFAULT 1,
  `date_inserted` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_mysql_server_2` (`id_mysql_server`,`constraint_schema`,`constraint_table`,`constraint_column`,`referenced_schema`,`referenced_table`,`referenced_column`),
  UNIQUE KEY `id_mysql_server_3` (`id_mysql_server`,`constraint_schema`,`constraint_table`,`constraint_column`),
  UNIQUE KEY `constraint_column` (`constraint_column`,`constraint_table`,`constraint_schema`,`id_mysql_server`),
  KEY `id_mysql_server` (`id_mysql_server`),
  KEY `constraint_schema` (`constraint_schema`,`constraint_table`,`constraint_column`),
  KEY `referenced_schema` (`referenced_schema`,`referenced_table`,`referenced_column`),
  CONSTRAINT `id_mysql_server_ibfk_1` FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `foreign_key_proposal`;


CREATE TABLE `foreign_key_proposal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL DEFAULT 0,
  `constraint_schema` varchar(64) NOT NULL DEFAULT '',
  `constraint_table` varchar(64) NOT NULL DEFAULT '',
  `constraint_column` varchar(64) NOT NULL DEFAULT '',
  `id_mysql_server__link` int(11) NOT NULL,
  `referenced_schema` varchar(64) NOT NULL DEFAULT '',
  `referenced_table` varchar(64) NOT NULL DEFAULT '',
  `referenced_column` varchar(64) NOT NULL DEFAULT '',
  `is_automatic` int(11) NOT NULL DEFAULT 1,
  `date_inserted` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_mysql_server_35` (`id_mysql_server`,`constraint_schema`,`constraint_table`,`constraint_column`),
  CONSTRAINT `id_mysql_server_ibfk_13` FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



DROP TABLE IF EXISTS `foreign_key_blacklist`;


CREATE TABLE `foreign_key_blacklist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL DEFAULT 0,
  `constraint_schema` varchar(64) NOT NULL DEFAULT '',
  `constraint_table` varchar(64) NOT NULL DEFAULT '',
  `constraint_column` varchar(64) NOT NULL DEFAULT '',
  `id_mysql_server__link` int(11) NOT NULL,
  `referenced_schema` varchar(64) NOT NULL DEFAULT '',
  `referenced_table` varchar(64) NOT NULL DEFAULT '',
  `referenced_column` varchar(64) NOT NULL DEFAULT '',
  `is_automatic` int(11) NOT NULL DEFAULT 1,
  `date_inserted` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_mysql_server_36` (`id_mysql_server`,`constraint_schema`,`constraint_table`,`constraint_column`),
  CONSTRAINT `id_mysql_server_ibfk_16` FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;