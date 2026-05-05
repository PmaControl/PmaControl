SET @llm_menu_exists := (
    SELECT COUNT(*)
    FROM `menu`
    WHERE `title` = 'SQL Assistant'
      AND `class` = 'Llm'
      AND `method` = 'index'
);

SET @llm_parent_id := (
    SELECT `id`
    FROM `menu`
    WHERE `title` = 'Tools'
      AND `active` = 1
    ORDER BY `id`
    LIMIT 1
);

SET @llm_insert_at := (
    SELECT `bd`
    FROM `menu`
    WHERE `id` = @llm_parent_id
);

UPDATE `menu`
SET `bd` = `bd` + 2
WHERE @llm_menu_exists = 0
  AND @llm_parent_id IS NOT NULL
  AND `bd` >= @llm_insert_at;

UPDATE `menu`
SET `bg` = `bg` + 2
WHERE @llm_menu_exists = 0
  AND @llm_parent_id IS NOT NULL
  AND `bg` > @llm_insert_at;

INSERT INTO `menu` (`title`, `bg`, `bd`, `parent_id`, `icon`, `class`, `method`, `active`)
SELECT 'SQL Assistant',
       @llm_insert_at,
       @llm_insert_at + 1,
       @llm_parent_id,
       '<i class="fa fa-magic" aria-hidden="true"></i>',
       'Llm',
       'index',
       1
FROM DUAL
WHERE @llm_menu_exists = 0
  AND @llm_parent_id IS NOT NULL;
