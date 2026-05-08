---
title: Using Cgroups to Limit MySQL and MongoDB memory usage
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-cgroups-to-limit-mysql-and-mongodb-memory-usage/
  post_id: 9341
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2015-07-01T12:00:30'
published_at_gmt: '2015-07-01T12:00:30'
modified_at: '2026-03-26T20:23:02'
modified_at_gmt: '2026-03-26T20:23:02'
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
- Benchmarks
- cgroups
- Docker
- innodb_buffer_pool_size
- Linux
- MMAP
- MongoDB
- MySQL
- RocksDB
- TokuDB
- tokumx
- Vadim Tkachenko
- WiredTiger
tag_slugs:
- benchmarks
- cgroups
- docker
- innodb_buffer_pool_size
- linux
- mmap
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

# Using Cgroups to Limit MySQL and MongoDB memory usage

Source: [Percona Blog](https://www.percona.com/blog/using-cgroups-to-limit-mysql-and-mongodb-memory-usage/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2015-07-01T12:00:30

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Quite often, especially for benchmarks, I am trying to limit available memory for a database server (usually for MySQL, but recently for MongoDB also). This is usually needed to test database performance in scenarios with different memory limits. I have physical servers with the usually high amount of memory (128GB or more), but I am … Continued

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
