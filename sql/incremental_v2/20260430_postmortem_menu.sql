SET @postmortem_menu_exists := (
    SELECT COUNT(*)
    FROM `menu`
    WHERE `title` = 'Post-mortem'
      AND `class` = 'PostMortem'
      AND `method` = 'index'
);

SET @postmortem_parent_id := (
    SELECT `id`
    FROM `menu`
    WHERE `title` = 'Tools'
      AND `active` = 1
    ORDER BY `id`
    LIMIT 1
);

SET @postmortem_insert_at := (
    SELECT `bd`
    FROM `menu`
    WHERE `id` = @postmortem_parent_id
);

UPDATE `menu`
SET `bd` = `bd` + 2
WHERE @postmortem_menu_exists = 0
  AND @postmortem_parent_id IS NOT NULL
  AND `bd` >= @postmortem_insert_at;

UPDATE `menu`
SET `bg` = `bg` + 2
WHERE @postmortem_menu_exists = 0
  AND @postmortem_parent_id IS NOT NULL
  AND `bg` > @postmortem_insert_at;

INSERT INTO `menu` (`title`, `bg`, `bd`, `parent_id`, `icon`, `url`, `class`, `method`, `active`)
SELECT 'Post-mortem',
       @postmortem_insert_at,
       @postmortem_insert_at + 1,
       @postmortem_parent_id,
       '<i class="fa fa-file-text-o" aria-hidden="true"></i>',
       '{LINK}postmortem/index',
       'PostMortem',
       'index',
       1
FROM DUAL
WHERE @postmortem_menu_exists = 0
  AND @postmortem_parent_id IS NOT NULL;
