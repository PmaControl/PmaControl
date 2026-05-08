---
title: 'Percona XtraBackup 8.4 Pro: Reduce Server Locking by up to 4300X'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-xtrabackup-8-4-pro-reduce-server-locking-by-up-to-4300x/
  post_id: 29309
source_author:
  name: Satya Bodapati
  slug: satya-bodapati
  url: https://www.percona.com/blog/author/satya-bodapati/
  website: ''
published_at: '2025-03-24T13:50:57'
published_at_gmt: '2025-03-24T13:50:57'
modified_at: '2026-03-26T20:25:39'
modified_at_gmt: '2026-03-26T20:25:39'
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
- InnoDB
- insight for DBAs
- MySQL
- mysql-and-variants
- Percona Server for MySQL
- Percona Software
- Percona XtraBackup
tag_slugs:
- innodb
- insight-for-dbas
- mysql
- mysql-and-variants
- percona-server
- percona-software
- percona-xtrabackup
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraBackup-8.4-Pro-locking.jpg
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona XtraBackup 8.4 Pro: Reduce Server Locking by up to 4300X

Source: [Percona Blog](https://www.percona.com/blog/percona-xtrabackup-8-4-pro-reduce-server-locking-by-up-to-4300x/)

Auteur source: [Satya Bodapati](https://www.percona.com/blog/author/satya-bodapati/)

Publication: 2025-03-24T13:50:57

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

When performing backups, reducing the amount of time your server is locked can significantly improve performance and minimize disruptions. Percona XtraBackup 8.4 Pro introduces improvements in how DDL (Data Definition Language) locks (aka Backup Locks) are managed, allowing for reduced locking during backups. In this post, we’ll explore the impact of these enhancements. TL;DR (Summary) … Continued

## Structure detectee

- H2: TL;DR (Summary)
- H2: Lock-reduction improvements
- H3: Examples of DDL blocking problems:
- H2: Design
- H3: Phase 1: Operations performed without the lock
- H3: Phase 2: Operations performed under the lock
- H2: Performance benchmarks
- H3: Backup to local disk
- H3: Backup to Amazon S3
- H2: Reduced replication lag

## Images et graphiques reperes

- featured / image: [Percona XtraBackup 8.4 Pro: Reduce Server Locking by up to 4300X](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraBackup-8.4-Pro-locking.jpg)
- content / image: [Percona XtraBackup to local disk](https://www.percona.com/wp-content/uploads/2026/03/reduced_lock_local_new-1024x615.png)
- content / image: [Percona XtraBackup to Amazon S3](https://www.percona.com/wp-content/uploads/2026/03/reduced_lock_amazon_s3_new-1024x615.png)
- content / image: [mysql-performance-tuning-1.png](https://www.percona.com/wp-content/uploads/2026/03/mysql-performance-tuning-1.png)

## Auteur source

Satya works with Percona Server Engineering team. He is responsible for Percona Server for MySQL, XtraBackup features, bug fixes, etc. He has an overall experience of 18 years in MySQL. He joined Percona in 2018. Before joining Percona, Satya worked with the InnoDB Development team at Oracle for 6 years.
