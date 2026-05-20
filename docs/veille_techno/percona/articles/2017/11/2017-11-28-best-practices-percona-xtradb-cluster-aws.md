---
title: Best Practices for Percona XtraDB Cluster on AWS
source:
  name: Percona Blog
  url: https://www.percona.com/blog/best-practices-percona-xtradb-cluster-aws/
  post_id: 17694
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2017-11-28T22:52:49'
published_at_gmt: '2017-11-28T22:52:49'
modified_at: '2026-05-05T23:58:34'
modified_at_gmt: '2026-05-05T23:58:34'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- ProxySQL
matched_filters:
- category:mysql:83
- search:proxysql
categories:
- MySQL
category_slugs:
- mysql
tags:
- amazon
- AWS
- benchmark
- cloud
- cluster
- High Availability
- Percona XtraDB Cluster
- pxc
tag_slugs:
- amazon
- aws
- benchmark
- cloud
- cluster
- high-availability
- percona-xtradb-cluster
- pxc
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-on-AWS-2-small.png
image_count: 12
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Best Practices for Percona XtraDB Cluster on AWS

Source: [Percona Blog](https://www.percona.com/blog/best-practices-percona-xtradb-cluster-aws/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2017-11-28T22:52:49

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post I’ll look at the performance of Percona XtraDB Cluster on AWS using different service instances, and recommend some best practices for maximizing performance. You can use Percona XtraDB Cluster in AWS environments. We often get questions about how best to deploy it, and how to optimize both performance and spend when … Continued

## Structure detectee

- H3: Results
- H4: Results summary, raw performance:
- H4: Results summary for jitter:
- H4: Results summary, cost
- H3: Percona XtraDB Cluster scalability
- H3: ProxySQL overhead
- H3: Summary
- H4: Amazon instances
- H4: ProxySQL overhead
- H4: Percona XtraDB Cluster scalability

## Images et graphiques reperes

- featured / image: [Best Practices for Percona XtraDB Cluster on AWS](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-on-AWS-2-small.png)
- content / image: [Percona XtraDB Cluster on AWS 1](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-on-AWS-1.png)
- content / image: [Percona XtraDB Cluster on AWS 2](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-on-AWS-2.png)
- content / image: [Percona XtraDB Cluster on AWS 3](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-on-AWS-3.png)
- content / image: [Percona XtraDB Cluster on AWS 4](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-on-AWS-4.png)
- content / image: [Percona XtraDB Cluster on AWS 5](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-on-AWS-5.png)
- content / image: [Percona XtraDB Cluster on AWS 6](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-on-AWS-6.png)
- content / image: [Percona XtraDB Cluster on AWS 7](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-on-AWS-7.png)
- content / image: [Percona XtraDB Cluster on AWS 8](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-on-AWS-8.png)
- content / image: [Percona XtraDB Cluster on AWS 9](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-on-AWS-9.png)
- content / image: [Percona XtraDB Cluster on AWS 10](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-on-AWS-10.png)
- content / image: [Percona XtraDB Cluster on AWS 11](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-on-AWS-11.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
