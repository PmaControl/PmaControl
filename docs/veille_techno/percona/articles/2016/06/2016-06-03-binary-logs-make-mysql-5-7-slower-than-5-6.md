---
title: MySQL 5.7 By Default 1/3rd Slower Than 5.6 When Using Binary Logs
source:
  name: Percona Blog
  url: https://www.percona.com/blog/binary-logs-make-mysql-5-7-slower-than-5-6/
  post_id: 15264
source_author:
  name: Roel Van de Paar
  slug: roel_van_de_paar
  url: https://www.percona.com/blog/author/roel_van_de_paar/
  website: http://au.linkedin.com/in/roelvandepaar
published_at: '2016-06-03T16:11:22'
published_at_gmt: '2016-06-03T16:11:22'
modified_at: '2026-03-20T21:03:52'
modified_at_gmt: '2026-03-20T21:03:52'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MySQL
category_slugs:
- mysql
tags:
- MySQL 5.7
- MySQL Performance
- Performance
- Roel Van de Paar
- sync_binlog
tag_slugs:
- mysql-5-7
- mysql-performance
- performance
- roel-van-de-paar
- sync_binlog
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/binary-logs-make-MySQL-5.7-slower.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL 5.7 By Default 1/3rd Slower Than 5.6 When Using Binary Logs

Source: [Percona Blog](https://www.percona.com/blog/binary-logs-make-mysql-5-7-slower-than-5-6/)

Auteur source: [Roel Van de Paar](https://www.percona.com/blog/author/roel_van_de_paar/)

Publication: 2016-06-03T16:11:22

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Researching a performance issue, we came to a startling discovery: MySQL 5.7 + binlogs is by default 37-45% slower than MySQL 5.6 + binlogs when otherwise using the default MySQL settings. Test server MySQL versions used:i7, 8 threads, SSD, Centos 7.2.1511mysql-5.6.30-linux-glibc2.5-x86_64mysql-5.7.12-linux-glibc2.5-x86_64 mysqld –options: -- no - defaults -- log - bin = mysql - bin -- server - id = 2 Run details:Sysbench version 0.5, 4 threads, socket file connection Sysbench Prepare: sysbench --test=/usr/share/doc/sysbench/tests/db/parallel_prepare.lua --oltp-auto-inc=off --mysql-engine-trx=yes --mysql-table-engine=innodb --oltp_table_size=1000000 --oltp_tables_count=1 --mysql-db=test --mysql-user=root --db-driver=mysql --mysql-socket=/path_to_socket_file/your_socket_file.sock prepare 1 sysbench -- test = / usr / share / doc / sysbench / tests / db / parallel_prepare . lua -- oltp -...

## Images et graphiques reperes

- featured / image: [MySQL 5.7 By Default 1/3rd Slower Than 5.6 When Using Binary Logs](https://www.percona.com/wp-content/uploads/2026/03/binary-logs-make-MySQL-5.7-slower.png)
- content / image: [binary logs make MySQL 5.7 slower](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Server-5.6-vs-MySQL-Server-5.7.7-with-binglogs-enabled.png)

## Auteur source

Roel leads Percona's QA team. Before coming to Percona, he contributed significantly to the QA infrastructure at Oracle. Roel has a varied background in IT, backed up by many industry leading certifications. He also enjoys time with God, his wife and 5 children, or heading into nature. Roel tweets at @RoelVandePaar
