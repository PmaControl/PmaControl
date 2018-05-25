ALTER TABLE mysql_replication_stats MODIFY column `file` varchar(200) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '';
ALTER TABLE mysql_replication_stats MODIFY column `position` int(11) NOT NULL DEFAULT '0';
ALTER TABLE mysql_replication_stats MODIFY column `databases` varchar(200) NOT NULL DEFAULT '';
ALTER TABLE mysql_replication_stats MODIFY column `ssh_ok` int(11) NOT NULL DEFAULT 0;
