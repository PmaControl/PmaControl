-- Issue #929: home-page surfacing of ProxySQL audit findings.
-- Materializes per-ProxySQL-server severity counts so /home can render a
-- deep-link card without re-running the full audit on every page load.
CREATE TABLE IF NOT EXISTS `proxysql_audit_snapshot` (
    `id_proxysql_server` int(11) NOT NULL,
    `findings_critical` int(10) unsigned NOT NULL DEFAULT 0,
    `findings_warning`  int(10) unsigned NOT NULL DEFAULT 0,
    `findings_info`     int(10) unsigned NOT NULL DEFAULT 0,
    `last_checked_at`   datetime NULL DEFAULT NULL,
    `last_error`        varchar(255) NULL DEFAULT NULL,
    PRIMARY KEY (`id_proxysql_server`),
    CONSTRAINT `fk_proxysql_audit_snapshot__proxysql_server`
        FOREIGN KEY (`id_proxysql_server`)
        REFERENCES `proxysql_server` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_general_ci;
