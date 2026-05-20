-- Audit module (#1235) — audit_user_agent
--
-- Deduplicated User-Agent table. Each request_log/auth_event/client_metrics
-- row stores a 32-char MD5 of the raw UA; the drain INSERT IGNORE here
-- once per new hash, then parses via Matomo DeviceDetector to populate
-- os/browser/family/is_robot. Saves storage + parsing CPU.

CREATE TABLE audit_user_agent (
    ua_hash         CHAR(32)        NOT NULL,
    ua_raw          VARCHAR(500)    NOT NULL,
    first_seen      DATETIME(3)     NOT NULL,
    last_seen       DATETIME(3)     NOT NULL,
    hit_count       INT UNSIGNED    NOT NULL DEFAULT 1,
    os_family       VARCHAR(32)     NULL,
    os_version      VARCHAR(16)     NULL,
    browser_family  VARCHAR(32)     NULL,
    browser_version VARCHAR(16)     NULL,
    device_type     VARCHAR(16)     NULL,
    is_robot        TINYINT(1)      NOT NULL DEFAULT 0,
    robot_family    VARCHAR(64)     NULL,
    PRIMARY KEY (ua_hash),
    KEY idx_last_seen (last_seen),
    KEY idx_browser (browser_family, browser_version),
    KEY idx_os (os_family),
    KEY idx_robot (is_robot, robot_family)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
