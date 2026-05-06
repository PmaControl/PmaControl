-- Issue #773: add a SuperAdmin > Token mismatch entry that surfaces the
-- PersistentAuthSession::logRevoke / logRotationLostRace events. The new
-- node is inserted as a sibling of "Errors" (id=250) under SuperAdmin
-- (id=249), so we shift every node with bg/bd >= 259 by 2.
--
-- Idempotent: skip if the entry already exists.

SET @exists := (SELECT COUNT(*) FROM `menu` WHERE `class`='AuthSession' AND `method`='tokenMismatch');

UPDATE `menu`
   SET `bg` = `bg` + 2
 WHERE @exists = 0 AND `group_id` = 1 AND `bg` >= 259;

UPDATE `menu`
   SET `bd` = `bd` + 2
 WHERE @exists = 0 AND `group_id` = 1 AND `bd` >= 259;

INSERT INTO `menu` (`parent_id`, `bg`, `bd`, `active`, `icon`, `title`, `url`, `class`, `method`, `position`, `group_id`, `level`)
SELECT 249, 259, 260, 1,
       '<i class="fa fa-key" aria-hidden="true"></i>',
       'Token mismatch',
       '{LINK}AuthSession/tokenMismatch',
       'AuthSession', 'tokenMismatch',
       0, 1, ''
  FROM DUAL
 WHERE @exists = 0;
