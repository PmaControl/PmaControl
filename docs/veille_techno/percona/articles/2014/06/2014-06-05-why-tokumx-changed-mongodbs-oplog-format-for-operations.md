---
title: Why TokuMX Changed MongoDB’s Oplog Format for Operations
source:
  name: Percona Blog
  url: https://www.percona.com/blog/why-tokumx-changed-mongodbs-oplog-format-for-operations/
  post_id: 9873
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2014-06-05T12:56:16'
published_at_gmt: '2014-06-05T12:56:16'
modified_at: '2026-05-04T22:43:48'
modified_at_gmt: '2026-05-04T22:43:48'
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
- B-Tree
- Fractal Tree™ indexes
- MongoDB
- oplog
- Replication
- tokumx
tag_slugs:
- b-tree
- fractal-tree-indexes
- mongodb
- oplog
- replication
- tokumx
featured_image_url: https://www.percona.com/blog/wp-content/uploads/2014/06/replication-io-preso.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Why TokuMX Changed MongoDB’s Oplog Format for Operations

Source: [Percona Blog](https://www.percona.com/blog/why-tokumx-changed-mongodbs-oplog-format-for-operations/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2014-06-05T12:56:16

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Over several posts, I’ve explained the differences between TokuMX replication and MongoDB replication, and why they are completely incompatible. In this (belated) post, I explain one last difference: the oplog format for operations. Specifically, TokuMX and MongoDB log updates and deletes differently. Suppose we have a collection foo, with the following element: <code> rs0:PRIMARY> db.foo.find() { "_id" : 0, "a" : 0, "b" : 0 } </code> 1 2 3 4 < code > rs0 : PRIMARY > db . foo . find ( ) { "_id" : 0 , "a" : 0 , "b" : 0 } < / code > … Continued

## Images et graphiques reperes

- content / image: [replication-io-preso](https://www.percona.com/blog/wp-content/uploads/2014/06/replication-io-preso.png)
