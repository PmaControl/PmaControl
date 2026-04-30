SET @reports_menu_exists := (
    SELECT COUNT(*)
    FROM `menu`
    WHERE `title` = 'Reports'
      AND `class` = 'Reports'
      AND `method` = 'index'
);

SET @reports_parent_id := (
    SELECT `id`
    FROM `menu`
    WHERE `title` = 'Dashboard'
      AND `active` = 1
    ORDER BY `id`
    LIMIT 1
);

SET @reports_insert_at := (
    SELECT `bd`
    FROM `menu`
    WHERE `id` = @reports_parent_id
);

SET @reports_group_id := (
    SELECT `group_id`
    FROM `menu`
    WHERE `id` = @reports_parent_id
);

UPDATE `menu`
SET `bd` = `bd` + 2
WHERE @reports_menu_exists = 0
  AND @reports_parent_id IS NOT NULL
  AND `group_id` = @reports_group_id
  AND `bd` >= @reports_insert_at;

UPDATE `menu`
SET `bg` = `bg` + 2
WHERE @reports_menu_exists = 0
  AND @reports_parent_id IS NOT NULL
  AND `group_id` = @reports_group_id
  AND `bg` > @reports_insert_at;

INSERT INTO `menu` (`title`, `bg`, `bd`, `parent_id`, `group_id`, `icon`, `url`, `class`, `method`, `active`)
SELECT 'Reports',
       @reports_insert_at,
       @reports_insert_at + 1,
       @reports_parent_id,
       @reports_group_id,
       '<i class="fa fa-bar-chart" aria-hidden="true"></i>',
       '{LINK}Reports/index',
       'Reports',
       'index',
       1
FROM DUAL
WHERE @reports_menu_exists = 0
  AND @reports_parent_id IS NOT NULL;
