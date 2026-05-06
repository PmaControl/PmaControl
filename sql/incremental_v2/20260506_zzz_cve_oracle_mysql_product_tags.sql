INSERT INTO `cve_product`
  (`product_code`, `product_name`, `product_family`, `icon_class`, `color`)
VALUES
  ('mysql_cluster', 'MySQL Cluster', 'component', 'fa fa-object-group', '#d97706'),
  ('mysql_client', 'MySQL Client', 'component', 'fa fa-terminal', '#b45309'),
  ('mysql_connectors', 'MySQL Connectors', 'component', 'fa fa-plug', '#92400e'),
  ('mysql_enterprise_backup', 'MySQL Enterprise Backup', 'component', 'fa fa-archive', '#a16207'),
  ('mysql_enterprise_firewall', 'MySQL Enterprise Firewall', 'component', 'fa fa-shield', '#b91c1c'),
  ('mysql_enterprise_monitor', 'MySQL Enterprise Monitor', 'component', 'fa fa-line-chart', '#1d4ed8'),
  ('mysql_installer', 'MySQL Installer', 'component', 'fa fa-download', '#0f766e'),
  ('mysql_shell', 'MySQL Shell', 'component', 'fa fa-terminal', '#0369a1'),
  ('mysql_shell_vscode', 'MySQL Shell for VS Code', 'component', 'fa fa-code', '#2563eb'),
  ('mysql_workbench', 'MySQL Workbench', 'component', 'fa fa-wrench', '#7c2d12'),
  ('enterprise_manager_mysql', 'Enterprise Manager for MySQL Database', 'component', 'fa fa-desktop', '#4338ca')
ON DUPLICATE KEY UPDATE
  `product_name` = VALUES(`product_name`),
  `product_family` = VALUES(`product_family`),
  `icon_class` = VALUES(`icon_class`),
  `color` = VALUES(`color`),
  `is_active` = 1;

INSERT INTO `cve_feed_source_product`
  (`id_cve_feed_source`, `product_code`, `product_name`, `cpe_vendor`, `cpe_product`, `notes`)
SELECT s.`id`, p.`product_code`, p.`product_name`, 'oracle', p.`product_code`, p.`notes`
FROM `cve_feed_source` s
INNER JOIN (
  SELECT 'mysql' AS `product_code`, 'MySQL Server' AS `product_name`, 'Oracle MySQL Risk Matrix Product column tag.' AS `notes`
  UNION ALL SELECT 'mysql_cluster', 'MySQL Cluster', 'Oracle MySQL Risk Matrix Product column tag.'
  UNION ALL SELECT 'mysql_client', 'MySQL Client', 'Oracle MySQL Risk Matrix Product column tag.'
  UNION ALL SELECT 'mysql_connectors', 'MySQL Connectors', 'Oracle MySQL Risk Matrix Product column tag.'
  UNION ALL SELECT 'mysql_enterprise_backup', 'MySQL Enterprise Backup', 'Oracle MySQL Risk Matrix Product column tag.'
  UNION ALL SELECT 'mysql_enterprise_firewall', 'MySQL Enterprise Firewall', 'Oracle MySQL Risk Matrix Product column tag.'
  UNION ALL SELECT 'mysql_enterprise_monitor', 'MySQL Enterprise Monitor', 'Oracle MySQL Risk Matrix Product column tag.'
  UNION ALL SELECT 'mysql_installer', 'MySQL Installer', 'Oracle MySQL Risk Matrix Product column tag.'
  UNION ALL SELECT 'mysql_shell', 'MySQL Shell', 'Oracle MySQL Risk Matrix Product column tag.'
  UNION ALL SELECT 'mysql_shell_vscode', 'MySQL Shell for VS Code', 'Oracle MySQL Risk Matrix Product column tag.'
  UNION ALL SELECT 'mysql_workbench', 'MySQL Workbench', 'Oracle MySQL Risk Matrix Product column tag.'
  UNION ALL SELECT 'enterprise_manager_mysql', 'Enterprise Manager for MySQL Database', 'Oracle MySQL Risk Matrix Product column tag.'
) p
WHERE s.`code` = 'oracle_cpu'
ON DUPLICATE KEY UPDATE
  `product_name` = VALUES(`product_name`),
  `cpe_vendor` = VALUES(`cpe_vendor`),
  `cpe_product` = VALUES(`cpe_product`),
  `notes` = VALUES(`notes`);
