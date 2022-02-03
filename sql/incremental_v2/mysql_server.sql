ALTER TABLE mysql_server MODIFY ip varchar(64) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL;


ALTER TABLE mysql_server ADD COLUMN `id_mysql_type` int(11) NOT NULL DEFAULT 1 AFTER id_environment;

CREATE TABLE `mysql_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(64) NOT NULL,
  `is_proxy` tinyint(4) NOT NULL,
  `is_admin` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT;



update mysql_server set id_mysql_type= 1;


ALTER TABLE `mysql_server` ADD  FOREIGN KEY (`id_mysql_type`) REFERENCES `mysql_type`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;


