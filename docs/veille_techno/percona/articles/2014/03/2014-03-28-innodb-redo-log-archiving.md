---
title: Innodb redo log archiving
source:
  name: Percona Blog
  url: https://www.percona.com/blog/innodb-redo-log-archiving/
  post_id: 7901
source_author:
  name: Vlad Lesin
  slug: vlad-lesin
  url: https://www.percona.com/blog/author/vlad-lesin/
  website: ''
published_at: '2014-03-28T13:00:12'
published_at_gmt: '2014-03-28T13:00:12'
modified_at: '2026-05-05T21:56:46'
modified_at_gmt: '2026-05-05T21:56:46'
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
- Insight for Developers
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
- percona-software
tags:
- archived logs
- incremental backups
- InnoDB
- log archiving
- Percona Server for MySQL
- Percona XtraDB Cluster
tag_slugs:
- archived-logs
- incremental-backups
- innodb
- log-archiving
- percona-server
- percona-xtradb-cluster
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Innodb-redo-log-archiving.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Innodb redo log archiving

Source: [Percona Blog](https://www.percona.com/blog/innodb-redo-log-archiving/)

Auteur source: [Vlad Lesin](https://www.percona.com/blog/author/vlad-lesin/)

Publication: 2014-03-28T13:00:12

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona Server 5.6.11-60.3 introduces a new “log archiving” feature. Percona XtraBackup 2.1.5 supports “apply archived logs.” What does it mean and how it can be used? Percona products propose three kinds of incremental backups. The first is full scan of data files and comparison the data with backup data to find some delta. This approach … Continued

## Structure detectee

- H2: What is the innodb log and how it is written?
- H3: Log files
- H3: Log blocks
- H3: How log blocks are stored in memory and on disk?
- H4: Global log object and log buffer
- H4: Where log records come from?
- H4: Writing log buffer to disk: innodb_flush_log_at_trx_commit is 1 or 2.
- H4: Writing log buffer to disk: innodb_flush_log_at_trx_commit is equal to 0
- H4: Special cases for logs flushing
- H4: If log files are treated as circular buffer what happens when the buffer is overflown?
- H2: How archived logs are written by server.
- H2: Logs recovery process, how it is started and works inside. Archived logs applying.
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Innodb redo log archiving](https://www.percona.com/wp-content/uploads/2026/03/Innodb-redo-log-archiving.jpg)

## Auteur source

Vladislav Lesin is a software engineer at Percona, where he joined in April 2012. Before coming to Percona he worked on improving performance and reliability of high load projects with LAMP architecture. His work consisted in developing fast servers and modules with C and C++, projects state monitoring, searching bottlenecks, open source projects patching including nginx, memcache, sphinx, php, ejabberd. He took part in developing not only server-side applications, but desktop and mobile ones too. Also he had experience in project/product management, hiring, partners negotiations. Before that he worked in several IT companies where he developed desktop applications on C++ for such areas as industrial automation, parallel computing, media production. He holds a Master's Degree in Technique and Technology from Tula State University. Now he lives in Tula city with his wife and daughter.
