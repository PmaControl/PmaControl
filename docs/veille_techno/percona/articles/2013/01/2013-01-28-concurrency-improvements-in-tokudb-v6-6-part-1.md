---
title: Concurrency Improvements in TokuDB v6.6 (Part 1)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/concurrency-improvements-in-tokudb-v6-6-part-1/
  post_id: 9756
source_author:
  name: Leif.Walsh
  slug: leif-walsh
  url: https://www.percona.com/blog/author/leif-walsh/
  website: ''
published_at: '2013-01-28T17:05:09'
published_at_gmt: '2013-01-28T17:05:09'
modified_at: '2026-03-25T18:25:14'
modified_at_gmt: '2026-03-25T18:25:14'
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
- Fractal Tree™ indexes
- MySQL
- NewSQL
- Storage Engine
- TokuDB
- Tokutek
tag_slugs:
- fractal-tree-indexes
- mysql
- newsql
- storage-engine
- tokudb
- tokutek
featured_image_url: https://www.percona.com/blog/wp-content/uploads/2013/01/promotion-iibench.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Concurrency Improvements in TokuDB v6.6 (Part 1)

Source: [Percona Blog](https://www.percona.com/blog/concurrency-improvements-in-tokudb-v6-6-part-1/)

Auteur source: [Leif.Walsh](https://www.percona.com/blog/author/leif-walsh/)

Publication: 2013-01-28T17:05:09

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

With TokuDB v6.6 out now, I’m excited to present one of my favorite enhancements: concurrency within a single index. Previously, while there could be many SQL transactions in-flight at any given moment, operations inside a single index were fairly serialized. We’ve been working on concurrency for a few versions, and things have been getting a … Continued

## Structure detectee

- H2: Summary of Results

## Images et graphiques reperes

- content / image: [promotion-iibench.png](https://www.percona.com/blog/wp-content/uploads/2013/01/promotion-iibench.png)
- content / image: [promotion-iibench-queries.png](https://www.percona.com/blog/wp-content/uploads/2013/01/promotion-iibench-queries.png)
- content / image: [promotion-queries.png](https://www.percona.com/blog/wp-content/uploads/2013/01/promotion-queries.png)
