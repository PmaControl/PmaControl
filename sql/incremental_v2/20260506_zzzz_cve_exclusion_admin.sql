-- CVE false-positive control table and SuperAdmin screen.
-- CVE-2022-22965 is Spring Framework/Spring4Shell and enters this backlog only
-- through Oracle Enterprise Manager for MySQL Database metadata, not through a
-- MySQL-like server component.

CREATE TABLE IF NOT EXISTS `cve_exclusion` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cve_id` varchar(32) NOT NULL,
  `is_disabled` tinyint(1) NOT NULL DEFAULT 1,
  `reason` text NULL,
  `disabled_by` varchar(128) DEFAULT NULL,
  `disabled_at` datetime DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cve_exclusion_cve_id` (`cve_id`),
  KEY `idx_cve_exclusion_disabled` (`is_disabled`,`disabled_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `cve_exclusion` (`cve_id`, `is_disabled`, `reason`, `disabled_by`, `disabled_at`)
VALUES (
  'CVE-2022-22965',
  1,
  'Spring Framework / Enterprise Manager false positive; not MySQL-like server backlog scope.',
  'migration',
  NOW()
)
ON DUPLICATE KEY UPDATE
  `cve_id` = `cve_id`;

SET @cve_exclusion_menu_group_id := 1;

SET @cve_exclusion_superadmin_id := (
    SELECT `id`
    FROM `menu`
    WHERE `group_id` = @cve_exclusion_menu_group_id
      AND `title` = 'SuperAdmin'
      AND `active` = 1
    ORDER BY `id`
    LIMIT 1
);

SET @cve_exclusion_menu_exists := (
    SELECT COUNT(*)
    FROM `menu`
    WHERE `group_id` = @cve_exclusion_menu_group_id
      AND `class` = 'cve'
      AND `method` = 'exclusions'
);

SET @cve_exclusion_insert_at := (
    SELECT `bd`
    FROM `menu`
    WHERE `id` = @cve_exclusion_superadmin_id
);

UPDATE `menu`
SET `bd` = `bd` + 2
WHERE @cve_exclusion_menu_exists = 0
  AND @cve_exclusion_superadmin_id IS NOT NULL
  AND @cve_exclusion_insert_at IS NOT NULL
  AND `group_id` = @cve_exclusion_menu_group_id
  AND `bd` >= @cve_exclusion_insert_at;

UPDATE `menu`
SET `bg` = `bg` + 2
WHERE @cve_exclusion_menu_exists = 0
  AND @cve_exclusion_superadmin_id IS NOT NULL
  AND @cve_exclusion_insert_at IS NOT NULL
  AND `group_id` = @cve_exclusion_menu_group_id
  AND `bg` > @cve_exclusion_insert_at;

INSERT INTO `menu` (`title`, `bg`, `bd`, `parent_id`, `group_id`, `icon`, `url`, `class`, `method`, `active`)
SELECT 'CVE exclusions',
       @cve_exclusion_insert_at,
       @cve_exclusion_insert_at + 1,
       @cve_exclusion_superadmin_id,
       @cve_exclusion_menu_group_id,
       '<i class="fa fa-ban" aria-hidden="true"></i>',
       '{LINK}cve/exclusions',
       'cve',
       'exclusions',
       1
FROM DUAL
WHERE @cve_exclusion_menu_exists = 0
  AND @cve_exclusion_superadmin_id IS NOT NULL
  AND @cve_exclusion_insert_at IS NOT NULL;
