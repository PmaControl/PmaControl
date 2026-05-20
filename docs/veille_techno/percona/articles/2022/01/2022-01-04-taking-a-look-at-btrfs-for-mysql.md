---
title: Taking a Look at BTRFS for MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/taking-a-look-at-btrfs-for-mysql/
  post_id: 25249
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2022-01-04T12:06:09'
published_at_gmt: '2022-01-04T12:06:09'
modified_at: '2026-05-05T22:35:16'
modified_at_gmt: '2026-05-05T22:35:16'
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
- MySQL
category_slugs:
- benchmarks
- mysql
tags:
- MySQL
- mysql-and-variants
tag_slugs:
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/BTRFS-for-MySQL.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Taking a Look at BTRFS for MySQL

Source: [Percona Blog](https://www.percona.com/blog/taking-a-look-at-btrfs-for-mysql/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2022-01-04T12:06:09

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Following my post MySQL/ZFS Performance Update, a few people have suggested I should take a look at BTRFS (“butter-FS”, “b-tree FS”) with MySQL. BTRFS is a filesystem with an architecture and a set of features that are similar to ZFS and with a GPL license. It is a copy-on-write (CoW) filesystem supporting snapshots, RAID, and … Continued

## Structure detectee

- H2: Test Environment
- H2: Benchmark Procedure
- H4: BTRFS
- H4: ZFS
- H4: ext4
- H2: Results
- H4: TPCC events
- H4: Filesystem Size
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Taking a Look at BTRFS for MySQL](https://www.percona.com/wp-content/uploads/2026/03/BTRFS-for-MySQL.png)
- content / image: [BTRFS for MySQL](https://www.percona.com/wp-content/uploads/2026/03/BTRFS-for-MySQL-300x157.png)
- content / image: [TPCC performance comparison](https://www.percona.com/wp-content/uploads/2026/03/btrfsVsZfs_TPCC.png)
  Caption: TPCC performance comparison, ext4, BTRFS and ZFS
- content / image: [TPCC dataset size comparison, ext4, BTRFS and ZFS](https://www.percona.com/wp-content/uploads/2026/03/btrfsVsZfs_size.png)
  Caption: TPCC dataset size comparison, ext4, BTRFS and ZFS

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
