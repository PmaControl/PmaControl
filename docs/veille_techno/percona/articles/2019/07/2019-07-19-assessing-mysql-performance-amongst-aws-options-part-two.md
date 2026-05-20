---
title: Assessing MySQL Performance Amongst AWS Options – Part Two
source:
  name: Percona Blog
  url: https://www.percona.com/blog/assessing-mysql-performance-amongst-aws-options-part-two/
  post_id: 20642
source_author:
  name: Alexey Stroganov
  slug: alexey-stroganov
  url: https://www.percona.com/blog/author/alexey-stroganov/
  website: http://www.percona.com
published_at: '2019-07-19T13:10:44'
published_at_gmt: '2019-07-19T13:10:44'
modified_at: '2026-04-27T21:19:21'
modified_at_gmt: '2026-04-27T21:19:21'
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
category_slugs:
- mysql
tags:
- MySQL
tag_slugs:
- mysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Compare-Amazon-RDS-to-Percona-Server.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Assessing MySQL Performance Amongst AWS Options – Part Two

Source: [Percona Blog](https://www.percona.com/blog/assessing-mysql-performance-amongst-aws-options-part-two/)

Auteur source: [Alexey Stroganov](https://www.percona.com/blog/author/alexey-stroganov/)

Publication: 2019-07-19T13:10:44

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

See part one of this series here. This post is part two of my series “Assessing MySQL Performance Amongst AWS Options”, taking a look at how current Amazon RDS services – Amazon Aurora and Amazon RDS for MySQL – compare with Percona Server with InnoDB and RocksDB engines on EC2 instances. This time around, I … Continued

## Structure detectee

- H3: The benchmark scripts
- H3: Amazon MySQL Environments
- H3: Technical Setup – Server
- H4: Server test #1: Amazon RDS Aurora
- H4: Server test #2: Amazon RDS for MySQL with InnoDB Storage Engine
- H4: Server test #3: Percona Server for MySQL with InnoDB Storage Engine
- H4: Server test #4: Percona Server for MySQL with RocksDB using LZ4 compression
- H3: Technical Setup – Client
- H2: Costs
- H3: Our total cost formulas
- H3: The results
- H2: Efficiency
- H2: Some concluding thoughts
- H2: One Final Note

## Images et graphiques reperes

- featured / image: [Assessing MySQL Performance Amongst AWS Options – Part Two](https://www.percona.com/wp-content/uploads/2026/03/Compare-Amazon-RDS-to-Percona-Server.png)
- content / image: [aws.final_.16_4.bar_.costs_1run.v3.png](https://www.percona.com/wp-content/uploads/2026/03/aws.final_.16_4.bar_.costs_1run.v3.png)
- content / image: [aws.final_.16_4.bar_.costs_1run_detailed.v6.png](https://www.percona.com/wp-content/uploads/2026/03/aws.final_.16_4.bar_.costs_1run_detailed.v6.png)
- content / image: [aws.final_.16_5.bar_.costs_1000trx.v3.png](https://www.percona.com/wp-content/uploads/2026/03/aws.final_.16_5.bar_.costs_1000trx.v3.png)

## Auteur source

Alexey Stroganov is a Performance Engineer at Percona, where he works on improvements and features that makes Percona Server even more flexible, faster and scalable. Before joining Percona he worked on the performance testings/analysis of MySQL server and it components at MySQL AB/Sun/Oracle for more than ten years. During this time he was focused on performance evaluations, benchmarks, analysis, profiling, various optimizations and tunings.
