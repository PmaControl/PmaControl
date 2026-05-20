---
title: Assessing MySQL Performance Amongst AWS Options – Part One
source:
  name: Percona Blog
  url: https://www.percona.com/blog/assessing-mysql-performance-amongst-aws-options-part-one/
  post_id: 20637
source_author:
  name: Alexey Stroganov
  slug: alexey-stroganov
  url: https://www.percona.com/blog/author/alexey-stroganov/
  website: http://www.percona.com
published_at: '2019-07-17T13:26:03'
published_at_gmt: '2019-07-17T13:26:03'
modified_at: '2026-03-20T22:44:45'
modified_at_gmt: '2026-03-20T22:44:45'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Cloud
- MySQL
category_slugs:
- cloud
- mysql
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Performance-on-AWS.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Assessing MySQL Performance Amongst AWS Options – Part One

Source: [Percona Blog](https://www.percona.com/blog/assessing-mysql-performance-amongst-aws-options-part-one/)

Auteur source: [Alexey Stroganov](https://www.percona.com/blog/author/alexey-stroganov/)

Publication: 2019-07-17T13:26:03

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

With such a wide range of options available for running MySQL based servers in Amazon cloud environments, how do you choose? There’s no doubt it’s a challenge. In this two-part series of blog posts, we’ll try to draw a fair and informative comparison based on well-established benchmark scenarios – at scale. In part one we … Continued

## Structure detectee

- H2: Evaluation scenario
- H3: The benchmark scripts
- H3: Amazon MySQL Environments
- H3: Technical Setup – Server
- H4: Server test #1: Amazon RDS Aurora
- H4: Server test #2: Amazon RDS for MySQL with InnoDB Storage Engine
- H4: Server test #3: Percona Server for MySQL with InnoDB Storage Engine
- H4: Server test #4: Percona Server for MySQL with RocksDB using LZ4 compression
- H3: Technical Setup – Client
- H2: Disk space consumption for sysbench/tpcc 5000 warehouses
- H2: The performance tests
- H3: Server tests
- H4: Server test #1: RDS/Amazon Aurora
- H4: Server test #2: RDS/MySQL
- H4: Server tests #3 and #4: Percona Server for MySQL on EC2
- H3: Client test
- H2: Throughput
- H4: Server test #1: RDS/Amazon Aurora
- H4: Server test #2: RDS/MySQL
- H4: Server test #3: Percona Server for MySQL with InnoDB
- H4: Server test #4: Percona Server for MySQL with RocksDB

## Images et graphiques reperes

- featured / image: [Assessing MySQL Performance Amongst AWS Options – Part One](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Performance-on-AWS.png)
- content / image: [MySQL Performance Amongst AWS](https://www.percona.com/wp-content/uploads/2026/03/aws.final_.16_0.v2.db_size.png)
- content / image: [MySQL Performance AWS Options](https://www.percona.com/wp-content/uploads/2026/03/aws.final_.16_3.bar_.tps_.v3.png)

## Auteur source

Alexey Stroganov is a Performance Engineer at Percona, where he works on improvements and features that makes Percona Server even more flexible, faster and scalable. Before joining Percona he worked on the performance testings/analysis of MySQL server and it components at MySQL AB/Sun/Oracle for more than ten years. During this time he was focused on performance evaluations, benchmarks, analysis, profiling, various optimizations and tunings.
