---
title: MySQL and the SSB – Part 2 – MyISAM vs InnoDB low concurrency
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-and-the-ssb-part-2-myisam-vs-innodb-low-concurrency/
  post_id: 6959
source_author:
  name: Justin Swanhart
  slug: justin-swanhart
  url: https://www.percona.com/blog/author/justin-swanhart/
  website: http://www.percona.com/about-us/our-team/justin-swanhart/
published_at: '2013-05-22T13:50:16'
published_at_gmt: '2013-05-22T13:50:16'
modified_at: '2026-05-05T16:51:04'
modified_at_gmt: '2026-05-05T16:51:04'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Benchmarks
- MySQL
category_slugs:
- benchmarks
- mysql
tags:
- InnoDB
- Justin Swanhart
- Low Concurrency
- MyISAM
- MySQL
- SSB
- star schema
- Star Schema Benchmark
tag_slugs:
- innodb
- cap-justin-swanhart
- low-concurrency
- myisam
- mysql
- ssb
- star-schema
- star-schema-benchmark
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/image009.png
image_count: 8
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL and the SSB – Part 2 – MyISAM vs InnoDB low concurrency

Source: [Percona Blog](https://www.percona.com/blog/mysql-and-the-ssb-part-2-myisam-vs-innodb-low-concurrency/)

Auteur source: [Justin Swanhart](https://www.percona.com/blog/author/justin-swanhart/)

Publication: 2013-05-22T13:50:16

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This blog post is part two in what is now a continuing series on the Star Schema Benchmark. In my previous blog post I compared MySQL 5.5.30 to MySQL 5.6.10, both with default settings using only the InnoDB storage engine. In my testing I discovered that innodb_old_blocks_time had an effect on performance of the benchmark. … Continued

## Images et graphiques reperes

- featured / image: [MySQL and the SSB – Part 2 – MyISAM vs InnoDB low concurrency](https://www.percona.com/wp-content/uploads/2026/03/image009.png)
- content / image: [image001](https://www.percona.com/wp-content/uploads/2026/03/image001.png)
- content / image: [image012](https://www.percona.com/wp-content/uploads/2026/03/image012.png)
- content / image: [image003](https://www.percona.com/wp-content/uploads/2026/03/image003.png)
- content / image: [image014](https://www.percona.com/wp-content/uploads/2026/03/image014.png)
- content / image: [image005](https://www.percona.com/wp-content/uploads/2026/03/image005.png)
- content / image: [image016](https://www.percona.com/wp-content/uploads/2026/03/image016.png)
- content / image: [image007](https://www.percona.com/wp-content/uploads/2026/03/image007.png)

## Auteur source

Justin is a former Principal Support Engineer on the support team. In the past, he was a trainer at Percona and a consultant. Justin also created and maintains Shard-Query, a middleware tool for sharding and parallel query execution and Flexviews, a tool for materialized views for MySQL. Prior to working at Percona Justin consulted for Proven Scaling, was a backend engineer at Yahoo! and a database administrator at Smule and Gazillion games.
