---
title: How To Get Your Backup to Half of Its Size – Introducing ZSTD Support in Percona XtraBackup
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-get-your-backup-to-half-of-its-size-introducing-zstd-support-in-percona-xtrabackup/
  post_id: 26246
source_author:
  name: Marcelo Altmann
  slug: marcelo-altmann
  url: https://www.percona.com/blog/author/marcelo-altmann/
  website: https://blog.marceloaltmann.com
published_at: '2022-11-17T13:00:23'
published_at_gmt: '2022-11-17T13:00:23'
modified_at: '2026-03-26T20:30:44'
modified_at_gmt: '2026-03-26T20:30:44'
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
- Percona XtraBackup
tag_slugs:
- mysql
- mysql-and-variants
- percona-xtrabackup
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/ZSTD-Support-in-Percona-XtraBackup.png
image_count: 7
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How To Get Your Backup to Half of Its Size – Introducing ZSTD Support in Percona XtraBackup

Source: [Percona Blog](https://www.percona.com/blog/how-to-get-your-backup-to-half-of-its-size-introducing-zstd-support-in-percona-xtrabackup/)

Auteur source: [Marcelo Altmann](https://www.percona.com/blog/author/marcelo-altmann/)

Publication: 2022-11-17T13:00:23

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Having a backup of your database is like insurance, you have to pay a monthly price to ensure you have a service available when you need to. When talking about backups, the storage required to keep your backups is what comes into factor when talking about price, the bigger your backup, or the bigger the … Continued

## Structure detectee

- H2: Usage
- H3: Compress
- H3: Decompress
- H2: Testing
- H2: Results
- H3: Summary

## Images et graphiques reperes

- featured / image: [How To Get Your Backup to Half of Its Size – Introducing ZSTD Support in Percona XtraBackup](https://www.percona.com/wp-content/uploads/2026/03/ZSTD-Support-in-Percona-XtraBackup.png)
- content / image: [ZSTD Support in Percona XtraBackup](https://www.percona.com/wp-content/uploads/2026/03/ZSTD-Support-in-Percona-XtraBackup-300x157.png)
- content / image: [ZSTD Support in Percona XtraBackup](https://www.percona.com/wp-content/uploads/2026/03/Backup-Size.png)
- content / image: [time to decompress](https://www.percona.com/wp-content/uploads/2026/03/Time-to-Decompress.png)
- content / image: [Time-to-Backup.png](https://www.percona.com/wp-content/uploads/2026/03/Time-to-Backup.png)
- content / image: [Time-to-Backup-Stream.png](https://www.percona.com/wp-content/uploads/2026/03/Time-to-Backup-Stream.png)
- content / image: [Time-to-Download-Decompress.png](https://www.percona.com/wp-content/uploads/2026/03/Time-to-Download-Decompress.png)

## Auteur source

Marcelo Altmann is a C++ Software Engineer working on MySQL related products. At Percona he has also worked as a Senior Support Engineer and a Tech Lead of the Support Team. Prior to joining Percona , he worked as a MySQL DBA at Ireland's CCTLD, and worked as a DBA/PHP developer in Brazil. He also blogs about other MySQL related stuff at his personal blog.
