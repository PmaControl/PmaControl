---
title: Clustering indexes vs. Covering indexes
source:
  name: Percona Blog
  url: https://www.percona.com/blog/clustering_indexes_vs_covering_indexes/
  post_id: 9467
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2009-05-29T03:51:00'
published_at_gmt: '2009-05-29T03:51:00'
modified_at: '2026-04-28T22:24:05'
modified_at_gmt: '2026-04-28T22:24:05'
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
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Clustering indexes vs. Covering indexes

Source: [Percona Blog](https://www.percona.com/blog/clustering_indexes_vs_covering_indexes/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2009-05-29T03:51:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Yesterday, I (Zardosht) posted an entry introducing clustering indexes. Here, I elaborate on three differences between a clustering index and a covering index: Clustering indexes can create indexes that would otherwise bounce up against the limits on the maximum length and maximum number of columns in a MySQL index. Clustering indexes simplify syntax … Continued

## Structure detectee

- H3: Expanding MySQL’s Limits
- H3: Syntactic simplification
- H3: Smaller key sizes
- H3: Tradeoffs for TokuDB vs. other storage engines
- H3: Conclusion
