---
title: How Many innodb_buffer_pool_instances Do You Need in MySQL 8?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-many-innodb_buffer_pool_instances-do-you-need-in-mysql-8/
  post_id: 22936
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2020-08-13T18:38:03'
published_at_gmt: '2020-08-13T18:38:03'
modified_at: '2026-04-27T22:12:58'
modified_at_gmt: '2026-04-27T22:12:58'
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
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/innodb_buffer_pool_instances-Do-You-Need-in-MySQL.png
image_count: 11
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How Many innodb_buffer_pool_instances Do You Need in MySQL 8?

Source: [Percona Blog](https://www.percona.com/blog/how-many-innodb_buffer_pool_instances-do-you-need-in-mysql-8/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2020-08-13T18:38:03

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Following up on my recent benchmark posts on MySQL and MariaDB, MySQL and MariaDB on Enterprise SSD Storage and How MySQL and MariaDB Perform on NVMe Storage, I wanted to dig a little deeper and understand how different MySQL parameters affect performance. One of the obscure MySQL Parameters (in my opinion) is innodb_buffer_pool_instances. In particular, … Continued

## Structure detectee

- H2: Benchmark
- H2: Results on SATA SSD
- H2: Final Thoughts

## Images et graphiques reperes

- featured / image: [How Many innodb_buffer_pool_instances Do You Need in MySQL 8?](https://www.percona.com/wp-content/uploads/2026/03/innodb_buffer_pool_instances-Do-You-Need-in-MySQL.png)
- content / image: [innodb_buffer_pool_instances-Do-You-Need-in-MySQL-300x157.png](https://www.percona.com/wp-content/uploads/2026/03/innodb_buffer_pool_instances-Do-You-Need-in-MySQL-300x157.png)
- content / image: [innodb_buffer_pool_instances=1](https://www.percona.com/wp-content/uploads/2026/03/1-11-1024x550.png)
- content / image: [innodb_buffer_pool_instances=2](https://www.percona.com/wp-content/uploads/2026/03/2-10-1024x545.png)
- content / image: [innodb_buffer_pool_instances=4](https://www.percona.com/wp-content/uploads/2026/03/3-11-1024x547.png)
- content / image: [innodb_buffer_pool_instances=8](https://www.percona.com/wp-content/uploads/2026/03/4-13-1024x547.png)
- content / image: [innodb_buffer_pool_instances=16](https://www.percona.com/wp-content/uploads/2026/03/5-6-1024x549.png)
- content / image: [innodb_buffer_pool_instances=32](https://www.percona.com/wp-content/uploads/2026/03/6-6-1024x545.png)
- content / image: [innodb_buffer_pool_instances=64](https://www.percona.com/wp-content/uploads/2026/03/7-5-1024x549.png)
- content / graph_or_chart: [innodb_buffer_pool_instances chart](https://www.percona.com/wp-content/uploads/2026/03/8-4-1024x550.png)
- content / image: [9-2-1024x547.png](https://www.percona.com/wp-content/uploads/2026/03/9-2-1024x547.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
