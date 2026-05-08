---
title: Binlog and Replication Improvements in Percona Server for MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/binlog-and-replication-improvements-in-percona-server-for-mysql/
  post_id: 17990
source_author:
  name: Dmitriy Kostiuk
  slug: dmitriy-kostiuk
  url: https://www.percona.com/blog/author/dmitriy-kostiuk/
  website: ''
published_at: '2018-04-17T00:13:56'
published_at_gmt: '2018-04-17T00:13:56'
modified_at: '2026-03-20T21:40:20'
modified_at_gmt: '2026-03-20T21:40:20'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MariaDB
- MySQL
matched_filters:
- category:mariadb:1281
- category:mysql:83
categories:
- Insight for DBAs
- MariaDB
- MySQL
category_slugs:
- insight-for-dbas
- mariadb
- mysql
tags:
- configuration
- InnoDB
- MariaDB
- MariaDB 10.2
- MyISAM
- MySQL
- MySQL 5.7
tag_slugs:
- configuration
- innodb
- mariadb
- mariadb-10-2
- myisam
- mysql
- mysql-5-7
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-Server-for-MySQL.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Binlog and Replication Improvements in Percona Server for MySQL

Source: [Percona Blog](https://www.percona.com/blog/binlog-and-replication-improvements-in-percona-server-for-mysql/)

Auteur source: [Dmitriy Kostiuk](https://www.percona.com/blog/author/dmitriy-kostiuk/)

Publication: 2018-04-17T00:13:56

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Due to continuous development and improvement, Percona Server for MySQL incorporates a number of improvements related to binary log handling and replication. This results in replication specifics, distinguishing it from MySQL Server. Temporary tables and mixed logging format Summary of the fix: As soon as some statement involving temporary tables was met when using a mixed binlog format, MySQL … Continued

## Structure detectee

- H3: Temporary tables and mixed logging format
- H3: Temporary table drops and binloging on GTID-enabled server
- H3: Safety of statements with a LIMIT clause
- H3: Performance improvements
- H3: Current status of fixes

## Images et graphiques reperes

- featured / image: [Binlog and Replication Improvements in Percona Server for MySQL](https://www.percona.com/wp-content/uploads/2026/03/Percona-Server-for-MySQL.jpg)
- content / image: [Percona Server for MySQL](https://www.percona.com/wp-content/uploads/2026/03/Percona-Server-for-MySQL-300x300.jpg)

## Auteur source

Dmitriy joined Percona as a Technical Writer in 2017. He has a long list of open source related activities targeted at software development, writing and managing technical documentation, education, and the community.
