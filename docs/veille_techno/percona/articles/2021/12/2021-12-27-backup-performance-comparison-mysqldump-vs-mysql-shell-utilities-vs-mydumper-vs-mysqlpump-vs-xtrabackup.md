---
title: 'Backup Performance Comparison: mysqldump vs MySQL Shell Utilities vs mydumper vs mysqlpump vs XtraBackup'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/backup-performance-comparison-mysqldump-vs-mysql-shell-utilities-vs-mydumper-vs-mysqlpump-vs-xtrabackup/
  post_id: 24880
source_author:
  name: Vinicius Grippa
  slug: vinicius-grippa
  url: https://www.percona.com/blog/author/vinicius-grippa/
  website: ''
published_at: '2021-12-27T13:30:13'
published_at_gmt: '2021-12-27T13:30:13'
modified_at: '2026-04-28T15:01:04'
modified_at_gmt: '2026-04-28T15:01:04'
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
- MySQL
- mysql-and-variants
tag_slugs:
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Backup-Performance-Comparison.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Backup Performance Comparison: mysqldump vs MySQL Shell Utilities vs mydumper vs mysqlpump vs XtraBackup

Source: [Percona Blog](https://www.percona.com/blog/backup-performance-comparison-mysqldump-vs-mysql-shell-utilities-vs-mydumper-vs-mysqlpump-vs-xtrabackup/)

Auteur source: [Vinicius Grippa](https://www.percona.com/blog/author/vinicius-grippa/)

Publication: 2021-12-27T13:30:13

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we will compare the performance of performing a backup from a MySQL database using mysqldump, MySQL Shell feature called Instance Dump, mysqlpump, mydumper, and Percona XtraBackup. All these available options are open source and free to use for the entire community. To start, let’s see the results of the test. Benchmark … Continued

## Structure detectee

- H2: Benchmark Results
- H2: Hardware and Software Specs
- H2: Performance Test
- H2: Analyzing the Results
- H3: Useful Resources

## Images et graphiques reperes

- featured / image: [Backup Performance Comparison: mysqldump vs MySQL Shell Utilities vs mydumper vs mysqlpump vs XtraBackup](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Backup-Performance-Comparison.png)
- content / image: [MySQL Backup Performance Comparison](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Backup-Performance-Comparison-300x168.png)
- content / image: [MySQL Backup Results](https://www.percona.com/wp-content/uploads/2026/03/total-3-1024x635.png)
- content / image: [multi-threaded options](https://www.percona.com/wp-content/uploads/2026/03/partial-1024x644.png)

## Auteur source

Vinicius Grippa is a Lead Database Engineer at Percona, an Oracle ACE Director, MySQL Rockstar, and co-author of Learning MySQL. With a Bachelor’s degree in Computer Science and 18 years of experience, he specializes in designing databases for mission-critical applications, focusing on MySQL and MongoDB ecosystems. As part of Percona’s Support team, he has assisted customers in resolving complex database challenges across a wide range of scenarios. An active member of the open-source community, he leads the MySQL User Group in Brazil and engages in knowledge sharing through Slack, Meetups, and international conferences, including FOSDEM, Percona Live, and events across Europe, Asia, and the Americas.
