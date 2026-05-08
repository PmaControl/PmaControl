---
title: PostgreSQL Accessing MySQL as a Data Source Using mysql_fdw
source:
  name: Percona Blog
  url: https://www.percona.com/blog/postgresql-accessing-mysql-as-a-data-source-using-mysql_fdw/
  post_id: 19218
source_author:
  name: Jobin Augustine
  slug: jobin-augustine
  url: https://www.percona.com/blog/author/jobin-augustine/
  website: ''
published_at: '2018-08-24T15:38:55'
published_at_gmt: '2018-08-24T15:38:55'
modified_at: '2026-03-26T20:11:09'
modified_at_gmt: '2026-03-26T20:11:09'
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
- PostgreSQL
category_slugs:
- mysql
- postgresql
tags:
- foreign tables
- MySQL views
- views
tag_slugs:
- foreign-tables
- mysql-views
- views
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/mysql-postgresql-linked.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# PostgreSQL Accessing MySQL as a Data Source Using mysql_fdw

Source: [Percona Blog](https://www.percona.com/blog/postgresql-accessing-mysql-as-a-data-source-using-mysql_fdw/)

Auteur source: [Jobin Augustine](https://www.percona.com/blog/author/jobin-augustine/)

Publication: 2018-08-24T15:38:55

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

There are many organizations where front/web-facing applications use MySQL and back end processing uses PostgreSQL®. Any system integration between these applications generally involves the replication—or duplication—of data from system to system. We recently blogged about pg_chameleon which can be used to replicate data from MySQL® to PostgreSQL. mysql_fdw can play a key role in eliminating … Continued

## Structure detectee

- H2: Preparing MySQL for fdw connectivity
- H2: Installing mysql_fdw on PostgreSQL server
- H2: Import schema objects
- H2: Foreign tables with a subset of columns
- H2: The challenges of incompatible syntax and datatypes
- H2: Handling views on the MySQL side
- H2: Views on the top of foreign table on PostgreSQL
- H2: Materializing the foreign tables (Materialized Views)
- H2: Automated Cleanup
- H2: Conclusion
- H4: Percona’s support for PostgreSQL
- H4: You May Also Like

## Images et graphiques reperes

- featured / image: [PostgreSQL Accessing MySQL as a Data Source Using mysql_fdw](https://www.percona.com/wp-content/uploads/2026/03/mysql-postgresql-linked.jpg)
- content / image: [PostgreSQL foreign tables in MySQL](https://www.percona.com/wp-content/uploads/2026/03/mysql-postgresql-linked-300x199.jpg)

## Auteur source

Jobin Augustine is a PostgreSQL expert, enthusiast, and Open Source advocate with more than 25 years of experience as a consultant, architect, administrator, writer, developer, and trainer. He is an active participant in Open Source communities, with a primary focus on database performance and optimization. A contributor to various open-source projects and an active blogger, Jobin also loves to code in C++ and Python. He is a senior member of the PostgreSQL community in India and a regular speaker at many international conferences. Jobin holds a Master’s in Computer Applications from NIT Calicut. He joined Percona in 2018 to launch their PostgreSQL chapter and currently serves as the Tech Lead for PostgreSQL. Prior to Percona, he worked at OpenSCG as an architect and was part of the BigSQL core team. His earlier career includes a decade-long tenure at Dell as a Senior Database Advisor, along with roles at several other technology firms.
