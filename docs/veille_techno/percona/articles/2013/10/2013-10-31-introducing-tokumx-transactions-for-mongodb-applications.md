---
title: Introducing TokuMX Transactions for MongoDB Applications
source:
  name: Percona Blog
  url: https://www.percona.com/blog/introducing-tokumx-transactions-for-mongodb-applications/
  post_id: 9820
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2013-10-31T13:43:58'
published_at_gmt: '2013-10-31T13:43:58'
modified_at: '2026-05-04T22:43:21'
modified_at_gmt: '2026-05-04T22:43:21'
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
- tokumx
- transactions
tag_slugs:
- mongodb
- tokumx
- transactions
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Introducing TokuMX Transactions for MongoDB Applications

Source: [Percona Blog](https://www.percona.com/blog/introducing-tokumx-transactions-for-mongodb-applications/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2013-10-31T13:43:58

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Since our initial release last summer, TokuMX has supported fully ACID and MVCC multi-statement transactions. I’d like to take this post to explain exactly what we’ve done and what features are now available to the user. But before beginning, an important note: we have implemented this for non-sharded clusters only. We do not support … Continued
