-- Epic #799 / lots 3-4 : columns the collectors need.
--
-- ndb_cluster gets the connection details so NdbMgmShowFetcher knows
-- where to run `ndb_mgm -e show` (either directly via TCP or wrapped
-- in an SSH command — the lab uses `pct exec` on the Proxmox host).
--
-- ndb_node gets memory snapshot columns refreshed by NdbInfoCollector
-- from `ndbinfo.memoryusage`. Snapshot columns (not time-series) so we
-- don't grow per-tick — alerting reads the latest value.

ALTER TABLE `ndb_cluster`
  ADD COLUMN IF NOT EXISTS `mgmd_host`        VARCHAR(100) NOT NULL DEFAULT '' AFTER `display_name`,
  ADD COLUMN IF NOT EXISTS `mgmd_port`        SMALLINT UNSIGNED NOT NULL DEFAULT 1186 AFTER `mgmd_host`,
  ADD COLUMN IF NOT EXISTS `ssh_target_host`  VARCHAR(100) NOT NULL DEFAULT '' AFTER `mgmd_port`,
  ADD COLUMN IF NOT EXISTS `ssh_target_port`  SMALLINT UNSIGNED NOT NULL DEFAULT 22 AFTER `ssh_target_host`,
  ADD COLUMN IF NOT EXISTS `ssh_login`        VARCHAR(64)  NOT NULL DEFAULT 'root' AFTER `ssh_target_port`,
  ADD COLUMN IF NOT EXISTS `command_template` VARCHAR(255) NOT NULL DEFAULT 'ndb_mgm -h {{mgmd_host}}:{{mgmd_port}} -e show' AFTER `ssh_login`,
  ADD COLUMN IF NOT EXISTS `id_mysql_server_sql` INT NULL AFTER `command_template`;

ALTER TABLE `ndb_node`
  ADD COLUMN IF NOT EXISTS `memory_used_mb`         INT UNSIGNED DEFAULT NULL AFTER `uptime_sec`,
  ADD COLUMN IF NOT EXISTS `memory_total_mb`        INT UNSIGNED DEFAULT NULL AFTER `memory_used_mb`,
  ADD COLUMN IF NOT EXISTS `data_memory_used_mb`    INT UNSIGNED DEFAULT NULL AFTER `memory_total_mb`,
  ADD COLUMN IF NOT EXISTS `index_memory_used_mb`   INT UNSIGNED DEFAULT NULL AFTER `data_memory_used_mb`;
