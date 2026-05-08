---
title: 20X Faster Backup Preparation With Percona XtraBackup 8.0.33-28!
source:
  name: Percona Blog
  url: https://www.percona.com/blog/20x-faster-backup-preparation-with-percona-xtrabackup-8-0-33-28/
  post_id: 27268
source_author:
  name: Satya Bodapati
  slug: satya-bodapati
  url: https://www.percona.com/blog/author/satya-bodapati/
  website: ''
published_at: '2023-07-25T14:05:07'
published_at_gmt: '2023-07-25T14:05:07'
modified_at: '2026-03-26T20:29:25'
modified_at_gmt: '2026-03-26T20:29:25'
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
- Insight for Developers
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- insight-for-developers
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
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Faster-Backup-Preparation-With-Percona-XtraBackup.jpeg
image_count: 12
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# 20X Faster Backup Preparation With Percona XtraBackup 8.0.33-28!

Source: [Percona Blog](https://www.percona.com/blog/20x-faster-backup-preparation-with-percona-xtrabackup-8-0-33-28/)

Auteur source: [Satya Bodapati](https://www.percona.com/blog/author/satya-bodapati/)

Publication: 2023-07-25T14:05:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we will describe the improvements to Percona XtraBackup 8.0.33-28 (PXB), which significantly reduces the time to prepare the backups before the restore operation. This improvement in Percona XtraBackup significantly reduces the time required for a new node to join the Percona XtraDB Cluster (PXC). Percona XtraDB Cluster uses Percona XtraBackup to … Continued

## Structure detectee

- H2: Old design (until Percona XtraBackup 8.0.33-27):
- H2: New design (from Percona XtraBackup 8.0.33-28)
- H2: Benchmarks
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [20X Faster Backup Preparation With Percona XtraBackup 8.0.33-28!](https://www.percona.com/wp-content/uploads/2026/03/Faster-Backup-Preparation-With-Percona-XtraBackup.jpeg)
- content / image: [Undo log records format](https://www.percona.com/wp-content/uploads/2026/03/Undo_log_records_format-scaled.jpg)
- content / image: [table schema is available within an IBD file](https://www.percona.com/wp-content/uploads/2026/03/SDI_Deserialize-scaled.jpg)
- content / image: [SDI_output-1024x524.png](https://www.percona.com/wp-content/uploads/2026/03/SDI_output-1024x524.png)
- content / image: [SDI_image-1024x619.jpg](https://www.percona.com/wp-content/uploads/2026/03/SDI_image-1024x619.jpg)
- content / image: [Dictionary-cache-OLD-DESIGN-1024x533.jpg](https://www.percona.com/wp-content/uploads/2026/03/Dictionary-cache-OLD-DESIGN-1024x533.jpg)
- content / image: [Dictionary-cache-NEW-DESIGN-1024x772.jpg](https://www.percona.com/wp-content/uploads/2026/03/Dictionary-cache-NEW-DESIGN-1024x772.jpg)
- content / image: [Percona XtraBackup benchmarks](https://www.percona.com/wp-content/uploads/2026/03/Dictionary-cache-500K-tables-prepare-scaled.jpg)
- content / image: [Dictionary-cache-750K-tables-prepare-scaled.jpg](https://www.percona.com/wp-content/uploads/2026/03/Dictionary-cache-750K-tables-prepare-scaled.jpg)
- content / image: [Dictionary-cache-1Million-tables-prepare-scaled.jpg](https://www.percona.com/wp-content/uploads/2026/03/Dictionary-cache-1Million-tables-prepare-scaled.jpg)
- content / image: [Memory_consumption_benchmark.png](https://www.percona.com/wp-content/uploads/2026/03/Memory_consumption_benchmark.png)
- content / image: [Time_taken_benchmark_new.png](https://www.percona.com/wp-content/uploads/2026/03/Time_taken_benchmark_new.png)

## Auteur source

Satya works with Percona Server Engineering team. He is responsible for Percona Server for MySQL, XtraBackup features, bug fixes, etc. He has an overall experience of 18 years in MySQL. He joined Percona in 2018. Before joining Percona, Satya worked with the InnoDB Development team at Oracle for 6 years.
