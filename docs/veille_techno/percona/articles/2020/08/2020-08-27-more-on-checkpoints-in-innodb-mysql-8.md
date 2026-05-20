---
title: More on Checkpoints in InnoDB MySQL 8
source:
  name: Percona Blog
  url: https://www.percona.com/blog/more-on-checkpoints-in-innodb-mysql-8/
  post_id: 23002
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2020-08-27T19:31:07'
published_at_gmt: '2020-08-27T19:31:07'
modified_at: '2026-05-05T16:32:18'
modified_at_gmt: '2026-05-05T16:32:18'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:pmm
categories:
- Benchmarks
- Insight for DBAs
- MySQL
category_slugs:
- benchmarks
- insight-for-dbas
- mysql
tags:
- InnoDB
- MySQL 8
- mysql-and-variants
tag_slugs:
- innodb
- mysql-8
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Checkpoints.jpg
image_count: 22
graph_or_chart_count: 3
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# More on Checkpoints in InnoDB MySQL 8

Source: [Percona Blog](https://www.percona.com/blog/more-on-checkpoints-in-innodb-mysql-8/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2020-08-27T19:31:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently I posted about checkpointing in MySQL, where MySQL showed interesting “wave” behavior. Soon after Dimitri posted a solution with how to fix “waves,” and I would like to dig a little more into proposed suggestions, as there are some materials to process. This post will be very heavy on InnoDB configuration, so let’s start … Continued

## Structure detectee

- H2: Initial results
- H2: Next run with Innodb_io_capacity = innodb_io_capacity_max = 7000
- H2: Results with innodb_doublewrite_files=2 and innodb_doublewrite_pages=128
- H2: Results with –innodb_adaptive_hash_index=0
- H2: Results with innodb_buffer_pool_instances=32
- H2: Results with innodb_change_buffering=none
- H2: Inverse relationship between innodb_io_capacity_max and innodb_log_file_size
- H2: Conclusions:

## Images et graphiques reperes

- featured / image: [More on Checkpoints in InnoDB MySQL 8](https://www.percona.com/wp-content/uploads/2026/03/Checkpoints.jpg)
- content / image: [Checkpoints-300x157.jpg](https://www.percona.com/wp-content/uploads/2026/03/Checkpoints-300x157.jpg)
- content / image: [wave.png](https://www.percona.com/wp-content/uploads/2026/03/wave.png)
- content / image: [InnoDB-Checkpoint-Age-1.png](https://www.percona.com/wp-content/uploads/2026/03/InnoDB-Checkpoint-Age-1.png)
- content / image: [sync-flushing.png](https://www.percona.com/wp-content/uploads/2026/03/sync-flushing.png)
- content / image: [innodb_io_capacity.png](https://www.percona.com/wp-content/uploads/2026/03/innodb_io_capacity.png)
- content / image: [InnoDB-Checkpoint.png](https://www.percona.com/wp-content/uploads/2026/03/InnoDB-Checkpoint.png)
- content / image: [innodb_doublewrite.png](https://www.percona.com/wp-content/uploads/2026/03/innodb_doublewrite.png)
- content / graph_or_chart: [InnoDB-Checkpoint-Age-chart.png](https://www.percona.com/wp-content/uploads/2026/03/InnoDB-Checkpoint-Age-chart.png)
- content / image: [adaptive_hash_index.png](https://www.percona.com/wp-content/uploads/2026/03/adaptive_hash_index.png)
- content / image: [barcharts.png](https://www.percona.com/wp-content/uploads/2026/03/barcharts.png)
- content / image: [pool_instances.png](https://www.percona.com/wp-content/uploads/2026/03/pool_instances.png)
- content / image: [buffering.png](https://www.percona.com/wp-content/uploads/2026/03/buffering.png)
- content / graph_or_chart: [PMM-change-buffer-chart.png](https://www.percona.com/wp-content/uploads/2026/03/PMM-change-buffer-chart.png)
- content / image: [innodb_io_capacity_max.png](https://www.percona.com/wp-content/uploads/2026/03/innodb_io_capacity_max.png)
- content / graph_or_chart: [throughput-4.png](https://www.percona.com/wp-content/uploads/2026/03/throughput-4.png)
- content / image: [max-8000.png](https://www.percona.com/wp-content/uploads/2026/03/max-8000.png)
- content / image: [max-7000.png](https://www.percona.com/wp-content/uploads/2026/03/max-7000.png)
- content / image: [max-6500.png](https://www.percona.com/wp-content/uploads/2026/03/max-6500.png)
- content / image: [filesize.png](https://www.percona.com/wp-content/uploads/2026/03/filesize.png)
- content / image: [max-4500.png](https://www.percona.com/wp-content/uploads/2026/03/max-4500.png)
- content / image: [throughput2.png](https://www.percona.com/wp-content/uploads/2026/03/throughput2.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
