-- Issue #1212 — BLACKHOLE binlog-relay infrastructure.
-- Lot 1 (#1213): is_binlog_relay flag on mysql_server
-- Lot 2 (#1214): blackhole_conversion tracking table
-- Lot 5 (#1217): Tools → BLACKHOLE menu entry

-- ── 1. is_binlog_relay flag ──────────────────────────────────────────
-- mysql_server uses MariaDB system-versioning; ALTER needs the bypass
-- session variable (KEEP=preserve history, see MDEV-16330).
SET @prev_versioning_alter := @@SESSION.system_versioning_alter_history;
SET SESSION system_versioning_alter_history = KEEP;

SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'mysql_server'
      AND column_name = 'is_binlog_relay'
);

SET @sql := IF(@col_exists = 0,
    'ALTER TABLE mysql_server ADD COLUMN is_binlog_relay TINYINT(1) NOT NULL DEFAULT 0 AFTER is_vip',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET SESSION system_versioning_alter_history = @prev_versioning_alter;


-- ── 2. blackhole_conversion tracking table ───────────────────────────
CREATE TABLE IF NOT EXISTS `blackhole_conversion` (
    `id`              INT(11)        NOT NULL AUTO_INCREMENT,
    `id_mysql_server` INT(11)        NOT NULL,
    `status`          ENUM('pending','running','done','failed','rolledback')
                                     NOT NULL DEFAULT 'pending',
    `dry_run`         TINYINT(1)     NOT NULL DEFAULT 0,
    `started_by`      VARCHAR(64)    NOT NULL DEFAULT '',
    `tables_total`    INT(11)        NOT NULL DEFAULT 0,
    `tables_converted` INT(11)       NOT NULL DEFAULT 0,
    `progress`        LONGTEXT       NOT NULL,
    `error_message`   TEXT           NOT NULL,
    `created_at`      DATETIME       NOT NULL,
    `completed_at`    DATETIME       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_server_status` (`id_mysql_server`, `status`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ── 3. Tools → BLACKHOLE menu entry (idempotent) ─────────────────────
SET @blackhole_exists := (
    SELECT COUNT(*) FROM `menu`
    WHERE `class` = 'Blackhole' AND `method` = 'index'
);

SET @blackhole_parent_id := (
    SELECT `id` FROM `menu`
    WHERE `title` = 'Tools' AND `active` = 1
    ORDER BY `id` LIMIT 1
);

SET @blackhole_insert_at := (
    SELECT `bd` FROM `menu` WHERE `id` = @blackhole_parent_id
);

UPDATE `menu`
SET `bd` = `bd` + 2
WHERE @blackhole_exists = 0
  AND @blackhole_parent_id IS NOT NULL
  AND `bd` >= @blackhole_insert_at;

UPDATE `menu`
SET `bg` = `bg` + 2
WHERE @blackhole_exists = 0
  AND @blackhole_parent_id IS NOT NULL
  AND `bg` > @blackhole_insert_at;

INSERT INTO `menu` (`title`, `bg`, `bd`, `parent_id`, `icon`, `url`, `class`, `method`, `active`)
SELECT 'BLACKHOLE',
       @blackhole_insert_at,
       @blackhole_insert_at + 1,
       @blackhole_parent_id,
       '<svg viewBox="0 0 16 16" width="14" height="14" style="vertical-align:-2px" aria-hidden="true"><defs><radialGradient id="bh" cx="50%" cy="50%" r="50%"><stop offset="0%" stop-color="#000"/><stop offset="55%" stop-color="#1a1a2e"/><stop offset="75%" stop-color="#7e22ce"/><stop offset="92%" stop-color="#f59e0b"/><stop offset="100%" stop-color="rgba(245,158,11,0)"/></radialGradient></defs><circle cx="8" cy="8" r="7.5" fill="url(#bh)"/><circle cx="8" cy="8" r="3.2" fill="#000"/></svg>',
       '{LINK}Blackhole/index',
       'Blackhole',
       'index',
       1
FROM DUAL
WHERE @blackhole_exists = 0
  AND @blackhole_parent_id IS NOT NULL;
