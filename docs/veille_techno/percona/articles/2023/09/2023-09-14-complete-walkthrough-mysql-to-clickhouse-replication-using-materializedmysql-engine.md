---
title: 'Complete Walkthrough: MySQL to ClickHouse Replication Using MaterializedMySQL Engine'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/complete-walkthrough-mysql-to-clickhouse-replication-using-materializedmysql-engine/
  post_id: 27462
source_author:
  name: Sri Sakthivel
  slug: sri-sakthivel
  url: https://www.percona.com/blog/author/sri-sakthivel/
  website: ''
published_at: '2023-09-14T11:57:11'
published_at_gmt: '2023-09-14T11:57:11'
modified_at: '2026-03-26T20:27:15'
modified_at_gmt: '2026-03-26T20:27:15'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- ClickHouse
- MySQL
- mysql-and-variants
tag_slugs:
- clickhouse
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/my-1.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Complete Walkthrough: MySQL to ClickHouse Replication Using MaterializedMySQL Engine

Source: [Percona Blog](https://www.percona.com/blog/complete-walkthrough-mysql-to-clickhouse-replication-using-materializedmysql-engine/)

Auteur source: [Sri Sakthivel](https://www.percona.com/blog/author/sri-sakthivel/)

Publication: 2023-09-14T11:57:11

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MySQL is an outstanding open source transactional database used by most web-based applications and is very good at handling OLTP workloads. However, modern business is very much dependent on analytical data. ClickHouse is a columnar database that handles analytical workloads quickly. I recommend you read our previous blog, Using ClickHouse as an Analytic Extension for … Continued

## Structure detectee

- H2: MaterializedMySQL Engine – Overview
- H2: Prerequisites for the replication
- H2: Replication setup
- H2: UPDATE on MySQL
- H2: Understanding ReplacingMergeTree
- H2: What happens if the replication event fails?
- H2: What happens if MySQL or ClickHouse restarted?
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Complete Walkthrough: MySQL to ClickHouse Replication Using MaterializedMySQL Engine](https://www.percona.com/wp-content/uploads/2026/03/my-1.jpg)

## Auteur source

Oracle certified MySQL DBA. Working on MySQL and related technologies to ensures database performance. Handling multi client projects round the clock. Currently focusing on MySQL Cluster technologies like Galera and Group replication/InnoDB cluster. Active MySQL Blogger.
