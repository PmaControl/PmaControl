---
title: Scaling IO-Bound Workloads for MySQL in the Cloud
source:
  name: Percona Blog
  url: https://www.percona.com/blog/scaling-io-bound-workloads-mysql-cloud/
  post_id: 19230
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2018-08-29T15:55:36'
published_at_gmt: '2018-08-29T15:55:36'
modified_at: '2026-03-20T21:59:54'
modified_at_gmt: '2026-03-20T21:59:54'
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
- Cloud
- Hardware and Storage
- MySQL
- Percona Software
- Storage Engine
category_slugs:
- benchmarks
- cloud
- hardware-and-storage
- mysql
- percona-software
- storage-engine
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Rplot03.png
image_count: 4
graph_or_chart_count: 3
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Scaling IO-Bound Workloads for MySQL in the Cloud

Source: [Percona Blog](https://www.percona.com/blog/scaling-io-bound-workloads-mysql-cloud/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2018-08-29T15:55:36

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Is increasing GP2 volumes size or increasing IOPS for IO1 volumes a valid method for scaling IO-Bound workloads? In this post I’ll focus on one question: how much can we improve performance if we use faster cloud volumes? This post is a continuance of previous cloud research posts: Saving With MyRocks in The Cloud Percona … Continued

## Structure detectee

- H3: Benchmark Scenario
- H4: Results on GP2 volumes:
- H4: Results on IO1 volumes
- H3: Conclusions
- H3: You May Also Like

## Images et graphiques reperes

- featured / image: [Scaling IO-Bound Workloads for MySQL in the Cloud](https://www.percona.com/wp-content/uploads/2026/03/Rplot03.png)
- content / graph_or_chart: [InnoDB / MyRocks throughput on IO1](https://www.percona.com/wp-content/uploads/2026/03/Rplot04-1.png)
- content / graph_or_chart: [InnoDB/MyRocks throughput on gp2 3400GB](https://www.percona.com/wp-content/uploads/2026/03/Rplot06.png)
- content / graph_or_chart: [InnoDB/MyRocks throughput on IO1 30000 IOPS](https://www.percona.com/wp-content/uploads/2026/03/Rplot05-1.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
