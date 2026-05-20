-- Issue #739: endpoints published by MySQL Router must stay in the
-- regular MySQL monitoring pipeline so `mysql_available` keeps being written.
UPDATE mysql_server a
INNER JOIN mysqlrouter_server__mysql_server b ON b.id_mysql_server = a.id
SET a.is_proxy = 1,
    a.is_monitored = 1
WHERE a.is_deleted = 0
  AND (a.is_proxy != 1 OR a.is_monitored != 1);
