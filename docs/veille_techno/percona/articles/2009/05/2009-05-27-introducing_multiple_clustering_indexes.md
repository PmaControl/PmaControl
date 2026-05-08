---
title: Introducing Multiple Clustering Indexes
source:
  name: Percona Blog
  url: https://www.percona.com/blog/introducing_multiple_clustering_indexes/
  post_id: 9465
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2009-05-27T22:24:00'
published_at_gmt: '2009-05-27T22:24:00'
modified_at: '2026-05-04T22:39:52'
modified_at_gmt: '2026-05-04T22:39:52'
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

# Introducing Multiple Clustering Indexes

Source: [Percona Blog](https://www.percona.com/blog/introducing_multiple_clustering_indexes/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2009-05-27T22:24:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this posting I’ll describe TokuDB’s multiple clustering index feature. In general (not just for TokuDB) a clustered index or a clustering index is an index that stores the all of the data for the rows. Quoting the MySQL 5.1 reference manual: Accessing a row through the clustered index is fast because the row data … Continued

## Structure detectee

- H3: How to define multiple clustered indexes
- H3: How clustering keys work
- H3: An example using clustering keys
- H3: A limitation of clustering keys
