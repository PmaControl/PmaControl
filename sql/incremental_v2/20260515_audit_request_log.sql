-- Audit module (#1235) — request_log
--
-- Server-side trace of every authenticated HTTP request. Written through
-- the NDJSON spool (`tmp/audit/requests/`) and drained in batch by the
-- AuditDrain CLI; never INSERTed synchronously on the hot path.

CREATE TABLE request_log (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    request_uid     CHAR(32)        NOT NULL,
    date            DATETIME(3)     NOT NULL,
    id_user_main    INT UNSIGNED    NULL,
    ip              VARCHAR(45)     NOT NULL,
    method          VARCHAR(8)      NOT NULL,
    uri             VARCHAR(2048)   NOT NULL,
    status          SMALLINT UNSIGNED NULL,
    bytes_sent      INT UNSIGNED    NULL,
    referer         VARCHAR(2048)   NULL,
    user_agent_hash CHAR(32)        NULL,
    duration_us     INT UNSIGNED    NULL,
    php_ms          INT UNSIGNED    NULL,
    controller      VARCHAR(64)     NULL,
    action          VARCHAR(64)     NULL,
    user_role_class VARCHAR(32)     NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_request_uid (request_uid),
    KEY idx_date (date),
    KEY idx_user_date (id_user_main, date),
    KEY idx_ip_date (ip, date),
    KEY idx_route (controller, action, date),
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
