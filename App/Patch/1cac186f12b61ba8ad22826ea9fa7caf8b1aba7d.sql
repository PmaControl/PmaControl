DROP TABLE IF EXISTS `foreign_key_real`;

CREATE TABLE `foreign_key_real` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL DEFAULT 0,
  `constraint_schema` varchar(64) NOT NULL DEFAULT '',
  `constraint_table` varchar(64) NOT NULL DEFAULT '',
  `constraint_column` varchar(64) NOT NULL DEFAULT '',
  `id_mysql_server__link` int(11) NOT NULL,
  `referenced_schema` varchar(64) NOT NULL DEFAULT '',
  `referenced_table` varchar(64) NOT NULL DEFAULT '',
  `referenced_column` varchar(64) NOT NULL DEFAULT '',
  `constraint_name` varchar(255) NOT NULL,
  `date_inserted` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_mysql_server` (`id_mysql_server`,`constraint_schema`,`referenced_schema`),
  KEY `id_mysql_server_2` (`id_mysql_server`,`constraint_schema`,`constraint_table`,`constraint_column`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `foreign_key_remove_prefix`;

CREATE TABLE `foreign_key_remove_prefix` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL,
  `database_name` varchar(255) NOT NULL,
  `prefix` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_mysql_server` (`id_mysql_server`),
  CONSTRAINT `foreign_key_remove_prefix_ibfk_1` FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_mysql_server_2` (`id_mysql_server`,`constraint_schema`,`constraint_table`,`constraint_column`,`referenced_schema`,`referenced_table`,`referenced_column`),
  UNIQUE KEY `id_mysql_server_3` (`id_mysql_server`,`constraint_schema`,`constraint_table`,`constraint_column`),
  UNIQUE KEY `constraint_column` (`constraint_column`,`constraint_table`,`constraint_schema`,`id_mysql_server`),
  KEY `id_mysql_server` (`id_mysql_server`),
  KEY `constraint_schema` (`constraint_schema`,`constraint_table`,`constraint_column`),
  KEY `referenced_schema` (`referenced_schema`,`referenced_table`,`referenced_column`),
  CONSTRAINT `id_mysql_server_ibfk_1` FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `listener`;

CREATE TABLE `listener` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_ts_file` int(11) NOT NULL,
  `date_previous_execution` datetime NOT NULL,
  `execution_time` double NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_ts_file` (`id_ts_file`),
  CONSTRAINT `listener_ibfk_1` FOREIGN KEY (`id_ts_file`) REFERENCES `ts_file` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT IGNORE INTO `listener` (`id_ts_file`,`date_previous_execution`,`execution_time`) SELECT id, now(),0 FROM `ts_file`;

DROP TRIGGER IF EXISTS after_ts_file_insert;

DELIMITER $$

CREATE TRIGGER after_ts_file_insert
AFTER INSERT ON ts_file
FOR EACH ROW
BEGIN
  INSERT INTO listener (id_ts_file, date_previous_execution, execution_time)
  VALUES (NEW.id, NOW(), 0);
END$$

DELIMITER ;

DROP TRIGGER IF EXISTS after_ts_file_delete;

DELIMITER $$

CREATE TRIGGER after_ts_file_delete
AFTER DELETE ON ts_file
FOR EACH ROW
BEGIN
  DELETE FROM listener WHERE id_ts_file = OLD.id;
END$$

DELIMITER ;

INSERT IGNORE INTO `ts_file` (`id`,`file_name`) VALUES (9, 'list_db');

DROP TABLE IF EXISTS `mysql_database`;

CREATE TABLE `mysql_database` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
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
  UNIQUE KEY `name` (`name`,`id_mysql_server`),
  KEY `id_mysql_server` (`id_mysql_server`),
  CONSTRAINT `mysql_database_ibfk_1` FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci WITH SYSTEM VERSIONING;

--DROP TABLE IF EXISTS `version`;

CREATE TABLE IF NOT EXISTS `version` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `version` varchar(32) NOT NULL,
  `build` char(40) NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
