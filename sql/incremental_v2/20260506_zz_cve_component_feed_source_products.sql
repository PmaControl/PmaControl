INSERT INTO `cve_feed_source_product`
  (`id_cve_feed_source`, `product_code`, `product_name`, `cpe_vendor`, `cpe_product`, `notes`)
SELECT s.`id`, p.`product_code`, p.`product_name`, p.`cpe_vendor`, p.`cpe_product`, p.`notes`
FROM `cve_feed_source` s
INNER JOIN (
  SELECT 'nvd' AS `source_code`, 'xtrabackup' AS `product_code`, 'Percona XtraBackup' AS `product_name`, 'percona' AS `cpe_vendor`, 'xtrabackup' AS `cpe_product`, 'Component CVEs must not be merged into Percona Server.' AS `notes`
  UNION ALL SELECT 'nvd', 'pmm', 'Percona Monitoring and Management', 'percona', 'monitoring_and_management', 'PMM CVEs must stay separate from Percona Server.'
  UNION ALL SELECT 'nvd', 'mariadb_backup', 'MariaDB Backup', 'mariadb', 'mariadb_backup', 'Backup component CVEs must stay separate from MariaDB Server.'
  UNION ALL SELECT 'percona_advisory', 'xtrabackup', 'Percona XtraBackup', 'percona', 'xtrabackup', 'Authoritative Percona advisory source for XtraBackup.'
  UNION ALL SELECT 'percona_advisory', 'pmm', 'Percona Monitoring and Management', 'percona', 'monitoring_and_management', 'Authoritative Percona advisory source for PMM.'
) p ON p.`source_code` = s.`code`
ON DUPLICATE KEY UPDATE
  `product_name` = VALUES(`product_name`),
  `cpe_vendor` = VALUES(`cpe_vendor`),
  `cpe_product` = VALUES(`cpe_product`),
  `notes` = VALUES(`notes`);
