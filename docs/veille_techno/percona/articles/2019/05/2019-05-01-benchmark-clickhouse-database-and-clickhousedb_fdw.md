---
title: Benchmark ClickHouse Database and clickhousedb_fdw
source:
  name: Percona Blog
  url: https://www.percona.com/blog/benchmark-clickhouse-database-and-clickhousedb_fdw/
  post_id: 20205
source_author:
  name: Ibrar Ahmed
  slug: ibrar-ahmed
  url: https://www.percona.com/blog/author/ibrar-ahmed/
  website: ''
published_at: '2019-05-01T16:37:29'
published_at_gmt: '2019-05-01T16:37:29'
modified_at: '2026-03-26T20:10:04'
modified_at_gmt: '2026-03-26T20:10:04'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Benchmarks
- MySQL
- PostgreSQL
category_slugs:
- benchmarks
- mysql
- postgresql
tags:
- foreign data wrapper
tag_slugs:
- foreign-data-wrapper
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/ch_ch_fdw-1.png
image_count: 2
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Benchmark ClickHouse Database and clickhousedb_fdw

Source: [Percona Blog](https://www.percona.com/blog/benchmark-clickhouse-database-and-clickhousedb_fdw/)

Auteur source: [Ibrar Ahmed](https://www.percona.com/blog/author/ibrar-ahmed/)

Publication: 2019-05-01T16:37:29

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this research, I wanted to see what kind of performance improvements could be gained by using a ClickHouse data source rather than PostgreSQL. Assuming that I would see performance advantages with using ClickHouse, would those advantages be retained if I access ClickHouse from within postgres using a foreign data wrapper (FDW)? The FDW in … Continued

## Structure detectee

- H2: Clickhouse Database
- H2: Clickhousedb_fdw
- H3: Benchmark environment
- H2: Benchmark tests
- H4: Benchmark Queries
- H4: Query executions
- H3: Reviewing the results
- H2: Conclusion

## Images et graphiques reperes

- featured / graph_or_chart: [Benchmark ClickHouse Database and clickhousedb_fdw](https://www.percona.com/wp-content/uploads/2026/03/ch_ch_fdw-1.png)
- content / image: [Clickhouse Vs Clickhousedb_fdw (Shows the overhead of clickhousedb_fdw)](https://www.percona.com/wp-content/uploads/2026/03/ch_ch_fdw-1024x298.png)
  Caption: Clickhouse Vs Clickhousedb_fdw (Shows the overhead of clickhousedb_fdw)

## Auteur source

Joined Percona in the month of July 2018. Before joining Percona, Ibrar worked as a Senior Database Architect at EnterpriseDB for 10 Years. Ibrar has 18 years of software development experience. Ibrar authored multiple books on PostgreSQL.
