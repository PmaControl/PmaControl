-- ============================================================
-- MariaDB ONLY — issue #1262
-- ============================================================
-- This file uses `CREATE OR REPLACE PROCEDURE`, a MariaDB-specific
-- syntax. MySQL parses it as an error and refuses the migration, so
-- non-MariaDB hosts can't apply it by accident. The procedure body
-- relies on `PURGE BINARY LOGS BEFORE` + dynamic SQL — the only purge
-- form a MariaDB stored procedure can use. Size-based enumeration
-- is impossible in pure MariaDB SQL: `SHOW BINARY LOGS` can't feed
-- `SELECT INTO` and no `information_schema` view exposes the binlog
-- file list.
--
-- Background: the legacy `purge_binlogs_if_needed` was written for
-- MySQL 8.0+, reading `mysql.log_status` (doesn't exist on MariaDB)
-- and `information_schema.files WHERE tablespace_name LIKE
-- 'mysql/binlog%'` (only lists InnoDB tablespaces, never matches
-- binlogs). It logged "Unknown column 'File_size' in 'SELECT'"
-- every 10 minutes since 2025-07-27.
--
-- Division of responsibility after this migration:
--   - PHP `daemon_main` #17 (`Binlog::purgeAll`, every ~71 s) keeps
--     enforcing the size cap from `binlogsize.max_size_gb`.
--   - This DB-side procedure is an AGE-based safety net only,
--     reading the new `binlogsize.retention_days` column (default 7).
--     `PURGE BEFORE` is a no-op when nothing is old enough, so the
--     event is harmless to call every 10 min.
--
-- Runtime guard: if the migration ever ends up routed to a MySQL
-- host (e.g. someone copies it manually), the SELECT below errors
-- out before any DDL runs — belt-and-braces on top of the
-- `CREATE OR REPLACE` parser-level guard.

SELECT IF(
    VERSION() LIKE '%MariaDB%', 'MariaDB host — applying',
    (SELECT CAST('Migration is MariaDB-only — skip on MySQL' AS UNSIGNED))
) AS migration_gate;

ALTER TABLE `binlogsize`
    ADD COLUMN IF NOT EXISTS `retention_days` INT NOT NULL DEFAULT 7
    AFTER `max_size_gb`;

DELIMITER $$

CREATE OR REPLACE DEFINER=`root`@`localhost` PROCEDURE `purge_binlogs_if_needed`()
BEGIN
    -- Age-based safety net for binlog retention. Size cap is handled
    -- by PmaControl PHP (`Binlog::purgeAll`); this procedure makes
    -- sure binlogs older than `retention_days` get purged even if
    -- the PHP daemon is down.

    DECLARE v_retention_days INT DEFAULT 7;

    SELECT GREATEST(1, COALESCE(retention_days, 7))
      INTO v_retention_days
      FROM `pmacontrol`.`binlogsize`
     ORDER BY id LIMIT 1;

    -- `PURGE BINARY LOGS BEFORE` requires a string literal, not a
    -- variable expression — build the statement dynamically.
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
