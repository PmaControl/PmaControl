-- Epic #799 / review #1023 P1: NdbAlertEmitter::buildUpsertSql relies on
-- ON DUPLICATE KEY UPDATE against `fingerprint`, but `idx_fingerprint_open`
-- on (fingerprint, status) is non-unique, so every collection cycle inserted
-- a new open row per persistent NDB alert.
--
-- Fix: enforce *one open row per fingerprint at a time* via a generated
-- column that exposes `fingerprint` only while `status = 'open'`. Resolved /
-- closed history rows keep accumulating (they get NULL on the generated
-- column, and NULLs are not enforced in a UNIQUE index on MariaDB / MySQL).
--
-- Pre-cleanup: drop the duplicates produced by the bug — keep the oldest
-- open row per fingerprint so existing dashboards do not lose context.
DELETE a FROM `pmc_event_alert` a
JOIN `pmc_event_alert` b
  ON a.`fingerprint` = b.`fingerprint`
 AND a.`status`      = 'open'
 AND b.`status`      = 'open'
 AND a.`fingerprint` IS NOT NULL
 AND a.`id` > b.`id`;

ALTER TABLE `pmc_event_alert`
    ADD COLUMN `open_fingerprint` BINARY(32) AS
        (CASE WHEN `status` = 'open' THEN `fingerprint` END) VIRTUAL,
    ADD UNIQUE KEY `uniq_open_fingerprint` (`open_fingerprint`);
