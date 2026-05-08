---
title: 'MySQL Query Performance Troubleshooting: Resource-Based Approach'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-query-performance-troubleshooting-resource-based-approach/
  post_id: 22745
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2020-07-15T17:00:15'
published_at_gmt: '2020-07-15T17:00:15'
modified_at: '2026-03-23T15:31:44'
modified_at_gmt: '2026-03-23T15:31:44'
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
- tag:pmm:2167
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
- MySQL
- mysql-and-variants
- Percona Monitoring and Management
- Percona Software
- PMM
tag_slugs:
- insight-for-dbas
- mysql
- mysql-and-variants
- percona-monitoring-and-management
- percona-software
- pmm
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Query-Performance-Troubleshooting.png
image_count: 16
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL Query Performance Troubleshooting: Resource-Based Approach

Source: [Percona Blog](https://www.percona.com/blog/mysql-query-performance-troubleshooting-resource-based-approach/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2020-07-15T17:00:15

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

When I speak about MySQL performance troubleshooting (or frankly any other database), I tend to speak about four primary resources which typically end up being a bottleneck and limiting system performance: CPU, Memory, Disk, and Network. It would be great if when seeing what resource is a bottleneck, we could also easily see what queries … Continued

## Structure detectee

- H2: CPU Analysis
- H2: Disk IO Analysis
- H2: Network Analysis
- H2: Memory Analysis
- H2: Locking Analysis

## Images et graphiques reperes

- featured / image: [MySQL Query Performance Troubleshooting: Resource-Based Approach](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Query-Performance-Troubleshooting.png)
- content / image: [MySQL Query Performance Troubleshooting](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Query-Performance-Troubleshooting-300x168.png)
- content / image: [MySQL Query Performance Troubleshooting](https://www.percona.com/wp-content/uploads/2026/03/1-10-1024x293.png)
- content / image: [CPU Analysis](https://www.percona.com/wp-content/uploads/2026/03/2-9-1024x275.png)
- content / image: [CPU Intensive Queries](https://www.percona.com/wp-content/uploads/2026/03/3-10-1024x214.png)
- content / image: [Query Analytics](https://www.percona.com/wp-content/uploads/2026/03/4-12-1024x345.png)
- content / image: [5-5-1024x141.png](https://www.percona.com/wp-content/uploads/2026/03/5-5-1024x141.png)
- content / image: [Disk IO Analysis](https://www.percona.com/wp-content/uploads/2026/03/6-5-1024x274.png)
- content / image: [IOScore](https://www.percona.com/wp-content/uploads/2026/03/7-4-1024x216.png)
- content / image: [ClickHouse Query](https://www.percona.com/wp-content/uploads/2026/03/8-3-1024x233.png)
- content / image: [Network Analysis](https://www.percona.com/wp-content/uploads/2026/03/9-1-1024x348.png)
- content / image: [10-4-1024x268.png](https://www.percona.com/wp-content/uploads/2026/03/10-4-1024x268.png)
- content / image: [Memory Analysis](https://www.percona.com/wp-content/uploads/2026/03/11-3-1024x404.png)
- content / image: [12-1-1024x273.png](https://www.percona.com/wp-content/uploads/2026/03/12-1-1024x273.png)
- content / image: [Locking Analysis](https://www.percona.com/wp-content/uploads/2026/03/13-1-1024x189.png)
- content / image: [14-1-1024x266.png](https://www.percona.com/wp-content/uploads/2026/03/14-1-1024x266.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
