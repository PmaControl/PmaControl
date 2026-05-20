---
title: Evaluating MongoDB Under Python TPCC 1000W Workload
source:
  name: Percona Blog
  url: https://www.percona.com/blog/evaluating-mongodb-under-python-tpcc-1000w-workload/
  post_id: 22608
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2020-06-24T14:42:17'
published_at_gmt: '2020-06-24T14:42:17'
modified_at: '2026-03-26T20:16:35'
modified_at_gmt: '2026-03-26T20:16:35'
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
- Percona Software
tag_slugs:
- benchmarks
- insight-for-dbas
- mongodb
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/evaluting-mongodb-python-tpcc.png
image_count: 10
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Evaluating MongoDB Under Python TPCC 1000W Workload

Source: [Percona Blog](https://www.percona.com/blog/evaluating-mongodb-under-python-tpcc-1000w-workload/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2020-06-24T14:42:17

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Following my blog post Evaluating the Python TPCC MongoDB Benchmark, I wanted to evaluate how MongoDB performs under workload with a bigger dataset. This time I will load a 1000 Warehouses dataset, which in raw format should equal to 100GB of data. For the comparison, I will use the same hardware and the same MongoDB … Continued

## Structure detectee

- H2: Hardware Specs
- H2: MongoDB Topology
- H3: MongoDB Versions:
- H3: Loading Data
- H3: 4.0
- H3: 4.2
- H3: 4.4
- H2: Benchmark Results
- H3: Results With an Unlimited Cache
- H3: Results With a Limited Cache
- H3: Results with 3 Nodes ReplicaSet and Limited Cache
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Evaluating MongoDB Under Python TPCC 1000W Workload](https://www.percona.com/wp-content/uploads/2026/03/evaluting-mongodb-python-tpcc.png)
- content / image: [evaluting mongodb python tpcc](https://www.percona.com/wp-content/uploads/2026/03/evaluting-mongodb-python-tpcc-300x168.png)
- content / image: [MongoDB Python Benchmarks](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-06-23-at-1.44.20-PM.png)
- content / image: [MongoDB Version Benchmarks](https://www.percona.com/wp-content/uploads/2026/03/image5-5-1024x633.png)
- content / image: [Results With a Limited Cache MongoDB](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-06-24-at-8.35.02-AM.png)
- content / image: [image2-1-3-1024x633.png](https://www.percona.com/wp-content/uploads/2026/03/image2-1-3-1024x633.png)
- content / image: [Results with 3 Nodes ReplicaSet and Limited Cache](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-06-23-at-1.52.53-PM.png)
- content / image: [image1-1-3-1024x633.png](https://www.percona.com/wp-content/uploads/2026/03/image1-1-3-1024x633.png)
- content / image: [image4-7-1024x633.png](https://www.percona.com/wp-content/uploads/2026/03/image4-7-1024x633.png)
- content / image: [image3-6-1024x633.png](https://www.percona.com/wp-content/uploads/2026/03/image3-6-1024x633.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
