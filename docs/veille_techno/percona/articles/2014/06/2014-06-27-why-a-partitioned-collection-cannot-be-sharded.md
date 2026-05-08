---
title: Why a Partitioned Collection Cannot Be Sharded
source:
  name: Percona Blog
  url: https://www.percona.com/blog/why-a-partitioned-collection-cannot-be-sharded/
  post_id: 9881
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2014-06-27T15:31:04'
published_at_gmt: '2014-06-27T15:31:04'
modified_at: '2026-03-25T18:29:08'
modified_at_gmt: '2026-03-25T18:29:08'
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
- MongoDB
- partitioned collection
- partitioning
- sharding
- tokumx
tag_slugs:
- mongodb
- partitioned-collection
- partitioning
- sharding
- tokumx
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Why a Partitioned Collection Cannot Be Sharded

Source: [Percona Blog](https://www.percona.com/blog/why-a-partitioned-collection-cannot-be-sharded/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2014-06-27T15:31:04

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In TokuMX 1.5, we introduced partitioned collections for non-sharded clusters. That is, one can have a partitioned collection in a replica set, but one cannot shard a partitioned collection. In this post, I explain why. As I mentioned here, partitioned collections are useful for time-series data where we would like to keep a rolling period … Continued
