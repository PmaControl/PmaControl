---
title: Parallel Query for MySQL with Shard-Query
source:
  name: Percona Blog
  url: https://www.percona.com/blog/parallel-query-mysql-shard-query/
  post_id: 8079
source_author:
  name: Justin Swanhart
  slug: justin-swanhart
  url: https://www.percona.com/blog/author/justin-swanhart/
  website: http://www.percona.com/about-us/our-team/justin-swanhart/
published_at: '2014-05-01T10:00:03'
published_at_gmt: '2014-05-01T10:00:03'
modified_at: '2026-05-05T17:43:15'
modified_at_gmt: '2026-05-05T17:43:15'
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
- aggregation
- Justin Swanhart
- parallel query
- parallelism
- Performance
- shard-query
tag_slugs:
- aggregation
- cap-justin-swanhart
- parallel-query
- parallelism
- performance
- shard-query
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Parallel Query for MySQL with Shard-Query

Source: [Percona Blog](https://www.percona.com/blog/parallel-query-mysql-shard-query/)

Auteur source: [Justin Swanhart](https://www.percona.com/blog/author/justin-swanhart/)

Publication: 2014-05-01T10:00:03

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

While Shard-Query can work over multiple nodes, this blog post focuses on using Shard-Query with a single node. Shard-Query can add parallelism to queries which use partitioned tables. Very large tables can often be partitioned fairly easily. Shard-Query can leverage partitioning to add paralellism, because each partition can be queried independently. Because MySQL 5.6 supports … Continued

## Auteur source

Justin is a former Principal Support Engineer on the support team. In the past, he was a trainer at Percona and a consultant. Justin also created and maintains Shard-Query, a middleware tool for sharding and parallel query execution and Flexviews, a tool for materialized views for MySQL. Prior to working at Percona Justin consulted for Proven Scaling, was a backend engineer at Yahoo! and a database administrator at Smule and Gazillion games.
