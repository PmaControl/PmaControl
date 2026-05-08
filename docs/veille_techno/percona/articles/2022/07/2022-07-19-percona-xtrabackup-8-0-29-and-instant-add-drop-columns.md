---
title: Percona XtraBackup 8.0.29 and INSTANT ADD/DROP Columns
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-xtrabackup-8-0-29-and-instant-add-drop-columns/
  post_id: 25833
source_author:
  name: Marcelo Altmann
  slug: marcelo-altmann
  url: https://www.percona.com/blog/author/marcelo-altmann/
  website: https://blog.marceloaltmann.com
published_at: '2022-07-19T12:53:05'
published_at_gmt: '2022-07-19T12:53:05'
modified_at: '2026-03-26T20:31:34'
modified_at_gmt: '2026-03-26T20:31:34'
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
- MySQL
- mysql-and-variants
- Percona Software
- Percona XtraBackup
tag_slugs:
- mysql
- mysql-and-variants
- percona-software
- percona-xtrabackup
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraBackup-8.0.29-and-INSTANT-ADDDROP-Columns.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona XtraBackup 8.0.29 and INSTANT ADD/DROP Columns

Source: [Percona Blog](https://www.percona.com/blog/percona-xtrabackup-8-0-29-and-instant-add-drop-columns/)

Auteur source: [Marcelo Altmann](https://www.percona.com/blog/author/marcelo-altmann/)

Publication: 2022-07-19T12:53:05

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Oracle’s MySQL 8.0.29 release extended the support for ALTER TABLE … ALGORITHM=INSTANT to 1) allow users to instantly add columns in any position of the table, and 2) instantly drop columns. As part of this work, the InnoDB redo log format has changed for all DML operations on the server. This new redo log format … Continued

## Structure detectee

- H2: Find all tables with INSTANT ADD/DROP COLUMNS:
- H2: Percona XtraBackup error message
- H2: Summary

## Images et graphiques reperes

- featured / image: [Percona XtraBackup 8.0.29 and INSTANT ADD/DROP Columns](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraBackup-8.0.29-and-INSTANT-ADDDROP-Columns.png)
- content / image: [Percona XtraBackup 8.0.29 and INSTANT ADD:DROP Columns](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraBackup-8.0.29-and-INSTANT-ADDDROP-Columns-300x157.png)

## Auteur source

Marcelo Altmann is a C++ Software Engineer working on MySQL related products. At Percona he has also worked as a Senior Support Engineer and a Tech Lead of the Support Team. Prior to joining Percona , he worked as a MySQL DBA at Ireland's CCTLD, and worked as a DBA/PHP developer in Brazil. He also blogs about other MySQL related stuff at his personal blog.
