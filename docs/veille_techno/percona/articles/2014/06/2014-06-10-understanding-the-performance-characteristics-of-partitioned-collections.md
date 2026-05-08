---
title: Understanding the Performance Characteristics of Partitioned Collections
source:
  name: Percona Blog
  url: https://www.percona.com/blog/understanding-the-performance-characteristics-of-partitioned-collections/
  post_id: 9877
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2014-06-10T14:21:09'
published_at_gmt: '2014-06-10T14:21:09'
modified_at: '2026-05-04T22:44:15'
modified_at_gmt: '2026-05-04T22:44:15'
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
- Fractal Trees
- MongoDB
- MySQL
- partitioning
- TokuDB
- tokumx
tag_slugs:
- fractal-trees
- mongodb
- mysql
- partitioning
- tokudb
- tokumx
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Understanding the Performance Characteristics of Partitioned Collections

Source: [Percona Blog](https://www.percona.com/blog/understanding-the-performance-characteristics-of-partitioned-collections/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2014-06-10T14:21:09

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In TokuMX 1.5 that is right around the corner, the big feature will be partitioned collections. This feature is similar to partitioned tables in Oracle, MySQL, SQL Server, and Postgres. A question many have is “why should I use partitioned tables?” In short, it’s complicated. The answer depends on your workload, your schema, and your … Continued
