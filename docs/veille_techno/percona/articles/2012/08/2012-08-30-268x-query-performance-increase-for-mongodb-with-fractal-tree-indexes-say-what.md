---
title: 268x Query Performance Increase for MongoDB with Fractal Tree Indexes, SAY WHAT?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/268x-query-performance-increase-for-mongodb-with-fractal-tree-indexes-say-what/
  post_id: 9707
source_author:
  name: Tim.Callaghan
  slug: tim-callaghan
  url: https://www.percona.com/blog/author/tim-callaghan/
  website: ''
published_at: '2012-08-30T15:46:28'
published_at_gmt: '2012-08-30T15:46:28'
modified_at: '2026-03-25T18:23:50'
modified_at_gmt: '2026-03-25T18:23:50'
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
- Fractal Trees
- Fractal Tree™ indexes
- MongoDB
- MySQL
- NewSQL
- NoSQL
- TokuDB
- Tokutek
tag_slugs:
- benchmarking
- fractal-trees
- fractal-tree-indexes
- mongodb
- mysql
- newsql
- nosql
- tokudb
- tokutek
featured_image_url: https://www.percona.com/blog/wp-content/uploads/2012/08/mongo-02-ips.png
image_count: 2
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# 268x Query Performance Increase for MongoDB with Fractal Tree Indexes, SAY WHAT?

Source: [Percona Blog](https://www.percona.com/blog/268x-query-performance-increase-for-mongodb-with-fractal-tree-indexes-say-what/)

Auteur source: [Tim.Callaghan](https://www.percona.com/blog/author/tim-callaghan/)

Publication: 2012-08-30T15:46:28

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Last week I wrote about our 10x insertion performance increase with MongoDB. We’ve continued our experimental integration of Fractal Tree® Indexes into MongoDB, adding support for clustered indexes. A clustered index stores all non-index fields as the “value” portion of the index, as opposed to a standard MongoDB index that stores a pointer to the … Continued

## Images et graphiques reperes

- content / image: [mongo-02-ips.png](https://www.percona.com/blog/wp-content/uploads/2012/08/mongo-02-ips.png)
- content / graph_or_chart: [mongo-02-query-latency.png](https://www.percona.com/blog/wp-content/uploads/2012/08/mongo-02-query-latency.png)
