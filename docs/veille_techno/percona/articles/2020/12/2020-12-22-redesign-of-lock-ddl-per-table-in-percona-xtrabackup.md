---
title: Redesign of –lock-ddl-per-table in Percona XtraBackup
source:
  name: Percona Blog
  url: https://www.percona.com/blog/redesign-of-lock-ddl-per-table-in-percona-xtrabackup/
  post_id: 23569
source_author:
  name: Marcelo Altmann
  slug: marcelo-altmann
  url: https://www.percona.com/blog/author/marcelo-altmann/
  website: https://blog.marceloaltmann.com
published_at: '2020-12-22T13:05:26'
published_at_gmt: '2020-12-22T13:05:26'
modified_at: '2026-04-27T22:20:08'
modified_at_gmt: '2026-04-27T22:20:08'
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
- Percona Server for MySQL
- Percona XtraBackup
tag_slugs:
- mysql
- mysql-and-variants
- percona-server
- percona-xtrabackup
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Redesign-of-lock-ddl-per-table-in-Percona-XtraBackup-1.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Redesign of –lock-ddl-per-table in Percona XtraBackup

Source: [Percona Blog](https://www.percona.com/blog/redesign-of-lock-ddl-per-table-in-percona-xtrabackup/)

Auteur source: [Marcelo Altmann](https://www.percona.com/blog/author/marcelo-altmann/)

Publication: 2020-12-22T13:05:26

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MySQL 5.7, alongside other many improvements, brought bulk load for creating an index (WL#7277 to be specific), which made ADD INDEX operations much faster by disabling redo logging and making the changes directly to tablespace files. This change requires extra care for backup tools. To block DDL statements on an instance, Percona Server for MySQL … Continued

## Structure detectee

- H2: Full-Text Index
- H2: New Table Added in the Middle of the Backup
- H2: Shared Tablespaces
- H2: Best/Worst Case Scenario
- H2: Redesign of –lock-ddl-per-table

## Images et graphiques reperes

- featured / image: [Redesign of –lock-ddl-per-table in Percona XtraBackup](https://www.percona.com/wp-content/uploads/2026/03/Redesign-of-lock-ddl-per-table-in-Percona-XtraBackup-1.png)
- content / image: [Redesign of -lock-ddl-per-table in Percona XtraBackup](https://www.percona.com/wp-content/uploads/2026/03/Redesign-of-lock-ddl-per-table-in-Percona-XtraBackup-1-300x168.png)

## Auteur source

Marcelo Altmann is a C++ Software Engineer working on MySQL related products. At Percona he has also worked as a Senior Support Engineer and a Tech Lead of the Support Team. Prior to joining Percona , he worked as a MySQL DBA at Ireland's CCTLD, and worked as a DBA/PHP developer in Brazil. He also blogs about other MySQL related stuff at his personal blog.
