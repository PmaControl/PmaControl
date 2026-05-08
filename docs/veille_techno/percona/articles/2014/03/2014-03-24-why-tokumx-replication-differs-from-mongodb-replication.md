---
title: Why TokuMX Replication Differs from MongoDB Replication
source:
  name: Percona Blog
  url: https://www.percona.com/blog/why-tokumx-replication-differs-from-mongodb-replication/
  post_id: 3204
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2014-03-24T12:33:18'
published_at_gmt: '2014-03-24T12:33:18'
modified_at: '2026-03-23T22:09:37'
modified_at_gmt: '2026-03-23T22:09:37'
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
- Replication
- tokumx
tag_slugs:
- fractal-tree-indexes
- mongodb
- replication
- tokumx
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Why TokuMX Replication Differs from MongoDB Replication

Source: [Percona Blog](https://www.percona.com/blog/why-tokumx-replication-differs-from-mongodb-replication/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2014-03-24T12:33:18

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MongoDB replication has some great features. As I discussed in my last post, MongoDB’s crash safety design is very elegant. In addition to that, MongoDB has automatic failover, parallel slave replication, and prefetch threads on secondaries. The latter, as Mark Callaghan points out, is similar to “InnoDB fake changes”, a feature that has helped Facebook … Continued
