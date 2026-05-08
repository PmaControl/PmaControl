---
title: Using ClickHouse as an Analytic Extension for MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-clickhouse-as-an-analytic-extension-for-mysql/
  post_id: 26123
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2022-10-13T14:31:30'
published_at_gmt: '2022-10-13T14:31:30'
modified_at: '2026-03-26T20:30:58'
modified_at_gmt: '2026-03-26T20:30:58'
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
- Open Source
- Percona Software
category_slugs:
- mysql
- open-source
- percona-software
tags:
- Altinity
- analytics
- ClickHouse
- MySQL
- mysql-and-variants
- Partners
tag_slugs:
- altinity
- analytics
- clickhouse
- mysql
- mysql-and-variants
- partners
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/ClickHouse-as-an-Analytic-Extension-for-MySQL.png
image_count: 9
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using ClickHouse as an Analytic Extension for MySQL

Source: [Percona Blog](https://www.percona.com/blog/using-clickhouse-as-an-analytic-extension-for-mysql/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2022-10-13T14:31:30

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MySQL is an outstanding database for online transaction processing. With suitable hardware, it is easy to execute more than 1M queries per second and handle tens of thousands of simultaneous connections. Many of the most demanding web applications on the planet are built on MySQL. With capabilities like that, why would MySQL users need anything … Continued

## Structure detectee

- H2: Signs that indicate MySQL needs analytic help
- H3: Huge tables of immutable data mixed in with transaction tables
- H3: Complex aggregation pipelines
- H3: MySQL is too slow or inflexible to answer important business questions
- H2: Why is ClickHouse a natural complement to MySQL?
- H2: Why is MySQL a natural complement to ClickHouse?
- H2: Introducing ClickHouse to MySQL integration
- H3: Viewing MySQL data from ClickHouse
- H3: Moving MySQL data to ClickHouse
- H3: Mirroring MySQL Data in ClickHouse
- H2: Tooling improvements are on the way!
- H2: Wrapping up and getting started

## Images et graphiques reperes

- featured / image: [Using ClickHouse as an Analytic Extension for MySQL](https://www.percona.com/wp-content/uploads/2026/03/ClickHouse-as-an-Analytic-Extension-for-MySQL.png)
- content / image: [ClickHouse as an Analytic Extension for MySQL](https://www.percona.com/wp-content/uploads/2026/03/ClickHouse-as-an-Analytic-Extension-for-MySQL-300x157.png)
- content / image: [altinity-0.png](https://www.percona.com/wp-content/uploads/2026/03/altinity-0.png)
- content / image: [altinity-1.png](https://www.percona.com/wp-content/uploads/2026/03/altinity-1.png)
- content / image: [altinity-2.png](https://www.percona.com/wp-content/uploads/2026/03/altinity-2.png)
- content / image: [altinity-3.png](https://www.percona.com/wp-content/uploads/2026/03/altinity-3.png)
- content / image: [altinity-4.png](https://www.percona.com/wp-content/uploads/2026/03/altinity-4.png)
- content / image: [altinity-5.png](https://www.percona.com/wp-content/uploads/2026/03/altinity-5.png)
- content / image: [altinity-6.png](https://www.percona.com/wp-content/uploads/2026/03/altinity-6.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
