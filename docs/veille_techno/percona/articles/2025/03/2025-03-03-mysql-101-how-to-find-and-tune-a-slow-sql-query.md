---
title: 'MySQL 101: How to Find and Tune a Slow MySQL Query'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-101-how-to-find-and-tune-a-slow-sql-query/
  post_id: 22648
source_author:
  name: Mike Benshoof
  slug: mbenshoof
  url: https://www.percona.com/blog/author/mbenshoof/
  website: ''
published_at: '2025-03-03T13:02:51'
published_at_gmt: '2025-03-03T13:02:51'
modified_at: '2026-03-26T20:25:42'
modified_at_gmt: '2026-03-26T20:25:42'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- Percona Toolkit
matched_filters:
- category:monitoring:2104
- category:mysql:83
- search:percona-monitoring-and-management
- search:percona-toolkit
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
- Percona Monitoring and Management
tag_slugs:
- insight-for-dbas
- monitoring
- mysql
- percona-monitoring-and-management
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/tune-a-slow-sql-query.png
image_count: 5
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL 101: How to Find and Tune a Slow MySQL Query

Source: [Percona Blog](https://www.percona.com/blog/mysql-101-how-to-find-and-tune-a-slow-sql-query/)

Auteur source: [Mike Benshoof](https://www.percona.com/blog/author/mbenshoof/)

Publication: 2025-03-03T13:02:51

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This blog was originally published in June 2020 and was updated in March 2025. One of the most common support tickets we get at Percona is the infamous “database is running slower” ticket. While this can be caused by a multitude of factors, it is more often than not caused by a bad or slow … Continued

## Structure detectee

- H2: Fixing Slow Queries With Percona Monitoring and Management
- H2: Fixing Slow Queries Without Percona Monitoring and Management
- H3: Limiting Rows Examined
- H3: Optimizing Sorts
- H2: In summary, the general process to find and tune a slow MySQL query follows this process:
- H2: Struggling with Slow Queries? Percona Monitoring and Management Can Help!
- H2: FAQs
- H3: What defines a query as “slow” in MySQL?
- H3: How do I improve query speed in MySQL?
- H3: What common issues cause queries to slow down in MySQL?

## Images et graphiques reperes

- featured / image: [MySQL 101: How to Find and Tune a Slow MySQL Query](https://www.percona.com/wp-content/uploads/2026/03/tune-a-slow-sql-query.png)
- content / image: [Percona Monitoring and Management (PMM) Query Analytics table helping to find slow MySQL queries.](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-06-15-at-2.52.02-PM-1024x349.png)
- content / image: [Percona Monitoring and Management queries](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-06-15-at-2.53.04-PM-1024x374.png)
- content / image: [Screen-Shot-2020-06-15-at-2.54.17-PM-1024x320.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-06-15-at-2.54.17-PM-1024x320.png)
- content / image: [mysql-performance-tuning-1.png](https://www.percona.com/wp-content/uploads/2026/03/mysql-performance-tuning-1.png)

## Auteur source

Michael joined Percona in 2012 as a US based consultant and is currently a Technical Account Manager. Prior to joining Percona, Michael spent several years in a DevOps role maintaining a SaaS application specializing in social networking. His experiences include application development and scaling, systems administration, along with database administration and design. He enjoys designing extensible and flexible solutions to problems. When not working, he enjoys time outdoors, grilling, most sports, and spending time with the family.
