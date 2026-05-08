---
title: Performance improvements in Percona XtraDB Cluster 5.7.17-29.20
source:
  name: Percona Blog
  url: https://www.percona.com/blog/performance-improvements-percona-xtradb-cluster-5-7-17/
  post_id: 16733
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2017-04-19T13:50:26'
published_at_gmt: '2017-04-19T13:50:26'
modified_at: '2026-03-20T21:23:36'
modified_at_gmt: '2026-03-20T21:23:36'
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
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- galera
- group replication
- High Availability
- MySQL
- Percona XtraDB Cluster
tag_slugs:
- galera
- group-replication
- high-availability
- mysql
- percona-xtradb-cluster
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-e1492551648372.png
image_count: 5
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Performance improvements in Percona XtraDB Cluster 5.7.17-29.20

Source: [Percona Blog](https://www.percona.com/blog/performance-improvements-percona-xtradb-cluster-5-7-17/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2017-04-19T13:50:26

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In our latest release of Percona XtraDB Cluster, we’ve introduced major performance improvements to the MySQLwrite-set replication layer. In this post, we want to show what these improvements look like. For the test, we used the sysbench OLTP_RW, UPDATE_KEY and UPDATE_NOKEY workloads with 100 tables, 4mln rows each, which gives about 100GB of datasize. In all … Continued

## Images et graphiques reperes

- featured / image: [Performance improvements in Percona XtraDB Cluster 5.7.17-29.20](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-e1492551648372.png)
- content / image: [Percona XtraDB Cluster](https://www.percona.com/wp-content/uploads/2026/03/pl17.blog_.chart1_.v1.png)
- content / image: [Percona XtraDB Cluster](https://www.percona.com/wp-content/uploads/2026/03/pl17.blog_.chart1_1.v1.large_-1024x488.png)
- content / image: [Percona XtraDB Cluster](https://www.percona.com/wp-content/uploads/2026/03/pl17.blog_.chart2_.sync1_.v1.png)
- content / image: [Percona XtraDB Cluster](https://www.percona.com/wp-content/uploads/2026/03/pl17.blog_.chart3_.sync0_.v1.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
