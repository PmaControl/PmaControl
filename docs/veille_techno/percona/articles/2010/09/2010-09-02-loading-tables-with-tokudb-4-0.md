---
title: Loading Tables with TokuDB 4.0
source:
  name: Percona Blog
  url: https://www.percona.com/blog/loading-tables-with-tokudb-4-0/
  post_id: 9529
source_author:
  name: Tokutek
  slug: tokutek
  url: https://www.percona.com/blog/author/tokutek/
  website: ''
published_at: '2010-09-02T17:47:27'
published_at_gmt: '2010-09-02T17:47:27'
modified_at: '2026-04-29T14:46:53'
modified_at_gmt: '2026-04-29T14:46:53'
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
- MySQL
- TokuDB
- TokuDB loader
tag_slugs:
- mysql
- tokudb
- tokudb-loader
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Loading Tables with TokuDB 4.0

Source: [Percona Blog](https://www.percona.com/blog/loading-tables-with-tokudb-4-0/)

Auteur source: [Tokutek](https://www.percona.com/blog/author/tokutek/)

Publication: 2010-09-02T17:47:27

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Often, the first step in evaluating and deploying a database is to load an existing dataset into the database. In the latest version, TokuDB makes use of multi-core parallelism to speed up loading (and new index creation). Using the loader, MySQL tables using TokuDB load 5x-8x faster than with previous versions of TokuDB. Measuring Load … Continued

## Structure detectee

- H2: Measuring Load Performance
- H3: Load Test
- H2: Results
- H4: TokuDB Version 3 (~single-threaded) v. TokuDB Version 4 (multi-threaded)
- H4: Other metrics
