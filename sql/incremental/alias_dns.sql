DROP TABLE IF EXISTS `alias_dns`;
CREATE TABLE `alias_dns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL,
  `dns` varchar(200) NOT NULL,
  `port` int(11) NOT NULL,
  `destination` varchar(15) NOT NULL,
  `is_dynamic` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dns` (`dns`,`port`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT WITH SYSTEM VERSIONING;
