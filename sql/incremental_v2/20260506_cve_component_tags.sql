INSERT INTO `cve_product`
  (`product_code`, `product_name`, `product_family`, `icon_class`, `color`)
VALUES
  ('xtrabackup', 'Percona XtraBackup', 'component', 'fa fa-archive', '#6b21a8'),
  ('pmm', 'Percona Monitoring and Management', 'component', 'fa fa-line-chart', '#7f1d1d'),
  ('mariadb_backup', 'MariaDB Backup', 'component', 'fa fa-archive', '#0f766e')
ON DUPLICATE KEY UPDATE
  `product_name` = VALUES(`product_name`),
  `product_family` = VALUES(`product_family`),
  `icon_class` = VALUES(`icon_class`),
  `color` = VALUES(`color`),
  `is_active` = 1;
