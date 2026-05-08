---
title: MongoDB Checkpointing Woes
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mongodb-checkpointing-woes/
  post_id: 22642
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2020-06-25T15:32:16'
published_at_gmt: '2020-06-25T15:32:16'
modified_at: '2026-03-26T20:16:34'
modified_at_gmt: '2026-03-26T20:16:34'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- search:percona-monitoring-and-management
categories:
- Benchmarks
- Insight for DBAs
- MongoDB
- Percona Software
category_slugs:
- benchmarks
- insight-for-dbas
- mongodb
- percona-software
tags:
- Benchmarks
- insight for DBAs
- MongoDB
- percona server for MongoDB
- Percona Software
tag_slugs:
- benchmarks
- insight-for-dbas
- mongodb
- percona-server-for-mongodb
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MongoDB-Checkpointing-Woes.png
image_count: 4
graph_or_chart_count: 2
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MongoDB Checkpointing Woes

Source: [Percona Blog](https://www.percona.com/blog/mongodb-checkpointing-woes/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2020-06-25T15:32:16

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In my recent post Evaluating MongoDB Under Python TPCC 1000W Workload with MongoDB benchmarks, I showed an average throughput for a prolonged period of time (900sec or 1800sec), and the average throughput tended to smooth and hide problems. But if we zoom in to 1-sec resolution for WiredTiger dashboard (Available in the Percona Monitoring and … Continued

## Images et graphiques reperes

- featured / image: [MongoDB Checkpointing Woes](https://www.percona.com/wp-content/uploads/2026/03/MongoDB-Checkpointing-Woes.png)
- content / graph_or_chart: [WiredTiger dashboard](https://www.percona.com/wp-content/uploads/2026/03/image1-2-3-1024x364.png)
- content / graph_or_chart: [Checkpoint Time dashboard](https://www.percona.com/wp-content/uploads/2026/03/image3-1-3-1024x365.png)
- content / image: [Tuning MongoDB](https://www.percona.com/wp-content/uploads/2026/03/image2-2-3-1024x362.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
