---
title: Four Benefits of TokuMX Transactions for MongoDB Applications
source:
  name: Percona Blog
  url: https://www.percona.com/blog/four-benefits-of-tokumx-transactions-for-mongodb-applications/
  post_id: 9821
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2013-11-07T14:23:19'
published_at_gmt: '2013-11-07T14:23:19'
modified_at: '2026-05-05T22:19:07'
modified_at_gmt: '2026-05-05T22:19:07'
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

# Four Benefits of TokuMX Transactions for MongoDB Applications

Source: [Percona Blog](https://www.percona.com/blog/four-benefits-of-tokumx-transactions-for-mongodb-applications/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2013-11-07T14:23:19

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

From the application’s perspective, TokuMX behaves very similarly, if not identically, to MongoDB in many ways. But in one subtle yet important way, on non-sharded clusters, TokuMX is different. With MongoDB, operations on each single document are transactional. With TokuMX, each statement is transactional. Although I explain this in my last post, let me reiterate … Continued
