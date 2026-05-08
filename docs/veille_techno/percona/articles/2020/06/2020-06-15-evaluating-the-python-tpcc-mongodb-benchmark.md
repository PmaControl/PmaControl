---
title: Evaluating the Python TPCC MongoDB Benchmark
source:
  name: Percona Blog
  url: https://www.percona.com/blog/evaluating-the-python-tpcc-mongodb-benchmark/
  post_id: 22545
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2020-06-15T17:52:46'
published_at_gmt: '2020-06-15T17:52:46'
modified_at: '2026-03-26T20:16:36'
modified_at_gmt: '2026-03-26T20:16:36'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- Percona Toolkit
matched_filters:
- search:percona-toolkit
categories:
- Benchmarks
- MongoDB
- Percona Software
category_slugs:
- benchmarks
- mongodb
- percona-software
tags:
- Benchmarks
- MongoDB
- Percona Software
tag_slugs:
- benchmarks
- mongodb
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/python-tpcc-mongodb.png
image_count: 8
graph_or_chart_count: 3
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Evaluating the Python TPCC MongoDB Benchmark

Source: [Percona Blog](https://www.percona.com/blog/evaluating-the-python-tpcc-mongodb-benchmark/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2020-06-15T17:52:46

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I was looking for a tool that could evaluate MongoDB performance under a complex workload, besides simple key-value operations that benchmarks like YCSB provides. That’s why the paper “Adapting TPC-C Benchmark to Measure Performance of Multi-Document Transactions in MongoDB” got my attention and I decided to try the tool https://github.com/mongodb-labs/py-tpcc mentioned in the paper. Py-tpcc, … Continued

## Structure detectee

- H2: Hardware Specs
- H2: MongoDB Topology
- H3: But First, An Aside – Using PyPy Makes Some Parts Much Better
- H2: Py-tpcc Benchmark Results
- H2: Conclusions

## Images et graphiques reperes

- featured / graph_or_chart: [Evaluating the Python TPCC MongoDB Benchmark](https://www.percona.com/wp-content/uploads/2026/03/python-tpcc-mongodb.png)
- content / image: [python tpcc mongodb](https://www.percona.com/wp-content/uploads/2026/03/python-tpcc-mongodb-300x168.png)
- content / image: [Screen-Shot-2020-06-15-at-12.08.32-PM-1024x227.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-06-15-at-12.08.32-PM-1024x227.png)
- content / graph_or_chart: [Python TPCC MongoDB Benchmark](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-06-15-at-12.09.17-PM.png)
  Caption: TPM (Transactions per minute) with PyPy vs. normal Python (CPython)
- content / graph_or_chart: [Python TPCC Benchmark](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-06-15-at-12.10.03-PM-1024x631.png)
- content / image: [CPU usage on the MongoDB server](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-06-15-at-12.10.31-PM-1024x399.png)
- content / image: [Screen-Shot-2020-06-15-at-12.11.14-PM.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-06-15-at-12.11.14-PM.png)
- content / image: [Screen-Shot-2020-06-15-at-12.11.50-PM-1024x632.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-06-15-at-12.11.50-PM-1024x632.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
