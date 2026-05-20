-- Add SuperAdmin > LogAudit menu entry for the existing AuditLog screen.

SET @log_audit_menu_group_id := 1;

SET @log_audit_superadmin_id := (
    SELECT `id`
    FROM `menu`
    WHERE `group_id` = @log_audit_menu_group_id
      AND `title` = 'SuperAdmin'
      AND `active` = 1
    ORDER BY `id`
    LIMIT 1
);

SET @log_audit_menu_exists := (
    SELECT COUNT(*)
    FROM `menu`
    WHERE `group_id` = @log_audit_menu_group_id
      AND `class` = 'AuditLog'
      AND `method` = 'index'
);

SET @log_audit_insert_at := (
    SELECT `bd`
    FROM `menu`
    WHERE `id` = @log_audit_superadmin_id
);

UPDATE `menu`
SET `bd` = `bd` + 2
WHERE @log_audit_menu_exists = 0
  AND @log_audit_superadmin_id IS NOT NULL
  AND @log_audit_insert_at IS NOT NULL
  AND `group_id` = @log_audit_menu_group_id
  AND `bd` >= @log_audit_insert_at;

UPDATE `menu`
SET `bg` = `bg` + 2
WHERE @log_audit_menu_exists = 0
  AND @log_audit_superadmin_id IS NOT NULL
  AND @log_audit_insert_at IS NOT NULL
  AND `group_id` = @log_audit_menu_group_id
  AND `bg` > @log_audit_insert_at;

INSERT INTO `menu` (`title`, `bg`, `bd`, `parent_id`, `group_id`, `icon`, `url`, `class`, `method`, `active`)
SELECT 'LogAudit',
       @log_audit_insert_at,
       @log_audit_insert_at + 1,
       @log_audit_superadmin_id,
       @log_audit_menu_group_id,
       '<i class="fa fa-history" aria-hidden="true"></i>',
       '{LINK}AuditLog/index',
       'AuditLog',
       'index',
       1
FROM DUAL
WHERE @log_audit_menu_exists = 0
  AND @log_audit_superadmin_id IS NOT NULL
  AND @log_audit_insert_at IS NOT NULL;
