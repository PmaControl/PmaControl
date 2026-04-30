CREATE TABLE IF NOT EXISTS `digest_report_schedule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `report_slug` varchar(64) NOT NULL DEFAULT 'digest_summary',
  `id_group` int(11) NOT NULL,
  `frequency` enum('daily','weekly') NOT NULL DEFAULT 'daily',
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `time_of_day` time NOT NULL DEFAULT '07:00:00',
  `day_of_week` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `limit_rows` tinyint(3) unsigned NOT NULL DEFAULT 5,
  `next_run_at` datetime DEFAULT NULL,
  `last_run_at` datetime DEFAULT NULL,
  `last_status` enum('ok','warning','error') DEFAULT NULL,
  `last_error` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_digest_report_due` (`is_active`, `next_run_at`),
  KEY `idx_digest_report_group` (`id_group`),
  CONSTRAINT `fk_digest_report_schedule_group` FOREIGN KEY (`id_group`) REFERENCES `group` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `digest_report_delivery` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_digest_report_schedule` int(10) unsigned NOT NULL,
  `recipient_email` varchar(255) NOT NULL,
  `sent_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('ok','error','dry_run') NOT NULL,
  `error_message` text DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `body_checksum` char(64) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_digest_report_delivery_schedule` (`id_digest_report_schedule`, `sent_at`),
  CONSTRAINT `fk_digest_report_delivery_schedule` FOREIGN KEY (`id_digest_report_schedule`) REFERENCES `digest_report_schedule` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET @digest_report_menu_exists := (
    SELECT COUNT(*)
    FROM `menu`
    WHERE `title` = 'Digest reports'
      AND `class` = 'DigestReport'
      AND `method` = 'index'
);

SET @digest_report_parent_id := COALESCE(
    (
        SELECT `id`
        FROM `menu`
        WHERE `title` = 'Settings'
          AND `active` = 1
        ORDER BY `id`
        LIMIT 1
    ),
    (
        SELECT `id`
        FROM `menu`
        WHERE `title` = 'Dashboard'
          AND `active` = 1
        ORDER BY `id`
        LIMIT 1
    )
);

SET @digest_report_insert_at := (
    SELECT `bd`
    FROM `menu`
    WHERE `id` = @digest_report_parent_id
);

SET @digest_report_group_id := (
    SELECT `group_id`
    FROM `menu`
    WHERE `id` = @digest_report_parent_id
);

UPDATE `menu`
SET `bd` = `bd` + 2
WHERE @digest_report_menu_exists = 0
  AND @digest_report_parent_id IS NOT NULL
  AND `group_id` = @digest_report_group_id
  AND `bd` >= @digest_report_insert_at;

UPDATE `menu`
SET `bg` = `bg` + 2
WHERE @digest_report_menu_exists = 0
  AND @digest_report_parent_id IS NOT NULL
  AND `group_id` = @digest_report_group_id
  AND `bg` > @digest_report_insert_at;

INSERT INTO `menu` (`title`, `bg`, `bd`, `parent_id`, `group_id`, `icon`, `url`, `class`, `method`, `active`)
SELECT 'Digest reports',
       @digest_report_insert_at,
       @digest_report_insert_at + 1,
       @digest_report_parent_id,
       @digest_report_group_id,
       '<i class="fa fa-envelope-o" aria-hidden="true"></i>',
       '{LINK}DigestReport/index',
       'DigestReport',
       'index',
       1
FROM DUAL
WHERE @digest_report_menu_exists = 0
  AND @digest_report_parent_id IS NOT NULL;
