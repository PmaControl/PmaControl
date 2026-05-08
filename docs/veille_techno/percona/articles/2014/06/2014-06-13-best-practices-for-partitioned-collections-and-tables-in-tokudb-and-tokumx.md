---
title: Best Practices for Partitioned Collections and Tables in TokuDB and TokuMX
source:
  name: Percona Blog
  url: https://www.percona.com/blog/best-practices-for-partitioned-collections-and-tables-in-tokudb-and-tokumx/
  post_id: 3269
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2014-06-13T15:47:54'
published_at_gmt: '2014-06-13T15:47:54'
modified_at: '2026-03-23T22:12:16'
modified_at_gmt: '2026-03-23T22:12:16'
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
- MySQL
- partitioned collection
- partitioning
- TokuDB
- tokumx
tag_slugs:
- mongodb
- mysql
- partitioned-collection
- partitioning
- tokudb
- tokumx
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Best Practices for Partitioned Collections and Tables in TokuDB and TokuMX

Source: [Percona Blog](https://www.percona.com/blog/best-practices-for-partitioned-collections-and-tables-in-tokudb-and-tokumx/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2014-06-13T15:47:54

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In my last post, I gave a technical explanation of the performance characteristics of partitioned collections in TokuMX 1.5 (which is right around the corner) and partitioned tables in relational databases. Given those performance characteristics, in this post, I will present some best practices when using this feature in TokuMX or TokuDB. Note that these … Continued
