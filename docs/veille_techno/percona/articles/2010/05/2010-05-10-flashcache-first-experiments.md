---
title: 'FlashCache: first experiments'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/flashcache-first-experiments/
  post_id: 2315
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2010-05-10T20:07:52'
published_at_gmt: '2010-05-10T20:07:52'
modified_at: '2026-03-23T21:41:22'
modified_at_gmt: '2026-03-23T21:41:22'
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
- MySQL
category_slugs:
- benchmarks
- hardware-and-storage
- mysql
tags:
- SSD
tag_slugs:
- ssd
featured_image_url: https://www.percona.com/docs/wiki/_media/benchmark:flashcache:sysbench:readonly.png
image_count: 3
graph_or_chart_count: 3
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# FlashCache: first experiments

Source: [Percona Blog](https://www.percona.com/blog/flashcache-first-experiments/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2010-05-10T20:07:52

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I wrote about FlashCache there, and since that I run couple benchmarks, to see what performance benefits we can expect.For initial tries I took sysbench oltp tests ( read-only and read-write) and case when data fully fits into L2 cache. I made binaries for FlashCache for CentOS 5.4, kernel 2.6.18-164.15, you can download it from … Continued

## Images et graphiques reperes

- featured / graph_or_chart: [FlashCache: first experiments](https://www.percona.com/docs/wiki/_media/benchmark:flashcache:sysbench:readonly.png)
- content / graph_or_chart: [Read Write X25-M](https://www.percona.com/docs/wiki/_media/benchmark:flashcache:sysbench:x25m.png)
- content / graph_or_chart: [ReadWrite X25-E](https://www.percona.com/docs/wiki/_media/benchmark:flashcache:sysbench:x25e.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
