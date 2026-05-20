---
title: New distribution of random generator for sysbench – Zipf
source:
  name: Percona Blog
  url: https://www.percona.com/blog/new-distribution-of-random-generator-for-sysbench-zipf/
  post_id: 3558
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2012-05-10T00:50:28'
published_at_gmt: '2012-05-10T00:50:28'
modified_at: '2026-03-23T22:20:25'
modified_at_gmt: '2026-03-23T22:20:25'
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
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/zipf.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# New distribution of random generator for sysbench – Zipf

Source: [Percona Blog](https://www.percona.com/blog/new-distribution-of-random-generator-for-sysbench-zipf/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2012-05-10T00:50:28

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Sysbench has three distribution for random numbers: uniform, special and gaussian. I mostly use uniform and special, and I feel that both do not fully reflect my needs when I run benchmarks. Uniform is stupidly simple: for a table with 1 mln rows, each row gets equal amount of hits. This barely reflects real system, … Continued

## Images et graphiques reperes

- featured / image: [New distribution of random generator for sysbench – Zipf](https://www.percona.com/wp-content/uploads/2026/03/zipf.png)
- content / image: [zipf-zoom.png](https://www.percona.com/wp-content/uploads/2026/03/zipf-zoom.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
