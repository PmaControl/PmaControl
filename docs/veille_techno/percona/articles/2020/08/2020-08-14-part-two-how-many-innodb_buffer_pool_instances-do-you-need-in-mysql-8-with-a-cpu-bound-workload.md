---
title: 'Part Two: How Many innodb_buffer_pool_instances Do You Need in MySQL 8 With a CPU-Bound Workload?'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/part-two-how-many-innodb_buffer_pool_instances-do-you-need-in-mysql-8-with-a-cpu-bound-workload/
  post_id: 22948
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2020-08-14T15:32:28'
published_at_gmt: '2020-08-14T15:32:28'
modified_at: '2026-04-27T22:13:19'
modified_at_gmt: '2026-04-27T22:13:19'
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
- Insight for Developers
- MySQL
category_slugs:
- benchmarks
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- Benchmarks
- InnoDB
- insight for DBAs
- insight for developers
- MySQL
- mysql-and-variants
tag_slugs:
- benchmarks
- innodb
- insight-for-dbas
- insight-for-developers
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/how-many-innodb_buffer_pool_instances-Do-You-Need-in-MySQL-8.png
image_count: 11
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Part Two: How Many innodb_buffer_pool_instances Do You Need in MySQL 8 With a CPU-Bound Workload?

Source: [Percona Blog](https://www.percona.com/blog/part-two-how-many-innodb_buffer_pool_instances-do-you-need-in-mysql-8-with-a-cpu-bound-workload/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2020-08-14T15:32:28

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Part one of this series can be found here: How Many innodb_buffer_pool_instances Do You Need in MySQL 8? Following up on my recent benchmark posts on MySQL and MariaDB, MySQL and MariaDB on Enterprise SSD Storage and How MySQL and MariaDB Perform on NVMe Storage, I wanted to dig a little deeper and understand how … Continued

## Structure detectee

- H2: Benchmark
- H2: Results on SATA SSD
- H2: Final Thoughts

## Images et graphiques reperes

- featured / image: [Part Two: How Many innodb_buffer_pool_instances Do You Need in MySQL 8 With a CPU-Bound Workload?](https://www.percona.com/wp-content/uploads/2026/03/how-many-innodb_buffer_pool_instances-Do-You-Need-in-MySQL-8.png)
- content / image: [how many innodb_buffer_pool_instances Do You Need in MySQL 8](https://www.percona.com/wp-content/uploads/2026/03/how-many-innodb_buffer_pool_instances-Do-You-Need-in-MySQL-8-300x157.png)
- content / image: [innodb_buffer_pool_instances=1](https://www.percona.com/wp-content/uploads/2026/03/1-1-1-1024x547.png)
- content / image: [innodb_buffer_pool_instances=2](https://www.percona.com/wp-content/uploads/2026/03/2-1-1-1024x547.png)
- content / image: [innodb_buffer_pool_instances=4](https://www.percona.com/wp-content/uploads/2026/03/3-1-1-1024x550.png)
- content / image: [innodb_buffer_pool_instances=8](https://www.percona.com/wp-content/uploads/2026/03/4-1-1-1024x550.png)
- content / image: [innodb_buffer_pool_instances=16](https://www.percona.com/wp-content/uploads/2026/03/5-1-1-1024x544.png)
- content / image: [innodb_buffer_pool_instances=32](https://www.percona.com/wp-content/uploads/2026/03/6-1-1-1024x550.png)
- content / image: [innodb_buffer_pool_instances=64](https://www.percona.com/wp-content/uploads/2026/03/7-1-1-1024x549.png)
- content / image: [increase innodb_buffer_pool_instances](https://www.percona.com/wp-content/uploads/2026/03/8-1-1-850x1024.png)
- content / graph_or_chart: [compare the throughput and deviation](https://www.percona.com/wp-content/uploads/2026/03/9-1-1-1024x615.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
