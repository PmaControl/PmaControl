---
title: Evaluating Percona XtraDB Cluster 8.0 Scaling Capabilities
source:
  name: Percona Blog
  url: https://www.percona.com/blog/evaluating-percona-xtradb-cluster-8-0-scaling-capabilities/
  post_id: 22208
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2020-04-14T18:08:14'
published_at_gmt: '2020-04-14T18:08:14'
modified_at: '2026-04-29T14:44:49'
modified_at_gmt: '2026-04-29T14:44:49'
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
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-Scaling.png
image_count: 8
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Evaluating Percona XtraDB Cluster 8.0 Scaling Capabilities

Source: [Percona Blog](https://www.percona.com/blog/evaluating-percona-xtradb-cluster-8-0-scaling-capabilities/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2020-04-14T18:08:14

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona XtraDB Cluster 8.0 is on the final stretch before GA release, and we have pre-release packages available for testing. I wanted to see how Percona XtraDB Cluster 8.0 performs in CPU and IO-bound scenarios, like in my previous posts about MySQL Group Replication. In this blog, I want to evaluate Percona XtraDB Cluster 8.0 … Continued

## Structure detectee

- H2: Results
- H2: 3 nodes vs. 5 nodes
- H2: Percona XtraDB Cluster vs. Group Replication
- H2: Conclusion
- H4: Appendix

## Images et graphiques reperes

- featured / image: [Evaluating Percona XtraDB Cluster 8.0 Scaling Capabilities](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-Scaling.png)
- content / image: [multi node bare metal servers](https://www.percona.com/wp-content/uploads/2026/03/image1-3-1.png)
- content / graph_or_chart: [throughput performance](https://www.percona.com/wp-content/uploads/2026/03/image3-3-1-1024x569.png)
- content / image: [individual scales](https://www.percona.com/wp-content/uploads/2026/03/image7-1-1-1024x569.png)
- content / image: [Timeline with 1 sec resolution for 64 threads](https://www.percona.com/wp-content/uploads/2026/03/image6-2-1-1024x569.png)
- content / image: [performance under 5 nodes](https://www.percona.com/wp-content/uploads/2026/03/image2-3-1-1024x569.png)
- content / image: [image4-3-1-1024x569.png](https://www.percona.com/wp-content/uploads/2026/03/image4-3-1-1024x569.png)
- content / image: [Percona XtraDB Cluster vs Group Replication](https://www.percona.com/wp-content/uploads/2026/03/image5-2-1-1024x569.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
