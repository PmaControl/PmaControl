-- Issue #1212 — greenfield relay provisioning. Extends
-- blackhole_conversion to track which pipeline ran (existing-slave
-- conversion vs from-scratch provisioning) and which master the new
-- relay should follow.

ALTER TABLE blackhole_conversion
    ADD COLUMN IF NOT EXISTS provision_mode
        ENUM('convert','greenfield') NOT NULL DEFAULT 'convert'
        AFTER dry_run;

ALTER TABLE blackhole_conversion
    ADD COLUMN IF NOT EXISTS id_mysql_server__master INT(11) NOT NULL DEFAULT 0
        AFTER id_mysql_server;

ALTER TABLE blackhole_conversion
    ADD COLUMN IF NOT EXISTS replication_user VARCHAR(64) NOT NULL DEFAULT ''
        AFTER id_mysql_server__master;
