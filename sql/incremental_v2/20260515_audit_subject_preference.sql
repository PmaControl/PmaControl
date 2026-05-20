-- Audit module (#1235) — audit_subject_preference
--
-- Per-user audit preferences: opt-in extended logging, retention
-- overrides, and forget/export markers. GDPR-tracked. Absent row =
-- default policy (request_log 14 d, auth_event 180 d, no extended).

CREATE TABLE audit_subject_preference (
    id_user_main         INT UNSIGNED NOT NULL,
    extended_logging     TINYINT(1)   NOT NULL DEFAULT 0,
    retention_request_d  SMALLINT UNSIGNED NULL,
    retention_auth_d     SMALLINT UNSIGNED NULL,
    forget_requested_at  DATETIME(3)  NULL,
    forget_completed_at  DATETIME(3)  NULL,
    last_export_at       DATETIME(3)  NULL,
    notes                VARCHAR(255) NULL,
    PRIMARY KEY (id_user_main)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
