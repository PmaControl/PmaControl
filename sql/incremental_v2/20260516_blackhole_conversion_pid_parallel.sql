-- Issue #1250 — track the converter pid directly on the conversion row,
-- record the post-conversion BLACKHOLE table count, and let the operator
-- choose a parallelism factor for the ALTER loop.
--
-- pid is intentionally duplicated from `job` (which already stores it for
-- the /job/index page) so the /Blackhole/index card can render a liveness
-- indicator with a single-table query.

ALTER TABLE blackhole_conversion
    ADD COLUMN pid                    INT          NULL                  AFTER started_by,
    ADD COLUMN pid_started_at         DATETIME     NULL                  AFTER pid,
    ADD COLUMN tables_blackhole_after INT          NOT NULL DEFAULT 0    AFTER tables_converted,
    ADD COLUMN parallelism            TINYINT      NOT NULL DEFAULT 1    AFTER tables_blackhole_after,
    ADD INDEX idx_pid_status (pid, status);
