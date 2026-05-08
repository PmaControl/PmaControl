---
title: Index Condition Pushdown in MySQL 5.6 and MariaDB 5.5 and its performance impact
source:
  name: Percona Blog
  url: https://www.percona.com/blog/index-condition-pushdown-in-mysql-5-6-and-mariadb-5-5-and-its-performance-impact/
  post_id: 3415
source_author:
  name: Ovais Tariq
  slug: ovaistariq
  url: https://www.percona.com/blog/author/ovaistariq/
  website: http://www.percona.com/blog/
published_at: '2012-03-12T18:21:03'
published_at_gmt: '2012-03-12T18:21:03'
modified_at: '2026-05-04T21:40:55'
modified_at_gmt: '2026-05-04T21:40:55'
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
- Insight for DBAs
- MySQL
category_slugs:
- benchmarks
- insight-for-dbas
- mysql
tags:
- Benchmarks
- InnoDB
- IO Performance
- MariaDB
- MySQL
- MySQL Indexes
- MySQL Performance
- Optimizer
tag_slugs:
- benchmarks
- innodb
- io-performance
- mariadb
- mysql
- mysql-indexes
- mysql-performance
- optimizer
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/oimg-10.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Index Condition Pushdown in MySQL 5.6 and MariaDB 5.5 and its performance impact

Source: [Percona Blog](https://www.percona.com/blog/index-condition-pushdown-in-mysql-5-6-and-mariadb-5-5-and-its-performance-impact/)

Auteur source: [Ovais Tariq](https://www.percona.com/blog/author/ovaistariq/)

Publication: 2012-03-12T18:21:03

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I have been working with Peter in preparation for the talk comparing the optimizer enhancements in MySQL 5.6 and MariaDB 5.5. We are taking a look at and benchmarking optimizer enhancements one by one. So in the same way this blog post is aimed at a new optimizer enhancement Index Condition Pushdown (ICP). Its available … Continued

## Structure detectee

- H3: Index Condition Pushdown
- H3: Benchmark results
- H4: In-memory workload
- H4: IO bound workload
- H4: MySQL Status Counters
- H3: Other Observations
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Index Condition Pushdown in MySQL 5.6 and MariaDB 5.5 and its performance impact](https://www.percona.com/wp-content/uploads/2026/03/oimg-10.png)
- content / image: [oimg-11.png](https://www.percona.com/wp-content/uploads/2026/03/oimg-11.png)
