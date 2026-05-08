---
title: 'MariaDB S3 Engine: Implementation and Benchmarking'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mariadb-s3-engine-implementation-and-benchmarking/
  post_id: 22765
source_author:
  name: Sri Sakthivel
  slug: sri-sakthivel
  url: https://www.percona.com/blog/author/sri-sakthivel/
  website: ''
published_at: '2020-07-17T16:31:24'
published_at_gmt: '2020-07-17T16:31:24'
modified_at: '2026-04-16T15:59:20'
modified_at_gmt: '2026-04-16T15:59:20'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MariaDB
matched_filters:
- category:mariadb:1281
categories:
- Benchmarks
- Insight for DBAs
- MariaDB
- Storage Engine
category_slugs:
- benchmarks
- insight-for-dbas
- mariadb
- storage-engine
tags:
- Benchmarks
- insight for DBAs
- MariaDB
- mysql-and-variants
- S3
- Storage Engine
tag_slugs:
- benchmarks
- insight-for-dbas
- mariadb
- mysql-and-variants
- s3
- storage-engine
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MariaDB-S3-Engine.png
image_count: 5
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MariaDB S3 Engine: Implementation and Benchmarking

Source: [Percona Blog](https://www.percona.com/blog/mariadb-s3-engine-implementation-and-benchmarking/)

Auteur source: [Sri Sakthivel](https://www.percona.com/blog/author/sri-sakthivel/)

Publication: 2020-07-17T16:31:24

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MariaDB no longer meeting your needs? Migrate to Percona software for MySQL – an open source, production-ready, and enterprise-grade MySQL alternative. Learn More MariaDB 10.5 includes an S3 storage engine plugin based on Aria. Its main feature is the ability to move tables from local storage to S3 using ALTER TABLE, while still allowing access … Continued

## Structure detectee

- H3: MariaDB no longer meeting your needs?
- H2: S3 Engine Implementation
- H2: Moving Tables to S3
- H2: S3 Engine Operations
- H4: SELECT
- H4: INSERT / UPDATE / DELETE
- H4: Index and Schema Changes
- H4: DROP TABLE
- H2: S3 vs Local Performance
- H3: COUNT(*)
- H3: Full Table Scan
- H3: Primary Key Lookup
- H2: Performance Considerations
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [MariaDB S3 Engine: Implementation and Benchmarking](https://www.percona.com/wp-content/uploads/2026/03/MariaDB-S3-Engine.png)
- content / image: [MariaDB S3 Engine](https://www.percona.com/wp-content/uploads/2026/03/MariaDB-S3-Engine-300x168.png)
- content / image: [S3-vs-Local-Count.png](https://www.percona.com/wp-content/uploads/2026/03/S3-vs-Local-Count.png)
- content / image: [S3-vs-Local-Entire-table-data-.png](https://www.percona.com/wp-content/uploads/2026/03/S3-vs-Local-Entire-table-data-.png)
- content / image: [S3-vs-Local-PRIMARY-KEY-based-lookup-.png](https://www.percona.com/wp-content/uploads/2026/03/S3-vs-Local-PRIMARY-KEY-based-lookup-.png)

## Auteur source

Oracle certified MySQL DBA. Working on MySQL and related technologies to ensures database performance. Handling multi client projects round the clock. Currently focusing on MySQL Cluster technologies like Galera and Group replication/InnoDB cluster. Active MySQL Blogger.
