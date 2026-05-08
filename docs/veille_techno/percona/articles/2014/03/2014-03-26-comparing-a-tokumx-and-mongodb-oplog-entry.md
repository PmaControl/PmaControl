---
title: Comparing a TokuMX and MongoDB Oplog Entry
source:
  name: Percona Blog
  url: https://www.percona.com/blog/comparing-a-tokumx-and-mongodb-oplog-entry/
  post_id: 9857
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2014-03-26T15:14:08'
published_at_gmt: '2014-03-26T15:14:08'
modified_at: '2026-05-05T17:47:02'
modified_at_gmt: '2026-05-05T17:47:02'
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
- oplog
- Replication
- tokumx
tag_slugs:
- mongodb
- oplog
- replication
- tokumx
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Comparing a TokuMX and MongoDB Oplog Entry

Source: [Percona Blog](https://www.percona.com/blog/comparing-a-tokumx-and-mongodb-oplog-entry/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2014-03-26T15:14:08

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

As I mentioned in my last post, TokuMX replication is completely incompatible with MongoDB replication. Replica sets (and sharded clusters, but that is for another blog) must be either entirely TokuMX or entirely MongoDB. This is by design. While elections and failover are basically the same, we have completely changed the oplog protocol. In the … Continued
