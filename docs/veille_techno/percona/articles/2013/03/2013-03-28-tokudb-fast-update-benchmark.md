---
title: TokuDB Fast Update Benchmark
source:
  name: Percona Blog
  url: https://www.percona.com/blog/tokudb-fast-update-benchmark/
  post_id: 9767
source_author:
  name: Tim.Callaghan
  slug: tim-callaghan
  url: https://www.percona.com/blog/author/tim-callaghan/
  website: ''
published_at: '2013-03-28T14:47:11'
published_at_gmt: '2013-03-28T14:47:11'
modified_at: '2026-03-25T18:25:36'
modified_at_gmt: '2026-03-25T18:25:36'
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
category_slugs:
- mysql
tags:
- Benchmarking
- Fractal Tree™ indexes
- MySQL
- NewSQL
- TokuDB
- Tokutek
tag_slugs:
- benchmarking
- fractal-tree-indexes
- mysql
- newsql
- tokudb
- tokutek
featured_image_url: https://www.percona.com/blog/wp-content/uploads/2013/03/fast-updates-tps-innodb1.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# TokuDB Fast Update Benchmark

Source: [Percona Blog](https://www.percona.com/blog/tokudb-fast-update-benchmark/)

Auteur source: [Tim.Callaghan](https://www.percona.com/blog/author/tim-callaghan/)

Publication: 2013-03-28T14:47:11

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Last month my colleague Rich Prohaska covered the technical details of our “Fast Update” feature which we added to TokuDB in version 6.6. The message based architecture of Fractal Tree Indexes allows us to defer certain operations while still maintaining the semantics that MySQL users require. In the case of Fast Updates, TokuDB is avoiding the read-before-write … Continued

## Images et graphiques reperes

- content / image: [fast-updates-tps-innodb1.png](https://www.percona.com/blog/wp-content/uploads/2013/03/fast-updates-tps-innodb1.png)
- content / image: [fast-updates-tps-tokudb.png](https://www.percona.com/blog/wp-content/uploads/2013/03/fast-updates-tps-tokudb.png)
