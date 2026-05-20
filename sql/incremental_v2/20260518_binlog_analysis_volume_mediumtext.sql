-- Binlog analysis time series can exceed 64KB on high-throughput windows.
-- Keep the existing JSON shape, but allow longer per-second graph payloads.
SET @binlog_analysis_exists := (
    SELECT COUNT(*)
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name = 'binlog_analysis'
);

SET @binlog_analysis_needs_mediumtext := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'binlog_analysis'
      AND column_name IN ('parallelism_per_second', 'volume_per_second')
      AND data_type NOT IN ('mediumtext', 'longtext')
);

SET @sql := IF(
    @binlog_analysis_exists = 1 AND @binlog_analysis_needs_mediumtext > 0,
    'ALTER TABLE `binlog_analysis`
       MODIFY `parallelism_per_second` MEDIUMTEXT DEFAULT NULL COMMENT ''JSON [{ts, txn_count}] for parallelism graph'',
       MODIFY `volume_per_second` MEDIUMTEXT DEFAULT NULL COMMENT ''JSON [{ts, bytes, txn}]''',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
