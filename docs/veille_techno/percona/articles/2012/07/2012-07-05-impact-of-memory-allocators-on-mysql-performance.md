---
title: Impact of memory allocators on MySQL performance
source:
  name: Percona Blog
  url: https://www.percona.com/blog/impact-of-memory-allocators-on-mysql-performance/
  post_id: 3677
source_author:
  name: Alexey Stroganov
  slug: alexey-stroganov
  url: https://www.percona.com/blog/author/alexey-stroganov/
  website: http://www.percona.com
published_at: '2012-07-05T17:39:18'
published_at_gmt: '2012-07-05T17:39:18'
modified_at: '2026-05-05T21:45:24'
modified_at_gmt: '2026-05-05T21:45:24'
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
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/malloc.oltp_ro.png
image_count: 8
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Impact of memory allocators on MySQL performance

Source: [Percona Blog](https://www.percona.com/blog/impact-of-memory-allocators-on-mysql-performance/)

Auteur source: [Alexey Stroganov](https://www.percona.com/blog/author/alexey-stroganov/)

Publication: 2012-07-05T17:39:18

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MySQL server intensively uses dynamic memory allocation so a good choice of memory allocator is quite important for the proper utilization of CPU/RAM resources. Efficient memory allocator should help to improve scalability, increase throughput and keep memory footprint under the control. In this post I’m going to check impact of several memory allocators on the … Continued

## Images et graphiques reperes

- featured / image: [Impact of memory allocators on MySQL performance](https://www.percona.com/wp-content/uploads/2026/03/malloc.oltp_ro.png)
- content / image: [tps_OLTP_RO.data_.png](https://www.percona.com/wp-content/uploads/2026/03/tps_OLTP_RO.data_.png)
- content / image: [pct99_OLTP_RO.data_.png](https://www.percona.com/wp-content/uploads/2026/03/pct99_OLTP_RO.data_.png)
- content / image: [tps_POINT_SELECT.data_.png](https://www.percona.com/wp-content/uploads/2026/03/tps_POINT_SELECT.data_.png)
- content / image: [pct99_POINT_SELECT.data_.png](https://www.percona.com/wp-content/uploads/2026/03/pct99_POINT_SELECT.data_.png)
- content / image: [jem.225.6721.png](https://www.percona.com/wp-content/uploads/2026/03/jem.225.6721.png)
- content / image: [jem.3.655.png](https://www.percona.com/wp-content/uploads/2026/03/jem.3.655.png)
- content / image: [tcm.658.png](https://www.percona.com/wp-content/uploads/2026/03/tcm.658.png)

## Auteur source

Alexey Stroganov is a Performance Engineer at Percona, where he works on improvements and features that makes Percona Server even more flexible, faster and scalable. Before joining Percona he worked on the performance testings/analysis of MySQL server and it components at MySQL AB/Sun/Oracle for more than ten years. During this time he was focused on performance evaluations, benchmarks, analysis, profiling, various optimizations and tunings.
