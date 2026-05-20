---
title: 'TokuMX vs. MongoDB : Sharding Balancer Performance'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/tokumx-vs-mongodb-sharding-balancer-performance/
  post_id: 9842
source_author:
  name: Tim.Callaghan
  slug: tim-callaghan
  url: https://www.percona.com/blog/author/tim-callaghan/
  website: ''
published_at: '2014-02-16T15:47:36'
published_at_gmt: '2014-02-16T15:47:36'
modified_at: '2026-03-25T18:27:54'
modified_at_gmt: '2026-03-25T18:27:54'
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
- MongoDB
- tokumx
tag_slugs:
- benchmarking
- mongodb
- tokumx
featured_image_url: https://www.percona.com/blog/wp-content/uploads/2014/02/blog-15-chunkmove.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# TokuMX vs. MongoDB : Sharding Balancer Performance

Source: [Percona Blog](https://www.percona.com/blog/tokumx-vs-mongodb-sharding-balancer-performance/)

Auteur source: [Tim.Callaghan](https://www.percona.com/blog/author/tim-callaghan/)

Publication: 2014-02-16T15:47:36

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I have always believed that TokuMX’s Fractal Tree indexes are an ideal fit for MongoDB’s sharding model, especially when it comes time for balancing to occur. At a very high level, balancing is needed when one shard contains more chunks than another. The actual formula is well described in the MongoDB documentation. Balancing shards impacts … Continued

## Images et graphiques reperes

- content / image: [blog-15-chunkmove](https://www.percona.com/blog/wp-content/uploads/2014/02/blog-15-chunkmove.png)
