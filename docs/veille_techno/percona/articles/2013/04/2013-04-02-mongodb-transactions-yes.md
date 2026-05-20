---
title: MongoDB Transactions? Yes
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mongodb-transactions-yes/
  post_id: 9769
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2013-04-02T14:32:17'
published_at_gmt: '2013-04-02T14:32:17'
modified_at: '2026-03-25T18:25:37'
modified_at_gmt: '2026-03-25T18:25:37'
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
- Fractal Tree™ indexes
- MongoDB
- MySQL
- NoSQL
- TokuDB
- Tokutek
tag_slugs:
- fractal-tree-indexes
- mongodb
- mysql
- nosql
- tokudb
- tokutek
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MongoDB Transactions? Yes

Source: [Percona Blog](https://www.percona.com/blog/mongodb-transactions-yes/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2013-04-02T14:32:17

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

People claim that MongoDB is not transactional. It actually is, and that’s a good thing. In MongoDB 2.2, individual operations are Atomic. By having per database locks control reads and writes to collections, write operations on collections are Consistent and Isolated. With journaling on, operations may be made Durable. Put these properties together, and you … Continued
