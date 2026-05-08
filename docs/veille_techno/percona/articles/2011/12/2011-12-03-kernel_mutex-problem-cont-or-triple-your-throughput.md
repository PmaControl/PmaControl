---
title: kernel_mutex problem cont. Or triple your throughput
source:
  name: Percona Blog
  url: https://www.percona.com/blog/kernel_mutex-problem-cont-or-triple-your-throughput/
  post_id: 3233
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2011-12-03T00:41:40'
published_at_gmt: '2011-12-03T00:41:40'
modified_at: '2026-03-23T22:11:06'
modified_at_gmt: '2026-03-23T22:11:06'
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
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/sysbench-concur.png
image_count: 2
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# kernel_mutex problem cont. Or triple your throughput

Source: [Percona Blog](https://www.percona.com/blog/kernel_mutex-problem-cont-or-triple-your-throughput/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2011-12-03T00:41:40

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This is to follow up my previous post with kernel_mutex problem. First, I may have an explanation why the performance degrades to significantly and why innodb_sync_spin_loops may fix it.Second, if that is correct ( or not, but we can try anyway), than playing with innodb_thread_concurrency also may help. So I ran some benchmarks with innodb_thread_concurrency.

## Images et graphiques reperes

- featured / graph_or_chart: [kernel_mutex problem cont. Or triple your throughput](https://www.percona.com/wp-content/uploads/2026/03/sysbench-concur.png)
- content / image: [sysbench-threads-conc.png](https://www.percona.com/wp-content/uploads/2026/03/sysbench-threads-conc.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
