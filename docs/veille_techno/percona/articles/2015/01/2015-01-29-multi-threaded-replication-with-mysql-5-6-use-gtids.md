---
title: 'Multi-threaded replication with MySQL 5.6: Use GTIDs!'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/multi-threaded-replication-with-mysql-5-6-use-gtids/
  post_id: 9016
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2015-01-29T08:00:26'
published_at_gmt: '2015-01-29T08:00:26'
modified_at: '2026-05-04T22:32:28'
modified_at_gmt: '2026-05-04T22:32:28'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- XtraBackup
matched_filters:
- category:mysql:83
- search:xtrabackup
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- FOSDEM2015
- GTIDs
- MTS
- Multi-threaded replication
- Multi-Threaded Slave
- MySQL
- Primary
- Stephane Combaudon
tag_slugs:
- fosdem2015
- gtids
- mts
- multi-threaded-replication
- multi-threaded-slave
- mysql
- primary
- stephane-combaudon
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Multi-threaded replication with MySQL 5.6: Use GTIDs!

Source: [Percona Blog](https://www.percona.com/blog/multi-threaded-replication-with-mysql-5-6-use-gtids/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2015-01-29T08:00:26

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MySQL 5.6 allows you to execute replicated events in parallel as long as data is split across several databases. This feature is named “Multi-Threaded Slave” (MTS) and it is easy to enable by setting slave_parallel_workers to a > 1 value. However if you decide to use MTS without GTIDs, you may run into annoying issues. … Continued

## Structure detectee

- H2: Skipping replication errors
- H2: Backups
- H2: GTIDs to the rescue!
- H2: Conclusion

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
