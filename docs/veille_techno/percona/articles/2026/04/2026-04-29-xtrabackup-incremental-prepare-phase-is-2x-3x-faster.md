---
title: XtraBackup incremental prepare phase is 2x-3x faster!
source:
  name: Percona Blog
  url: https://www.percona.com/blog/xtrabackup-incremental-prepare-phase-is-2x-3x-faster/
  post_id: 44623
source_author:
  name: Satya Bodapati
  slug: satya-bodapati
  url: https://www.percona.com/blog/author/satya-bodapati/
  website: ''
published_at: '2026-04-29T18:52:44'
published_at_gmt: '2026-04-29T18:52:44'
modified_at: '2026-04-29T18:52:44'
modified_at_gmt: '2026-04-29T18:52:44'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- XtraBackup
matched_filters:
- category:mysql:83
- category:xtrabackup:3845
- search:percona-monitoring-and-management
- search:percona-xtrabackup
- search:pmm
- search:xtrabackup
- tag:percona-xtrabackup:330
- tag:xtrabackup:153
categories:
- MySQL
- Open Source
- Xtrabackup
category_slugs:
- mysql
- open-source
- xtrabackup
tags:
- .delta file
- apply-log-only
- incremental
- incremental backup
- MySQL Backup
- MySQL Backup tools
- Percona XtraBackup
- prepare
- xtrabackup
tag_slugs:
- delta-file
- apply-log-only
- incremental
- incremental-backup
- mysql-backup
- mysql-backup-tools
- percona-xtrabackup
- prepare
- xtrabackup
featured_image_url: https://www.percona.com/wp-content/uploads/2026/04/prepare_performance_plot-scaled.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# XtraBackup incremental prepare phase is 2x-3x faster!

Source: [Percona Blog](https://www.percona.com/blog/xtrabackup-incremental-prepare-phase-is-2x-3x-faster/)

Auteur source: [Satya Bodapati](https://www.percona.com/blog/author/satya-bodapati/)

Publication: 2026-04-29T18:52:44

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

TL;DR Percona XtraBackup is a 100% open-source backup solution for Percona Server for MySQL and MySQL®. It is designed for high-availability environments, performing online, non-blocking, and highly secure backups of transactional systems without interrupting your production traffic. While full backups work for small databases, large-scale systems rely on incremental backups to save space and time. … Continued

## Structure detectee

- H2: TL;DR
- H2: The Incremental Backup Workflow
- H3: 1. Creating the Backups
- H3: 2. Preparing the Backups
- H2: The Improvement: Parallel Incremental Delta Apply
- H2: Benchmarks
- H2: Disk Utilization with XtraBackup prepare using --parallel=1 vs --parallel=64
- H3: With --parallel=1
- H3: With --parallel=64
- H2: Results from the bug reporter

## Images et graphiques reperes

- featured / image: [XtraBackup incremental prepare phase is 2x-3x faster!](https://www.percona.com/wp-content/uploads/2026/04/prepare_performance_plot-scaled.png)
- content / image: [xtrabackup prepare performance](https://www.percona.com/wp-content/uploads/2026/04/prepare_performance_plot-1024x640.png)
- content / image: [xtrabackup incremental disk IOPs with --parllel=1](https://www.percona.com/wp-content/uploads/2026/04/parallel_00.png)
- content / image: [xtrabackup incremental delta prepare performance with parallel 64](https://www.percona.com/wp-content/uploads/2026/04/parallel_64_11.png)

## Auteur source

Satya works with Percona Server Engineering team. He is responsible for Percona Server for MySQL, XtraBackup features, bug fixes, etc. He has an overall experience of 18 years in MySQL. He joined Percona in 2018. Before joining Percona, Satya worked with the InnoDB Development team at Oracle for 6 years.
