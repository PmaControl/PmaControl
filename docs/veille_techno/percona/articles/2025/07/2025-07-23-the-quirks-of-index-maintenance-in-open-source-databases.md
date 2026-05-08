---
title: The Quirks of Index Maintenance in Open Source Databases
source:
  name: Percona Blog
  url: https://www.percona.com/blog/the-quirks-of-index-maintenance-in-open-source-databases/
  post_id: 35097
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2025-07-23T14:04:46'
published_at_gmt: '2025-07-23T14:04:46'
modified_at: '2026-03-26T20:25:22'
modified_at_gmt: '2026-03-26T20:25:22'
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
- MongoDB
- MySQL
- Open Source
- PostgreSQL
category_slugs:
- insight-for-dbas
- mongodb
- mysql
- open-source
- postgresql
tags:
- indexing
- Open Source
tag_slugs:
- indexing
- open-source
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/index-maintenance-open-source.jpg
image_count: 7
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# The Quirks of Index Maintenance in Open Source Databases

Source: [Percona Blog](https://www.percona.com/blog/the-quirks-of-index-maintenance-in-open-source-databases/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2025-07-23T14:04:46

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Index maintenance can be a real challenge for anyone managing databases, and what makes it even trickier is that open source databases each handle it differently. In this post, we’ll take a closer look at how those differences show up in practice, and what they mean for you. When rows are added, updated, or deleted … Continued

## Structure detectee

- H2: Insertion results
- H3: MySQL InnoDB
- H3: MySQL MyRocks/RocksDB
- H3: MySQL InnoDB compression
- H3: PostgreSQL
- H3: MongoDB
- H2: Insert rate stability
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [The Quirks of Index Maintenance in Open Source Databases](https://www.percona.com/wp-content/uploads/2026/03/index-maintenance-open-source.jpg)
- content / image: [MySQL-Vector-Search-Survey.png](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Vector-Search-Survey.png)
- content / image: [Read and Write IOPs](https://www.percona.com/wp-content/uploads/2026/03/dbbench-insert-IOPs.png)
  Caption: Read and write IOPs for 10M rows inserted
- content / image: [InnoDB insert rate stability](https://www.percona.com/wp-content/uploads/2026/03/InnoDB_inserts.png)
  Caption: InnoDB insert rate stability
- content / image: [MyRocks/RocksDB insert rate stability](https://www.percona.com/wp-content/uploads/2026/03/Rocksdb_inserts.png)
  Caption: MyRocks/RocksDB insert rate stability
- content / image: [PostgreSQL mysterious insert stalls](https://www.percona.com/wp-content/uploads/2026/03/Pg_insert_dips_3.png)
  Caption: PostgreSQL mysterious insert stalls
- content / image: [PostgreSQL insert rate with statistics refresh disabled.](https://www.percona.com/wp-content/uploads/2026/03/Pg_inserts_stable_best.png)
  Caption: PostgreSQL insert rate with statistics refresh disabled.

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
