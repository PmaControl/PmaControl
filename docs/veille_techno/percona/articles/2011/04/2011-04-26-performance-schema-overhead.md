---
title: Performance Schema overhead
source:
  name: Percona Blog
  url: https://www.percona.com/blog/performance-schema-overhead/
  post_id: 2834
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2011-04-26T04:10:36'
published_at_gmt: '2011-04-26T04:10:36'
modified_at: '2026-03-23T21:57:55'
modified_at_gmt: '2026-03-23T21:57:55'
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
- Hardware and Storage
- Insight for DBAs
- MySQL
category_slugs:
- benchmarks
- hardware-and-storage
- insight-for-dbas
- mysql
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/ps_overhead_ro.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Performance Schema overhead

Source: [Percona Blog](https://www.percona.com/blog/performance-schema-overhead/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2011-04-26T04:10:36

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

As continuation of my CPU benchmarks it is interesting to see what is scalability limitation in MySQL 5.6.2, and I am going to check that using PERFORMANCE SCHEMA, but before that let’s estimate what is potential overhead of using PERFORMANCE SCHEMA. So I am going to run the same benchmarks (sysbench read-only and read-write) as … Continued

## Images et graphiques reperes

- featured / image: [Performance Schema overhead](https://www.percona.com/wp-content/uploads/2026/03/ps_overhead_ro.png)
- content / image: [ps_overhead_rw.png](https://www.percona.com/wp-content/uploads/2026/03/ps_overhead_rw.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
