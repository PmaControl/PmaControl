---
title: The Effects of Database Heap Storage Choices in MongoDB
source:
  name: Percona Blog
  url: https://www.percona.com/blog/the-effects-of-database-heap-storage-choices-in-mongodb/
  post_id: 9832
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2014-02-03T20:52:50'
published_at_gmt: '2014-02-03T20:52:50'
modified_at: '2026-03-25T18:27:37'
modified_at_gmt: '2026-03-25T18:27:37'
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
- clustering indexes
- InnoDB
- MongoDB
- MySQL
- TokuDB
- tokumx
tag_slugs:
- clustering-indexes
- innodb
- mongodb
- mysql
- tokudb
- tokumx
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# The Effects of Database Heap Storage Choices in MongoDB

Source: [Percona Blog](https://www.percona.com/blog/the-effects-of-database-heap-storage-choices-in-mongodb/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2014-02-03T20:52:50

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

William Zola over at MongoDB gave a great talk called “The (Only) Three Reasons for Slow MongoDB Performance”. It reminded me of an interesting characteristic of updates in MongoDB. Because MongoDB’s main data store is a flat file and secondary indexes store offsets into the flat file (as I explain here), if the location of … Continued
