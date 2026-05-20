-- ============================================================
-- Issue #583 — split /database/refresh into dump + load phases
-- so the load can be restarted without re-dumping.
-- ============================================================
--
-- Adds 5 columns to `job`:
--   - `dump_status`         : per-phase status (RUNNING / SUCCESS / ERROR / NULL)
--   - `load_status`         : idem for the load phase
--   - `artifact_path`       : absolute path of the mydumper output dir on disk;
--                             populated when the dump starts, cleared once the
--                             load completes successfully (only then can we rm).
--   - `artifact_size_kb`    : `du -sk` snapshot, surfaced in /home (#584) and
--                             /job/index for capacity planning.
--   - `artifact_expires_at` : conservative TTL for orphan cleanup. The Restart
--                             load only button hides itself past this date.
--
-- All five columns are nullable: the only refreshes that fill them are the
-- new ones; pre-existing rows stay untouched.

ALTER TABLE `job`
  ADD COLUMN `dump_status`         VARCHAR(32)    DEFAULT NULL AFTER `status`,
  ADD COLUMN `load_status`         VARCHAR(32)    DEFAULT NULL AFTER `dump_status`,
  ADD COLUMN `artifact_path`       VARCHAR(255)   DEFAULT NULL AFTER `load_status`,
  ADD COLUMN `artifact_size_kb`    BIGINT UNSIGNED DEFAULT NULL AFTER `artifact_path`,
  ADD COLUMN `artifact_expires_at` DATETIME       DEFAULT NULL AFTER `artifact_size_kb`;
