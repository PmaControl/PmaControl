CREATE TABLE `mysqlrouter_server` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `display_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `is_ssl` int(11) NOT NULL DEFAULT 0,
  `hostname` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `port` int(11) NOT NULL,
  `login` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date_inserted` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci WITH SYSTEM VERSIONING;

CREATE TABLE `mysqlrouter_server__mysql_server` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysqlrouter_server` int(11) NOT NULL,
  `id_mysql_server` int(11) NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_mysqlrouter_server` (`id_mysqlrouter_server`,`id_mysql_server`),
  KEY `mysqlrouter_server__mysql_server_ibfk_2` (`id_mysql_server`),
  CONSTRAINT `mysqlrouter_server__mysql_server_ibfk_1` FOREIGN KEY (`id_mysqlrouter_server`) REFERENCES `mysqlrouter_server` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `daemon_main` (`id`, `name`, `date`, `pid`, `refresh_time`, `max_delay`, `class`, `method`, `params`, `debug`)
VALUES (36, 'Aspirateur MySQL Router', NOW(), 0, 5, 3, 'Worker', 'addToQueue', '5', 0);

INSERT IGNORE INTO `worker_queue` (`id`, `id_daemon_main`, `table`, `name`, `nb_worker`, `timeout`, `queue_number`, `worker_class`, `worker_method`, `max_execution_time`, `query`)
VALUES (5, 36, 'mysqlrouter_server', 'worker_mysqlrouter', 10, 3, 158849, 'Aspirateur', 'tryMysqlRouterConnection', 5, 'select group_concat(b.id_mysql_server) as name, a.`id` as `id` from mysqlrouter_server a INNER JOIN mysqlrouter_server__mysql_server b ON a.id=b.id_mysqlrouter_server INNER JOIN mysql_server c ON b.id_mysql_server = c.id INNER JOIN client d ON d.id = c.id_client WHERE d.is_monitored = 1 group by a.id;');

INSERT IGNORE INTO `ts_file` (`id`, `file_name`, `each`) VALUES
(3799, 'mysqlrouter_routes', 1),
(3800, 'mysqlrouter_metadata_config', 1),
(3801, 'mysqlrouter_metadata_status', 1),
(3802, 'mysqlrouter_server', 1);

INSERT IGNORE INTO `ts_variable` (`id`, `id_ts_file`, `name`, `type`, `from`, `radical`, `is_derived`, `is_dynamic`) VALUES
(6920, 3799, 'mysqlrouter_routes', 'JSON', 'mysqlrouter', 'general', 1, 1),
(6921, 3800, 'mysqlrouter_metadata_config', 'JSON', 'mysqlrouter', 'general', 1, 1),
(6922, 3801, 'mysqlrouter_metadata_status', 'JSON', 'mysqlrouter', 'general', 1, 1),
(6923, 3802, 'mysqlrouter_available', 'INT', 'mysqlrouter_server', 'general', 1, 1),
(6924, 3802, 'mysqlrouter_ping', 'DOUBLE', 'mysqlrouter_server', 'general', 1, 1),
(6925, 3802, 'mysqlrouter_error', 'TEXT', 'mysqlrouter_server', 'general', 1, 1);

UPDATE `daemon_main`
SET `params` = 'maxscale_*,proxysql_*,mysqlrouter_*'
WHERE `id` = 31;
