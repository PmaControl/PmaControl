-- Audit module (#1235) — auth_event
--
-- Login, logout, token_mismatch, csrf_reject and other auth-pipeline
-- events. Higher retention than request_log (180 d default vs 14 d).

CREATE TABLE auth_event (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    request_uid     CHAR(32)        NULL,
    date            DATETIME(3)     NOT NULL,
    id_user_main    INT UNSIGNED    NULL,
    ip              VARCHAR(45)     NOT NULL,
    user_agent_hash CHAR(32)        NULL,
    event_type      ENUM(
        'login_success',
        'login_fail',
        'logout',
        'token_mismatch',
        'fingerprint_mismatch',
        'expired',
        'unknown_selector',
        'csrf_reject',
        'rate_limit',
        'password_change'
    ) NOT NULL,
    selector_prefix CHAR(12)        NULL,
    detail          TEXT            NULL,
    PRIMARY KEY (id),
    KEY idx_date (date),
    KEY idx_user_date (id_user_main, date),
    KEY idx_event_date (event_type, date),
    KEY idx_request_uid (request_uid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
