---
title: Evaluating Group Replication Scaling Capabilities in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/evaluating-group-replication-scaling-capabilities-in-mysql/
  post_id: 22144
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2020-04-08T19:01:45'
published_at_gmt: '2020-04-08T19:01:45'
modified_at: '2026-05-05T16:26:45'
modified_at_gmt: '2026-05-05T16:26:45'
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
- Benchmarks
- MySQL
- Replication
tag_slugs:
- benchmarks
- mysql
- replication
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Group-Replication-Scaling-Capabilities-in-MySQL.png
image_count: 7
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Evaluating Group Replication Scaling Capabilities in MySQL

Source: [Percona Blog](https://www.percona.com/blog/evaluating-group-replication-scaling-capabilities-in-mysql/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2020-04-08T19:01:45

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog, I want to evaluate Group Replication Scaling capabilities in cases when we increase the number of nodes and increase user connections. For testing, I will deploy multi-node bare metal servers, where each node and client are dedicated to an individual server and connected between themselves by a 10Gb network. Also, I will … Continued

## Structure detectee

- H2: Results
- H3: 3 nodes vs. 5 nodes
- H2: Conclusions

## Images et graphiques reperes

- featured / image: [Evaluating Group Replication Scaling Capabilities in MySQL](https://www.percona.com/wp-content/uploads/2026/03/Group-Replication-Scaling-Capabilities-in-MySQL.png)
- content / image: [image2-4.png](https://www.percona.com/wp-content/uploads/2026/03/image2-4.png)
- content / image: [Group Replication Scaling threads](https://www.percona.com/wp-content/uploads/2026/03/image4-5.png)
- content / image: [image1-5.png](https://www.percona.com/wp-content/uploads/2026/03/image1-5.png)
- content / image: [image6-4.png](https://www.percona.com/wp-content/uploads/2026/03/image6-4.png)
- content / image: [group replication](https://www.percona.com/wp-content/uploads/2026/03/image3-5.png)
- content / image: [3 nodes vs. 5 nodes](https://www.percona.com/wp-content/uploads/2026/03/image5-4.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
