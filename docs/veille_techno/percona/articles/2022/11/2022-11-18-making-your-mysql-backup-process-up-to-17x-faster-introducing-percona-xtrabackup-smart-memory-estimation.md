---
title: Making Your MySQL Backup Process up to 17X Faster – Introducing Percona XtraBackup Smart Memory Estimation
source:
  name: Percona Blog
  url: https://www.percona.com/blog/making-your-mysql-backup-process-up-to-17x-faster-introducing-percona-xtrabackup-smart-memory-estimation/
  post_id: 26256
source_author:
  name: Marcelo Altmann
  slug: marcelo-altmann
  url: https://www.percona.com/blog/author/marcelo-altmann/
  website: https://blog.marceloaltmann.com
published_at: '2022-11-18T14:32:28'
published_at_gmt: '2022-11-18T14:32:28'
modified_at: '2026-03-26T20:30:43'
modified_at_gmt: '2026-03-26T20:30:43'
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
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraBackup-Smart-Memory-Estimation.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Making Your MySQL Backup Process up to 17X Faster – Introducing Percona XtraBackup Smart Memory Estimation

Source: [Percona Blog](https://www.percona.com/blog/making-your-mysql-backup-process-up-to-17x-faster-introducing-percona-xtrabackup-smart-memory-estimation/)

Auteur source: [Marcelo Altmann](https://www.percona.com/blog/author/marcelo-altmann/)

Publication: 2022-11-18T14:32:28

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Taking a MySQL backup using Percona XtraBackup (PXB) consists of basically two steps: 1) take the backup and 2) prepare the backup. Briefly speaking, taking a backup means that PXB will copy all of the files from your instance and transfer them to another location. While it does the copy, it spawns a thread that … Continued

## Structure detectee

- H2: Memory usage
- H2: Motivation
- H2: Smart Memory Estimation
- H2: Benchmarks
- H2: Summary

## Images et graphiques reperes

- featured / image: [Making Your MySQL Backup Process up to 17X Faster – Introducing Percona XtraBackup Smart Memory Estimation](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraBackup-Smart-Memory-Estimation.png)
- content / image: [Percona XtraBackup Smart Memory Estimation](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraBackup-Smart-Memory-Estimation-300x157.png)
- content / image: [pxb_smart_memory_estimation-1.png](https://www.percona.com/wp-content/uploads/2026/03/pxb_smart_memory_estimation-1.png)
- content / image: [Percona XtraBackup time to run](https://www.percona.com/wp-content/uploads/2026/03/Time-to-run-prepare-in-seconds.png)

## Auteur source

Marcelo Altmann is a C++ Software Engineer working on MySQL related products. At Percona he has also worked as a Senior Support Engineer and a Tech Lead of the Support Team. Prior to joining Percona , he worked as a MySQL DBA at Ireland's CCTLD, and worked as a DBA/PHP developer in Brazil. He also blogs about other MySQL related stuff at his personal blog.
