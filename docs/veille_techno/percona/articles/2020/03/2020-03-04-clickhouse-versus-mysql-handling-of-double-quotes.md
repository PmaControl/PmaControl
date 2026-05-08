---
title: ClickHouse Versus MySQL Handling of Double Quotes
source:
  name: Percona Blog
  url: https://www.percona.com/blog/clickhouse-versus-mysql-handling-of-double-quotes/
  post_id: 21793
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2020-03-04T20:34:35'
published_at_gmt: '2020-03-04T20:34:35'
modified_at: '2026-05-05T17:57:26'
modified_at_gmt: '2026-05-05T17:57:26'
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
- insight for DBAs
- MySQL
tag_slugs:
- clickhouse
- insight-for-dbas
- mysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/ClickHouse-MySQL-Double-Quotes.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# ClickHouse Versus MySQL Handling of Double Quotes

Source: [Percona Blog](https://www.percona.com/blog/clickhouse-versus-mysql-handling-of-double-quotes/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2020-03-04T20:34:35

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

If you’re a MySQL user trying ClickHouse, one thing which is likely to surprise – and annoy you – is the handling of Double Quotes. In MySQL, you can use both double quotes and single quotes to quote strings, and as an example, these two queries are equivalent: Shell mysql> select * from performance_schema.global_variables where variable_name='max_connections'; +-----------------+----------------+ | VARIABLE_NAME | VARIABLE_VALUE | +-----------------+----------------+ | max_connections | 151 | +-----------------+----------------+ 1 row in set (0.01 sec) mysql> select * from performance_schema.global_variables where variable_name="max_connections"; +-----------------+----------------+ | VARIABLE_NAME | VARIABLE_VALUE | +-----------------+----------------+ | max_connections | 151 | +-----------------+----------------+ 1 row in set (0.00 sec) 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 mysql > s...

## Images et graphiques reperes

- featured / image: [ClickHouse Versus MySQL Handling of Double Quotes](https://www.percona.com/wp-content/uploads/2026/03/ClickHouse-MySQL-Double-Quotes.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
