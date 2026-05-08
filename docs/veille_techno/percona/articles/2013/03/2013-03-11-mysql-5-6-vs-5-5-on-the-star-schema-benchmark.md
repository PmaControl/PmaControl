---
title: MySQL 5.6 vs MySQL 5.5 and the Star Schema Benchmark
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-5-6-vs-5-5-on-the-star-schema-benchmark/
  post_id: 6670
source_author:
  name: Justin Swanhart
  slug: justin-swanhart
  url: https://www.percona.com/blog/author/justin-swanhart/
  website: http://www.percona.com/about-us/our-team/justin-swanhart/
published_at: '2013-03-11T13:19:35'
published_at_gmt: '2013-03-11T13:19:35'
modified_at: '2026-05-05T22:36:40'
modified_at_gmt: '2026-05-05T22:36:40'
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
- InnoDB plugin
- Justin Swanhart
- MySQL 5.5
- MySQL 5.6
- Star Schema Benchmark (SSB)
- sysbench
tag_slugs:
- innodb-plugin
- cap-justin-swanhart
- mysql-5-5
- mysql-5-6
- star-schema-benchmark-ssb
- sysbench
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Database-Development-Planning.jpg
image_count: 2
graph_or_chart_count: 2
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL 5.6 vs MySQL 5.5 and the Star Schema Benchmark

Source: [Percona Blog](https://www.percona.com/blog/mysql-5-6-vs-5-5-on-the-star-schema-benchmark/)

Auteur source: [Justin Swanhart](https://www.percona.com/blog/author/justin-swanhart/)

Publication: 2013-03-11T13:19:35

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

So far most of the benchmarks posted about MySQL 5.6 use the sysbench OLTP workload. I wanted to test a set of queries which, unlike sysbench, utilize joins. I also wanted an easily reproducible set of data which is more rich than the simple sysbench table. The Star Schema Benchmark (SSB) seems ideal for this. … Continued

## Structure detectee

- H2: Benchmark Details:
- H3: Test Environment
- H3: Rationale:
- H3: Test Server:
- H3: Why is the MySQL 5.6.10 with default settings test significantly slower than MySQL 5.5.30 in repeat runs?
- H3: Digging into why innodb_old_blocks_time change the performance?

## Images et graphiques reperes

- featured / graph_or_chart: [MySQL 5.6 vs MySQL 5.5 and the Star Schema Benchmark](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Database-Development-Planning.jpg)
- content / graph_or_chart: [MySQL 5.6 vs MySQL 5.5 and the Star Schema Benchmark](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Database-Development-Planning-300x199.jpg)
  Caption: MySQL 5.6 vs MySQL 5.5 & the Star Schema Benchmark

## Auteur source

Justin is a former Principal Support Engineer on the support team. In the past, he was a trainer at Percona and a consultant. Justin also created and maintains Shard-Query, a middleware tool for sharding and parallel query execution and Flexviews, a tool for materialized views for MySQL. Prior to working at Percona Justin consulted for Proven Scaling, was a backend engineer at Yahoo! and a database administrator at Smule and Gazillion games.
