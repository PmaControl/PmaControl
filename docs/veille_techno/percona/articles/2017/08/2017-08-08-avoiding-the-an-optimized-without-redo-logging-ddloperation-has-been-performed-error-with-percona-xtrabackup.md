---
title: Avoiding the "An optimized (without redo logging) DDL operation has been performed" Error with Percona XtraBackup
source:
  name: Percona Blog
  url: https://www.percona.com/blog/avoiding-the-an-optimized-without-redo-logging-ddloperation-has-been-performed-error-with-percona-xtrabackup/
  post_id: 17195
source_author:
  name: Shahriyar Rzayev
  slug: shahryar-rzayev
  url: https://www.percona.com/blog/author/shahryar-rzayev/
  website: http://mysql.az
published_at: '2017-08-08T13:51:54'
published_at_gmt: '2017-08-08T13:51:54'
modified_at: '2026-05-05T18:47:12'
modified_at_gmt: '2026-05-05T18:47:12'
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
category_slugs:
- insight-for-dbas
- mysql
tags:
- --lock-ddl
- --lock-ddl-per-table
- An optimized(without redo logging) DDLoperation has been performed
- Percona XtraBackup
tag_slugs:
- lock-ddl
- lock-ddl-per-table
- an-optimizedwithout-redo-logging-ddloperation-has-been-performed
- percona-xtrabackup
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraBackup-1.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Avoiding the "An optimized (without redo logging) DDL operation has been performed" Error with Percona XtraBackup

Source: [Percona Blog](https://www.percona.com/blog/avoiding-the-an-optimized-without-redo-logging-ddloperation-has-been-performed-error-with-percona-xtrabackup/)

Auteur source: [Shahriyar Rzayev](https://www.percona.com/blog/author/shahryar-rzayev/)

Publication: 2017-08-08T13:51:54

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This blog discusses newly added options for Percona XtraBackup 2.4.8 and how they can impact your database backups. To avoid issues with MySQL 5.7 skipping the redo log for DDL, Percona XtraBackup has implemented three new options ( xtrabackup -- lock - ddl , xtrabackup -- lock - ddl - timeout , xtrabackup -- lock - ddl - per - table ) that can be used to place MDL locks on tables while they are copied. So … Continued

## Images et graphiques reperes

- featured / image: [Avoiding the "An optimized (without redo logging) DDL operation has been performed" Error with Percona XtraBackup](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraBackup-1.png)

## Auteur source

Shako from Azerbaijan/Baku. My hobby is cooking kebap. Bug lover by design.
