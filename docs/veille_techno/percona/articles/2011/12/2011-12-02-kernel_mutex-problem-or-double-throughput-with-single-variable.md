---
title: kernel_mutex problem. Or double throughput with single variable
source:
  name: Percona Blog
  url: https://www.percona.com/blog/kernel_mutex-problem-or-double-throughput-with-single-variable/
  post_id: 3220
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2011-12-02T18:00:21'
published_at_gmt: '2011-12-02T18:00:21'
modified_at: '2026-05-04T19:43:14'
modified_at_gmt: '2026-05-04T19:43:14'
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
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/sysbench-spinloops.png
image_count: 3
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# kernel_mutex problem. Or double throughput with single variable

Source: [Percona Blog](https://www.percona.com/blog/kernel_mutex-problem-or-double-throughput-with-single-variable/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2011-12-02T18:00:21

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Problem with kernel_mutex in MySQL 5.1 and MySQL 5.5 is known: Bug report. In fact in MySQL 5.6 there are some fixes that suppose to provide a solution, but MySQL 5.6 yet has long way ahead before production, and it is also not clear if the problem is really fixed. Meantime the problem with kernel_mutex … Continued

## Images et graphiques reperes

- featured / graph_or_chart: [kernel_mutex problem. Or double throughput with single variable](https://www.percona.com/wp-content/uploads/2026/03/sysbench-spinloops.png)
- content / image: [sysbench-base.png](https://www.percona.com/wp-content/uploads/2026/03/sysbench-base.png)
- content / image: [sysbench-base-spin.png](https://www.percona.com/wp-content/uploads/2026/03/sysbench-base-spin.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
