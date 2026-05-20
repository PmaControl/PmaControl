---
title: RAID throughput on FusionIO
source:
  name: Percona Blog
  url: https://www.percona.com/blog/raid-throughput-on-fusionio/
  post_id: 2258
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2010-03-25T03:35:16'
published_at_gmt: '2010-03-25T03:35:16'
modified_at: '2026-05-04T19:42:21'
modified_at_gmt: '2026-05-04T19:42:21'
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
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/io_throughput_16kb.png
image_count: 1
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# RAID throughput on FusionIO

Source: [Percona Blog](https://www.percona.com/blog/raid-throughput-on-fusionio/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2010-03-25T03:35:16

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Along with maximal possible fsync/sec it is interesting how different software RAID modes affects throughput on FusionIO cards. In short conclusion, RAID10 modes really disappoint me, the detailed numbers to follow. To get numbers I run sysbench fileio 1 sysbench fileio test with 16KB page size, random read and writes, 1 and 16 threads, O_DIRECT mode. FusionIO cards are … Continued

## Images et graphiques reperes

- featured / graph_or_chart: [RAID throughput on FusionIO](https://www.percona.com/wp-content/uploads/2026/03/io_throughput_16kb.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
