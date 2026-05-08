---
title: Join Optimizations in MySQL 5.6 and MariaDB 5.5
source:
  name: Percona Blog
  url: https://www.percona.com/blog/join-optimizations-in-mysql-5-6-and-mariadb-5-5/
  post_id: 3449
source_author:
  name: Ovais Tariq
  slug: ovaistariq
  url: https://www.percona.com/blog/author/ovaistariq/
  website: http://www.percona.com/blog/
published_at: '2012-04-05T04:02:32'
published_at_gmt: '2012-04-05T04:02:32'
modified_at: '2026-04-28T21:34:51'
modified_at_gmt: '2026-04-28T21:34:51'
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
- batched key access
- BKA
- hash join
- io
- JOIN Performance
- join_buffer
- MariaDB
- mrr_buffer
- multi range read
- MySQL Performance
- nested loop join
- Optimizer
- random read
- read_rnd_buffer
- sequential read
tag_slugs:
- batched-key-access
- bka
- hash-join
- io
- join-performance
- join_buffer
- mariadb
- mrr_buffer
- multi-range-read
- mysql-performance
- nested-loop-join
- optimizer
- random-read
- read_rnd_buffer
- sequential-read
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/oimg-14.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Join Optimizations in MySQL 5.6 and MariaDB 5.5

Source: [Percona Blog](https://www.percona.com/blog/join-optimizations-in-mysql-5-6-and-mariadb-5-5/)

Auteur source: [Ovais Tariq](https://www.percona.com/blog/author/ovaistariq/)

Publication: 2012-04-05T04:02:32

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This is the third blog post in the series of blog posts leading up to the talk comparing the optimizer enhancements in MySQL 5.6 and MariaDB 5.5. This blog post is targeted at the join related optimizations introduced in the optimizer. These optimizations are available in both MySQL 5.6 and MariaDB 5.5, and MariaDB 5.5 … Continued

## Structure detectee

- H3: Batched Key Access
- H3: Key-ordered Scan for BKA
- H3: Hash Join
- H3: Benchmark results
- H4: In-memory workload
- H4: IO bound workload
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Join Optimizations in MySQL 5.6 and MariaDB 5.5](https://www.percona.com/wp-content/uploads/2026/03/oimg-14.png)
- content / image: [oimg-15.png](https://www.percona.com/wp-content/uploads/2026/03/oimg-15.png)
