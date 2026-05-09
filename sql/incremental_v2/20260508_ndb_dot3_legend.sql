-- Epic #799 / lot 5 : Dot3 legend rows for NDB cluster theme colors.
-- Mirrors the INNODB_CLUSTER_* triplet (OK / WARN / CRIT). Inserted via
-- INSERT IGNORE so the migration is idempotent on already-installed hosts.

INSERT IGNORE INTO `dot3_legend` (`const`, `name`, `font`, `color`, `background`, `style`, `order`, `type`, `condition`)
VALUES
    ('NDB_CLUSTER_OK',   'NDB cluster healthy',                          '#ffffff', '#1565c0', '#1565c0', 'filled', 90, 'NDB', ''),
    ('NDB_CLUSTER_WARN', 'NDB cluster degraded (data/sql node missing)', '#ffffff', '#FFA500', '#FFA500', 'filled', 91, 'NDB', ''),
    ('NDB_CLUSTER_CRIT', 'NDB cluster critical (node group lost / no mgmd)', '#ffffff', '#FF0000', '#FF0000', 'filled', 92, 'NDB', '');
