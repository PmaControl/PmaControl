---
title: Performance Schema tables stats
source:
  name: Percona Blog
  url: https://www.percona.com/blog/performance-schema-tables-stats/
  post_id: 2989
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2011-05-02T07:00:01'
published_at_gmt: '2011-05-02T07:00:01'
modified_at: '2026-05-04T21:34:39'
modified_at_gmt: '2026-05-04T21:34:39'
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
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Performance Schema tables stats

Source: [Percona Blog](https://www.percona.com/blog/performance-schema-tables-stats/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2011-05-02T07:00:01

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

My previous benchmark on Performance Schema was mainly in memory workload and against single tables. Now after adding multi-tables support to sysbench, it is interesting to see what statistic we can get from workload that produces some disk IO. So let’s run sysbench against 100 tables, each 5000000 rows (~1.2G ) and buffer pool … Continued

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
