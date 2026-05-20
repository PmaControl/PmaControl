---
title: Evaluating Percona XtraDB Cluster 8.0 in I/O Bound Workload
source:
  name: Percona Blog
  url: https://www.percona.com/blog/evaluating-percona-xtradb-cluster-8-0-in-i-o-bound-workload/
  post_id: 22231
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2020-04-16T13:05:47'
published_at_gmt: '2020-04-16T13:05:47'
modified_at: '2026-04-27T21:37:15'
modified_at_gmt: '2026-04-27T21:37:15'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MySQL
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- High Availability
- MySQL
- Percona Software
- Percona XtraDB
tag_slugs:
- high-availability
- mysql
- percona-software
- percona-xtradb
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-8.0-in-IO-Bound-Workload.png
image_count: 9
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Evaluating Percona XtraDB Cluster 8.0 in I/O Bound Workload

Source: [Percona Blog](https://www.percona.com/blog/evaluating-percona-xtradb-cluster-8-0-in-i-o-bound-workload/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2020-04-16T13:05:47

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona XtraDB Cluster 8.0 is in the final stretch before GA release and we have pre-release packages available for testing, and I wanted to see how Percona XtraDB Cluster 8.0 performs in CPU and IO-bound scenarios, like in my previous posts about MySQL Group Replication. In this blog, I want to evaluate Percona XtraDB Cluster … Continued

## Structure detectee

- H2: Results
- H3: Percona XtraDB Cluster 3 Nodes – Individual Scales
- H3: Percona XtraDB Cluster 3 Nodes, Timeline for 64 Threads
- H3: 3 Nodes vs. 5 Nodes
- H3: Percona XtraDB Cluster 3 Nodes, Timeline for 64 Threads
- H3: Percona XtraDB Cluster vs Group Replication
- H3: Coefficient of Variation

## Images et graphiques reperes

- featured / image: [Evaluating Percona XtraDB Cluster 8.0 in I/O Bound Workload](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-8.0-in-IO-Bound-Workload.png)
- content / image: [multi node bare metal servers](https://www.percona.com/wp-content/uploads/2026/03/image1-3-1.png)
- content / image: [increase user threads from 1 to 256 for 3 nodes](https://www.percona.com/wp-content/uploads/2026/03/download-1024x569.png)
- content / image: [Percona XtraDB Cluster 3 Nodes - Individual Scales](https://www.percona.com/wp-content/uploads/2026/03/download-1-1024x569.png)
- content / image: [Percona XtraDB Cluster 3 Nodes, Timeline for 64 Threads](https://www.percona.com/wp-content/uploads/2026/03/download-2-1024x569.png)
- content / image: [3 Nodes vs. 5 Nodes](https://www.percona.com/wp-content/uploads/2026/03/download-3-1024x569.png)
- content / image: [Percona XtraDB Cluster 3 Nodes, Timeline for 64 Threads](https://www.percona.com/wp-content/uploads/2026/03/download-4-1024x569.png)
- content / image: [Percona XtraDB Cluster vs Group Replication](https://www.percona.com/wp-content/uploads/2026/03/download-5-1024x569.png)
- content / image: [Coefficient of Variation](https://www.percona.com/wp-content/uploads/2026/03/download-6-1024x569.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
