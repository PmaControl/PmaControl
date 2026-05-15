-- Code review follow-up for #1236: give the top Settings menu a stable
-- identifier so the language submenu is not tied only to the editable title.

ALTER TABLE `menu`
    ADD COLUMN IF NOT EXISTS `code` VARCHAR(64) NULL DEFAULT NULL AFTER `title`;

CREATE INDEX IF NOT EXISTS `idx_menu_code` ON `menu` (`code`);

UPDATE `menu`
SET `code` = 'settings'
WHERE (`code` IS NULL OR `code` = '')
  AND `title` = 'Settings'
  AND `url` = ''
  AND `active` = 1
  AND `parent_id` IN (
      SELECT `id`
      FROM (
          SELECT `id`
          FROM `menu`
          WHERE `parent_id` IS NULL
            AND `group_id` = 1
            AND `active` = 1
      ) AS root_menu
  );
