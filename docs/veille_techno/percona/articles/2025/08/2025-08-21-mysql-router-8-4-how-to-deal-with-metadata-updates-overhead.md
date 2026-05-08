---
title: 'MySQL Router 8.4: How to Deal with Metadata Updates Overhead'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-router-8-4-how-to-deal-with-metadata-updates-overhead/
  post_id: 35222
source_author:
  name: Przemysław Malkowski
  slug: przemek-malkowski
  url: https://www.percona.com/blog/author/przemek-malkowski/
  website: ''
published_at: '2025-08-21T13:40:59'
published_at_gmt: '2025-08-21T13:40:59'
modified_at: '2026-03-26T20:25:20'
modified_at_gmt: '2026-03-26T20:25:20'
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
- search:percona-monitoring-and-management
- search:pmm
- search:proxysql
categories:
- Insight for DBAs
- Insight for Developers
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- group replication
- MySQL Router
- mysql-and-variants
tag_slugs:
- group-replication
- mysql-router
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Router-8.4-Metadata-Updates-Overhead.jpg
image_count: 8
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL Router 8.4: How to Deal with Metadata Updates Overhead

Source: [Percona Blog](https://www.percona.com/blog/mysql-router-8-4-how-to-deal-with-metadata-updates-overhead/)

Auteur source: [Przemysław Malkowski](https://www.percona.com/blog/author/przemek-malkowski/)

Publication: 2025-08-21T13:40:59

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

It may be surprising when a new InnoDB Cluster is set up, and despite not being in production yet and completely idle, it manifests a significant amount of writes visible in growing binary logs. This effect became much more spectacular after MySQL version 8.4. In this write-up, I will explain why it happens and how to address … Continued

## Structure detectee

- H2: MySQL Router 8.4 metadata enhancements
- H2: Binlog MINIMAL image
- H2: Group replication notifications
- H3: Summary

## Images et graphiques reperes

- featured / image: [MySQL Router 8.4: How to Deal with Metadata Updates Overhead](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Router-8.4-Metadata-Updates-Overhead.jpg)
- content / image: [MySQL-Vector-Search-Survey.png](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Vector-Search-Survey.png)
- content / image: [MySQL InnoDB Cluster](https://www.percona.com/wp-content/uploads/2026/03/innodb2.drawio.png)
- content / image: [binlog data](https://www.percona.com/wp-content/uploads/2026/03/2025-07-25_01-50.png)
- content / image: [2025-07-25_16-28.png](https://www.percona.com/wp-content/uploads/2026/03/2025-07-25_16-28.png)
- content / image: [2025-07-26_00-03.png](https://www.percona.com/wp-content/uploads/2026/03/2025-07-26_00-03.png)
- content / image: [2025-07-26_00-29.png](https://www.percona.com/wp-content/uploads/2026/03/2025-07-26_00-29.png)
- content / image: [mysql-performance-tuning-11.png](https://www.percona.com/wp-content/uploads/2026/03/mysql-performance-tuning-11.png)

## Auteur source

Przemek joined Support Team at Percona in August 2012. Before that he spent over five years working for Wikia.com (Quantcast Top 50) as System Administrator where he was a key person responsible for seamless building up MySQL powered database infrastructure. Besides MySQL he worked on maintaining all other parts of LAMP stack, with main focus on automation, monitoring and backups.
