---
title: Using Percona XtraBackup on a MySQL Instance with a Large Number of Tables
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-percona-xtrabackup-mysql-instance-large-number-tables/
  post_id: 16109
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2016-12-28T16:51:21'
published_at_gmt: '2016-12-28T16:51:21'
modified_at: '2026-05-05T18:26:32'
modified_at_gmt: '2026-05-05T18:26:32'
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
- tag:percona-xtrabackup:330
categories:
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- backup
- InnoDB
- Percona XtraBackup
- tables
tag_slugs:
- backup
- innodb
- percona-xtrabackup
- tables
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona_XtraBackupLogoVert_CMYK-1-e1496163100924.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using Percona XtraBackup on a MySQL Instance with a Large Number of Tables

Source: [Percona Blog](https://www.percona.com/blog/using-percona-xtrabackup-mysql-instance-large-number-tables/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2016-12-28T16:51:21

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll find out how to use Percona XtraBackup on a MySQL instance with a large number of tables. As of Percona Xtrabackup 2.4.5, you are required to have enough open files to open every single InnoDB tablespace in the instance you’re trying to back up. So if you’re running innodb_file_per_table=1, and … Continued

## Images et graphiques reperes

- featured / image: [Using Percona XtraBackup on a MySQL Instance with a Large Number of Tables](https://www.percona.com/wp-content/uploads/2026/03/Percona_XtraBackupLogoVert_CMYK-1-e1496163100924.jpg)
- content / image: [Percona XtraBackup](https://www.percona.com/wp-content/uploads/2026/03/Percona_XtraBackupLogoVert_CMYK-1-e1480440893407.jpg)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
