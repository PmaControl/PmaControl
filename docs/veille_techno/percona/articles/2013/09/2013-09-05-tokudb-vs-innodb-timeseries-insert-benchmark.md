---
title: TokuDB vs InnoDB in timeseries INSERT benchmark
source:
  name: Percona Blog
  url: https://www.percona.com/blog/tokudb-vs-innodb-timeseries-insert-benchmark/
  post_id: 7320
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2013-09-05T18:00:47'
published_at_gmt: '2013-09-05T18:00:47'
modified_at: '2026-04-28T21:55:35'
modified_at_gmt: '2026-04-28T21:55:35'
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
tags:
- InnoDB
- INSERT benchmark
- timeseries
- TokuDB
- Vadim Tkachenko
tag_slugs:
- innodb
- insert-benchmark
- timeseries
- tokudb
- vadim-tkachenko
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/innodb_resp.png
image_count: 7
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# TokuDB vs InnoDB in timeseries INSERT benchmark

Source: [Percona Blog](https://www.percona.com/blog/tokudb-vs-innodb-timeseries-insert-benchmark/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2013-09-05T18:00:47

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This post is a continuation of my research of TokuDB’s storage engine to understand if it is suitable for timeseries workloads. While inserting LOAD DATA INFILE into an empty table shows great results for TokuDB, what’s more interesting is seeing some realistic workloads. So this time let’s take a look at the INSERT benchmark.

## Images et graphiques reperes

- featured / graph_or_chart: [TokuDB vs InnoDB in timeseries INSERT benchmark](https://www.percona.com/wp-content/uploads/2026/03/innodb_resp.png)
- content / image: [TokuDB](https://www.percona.com/wp-content/uploads/2026/03/TokuDB_investigating-300x225.jpg)
- content / image: [innodb-thr](https://www.percona.com/wp-content/uploads/2026/03/innodb-thr.png)
- content / image: [tokudb_thr](https://www.percona.com/wp-content/uploads/2026/03/tokudb_thr.png)
- content / image: [tokudb_resp](https://www.percona.com/wp-content/uploads/2026/03/tokudb_resp.png)
- content / image: [zoom](https://www.percona.com/wp-content/uploads/2026/03/zoom.png)
- content / image: [tokudb_key](https://www.percona.com/wp-content/uploads/2026/03/tokudb_key.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
