ALTER TABLE mysql_server ADD COLUMN `warning` text NOT NULL DEFAULT '' AFTER is_acknowledged;
