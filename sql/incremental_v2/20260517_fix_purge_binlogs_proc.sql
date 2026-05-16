-- Issue #1262 — rewrite the broken `purge_binlogs_if_needed` procedure
-- for MariaDB.
--
-- The original implementation tried to enforce a size cap by reading
-- `mysql.log_status` (a MySQL 8.0+ table that does NOT exist on
-- MariaDB) and `information_schema.files` filtered on
-- `tablespace_name LIKE 'mysql/binlog%'` (this view only lists InnoDB
-- tablespaces — no binlog row was ever returned). The event scheduler
-- has been logging "Unknown column 'File_size' in 'SELECT'" every 10
-- minutes since the procedure was installed.
--
-- Why we can't keep size-based purge in pure SQL on MariaDB:
--   - `SHOW BINARY LOGS` cannot feed `SELECT INTO`, cannot be used as
--     a subquery, and cannot be captured into a TEMPORARY TABLE via
--     `INSERT … SHOW`. No `information_schema` view exposes the
--     binlog file list either.
--   - Size-based enumeration from SQL is therefore impossible.
--
-- This migration:
--   1. Adds `retention_days` to `pmacontrol.binlogsize` (default 7).
--      `max_size_gb` stays for `Binlog::purgeAll` (PHP) which still
--      does size-based capping every ~71 s via `daemon_main` #17.
--   2. Rewrites `purge_binlogs_if_needed` to do AGE-based purge using
--      `PURGE BINARY LOGS BEFORE` — the only purge form MariaDB
--      accepts from a stored procedure. Reads `retention_days` from
--      the same row.
--
-- The event `evt_purge_binlogs` (every 10 min) is left in place; it
-- now calls the working procedure.
--
-- Idempotent: `ALTER TABLE … ADD COLUMN IF NOT EXISTS` covers reruns;
-- `DROP PROCEDURE IF EXISTS` + `CREATE PROCEDURE` covers prior
-- installs.

ALTER TABLE `binlogsize`
    ADD COLUMN IF NOT EXISTS `retention_days` INT NOT NULL DEFAULT 7
    AFTER `max_size_gb`;

DROP PROCEDURE IF EXISTS `purge_binlogs_if_needed`;

DELIMITER $$

CREATE DEFINER=`root`@`localhost` PROCEDURE `purge_binlogs_if_needed`()
BEGIN
    -- Age-based safety net. `Binlog::purgeAll` (PmaControl PHP) does
    -- the size cap; this procedure makes sure we never keep binlogs
    -- past `retention_days` even when PHP is down. PURGE BEFORE is a
    -- no-op when nothing is old enough, so it's safe to call every
    -- 10 minutes from `evt_purge_binlogs`.

    DECLARE v_retention_days INT DEFAULT 7;

    SELECT GREATEST(1, COALESCE(retention_days, 7))
      INTO v_retention_days
      FROM `pmacontrol`.`binlogsize`
     ORDER BY id LIMIT 1;

    -- `PURGE BINARY LOGS BEFORE` requires a string literal, not a
    -- variable expression, so build the statement dynamically.
    SET @bh_cutoff = DATE_FORMAT(
        DATE_SUB(NOW(), INTERVAL v_retention_days DAY),
        '%Y-%m-%d %H:%i:%s'
    );
    SET @bh_sql = CONCAT("PURGE BINARY LOGS BEFORE '", @bh_cutoff, "'");
    PREPARE bh_stmt FROM @bh_sql;
    EXECUTE bh_stmt;
    DEALLOCATE PREPARE bh_stmt;
END$$

DELIMITER ;
