ALTER TABLE `daemon_main`
  ADD COLUMN `is_enabled` TINYINT(1) NOT NULL DEFAULT 1
  COMMENT 'Whether check_daemon should auto-restart this daemon (1=yes, 0=no)';
