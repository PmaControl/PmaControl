---
title: MySQL/ZFS Performance Update
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-zfs-performance-update/
  post_id: 24609
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2021-07-09T12:14:38'
published_at_gmt: '2021-07-09T12:14:38'
modified_at: '2026-04-27T22:31:03'
modified_at_gmt: '2026-04-27T22:31:03'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Benchmarks
- Hardware and Storage
- Insight for DBAs
- MySQL
category_slugs:
- benchmarks
- hardware-and-storage
- insight-for-dbas
- mysql
tags:
- InnoDB
- MySQL
- mysql-and-variants
- TPCC
- ZFS
tag_slugs:
- innodb
- mysql
- mysql-and-variants
- tpcc
- zfs
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQLZFS-Performance-Update.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL/ZFS Performance Update

Source: [Percona Blog](https://www.percona.com/blog/mysql-zfs-performance-update/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2021-07-09T12:14:38

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

As some of you likely know, I have a favorable view of ZFS and especially of MySQL on ZFS. As I published a few years ago, the argument for ZFS was less about performance than its useful features like data compression and snapshots. At the time, ZFS was significantly slower than xfs and ext4 except … Continued

## Structure detectee

- H2: ZFS Evolution
- H2: Benchmark Tools
- H2: Test Environment
- H2: Configuration
- H2: Dataset
- H2: Test Procedure
- H2: Results
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [MySQL/ZFS Performance Update](https://www.percona.com/wp-content/uploads/2026/03/MySQLZFS-Performance-Update.png)
- content / image: [MySQL/ZFS Performance Update](https://www.percona.com/wp-content/uploads/2026/03/MySQLZFS-Performance-Update-300x168.png)
- content / image: [TPCC transactions ZFS](https://www.percona.com/wp-content/uploads/2026/03/tpcc_ext4_zfs.png)
  Caption: MySQL TPCC results for ext4 and ZFS

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
