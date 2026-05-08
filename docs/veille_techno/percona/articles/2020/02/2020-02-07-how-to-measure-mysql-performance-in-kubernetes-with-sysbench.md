---
title: How to Measure MySQL Performance in Kubernetes with Sysbench
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-measure-mysql-performance-in-kubernetes-with-sysbench/
  post_id: 21572
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2020-02-07T18:59:18'
published_at_gmt: '2020-02-07T18:59:18'
modified_at: '2026-04-27T21:30:25'
modified_at_gmt: '2026-04-27T21:30:25'
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
- search:pmm
- search:proxysql
categories:
- Cloud
- MySQL
- Percona Software
category_slugs:
- cloud
- mysql
- percona-software
tags:
- cloud
- Kubernetes
- MySQL
- Percona Software
- sysbench
tag_slugs:
- cloud
- kubernetes
- mysql
- percona-software
- sysbench
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Kubernetes-Sysbench.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to Measure MySQL Performance in Kubernetes with Sysbench

Source: [Percona Blog](https://www.percona.com/blog/how-to-measure-mysql-performance-in-kubernetes-with-sysbench/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2020-02-07T18:59:18

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

As our Percona Kubernetes Operator for Percona XtraDB Cluster gains in popularity, I am getting questions about its performance and how to measure it properly. Sysbench is the most popular tool for database performance evaluation, so let’s review how we can use it with Percona XtraDB Cluster Operator. Operator Setup I will assume that you … Continued

## Structure detectee

- H2: Operator Setup
- H2: Sysbench on an External to Kubernetes Host
- H2: Sysbench Running Inside Kubernetes
- H2: A Quick Intro to Sysbench
- H3: Prepare Data
- H3: Running Benchmark
- H4: Parameters to Play
- H4: Results interpretation

## Images et graphiques reperes

- featured / image: [How to Measure MySQL Performance in Kubernetes with Sysbench](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Kubernetes-Sysbench.png)
- content / image: [MySQL Kubernetes Sysbench](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Kubernetes-Sysbench-300x168.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
