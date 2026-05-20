---
title: One Billion Tables in MySQL 8.0 with ZFS
source:
  name: Percona Blog
  url: https://www.percona.com/blog/one-billion-tables-in-mysql-8-0-with-zfs/
  post_id: 19509
source_author:
  name: Alexander Rubin
  slug: alexanderrubin
  url: https://www.percona.com/blog/author/alexanderrubin/
  website: http://www.percona.com/blog
published_at: '2018-10-22T14:22:10'
published_at_gmt: '2018-10-22T14:22:10'
modified_at: '2026-05-05T19:24:41'
modified_at_gmt: '2026-05-05T19:24:41'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- Big Data
- MySQL Scalability
- Scalability
tag_slugs:
- big-data
- mysql-scalability
- scalability
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/one-billion-tables-mysql.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# One Billion Tables in MySQL 8.0 with ZFS

Source: [Percona Blog](https://www.percona.com/blog/one-billion-tables-in-mysql-8-0-with-zfs/)

Auteur source: [Alexander Rubin](https://www.percona.com/blog/author/alexanderrubin/)

Publication: 2018-10-22T14:22:10

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The short version I created > one billion InnoDB tables in MySQL 8.0 (tables, not rows) just for fun. Here is the proof: MySQL $ mysql -A Welcome to the MySQL monitor. Commands end with ; or g. Your MySQL connection id is 1425329 Server version: 8.0.12 MySQL Community Server - GPL Copyright (c) 2000, 2018, Oracle and/or its affiliates. All rights reserved. Oracle is a registered trademark of Oracle Corporation and/or its affiliates. Other names may be trademarks of their respective owners. Type 'help;' or 'h' for help. Type 'c' to clear the current input statement. mysql> select count(*) from information_schema.tables; +------------+ | count(*) | +------------+ | 1011570298 | +------------+ 1 row in set (6 hours 57 min 6.31 sec) 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19 20 $ mysql -A Welcome to the MySQL monitor. Commands end with ; or g. Your MySQL connection id is 1425329 Serv...

## Structure detectee

- H2: The short version
- H2: Why does anyone need one billion tables?
- H2: Challenges with one billion InnoDB tables
- H3: Disk space
- H3: Too many tiny files
- H3: Creating tables
- H2: Counting the tables
- H2: Conclusions

## Images et graphiques reperes

- featured / image: [One Billion Tables in MySQL 8.0 with ZFS](https://www.percona.com/wp-content/uploads/2026/03/one-billion-tables-mysql.jpg)
- content / image: [one billion tables MySQL](https://www.percona.com/wp-content/uploads/2026/03/one-billion-tables-mysql-300x204.jpg)

## Auteur source

Alexander joined Percona in 2013. Alexander worked with MySQL since 2000 as DBA and Application Developer. Before joining Percona he was doing MySQL consulting as a principal consultant for over 7 years (started with MySQL AB in 2006, then Sun Microsystems and then Oracle). He has helped many customers design large, scalable and highly available MySQL systems and optimize MySQL performance. Alexander has also helped customers design Big Data stores with Apache Hadoop and related technologies.
