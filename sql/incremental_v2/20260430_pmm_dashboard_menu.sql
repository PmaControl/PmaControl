SET @pmm_menu_exists := (
    SELECT COUNT(*)
    FROM `menu`
    WHERE `title` = 'PMM Dashboards'
      AND `class` = 'Pmm'
      AND `method` = 'index'
);

SET @pmm_parent_id := (
    SELECT `id`
    FROM `menu`
    WHERE `title` = 'Dashboard'
      AND `active` = 1
    ORDER BY `id`
    LIMIT 1
);

SET @pmm_insert_at := (
    SELECT `bd`
    FROM `menu`
    WHERE `id` = @pmm_parent_id
);

SET @pmm_group_id := (
    SELECT `group_id`
    FROM `menu`
    WHERE `id` = @pmm_parent_id
);

UPDATE `menu`
SET `bd` = `bd` + 2
WHERE @pmm_menu_exists = 0
  AND @pmm_parent_id IS NOT NULL
  AND `group_id` = @pmm_group_id
  AND `bd` >= @pmm_insert_at;

UPDATE `menu`
SET `bg` = `bg` + 2
WHERE @pmm_menu_exists = 0
  AND @pmm_parent_id IS NOT NULL
  AND `group_id` = @pmm_group_id
  AND `bg` > @pmm_insert_at;

INSERT INTO `menu` (`title`, `bg`, `bd`, `parent_id`, `group_id`, `icon`, `url`, `class`, `method`, `active`)
SELECT 'PMM Dashboards',
       @pmm_insert_at,
       @pmm_insert_at + 1,
       @pmm_parent_id,
       @pmm_group_id,
       '<i class="fa fa-area-chart" aria-hidden="true"></i>',
       '{LINK}Pmm/index',
       'Pmm',
       'index',
       1
FROM DUAL
WHERE @pmm_menu_exists = 0
  AND @pmm_parent_id IS NOT NULL;
