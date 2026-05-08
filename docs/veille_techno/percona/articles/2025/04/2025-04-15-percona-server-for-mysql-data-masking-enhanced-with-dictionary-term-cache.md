---
title: Percona Server for MySQL Data Masking Enhanced with Dictionary Term Cache
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-server-for-mysql-data-masking-enhanced-with-dictionary-term-cache/
  post_id: 34776
source_author:
  name: Yura Sorokin
  slug: yura-sorokin
  url: https://www.percona.com/blog/author/yura-sorokin/
  website: ''
published_at: '2025-04-15T15:32:58'
published_at_gmt: '2025-04-15T15:32:58'
modified_at: '2026-03-26T20:25:37'
modified_at_gmt: '2026-03-26T20:25:37'
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
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- Data Masking
- MySQL
- Percona Server for MySQL
tag_slugs:
- data-masking
- mysql
- percona-server
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-Server-for-MySQL-Data-Masking-Enhanced-with-Dictionary-Term-Cache.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona Server for MySQL Data Masking Enhanced with Dictionary Term Cache

Source: [Percona Blog](https://www.percona.com/blog/percona-server-for-mysql-data-masking-enhanced-with-dictionary-term-cache/)

Auteur source: [Yura Sorokin](https://www.percona.com/blog/author/yura-sorokin/)

Publication: 2025-04-15T15:32:58

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In Percona Server for MySQL 8.0.41 / 8.4.4, we significantly re-designed the Data Masking Component. In particular, we made the following changes: Changed the user on behalf of whom we execute internal queries for dictionary operations. Introduced an in-memory dictionary term cache that allows significant speed-up of dictionary operations. Introduced masking_dictionaries_flush() User Defined Function. Introduced … Continued

## Structure detectee

- H2: Internal SQL user
- H2: In-memory dictionary term cache
- H2: Manual dictionary cache reloading
- H2: Periodic dictionary term cache reloading
- H2: Custom location for dictionary table
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Percona Server for MySQL Data Masking Enhanced with Dictionary Term Cache](https://www.percona.com/wp-content/uploads/2026/03/Percona-Server-for-MySQL-Data-Masking-Enhanced-with-Dictionary-Term-Cache.jpg)
- content / image: [MySQL performance tuning](https://www.percona.com/wp-content/uploads/2026/03/mysql-performance-tuning-7.png)

## Auteur source

Yura is a Principal Software Engineer at Percona, mostly working on Percona Server Core. You might have heard of him as an author of "Compressed Columns with Dictionaries", "SEQUENCE_TABLE()" and "C++ UDF wrappers". Before joining in July 2015 he was leading a cloud file service backend dev team which was focusing on client-side encryption. He has 20+ years of software development experience, primarily in C++. Yura holds Master degree in Computer Science from National Technical University of Ukraine. He lives in Kyiv, Ukraine.
