---
title: 'MongoDB Index Shootout: Covered Indexes vs. Clustered Fractal Tree Indexes'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mongodb-index-shootout-covered-indexes-vs-clustered-fractal-tree-indexes/
  post_id: 9709
source_author:
  name: Tim.Callaghan
  slug: tim-callaghan
  url: https://www.percona.com/blog/author/tim-callaghan/
  website: ''
published_at: '2012-09-06T17:15:53'
published_at_gmt: '2012-09-06T17:15:53'
modified_at: '2026-04-28T22:40:25'
modified_at_gmt: '2026-04-28T22:40:25'
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
- clustering indexes
- Fractal Tree™ indexes
- indexes
- indexing
- MongoDB
- MySQL
- NewSQL
- Tokutek
tag_slugs:
- benchmarking
- clustering-indexes
- fractal-tree-indexes
- indexes
- indexing
- mongodb
- mysql
- newsql
- tokutek
featured_image_url: https://www.percona.com/blog/wp-content/uploads/2012/09/benchmark03-insertion-performance.png
image_count: 2
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MongoDB Index Shootout: Covered Indexes vs. Clustered Fractal Tree Indexes

Source: [Percona Blog](https://www.percona.com/blog/mongodb-index-shootout-covered-indexes-vs-clustered-fractal-tree-indexes/)

Auteur source: [Tim.Callaghan](https://www.percona.com/blog/author/tim-callaghan/)

Publication: 2012-09-06T17:15:53

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In my two previous blogs I wrote about our implementation of Fractal Tree Indexes on MongoDB, showing a 10x insertion performance increase and a 268x query performance increase. MongoDB’s covered indexes can provide some performance benefits over a regular MongoDB index, as they reduce the amount of IO required to satisfy certain queries. In essence, … Continued

## Images et graphiques reperes

- content / image: [benchmark03-insertion-performance.png](https://www.percona.com/blog/wp-content/uploads/2012/09/benchmark03-insertion-performance.png)
- content / graph_or_chart: [benchmark03-query-latency.png](https://www.percona.com/blog/wp-content/uploads/2012/09/benchmark03-query-latency.png)
