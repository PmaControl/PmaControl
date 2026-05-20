---
title: On TokuMX (and MongoDB) Replication and Transactions
source:
  name: Percona Blog
  url: https://www.percona.com/blog/on-tokumx-and-mongodb-replication-and-transactions/
  post_id: 9858
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2014-03-28T12:42:23'
published_at_gmt: '2014-03-28T12:42:23'
modified_at: '2026-03-25T18:28:20'
modified_at_gmt: '2026-03-25T18:28:20'
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

# On TokuMX (and MongoDB) Replication and Transactions

Source: [Percona Blog](https://www.percona.com/blog/on-tokumx-and-mongodb-replication-and-transactions/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2014-03-28T12:42:23

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In my last post, I describe the differences between a TokuMX oplog entry and a MongoDB oplog entry. One reason why the entries are so different is that TokuMX supports multi-statement and multi-document transactions. In this post, I want to elaborate on why multi-statement transactions cause changes to the oplog, and explain how we changed … Continued
