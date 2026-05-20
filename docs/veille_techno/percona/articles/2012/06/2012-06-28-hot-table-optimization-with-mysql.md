---
title: Hot Table Optimization with MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/hot-table-optimization-with-mysql/
  post_id: 9689
source_author:
  name: Rich.Prohaska
  slug: rich-prohaska
  url: https://www.percona.com/blog/author/rich-prohaska/
  website: ''
published_at: '2012-06-28T15:36:57'
published_at_gmt: '2012-06-28T15:36:57'
modified_at: '2026-05-05T22:15:49'
modified_at_gmt: '2026-05-05T22:15:49'
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
- Big Data
- InnoDB
- MySQL
- NewSQL
- Storage Engine
- TokuDB
- Tokutek
tag_slugs:
- big-data
- innodb
- mysql
- newsql
- storage-engine
- tokudb
- tokutek
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Hot Table Optimization with MySQL

Source: [Percona Blog](https://www.percona.com/blog/hot-table-optimization-with-mysql/)

Auteur source: [Rich.Prohaska](https://www.percona.com/blog/author/rich-prohaska/)

Publication: 2012-06-28T15:36:57

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Table optimization is a necessary evil; tables sometimes need to be optimized to reclaim space or to improve query performance. Unfortunately, MySQL blocks writes to a table while it is being optimized. Because optimization time is proportional to the table size, writes can be blocked for a long time. Fractal Tree indexes support online optimization; … Continued
