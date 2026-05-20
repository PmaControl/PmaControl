-- Issue #1257 — turn the MySQL-log import pipeline on by default.
--
-- daemon_main #37 (Worker::addToQueue 6) enqueues an
-- Aspirateur::tryMysqlLogCollection job per monitored server every 15 s.
-- daemon_main #38 (IntegrateLog::integrateAll) drains the *.part.* files
-- those collectors write into data/logs/, parses them, and inserts into
-- ssh_log_mysql_line / ssh_log_mysql_agg_{day,hour,minute}.
--
-- Both rows shipped with is_enabled = 0, so /MysqlServer/logs/<id>/ has
-- shown stale (or empty) data on fresh installations. Flip them on so
-- new installations get a working log import out of the box.
--
-- Operators who deliberately want it off can re-disable after migration:
--    UPDATE daemon_main SET is_enabled = 0 WHERE id IN (37, 38);

UPDATE daemon_main SET is_enabled = 1 WHERE id IN (37, 38);
