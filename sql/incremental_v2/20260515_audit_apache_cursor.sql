-- Audit module (#1235) — audit_apache_cursor
--
-- Persistent cursor for the optional Apache access-log backfill
-- (static assets the app's RequestAuditCollector doesn't see). One row
-- per access-log file path; tracks the last fully-parsed byte offset
-- and the last seen mtime so a restart resumes where it left off.

CREATE TABLE audit_apache_cursor (
    log_path        VARCHAR(512)    NOT NULL,
    last_inode      BIGINT UNSIGNED NOT NULL DEFAULT 0,
    last_byte_offset BIGINT UNSIGNED NOT NULL DEFAULT 0,
    last_mtime      DATETIME        NULL,
    last_run        DATETIME(3)     NULL,
    lines_total     BIGINT UNSIGNED NOT NULL DEFAULT 0,
    lines_skipped   BIGINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (log_path)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
