---
title: Estimating potential for MySQL 5.7 parallel replication
source:
  name: Percona Blog
  url: https://www.percona.com/blog/estimating-potential-for-mysql-5-7-parallel-replication/
  post_id: 14629
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2016-02-10T18:19:36'
published_at_gmt: '2016-02-10T18:19:36'
modified_at: '2026-05-05T18:00:57'
modified_at_gmt: '2026-05-05T18:00:57'
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
- parallel replication
- PERFORMANCE_SCHEMA
tag_slugs:
- mysql-5-7
- parallel-replication
- performance_schema
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/parallel-replication.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Estimating potential for MySQL 5.7 parallel replication

Source: [Percona Blog](https://www.percona.com/blog/estimating-potential-for-mysql-5-7-parallel-replication/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2016-02-10T18:19:36

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Unlike MySQL 5.6, where parallel replication can only be used when replicas have several schemas, MySQL 5.7 replicas can read binlog group commit information coming from the master to replicate transactions in parallel even when a single schema is used. Now the question is: how many replication threads should you use? A simple benchmark Let’s … Continued

## Structure detectee

- H2: A simple benchmark
- H2: Some instrumentation with performance_schema
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Estimating potential for MySQL 5.7 parallel replication](https://www.percona.com/wp-content/uploads/2026/03/parallel-replication.png)
- content / image: [parallel replication](https://www.percona.com/wp-content/uploads/2026/03/parallel-replication-300x252.png)
- content / image: [lag_all](https://www.percona.com/wp-content/uploads/2026/03/lag_all-300x150.png)

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
