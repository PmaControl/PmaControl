---
title: How to Benchmark Replication Performance in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-benchmark-replication-performance-in-mysql/
  post_id: 25869
source_author:
  name: Sveta Smirnova
  slug: sveta-smirnova
  url: https://www.percona.com/blog/author/sveta-smirnova/
  website: http://www.percona.com/blog/
published_at: '2022-07-29T11:56:16'
published_at_gmt: '2022-07-29T11:56:16'
modified_at: '2026-03-26T20:31:29'
modified_at_gmt: '2026-03-26T20:31:29'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- ProxySQL
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:pmm
- search:proxysql
categories:
- Benchmarks
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- benchmarks
- insight-for-dbas
- mysql
- percona-software
tags:
- async replication
- benchmark
- Benchmarking
- Benchmarks
- MySQL
- mysql-and-variants
- Replication
tag_slugs:
- async-replication
- benchmark
- benchmarking
- benchmarks
- mysql
- mysql-and-variants
- replication
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Benchmark-Replication-Performance-in-MySQL.png
image_count: 1
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to Benchmark Replication Performance in MySQL

Source: [Percona Blog](https://www.percona.com/blog/how-to-benchmark-replication-performance-in-mysql/)

Auteur source: [Sveta Smirnova](https://www.percona.com/blog/author/sveta-smirnova/)

Publication: 2022-07-29T11:56:16

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog, I will cover important aspects which you need to test when benchmarking replication setup. MySQL has great tools that could be used to test its performance. They include: sysbench – https://github.com/akopytov/sysbench BMK-kit – http://dimitrik.free.fr/blog/posts/mysql-perf-bmk-kit.html mysqlslap – https://dev.mysql.com/doc/refman/8.0/en/mysqlslap.html LinkBench – https://github.com/facebookarchive/linkbench I will not describe how to use them here, as you can … Continued

## Structure detectee

- H2: Can the replica catch up to the source server?
- H2: Can the replica run queries while applying updates from the source server?
- H2: Synchronous replication
- H2: Your best test is your production
- H2: How fast will the replica catch up?

## Images et graphiques reperes

- featured / graph_or_chart: [How to Benchmark Replication Performance in MySQL](https://www.percona.com/wp-content/uploads/2026/03/Benchmark-Replication-Performance-in-MySQL.png)

## Auteur source

Sveta joined Percona in 2015. Her main professional interests are problem solving, working with tricky issues, bugs, finding patterns that can solve typical issues quicker and teaching others how to deal with MySQL issues, bugs and gotchas effectively. Before joining Percona Sveta worked as a Support Engineer in the MySQL Bugs Analysis Support Group in MySQL AB-Sun-Oracle. She is the author of the books "MySQL Troubleshooting" and "MySQL Cookbook, 4th Edition".
