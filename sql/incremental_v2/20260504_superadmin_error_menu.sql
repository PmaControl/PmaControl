SET @superadmin_root_parent_id := (
    SELECT `parent_id`
    FROM `menu`
    WHERE `group_id` = 1
      AND `title` = 'Logout'
      AND `class` = 'user'
      AND `method` = 'logout'
      AND `active` = 1
    ORDER BY `id`
    LIMIT 1
);

SET @superadmin_group_id := 1;

SET @superadmin_insert_at := (
    SELECT `bg`
    FROM `menu`
    WHERE `group_id` = @superadmin_group_id
      AND `parent_id` = @superadmin_root_parent_id
      AND `title` = 'Logout'
      AND `class` = 'user'
      AND `method` = 'logout'
      AND `active` = 1
    ORDER BY `bg`
    LIMIT 1
);

SET @superadmin_menu_exists := (
    SELECT COUNT(*)
    FROM `menu`
    WHERE `group_id` = @superadmin_group_id
      AND `parent_id` = @superadmin_root_parent_id
      AND `title` = 'SuperAdmin'
);

UPDATE `menu`
SET `bd` = `bd` + 2
WHERE @superadmin_menu_exists = 0
  AND @superadmin_root_parent_id IS NOT NULL
  AND @superadmin_insert_at IS NOT NULL
  AND `group_id` = @superadmin_group_id
  AND `bd` >= @superadmin_insert_at;

UPDATE `menu`
SET `bg` = `bg` + 2
WHERE @superadmin_menu_exists = 0
  AND @superadmin_root_parent_id IS NOT NULL
  AND @superadmin_insert_at IS NOT NULL
  AND `group_id` = @superadmin_group_id
  AND `bg` >= @superadmin_insert_at;

INSERT INTO `menu` (`title`, `bg`, `bd`, `parent_id`, `group_id`, `icon`, `url`, `class`, `method`, `active`)
SELECT 'SuperAdmin',
       @superadmin_insert_at,
       @superadmin_insert_at + 1,
       @superadmin_root_parent_id,
       @superadmin_group_id,
       '<i class="fa fa-user-secret" aria-hidden="true"></i>',
       '',
       '',
       '',
       1
FROM DUAL
WHERE @superadmin_menu_exists = 0
  AND @superadmin_root_parent_id IS NOT NULL
  AND @superadmin_insert_at IS NOT NULL;

SET @superadmin_menu_id := (
    SELECT `id`
    FROM `menu`
    WHERE `group_id` = @superadmin_group_id
      AND `parent_id` = @superadmin_root_parent_id
      AND `title` = 'SuperAdmin'
    ORDER BY `id`
    LIMIT 1
);

SET @superadmin_errors_exists := (
    SELECT COUNT(*)
    FROM `menu`
    WHERE `group_id` = @superadmin_group_id
      AND `parent_id` = @superadmin_menu_id
      AND `title` = 'Errors'
      AND `class` = 'error'
      AND `method` = 'index'
);

SET @superadmin_errors_insert_at := (
    SELECT `bd`
    FROM `menu`
    WHERE `id` = @superadmin_menu_id
);

UPDATE `menu`
SET `bd` = `bd` + 2
WHERE @superadmin_errors_exists = 0
  AND @superadmin_menu_id IS NOT NULL
  AND @superadmin_errors_insert_at IS NOT NULL
  AND `group_id` = @superadmin_group_id
  AND `bd` >= @superadmin_errors_insert_at;

UPDATE `menu`
SET `bg` = `bg` + 2
WHERE @superadmin_errors_exists = 0
  AND @superadmin_menu_id IS NOT NULL
  AND @superadmin_errors_insert_at IS NOT NULL
  AND `group_id` = @superadmin_group_id
  AND `bg` > @superadmin_errors_insert_at;

INSERT INTO `menu` (`title`, `bg`, `bd`, `parent_id`, `group_id`, `icon`, `url`, `class`, `method`, `active`)
SELECT 'Errors',
       @superadmin_errors_insert_at,
       @superadmin_errors_insert_at + 1,
       @superadmin_menu_id,
       @superadmin_group_id,
       '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>',
       '{LINK}error/index',
       'error',
       'index',
       1
FROM DUAL
WHERE @superadmin_errors_exists = 0
  AND @superadmin_menu_id IS NOT NULL
  AND @superadmin_errors_insert_at IS NOT NULL;

SET @superadmin_developer_id := (
    SELECT `id`
    FROM `menu`
    WHERE `group_id` = @superadmin_group_id
      AND `title` = 'Developer'
      AND (`parent_id` = @superadmin_root_parent_id OR `parent_id` = @superadmin_menu_id)
    ORDER BY (`parent_id` = @superadmin_menu_id) DESC, `id`
    LIMIT 1
);

SET @superadmin_developer_parent_id := (
    SELECT `parent_id`
    FROM `menu`
    WHERE `id` = @superadmin_developer_id
);

SET @superadmin_developer_bg := (
    SELECT `bg`
    FROM `menu`
    WHERE `id` = @superadmin_developer_id
);

SET @superadmin_developer_bd := (
    SELECT `bd`
    FROM `menu`
    WHERE `id` = @superadmin_developer_id
);

SET @superadmin_developer_width := @superadmin_developer_bd - @superadmin_developer_bg + 1;

SET @superadmin_developer_insert_at := (
    SELECT `bd`
    FROM `menu`
    WHERE `id` = @superadmin_menu_id
);

SET @superadmin_move_developer := (
    @superadmin_developer_id IS NOT NULL
    AND @superadmin_developer_parent_id <> @superadmin_menu_id
    AND @superadmin_developer_insert_at IS NOT NULL
    AND @superadmin_developer_width > 0
    AND @superadmin_developer_bg > @superadmin_developer_insert_at
);

SET @superadmin_developer_offset := @superadmin_developer_insert_at - @superadmin_developer_bg;

UPDATE `menu`
SET `bg` = -`bg`,
    `bd` = -`bd`
WHERE @superadmin_move_developer = 1
  AND `group_id` = @superadmin_group_id
  AND `bg` >= @superadmin_developer_bg
  AND `bd` <= @superadmin_developer_bd;

UPDATE `menu`
SET `bg` = `bg` - @superadmin_developer_width
WHERE @superadmin_move_developer = 1
  AND `group_id` = @superadmin_group_id
  AND `bg` > @superadmin_developer_bd;

UPDATE `menu`
SET `bd` = `bd` - @superadmin_developer_width
WHERE @superadmin_move_developer = 1
  AND `group_id` = @superadmin_group_id
  AND `bd` > @superadmin_developer_bd;

UPDATE `menu`
SET `bd` = `bd` + @superadmin_developer_width
WHERE @superadmin_move_developer = 1
  AND `group_id` = @superadmin_group_id
  AND `bd` >= @superadmin_developer_insert_at;

UPDATE `menu`
SET `bg` = `bg` + @superadmin_developer_width
WHERE @superadmin_move_developer = 1
  AND `group_id` = @superadmin_group_id
  AND `bg` >= @superadmin_developer_insert_at;

UPDATE `menu`
SET `bg` = -`bg` + @superadmin_developer_offset,
    `bd` = -`bd` + @superadmin_developer_offset
WHERE @superadmin_move_developer = 1
  AND `group_id` = @superadmin_group_id
  AND `bg` < 0
  AND `bd` < 0;

UPDATE `menu`
SET `parent_id` = @superadmin_menu_id
WHERE @superadmin_move_developer = 1
  AND `id` = @superadmin_developer_id;
