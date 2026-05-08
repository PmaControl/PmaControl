---
title: Incremental Backup in MySQL Using Page Tracking
source:
  name: Percona Blog
  url: https://www.percona.com/blog/incremental-backup-in-mysql-using-page-tracking/
  post_id: 25399
source_author:
  name: Dev Montiontactic
  slug: mt_admin
  url: https://www.percona.com/blog/author/mt_admin/
  website: ''
published_at: '2022-02-11T13:00:59'
published_at_gmt: '2022-02-11T13:00:59'
modified_at: '2026-05-05T16:42:31'
modified_at_gmt: '2026-05-05T16:42:31'
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
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- incremental backup
- MySQL
- mysql-and-variants
- page tracking
- Percona Software
tag_slugs:
- incremental-backup
- mysql
- mysql-and-variants
- page-tracking
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Incremental-Backup-in-MySQL-Using-Page-Tracking.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Incremental Backup in MySQL Using Page Tracking

Source: [Percona Blog](https://www.percona.com/blog/incremental-backup-in-mysql-using-page-tracking/)

Auteur source: [Dev Montiontactic](https://www.percona.com/blog/author/mt_admin/)

Publication: 2022-02-11T13:00:59

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Incremental backups of MySQL, specifically for the InnoDB engine, are taken by copying modified pages from the previous backup. The brute force method takes backups by scanning every page in tablespace file in the server data directory is an expensive operation. The time required for incremental backups increases as the data-dir size increases. To solve … Continued

## Structure detectee

- H2: Cases Where Page Tracking is Useful
- H2: Prerequisite
- H2: Usage
- H3: Example of a Full Backup
- H3: Example of an Incremental Backup
- H3: Purging Page-Tracking on Server
- H3: Open Issue with Page Tracking

## Images et graphiques reperes

- featured / image: [Incremental Backup in MySQL Using Page Tracking](https://www.percona.com/wp-content/uploads/2026/03/Incremental-Backup-in-MySQL-Using-Page-Tracking.png)
- content / image: [Incremental Backup in MySQL Using Page Tracking](https://www.percona.com/wp-content/uploads/2026/03/Incremental-Backup-in-MySQL-Using-Page-Tracking-300x169.png)
