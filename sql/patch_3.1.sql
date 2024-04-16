CREATE TABLE `mysql_database` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL,
  `schema_name` varchar(64) NOT NULL,
  `tables` int(11) NOT NULL DEFAULT 0,
  `rows` bigint(20) NOT NULL DEFAULT 0,
  `data_length` bigint(20) NOT NULL DEFAULT 0,
  `data_free` bigint(20) NOT NULL DEFAULT 0,
  `index_length` bigint(20) NOT NULL DEFAULT 0,
  `collation_name` varchar(64) NOT NULL,
  `character_set_name` varchar(32) NOT NULL,
  `binlog_do_db` int(11) NOT NULL DEFAULT 0,
  `binlog_ignore_db` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `schema_name` (`schema_name`,`id_mysql_server`),
  KEY `id_mysql_server` (`id_mysql_server`),
  CONSTRAINT `mysql_database_ibfk_1` FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=448 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci WITH SYSTEM VERSIONING;


CREATE TABLE `mysql_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_database` int(11) NOT NULL,
  `table_name` int(11) NOT NULL,
  `schema` text NOT NULL,
  `date_inserted` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_mysql_database` (`id_mysql_database`,`table_name`),
  CONSTRAINT `mysql_table_ibfk_1` FOREIGN KEY (`id_mysql_database`) REFERENCES `mysql_database` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
