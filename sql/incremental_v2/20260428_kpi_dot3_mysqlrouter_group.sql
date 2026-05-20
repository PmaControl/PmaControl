ALTER TABLE `dot3_run_group`
  MODIFY COLUMN `group_kind` ENUM('master_slave','proxysql','innodb_cluster','vip','galera','maxscale','imported','mysqlrouter') NOT NULL;
