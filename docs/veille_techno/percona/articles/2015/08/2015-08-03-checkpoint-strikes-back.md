---
title: Checkpoint strikes back
source:
  name: Percona Blog
  url: https://www.percona.com/blog/checkpoint-strikes-back/
  post_id: 9380
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2015-08-03T10:00:55'
published_at_gmt: '2015-08-03T10:00:55'
modified_at: '2026-03-26T20:22:59'
modified_at_gmt: '2026-03-26T20:22:59'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MongoDB
- MySQL
category_slugs:
- mongodb
- mysql
tags:
- checkpoint
- MongoDB
- MySQL
- RocksDB
- TokuDB
- tokumx
- Vadim Tkachenko
- WiredTiger
tag_slugs:
- checkpoint
- mongodb
- mysql
- rocksdb
- tokudb
- tokumx
- vadim-tkachenko
- wiredtiger
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Checkpoint strikes back

Source: [Percona Blog](https://www.percona.com/blog/checkpoint-strikes-back/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2015-08-03T10:00:55

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In my recent benchmarks for MongoDB, we can see that the two engines WiredTiger and TokuMX struggle from periodical drops in throughput, which is clearly related to a checkpoint interval – and therefore I correspond it to a checkpoint activity. The funny thing is that I thought we solved checkpointing issues in InnoDB once and … Continued

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
