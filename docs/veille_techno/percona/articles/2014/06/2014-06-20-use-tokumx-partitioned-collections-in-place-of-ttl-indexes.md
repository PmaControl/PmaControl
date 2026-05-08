---
title: Use TokuMX Partitioned Collections in Place of TTL Indexes
source:
  name: Percona Blog
  url: https://www.percona.com/blog/use-tokumx-partitioned-collections-in-place-of-ttl-indexes/
  post_id: 9878
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2014-06-20T14:22:31'
published_at_gmt: '2014-06-20T14:22:31'
modified_at: '2026-03-25T18:29:02'
modified_at_gmt: '2026-03-25T18:29:02'
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
- capped collection
- MongoDB
- partitioned collections
- partitioning
- tokumx
- TTL
- TTL indexes
tag_slugs:
- capped-collection
- mongodb
- partitioned-collections
- partitioning
- tokumx
- ttl
- ttl-indexes
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Use TokuMX Partitioned Collections in Place of TTL Indexes

Source: [Percona Blog](https://www.percona.com/blog/use-tokumx-partitioned-collections-in-place-of-ttl-indexes/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2014-06-20T14:22:31

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Take the following scenario. You have a time-series data application for which you would like to store a rolling period of data. For example, you may want to maintain the last six months of traffic logs for a website, in order to analyze activity of different periods of time. Or, you have an application maintaining … Continued
