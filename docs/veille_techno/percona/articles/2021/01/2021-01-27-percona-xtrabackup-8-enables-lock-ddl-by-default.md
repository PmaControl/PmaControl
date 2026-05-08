---
title: Percona XtraBackup 8 Enables –lock-ddl by Default
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-xtrabackup-8-enables-lock-ddl-by-default/
  post_id: 23864
source_author:
  name: Patrick Birch
  slug: patrick-birch
  url: https://www.percona.com/blog/author/patrick-birch/
  website: ''
published_at: '2021-01-27T18:22:06'
published_at_gmt: '2021-01-27T18:22:06'
modified_at: '2026-03-23T18:16:15'
modified_at_gmt: '2026-03-23T18:16:15'
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
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraBackup-8-Enables-lock-ddl-by-Default.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona XtraBackup 8 Enables –lock-ddl by Default

Source: [Percona Blog](https://www.percona.com/blog/percona-xtrabackup-8-enables-lock-ddl-by-default/)

Auteur source: [Patrick Birch](https://www.percona.com/blog/author/patrick-birch/)

Publication: 2021-01-27T18:22:06

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona XtraBackup 8.0.23 enables the “lock-ddl” option by default to ensure any DDL events do not corrupt the backups. Any DML events continue to occur, and only DDL events are blocked. A DDL lock protects the definition of tables and views. With the “–lock-ddl” option disabled, Percona XtraBackup allows backups while concurrent DDL events continue … Continued

## Images et graphiques reperes

- featured / image: [Percona XtraBackup 8 Enables –lock-ddl by Default](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraBackup-8-Enables-lock-ddl-by-Default.png)
- content / image: [Percona XtraBackup 8 Enables --lock-ddl by Default](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraBackup-8-Enables-lock-ddl-by-Default-300x169.png)
