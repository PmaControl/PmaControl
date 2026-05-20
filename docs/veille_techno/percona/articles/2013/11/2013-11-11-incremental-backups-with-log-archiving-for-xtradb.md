---
title: Incremental backups with log archiving for XtraDB
source:
  name: Percona Blog
  url: https://www.percona.com/blog/incremental-backups-with-log-archiving-for-xtradb/
  post_id: 7539
source_author:
  name: Hrvoje Matijakovic
  slug: hrvojem
  url: https://www.percona.com/blog/author/hrvojem/
  website: ''
published_at: '2013-11-11T21:24:25'
published_at_gmt: '2013-11-11T21:24:25'
modified_at: '2026-04-28T21:57:42'
modified_at_gmt: '2026-04-28T21:57:42'
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
- MySQL
category_slugs:
- mysql
tags:
- incremental backups
- log archiving
tag_slugs:
- incremental-backups
- log-archiving
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Incremental backups with log archiving for XtraDB

Source: [Percona Blog](https://www.percona.com/blog/incremental-backups-with-log-archiving-for-xtradb/)

Auteur source: [Hrvoje Matijakovic](https://www.percona.com/blog/author/hrvojem/)

Publication: 2013-11-11T21:24:25

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona Server 5.6.11-60.3 has introduced a new feature called Log Archiving for XtraDB. This feature makes copies of the old log files before they are overwritten, thus saving all the redo log for a write workload. When log archiving is enabled, it duplicates all redo log writes in a separate set of files in addition … Continued

## Structure detectee

- H2: Creating the Backup
- H2: Using the Log Archiving to prepare the backup
