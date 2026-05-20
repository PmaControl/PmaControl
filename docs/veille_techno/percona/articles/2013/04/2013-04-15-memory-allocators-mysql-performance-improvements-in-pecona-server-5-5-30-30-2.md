---
title: 'Memory allocators: MySQL performance improvements in Percona Server 5.5.30-30.2'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/memory-allocators-mysql-performance-improvements-in-pecona-server-5-5-30-30-2/
  post_id: 6806
source_author:
  name: Alexey Stroganov
  slug: alexey-stroganov
  url: https://www.percona.com/blog/author/alexey-stroganov/
  website: http://www.percona.com
published_at: '2013-04-15T10:00:59'
published_at_gmt: '2013-04-15T10:00:59'
modified_at: '2026-03-25T16:51:13'
modified_at_gmt: '2026-03-25T16:51:13'
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
- Percona Software
category_slugs:
- benchmarks
- mysql
- percona-software
tags:
- Alexey Stroganov
- glibc
- jemalloc
- MySQL 5.5.30
- MySQL 5.6.10
- Pecona Server 5.5.30-30.2
- sysbench
tag_slugs:
- alexey-stroganov
- glibc
- jemalloc
- mysql-5-5-30
- mysql-5-6-10
- pecona-server-5-5-30-30-2
- sysbench
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/point_select_qps_1024_alloc1.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Memory allocators: MySQL performance improvements in Percona Server 5.5.30-30.2

Source: [Percona Blog](https://www.percona.com/blog/memory-allocators-mysql-performance-improvements-in-pecona-server-5-5-30-30-2/)

Auteur source: [Alexey Stroganov](https://www.percona.com/blog/author/alexey-stroganov/)

Publication: 2013-04-15T10:00:59

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In addition to the problem with trx_list scan we discussed in Friday’s post, there is another issue in InnoDB transaction processing that notably affects MySQL performance – for every transaction InnoDB creates a read view and allocates memory for this structure from heap. The problem is that the heap for that allocation is destroyed on … Continued

## Images et graphiques reperes

- featured / image: [Memory allocators: MySQL performance improvements in Percona Server 5.5.30-30.2](https://www.percona.com/wp-content/uploads/2026/03/point_select_qps_1024_alloc1.png)

## Auteur source

Alexey Stroganov is a Performance Engineer at Percona, where he works on improvements and features that makes Percona Server even more flexible, faster and scalable. Before joining Percona he worked on the performance testings/analysis of MySQL server and it components at MySQL AB/Sun/Oracle for more than ten years. During this time he was focused on performance evaluations, benchmarks, analysis, profiling, various optimizations and tunings.
