-- Audit module (#1235) — subprocess_log
--
-- Every CLI / shell process spawned from the app via AuditedProcess.
-- Linked back to the originating request via request_uid so the
-- AuditLog UI can show a request → sub-process tree.

CREATE TABLE subprocess_log (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    request_uid     CHAR(32)        NULL,
    date_start      DATETIME(3)     NOT NULL,
    date_end        DATETIME(3)     NULL,
    duration_ms     INT UNSIGNED    NULL,
    pid             INT UNSIGNED    NULL,
    process_start_ticks BIGINT UNSIGNED NULL,
    command         TEXT            NOT NULL,
    label           VARCHAR(64)     NULL,
    exit_code       SMALLINT        NULL,
    log_path        VARCHAR(512)    NULL,
    id_user_main    INT UNSIGNED    NULL,
    status          ENUM('running','done','failed','timeout','killed') NOT NULL DEFAULT 'running',
    PRIMARY KEY (id),
    KEY idx_request_uid (request_uid),
    KEY idx_date_start (date_start),
    KEY idx_label (label),
    KEY idx_pid (pid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
