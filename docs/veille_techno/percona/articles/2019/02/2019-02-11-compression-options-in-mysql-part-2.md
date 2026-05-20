---
title: Compression Options in MySQL (Part 2)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/compression-options-in-mysql-part-2/
  post_id: 19915
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2019-02-11T16:39:38'
published_at_gmt: '2019-02-11T16:39:38'
modified_at: '2026-04-27T21:11:12'
modified_at_gmt: '2026-04-27T21:11:12'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- XtraBackup
matched_filters:
- category:mysql:83
- search:percona-xtrabackup
- search:xtrabackup
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- compression
- data compression
- InnoDB page compression
- transparent page compression
tag_slugs:
- compression
- data-compression
- innodb-page-compression
- transparent-page-compression
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Swiss-chese-FS.png
image_count: 8
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Compression Options in MySQL (Part 2)

Source: [Percona Blog](https://www.percona.com/blog/compression-options-in-mysql-part-2/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2019-02-11T16:39:38

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In one of my previous posts, I started a series on data compression options with MySQL. The first post focused on the more traditional compression options like InnoDB Barracuda page compression and MyISAM packing. With this second part, I’ll discuss a newer compression option, InnoDB transparent page compression with punch holes available since 5.7. First, … Continued

## Structure detectee

- H2: InnoDB transparent page compression
- H3: Sparse files 101
- H3: InnoDB using sparse files
- H3: MySQL impacts
- H2: Review of the test procedure
- H2: Results
- H3: Final sizes
- H3: Insertion time
- H3: Data written by inserts
- H3: Range selects
- H3: 20k updates time
- H3: Bytes written per update
- H2: Operational considerations for larger InnoDB pages and TC

## Images et graphiques reperes

- featured / image: [Compression Options in MySQL (Part 2)](https://www.percona.com/wp-content/uploads/2026/03/Swiss-chese-FS.png)
- content / image: [Figure 1: InnoDB Transparent page compression](https://www.percona.com/wp-content/uploads/2026/03/TP-compression.png)
  Caption: Figure 1: InnoDB Transparent page compression
- content / image: [Figure 3, Innodb transparent page compression final sizes](https://www.percona.com/wp-content/uploads/2026/03/tc_size.png)
  Caption: Figure 3, Innodb transparent page compression final sizes
- content / image: [Figure 4, InnoDB transparent page compression insert times](https://www.percona.com/wp-content/uploads/2026/03/tc_insert_time.png)
  Caption: Figure 4, InnoDB transparent page compression insert times
- content / image: [Figure 5, total amount of data written during the inserts](https://www.percona.com/wp-content/uploads/2026/03/tc_datawritten_insert.png)
  Caption: Figure 5, total amount of data written during the inserts
- content / image: [Figure 6, time to complete a long range scan](https://www.percona.com/wp-content/uploads/2026/03/tc_select_time.png)
  Caption: Figure 6, time to complete a long range scan
- content / image: [Figure 7, time needed to perform 20k updates](https://www.percona.com/wp-content/uploads/2026/03/tc_update_time.png)
  Caption: Figure 7, time needed to perform 20k updates
- content / image: [Figure 8, average bytes written per update](https://www.percona.com/wp-content/uploads/2026/03/tc_byte_written_per_update.png)
  Caption: Figure 8, average bytes written per update

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
