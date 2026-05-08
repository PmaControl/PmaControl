---
title: ClickHouse Performance Uint32 vs Uint64 vs Float32 vs Float64
source:
  name: Percona Blog
  url: https://www.percona.com/blog/clickhouse-performance-uint32-vs-uint64-vs-float32-vs-float64/
  post_id: 20020
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2019-02-15T13:23:18'
published_at_gmt: '2019-02-15T13:23:18'
modified_at: '2026-04-27T21:11:53'
modified_at_gmt: '2026-04-27T21:11:53'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:pmm
categories:
- Benchmarks
- Insight for DBAs
- MySQL
category_slugs:
- benchmarks
- insight-for-dbas
- mysql
tags:
- compression
- data compression
tag_slugs:
- compression
- data-compression
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Q1-least-compression.png
image_count: 7
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# ClickHouse Performance Uint32 vs Uint64 vs Float32 vs Float64

Source: [Percona Blog](https://www.percona.com/blog/clickhouse-performance-uint32-vs-uint64-vs-float32-vs-float64/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2019-02-15T13:23:18

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

While implementing ClickHouse for query executions statistics storage in Percona Monitoring and Management (PMM), we were faced with a question of choosing the data type for metrics we store. It came down to this question: what is the difference in performance and space usage between Uint32, Uint64, Float32, and Float64 column types? To test this, … Continued

## Structure detectee

- H2: Query Performance
- H2: Compression
- H2: Summary

## Images et graphiques reperes

- featured / image: [ClickHouse Performance Uint32 vs Uint64 vs Float32 vs Float64](https://www.percona.com/wp-content/uploads/2026/03/Q1-least-compression.png)
- content / image: [Q2 least compression](https://www.percona.com/wp-content/uploads/2026/03/Q2-least-compression.png)
- content / image: [Q1 GB per second](https://www.percona.com/wp-content/uploads/2026/03/Q1-GB-per-second.png)
- content / image: [On disk data size for UINT32](https://www.percona.com/wp-content/uploads/2026/03/On-disk-data-size-for-UINT32.png)
- content / image: [On disk data size for UINT64](https://www.percona.com/wp-content/uploads/2026/03/On-disk-data-size-for-UINT64.png)
- content / image: [Q1 time for UINT32](https://www.percona.com/wp-content/uploads/2026/03/Q1-time-for-UINT32.png)
- content / image: [Q1 time for UINT64](https://www.percona.com/wp-content/uploads/2026/03/Q1-time-for-UINT64.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
