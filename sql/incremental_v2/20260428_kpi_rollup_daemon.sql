INSERT IGNORE INTO `daemon_main`
  (`name`, `date`, `pid`, `refresh_time`, `max_delay`, `class`, `method`, `params`, `debug`, `is_enabled`)
VALUES
  ('KPI minute rollup', NOW(), 0, 60, 30, 'KpiRollup', 'rollupMinute', '', 0, 1);

ALTER TABLE `event_log`
  ADD INDEX IF NOT EXISTS `idx_kpi_open_incidents` (`date_start`, `date_end`),
  ALGORITHM=INPLACE,
  LOCK=NONE;
