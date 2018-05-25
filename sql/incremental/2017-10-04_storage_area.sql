ALTER TABLE `backup_storage_area` ADD `ssh_key` TEXT NOT NULL AFTER `ssh_password`;

ALTER TABLE mysql_server MODIFY COLUMN `display_name` varchar(100) NOT NULL;

ALTER TABLE cleaner_main ADD COLUMN id_backup_storage_area int(11) NULL DEFAULT 0 AFTER id_mysql_server;
