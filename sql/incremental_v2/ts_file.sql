-- Fix ts_file.file_name truncation: proxysql_runtime_mysql_group_replication_hostgroups
-- was 51 chars but column was varchar(50), causing data loss for GR hostgroup collection.
ALTER TABLE ts_file MODIFY file_name VARCHAR(128) NOT NULL;

-- Fix the truncated entry (missing trailing 's')
UPDATE ts_file
SET file_name = 'proxysql_runtime_mysql_group_replication_hostgroups'
WHERE file_name = 'proxysql_runtime_mysql_group_replication_hostgroup';

-- Create the missing ts_variable so Extraction2 can resolve
-- proxysql_runtime::mysql_group_replication_hostgroups
INSERT IGNORE INTO ts_variable (id_ts_file, name, type, `from`, radical)
SELECT id, 'mysql_group_replication_hostgroups', 'JSON', 'proxysql_runtime', 'general'
FROM ts_file
WHERE file_name = 'proxysql_runtime_mysql_group_replication_hostgroups';
