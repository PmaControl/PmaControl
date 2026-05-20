---
title: Maximizing Performance of AWS RDS for MySQL with Dedicated Log Volumes
source:
  name: Percona Blog
  url: https://www.percona.com/blog/maximizing-performance-of-aws-rds-for-mysql-with-dedicated-log-volumes/
  post_id: 27775
source_author:
  name: Kedar Vaijanapurkar
  slug: kedar-vaijanapurkar
  url: https://www.percona.com/blog/author/kedar-vaijanapurkar/
  website: http://kedar.nitty-witty.com/blog
published_at: '2023-12-11T15:09:52'
published_at_gmt: '2023-12-11T15:09:52'
modified_at: '2026-03-26T20:26:53'
modified_at_gmt: '2026-03-26T20:26:53'
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
- Insight for DBAs
- MySQL
category_slugs:
- cloud
- insight-for-dbas
- mysql
tags:
- AWS RDS
- Dedicated Log Volumes
- MySQL
- mysql-and-variants
tag_slugs:
- aws-rds
- dedicated-log-volumes
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/lucas.speyer_the_texture_inside_software_teal_and_blue_colors_1272a8db-4250-4738-b48c-8758eba6151c.png
image_count: 5
graph_or_chart_count: 3
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Maximizing Performance of AWS RDS for MySQL with Dedicated Log Volumes

Source: [Percona Blog](https://www.percona.com/blog/maximizing-performance-of-aws-rds-for-mysql-with-dedicated-log-volumes/)

Auteur source: [Kedar Vaijanapurkar](https://www.percona.com/blog/author/kedar-vaijanapurkar/)

Publication: 2023-12-11T15:09:52

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

A quick configuration change may do the trick in improving the performance of your AWS RDS for MySQL instance. Here, we will discuss a notable new feature in Amazon RDS, the Dedicated Log Volume (DLV), that has been introduced to boost database performance. While this discussion primarily targets MySQL instances, the principles are also relevant to … Continued

## Structure detectee

- H2: What is a Dedicated Log Volume (DLV)?
- H2: Who can benefit from DLV?
- H2: Cost of enabling Dedicated Log Volumes (DLV) in RDS
- H2: Are DLVs effective for your RDS instance?
- H2: Benchmarking AWS RDS DLV setup
- H3: Benchmark results for DLV-enabled instance vs. standard instance
- H2: Implementation considerations
- H3: Conclusion
- H2: How Percona can help

## Images et graphiques reperes

- featured / image: [Maximizing Performance of AWS RDS for MySQL with Dedicated Log Volumes](https://www.percona.com/wp-content/uploads/2026/03/lucas.speyer_the_texture_inside_software_teal_and_blue_colors_1272a8db-4250-4738-b48c-8758eba6151c.png)
- content / image: [aws-calculator-dlv-cost-estimate.png](https://www.percona.com/wp-content/uploads/2026/03/aws-calculator-dlv-cost-estimate.png)
- content / graph_or_chart: [AWS RDS for MySQL - DLV benchmarking](https://www.percona.com/wp-content/uploads/2026/03/benchmark-rds-mysql-dlv1-1024x628.png)
- content / graph_or_chart: [AWS RDS for MySQL - DLV benchmarking](https://www.percona.com/wp-content/uploads/2026/03/benchmark-rds-mysql-dlv2-1024x632.png)
- content / graph_or_chart: [AWS RDS for MySQL - DLV benchmarking](https://www.percona.com/wp-content/uploads/2026/03/benchmark-rds-mysql-dlv3-1024x629.png)

## Auteur source

Kedar Vaijanapurkar is a Tier 2 MySQL DBA at Percona since Oct 2021. He's experienced in MySQL related technologies and constantly working on improving skills. He lives in the cultural city of Vadodara with his SPOF (wife) and two HA (highly active) replicas.
