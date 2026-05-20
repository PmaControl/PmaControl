---
title: Understanding MySQL Memory Usage with Performance Schema
source:
  name: Percona Blog
  url: https://www.percona.com/blog/understanding-mysql-memory-usage-with-performance-schema/
  post_id: 23428
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2020-11-02T18:18:37'
published_at_gmt: '2020-11-02T18:18:37'
modified_at: '2026-04-27T22:16:41'
modified_at_gmt: '2026-04-27T22:16:41'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:monitoring:2104
- category:mysql:83
- search:percona-monitoring-and-management
- search:pmm
- tag:percona-monitoring-and-management:2166
categories:
- Insight for DBAs
- Monitoring
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- monitoring
- mysql
- percona-software
tags:
- insight for DBAs
- Monitoring
- MySQL
- mysql-and-variants
- Percona Monitoring and Management
- Percona Software
tag_slugs:
- insight-for-dbas
- monitoring
- mysql
- mysql-and-variants
- percona-monitoring-and-management
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Memory-Usage-with-Performance-Schema.png
image_count: 7
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Understanding MySQL Memory Usage with Performance Schema

Source: [Percona Blog](https://www.percona.com/blog/understanding-mysql-memory-usage-with-performance-schema/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2020-11-02T18:18:37

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Understanding how MySQL uses memory is key to tuning it for optimal performance as well as troubleshooting cases of unexpected memory usage, i.e. when you have MySQL Server using a lot more than you would expect based on your configuration settings. Early in MySQL history, understanding memory usage details was hard and included a lot … Continued

## Structure detectee

- H2: MySQL Custom Queries

## Images et graphiques reperes

- featured / image: [Understanding MySQL Memory Usage with Performance Schema](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Memory-Usage-with-Performance-Schema.png)
- content / image: [MySQL Memory Usage with Performance Schema](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Memory-Usage-with-Performance-Schema-300x168.png)
- content / graph_or_chart: [MySQL Memory Usage Details dashboard](https://www.percona.com/wp-content/uploads/2026/03/image3-7-1024x145.png)
- content / image: [MySQL Memory Usage Summary](https://www.percona.com/wp-content/uploads/2026/03/image4-9-1024x266.png)
- content / image: [MySQL Memory Usage by host](https://www.percona.com/wp-content/uploads/2026/03/image5-6-1024x466.png)
- content / image: [MySQL memory usage](https://www.percona.com/wp-content/uploads/2026/03/image1-9-1024x240.png)
- content / image: [image2-8-1024x392.png](https://www.percona.com/wp-content/uploads/2026/03/image2-8-1024x392.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
