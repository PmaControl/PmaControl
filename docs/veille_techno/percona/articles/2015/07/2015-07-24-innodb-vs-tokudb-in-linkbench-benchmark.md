---
title: InnoDB vs TokuDB in LinkBench benchmark
source:
  name: Percona Blog
  url: https://www.percona.com/blog/innodb-vs-tokudb-in-linkbench-benchmark/
  post_id: 9399
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2015-07-24T14:12:16'
published_at_gmt: '2015-07-24T14:12:16'
modified_at: '2026-03-25T18:05:15'
modified_at_gmt: '2026-03-25T18:05:15'
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
- Percona Software
category_slugs:
- benchmarks
- mysql
- percona-software
tags:
- benchmark
- Fractal Tree
- InnoDB
- LinkBenchX
- MongoDB
- MySQL
- Percona Server for MySQL
- Primary
- storage engines
- TokuDB
- tokumx
- TokuMXSE
- Tokutek
- Vadim Tkachenko
tag_slugs:
- benchmark
- fractal-tree
- innodb
- linkbenchx
- mongodb
- mysql
- percona-server
- primary
- storage-engines
- tokudb
- tokumx
- tokumxse
- tokutek
- vadim-tkachenko
featured_image_url: http://lab-docs.percona.com/en/latest/_images/m500.png
image_count: 4
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# InnoDB vs TokuDB in LinkBench benchmark

Source: [Percona Blog](https://www.percona.com/blog/innodb-vs-tokudb-in-linkbench-benchmark/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2015-07-24T14:12:16

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Previously I tested Tokutek’s Fractal Trees (TokuMX & TokuMXse) as MongoDB storage engines – today let’s look into the MySQL area. I am going to use modified LinkBench in a heavy IO-load. I compared InnoDB without compression, InnoDB with 8k compression, TokuDB with quicklz compression.Uncompressed datasize is 115GiB, and cachesize is 12GiB for InnoDB and … Continued

## Images et graphiques reperes

- featured / graph_or_chart: [InnoDB vs TokuDB in LinkBench benchmark](http://lab-docs.percona.com/en/latest/_images/m500.png)
- content / image: [Intel P3600](http://lab-docs.percona.com/en/latest/_images/i3600.png)
- content / image: [IO Reads](http://lab-docs.percona.com/en/latest/_images/reads.png)
- content / image: [IO Writes](http://lab-docs.percona.com/en/latest/_images/writes.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
