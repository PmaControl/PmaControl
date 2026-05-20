-- Audit module #1235 (post-MVP): per-page-render UUIDv7 in request_log.
--
-- A non-AJAX HTTP request (HTML page render, GET form, POST redirect
-- target, etc.) gets a fresh UUIDv7 stamped in `page_uid` by the
-- collector on the hot path. AJAX requests (URI contains
-- `/ajax:true/`) leave page_uid NULL on the hot path; the audit drain
-- backfills them by matching the Referer against the parent row's
-- URI, so every AJAX descendant inherits its parent page's page_uid.
--
-- The shared page_uid then becomes the grouping key for the
-- "page render → AJAX descendants" tree in /AuditLog/.
-- UUIDv7 is time-ordered, so ORDER BY page_uid yields chronological
-- page-renders without joining on `date`.
--
-- Stored as BINARY(16) (vs CHAR(36)): 2.25x denser on disk + index, no
-- collation issues. The drain converts the UUIDv7 string from the
-- NDJSON spool with UNHEX(REPLACE(uuid, '-', '')) on the way in.
-- Display side gets the canonical 8-4-4-4-12 form via HEX(page_uid)
-- reformatted in PHP (MariaDB 10.11 has no UUID_TO_BIN/BIN_TO_UUID).
ALTER TABLE `request_log`
    ADD COLUMN `page_uid` BINARY(16) NULL DEFAULT NULL AFTER `request_uid`,
    ADD INDEX `idx_request_log_page_uid` (`page_uid`);
