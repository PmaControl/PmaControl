INSERT INTO `cve_product`
  (`product_code`, `product_name`, `product_family`, `icon_class`, `color`)
VALUES
  ('percona_xtradb_cluster', 'Percona XtraDB Cluster', 'mysql_like', 'fa fa-object-group', '#b91c1c')
ON DUPLICATE KEY UPDATE
  `product_name` = VALUES(`product_name`),
  `product_family` = VALUES(`product_family`),
  `icon_class` = VALUES(`icon_class`),
  `color` = VALUES(`color`),
  `is_active` = 1;

INSERT INTO `cve_feed_source_product`
  (`id_cve_feed_source`, `product_code`, `product_name`, `cpe_vendor`, `cpe_product`, `notes`)
SELECT s.`id`, p.`product_code`, p.`product_name`, p.`cpe_vendor`, p.`cpe_product`, p.`notes`
FROM `cve_feed_source` s
INNER JOIN (
  SELECT 'nvd' AS `source_code`, 'percona_xtradb_cluster' AS `product_code`, 'Percona XtraDB Cluster' AS `product_name`, 'percona' AS `cpe_vendor`, 'xtradb_cluster' AS `cpe_product`, 'Percona XtraDB Cluster build suffixes require product-aware version parsing.' AS `notes`
  UNION ALL SELECT 'percona_advisory', 'percona_xtradb_cluster', 'Percona XtraDB Cluster', 'percona', 'xtradb_cluster', 'Authoritative Percona advisory source for Percona XtraDB Cluster.'
) p ON p.`source_code` = s.`code`
ON DUPLICATE KEY UPDATE
  `product_name` = VALUES(`product_name`),
  `cpe_vendor` = VALUES(`cpe_vendor`),
  `cpe_product` = VALUES(`cpe_product`),
  `notes` = VALUES(`notes`);
