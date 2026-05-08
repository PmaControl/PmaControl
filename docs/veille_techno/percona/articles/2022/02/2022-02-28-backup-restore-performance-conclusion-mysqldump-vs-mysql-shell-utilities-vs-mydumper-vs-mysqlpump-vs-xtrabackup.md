---
title: 'Backup/Restore Performance Conclusion: mysqldump vs MySQL Shell Utilities vs mydumper vs mysqlpump vs XtraBackup'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/backup-restore-performance-conclusion-mysqldump-vs-mysql-shell-utilities-vs-mydumper-vs-mysqlpump-vs-xtrabackup/
  post_id: 25449
source_author:
  name: Vinicius Grippa
  slug: vinicius-grippa
  url: https://www.percona.com/blog/author/vinicius-grippa/
  website: ''
published_at: '2022-02-28T13:53:56'
published_at_gmt: '2022-02-28T13:53:56'
modified_at: '2026-03-23T19:02:44'
modified_at_gmt: '2026-03-23T19:02:44'
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
- Percona XtraBackup
tag_slugs:
- mysql
- percona-xtrabackup
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Restore-Backup-Comparison.png
image_count: 7
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Backup/Restore Performance Conclusion: mysqldump vs MySQL Shell Utilities vs mydumper vs mysqlpump vs XtraBackup

Source: [Percona Blog](https://www.percona.com/blog/backup-restore-performance-conclusion-mysqldump-vs-mysql-shell-utilities-vs-mydumper-vs-mysqlpump-vs-xtrabackup/)

Auteur source: [Vinicius Grippa](https://www.percona.com/blog/author/vinicius-grippa/)

Publication: 2022-02-28T13:53:56

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

A little bit ago, I released a blog post comparing the backup performance of different MySQL tools such as mysqldump, the MySQL Shell feature called Instance Dump, mysqlpump, mydumper, and Percona XtraBackup. You can find the first analysis here: Backup Performance Comparison: mysqldump vs. MySQL Shell Utilities vs. mydumper vs. mysqlpump vs. XtraBackup However, we know … Continued

## Structure detectee

- H2: Benchmark Results
- H2: Analyzing The Results
- H2: Hardware and Software Specs
- H2: Useful Resources

## Images et graphiques reperes

- featured / image: [Backup/Restore Performance Conclusion: mysqldump vs MySQL Shell Utilities vs mydumper vs mysqlpump vs XtraBackup](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Restore-Backup-Comparison.png)
- content / image: [MySQL Restore Backup Comparison](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Restore-Backup-Comparison-300x168.png)
- content / image: [MySQL Backup and Restore](https://www.percona.com/wp-content/uploads/2026/03/time_total-1024x769.png)
- content / image: [time_total_no_mysqldump-1024x773.png](https://www.percona.com/wp-content/uploads/2026/03/time_total_no_mysqldump-1024x773.png)
- content / image: [MySQL Backup Size](https://www.percona.com/wp-content/uploads/2026/03/backup_size-1024x533.png)
- content / image: [Time to execute MySQL backup](https://www.percona.com/wp-content/uploads/2026/03/time_bkp-1024x772.png)
- content / image: [Time to restore MySQL](https://www.percona.com/wp-content/uploads/2026/03/time_restore-1024x660.png)

## Auteur source

Vinicius Grippa is a Lead Database Engineer at Percona, an Oracle ACE Director, MySQL Rockstar, and co-author of Learning MySQL. With a Bachelor’s degree in Computer Science and 18 years of experience, he specializes in designing databases for mission-critical applications, focusing on MySQL and MongoDB ecosystems. As part of Percona’s Support team, he has assisted customers in resolving complex database challenges across a wide range of scenarios. An active member of the open-source community, he leads the MySQL User Group in Brazil and engages in knowledge sharing through Slack, Meetups, and international conferences, including FOSDEM, Percona Live, and events across Europe, Asia, and the Americas.
